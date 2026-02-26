<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Application;
use App\Models\License;
use App\Models\RegistrationRequest;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'users' => User::count(),
            'active_users' => User::where('status', 'active')->count(),
            'roles' => Role::count(),
            'recent_logs' => ActivityLog::where('created_at', '>=', now()->subDay())->count(),
            'schools' => School::count(),
            'classes' => SchoolClass::count(),
            'licenses' => License::active()->count(),
            'applications' => Application::active()->count(),
            'pending_requests' => RegistrationRequest::whereIn('status', ['pending', 'new'])->count(),
        ];

        // License utilization per school
        $licenseUtilization = License::with('school')
            ->active()
            ->get()
            ->map(function ($license) {
                $total = $license->totalSeats();
                $used = $license->used_seats;
                return [
                    'school' => $license->school->name ?? '—',
                    'used' => $used,
                    'total' => $total,
                    'percent' => $total > 0 ? round(($used / $total) * 100) : 0,
                    'remaining' => $total - $used,
                    'end_date' => $license->expires_at,
                ];
            })
            ->sortByDesc('percent')
            ->take(10)
            ->values();

        // Recent activity
        $recentActivity = ActivityLog::with('actor')
            ->latest()
            ->take(10)
            ->get();

        // Pending registration requests
        $pendingRequests = RegistrationRequest::whereIn('status', ['pending', 'new'])
            ->latest()
            ->take(5)
            ->get();

        // App usage stats with sync details
        $appStats = Application::active()
            ->withCount([
                'users',
                'users as synced_count' => fn($q) => $q->where('application_user.sync_status', 'synced'),
                'users as failed_count' => fn($q) => $q->where('application_user.sync_status', 'failed'),
                'users as pending_count' => fn($q) => $q->where('application_user.sync_status', 'pending'),
            ])
            ->orderByDesc('users_count')
            ->take(6)
            ->get();

        return view('admin.dashboard', compact(
            'stats',
            'licenseUtilization',
            'recentActivity',
            'pendingRequests',
            'appStats'
        ));
    }

    public function switchLocale(Request $request)
    {
        $locale = $request->input('locale', 'tr');
        $validLocales = \App\Models\Language::getActiveCodes();

        if (in_array($locale, $validLocales)) {
            session(['locale' => $locale]);
            if (auth()->check()) {
                auth()->user()->update(['locale' => $locale]);
            }
        }

        return back();
    }

    public function toggleDarkMode(Request $request)
    {
        if (auth()->check()) {
            auth()->user()->update(['dark_mode' => !auth()->user()->dark_mode]);
        }

        return back();
    }
}
