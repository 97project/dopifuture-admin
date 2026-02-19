<?php

namespace Tests\Feature\Cms;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PostTest extends TestCase
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

    public function test_admin_can_list_posts(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $response = $this->get('/admin/posts');
        $response->assertOk();
    }

    public function test_admin_can_view_create_post_form(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $response = $this->get('/admin/posts/create');
        $response->assertOk();
    }

    public function test_admin_can_create_post(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $category = Category::create([
            'name' => ['tr' => 'Genel', 'en' => 'General'],
            'slug' => 'genel',
            'type' => 'post',
            'is_active' => true,
        ]);

        $response = $this->post('/admin/posts', [
            'title' => ['tr' => 'Yeni Yazı', 'en' => 'New Post'],
            'content' => ['tr' => 'İçerik', 'en' => 'Content'],
            'status' => 'draft',
            'categories' => [$category->id],
            'tags' => ['laravel', 'php'],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('posts', ['slug' => 'yeni-yazi']);
    }

    public function test_admin_can_update_post(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $post = Post::create([
            'title' => ['tr' => 'Eski Yazı', 'en' => 'Old Post'],
            'slug' => 'eski-yazi',
            'status' => 'draft',
            'author_id' => $admin->id,
        ]);

        $response = $this->put("/admin/posts/{$post->id}", [
            'title' => ['tr' => 'Güncel Yazı', 'en' => 'Updated Post'],
            'status' => 'published',
        ]);

        $response->assertRedirect();
        $post->refresh();
        $this->assertEquals('published', $post->status);
    }

    public function test_admin_can_delete_post(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $post = Post::create([
            'title' => ['tr' => 'Silinecek', 'en' => 'Delete Me'],
            'slug' => 'silinecek',
            'status' => 'draft',
            'author_id' => $admin->id,
        ]);

        $response = $this->delete("/admin/posts/{$post->id}");
        $response->assertRedirect();
        $this->assertSoftDeleted('posts', ['id' => $post->id]);
    }

    public function test_post_store_validates_required_fields(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $response = $this->post('/admin/posts', []);
        $response->assertSessionHasErrors(['title', 'status']);
    }

    public function test_post_category_filter_works(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $category = Category::create([
            'name' => ['tr' => 'Filtre', 'en' => 'Filter'],
            'slug' => 'filtre',
            'type' => 'post',
            'is_active' => true,
        ]);

        $response = $this->get("/admin/posts?category={$category->id}");
        $response->assertOk();
    }

    public function test_tags_sync_on_create(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $this->post('/admin/posts', [
            'title' => ['tr' => 'Tag Test', 'en' => 'Tag Test'],
            'status' => 'draft',
            'tags' => ['laravel', 'php'],
        ]);

        $this->assertDatabaseHas('tags', ['slug' => 'laravel']);
        $this->assertDatabaseHas('tags', ['slug' => 'php']);
    }
}
