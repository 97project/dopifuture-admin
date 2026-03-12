<?php

namespace App\Http\Controllers;

use App\Connectors\MissionWayConnector;
use App\Connectors\VegaConnector;
use App\Connectors\WayStartupConnector;
use App\Models\Application;
use App\Models\SchoolClass;
use App\Models\User;
use App\Services\ConnectorSyncService;
use App\Services\ReportService;
use Illuminate\Support\Facades\DB;

/**
 * Portal-side reporting controller.
 * Access is scoped by role via PortalRole middleware.
 *
 * Rol bazlı erişim:
 *   - Okul yöneticisi → tüm okul öğrencileri
 *   - Öğretmen → kendi sınıflarının öğrencileri
 *   - Öğrenci → sadece kendi verileri
 */
class PortalReportController extends Controller
{
    public function __construct(
        private ReportService $reportService,
        private ConnectorSyncService $syncService,
    ) {
    }

    /**
     * Reports overview dashboard.
     */
    public function index()
    {
        $user = auth()->user();
        $data = ['user' => $user];

        if ($user->hasAnyRole(['school-admin', 'school-principal'])) {
            $schoolIds = $user->schools()->pluck('schools.id');
            $data['overview'] = $this->reportService->getSchoolOverviewStats($schoolIds);
            $data['apps'] = Application::active()->ordered()->get();
        } elseif ($user->hasRole('teacher')) {
            $data['myClasses'] = $user->classes()
                ->with('school')
                ->withCount('students')
                ->get();
            $data['apps'] = Application::active()->ordered()->get();
        } elseif ($user->hasRole('student')) {
            $data['studentReport'] = $this->reportService->getStudentReport($user);
        }

        return view('portal.reports.index', $data);
    }

    /**
     * Per-application detailed report.
     * Figma F-38 (Assignments tab) + F-63 (Performance tab)
     *
     * Canlı veri: ReportService (DB) + Connector (API) birleşimi.
     */
    public function appReport(Application $app)
    {
        $user = auth()->user();
        $scopedUserIds = $this->getScopedUserIds($user);

        // ── ReportService ile DB'deki normalize edilmiş veriler ──
        $reportData = $this->reportService->getAppReport($app, $scopedUserIds);

        // ── Connector'dan uygulama bazlı canlı veriler ──
        $connector = $app->resolveConnector();
        $missions = collect();
        $startups = collect();

        if ($connector instanceof MissionWayConnector) {
            $missions = $this->getMissionWayLiveData($connector, $app, $scopedUserIds);
        } elseif ($connector instanceof WayStartupConnector) {
            $startups = $this->getWayStartupLiveData($connector, $app, $scopedUserIds);
        }

        // ── Per-user stats from DB ──
        $userStats = collect($reportData['user_stats'] ?? []);

        // Connector-specific user enrichment
        if ($connector) {
            $userStats = $userStats->map(function ($stat) use ($connector, $app) {
                $u = $stat['user'] ?? null;
                if (!$u) return $stat;

                if ($connector instanceof MissionWayConnector) {
                    $report = $connector->getUserReport($u);
                    if ($report && ($report['success'] ?? false)) {
                        $d = $report['data'] ?? [];
                        $stat['health_point'] = $d['composition']['player']['healthMetric'] ?? null;
                        $stat['resource_point'] = $d['composition']['player']['resourceMetric'] ?? null;
                        $stat['ethics_point'] = $d['composition']['player']['ethicsMetric'] ?? null;
                        $stat['adaptation_point'] = $d['composition']['player']['adaptationMetric'] ?? null;
                    }
                } elseif ($connector instanceof WayStartupConnector) {
                    $report = $connector->getUserReport($u);
                    if ($report && ($report['success'] ?? false)) {
                        $d = $report['data'] ?? [];
                        $stat['startup_type'] = $d['member']['type'] ?? null;
                        $stat['teacher_score'] = $d['member']['teacherScore'] ?? null;
                        $stat['completed_steps'] = $d['completed_steps'] ?? 0;
                        $stat['total_steps'] = $d['total_steps'] ?? 0;
                    }
                } elseif ($connector instanceof VegaConnector) {
                    $report = $connector->getUserReport($u);
                    if ($report && ($report['success'] ?? false)) {
                        $d = $report['data'] ?? [];
                        $stat['session_count'] = $d['session_count'] ?? 0;
                        $stat['lecturer_count'] = $d['modules']['lecturer'] ?? 0;
                        $stat['simulator_count'] = $d['modules']['simulator'] ?? 0;
                    }
                }

                return $stat;
            })->values();
        }

        $data = [
            'app'              => $app,
            'user'             => $user,
            'missions'         => $missions,
            'startups'         => $startups,
            'total_missions'   => $reportData['total_progress'] ?? 0,
            'total_progress'   => $reportData['total_progress'] ?? 0,
            'total_completed'  => $reportData['total_completed'] ?? 0,
            'total_sessions'   => $reportData['total_sessions'] ?? 0,
            'total_duration'   => $reportData['total_duration'] ?? 0,
            'avg_score'        => round($reportData['avg_score'] ?? 0, 1),
            'module_stats'     => $reportData['module_stats'] ?? collect(),
            'user_stats'       => $userStats,
            'sessions_by_day'  => $reportData['sessions_by_day'] ?? collect(),
            'recent_sessions'  => $reportData['recent_sessions'] ?? collect(),
        ];

        return view('portal.reports.app', $data);
    }

    /**
     * Student detailed report (all apps).
     */
    public function studentReport(User $student)
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

        $student->load(['roles', 'schools', 'classes.school', 'applications']);

        // Önce connector data sync et (güncel veri için)
        $this->syncService->syncAllAppsForUser($student);

        // Sonra ReportService ile normalize edilmiş raporu getir
        $reportData = $this->reportService->getStudentReport($student);

        // Connector-level enrichment: API'den direkt profil verileri
        $connectorProfiles = [];
        $apps = Application::active()->ordered()->get();
        foreach ($apps as $a) {
            $conn = $a->resolveConnector();
            if (!$conn) continue;

            if ($conn instanceof MissionWayConnector) {
                $report = $conn->getUserReport($student);
                if ($report && ($report['success'] ?? false)) {
                    $d = $report['data'] ?? [];
                    $connectorProfiles[$a->slug] = [
                        'player_id'       => $d['player_id'] ?? null,
                        'total_score'     => $d['profile']['totalScore'] ?? 0,
                        'simulations_completed' => $d['profile']['totalSimulationsCompleted'] ?? 0,
                        'play_time_minutes' => $d['profile']['totalPlayTimeMinutes'] ?? 0,
                        'session_count'   => $d['session_count'] ?? 0,
                        'achievements'    => $d['profile']['achievements'] ?? null,
                    ];
                }
            } elseif ($conn instanceof WayStartupConnector) {
                $report = $conn->getUserReport($student);
                if ($report && ($report['success'] ?? false)) {
                    $d = $report['data'] ?? [];
                    $connectorProfiles[$a->slug] = [
                        'member_id'       => $d['member_id'] ?? null,
                        'points'          => $d['member']['points'] ?? 0,
                        'completed_steps' => $d['completed_steps'] ?? 0,
                        'total_steps'     => $d['total_steps'] ?? 0,
                        'simulations_count' => $d['simulations_count'] ?? 0,
                        'simulations_with_progress' => $d['simulations_with_progress'] ?? [],
                    ];
                }
            }
        }

        return view('portal.reports.student', [
            'student'     => $student,
            'reportData'  => $reportData,
            'apps'        => $apps,
            'connectorProfiles' => $connectorProfiles,
        ]);
    }

    /**
     * Class report (optionally filtered by app).
     */
    public function classReport(SchoolClass $class, ?Application $app = null)
    {
        $user = auth()->user();

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

    /* ══════════════════════════════════════════════════════
     *  Detail Pages — Canlı Connector Verisi
     * ══════════════════════════════════════════════════════ */

    /**
     * Mission WAY — Mission Detail — Figma F-41/42/62
     * Canlı veri: MissionWayConnector'dan simülasyon + oturumlar + oyuncu detayları.
     */
    public function missionDetail($id)
    {
        $app = Application::where('slug', 'mission-way')->first();
        $connector = $app ? $app->resolveConnector() : null;

        $mission = null;
        $students = collect();
        $questions = collect();

        if ($connector instanceof MissionWayConnector) {
            // Simülasyon detayını API'den çek
            $simData = $connector->getSimulation((int) $id);

            if ($simData) {
                $mission = (object) [
                    'id'              => $simData['id'] ?? $id,
                    'title'           => $simData['name'] ?? $simData['title'] ?? 'Simülasyon #' . $id,
                    'status'          => $this->formatStatus($simData['status'] ?? 'active'),
                    'difficulty'      => $simData['difficultyLevel'] ?? $simData['difficulty'] ?? '-',
                    'created'         => isset($simData['createdAt']) ? \Carbon\Carbon::parse($simData['createdAt'])->format('d.m.Y') : '-',
                    'description'     => $simData['description'] ?? '',
                    'result'          => $simData['result'] ?? $simData['summary'] ?? '',
                    'completion_rate' => $simData['completionRate'] ?? 0,
                    'avg_score'       => $simData['avgScore'] ?? 0,
                    'health'          => $simData['healthMetric'] ?? $simData['health'] ?? 0,
                    'resource'        => $simData['resourceMetric'] ?? $simData['resource'] ?? 0,
                    'ethics'          => $simData['ethicsMetric'] ?? $simData['ethics'] ?? 0,
                    'adaptation'      => $simData['adaptationMetric'] ?? $simData['adaptation'] ?? 0,
                    'image'           => $simData['coverImage'] ?? $simData['image'] ?? null,
                ];

                // Oturum oyuncularını çek
                $sessions = $connector->getSimulationSessions(['filter' => "simulationId||eq||{$id}"]);
                $sessionList = is_array($sessions) ? ($sessions['data'] ?? $sessions) : [];

                foreach ($sessionList as $session) {
                    $sessionId = $session['id'] ?? null;
                    if (!$sessionId) continue;

                    $players = $connector->getSessionPlayers($sessionId);
                    if (is_array($players)) {
                        foreach ($players as $player) {
                            $students->push((object) [
                                'name'           => $player['name'] ?? $player['playerName'] ?? 'Oyuncu',
                                'surname'        => $player['surname'] ?? '',
                                'role'           => $player['role'] ?? $player['roleName'] ?? '-',
                                'grade'          => $player['grade'] ?? '-',
                                'completed'      => $player['completedDecisions'] ?? $player['completed'] ?? 0,
                                'total_missions' => $player['totalDecisions'] ?? $player['total'] ?? 0,
                                'health'         => $player['healthMetric'] ?? $player['health'] ?? 0,
                                'resource'       => $player['resourceMetric'] ?? $player['resource'] ?? 0,
                                'ethics'         => $player['ethicsMetric'] ?? $player['ethics'] ?? 0,
                                'adaptation'     => $player['adaptationMetric'] ?? $player['adaptation'] ?? 0,
                            ]);
                        }
                    }
                }
            }
        }

        // Simülasyon bulunamadıysa 404
        if (!$mission) {
            abort(404, 'Simülasyon bulunamadı veya API erişilemez.');
        }

        return view('portal.reports.mission-detail', compact('mission', 'students', 'questions'));
    }

    /**
     * Startup — Project Detail — Figma F-66/67/68
     * Canlı veri: WayStartupConnector'dan simülasyon + adımlar + üyeler.
     */
    public function startupDetail($id)
    {
        $app = Application::where('slug', 'way-startup')->first();
        $connector = $app ? $app->resolveConnector() : null;

        $project = null;
        $steps = collect();
        $team = collect();

        if ($connector instanceof WayStartupConnector) {
            $simData = $connector->getSimulation((int) $id);

            if ($simData) {
                // Adımları çek
                $stepsData = $connector->getSteps((int) $id);
                $stepsData = is_array($stepsData) ? $stepsData : [];

                $completedSteps = 0;
                $totalScore = 0;
                $maxScore = 0;

                foreach ($stepsData as $step) {
                    $isCompleted = ($step['status'] ?? '') === 'completed';
                    if ($isCompleted) $completedSteps++;
                    $stepScore = $step['score'] ?? $step['point'] ?? 0;
                    $stepMax = $step['maxScore'] ?? $step['maxPoint'] ?? 150;
                    $totalScore += $stepScore;
                    $maxScore += $stepMax;

                    $steps->push((object) [
                        'title'       => $step['name'] ?? $step['title'] ?? 'Adım',
                        'responsible' => $step['responsibleName'] ?? $step['assignee'] ?? '-',
                        'ai_score'    => $step['aiScore'] ?? $stepMax,
                        'score'       => $stepScore,
                        'difficulty'  => $step['difficulty'] ?? $step['difficultyLevel'] ?? '-',
                        'completed'   => $isCompleted,
                    ]);
                }

                // Sorunlu adımı bul (puan < %30 ise)
                $problemStep = null;
                foreach ($stepsData as $si => $step) {
                    $ss = $step['score'] ?? $step['point'] ?? 0;
                    $sm = $step['maxScore'] ?? $step['maxPoint'] ?? 150;
                    if ($sm > 0 && ($ss / $sm) < 0.3) {
                        $problemStep = $si + 1;
                        break;
                    }
                }

                $project = (object) [
                    'id'              => $simData['id'] ?? $id,
                    'name'            => ($simData['name'] ?? '-') . ' / ' . ($simData['type'] ?? $simData['category'] ?? ''),
                    'steps_completed' => $completedSteps,
                    'total_steps'     => count($stepsData),
                    'product_score'   => $totalScore,
                    'max_score'       => $maxScore ?: 0,
                    'problem_step'    => $problemStep,
                ];

                // Dosyaları ve linkleri çek
                $files = collect($simData['files'] ?? $simData['attachments'] ?? $simData['submissions'] ?? [])
                    ->filter(fn($f) => !empty($f['name'] ?? $f['fileName'] ?? null))
                    ->map(fn($f) => [
                        'step' => $f['stepId'] ?? $f['step'] ?? '-',
                        'name' => $f['name'] ?? $f['fileName'] ?? '-',
                        'size' => $f['size'] ?? $f['fileSize'] ?? '',
                        'url'  => $f['url'] ?? $f['fileUrl'] ?? '',
                    ])->values()->all();

                $links = collect($simData['links'] ?? $simData['submittedLinks'] ?? [])
                    ->filter(fn($l) => !empty($l['url'] ?? null))
                    ->map(fn($l) => [
                        'step' => $l['stepId'] ?? $l['step'] ?? '-',
                        'url'  => $l['url'] ?? '',
                    ])->values()->all();

                // Üyeleri çek (simülasyona atanmış kullanıcılar)
                if ($app) {
                    $appUsers = $app->users()->limit(20)->get();
                    foreach ($appUsers as $appUser) {
                        $member = $connector->getUser($appUser);
                        if ($member) {
                            $team->push((object) [
                                'name'    => $appUser->name,
                                'surname' => $appUser->surname ?? '',
                                'steps'   => $member['assignedSteps'] ?? '-',
                            ]);
                        }
                    }
                }
            }
        }

        $files = $files ?? [];
        $links = $links ?? [];

        if (!$project) {
            abort(404, 'Startup projesi bulunamadı veya API erişilemez.');
        }

        return view('portal.reports.startup-detail', compact('project', 'steps', 'team', 'files', 'links'));
    }

    /**
     * WAY AI Coach — Question Detail — Figma F-71/F-74
     * Canlı veri: VegaConnector'dan oturum detayı + mesaj geçmişi.
     */
    public function coachQuestions($id)
    {
        $app = Application::where('slug', 'way-ai-coach')->first();
        $connector = $app ? $app->resolveConnector() : null;

        $student = null;
        $questions = collect();
        $sessionDuration = null;

        if ($connector instanceof VegaConnector) {
            // Oturum detayını çek (lecturer modülü)
            $sessionData = $connector->getSessionDetail((string) $id, 'lecturer');

            if ($sessionData) {
                $student = (object) [
                    'name' => $sessionData['userName'] ?? $sessionData['user']['name'] ?? 'Öğrenci',
                    'surname' => $sessionData['userSurname'] ?? $sessionData['user']['surname'] ?? '',
                ];

                // Oturum süresi hesapla
                $sessionDuration = null;
                if (!empty($sessionData['startedAt']) && !empty($sessionData['endedAt'])) {
                    $start = \Carbon\Carbon::parse($sessionData['startedAt']);
                    $end = \Carbon\Carbon::parse($sessionData['endedAt']);
                    $diffMinutes = $start->diffInMinutes($end);
                    $sessionDuration = $diffMinutes > 60
                        ? floor($diffMinutes / 60) . 'sa ' . ($diffMinutes % 60) . 'dk'
                        : $diffMinutes . ' dk';
                } elseif (!empty($sessionData['duration'])) {
                    $sessionDuration = $sessionData['duration'] . ' dk';
                }

                // Oturumdaki soru-cevap mesajlarını parse et
                $messages = $sessionData['messages'] ?? $sessionData['history'] ?? [];

                foreach ($messages as $msg) {
                    // AI tarafından sorulan sorular ve öğrenci cevapları
                    if (($msg['role'] ?? '') === 'assistant' && isset($msg['question'])) {
                        $questions->push((object) [
                            'question'   => $msg['question'] ?? $msg['content'] ?? '',
                            'score'      => $msg['score'] ?? 0,
                            'max_score'  => $msg['maxScore'] ?? 20,
                            'answer'     => $msg['studentAnswer'] ?? $msg['answer'] ?? '',
                            'feedback'   => $msg['aiFeedback'] ?? $msg['feedback'] ?? '',
                            'health'     => $msg['healthMetric'] ?? $msg['health'] ?? 0,
                            'resource'   => $msg['resourceMetric'] ?? $msg['resource'] ?? 0,
                            'ethics'     => $msg['ethicsMetric'] ?? $msg['ethics'] ?? 0,
                            'adaptation' => $msg['adaptationMetric'] ?? $msg['adaptation'] ?? 0,
                            'options'    => collect($msg['options'] ?? []),
                        ]);
                    }
                }

                // Mesaj bazlı Q&A yoksa, ham mesajlardan çıkar
                if ($questions->isEmpty() && !empty($messages)) {
                    $currentQuestion = null;
                    foreach ($messages as $msg) {
                        $role = $msg['role'] ?? $msg['sender'] ?? '';
                        $content = $msg['content'] ?? $msg['text'] ?? '';

                        if ($role === 'assistant') {
                            $currentQuestion = $content;
                        } elseif ($role === 'user' && $currentQuestion) {
                            $questions->push((object) [
                                'question'   => $currentQuestion,
                                'score'      => $msg['score'] ?? 0,
                                'max_score'  => 20,
                                'answer'     => $content,
                                'feedback'   => $msg['feedback'] ?? '',
                                'health'     => 0,
                                'resource'   => 0,
                                'ethics'     => 0,
                                'adaptation' => 0,
                                'options'    => collect(),
                            ]);
                            $currentQuestion = null;
                        }
                    }
                }
            }
        }

        if (!$student) {
            abort(404, 'AI Coach oturumu bulunamadı veya API erişilemez.');
        }

        return view('portal.reports.coach-questions', compact('student', 'questions', 'sessionDuration'));
    }

    /**
     * Study Space — Chatbot Session Detail
     * Canlı veri: VegaConnector'dan chatbot oturum + mesaj geçmişi.
     */
    public function chatbotDetail($id)
    {
        $app = Application::where('slug', 'study-space')->first();
        $connector = $app ? $app->resolveConnector() : null;

        $student = null;
        $messages = [];
        $sessionDate = null;

        if ($connector instanceof VegaConnector) {
            $sessionData = $connector->getSessionDetail((string) $id, 'all');

            if ($sessionData) {
                $student = (object) [
                    'name' => $sessionData['userName'] ?? $sessionData['user']['name'] ?? 'Öğrenci',
                    'surname' => $sessionData['userSurname'] ?? $sessionData['user']['surname'] ?? '',
                ];

                $messages = $sessionData['messages'] ?? $sessionData['history'] ?? [];

                if (!empty($sessionData['created_at'])) {
                    $sessionDate = \Carbon\Carbon::parse($sessionData['created_at'])->format('d.m.Y H:i');
                }
            }
        }

        if (!$student) {
            abort(404, 'Chatbot oturumu bulunamadı veya API erişilemez.');
        }

        return view('portal.reports.chatbot-detail', compact('student', 'messages', 'sessionDate'));
    }

    /* ══════════════════════════════════════════════════════
     *  Private Helpers
     * ══════════════════════════════════════════════════════ */

    /**
     * Rol bazlı kullanıcı ID filtreleme.
     * school-admin → okulun tüm kullanıcıları
     * teacher → sınıflarının öğrencileri
     * student → sadece kendisi
     */
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
     * MissionWay'den canlı simülasyon listesi çek.
     */
    private function getMissionWayLiveData(MissionWayConnector $connector, Application $app, $scopedUserIds): \Illuminate\Support\Collection
    {
        $missions = collect();
        $simulations = $connector->getSimulations(['limit' => 50]);
        $simList = is_array($simulations) ? ($simulations['data'] ?? $simulations) : [];

        foreach ($simList as $sim) {
            // Oturum bilgilerini al
            $simId = $sim['id'] ?? null;
            $sessionData = [];
            if ($simId) {
                $sessions = $connector->getSimulationSessions(['filter' => "simulationId||eq||{$simId}", 'limit' => 50]);
                $sessionData = is_array($sessions) ? ($sessions['data'] ?? $sessions) : [];
            }

            // Bu simülasyona katılan kullanıcıları bul
            $participants = collect();
            $scopedUsers = User::whereIn('id', $scopedUserIds)->get();
            foreach ($scopedUsers as $u) {
                $comp = $connector->getUser($u);
                if ($comp) {
                    $participants->push($u);
                }
            }

            $missions->push((object) [
                'id'               => $simId,
                'name'             => $sim['name'] ?? $sim['title'] ?? 'Simülasyon',
                'students'         => $participants->take(5),
                'assigned_date'    => isset($sim['createdAt']) ? \Carbon\Carbon::parse($sim['createdAt'])->format('m/d/Y') : '-',
                'deadline'         => isset($sim['deadline']) ? \Carbon\Carbon::parse($sim['deadline'])->format('m/d/Y') : '-',
                'health_point'     => $sim['healthMetric'] ?? $sim['health'] ?? null,
                'resource_point'   => $sim['resourceMetric'] ?? $sim['resource'] ?? null,
                'ethics_point'     => $sim['ethicsMetric'] ?? $sim['ethics'] ?? null,
                'adaptation_point' => $sim['adaptationMetric'] ?? $sim['adaptation'] ?? null,
                'health_trend'     => null,
                'resource_trend'   => null,
                'ethics_trend'     => null,
                'adaptation_trend' => null,
            ]);
        }

        return $missions;
    }

    /**
     * WayStartup'dan canlı startup listesi çek.
     */
    private function getWayStartupLiveData(WayStartupConnector $connector, Application $app, $scopedUserIds): \Illuminate\Support\Collection
    {
        $startups = collect();
        $simulations = $connector->getSimulationsWithProgress();
        $simList = is_array($simulations) ? ($simulations['data'] ?? $simulations) : [];

        if (empty($simList)) {
            $simulations = $connector->getSimulations(['limit' => 50]);
            $simList = is_array($simulations) ? ($simulations['data'] ?? $simulations) : [];
        }

        foreach ($simList as $sim) {
            $simId = $sim['id'] ?? null;
            $steps = $simId ? $connector->getSteps((int) $simId) : [];
            $steps = is_array($steps) ? $steps : [];
            $completedSteps = collect($steps)->where('status', 'completed')->count();
            $totalSteps = count($steps);
            $totalScore = collect($steps)->sum(fn($s) => $s['score'] ?? $s['point'] ?? 0);
            $maxScore = collect($steps)->sum(fn($s) => $s['maxScore'] ?? $s['maxPoint'] ?? 150);

            $participants = collect();
            $scopedUsers = User::whereIn('id', $scopedUserIds)->get();
            foreach ($scopedUsers as $u) {
                $member = $connector->getUser($u);
                if ($member) {
                    $participants->push($u);
                }
            }

            $startups->push((object) [
                'id'             => $simId,
                'name'           => $sim['name'] ?? 'Proje',
                'type'           => $sim['type'] ?? $sim['category'] ?? '-',
                'type_icon'      => $this->getTypeIcon($sim['type'] ?? ''),
                'students'       => $participants->take(4),
                'deadline'       => isset($sim['deadline']) ? \Carbon\Carbon::parse($sim['deadline'])->format('m/d/Y') : '-',
                'deadline_overdue' => isset($sim['deadline']) && \Carbon\Carbon::parse($sim['deadline'])->isPast(),
                'step_completed' => $completedSteps,
                'step_total'     => $totalSteps,
                'system_point'   => $totalScore,
                'max_point'      => $maxScore ?: 1500,
                'teacher_point'  => $sim['teacherScore'] ?? null,
                'status'         => $this->formatStatusSlug($sim['status'] ?? ($completedSteps >= $totalSteps && $totalSteps > 0 ? 'completed' : 'in_progress')),
            ]);
        }

        return $startups;
    }

    private function formatStatus(?string $raw): string
    {
        return match (strtolower($raw ?? '')) {
            'completed', 'done', 'finished' => 'Tamamlandı',
            'in_progress', 'active', 'started' => 'Devam Ediyor',
            'not_started', 'pending' => 'Başlanmadı',
            'cancelled', 'aborted' => 'İptal',
            default => $raw ?? '-',
        };
    }

    private function formatStatusSlug(?string $raw): string
    {
        return match (strtolower($raw ?? '')) {
            'completed', 'done', 'finished' => 'completed',
            'in_progress', 'active', 'started', 'playing' => 'in_progress',
            'not_started', 'pending', 'created' => 'not_started',
            default => $raw ?? 'not_started',
        };
    }

    private function getTypeIcon(string $type): string
    {
        return match (strtolower($type)) {
            'edtech' => '📚',
            'healthcare tech', 'healthcare' => '🏥',
            'fintech', 'finance' => '💰',
            'e-commerce', 'ecommerce' => '🛒',
            'robotics' => '🤖',
            'virtual reality', 'vr' => '🎮',
            'wearable tech', 'wearable' => '⌚',
            'travel management', 'travel' => '✈️',
            'cybersecurity', 'security' => '🔒',
            'conversational ai', 'ai' => '💬',
            'blockchain' => '🔗',
            default => '📁',
        };
    }
}
