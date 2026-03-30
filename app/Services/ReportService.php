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
 * Report service — generates reports from sync DB data.
 */
class ReportService
{
    /**
     * School overview statistics.
     */
    public function getSchoolOverviewStats(Collection $schoolIds): array
    {
        $userIds = DB::table('school_user')
            ->whereIn('school_id', $schoolIds)
            ->pluck('user_id')
            ->unique();

        $apps = Application::active()->ordered()->get();

        $appStats = $apps->map(function ($app) use ($userIds) {
            if ($app->slug === 'mission-way') {
                $playerIds = \App\Models\MissionWay\MwPlayer::whereIn('user_id', $userIds)->pluck('id');
                $sessionQuery = \App\Models\MissionWay\MwSimulationSession::whereHas('players', function($q) use ($playerIds) {
                    $q->whereIn('player_id', $playerIds);
                });
                return [
                    'app' => $app,
                    'total_users' => $app->users()->whereIn('users.id', $userIds)->count(),
                    'total_progress' => (clone $sessionQuery)->count(),
                    'completed' => (clone $sessionQuery)->where('status', 'completed')->count(),
                    'in_progress' => (clone $sessionQuery)->where('status', 'in_progress')->count(),
                    'avg_score' => (clone $sessionQuery)->whereNotNull('final_score')->avg('final_score'),
                    'total_sessions' => (clone $sessionQuery)->count(),
                    'total_duration' => 0,
                ];
            }
            if ($app->slug === 'way-startup') {
                $members = \App\Models\WsMember::where('application_id', $app->id)
                    ->whereIn('user_id', $userIds)->get();
                $allProgress = $members->flatMap(fn($m) => collect($m->step_progress ?? []));
                return [
                    'app' => $app,
                    'total_users' => $app->users()->whereIn('users.id', $userIds)->count(),
                    'total_progress' => $members->count(),
                    'completed' => $allProgress->where('status', 'completed')->count(),
                    'in_progress' => $allProgress->where('status', 'in_progress')->count(),
                    'avg_score' => $members->avg('points'),
                    'total_sessions' => $members->count(),
                    'total_duration' => 0,
                ];
            }
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
     * Per-application detailed report (for specific schools).
     */
    public function getAppReport(Application $app, Collection $userIds): array
    {
        if ($app->slug === 'mission-way') {
            $playerIds = \App\Models\MissionWay\MwPlayer::whereIn('user_id', $userIds)->pluck('id');
            $sessionQuery = \App\Models\MissionWay\MwSimulationSession::whereHas('players', function($q) use ($playerIds) {
                $q->whereIn('player_id', $playerIds);
            });
            $sessions = (clone $sessionQuery)->get();
            return [
                'app' => $app,
                'total_progress' => $sessions->count(),
                'total_completed' => $sessions->where('status', 'completed')->count(),
                'total_sessions' => $sessions->count(),
                'total_duration' => 0,
                'avg_score' => $sessions->avg('final_score'),
                'module_stats' => collect(),
                'user_stats' => collect(),
                'sessions_by_day' => collect(),
                'recent_sessions' => $sessions->take(20),
            ];
        }

        if ($app->slug === 'way-startup') {
            $members = \App\Models\WsMember::where('application_id', $app->id)
                ->whereIn('user_id', $userIds)->get();
            $allProgress = $members->flatMap(fn($m) => collect($m->step_progress ?? []));
            return [
                'app' => $app,
                'total_progress' => $members->count(),
                'total_completed' => $allProgress->where('status', 'completed')->count(),
                'total_sessions' => $members->count(),
                'total_duration' => 0,
                'avg_score' => $members->avg('points'),
                'module_stats' => collect(),
                'user_stats' => collect(),
                'sessions_by_day' => collect(),
                'recent_sessions' => collect(),
            ];
        }

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
     * Student-level app report (single student, all applications).
     */
    public function getStudentReport(User $user): array
    {
        $apps = $user->applications()->active()->ordered()->get();

        // Vega connector apps may not be directly assigned — check all active apps
        $allApps = Application::active()->ordered()->get();

        return $allApps->map(function ($app) use ($user) {
            $isVega = $app->connector_class === 'App\\Connectors\\VegaConnector';

            if ($isVega) {
                // VegaConnector: read from remote vega_db
                $vegaReportService = app(\App\Services\VegaReportService::class);
                $vegaUserId = $vegaReportService->resolveVegaUserId($user->id);

                if (!$vegaUserId) return null;

                $vegaSessions = \App\Models\Vega\VegaDbSession::forUser($vegaUserId)
                    ->orderByDesc('created_at')
                    ->get();

                if ($vegaSessions->isEmpty()) return null;

                // Module-level progress
                $moduleGroups = $vegaSessions->groupBy('module');
                $progress = $moduleGroups->map(function ($items, $module) {
                    $hasActive = $items->contains(fn($s) => strtoupper($s->status ?? '') === 'ACTIVE');
                    $hasCompleted = $items->filter(fn($s) => strtoupper($s->status ?? '') === 'COMPLETED')->count() > 0;

                    return (object) [
                        'module_name'  => ucfirst($module),
                        'module_id'    => $module,
                        'module_type'  => $module,
                        'status'       => $hasActive ? 'in_progress' : ($hasCompleted ? 'completed' : 'in_progress'),
                        'score'        => $items->whereNotNull('score')->avg('score'),
                        'max_score'    => 100,
                        'attempts'     => $items->count(),
                        'started_at'   => $items->min('created_at'),
                        'completed_at' => $items->max('updated_at'),
                    ];
                })->values();

                // Return sessions in AppUserSession format (rich data fields)
                $sessions = $vegaSessions->map(function ($s) {
                    return (object) [
                        'session_name'        => $s->title ?: (ucfirst($s->module) . ' — ' . ($s->scenario ?? $s->subject ?? 'Session')),
                        'external_session_id' => $s->external_session_id,
                        'vega_session_id'     => $s->id,
                        'session_type'        => $s->module,
                        'started_at'          => $s->created_at,
                        'duration_seconds'    => $s->duration_seconds,
                        'score'               => $s->score,
                        'status'              => $s->status,
                        'threshold'           => $s->threshold,
                        'subject'             => $s->subject,
                        'topic'               => $s->topic,
                        'theme'               => $s->theme,
                        'language'            => $s->language,
                    ];
                });

                // Real completion calculation
                $completedModules = $moduleGroups->filter(fn($items) => !$items->contains(fn($s) => strtoupper($s->status ?? '') === 'ACTIVE'))->count();
                $inProgressModules = $moduleGroups->count() - $completedModules;

                return [
                    'app' => $app,
                    'progress' => $progress,
                    'sessions' => $sessions,
                    'stats' => [
                        'total_modules'   => $moduleGroups->count(),
                        'completed'       => $completedModules,
                        'in_progress'     => $inProgressModules,
                        'completion_rate' => $moduleGroups->count() > 0
                            ? round(($completedModules / $moduleGroups->count()) * 100, 1)
                            : 0,
                        'avg_score'       => $vegaSessions->whereNotNull('score')->avg('score'),
                        'total_sessions'  => $vegaSessions->count(),
                        'total_duration'  => $vegaSessions->sum(fn($s) => $s->duration_seconds),
                    ],
                ];
            }

            // Non-Vega: existing AppUserProgress/AppUserSession logic
            if ($app->slug === 'mission-way') {
                $player = \App\Models\MissionWay\MwPlayer::where('user_id', $user->id)->first();
                if (!$player) return null;
                $sessions = \App\Models\MissionWay\MwSimulationSession::whereHas('players', fn($q) => $q->where('player_id', $player->id))
                    ->orderByDesc('created_at')->get();
                if ($sessions->isEmpty()) return null;
                $completed = $sessions->where('status', 'completed')->count();
                $totalSessions = $sessions->count();
                return [
                    'app' => $app,
                    'progress' => collect(),
                    'sessions' => $sessions,
                    'stats' => [
                        'total_modules' => $totalSessions,
                        'completed' => $completed,
                        'in_progress' => $sessions->where('status', 'in_progress')->count(),
                        'completion_rate' => $totalSessions > 0 ? round(($completed / $totalSessions) * 100, 1) : 0,
                        'avg_score' => $sessions->whereNotNull('final_score')->avg('final_score'),
                        'total_sessions' => $totalSessions,
                        'total_duration' => 0,
                    ],
                ];
            }
            if ($app->slug === 'way-startup') {
                $member = \App\Models\WsMember::where('application_id', $app->id)->where('user_id', $user->id)->first();
                if (!$member) return null;
                $allProgress = collect($member->step_progress ?? []);
                if ($allProgress->isEmpty() && !$member->points) return null;
                $totalSteps = count($allProgress);
                $completed = $allProgress->where('status', 'completed')->count();
                return [
                    'app' => $app,
                    'progress' => $allProgress,
                    'sessions' => collect(),
                    'stats' => [
                        'total_modules' => $totalSteps,
                        'completed' => $completed,
                        'in_progress' => $allProgress->where('status', 'in_progress')->count(),
                        'completion_rate' => $totalSteps > 0 ? round(($completed / $totalSteps) * 100, 1) : 0,
                        'avg_score' => $member->points ?? 0,
                        'total_sessions' => 1,
                        'total_duration' => 0,
                    ],
                ];
            }

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
     * Class-level report.
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
            if ($a->slug === 'mission-way') {
                $playerIds = \App\Models\MissionWay\MwPlayer::whereIn('user_id', $studentIds)->pluck('id');
                $sessionQuery = \App\Models\MissionWay\MwSimulationSession::whereHas('players', function($q) use ($playerIds) {
                    $q->whereIn('player_id', $playerIds);
                });
                $totalCount = (clone $sessionQuery)->count();
                $completed = (clone $sessionQuery)->where('status', 'completed')->count();
                return [
                    'app' => $a,
                    'students_count' => $studentIds->count(),
                    'total_progress' => $totalCount,
                    'completed' => $completed,
                    'completion_rate' => $totalCount > 0 ? round(($completed / $totalCount) * 100, 1) : 0,
                    'avg_score' => (clone $sessionQuery)->whereNotNull('final_score')->avg('final_score'),
                ];
            }
            if ($a->slug === 'way-startup') {
                $members = \App\Models\WsMember::where('application_id', $a->id)
                    ->whereIn('user_id', $studentIds)->get();
                $allProgress = $members->flatMap(fn($m) => collect($m->step_progress ?? []));
                $totalCount = $members->count();
                $completed = $allProgress->where('status', 'completed')->count();
                return [
                    'app' => $a,
                    'students_count' => $studentIds->count(),
                    'total_progress' => $totalCount,
                    'completed' => $completed,
                    'completion_rate' => $totalCount > 0 ? round(($completed / $totalCount) * 100, 1) : 0,
                    'avg_score' => $members->avg('points'),
                ];
            }
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
     * Teacher's all classes report.
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
     * Single student, single application report.
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
