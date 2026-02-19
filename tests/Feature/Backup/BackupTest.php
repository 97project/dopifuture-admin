<?php

namespace Tests\Feature\Backup;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class BackupTest extends TestCase
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

        foreach (['backups.view', 'backups.create', 'backups.delete'] as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }
        $role->syncPermissions(Permission::all());

        $user = User::factory()->create(['password' => Hash::make('password'), 'status' => 'active']);
        $user->assignRole($role);
        return $user;
    }

    public function test_admin_can_view_backup_index(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $response = $this->get('/admin/backups');
        $response->assertOk();
        $response->assertSee(__('admin.backups'));
    }

    public function test_backup_index_shows_empty_state(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $response = $this->get('/admin/backups');
        $response->assertOk();
        $response->assertSee(__('admin.no_backups'));
    }

    public function test_unauthorized_user_cannot_view_backups(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $this->actingAs($user);

        $response = $this->get('/admin/backups');
        $response->assertForbidden();
    }

    public function test_unauthenticated_user_redirected_from_backups(): void
    {
        $response = $this->get('/admin/backups');
        $response->assertRedirect('/admin/login');
    }

    public function test_delete_nonexistent_backup_returns_error(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $response = $this->delete('/admin/backups/nonexistent.zip');
        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_download_nonexistent_backup_returns_error(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $response = $this->get('/admin/backups/nonexistent.zip/download');
        $response->assertRedirect();
        $response->assertSessionHas('error');
    }
}
