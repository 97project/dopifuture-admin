<?php

namespace Tests\Feature\Cms;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CategoryTest extends TestCase
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

    public function test_admin_can_list_categories(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $response = $this->get('/admin/categories');
        $response->assertOk();
    }

    public function test_admin_can_list_categories_by_type(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $response = $this->get('/admin/categories?type=page');
        $response->assertOk();
    }

    public function test_admin_can_create_category(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $response = $this->post('/admin/categories', [
            'name' => ['tr' => 'Teknoloji', 'en' => 'Technology'],
            'type' => 'post',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('categories', ['slug' => 'teknoloji']);
    }

    public function test_admin_can_update_category(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $category = Category::create([
            'name' => ['tr' => 'Eski', 'en' => 'Old'],
            'slug' => 'eski',
            'type' => 'post',
            'is_active' => true,
        ]);

        $response = $this->put("/admin/categories/{$category->id}", [
            'name' => ['tr' => 'Güncel', 'en' => 'Updated'],
            'sort_order' => 5,
        ]);

        $response->assertRedirect();
        $category->refresh();
        $this->assertEquals(5, $category->sort_order);
    }

    public function test_admin_can_delete_category(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $category = Category::create([
            'name' => ['tr' => 'Sil', 'en' => 'Delete'],
            'slug' => 'sil',
            'type' => 'post',
        ]);

        $response = $this->delete("/admin/categories/{$category->id}");
        $response->assertRedirect();
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_category_validates_required_fields(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $response = $this->post('/admin/categories', []);
        $response->assertSessionHasErrors(['name', 'type']);
    }

    public function test_nested_category_parent_child(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $parent = Category::create([
            'name' => ['tr' => 'Üst', 'en' => 'Parent'],
            'slug' => 'ust',
            'type' => 'post',
        ]);

        $response = $this->post('/admin/categories', [
            'name' => ['tr' => 'Alt', 'en' => 'Child'],
            'type' => 'post',
            'parent_id' => $parent->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('categories', ['parent_id' => $parent->id]);
    }
}
