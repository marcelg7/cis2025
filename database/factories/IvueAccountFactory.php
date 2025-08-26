<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class IvueAccountFactory extends Factory
{
    protected $model = \App\Models\IvueAccount::class;

    public function definition()
    {
        return [
            'ivue_account' => $this->faker->unique()->numerify('102#####'),
            'status' => 'active',
            'customer_id' => \App\Models\Customer::factory(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}