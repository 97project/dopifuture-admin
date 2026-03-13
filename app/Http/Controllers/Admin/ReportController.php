<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\School;
use App\Models\User;
use App\Services\ReportService;
use Illuminate\Support\Facades\DB;

/**
 * Admin-side reporting controller — more detailed than portal.
 * Enriched with connector data for portal parity.
 */
class ReportController extends Controller
{
    public function __construct(private ReportService $reportService)
    {
    }

    /**
     * System-wide reports dashboard.
     * Enriched: per-app connector health status + sync counts.
     */
    public function index()
    {
        $allSchoolIds = School::active()->pluck('id');
        $overview = $this->reportService->getSchoolOverviewStats($allSchoolIds);
        $apps = Application::active()->ordered()->get();
        $schools = School::active()
            ->withCount(['users', 'classes', 'licenses'])
            ->orderByDesc('users_count')
            ->get();

        // Connector API health for each app
        $connectorHealth = [];
        foreach ($apps as $app) {
            try {
                $connector = $app->resolveConnector();
                if (!$connector) {
                    $connectorHealth[$app->slug] = ['ok' => false, 'error' => 'No connector'];
                    continue;
                }

                if (method_exists($connector, 'getHealth')) {
                    $connectorHealth[$app->slug] = $connector->getHealth();
                } elseif (method_exists($connector, 'getHealthCheck')) {
                    $connectorHealth[$app->slug] = $connector->getHealthCheck();
                } else {
                    $connectorHealth[$app->slug] = ['ok' => true, 'error' => null];
                }
            } catch (\Throwable $e) {
                $connectorHealth[$app->slug] = ['ok' => false, 'error' => $e->getMessage()];
            }
        }

        return view('admin.reports.index', compact('overview', 'apps', 'schools', 'connectorHealth'));
    }

    /**
     * Per-application detailed report (all schools).
     * Enriched: connector-specific report data via gatherReportData().
     */
    public function appReport(Application $app)
    {
        $allUserIds = $app->users()->pluck('users.id');
        $data = $this->reportService->getAppReport($app, $allUserIds);

        // Connector enrichment
        try {
            $connector = $app->resolveConnector();
            if ($connector) {
                // Health check
                if (method_exists($connector, 'getHealth')) {
                    $data['api_health'] = $connector->getHealth();
                } elseif (method_exists($connector, 'getHealthCheck')) {
                    $data['api_health'] = $connector->getHealthCheck();
                }

                // Sync stats
                $data['sync_stats'] = [
                    'total_users' => $app->users()->count(),
                    'synced' => $app->users()->wherePivot('sync_status', 'synced')->count(),
                    'failed' => $app->users()->wherePivot('sync_status', 'failed')->count(),
                ];
            }
        } catch (\Throwable $e) {
            $data['api_health'] = ['ok' => false, 'error' => $e->getMessage()];
        }

        return view('admin.reports.app', $data);
    }

    /**
     * Per-school detailed report.
     * Enriched: connector health + per-app sync status for the school.
     */
    public function schoolReport(School $school)
    {
        $schoolIds = collect([$school->id]);
        $overview = $this->reportService->getSchoolOverviewStats($schoolIds);
        $apps = Application::active()->ordered()->get();

        $school->load(['users', 'classes.students', 'licenses.purchases']);

        // Per-app connector health + sync counts for this school's users
        $appConnectorData = [];
        $schoolUserIds = $school->users->pluck('id');

        foreach ($apps as $app) {
            try {
                $connector = $app->resolveConnector();
                $syncedCount = DB::table('application_user')
                    ->where('application_id', $app->id)
                    ->whereIn('user_id', $schoolUserIds)
                    ->where('sync_status', 'synced')
                    ->count();

                $totalInApp = DB::table('application_user')
                    ->where('application_id', $app->id)
                    ->whereIn('user_id', $schoolUserIds)
                    ->count();

                $health = ['ok' => true, 'error' => null];
                if ($connector) {
                    if (method_exists($connector, 'getHealth')) {
                        $health = $connector->getHealth();
                    } elseif (method_exists($connector, 'getHealthCheck')) {
                        $health = $connector->getHealthCheck();
                    }
                }

                $appConnectorData[$app->slug] = [
                    'health' => $health,
                    'synced_count' => $syncedCount,
                    'total_in_app' => $totalInApp,
                ];
            } catch (\Throwable $e) {
                $appConnectorData[$app->slug] = [
                    'health' => ['ok' => false, 'error' => $e->getMessage()],
                    'synced_count' => 0,
                    'total_in_app' => 0,
                ];
            }
        }

        return view('admin.reports.school', compact('school', 'overview', 'apps', 'appConnectorData'));
    }

    /**
     * Admin-level student detailed report.
     * Enriched: connector profiles matching portal student report parity.
     */
    public function studentReport(User $student)
    {
        $student->load(['roles', 'schools', 'classes.school', 'applications']);
        $reportData = $this->reportService->getStudentReport($student);
        $apps = Application::active()->ordered()->get();

        // Portal parity: connector profiles for each app
        $connectorProfiles = [];
        foreach ($student->applications as $app) {
            try {
                $connector = $app->resolveConnector();
                if (!$connector) continue;

                $report = $connector->getUserReport($student);
                if (!$report || !($report['success'] ?? false)) continue;

                $d = $report['data'] ?? [];

                if ($connector instanceof \App\Connectors\MissionWayConnector) {
                    $player = $d['player'] ?? $d;
                    $profile = $d['profile'] ?? $player;
                    $sessions = $d['sessions'] ?? [];
                    // Scenario breakdown
                    $scenarioMap = [
                        'village_life' => ['name' => 'Köy Hayatı', 'icon' => '🏠', 'color' => '#4364F7'],
                        'world_traveler' => ['name' => 'Dünya Gezgini', 'icon' => '🌍', 'color' => '#8B5CF6'],
                        'novaris' => ['name' => 'Novaris', 'icon' => '🏢', 'color' => '#F59E0B'],
                        'biolab' => ['name' => 'BioLab', 'icon' => '🧪', 'color' => '#10B981'],
                        'what_if' => ['name' => 'Ya Olsaydı?', 'icon' => '💡', 'color' => '#EF4444'],
                        'lost_egg' => ['name' => 'Kayıp Yumurta', 'icon' => '🔍', 'color' => '#06B6D4'],
                    ];
                    $scenarioBreakdown = [];
                    foreach ($sessions as $sess) {
                        $simName = $sess['simulation_name'] ?? $sess['simulationName'] ?? $sess['scenario'] ?? '';
                        $simSlug = strtolower(str_replace([' ', '-'], '_', preg_replace('/[^a-zA-Z0-9_\- ]/', '', $simName)));
                        $mk = 'other';
                        foreach (array_keys($scenarioMap) as $k) { if (str_contains($simSlug, $k)) { $mk = $k; break; } }
                        if (!isset($scenarioBreakdown[$mk])) {
                            $m = $scenarioMap[$mk] ?? ['name' => $simName ?: 'Diğer', 'icon' => '🎮', 'color' => '#6B7280'];
                            $scenarioBreakdown[$mk] = array_merge($m, ['sessions' => 0, 'total_score' => 0, 'total_time' => 0]);
                        }
                        $scenarioBreakdown[$mk]['sessions']++;
                        $scenarioBreakdown[$mk]['total_score'] += $sess['score'] ?? $sess['totalScore'] ?? 0;
                        $scenarioBreakdown[$mk]['total_time'] += $sess['playTimeMinutes'] ?? $sess['play_time_minutes'] ?? 0;
                    }
                    foreach ($scenarioBreakdown as &$sb) { $sb['avg_score'] = $sb['sessions'] > 0 ? round($sb['total_score'] / $sb['sessions']) : 0; }
                    $connectorProfiles[$app->slug] = [
                        'type' => 'missionway',
                        'total_score' => $player['totalScore'] ?? $player['total_score'] ?? 0,
                        'simulations_completed' => $d['simulations_completed'] ?? 0,
                        'play_time_minutes' => round(($player['totalPlayTime'] ?? $player['total_play_time'] ?? 0) / 60),
                        'achievements' => $player['achievements'] ?? [],
                        'level' => $player['level'] ?? $player['currentLevel'] ?? null,
                        'scenario_breakdown' => $scenarioBreakdown,
                    ];
                } elseif ($connector instanceof \App\Connectors\WayStartupConnector) {
                    $connectorProfiles[$app->slug] = [
                        'type' => 'waystartup',
                        'points' => $d['member']['points'] ?? 0,
                        'completed_steps' => $d['completed_steps'] ?? 0,
                        'total_steps' => $d['total_steps'] ?? 0,
                        'simulations_with_progress' => $d['simulations_with_progress'] ?? [],
                    ];
                } elseif ($connector instanceof \App\Connectors\VegaConnector) {
                    $allSessions = $d['sessions'] ?? [];
                    $connectorProfiles[$app->slug] = [
                        'type' => 'vega',
                        'session_count' => $d['session_count'] ?? 0,
                        'lecturer_count' => $d['modules']['lecturer'] ?? 0,
                        'simulator_count' => $d['modules']['simulator'] ?? 0,
                        'chatbot_count' => $d['modules']['chatbot'] ?? $d['modules']['all'] ?? 0,
                        'profile' => $d['profile'] ?? [],
                        'lecturer_sessions' => array_values(array_filter($allSessions, fn($s) => ($s['module'] ?? '') === 'lecturer')),
                        'chatbot_sessions' => array_values(array_filter($allSessions, fn($s) => !in_array($s['module'] ?? '', ['simulator', 'lecturer']))),
                    ];
                }
            } catch (\Throwable $e) {
                // Failsafe — skip this connector
            }
        }

        return view('admin.reports.student', compact('student', 'reportData', 'apps', 'connectorProfiles'));
    }
}
