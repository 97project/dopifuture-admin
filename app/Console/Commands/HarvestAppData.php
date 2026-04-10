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
        // Prevent massive array retention on bulk inserts (Memory exhausted crash fix)
        DB::disableQueryLog();

        $startTime = microtime(true);
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

        $elapsed = round(microtime(true) - $startTime, 1);
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
        $this->harvestMwLanguages($connector);

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

        // 3. Tüm oyuncuları çek (+ profile + progress)
        $this->line('  👥 Oyuncular...');
        $this->harvestMwPlayers($app, $connector);

        // 4. Tüm session'ları toplu çek
        $this->line('  🎮 Oturumlar...');
        $this->harvestMwAllSessions($connector);

        // 4.5 BULK: Tüm session player'ları (tek paginated çağrı)
        $this->line('  🎭 Oturum Oyuncuları...');
        $this->harvestMwAllSessionPlayers($connector);

        // 5. BULK: Tüm paths (filtresiz, tek paginated çağrı)
        $this->line('  🛤️  Yollar...');
        $this->harvestMwAllPaths($connector);

        // 5.5 BULK: Tüm Player Progressleri (tek paginated çağrı)
        $this->line('  📈 İlerlemeler...');
        $this->harvestMwAllPlayerProgresses($connector);

        // 6. BULK: Tüm choices (filtresiz, tek paginated çağrı)
        $this->line('  🎯 Seçimler...');
        $this->harvestMwAllChoices($connector);

        // 7. Assignments
        $this->line('  📝 Görevler...');
        $this->harvestMwAssignments($connector);

        // 8. Translations
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
            $allSessionPlayers = [];

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
                            DB::statement('SET FOREIGN_KEY_CHECKS=0');
                            RefSimulationVersion::updateOrCreate(
                                ['id' => $versionId],
                                [
                                    'simulation_id'  => $simId,
                                    'version_number' => $versionId,
                                    'version_code'   => "v{$versionId}",
                                    'status'         => 'published',
                                    'is_default'     => false,
                                ]
                            );
                            DB::statement('SET FOREIGN_KEY_CHECKS=1');
                            $this->synced++;
                        }
                        $discoveredVersionIds[$versionId] = true;
                    }

                    DB::statement('SET FOREIGN_KEY_CHECKS=0');
                    MwSimulationSession::updateOrCreate(
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
                    DB::statement('SET FOREIGN_KEY_CHECKS=1');
                    $this->synced++;
                    $totalSessions++;

                    // Collect session players for batch upsert
                    try {
                        $sessionPlayers = $connector->getSessionPlayers($sessExtId) ?? [];
                        foreach ($sessionPlayers as $sp) {
                            $playerId = $sp['playerId'] ?? null;
                            if (!$playerId) continue;
                            $allSessionPlayers[] = [
                                'simulation_session_id' => $sessExtId,
                                'player_id'             => $playerId,
                                'role_id'               => $sp['roleId'] ?? null,
                                'joined_at'             => isset($sp['joinedAt']) ? \Carbon\Carbon::parse($sp['joinedAt'])->toDateTimeString() : now()->toDateTimeString(),
                                'created_at'            => now()->toDateTimeString(),
                                'updated_at'            => now()->toDateTimeString(),
                            ];
                        }
                    } catch (\Throwable $e) {
                        // Log but don't fail
                    }
                }

                $page++;
                $pageCount = $sessionsResp['pageCount'] ?? 1;
            } while ($page <= $pageCount && count($sessions) > 0);

            // Batch upsert all session players
            if (!empty($allSessionPlayers)) {
                DB::statement('SET FOREIGN_KEY_CHECKS=0');
                foreach (array_chunk($allSessionPlayers, 100) as $chunk) {
                    DB::table('mw_session_players')->upsert(
                        $chunk,
                        ['simulation_session_id', 'player_id'],
                        ['role_id', 'joined_at', 'updated_at']
                    );
                }
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
                $spCount = count($allSessionPlayers);
                $this->synced += $spCount;
                $this->info("    ✅ SessionPlayers: {$spCount}");
            }

            $this->info("    ✅ Sessions: {$totalSessions}");
        } catch (\Throwable $e) {
            $this->failed++;
            $this->warn("    ⚠️ Sessions: {$e->getMessage()}");
        }
    }

    /**
     * Bulk fetch ALL session-players and upsert.
     */
    private function harvestMwAllSessionPlayers(MissionWayConnector $connector): void
    {
        try {
            $page = 1;
            $total = 0;
            do {
                $result = $connector->apiGetPublic('/v1/session-players', ['limit' => 500, 'page' => $page]);
                $items = $result['data'] ?? [];

                $batch = [];
                foreach ($items as $sp) {
                    $playerId = $sp['playerId'] ?? null;
                    $sessionId = $sp['simulationSessionId'] ?? null;
                    if (!$playerId || !$sessionId) continue;

                    $batch[] = [
                        'simulation_session_id' => $sessionId,
                        'player_id'             => $playerId,
                        'role_id'               => $sp['roleId'] ?? null,
                        'joined_at'             => isset($sp['joinedAt']) ? \Carbon\Carbon::parse($sp['joinedAt']) : now(),
                        'created_at'            => now(),
                        'updated_at'            => now(),
                    ];
                }

                if (!empty($batch)) {
                    DB::statement('SET FOREIGN_KEY_CHECKS=0');
                    DB::table('mw_session_players')->upsert(
                        $batch,
                        ['simulation_session_id', 'player_id'],
                        ['role_id', 'joined_at', 'updated_at']
                    );
                    DB::statement('SET FOREIGN_KEY_CHECKS=1');
                    $total += count($batch);
                }

                $pageCount = $result['pageCount'] ?? 1;
                $page++;
            } while ($page <= $pageCount && count($items) > 0);

            $this->synced += $total;
        } catch (\Throwable $e) {
            Log::channel('daily')->error('[HarvestAppData] SessionPlayers bulk error', ['e' => $e->getMessage()]);
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
            }
        } catch (\Throwable $e) {
            $this->failed++;
            Log::channel('daily')->error('[HarvestAppData] MW sessions error', [
                'simId' => $simExternalId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * BULK: Tüm simulation paths'ı filtresiz tek paginated çağrı ile çek.
     * ~900 API çağrısı yerine ~10 çağrı (1000 per page).
     */
    private function harvestMwAllPaths(MissionWayConnector $connector): void
    {
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            $page = 1;
            $totalPaths = 0;
            do {
                $result = $connector->apiGetPublic('/v1/simulation-paths', [
                    'limit' => 500,
                    'page'  => $page,
                ]);
                $paths = $result['data'] ?? [];
                $pagePathCount = count($paths);

                $batch = [];
                $now = now()->toDateTimeString();
                foreach ($paths as $path) {
                    $pathExtId = $path['id'] ?? null;
                    if (!$pathExtId) continue;

                    $metrics = $path['metrics'] ?? null;
                    if (is_array($metrics)) {
                        $metrics = json_encode($metrics, JSON_UNESCAPED_UNICODE);
                        if (strlen($metrics) > 16000) {
                            $metrics = mb_substr($metrics, 0, 16000);
                        }
                    }

                    $batch[] = [
                        'id'                    => $pathExtId,
                        'simulation_version_id' => $path['simulationVersionId'] ?? null,
                        'parent_path_id'        => $path['parentPathId'] ?? null,
                        'path_type'             => $path['pathType'] ?? 'narrative',
                        'order_index'           => $path['orderIndex'] ?? 0,
                        'points'                => $path['points'] ?? $path['pathPoints'] ?? 0,
                        'metrics'               => $metrics,
                        'is_ended'              => $path['isEnded'] ?? false,
                        'wait_time_min'         => $path['waitTimeMin'] ?? null,
                        'wait_time_max'         => $path['waitTimeMax'] ?? null,
                        'created_at'            => $now,
                        'updated_at'            => $now,
                    ];
                }

                if (!empty($batch)) {
                    // Chunk in groups of 100 for upsert
                    foreach (array_chunk($batch, 100) as $chunk) {
                        DB::table('ref_simulation_paths')->upsert(
                            $chunk,
                            ['id'],
                            ['simulation_version_id', 'parent_path_id', 'path_type', 'order_index', 'points', 'metrics', 'is_ended', 'wait_time_min', 'wait_time_max', 'updated_at']
                        );
                    }
                    $totalPaths += count($batch);
                    $this->synced += count($batch);
                }

                unset($paths, $batch);
                $pageCount = $result['pageCount'] ?? 1;
                $page++;
            } while ($page <= $pageCount && $pagePathCount > 0);

            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            $this->info("    ✅ Paths: {$totalPaths} (" . ($page - 1) . " sayfa)");
        } catch (\Throwable $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            $this->failed++;
            $this->warn("    ⚠️ Paths: {$e->getMessage()}");
            Log::channel('daily')->error('[HarvestAppData] Bulk paths error', ['e' => $e->getMessage()]);
        }
    }

    /**
     * BULK: Tüm player choices'ı filtresiz tek paginated çağrı ile çek.
     */
    private function harvestMwAllChoices(MissionWayConnector $connector): void
    {
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            $page = 1;
            $totalChoices = 0;
            do {
                $result = $connector->apiGetPublic('/v1/player-choices', [
                    'limit' => 500,
                    'page'  => $page,
                ]);
                $choices = $result['data'] ?? [];
                $pageCount = $result['pageCount'] ?? 1;
                $choiceCount = count($choices);

                $batch = [];
                $now = now()->toDateTimeString();
                foreach ($choices as $choice) {
                    $choiceId = $choice['id'] ?? null;
                    if (!$choiceId) continue;

                    $metricsBefore = $choice['metricsBefore'] ?? null;
                    $metricsAfter = $choice['metricsAfter'] ?? null;
                    if (is_array($metricsBefore)) $metricsBefore = json_encode($metricsBefore, JSON_UNESCAPED_UNICODE);
                    if (is_array($metricsAfter)) $metricsAfter = json_encode($metricsAfter, JSON_UNESCAPED_UNICODE);

                    $batch[] = [
                        'id'                     => $choiceId,
                        'player_id'              => $choice['playerId'] ?? null,
                        'simulation_session_id'  => $choice['simulationSessionId'] ?? null,
                        'previous_path_id'       => $choice['previousPathId'] ?? null,
                        'simulation_path_id'     => $choice['simulationPathId'] ?? null,
                        'selected_path_id'       => $choice['selectedPathId'] ?? null,
                        'decided_path_id'        => $choice['decidedPathId'] ?? null,
                        'response_time_seconds'  => $choice['responseTimeSeconds'] ?? null,
                        'points_earned'          => $choice['pointsEarned'] ?? 0,
                        'is_correct'             => $choice['isCorrect'] ?? null,
                        'metrics_before'         => $metricsBefore,
                        'metrics_after'          => $metricsAfter,
                        'created_at'             => $now,
                        'updated_at'             => $now,
                    ];
                }

                if (!empty($batch)) {
                    foreach (array_chunk($batch, 100) as $chunk) {
                        DB::table('mw_player_choices')->upsert(
                            $chunk,
                            ['id'],
                            ['player_id', 'simulation_session_id', 'previous_path_id', 'simulation_path_id', 'selected_path_id', 'decided_path_id', 'response_time_seconds', 'points_earned', 'is_correct', 'metrics_before', 'metrics_after', 'updated_at']
                        );
                    }
                    $totalChoices += count($batch);
                    $this->synced += count($batch);
                }

                unset($choices, $batch);
                $page++;
            } while ($page <= $pageCount && $choiceCount > 0);

            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            $this->info("    ✅ Choices: {$totalChoices} (" . ($page - 1) . " sayfa)");
        } catch (\Throwable $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            $this->failed++;
            $this->warn("    ⚠️ Choices: {$e->getMessage()}");
            Log::channel('daily')->error('[HarvestAppData] Bulk choices error', ['e' => $e->getMessage()]);
        }
    }

    /**
     * Harvest languages from /v1/languages.
     */
    private function harvestMwLanguages(MissionWayConnector $connector): void
    {
        try {
            $result = $connector->apiGetPublic('/v1/languages', ['limit' => 100]);
            $languages = $result['data'] ?? [];
            if (empty($languages)) return;

            $batch = [];
            $now = now()->toDateTimeString();
            foreach ($languages as $lang) {
                $langId = $lang['id'] ?? null;
                if (!$langId) continue;
                $batch[] = [
                    'id'         => $langId,
                    'code'       => $lang['code'] ?? $lang['languageCode'] ?? 'unknown',
                    'name'       => $lang['name'] ?? $lang['code'] ?? 'Unknown',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if (!empty($batch)) {
                DB::table('ref_languages')->upsert($batch, ['id'], ['code', 'name', 'updated_at']);
                $this->synced += count($batch);
                $this->info("    ✅ Languages: " . count($batch));
            }
        } catch (\Throwable $e) {
            $this->warn("    ⚠️ Languages: {$e->getMessage()}");
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

                    // Email-based user eşleştirme: Way Backend'in userId'si Panel26 user.id ile
                    // eşleşmediği için (farklı ID alanları) email üzerinden eşleştir
                    $localUser = \App\Models\User::where('email', $email)->first();
                    $userId = $localUser?->id;

                    // Önce id ile bul, yoksa email/username ile eşleştir (harvest:way-db pattern)
                    $mwPlayer = MwPlayer::find($extId)
                             ?? MwPlayer::where('email', $email)->first()
                             ?? MwPlayer::where('username', $username)->first();

                    $data = [
                        'username'        => $username,
                        'email'           => $email,
                        'name'            => $player['name'] ?? 'Oyuncu',
                        'surname'         => $player['surname'] ?? '',
                        'user_id'         => $userId,
                        'organization_id' => $organizationId ?: null,
                        'avatar_media_id' => $player['avatarMediaId'] ?? $player['avatarId'] ?? null,
                        'avatar_id'       => $player['avatarId'] ?? null,
                        'preferred_language_id' => $player['preferredLanguageId'] ?? $player['languageId'] ?? null,
                        'language_id'     => $player['languageId'] ?? null,
                        'deactivated_at'  => !empty($player['deactivatedAt']) ? \Carbon\Carbon::parse($player['deactivatedAt']) : null,
                    ];

                    if ($mwPlayer) {
                        $mwPlayer->update(array_filter($data, fn($v) => $v !== null));
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

                    // NOT: mw_player_progress bulk operasyonla ayrı olarak (harvestMwAllPlayerProgresses) çekilecektir.
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

    /**
     * BULK: Tüm player progress verilerini tek bir paginated endpoint'ten çekerek kaydeder.
     * N+1 API sorgusu problemlerini çözer.
     */
    private function harvestMwAllPlayerProgresses(MissionWayConnector $connector): void
    {
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            $page = 1;
            $totalPages = 1;
            $syncedBatch = 0;

            do {
                $result = $connector->getPlayerProgressList([
                    'limit' => 500,
                    'page'  => $page,
                ]);

                $progresses = $result ?? []; // It returns data array directly
                $pageCount = count($progresses);

                $batch = [];
                $now = now()->toDateTimeString();
                foreach ($progresses as $prog) {
                    $progExtId = $prog['id'] ?? null;
                    if (!$progExtId) continue;

                    // Bulk Player Mapping ID (local mw_players mapped from playerId)
                    $localPlayerId = $prog['playerId'] ?? null;

                    $currentMetrics = $prog['currentMetrics'] ?? null;
                    if (is_array($currentMetrics)) $currentMetrics = json_encode($currentMetrics, JSON_UNESCAPED_UNICODE);

                    $batch[] = [
                        'id'                    => $progExtId,
                        'player_id'             => $localPlayerId, // The local ID corresponds directly because we forced extId onto id
                        'simulation_session_id' => $prog['simulationSessionId'] ?? null,
                        'simulation_version_id' => $prog['simulationVersionId'] ?? null,
                        'current_path_id'       => $prog['currentPathId'] ?? null,
                        'current_score'         => $prog['currentScore'] ?? 0,
                        'current_metrics'       => $currentMetrics,
                        'started_at'            => isset($prog['startedAt']) ? \Carbon\Carbon::parse($prog['startedAt'])->toDateTimeString() : null,
                        'completed_at'          => isset($prog['completedAt']) ? \Carbon\Carbon::parse($prog['completedAt'])->toDateTimeString() : null,
                        'created_at'            => isset($prog['createdAt']) ? \Carbon\Carbon::parse($prog['createdAt'])->toDateTimeString() : $now,
                        'updated_at'            => isset($prog['updatedAt']) ? \Carbon\Carbon::parse($prog['updatedAt'])->toDateTimeString() : $now,
                    ];
                }

                if (!empty($batch)) {
                    foreach (array_chunk($batch, 100) as $chunk) {
                        DB::table('mw_player_progress')->upsert(
                            $chunk,
                            ['id'],
                            ['player_id', 'simulation_session_id', 'simulation_version_id', 'current_path_id', 'current_score', 'current_metrics', 'started_at', 'completed_at', 'updated_at']
                        );
                    }
                    $syncedBatch += count($batch);
                    $this->synced += count($batch);
                }
                unset($progresses, $batch);
                $page++;
            } while ($pageCount >= 500);

            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            $this->info("    ✅ Progresses: {$syncedBatch}");
        } catch (\Throwable $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            $this->failed++;
            $this->warn("    ⚠️ Progresses Bulk Sync: {$e->getMessage()}");
            Log::channel('daily')->error('[HarvestAppData] bulk player progresses error', ['e' => $e->getMessage()]);
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

                $key = $item['key'] ?? null;
                $metricKey = $key ?: "metric_{$id}";

                RefMetricDefinition::updateOrCreate(
                    ['id' => $id],
                    [
                        'metric_key' => $metricKey,
                        'key'        => $key ?: $metricKey,
                        'name'       => $item['name'] ?? ucfirst($key ?? 'Metric'),
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
            $page = 1;
            $total = 0;

            do {
                $result = $connector->apiGetPublic('/v1/translations', ['limit' => 500, 'page' => $page]);
                $items = $result['data'] ?? [];
                $itemCount = count($items);

                $batch = [];
                $now = now()->toDateTimeString();
                foreach ($items as $item) {
                    $entityType = $item['entityType'] ?? 'unknown';
                    $entityId = $item['entityId'] ?? 0;
                    $languageId = $item['languageId'] ?? null;
                    if (!$languageId) continue;

                    $fields = $item['fields'] ?? $item['content'] ?? null;
                    if (is_array($fields)) {
                        $fields = json_encode($fields, JSON_UNESCAPED_UNICODE);
                    }

                    $batch[] = [
                        'entity_type' => $entityType,
                        'entity_id'   => $entityId,
                        'language_id' => $languageId,
                        'fields'      => $fields,
                        'created_at'  => $now,
                        'updated_at'  => $now,
                    ];
                }

                if (!empty($batch)) {
                    foreach (array_chunk($batch, 100) as $chunk) {
                        DB::table('ref_translations')->upsert(
                            $chunk,
                            ['entity_type', 'entity_id', 'language_id'],
                            ['fields', 'updated_at']
                        );
                    }
                    $total += count($batch);
                    $this->synced += count($batch);
                }

                $pageCount = $result['pageCount'] ?? 1;
                $page++;
            } while ($page <= $pageCount && $itemCount > 0);

            $this->info("    ✅ Translations: {$total}");
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
            // Per-user: getMemberByUserId çalışıyor çünkü SyncUserToAppsJob panel user.id gönderdi.
            // Not: getMembers bulk endpoint 401 veriyor, bu yüzden per-user yaklaşım kullanıyoruz.
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
}
