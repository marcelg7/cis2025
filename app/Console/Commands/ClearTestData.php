<?php
// app/Console/Commands/ClearTestData.php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class ClearTestData extends Command
{
    protected $signature = 'db:clear-test-data {--reset : Reset and reseed default data after clearing} {--dry-run : Simulate the operation without making changes}';
    protected $description = 'Clear test data from the database and optionally reset defaults';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $reset = $this->option('reset');

        $testContracts = DB::table('contracts')->where('is_test', 1)->count();
        $testSubscribers = DB::table('subscribers')->where('is_test', 1)->count();
        $testCustomers = DB::table('customers')->where('is_test', 1)->count();
        $testPlans = DB::table('plans')->where('is_test', 1)->where('id', '!=', 1)->count(); // Exclude default plan ID 1

        if ($dryRun) {
            $this->info("Dry run: Would delete $testContracts test contracts, $testSubscribers test subscribers, $testCustomers test customers, and $testPlans test plans.");
            if ($reset) {
                $this->info("Would also reset and reseed default data.");
            }
            return 0;
        }

        DB::table('contracts')->where('is_test', 1)->delete();
        DB::table('subscribers')->where('is_test', 1)->delete();
        DB::table('customers')->where('is_test', 1)->delete();
        DB::table('plans')->where('is_test', 1)->where('id', '!=', 1)->delete(); // Exclude default plan ID 1

        $this->info("Deleted $testContracts test contracts, $testSubscribers test subscribers, $testCustomers test customers, and $testPlans test plans.");

        if ($reset) {
            $this->call('migrate:refresh', ['--seed' => true]);
            $this->info("Database reset and default data reseeded.");
        }
    }
}