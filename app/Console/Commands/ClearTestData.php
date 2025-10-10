<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ClearTestData extends Command
{
    protected $signature = 'db:clear-test-data {--dry-run : Simulate the operation without making changes} {--reset : Reseed after clearing}';

    protected $description = 'Clear test data from the database';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $reset = $this->option('reset');

        $this->info($dryRun ? 'Dry run: Simulating clearing test data...' : 'Clearing test data...');

        // Clear test contracts
        $testContractsCount = DB::table('contracts')->where('is_test', 1)->count();
        if (!$dryRun) {
            DB::table('contracts')->where('is_test', 1)->delete();
        }
        $this->info("Cleared {$testContractsCount} test contracts.");

        // Clear test subscribers
        $testSubscribersCount = DB::table('subscribers')->where('is_test', 1)->count();
        if (!$dryRun) {
            DB::table('subscribers')->where('is_test', 1)->delete();
        }
        $this->info("Cleared {$testSubscribersCount} test subscribers.");

        // Clear test customers
        $testCustomersCount = DB::table('customers')->where('is_test', 1)->count();
        if (!$dryRun) {
            DB::table('customers')->where('is_test', 1)->delete();
        }
        $this->info("Cleared {$testCustomersCount} test customers.");

        // Clear test Bell devices
        $testBellDevicesCount = DB::table('bell_devices')->where('is_test', 1)->count();
        if (!$dryRun) {
            DB::table('bell_devices')->where('is_test', 1)->delete();
        }
        $this->info("Cleared {$testBellDevicesCount} test Bell devices.");

        // Clear test Bell pricing
        $testBellPricingCount = DB::table('bell_pricing')->whereExists(function ($query) {
            $query->select(DB::raw(1))
                  ->from('bell_devices')
                  ->whereColumn('bell_devices.id', 'bell_pricing.bell_device_id')
                  ->where('bell_devices.is_test', 1);
        })->count();
        if (!$dryRun) {
            DB::table('bell_pricing')->whereExists(function ($query) {
                $query->select(DB::raw(1))
                      ->from('bell_devices')
                      ->whereColumn('bell_devices.id', 'bell_pricing.bell_device_id')
                      ->where('bell_devices.is_test', 1);
            })->delete();
        }
        $this->info("Cleared {$testBellPricingCount} test Bell pricing entries.");

        // Clear test Bell DRO pricing
        $testBellDroPricingCount = DB::table('bell_dro_pricing')->whereExists(function ($query) {
            $query->select(DB::raw(1))
                  ->from('bell_devices')
                  ->whereColumn('bell_devices.id', 'bell_dro_pricing.bell_device_id')
                  ->where('bell_devices.is_test', 1);
        })->count();
        if (!$dryRun) {
            DB::table('bell_dro_pricing')->whereExists(function ($query) {
                $query->select(DB::raw(1))
                      ->from('bell_devices')
                      ->whereColumn('bell_devices.id', 'bell_dro_pricing.bell_device_id')
                      ->where('bell_devices.is_test', 1);
            })->delete();
        }
        $this->info("Cleared {$testBellDroPricingCount} test Bell DRO pricing entries.");

        if ($reset && !$dryRun) {
            $this->call('db:seed');
            $this->info('Database reseeded successfully.');
        }

        $this->info('Operation completed.');
    }
}