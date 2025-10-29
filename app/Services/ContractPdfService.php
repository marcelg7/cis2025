<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\TermsOfService;
use Barryvdh\DomPDF\Facade\Pdf;
use HTMLPurifier;
use HTMLPurifier_Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Fpdi;

class ContractPdfService
{
    /**
     * Generate the merged PDF content for a contract.
     *
     * @param Contract $contract
     * @return string The raw PDF content (merged)
     */
    public function generateMergedPdfContent(Contract $contract): string
    {
        // Load relations
        $contract->load([
            'addOns',
            'oneTimeFees',
            'subscriber.mobilityAccount.ivueAccount.customer',
            'activityType',
            'commitmentPeriod',
            'ratePlan',
            'mobileInternetPlan',
            'bellDevice',
            'locationModel'
        ]);

        // Financial calculations
        $devicePrice = $contract->bell_retail_price ?? $contract->device_price ?? 0;
        $deviceAmount = $devicePrice - ($contract->agreement_credit_amount ?? 0);
        $totalFinancedAmount = $deviceAmount - ($contract->required_upfront_payment ?? 0) - ($contract->optional_down_payment ?? 0);
        $deferredPayment = $contract->deferred_payment_amount ?? 0;
        $remainingBalance = $totalFinancedAmount - $deferredPayment;
        $monthlyDevicePayment = $remainingBalance / 24;
        $earlyCancellationFee = $totalFinancedAmount + ($contract->bell_dro_amount ?? 0);
        $monthlyReduction = $monthlyDevicePayment;
        
        // NEW: Calculate buyout cost using CSR's formula
        $buyoutCost = ($devicePrice - $deferredPayment) / 24;
        
        // NEW: Get and format the cancellation policy from CommitmentPeriod
        $cancellationPolicy = '';
        if ($contract->commitmentPeriod && $contract->commitmentPeriod->cancellation_policy) {
            $cancellationPolicy = str_replace(
                [
                    '{balance}',
                    '{monthly_reduction}',
                    '{start_date}',
                    '{end_date}',
                    '{buyout_cost}',
                    '{device_return_option}'
                ],
                [
                    number_format($remainingBalance, 2),
                    number_format($buyoutCost, 2),
                    $contract->start_date->format('M d, Y'),
                    $contract->end_date->format('M d, Y'),
                    number_format($buyoutCost, 2),
                    number_format($deferredPayment, 2)
                ],
                $contract->commitmentPeriod->cancellation_policy
            );
        }
        
        $totalAddOnCost = $contract->addOns->sum('cost') ?? 0;
        $totalOneTimeFeeCost = $contract->oneTimeFees->sum('cost') ?? 0;
        $totalCost = ($totalAddOnCost + ($contract->rate_plan_price ?? $contract->bell_plan_cost ?? 0) + $monthlyDevicePayment) * 24 + $totalOneTimeFeeCost;

        Log::debug('Calculated financials', [
            'contract_id' => $contract->id,
            'devicePrice' => $devicePrice,
            'buyoutCost' => $buyoutCost, // NEW
        ]);

        // Sanitize HTML
        $config = HTMLPurifier_Config::createDefault();
        $config->set('Core.Encoding', 'UTF-8');
        $config->set('HTML.Doctype', 'HTML 4.01 Transitional');
        $config->set('HTML.Allowed', 'p,strong,em,ul,ol,li,a[href|title],br,div[class],span[class],table,tr,td,th,hr');
        $config->set('AutoFormat.AutoParagraph', true);
        $config->set('AutoFormat.Linkify', true);
        $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true, 'ftp' => true]);
        $config->set('Cache.SerializerPath', storage_path('htmlpurifier'));
        $purifier = new HTMLPurifier($config);

        if ($contract->ratePlan && $contract->ratePlan->features) {
            $contract->ratePlan->features = $purifier->purify($contract->ratePlan->features);
        }
        if ($contract->mobileInternetPlan && $contract->mobileInternetPlan->description) {
            $contract->mobileInternetPlan->description = $purifier->purify($contract->mobileInternetPlan->description);
        }

        // Prepare view data
        $viewData = compact(
            'contract',
            'totalAddOnCost',
            'totalOneTimeFeeCost',
            'totalCost',
            'devicePrice',
            'deviceAmount',
            'totalFinancedAmount',
            'monthlyDevicePayment',
            'earlyCancellationFee',
            'monthlyReduction',
            'buyoutCost',           // NEW
            'cancellationPolicy'    // NEW
        );

        // Generate main contract PDF using clean PDF view
        $pdf = Pdf::loadView('contracts.pdf-view', $viewData)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'dpi' => 300,
                'defaultFont' => 'sans-serif',
                'memory_limit' => '512M',
                'chroot' => base_path(),
                'isPhpEnabled' => false, // SECURITY: Never enable PHP in PDFs (prevents RCE)
                'margin_top' => 10,
                'margin_bottom' => 10,
                'margin_left' => 10,
                'margin_right' => 10,
            ]);

        $mainPdfContent = $pdf->output();

        // Initialize FPDI for merging
        $fpdi = new Fpdi();

        // Add main contract pages
        $tempFile = storage_path('app/temp/contract_' . $contract->id . '.pdf');
        if (!file_exists(dirname($tempFile))) {
            mkdir(dirname($tempFile), 0755, true);
        }
        file_put_contents($tempFile, $mainPdfContent);
        $pageCount = $fpdi->setSourceFile($tempFile);
        for ($i = 1; $i <= $pageCount; $i++) {
            $fpdi->AddPage();
            $tplIdx = $fpdi->importPage($i);
            $fpdi->useTemplate($tplIdx);
        }
        unlink($tempFile);

        // Add financing if applicable
        if ($contract->requiresFinancing() &&
            $contract->financing_status === 'finalized' &&
            $contract->financing_pdf_path &&
            Storage::disk('public')->exists($contract->financing_pdf_path)) {
            
            Log::info('Adding financing PDF to merged document', ['contract_id' => $contract->id]);
            $financingPdfPath = storage_path('app/public/' . $contract->financing_pdf_path);
            $pageCount = $fpdi->setSourceFile($financingPdfPath);
            for ($i = 1; $i <= $pageCount; $i++) {
                $fpdi->AddPage();
                $tplIdx = $fpdi->importPage($i);
                $fpdi->useTemplate($tplIdx);
            }
        }

        // Add DRO if applicable
        if ($contract->requiresDro() &&
            $contract->dro_status === 'finalized' &&
            $contract->dro_pdf_path &&
            Storage::disk('public')->exists($contract->dro_pdf_path)) {
            
            Log::info('Adding DRO PDF to merged document', ['contract_id' => $contract->id]);
            $droPdfPath = storage_path('app/public/' . $contract->dro_pdf_path);
            $pageCount = $fpdi->setSourceFile($droPdfPath);
            for ($i = 1; $i <= $pageCount; $i++) {
                $fpdi->AddPage();
                $tplIdx = $fpdi->importPage($i);
                $fpdi->useTemplate($tplIdx);
            }
        }

        // NEW: Add active Terms of Service from database
        $activeTerms = TermsOfService::getActive();
        if ($activeTerms && Storage::disk('public')->exists($activeTerms->path)) {
            $termsPdfPath = storage_path('app/public/' . $activeTerms->path);
            Log::info('Adding Terms of Service to merged document', [
                'contract_id' => $contract->id,
                'tos_version' => $activeTerms->version,
                'tos_id' => $activeTerms->id,
                'tos_path' => $termsPdfPath,
                'file_exists' => file_exists($termsPdfPath),
                'file_size' => file_exists($termsPdfPath) ? filesize($termsPdfPath) : 0
            ]);

            try {
                $pageCount = $fpdi->setSourceFile($termsPdfPath);
                Log::info('Terms of Service PDF loaded successfully', [
                    'contract_id' => $contract->id,
                    'page_count' => $pageCount
                ]);

                for ($i = 1; $i <= $pageCount; $i++) {
                    $fpdi->AddPage();
                    $tplIdx = $fpdi->importPage($i);
                    $fpdi->useTemplate($tplIdx);
                }

                Log::info('Terms of Service pages added to merged PDF', [
                    'contract_id' => $contract->id,
                    'pages_added' => $pageCount
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to add Terms of Service to merged PDF', [
                    'contract_id' => $contract->id,
                    'tos_id' => $activeTerms->id,
                    'tos_path' => $termsPdfPath,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        } else {
            Log::warning('No active Terms of Service found or file does not exist', [
                'contract_id' => $contract->id,
                'has_active_terms' => !is_null($activeTerms),
                'path' => $activeTerms?->path ?? 'null',
                'storage_path' => $activeTerms ? storage_path('app/public/' . $activeTerms->path) : 'null'
            ]);
        }

        // LEGACY: Add old hardcoded terms files as fallback (remove once new system is in use)
        $legacyTermsFiles = [
            public_path('pdfs/OURAGREEMENTpage.pdf'),
            public_path('pdfs/HayCommTermsOfServicerev2020.pdf'),
        ];
        
        // Only use legacy files if no active Terms of Service exists
        if (!$activeTerms) {
            Log::info('Using legacy terms files as fallback', ['contract_id' => $contract->id]);
            foreach ($legacyTermsFiles as $file) {
                if (file_exists($file)) {
                    try {
                        $pageCount = $fpdi->setSourceFile($file);
                        for ($i = 1; $i <= $pageCount; $i++) {
                            $fpdi->AddPage();
                            $tplIdx = $fpdi->importPage($i);
                            $fpdi->useTemplate($tplIdx);
                        }
                    } catch (\Exception $e) {
                        Log::error('Failed to add legacy terms file', [
                            'file' => $file,
                            'error' => $e->getMessage()
                        ]);
                    }
                } else {
                    Log::warning('Legacy terms file not found', ['file' => $file]);
                }
            }
        }

        // Return merged content
        return $fpdi->Output('S');
    }
}