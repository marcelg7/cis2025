<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class DeviceFactory extends Factory
{
    protected $model = \App\Models\Device::class;

    public function definition()
    {
        return [
            'name' => $this->faker->randomElement(['iPhone 15', 'Galaxy S23', 'Pixel 7']),
            'manufacturer' => $this->faker->randomElement(['Apple', 'Samsung', 'Google']),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}