<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Application::class);

        $query = Application::withCount('users');

        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('slug', 'like', "%{$search}%");
        }
        if ($request->filled('status')) {
            $query->where('is_active', $request->boolean('status'));
        }

        $applications = $query->ordered()->paginate(20)->withQueryString();

        $stats = [
            'total' => Application::count(),
            'active' => Application::active()->count(),
            'total_users' => \DB::table('application_user')->count(),
        ];

        return view('admin.applications.index', compact('applications', 'stats'));
    }

    public function show(Request $request, Application $application)
    {
        $this->authorize('view', $application);

        $activeTab = $request->input('tab', 'overview');

        // Users with sync pivot data
        $usersQuery = $application->users();

        // Search filter
        if ($search = $request->input('search')) {
            $usersQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('surname', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $usersQuery->orderByPivot('created_at', 'desc')->paginate(25);

        // Sync stats
        $syncStats = [
            'total' => $application->users()->count(),
            'synced' => $application->users()->wherePivot('sync_status', 'synced')->count(),
            'failed' => $application->users()->wherePivot('sync_status', 'failed')->count(),
            'pending' => $application->users()->wherePivot('sync_status', 'pending')->count(),
        ];

        // All users for assignment dropdown (exclude already assigned)
        $assignedIds = $application->users()->pluck('users.id')->toArray();
        $availableUsers = \App\Models\User::whereNotIn('id', $assignedIds)
            ->orderBy('name')
            ->limit(200)
            ->get(['id', 'name', 'surname', 'email']);

        // Connector hazır mı?
        $connector = $application->resolveConnector();
        $connectorReady = $connector && $connector::isReady();

        // API health kontrolü (tüm connector'lar)
        $apiHealth = null;
        if ($connector instanceof \App\Connectors\VegaConnector) {
            $apiHealth = $connector->getHealth();
        } elseif ($connector instanceof \App\Connectors\MissionWayConnector) {
            $apiHealth = $connector->getHealthCheck();
        } elseif ($connector instanceof \App\Connectors\WayStartupConnector) {
            $apiHealth = $connector->getHealthCheck();
        }

        // Connector tipi belirleme (view'da partial seçimi için)
        $connectorType = 'generic';
        if ($connector instanceof \App\Connectors\VegaConnector) {
            $connectorType = 'vega';
        } elseif ($connector instanceof \App\Connectors\MissionWayConnector) {
            $connectorType = 'missionway';
        } elseif ($connector instanceof \App\Connectors\WayStartupConnector) {
            $connectorType = 'waystartup';
        }

        // Rapor sekmesi verisi
        $reportData = null;
        if ($activeTab === 'report' && $connector && $connectorReady) {
            $reportData = $this->gatherReportData($application, $connector);
        }

        return view('admin.applications.show', compact(
            'application',
            'users',
            'syncStats',
            'availableUsers',
            'connectorReady',
            'apiHealth',
            'activeTab',
            'connectorType',
            'reportData'
        ));
    }

    /**
     * Tek kullanıcı için connector'dan detaylı rapor.
     */
    public function userReport(Request $request, Application $application, \App\Models\User $user)
    {
        $this->authorize('view', $application);

        $connector = $application->resolveConnector();
        if (!$connector) {
            return back()->with('error', 'Bu uygulama için connector tanımlı değil.');
        }

        $report = $connector->getUserReport($user);

        $connectorType = 'generic';
        $extraData = [];

        if ($connector instanceof \App\Connectors\VegaConnector) {
            $connectorType = 'vega';
            // Enrich with sessionsOverview
            try {
                $vegaId = $report['data']['vega_id'] ?? null;
                if ($vegaId) {
                    $extraData['sessions_overview'] = $connector->getSessionsOverview($vegaId);
                }
            } catch (\Throwable $e) {}

        } elseif ($connector instanceof \App\Connectors\MissionWayConnector) {
            $connectorType = 'missionway';
            // Enrich with player profile (achievements, scores, play time)
            try {
                $composition = $connector->getUser($user);
                if ($composition) {
                    $player = $composition['player'] ?? $composition;
                    $extraData['player_profile'] = [
                        'level'        => $player['level'] ?? $player['currentLevel'] ?? null,
                        'total_score'  => $player['totalScore'] ?? $player['total_score'] ?? 0,
                        'play_time'    => $player['totalPlayTime'] ?? $player['total_play_time'] ?? 0,
                        'achievements' => $player['achievements'] ?? [],
                    ];
                }
                // Recent sessions
                $playerId = $composition['player']['id'] ?? $composition['playerId'] ?? null;
                if ($playerId) {
                    $sessions = $connector->getPlayerSessions([
                        'filter' => "playerId||eq||{$playerId}",
                        'limit' => 10,
                    ]);
                    $extraData['recent_sessions'] = is_array($sessions) ? $sessions : [];

                    // Scenario breakdown (6 senaryo: village_life, world_traveler, novaris, biolab, what_if, lost_egg)
                    $scenarioMap = [
                        'village_life' => ['name' => 'Köy Hayatı', 'icon' => '🏠', 'color' => '#4364F7'],
                        'world_traveler' => ['name' => 'Dünya Gezgini', 'icon' => '🌍', 'color' => '#8B5CF6'],
                        'novaris' => ['name' => 'Novaris', 'icon' => '🏢', 'color' => '#F59E0B'],
                        'biolab' => ['name' => 'BioLab', 'icon' => '🧪', 'color' => '#10B981'],
                        'what_if' => ['name' => 'Ya Olsaydı?', 'icon' => '💡', 'color' => '#EF4444'],
                        'lost_egg' => ['name' => 'Kayıp Yumurta', 'icon' => '🔍', 'color' => '#06B6D4'],
                    ];
                    $scenarioBreakdown = [];
                    foreach ($extraData['recent_sessions'] as $sess) {
                        $simName = $sess['simulation_name'] ?? $sess['simulationName'] ?? $sess['scenario'] ?? '';
                        $simSlug = strtolower(str_replace([' ', '-'], '_', preg_replace('/[^a-zA-Z0-9_\- ]/', '', $simName)));
                        $matchedKey = 'other';
                        foreach (array_keys($scenarioMap) as $k) {
                            if (str_contains($simSlug, $k)) { $matchedKey = $k; break; }
                        }
                        if (!isset($scenarioBreakdown[$matchedKey])) {
                            $m = $scenarioMap[$matchedKey] ?? ['name' => $simName ?: 'Diğer', 'icon' => '🎮', 'color' => '#6B7280'];
                            $scenarioBreakdown[$matchedKey] = array_merge($m, ['sessions' => 0, 'total_score' => 0, 'total_time' => 0]);
                        }
                        $scenarioBreakdown[$matchedKey]['sessions']++;
                        $scenarioBreakdown[$matchedKey]['total_score'] += $sess['score'] ?? $sess['totalScore'] ?? 0;
                        $scenarioBreakdown[$matchedKey]['total_time'] += $sess['playTimeMinutes'] ?? $sess['play_time_minutes'] ?? 0;
                    }
                    foreach ($scenarioBreakdown as &$sb) {
                        $sb['avg_score'] = $sb['sessions'] > 0 ? round($sb['total_score'] / $sb['sessions']) : 0;
                    }
                    $extraData['scenario_breakdown'] = $scenarioBreakdown;
                }
            } catch (\Throwable $e) {}

        } elseif ($connector instanceof \App\Connectors\WayStartupConnector) {
            $connectorType = 'waystartup';
            // Enrich with member detail + step progress
            try {
                $member = $connector->getUser($user);
                if ($member) {
                    $memberId = $member['id'] ?? null;
                    $extraData['member'] = $member;
                    if ($memberId) {
                        $extraData['step_progress'] = $connector->getUserStepProgress($memberId) ?? [];
                        $extraData['simulations_with_progress'] = $connector->getSimulationsWithProgress($user->id) ?? [];
                    }
                }
            } catch (\Throwable $e) {}
        }

        return view('admin.applications.user-report', compact(
            'application',
            'user',
            'report',
            'connectorType',
            'extraData'
        ));
    }

    /**
     * Uygulama raporları için toplu veri toplama.
     */
    private function gatherReportData(Application $application, $connector): array
    {
        $data = [
            'connector_type' => get_class($connector),
            'user_count' => $application->users()->count(),
            'synced_count' => $application->users()->wherePivot('sync_status', 'synced')->count(),
        ];

        // Vega uygulamaları — slug bazlı zengin veri
    if ($connector instanceof \App\Connectors\VegaConnector) {
        $data['app_slug'] = $application->slug;
        $data['module'] = match ($application->slug) {
            'role-galaxy' => 'simulator',
            'way-ai-coach' => 'lecturer',
            'study-space' => 'all',
            default => 'all',
        };

        // Örnek kullanıcılardan oturum verisi çek
        $sampleUsers = $application->users()->wherePivot('sync_status', 'synced')->limit(5)->get();
        $allSessions = [];

        foreach ($sampleUsers as $user) {
            try {
                $vegaUser = $connector->findByEmail($user->email);
                if (!$vegaUser) continue;
                $vegaId = $vegaUser['id'] ?? null;
                if (!$vegaId) continue;

                $sessionsResult = $connector->getUserSessions($vegaId, $data['module']);
                $sessions = $sessionsResult['sessions'] ?? [];
                foreach ($sessions as &$s) {
                    $s['panel_user_id'] = $user->id;
                    $s['panel_user_name'] = $user->full_name;
                }
                $allSessions = array_merge($allSessions, $sessions);
            } catch (\Throwable $e) {
                \Log::warning("Vega session fetch for user {$user->id}: " . $e->getMessage());
            }
        }

        // Tarihe göre sırala (yeniden eskiye)
        usort($allSessions, fn($a, $b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));
        $data['sessions'] = array_slice($allSessions, 0, 20);
        $data['session_count'] = count($allSessions);

        // Slug bazlı istatistikler
        if ($application->slug === 'role-galaxy') {
            $scores = array_filter(array_column($allSessions, 'score'));
            $data['avg_score'] = count($scores) > 0 ? round(array_sum($scores) / count($scores)) : 0;
            $data['completed_count'] = count(array_filter($allSessions, fn($s) => ($s['status'] ?? '') === 'completed'));
        } elseif ($application->slug === 'way-ai-coach') {
            $data['total_messages'] = array_sum(array_column($allSessions, 'message_count'));
        }

        // API health
        $data['api_health'] = $connector->getHealth();

        // Anlık API: Katalog verileri
        try {
            if ($application->slug === 'way-ai-coach') {
                $data['lessons'] = $connector->getLecturerLessons();
            }
            if ($application->slug === 'role-galaxy') {
                $data['scenarios'] = $connector->getSimulatorScenarios();
            }
            $data['wings'] = $connector->getWings();
            $data['wingPoints'] = $connector->getWingPoints();
            $data['chatSessions'] = $connector->getChatSessions();
        } catch (\Throwable $e) {
            \Log::warning('Vega API catalog in admin: ' . $e->getMessage());
        }
    };
        // MissionWay — simülasyonlar + oturumlar + player composition
        if ($connector instanceof \App\Connectors\MissionWayConnector) {
            // Simülasyon listesi
            $data['simulations'] = $connector->getSimulations(['limit' => 50]) ?? [];

            // Oturum istatistikleri
            $sessions = $connector->getSimulationSessions(['limit' => 100]);
            $sessionList = [];
            if (is_array($sessions)) {
                $sessionList = isset($sessions['data']) ? $sessions['data'] : (isset($sessions[0]) ? $sessions : []);
            }
            $data['session_stats'] = [
                'total' => count($sessionList),
                'by_status' => collect($sessionList)->groupBy('status')->map(fn($group) => $group->count())->toArray(),
            ];

            // Örnek player composition
            $sampleUsers = $application->users()->limit(5)->get();
            $compositions = [];
            foreach ($sampleUsers as $user) {
                $comp = $connector->getUser($user);
                if ($comp) {
                    $compositions[] = [
                        'user_id' => $user->id,
                        'user_name' => $user->full_name,
                        'data' => $comp,
                    ];
                }
            }
            $data['sample_compositions'] = $compositions;

            // API health
            $data['api_health'] = $connector->getHealthCheck();

            // Anlık API: Katalog verileri
            try {
                $data['objectives'] = $connector->getObjectives();
                $data['mediaAssets'] = $connector->getMediaAssets();
                $data['languages'] = $connector->getLanguages();
                $data['simWingStats'] = $connector->getSimulationWingStats();
                $data['simVersionRoles'] = $connector->getSimVersionRoles();
            } catch (\Throwable $e) {
                \Log::warning('MW API catalog in admin: ' . $e->getMessage());
            }
        }

        // WayStartup — simülasyonlar + genel ilerleme
        if ($connector instanceof \App\Connectors\WayStartupConnector) {
            // Simülasyon listesi
            $data['simulations'] = $connector->getSimulations(['limit' => 50]) ?? [];

            // İlerleme ile birlikte simülasyonlar
            $data['simulations_with_progress'] = $connector->getSimulationsWithProgress() ?? [];

            // Örnek kullanıcı verileri
            $sampleUsers = $application->users()->limit(5)->get();
            $memberSamples = [];
            foreach ($sampleUsers as $user) {
                $member = $connector->getUser($user);
                if ($member) {
                    $memberSamples[] = [
                        'user_id' => $user->id,
                        'user_name' => $user->full_name,
                        'data' => $member,
                    ];
                }
            }
            $data['sample_members'] = $memberSamples;

            // API health
            $data['api_health'] = $connector->getHealthCheck();

            // Anlık API: Ek veriler
            try {
                $data['stepQuestionAnswers'] = $connector->getStepQuestionAnswers();
                $data['allMembers'] = $connector->getMembers();
            } catch (\Throwable $e) {
                \Log::warning('WS API catalog in admin: ' . $e->getMessage());
            }
        }

        return $data;
    }

    public function create()
    {
        $this->authorize('create', Application::class);
        return view('admin.applications.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Application::class);

        $request->validate([
            'slug' => 'required|string|max:100|unique:applications,slug|regex:/^[a-z0-9\-]+$/',
            'name_tr' => 'required|string|max:100',
            'name_en' => 'required|string|max:100',
            'description_tr' => 'nullable|string|max:500',
            'description_en' => 'nullable|string|max:500',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:20',
            'connector_class' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $application = Application::create([
            'slug' => $request->input('slug'),
            'name' => ['tr' => $request->input('name_tr'), 'en' => $request->input('name_en')],
            'description' => ['tr' => $request->input('description_tr', ''), 'en' => $request->input('description_en', '')],
            'icon' => $request->input('icon'),
            'color' => $request->input('color'),
            'connector_class' => $request->input('connector_class'),
            'sort_order' => $request->input('sort_order', 0),
            'is_active' => $request->boolean('is_active'),
        ]);

        ActivityLog::log('created', 'applications', $application);

        return redirect()->route('admin.applications.index')
            ->with('success', __('admin.application_created'));
    }

    public function edit(Application $application)
    {
        $this->authorize('update', $application);

        // Get raw JSON values for form
        $rawName = $application->getRawOriginal('name');
        $rawDesc = $application->getRawOriginal('description');
        $nameData = is_string($rawName) ? json_decode($rawName, true) : $rawName;
        $descData = is_string($rawDesc) ? json_decode($rawDesc, true) : $rawDesc;

        return view('admin.applications.edit', compact('application', 'nameData', 'descData'));
    }

    public function update(Request $request, Application $application)
    {
        $this->authorize('update', $application);

        $request->validate([
            'slug' => 'required|string|max:100|regex:/^[a-z0-9\-]+$/|unique:applications,slug,' . $application->id,
            'name_tr' => 'required|string|max:100',
            'name_en' => 'required|string|max:100',
            'description_tr' => 'nullable|string|max:500',
            'description_en' => 'nullable|string|max:500',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:20',
            'connector_class' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $application->update([
            'slug' => $request->input('slug'),
            'name' => ['tr' => $request->input('name_tr'), 'en' => $request->input('name_en')],
            'description' => ['tr' => $request->input('description_tr', ''), 'en' => $request->input('description_en', '')],
            'icon' => $request->input('icon'),
            'color' => $request->input('color'),
            'connector_class' => $request->input('connector_class'),
            'sort_order' => $request->input('sort_order', 0),
            'is_active' => $request->boolean('is_active'),
        ]);

        ActivityLog::log('updated', 'applications', $application);

        return redirect()->route('admin.applications.index')
            ->with('success', __('admin.application_updated'));
    }

    public function destroy(Application $application)
    {
        $this->authorize('delete', $application);

        ActivityLog::log('deleted', 'applications', $application);
        $application->delete();

        return redirect()->route('admin.applications.index')
            ->with('success', __('admin.application_deleted'));
    }

    /* ─── Connector Sync Endpoints ───────────────────── */

    /**
     * Kullanıcıyı uygulamaya ata ve senkronla.
     */
    public function assignUser(Request $request, Application $application)
    {
        $this->authorize('update', $application);

        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = \App\Models\User::findOrFail($request->input('user_id'));

        // Pivot'a ekle (zaten yoksa)
        if (!$application->users()->where('user_id', $user->id)->exists()) {
            $application->users()->attach($user->id, [
                'granted_by' => auth()->id(),
                'granted_at' => now(),
                'sync_status' => 'pending',
            ]);
        }

        // Senkronla
        $syncService = app(\App\Services\ApplicationSyncService::class);
        $result = $syncService->syncUserToApp($user, $application);

        ActivityLog::log('assigned_user', 'applications', $application, [], [
            'user_id' => $user->id,
            'sync_result' => $result['success'],
        ]);

        if ($result['success']) {
            return back()->with('success', "{$user->full_name} başarıyla senkronlandı.");
        }

        if (($result['error'] ?? '') === 'not_ready') {
            return back()->with('info', "Kullanıcı atandı. Bu uygulamanın entegrasyonu henüz tamamlanmadığı için senkronizasyon yapılamadı.");
        }

        return back()->with('warning', "Kullanıcı atandı ama senkron başarısız: {$result['error']}");
    }

    /**
     * Kullanıcıyı uygulamadan çıkar ve uzak sistemden sil.
     */
    public function removeUser(Request $request, Application $application, \App\Models\User $user)
    {
        $this->authorize('update', $application);

        $syncService = app(\App\Services\ApplicationSyncService::class);
        $syncService->removeUserFromApp($user, $application);

        ActivityLog::log('removed_user', 'applications', $application, [], [
            'user_id' => $user->id,
        ]);

        return back()->with('success', "{$user->full_name} uygulamadan çıkarıldı.");
    }

    /**
     * Kullanıcıyı tekrar senkronla (manuel).
     */
    public function syncUser(Request $request, Application $application, \App\Models\User $user)
    {
        $this->authorize('update', $application);

        $syncService = app(\App\Services\ApplicationSyncService::class);
        $result = $syncService->syncUserToApp($user, $application);

        if ($result['success']) {
            return back()->with('success', "{$user->full_name} başarıyla senkronlandı.");
        }

        if (($result['error'] ?? '') === 'not_ready') {
            return back()->with('info', 'Bu uygulamanın API entegrasyonu henüz tamamlanmadı. Senkronizasyon şu an yapılamıyor.');
        }

        return back()->with('error', "Senkron başarısız: {$result['error']}");
    }

    /**
     * Uygulamanın tüm kullanıcılarını toplu senkronla.
     */
    public function syncAll(Request $request, Application $application)
    {
        $this->authorize('update', $application);

        $syncService = app(\App\Services\ApplicationSyncService::class);
        $results = $syncService->syncAllUsersForApp($application);

        ActivityLog::log('sync_all', 'applications', $application, [], $results);

        return back()->with('success', "Toplu senkron: {$results['synced']}/{$results['total']} başarılı, {$results['failed']} başarısız.");
    }

    /**
     * Cross-app reconcile — tüm kullanıcıların tüm app'lerdeki eksiklerini tamamla.
     */
    public function reconcile(Request $request)
    {
        $crossSync = app(\App\Services\CrossAppSyncService::class);
        $results = $crossSync->reconcileAllUsers();

        ActivityLog::log('reconcile_all', 'applications', null, [], $results);

        return back()->with('success',
            "Reconcile tamamlandı: {$results['exists']} mevcut, {$results['created']} oluşturuldu, {$results['failed']} başarısız."
        );
    }

    /**
     * Manuel veri toplama tetikle — tüm app'ler veya belirli uygulama.
     */
    public function triggerHarvest(Request $request, ?Application $application = null)
    {
        $dataSyncService = app(\App\Services\ConnectorSyncService::class);

        if ($application) {
            $results = $dataSyncService->syncAllUsersForApp($application);
            $msg = "{$application->name}: {$results['success']}/{$results['total']} kullanıcı verisi güncellendi.";
        } else {
            $apps = Application::active()->whereNotNull('connector_class')->get();
            $total = ['success' => 0, 'failed' => 0];
            foreach ($apps as $app) {
                $r = $dataSyncService->syncAllUsersForApp($app);
                $total['success'] += $r['success'];
                $total['failed'] += $r['failed'];
            }
            $msg = "Tüm uygulamalar: {$total['success']} başarılı, {$total['failed']} başarısız.";
        }

        ActivityLog::log('trigger_harvest', 'applications', $application);

        return back()->with('success', "Veri toplama: {$msg}");
    }

    /**
     * Remote discovery — uzak app'teki kullanıcıları panele çek.
     */
    public function discoverRemote(Request $request, Application $application)
    {
        $this->authorize('update', $application);

        $crossSync = app(\App\Services\CrossAppSyncService::class);
        $results = $crossSync->discoverRemoteUsers($application);

        ActivityLog::log('discover_remote', 'applications', $application, [], $results);

        $orphanCount = $results['orphaned'];
        return back()->with('success',
            "Discovery: {$results['matched']} eşleşti, {$orphanCount} orphan bulundu."
        );
    }

    /**
     * Admin Session Detail — Bireysel oturum detayı.
     * Vega connector üzerinden simulator/lecturer/chatbot oturum verisi.
     */
    public function sessionDetail(Request $request, Application $application, string $sessionId)
    {
        $this->authorize('view', $application);

        $connector = $application->resolveConnector();
        if (!$connector || !($connector instanceof \App\Connectors\VegaConnector)) {
            return back()->with('error', 'Bu uygulama için oturum detayı desteklenmiyor.');
        }

        $module = match ($application->slug) {
            'role-galaxy' => 'simulator',
            'way-ai-coach' => 'lecturer',
            default => 'all',
        };

        $sessionData = $connector->getSessionDetail($sessionId, $module);

        if (!$sessionData) {
            abort(404, 'Oturum bulunamadı veya API erişilemez.');
        }

        return view('admin.applications.session-detail', [
            'application' => $application,
            'sessionData' => $sessionData,
            'sessionId' => $sessionId,
            'module' => $module,
        ]);
    }
}
