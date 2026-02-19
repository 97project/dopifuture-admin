<?php

namespace Tests\Feature\AccountDeletion;

use App\Models\User;
use App\Services\AccountDeletionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AccountDeletionTest extends TestCase
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
    }

    // ── Service Unit Tests ───────────────────────────────────

    public function test_account_deletion_service_anonymizes_user(): void
    {
        $user = User::factory()->create([
            'name' => 'Ali',
            'surname' => 'Veli',
            'email' => 'ali@example.com',
            'phone' => '+905551234567',
            'status' => 'active',
        ]);

        $service = new AccountDeletionService();
        $service->executeDelete($user);

        $user->refresh();

        $this->assertEquals('Deleted', $user->name);
        $this->assertEquals('User', $user->surname);
        $this->assertStringContainsString('deleted_', $user->email);
        $this->assertNull($user->phone);
        $this->assertEquals('inactive', $user->status);
        $this->assertNotNull($user->deleted_at);
    }

    public function test_account_deletion_revokes_tokens(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->createToken('test-token');
        $user->createToken('another-token');

        $this->assertCount(2, $user->tokens);

        $service = new AccountDeletionService();
        $service->executeDelete($user);

        $this->assertCount(0, $user->fresh()->tokens);
    }

    public function test_confirm_and_delete_with_wrong_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('correct_password'),
            'status' => 'active',
        ]);

        $service = new AccountDeletionService();
        $result = $service->confirmAndDelete($user, 'wrong_password');

        $this->assertFalse($result);
        $this->assertNull($user->fresh()->deleted_at);
    }

    public function test_confirm_and_delete_with_correct_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('correct_password'),
            'status' => 'active',
        ]);

        $service = new AccountDeletionService();
        $result = $service->confirmAndDelete($user, 'correct_password');

        $this->assertTrue($result);
        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }

    // ── API Endpoint Tests ───────────────────────────────────

    public function test_api_delete_account_with_correct_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
            'status' => 'active',
        ]);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->deleteJson('/api/v1/auth/account', [
                'password' => 'password',
            ]);

        $response->assertOk();
        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }

    public function test_api_delete_account_with_wrong_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
            'status' => 'active',
        ]);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->deleteJson('/api/v1/auth/account', [
                'password' => 'wrong_password',
            ]);

        $response->assertUnauthorized();
        $this->assertNull($user->fresh()->deleted_at);
    }

    public function test_api_delete_account_requires_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
            'status' => 'active',
        ]);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->deleteJson('/api/v1/auth/account', []);

        $response->assertUnprocessable();
    }

    public function test_api_delete_account_unauthenticated(): void
    {
        $response = $this->deleteJson('/api/v1/auth/account', [
            'password' => 'password',
        ]);

        $response->assertUnauthorized();
    }

    // ── Edge Cases ───────────────────────────────────────────

    public function test_deleted_user_cannot_login(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
            'status' => 'active',
        ]);

        $service = new AccountDeletionService();
        $service->executeDelete($user);

        // Try to login with the original email (should fail)
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'ali@example.com', // original email is now anonymized
            'password' => 'password',
        ]);

        $response->assertUnauthorized();
    }

    public function test_deletion_clears_2fa_data(): void
    {
        $user = User::factory()->create([
            'two_factor_secret' => 'secret',
            'two_factor_recovery_codes' => json_encode(['code1', 'code2']),
            'two_factor_confirmed_at' => now(),
            'status' => 'active',
        ]);

        $service = new AccountDeletionService();
        $service->executeDelete($user);

        $user->refresh();
        $this->assertNull($user->two_factor_secret);
        $this->assertNull($user->two_factor_recovery_codes);
        $this->assertNull($user->two_factor_confirmed_at);
    }

    public function test_deletion_clears_device_token(): void
    {
        $user = User::factory()->create([
            'device_token' => 'fcm_token_123',
            'device_platform' => 'ios',
            'status' => 'active',
        ]);

        $service = new AccountDeletionService();
        $service->executeDelete($user);

        $user->refresh();
        $this->assertNull($user->device_token);
        $this->assertNull($user->device_platform);
    }
}
