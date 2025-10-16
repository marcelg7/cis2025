<?php

namespace App\Services;

use App\Models\Contract;
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
            'bellDevice'
        ]);

        // Financial calculations
        $devicePrice = $contract->bell_retail_price ?? $contract->device_price ?? 0;
        $deviceAmount = $devicePrice - ($contract->agreement_credit_amount ?? 0);
        $totalFinancedAmount = $deviceAmount - ($contract->required_upfront_payment ?? 0) - ($contract->optional_down_payment ?? 0);
        $monthlyDevicePayment = ($totalFinancedAmount - ($contract->deferred_payment_amount ?? 0)) / 24;
        $earlyCancellationFee = $totalFinancedAmount + ($contract->bell_dro_amount ?? 0);
        $monthlyReduction = $monthlyDevicePayment;
        $totalAddOnCost = $contract->addOns->sum('cost') ?? 0;
        $totalOneTimeFeeCost = $contract->oneTimeFees->sum('cost') ?? 0;
        $totalCost = ($totalAddOnCost + ($contract->rate_plan_price ?? $contract->bell_plan_cost ?? 0) + $monthlyDevicePayment) * 24 + $totalOneTimeFeeCost;

        Log::debug('Calculated financials', [
            'contract_id' => $contract->id,
            'devicePrice' => $devicePrice,
            // ... add other vars if needed for debugging
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
            'monthlyReduction'
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
                'isPhpEnabled' => true,
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
        $attachFinancing = false;
        if ($contract->requiresFinancing() &&
            $contract->financing_status === 'finalized' &&
            $contract->financing_pdf_path &&
            Storage::disk('public')->exists($contract->financing_pdf_path)) {
            
            $attachFinancing = true;
            $financingPdfPath = storage_path('app/public/' . $contract->financing_pdf_path);
            $pageCount = $fpdi->setSourceFile($financingPdfPath);
            for ($i = 1; $i <= $pageCount; $i++) {
                $fpdi->AddPage();
                $tplIdx = $fpdi->importPage($i);
                $fpdi->useTemplate($tplIdx);
            }
        }

        // Add terms and conditions
        $termsFiles = [
            public_path('pdfs/OURAGREEMENTpage.pdf'),
            public_path('pdfs/HayCommTermsOfServicerev2020.pdf'),
        ];
        foreach ($termsFiles as $file) {
            if (file_exists($file)) {
                $pageCount = $fpdi->setSourceFile($file);
                for ($i = 1; $i <= $pageCount; $i++) {
                    $fpdi->AddPage();
                    $tplIdx = $fpdi->importPage($i);
                    $fpdi->useTemplate($tplIdx);
                }
            } else {
                Log::warning('Terms file not found', ['file' => $file]);
            }
        }

        // Return merged content
        return $fpdi->Output('S');
    }
}