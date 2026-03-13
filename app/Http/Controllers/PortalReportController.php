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

        // 5.2: Class filter — if class_id is provided, narrow scoped users to that class
        $classId = request('class_id');
        $schoolIds = $user->schools()->pluck('schools.id');
        $classes = \App\Models\SchoolClass::whereIn('school_id', $schoolIds)->orderBy('name')->get();

        if ($classId) {
            $classUserIds = \DB::table('class_user')->where('class_id', $classId)->pluck('user_id');
            $scopedUserIds = $scopedUserIds->intersect($classUserIds);
        }

        // ── ReportService ile DB'deki normalize edilmiş veriler ──
        $reportData = $this->reportService->getAppReport($app, $scopedUserIds);

        // ── Connector'dan uygulama bazlı canlı veriler (failsafe) ──
        $connector = $app->resolveConnector();
        $missions = collect();
        $startups = collect();

        try {
            if ($connector instanceof MissionWayConnector) {
                $missions = $this->getMissionWayLiveData($connector, $app, $scopedUserIds);
            } elseif ($connector instanceof WayStartupConnector) {
                $startups = $this->getWayStartupLiveData($connector, $app, $scopedUserIds);
            }
        } catch (\Throwable $e) {
            \Log::warning("Connector live data failed for {$app->slug}: " . $e->getMessage());
        }

        // ── Per-user stats from DB ──
        $userStats = collect($reportData['user_stats'] ?? []);

        // Connector-specific user enrichment (limit to 10 to prevent 504 timeout)
        if ($connector) {
            $enriched = 0;
            $userStats = $userStats->map(function ($stat) use ($connector, $app, &$enriched) {
                $u = $stat['user'] ?? null;
                if (!$u || $enriched >= 10) return $stat;

                try {
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
                    $enriched++;
                } catch (\Throwable $e) {
                    \Log::warning("Connector enrichment failed for user {$u->id}: " . $e->getMessage());
                }

                return $stat;
            })->values();
        }

        $data = [
            'app'              => $app,
            'user'             => $user,
            'classes'          => $classes,
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
                    $profile = $d['profile'] ?? [];
                    $stats = $profile['statistics'] ?? [];
                    $connectorProfiles[$a->slug] = [
                        'player_id'       => $d['player_id'] ?? null,
                        'total_score'     => $profile['totalScore'] ?? 0,
                        'simulations_completed' => $profile['totalSimulationsCompleted'] ?? 0,
                        'play_time_minutes' => $profile['totalPlayTimeMinutes'] ?? 0,
                        'session_count'   => $d['session_count'] ?? 0,
                        'achievements'    => $profile['achievements'] ?? null,
                        // Statistics JSONB fields
                        'avg_score'       => $stats['avgScore'] ?? null,
                        'best_score'      => $stats['bestScore'] ?? null,
                        'avg_health'      => $stats['avgHealth'] ?? $stats['averageHealth'] ?? null,
                        'avg_resource'    => $stats['avgResource'] ?? $stats['averageResource'] ?? null,
                        'avg_ethics'      => $stats['avgEthics'] ?? $stats['averageEthics'] ?? null,
                        'avg_adaptation'  => $stats['avgAdaptation'] ?? $stats['averageAdaptation'] ?? null,
                    ];
                }
            } elseif ($conn instanceof WayStartupConnector) {
                $report = $conn->getUserReport($student);
                if ($report && ($report['success'] ?? false)) {
                    $d = $report['data'] ?? [];
                    $totalSteps = $d['total_steps'] ?? 0;
                    $completedSteps = $d['completed_steps'] ?? 0;
                    $connectorProfiles[$a->slug] = [
                        'member_id'       => $d['member_id'] ?? null,
                        'points'          => $d['member']['points'] ?? 0,
                        'completed_steps' => $completedSteps,
                        'total_steps'     => $totalSteps,
                        'tasks_remaining' => max(0, $totalSteps - $completedSteps),
                        'simulations_count' => $d['simulations_count'] ?? 0,
                        'simulations_with_progress' => $d['simulations_with_progress'] ?? [],
                    ];
                }
            } elseif ($conn instanceof VegaConnector) {
                $report = $conn->getUserReport($student);
                if ($report && ($report['success'] ?? false)) {
                    $d = $report['data'] ?? [];
                    $connectorProfiles[$a->slug] = [
                        'vega_id'         => $d['vega_id'] ?? null,
                        'session_count'   => $d['session_count'] ?? 0,
                        'lecturer_count'  => $d['modules']['lecturer'] ?? 0,
                        'simulator_count' => $d['modules']['simulator'] ?? 0,
                        'has_details'     => $d['has_details'] ?? 0,
                        'profile'         => $d['profile'] ?? [],
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
            $simData = $connector->getSimulation((int) $id);

            if ($simData) {
                // ── Oturumları çek → finalScore/finalMetrics ve versionId tespit et ──
                $sessions = $connector->getSimulationSessions(['filter' => "simulationId||eq||{$id}"]);
                $sessionList = is_array($sessions) ? ($sessions['data'] ?? $sessions) : [];

                $bestSession = null;
                $aggregatedMetrics = ['health' => 0, 'resource' => 0, 'ethics' => 0, 'adaptation' => 0];
                $completedCount = 0;
                $totalScore = 0;
                $versionId = null;

                // Player ID → Player Name cache
                $playerNameCache = [];
                $choicesByPath = [];

                foreach ($sessionList as $session) {
                    $sessionId = $session['id'] ?? null;
                    if (!$sessionId) continue;

                    // SimulationVersionId'yi ilk session'dan al
                    if (!$versionId && !empty($session['simulationVersionId'])) {
                        $versionId = (int) $session['simulationVersionId'];
                    }

                    // finalMetrics varsa topla
                    $fm = $session['finalMetrics'] ?? null;
                    $fs = $session['finalScore'] ?? null;
                    if ($fm || $fs) {
                        $completedCount++;
                        $totalScore += (int) ($fs ?? 0);
                        if (is_array($fm)) {
                            $aggregatedMetrics['health'] += (int) ($fm['health'] ?? 0);
                            $aggregatedMetrics['resource'] += (int) ($fm['resource'] ?? 0);
                            $aggregatedMetrics['ethics'] += (int) ($fm['ethics'] ?? 0);
                            $aggregatedMetrics['adaptation'] += (int) ($fm['adaptation'] ?? 0);
                        }
                    }
                    if (!$bestSession || ($fs ?? 0) > ($bestSession['finalScore'] ?? 0)) {
                        $bestSession = $session;
                    }

                    // ── Session Players → Öğrenci listesi (isim eşleştirmesiyle) ──
                    $players = $connector->getSessionPlayers($sessionId);
                    if (is_array($players)) {
                        foreach ($players as $sp) {
                            $playerId = $sp['playerId'] ?? null;
                            $playerName = 'Oyuncu';
                            $playerSurname = '';

                            // Player ID ile isim çöz (cache'le)
                            if ($playerId && !isset($playerNameCache[$playerId])) {
                                $playerData = $connector->getPlayer($playerId);
                                if ($playerData) {
                                    $playerNameCache[$playerId] = [
                                        'name' => $playerData['name'] ?? 'Oyuncu',
                                        'surname' => $playerData['surname'] ?? '',
                                    ];
                                }
                            }
                            if ($playerId && isset($playerNameCache[$playerId])) {
                                $playerName = $playerNameCache[$playerId]['name'];
                                $playerSurname = $playerNameCache[$playerId]['surname'];
                            }

                            $students->push((object) [
                                'name'           => $sp['name'] ?? $playerName,
                                'surname'        => $sp['surname'] ?? $playerSurname,
                                'role'           => $sp['role'] ?? $sp['roleName'] ?? '-',
                                'grade'          => $sp['grade'] ?? '-',
                                'completed'      => $sp['completedDecisions'] ?? $sp['completed'] ?? 0,
                                'total_missions' => $sp['totalDecisions'] ?? $sp['total'] ?? 0,
                                'health'         => $sp['healthMetric'] ?? $sp['health'] ?? 0,
                                'resource'       => $sp['resourceMetric'] ?? $sp['resource'] ?? 0,
                                'ethics'         => $sp['ethicsMetric'] ?? $sp['ethics'] ?? 0,
                                'adaptation'     => $sp['adaptationMetric'] ?? $sp['adaptation'] ?? 0,
                            ]);
                        }
                    }

                    // ── PlayerChoices → soru/cevap eşleştirme verisi ──
                    $choices = $connector->getPlayerChoices($sessionId);
                    foreach ($choices as $choice) {
                        $choicesByPath[$choice['simulationPathId'] ?? 0][] = $choice;
                    }
                }

                // ── SimulationPaths → Group Flow question cards (blade uyumlu) ──
                // Blade expects: $q->question, $q->options[], $q->unanimity, $q->health/resource/ethics/adaptation

                if ($versionId) {
                    $paths = $connector->getSimulationPaths($versionId);
                } else {
                    $paths = [];
                }

                // Path'leri tree olarak organize et: parentPathId => children
                $pathsById = [];
                $childrenOf = [];
                foreach ($paths as $path) {
                    $pid = $path['id'] ?? null;
                    $parentId = $path['parentPathId'] ?? $path['parent_path_id'] ?? null;
                    if ($pid) {
                        $pathsById[$pid] = $path;
                        $childrenOf[$parentId ?? 0][] = $path;
                    }
                }

                // Decision type path'leri question olarak al
                foreach ($paths as $path) {
                    $pathType = $path['pathType'] ?? $path['path_type'] ?? '';
                    if (!in_array($pathType, ['decision', 'question'])) continue;

                    $pathId = $path['id'] ?? null;
                    $translations = $path['translations'] ?? [];
                    $metrics = $path['metrics'] ?? [];
                    $questionText = $translations['question'] ?? $translations['narrative'] ?? $path['narrative'] ?? "Soru #{$pathId}";

                    // Alt path'ler (child options)
                    $childPaths = $childrenOf[$pathId] ?? [];
                    $options = [];
                    $totalPlayers = 0;
                    $selectedCount = 0;

                    foreach ($childPaths as $ci => $child) {
                        $childId = $child['id'] ?? null;
                        $childTr = $child['translations'] ?? [];
                        $optionText = $childTr['optionText'] ?? $childTr['narrative'] ?? $child['narrative'] ?? "Seçenek " . chr(65 + $ci);

                        // Bu option'ı kaç oyuncu seçmiş?
                        $isSelected = false;
                        if ($pathId && !empty($choicesByPath[$pathId])) {
                            foreach ($choicesByPath[$pathId] as $ch) {
                                $totalPlayers++;
                                if (($ch['selectedPathId'] ?? null) == $childId) {
                                    $isSelected = true;
                                    $selectedCount++;
                                }
                            }
                        }

                        $options[] = (object) [
                            'text'     => $optionText,
                            'selected' => $isSelected,
                            'path_id'  => $childId,
                        ];
                    }

                    // Eğer option yoksa ama playerChoice varsa, raw choice'tan option oluştur
                    if (empty($options) && !empty($choicesByPath[$pathId])) {
                        foreach ($choicesByPath[$pathId] as $ch) {
                            $options[] = (object) [
                                'text'     => $playerNameCache[$ch['playerId'] ?? 0]['name'] ?? 'Seçim',
                                'selected' => true,
                                'path_id'  => $ch['selectedPathId'] ?? null,
                            ];
                        }
                    }

                    // Unanimity: seçenlerin yüzdesi
                    $unanimity = $totalPlayers > 0 ? round(($selectedCount / $totalPlayers) * 100) : 0;

                    $questions->push((object) [
                        'question'   => $questionText,
                        'options'    => $options,
                        'unanimity'  => $unanimity,
                        'health'     => $metrics['health'] ?? 0,
                        'resource'   => $metrics['resource'] ?? 0,
                        'ethics'     => $metrics['ethics'] ?? 0,
                        'adaptation' => $metrics['adaptation'] ?? 0,
                        'path_id'    => $pathId,
                        'points'     => $path['points'] ?? $path['pathPoints'] ?? 0,
                    ]);
                }

                // Eğer SimulationPaths boşsa, playerChoices'tan en azından temel question card oluştur
                if ($questions->isEmpty() && !empty($choicesByPath)) {
                    $qi = 0;
                    foreach ($choicesByPath as $pId => $choices) {
                        $qi++;
                        $ch = $choices[0] ?? [];
                        $mAfter = $ch['metricsAfter'] ?? [];
                        $questions->push((object) [
                            'question'   => "Karar #{$qi}",
                            'options'    => collect($choices)->map(fn($c) => (object) [
                                'text' => ($playerNameCache[$c['playerId'] ?? 0]['name'] ?? 'Oyuncu') .
                                          ($c['isCorrect'] ? ' ✓' : ' ✗') .
                                          ' (' . ($c['pointsEarned'] ?? 0) . ' puan)',
                                'selected' => $c['isCorrect'] ?? false,
                            ])->all(),
                            'unanimity'  => 0,
                            'health'     => $mAfter['health'] ?? 0,
                            'resource'   => $mAfter['resource'] ?? 0,
                            'ethics'     => $mAfter['ethics'] ?? 0,
                            'adaptation' => $mAfter['adaptation'] ?? 0,
                            'path_id'    => $pId,
                            'points'     => $ch['pointsEarned'] ?? 0,
                        ]);
                    }
                }

                // Metrikleri ortala (completed > 0 ise)
                if ($completedCount > 0) {
                    $aggregatedMetrics['health'] = round($aggregatedMetrics['health'] / $completedCount);
                    $aggregatedMetrics['resource'] = round($aggregatedMetrics['resource'] / $completedCount);
                    $aggregatedMetrics['ethics'] = round($aggregatedMetrics['ethics'] / $completedCount);
                    $aggregatedMetrics['adaptation'] = round($aggregatedMetrics['adaptation'] / $completedCount);
                }

                // Best session'dan veya aggregated'dan metrik al
                $bfm = $bestSession['finalMetrics'] ?? null;

                $mission = (object) [
                    'id'              => $simData['id'] ?? $id,
                    'title'           => $simData['name'] ?? $simData['title'] ?? 'Simülasyon #' . $id,
                    'status'          => $this->formatStatus($simData['status'] ?? 'active'),
                    'difficulty'      => $simData['difficultyLevel'] ?? $simData['difficulty'] ?? '-',
                    'created'         => isset($simData['createdAt']) ? \Carbon\Carbon::parse($simData['createdAt'])->format('d.m.Y') : '-',
                    'description'     => $simData['description'] ?? '',
                    'result'          => $simData['result'] ?? $simData['summary'] ?? '',
                    'completion_rate' => $completedCount > 0 ? round(($completedCount / max(count($sessionList), 1)) * 100) : 0,
                    'avg_score'       => $completedCount > 0 ? round($totalScore / $completedCount) : 0,
                    'health'          => (is_array($bfm) ? ($bfm['health'] ?? 0) : 0) ?: $aggregatedMetrics['health'],
                    'resource'        => (is_array($bfm) ? ($bfm['resource'] ?? 0) : 0) ?: $aggregatedMetrics['resource'],
                    'ethics'          => (is_array($bfm) ? ($bfm['ethics'] ?? 0) : 0) ?: $aggregatedMetrics['ethics'],
                    'adaptation'      => (is_array($bfm) ? ($bfm['adaptation'] ?? 0) : 0) ?: $aggregatedMetrics['adaptation'],
                    'image'           => $simData['coverImage'] ?? $simData['image'] ?? null,
                    'final_score'     => $bestSession['finalScore'] ?? 0,
                    'session_count'   => count($sessionList),
                    'completed_count' => $completedCount,
                ];
            }
        }

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
        $files = [];
        $links = [];
        $tools = [];
        $aiEvaluation = null;

        if ($connector instanceof WayStartupConnector) {
            $simData = $connector->getSimulation((int) $id);

            if ($simData) {
                // ── Adımları çek ──
                $stepsData = $connector->getSteps((int) $id);
                $stepsData = is_array($stepsData) ? $stepsData : [];

                // ── İlk üyeyi bul (memberId için) ──
                $memberId = null;
                if ($app) {
                    $firstUser = $app->users()->first();
                    if ($firstUser) {
                        $memberData = $connector->getMemberByUserId((string) $firstUser->id);
                        $memberId = $memberData['id'] ?? null;
                    }
                }

                // ── UserStepProgress: step bazlı earnedPoint/earnedCoin ──
                $stepProgressMap = [];
                if ($memberId) {
                    $allStepProgress = $connector->getUserStepProgress($memberId);
                    if (is_array($allStepProgress)) {
                        foreach ($allStepProgress as $sp) {
                            $spStepId = $sp['stepId'] ?? $sp['step_id'] ?? null;
                            if ($spStepId) {
                                $stepProgressMap[$spStepId] = $sp;
                            }
                        }
                    }
                }

                // ── Step Submissions → files + links ──
                $submissions = $connector->getStepSubmissions((int) $id);
                foreach ($submissions as $sub) {
                    // File submission
                    if (!empty($sub['fileName'] ?? $sub['file_name'] ?? null)) {
                        $files[] = [
                            'step'   => $sub['stepId'] ?? $sub['step_id'] ?? '-',
                            'name'   => $sub['fileName'] ?? $sub['file_name'] ?? '-',
                            'size'   => $sub['fileSize'] ?? $sub['file_size'] ?? '',
                            'url'    => $sub['fileUrl'] ?? $sub['file_url'] ?? '',
                            'type'   => $sub['fileType'] ?? $sub['file_type'] ?? '',
                            'status' => $sub['status'] ?? '',
                        ];
                    }
                    // Link submission
                    if (!empty($sub['linkUrl'] ?? $sub['link_url'] ?? null)) {
                        $links[] = [
                            'step'     => $sub['stepId'] ?? $sub['step_id'] ?? '-',
                            'url'      => $sub['linkUrl'] ?? $sub['link_url'] ?? '',
                            'title'    => $sub['linkTitle'] ?? $sub['link_title'] ?? '',
                            'platform' => $sub['linkPlatform'] ?? $sub['link_platform'] ?? '',
                        ];
                    }
                }

                // ── AI Evaluation (toplam değerlendirme) ──
                if ($memberId) {
                    $evaluations = $connector->getStepQuestionEvaluations($memberId);
                    if (!empty($evaluations)) {
                        // En son veya en yüksek değerlendirmeyi al
                        $bestEval = collect($evaluations)->sortByDesc('aiTotalScore')->first();
                        $aiEvaluation = (object) [
                            'total_score'      => $bestEval['aiTotalScore'] ?? $bestEval['ai_total_score'] ?? 0,
                            'max_score'        => $bestEval['aiMaxScore'] ?? $bestEval['ai_max_score'] ?? 100,
                            'coins'            => $bestEval['aiCoins'] ?? $bestEval['ai_coins'] ?? 0,
                            'overall_feedback' => $bestEval['aiOverallFeedback'] ?? $bestEval['ai_overall_feedback'] ?? '',
                            'status'           => $bestEval['status'] ?? 'pending',
                            'step_id'          => $bestEval['stepId'] ?? $bestEval['step_id'] ?? null,
                        ];
                    }
                }

                // ── Adımları build et (step tools + gerçek skorlar) ──
                $completedSteps = 0;
                $totalScore = 0;
                $maxScore = 0;
                $allTools = [];

                foreach ($stepsData as $step) {
                    $stepId = $step['id'] ?? null;
                    $progress = $stepProgressMap[$stepId] ?? null;

                    // Gerçek skor: UserStepProgress.earnedPoint > step.score
                    $earnedPoint = $progress['earnedPoint'] ?? $progress['earned_point'] ?? null;
                    $earnedCoin = $progress['earnedCoin'] ?? $progress['earned_coin'] ?? 0;
                    $stepScore = $earnedPoint ?? $step['score'] ?? $step['point'] ?? $step['points'] ?? 0;
                    $stepMax = $step['maxScore'] ?? $step['maxPoint'] ?? $step['points'] ?? 150;
                    $isCompleted = ($progress['status'] ?? $step['status'] ?? '') === 'completed'
                                || ($progress['status'] ?? '') === 'COMPLETED';

                    if ($isCompleted) $completedSteps++;
                    $totalScore += (int) $stepScore;
                    $maxScore += (int) $stepMax;

                    // Step tools çek
                    $stepToolsList = [];
                    if ($stepId) {
                        $rawTools = $connector->getStepTools((int) $stepId);
                        foreach ($rawTools as $st) {
                            $tool = $st['tool'] ?? [];
                            $toolItem = [
                                'name'           => $tool['name'] ?? $st['toolName'] ?? '-',
                                'description'    => $tool['description'] ?? '',
                                'icon_url'       => $tool['iconUrl'] ?? $tool['icon_url'] ?? '',
                                'website_url'    => $tool['websiteUrl'] ?? $tool['website_url'] ?? '',
                                'category'       => $tool['category'] ?? '',
                                'is_recommended' => $st['isRecommended'] ?? $st['is_recommended'] ?? false,
                                'custom_note'    => $st['customNote'] ?? $st['custom_note'] ?? '',
                            ];
                            $stepToolsList[] = $toolItem;
                            $allTools[] = $toolItem;
                        }
                    }

                    $steps->push((object) [
                        'id'          => $stepId,
                        'title'       => $step['name'] ?? $step['title'] ?? 'Adım',
                        'responsible' => $step['responsibleName'] ?? $step['assignee'] ?? '-',
                        'ai_score'    => $step['aiScore'] ?? $stepMax,
                        'score'       => (int) $stepScore,
                        'max_score'   => (int) $stepMax,
                        'earned_coin' => (int) $earnedCoin,
                        'difficulty'  => $step['difficulty'] ?? $step['difficultyLevel'] ?? '-',
                        'skill'       => $step['skill'] ?? '',
                        'completed'   => $isCompleted,
                        'tools'       => $stepToolsList,
                        // Per-step tarih bilgisi (4.5)
                        'started_at'  => $progress['startedAt'] ?? $progress['started_at'] ?? $progress['createdAt'] ?? null,
                        'completed_at' => $progress['completedAt'] ?? $progress['completed_at'] ?? null,
                        // AI soru detayları (4.3) — lazy: sadece step id sakla, blade'de render
                        'questions'   => $stepId ? $this->getStepQuestionsData($connector, $stepId) : [],
                    ]);
                }

                $tools = $allTools;

                // Sorunlu adımı bul (puan < %30)
                $problemStep = null;
                foreach ($stepsData as $si => $step) {
                    $stepId = $step['id'] ?? null;
                    $progress = $stepProgressMap[$stepId] ?? null;
                    $ss = $progress['earnedPoint'] ?? $progress['earned_point'] ?? $step['score'] ?? $step['point'] ?? 0;
                    $sm = $step['maxScore'] ?? $step['maxPoint'] ?? $step['points'] ?? 150;
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

                // ── Üyeleri çek ──
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

        if (!$project) {
            abort(404, 'Startup projesi bulunamadı veya API erişilemez.');
        }

        return view('portal.reports.startup-detail', compact('project', 'steps', 'team', 'files', 'links', 'tools', 'aiEvaluation'));
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
     * Role Galaxy — Simulator Session Detail (Turn-by-turn)
     * Canlı veri: VegaConnector'dan simülatör oturum detayı.
     */
    public function simulatorDetail($id)
    {
        $vegaApp = Application::where('slug', 'like', '%vega%')
            ->orWhere('slug', 'like', '%coach%')
            ->orWhere('slug', 'like', '%role-galaxy%')
            ->active()
            ->first();

        if (!$vegaApp) {
            abort(404, 'Vega/Coach application not found');
        }

        $connector = $vegaApp->resolveConnector();
        if (!$connector) {
            abort(404, 'Connector not available');
        }

        $detail = $connector->getSessionDetail($id, 'simulator');

        if (!$detail || !($detail['success'] ?? false)) {
            abort(404, 'Simulator session not found');
        }

        $sessionData = $detail['data'] ?? [];
        $turns = $sessionData['turns'] ?? $sessionData['steps'] ?? [];
        $summary = $sessionData['summary'] ?? $sessionData;

        // Öğrenci bilgisi
        $student = null;
        $vegaId = $sessionData['user_id'] ?? $sessionData['vega_id'] ?? null;
        if ($vegaId) {
            $student = \App\Models\User::whereHas('applications', function ($q) use ($vegaApp, $vegaId) {
                $q->where('application_id', $vegaApp->id)
                  ->where('external_user_id', $vegaId);
            })->first();
        }

        return view('portal.reports.simulator-detail', [
            'sessionData' => $sessionData,
            'turns' => $turns,
            'summary' => $summary,
            'student' => $student,
            'sessionId' => $id,
        ]);
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

            // Session finalMetrics'ten metrikleri hesapla (sim-level metric alanları genellikle boş)
                $sessionHealth = 0; $sessionResource = 0; $sessionEthics = 0; $sessionAdaptation = 0;
                $completedSessions = 0;
                $lastSessionMetrics = null;
                foreach ($sessionData as $sess) {
                    $fm = $sess['finalMetrics'] ?? null;
                    if (is_array($fm)) {
                        $completedSessions++;
                        $sessionHealth += (int) ($fm['health'] ?? 0);
                        $sessionResource += (int) ($fm['resource'] ?? 0);
                        $sessionEthics += (int) ($fm['ethics'] ?? 0);
                        $sessionAdaptation += (int) ($fm['adaptation'] ?? 0);
                        $lastSessionMetrics = $fm;
                    }
                }
                $avgH = $completedSessions > 0 ? round($sessionHealth / $completedSessions) : null;
                $avgR = $completedSessions > 0 ? round($sessionResource / $completedSessions) : null;
                $avgE = $completedSessions > 0 ? round($sessionEthics / $completedSessions) : null;
                $avgA = $completedSessions > 0 ? round($sessionAdaptation / $completedSessions) : null;

                // Fallback: sim-level alanlardan oku
                $hp = $avgH ?? ($sim['healthMetric'] ?? $sim['health'] ?? null);
                $rp = $avgR ?? ($sim['resourceMetric'] ?? $sim['resource'] ?? null);
                $ep = $avgE ?? ($sim['ethicsMetric'] ?? $sim['ethics'] ?? null);
                $ap = $avgA ?? ($sim['adaptationMetric'] ?? $sim['adaptation'] ?? null);

                // Trend: son session metriği ortalamadan yüksekse up
                $trendH = ($lastSessionMetrics && $avgH) ? (($lastSessionMetrics['health'] ?? 0) >= $avgH ? 'up' : 'down') : null;
                $trendR = ($lastSessionMetrics && $avgR) ? (($lastSessionMetrics['resource'] ?? 0) >= $avgR ? 'up' : 'down') : null;
                $trendE = ($lastSessionMetrics && $avgE) ? (($lastSessionMetrics['ethics'] ?? 0) >= $avgE ? 'up' : 'down') : null;
                $trendA = ($lastSessionMetrics && $avgA) ? (($lastSessionMetrics['adaptation'] ?? 0) >= $avgA ? 'up' : 'down') : null;

            $missions->push((object) [
                'id'               => $simId,
                'name'             => $sim['name'] ?? $sim['title'] ?? 'Simülasyon',
                'students'         => $participants->take(5),
                'assigned_date'    => isset($sim['createdAt']) ? \Carbon\Carbon::parse($sim['createdAt'])->format('m/d/Y') : '-',
                'deadline'         => isset($sim['deadline']) ? \Carbon\Carbon::parse($sim['deadline'])->format('m/d/Y') : '-',
                'health_point'     => $hp,
                'resource_point'   => $rp,
                'ethics_point'     => $ep,
                'adaptation_point' => $ap,
                'health_trend'     => $trendH,
                'resource_trend'   => $trendR,
                'ethics_trend'     => $trendE,
                'adaptation_trend' => $trendA,
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

    /**
     * 5.3 — Tools Catalog (WayStartup getTools())
     */
    public function toolsCatalog()
    {
        $app = Application::where('slug', 'way-startup')->active()->first();
        $tools = collect();

        if ($app) {
            $connector = $app->resolveConnector();
            if ($connector && method_exists($connector, 'getTools')) {
                try {
                    $rawTools = $connector->getTools();
                    if (is_array($rawTools)) {
                        $tools = collect($rawTools)->map(fn($t) => (object) [
                            'name'        => $t['name'] ?? '-',
                            'description' => $t['description'] ?? '',
                            'icon_url'    => $t['iconUrl'] ?? $t['icon_url'] ?? '',
                            'website_url' => $t['websiteUrl'] ?? $t['website_url'] ?? '',
                            'category'    => $t['category'] ?? $t['type'] ?? 'Genel',
                        ]);
                    }
                } catch (\Throwable $e) {
                    // silently fail
                }
            }
        }

        $grouped = $tools->groupBy('category');
        return view('portal.reports.tools', compact('tools', 'grouped'));
    }

    /**
     * Step bazlı AI soru detaylarını çek.
     */
    private function getStepQuestionsData($connector, int $stepId): array
    {
        if (!method_exists($connector, 'getStepQuestions')) {
            return [];
        }

        try {
            $questions = $connector->getStepQuestions($stepId);
            if (!is_array($questions)) return [];

            return array_map(fn($q) => [
                'text'       => $q['question'] ?? $q['questionText'] ?? $q['question_text'] ?? '-',
                'max_score'  => $q['maxScore'] ?? $q['max_score'] ?? $q['aiMaxScore'] ?? 0,
                'score'      => $q['score'] ?? $q['aiScore'] ?? null,
                'feedback'   => $q['feedback'] ?? $q['aiFeedback'] ?? $q['ai_feedback'] ?? null,
            ], $questions);
        } catch (\Throwable $e) {
            return [];
        }
    }
}
