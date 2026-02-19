<?php

namespace Tests\Feature\Translation;

use App\Models\Translation;
use App\Models\User;
use App\Services\TranslationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TranslationTest extends TestCase
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

    private function createTranslation(array $attrs = []): Translation
    {
        return Translation::create(array_merge([
            'locale' => 'tr',
            'group' => 'admin',
            'key' => 'test_key',
            'value' => 'Test Değer',
        ], $attrs));
    }

    // ── Admin CRUD ───────────────────────────────────────────

    public function test_admin_can_view_translation_index(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $this->createTranslation();

        $response = $this->get('/admin/translations');
        $response->assertOk();
    }

    public function test_admin_can_create_translation(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $response = $this->post('/admin/translations', [
            'group' => 'admin',
            'key' => 'new_key',
            'values' => [
                'tr' => 'Yeni Değer',
                'en' => 'New Value',
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('translations', [
            'group' => 'admin',
            'key' => 'new_key',
            'locale' => 'tr',
            'value' => 'Yeni Değer',
        ]);
        $this->assertDatabaseHas('translations', [
            'group' => 'admin',
            'key' => 'new_key',
            'locale' => 'en',
            'value' => 'New Value',
        ]);
    }

    public function test_admin_can_update_translation(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $tr = $this->createTranslation();

        $response = $this->put("/admin/translations/{$tr->id}", [
            'values' => [
                'tr' => 'Güncellenmiş Değer',
            ],
        ]);

        $response->assertRedirect();
        $tr->refresh();
        $this->assertEquals('Güncellenmiş Değer', $tr->value);
    }

    public function test_admin_can_delete_translation(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $tr = $this->createTranslation();

        $response = $this->delete("/admin/translations/{$tr->id}");
        $response->assertRedirect();
        $this->assertDatabaseMissing('translations', ['id' => $tr->id]);
    }

    public function test_translation_validates_required_fields(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $response = $this->post('/admin/translations', []);
        $response->assertSessionHasErrors(['group', 'key', 'values']);
    }

    // ── Service ──────────────────────────────────────────────

    public function test_service_can_set_and_get_translation(): void
    {
        $service = new TranslationService();
        $service->setTranslation('admin', 'hello', 'tr', 'Merhaba');
        $service->setTranslation('admin', 'hello', 'en', 'Hello');

        $this->assertEquals('Merhaba', $service->getTranslation('admin', 'hello', 'tr'));
        $this->assertEquals('Hello', $service->getTranslation('admin', 'hello', 'en'));
    }

    public function test_service_can_delete_translation_group_key(): void
    {
        $service = new TranslationService();
        $service->setTranslation('admin', 'del_key', 'tr', 'Silinecek');
        $service->setTranslation('admin', 'del_key', 'en', 'To Delete');

        $service->deleteTranslation('admin', 'del_key');

        $this->assertDatabaseMissing('translations', ['group' => 'admin', 'key' => 'del_key']);
    }

    public function test_service_export_json(): void
    {
        $this->createTranslation(['locale' => 'tr', 'group' => 'admin', 'key' => 'k1', 'value' => 'V1']);
        $this->createTranslation(['locale' => 'tr', 'group' => 'admin', 'key' => 'k2', 'value' => 'V2']);

        $service = new TranslationService();
        $exported = $service->exportJson('tr');

        $this->assertArrayHasKey('admin', $exported);
        $this->assertEquals('V1', $exported['admin']['k1']);
        $this->assertEquals('V2', $exported['admin']['k2']);
    }

    public function test_service_import_json(): void
    {
        $service = new TranslationService();
        $count = $service->importJson('en', [
            'admin' => ['key1' => 'Value 1', 'key2' => 'Value 2'],
            'api' => ['msg' => 'Hello API'],
        ]);

        $this->assertEquals(3, $count);
        $this->assertDatabaseHas('translations', [
            'locale' => 'en',
            'group' => 'admin',
            'key' => 'key1',
            'value' => 'Value 1',
        ]);
        $this->assertDatabaseHas('translations', [
            'locale' => 'en',
            'group' => 'api',
            'key' => 'msg',
            'value' => 'Hello API',
        ]);
    }

    // ── Model Scopes ─────────────────────────────────────────

    public function test_translation_scopes(): void
    {
        $this->createTranslation(['locale' => 'tr', 'group' => 'admin', 'key' => 'a']);
        $this->createTranslation(['locale' => 'en', 'group' => 'admin', 'key' => 'a']);
        $this->createTranslation(['locale' => 'tr', 'group' => 'api', 'key' => 'b']);

        $this->assertCount(2, Translation::forLocale('tr')->get());
        $this->assertCount(1, Translation::forLocale('en')->get());
        $this->assertCount(2, Translation::forGroup('admin')->get());
        $this->assertCount(1, Translation::forGroup('api')->get());
    }

    public function test_translation_search_scope(): void
    {
        $this->createTranslation(['key' => 'dashboard_title', 'value' => 'Kontrol Paneli']);
        $this->createTranslation(['key' => 'sidebar_users', 'value' => 'Kullanıcılar']);

        $results = Translation::search('dashboard')->get();
        $this->assertCount(1, $results);
        $this->assertEquals('dashboard_title', $results->first()->key);
    }

    // ── Unique Constraint ────────────────────────────────────

    public function test_translation_upsert_behavior(): void
    {
        $this->createTranslation(['locale' => 'tr', 'group' => 'admin', 'key' => 'upsert_key', 'value' => 'Old']);

        Translation::updateOrCreate(
            ['locale' => 'tr', 'group' => 'admin', 'key' => 'upsert_key'],
            ['value' => 'New']
        );

        $this->assertDatabaseCount('translations', 1);
        $this->assertDatabaseHas('translations', [
            'locale' => 'tr',
            'group' => 'admin',
            'key' => 'upsert_key',
            'value' => 'New',
        ]);
    }
}
