<?php

namespace Tests\Feature\Cms;

use App\Models\Menu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MenuTest extends TestCase
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

    public function test_admin_can_list_menus(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $response = $this->get('/admin/menus');
        $response->assertOk();
    }

    public function test_admin_can_create_menu(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $response = $this->post('/admin/menus', [
            'name' => 'Ana Menü',
            'location' => 'header',
            'is_active' => true,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('menus', ['name' => 'Ana Menü']);
    }

    public function test_admin_can_update_menu(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $menu = Menu::create([
            'name' => 'Eski Menü',
            'location' => 'header',
            'is_active' => true,
        ]);

        $response = $this->put("/admin/menus/{$menu->id}", [
            'name' => 'Güncel Menü',
            'location' => 'footer',
            'is_active' => true,
        ]);

        $response->assertRedirect();
        $menu->refresh();
        $this->assertEquals('footer', $menu->location);
    }

    public function test_admin_can_delete_menu(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $menu = Menu::create([
            'name' => 'Silinecek',
            'location' => 'header',
        ]);

        $response = $this->delete("/admin/menus/{$menu->id}");
        $response->assertRedirect();
        $this->assertDatabaseMissing('menus', ['id' => $menu->id]);
    }

    public function test_menu_validates_required_fields(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $response = $this->post('/admin/menus', []);
        $response->assertSessionHasErrors(['name']);
    }
}
