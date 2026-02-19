<?php

namespace Tests\Feature\Cms;

use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MediaTest extends TestCase
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

    public function test_admin_can_view_media_index(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $response = $this->get('/admin/media');
        $response->assertOk();
    }

    public function test_admin_can_upload_file(): void
    {
        Storage::fake('public');
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $response = $this->post('/admin/media/upload', [
            'files' => [UploadedFile::fake()->image('test-image.jpg', 800, 600)],
            'folder' => '/',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseCount('media', 1);
    }

    public function test_admin_can_delete_media(): void
    {
        Storage::fake('public');
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $media = Media::create([
            'name' => 'test.jpg',
            'path' => 'uploads/test.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 1024,
            'disk' => 'public',
            'uploaded_by' => $admin->id,
        ]);

        $response = $this->delete("/admin/media/{$media->id}");
        $response->assertRedirect();
        $this->assertDatabaseMissing('media', ['id' => $media->id]);
    }

    public function test_media_folder_filter_works(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $response = $this->get('/admin/media?folder=images');
        $response->assertOk();
    }
}
