<?php

namespace App\Http\Controllers;

use App\Jobs\SyncUserToAppsJob;
use App\Models\License;
use App\Models\User;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class PortalUserController extends Controller
{
    public function index(Request $request)
    {
        // Mock data matching Figma frame 1158-14034
        $currentRole = $request->get('role', 'student');

        $mockStudents = collect([
            (object)['id'=>1, 'name'=>'Allison',      'surname'=>'Gouse',    'email'=>'emily.smith@test.com',       'grade'=>'4', 'branch'=>null],
            (object)['id'=>2, 'name'=>'John',          'surname'=>'Doe',      'email'=>'john.doe@example.com',       'grade'=>'3', 'branch'=>null],
            (object)['id'=>3, 'name'=>'Emerson',       'surname'=>'Rosser',   'email'=>'student01@example.com',      'grade'=>'4', 'branch'=>null],
            (object)['id'=>4, 'name'=>'Maren',         'surname'=>'Dokidis',  'email'=>'license.admin@sample...',    'grade'=>'5', 'branch'=>null],
            (object)['id'=>5, 'name'=>'Cristofer',     'surname'=>'Curtis',   'email'=>'info@demo-example.com',      'grade'=>'2', 'branch'=>null],
            (object)['id'=>6, 'name'=>'Chance Rhiel',  'surname'=>'Madsen',   'email'=>'name@example.com',           'grade'=>'5', 'branch'=>null],
            (object)['id'=>7, 'name'=>'Corey',         'surname'=>'Bergson',  'email'=>'olivia.johnson@example...',  'grade'=>'4', 'branch'=>null],
            (object)['id'=>8, 'name'=>'Anika',         'surname'=>'Mango',    'email'=>'mason.thomas@exampl...',     'grade'=>'1', 'branch'=>null],
            (object)['id'=>9, 'name'=>'Kadin',         'surname'=>'Septimus', 'email'=>'ethan.jackson@example..',    'grade'=>'5', 'branch'=>null],
        ]);

        $mockTeachers = collect([
            (object)['id'=>101, 'name'=>'Sarah',   'surname'=>'Johnson',  'email'=>'sarah.j@school.com',   'grade'=>null, 'branch'=>'Mathematics'],
            (object)['id'=>102, 'name'=>'Michael', 'surname'=>'Chen',     'email'=>'m.chen@school.com',    'grade'=>null, 'branch'=>'Science'],
            (object)['id'=>103, 'name'=>'Emily',   'surname'=>'Davis',    'email'=>'e.davis@school.com',   'grade'=>null, 'branch'=>'English'],
            (object)['id'=>104, 'name'=>'Robert',  'surname'=>'Wilson',   'email'=>'r.wilson@school.com',  'grade'=>null, 'branch'=>'History'],
            (object)['id'=>105, 'name'=>'Jessica', 'surname'=>'Brown',    'email'=>'j.brown@school.com',   'grade'=>null, 'branch'=>'Art'],
        ]);

        $items = $currentRole === 'teacher' ? $mockTeachers : $mockStudents;
        $total = $currentRole === 'teacher' ? 24 : 47;

        $page = $request->get('page', 1);
        $users = new \Illuminate\Pagination\LengthAwarePaginator(
            $items->forPage($page, 10),
            $total,
            10,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // License stats for stat cards (from Figma)
        $licenseStats = (object)[
            'totalLicence' => 52,
            'usedLicence' => 47,
            'licenceDuration' => '12/31/2026',
        ];

        $roles = [];
        return view('portal.users.index', compact('users', 'roles', 'licenseStats'));
    }

    public function show(User $user)
    {
        $authUser = auth()->user();

        // Student can only see themselves
        if ($authUser->hasRole('student') && $authUser->id !== $user->id) {
            abort(403);
        }

        // Teacher can only see their own students
        if ($authUser->hasRole('teacher')) {
            $classIds = $authUser->classes()->pluck('school_classes.id');
            $studentIds = \DB::table('class_user')->whereIn('class_id', $classIds)->pluck('user_id');
            if (!$studentIds->contains($user->id) && $authUser->id !== $user->id) {
                abort(403);
            }
        }

        $user->load(['roles', 'schools', 'classes.school', 'applications']);

        // Load per-app report data
        $reportService = app(\App\Services\ReportService::class);
        $reportData = $reportService->getStudentReport($user);

        return view('portal.users.show', compact('user', 'reportData'));
    }

    public function create()
    {
        $this->guardManageRoles();
        $roles = $this->getAllowedRoles();
        $schools = $this->getAvailableSchools();
        return view('portal.users.form', ['editUser' => new User, 'roles' => $roles, 'schools' => $schools]);
    }

    public function store(Request $request)
    {
        $this->guardManageRoles();

        $data = $request->validate([
            'name' => 'required|string|max:60',
            'surname' => 'nullable|string|max:60',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|string|exists:roles,name',
            'school_id' => 'nullable|exists:schools,id',
        ]);

        // License seat check for student role
        if ($data['role'] === 'student' && !empty($data['school_id'])) {
            $license = License::where('school_id', $data['school_id'])
                ->where('is_active', true)
                ->first();

            if ($license && $license->used_seats >= $license->seat_count) {
                return back()
                    ->withInput()
                    ->withErrors(['role' => __('admin.seat_limit_reached')]);
            }
        }

        $newUser = User::create([
            'name' => $data['name'],
            'surname' => $data['surname'] ?? null,
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'status' => 'active',
            'locale' => 'tr',
            'timezone' => 'Europe/Istanbul',
            'email_verified_at' => now(),
        ]);

        $newUser->assignRole($data['role']);

        // Attach to school
        if (!empty($data['school_id'])) {
            \DB::table('school_user')->insertOrIgnore([
                'school_id' => $data['school_id'],
                'user_id' => $newUser->id,
                'role' => $data['role'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Increment used_seats if student
            if ($data['role'] === 'student') {
                License::where('school_id', $data['school_id'])
                    ->where('is_active', true)
                    ->increment('used_seats');
            }
        }

        // Auto-sync to connector applications
        SyncUserToAppsJob::dispatch($newUser);

        return redirect()->route('portal.users.index')
            ->with('success', __('admin.user_created'));
    }

    public function edit(User $user)
    {
        $this->guardManageRoles();
        $this->guardPrincipalCannotEditAdmin($user);

        $roles = $this->getAllowedRoles();
        $schools = $this->getAvailableSchools();
        return view('portal.users.form', ['editUser' => $user, 'roles' => $roles, 'schools' => $schools]);
    }

    public function update(Request $request, User $user)
    {
        $this->guardManageRoles();
        $this->guardPrincipalCannotEditAdmin($user);

        $data = $request->validate([
            'name' => 'required|string|max:60',
            'surname' => 'nullable|string|max:60',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6',
            'role' => 'required|string|exists:roles,name',
            'status' => 'required|in:active,inactive',
        ]);

        $user->update([
            'name' => $data['name'],
            'surname' => $data['surname'] ?? $user->surname,
            'email' => $data['email'],
            'status' => $data['status'],
        ]);

        if (!empty($data['password'])) {
            $user->update(['password' => Hash::make($data['password'])]);
        }

        $user->syncRoles([$data['role']]);

        return redirect()->route('portal.users.index')
            ->with('success', __('admin.user_updated'));
    }

    public function destroy(User $user)
    {
        $this->guardManageRoles();
        $this->guardPrincipalCannotEditAdmin($user);

        $user->delete();
        return redirect()->route('portal.users.index')
            ->with('success', __('admin.user_deleted'));
    }

    /* -- Guards -------------------------------------------------- */

    /**
     * Only school-admin and school-principal can manage users.
     * Teacher and student -> 403.
     */
    private function guardManageRoles(): void
    {
        if (!auth()->user()->hasAnyRole(['school-admin', 'school-principal'])) {
            abort(403);
        }
    }

    /**
     * Principal cannot edit/delete school-admin users.
     */
    private function guardPrincipalCannotEditAdmin(User $target): void
    {
        $user = auth()->user();
        if ($user->hasRole('school-principal') && $target->hasRole('school-admin')) {
            abort(403, __('auth.insufficient_permissions'));
        }
    }

    /* -- Helpers -------------------------------------------------- */

    private function getAllowedRoles(): \Illuminate\Support\Collection
    {
        $user = auth()->user();
        if ($user->hasRole('school-admin')) {
            return collect(['teacher', 'student', 'school-principal']);
        }
        if ($user->hasRole('school-principal')) {
            return collect(['teacher', 'student']);
        }
        return collect([]);
    }

    private function getAvailableSchools()
    {
        return auth()->user()->schools()->get();
    }
}
