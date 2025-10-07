<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class ContractFactory extends Factory
{
    protected $model = \App\Models\Contract::class;

    public function definition()
    {
        $startDate = $this->faker->dateTimeBetween('-1 year', 'now');
        $firstBillDate = Carbon::instance($startDate)->addDays($this->faker->numberBetween(0, 30));
        $endDate = Carbon::instance($startDate)->addMonths($this->faker->numberBetween(12, 36));

        return [
            'start_date' => $startDate,
            'contract_date' => $startDate,
            'first_bill_date' => $firstBillDate,
            'end_date' => $endDate,
            'location' => $this->faker->randomElement(['zurich', 'exeter', 'grand_bend']),
            'agreement_credit_amount' => $this->faker->randomFloat(2, 0, 200), // e.g., 0.00 to 200.00
            'status' => $this->faker->randomElement(['draft', 'finalized']),
            'plan_id' => \App\Models\Plan::factory(),
            'subscriber_id' => \App\Models\Subscriber::factory(),
            'manufacturer' => $this->faker->randomElement(['Apple', 'Samsung', 'Google']),
            'model' => $this->faker->randomElement(['iPhone', 'Galaxy', 'Pixel']),
            'version' => $this->faker->randomElement(['15', 'S23', '7']),
            'device_storage' => $this->faker->randomElement(['128GB', '256GB', '512GB']),
            'extra_info' => $this->faker->randomElement(['Retail', 'Refurbished', null]),
            'imei_number' => $this->faker->optional()->numerify('###############'),
            'device_price' => $this->faker->randomFloat(2, 200, 1000), // e.g., 200.00 to 1000.00
            'required_upfront_payment' => $this->faker->optional()->randomFloat(2, 0, 300),
            'optional_down_payment' => $this->faker->optional()->randomFloat(2, 0, 200),
            'deferred_payment_amount' => $this->faker->optional()->randomFloat(2, 0, 500),
            'pdf_path' => $this->faker->optional()->filePath(),
			'shortcode_id' => $this->faker->randomElement(['15', '11', '7'])
            'signature_path' => $this->faker->optional()->filePath(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}