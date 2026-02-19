<?php

namespace Tests\Feature\Policy;

use App\Models\User;
use App\Models\ApiKey;
use App\Models\Setting;
use App\Models\Language;
use App\Models\Translation;
use App\Models\ActivityLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class PolicyTest extends TestCase
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

        // Create all permissions that policies check for
        $permissions = [
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            'users.bulkAction',
            'roles.view',
            'roles.create',
            'roles.edit',
            'roles.delete',
            'roles.syncPermissions',
            'settings.view',
            'settings.edit',
            'languages.view',
            'languages.create',
            'languages.edit',
            'languages.delete',
            'translations.view',
            'translations.create',
            'translations.edit',
            'translations.delete',
            'translations.import',
            'translations.export',
            'activity-logs.view',
            'activity-logs.export',
            'api-keys.view',
            'api-keys.create',
            'api-keys.edit',
            'api-keys.delete',
            'api-keys.rotate',
            'api-keys.revoke',
        ];
        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    private function createSuperAdmin(): User
    {
        $role = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $user = User::factory()->create([
            'status' => 'active',
            'password' => Hash::make('password'),
        ]);
        $user->assignRole($role);
        return $user;
    }

    private function createUserWithPermissions(array $permissions = []): User
    {
        // Create the 'editor' role
        $role = Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);

        foreach ($permissions as $perm) {
            $permission = Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
            $role->givePermissionTo($permission);
        }

        $user = User::factory()->create([
            'status' => 'active',
            'password' => Hash::make('password'),
        ]);
        $user->assignRole($role);

        return $user;
    }

    private function createUserWithoutPermissions(): User
    {
        $role = Role::firstOrCreate(['name' => 'viewer', 'guard_name' => 'web']);
        $user = User::factory()->create([
            'status' => 'active',
            'password' => Hash::make('password'),
        ]);
        $user->assignRole($role);
        return $user;
    }

    // ── Super Admin Bypass ──────────────────────────────────────

    public function test_super_admin_can_access_all_resources(): void
    {
        $admin = $this->createSuperAdmin();

        // Users
        $response = $this->actingAs($admin)->get('/admin/users');
        $response->assertStatus(200);

        // Roles
        $response = $this->actingAs($admin)->get('/admin/roles');
        $response->assertStatus(200);

        // Settings
        $response = $this->actingAs($admin)->get('/admin/settings');
        $response->assertStatus(200);

        // Languages
        $response = $this->actingAs($admin)->get('/admin/languages');
        $response->assertStatus(200);

        // Activity Logs
        $response = $this->actingAs($admin)->get('/admin/activity-logs');
        $response->assertStatus(200);
    }

    // ── User Policy ─────────────────────────────────────────────

    public function test_user_with_users_view_can_list_users(): void
    {
        $user = $this->createUserWithPermissions(['users.view']);

        $response = $this->actingAs($user)->get('/admin/users');
        $response->assertStatus(200);
    }

    public function test_user_without_permission_cannot_list_users(): void
    {
        $user = $this->createUserWithoutPermissions();

        $response = $this->actingAs($user)->get('/admin/users');
        $response->assertStatus(403);
    }

    public function test_user_with_users_create_can_access_create_form(): void
    {
        $user = $this->createUserWithPermissions(['users.create']);

        $response = $this->actingAs($user)->get('/admin/users/create');
        $response->assertStatus(200);
    }

    public function test_user_without_create_permission_cannot_create_user(): void
    {
        $user = $this->createUserWithoutPermissions();

        $response = $this->actingAs($user)->get('/admin/users/create');
        $response->assertStatus(403);
    }

    public function test_user_cannot_delete_themselves(): void
    {
        $admin = $this->createSuperAdmin();

        // Super admin bypass applies, but the controller has explicit self-delete check
        $response = $this->actingAs($admin)->delete("/admin/users/{$admin->id}");

        // Should redirect with error, not actually delete
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_user_with_delete_permission_can_delete_other_user(): void
    {
        $user = $this->createUserWithPermissions(['users.delete']);
        $targetUser = User::factory()->create(['status' => 'active']);

        $response = $this->actingAs($user)->delete("/admin/users/{$targetUser->id}");

        $response->assertRedirect();
        $this->assertSoftDeleted('users', ['id' => $targetUser->id]);
    }

    // ── Role Policy ─────────────────────────────────────────────

    public function test_user_with_roles_view_can_list_roles(): void
    {
        $user = $this->createUserWithPermissions(['roles.view']);

        $response = $this->actingAs($user)->get('/admin/roles');
        $response->assertStatus(200);
    }

    public function test_user_without_roles_permission_cannot_list_roles(): void
    {
        $user = $this->createUserWithoutPermissions();

        $response = $this->actingAs($user)->get('/admin/roles');
        $response->assertStatus(403);
    }

    // ── Setting Policy ──────────────────────────────────────────

    public function test_user_with_settings_view_can_access_settings(): void
    {
        $user = $this->createUserWithPermissions(['settings.view']);

        $response = $this->actingAs($user)->get('/admin/settings');
        $response->assertStatus(200);
    }

    public function test_user_without_settings_permission_cannot_access_settings(): void
    {
        $user = $this->createUserWithoutPermissions();

        $response = $this->actingAs($user)->get('/admin/settings');
        $response->assertStatus(403);
    }

    // ── Language Policy ─────────────────────────────────────────

    public function test_user_with_languages_view_can_list_languages(): void
    {
        $user = $this->createUserWithPermissions(['languages.view']);

        $response = $this->actingAs($user)->get('/admin/languages');
        $response->assertStatus(200);
    }

    public function test_user_without_languages_permission_cannot_list(): void
    {
        $user = $this->createUserWithoutPermissions();

        $response = $this->actingAs($user)->get('/admin/languages');
        $response->assertStatus(403);
    }

    // ── Translation Policy ──────────────────────────────────────

    public function test_user_with_translations_view_can_list_translations(): void
    {
        $user = $this->createUserWithPermissions(['translations.view']);

        $response = $this->actingAs($user)->get('/admin/translations');
        $response->assertStatus(200);
    }

    public function test_user_without_translations_permission_denied(): void
    {
        $user = $this->createUserWithoutPermissions();

        $response = $this->actingAs($user)->get('/admin/translations');
        $response->assertStatus(403);
    }

    // ── Activity Log Policy ─────────────────────────────────────

    public function test_user_with_activity_logs_view_can_access(): void
    {
        $user = $this->createUserWithPermissions(['activity-logs.view']);

        $response = $this->actingAs($user)->get('/admin/activity-logs');
        $response->assertStatus(200);
    }

    public function test_user_without_activity_logs_permission_denied(): void
    {
        $user = $this->createUserWithoutPermissions();

        $response = $this->actingAs($user)->get('/admin/activity-logs');
        $response->assertStatus(403);
    }

    // ── API Key Policy (API endpoints) ──────────────────────────

    public function test_user_can_list_own_api_keys(): void
    {
        $user = $this->createUserWithPermissions(['api-keys.view']);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->getJson('/api/v1/api-keys', [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertOk();
    }
}
