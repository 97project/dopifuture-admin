<?php

namespace Tests\Feature\Cms;

use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PageTest extends TestCase
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

    public function test_admin_can_list_pages(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        Page::create([
            'title' => ['tr' => 'Test Sayfa', 'en' => 'Test Page'],
            'slug' => 'test-page',
            'content' => ['tr' => 'İçerik', 'en' => 'Content'],
            'status' => 'published',
            'author_id' => $admin->id,
        ]);

        $response = $this->get('/admin/pages');
        $response->assertOk();
        $response->assertSee('Test');
    }

    public function test_admin_can_view_create_page_form(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $response = $this->get('/admin/pages/create');
        $response->assertOk();
    }

    public function test_admin_can_create_page(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $response = $this->post('/admin/pages', [
            'title' => ['tr' => 'Yeni Sayfa', 'en' => 'New Page'],
            'content' => ['tr' => 'İçerik', 'en' => 'Content'],
            'status' => 'draft',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('pages', ['slug' => 'yeni-sayfa']);
    }

    public function test_admin_can_edit_page(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $page = Page::create([
            'title' => ['tr' => 'Düzenle', 'en' => 'Edit'],
            'slug' => 'duzenle',
            'status' => 'draft',
            'author_id' => $admin->id,
        ]);

        $response = $this->get("/admin/pages/{$page->id}/edit");
        $response->assertOk();
    }

    public function test_admin_can_update_page(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $page = Page::create([
            'title' => ['tr' => 'Eski', 'en' => 'Old'],
            'slug' => 'eski',
            'status' => 'draft',
            'author_id' => $admin->id,
        ]);

        $response = $this->put("/admin/pages/{$page->id}", [
            'title' => ['tr' => 'Güncel', 'en' => 'Updated'],
            'status' => 'published',
        ]);

        $response->assertRedirect();
        $page->refresh();
        $this->assertEquals('published', $page->status);
    }

    public function test_admin_can_delete_page(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $page = Page::create([
            'title' => ['tr' => 'Silinecek', 'en' => 'To Delete'],
            'slug' => 'silinecek',
            'status' => 'draft',
            'author_id' => $admin->id,
        ]);

        $response = $this->delete("/admin/pages/{$page->id}");
        $response->assertRedirect();
        $this->assertSoftDeleted('pages', ['id' => $page->id]);
    }

    public function test_page_store_validates_required_fields(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $response = $this->post('/admin/pages', []);
        $response->assertSessionHasErrors(['title', 'status']);
    }

    public function test_page_search_filter_works(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        Page::create([
            'title' => ['tr' => 'Aranacak', 'en' => 'Searchable'],
            'slug' => 'aranacak',
            'status' => 'published',
            'author_id' => $admin->id,
        ]);

        $response = $this->get('/admin/pages?search=Aranacak');
        $response->assertOk();
    }

    public function test_page_status_filter_works(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $response = $this->get('/admin/pages?status=published');
        $response->assertOk();
    }
}
