<?php

namespace Database\Factories;

use App\Models\Subscriber;
use App\Models\ActivityType;
use App\Models\CommitmentPeriod;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

class ContractFactory extends Factory
{
    public function definition(): array
    {
        $tiers = ['Ultra', 'Max', 'Select', 'Lite'];
        $pricingTypes = ['smartpay', 'dro'];

        return [
            'subscriber_id' => Subscriber::factory(),
            'start_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'end_date' => $this->faker->dateTimeBetween('now', '+2 years'),
            'activity_type_id' => ActivityType::inRandomOrder()->first()->id ?? 1,
            'contract_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'location' => $this->faker->randomElement(['zurich', 'exeter', 'grand_bend']),
            'bell_device_id' =>$this->faker->randomElement(['1', '2', '3']),
            'bell_pricing_type' => $this->faker->randomElement($pricingTypes),
            'bell_tier' => $this->faker->randomElement($tiers),
            'bell_retail_price' => $this->faker->randomFloat(2, 500, 2000),
            'bell_monthly_device_cost' => $this->faker->randomFloat(2, 10, 50),
            'bell_plan_cost' => $this->faker->randomFloat(2, 50, 150),
            'bell_dro_amount' => $this->faker->randomFloat(2, 100, 500),
            'bell_plan_plus_device' => $this->faker->randomFloat(2, 60, 200),
            'agreement_credit_amount' => $this->faker->randomFloat(2, 0, 500),
            'required_upfront_payment' => $this->faker->randomFloat(2, 0, 200),
            'optional_down_payment' => $this->faker->randomFloat(2, 0, 100),
            'deferred_payment_amount' => $this->faker->randomFloat(2, 0, 300),
            'commitment_period_id' => CommitmentPeriod::inRandomOrder()->first()->id ?? 1,
            'first_bill_date' => $this->faker->dateTimeBetween('now', '+1 month'),
            'pdf_path' => null,
            'status' => $this->faker->randomElement(['draft', 'signed', 'finalized']),
            'signature_path' => null,
            'is_test' => 1,
        ];
    }
}