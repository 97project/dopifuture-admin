<?php

namespace App\Http\Controllers;

use App\Models\Application;
use Illuminate\Http\Request;

class PortalApplicationController extends Controller
{
    /**
     * Portal Application Status — Read-only overview.
     * Okul admini hangi uygulamaların aktif/sync durumda olduğunu görür.
     */
    public function index()
    {
        $user = auth()->user();
        $schoolIds = $user->schools()->pluck('schools.id');

        if ($schoolIds->isEmpty()) {
            $apps = collect();
            return view('portal.applications.index', compact('apps'));
        }

        $schoolUserIds = \DB::table('school_user')
            ->whereIn('school_id', $schoolIds)
            ->pluck('user_id');

        $apps = Application::active()->ordered()->get()->map(function ($app) use ($schoolUserIds) {
            $appUsers = $app->users()->whereIn('users.id', $schoolUserIds);
            $totalUsers = $appUsers->count();

            if ($totalUsers === 0) return null;

            $syncedCount = (clone $appUsers)->wherePivot('sync_status', 'synced')->count();
            $pendingCount = (clone $appUsers)->wherePivot('sync_status', 'pending')->count();
            $failedCount = (clone $appUsers)->wherePivot('sync_status', 'failed')->count();

            // Health check
            $connector = $app->resolveConnector();
            $health = null;
            if ($connector && method_exists($connector, 'isReady')) {
                try {
                    $health = $connector->isReady() ? 'healthy' : 'down';
                } catch (\Throwable $e) {
                    $health = 'error';
                }
            }

            return (object) [
                'id' => $app->id,
                'name' => $app->name,
                'slug' => $app->slug,
                'icon' => $app->icon,
                'color' => $app->color,
                'connector_type' => $app->connector_type,
                'total_users' => $totalUsers,
                'synced' => $syncedCount,
                'pending' => $pendingCount,
                'failed' => $failedCount,
                'health' => $health,
                'sync_percent' => $totalUsers > 0 ? round(($syncedCount / $totalUsers) * 100) : 0,
            ];
        })->filter();

        return view('portal.applications.index', compact('apps'));
    }
}
