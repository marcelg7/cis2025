<?php

namespace App\Console\Commands;

use App\Models\Contract;
use App\Models\User;
use App\Models\NotificationPreference;
use App\Notifications\ContractPendingSignatureNotification;
use App\Notifications\FtpUploadFailedNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckPendingContractsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:check-pending-contracts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for contracts pending signature > 24 hours and failed FTP uploads';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for pending contracts...');

        // Check for contracts pending signature > 24 hours
        $this->checkPendingSignatures();

        // Check for contracts with failed FTP uploads
        $this->checkFailedFtpUploads();

        $this->info('Completed checking pending contracts.');
    }

    /**
     * Check for contracts pending signature > 24 hours
     */
    protected function checkPendingSignatures()
    {
        $threshold = Carbon::now()->subHours(24);

        $pendingContracts = Contract::where('status', 'draft')
            ->where('created_at', '<=', $threshold)
            ->get();

        if ($pendingContracts->isEmpty()) {
            $this->info('No contracts pending signature > 24 hours');
            return;
        }

        $this->info("Found {$pendingContracts->count()} contracts pending signature > 24 hours");

        // Get all users who should receive these notifications
        $users = User::all()->filter(function ($user) {
            return NotificationPreference::isEnabled($user->id, 'contract_pending_signature');
        });

        foreach ($pendingContracts as $contract) {
            $hoursPending = $contract->created_at->diffInHours(Carbon::now());

            foreach ($users as $user) {
                // Check if user already has an unread notification for this contract
                $existingNotification = $user->unreadNotifications()
                    ->where('type', ContractPendingSignatureNotification::class)
                    ->whereJsonContains('data->contract_id', $contract->id)
                    ->first();

                if (!$existingNotification) {
                    $user->notify(new ContractPendingSignatureNotification($contract, $hoursPending));
                    $this->info("  Notified {$user->name} about contract #{$contract->id}");
                }
            }
        }
    }

    /**
     * Check for contracts with failed FTP uploads
     */
    protected function checkFailedFtpUploads()
    {
        $failedContracts = Contract::where('status', 'finalized')
            ->whereNull('ftp_to_vault')
            ->whereNotNull('ftp_error')
            ->get();

        if ($failedContracts->isEmpty()) {
            $this->info('No contracts with failed FTP uploads');
            return;
        }

        $this->info("Found {$failedContracts->count()} contracts with failed FTP uploads");

        // Get all users who should receive these notifications
        $users = User::all()->filter(function ($user) {
            return NotificationPreference::isEnabled($user->id, 'ftp_upload_failed');
        });

        foreach ($failedContracts as $contract) {
            foreach ($users as $user) {
                // Check if user already has an unread notification for this contract
                $existingNotification = $user->unreadNotifications()
                    ->where('type', FtpUploadFailedNotification::class)
                    ->whereJsonContains('data->contract_id', $contract->id)
                    ->first();

                if (!$existingNotification) {
                    $user->notify(new FtpUploadFailedNotification($contract, $contract->ftp_error));
                    $this->info("  Notified {$user->name} about failed FTP for contract #{$contract->id}");
                }
            }
        }
    }
}
