<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Models\ApiKey;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed required settings for auth service
        \App\Models\Setting::firstOrCreate(
            ['group' => 'security', 'key' => 'recaptcha_enabled'],
            ['value' => '0', 'type' => 'boolean', 'is_encrypted' => false]
        );
        \App\Models\Setting::firstOrCreate(
            ['group' => 'security', 'key' => 'max_login_attempts'],
            ['value' => '5', 'type' => 'string', 'is_encrypted' => false]
        );
        \App\Models\Setting::firstOrCreate(
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

    // ── Web Login ──────────────────────────────────────────────

    public function test_login_page_renders(): void
    {
        $response = $this->get('/admin/login');
        $response->assertStatus(200);
        $response->assertSee('Panel');
    }

    public function test_login_with_valid_credentials(): void
    {
        $user = $this->createAdminUser();

        $response = $this->post('/admin/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/admin');
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_with_invalid_credentials(): void
    {
        $user = $this->createAdminUser();

        $response = $this->post('/admin/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors();
        $this->assertGuest();
    }

    public function test_login_increments_failed_count(): void
    {
        $user = $this->createAdminUser();
        $this->assertEquals(0, $user->failed_login_count);

        $this->post('/admin/login', [
            'email' => $user->email,
            'password' => 'wrongpassword',
        ]);

        $user->refresh();
        $this->assertGreaterThan(0, $user->failed_login_count);
    }

    public function test_locked_user_cannot_login(): void
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

    public function test_inactive_user_cannot_login(): void
    {
        $user = $this->createAdminUser(['status' => 'inactive']);

        $response = $this->post('/admin/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors();
        $this->assertGuest();
    }

    public function test_logout(): void
    {
        $user = $this->createAdminUser();
        $this->actingAs($user);

        $response = $this->post('/admin/logout');

        $response->assertRedirect();
        $this->assertGuest();
    }

    // ── API Bearer Token Auth ──────────────────────────────────

    public function test_api_login_returns_token(): void
    {
        $user = $this->createAdminUser();

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['data' => ['token', 'user']]);
    }

    public function test_api_login_with_invalid_credentials(): void
    {
        $user = $this->createAdminUser();

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401);
    }

    public function test_api_authenticated_me_endpoint(): void
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->getJson('/api/v1/auth/me', [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.email', $user->email);
    }

    public function test_api_unauthenticated_me_returns_401(): void
    {
        $response = $this->getJson('/api/v1/auth/me');
        $response->assertStatus(401);
    }

    public function test_api_logout_revokes_token(): void
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->postJson('/api/v1/auth/logout', [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertOk();

        // Verify token record was removed from DB
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    // ── API Key Auth ───────────────────────────────────────────

    public function test_api_key_authentication(): void
    {
        $user = $this->createAdminUser();
        $plain = 'test-api-key-' . bin2hex(random_bytes(16));

        ApiKey::create([
            'user_id' => $user->id,
            'name' => 'Test Key',
            'key_hash' => hash('sha256', $plain),
            'key_prefix' => substr($plain, 0, 8),
            'abilities' => ['*'],
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/v1/auth/me', [
            'X-API-KEY' => $plain,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.email', $user->email);
    }

    public function test_invalid_api_key_returns_401(): void
    {
        $response = $this->getJson('/api/v1/auth/me', [
            'X-API-KEY' => 'invalid-key',
        ]);

        $response->assertStatus(401);
    }

    public function test_revoked_api_key_returns_401(): void
    {
        $user = $this->createAdminUser();
        $plain = 'revoked-key-' . bin2hex(random_bytes(16));

        ApiKey::create([
            'user_id' => $user->id,
            'name' => 'Revoked Key',
            'key_hash' => hash('sha256', $plain),
            'key_prefix' => substr($plain, 0, 8),
            'abilities' => ['*'],
            'is_active' => false,
        ]);

        $response = $this->getJson('/api/v1/auth/me', [
            'X-API-KEY' => $plain,
        ]);

        $response->assertStatus(401);
    }

    public function test_expired_api_key_returns_401(): void
    {
        $user = $this->createAdminUser();
        $plain = 'expired-key-' . bin2hex(random_bytes(16));

        ApiKey::create([
            'user_id' => $user->id,
            'name' => 'Expired Key',
            'key_hash' => hash('sha256', $plain),
            'key_prefix' => substr($plain, 0, 8),
            'abilities' => ['*'],
            'is_active' => true,
            'expires_at' => now()->subDay(),
        ]);

        $response = $this->getJson('/api/v1/auth/me', [
            'X-API-KEY' => $plain,
        ]);

        $response->assertStatus(401);
    }
}
