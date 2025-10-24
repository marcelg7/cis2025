<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Create new permissions (safely, no duplicates)
        Permission::firstOrCreate(['name' => 'upload-device-pricing']);
        Permission::firstOrCreate(['name' => 'upload-plan-pricing']);
        Permission::firstOrCreate(['name' => 'manage-users']);
        Permission::firstOrCreate(['name' => 'view_all_logs']);
        Permission::firstOrCreate(['name' => 'manage-terms-of-service']);
        Permission::firstOrCreate(['name' => 'view_all_analytics']);

        // Roles
        $admin = Role::findOrCreate('admin');
        $user = Role::findOrCreate('user');

        // Assign permissions to admin
        $admin->givePermissionTo(Permission::all());
    }
}