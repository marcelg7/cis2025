<?php


namespace Database\Factories;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

class PlanFactory extends Factory
{
    protected $model = Plan::class;

    public function definition()
    {
        return [
            'service_level' => 'consumer',
            'plan_type' => 'smartpay',
            'name' => $this->faker->unique()->word,
            'price' => $this->faker->randomFloat(2, 10, 100),
            'details' => $this->faker->paragraph,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
            'is_test' => 1, // Add this
        ];
    }
}