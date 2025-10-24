<?php

namespace App\Console\Commands;

use App\Models\Contract;
use App\Models\User;
use App\Models\NotificationPreference;
use App\Notifications\ContractRenewalNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckContractRenewalsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:check-contract-renewals';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for contracts approaching end date (renewal opportunities)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for contract renewal opportunities...');

        // Check for contracts expiring in 30, 60, or 90 days
        $thresholds = [30, 60, 90];

        foreach ($thresholds as $days) {
            $this->checkContractsExpiringIn($days);
        }

        $this->info('Completed checking contract renewals.');
    }

    /**
     * Check for contracts expiring in a specific number of days
     */
    protected function checkContractsExpiringIn(int $days)
    {
        $targetDate = Carbon::now()->addDays($days)->startOfDay();
        $endDate = $targetDate->copy()->endOfDay();

        $expiringContracts = Contract::where('status', 'finalized')
            ->whereBetween('end_date', [$targetDate, $endDate])
            ->get();

        if ($expiringContracts->isEmpty()) {
            $this->info("No contracts expiring in {$days} days");
            return;
        }

        $this->info("Found {$expiringContracts->count()} contracts expiring in {$days} days");

        // Get all users who should receive these notifications
        $users = User::all()->filter(function ($user) {
            return NotificationPreference::isEnabled($user->id, 'contract_renewal');
        });

        foreach ($expiringContracts as $contract) {
            foreach ($users as $user) {
                // Check if user already has an unread notification for this contract and time period
                $existingNotification = $user->unreadNotifications()
                    ->where('type', ContractRenewalNotification::class)
                    ->whereJsonContains('data->contract_id', $contract->id)
                    ->whereJsonContains('data->days_until_expiry', $days)
                    ->first();

                if (!$existingNotification) {
                    $user->notify(new ContractRenewalNotification($contract, $days));
                    $this->info("  Notified {$user->name} about contract #{$contract->id} expiring in {$days} days");
                }
            }
        }
    }
}
