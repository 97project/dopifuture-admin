<?php

namespace Tests\Feature\Cms;

use App\Models\Faq;
use App\Models\FaqCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FaqTest extends TestCase
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

    private function createCategory(): FaqCategory
    {
        return FaqCategory::create([
            'name' => ['tr' => 'Genel', 'en' => 'General'],
            'slug' => 'genel',
            'is_active' => true,
            'sort_order' => 0,
        ]);
    }

    // ── FAQ Categories ───────────────────────────────────

    public function test_admin_can_view_faq_index(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $response = $this->get('/admin/faqs');
        $response->assertOk();
    }

    public function test_admin_can_create_faq_category(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $response = $this->post('/admin/faq-categories', [
            'name' => ['tr' => 'Ödeme', 'en' => 'Payment'],
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('faq_categories', ['slug' => 'odeme']);
    }

    public function test_admin_can_update_faq_category(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $cat = $this->createCategory();

        $response = $this->put("/admin/faq-categories/{$cat->id}", [
            'name' => ['tr' => 'Güncel', 'en' => 'Updated'],
            'sort_order' => 5,
        ]);

        $response->assertRedirect();
        $cat->refresh();
        $this->assertEquals(5, $cat->sort_order);
    }

    public function test_admin_can_delete_faq_category(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $cat = $this->createCategory();

        $response = $this->delete("/admin/faq-categories/{$cat->id}");
        $response->assertRedirect();
        $this->assertDatabaseMissing('faq_categories', ['id' => $cat->id]);
    }

    // ── FAQ Items ────────────────────────────────────────

    public function test_admin_can_create_faq(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $cat = $this->createCategory();

        $response = $this->post('/admin/faqs', [
            'faq_category_id' => $cat->id,
            'question' => ['tr' => 'Nasıl çalışır?', 'en' => 'How does it work?'],
            'answer' => ['tr' => 'Şöyle çalışır.', 'en' => 'It works like this.'],
            'sort_order' => 0,
            'is_active' => true,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseCount('faqs', 1);
    }

    public function test_admin_can_update_faq(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $cat = $this->createCategory();
        $faq = Faq::create([
            'faq_category_id' => $cat->id,
            'question' => ['tr' => 'Eski Soru', 'en' => 'Old Q'],
            'answer' => ['tr' => 'Eski Cevap', 'en' => 'Old A'],
            'sort_order' => 0,
            'is_active' => true,
        ]);

        $response = $this->put("/admin/faqs/{$faq->id}", [
            'faq_category_id' => $cat->id,
            'question' => ['tr' => 'Yeni Soru', 'en' => 'New Q'],
            'answer' => ['tr' => 'Yeni Cevap', 'en' => 'New A'],
            'sort_order' => 1,
        ]);

        $response->assertRedirect();
    }

    public function test_admin_can_delete_faq(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $cat = $this->createCategory();
        $faq = Faq::create([
            'faq_category_id' => $cat->id,
            'question' => ['tr' => 'Sil', 'en' => 'Del'],
            'answer' => ['tr' => 'C', 'en' => 'C'],
        ]);

        $response = $this->delete("/admin/faqs/{$faq->id}");
        $response->assertRedirect();
        $this->assertDatabaseMissing('faqs', ['id' => $faq->id]);
    }

    public function test_faq_validates_required_fields(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $response = $this->post('/admin/faqs', []);
        $response->assertSessionHasErrors(['faq_category_id', 'question', 'answer']);
    }
}
