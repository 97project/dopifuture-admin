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
     * Dashboard — role-specific views.
     */
    public function index()
    {
        $user = auth()->user();

        // ── Teacher Dashboard ──────────────────────────
        if ($user->hasRole('teacher')) {
            $classes = \App\Models\SchoolClass::whereIn('id', $user->classes()->pluck('school_classes.id'))
                ->with('school')
                ->withCount('students')
                ->get();

            // Last 5 students (from teacher's classes)
            $classIds = $user->classes()->pluck('school_classes.id');
            $recentStudents = \App\Models\User::whereHas('classes', fn($q) => $q->whereIn('school_classes.id', $classIds))
                ->role('student')
                ->orderByDesc('created_at')
                ->limit(5)
                ->get();

            return view('portal.dashboard-teacher', [
                'user' => $user,
                'classes' => $classes,
                'recentStudents' => $recentStudents,
                'mode' => 'teacher',
            ]);
        }

        // ── Student Dashboard ──────────────────────────
        if ($user->hasRole('student')) {
            $user->load(['applications', 'classes.school']);

            // App statistics
            $appStats = $user->applications->map(fn($app) => (object) [
                'name' => $app->name,
                'slug' => $app->slug,
                'icon' => $app->icon,
                'color' => $app->color,
                'sync_status' => $app->pivot->sync_status ?? 'pending',
            ]);

            return view('portal.dashboard-student', [
                'user' => $user,
                'appStats' => $appStats,
                'mode' => 'student',
            ]);
        }

        // ── School Admin / Principal ───────────────────
        $isSchoolRole = $user->hasAnyRole(['school-admin', 'school-principal']);

        if ($isSchoolRole) {
            $schoolIds = $user->schools()->pluck('schools.id');
            $school = School::whereIn('id', $schoolIds)
                ->withCount(['users', 'classes'])
                ->withCount(['users as students_count' => fn($q) => $q->whereHas('roles', fn($r) => $r->where('name', 'student'))])
                ->withCount(['users as teachers_count' => fn($q) => $q->whereHas('roles', fn($r) => $r->where('name', 'teacher'))])
                ->first();

            $license = License::where('school_id', $school?->id)
                ->with(['purchases' => fn($q) => $q->orderByDesc('purchased_at'), 'school'])
                ->first();

            // License warning
            $licenseWarning = null;
            if ($license && $license->is_active && $license->expires_at) {
                $daysLeft = now()->diffInDays($license->expires_at, false);
                if ($daysLeft <= 7 && $daysLeft >= 0) {
                    $licenseWarning = 'critical';
                } elseif ($daysLeft <= 30 && $daysLeft > 7) {
                    $licenseWarning = 'warning';
                }
            }

            // Recent students (last 5)
            $recentStudents = \App\Models\User::whereHas('schools', fn($q) => $q->whereIn('schools.id', $schoolIds))
                ->role('student')
                ->orderByDesc('created_at')
                ->limit(5)
                ->get();

            // Seat requests for this school
            $seatRequests = \App\Models\SeatRequest::where('school_id', $school?->id)
                ->orderByDesc('created_at')
                ->limit(10)
                ->get();

            // Vega app summary (Role Galaxy / Study Space / WAY AI Coach)
            $vegaService = app(\App\Services\VegaReportService::class);
            $schoolStudentIds = \App\Models\User::whereHas('schools', fn($q) => $q->whereIn('schools.id', $schoolIds))
                ->role('student')
                ->pluck('id');
            $vegaUserMap = $vegaService->resolveVegaUserIds($schoolStudentIds);
            $vegaSummary = $vegaService->getDashboardSummary(array_values($vegaUserMap));

            return view('portal.dashboard-school', [
                'user' => $user,
                'school' => $school,
                'license' => $license,
                'licenseWarning' => $licenseWarning,
                'recentStudents' => $recentStudents,
                'seatRequests' => $seatRequests,
                'vegaSummary' => $vegaSummary,
            ]);
        } else {
            // Super admin → license table
            $licenses = License::with('school')
                ->orderByDesc('created_at')
                ->paginate(15);

            $data = [
                'user'     => $user,
                'licenses' => $licenses,
                'mode'     => 'admin',
            ];
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

