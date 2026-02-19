<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RateLimitTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Setting::firstOrCreate(
            ['group' => 'security', 'key' => 'recaptcha_enabled'],
            ['value' => '0', 'type' => 'boolean', 'is_encrypted' => false]
        );
        Setting::firstOrCreate(
            ['group' => 'security', 'key' => 'max_login_attempts'],
            ['value' => '5', 'type' => 'string', 'is_encrypted' => false]
        );
        Setting::firstOrCreate(
            ['group' => 'security', 'key' => 'lockout_minutes'],
            ['value' => '30', 'type' => 'string', 'is_encrypted' => false]
        );
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    private function createAdminUser(array $attrs = []): User
    {
        $role = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $user = User::factory()->create(array_merge([
            'status' => 'active',
            'password' => Hash::make('password'),
        ], $attrs));
        $user->assignRole($role);
        return $user;
    }

    // ── Web Login Rate Limiting ─────────────────────────────────

    public function test_login_is_rate_limited_after_5_attempts(): void
    {
        $user = $this->createAdminUser();

        // Make 5 failed attempts
        for ($i = 0; $i < 5; $i++) {
            $this->post('/admin/login', [
                'email' => $user->email,
                'password' => 'wrongpassword',
            ]);
        }

        // 6th attempt should be rate limited
        $response = $this->post('/admin/login', [
            'email' => $user->email,
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(429);
    }

    // ── Account Lockout ─────────────────────────────────────────

    public function test_account_locked_after_max_failed_attempts(): void
    {
        $user = $this->createAdminUser();

        // Make max failed attempts (5)
        for ($i = 0; $i < 5; $i++) {
            $this->post('/admin/login', [
                'email' => $user->email,
                'password' => 'wrongpassword',
            ]);
        }

        $user->refresh();
        $this->assertGreaterThanOrEqual(5, $user->failed_login_count);
    }

    public function test_successful_login_resets_failed_count(): void
    {
        $user = $this->createAdminUser();

        // Make a few failed attempts
        for ($i = 0; $i < 3; $i++) {
            $this->post('/admin/login', [
                'email' => $user->email,
                'password' => 'wrongpassword',
            ]);
        }

        $user->refresh();
        $this->assertGreaterThan(0, $user->failed_login_count);

        // Successful login
        $this->post('/admin/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $user->refresh();
        $this->assertEquals(0, $user->failed_login_count);
    }

    public function test_locked_user_cannot_login_even_with_correct_password(): void
    {
        $user = $this->createAdminUser([
            'locked_until' => now()->addMinutes(30),
        ]);

        $response = $this->post('/admin/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors();
        $this->assertGuest();
    }

    public function test_lock_expires_and_login_is_allowed(): void
    {
        $user = $this->createAdminUser([
            'locked_until' => now()->subMinute(),
            'failed_login_count' => 5,
        ]);

        $response = $this->post('/admin/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/admin');
        $this->assertAuthenticatedAs($user);
    }

    // ── API Login Rate Limiting ─────────────────────────────────

    public function test_api_login_tracks_failed_attempts(): void
    {
        $user = $this->createAdminUser();

        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'wrongpassword',
        ]);

        $user->refresh();
        $this->assertGreaterThan(0, $user->failed_login_count);
    }

    public function test_api_login_resets_count_on_success(): void
    {
        $user = $this->createAdminUser(['failed_login_count' => 3]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertOk();

        $user->refresh();
        $this->assertEquals(0, $user->failed_login_count);
    }
}
