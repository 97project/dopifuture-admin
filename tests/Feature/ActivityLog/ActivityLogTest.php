<?php

namespace Tests\Feature\ActivityLog;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
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

    private function createAdmin(): User
    {
        $role = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $user = User::factory()->create(['password' => Hash::make('password'), 'status' => 'active']);
        $user->assignRole($role);
        return $user;
    }

    private function seedLogs(User $user, int $count = 10): void
    {
        $actions = ['create', 'update', 'delete', 'login_success'];
        $modules = ['users', 'roles', 'settings'];

        for ($i = 0; $i < $count; $i++) {
            ActivityLog::create([
                'actor_type' => User::class,
                'actor_id' => $user->id,
                'action' => $actions[array_rand($actions)],
                'module' => $modules[array_rand($modules)],
                'subject_type' => User::class,
                'subject_id' => $user->id,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'PHPUnit',
                'properties' => json_encode(['old' => ['name' => 'Before'], 'new' => ['name' => 'After']]),
            ]);
        }
    }

    // ── Activity Log Index ─────────────────────────────────────

    public function test_admin_can_view_activity_logs(): void
    {
        $admin = $this->createAdmin();
        $this->seedLogs($admin);
        $this->actingAs($admin);

        $response = $this->get('/admin/activity-logs');
        $response->assertOk();
    }

    public function test_activity_logs_can_be_filtered_by_module(): void
    {
        $admin = $this->createAdmin();
        $this->seedLogs($admin, 20);
        $this->actingAs($admin);

        $response = $this->get('/admin/activity-logs?module=users');
        $response->assertOk();
    }

    public function test_activity_logs_can_be_filtered_by_action(): void
    {
        $admin = $this->createAdmin();
        $this->seedLogs($admin, 20);
        $this->actingAs($admin);

        $response = $this->get('/admin/activity-logs?action=create');
        $response->assertOk();
    }

    public function test_activity_logs_can_be_filtered_by_date_range(): void
    {
        $admin = $this->createAdmin();
        $this->seedLogs($admin);
        $this->actingAs($admin);

        $response = $this->get('/admin/activity-logs?date_from=' . now()->subDay()->toDateString() . '&date_to=' . now()->toDateString());
        $response->assertOk();
    }

    // ── Export ──────────────────────────────────────────────────

    public function test_admin_can_export_activity_logs(): void
    {
        $admin = $this->createAdmin();
        $this->seedLogs($admin);
        $this->actingAs($admin);

        $response = $this->get('/admin/activity-logs/export');
        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    // ── Automatic Logging ──────────────────────────────────────

    public function test_user_login_creates_activity_log(): void
    {
        $admin = $this->createAdmin();

        $this->post('/admin/login', [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        // Since auth()->user() is null when handleSuccessfulLogin logs,
        // the actor_id is null but subject_id is the user
        $this->assertDatabaseHas('activity_logs', [
            'subject_id' => $admin->id,
            'action' => 'login_success',
        ]);
    }

    // ── API Activity Logs ──────────────────────────────────────

    public function test_api_list_activity_logs(): void
    {
        $admin = $this->createAdmin();
        $token = $admin->createToken('test')->plainTextToken;
        $this->seedLogs($admin);

        $response = $this->getJson('/api/v1/activity-logs', [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertOk()
            ->assertJsonStructure(['data']);
    }
}
