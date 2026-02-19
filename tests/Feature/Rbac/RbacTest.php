<?php

namespace Tests\Feature\Rbac;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RbacTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    private function createSuperAdmin(): User
    {
        $role = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $user = User::factory()->create(['password' => Hash::make('password'), 'status' => 'active']);
        $user->assignRole($role);
        return $user;
    }

    private function createViewerUser(): User
    {
        $viewerRole = Role::firstOrCreate(['name' => 'viewer', 'guard_name' => 'web']);
        $user = User::factory()->create(['password' => Hash::make('password'), 'status' => 'active']);
        $user->assignRole($viewerRole);
        return $user;
    }

    // ── Role CRUD ──────────────────────────────────────────────

    public function test_super_admin_can_list_roles(): void
    {
        $admin = $this->createSuperAdmin();
        $this->actingAs($admin);

        $response = $this->get('/admin/roles');
        $response->assertOk();
    }

    public function test_super_admin_can_create_role(): void
    {
        $admin = $this->createSuperAdmin();
        $this->actingAs($admin);

        $response = $this->post('/admin/roles', [
            'name' => 'test-role',
            'permissions' => [],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('roles', ['name' => 'test-role']);
    }

    public function test_super_admin_can_update_role(): void
    {
        $admin = $this->createSuperAdmin();
        $this->actingAs($admin);

        $role = Role::create(['name' => 'editable', 'guard_name' => 'web']);

        $response = $this->put("/admin/roles/{$role->id}", [
            'name' => 'edited-role',
            'permissions' => [],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('roles', ['name' => 'edited-role']);
    }

    public function test_super_admin_can_delete_role(): void
    {
        $admin = $this->createSuperAdmin();
        $this->actingAs($admin);

        $role = Role::create(['name' => 'deletable', 'guard_name' => 'web']);

        $response = $this->delete("/admin/roles/{$role->id}");
        $response->assertRedirect();
        $this->assertDatabaseMissing('roles', ['name' => 'deletable']);
    }

    public function test_role_with_users_cannot_be_deleted(): void
    {
        $admin = $this->createSuperAdmin();
        $this->actingAs($admin);

        $role = Role::create(['name' => 'in-use', 'guard_name' => 'web']);
        User::factory()->create(['status' => 'active'])->assignRole($role);

        $response = $this->delete("/admin/roles/{$role->id}");
        $response->assertRedirect();
        $this->assertDatabaseHas('roles', ['name' => 'in-use']);
    }

    // ── Permission Assignment ──────────────────────────────────

    public function test_permissions_can_be_assigned_to_role(): void
    {
        $admin = $this->createSuperAdmin();
        $this->actingAs($admin);

        $perm = Permission::create(['name' => 'users.view', 'guard_name' => 'web']);
        $role = Role::create(['name' => 'with-perms', 'guard_name' => 'web']);

        $this->put("/admin/roles/{$role->id}", [
            'name' => 'with-perms',
            'permissions' => [$perm->id],
        ]);

        $role->refresh();
        $this->assertTrue($role->hasPermissionTo('users.view'));
    }

    // ── Permission Sync Command ────────────────────────────────

    public function test_permissions_sync_command_runs(): void
    {
        $this->artisan('permissions:sync')
            ->assertExitCode(0);

        $this->assertGreaterThan(0, Permission::count());
    }

    // ── Access Control ─────────────────────────────────────────

    public function test_guest_cannot_access_admin_panel(): void
    {
        $response = $this->get('/admin');
        $response->assertRedirect();
    }

    public function test_api_user_crud_requires_permission(): void
    {
        $admin = $this->createSuperAdmin();
        $token = $admin->createToken('test')->plainTextToken;

        $response = $this->getJson('/api/v1/users', [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertOk();
    }
}
