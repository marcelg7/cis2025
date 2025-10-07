<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class DeviceFactory extends Factory
{
    protected $model = \App\Models\Device::class;

	public function definition()
	{
		return [
			'manufacturer' => $this->faker->randomElement(['Apple', 'Samsung', 'Google']),
			'model' => $this->faker->randomElement(['iPhone 15', 'Galaxy S23', 'Pixel 7']),
			'srp' => $this->faker->randomFloat(2, 100, 2000),
		];
	}
}