<?php

namespace Tests\Feature\Settings;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SettingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    private function createAdmin(): User
    {
        $role = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $user = User::factory()->create(['password' => Hash::make('password'), 'status' => 'active']);
        $user->assignRole($role);
        return $user;
    }

    private function seedSettings(): void
    {
        Setting::create(['group' => 'general', 'key' => 'site_name', 'value' => 'Panel26', 'type' => 'string', 'is_encrypted' => false]);
        Setting::create(['group' => 'general', 'key' => 'maintenance_mode', 'value' => '0', 'type' => 'boolean', 'is_encrypted' => false]);
        Setting::create(['group' => 'mail', 'key' => 'smtp_password', 'value' => 'secret', 'type' => 'string', 'is_encrypted' => true]);
    }

    // ── Settings Index ─────────────────────────────────────────

    public function test_admin_can_view_settings(): void
    {
        $admin = $this->createAdmin();
        $this->seedSettings();
        $this->actingAs($admin);

        $response = $this->get('/admin/settings');
        $response->assertOk();
        $response->assertSee('site_name');
    }

    public function test_settings_grouped_correctly(): void
    {
        $admin = $this->createAdmin();
        $this->seedSettings();
        $this->actingAs($admin);

        $response = $this->get('/admin/settings');
        $response->assertOk();
        $response->assertSee('general');
        $response->assertSee('mail');
    }

    // ── Settings Update ────────────────────────────────────────

    public function test_admin_can_update_settings(): void
    {
        $admin = $this->createAdmin();
        $this->seedSettings();
        $this->actingAs($admin);

        $response = $this->put('/admin/settings', [
            'settings' => [
                ['group' => 'general', 'key' => 'site_name', 'value' => 'Panel26 Updated'],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('settings', ['key' => 'site_name', 'value' => 'Panel26 Updated']);
    }

    public function test_boolean_setting_can_be_toggled(): void
    {
        $admin = $this->createAdmin();
        $this->seedSettings();
        $this->actingAs($admin);

        $response = $this->put('/admin/settings', [
            'settings' => [
                ['group' => 'general', 'key' => 'maintenance_mode', 'value' => '1'],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('settings', ['key' => 'maintenance_mode', 'value' => '1']);
    }

    public function test_encrypted_setting_can_be_updated(): void
    {
        $admin = $this->createAdmin();
        $this->seedSettings();
        $this->actingAs($admin);

        $response = $this->put('/admin/settings', [
            'settings' => [
                ['group' => 'mail', 'key' => 'smtp_password', 'value' => 'new-secret-password'],
            ],
        ]);

        $response->assertRedirect();
    }

    // ── API Public Settings ────────────────────────────────────

    public function test_api_returns_public_settings(): void
    {
        $this->seedSettings();

        $response = $this->getJson('/api/v1/settings/public');
        $response->assertOk()
            ->assertJsonStructure(['data']);
    }

    public function test_api_settings_excludes_encrypted(): void
    {
        $this->seedSettings();

        $response = $this->getJson('/api/v1/settings/public');
        $response->assertOk();

        $data = $response->json('data');
        if (is_array($data)) {
            foreach ($data as $setting) {
                $this->assertNotEquals('smtp_password', $setting['key'] ?? '');
            }
        }
    }
}
