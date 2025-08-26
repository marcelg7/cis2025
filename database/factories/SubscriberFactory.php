<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SubscriberFactory extends Factory
{
    protected $model = \App\Models\Subscriber::class;

    public function definition()
    {
        return [
            'mobile_number' => $this->faker->unique()->numerify('###-###-####'),
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'status' => 'active',
            'mobility_account_id' => \App\Models\MobilityAccount::factory(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}