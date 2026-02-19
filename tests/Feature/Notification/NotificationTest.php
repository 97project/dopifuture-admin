<?php

namespace Tests\Feature\Notification;

use App\Models\NotificationTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class NotificationTest extends TestCase
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

        foreach (['notifications.view', 'notifications.create', 'notifications.edit', 'notifications.delete', 'notifications.send'] as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }
        $role->syncPermissions(Permission::all());

        $user = User::factory()->create(['password' => Hash::make('password'), 'status' => 'active']);
        $user->assignRole($role);
        return $user;
    }

    private function createTemplate(array $attrs = []): NotificationTemplate
    {
        return NotificationTemplate::create(array_merge([
            'key' => 'test_template',
            'title' => ['tr' => 'Test Başlık', 'en' => 'Test Title'],
            'body' => ['tr' => 'Test İçerik', 'en' => 'Test Body'],
            'channels' => ['database'],
            'is_active' => true,
        ], $attrs));
    }

    // ── Admin Notification Templates ─────────────────────────

    public function test_admin_can_view_notification_index(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $response = $this->get('/admin/notifications');
        $response->assertOk();
    }

    public function test_admin_can_view_template_index(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $this->createTemplate();

        $response = $this->get('/admin/notification-templates');
        $response->assertOk();
        $response->assertSee('test_template');
    }

    public function test_admin_can_create_template(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $response = $this->get('/admin/notification-templates/create');
        $response->assertOk();

        $response = $this->post('/admin/notification-templates', [
            'key' => 'welcome_msg',
            'title' => ['tr' => 'Hoşgeldiniz', 'en' => 'Welcome'],
            'body' => ['tr' => 'Merhaba!', 'en' => 'Hello!'],
            'channels' => ['database'],
            'is_active' => true,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('notification_templates', ['key' => 'welcome_msg']);
    }

    public function test_admin_can_update_template(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $template = $this->createTemplate();

        $response = $this->put("/admin/notification-templates/{$template->id}", [
            'key' => 'updated_key',
            'title' => ['tr' => 'Güncel', 'en' => 'Updated'],
            'body' => ['tr' => 'Güncel İçerik', 'en' => 'Updated Body'],
            'channels' => ['database', 'fcm'],
            'is_active' => false,
        ]);

        $response->assertRedirect();
        $template->refresh();
        $this->assertEquals('updated_key', $template->key);
        $this->assertFalse($template->is_active);
    }

    public function test_admin_can_delete_template(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $template = $this->createTemplate();

        $response = $this->delete("/admin/notification-templates/{$template->id}");
        $response->assertRedirect();
        $this->assertDatabaseMissing('notification_templates', ['id' => $template->id]);
    }

    public function test_template_validates_required_fields(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $response = $this->post('/admin/notification-templates', []);
        $response->assertSessionHasErrors(['key', 'title', 'body']);
    }

    public function test_template_key_must_be_unique(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $this->createTemplate(['key' => 'unique_key']);

        $response = $this->post('/admin/notification-templates', [
            'key' => 'unique_key',
            'title' => ['tr' => 'Dup', 'en' => 'Dup'],
            'body' => ['tr' => 'Dup', 'en' => 'Dup'],
            'channels' => ['database'],
        ]);

        $response->assertSessionHasErrors(['key']);
    }

    // ── Send Notifications ───────────────────────────────────

    public function test_admin_can_send_custom_notification(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $target = User::factory()->create(['status' => 'active']);

        $response = $this->post('/admin/notifications/send', [
            'mode' => 'custom',
            'custom_title' => 'Test Bildirim',
            'custom_body' => 'Bu bir test bildirimidir',
            'channels' => ['database'],
            'target' => 'selected',
            'user_ids' => [$target->id],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    public function test_admin_can_send_template_notification(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $this->createTemplate();
        $target = User::factory()->create(['status' => 'active']);

        $response = $this->post('/admin/notifications/send', [
            'mode' => 'template',
            'template_key' => 'test_template',
            'channels' => ['database'],
            'target' => 'selected',
            'user_ids' => [$target->id],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    // ── API Notification Endpoints ───────────────────────────

    public function test_api_can_list_notifications(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->getJson('/api/v1/notifications');

        $response->assertOk();
    }

    public function test_api_can_get_unread_count(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->getJson('/api/v1/notifications/unread-count');

        $response->assertOk();
        $response->assertJsonStructure(['data' => ['count']]);
    }

    public function test_api_can_mark_all_as_read(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->postJson('/api/v1/notifications/read-all');

        $response->assertOk();
    }

    public function test_api_can_update_device_token(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->postJson('/api/v1/auth/device-token', [
                'device_token' => 'fcm_test_token_12345',
                'device_platform' => 'android',
            ]);

        $response->assertOk();
        $user->refresh();
        $this->assertEquals('fcm_test_token_12345', $user->device_token);
        $this->assertEquals('android', $user->device_platform);
    }

    // ── Authorization ────────────────────────────────────────

    public function test_unauthorized_user_cannot_view_notifications(): void
    {
        // Register permissions so Gate doesn't throw
        foreach (['notifications.view', 'notifications.create', 'notifications.edit', 'notifications.delete', 'notifications.send'] as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $user = User::factory()->create(['status' => 'active']);
        $this->actingAs($user);

        $response = $this->get('/admin/notifications');
        $response->assertForbidden();
    }

    public function test_unauthorized_user_cannot_manage_templates(): void
    {
        // Register permissions so Gate doesn't throw
        foreach (['notifications.view', 'notifications.create', 'notifications.edit', 'notifications.delete', 'notifications.send'] as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $user = User::factory()->create(['status' => 'active']);
        $this->actingAs($user);

        $response = $this->get('/admin/notification-templates');
        $response->assertForbidden();
    }

    // ── Model Tests ──────────────────────────────────────────

    public function test_notification_template_find_by_key(): void
    {
        $this->createTemplate(['key' => 'find_me']);

        $found = NotificationTemplate::findByKey('find_me');
        $this->assertNotNull($found);
        $this->assertEquals('find_me', $found->key);

        $notFound = NotificationTemplate::findByKey('nonexistent');
        $this->assertNull($notFound);
    }

    public function test_notification_template_translations(): void
    {
        $template = $this->createTemplate();

        app()->setLocale('tr');
        $this->assertEquals('Test Başlık', $template->getTranslation('title'));

        app()->setLocale('en');
        $this->assertEquals('Test Title', $template->getTranslation('title'));
    }

    public function test_notification_template_active_scope(): void
    {
        $this->createTemplate(['key' => 'active_one', 'is_active' => true]);
        $this->createTemplate(['key' => 'inactive_one', 'is_active' => false]);

        $activeOnes = NotificationTemplate::active()->get();
        $this->assertCount(1, $activeOnes);
        $this->assertEquals('active_one', $activeOnes->first()->key);
    }
}
