<?php

namespace App\Services;

use App\Models\Application;
use App\Models\AppUserProgress;
use App\Models\AppUserSession;
use App\Models\School;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Raporlama servisi — DB'deki sync verileri üzerinden rapor üretir.
 */
class ReportService
{
    /**
     * Okul genel istatistikleri.
     */
    public function getSchoolOverviewStats(Collection $schoolIds): array
    {
        $userIds = DB::table('school_user')
            ->whereIn('school_id', $schoolIds)
            ->pluck('user_id')
            ->unique();

        $apps = Application::active()->ordered()->get();

        $appStats = $apps->map(function ($app) use ($userIds) {
            $progressQuery = AppUserProgress::where('application_id', $app->id)
                ->whereIn('user_id', $userIds);

            return [
                'app' => $app,
                'total_users' => $app->users()->whereIn('users.id', $userIds)->count(),
                'total_progress' => (clone $progressQuery)->count(),
                'completed' => (clone $progressQuery)->where('status', 'completed')->count(),
                'in_progress' => (clone $progressQuery)->where('status', 'in_progress')->count(),
                'avg_score' => (clone $progressQuery)->whereNotNull('score')->avg('score'),
                'total_sessions' => AppUserSession::where('application_id', $app->id)
                    ->whereIn('user_id', $userIds)->count(),
                'total_duration' => AppUserSession::where('application_id', $app->id)
                    ->whereIn('user_id', $userIds)->sum('duration_seconds'),
            ];
        });

        return [
            'total_users' => $userIds->count(),
            'total_students' => DB::table('school_user')
                ->whereIn('school_id', $schoolIds)
                ->where('role', 'student')
                ->count(),
            'app_stats' => $appStats,
        ];
    }

    /**
     * Uygulama bazlı detaylı rapor (belirli okullar için).
     */
    public function getAppReport(Application $app, Collection $userIds): array
    {
        $progress = AppUserProgress::where('application_id', $app->id)
            ->whereIn('user_id', $userIds)
            ->get();

        $sessions = AppUserSession::where('application_id', $app->id)
            ->whereIn('user_id', $userIds)
            ->orderByDesc('started_at')
            ->get();

        // Module breakdown
        $moduleStats = $progress->groupBy('module_type')->map(function ($items, $type) {
            return [
                'type' => $type,
                'total' => $items->count(),
                'completed' => $items->where('status', 'completed')->count(),
                'in_progress' => $items->where('status', 'in_progress')->count(),
                'avg_score' => $items->whereNotNull('score')->avg('score'),
                'avg_duration' => $items->whereNotNull('duration_seconds')->avg('duration_seconds'),
            ];
        });

        // Per-user completion rates
        $userStats = $progress->groupBy('user_id')->map(function ($items, $userId) {
            $user = User::find($userId);
            return [
                'user' => $user,
                'total' => $items->count(),
                'completed' => $items->where('status', 'completed')->count(),
                'completion_rate' => $items->count() > 0
                    ? round(($items->where('status', 'completed')->count() / $items->count()) * 100, 1)
                    : 0,
                'avg_score' => $items->whereNotNull('score')->avg('score'),
                'total_duration' => $items->sum('duration_seconds'),
            ];
        })->sortByDesc('completion_rate')->values();

        // Session histogram (last 30 days)
        $sessionsByDay = $sessions
            ->where('started_at', '>=', now()->subDays(30))
            ->groupBy(fn($s) => $s->started_at ? $s->started_at->format('Y-m-d') : 'unknown')
            ->map(fn($items) => $items->count());

        return [
            'app' => $app,
            'total_progress' => $progress->count(),
            'total_completed' => $progress->where('status', 'completed')->count(),
            'total_sessions' => $sessions->count(),
            'total_duration' => $sessions->sum('duration_seconds'),
            'avg_score' => $progress->whereNotNull('score')->avg('score'),
            'module_stats' => $moduleStats,
            'user_stats' => $userStats,
            'sessions_by_day' => $sessionsByDay,
            'recent_sessions' => $sessions->take(20),
        ];
    }

    /**
     * Öğrenci bazlı uygulama raporu (tek öğrenci, tüm uygulamalar).
     */
    public function getStudentReport(User $user): array
    {
        $apps = $user->applications()->active()->ordered()->get();

        // VegaConnector uygulamaları için direkt atanmış olamayabilir — tüm aktif uygulamalardan kontrol et
        $allApps = Application::active()->ordered()->get();

        return $allApps->map(function ($app) use ($user) {
            $isVega = $app->connector_class === 'App\\Connectors\\VegaConnector';

            if ($isVega) {
                // VegaConnector: vega_sessions tablosundan oku
                $vegaSessions = \App\Models\VegaSession::where('application_id', $app->id)
                    ->where('user_id', $user->id)
                    ->orderByDesc('started_at')
                    ->get();

                if ($vegaSessions->isEmpty()) return null;

                $messages = \App\Models\VegaSessionMessage::whereIn('session_id', $vegaSessions->pluck('id'))->get();

                // Module bazında progress simüle et
                $moduleGroups = $vegaSessions->groupBy('module');
                $progress = $moduleGroups->map(function ($items, $module) use ($app) {
                    return (object) [
                        'module_name'  => ucfirst($module),
                        'module_id'    => $module,
                        'module_type'  => $module,
                        'status'       => 'completed',
                        'score'        => $items->whereNotNull('score')->avg('score'),
                        'max_score'    => 100,
                        'attempts'     => $items->count(),
                        'started_at'   => $items->min('started_at'),
                        'completed_at' => $items->max('ended_at'),
                    ];
                })->values();

                // Session'ları AppUserSession formatında dön
                $sessions = $vegaSessions->map(function ($s) {
                    return (object) [
                        'session_name'        => $s->user_name . ' — ' . ucfirst($s->module),
                        'external_session_id' => $s->external_id,
                        'session_type'        => $s->module,
                        'started_at'          => $s->started_at,
                        'duration_seconds'    => min(($s->duration_minutes ?? 0), 120) * 60,
                        'score'               => $s->score,
                    ];
                });

                return [
                    'app' => $app,
                    'progress' => $progress,
                    'sessions' => $sessions,
                    'stats' => [
                        'total_modules'   => $moduleGroups->count(),
                        'completed'       => $moduleGroups->count(),
                        'in_progress'     => 0,
                        'completion_rate' => 100,
                        'avg_score'       => $vegaSessions->whereNotNull('score')->avg('score'),
                        'total_sessions'  => $vegaSessions->count(),
                        'total_duration'  => $vegaSessions->sum(fn($s) => min($s->duration_minutes ?? 0, 120)) * 60,
                    ],
                ];
            }

            // Non-Vega: mevcut AppUserProgress/AppUserSession mantığı
            $progress = AppUserProgress::where('user_id', $user->id)
                ->where('application_id', $app->id)
                ->orderBy('module_type')
                ->get();

            $sessions = AppUserSession::where('user_id', $user->id)
                ->where('application_id', $app->id)
                ->orderByDesc('started_at')
                ->get();

            if ($progress->isEmpty() && $sessions->isEmpty()) return null;

            return [
                'app' => $app,
                'progress' => $progress,
                'sessions' => $sessions,
                'stats' => [
                    'total_modules' => $progress->count(),
                    'completed' => $progress->where('status', 'completed')->count(),
                    'in_progress' => $progress->where('status', 'in_progress')->count(),
                    'completion_rate' => $progress->count() > 0
                        ? round(($progress->where('status', 'completed')->count() / $progress->count()) * 100, 1)
                        : 0,
                    'avg_score' => $progress->whereNotNull('score')->avg('score'),
                    'total_sessions' => $sessions->count(),
                    'total_duration' => $sessions->sum('duration_seconds'),
                ],
            ];
        })->filter()->keyBy(fn($item) => $item['app']->slug)->toArray();
    }

    /**
     * Sınıf bazlı rapor.
     */
    public function getClassReport($class, ?Application $app = null): array
    {
        $studentIds = $class->students()->pluck('users.id');

        if ($app) {
            return $this->getAppReport($app, $studentIds);
        }

        // All apps summary
        $apps = Application::active()->ordered()->get();
        return $apps->map(function ($a) use ($studentIds) {
            $progress = AppUserProgress::where('application_id', $a->id)
                ->whereIn('user_id', $studentIds);

            return [
                'app' => $a,
                'students_count' => $studentIds->count(),
                'total_progress' => (clone $progress)->count(),
                'completed' => (clone $progress)->where('status', 'completed')->count(),
                'completion_rate' => (clone $progress)->count() > 0
                    ? round(((clone $progress)->where('status', 'completed')->count() / (clone $progress)->count()) * 100, 1)
                    : 0,
                'avg_score' => (clone $progress)->whereNotNull('score')->avg('score'),
            ];
        })->toArray();
    }

    /**
     * Öğretmenin tüm sınıflarının raporu.
     */
    public function getTeacherClassReport(User $teacher): array
    {
        $classes = $teacher->classes()->with('school')->withCount('students')->get();

        return $classes->map(function ($class) {
            return [
                'class' => $class,
                'school' => $class->school,
                'students_count' => $class->students_count,
                'app_stats' => $this->getClassReport($class),
            ];
        })->toArray();
    }

    /**
     * Tek öğrenci, tek uygulama raporu.
     */
    public function getStudentAppReport(User $user, Application $app): array
    {
        $progress = AppUserProgress::where('user_id', $user->id)
            ->where('application_id', $app->id)
            ->orderBy('module_type')
            ->get();

        $sessions = AppUserSession::where('user_id', $user->id)
            ->where('application_id', $app->id)
            ->orderByDesc('started_at')
            ->get();

        return [
            'app' => $app,
            'progress' => $progress,
            'sessions' => $sessions,
            'stats' => [
                'total_modules' => $progress->count(),
                'completed' => $progress->where('status', 'completed')->count(),
                'in_progress' => $progress->where('status', 'in_progress')->count(),
                'completion_rate' => $progress->count() > 0
                    ? round(($progress->where('status', 'completed')->count() / $progress->count()) * 100, 1)
                    : 0,
                'avg_score' => $progress->whereNotNull('score')->avg('score'),
                'total_sessions' => $sessions->count(),
                'total_duration' => $sessions->sum('duration_seconds'),
            ],
        ];
    }

    /**
     * Format duration seconds to human readable.
     */
    public static function formatDuration(int $seconds): string
    {
        if ($seconds < 60) return $seconds . 's';
        if ($seconds < 3600) return floor($seconds / 60) . 'm ' . ($seconds % 60) . 's';
        return floor($seconds / 3600) . 'h ' . floor(($seconds % 3600) / 60) . 'm';
    }
}
