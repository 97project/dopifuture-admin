<?php

namespace Tests\Feature\Cms;

use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FormTest extends TestCase
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

    private function createForm(): Form
    {
        return Form::create([
            'name' => 'İletişim Formu',
            'slug' => 'iletisim',
            'fields' => [
                ['name' => 'email', 'type' => 'email', 'label' => ['tr' => 'E-posta', 'en' => 'Email'], 'required' => true],
                ['name' => 'message', 'type' => 'textarea', 'label' => ['tr' => 'Mesaj', 'en' => 'Message'], 'required' => true],
            ],
            'is_active' => true,
        ]);
    }

    public function test_admin_can_list_forms(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $this->createForm();

        $response = $this->get('/admin/forms');
        $response->assertOk();
        $response->assertSee('İletişim Formu');
    }

    public function test_admin_can_view_create_form(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $response = $this->get('/admin/forms/create');
        $response->assertOk();
    }

    public function test_admin_can_create_form(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $response = $this->post('/admin/forms', [
            'name' => 'Başvuru Formu',
            'fields' => [
                ['name' => 'name', 'type' => 'text', 'label' => ['tr' => 'Ad', 'en' => 'Name']],
            ],
            'is_active' => true,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('forms', ['slug' => 'basvuru-formu']);
    }

    public function test_admin_can_update_form(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $form = $this->createForm();

        $response = $this->put("/admin/forms/{$form->id}", [
            'name' => 'Güncel Form',
            'fields' => [
                ['name' => 'email', 'type' => 'email', 'label' => ['tr' => 'E-posta', 'en' => 'Email']],
            ],
            'is_active' => false,
        ]);

        $response->assertRedirect();
        $form->refresh();
        $this->assertFalse((bool) $form->is_active);
    }

    public function test_admin_can_delete_form(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $form = $this->createForm();

        $response = $this->delete("/admin/forms/{$form->id}");
        $response->assertRedirect();
        $this->assertDatabaseMissing('forms', ['id' => $form->id]);
    }

    public function test_form_validates_required_fields(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $response = $this->post('/admin/forms', []);
        $response->assertSessionHasErrors(['name', 'fields']);
    }

    // ── Submissions ──────────────────────────────────────

    public function test_admin_can_view_submissions(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $form = $this->createForm();
        FormSubmission::create([
            'form_id' => $form->id,
            'data' => ['email' => 'test@example.com', 'message' => 'Hello'],
            'ip_address' => '127.0.0.1',
        ]);

        $response = $this->get("/admin/forms/{$form->id}/submissions");
        $response->assertOk();
        $response->assertSee('test@example.com');
    }

    public function test_admin_can_view_submission_detail(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $form = $this->createForm();
        $sub = FormSubmission::create([
            'form_id' => $form->id,
            'data' => ['email' => 'detail@test.com'],
            'ip_address' => '1.2.3.4',
        ]);

        $response = $this->get("/admin/forms/{$form->id}/submissions/{$sub->id}");
        $response->assertOk();
        $response->assertSee('detail@test.com');

        // Marks as read
        $sub->refresh();
        $this->assertTrue((bool) $sub->is_read);
    }

    public function test_admin_can_delete_submission(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $form = $this->createForm();
        $sub = FormSubmission::create([
            'form_id' => $form->id,
            'data' => ['msg' => 'delete me'],
            'ip_address' => '0.0.0.0',
        ]);

        $response = $this->delete("/admin/forms/{$form->id}/submissions/{$sub->id}");
        $response->assertRedirect();
        $this->assertDatabaseMissing('form_submissions', ['id' => $sub->id]);
    }

    public function test_unread_filter_works(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $form = $this->createForm();
        FormSubmission::create([
            'form_id' => $form->id,
            'data' => ['m' => 'unread'],
            'ip_address' => '5.5.5.5',
            'is_read' => false,
        ]);

        $response = $this->get("/admin/forms/{$form->id}/submissions?unread=1");
        $response->assertOk();
    }
}
