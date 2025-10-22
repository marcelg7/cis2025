<?php

namespace Database\Seeders;

use App\Models\ActivityType;
use Illuminate\Database\Seeder;

class ActivityTypeSeeder extends Seeder
{
    public function run(): void
    {
        $activityTypes = [
            ['name' => 'New Activation', 'is_active' => true],
            ['name' => 'Upgrade', 'is_active' => true],
            ['name' => 'Bring Your Own Device', 'is_active' => true],
            ['name' => 'Plan Change', 'is_active' => true],
        ];

        foreach ($activityTypes as $type) {
            ActivityType::firstOrCreate(['name' => $type['name']], $type);
        }
    }
}
