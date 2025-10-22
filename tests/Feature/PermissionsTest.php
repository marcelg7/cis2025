<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PermissionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed permissions
        $this->artisan('db:seed', ['--class' => 'PermissionSeeder']);
    }

    /** @test */
    public function it_creates_default_permissions()
    {
        $this->assertDatabaseHas('permissions', ['name' => 'upload-device-pricing']);
        $this->assertDatabaseHas('permissions', ['name' => 'upload-plan-pricing']);
        $this->assertDatabaseHas('permissions', ['name' => 'manage-users']);
        $this->assertDatabaseHas('permissions', ['name' => 'view_all_logs']);
        $this->assertDatabaseHas('permissions', ['name' => 'manage-terms-of-service']);
    }

    /** @test */
    public function it_creates_default_roles()
    {
        $this->assertDatabaseHas('roles', ['name' => 'admin']);
        $this->assertDatabaseHas('roles', ['name' => 'user']);
    }

    /** @test */
    public function admin_role_has_all_permissions()
    {
        $adminRole = Role::findByName('admin');

        $this->assertTrue($adminRole->hasPermissionTo('upload-device-pricing'));
        $this->assertTrue($adminRole->hasPermissionTo('upload-plan-pricing'));
        $this->assertTrue($adminRole->hasPermissionTo('manage-users'));
        $this->assertTrue($adminRole->hasPermissionTo('view_all_logs'));
        $this->assertTrue($adminRole->hasPermissionTo('manage-terms-of-service'));
    }

    /** @test */
    public function user_can_be_assigned_admin_role()
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->assertTrue($user->hasRole('admin'));
    }

    /** @test */
    public function user_can_be_assigned_user_role()
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $this->assertTrue($user->hasRole('user'));
    }

    /** @test */
    public function admin_user_has_all_permissions()
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->assertTrue($user->can('upload-device-pricing'));
        $this->assertTrue($user->can('upload-plan-pricing'));
        $this->assertTrue($user->can('manage-users'));
        $this->assertTrue($user->can('view_all_logs'));
        $this->assertTrue($user->can('manage-terms-of-service'));
    }

    /** @test */
    public function regular_user_does_not_have_admin_permissions()
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $this->assertFalse($user->can('upload-device-pricing'));
        $this->assertFalse($user->can('upload-plan-pricing'));
        $this->assertFalse($user->can('manage-users'));
    }

    /** @test */
    public function user_can_be_given_specific_permission()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('upload-device-pricing');

        $this->assertTrue($user->can('upload-device-pricing'));
        $this->assertFalse($user->can('upload-plan-pricing'));
    }

    /** @test */
    public function user_can_have_permission_revoked()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('upload-device-pricing');

        $this->assertTrue($user->can('upload-device-pricing'));

        $user->revokePermissionTo('upload-device-pricing');

        $this->assertFalse($user->can('upload-device-pricing'));
    }

    /** @test */
    public function user_can_have_role_removed()
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->assertTrue($user->hasRole('admin'));

        $user->removeRole('admin');

        $this->assertFalse($user->hasRole('admin'));
    }

    /** @test */
    public function user_can_have_multiple_roles()
    {
        $user = User::factory()->create();
        $user->assignRole(['admin', 'user']);

        $this->assertTrue($user->hasRole('admin'));
        $this->assertTrue($user->hasRole('user'));
    }

    /** @test */
    public function user_can_check_any_role()
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->assertTrue($user->hasAnyRole(['admin', 'user']));
    }

    /** @test */
    public function user_can_check_all_roles()
    {
        $user = User::factory()->create();
        $user->assignRole(['admin', 'user']);

        $this->assertTrue($user->hasAllRoles(['admin', 'user']));
    }

    /** @test */
    public function permissions_are_cached()
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        // First call loads permissions
        $this->assertTrue($user->can('manage-users'));

        // Clear cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Should still work after cache clear
        $this->assertTrue($user->can('manage-users'));
    }

    /** @test */
    public function permission_names_are_unique()
    {
        $permission1 = Permission::create(['name' => 'test-permission']);

        $this->expectException(\Exception::class);
        $permission2 = Permission::create(['name' => 'test-permission']);
    }

    /** @test */
    public function role_names_are_unique()
    {
        $role1 = Role::create(['name' => 'test-role']);

        $this->expectException(\Exception::class);
        $role2 = Role::create(['name' => 'test-role']);
    }
}
