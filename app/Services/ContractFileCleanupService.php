<?php

namespace App\Services;

use App\Models\Contract;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ContractFileCleanupService
{
    /**
     * Delete all contract-related files after successful vault upload
     */
    public function cleanupContractFiles(Contract $contract): array
    {
        $deletedFiles = [];
        $errors = [];

        try {
            // Main contract PDF
            if ($contract->pdf_path && Storage::disk('public')->exists($contract->pdf_path)) {
                Storage::disk('public')->delete($contract->pdf_path);
                $deletedFiles[] = $contract->pdf_path;
                Log::info('Deleted main contract PDF', ['contract_id' => $contract->id, 'path' => $contract->pdf_path]);
            }

            // Contract signature
            if ($contract->signature_path) {
                $signaturePath = str_replace('storage/', '', $contract->signature_path);
                if (Storage::disk('public')->exists($signaturePath)) {
                    Storage::disk('public')->delete($signaturePath);
                    $deletedFiles[] = $signaturePath;
                    Log::info('Deleted contract signature', ['contract_id' => $contract->id, 'path' => $signaturePath]);
                }
            }

            // Financing PDF
            if ($contract->financing_pdf_path && Storage::disk('public')->exists($contract->financing_pdf_path)) {
                Storage::disk('public')->delete($contract->financing_pdf_path);
                $deletedFiles[] = $contract->financing_pdf_path;
                Log::info('Deleted financing PDF', ['contract_id' => $contract->id, 'path' => $contract->financing_pdf_path]);
            }

            // Financing signature
            if ($contract->financing_signature_path) {
                $financingSignaturePath = str_replace('storage/', '', $contract->financing_signature_path);
                if (Storage::disk('public')->exists($financingSignaturePath)) {
                    Storage::disk('public')->delete($financingSignaturePath);
                    $deletedFiles[] = $financingSignaturePath;
                    Log::info('Deleted financing signature', ['contract_id' => $contract->id, 'path' => $financingSignaturePath]);
                }
            }

            // Financing CSR initials
            if ($contract->financing_csr_initials_path) {
                $financingInitialsPath = str_replace('storage/', '', $contract->financing_csr_initials_path);
                if (Storage::disk('public')->exists($financingInitialsPath)) {
                    Storage::disk('public')->delete($financingInitialsPath);
                    $deletedFiles[] = $financingInitialsPath;
                    Log::info('Deleted financing CSR initials', ['contract_id' => $contract->id, 'path' => $financingInitialsPath]);
                }
            }

            // DRO PDF
            if ($contract->dro_pdf_path && Storage::disk('public')->exists($contract->dro_pdf_path)) {
                Storage::disk('public')->delete($contract->dro_pdf_path);
                $deletedFiles[] = $contract->dro_pdf_path;
                Log::info('Deleted DRO PDF', ['contract_id' => $contract->id, 'path' => $contract->dro_pdf_path]);
            }

            // DRO signature
            if ($contract->dro_signature_path) {
                $droSignaturePath = str_replace('storage/', '', $contract->dro_signature_path);
                if (Storage::disk('public')->exists($droSignaturePath)) {
                    Storage::disk('public')->delete($droSignaturePath);
                    $deletedFiles[] = $droSignaturePath;
                    Log::info('Deleted DRO signature', ['contract_id' => $contract->id, 'path' => $droSignaturePath]);
                }
            }

            // DRO CSR initials
            if ($contract->dro_csr_initials_path) {
                $droInitialsPath = str_replace('storage/', '', $contract->dro_csr_initials_path);
                if (Storage::disk('public')->exists($droInitialsPath)) {
                    Storage::disk('public')->delete($droInitialsPath);
                    $deletedFiles[] = $droInitialsPath;
                    Log::info('Deleted DRO CSR initials', ['contract_id' => $contract->id, 'path' => $droInitialsPath]);
                }
            }

        } catch (\Exception $e) {
            $errors[] = $e->getMessage();
            Log::error('Error during file cleanup', [
                'contract_id' => $contract->id,
                'error' => $e->getMessage()
            ]);
        }

        return [
            'deleted_count' => count($deletedFiles),
            'deleted_files' => $deletedFiles,
            'errors' => $errors
        ];
    }

    /**
     * Check if all required files for cleanup exist
     */
    public function canCleanup(Contract $contract): bool
    {
        // Only cleanup if successfully uploaded to vault
        return $contract->ftp_to_vault === true && $contract->vault_path !== null;
    }
}