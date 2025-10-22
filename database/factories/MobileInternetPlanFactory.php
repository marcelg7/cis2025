<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class MobileInternetPlanFactory extends Factory
{
    protected $model = \App\Models\MobileInternetPlan::class;

    public function definition(): array
    {
        $categories = ['mobile', 'tablet', 'hotspot'];

        return [
            'soc_code' => $this->faker->unique()->regexify('[A-Z]{3}[0-9]{3}'),
            'plan_name' => $this->faker->randomElement(['Mobile Internet 5GB', 'Mobile Internet 10GB', 'Mobile Internet 20GB', 'Mobile Internet Unlimited']),
            'monthly_rate' => $this->faker->randomFloat(2, 15, 60),
            'category' => $this->faker->randomElement($categories),
            'promo_group' => $this->faker->optional()->word(),
            'description' => $this->faker->paragraph(),
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
}
