<?php

namespace Tests\Feature\RBAC;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_middleware_allows_access_to_admin_users()
    {
        $this->seed();

        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this->actingAs($user)->get('/admin/users');

        $response->assertStatus(200);
    }

    public function test_admin_middleware_allows_access_to_super_admin_users()
    {
        $this->seed();

        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $response = $this->actingAs($user)->get('/admin/users');

        $response->assertStatus(200);
    }

    public function test_admin_middleware_redirects_non_admin_users()
    {
        $this->seed();

        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/admin/users');

        $response->assertRedirect('/dashboard');
    }

    public function test_admin_middleware_redirects_guests()
    {
        $response = $this->get('/admin/users');

        $response->assertRedirect('/login');
    }
}