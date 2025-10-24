<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Location;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $locations = [
            [
                'name' => 'Head Office',
                'code' => 'HO',
                'address' => null,
                'active' => true,
            ],
            [
                'name' => 'Hay River',
                'code' => 'HAY',
                'address' => null,
                'active' => true,
            ],
            [
                'name' => 'Fort Smith',
                'code' => 'FS',
                'address' => null,
                'active' => true,
            ],
            [
                'name' => 'Enterprise',
                'code' => 'ENT',
                'address' => null,
                'active' => true,
            ],
        ];

        foreach ($locations as $location) {
            Location::firstOrCreate(
                ['code' => $location['code']],
                $location
            );
        }
    }
}
