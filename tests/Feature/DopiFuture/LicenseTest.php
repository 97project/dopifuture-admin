<?php

namespace Tests\Feature\DopiFuture;

use App\Models\License;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LicenseTest extends TestCase
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
        foreach (['licenses.view', 'licenses.create', 'licenses.edit', 'licenses.delete'] as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }
        $user = User::factory()->create(['password' => Hash::make('password'), 'status' => 'active']);
        $user->assignRole($role);
        return $user;
    }

    private function makeSchool(): School
    {
        return School::create([
            'name' => json_encode(['tr' => 'Okul', 'en' => 'School']),
            'country' => 'TR',
            'city' => 'Istanbul',
            'email' => 'school' . uniqid() . '@test.com',
            'is_active' => true,
        ]);
    }

    private function makeLicense(array $overrides = []): License
    {
        $school = $overrides['school_id'] ?? $this->makeSchool()->id;
        return License::create(array_merge([
            'school_id' => $school,
            'seat_count' => 50,
            'used_seats' => 10,
            'starts_at' => now()->subMonth(),
            'expires_at' => now()->addYear(),
            'is_active' => true,
        ], $overrides));
    }

    // ── Model Business Logic ─────────────────────────────────

    public function test_license_available_seats(): void
    {
        $license = $this->makeLicense(['seat_count' => 100, 'used_seats' => 25]);

        $this->assertEquals(75, $license->availableSeats());
        $this->assertTrue($license->hasAvailableSeats());
    }

    public function test_license_no_available_seats(): void
    {
        $license = $this->makeLicense(['seat_count' => 10, 'used_seats' => 10]);

        $this->assertEquals(0, $license->availableSeats());
        $this->assertFalse($license->hasAvailableSeats());
    }

    public function test_license_is_expired(): void
    {
        $expired = $this->makeLicense(['expires_at' => now()->subDay()]);
        $valid = $this->makeLicense(['expires_at' => now()->addDay()]);

        $this->assertTrue($expired->isExpired());
        $this->assertFalse($valid->isExpired());
    }

    public function test_license_null_expiry_is_not_expired(): void
    {
        $license = $this->makeLicense(['expires_at' => null]);

        $this->assertFalse($license->isExpired());
    }

    public function test_license_active_scope(): void
    {
        $this->makeLicense(['is_active' => true, 'expires_at' => now()->addYear()]);
        $this->makeLicense(['is_active' => false]);
        $this->makeLicense(['is_active' => true, 'expires_at' => now()->subDay()]);

        $this->assertCount(1, License::active()->get());
    }

    public function test_license_belongs_to_school(): void
    {
        $school = $this->makeSchool();
        $license = $this->makeLicense(['school_id' => $school->id]);

        $this->assertEquals($school->id, $license->school->id);
    }

    // ── Admin CRUD ───────────────────────────────────────────

    public function test_admin_can_list_licenses(): void
    {
        $admin = $this->createAdmin();
        $this->makeLicense();

        $this->actingAs($admin)->get('/admin/licenses')->assertOk();
    }

    public function test_admin_can_create_license(): void
    {
        $admin = $this->createAdmin();
        $school = $this->makeSchool();

        $response = $this->actingAs($admin)->post('/admin/licenses', [
            'school_id' => $school->id,
            'seat_count' => 100,
            'used_seats' => 0,
            'starts_at' => now()->toDateString(),
            'expires_at' => now()->addYear()->toDateString(),
            'is_active' => true,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('licenses', ['school_id' => $school->id, 'seat_count' => 100]);
    }

    public function test_admin_can_delete_license(): void
    {
        $admin = $this->createAdmin();
        $license = $this->makeLicense();

        $response = $this->actingAs($admin)->delete("/admin/licenses/{$license->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('licenses', ['id' => $license->id]);
    }

    // ── API ──────────────────────────────────────────────────

    public function test_api_lists_licenses_with_auth(): void
    {
        $admin = $this->createAdmin();
        $token = $admin->createToken('test')->plainTextToken;
        $this->makeLicense();

        $response = $this->getJson('/api/v1/licenses', [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertOk();
    }

    public function test_api_licenses_requires_authentication(): void
    {
        $this->getJson('/api/v1/licenses')->assertUnauthorized();
    }

    // ── Guest ────────────────────────────────────────────────

    public function test_guest_cannot_access_licenses_admin(): void
    {
        $this->get('/admin/licenses')->assertRedirect();
    }
}
