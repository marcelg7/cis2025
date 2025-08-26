<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class MobilityAccountFactory extends Factory
{
    protected $model = \App\Models\MobilityAccount::class;

    public function definition()
    {
        return [
            'mobility_account' => $this->faker->unique()->numerify('###-###-####'),
            'status' => 'active',
            'ivue_account_id' => \App\Models\IvueAccount::factory(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}