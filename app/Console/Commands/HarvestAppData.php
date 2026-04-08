<?php

namespace App\Console\Commands;

use App\Connectors\MissionWayConnector;
use App\Connectors\VegaConnector;
use App\Connectors\WayStartupConnector;
use App\Models\Application;
use App\Models\MissionWay\MwAssignment;
use App\Models\MissionWay\MwAssignmentPlayer;
use App\Models\MissionWay\MwPlayer;
use App\Models\MissionWay\MwPlayerChoice;
use App\Models\MissionWay\MwPlayerProfile;
use App\Models\MissionWay\MwPlayerProgress;
use App\Models\MissionWay\MwSessionPlayer;
use App\Models\MissionWay\MwSimulationSession;
use App\Models\MissionWay\RefInfoCard;
use App\Models\MissionWay\RefMediaAsset;
use App\Models\MissionWay\RefMetricBandCategory;
use App\Models\MissionWay\RefMetricDefinition;
use App\Models\MissionWay\RefObjective;
use App\Models\MissionWay\RefPathObjective;
use App\Models\MissionWay\RefRole;
use App\Models\MissionWay\RefSimulation;
use App\Models\MissionWay\RefSimulationMetricBand;
use App\Models\MissionWay\RefSimulationPath;
use App\Models\MissionWay\RefSimulationVersion;
use App\Models\MissionWay\RefSimulationVersionRole;
use App\Models\MissionWay\RefTranslation;
use App\Models\Vega\VegaLesson;
use App\Models\Vega\VegaScenario;
use App\Models\Vega\VegaWing;
use App\Models\VegaSession;
use App\Models\VegaSessionMessage;
use App\Models\WsMember;
use App\Models\WsSimulation;
use App\Models\WsStep;
use App\Models\WsStepEvaluation;
use App\Models\WsStepQuestion;
use App\Models\WsStepQuestionAnswer;
use App\Models\WsTool;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Uygulama düzeyindeki verileri API'dan çekip kalıcı tablolara yazar.
 * Simülasyonlar, oturumlar, oyuncular, yollar, adımlar, araçlar.
 *
 * Usage:
 *   php artisan harvest:app-data              → tüm uygulamalar
 *   php artisan harvest:app-data --app=mission-way  → tek uygulama
 */
class HarvestAppData extends Command
{
    protected $signature = 'harvest:app-data {--app= : App slug (optional)}';
    protected $description = 'Uygulama-düzeyi verileri (simülasyon, oturum, arçlar) API\'den çekip DB\'ye yaz';

    private int $synced = 0;
    private int $failed = 0;

    public function handle(): int
    {
        $startTime = now();
        $this->info('🏗️ Uygulama verileri toplanıyor...');
        $this->newLine();

        if ($slug = $this->option('app')) {
            $apps = Application::where('slug', $slug)->active()->get();
        } else {
            $apps = Application::active()->whereNotNull('connector_class')->ordered()->get();
        }

        foreach ($apps as $app) {
            $connector = $app->resolveConnector();
            if (!$connector) continue;

            $connectorClass = class_basename($connector);
            $this->comment("→ {$app->getTranslation('name')} [{$connectorClass}]");

            try {
                match ($connectorClass) {
                    'MissionWayConnector' => $this->harvestMissionWay($app, $connector),
                    'WayStartupConnector' => $this->harvestWayStartup($app, $connector),
                    'VegaConnector'       => $this->harvestVega($app, $connector),
                    default => $this->line("  ⏭️  Harvest desteği yok (connector: {$connectorClass})"),
                };
            } catch (\Throwable $e) {
                $this->failed++;
                $this->error("  ❌ {$e->getMessage()}");
                Log::channel('daily')->error('[HarvestAppData] Exception', [
                    'app' => $app->slug,
                    'error' => $e->getMessage(),
                ]);
            }

            $this->newLine();
        }

        $elapsed = now()->diffInSeconds($startTime);
        $this->table(['Metrik', 'Değer'], [
            ['Sync edilen', $this->synced],
            ['Başarısız', $this->failed],
            ['Süre', "{$elapsed}s"],
        ]);

        return self::SUCCESS;
    }

    // ═══════════════════════════════════════════
    //  MissionWay — 1:1 PostgreSQL Parite
    // ═══════════════════════════════════════════

    private function harvestMissionWay(Application $app, MissionWayConnector $connector): void
    {
        // 1. Ref tabloları — metric definitions, band categories, roles
        $this->line('  📊 Referans verileri...');
        $this->harvestMwMetricDefinitions($connector);
        $this->harvestMwMetricBandCategories($connector);
        $this->harvestMwRoles($connector);

        // 2. Simülasyonları çek → ref_simulations
        $this->line('  📋 Simülasyonlar...');
        $simData = $connector->getSimulations(['limit' => 100]);
        $simulations = $simData['data'] ?? $simData ?? [];

        foreach ($simulations as $sim) {
            $extId = $sim['id'] ?? null;
            if (!$extId) continue;

            RefSimulation::updateOrCreate(
                ['id' => $extId],
                [
                    'name'               => $sim['name'] ?? $sim['title'] ?? "Simülasyon #{$extId}",
                    'difficulty'         => $sim['difficultyLevel'] ?? $sim['difficulty'] ?? null,
                    'description'        => $sim['description'] ?? null,
                    'min_players'        => $sim['minPlayers'] ?? 1,
                    'max_players'        => $sim['maxPlayers'] ?? 5,
                    'background_image_path' => $sim['backgroundImageFileUrl'] ?? $sim['backgroundImagePath'] ?? $sim['coverImageFileUrl'] ?? null,
                    'deactivated_at'     => !empty($sim['deactivatedAt']) ? \Carbon\Carbon::parse($sim['deactivatedAt']) : null,
                ]
            );
            $this->synced++;
        }

        // 3. Tüm oyuncuları çek (+ profile + progress) — ÖNCE oyuncular, sonra session player eşleşmesi
        $this->line('  👥 Oyuncular...');
        $this->harvestMwPlayers($app, $connector);

        // 4. Tüm session'ları toplu çek
        $this->line('  🎮 Oturumlar...');
        $this->harvestMwAllSessions($connector);

        // 5. Assignments
        $this->line('  📝 Görevler...');
        $this->harvestMwAssignments($connector);

        // 6. Translations (tüm simulation_path'ler için)
        $this->line('  🌐 Çeviriler...');
        $this->harvestMwTranslations($connector);

        // 7. Objectives & Path Objectives
        $this->line('  🎯 Hedefler...');
        $this->harvestMwObjectives($connector);

        // 8. Media Assets
        $this->line('  🖼️ Medya varlıkları...');
        $this->harvestMwMediaAssets($connector);

        // 9. Simulation Version Roles
        $this->line('  🎭 Versiyon rolleri...');
        $this->harvestMwSimVersionRoles($connector);

        // 10. SimulationWing Stats (cache/log)
        $this->line('  📊 SimulationWing...');
        $this->harvestMwSimWingStats($connector);
    }

    /**
     * Fetch ALL sessions in bulk, auto-create versions, and link to simulations.
     */
    private function harvestMwAllSessions(MissionWayConnector $connector): void
    {
        try {
            $page = 1;
            $totalSessions = 0;
            $discoveredVersionIds = [];

            do {
                $sessionsResp = $connector->getSimulationSessions([
                    'limit' => 200,
                    'page'  => $page,
                ]);
                $sessions = $sessionsResp['data'] ?? $sessionsResp ?? [];

                foreach ($sessions as $sess) {
                    $sessExtId = $sess['id'] ?? null;
                    if (!$sessExtId) continue;

                    $versionId = $sess['simulationVersionId'] ?? null;

                    // Auto-create version if not exists
                    if ($versionId && !isset($discoveredVersionIds[$versionId])) {
                        $existingVersion = RefSimulationVersion::find($versionId);
                        if (!$existingVersion) {
                            $simId = $this->resolveSimulationIdForVersion($versionId, $connector);
                            \DB::statement('SET FOREIGN_KEY_CHECKS=0');
                            RefSimulationVersion::updateOrCreate(
                                [
                                    'simulation_id'  => $simId,
                                    'version_number' => $versionId
                                ],
                                [
                                    // Make sure we update/create with the passed ID if it's new
                                    'version_code'   => "v{$versionId}",
                                    'status'         => 'published',
                                    'is_default'     => true,
                                ]
                            );
                            \DB::statement('SET FOREIGN_KEY_CHECKS=1');
                            $this->synced++;
                        }
                        $discoveredVersionIds[$versionId] = true;
                    }

                    \DB::statement('SET FOREIGN_KEY_CHECKS=0');
                    $mwSession = MwSimulationSession::updateOrCreate(
                        ['id' => $sessExtId],
                        [
                            'simulation_version_id' => $versionId,
                            'session_code'          => $sess['sessionCode'] ?? null,
                            'status'                => $sess['status'] ?? 'waiting',
                            'game_mode'             => $sess['gameMode'] ?? null,
                            'final_score'           => $sess['finalScore'] ?? null,
                            'final_metrics'         => $sess['finalMetrics'] ?? null,
                            'final_path_id'         => $sess['finalPathId'] ?? null,
                            'started_at'            => isset($sess['startedAt']) ? \Carbon\Carbon::parse($sess['startedAt']) : null,
                            'completed_at'          => isset($sess['completedAt']) ? \Carbon\Carbon::parse($sess['completedAt']) : null,
                            'abandoned_at'          => isset($sess['abandonedAt']) ? \Carbon\Carbon::parse($sess['abandonedAt']) : null,
                        ]
                    );
                    \DB::statement('SET FOREIGN_KEY_CHECKS=1');
                    $this->synced++;
                    $totalSessions++;

                    // Session players
                    try {
                        $sessionPlayers = $connector->getSessionPlayers($sessExtId) ?? [];
                        foreach ($sessionPlayers as $sp) {
                            $playerId = $sp['playerId'] ?? null;
                            if (!$playerId) continue;

                            $mwPlayer = MwPlayer::find($playerId);
                            if (!$mwPlayer) continue;

                            MwSessionPlayer::updateOrCreate(
                                ['simulation_session_id' => $mwSession->id, 'player_id' => $mwPlayer->id],
                                [
                                    'role_id'   => $sp['roleId'] ?? null,
                                    'joined_at' => isset($sp['joinedAt']) ? \Carbon\Carbon::parse($sp['joinedAt']) : now(),
                                ]
                            );
                        }
                    } catch (\Throwable $e) {}

                    // Player choices
                    try {
                        $choices = $connector->getPlayerChoices($sessExtId);
                        if (is_array($choices)) {
                            foreach ($choices as $choice) {
                                $choicePlayerId = $choice['playerId'] ?? null;
                                if (!$choicePlayerId) continue;

                                MwPlayerChoice::updateOrCreate(
                                    ['id' => $choice['id'] ?? null],
                                    [
                                        'player_id'              => $choicePlayerId,
                                        'simulation_session_id'  => $mwSession->id,
                                        'previous_path_id'       => $choice['previousPathId'] ?? null,
                                        'simulation_path_id'     => $choice['simulationPathId'] ?? null,
                                        'selected_path_id'       => $choice['selectedPathId'] ?? null,
                                        'decided_path_id'        => $choice['decidedPathId'] ?? null,
                                        'response_time_seconds'  => $choice['responseTimeSeconds'] ?? null,
                                        'points_earned'          => $choice['pointsEarned'] ?? 0,
                                        'is_correct'             => $choice['isCorrect'] ?? null,
                                        'metrics_before'         => $choice['metricsBefore'] ?? null,
                                        'metrics_after'          => $choice['metricsAfter'] ?? null,
                                    ]
                                );
                            }
                        }
                    } catch (\Throwable $e) {}

                    // Simulation paths (once per version)
                    if ($versionId && !RefSimulationPath::where('simulation_version_id', $versionId)->exists()) {
                        $this->harvestMwPaths($connector, $versionId);
                    }
                }

                $page++;
                $pageCount = $sessionsResp['pageCount'] ?? 1;
            } while ($page <= $pageCount && count($sessions) > 0);

            $this->info("    ✅ Sessions: {$totalSessions}");
        } catch (\Throwable $e) {
            $this->failed++;
            $this->warn("    ⚠️ Sessions: {$e->getMessage()}");
        }
    }

    /**
     * Try to determine which simulation a version belongs to.
     * Uses assignment table or defaults to sim ID 1.
     */
    private function resolveSimulationIdForVersion(int $versionId, MissionWayConnector $connector): int
    {
        // Check if any assignment references a session with this version
        $assignment = MwAssignment::whereHas('session', function ($q) use ($versionId) {
            $q->where('simulation_version_id', $versionId);
        })->first();

        if ($assignment) {
            return $assignment->simulation_id;
        }

        // Default: assign to first simulation
        $firstSim = RefSimulation::first();
        return $firstSim?->id ?? 1;
    }

    private function harvestMwSessions(MissionWayConnector $connector, RefSimulation $refSim, int $simExternalId): void
    {
        try {
            $sessionsResp = $connector->getSimulationSessions([
                'filter' => "simulationId||eq||{$simExternalId}",
                'limit'  => 200,
            ]);
            $sessions = $sessionsResp['data'] ?? $sessionsResp ?? [];

            foreach ($sessions as $sess) {
                $sessExtId = $sess['id'] ?? null;
                if (!$sessExtId) continue;

                $versionId = $sess['simulationVersionId'] ?? null;

                $mwSession = MwSimulationSession::updateOrCreate(
                    ['id' => $sessExtId],
                    [
                        'simulation_version_id' => $versionId,
                        'session_code'          => $sess['sessionCode'] ?? null,
                        'status'                => $sess['status'] ?? 'waiting',
                        'game_mode'             => $sess['gameMode'] ?? null,
                        'final_score'           => $sess['finalScore'] ?? null,
                        'final_metrics'         => $sess['finalMetrics'] ?? null,
                        'final_path_id'         => $sess['finalPathId'] ?? null,
                        'started_at'            => isset($sess['startedAt']) ? \Carbon\Carbon::parse($sess['startedAt']) : null,
                        'completed_at'          => isset($sess['completedAt']) ? \Carbon\Carbon::parse($sess['completedAt']) : null,
                        'abandoned_at'          => isset($sess['abandonedAt']) ? \Carbon\Carbon::parse($sess['abandonedAt']) : null,
                    ]
                );
                $this->synced++;

                // Session players → mw_session_players
                $sessionPlayers = [];
                try {
                    $sessionPlayers = $connector->getSessionPlayers($sessExtId) ?? [];
                } catch (\Throwable $e) {}

                foreach ($sessionPlayers as $sp) {
                    $playerId = $sp['playerId'] ?? null;
                    if (!$playerId) continue;

                    $mwPlayer = MwPlayer::find($playerId);
                    if (!$mwPlayer) continue;

                    $roleId = $sp['roleId'] ?? null;

                    MwSessionPlayer::updateOrCreate(
                        ['simulation_session_id' => $mwSession->id, 'player_id' => $mwPlayer->id],
                        [
                            'role_id'   => $roleId,
                            'joined_at' => isset($sp['joinedAt']) ? \Carbon\Carbon::parse($sp['joinedAt']) : now(),
                        ]
                    );
                }

                // Player choices → mw_player_choices
                try {
                    $choices = $connector->getPlayerChoices($sessExtId);
                    if (is_array($choices)) {
                        foreach ($choices as $choice) {
                            $choicePlayerId = $choice['playerId'] ?? null;
                            if (!$choicePlayerId) continue;

                            MwPlayerChoice::updateOrCreate(
                                ['id' => $choice['id'] ?? null],
                                [
                                    'player_id'              => $choicePlayerId,
                                    'simulation_session_id'  => $mwSession->id,
                                    'previous_path_id'       => $choice['previousPathId'] ?? null,
                                    'simulation_path_id'     => $choice['simulationPathId'] ?? null,
                                    'selected_path_id'       => $choice['selectedPathId'] ?? null,
                                    'decided_path_id'        => $choice['decidedPathId'] ?? null,
                                    'response_time_seconds'  => $choice['responseTimeSeconds'] ?? null,
                                    'points_earned'          => $choice['pointsEarned'] ?? 0,
                                    'is_correct'             => $choice['isCorrect'] ?? null,
                                    'metrics_before'         => $choice['metricsBefore'] ?? null,
                                    'metrics_after'          => $choice['metricsAfter'] ?? null,
                                ]
                            );
                        }
                    }
                } catch (\Throwable $e) {}

                // Simulation paths (bir kez version başına)
                if ($versionId && !RefSimulationPath::where('simulation_version_id', $versionId)->exists()) {
                    $this->harvestMwPaths($connector, $versionId);
                }
            }
        } catch (\Throwable $e) {
            $this->failed++;
            Log::channel('daily')->error('[HarvestAppData] MW sessions error', [
                'simId' => $simExternalId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function harvestMwPaths(MissionWayConnector $connector, int $versionId): void
    {
        try {
            $paths = $connector->getSimulationPaths($versionId);
            foreach ($paths as $path) {
                $pathExtId = $path['id'] ?? null;
                if (!$pathExtId) continue;

                RefSimulationPath::updateOrCreate(
                    ['id' => $pathExtId],
                    [
                        'simulation_version_id' => $versionId,
                        'parent_path_id'        => $path['parentPathId'] ?? $path['parent_path_id'] ?? null,
                        'path_type'             => $path['pathType'] ?? $path['path_type'] ?? 'narrative',
                        'order_index'           => $path['orderIndex'] ?? 0,
                        'points'                => $path['points'] ?? $path['pathPoints'] ?? 0,
                        'metrics'               => $path['metrics'] ?? null,
                        'is_ended'              => $path['isEnded'] ?? false,
                        'wait_time_min'         => $path['waitTimeMin'] ?? null,
                        'wait_time_max'         => $path['waitTimeMax'] ?? null,
                    ]
                );
                $this->synced++;
            }
        } catch (\Throwable $e) {
            $this->failed++;
        }
    }

    private function harvestMwPlayers(Application $app, MissionWayConnector $connector): void
    {
        try {
            $page = 1;
            do {
                $result = $connector->getPlayers(['limit' => 50, 'page' => $page]);
                $players = $result['data'] ?? [];

                foreach ($players as $player) {
                    $extId = $player['id'] ?? null;
                    if (!$extId) continue;

                    $userId = $player['userId'] ?? null;
                    $organizationId = $player['organizationId'] ?? null;
                    $email = $player['email'] ?? "{$extId}@missionway.local";
                    $username = $player['username'] ?? $email;

                    // ── EMAIL-BASED user matching ──
                    // Way Backend'in userId'si Vega'nın iç ID'sidir, panel26 user ID'si DEĞİLDİR.
                    // Doğru eşleştirme: email üzerinden portal users tablosundan resolve et.
                    $portalUser = \App\Models\User::where('email', $email)->first();
                    $localUserId = $portalUser?->id;

                    // Önce id ile bul, yoksa email/username ile eşleştir (harvest:way-db pattern)
                    $mwPlayer = MwPlayer::find($extId)
                             ?? MwPlayer::where('email', $email)->first()
                             ?? MwPlayer::where('username', $username)->first();

                    $data = [
                        'username'        => $username,
                        'email'           => $email,
                        'name'            => $player['name'] ?? 'Oyuncu',
                        'surname'         => $player['surname'] ?? '',
                        'user_id'         => $localUserId,
                        'organization_id' => $organizationId ?: null,
                        'avatar_media_id' => $player['avatarMediaId'] ?? $player['avatarId'] ?? null,
                        'avatar_id'       => $player['avatarId'] ?? null,
                        'preferred_language_id' => $player['preferredLanguageId'] ?? $player['languageId'] ?? null,
                        'language_id'     => $player['languageId'] ?? null,
                        'deactivated_at'  => !empty($player['deactivatedAt']) ? \Carbon\Carbon::parse($player['deactivatedAt']) : null,
                    ];

                    if ($mwPlayer) {
                        // user_id her zaman açıkça güncellenmeli (null dahil — eski yanlış Vega ID'leri temizlenir)
                        $updateData = array_filter($data, fn($v) => $v !== null);
                        $updateData['user_id'] = $localUserId; // null olsa bile yaz
                        $mwPlayer->update($updateData);
                    } else {
                        $mwPlayer = MwPlayer::create(array_merge(['id' => $extId], $data));
                    }
                    $this->synced++;

                    // Player profile → mw_player_profiles
                    try {
                        $profile = $connector->getPlayerProfile($extId);
                        if ($profile) {
                            MwPlayerProfile::updateOrCreate(
                                ['player_id' => $mwPlayer->id],
                                [
                                    'total_score'     => $profile['totalScore'] ?? 0,
                                    'games_played'    => $profile['gamesPlayed'] ?? 0,
                                    'games_won'       => $profile['gamesWon'] ?? 0,
                                    'achievements'    => $profile['achievements'] ?? null,
                                    'statistics'      => $profile['statistics'] ?? null,
                                    'metric_stats'    => $profile['metricStats'] ?? null,
                                ]
                            );
                        }
                    } catch (\Throwable $e) {}

                    // Player progress → mw_player_progress
                    try {
                        $progressList = $connector->getPlayerProgressList([
                            'filter' => "playerId||eq||{$extId}",
                        ]);
                        if (is_array($progressList)) {
                            foreach ($progressList as $prog) {
                                $progId = $prog['id'] ?? null;
                                if (!$progId) continue;

                                MwPlayerProgress::updateOrCreate(
                                    ['id' => $progId],
                                    [
                                        'player_id'              => $mwPlayer->id,
                                        'simulation_session_id'  => $prog['simulationSessionId'] ?? null,
                                        'simulation_version_id'  => $prog['simulationVersionId'] ?? null,
                                        'current_path_id'        => $prog['currentPathId'] ?? null,
                                        'current_score'          => $prog['currentScore'] ?? 0,
                                        'current_metrics'        => $prog['currentMetrics'] ?? null,
                                        'started_at'             => isset($prog['startedAt']) ? \Carbon\Carbon::parse($prog['startedAt']) : null,
                                        'completed_at'           => isset($prog['completedAt']) ? \Carbon\Carbon::parse($prog['completedAt']) : null,
                                    ]
                                );
                                $this->synced++;
                            }
                        }
                    } catch (\Throwable $e) {}
                }

                $page++;
                $pageCount = $result['pageCount'] ?? 1;
            } while ($page <= $pageCount);
        } catch (\Throwable $e) {
            $this->failed++;
            Log::channel('daily')->error('[HarvestAppData] MW players error', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    // ── MW Ref Data Harvesters ────────────────────────

    private function harvestMwMetricDefinitions(MissionWayConnector $connector): void
    {
        try {
            $items = $connector->getMetricDefinitions();
            if (!is_array($items) || empty($items)) {
                $this->warn('    ⚠️ MetricDefinitions: API boş döndü (401?). Referans verileri için harvest:way-db kullanın.');
                return;
            }

            foreach ($items as $item) {
                $id = $item['id'] ?? null;
                if (!$id) continue;

                RefMetricDefinition::updateOrCreate(
                    ['id' => $id],
                    [
                        'metric_key' => $item['key'] ?? ('unknown_' . $id),
                        'key'        => $item['key'] ?? ('unknown_' . $id),
                        'name'       => $item['name'] ?? ucfirst($item['key'] ?? 'Metric'),
                        'icon'       => $item['icon'] ?? '📊',
                        'color'      => $item['color'] ?? '#6B7280',
                        'unit_label' => $item['unitLabel'] ?? null,
                    ]
                );
                $this->synced++;
            }
            $this->info("    ✅ MetricDefinitions: " . count($items));
        } catch (\Throwable $e) {
            $this->failed++;
            $this->warn("    ⚠️ MetricDefinitions: {$e->getMessage()}");
        }
    }

    private function harvestMwMetricBandCategories(MissionWayConnector $connector): void
    {
        try {
            $items = $connector->getMetricBandCategories();
            if (!is_array($items) || empty($items)) {
                $this->warn('    ⚠️ MetricBandCategories: API boş döndü (401?). Referans verileri için harvest:way-db kullanın.');
                return;
            }

            foreach ($items as $item) {
                $id = $item['id'] ?? null;
                if (!$id) continue;

                RefMetricBandCategory::updateOrCreate(
                    ['id' => $id],
                    [
                        'key'            => $item['key'] ?? 'unknown',
                        'label'          => $item['label'] ?? $item['name'] ?? ucfirst($item['key'] ?? ''),
                        'color'          => $item['color'] ?? '#6B7280',
                        'scoring_impact' => $item['scoringImpact'] ?? 0,
                    ]
                );
                $this->synced++;
            }
            $this->info("    ✅ MetricBandCategories: " . count($items));
        } catch (\Throwable $e) {
            $this->failed++;
            $this->warn("    ⚠️ MetricBandCategories: {$e->getMessage()}");
        }
    }

    private function harvestMwMetricBands(MissionWayConnector $connector, int $versionId): void
    {
        try {
            $items = $connector->getSimulationMetricBands($versionId);
            foreach ($items as $item) {
                $id = $item['id'] ?? null;
                if (!$id) continue;

                RefSimulationMetricBand::updateOrCreate(
                    ['id' => $id],
                    [
                        'simulation_version_id' => $versionId,
                        'metric_id'             => $item['metricId'] ?? $item['metricDefinitionId'] ?? null,
                        'metric_key'            => $item['metricKey'] ?? null,
                        'category_id'           => $item['categoryId'] ?? $item['metricBandCategoryId'] ?? null,
                        'min_value'             => $item['minValue'] ?? 0,
                        'max_value'             => $item['maxValue'] ?? 100,
                    ]
                );
                $this->synced++;
            }
        } catch (\Throwable $e) {
            // Metric bands are optional per version — don't fail hard
        }
    }

    private function harvestMwRoles(MissionWayConnector $connector): void
    {
        try {
            $items = $connector->getRoles();
            if (!is_array($items) || empty($items)) {
                $this->warn('    ⚠️ Roles: API boş döndü (401?). Referans verileri için harvest:way-db kullanın.');
                return;
            }

            foreach ($items as $item) {
                $id = $item['id'] ?? null;
                if (!$id) continue;

                RefRole::updateOrCreate(
                    ['id' => $id],
                    [
                        'name'       => $item['name'] ?? 'Role',
                        'key'        => $item['key'] ?? null,
                        'icon'       => $item['icon'] ?? null,
                        'color'      => $item['color'] ?? null,
                    ]
                );
                $this->synced++;
            }
            $this->info("    ✅ Roles: " . count($items));
        } catch (\Throwable $e) {
            $this->failed++;
            $this->warn("    ⚠️ Roles: {$e->getMessage()}");
        }
    }

    private function harvestMwAssignments(MissionWayConnector $connector): void
    {
        try {
            $items = $connector->getAssignments();
            if (!is_array($items)) return;

            foreach ($items as $item) {
                $id = $item['id'] ?? null;
                if (!$id) continue;

                // FK safety: session might not exist locally yet
                $sessionId = $item['simulationSessionId'] ?? null;
                if ($sessionId && !MwSimulationSession::find($sessionId)) {
                    $sessionId = null;
                }

                $assignment = MwAssignment::updateOrCreate(
                    ['id' => $id],
                    [
                        'simulation_id'          => $item['simulationId'] ?? null,
                        'simulation_session_id'  => $sessionId,
                        'grade'                  => $item['grade'] ?? null,
                        'deadline'               => isset($item['deadline']) ? \Carbon\Carbon::parse($item['deadline']) : null,
                        'status'                 => $item['status'] ?? 'active',
                        'created_by'             => $item['createdBy'] ?? null,
                    ]
                );
                $this->synced++;

                // Assignment players
                try {
                    $players = $item['players'] ?? $connector->getAssignmentPlayers($id);
                    if (is_array($players)) {
                        foreach ($players as $ap) {
                            $playerId = $ap['playerId'] ?? $ap['id'] ?? null;
                            if (!$playerId) continue;

                            MwAssignmentPlayer::updateOrCreate(
                                ['assignment_id' => $assignment->id, 'player_id' => $playerId],
                                [
                                    'status' => $ap['status'] ?? 'assigned',
                                ]
                            );
                        }
                    }
                } catch (\Throwable $e) {}
            }
            $this->info("    ✅ Assignments: " . count($items));
        } catch (\Throwable $e) {
            $this->failed++;
            $this->warn("    ⚠️ Assignments: {$e->getMessage()}");
        }
    }

    private function harvestMwTranslations(MissionWayConnector $connector): void
    {
        try {
            $items = $connector->getTranslations(['limit' => 1000]);
            if (!is_array($items)) return;

            foreach ($items as $item) {
                $id = $item['id'] ?? null;
                if (!$id) continue;

                RefTranslation::updateOrCreate(
                    ['id' => $id],
                    [
                        'entity_type' => $item['entityType'] ?? 'unknown',
                        'entity_id'   => $item['entityId'] ?? 0,
                        'language_id' => $item['languageId'] ?? null,
                        'fields'      => $item['fields'] ?? $item['content'] ?? null,
                    ]
                );
                $this->synced++;
            }
            $this->info("    ✅ Translations: " . count($items));
        } catch (\Throwable $e) {
            $this->failed++;
            $this->warn("    ⚠️ Translations: {$e->getMessage()}");
        }
    }

    private function harvestMwObjectives(MissionWayConnector $connector): void
    {
        try {
            $items = $connector->getObjectives(['limit' => 500]);
            foreach ($items as $item) {
                $id = $item['id'] ?? null;
                if (!$id) continue;
                RefObjective::updateOrCreate(
                    ['id' => $id],
                    [
                        'name'        => $item['name'] ?? $item['title'] ?? "Objective #{$id}",
                        'description' => $item['description'] ?? null,
                        'key'         => $item['key'] ?? $item['slug'] ?? null,
                    ]
                );
                $this->synced++;
            }

            // Path Objectives
            $pathItems = $connector->getPathObjectives(['limit' => 1000]);
            foreach ($pathItems as $po) {
                $poId = $po['id'] ?? null;
                if (!$poId) continue;
                RefPathObjective::updateOrCreate(
                    ['id' => $poId],
                    [
                        'simulation_path_id' => $po['simulationPathId'] ?? $po['simulation_path_id'] ?? null,
                        'objective_id'       => $po['objectiveId'] ?? $po['objective_id'] ?? null,
                        'target_value'       => $po['targetValue'] ?? $po['target_value'] ?? null,
                        'weight'             => $po['weight'] ?? null,
                    ]
                );
                $this->synced++;
            }
            $this->info('    ✅ Objectives: ' . count($items) . ', PathObjectives: ' . count($pathItems));
        } catch (\Throwable $e) {
            $this->failed++;
            $this->warn("    ⚠️ Objectives: {$e->getMessage()}");
        }
    }

    private function harvestMwMediaAssets(MissionWayConnector $connector): void
    {
        try {
            $items = $connector->getMediaAssets(['limit' => 500]);
            foreach ($items as $item) {
                $id = $item['id'] ?? null;
                if (!$id) continue;
                RefMediaAsset::updateOrCreate(
                    ['id' => $id],
                    [
                        'name'      => $item['name'] ?? $item['title'] ?? null,
                        'type'      => $item['type'] ?? $item['assetType'] ?? null,
                        'file_url'  => $item['fileUrl'] ?? $item['url'] ?? null,
                        'file_key'  => $item['fileKey'] ?? $item['key'] ?? null,
                        'mime_type' => $item['mimeType'] ?? $item['contentType'] ?? null,
                        'file_size' => $item['fileSize'] ?? $item['size'] ?? null,
                        'metadata'  => $item,
                    ]
                );
                $this->synced++;
            }
            $this->info('    ✅ MediaAssets: ' . count($items));
        } catch (\Throwable $e) {
            $this->failed++;
            $this->warn("    ⚠️ MediaAssets: {$e->getMessage()}");
        }
    }

    private function harvestMwSimVersionRoles(MissionWayConnector $connector): void
    {
        try {
            $items = $connector->getSimVersionRoles(['limit' => 500]);
            foreach ($items as $item) {
                $id = $item['id'] ?? null;
                if (!$id) continue;
                RefSimulationVersionRole::updateOrCreate(
                    ['id' => $id],
                    [
                        'simulation_version_id' => $item['simulationVersionId'] ?? $item['simulation_version_id'] ?? null,
                        'role_id'               => $item['roleId'] ?? $item['role_id'] ?? null,
                        'name'                  => $item['name'] ?? $item['roleName'] ?? null,
                    ]
                );
                $this->synced++;
            }
            $this->info('    ✅ VersionRoles: ' . count($items));
        } catch (\Throwable $e) {
            $this->failed++;
            $this->warn("    ⚠️ VersionRoles: {$e->getMessage()}");
        }
    }

    private function harvestMwSimWingStats(MissionWayConnector $connector): void
    {
        try {
            $stats = $connector->getSimulationWingStats();
            if ($stats) {
                // Cache to settings or log for dashboard use
                \Cache::put('simulation_wing_stats', $stats, now()->addMinutes(30));
                $this->info('    ✅ SimulationWing stats cached');
                $this->synced++;
            } else {
                $this->warn('    ⚠️ SimulationWing stats boş döndü');
            }
        } catch (\Throwable $e) {
            $this->failed++;
            $this->warn("    ⚠️ SimWingStats: {$e->getMessage()}");
        }
    }

    // ═══════════════════════════════════════════
    //  WayStartup
    // ═══════════════════════════════════════════

    private function harvestWayStartup(Application $app, WayStartupConnector $connector): void
    {
        // 1. Tools kataloğu
        $this->line('  🧰 Araçlar...');
        try {
            $rawTools = $connector->getTools();
            WsTool::where('application_id', $app->id)->delete();
            foreach ($rawTools as $t) {
                $tool = $t['tool'] ?? $t;
                WsTool::create([
                    'application_id' => $app->id,
                    'name'           => $tool['name'] ?? $t['name'] ?? '-',
                    'description'    => $tool['description'] ?? '',
                    'icon_url'       => $tool['iconUrl'] ?? $tool['icon_url'] ?? '',
                    'website_url'    => $tool['websiteUrl'] ?? $tool['website_url'] ?? '',
                    'category'       => $tool['category'] ?? $tool['type'] ?? 'Genel',
                    'synced_at'      => now(),
                ]);
                $this->synced++;
            }
        } catch (\Throwable $e) {
            $this->failed++;
            $this->warn("  ⚠️  Araçlar çekilemedi: {$e->getMessage()}");
        }

        // 2. Simülasyonları çek
        $this->line('  📋 Simülasyonlar...');
        $wsSimulations = collect();
        try {
            $simData = $connector->getSimulations(['limit' => 100]);
            $simulations = $simData['data'] ?? $simData ?? [];

            foreach ($simulations as $sim) {
                $extId = $sim['id'] ?? null;
                if (!$extId) continue;

                $wsSim = WsSimulation::updateOrCreate(
                    ['external_id' => $extId],
                    [
                        'application_id' => $app->id,
                        'name'           => $sim['name'] ?? "Proje #{$extId}",
                        'type'           => $sim['type'] ?? null,
                        'category'       => $sim['category'] ?? null,
                        'status'         => $sim['status'] ?? 'active',
                        'metadata'       => $sim,
                        'synced_at'      => now(),
                    ]
                );
                $wsSimulations->push($wsSim);
                $this->synced++;

                // 3. Her simülasyon için adımları çek
                $this->harvestWsSteps($connector, $wsSim, $extId);
            }
        } catch (\Throwable $e) {
            $this->failed++;
            $this->error("  ❌ Simülasyonlar: {$e->getMessage()}");
        }

        // 4. Members — portal kullanıcılarını WS üye verileriyle eşleştir
        $this->line('  👥 Üyeler...');
        $this->harvestWsMembers($app, $connector, $wsSimulations);
    }

    private function harvestWsSteps(WayStartupConnector $connector, WsSimulation $wsSim, int $simExternalId): void
    {
        try {
            $stepsData = $connector->getSteps($simExternalId);
            if (!is_array($stepsData)) return;

            foreach ($stepsData as $idx => $step) {
                $stepExtId = $step['id'] ?? null;
                if (!$stepExtId) continue;

                // Step tools
                $stepTools = [];
                try {
                    $rawTools = $connector->getStepTools($stepExtId);
                    foreach ($rawTools as $st) {
                        $tool = $st['tool'] ?? [];
                        $stepTools[] = [
                            'name'           => $tool['name'] ?? $st['toolName'] ?? '-',
                            'description'    => $tool['description'] ?? '',
                            'icon_url'       => $tool['iconUrl'] ?? $tool['icon_url'] ?? '',
                            'website_url'    => $tool['websiteUrl'] ?? $tool['website_url'] ?? '',
                            'category'       => $tool['category'] ?? '',
                            'is_recommended' => $st['isRecommended'] ?? false,
                            'custom_note'    => $st['customNote'] ?? '',
                        ];
                    }
                } catch (\Throwable $e) {}

                // Step questions
                $stepQuestions = [];
                try {
                    if (method_exists($connector, 'getStepQuestions')) {
                        $rawQ = $connector->getStepQuestions($stepExtId);
                        if (is_array($rawQ)) {
                            $stepQuestions = array_map(fn($q) => [
                                'text'      => $q['question'] ?? $q['questionText'] ?? '-',
                                'max_score' => $q['maxScore'] ?? $q['aiMaxScore'] ?? 0,
                                'score'     => $q['score'] ?? $q['aiScore'] ?? null,
                                'feedback'  => $q['feedback'] ?? $q['aiFeedback'] ?? null,
                            ], $rawQ);
                        }
                    }
                } catch (\Throwable $e) {}

                $wsStep = WsStep::updateOrCreate(
                    ['external_id' => $stepExtId],
                    [
                        'simulation_id'   => $wsSim->id,
                        'name'            => $step['name'] ?? $step['title'] ?? "Adım #{$idx}",
                        'difficulty'      => $step['difficulty'] ?? $step['difficultyLevel'] ?? null,
                        'skill'           => $step['skill'] ?? null,
                        'responsible_name' => $step['responsibleName'] ?? $step['assignee'] ?? null,
                        'points'          => $step['score'] ?? $step['point'] ?? $step['points'] ?? 0,
                        'max_score'       => $step['maxScore'] ?? $step['maxPoint'] ?? 150,
                        'ai_score'        => $step['aiScore'] ?? null,
                        'order_index'     => $idx,
                        'tools'           => $stepTools,
                        'questions'       => $stepQuestions,
                        'synced_at'       => now(),
                    ]
                );
                $this->synced++;

                // Write questions to normalized table
                try {
                    if (method_exists($connector, 'getStepQuestions')) {
                        $rawQuestions = $connector->getStepQuestions($stepExtId);
                        if (is_array($rawQuestions)) {
                            foreach ($rawQuestions as $q) {
                                $qExtId = $q['id'] ?? null;
                                if (!$qExtId) continue;

                                WsStepQuestion::updateOrCreate(
                                    ['external_id' => $qExtId],
                                    [
                                        'step_id'       => $wsStep->id,
                                        'question_text' => $q['questionText'] ?? $q['question'] ?? '-',
                                        'max_score'     => $q['maxScore'] ?? 0,
                                        'sort_order'    => $q['sortOrder'] ?? 0,
                                        'is_required'   => $q['isRequired'] ?? true,
                                        'synced_at'     => now(),
                                    ]
                                );
                                $this->synced++;
                            }
                        }
                    }
                } catch (\Throwable $e) {}
            }
        } catch (\Throwable $e) {
            $this->failed++;
        }
    }

    // ── WS Member + Progress Harvesters ────────────────

    private function harvestWsMembers(Application $app, WayStartupConnector $connector, $wsSimulations): void
    {
        try {
            // Sadece bu uygulamaya atanmış kullanıcıları sorgula (tüm user tablosunu sorgulamak 404 spam'ine neden olur)
            $portalUsers = $app->users()->get();
            if ($portalUsers->isEmpty()) {
                $portalUsers = \App\Models\User::all();
            }
            $membersHarvested = 0;

            foreach ($portalUsers as $user) {
                try {
                    $memberData = $connector->getMemberByUserId((string) $user->id);
                    if (!$memberData || !isset($memberData['id'])) continue;

                    $extId = $memberData['id'];

                    // User progress (simülasyon bazlı ilerleme)
                    $stepProgress = [];
                    $stepEvaluations = [];
                    $stepSubmissions = [];

                    try {
                        $progress = $connector->getUserStepProgress($extId);
                        if (is_array($progress)) {
                            $stepProgress = $progress;
                        }
                    } catch (\Throwable $e) {}

                    // Step submissions — her simülasyon için
                    foreach ($wsSimulations as $wsSim) {
                        try {
                            $subs = $connector->getStepSubmissions($wsSim->external_id);
                            if (is_array($subs)) {
                                foreach ($subs as $sub) {
                                    if (($sub['memberId'] ?? null) == $extId) {
                                        $stepSubmissions[] = $sub;
                                    }
                                }
                            }
                        } catch (\Throwable $e) {}
                    }

                    // ── MEMBER KAYDI — evaluations'dan ÖNCE oluşturulmalı ──
                    $localMember = WsMember::updateOrCreate(
                        ['external_id' => $extId],
                        [
                            'user_id'          => $user->id,
                            'application_id'   => $app->id,
                            'points'           => $memberData['points'] ?? 0,
                            'step_progress'    => $stepProgress,
                            'step_submissions' => $stepSubmissions,
                            'synced_at'        => now(),
                        ]
                    );
                    $membersHarvested++;
                    $this->synced++;

                    // ── EVALUATIONS (member artık DB'de var) ──

                    try {
                        $evals = $connector->getStepQuestionEvaluations($extId);
                        if (is_array($evals)) {
                            $stepEvaluations = $evals;

                            // Write evaluations to normalized table
                            foreach ($evals as $ev) {
                                $evStepId = $ev['stepId'] ?? $ev['step_id'] ?? null;
                                if (!$evStepId) continue;

                                $localStep = WsStep::where('external_id', $evStepId)->first();
                                if (!$localStep) continue;

                                $wsEval = WsStepEvaluation::updateOrCreate(
                                    ['step_id' => $localStep->id, 'member_id' => $localMember->id, 'attempt' => $ev['attempt'] ?? 1],
                                    [
                                        'external_id'         => $ev['id'] ?? null,
                                        'ai_total_score'      => $ev['aiTotalScore'] ?? $ev['earnedPoint'] ?? 0,
                                        'ai_max_score'        => $ev['aiMaxScore'] ?? $ev['maxScore'] ?? 0,
                                        'ai_coins'            => $ev['aiCoins'] ?? $ev['earnedCoin'] ?? 0,
                                        'ai_overall_feedback' => $ev['aiOverallFeedback'] ?? null,
                                        'status'              => $ev['status'] ?? 'COMPLETED',
                                        'ai_evaluated_at'     => isset($ev['aiEvaluatedAt']) ? \Carbon\Carbon::parse($ev['aiEvaluatedAt']) : null,
                                        'synced_at'           => now(),
                                    ]
                                );
                                $this->synced++;

                                // Write per-question answers if evaluation has questions detail
                                $evalQuestions = $ev['questions'] ?? [];
                                foreach ($evalQuestions as $eq) {
                                    $questionNumber = $eq['questionNumber'] ?? $eq['sortOrder'] ?? null;
                                    if ($questionNumber === null) continue;

                                    // Find the local question by step + sort_order
                                    $localQuestion = WsStepQuestion::where('step_id', $localStep->id)
                                        ->where('sort_order', $questionNumber - 1)
                                        ->first();
                                    // Fallback: try by sort_order matching questionNumber directly
                                    if (!$localQuestion) {
                                        $localQuestion = WsStepQuestion::where('step_id', $localStep->id)
                                            ->where('sort_order', $questionNumber)
                                            ->first();
                                    }
                                    if (!$localQuestion) continue;

                                    WsStepQuestionAnswer::updateOrCreate(
                                        ['question_id' => $localQuestion->id, 'member_id' => $localMember->id, 'attempt' => $ev['attempt'] ?? 1],
                                        [
                                            'text_answer'  => $eq['userAnswer'] ?? $eq['textAnswer'] ?? '',
                                            'ai_score'     => $eq['score'] ?? $eq['aiScore'] ?? 0,
                                            'ai_max_score' => $eq['maxScore'] ?? $eq['aiMaxScore'] ?? 0,
                                            'ai_feedback'  => $eq['feedback'] ?? $eq['aiFeedback'] ?? null,
                                            'synced_at'    => now(),
                                        ]
                                    );
                                    $this->synced++;
                                }
                            }
                        }
                    } catch (\Throwable $e) {}

                    // stepEvaluations JSON'u da member'a kaydet
                    if (!empty($stepEvaluations)) {
                        $localMember->update(['step_evaluations' => $stepEvaluations]);
                    }

                } catch (\Throwable $e) {
                    // Kullanıcı WS'de yoksa devam et
                    continue;
                }
            }

            $this->info("    ✅ Members: {$membersHarvested}");
        } catch (\Throwable $e) {
            $this->failed++;
            $this->warn("    ⚠️ Members: {$e->getMessage()}");
        }
    }

    // ═══════════════════════════════════════════
    //  Vega (Way AI Coach / Role Galaxy)
    // ═══════════════════════════════════════════

    private function harvestVega(Application $app, VegaConnector $connector): void
    {
        // 1. Lecturer Lessons
        $this->line('  📚 Dersler (Lecturer)...');
        try {
            $lessons = $connector->getLecturerLessons();
            foreach ($lessons as $lesson) {
                $extId = $lesson['id'] ?? null;
                if (!$extId) continue;
                VegaLesson::updateOrCreate(
                    ['external_id' => $extId],
                    [
                        'title'            => $lesson['title'] ?? $lesson['name'] ?? "Ders #{$extId}",
                        'description'      => $lesson['description'] ?? null,
                        'category'         => $lesson['category'] ?? $lesson['type'] ?? null,
                        'difficulty'       => $lesson['difficulty'] ?? $lesson['level'] ?? null,
                        'duration_minutes' => $lesson['duration'] ?? $lesson['durationMinutes'] ?? null,
                        'icon_url'         => $lesson['iconUrl'] ?? $lesson['icon'] ?? null,
                        'metadata'         => $lesson,
                        'synced_at'        => now(),
                    ]
                );
                $this->synced++;
            }
            $this->info('    ✅ Lessons: ' . count($lessons));
        } catch (\Throwable $e) {
            $this->failed++;
            $this->warn("    ⚠️ Lessons: {$e->getMessage()}");
        }

        // 2. Simulator Scenarios
        $this->line('  🎮 Senaryolar (Simulator)...');
        try {
            $scenarios = $connector->getSimulatorScenarios();
            foreach ($scenarios as $scenario) {
                $extId = $scenario['id'] ?? null;
                if (!$extId) continue;
                VegaScenario::updateOrCreate(
                    ['external_id' => $extId],
                    [
                        'title'       => $scenario['title'] ?? $scenario['name'] ?? "Senaryo #{$extId}",
                        'description' => $scenario['description'] ?? null,
                        'category'    => $scenario['category'] ?? $scenario['type'] ?? null,
                        'difficulty'  => $scenario['difficulty'] ?? $scenario['level'] ?? null,
                        'icon_url'    => $scenario['iconUrl'] ?? $scenario['icon'] ?? null,
                        'metadata'    => $scenario,
                        'synced_at'   => now(),
                    ]
                );
                $this->synced++;
            }
            $this->info('    ✅ Scenarios: ' . count($scenarios));
        } catch (\Throwable $e) {
            $this->failed++;
            $this->warn("    ⚠️ Scenarios: {$e->getMessage()}");
        }

        // 3. WayWing Badges
        $this->line('  🦋 Kanatlar (WayWing)...');
        try {
            $wings = $connector->getWings();
            foreach ($wings as $wing) {
                $extId = $wing['id'] ?? null;
                if (!$extId) continue;
                VegaWing::updateOrCreate(
                    ['external_id' => $extId],
                    [
                        'name'             => $wing['name'] ?? $wing['title'] ?? "Wing #{$extId}",
                        'description'      => $wing['description'] ?? null,
                        'icon_url'         => $wing['iconUrl'] ?? $wing['icon'] ?? null,
                        'points_required'  => $wing['pointsRequired'] ?? $wing['points'] ?? 0,
                        'metadata'         => $wing,
                        'synced_at'        => now(),
                    ]
                );
                $this->synced++;
            }
            $this->info('    ✅ Wings: ' . count($wings));
        } catch (\Throwable $e) {
            $this->failed++;
            $this->warn("    ⚠️ Wings: {$e->getMessage()}");
        }

        // 4. Chat Sessions (Study Space) — API → DB fallback
        $this->line('  💬 Chat oturumları (Study Space)...');
        $this->harvestVegaChatSessions($app);

        // 5. Wing Points (istatistik)
        $this->line('  📊 Kanat puanları...');
        try {
            $points = $connector->getWingPoints();
            if (!empty($points)) {
                \Cache::put('vega_wing_points', $points, now()->addMinutes(30));
                $this->info('    ✅ WingPoints cached');
            }
        } catch (\Throwable $e) {
            $this->warn("    ⚠️ WingPoints: {$e->getMessage()}");
        }
    }

    /**
     * Chat Sessions (Study Space) harvest:
     * 1. Önce API ile dener (getChatSessions)
     * 2. API boş dönerse Vega DB'ye doğrudan bağlanıp vega_sessions + vega_chat_messages çeker
     * 3. Email ile local user eşleştirmesi yapar
     */
    private function harvestVegaChatSessions(Application $app): void
    {
        // 1. Önce API'yi dene
        try {
            $connector = new VegaConnector();
            $chatSessions = $connector->getChatSessions();
            if (!empty($chatSessions)) {
                $this->info('    ✅ ChatSessions via API: ' . count($chatSessions));
                // API'den veri geldiyse local'e kaydet
                foreach ($chatSessions as $cs) {
                    $this->upsertVegaChatSession($app, $cs);
                }
                return;
            }
        } catch (\Throwable $e) {
            // API başarısız — DB fallback'e devam
        }

        // 2. DB Fallback — Vega SQL'e doğrudan bağlan
        $this->line('    🔄 API boş, Vega DB fallback...');
        try {
            // Vega DB bağlantısını kontrol et
            $vegaDb = DB::connection('vega_db');
            $vegaDb->getPdo(); // test connection

            // Email → local user_id mapping cache'i oluştur
            $localUsers = \App\Models\User::pluck('id', 'email')
                ->mapWithKeys(fn($id, $email) => [mb_strtolower($email) => $id]);

            // Vega DB'den chatbot oturumlarını çek
            // vega_sessions tablosunda module = 'chatbot' olanlar Study Space'e ait
            $remoteSessions = $vegaDb->table('vega_sessions')
                ->where('module', 'chatbot')
                ->orderBy('id')
                ->get();

            if ($remoteSessions->isEmpty()) {
                $this->info('    ℹ️  ChatSessions: Vega DB\'de chatbot oturumu yok');
                return;
            }

            // Vega users tablosundan user_id → email mapping
            $vegaUserIds = $remoteSessions->pluck('user_id')->unique()->filter();
            $vegaUsers = $vegaDb->table('users')
                ->whereIn('id', $vegaUserIds)
                ->pluck('email', 'id')
                ->mapWithKeys(fn($email, $id) => [(string) $id => mb_strtolower($email ?? '')]);

            $sessionsSynced = 0;
            $messagesSynced = 0;

            foreach ($remoteSessions as $rs) {
                // Vega user_id → email → local user_id
                $vegaEmail = $vegaUsers[(string) $rs->user_id] ?? null;
                $localUserId = $vegaEmail ? ($localUsers[$vegaEmail] ?? null) : null;

                $localSession = VegaSession::updateOrCreate(
                    ['external_id' => $rs->external_session_id ?? ('vega_chat_' . $rs->id)],
                    [
                        'user_id'        => $localUserId,
                        'application_id' => $app->id,
                        'module'         => 'chatbot',
                        'user_name'      => null,
                        'user_surname'   => null,
                        'score'          => $rs->score,
                        'duration_minutes' => null,
                        'summary'        => [
                            'subject' => $rs->subject,
                            'topic'   => $rs->topic,
                            'grade'   => $rs->grade,
                            'title'   => $rs->title,
                            'status'  => $rs->status,
                        ],
                        'started_at'     => $rs->created_at_ext ?? $rs->created_at,
                        'ended_at'       => $rs->updated_at_ext ?? $rs->updated_at,
                        'synced_at'      => now(),
                    ]
                );
                $sessionsSynced++;
                $this->synced++;

                // Chat mesajlarını çek
                try {
                    $remoteMessages = $vegaDb->table('vega_chat_messages')
                        ->where('session_id', $rs->id)
                        ->orderBy('created_at')
                        ->get();

                    foreach ($remoteMessages as $idx => $rm) {
                        VegaSessionMessage::updateOrCreate(
                            ['session_id' => $localSession->id, 'order_index' => $idx],
                            [
                                'role'    => $rm->role ?? 'user',
                                'content' => $rm->content,
                                'score'   => null,
                            ]
                        );
                        $messagesSynced++;
                        $this->synced++;
                    }
                } catch (\Throwable $e) {
                    // Mesajlar opsiyonel — devam et
                }
            }

            $this->info("    ✅ ChatSessions via DB: {$sessionsSynced} sessions, {$messagesSynced} messages");
        } catch (\Throwable $e) {
            $this->failed++;
            $this->warn("    ⚠️ ChatSessions DB fallback hatası: {$e->getMessage()}");
        }
    }

    /**
     * API'den gelen tek bir chat session'ı local'e kaydet.
     */
    private function upsertVegaChatSession(Application $app, array $cs): void
    {
        $extId = $cs['id'] ?? $cs['sessionId'] ?? null;
        if (!$extId) return;

        VegaSession::updateOrCreate(
            ['external_id' => (string) $extId],
            [
                'application_id' => $app->id,
                'module'         => 'chatbot',
                'summary'        => $cs,
                'started_at'     => isset($cs['createdAt']) ? \Carbon\Carbon::parse($cs['createdAt']) : null,
                'ended_at'       => isset($cs['updatedAt']) ? \Carbon\Carbon::parse($cs['updatedAt']) : null,
                'synced_at'      => now(),
            ]
        );
        $this->synced++;
    }
}
