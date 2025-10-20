<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;
use App\Models\IvueAccount;
use App\Models\MobilityAccount;
use App\Models\Subscriber;
use App\Models\Contract;

use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Create 5 customers with is_test = 1, each with related records
        Customer::factory()
            ->count(5)
            ->create(['is_test' => 1])
            ->each(function ($customer) {
                // Create 1-2 IVUE accounts per customer
                $ivueAccounts = IvueAccount::factory()
                    ->count(rand(1, 2))
                    ->create(['customer_id' => $customer->id]);
                // For each IVUE account, create a Mobility account
                $ivueAccounts->each(function ($ivueAccount) {
                    $mobilityAccount = MobilityAccount::factory()
                        ->create(['ivue_account_id' => $ivueAccount->id]);
                    // Create 1-2 Subscribers per Mobility account with is_test = 1
                    $subscribers = Subscriber::factory()
                        ->count(rand(1, 2))
                        ->create(['mobility_account_id' => $mobilityAccount->id, 'is_test' => 1]);
                    // Create 1-2 Contracts per Subscriber with is_test = 1
                    $subscribers->each(function ($subscriber) {
                        Contract::factory()
                            ->count(rand(1, 2))
                            ->create([
                                'subscriber_id' => $subscriber->id,
                                'is_test' => 1,
                            ]);
                    });
                });
            });

        // Insert default activity types only if they don't exist
        $activityTypes = [
            ['id' => 1, 'name' => 'New Postpaid Activation', 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()],
            // ... (other activity types)
        ];
        DB::table('activity_types')->insertOrIgnore($activityTypes);

        // Insert default commitment periods only if they don't exist
        $commitmentPeriods = [
            ['id' => 1, 'name' => '2 Year Term Smart Pay', 'cancellation_policy' => 'Remaining Device Balance: {balance}...', 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'No Term', 'cancellation_policy' => 'There is no cancellation fee...', 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()],
        ];
        DB::table('commitment_periods')->insertOrIgnore($commitmentPeriods);

		$this->call(PermissionSeeder::class);
    }
}