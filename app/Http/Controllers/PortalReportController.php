<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\SchoolClass;
use App\Models\User;
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
        } elseif ($user->hasRole('teacher')) {
            // Teacher sees class-level overview
            $data['myClasses'] = $user->classes()
                ->with('school')
                ->withCount('students')
                ->get();
            $data['apps'] = Application::active()->ordered()->get();
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
     * Mission WAY, Way Startup: fixture data.
     */
    public function appReport(Application $app)
    {
        $user = auth()->user();
        $panelUserIds = $this->getScopedUserIds($user);

        // ── VEGA APPS: Direct SQL from Vega remote DB ──────────────
        if (in_array($app->slug, ['role-galaxy', 'way-ai-coach', 'study-space'])) {
            $vegaUserMap = $this->vegaReportService->resolveVegaUserIds($panelUserIds);

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

            return view('portal.reports.app', $data);
        }

        // ── NON-VEGA APPS: Mock data (mission-way, way-startup) ────
        $mockStudents = collect([
            (object)['id'=>901,'name'=>'Elif','surname'=>'Demir','avatar'=>null,'classes'=>collect([(object)['name'=>'9-A']])],
            (object)['id'=>902,'name'=>'Ahmet','surname'=>'Çelik','avatar'=>null,'classes'=>collect([(object)['name'=>'10-B']])],
            (object)['id'=>903,'name'=>'Fatma','surname'=>'Şahin','avatar'=>null,'classes'=>collect([(object)['name'=>'9-A']])],
            (object)['id'=>904,'name'=>'Emre','surname'=>'Aydın','avatar'=>null,'classes'=>collect([(object)['name'=>'11-C']])],
            (object)['id'=>905,'name'=>'Selin','surname'=>'Öztürk','avatar'=>null,'classes'=>collect([(object)['name'=>'10-B']])],
            (object)['id'=>906,'name'=>'Burak','surname'=>'Yılmaz','avatar'=>null,'classes'=>collect([(object)['name'=>'9-A']])],
            (object)['id'=>907,'name'=>'Cansu','surname'=>'Koç','avatar'=>null,'classes'=>collect([(object)['name'=>'11-C']])],
            (object)['id'=>908,'name'=>'Deniz','surname'=>'Arslan','avatar'=>null,'classes'=>collect([(object)['name'=>'10-B']])],
        ]);

        // ── MISSION WAY: Figma F-38 Assignments mock data ──────────
        $missions = collect();
        if ($app->slug === 'mission-way') {
            $missionNames = [
                'The Mystery of Göbeklitepe',
                "The Sphinx's Code",
                'The Trojan Horse Plan',
                'Reinvent the Machine',
                'After the Earthquake',
                'The Village Choice',
                'The Track Dilemma',
                'The Quantum Lab Experiment',
                'The Dinosaur Fossil',
                'The Science Fair Decision',
            ];
            foreach ($missionNames as $idx => $name) {
                $studentSlice = $mockStudents->random(rand(3, 5));
                $hp = $idx === 1 || $idx === 4 ? null : rand(35, 100);
                $rp = $idx === 1 || $idx === 5 ? null : rand(55, 100);
                $ep = $idx === 1 || $idx === 5 ? null : rand(40, 100);
                $ap = $idx === 1 || $idx === 2 || $idx === 3 || $idx === 5 || $idx === 7 || $idx === 9 ? null : rand(60, 100);
                $missions->push((object)[
                    'id'             => $idx + 1,
                    'name'           => $name,
                    'students'       => $studentSlice,
                    'assigned_date'  => '01/01/2026',
                    'deadline'       => '03/01/2026',
                    'health_point'   => $hp,
                    'resource_point' => $rp,
                    'ethics_point'   => $ep,
                    'adaptation_point' => $ap,
                    'health_trend'   => $hp !== null ? ($hp < 50 ? 'down' : 'up') : null,
                    'resource_trend' => $rp !== null ? ($rp < 60 ? 'down' : 'up') : null,
                    'ethics_trend'   => $ep !== null ? ($ep < 50 ? 'down' : 'up') : null,
                    'adaptation_trend' => $ap !== null ? ($ap >= 0 ? 'up' : 'down') : null,
                ]);
            }
        }

        // ── WAY STARTUP: Figma F-4 Assignments mock data ───────────
        $startups = collect();
        if ($app->slug === 'way-startup') {
            $startupData = [
                ['SmartClass',   'Edtech',           2, 12, 150, 1500, null,   'in_progress'],
                ['VitaCare',     'Healthcare Tech',   0, 12,   0, 1500, null,   'not_started'],
                ['StudyFund',    'Fintech',           3, 12, 450, 1500, null,   'in_progress'],
                ['TrendBox',     'E-commerce',        0, 12,1100, 1500, 'Score','completed'],
                ['FutureBot',    'Robotics',          0, 12,   0, 1500, null,   'not_started'],
                ['DreamVR',      'Virtual Reality',   0, 12, 750, 1500, null,   'completed'],
                ['LifeCheck',    'Healthcare Tech',   6, 12, 600, 1500, null,   'in_progress'],
                ['SenseFit',     'Wearable Tech',     0, 12,1300, 1500, 'Score','completed'],
                ['EasyTrip',     'Travel Management', 0, 12, 350, 1500, null,   'not_started'],
                ['SafeCore',     'Cybersecurity',    11, 12,1300, 1500, null,   'in_progress'],
                ['DialogAI',     'Conversational AI', 0, 12,   0, 1500, null,   'not_started'],
                ['TokenLab',     'Blockchain',        0, 12,1200, 1500, null,   'completed'],
                ['ShopNest',     'E-commerce',        2, 12, 300, 1500, null,   'in_progress'],
                ['TrustNet',     'Cybersecurity',     0, 12,1450, 1500, 'Score','completed'],
                ['Learnify',     'Edtech',            0, 12,   0, 1500, null,   'not_started'],
            ];
            $typeIcons = [
                'Edtech' => '📚', 'Healthcare Tech' => '🏥', 'Fintech' => '💰',
                'E-commerce' => '🛒', 'Robotics' => '🤖', 'Virtual Reality' => '🎮',
                'Wearable Tech' => '⌚', 'Travel Management' => '✈️',
                'Cybersecurity' => '🔒', 'Conversational AI' => '💬', 'Blockchain' => '🔗',
            ];
            foreach ($startupData as $idx => $row) {
                $studentSlice = $mockStudents->random(rand(2, 4));
                $deadlineOverdue = in_array($idx, [9, 12, 14]);
                $startups->push((object)[
                    'id'            => $idx + 1,
                    'name'          => $row[0],
                    'type'          => $row[1],
                    'type_icon'     => $typeIcons[$row[1]] ?? '📁',
                    'students'      => $studentSlice,
                    'deadline'      => $deadlineOverdue ? now()->subDays(rand(10,50))->format('m/d/Y') : '03/16/2026',
                    'deadline_overdue' => $deadlineOverdue,
                    'step_completed'=> $row[2],
                    'step_total'    => $row[3],
                    'system_point'  => $row[4],
                    'max_point'     => $row[5],
                    'teacher_point' => $row[6],
                    'status'        => $row[7],
                ]);
            }
        }

        // ── USER STATS for Performance tab (non-Vega apps) ─────────
        $userStats = $mockStudents->map(function($s, $i) use ($app) {
            $base = [
                'user' => $s,
                'total' => rand(4,8),
                'completed' => rand(1,5),
                'completion_rate' => rand(30,95),
                'avg_score' => rand(45,92) + rand(0,9)/10,
                'total_duration' => rand(1800, 14400),
            ];
            if ($app->slug === 'mission-way') {
                $base['health_point'] = rand(60,100);
                $base['resource_point'] = rand(40,95);
                $base['ethics_point'] = rand(50,90);
                $base['adaptation_point'] = rand(35,88);
            } elseif ($app->slug === 'way-startup') {
                $base['startup_type'] = ['Technology','Health','Education','Finance'][$i % 4];
                $base['deadline'] = now()->addDays(rand(5,30))->format('d.m.Y');
                $base['teacher_score'] = rand(60,95);
            }
            return $base;
        })->values();

        $moduleStats = collect([
            'simulation' => ['type'=>'simulation','total'=>12,'completed'=>8,'in_progress'=>3,'avg_score'=>72.5,'avg_duration'=>1800],
            'step'       => ['type'=>'step','total'=>18,'completed'=>14,'in_progress'=>3,'avg_score'=>68.0,'avg_duration'=>900],
            'practice'   => ['type'=>'practice','total'=>8,'completed'=>5,'in_progress'=>2,'avg_score'=>81.2,'avg_duration'=>1200],
        ]);

        $sessionsByDay = collect();
        for ($d = 29; $d >= 0; $d--) {
            $date = now()->subDays($d)->format('Y-m-d');
            $sessionsByDay[$date] = rand(0, 8);
        }

        $data = [
            'app'              => $app,
            'user'             => $user,
            'missions'         => $missions,
            'startups'         => $startups,
            'total_missions'   => 24,
            'total_progress'   => 38,
            'total_completed'  => 27,
            'total_sessions'   => 64,
            'total_duration'   => 45600,
            'avg_score'        => 74.3,
            'module_stats'     => $moduleStats,
            'user_stats'       => $userStats,
            'sessions_by_day'  => $sessionsByDay,
            'recent_sessions'  => collect(),
        ];

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
        $vegaUserId = $this->vegaReportService->resolveVegaUserId($student->id);
        $wingsPoints = $vegaUserId ? $this->vegaReportService->getWingsPoints($vegaUserId) : ['total_wings' => 0, 'categories' => []];

        return view('portal.reports.student', [
            'student' => $student,
            'reportData' => $reportData,
            'apps' => Application::active()->ordered()->get(),
            'activeApp' => $activeApp,
            'wingsPoints' => $wingsPoints,
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
        $mission = (object)[
            'id' => $id, 'title' => 'After the Earthquake',
            'status' => 'Completed', 'difficulty' => 'Easy', 'created' => '28.02.2026',
            'description' => 'In this mission, students will learn and practice the digital map creation process.',
            'result' => 'The people willingly carry stones and repair walls with the belief that "salvation is near." However, the difference between the reinforcement time (35 min) and the door endurance time determines the lifespan of the lie. If reinforcement does not arrive on time, the people will open the doors.',
            'completion_rate' => 72, 'avg_score' => 74.3,
            'health' => 75, 'resource' => 40, 'ethics' => 85, 'adaptation' => 100,
            'image' => 'https://images.unsplash.com/photo-1573648952826-b4f5e09c7370?w=1200&h=400&fit=crop',
        ];
        $students = collect([
            (object)['name'=>'Chance','surname'=>'Rhiel Madsen','role'=>'Diplomat','grade'=>11,'completed'=>8,'total_missions'=>8,'health'=>7,'resource'=>8,'ethics'=>5,'adaptation'=>6],
            (object)['name'=>'Emery','surname'=>'Dorwart','role'=>'Safety','grade'=>12,'completed'=>12,'total_missions'=>12,'health'=>9,'resource'=>12,'ethics'=>8,'adaptation'=>3],
            (object)['name'=>'Kadin','surname'=>'Septimus','role'=>'Logistics','grade'=>10,'completed'=>5,'total_missions'=>5,'health'=>3,'resource'=>5,'ethics'=>2,'adaptation'=>5],
            (object)['name'=>'Alena','surname'=>'Rosser','role'=>'Medic','grade'=>9,'completed'=>7,'total_missions'=>7,'health'=>4,'resource'=>6,'ethics'=>7,'adaptation'=>5],
        ]);
        $questions = collect([
            (object)[
                'question' => 'The earthquake has started! What should you do?',
                'unanimity' => 75, 'health' => 45, 'resource' => 70, 'ethics' => 65, 'adaptation' => 100,
                'options' => collect([
                    (object)['text' => 'Get under the table (DUCK-COVER-HOLD ON)', 'correct' => true, 'selected' => true],
                    (object)['text' => 'Run Outside', 'correct' => false, 'selected' => false],
                    (object)['text' => 'Take the elevator down', 'correct' => false, 'selected' => false],
                ]),
            ],
            (object)[
                'question' => 'The earthquake has started! What should you do?',
                'unanimity' => 100, 'health' => 45, 'resource' => 70, 'ethics' => 65, 'adaptation' => 100,
                'options' => collect([
                    (object)['text' => 'Get under the table (DUCK-COVER-HOLD ON)', 'correct' => true, 'selected' => false],
                    (object)['text' => 'Run Outside', 'correct' => false, 'selected' => false],
                    (object)['text' => 'Take the elevator down', 'correct' => false, 'selected' => true],
                ]),
            ],
            (object)[
                'question' => 'The earthquake has started! What should you do?',
                'unanimity' => 75, 'health' => 45, 'resource' => 70, 'ethics' => 65, 'adaptation' => 100,
                'options' => collect([
                    (object)['text' => 'Get under the table (DUCK-COVER-HOLD ON)', 'correct' => true, 'selected' => false],
                    (object)['text' => 'Run Outside', 'correct' => false, 'selected' => true],
                    (object)['text' => 'Take the elevator down', 'correct' => false, 'selected' => false],
                ]),
            ],
        ]);
        return view('portal.reports.mission-detail', compact('mission', 'students', 'questions'));
    }

    /**
     * Startup — Project Detail — Figma F-66/67/68
     */
    public function startupDetail($id)
    {
        $project = (object)['id'=>$id,'name'=>'StudyFund / Fintech','steps_completed'=>6,'total_steps'=>12,'product_score'=>120,'max_score'=>2500];
        $steps = collect([
            (object)['title'=>'Team Formation & Roles','responsible'=>'John Doe','ai_score'=>150,'score'=>150,'difficulty'=>'Easy','completed'=>true],
            (object)['title'=>'Idea Generation','responsible'=>'Sophia Wilson','ai_score'=>150,'score'=>150,'difficulty'=>'Easy','completed'=>true],
            (object)['title'=>'User Research','responsible'=>'Terry Franci','ai_score'=>150,'score'=>145,'difficulty'=>'Easy','completed'=>true],
            (object)['title'=>'Benchmark','responsible'=>'John Doe','ai_score'=>150,'score'=>50,'difficulty'=>'Easy','completed'=>false],
            (object)['title'=>'Ideation','responsible'=>'Sophia Wilson','ai_score'=>150,'score'=>150,'difficulty'=>'Medium','completed'=>true],
            (object)['title'=>'Business Model Canvas','responsible'=>'Terry Franci','ai_score'=>150,'score'=>0,'difficulty'=>'Medium','completed'=>false],
        ]);
        $team = collect([
            (object)['name'=>'John','surname'=>'Doe','steps'=>'Step 1, 4, 7'],
            (object)['name'=>'Sophia','surname'=>'Wilson','steps'=>'Step 2, 5, 8'],
            (object)['name'=>'Terry','surname'=>'Franci','steps'=>'Step 3, 6, 9'],
        ]);
        return view('portal.reports.startup-detail', compact('project', 'steps', 'team'));
    }

    /**
     * WAY AI Coach — Question Detail — Figma F-71/F-74
     * Real Vega lecturer messages → question/answer/feedback timeline.
     */
    public function coachQuestions($id)
    {
        $student = User::findOrFail($id);
        $vegaUserId = $this->vegaReportService->resolveVegaUserId($student->id);
        $questions = $this->vegaReportService->getCoachFeedback($vegaUserId);

        return view('portal.reports.coach-questions', compact('student', 'questions'))
            ->with('activeApp', 'way-ai-coach');
    }


    /**
     * AJAX: Student enrichment data — Tier 2/3
     * Returns scenario breakdown, theme breakdown, score trend.
     */
    public function studentEnrichment(User $student)
    {
        $vegaUserId = $this->vegaReportService->resolveVegaUserId($student->id);

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

        $vegaUserId = $this->vegaReportService->resolveVegaUserId($student->id);
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

        $vegaUserId = $this->vegaReportService->resolveVegaUserId($student->id);
        $sessions = collect();
        $themeBreakdown = collect();
        $stats = ['total_sessions' => 0, 'lecturer' => 0, 'chatbot' => 0, 'avg_score' => null, 'total_duration' => 0, 'total_messages' => 0];

        if ($vegaUserId) {
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
        }

        $wingsPoints = $vegaUserId ? $this->vegaReportService->getWingsPoints($vegaUserId) : ['total_wings' => 0, 'categories' => []];

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

        $vegaUserId = $this->vegaReportService->resolveVegaUserId($student->id);
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
        $session = \App\Models\Vega\VegaDbSession::on('vega_db')
            ->with(['simulatorSteps', 'lecturerMessages', 'chatMessages'])
            ->findOrFail($sessionId);

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
