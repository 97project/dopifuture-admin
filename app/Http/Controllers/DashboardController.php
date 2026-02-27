<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\License;
use App\Models\School;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DashboardController extends Controller
{

    /**
     * Role-based dashboard.
     */
    public function index()
    {
        $user = auth()->user();
        $data = ['user' => $user];

        // Super-admin / Admin / Moderator → system overview
        if ($user->hasAnyRole(['super-admin', 'admin', 'moderator', 'license-manager'])) {
            $data['totalSchools'] = School::count();
            $data['activeSchools'] = School::active()->count();
            $data['totalClasses'] = SchoolClass::count();
            $data['totalLicenses'] = License::count();
            $data['activeLicenses'] = License::active()->count();
            $data['totalApps'] = Application::active()->count();
            $data['totalUsers'] = \App\Models\User::count();
            $data['totalStudents'] = \App\Models\User::whereHas('roles', fn($q) => $q->where('name', 'student'))->count();
            $data['totalTeachers'] = \App\Models\User::whereHas('roles', fn($q) => $q->where('name', 'teacher'))->count();

            // App usage: per-application user count
            $data['appStats'] = Application::active()->ordered()
                ->withCount('users')
                ->get();

            // License utilization
            $data['licenseStats'] = License::with('school')
                ->where('is_active', true)
                ->orderByDesc('used_seats')
                ->take(10)
                ->get();

            // Recent users
            $data['recentUsers'] = \App\Models\User::latest()
                ->take(8)
                ->get();

            // School distribution
            $data['schoolDistribution'] = School::active()
                ->withCount(['users', 'classes', 'licenses'])
                ->orderByDesc('users_count')
                ->take(10)
                ->get();
        }

        // School-admin / Principal → school-scoped
        if ($user->hasAnyRole(['school-admin', 'school-principal'])) {
            $mySchools = $user->schools()
                ->withCount(['classes', 'users', 'licenses'])
                ->get();

            $schoolIds = $mySchools->pluck('id');

            $data['totalSchools'] = $mySchools->count();
            $data['activeSchools'] = $mySchools->where('is_active', true)->count();
            $data['totalClasses'] = SchoolClass::whereIn('school_id', $schoolIds)->count();
            $data['totalUsers'] = \DB::table('school_user')
                ->whereIn('school_id', $schoolIds)
                ->distinct('user_id')
                ->count('user_id');
            $data['totalStudents'] = \DB::table('school_user')
                ->whereIn('school_id', $schoolIds)
                ->where('role', 'student')
                ->count();
            $data['totalTeachers'] = \DB::table('school_user')
                ->whereIn('school_id', $schoolIds)
                ->where('role', 'teacher')
                ->count();
            $data['totalLicenses'] = License::whereIn('school_id', $schoolIds)->count();
            $data['activeLicenses'] = License::whereIn('school_id', $schoolIds)->active()->count();

            $data['appStats'] = Application::active()->ordered()
                ->withCount([
                    'users' => fn($q) => $q->whereIn(
                        'users.id',
                        \DB::table('school_user')->whereIn('school_id', $schoolIds)->pluck('user_id')
                    )
                ])
                ->get();

            $data['licenseStats'] = License::with('school')
                ->whereIn('school_id', $schoolIds)
                ->where('is_active', true)
                ->get();

            $data['schoolDistribution'] = $mySchools;

            $data['recentUsers'] = \App\Models\User::whereIn(
                'id',
                \DB::table('school_user')->whereIn('school_id', $schoolIds)->pluck('user_id')
            )
                ->latest()
                ->take(8)
                ->get();
        }

        // Teacher → classes they belong to
        if ($user->hasRole('teacher')) {
            $data['myClasses'] = $user->classes()
                ->with('school')
                ->withCount('students')
                ->get();
        }

        // Student → their class + applications
        if ($user->hasRole('student')) {
            $data['myClasses'] = $user->classes()->with('school')->get();
            $data['myApplications'] = $user->applications()->active()->ordered()->get();
        }

        return view('portal.dashboard', compact('data'));
    }


    /**
     * Profile page.
     */
    public function profile()
    {
        $user = auth()->user();
        $user->load(['schools', 'classes.school', 'applications']);

        return view('portal.profile', compact('user'));
    }

    /**
     * Update user profile.
     */
    public function profileUpdate(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string|max:60',
            'surname' => 'required|string|max:60',
            'phone' => 'nullable|string|max:30',
            'locale' => 'required|in:tr,en',
            'timezone' => 'nullable|string|max:50',
        ]);

        $user->update($validated);

        // Password change (optional)
        if ($request->filled('current_password')) {
            $request->validate([
                'current_password' => 'required',
                'new_password' => 'required|string|min:8|confirmed',
            ]);

            if (!Hash::check($request->input('current_password'), $user->password)) {
                return back()->withErrors(['current_password' => __('admin.current_password_incorrect')]);
            }

            $user->update(['password' => \Illuminate\Support\Facades\Hash::make($request->input('new_password'))]);
        }

        return back()->with('success', __('admin.saved'));
    }

    /**
     * Reports page — app usage, license utilization, per-school distribution.
     */
    public function reports()
    {
        $user = auth()->user();

        // Route-level role protection
        if (!$user->hasAnyRole(['super-admin', 'admin', 'moderator', 'school-admin', 'school-principal'])) {
            abort(403);
        }

        $data = ['user' => $user];

        // App usage stats (bar chart)
        $appQuery = Application::active()->ordered()->withCount('users');

        // Scope app users for school-admin
        if ($user->hasAnyRole(['school-admin', 'school-principal'])) {
            $schoolIds = $user->schools()->pluck('schools.id');
            $schoolUserIds = \DB::table('school_user')->whereIn('school_id', $schoolIds)->pluck('user_id');
            $appQuery = Application::active()->ordered()
                ->withCount(['users' => fn($q) => $q->whereIn('users.id', $schoolUserIds)]);
        }
        $data['appStats'] = $appQuery->get();

        // Per-application user details
        $appDetailsQuery = Application::active()->ordered()
            ->with([
                'users' => function ($q) use ($user) {
                    if ($user->hasAnyRole(['school-admin', 'school-principal'])) {
                        $schoolIds = $user->schools()->pluck('schools.id');
                        $schoolUserIds = \DB::table('school_user')->whereIn('school_id', $schoolIds)->pluck('user_id');
                        $q->whereIn('users.id', $schoolUserIds);
                    }
                    $q->select('users.id', 'users.name', 'users.surname', 'users.email', 'users.status');
                }
            ]);
        $data['appDetails'] = $appDetailsQuery->get();

        // License utilization
        if ($user->hasAnyRole(['super-admin', 'admin', 'license-manager'])) {
            $data['licenseStats'] = License::with('school')
                ->orderByDesc('used_seats')
                ->get();

            $data['schoolStats'] = School::active()
                ->withCount(['users', 'classes'])
                ->withCount(['users as teachers_count' => fn($q) => $q->whereHas('roles', fn($r) => $r->where('name', 'teacher'))])
                ->withCount(['users as students_count' => fn($q) => $q->whereHas('roles', fn($r) => $r->where('name', 'student'))])
                ->orderByDesc('users_count')
                ->get();
        }

        if ($user->hasAnyRole(['school-admin', 'school-principal'])) {
            $schoolIds = $user->schools()->pluck('schools.id');
            $data['licenseStats'] = License::with('school')
                ->whereIn('school_id', $schoolIds)
                ->get();

            $data['schoolStats'] = School::whereIn('id', $schoolIds)
                ->withCount(['users', 'classes'])
                ->withCount(['users as teachers_count' => fn($q) => $q->whereHas('roles', fn($r) => $r->where('name', 'teacher'))])
                ->withCount(['users as students_count' => fn($q) => $q->whereHas('roles', fn($r) => $r->where('name', 'student'))])
                ->get();
        }

        return view('portal.reports', $data);
    }
}

