<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;
use App\Models\IvueAccount;
use App\Models\MobilityAccount;
use App\Models\Subscriber;
use App\Models\Contract;
use App\Models\Plan;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Create 5 customers, each with related records
        Customer::factory()
            ->count(5)
            ->create()
            ->each(function ($customer) {
                // Create 1-2 IVUE accounts per customer
                $ivueAccounts = IvueAccount::factory()
                    ->count(rand(1, 2))
                    ->create(['customer_id' => $customer->id]);

                // For each IVUE account, create a Mobility account
                $ivueAccounts->each(function ($ivueAccount) {
                    $mobilityAccount = MobilityAccount::factory()
                        ->create(['ivue_account_id' => $ivueAccount->id]);

                    // Create 1-2 Subscribers per Mobility account
                    $subscribers = Subscriber::factory()
                        ->count(rand(1, 2))
                        ->create(['mobility_account_id' => $mobilityAccount->id]);

                    // Create 1-2 Contracts per Subscriber
                    $subscribers->each(function ($subscriber) {
                        Contract::factory()
                            ->count(rand(1, 2))
                            ->create([
                                'subscriber_id' => $subscriber->id,
                                'plan_id' => Plan::factory()->create()->id,
                            ]);
                    });
                });
            });
    }
}