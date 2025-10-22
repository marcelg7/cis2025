<?php

namespace Database\Seeders;

use App\Models\CommitmentPeriod;
use Illuminate\Database\Seeder;

class CommitmentPeriodSeeder extends Seeder
{
    public function run(): void
    {
        $periods = [
            [
                'name' => '24-Month Commitment',
                'cancellation_policy' => 'Early cancellation will result in a fee equal to the remaining device balance.',
                'is_active' => true,
            ],
            [
                'name' => 'Month-to-Month',
                'cancellation_policy' => 'No commitment period. Cancel anytime.',
                'is_active' => true,
            ],
        ];

        foreach ($periods as $period) {
            CommitmentPeriod::firstOrCreate(['name' => $period['name']], $period);
        }
    }
}
