<?php

namespace Tests\Feature\DopiFuture;

use App\Models\Application;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ApplicationTest extends TestCase
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
        Permission::firstOrCreate(['name' => 'applications.view', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'applications.create', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'applications.edit', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'applications.delete', 'guard_name' => 'web']);
        $user = User::factory()->create(['password' => Hash::make('password'), 'status' => 'active']);
        $user->assignRole($role);
        return $user;
    }

    private function makeApp(array $overrides = []): Application
    {
        return Application::create(array_merge([
            'slug' => 'test-app-' . uniqid(),
            'name' => ['tr' => 'Test Uygulama', 'en' => 'Test App'],
            'description' => ['tr' => 'Açıklama', 'en' => 'Description'],
            'icon' => '📱',
            'color' => '#3b82f6',
            'is_active' => true,
            'sort_order' => 1,
        ], $overrides));
    }

    // ── Model ────────────────────────────────────────────────

    public function test_application_active_scope_filters_inactive(): void
    {
        $this->makeApp(['is_active' => true, 'slug' => 'active-app']);
        $this->makeApp(['is_active' => false, 'slug' => 'inactive-app']);

        $active = Application::active()->get();

        $this->assertCount(1, $active);
        $this->assertEquals('active-app', $active->first()->slug);
    }

    public function test_application_ordered_scope(): void
    {
        $this->makeApp(['sort_order' => 3, 'slug' => 'c-app']);
        $this->makeApp(['sort_order' => 1, 'slug' => 'a-app']);
        $this->makeApp(['sort_order' => 2, 'slug' => 'b-app']);

        $ordered = Application::ordered()->pluck('slug')->toArray();

        $this->assertEquals(['a-app', 'b-app', 'c-app'], $ordered);
    }

    public function test_application_translation_helper(): void
    {
        $app = $this->makeApp();
        $app = Application::find($app->id);

        app()->setLocale('tr');
        $this->assertEquals('Açıklama', $app->getTranslation('description'));

        $this->assertEquals('Description', $app->getTranslation('description', 'en'));
    }

    // ── Admin CRUD ───────────────────────────────────────────

    public function test_admin_can_list_applications(): void
    {
        $admin = $this->createAdmin();
        $this->makeApp();

        $this->actingAs($admin)
            ->get('/admin/applications')
            ->assertOk();
    }

    public function test_admin_can_create_application(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post('/admin/applications', [
            'slug' => 'new-app',
            'name_tr' => 'Yeni',
            'name_en' => 'New',
            'description_tr' => 'Açıklama',
            'description_en' => 'Desc',
            'icon' => '🚀',
            'color' => '#10b981',
            'is_active' => true,
            'sort_order' => 5,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('applications', ['slug' => 'new-app']);
    }

    public function test_admin_can_update_application(): void
    {
        $admin = $this->createAdmin();
        $app = $this->makeApp(['slug' => 'edit-me']);

        $response = $this->actingAs($admin)->put("/admin/applications/{$app->id}", [
            'slug' => 'edited',
            'name_tr' => 'Düzenlendi',
            'name_en' => 'Edited',
            'description_tr' => 'A',
            'description_en' => 'D',
            'icon' => '✏️',
            'color' => '#000',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('applications', ['slug' => 'edited']);
    }

    public function test_admin_can_delete_application(): void
    {
        $admin = $this->createAdmin();
        $app = $this->makeApp(['slug' => 'delete-me']);

        $response = $this->actingAs($admin)->delete("/admin/applications/{$app->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('applications', ['slug' => 'delete-me']);
    }

    public function test_guest_cannot_access_applications_admin(): void
    {
        $this->get('/admin/applications')->assertRedirect();
    }

    // ── API ──────────────────────────────────────────────────

    public function test_api_lists_active_applications(): void
    {
        $this->makeApp(['is_active' => true, 'slug' => 'visible']);
        $this->makeApp(['is_active' => false, 'slug' => 'hidden']);

        $response = $this->getJson('/api/v1/applications');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
    }

    public function test_api_shows_application_by_slug(): void
    {
        $this->makeApp(['slug' => 'find-me']);

        $response = $this->getJson('/api/v1/applications/find-me');

        $response->assertOk();
        $response->assertJsonPath('data.slug', 'find-me');
    }

    public function test_api_returns_404_for_missing_application(): void
    {
        $response = $this->getJson('/api/v1/applications/nonexistent');
        $response->assertNotFound();
    }

    // ── i18n ─────────────────────────────────────────────────

    public function test_application_description_respects_locale_tr(): void
    {
        $app = $this->makeApp();
        $app = Application::find($app->id);
        app()->setLocale('tr');
        $this->assertEquals('Açıklama', $app->getTranslation('description'));
    }

    public function test_application_description_respects_locale_en(): void
    {
        $app = $this->makeApp();
        $app = Application::find($app->id);
        $this->assertEquals('Description', $app->getTranslation('description', 'en'));
    }
}
