<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class VaultFtpService
{
    /**
     * Upload a file to the vault via FTP
     *
     * @param string $localPath - Path to local file (e.g., 'contracts/contract_123.pdf')
     * @param string $remotePath - Remote filename on FTP server
     * @return array ['success' => bool, 'path' => string|null, 'error' => string|null]
     */
    public function uploadToVault(string $localPath, string $remotePath): array
    {
        try {
            // Check if local file exists
            if (!Storage::disk('public')->exists($localPath)) {
                Log::error('Local file not found for FTP upload', [
                    'local_path' => $localPath
                ]);
                return [
                    'success' => false,
                    'path' => null,
                    'error' => 'Local file not found'
                ];
            }

            // Get file contents
            $fileContents = Storage::disk('public')->get($localPath);
            
            // Upload to vault FTP (directly to root, no subdirectories)
            $uploaded = Storage::disk('vault_ftp')->put($remotePath, $fileContents);

            if ($uploaded) {
                Log::info('File uploaded to vault successfully', [
                    'local_path' => $localPath,
                    'remote_path' => $remotePath
                ]);
                
                return [
                    'success' => true,
                    'path' => $remotePath,
                    'error' => null
                ];
            } else {
                Log::error('FTP upload failed', [
                    'local_path' => $localPath,
                    'remote_path' => $remotePath
                ]);
                
                return [
                    'success' => false,
                    'path' => null,
                    'error' => 'FTP upload returned false'
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
                'error' => $e->getMessage()
            ];
        }
    }

	/**
	 * Generate standardized filename for contract PDF
	 * Format: IVUE_CUSTOMER_NUMBER-IVUE_ACCOUNT-ACCOUNT_NAME-SUBSCRIBER-MOBILE_NUMBER.pdf
	 * Example: 50208810-12345-Hay_Communications-John_Smith-5195551234.pdf
	 */
	 public function getRemoteFilename($contract): string
	{
		// Get iVue customer number (format: 50208810)
		$ivueCustomerNumber = $contract->subscriber->mobilityAccount->ivueAccount->customer->ivue_customer_number;
		
		// Get iVue account number
		$ivueAccount = $contract->subscriber->mobilityAccount->ivueAccount->ivue_account;
		
		// Get account/company name (Title Case)
		$accountName = $contract->subscriber->mobilityAccount->ivueAccount->customer->display_name;
		$safeAccountName = $this->sanitizeFilename($accountName);
		
		// Get subscriber name (Title Case)
		$subscriberName = $contract->subscriber->first_name . ' ' . $contract->subscriber->last_name;
		$safeSubscriberName = $this->sanitizeFilename($subscriberName);
		
		// Get mobile number (remove non-digits)
		$mobileNumber = preg_replace('/[^0-9]/', '', $contract->subscriber->mobile_number);
		
		// Build filename
		$filename = "{$ivueCustomerNumber}-{$ivueAccount}-{$safeAccountName}-{$safeSubscriberName}-{$mobileNumber}.pdf";
		
		// Ensure filename isn't too long (max 255 chars for most filesystems)
		if (strlen($filename) > 255) {
			// Truncate account name if needed
			$maxAccountNameLength = 255 - strlen("{$ivueCustomerNumber}-{$ivueAccount}--{$safeSubscriberName}-{$mobileNumber}.pdf");
			$safeAccountName = substr($safeAccountName, 0, $maxAccountNameLength);
			$filename = "{$ivueCustomerNumber}-{$ivueAccount}-{$safeAccountName}-{$safeSubscriberName}-{$mobileNumber}.pdf";
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