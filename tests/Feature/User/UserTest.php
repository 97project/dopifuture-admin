<?php

namespace Tests\Feature\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserTest extends TestCase
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

    // ── Web User CRUD ──────────────────────────────────────────

    public function test_admin_can_list_users(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        User::factory()->count(5)->create();

        $response = $this->get('/admin/users');
        $response->assertOk();
    }

    public function test_admin_can_search_users(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        User::factory()->create(['name' => 'SearchableUser']);

        $response = $this->get('/admin/users?search=SearchableUser');
        $response->assertOk();
        $response->assertSee('SearchableUser');
    }

    public function test_admin_can_filter_users_by_status(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        User::factory()->create(['status' => 'inactive', 'name' => 'InactiveGuy']);

        $response = $this->get('/admin/users?status=inactive');
        $response->assertOk();
    }

    public function test_admin_can_create_user(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $response = $this->post('/admin/users', [
            'name' => 'New User',
            'surname' => 'Test',
            'email' => 'newuser@panel26.test',
            'password' => 'securePassword1!',
            'password_confirmation' => 'securePassword1!',
            'status' => 'active',
            'locale' => 'tr',
            'timezone' => 'Europe/Istanbul',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', ['email' => 'newuser@panel26.test']);
    }

    public function test_admin_can_view_user_detail(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $user = User::factory()->create();

        $response = $this->get("/admin/users/{$user->id}");
        $response->assertOk();
        $response->assertSee($user->name);
    }

    public function test_admin_can_view_user_tabs(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $user = User::factory()->create();

        foreach (['profile', 'security', 'roles', 'api_keys', 'devices', 'audit'] as $tab) {
            $response = $this->get("/admin/users/{$user->id}?tab={$tab}");
            $response->assertOk();
        }
    }

    public function test_admin_can_update_user(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $user = User::factory()->create();

        $response = $this->put("/admin/users/{$user->id}", [
            'name' => 'Updated Name',
            'email' => $user->email,
            'status' => 'active',
            'locale' => 'en',
            'timezone' => 'UTC',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Updated Name']);
    }

    public function test_admin_can_delete_user(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $user = User::factory()->create();

        $response = $this->delete("/admin/users/{$user->id}");
        $response->assertRedirect();
    }

    public function test_admin_cannot_delete_self(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $response = $this->delete("/admin/users/{$admin->id}");
        $response->assertRedirect();
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_user_create_validates_required_fields(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $response = $this->post('/admin/users', []);
        $response->assertSessionHasErrors(['name', 'email', 'password']);
    }

    public function test_user_create_validates_unique_email(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $existing = User::factory()->create();

        $response = $this->post('/admin/users', [
            'name' => 'Dup',
            'email' => $existing->email,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
    }

    // ── Bulk Actions ───────────────────────────────────────────

    public function test_admin_can_bulk_activate_users(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $users = User::factory()->count(3)->create(['status' => 'inactive']);

        $response = $this->post('/admin/users/bulk-action', [
            'ids' => $users->pluck('id')->toArray(),
            'action' => 'activate',
        ]);

        $response->assertRedirect();
        foreach ($users as $u) {
            $u->refresh();
            $this->assertEquals('active', $u->status);
        }
    }

    // ── Avatar Upload ──────────────────────────────────────────

    public function test_admin_can_upload_user_avatar(): void
    {
        Storage::fake('public');
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $response = $this->post('/admin/users', [
            'name' => 'Avatar User',
            'email' => 'avatar@panel26.test',
            'password' => 'securePassword1!',
            'password_confirmation' => 'securePassword1!',
            'status' => 'active',
            'locale' => 'tr',
            'avatar' => UploadedFile::fake()->image('avatar.jpg', 200, 200),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', ['email' => 'avatar@panel26.test']);
    }

    // ── API User CRUD ──────────────────────────────────────────

    public function test_api_list_users(): void
    {
        $admin = $this->createAdmin();
        $token = $admin->createToken('test')->plainTextToken;

        User::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/users', [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertOk()
            ->assertJsonStructure(['data']);
    }

    public function test_api_create_user(): void
    {
        $admin = $this->createAdmin();
        $token = $admin->createToken('test')->plainTextToken;

        $response = $this->postJson('/api/v1/users', [
            'name' => 'API User',
            'email' => 'apiuser@panel26.test',
            'password' => 'securePassword1!',
            'password_confirmation' => 'securePassword1!',
        ], ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(201)
            ->assertJsonPath('data.email', 'apiuser@panel26.test');
    }

    public function test_api_show_user(): void
    {
        $admin = $this->createAdmin();
        $token = $admin->createToken('test')->plainTextToken;

        $user = User::factory()->create();

        $response = $this->getJson("/api/v1/users/{$user->id}", [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.id', $user->id);
    }

    public function test_api_update_user(): void
    {
        $admin = $this->createAdmin();
        $token = $admin->createToken('test')->plainTextToken;

        $user = User::factory()->create();

        $response = $this->putJson("/api/v1/users/{$user->id}", [
            'name' => 'Updated API User',
            'email' => $user->email,
        ], ['Authorization' => 'Bearer ' . $token]);

        $response->assertOk();
    }

    public function test_api_delete_user(): void
    {
        $admin = $this->createAdmin();
        $token = $admin->createToken('test')->plainTextToken;

        $user = User::factory()->create();

        $response = $this->deleteJson("/api/v1/users/{$user->id}", [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertOk();
    }
}
