<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\SchoolClass;
use App\Models\User;
use App\Services\ReportService;
use Illuminate\Support\Facades\DB;

/**
 * Portal-side reporting controller.
 * Access is scoped by role via PortalRole middleware.
 */
class PortalReportController extends Controller
{
    public function __construct(private ReportService $reportService)
    {
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
     * TODO: Remove mock data block and reconnect ReportService when DB is populated.
     */
    public function appReport(Application $app)
    {
        $user = auth()->user();

        // ── MOCK STUDENTS ──────────────────────────────────────────
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

        // ── USER STATS for Performance tab ─────────────────────────
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
                $base['startup_type'] = ['Teknoloji','Sağlık','Eğitim','Finans'][$i % 4];
                $base['deadline'] = now()->addDays(rand(5,30))->format('d.m.Y');
                $base['teacher_score'] = rand(60,95);
            } elseif ($app->slug === 'way-ai-coach') {
                $base['alert'] = $i === 2;
            } elseif ($app->slug === 'role-galaxy') {
                $base['galaxy_selected'] = ['Liderlik','İletişim','Empati','Problem Çözme'][$i % 4];
                $base['role_played'] = ['Kaptan','Arabulucu','Danışman','Gözlemci'][$i % 4];
            } elseif ($app->slug === 'study-space') {
                $base['discussion_minutes'] = rand(15,120);
                $base['discussion_count'] = rand(3,18);
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

        return view('portal.reports.student', [
            'student' => $student,
            'reportData' => $reportData,
            'apps' => Application::active()->ordered()->get(),
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
            'id' => $id, 'title' => 'Keşfet - Dijital Harita',
            'status' => 'Tamamlandı', 'difficulty' => 'Easy', 'created' => '28.02.2026',
            'description' => 'Bu görevde öğrenciler dijital harita oluşturma sürecini öğrenecek ve pratik yapacaklar.',
            'completion_rate' => 72, 'avg_score' => 74.3,
        ];
        $students = collect([
            (object)['name'=>'Allison','surname'=>'Gouse','grade'=>11,'completed'=>3,'total_missions'=>3,'health'=>1,'resource'=>3,'ethics'=>3,'adaptation'=>1],
            (object)['name'=>'John','surname'=>'Doe','grade'=>8,'completed'=>7,'total_missions'=>7,'health'=>4,'resource'=>6,'ethics'=>7,'adaptation'=>5],
            (object)['name'=>'Emerson','surname'=>'Rosser','grade'=>12,'completed'=>12,'total_missions'=>12,'health'=>9,'resource'=>12,'ethics'=>8,'adaptation'=>3],
            (object)['name'=>'Maren','surname'=>'Dokidis','grade'=>9,'completed'=>8,'total_missions'=>8,'health'=>8,'resource'=>4,'ethics'=>6,'adaptation'=>7],
            (object)['name'=>'Cristofer','surname'=>'Curtis','grade'=>9,'completed'=>5,'total_missions'=>5,'health'=>4,'resource'=>5,'ethics'=>5,'adaptation'=>4],
            (object)['name'=>'Chance','surname'=>'Rhiel','grade'=>11,'completed'=>8,'total_missions'=>8,'health'=>7,'resource'=>8,'ethics'=>5,'adaptation'=>6],
            (object)['name'=>'Corey','surname'=>'Bergson','grade'=>9,'completed'=>10,'total_missions'=>10,'health'=>5,'resource'=>6,'ethics'=>7,'adaptation'=>5],
            (object)['name'=>'Anika','surname'=>'Mango','grade'=>12,'completed'=>7,'total_missions'=>7,'health'=>4,'resource'=>6,'ethics'=>7,'adaptation'=>5],
            (object)['name'=>'Kadin','surname'=>'Septimus','grade'=>10,'completed'=>5,'total_missions'=>5,'health'=>3,'resource'=>5,'ethics'=>2,'adaptation'=>5],
            (object)['name'=>'Jordyn','surname'=>'Geidt','grade'=>10,'completed'=>2,'total_missions'=>2,'health'=>0,'resource'=>1,'ethics'=>2,'adaptation'=>0],
        ]);
        return view('portal.reports.mission-detail', compact('mission', 'students'));
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
     */
    public function coachQuestions($id)
    {
        $student = (object)['name' => 'Ahmet Çelik'];
        $questions = collect([
            (object)[
                'question' => 'The earthquake has started! What should you do?',
                'health' => 65, 'resource' => 70, 'ethics' => 80, 'adaptation' => 60,
                'options' => collect([
                    (object)['text' => 'Get under the table (DUCK-COVER-HOLD ON)', 'correct' => true, 'selected' => false],
                    (object)['text' => 'Run Outside', 'correct' => false, 'selected' => false],
                    (object)['text' => 'Take the elevator down', 'correct' => false, 'selected' => true],
                ]),
            ],
            (object)[
                'question' => 'The earthquake has started! What should you do?',
                'health' => 45, 'resource' => 70, 'ethics' => 95, 'adaptation' => 75,
                'options' => collect([
                    (object)['text' => 'Get under the table (DUCK-COVER-HOLD ON)', 'correct' => true, 'selected' => false],
                    (object)['text' => 'Run Outside', 'correct' => false, 'selected' => true],
                    (object)['text' => 'Take the elevator down', 'correct' => false, 'selected' => false],
                ]),
            ],
        ]);
        return view('portal.reports.coach-questions', compact('student', 'questions'));
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
}
