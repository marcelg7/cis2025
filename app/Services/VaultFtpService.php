<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class VaultFtpService
{
    /**
     * Check if vault is in test mode
     */
    public function isTestMode(): bool
    {
        return config('filesystems.disks.vault_ftp.test_mode', true);
    }

    /**
     * Upload a file to the vault via FTP (or simulate in test mode)
     *
     * @param string $localPath - Path to local file (e.g., 'contracts/contract_123.pdf')
     * @param string $remotePath - Remote filename on FTP server
     * @return array ['success' => bool, 'path' => string|null, 'error' => string|null, 'test_mode' => bool]
     */
    public function uploadToVault(string $localPath, string $remotePath): array
    {
        // Test mode - simulate successful upload without actually uploading
        if ($this->isTestMode()) {
            Log::info('Vault test mode: Simulating FTP upload', [
                'local_path' => $localPath,
                'remote_filename' => $remotePath
            ]);

            return [
                'success' => true,
                'path' => '/test/' . $remotePath,
                'test_mode' => true,
                'error' => null
            ];
        }

        // Production mode - actual FTP upload
        // Note: VAULT_FTP_PATH should be set to /Scan/ in .env for NISC Vault integration
        try {
            // Check if local file exists
            if (!Storage::disk('public')->exists($localPath)) {
                Log::error('Local file not found for FTP upload', [
                    'local_path' => $localPath,
                    'remote_path' => $remotePath
                ]);
                return [
                    'success' => false,
                    'path' => null,
                    'error' => 'Local file not found',
                    'test_mode' => false
                ];
            }

            // Get file contents
            $fileContents = Storage::disk('public')->get($localPath);

            // Upload to vault FTP (path configured via VAULT_FTP_PATH in .env)
            $uploaded = Storage::disk('vault_ftp')->put($remotePath, $fileContents);

            if ($uploaded) {
                Log::info('File uploaded to vault successfully', [
                    'local_path' => $localPath,
                    'remote_path' => $remotePath
                ]);

                return [
                    'success' => true,
                    'path' => $remotePath,
                    'error' => null,
                    'test_mode' => false
                ];
            } else {
                // Try to get more diagnostic information
                $ftpConfig = config('filesystems.disks.vault_ftp');
                Log::error('FTP upload failed', [
                    'local_path' => $localPath,
                    'remote_path' => $remotePath,
                    'ftp_host' => $ftpConfig['host'] ?? 'not set',
                    'ftp_root' => $ftpConfig['root'] ?? 'not set',
                    'ftp_passive' => $ftpConfig['passive'] ?? 'not set'
                ]);

                return [
                    'success' => false,
                    'path' => null,
                    'error' => 'FTP upload returned false - check FTP permissions and directory access',
                    'test_mode' => false
                ];
            }
        } catch (\Exception $e) {
            Log::error('Exception during FTP upload', [
                'local_path' => $localPath,
                'remote_path' => $remotePath,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'path' => null,
                'error' => $e->getMessage(),
                'test_mode' => false
            ];
        }
    }

    /**
     * Generate standardized filename for contract PDF
     * Format: CUSTOMER_NUMBER-ACCOUNT_NUMBER-CUSTOMER_NAME-SUBSCRIBER_NAME-PHONE-Contract-CONTRACT_ID.pdf
     * Example: 50215279-10220876-Marcel_Gelinas-Marcel_Gelinas-519-3177086-Contract-18.pdf
     */
    public function getRemoteFilename($contract): string
    {
        // Get iVue customer number (format: 50208810)
        $ivueCustomerNumber = $contract->subscriber->mobilityAccount->ivueAccount->customer->ivue_customer_number;

        // Get iVue account number
        $ivueAccount = $contract->subscriber->mobilityAccount->ivueAccount->ivue_account;

        // Get customer name (First Last format, not display_name which can be Last First)
        $customer = $contract->subscriber->mobilityAccount->ivueAccount->customer;
        $customerName = $customer->first_name . ' ' . $customer->last_name;
        $safeCustomerName = $this->sanitizeFilename($customerName);

        // Get subscriber name (Title Case)
        $subscriberName = $contract->subscriber->first_name . ' ' . $contract->subscriber->last_name;
        $safeSubscriberName = $this->sanitizeFilename($subscriberName);

        // Get mobile number (remove non-digits, then format as XXX-XXXXXXX)
        $mobileNumber = preg_replace('/[^0-9]/', '', $contract->subscriber->mobile_number);
        // Format phone number with dashes (XXX-XXXXXXX for 10-digit, or just the number as-is for other lengths)
        if (strlen($mobileNumber) === 10) {
            $formattedPhone = substr($mobileNumber, 0, 3) . '-' . substr($mobileNumber, 3);
        } else {
            $formattedPhone = $mobileNumber;
        }

        // Get contract ID
        $contractId = $contract->id;

        // Build filename
        $filename = "{$ivueCustomerNumber}-{$ivueAccount}-{$safeCustomerName}-{$safeSubscriberName}-{$formattedPhone}-Contract-{$contractId}.pdf";

        // Ensure filename isn't too long (max 255 chars for most filesystems)
        if (strlen($filename) > 255) {
            // Truncate customer name if needed
            $maxCustomerNameLength = 255 - strlen("{$ivueCustomerNumber}-{$ivueAccount}--{$safeSubscriberName}-{$formattedPhone}-Contract-{$contractId}.pdf");
            $safeCustomerName = substr($safeCustomerName, 0, $maxCustomerNameLength);
            $filename = "{$ivueCustomerNumber}-{$ivueAccount}-{$safeCustomerName}-{$safeSubscriberName}-{$formattedPhone}-Contract-{$contractId}.pdf";
        }

        return $filename;
    }
        
    /**
     * Sanitize string for use in filename
     * Converts to Title Case, replaces spaces with underscores, removes special chars
     */
    private function sanitizeFilename(string $name): string
    {
        // Convert to Title Case (first letter of each word capitalized)
        $name = ucwords(strtolower($name));
        
        // Replace spaces with underscores
        $name = str_replace(' ', '_', $name);
        
        // Remove any character that's not alphanumeric, underscore, or hyphen
        $name = preg_replace('/[^A-Za-z0-9_\-]/', '', $name);
        
        // Replace multiple underscores/hyphens with single one
        $name = preg_replace('/[_\-]+/', '_', $name);
        
        // Trim underscores/hyphens from start and end
        $name = trim($name, '_-');
        
        return $name;
    }
}