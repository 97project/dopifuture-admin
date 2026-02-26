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
        $user = auth()->user();

        // Student can only see their own profile
        if ($user->hasRole('student')) {
            return redirect()->route('portal.users.show', $user);
        }

        $query = User::with('roles');

        // Scope by school
        if ($user->hasAnyRole(['school-admin', 'school-principal'])) {
            $schoolIds = $user->schools()->pluck('schools.id');
            $userIds = \DB::table('school_user')->whereIn('school_id', $schoolIds)->pluck('user_id');
            $query->whereIn('id', $userIds);
        }

        // Teacher → only students in their classes
        if ($user->hasRole('teacher')) {
            $classIds = $user->classes()->pluck('school_classes.id');
            $studentIds = \DB::table('class_student')->whereIn('school_class_id', $classIds)->pluck('user_id');
            $query->whereIn('id', $studentIds);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('surname', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%");
            });
        }

        if ($request->filled('role')) {
            $query->role($request->role);
        }

        $users = $query->latest()->paginate(20);
        $roles = $this->getAllowedRoles();
        return view('portal.users.index', compact('users', 'roles'));
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
            $studentIds = \DB::table('class_student')->whereIn('school_class_id', $classIds)->pluck('user_id');
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

    /* ── Guards ─────────────────────────────────────── */

    /**
     * Only school-admin and school-principal can manage users.
     * Teacher and student → 403.
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

    /* ── Helpers ─────────────────────────────────────── */

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
