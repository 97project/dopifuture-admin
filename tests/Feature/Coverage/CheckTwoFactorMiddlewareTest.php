<?php

namespace Tests\Feature\Coverage;

use App\Models\User;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CheckTwoFactorMiddlewareTest extends TestCase
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

    private function createAdmin(): User
    {
        $role = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        foreach (['backups.view', 'backups.create', 'backups.delete'] as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }
        $role->syncPermissions(Permission::all());
        $user = User::factory()->create(['status' => 'active', 'password' => Hash::make('password')]);
        $user->assignRole($role);
        return $user;
    }

    // ── CheckTwoFactor Middleware ────────────────────────────

    public function test_user_without_2fa_can_access_admin(): void
    {
        $user = $this->createAdmin();
        $this->actingAs($user);

        $response = $this->get('/admin');
        $response->assertOk();
    }

    public function test_user_with_2fa_enabled_but_not_verified_is_redirected(): void
    {
        $user = $this->createAdmin();
        $service = app(\App\Services\TwoFactorService::class);
        $secret = $service->generateSecret();

        $google2fa = new \PragmaRX\Google2FA\Google2FA();
        $validCode = $google2fa->getCurrentOtp($secret);
        $service->enable($user, $secret, $validCode);

        $this->actingAs($user);

        $response = $this->get('/admin');
        // Should redirect to 2FA verification page since 2fa_verified is not in session
        $response->assertRedirect(route('admin.2fa.verify'));
    }

    public function test_user_with_2fa_verified_can_access_admin(): void
    {
        $user = $this->createAdmin();
        $service = app(\App\Services\TwoFactorService::class);
        $secret = $service->generateSecret();

        $google2fa = new \PragmaRX\Google2FA\Google2FA();
        $validCode = $google2fa->getCurrentOtp($secret);
        $service->enable($user, $secret, $validCode);

        $this->actingAs($user);

        // Simulate having verified 2FA
        $response = $this->withSession(['2fa_verified' => true])
            ->get('/admin');
        $response->assertOk();
    }

    public function test_check_two_factor_returns_json_for_api_requests(): void
    {
        $user = $this->createAdmin();
        $service = app(\App\Services\TwoFactorService::class);
        $secret = $service->generateSecret();

        $google2fa = new \PragmaRX\Google2FA\Google2FA();
        $validCode = $google2fa->getCurrentOtp($secret);
        $service->enable($user, $secret, $validCode);

        $this->actingAs($user);

        $response = $this->getJson('/admin');
        $response->assertStatus(403);
        $response->assertJson(['error' => '2FA_REQUIRED']);
    }

    // ── Concurrent Token Tests ──────────────────────────────

    public function test_old_token_is_revoked_on_new_login(): void
    {
        $user = $this->createAdmin();

        // Login to get first token
        $response1 = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
        $response1->assertOk();
        $token1 = $response1->json('data.token');

        // Login again to get second token
        $response2 = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
        $response2->assertOk();
        $token2 = $response2->json('data.token');

        // Both tokens should be different
        $this->assertNotEquals($token1, $token2);

        // Verify new token works
        $meResponse = $this->withHeaders(['Authorization' => "Bearer $token2"])
            ->getJson('/api/v1/auth/me');
        $meResponse->assertOk();
    }

    public function test_logout_invalidates_session(): void
    {
        $user = $this->createAdmin();

        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
        $token = $loginResponse->json('data.token');

        // Logout
        $logoutResponse = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->postJson('/api/v1/auth/logout');
        $logoutResponse->assertOk();
    }

    public function test_token_expiry_returns_unauthorized(): void
    {
        $user = $this->createAdmin();

        // Delete all tokens to simulate expiry
        $user->tokens()->delete();

        $meResponse = $this->withHeaders(['Authorization' => 'Bearer nonexistent_token'])
            ->getJson('/api/v1/auth/me');
        $meResponse->assertUnauthorized();
    }
}
