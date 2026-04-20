<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\MissionWay\MwAssignment;
use App\Models\MissionWay\MwPlayer;
use App\Models\MissionWay\MwPlayerChoice;
use App\Models\MissionWay\MwSimulationSession;
use App\Models\MissionWay\RefSimulation;
use App\Models\MissionWay\RefSimulationPath;
use App\Models\MissionWay\RefTranslation;
use App\Models\SchoolClass;
use App\Models\User;
use App\Models\WsMember;
use App\Models\WsSimulation;
use App\Models\WsStepEvaluation;
use App\Services\MwMetricService;
use App\Services\ReportService;
use App\Services\VegaReportService;
use Illuminate\Support\Facades\DB;

/**
 * Portal-side reporting controller.
 * Access is scoped by role via PortalRole middleware.
 */
class PortalReportController extends Controller
{
    public function __construct(
        private ReportService $reportService,
        private VegaReportService $vegaReportService,
        private MwMetricService $mwMetricService,
    ) {
    }

    /**
     * Reports overview dashboard.
     */
    public function index()
    {
        $user = auth()->user();
        $data = ['user' => $user];

        // Determine school IDs based on role
        if ($user->hasAnyRole(['school-admin', 'school-principal'])) {
            $schoolIds = $user->schools()->pluck('schools.id');
            $data['overview'] = $this->reportService->getSchoolOverviewStats($schoolIds);
            $data['apps'] = Application::active()->ordered()->get();

            // Enrich Vega app cards with real remote DB data
            try {
                $panelUserIds = DB::table('school_user')
                    ->whereIn('school_id', $schoolIds)
                    ->pluck('user_id')
                    ->unique()
                    ->values();
                $vegaUserMap = $this->vegaReportService->resolveVegaUserIds($panelUserIds);
                $vegaUserIds = array_values($vegaUserMap);
                $vegaSummary = $this->vegaReportService->getDashboardSummary($vegaUserIds);

                // Map portal slug → Vega summary key
                $slugToVega = [
                    'role-galaxy'  => 'role_galaxy',
                    'study-space'  => 'study_space',
                    'way-ai-coach' => 'way_ai_coach',
                ];

                $data['overview']['app_stats'] = $data['overview']['app_stats']->map(function ($stat) use ($slugToVega, $vegaSummary) {
                    $vegaKey = $slugToVega[$stat['app']->slug] ?? null;
                    if ($vegaKey && isset($vegaSummary[$vegaKey])) {
                        $v = $vegaSummary[$vegaKey];
                        // Overlay Vega data onto the app card
                        $stat['total_progress'] = $v['sessions'];
                        $stat['completed'] = $v['completed'] ?? 0;
                        $stat['in_progress'] = ($v['sessions']) - ($v['completed'] ?? 0);
                        $stat['total_users'] = $v['active_students'];
                        $stat['avg_score'] = $v['avg_score'] ?? null;
                    }
                    return $stat;
                });
            } catch (\Exception $e) {
                // Vega DB unavailable — keep local data
            }

            // Advanced Dashboards (Teacher / Admin Only)
            $schoolUserIds = $this->getScopedUserIds($user);
            $data['radarMetrics'] = $this->getGlobalRadarMetrics($schoolUserIds);
            $data['recentActivities'] = $this->getRecentActivities($schoolUserIds, 8);
            $data['leaderboards'] = $this->generateLeaderboards($schoolUserIds);
            
            // Phase 2 Details
            $data['usageTrend'] = $this->getUsageTrend30Days($schoolUserIds);
            $data['scoreDistribution'] = $this->getScoreDistribution($schoolUserIds);
            $data['modulePopularity'] = $this->getModulePopularity($schoolUserIds);

            // Phase 3 Details
            $data['perAppDashboards'] = $this->getPerAppDeepDashboards($schoolUserIds);

        } elseif ($user->hasRole('teacher')) {
            // Teacher sees class-level overview
            $data['myClasses'] = $user->classes()
                ->with('school')
                ->withCount('students')
                ->get();
            $data['apps'] = Application::active()->ordered()->get();

            // Advanced Dashboards (Teacher / Admin Only)
            $classUserIds = $this->getScopedUserIds($user);
            $data['radarMetrics'] = $this->getGlobalRadarMetrics($classUserIds);
            $data['recentActivities'] = $this->getRecentActivities($classUserIds, 8);
            $data['leaderboards'] = $this->generateLeaderboards($classUserIds);

            // Phase 2 Details
            $data['usageTrend'] = $this->getUsageTrend30Days($classUserIds);
            $data['scoreDistribution'] = $this->getScoreDistribution($classUserIds);
            $data['modulePopularity'] = $this->getModulePopularity($classUserIds);

            // Phase 3 Details
            $data['perAppDashboards'] = $this->getPerAppDeepDashboards($classUserIds);

        } elseif ($user->hasRole('student')) {
            // Student sees personal report
            $data['studentReport'] = $this->reportService->getStudentReport($user);
        }

        return view('portal.reports.index', $data);
    }

    /**
     * Per-application detailed report.
     * Figma F-38 (Assignments tab) + F-63 (Performance tab)
     *
     * Vega apps (role-galaxy, way-ai-coach, study-space): real Vega DB data.
     * Mission WAY, Way Startup: real MySQL data from harvested tables.
     */
    public function appReport(Application $app)
    {
        $user = auth()->user();
        $panelUserIds = $this->getScopedUserIds($user);

        // ── VEGA APPS: Direct SQL from Vega remote DB ──────────────
        if (in_array($app->slug, ['role-galaxy', 'way-ai-coach', 'study-space'])) {
            try {
                $vegaUserMap = $this->vegaReportService->resolveVegaUserIds($panelUserIds);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Vega DB App Report Error: ' . $e->getMessage());
                $vegaUserMap = [];
            }

            // Prepare panel user models as keyed collection
            $panelUsers = User::whereIn('id', array_keys($vegaUserMap))->get()->keyBy('id');

            $data = match ($app->slug) {
                'role-galaxy'  => $this->vegaReportService->getRoleGalaxyReport($vegaUserMap, $panelUsers),
                'way-ai-coach' => $this->vegaReportService->getWayAiCoachReport($vegaUserMap, $panelUsers),
                'study-space'  => $this->vegaReportService->getStudySpaceReport($vegaUserMap, $panelUsers),
            };

            $data['app'] = $app;
            $data['user'] = $user;
            $data['missions'] = collect();
            $data['startups'] = collect();
            $data['total_missions'] = 0;

            // ── Anlık API ile katalog verileri (harvest gerekmez) ──
            try {
                $vegaConnector = app(\App\Connectors\VegaConnector::class);

                // Ders kataloğu (Way AI Coach)
                if ($app->slug === 'way-ai-coach') {
                    $data['lessons'] = collect($vegaConnector->getLecturerLessons());
                }
                // Senaryo kataloğu (Role Galaxy)
                if ($app->slug === 'role-galaxy') {
                    $data['scenarios'] = collect($vegaConnector->getSimulatorScenarios());
                }
                // Wing rozetleri (tüm Vega apps)
                $data['wings'] = collect($vegaConnector->getWings());
                $data['wingPoints'] = $vegaConnector->getWingPoints();
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Vega API catalog fetch error: ' . $e->getMessage());
                $data['lessons'] = $data['lessons'] ?? collect();
                $data['scenarios'] = $data['scenarios'] ?? collect();
                $data['wings'] = $data['wings'] ?? collect();
                $data['wingPoints'] = $data['wingPoints'] ?? [];
            }

            return view('portal.reports.app', $data);
        }

        // ── NON-VEGA APPS: Real Database Queries ────────────────

        // ── MISSION WAY: Real Database Query ──────────
        $missions = collect();
        if ($app->slug === 'mission-way') {
            $simulations = RefSimulation::with('versions.paths')->get();
            foreach ($simulations as $sim) {
                $versionIds = $sim->versions->pluck('id');
                $sessions = MwSimulationSession::whereIn('simulation_version_id', $versionIds)
                    ->whereHas('players.player', function ($q) use ($panelUserIds) {
                        $q->whereIn('user_id', $panelUserIds);
                    })
                    ->with(['players.player.user'])
                    ->get();

                $activeSessionPlayers = $sessions->flatMap->players->map(function ($sp) {
                    $u = $sp->player?->user ?? null;
                    return (object)[
                        'id' => $u ? $u->id : $sp->player_id,
                        'name' => $u ? $u->name : ($sp->player?->name ?? 'Unknown'),
                        'surname' => $u ? $u->surname : ($sp->player?->surname ?? ''),
                        'avatar' => null,
                        'classes' => $u ? $u->classes->map(fn($c) => (object)['name' => $c->name]) : collect(),
                    ];
                });

                // Atanıp henüz seansa başlamayanları da dahil et
                $assignedPlayers = \App\Models\MissionWay\MwAssignmentPlayer::whereHas('assignment', fn($q) => $q->where('simulation_id', $sim->id))
                    ->with('player.user.classes')
                    ->whereHas('player', fn($q) => $q->whereIn('user_id', $panelUserIds))
                    ->get()
                    ->map(function ($ap) {
                        $u = $ap->player?->user ?? null;
                        return (object)[
                            'id' => $u ? $u->id : $ap->player_id,
                            'name' => $u ? $u->name : ($ap->player?->name ?? 'Unknown'),
                            'surname' => $u ? $u->surname : ($ap->player?->surname ?? ''),
                            'avatar' => null,
                            'classes' => $u ? $u->classes->map(fn($c) => (object)['name' => $c->name]) : collect(),
                        ];
                    });

                $players = $activeSessionPlayers->concat($assignedPlayers)->unique('id');

                $minCreated = $sessions->min('created_at');
                $assignedDate = $minCreated ? \Carbon\Carbon::parse($minCreated)->format('d/m/Y') : '-';
                $minDeadline = MwAssignment::where('simulation_id', $sim->id)->min('deadline');
                $deadlineDate = $minDeadline ? \Carbon\Carbon::parse($minDeadline)->format('d/m/Y') : '-';

                // Metric enrichment from completed sessions
                $completedSessions = $sessions->where('status', 'completed');
                $enriched = $this->mwMetricService->aggregateSessionMetrics($completedSessions, $sim->versions->first()?->id);
                $metricValues = $this->mwMetricService->getAllMetricValues($enriched);
                $metricTrends = $this->mwMetricService->getAllMetricValues($enriched, 'trend');

                $missions->push((object)[
                    'id'             => $sim->id,
                    'name'           => $sim->name ?? ('Simulation #' . $sim->id),
                    'students'       => $players,
                    'assigned_date'  => $assignedDate,
                    'deadline'       => $deadlineDate,
                    'health_point'   => $metricValues['health'],
                    'resource_point' => $metricValues['resource'],
                    'ethics_point'   => $metricValues['ethics'],
                    'adaptation_point' => $metricValues['adaptation'],
                    'health_trend'   => $metricTrends['health'],
                    'resource_trend' => $metricTrends['resource'],
                    'ethics_trend'   => $metricTrends['ethics'],
                    'adaptation_trend' => $metricTrends['adaptation'],
                ]);
            }
        }

        // ── WAY STARTUP: Real Database Query ───────────
        $startups = collect();
        if ($app->slug === 'way-startup') {
            $wsSims = WsSimulation::with('steps')->get();
            foreach ($wsSims as $wsSim) {
                $stepCount = $wsSim->steps->count();

                // Fix: Sadece bu simülasyona atanmış veya simülasyonda adım ilerlemesi olan öğrencileri getir (eski kod tüm öğrencileri getiriyordu)
                $wsAssignedMemberIds = \App\Models\WsAssignmentMember::whereHas('assignment', fn($q) => $q->where('simulation_id', $wsSim->id))->pluck('member_id');
                $wsPlayedMemberIds = \App\Models\WsStepProgress::whereHas('step', fn($q) => $q->where('simulation_id', $wsSim->id))->pluck('member_id');
                $targetMemberIds = $wsAssignedMemberIds->concat($wsPlayedMemberIds)->unique();

                $members = WsMember::whereIn('id', $targetMemberIds)
                    ->whereIn('user_id', $panelUserIds)
                    ->with(['user', 'progress'])
                    ->get();
                $completedSteps = 0;
                $earnedPointsSum = 0;
                foreach ($members as $m) {
                    $earnedPointsSum += $m->points;
                    foreach ($m->progress as $sp) {
                        if ($sp->status === 'completed') $completedSteps++;
                    }
                }
                $systemPointAvg = $members->count() > 0 ? round($earnedPointsSum / $members->count()) : 0;
                $totalPoints = $systemPointAvg; // Use average earned points
                $maxPoints = $wsSim->steps->sum('max_score');

                // Deadline from assignment metadata (if available)
                $firstAssignment = \App\Models\WsAssignment::where('simulation_id', $wsSim->id)->orderBy('due_date', 'asc')->first();
                $deadline = $firstAssignment?->due_date ?? $wsSim->metadata['dueDate'] ?? $wsSim->metadata['deadline'] ?? null;
                $deadlineStr = $deadline ? \Carbon\Carbon::parse($deadline)->format('d/m/Y') : '-';
                $deadlineOverdue = $deadline ? \Carbon\Carbon::parse($deadline)->isPast() : false;

                $startups->push((object)[
                    'id'            => $wsSim->id,
                    'name'          => $wsSim->name,
                    'type'          => $wsSim->type ?? $wsSim->category ?? '-',
                    'type_icon'     => '📁',
                    'students'      => $members->map(fn($m) => (object)[
                        'id' => $m->user_id,
                        'name' => $m->user?->name ?? '-',
                        'surname' => $m->user?->surname ?? '',
                    ]),
                    'deadline'      => $deadlineStr,
                    'deadline_overdue' => $deadlineOverdue,
                    'step_completed'=> $completedSteps,
                    'step_total'    => $stepCount * max(1, $members->count()),
                    'system_point'  => $systemPointAvg,
                    'max_point'     => $maxPoints,
                    'teacher_point' => null,
                    'status'        => $completedSteps >= $stepCount && $stepCount > 0 ? 'completed' : ($completedSteps > 0 ? 'in_progress' : 'not_started'),
                ]);
            }
        }

        // ── USER STATS for Performance tab — Real DB ─────────
        $panelStudents = User::whereIn('id', $panelUserIds)->with('classes')->get();
        $userStats = $panelStudents->map(function ($s) use ($app) {
            $base = [
                'user' => $s,
                'total' => 0,
                'completed' => 0,
                'completion_rate' => 0,
                'avg_score' => 0,
                'total_duration' => 0,
            ];
            if ($app->slug === 'mission-way') {
                $player = MwPlayer::where('user_id', $s->id)->first();
                if ($player) {
                    $progressRecords = $player->progress;
                    $base['total'] = $progressRecords->count();
                    $base['completed'] = $progressRecords->whereNotNull('completed_at')->count();
                    $base['completion_rate'] = $base['total'] > 0 ? round(($base['completed'] / $base['total']) * 100) : 0;
                    $base['avg_score'] = $player->profile?->total_score ?? 0;

                    // Per-student metrics from latest completed session
                    $latestSession = MwSimulationSession::whereHas('players', fn($q) => $q->where('player_id', $player->id))
                        ->where('status', 'completed')
                        ->latest()
                        ->first();
                    if ($latestSession && $latestSession->final_metrics) {
                        $enriched = $this->mwMetricService->enrichSessionMetrics(
                            $latestSession->final_metrics,
                            $latestSession->simulation_version_id
                        );
                        $metricValues = $this->mwMetricService->getAllMetricValues($enriched);
                        $base['health_point'] = $metricValues['health'];
                        $base['resource_point'] = $metricValues['resource'];
                        $base['ethics_point'] = $metricValues['ethics'];
                        $base['adaptation_point'] = $metricValues['adaptation'];
                    } else {
                        $base['health_point'] = null;
                        $base['resource_point'] = null;
                        $base['ethics_point'] = null;
                        $base['adaptation_point'] = null;
                    }
                } else {
                    $base['health_point'] = null;
                    $base['resource_point'] = null;
                    $base['ethics_point'] = null;
                    $base['adaptation_point'] = null;
                }
            } elseif ($app->slug === 'way-startup') {
                // WS per-student stats from member data
                $member = WsMember::where('user_id', $s->id)->with('progress')->first();
                $base['startup_type'] = '-';
                if ($member) {
                    $completed = $member->progress->where('status', 'completed')->count();
                    $total = $member->progress->count();
                    $base['total'] = $total;
                    $base['completed'] = $completed;
                    $base['completion_rate'] = $total > 0 ? round(($completed / $total) * 100) : 0;
                    $base['avg_score'] = $member->points ?? 0;
                }
                $base['deadline'] = '-';
                $base['teacher_score'] = null;
            }
            return $base;
        })->values();

        // ── Module Stats — Computed from DB ─────────
        $totalSessions = 0;
        $totalCompleted = 0;
        $totalDuration = 0;
        $avgScore = 0;

        if ($app->slug === 'mission-way') {
            $totalSessions = MwSimulationSession::count();
            $totalCompleted = MwSimulationSession::where('status', 'completed')->count();
        } elseif ($app->slug === 'way-startup') {
            $totalSessions = WsSimulation::count();
            $totalCompleted = 0;
            // Count simulations that have any completed steps
            foreach (WsSimulation::with('steps')->get() as $wsSim) {
                $wsMembers = WsMember::where('application_id', $wsSim->application_id)->with('progress')->get();
                $hasCompleted = $wsMembers->contains(fn($m) =>
                    $m->progress->contains('status', 'completed')
                );
                if ($hasCompleted) $totalCompleted++;
            }
        }

        $moduleStats = collect();
        $sessionsByDay = collect();
        for ($d = 29; $d >= 0; $d--) {
            $date = now()->subDays($d)->format('Y-m-d');
            if ($app->slug === 'mission-way') {
                $sessionsByDay[$date] = MwSimulationSession::whereDate('created_at', $date)->count();
            } else {
                $sessionsByDay[$date] = 0;
            }
        }

        $data = [
            'app'              => $app,
            'user'             => $user,
            'missions'         => $missions,
            'startups'         => $startups,
            'total_missions'   => $missions->count(),
            'total_progress'   => $totalSessions,
            'total_completed'  => $totalCompleted,
            'total_sessions'   => $totalSessions,
            'total_duration'   => $totalDuration,
            'avg_score'        => $avgScore,
            'module_stats'     => $moduleStats,
            'user_stats'       => $userStats,
            'sessions_by_day'  => $sessionsByDay,
            'recent_sessions'  => collect(),
            // Assignment modal data — MW simulations with role counts
            'mw_simulations'   => RefSimulation::where('name', 'not like', 'Simülasyon #%')->orderBy('name')->get()->map(function ($sim) {
                $activeVersion = \DB::table('ref_simulation_versions')
                    ->where('simulation_id', $sim->id)
                    ->where('is_active', true)
                    ->first();
                $sim->role_count = $activeVersion
                    ? \DB::table('ref_simulation_version_roles')->where('simulation_version_id', $activeVersion->id)->count()
                    : 4; // default fallback
                return $sim;
            }),
            'ws_simulations'   => WsSimulation::where('name', 'not like', 'Simülasyon #%')->orderBy('name')->get(),
            'panel_students'   => $panelStudents ?? collect(),
            // Filtered: only students with active platform accounts (with grade info)
            'mw_students'      => isset($panelUserIds) ? User::whereIn('id', MwPlayer::whereIn('user_id', $panelUserIds)->whereNotNull('user_id')->pluck('user_id'))->orderBy('name')->get()->map(function($u) {
                $u->grade = \DB::table('class_user')
                    ->join('school_classes', 'school_classes.id', '=', 'class_user.class_id')
                    ->where('class_user.user_id', $u->id)
                    ->value('school_classes.grade_level');
                return $u;
            }) : collect(),
            'ws_students'      => isset($panelUserIds) ? User::whereIn('id', WsMember::whereIn('user_id', $panelUserIds)->whereNotNull('user_id')->pluck('user_id'))->orderBy('name')->get()->map(function($u) {
                $u->grade = \DB::table('class_user')
                    ->join('school_classes', 'school_classes.id', '=', 'class_user.class_id')
                    ->where('class_user.user_id', $u->id)
                    ->value('school_classes.grade_level');
                return $u;
            }) : collect(),
        ];

        // ── Anlık API ile ek veriler (MW) ──
        if ($app->slug === 'mission-way') {
            try {
                $mwConnector = app(\App\Connectors\MissionWayConnector::class);
                $data['objectives'] = collect($mwConnector->getObjectives());
                $data['mediaAssets'] = collect($mwConnector->getMediaAssets());
                $data['languages'] = collect($mwConnector->getLanguages());
                $data['simWingStats'] = $mwConnector->getSimulationWingStats();
                $data['simVersionRoles'] = collect($mwConnector->getSimVersionRoles());
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('MW API catalog fetch: ' . $e->getMessage());
                $data['objectives'] = collect();
                $data['mediaAssets'] = collect();
                $data['languages'] = collect();
                $data['simWingStats'] = null;
                $data['simVersionRoles'] = collect();
            }
        }

        // ── Anlık API ile ek veriler (WS) ──
        if ($app->slug === 'way-startup') {
            try {
                $wsConnector = app(\App\Connectors\WayStartupConnector::class);
                $data['stepQuestionAnswers'] = collect($wsConnector->getStepQuestionAnswers());
                $data['wsMembers'] = collect($wsConnector->getMembers());
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('WS API catalog fetch: ' . $e->getMessage());
                $data['stepQuestionAnswers'] = collect();
                $data['wsMembers'] = collect();
            }
        }

        return view('portal.reports.app', $data);
    }


    /**
     * Student detailed report (all apps).
     */
    public function studentReport(User $student)
    {
        $authUser = auth()->user();

        // Authorization checks
        if ($authUser->hasRole('student') && $authUser->id !== $student->id) {
            abort(403);
        }

        if ($authUser->hasRole('teacher')) {
            $classIds = $authUser->classes()->pluck('school_classes.id');
            $studentIds = DB::table('class_user')->whereIn('class_id', $classIds)->pluck('user_id');
            if (!$studentIds->contains($student->id)) {
                abort(403);
            }
        }

        $student->load(['roles', 'schools', 'classes.school', 'applications']);
        $reportData = $this->reportService->getStudentReport($student);

        // Determine which app to highlight in sidebar
        $activeApp = request('app');

        // Wings Points — accumulated theme-based scores from vega sessions
        try {
            $vegaUserId = $this->vegaReportService->resolveVegaUserId($student->id);
            $wingsPoints = $vegaUserId ? $this->vegaReportService->getWingsPoints($vegaUserId) : ['total_wings' => 0, 'categories' => []];
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Vega DB Student Report Error: ' . $e->getMessage());
            $wingsPoints = ['total_wings' => 0, 'categories' => []];
        }
        // Anlık API ile rozet verileri
        $wingBadges = collect();
        $premiumStatus = null;
        try {
            $vegaConnector = app(\App\Connectors\VegaConnector::class);
            $wingBadges = collect($vegaConnector->getWings());
            $premiumStatus = $vegaConnector->getPremiumStatus();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Vega API student enrichment: ' . $e->getMessage());
        }

        return view('portal.reports.student', [
            'student' => $student,
            'reportData' => $reportData,
            'apps' => Application::active()->ordered()->get(),
            'activeApp' => $activeApp,
            'wingsPoints' => $wingsPoints,
            'wingBadges' => $wingBadges,
            'premiumStatus' => $premiumStatus,
        ]);
    }

    /**
     * Class report (optionally filtered by app).
     */
    public function classReport(SchoolClass $class, ?Application $app = null)
    {
        $user = auth()->user();

        // Teacher must be assigned to the class
        if ($user->hasRole('teacher')) {
            if (!$user->classes()->where('school_classes.id', $class->id)->exists()) {
                abort(403);
            }
        }

        $class->load(['school', 'students', 'teachers']);
        $reportData = $this->reportService->getClassReport($class, $app);

        return view('portal.reports.class', [
            'class' => $class,
            'reportData' => $reportData,
            'selectedApp' => $app,
            'apps' => Application::active()->ordered()->get(),
        ]);
    }

    /* ── Detail Pages ────────────────────────────────── */

    /**
     * Mission WAY — Mission Detail — Figma F-41/42/62
     */
    public function missionDetail($id)
    {
        $user = auth()->user();
        $panelUserIds = $this->getScopedUserIds($user)->toArray();

        $simModel = RefSimulation::with(['versions.paths'])->find($id);

        if (!$simModel) {
            abort(404, 'Mission not found in real database.');
        }

        $sessions = MwSimulationSession::whereIn('simulation_version_id', $simModel->versions->pluck('id'))
            ->whereHas('players.player', function ($q) use ($panelUserIds) {
                $q->whereIn('user_id', $panelUserIds);
            })
            ->with(['players.player.user', 'players.role'])
            ->get();

        // Metric enrichment from ALL completed sessions
        $completedSessions = $sessions->where('status', 'completed');
        $enriched = $completedSessions->isEmpty() ? [] : $this->mwMetricService->aggregateSessionMetrics($completedSessions, $simModel->versions->first()?->id);
        $metricValues = $this->mwMetricService->getAllMetricValues($enriched);

        $completedCount = $completedSessions->count();
        $totalCount = $sessions->count();

        $mission = (object)[
            'id' => $simModel->id,
            'title' => $simModel->name ?? ('Simulation #' . $simModel->id),
            'status' => $completedCount > 0 ? 'Active' : 'Pending',
            'difficulty' => $simModel->difficulty ?? 'Normal',
            'created' => $simModel->created_at?->format('d.m.Y') ?? now()->format('d.m.Y'),
            'description' => $simModel->description ?? 'No description available.',
            'result' => $completedCount > 0
                ? "{$completedCount}/{$totalCount} session completed."
                : 'Awaiting completion data.',
            'completion_rate' => $totalCount > 0 ? round(($completedCount / $totalCount) * 100) : 0,
            'avg_score' => $completedCount > 0 ? round($completedSessions->avg('final_score') ?? 0) : 0,
            'health' => $metricValues['health'],
            'resource' => $metricValues['resource'],
            'ethics' => $metricValues['ethics'],
            'adaptation' => $metricValues['adaptation'],
            'image' => $simModel->background_image_path ?? null,
        ];

        $students = $sessions->flatMap->players->map(function ($sp) use ($enriched) {
            $u = $sp->player?->user ?? null;
            // Per-student metrics from the session they participated in
            $sessionMetrics = $sp->session?->final_metrics ?? [];
            $studentEnriched = !empty($sessionMetrics)
                ? $this->mwMetricService->enrichSessionMetrics($sessionMetrics, $sp->session?->simulation_version_id)
                : $enriched;
            $studentMetrics = $this->mwMetricService->getAllMetricValues($studentEnriched);

            return (object)[
                'name' => $u ? $u->name : ($sp->player?->name ?? 'Unknown'),
                'surname' => $u ? $u->surname : ($sp->player?->surname ?? ''),
                'role' => $sp->role?->name ?? 'Participant',
                'grade' => '-',
                'completed' => $sp->session?->status === 'completed' ? 1 : 0,
                'total_missions' => 1,
                'health' => $studentMetrics['health'],
                'resource' => $studentMetrics['resource'],
                'ethics' => $studentMetrics['ethics'],
                'adaptation' => $studentMetrics['adaptation'],
            ];
        })->unique(function ($s) { return $s->name . $s->surname; });

        // Group Flow: Build question cards from ALL completed sessions choices
        $questions = collect();
        if ($completedCount > 0) {
            $choices = \App\Models\MissionWay\MwPlayerChoice::whereIn('simulation_session_id', $completedSessions->pluck('id'))
                ->orderBy('id')
                ->get();

            // Group by path ID to find the most popular options
            $groupedChoices = $choices->groupBy('simulation_path_id');

            $questions = $groupedChoices->map(function ($pathChoices, $pathId) use ($completedCount) {
                $path = RefSimulationPath::with('childPaths')->find($pathId);
                
                // Get path text from translations (entity_type uses hyphen)
                $pathTranslation = RefTranslation::where('entity_type', 'simulation-path')
                    ->where('entity_id', $pathId)
                    ->first();
                $questionText = null;
                if ($pathTranslation && is_array($pathTranslation->fields)) {
                    $questionText = $pathTranslation->fields['question'] ?? $pathTranslation->fields['text'] ?? $pathTranslation->fields['name'] ?? null;
                }

                // Calculate unanimity based on the most frequent response
                $responseCounts = $pathChoices->countBy('selected_path_id');
                $mostFrequentCount = $responseCounts->max() ?? 0;
                $unanimity = $pathChoices->count() > 0 ? round(($mostFrequentCount / $pathChoices->count()) * 100) : null;
                
                // The option selected by the class is the most frequent one
                $classSelectedPathId = $responseCounts->keys()->first() ?? null;
                if ($mostFrequentCount > 0 && $pathChoices->count() > 0) {
                     $classSelectedPathId = $responseCounts->sortDesc()->keys()->first();
                }

                // Build option list from child paths
                $options = ($path?->childPaths ?? collect())->map(function ($child) use ($classSelectedPathId) {
                    $childTranslation = RefTranslation::where('entity_type', 'simulation-path')
                        ->where('entity_id', $child->id)
                        ->first();
                    $optionText = 'Option';
                    if ($childTranslation && is_array($childTranslation->fields)) {
                        $optionText = $childTranslation->fields['optionText'] ?? $childTranslation->fields['text'] ?? $childTranslation->fields['name'] ?? 'Option';
                    }
                    return (object)[
                        'text' => $optionText,
                        'selected' => $child->id === $classSelectedPathId,
                    ];
                });

                // Average numeric metrics from the most popular choice
                $popularChoices = $pathChoices->where('selected_path_id', $classSelectedPathId);
                $avgMetric = function ($key) use ($popularChoices) {
                    $vals = $popularChoices->map(function($c) use ($key) {
                       $v = $c->metrics_after[$key] ?? null;
                       return is_array($v) ? ($v['current'] ?? null) : $v; 
                    })->filter(fn($v) => is_numeric($v));
                    return $vals->count() > 0 ? round($vals->avg()) : null;
                };

                return (object)[
                    'question' => $questionText ?? 'Decision Point',
                    'unanimity' => $unanimity,
                    'options' => $options,
                    'health' => $avgMetric('health'),
                    'resource' => $avgMetric('resource'),
                    'ethics' => $avgMetric('ethics'),
                    'adaptation' => $avgMetric('adaptation'),
                ];
            })->values();
        }

        return view('portal.reports.mission-detail', compact('mission', 'students', 'questions'));
    }

    /**
     * Startup — Project Detail — Figma F-66/67/68
     */
    public function startupDetail($id)
    {
        $user = auth()->user();
        $panelUserIds = $this->getScopedUserIds($user)->toArray();

        $wsSim = WsSimulation::with('steps.stepQuestions.answers', 'steps.evaluations.member')->find($id);

        if (!$wsSim) {
            abort(404, 'Startup project not found.');
        }

        $stepsCollection = $wsSim->steps;

        // Real completion from member step_progress for this specific class
        $members = WsMember::where('application_id', $wsSim->application_id)
            ->whereIn('user_id', $panelUserIds)
            ->with(['user', 'progress', 'submissions'])
            ->get();
        $allStepProgress = $members->flatMap->progress;
        $stepsCompleted = $allStepProgress->where('status', 'completed')->count();

        $totalPoints = $stepsCollection->sum('points');
        $maxPoints = $stepsCollection->sum('max_score');

        // Per-step evaluation scores from normalized table
        $allEvaluations = WsStepEvaluation::whereIn('step_id', $stepsCollection->pluck('id'))->get();

        $project = (object)[
            'id' => $wsSim->id,
            'name' => $wsSim->name . ($wsSim->type ? ' / ' . $wsSim->type : ''),
            'steps_completed' => $stepsCompleted,
            'total_steps' => $stepsCollection->count(),
            'product_score' => $totalPoints,
            'max_score' => $maxPoints,
        ];

        $steps = $stepsCollection->map(function ($s) use ($allStepProgress, $allEvaluations) {
            // Check if any member completed this step
            $stepCompleted = $allStepProgress->contains(function ($sp) use ($s) {
                return $sp->step_external_id == $s->external_id && $sp->status === 'completed';
            });

            // Get AI evaluation from normalized table
            $stepEval = $allEvaluations->where('step_id', $s->id)->sortByDesc('attempt')->first();
            $aiScore = $stepEval->ai_total_score ?? $s->ai_score ?? 0;
            $aiMaxScore = $stepEval->ai_max_score ?? $s->max_score ?? 0;

            // Load questions with answers for this step
            $questionsWithAnswers = $s->stepQuestions->map(function ($q) {
                $latestAnswer = $q->answers->sortByDesc('attempt')->first();
                return (object)[
                    'question_text' => $q->question_text,
                    'max_score'     => $q->max_score,
                    'sort_order'    => $q->sort_order,
                    'user_answer'   => $latestAnswer->text_answer ?? null,
                    'ai_score'      => $latestAnswer->ai_score ?? 0,
                    'ai_max_score'  => $latestAnswer->ai_max_score ?? $q->max_score,
                    'ai_feedback'   => $latestAnswer->ai_feedback ?? null,
                ];
            });

            return (object)[
                'title' => $s->name,
                'responsible' => $s->responsible_name ?? '-',
                'ai_score' => $aiScore,
                'ai_max_score' => $aiMaxScore,
                'score' => $s->points ?? 0,
                'difficulty' => $s->difficulty ?? '-',
                'completed' => $stepCompleted,
                'overall_feedback' => $stepEval->ai_overall_feedback ?? null,
                'ai_coins' => $stepEval->ai_coins ?? 0,
                'questions' => $questionsWithAnswers,
            ];
        });

        $projectStepIds = $stepsCollection->pluck('external_id')->toArray();

        // Team members with step mapping, filtering to show only active members
        $team = $members->filter(function ($m) use ($projectStepIds, $stepsCollection) {
            if (!$m->user) return false;
            
            // Is the user listed as responsible for any step?
            $isResponsible = $stepsCollection->contains(function ($s) use ($m) {
                return !empty($s->responsible_name) && mb_strtolower(trim($s->responsible_name)) === mb_strtolower(trim($m->user->name));
            });

            // Or do they have any progress on this project's steps?
            $hasProgress = collect($m->progress)->contains(function ($p) use ($projectStepIds) {
                return in_array($p->step_external_id, $projectStepIds);
            });

            return $isResponsible || $hasProgress;
        })->map(function ($m) use ($stepsCollection) {
            // Find which steps this member is responsible for
            $responsibleSteps = $stepsCollection->filter(fn($s) => !empty($s->responsible_name) && mb_strtolower(trim($s->responsible_name)) === mb_strtolower(trim($m->user?->name ?? '')))
                ->pluck('name')
                ->join(', ');

            return (object)[
                'name' => $m->user?->name ?? '-',
                'surname' => $m->user?->surname ?? '',
                'steps' => $responsibleSteps ?: '-',
            ];
        })->values();

        // Extract submitted files and links from step_submissions
        $files = [];
        $links = [];
        foreach ($members as $member) {
            foreach ($member->submissions as $sub) {
                $stepIdx = $sub->step_external_id;
                if (!empty($sub->file_name)) {
                    $files[] = [
                        'step' => $stepIdx,
                        'name' => $sub->file_name ?? 'file',
                        'size' => $sub->file_size ?? '',
                        'url'  => $sub->file_url ?? '',
                        'status' => $sub->status ?? null,
                        'feedback' => $sub->feedback ?? null,
                        'points_earned' => $sub->points_earned ?? null,
                    ];
                }
                if (!empty($sub->link_url)) {
                    $links[] = [
                        'step' => $stepIdx,
                        'url'  => $sub->link_url ?? '',
                        'title' => $sub->link_title ?? null,
                        'platform' => $sub->link_platform ?? null,
                        'status' => $sub->status ?? null,
                    ];
                }
            }
        }

        // Ranking data (from reference app mockRankingData — backend ranking API is not yet live)
        // TODO: Replace with real API data when backend ranking endpoint is available
        $rankings = [
            (object)['rank' => 1, 'name' => 'Team Alpha', 'score' => 2850, 'is_current' => false],
            (object)['rank' => 2, 'name' => 'Team Beta', 'score' => 2650, 'is_current' => false],
            (object)['rank' => 3, 'name' => 'Team Gamma', 'score' => 2450, 'is_current' => false],
            (object)['rank' => 4, 'name' => 'Your Team', 'score' => 2350, 'is_current' => true],
            (object)['rank' => 5, 'name' => 'Team Delta', 'score' => 2200, 'is_current' => false],
            (object)['rank' => 6, 'name' => 'Team Epsilon', 'score' => 2100, 'is_current' => false],
            (object)['rank' => 7, 'name' => 'Team Zeta', 'score' => 1950, 'is_current' => false],
            (object)['rank' => 8, 'name' => 'Team Eta', 'score' => 1800, 'is_current' => false],
        ];

        return view('portal.reports.startup-detail', compact('project', 'steps', 'team', 'files', 'links', 'rankings'));
    }

    /**
     * WAY AI Coach — Question Detail — Figma F-71/F-74
     * Real Vega lecturer messages → question/answer/feedback timeline.
     */
    public function coachQuestions($id)
    {
        $student = User::findOrFail($id);
        try {
            $vegaUserId = $this->vegaReportService->resolveVegaUserId($student->id);
            $questions = $this->vegaReportService->getCoachFeedback($vegaUserId);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Vega DB Coach Questions Error: ' . $e->getMessage());
            $questions = collect();
        }

        return view('portal.reports.coach-questions', compact('student', 'questions'))
            ->with('activeApp', 'way-ai-coach');
    }


    /**
     * AJAX: Student enrichment data — Tier 2/3
     * Returns scenario breakdown, theme breakdown, score trend.
     */
    public function studentEnrichment(User $student)
    {
        try {
            $vegaUserId = $this->vegaReportService->resolveVegaUserId($student->id);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Vega DB Enrichment Error: ' . $e->getMessage());
            $vegaUserId = null;
        }

        if (!$vegaUserId) {
            return response()->json([
                'scenario_breakdown' => [],
                'theme_breakdown'    => [],
                'score_trend'        => [],
            ]);
        }

        return response()->json([
            'scenario_breakdown' => $this->vegaReportService->getScenarioBreakdown($vegaUserId),
            'theme_breakdown'    => $this->vegaReportService->getThemeBreakdown($vegaUserId),
            'score_trend'        => $this->vegaReportService->getScoreTrend($vegaUserId),
        ]);
    }

    /**
     * Per-app student detail: Role Galaxy (simulator)
     * Matches mobile RoleGalaxyScreen → 12 scenario cards with per-scenario breakdown.
     */
    public function roleGalaxyDetail(User $student)
    {
        $this->authorizeStudentAccess($student);
        $student->load(['roles', 'schools', 'classes']);

        try {
            $vegaUserId = $this->vegaReportService->resolveVegaUserId($student->id);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Vega DB Role Galaxy Detail Error: ' . $e->getMessage());
            $vegaUserId = null;
        }
        $sessions = collect();
        $scenarioBreakdown = collect();
        $stats = ['total_sessions' => 0, 'completed' => 0, 'avg_score' => null, 'total_duration' => 0];

        if ($vegaUserId) {
            $sessions = \App\Models\Vega\VegaDbSession::simulator()
                ->forUser($vegaUserId)
                ->with('simulatorSteps:id,session_id,created_at')
                ->orderByDesc('created_at')
                ->get();

            $stats = [
                'total_sessions' => $sessions->count(),
                'completed'      => $sessions->where('status', 'COMPLETED')->count(),
                'avg_score'      => $sessions->whereNotNull('score')->avg('score'),
                'total_duration' => $sessions->sum(fn($s) => $s->duration_seconds),
            ];
            $scenarioBreakdown = $this->vegaReportService->getScenarioBreakdown($vegaUserId);
        }

        return view('portal.reports.role-galaxy-detail', [
            'student'            => $student,
            'sessions'           => $sessions,
            'stats'              => $stats,
            'scenarioBreakdown'  => $scenarioBreakdown,
            'scenarioConfig'     => VegaReportService::getScenarioConfig(),
        ]);
    }

    /**
     * Per-app student detail: WAY AI Coach (lecturer + chatbot)
     * Matches mobile WayAICoachScreen → 13 theme cards + WayAICoachDetailScreen per-theme sessions.
     */
    public function wayAiCoachDetail(User $student)
    {
        $this->authorizeStudentAccess($student);
        $student->load(['roles', 'schools', 'classes']);

        try {
            $vegaUserId = $this->vegaReportService->resolveVegaUserId($student->id);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Vega DB Way AI Coach Detail Error: ' . $e->getMessage());
            $vegaUserId = null;
        }
        $sessions = collect();
        $themeBreakdown = collect();
        $stats = ['total_sessions' => 0, 'lecturer' => 0, 'chatbot' => 0, 'avg_score' => null, 'total_duration' => 0, 'total_messages' => 0];

        if ($vegaUserId) {
            try {
                $sessions = \App\Models\Vega\VegaDbSession::forUser($vegaUserId)
                    ->whereIn('module', ['lecturer', 'chatbot'])
                    ->withCount(['lecturerMessages', 'chatMessages'])
                    ->orderByDesc('created_at')
                    ->get();

                $stats = [
                    'total_sessions'  => $sessions->count(),
                    'lecturer'        => $sessions->where('module', 'lecturer')->count(),
                    'chatbot'         => $sessions->where('module', 'chatbot')->count(),
                    'avg_score'       => $sessions->whereNotNull('score')->avg('score'),
                    'total_duration'  => $sessions->sum(fn($s) => $s->duration_seconds),
                    'total_messages'  => $sessions->sum('lecturer_messages_count') + $sessions->sum('chat_messages_count'),
                ];
                $themeBreakdown = $this->vegaReportService->getThemeBreakdown($vegaUserId);
            } catch (\Exception $e) {
                // Handled gracefully below
            }
        }

        try {
            $wingsPoints = $vegaUserId ? $this->vegaReportService->getWingsPoints($vegaUserId) : ['total_wings' => 0, 'categories' => []];
        } catch (\Exception $e) {
            $wingsPoints = ['total_wings' => 0, 'categories' => []];
        }

        return view('portal.reports.way-ai-coach-detail', [
            'student'        => $student,
            'sessions'       => $sessions,
            'stats'          => $stats,
            'themeBreakdown' => $themeBreakdown,
            'themeConfig'    => VegaReportService::THEME_CONFIG,
            'wingsPoints'    => $wingsPoints,
        ]);
    }

    /**
     * Per-app student detail: Study Space (chatbot)
     * Matches mobile WorkspaceScreen chat workspace.
     */
    public function studySpaceDetail(User $student)
    {
        $this->authorizeStudentAccess($student);
        $student->load(['roles', 'schools', 'classes']);

        try {
            $vegaUserId = $this->vegaReportService->resolveVegaUserId($student->id);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Vega DB Study Space Detail Error: ' . $e->getMessage());
            $vegaUserId = null;
        }
        $sessions = collect();
        $themeBreakdown = collect();
        $stats = ['total_sessions' => 0, 'total_messages' => 0, 'total_duration' => 0];

        if ($vegaUserId) {
            $sessions = \App\Models\Vega\VegaDbSession::chatbot()
                ->forUser($vegaUserId)
                ->withCount('chatMessages')
                ->orderByDesc('created_at')
                ->get();

            $stats = [
                'total_sessions'  => $sessions->count(),
                'total_messages'  => $sessions->sum('chat_messages_count'),
                'total_duration'  => $sessions->sum(fn($s) => $s->duration_seconds),
            ];

            // Theme breakdown for chatbot sessions
            $themeBreakdown = $sessions->groupBy('theme')
                ->map(fn($items, $theme) => [
                    'theme'   => $theme ?: 'unknown',
                    'count'   => $items->count(),
                    'modules' => ['chatbot'],
                ])
                ->values();
        }

        return view('portal.reports.study-space-detail', [
            'student'        => $student,
            'sessions'       => $sessions,
            'stats'          => $stats,
            'themeBreakdown' => $themeBreakdown,
            'themeConfig'    => VegaReportService::THEME_CONFIG,
        ]);
    }

    /**
     * Individual session detail page.
     * Displays full timeline for simulator, or WhatsApp-style chat for lecturer/chatbot.
     */
    public function sessionDetail(string $sessionId)
    {
        try {
            $session = \App\Models\Vega\VegaDbSession::on('vega_db')
                ->with(['simulatorSteps', 'lecturerMessages', 'chatMessages'])
                ->findOrFail($sessionId);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Vega DB Session Detail Error: ' . $e->getMessage());
            abort(404, 'Session not found or Vega database is unreachable.');
        }

        $module = $session->module; // simulator, lecturer, chatbot

        // Resolve student from vega user_id
        $vegaUser = \App\Models\Vega\VegaDbUser::on('vega_db')->find($session->user_id);
        $student = null;
        if ($vegaUser?->email) {
            $student = User::where('email', $vegaUser->email)->first();
        }

        // Authorization: ensure the logged-in user has access to this student
        if ($student) {
            $this->authorizeStudentAccess($student);
        }

        // Simulator chart data
        $chartData = [];
        if ($module === 'simulator' && $session->simulatorSteps->count() > 0) {
            $chartData = $session->simulatorSteps->sortBy('turn')->map(fn($step) => [
                'turn'  => $step->turn,
                'score' => $step->score_after ?? 0,
            ])->values()->toArray();
        }

        // Token estimation for chat sessions
        $totalTokens = 0;
        if ($module === 'lecturer') {
            $totalTokens = $session->lecturerMessages->sum(fn($m) => (int) ceil(strlen($m->content ?? '') / 4));
        } elseif ($module === 'chatbot') {
            $totalTokens = $session->chatMessages->sum(fn($m) => (int) ceil(strlen($m->content ?? '') / 4));
        }

        return view('portal.reports.session-detail', [
            'session'    => $session,
            'module'     => $module,
            'student'    => $student,
            'chartData'  => $chartData,
            'totalTokens' => $totalTokens,
        ]);
    }

    /**
     * Authorization check for student access.
     */
    private function authorizeStudentAccess(User $student): void
    {
        $authUser = auth()->user();

        if ($authUser->hasRole('student') && $authUser->id !== $student->id) {
            abort(403);
        }

        if ($authUser->hasRole('teacher')) {
            $classIds = $authUser->classes()->pluck('school_classes.id');
            $studentIds = DB::table('class_user')->whereIn('class_id', $classIds)->pluck('user_id');
            if (!$studentIds->contains($student->id)) {
                abort(403);
            }
        }
    }

    /* ── Helpers ─────────────────────────────────────── */

    private function getScopedUserIds(User $user): \Illuminate\Support\Collection
    {
        // Admin & super-admin see ALL students
        if ($user->hasAnyRole(['admin', 'super-admin'])) {
            return DB::table('users')->pluck('id');
        }

        if ($user->hasAnyRole(['school-admin', 'school-principal'])) {
            $schoolIds = $user->schools()->pluck('schools.id');
            return DB::table('school_user')
                ->whereIn('school_id', $schoolIds)
                ->pluck('user_id')
                ->unique();
        }

        if ($user->hasRole('teacher')) {
            $classIds = $user->classes()->pluck('school_classes.id');
            return DB::table('class_user')
                ->whereIn('class_id', $classIds)
                ->pluck('user_id')
                ->unique();
        }

        // Student
        return collect([$user->id]);
    }

    /**
     * Compute the global radar chart metrics (Health, Resource, Ethics, Adaptation)
     */
    private function getGlobalRadarMetrics(\Illuminate\Support\Collection $userIds): array
    {
        $mwPlayerIds = MwPlayer::whereIn('user_id', $userIds)->pluck('id');
        $sessions = MwSimulationSession::whereIn('status', ['completed', 'evaluated'])
            ->whereHas('players', function($q) use ($mwPlayerIds) {
                $q->whereIn('player_id', $mwPlayerIds);
            })->get();

        if ($sessions->isEmpty()) {
            return ['health' => 0, 'resource' => 0, 'ethics' => 0, 'adaptation' => 0];
        }

        $enriched = $this->mwMetricService->aggregateSessionMetrics($sessions);
        return $this->mwMetricService->getAllMetricValues($enriched);
    }

    /**
     * Fetch a cross-app timeline of recent activities (completions, joins, evals).
     */
    private function getRecentActivities(\Illuminate\Support\Collection $userIds, int $limit = 5): \Illuminate\Support\Collection
    {
        $activities = collect();

        // 1. Mission WAY events (completed sessions)
        $mwPlayerIds = MwPlayer::whereIn('user_id', $userIds)->pluck('id');
        $mwSessions = MwSimulationSession::with('players.player.user')
            ->whereIn('status', ['completed', 'evaluated'])
            ->whereHas('players', function($q) use ($mwPlayerIds) {
                $q->whereIn('player_id', $mwPlayerIds);
            })
            ->orderByDesc('updated_at')
            ->take($limit)
            ->get();

        foreach ($mwSessions as $s) {
            $student = $s->players->first()?->player?->user;
            if (!$student) continue;
            $activities->push([
                'type' => 'mission',
                'app' => 'Mission WAY',
                'student' => $student,
                'action' => 'completed a simulation',
                'detail' => 'Score: ' . ($s->final_score ?? 0),
                'date' => $s->updated_at,
                'color' => '#3B82F6' // Blue
            ]);
        }

        // 2. Way Startup events (steps evaluated)
        $wsMembers = WsMember::with('user')->whereIn('user_id', $userIds)->get();
        if ($wsMembers->isNotEmpty()) {
            foreach ($wsMembers as $m) {
                $progress = collect($m->step_progress ?? []);
                $recent = $progress->where('status', 'completed')->sortByDesc('updated_at')->take(2);
                foreach ($recent as $step) {
                    $activities->push([
                        'type' => 'startup',
                        'app' => 'Way Startup',
                        'student' => $m->user,
                        'action' => 'completed Step ' . ($step['step_number'] ?? '?'),
                        'detail' => 'Startup: ' . ($m->team_name ?? 'Unknown'),
                        'date' => \Carbon\Carbon::parse($step['updated_at'] ?? now()),
                        'color' => '#10B981' // Green
                    ]);
                }
            }
        }

        return $activities->sortByDesc('date')->take($limit)->values();
    }

    /**
     * Generate gamified leaderboards based on all tracking data.
     */
    private function generateLeaderboards(\Illuminate\Support\Collection $userIds): array
    {
        $users = User::whereIn('id', $userIds)->get()->keyBy('id');
        
        $mwPlayerIds = MwPlayer::whereIn('user_id', $userIds)->pluck('id');
        $sessions = MwSimulationSession::whereIn('status', ['completed', 'evaluated'])
            ->whereHas('players', function($q) use ($mwPlayerIds) {
                $q->whereIn('player_id', $mwPlayerIds);
            })
            ->with('players.player')
            ->get();

        $metricsByUser = [];
        foreach ($sessions as $s) {
            foreach ($s->players as $sp) {
                $userId = $sp->player?->user_id;
                if (!$userId) continue;

                if (!isset($metricsByUser[$userId])) {
                    $metricsByUser[$userId] = ['health'=>[], 'resource'=>[], 'ethics'=>[], 'adaptation'=>[], 'scores'=>[]];
                }
                
                $metricsByUser[$userId]['scores'][] = $s->final_score ?? 0;
                
                $enriched = $this->mwMetricService->enrichSessionMetrics($s->final_metrics ?? []);
                $vals = $this->mwMetricService->getAllMetricValues($enriched);
                if ($vals['health'] > 0) $metricsByUser[$userId]['health'][] = $vals['health'];
                if ($vals['resource'] > 0) $metricsByUser[$userId]['resource'][] = $vals['resource'];
                if ($vals['ethics'] > 0) $metricsByUser[$userId]['ethics'][] = $vals['ethics'];
                if ($vals['adaptation'] > 0) $metricsByUser[$userId]['adaptation'][] = $vals['adaptation'];
            }
        }

        // Aggregate to averages
        $aggregated = [];
        foreach ($metricsByUser as $uid => $data) {
            $aggregated[$uid] = [
                'user' => $users->get($uid),
                'score' => empty($data['scores']) ? 0 : round(array_sum($data['scores']) / count($data['scores'])),
                'health' => empty($data['health']) ? 0 : round(array_sum($data['health']) / count($data['health'])),
                'resource' => empty($data['resource']) ? 0 : round(array_sum($data['resource']) / count($data['resource'])),
                'ethics' => empty($data['ethics']) ? 0 : round(array_sum($data['ethics']) / count($data['ethics'])),
                'adaptation' => empty($data['adaptation']) ? 0 : round(array_sum($data['adaptation']) / count($data['adaptation'])),
            ];
        }
        $aggregated = collect($aggregated);

        return [
            'top_score' => $aggregated->sortByDesc('score')->take(5)->values(),
            'top_ethics' => $aggregated->sortByDesc('ethics')->take(5)->values(),
            'top_adaptation' => $aggregated->sortByDesc('adaptation')->take(5)->values(),
        ];
    }

    /**
     * Get usage trend for the last 30 days (completions per day).
     */
    private function getUsageTrend30Days(\Illuminate\Support\Collection $userIds): array
    {
        $days = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = \Carbon\Carbon::now()->subDays($i)->format('Y-m-d');
            $days[$date] = 0;
        }

        $mwPlayerIds = MwPlayer::whereIn('user_id', $userIds)->pluck('id');
        $mwSessions = MwSimulationSession::whereIn('status', ['completed', 'evaluated'])
            ->whereHas('players', function($q) use ($mwPlayerIds) {
                $q->whereIn('player_id', $mwPlayerIds);
            })
            ->where('updated_at', '>=', now()->subDays(30))
            ->get();

        foreach ($mwSessions as $s) {
            $d = $s->updated_at->format('Y-m-d');
            if (isset($days[$d])) {
                $days[$d]++;
            }
        }

        // Format dates as basic strings
        $formattedDates = array_map(function($date) {
            return \Carbon\Carbon::parse($date)->format('M d');
        }, array_keys($days));

        return [
            'labels' => $formattedDates,
            'data' => array_values($days),
        ];
    }

    /**
     * Group users into score brackets.
     */
    private function getScoreDistribution(\Illuminate\Support\Collection $userIds): array
    {
        $mwPlayerIds = MwPlayer::whereIn('user_id', $userIds)->pluck('id');
        $sessions = MwSimulationSession::whereIn('status', ['completed', 'evaluated'])
            ->whereHas('players', function($q) use ($mwPlayerIds) {
                $q->whereIn('player_id', $mwPlayerIds);
            })
            ->with('players')
            ->get();

        $scoresByUser = [];
        foreach ($sessions as $s) {
            foreach ($s->players as $sp) {
                $userId = $sp->player?->user_id;
                if (!$userId) continue;
                if (!isset($scoresByUser[$userId])) $scoresByUser[$userId] = [];
                $scoresByUser[$userId][] = $s->final_score ?? 0;
            }
        }

        $distribution = [
            '0-20' => 0,
            '21-40' => 0,
            '41-60' => 0,
            '61-80' => 0,
            '81-100' => 0,
        ];

        foreach ($scoresByUser as $uid => $scores) {
            $avg = count($scores) > 0 ? round(array_sum($scores) / count($scores)) : 0;
            if ($avg <= 20) $distribution['0-20']++;
            elseif ($avg <= 40) $distribution['21-40']++;
            elseif ($avg <= 60) $distribution['41-60']++;
            elseif ($avg <= 80) $distribution['61-80']++;
            else $distribution['81-100']++;
        }

        return [
            'labels' => ['0-20', '21-40', '41-60', '61-80', '81-100'],
            'data' => array_values($distribution),
        ];
    }

    /**
     * Ranks most popular simulations and returns top 5 labels and counts.
     */
    private function getModulePopularity(\Illuminate\Support\Collection $userIds): array
    {
        $mwPlayerIds = MwPlayer::whereIn('user_id', $userIds)->pluck('id');
        $sessions = MwSimulationSession::whereHas('players', function($q) use ($mwPlayerIds) {
                $q->whereIn('player_id', $mwPlayerIds);
            })
            ->with(['version.simulation'])
            ->get();

        $counts = [];
        foreach ($sessions as $s) {
            $sim = $s->version?->simulation;
            if (!$sim) continue;

            $translation = RefTranslation::where('entity_type', 'ref_simulations')
                ->where('entity_id', $sim->id)
                ->first();
                
            $name = $translation->fields['name'] ?? 'Simulation ' . $sim->id;
            
            if (!isset($counts[$name])) $counts[$name] = 0;
            $counts[$name]++;
        }

        arsort($counts);
        $top5 = array_slice($counts, 0, 5, true);

        return [
            'labels' => array_keys($top5),
            'data' => array_values($top5),
        ];
    }

    /**
     * Phase 3: Gather massive per-app dashboards for the Command Center.
     */
    private function getPerAppDeepDashboards(\Illuminate\Support\Collection $userIds): array
    {
        $dashboards = [];
        $apps = Application::active()->ordered()->get();
        if ($apps->isEmpty()) return [];

        $vegaUserMap = [];
        try {
            $vegaUserMap = $this->vegaReportService->resolveVegaUserIds($userIds);
        } catch (\Exception $e) {}
        $panelUsers = User::whereIn('id', array_keys($vegaUserMap))->get()->keyBy('id');

        $mwPlayerIds = MwPlayer::whereIn('user_id', $userIds)->pluck('id');
        $wsMembers = WsMember::whereIn('user_id', $userIds)->with('user')->get();

        foreach ($apps as $app) {
            $slug = $app->slug;
            $charts = [
                'completion' => ['labels' => ['Completed', 'In Progress'], 'data' => [0, 0]],
                'scores' => ['labels' => ['High', 'Med', 'Low'], 'data' => [0, 0, 0]],
                'activity' => ['labels' => [], 'data' => []],
            ];
            $lists = [
                'top' => collect(),
                'needs_attention' => collect(),
                'recent' => collect(),
            ];

            if (in_array($slug, ['role-galaxy', 'way-ai-coach', 'study-space'])) {
                // Vega Apps
                $report = match ($slug) {
                    'role-galaxy'  => $this->vegaReportService->getRoleGalaxyReport($vegaUserMap, $panelUsers),
                    'way-ai-coach' => $this->vegaReportService->getWayAiCoachReport($vegaUserMap, $panelUsers),
                    'study-space'  => $this->vegaReportService->getStudySpaceReport($vegaUserMap, $panelUsers),
                };

                $completed = $report['total_completed'] ?? 0;
                $inProgress = max(0, ($report['total_sessions'] ?? 0) - $completed);
                $charts['completion']['data'] = [$completed, $inProgress];

                if (!empty($report['sessions_by_day']) && count($report['sessions_by_day']) > 0) {
                    $charts['activity']['labels'] = collect($report['sessions_by_day'])->keys()->toArray();
                    $charts['activity']['data'] = collect($report['sessions_by_day'])->values()->toArray();
                }

                $uStats = collect($report['user_stats'] ?? []);
                // Score distribution for Vega Apps
                $high=0; $med=0; $low=0;
                foreach($uStats as $u) {
                    $score = $u['avg_score'] ?? ($u['interaction_count'] ?? 0);
                    if ($score > 70) $high++;
                    elseif ($score > 40) $med++;
                    else $low++;
                }
                $charts['scores']['data'] = [$high, $med, $low];

                // Lists
                $lists['top'] = $uStats->sortByDesc('total_duration')->take(5)->map(function($u) {
                    return ['name' => $u['user']->name ?? '?', 'detail' => ($u['total_duration'] ?? 0).' sec'];
                });
                $lists['needs_attention'] = $uStats->where('alert', true)->take(5)->map(function($u) {
                    return ['name' => $u['user']->name ?? '?', 'detail' => 'Low Interaction'];
                });
                // We'll mock 5 recent entries based on generic dates since VegaSummary lacks raw sessions array
                for($i=1; $i<=5; $i++) {
                    $lists['recent']->push(['user' => 'Session #'.rand(100,999), 'action' => 'Activity recorded', 'date' => now()->subHours($i)->diffForHumans()]);
                }

            } elseif ($slug === 'mission-way') {
                // Mission WAY
                $sessions = MwSimulationSession::whereIn('status', ['completed', 'evaluated'])
                    ->whereHas('players', function($q) use ($mwPlayerIds) { $q->whereIn('player_id', $mwPlayerIds); })
                    ->with('players.player.user')->get();

                $completed = $sessions->count();
                $charts['completion']['data'] = [$completed, 0];

                $high=0; $med=0; $low=0;
                $days = [];
                foreach($sessions as $s) {
                    $score = $s->final_score ?? 0;
                    if ($score > 70) $high++; elseif ($score > 40) $med++; else $low++;
                    $d = $s->updated_at->format('M d');
                    if (!isset($days[$d])) $days[$d] = 0;
                    $days[$d]++;
                }
                $charts['scores']['data'] = [$high, $med, $low];
                $charts['activity']['labels'] = array_keys($days);
                $charts['activity']['data'] = array_values($days);

                // Lists
                $sorted = $sessions->sortByDesc('final_score');
                $lists['top'] = $sorted->take(5)->map(function($s) {
                    return ['name' => $s->players->first()?->player?->user?->name ?? 'Unknown', 'detail' => 'Score: '.$s->final_score];
                });
                $lists['needs_attention'] = $sorted->reverse()->take(5)->map(function($s) {
                    return ['name' => $s->players->first()?->player?->user?->name ?? 'Unknown', 'detail' => 'Score: '.$s->final_score];
                });
                $lists['recent'] = $sessions->sortByDesc('updated_at')->take(5)->map(function($s) {
                    return ['user' => $s->players->first()?->player?->user?->name ?? 'Unknown', 'action' => 'Completed Simulation', 'date' => $s->updated_at->diffForHumans()];
                });

            } elseif ($slug === 'way-startup') {
                // Way Startup
                $completed = 0; $inProgress = 0;
                $days = [];
                $high=0; $med=0; $low=0;
                $recent = collect();

                foreach($wsMembers as $m) {
                    $prog = collect($m->step_progress ?? []);
                    $c = $prog->where('status', 'completed')->count();
                    if ($c > 0) $completed += $c;
                    $high += $c; // Mock score

                    foreach($prog->where('status', 'completed') as $p) {
                        $d = \Carbon\Carbon::parse($p['updated_at'] ?? now())->format('M d');
                        if (!isset($days[$d])) $days[$d] = 0;
                        $days[$d]++;
                        $recent->push(['user' => $m->user?->name ?? '?', 'action' => 'Step '.($p['step_number']??''), 'date' => \Carbon\Carbon::parse($p['updated_at'] ?? now())->diffForHumans(), 'raw' => \Carbon\Carbon::parse($p['updated_at'] ?? now())]);
                    }
                }
                $charts['completion']['data'] = [$completed, $wsMembers->count()];
                $charts['scores']['data'] = [$high, $med, $low];
                $charts['activity']['labels'] = array_keys($days);
                $charts['activity']['data'] = array_values($days);

                $lists['top'] = $wsMembers->take(5)->map(fn($m) => ['name' => $m->user?->name ?? '?', 'detail' => $m->team_name]);
                $lists['needs_attention'] = collect([['name' => 'General', 'detail' => 'Monitoring active']]);
                $lists['recent'] = $recent->sortByDesc('raw')->take(5)->values();
            }

            $dashboards[] = [
                'app' => $app,
                'charts' => $charts,
                'lists' => $lists
            ];
        }

        return $dashboards;
    }

    /**
     * AJAX: Search students by name for the top bar search.
     */
    public function searchStudents()
    {
        $q = request('q', '');
        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $allowedIds = $this->getScopedUserIds(auth()->user());

        $students = User::whereIn('id', $allowedIds)
            ->where(function ($query) use ($q) {
                $query->where('name', 'LIKE', "%{$q}%")
                      ->orWhere('surname', 'LIKE', "%{$q}%")
                      ->orWhere('email', 'LIKE', "%{$q}%");
            })
            ->take(10)
            ->get(['id', 'name', 'surname', 'email']);

        return response()->json($students->map(fn($s) => [
            'id'     => $s->id,
            'name'   => $s->name . ' ' . $s->surname,
            'email'  => $s->email,
            'url'    => route('portal.reports.student', $s->id),
            'initials' => strtoupper(substr($s->name, 0, 1) . substr($s->surname ?? '', 0, 1)),
        ]));
    }
}
