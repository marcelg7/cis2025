<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class RatePlanFactory extends Factory
{
    protected $model = \App\Models\RatePlan::class;

    public function definition(): array
    {
        $tiers = ['Ultra', 'Max', 'Select', 'Lite'];
        $planTypes = ['byod', 'smartpay'];
        $basePrice = $this->faker->randomFloat(2, 40, 120);

        return [
            'soc_code' => $this->faker->unique()->regexify('[A-Z]{3}[0-9]{3}'),
            'plan_name' => $this->faker->randomElement(['Basic Plan', 'Standard Plan', 'Premium Plan', 'Ultimate Plan']),
            'plan_type' => $this->faker->randomElement($planTypes),
            'tier' => $this->faker->randomElement($tiers),
            'base_price' => $basePrice,
            'promo_price' => $this->faker->optional(0.3)->randomFloat(2, 30, $basePrice),
            'promo_description' => $this->faker->optional()->sentence(),
            'credit_eligible' => $this->faker->boolean(50),
            'credit_amount' => $this->faker->randomFloat(2, 0, 25),
            'credit_type' => $this->faker->randomElement(['monthly', 'one-time']),
            'data_amount' => $this->faker->randomElement(['10GB', '20GB', '50GB', 'Unlimited']),
            'is_international' => $this->faker->boolean(30),
            'is_us_mexico' => $this->faker->boolean(40),
            'features' => $this->faker->paragraph(),
            'effective_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'is_current' => true,
            'is_active' => true,
            'is_test' => true,
        ];
    }

    public function inactive()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => false,
            ];
        });
    }

    public function withPromo()
    {
        return $this->state(function (array $attributes) {
            return [
                'promo_price' => $attributes['base_price'] - 10,
                'promo_description' => 'Special promotional pricing',
            ];
        });
    }
}
