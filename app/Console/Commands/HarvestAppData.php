<?php

namespace App\Console\Commands;

use App\Connectors\MissionWayConnector;
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
use App\Models\MissionWay\RefMetricBandCategory;
use App\Models\MissionWay\RefMetricDefinition;
use App\Models\MissionWay\RefRole;
use App\Models\MissionWay\RefSimulation;
use App\Models\MissionWay\RefSimulationMetricBand;
use App\Models\MissionWay\RefSimulationPath;
use App\Models\MissionWay\RefSimulationVersion;
use App\Models\MissionWay\RefTranslation;
use App\Models\WsMember;
use App\Models\WsSimulation;
use App\Models\WsStep;
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
                            RefSimulationVersion::create([
                                'id'             => $versionId,
                                'simulation_id'  => $simId,
                                'version_number' => $versionId,
                                'version_code'   => "v{$versionId}",
                                'status'         => 'published',
                                'is_default'     => true,
                            ]);
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

                    $mwPlayer = MwPlayer::updateOrCreate(
                        ['id' => $extId],
                        [
                            'username'        => $player['username'] ?? $player['email'] ?? "player_{$extId}",
                            'email'           => $player['email'] ?? "{$extId}@missionway.local",
                            'name'            => $player['name'] ?? 'Oyuncu',
                            'surname'         => $player['surname'] ?? '',
                            'user_id'         => $userId,
                            'organization_id' => $organizationId,
                            'avatar_media_id' => $player['avatarMediaId'] ?? $player['avatarId'] ?? null,
                            'avatar_id'       => $player['avatarId'] ?? null,
                            'preferred_language_id' => $player['preferredLanguageId'] ?? $player['languageId'] ?? null,
                            'language_id'     => $player['languageId'] ?? null,
                            'deactivated_at'  => !empty($player['deactivatedAt']) ? \Carbon\Carbon::parse($player['deactivatedAt']) : null,
                        ]
                    );
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
            if (!is_array($items)) return;

            foreach ($items as $item) {
                $id = $item['id'] ?? null;
                if (!$id) continue;

                RefMetricDefinition::updateOrCreate(
                    ['id' => $id],
                    [
                        'metric_key' => $item['key'] ?? 'unknown',
                        'key'        => $item['key'] ?? 'unknown',
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
            if (!is_array($items)) return;

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
            if (!is_array($items)) return;

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

                WsStep::updateOrCreate(
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
            }
        } catch (\Throwable $e) {
            $this->failed++;
        }
    }

    // ── WS Member + Progress Harvesters ────────────────

    private function harvestWsMembers(Application $app, WayStartupConnector $connector, $wsSimulations): void
    {
        try {
            // Portal'daki tüm kullanıcıları al — her biri WS'de member olabilir
            $portalUsers = \App\Models\User::all();
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

                    try {
                        $evals = $connector->getStepQuestionEvaluations($extId);
                        if (is_array($evals)) {
                            $stepEvaluations = $evals;
                        }
                    } catch (\Throwable $e) {}

                    // Step submissions — her simülasyon için
                    foreach ($wsSimulations as $wsSim) {
                        try {
                            $subs = $connector->getStepSubmissions($wsSim->external_id);
                            if (is_array($subs)) {
                                // Bu member'a ait submission'ları filtrele
                                foreach ($subs as $sub) {
                                    if (($sub['memberId'] ?? null) == $extId) {
                                        $stepSubmissions[] = $sub;
                                    }
                                }
                            }
                        } catch (\Throwable $e) {}
                    }

                    WsMember::updateOrCreate(
                        ['external_id' => $extId],
                        [
                            'user_id'          => $user->id,
                            'application_id'   => $app->id,
                            'points'           => $memberData['points'] ?? 0,
                            'step_progress'    => $stepProgress,
                            'step_evaluations' => $stepEvaluations,
                            'step_submissions' => $stepSubmissions,
                            'synced_at'        => now(),
                        ]
                    );
                    $membersHarvested++;
                    $this->synced++;
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
}
