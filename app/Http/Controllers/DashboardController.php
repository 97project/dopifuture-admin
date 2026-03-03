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
     * Lisans Yönetimi — Figma F-51 node-id: 1117-25324
     */
    public function index()
    {
        // Mock data matching Figma F-51 "Doping Admin - Lisans Yönetimi" exactly
        $data = [
            'user' => auth()->user(),
            'licenses' => [
                ['school' => 'Stuyvesant High School',     'location' => 'New York City/New York',       'total' => 4, 'status' => 'active',      'purchase_date' => '01/01/2026', 'duration' => '12/31/2026', 'email' => 'john.doe@example.com'],
                ['school' => 'Lincoln High School',         'location' => 'San Diego/California',         'total' => 3, 'status' => 'not_started', 'purchase_date' => '03/01/2026', 'duration' => '02/31/2027', 'email' => 'emily.smith@test.com'],
                ['school' => 'Beverly Hills High School',   'location' => 'Beverly Hills/California',     'total' => 4, 'status' => 'active',      'purchase_date' => '01/01/2026', 'duration' => '12/31/2026', 'email' => 'student01@example.com'],
                ['school' => 'Phillips Academy Andover',    'location' => 'Andover/Massachusetts',        'total' => 5, 'status' => 'cancelled',   'purchase_date' => '01/01/2026', 'duration' => '12/31/2026', 'email' => 'license.admin@sample...'],
                ['school' => 'Phillips Exeter Academy',     'location' => 'Exeter/New Hampshire',         'total' => 2, 'status' => 'active',      'purchase_date' => '03/16/2026', 'duration' => '12/31/2026', 'email' => 'info@demo-example.com'],
                ['school' => 'Miami Senior High School',    'location' => 'Miami/Florida',                'total' => 5, 'status' => 'active',      'purchase_date' => '01/01/2026', 'duration' => '12/31/2026', 'email' => 'name@example.com'],
                ['school' => 'Choate Rosemary Hall',        'location' => 'Wallingford/Connecticut',      'total' => 4, 'status' => 'not_started', 'purchase_date' => '04/01/2026', 'duration' => '03/31/2027', 'email' => 'olivia.johnson@example...'],
                ['school' => 'The Hotchkiss School',        'location' => 'Lakeville/Connecticut',        'total' => 1, 'status' => 'cancelled',   'purchase_date' => '01/01/2026', 'duration' => '12/31/2026', 'email' => 'mason.thomas@exampl...'],
                ['school' => 'Harvard-Westlake School',     'location' => 'Los Angeles/California',       'total' => 5, 'status' => 'active',      'purchase_date' => '01/01/2026', 'duration' => '12/31/2026', 'email' => 'ethan.jackson@example...'],
                ['school' => 'Deerfield Academy',           'location' => 'Deerfield/Massachusetts',      'total' => 4, 'status' => 'expired',     'purchase_date' => '01/01/2025', 'duration' => '12/31/2025', 'email' => 'mia.harris@example.com'],
                ['school' => 'Groton School',               'location' => 'Groton/Massachusetts',         'total' => 4, 'status' => 'active',      'purchase_date' => '01/01/2026', 'duration' => '12/31/2026', 'email' => 'james.clark@example...'],
            ],
        ];

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

