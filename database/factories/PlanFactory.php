<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PlanFactory extends Factory
{
    protected $model = \App\Models\Plan::class;

    public function definition()
    {
        return [
            'name' => $this->faker->randomElement([
                'Basic SmartPay (BASPC2524)',
                'Premium Plan (PREM2524)',
                'Family Plan (FAM2524)',
                'Unlimited Data (UNLTD2524)',
            ]),
            'price' => $this->faker->randomFloat(2, 10, 100), // Price between $10.00 and $100.00
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}