<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Models\Setting;
use App\Services\TwoFactorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TwoFactorTest extends TestCase
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

    // ── 2FA Setup Flow ──────────────────────────────────────────

    public function test_2fa_setup_page_renders(): void
    {
        $user = $this->createAdminUser();

        $response = $this->actingAs($user)->get('/admin/2fa/setup');

        $response->assertStatus(200);
        $response->assertSessionHas('2fa_setup_secret');
    }

    public function test_2fa_enable_with_valid_code(): void
    {
        $user = $this->createAdminUser();
        $service = app(TwoFactorService::class);
        $secret = $service->generateSecret();

        // Generate a valid TOTP code
        $google2fa = new \PragmaRX\Google2FA\Google2FA();
        $validCode = $google2fa->getCurrentOtp($secret);

        $response = $this->actingAs($user)
            ->withSession(['2fa_setup_secret' => $secret])
            ->post('/admin/2fa/enable', ['code' => $validCode]);

        $response->assertRedirect(route('admin.profile'));
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertNotNull($user->two_factor_confirmed_at);
        $this->assertTrue($user->hasTwoFactorEnabled());
    }

    public function test_2fa_enable_with_invalid_code(): void
    {
        $user = $this->createAdminUser();
        $service = app(TwoFactorService::class);
        $secret = $service->generateSecret();

        $response = $this->actingAs($user)
            ->withSession(['2fa_setup_secret' => $secret])
            ->post('/admin/2fa/enable', ['code' => '000000']);

        $response->assertSessionHasErrors('code');

        $user->refresh();
        $this->assertNull($user->two_factor_confirmed_at);
    }

    public function test_2fa_enable_without_session_secret(): void
    {
        $user = $this->createAdminUser();

        $response = $this->actingAs($user)
            ->post('/admin/2fa/enable', ['code' => '123456']);

        $response->assertRedirect(route('admin.profile'));
        $response->assertSessionHas('error');
    }

    // ── 2FA Challenge/Verify Flow ───────────────────────────────

    public function test_2fa_challenge_page_requires_session(): void
    {
        $response = $this->get('/admin/2fa/challenge');
        $response->assertRedirect(route('admin.login'));
    }

    public function test_2fa_challenge_page_renders_with_session(): void
    {
        $response = $this->withSession(['2fa_user_id' => 1])
            ->get('/admin/2fa/challenge');

        $response->assertStatus(200);
    }

    public function test_2fa_verify_with_valid_code(): void
    {
        $user = $this->createAdminUser();
        $service = app(TwoFactorService::class);
        $secret = $service->generateSecret();

        // Enable 2FA for user
        $google2fa = new \PragmaRX\Google2FA\Google2FA();
        $validCode = $google2fa->getCurrentOtp($secret);
        $service->enable($user, $secret, $validCode);

        // Generate a new valid code for verification
        $newCode = $google2fa->getCurrentOtp($secret);

        $response = $this->withSession(['2fa_user_id' => $user->id])
            ->post('/admin/2fa/verify', ['code' => $newCode]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_2fa_verify_with_invalid_code(): void
    {
        $user = $this->createAdminUser();
        $service = app(TwoFactorService::class);
        $secret = $service->generateSecret();

        $google2fa = new \PragmaRX\Google2FA\Google2FA();
        $validCode = $google2fa->getCurrentOtp($secret);
        $service->enable($user, $secret, $validCode);

        $response = $this->withSession(['2fa_user_id' => $user->id])
            ->post('/admin/2fa/verify', ['code' => '000000']);

        $response->assertSessionHasErrors('code');
        $this->assertGuest();
    }

    public function test_2fa_verify_with_recovery_code(): void
    {
        $user = $this->createAdminUser();
        $service = app(TwoFactorService::class);
        $secret = $service->generateSecret();

        $google2fa = new \PragmaRX\Google2FA\Google2FA();
        $validCode = $google2fa->getCurrentOtp($secret);
        $result = $service->enable($user, $secret, $validCode);
        $recoveryCodes = $result['recovery_codes'];

        // Use a recovery code to verify
        $response = $this->withSession(['2fa_user_id' => $user->id])
            ->post('/admin/2fa/verify', ['code' => $recoveryCodes[0]]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($user);

        // Verify recovery code was consumed
        $user->refresh();
        $this->assertNotContains($recoveryCodes[0], $user->two_factor_recovery_codes);
    }

    // ── 2FA Disable ─────────────────────────────────────────────

    public function test_2fa_disable_with_correct_password(): void
    {
        $user = $this->createAdminUser();
        $service = app(TwoFactorService::class);
        $secret = $service->generateSecret();

        $google2fa = new \PragmaRX\Google2FA\Google2FA();
        $validCode = $google2fa->getCurrentOtp($secret);
        $service->enable($user, $secret, $validCode);

        $response = $this->actingAs($user)
            ->withSession(['2fa_verified' => true])
            ->post('/admin/2fa/disable', ['password' => 'password']);

        $response->assertRedirect(route('admin.profile'));

        $user->refresh();
        $this->assertNull($user->two_factor_confirmed_at);
        $this->assertFalse($user->hasTwoFactorEnabled());
    }

    public function test_2fa_disable_with_wrong_password(): void
    {
        $user = $this->createAdminUser();
        $service = app(TwoFactorService::class);
        $secret = $service->generateSecret();

        $google2fa = new \PragmaRX\Google2FA\Google2FA();
        $validCode = $google2fa->getCurrentOtp($secret);
        $service->enable($user, $secret, $validCode);

        $response = $this->actingAs($user)
            ->withSession(['2fa_verified' => true])
            ->post('/admin/2fa/disable', ['password' => 'wrongpassword']);

        $response->assertSessionHasErrors('password');

        $user->refresh();
        $this->assertTrue($user->hasTwoFactorEnabled());
    }

    // ── Recovery Codes ──────────────────────────────────────────

    public function test_regenerate_recovery_codes(): void
    {
        $user = $this->createAdminUser();
        $service = app(TwoFactorService::class);
        $secret = $service->generateSecret();

        $google2fa = new \PragmaRX\Google2FA\Google2FA();
        $validCode = $google2fa->getCurrentOtp($secret);
        $result = $service->enable($user, $secret, $validCode);
        $oldCodes = $result['recovery_codes'];

        $response = $this->actingAs($user)
            ->withSession(['2fa_verified' => true])
            ->post('/admin/2fa/recovery-codes');

        $response->assertRedirect(route('admin.profile'));

        $user->refresh();
        $newCodes = $user->two_factor_recovery_codes;
        $this->assertNotEquals($oldCodes, $newCodes);
        $this->assertCount(8, $newCodes);
    }
}
