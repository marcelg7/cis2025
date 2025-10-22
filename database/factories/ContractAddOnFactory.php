<?php

namespace Database\Factories;

use App\Models\Contract;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContractAddOnFactory extends Factory
{
    protected $model = \App\Models\ContractAddOn::class;

    public function definition(): array
    {
        $addOns = [
            ['name' => 'Voicemail', 'code' => 'VM'],
            ['name' => 'Call Display', 'code' => 'CD'],
            ['name' => 'Call Waiting', 'code' => 'CW'],
            ['name' => 'Data Boost 5GB', 'code' => 'DB5'],
            ['name' => 'International Calling', 'code' => 'IC'],
        ];

        $selectedAddOn = $this->faker->randomElement($addOns);

        return [
            'contract_id' => Contract::factory(),
            'name' => $selectedAddOn['name'],
            'code' => $selectedAddOn['code'],
            'cost' => $this->faker->randomFloat(2, 5, 50),
        ];
    }
}
