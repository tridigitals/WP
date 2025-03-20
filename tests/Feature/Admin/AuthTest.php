<?php

namespace Tests\Feature\Admin;

use Tests\Feature\BaseTestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthTest extends BaseTestCase
{
    /**
     * Test admin can access dashboard.
     */
    public function test_admin_can_access_dashboard(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.dashboard'));

        $response->assertStatus(200);
    }

    /**
     * Test non-admin cannot access dashboard.
     */
    public function test_non_admin_cannot_access_dashboard(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('admin.dashboard'));

        $response->assertStatus(403);
    }

    /**
     * Test guest is redirected to login.
     */
    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect(route('login'));
    }

    /**
     * Test admin can access all admin routes.
     */
    public function test_admin_can_access_all_admin_routes(): void
    {
        $routes = [
            'admin.posts.index',
            'admin.categories.index',
            'admin.tags.index',
            'admin.media.index',
            'admin.settings.index'
        ];

        foreach ($routes as $route) {
            $response = $this->actingAs($this->admin)->get(route($route));
            $response->assertStatus(200);
        }
    }

    /**
     * Test non-admin cannot access any admin routes.
     */
    public function test_non_admin_cannot_access_any_admin_routes(): void
    {
        $routes = [
            'admin.posts.index',
            'admin.categories.index',
            'admin.tags.index',
            'admin.media.index',
            'admin.settings.index'
        ];

        foreach ($routes as $route) {
            $response = $this->actingAs($this->user)->get(route($route));
            $response->assertStatus(403);
        }
    }

    /**
     * Test admin session security.
     */
    public function test_admin_session_security(): void
    {
        // Login as admin
        $response = $this->post(route('login'), [
            'email' => $this->admin->email,
            'password' => 'password'
        ]);

        $response->assertRedirect(route('admin.dashboard'));

        // Check session security headers
        $response = $this->actingAs($this->admin)
            ->get(route('admin.dashboard'));

        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    /**
     * Test admin password validation.
     */
    public function test_admin_password_validation(): void
    {
        $response = $this->post(route('login'), [
            'email' => $this->admin->email,
            'password' => 'wrong-password'
        ]);

        $response->assertSessionHasErrors('email');
    }

    /**
     * Test admin lockout after failed attempts.
     */
    public function test_admin_lockout_after_failed_attempts(): void
    {
        foreach (range(1, 5) as $_) {
            $this->post(route('login'), [
                'email' => $this->admin->email,
                'password' => 'wrong-password'
            ]);
        }

        $response = $this->post(route('login'), [
            'email' => $this->admin->email,
            'password' => 'wrong-password'
        ]);

        $response->assertStatus(429); // Too Many Requests
    }

    /**
     * Test admin can logout.
     */
    public function test_admin_can_logout(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('logout'));

        $response->assertRedirect('/');
        $this->assertGuest();
    }

    /**
     * Test remember me functionality.
     */
    public function test_remember_me_functionality(): void
    {
        $response = $this->post(route('login'), [
            'email' => $this->admin->email,
            'password' => 'password',
            'remember' => 'on'
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertNotNull($this->admin->fresh()->remember_token);
    }

    /**
     * Test concurrent session handling.
     */
    public function test_concurrent_session_handling(): void
    {
        // First login
        $this->post(route('login'), [
            'email' => $this->admin->email,
            'password' => 'password'
        ]);

        // Second login from different device
        $response = $this->post(route('login'), [
            'email' => $this->admin->email,
            'password' => 'password'
        ]);

        $response->assertStatus(200);
    }

    /**
     * Test CSRF protection.
     */
    public function test_csrf_protection(): void
    {
        $response = $this->withoutMiddleware()->post(route('admin.posts.store'), [
            'title' => 'Test Post'
        ]);

        $response->assertStatus(419); // CSRF token mismatch
    }

    /**
     * Test admin cannot access with expired session.
     */
    public function test_admin_cannot_access_with_expired_session(): void
    {
        $this->actingAs($this->admin);
        
        // Simulate session expiration
        $this->travel(config('session.lifetime') + 1)->minutes();

        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect(route('login'));
    }

    /**
     * Test admin IP tracking.
     */
    public function test_admin_ip_tracking(): void
    {
        $response = $this->post(route('login'), [
            'email' => $this->admin->email,
            'password' => 'password'
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->admin->id,
            'last_login_ip' => request()->ip()
        ]);
    }

    /**
     * Test password reset functionality.
     */
    public function test_password_reset_functionality(): void
    {
        // Request password reset
        $response = $this->post(route('password.email'), [
            'email' => $this->admin->email
        ]);

        $response->assertStatus(200);

        // Get the reset token
        $token = null;
        \Notification::fake();
        $this->post(route('password.email'), ['email' => $this->admin->email]);
        
        // Reset password
        $response = $this->post(route('password.update'), [
            'email' => $this->admin->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
            'token' => $token
        ]);

        $response->assertSessionHasErrors(); // Token is fake
    }

    /**
     * Test two-factor authentication.
     */
    public function test_two_factor_authentication(): void
    {
        $this->admin->forceFill([
            'two_factor_secret' => encrypt('test-secret'),
            'two_factor_recovery_codes' => encrypt(json_encode([
                'recovery-code-1',
                'recovery-code-2'
            ]))
        ])->save();

        $response = $this->actingAs($this->admin)->post(route('two-factor.enable'));

        $response->assertStatus(200);
        $this->assertNotNull($this->admin->fresh()->two_factor_secret);
    }
}