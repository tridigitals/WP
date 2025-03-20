<?php

namespace Tests\Feature\Auth;

use Tests\Feature\BaseTestCase;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthenticationTest extends BaseTestCase
{
    /**
     * Test login page can be displayed.
     */
    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    /**
     * Test users can authenticate.
     */
    public function test_users_can_authenticate(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/admin');
    }

    /**
     * Test users cannot authenticate with invalid password.
     */
    public function test_users_cannot_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    /**
     * Test login throttling.
     */
    public function test_login_attempts_are_throttled(): void
    {
        $user = User::factory()->create();

        foreach (range(0, 5) as $_) {
            $response = $this->post('/login', [
                'email' => $user->email,
                'password' => 'wrong-password',
            ]);
        }

        $response->assertStatus(429);
    }

    /**
     * Test remember me functionality.
     */
    public function test_users_can_be_remembered(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'remember' => 'on',
        ]);

        $response->assertRedirect('/admin');
        $this->assertNotNull(Auth::user()->getRememberToken());
    }

    /**
     * Test users can logout.
     */
    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);
        $this->assertAuthenticated();

        $response = $this->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/login');
    }

    /**
     * Test authentication is required for admin routes.
     */
    public function test_auth_is_required_for_admin_routes(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect('/login');
    }

    /**
     * Test admin access is required for admin routes.
     */
    public function test_admin_access_is_required_for_admin_routes(): void
    {
        $user = User::factory()->create([
            'is_admin' => false
        ]);

        $response = $this->actingAs($user)->get('/admin');

        $response->assertStatus(403);
    }

    /**
     * Test last login information is updated.
     */
    public function test_last_login_info_is_updated(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $user->refresh();
        
        $this->assertArrayHasKey('last_login_at', $user->meta_data);
        $this->assertArrayHasKey('last_login_ip', $user->meta_data);
    }

    /**
     * Test invalid users cannot authenticate.
     */
    public function test_invalid_users_cannot_authenticate(): void
    {
        $response = $this->post('/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    /**
     * Test validation rules are enforced.
     */
    public function test_login_validation_rules(): void
    {
        $response = $this->post('/login', []);

        $response->assertSessionHasErrors(['email', 'password']);
    }
}