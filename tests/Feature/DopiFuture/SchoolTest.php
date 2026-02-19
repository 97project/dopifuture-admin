<?php

namespace Tests\Feature\DopiFuture;

use App\Models\School;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SchoolTest extends TestCase
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
        foreach (['schools.view', 'schools.create', 'schools.edit', 'schools.delete'] as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }
        $user = User::factory()->create(['password' => Hash::make('password'), 'status' => 'active']);
        $user->assignRole($role);
        return $user;
    }

    private function makeSchool(array $overrides = []): School
    {
        return School::create(array_merge([
            'name' => json_encode(['tr' => 'Test Okul', 'en' => 'Test School']),
            'country' => 'TR',
            'city' => 'Istanbul',
            'email' => 'school' . uniqid() . '@test.com',
            'is_active' => true,
        ], $overrides));
    }

    // ── Model ────────────────────────────────────────────────

    public function test_school_active_scope(): void
    {
        $this->makeSchool(['is_active' => true]);
        $this->makeSchool(['is_active' => false]);

        $this->assertCount(1, School::active()->get());
    }

    public function test_school_has_classes_relationship(): void
    {
        $school = $this->makeSchool();
        SchoolClass::create([
            'school_id' => $school->id,
            'name' => '10-A',
            'grade_level' => '10',
        ]);

        $this->assertCount(1, $school->classes);
    }

    public function test_school_has_users_relationship(): void
    {
        $school = $this->makeSchool();
        $user = User::factory()->create(['status' => 'active']);
        $school->users()->attach($user->id, ['role' => 'teacher']);

        $this->assertCount(1, $school->users);
        $this->assertCount(1, $school->teachers);
    }

    public function test_school_has_licenses(): void
    {
        $school = $this->makeSchool();
        \App\Models\License::create([
            'school_id' => $school->id,
            'seat_count' => 50,
            'used_seats' => 0,
            'starts_at' => now(),
            'expires_at' => now()->addYear(),
            'is_active' => true,
        ]);

        $this->assertCount(1, $school->licenses);
    }

    // ── Admin CRUD ───────────────────────────────────────────

    public function test_admin_can_list_schools(): void
    {
        $admin = $this->createAdmin();
        $this->makeSchool();

        $this->actingAs($admin)->get('/admin/schools')->assertOk();
    }

    public function test_admin_can_create_school(): void
    {
        $admin = $this->createAdmin();
        $count = School::count();

        $response = $this->actingAs($admin)->post('/admin/schools', [
            'name_tr' => 'Yeni Okul',
            'name_en' => 'New School',
            'country' => 'TR',
            'city' => 'Ankara',
            'email' => 'new@school.com',
            'is_active' => true,
        ]);

        $response->assertRedirect();
        $this->assertEquals($count + 1, School::count());
    }

    public function test_admin_can_update_school(): void
    {
        $admin = $this->createAdmin();
        $school = $this->makeSchool(['email' => 'old@school.com']);

        $response = $this->actingAs($admin)->put("/admin/schools/{$school->id}", [
            'name_tr' => 'Düzenlendi',
            'name_en' => 'Edited',
            'country' => 'TR',
            'city' => 'Izmir',
            'email' => 'updated@school.com',
            'is_active' => true,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('schools', ['email' => 'updated@school.com']);
    }

    public function test_admin_can_delete_school(): void
    {
        $admin = $this->createAdmin();
        $school = $this->makeSchool(['email' => 'delete@school.com']);

        $response = $this->actingAs($admin)->delete("/admin/schools/{$school->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('schools', ['email' => 'delete@school.com']);
    }

    // ── API ──────────────────────────────────────────────────

    public function test_api_lists_schools_with_auth(): void
    {
        $admin = $this->createAdmin();
        $token = $admin->createToken('test')->plainTextToken;
        $this->makeSchool();

        $response = $this->getJson('/api/v1/schools', [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertOk();
    }

    public function test_api_schools_requires_authentication(): void
    {
        $this->getJson('/api/v1/schools')->assertUnauthorized();
    }

    // ── Guest ────────────────────────────────────────────────

    public function test_guest_cannot_access_schools_admin(): void
    {
        $this->get('/admin/schools')->assertRedirect();
    }
}
