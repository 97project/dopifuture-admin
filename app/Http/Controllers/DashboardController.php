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
        $user = auth()->user();

        // Rol bazlı lisans filtreleme
        $licensesQuery = License::with('school')->orderByDesc('created_at');

        if ($user->hasAnyRole(['school-admin', 'school-principal'])) {
            $schoolIds = $user->schools()->pluck('schools.id');
            $licensesQuery->whereIn('school_id', $schoolIds);
        }

        $data = [
            'user' => $user,
            'licenses' => $licensesQuery->get(),
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

