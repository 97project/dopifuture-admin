<?php

namespace Tests\Feature\DopiFuture;

use App\Models\RegistrationRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RegistrationRequestTest extends TestCase
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
        foreach (['registration_requests.view', 'registration_requests.edit', 'registration_requests.delete'] as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }
        $user = User::factory()->create(['password' => Hash::make('password'), 'status' => 'active']);
        $user->assignRole($role);
        return $user;
    }

    private function makeRequest(array $overrides = []): RegistrationRequest
    {
        return RegistrationRequest::create(array_merge([
            'school_name' => 'Test Okul',
            'country' => 'TR',
            'contact_name' => 'Ali',
            'contact_surname' => 'Yılmaz',
            'email' => 'test' . uniqid() . '@school.com',
            'phone' => '+905551234567',
            'status' => 'new',
        ], $overrides));
    }

    // ── Model Scopes ─────────────────────────────────────────

    public function test_status_scope_filters(): void
    {
        $this->makeRequest(['status' => 'new']);
        $this->makeRequest(['status' => 'approved']);
        $this->makeRequest(['status' => 'rejected']);

        $this->assertCount(1, RegistrationRequest::status('new')->get());
        $this->assertCount(1, RegistrationRequest::status('approved')->get());
    }

    public function test_new_scope(): void
    {
        $this->makeRequest(['status' => 'new']);
        $this->makeRequest(['status' => 'approved']);

        $this->assertCount(1, RegistrationRequest::new()->get());
    }

    // ── Public Form ──────────────────────────────────────────

    public function test_registration_form_renders(): void
    {
        $this->get('/register')->assertOk();
    }

    public function test_registration_form_submits_successfully(): void
    {
        $response = $this->post('/register', [
            'school_name' => 'New School',
            'country' => 'TR',
            'contact_name' => 'Mehmet',
            'contact_surname' => 'Demir',
            'email' => 'mehmet@school.com',
            'phone' => '+905559876543',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('registration_requests', [
            'email' => 'mehmet@school.com',
            'status' => 'new',
        ]);
    }

    public function test_registration_form_validation_requires_fields(): void
    {
        $response = $this->from('/register')->post('/register', []);

        // school_name, contact_name, contact_surname, email are required; country is nullable
        $response->assertSessionHasErrors(['school_name', 'contact_name', 'contact_surname', 'email']);
    }

    // ── API Registration ─────────────────────────────────────

    public function test_api_registration_creates_request(): void
    {
        $response = $this->postJson('/api/v1/register', [
            'school_name' => 'API School',
            'country' => 'TR',
            'contact_name' => 'API',
            'contact_surname' => 'Test',
            'email' => 'api@school.com',
            'phone' => '+905550000000',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('registration_requests', ['email' => 'api@school.com']);
    }

    public function test_api_registration_validates(): void
    {
        $response = $this->postJson('/api/v1/register', []);
        $response->assertUnprocessable();
    }

    // ── Admin Management ─────────────────────────────────────

    public function test_admin_can_list_registration_requests(): void
    {
        $admin = $this->createAdmin();
        $this->makeRequest();

        $this->actingAs($admin)->get('/admin/registration-requests')->assertOk();
    }

    public function test_admin_can_approve_request(): void
    {
        $admin = $this->createAdmin();
        $req = $this->makeRequest(['status' => 'new']);

        $response = $this->actingAs($admin)->put("/admin/registration-requests/{$req->id}", [
            'status' => 'approved',
            'admin_notes' => 'Onaylandı',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('registration_requests', ['id' => $req->id, 'status' => 'approved']);
    }

    public function test_admin_can_reject_request(): void
    {
        $admin = $this->createAdmin();
        $req = $this->makeRequest(['status' => 'new']);

        $response = $this->actingAs($admin)->put("/admin/registration-requests/{$req->id}", [
            'status' => 'rejected',
            'admin_notes' => 'Reddedildi',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('registration_requests', ['id' => $req->id, 'status' => 'rejected']);
    }

    public function test_admin_can_delete_request(): void
    {
        $admin = $this->createAdmin();
        $req = $this->makeRequest();

        $response = $this->actingAs($admin)->delete("/admin/registration-requests/{$req->id}");
        $response->assertRedirect();
        $this->assertDatabaseMissing('registration_requests', ['id' => $req->id]);
    }

    // ── Guest ────────────────────────────────────────────────

    public function test_guest_cannot_access_registration_admin(): void
    {
        $this->get('/admin/registration-requests')->assertRedirect();
    }
}
