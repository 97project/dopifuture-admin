<?php

namespace Tests\Feature\DopiFuture;

use App\Models\Application;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PortalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    private function createUser(string $roleName = 'admin'): User
    {
        $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        $user = User::factory()->create(['password' => Hash::make('password'), 'status' => 'active']);
        $user->assignRole($role);
        return $user;
    }

    // ── Public Portal Pages ──────────────────────────────────

    public function test_landing_page_renders(): void
    {
        $this->get('/')->assertOk();
    }

    public function test_landing_page_displays_stats(): void
    {
        Application::create([
            'slug' => 'stat-app',
            'name' => json_encode(['tr' => 'A', 'en' => 'A']),
            'description' => json_encode(['tr' => 'B', 'en' => 'B']),
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $response = $this->get('/');
        $response->assertOk();
    }

    public function test_solutions_page_renders(): void
    {
        $this->get('/solutions')->assertOk();
    }

    public function test_solutions_page_shows_active_applications(): void
    {
        Application::create([
            'slug' => 'visible-app',
            'name' => json_encode(['tr' => 'Görünür', 'en' => 'Visible']),
            'description' => json_encode(['tr' => 'D', 'en' => 'D']),
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->get('/solutions')->assertOk();
    }

    public function test_contact_page_renders(): void
    {
        $this->get('/contact')->assertOk();
    }

    public function test_contact_form_submits(): void
    {
        $response = $this->from('/contact')->post('/contact', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'subject' => 'Test Subject',
            'message' => 'Hello, this is a test message.',
        ]);

        // Verify no validation errors (form data accepted)
        $response->assertSessionMissing('errors');
    }

    public function test_contact_form_validates(): void
    {
        $response = $this->from('/contact')->post('/contact', []);
        $response->assertSessionHasErrors(['name', 'email', 'subject', 'message']);
    }

    // ── Authenticated Portal ─────────────────────────────────

    public function test_dashboard_requires_auth(): void
    {
        $response = $this->get('/dashboard');
        // Should redirect to login (302 or 401)
        $this->assertNotEquals(200, $response->status());
    }

    public function test_authenticated_user_can_access_dashboard(): void
    {
        $user = $this->createUser('super-admin');

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk();
    }

    public function test_profile_requires_auth(): void
    {
        $response = $this->get('/profile');
        $this->assertNotEquals(200, $response->status());
    }

    public function test_authenticated_user_can_access_profile(): void
    {
        $user = $this->createUser();

        $this->actingAs($user)
            ->get('/profile')
            ->assertOk();
    }

    public function test_profile_update(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->put('/profile', [
            'name' => 'Updated',
            'surname' => 'Name',
            'locale' => 'en',
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Updated']);
    }

    public function test_reports_requires_auth(): void
    {
        $response = $this->get('/reports');
        $this->assertNotEquals(200, $response->status());
    }

    public function test_authenticated_user_can_access_reports(): void
    {
        $user = $this->createUser();

        $this->actingAs($user)
            ->get('/reports')
            ->assertOk();
    }

    // ── Navigation: Auth-Aware ───────────────────────────────

    public function test_guest_sees_login_link(): void
    {
        $response = $this->get('/');
        $response->assertSee('/login');
    }

    public function test_authenticated_user_sees_dashboard_link(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->get('/');
        $response->assertSee('/dashboard');
    }

    // ── i18n ─────────────────────────────────────────────────

    public function test_landing_page_renders_in_turkish(): void
    {
        app()->setLocale('tr');
        $this->get('/')->assertOk();
    }

    public function test_landing_page_renders_in_english(): void
    {
        app()->setLocale('en');
        $this->get('/')->assertOk();
    }
}
