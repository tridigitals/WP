<?php

namespace Tests\Feature\RBAC;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_controller_allows_admin_to_view_roles()
    {
        $this->seed();

        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this->actingAs($user)->get(route('admin.roles.index'));

        $response->assertStatus(200);
    }

    public function test_role_controller_denies_access_to_users_without_permission()
    {
        $this->seed();

        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.roles.index'));

        $response->assertStatus(403);
    }

    public function test_role_controller_allows_super_admin_to_view_roles()
    {
        $this->seed();

        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $response = $this->actingAs($user)->get(route('admin.roles.index'));

        $response->assertStatus(200);
    }

    public function test_role_controller_allows_user_with_view_roles_permission()
    {
        $this->seed();
        $permission = Permission::create(['name' => 'view roles']);
        $role = Role::create(['name' => 'test-role']);
        $role->givePermissionTo($permission);

        $user = User::factory()->create();
        $user->assignRole($role);

        $response = $this->actingAs($user)->get(route('admin.roles.index'));

        $response->assertStatus(200);
    }
}