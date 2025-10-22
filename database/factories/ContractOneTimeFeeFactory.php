<?php

namespace Database\Factories;

use App\Models\Contract;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContractOneTimeFeeFactory extends Factory
{
    protected $model = \App\Models\ContractOneTimeFee::class;

    public function definition(): array
    {
        $fees = [
            'Connection Fee',
            'Activation Fee',
            'SIM Card Fee',
            'Setup Fee',
            'Transfer Fee',
        ];

        return [
            'contract_id' => Contract::factory(),
            'name' => $this->faker->randomElement($fees),
            'cost' => $this->faker->randomFloat(2, 10, 100),
        ];
    }
}
