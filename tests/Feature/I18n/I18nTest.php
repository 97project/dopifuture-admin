<?php

namespace Tests\Feature\I18n;

use App\Models\Language;
use App\Models\Translation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class I18nTest extends TestCase
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
        $user = User::factory()->create(['password' => Hash::make('password'), 'status' => 'active', 'locale' => 'tr']);
        $user->assignRole($role);
        return $user;
    }

    private function seedLanguages(): void
    {
        Language::create(['code' => 'tr', 'name' => 'Turkish', 'native_name' => 'Türkçe', 'is_active' => true, 'is_default' => true, 'direction' => 'ltr', 'sort_order' => 1]);
        Language::create(['code' => 'en', 'name' => 'English', 'native_name' => 'English', 'is_active' => true, 'is_default' => false, 'direction' => 'ltr', 'sort_order' => 2]);
    }

    // ── Language CRUD ──────────────────────────────────────────

    public function test_admin_can_list_languages(): void
    {
        $admin = $this->createAdmin();
        $this->seedLanguages();
        $this->actingAs($admin);

        $response = $this->get('/admin/languages');
        $response->assertOk();
        $response->assertSee('Türkçe');
    }

    public function test_admin_can_create_language(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $response = $this->post('/admin/languages', [
            'code' => 'de',
            'name' => 'German',
            'native_name' => 'Deutsch',
            'direction' => 'ltr',
            'is_active' => true,
            'sort_order' => 3,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('languages', ['code' => 'de']);
    }

    public function test_admin_can_update_language(): void
    {
        $admin = $this->createAdmin();
        $this->seedLanguages();
        $this->actingAs($admin);

        $lang = Language::where('code', 'en')->first();

        $response = $this->put("/admin/languages/{$lang->id}", [
            'code' => 'en',
            'name' => 'English (US)',
            'native_name' => 'English',
            'direction' => 'ltr',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('languages', ['name' => 'English (US)']);
    }

    public function test_default_language_cannot_be_deleted(): void
    {
        $admin = $this->createAdmin();
        $this->seedLanguages();
        $this->actingAs($admin);

        $lang = Language::where('is_default', true)->first();

        $response = $this->delete("/admin/languages/{$lang->id}");
        $response->assertRedirect();
        $this->assertDatabaseHas('languages', ['code' => 'tr']);
    }

    // ── Locale Switching ───────────────────────────────────────

    public function test_locale_can_be_switched_via_web(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $response = $this->post('/admin/switch-locale', ['locale' => 'en']);
        $response->assertRedirect();
    }

    // ── Translation CRUD ───────────────────────────────────────

    public function test_admin_can_list_translations(): void
    {
        $admin = $this->createAdmin();
        $this->seedLanguages();
        $this->actingAs($admin);

        Translation::create([
            'group' => 'admin',
            'key' => 'test_key',
            'locale' => 'tr',
            'value' => 'Test Değer',
        ]);

        $response = $this->get('/admin/translations');
        $response->assertOk();
        $response->assertSee('test_key');
    }

    public function test_admin_can_create_translation(): void
    {
        $admin = $this->createAdmin();
        $this->seedLanguages();
        $this->actingAs($admin);

        $response = $this->post('/admin/translations', [
            'group' => 'messages',
            'key' => 'hello',
            'values' => ['tr' => 'Merhaba', 'en' => 'Hello'],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('translations', [
            'group' => 'messages',
            'key' => 'hello',
            'locale' => 'tr',
            'value' => 'Merhaba',
        ]);
    }

    // ── API Language Endpoint ──────────────────────────────────

    public function test_api_returns_active_languages(): void
    {
        $this->seedLanguages();

        $response = $this->getJson('/api/v1/languages');
        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }
}
