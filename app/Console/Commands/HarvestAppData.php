<?php

namespace App\Console\Commands;

use App\Connectors\MissionWayConnector;
use App\Connectors\WayStartupConnector;
use App\Models\Application;
use App\Models\MwPlayer;
use App\Models\MwSession;
use App\Models\MwSessionPlayer;
use App\Models\MwSimulation;
use App\Models\MwSimulationPath;
use App\Models\VegaSession;
use App\Models\VegaSessionMessage;
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
                    'VegaConnector'       => $this->harvestVega($app),
                    default => $this->line("  ⏭️  Harvest desteği yok"),
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
    //  MissionWay
    // ═══════════════════════════════════════════

    private function harvestMissionWay(Application $app, MissionWayConnector $connector): void
    {
        // 1. Simülasyonları çek
        $this->line('  📋 Simülasyonlar...');
        $simData = $connector->getSimulations(['limit' => 100]);
        $simulations = $simData['data'] ?? $simData ?? [];

        foreach ($simulations as $sim) {
            $extId = $sim['id'] ?? null;
            if (!$extId) continue;

            $mwSim = MwSimulation::updateOrCreate(
                ['external_id' => $extId],
                [
                    'application_id'     => $app->id,
                    'name'               => $sim['name'] ?? $sim['title'] ?? "Simülasyon #{$extId}",
                    'difficulty_level'   => $sim['difficultyLevel'] ?? null,
                    'status'             => !empty($sim['deactivatedAt']) ? 'inactive' : 'active',
                    'description'        => $sim['description'] ?? null,
                    'cover_image'        => $sim['coverImage'] ?? $sim['coverImageAssetId'] ?? null,
                    'min_players'        => $sim['minPlayers'] ?? 1,
                    'max_players'        => $sim['maxPlayers'] ?? 5,
                    'estimated_duration' => $sim['estimatedDuration'] ?? null,
                    'metadata'           => $sim,
                    'synced_at'          => now(),
                ]
            );
            $this->synced++;

            // 2. Her simülasyon için oturumları çek
            $this->harvestMwSessions($connector, $mwSim, $extId);
        }

        // 3. Tüm oyuncuları çek
        $this->line('  👥 Oyuncular...');
        $this->harvestMwPlayers($app, $connector);
    }

    private function harvestMwSessions(MissionWayConnector $connector, MwSimulation $mwSim, int $simExternalId): void
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

                // Player choices for this session
                $choices = [];
                try {
                    $choices = $connector->getPlayerChoices($sessExtId);
                } catch (\Throwable $e) {}

                // Session players
                $sessionPlayers = [];
                try {
                    $sessionPlayers = $connector->getSessionPlayers($sessExtId) ?? [];
                } catch (\Throwable $e) {}

                $mwSession = MwSession::updateOrCreate(
                    ['external_id' => $sessExtId],
                    [
                        'simulation_id'         => $mwSim->id,
                        'simulation_version_id' => $versionId,
                        'session_code'          => $sess['sessionCode'] ?? null,
                        'status'                => $sess['status'] ?? 'waiting',
                        'final_score'           => $sess['finalScore'] ?? null,
                        'final_metrics'         => $sess['finalMetrics'] ?? null,
                        'player_choices'        => $choices,
                        'started_at'            => isset($sess['startedAt']) ? \Carbon\Carbon::parse($sess['startedAt']) : null,
                        'completed_at'          => isset($sess['completedAt']) ? \Carbon\Carbon::parse($sess['completedAt']) : null,
                        'synced_at'             => now(),
                    ]
                );
                $this->synced++;

                // Session players → mw_session_players
                foreach ($sessionPlayers as $sp) {
                    $playerId = $sp['playerId'] ?? null;
                    if (!$playerId) continue;

                    $mwPlayer = MwPlayer::where('external_id', $playerId)->first();
                    if (!$mwPlayer) continue;

                    MwSessionPlayer::updateOrCreate(
                        ['session_id' => $mwSession->id, 'player_id' => $mwPlayer->id],
                        [
                            'role'                => $sp['role'] ?? $sp['roleName'] ?? null,
                            'grade'               => $sp['grade'] ?? null,
                            'completed_decisions' => $sp['completedDecisions'] ?? $sp['completed'] ?? 0,
                            'total_decisions'     => $sp['totalDecisions'] ?? $sp['total'] ?? 0,
                            'health_metric'       => $sp['healthMetric'] ?? $sp['health'] ?? 0,
                            'resource_metric'     => $sp['resourceMetric'] ?? $sp['resource'] ?? 0,
                            'ethics_metric'       => $sp['ethicsMetric'] ?? $sp['ethics'] ?? 0,
                            'adaptation_metric'   => $sp['adaptationMetric'] ?? $sp['adaptation'] ?? 0,
                            'joined_at'           => isset($sp['joinedAt']) ? \Carbon\Carbon::parse($sp['joinedAt']) : null,
                        ]
                    );
                }

                // Simulation paths (bir kez version başına)
                if ($versionId && !MwSimulationPath::where('simulation_version_id', $versionId)->exists()) {
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

                MwSimulationPath::updateOrCreate(
                    ['simulation_version_id' => $versionId, 'external_id' => $pathExtId],
                    [
                        'parent_path_id' => $path['parentPathId'] ?? $path['parent_path_id'] ?? null,
                        'path_type'      => $path['pathType'] ?? $path['path_type'] ?? 'narrative',
                        'order_index'    => $path['orderIndex'] ?? 0,
                        'points'         => $path['points'] ?? $path['pathPoints'] ?? 0,
                        'metrics'        => $path['metrics'] ?? null,
                        'translations'   => $path['translations'] ?? null,
                        'is_ended'       => $path['isEnded'] ?? false,
                        'synced_at'      => now(),
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

                    // Player profile
                    $profile = null;
                    try {
                        $profile = $connector->getPlayerProfile($extId);
                    } catch (\Throwable $e) {}

                    // userId → user eşleştirmesi
                    $userId = $player['userId'] ?? null;

                    MwPlayer::updateOrCreate(
                        ['external_id' => $extId],
                        [
                            'user_id'        => $userId,
                            'application_id' => $app->id,
                            'name'           => $player['name'] ?? 'Oyuncu',
                            'surname'        => $player['surname'] ?? '',
                            'email'          => $player['email'] ?? null,
                            'username'       => $player['username'] ?? null,
                            'profile_data'   => $profile,
                            'synced_at'      => now(),
                        ]
                    );
                    $this->synced++;
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

    // ═══════════════════════════════════════════
    //  WayStartup
    // ═══════════════════════════════════════════

    private function harvestWayStartup(Application $app, WayStartupConnector $connector): void
    {
        // 1. Tools kataloğu
        $this->line('  🧰 Araçlar...');
        try {
            $rawTools = $connector->getTools();
            // Eski kayıtları temizle, yenileri yaz
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
                $this->synced++;

                // 3. Her simülasyon için adımları çek
                $this->harvestWsSteps($connector, $wsSim, $extId);
            }
        } catch (\Throwable $e) {
            $this->failed++;
            $this->error("  ❌ Simülasyonlar: {$e->getMessage()}");
        }
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

    // ═══════════════════════════════════════════
    //  Vega (Way AI Coach / Role Galaxy / Study Space)
    //  Doğrudan DB'den okuyor — API auth sorunu yok
    // ═══════════════════════════════════════════

    private function harvestVega(Application $app): void
    {
        // Vega DB bağlantısı var mı kontrol et
        try {
            DB::connection('vega_db')->getPdo();
        } catch (\Throwable $e) {
            $this->error("  ❌ Vega DB bağlantısı kurulamadı: {$e->getMessage()}");
            $this->failed++;
            return;
        }

        $this->line('  📋 Vega oturumları (doğrudan DB)...');

        // 1. Vega'dan tüm oturumları çek
        $vegaSessions = DB::connection('vega_db')
            ->table('vega_sessions')
            ->orderBy('created_at', 'desc')
            ->get();

        $this->line("  📊 Toplam {$vegaSessions->count()} oturum bulundu");

        // Email→User eşleme tablosu (Vega user_id string, panel26'da users tablosunda user_id int)
        // Vega'da user_id aslında users tablosundaki id. Email ile eşleştirelim.
        $vegaUsers = DB::connection('vega_db')
            ->table('users')
            ->select('id', 'email', 'name', 'surname')
            ->get()
            ->keyBy('id');

        // Panel26'daki kullanıcıları email ile eşle
        $localUsers = \App\Models\User::all()->keyBy('email');

        $bar = $this->output->createProgressBar($vegaSessions->count());
        $bar->start();

        foreach ($vegaSessions as $vs) {
            $bar->advance();

            // external_session_id NULL ise Vega DB id'sini fallback olarak kullan
            $extSessionId = $vs->external_session_id;
            if (!$extSessionId) {
                $extSessionId = 'vega_internal_' . $vs->id;
            }

            // Vega user_id → panel26 user_id (email ile eşle)
            // Vega user_id string ('7', 'unknown', 'anon') olabilir
            $localUserId = null;
            $userName = null;
            $userSurname = null;

            $vegaUserId = $vs->user_id;
            // Numeric user_id ise Vega users tablosundan bul
            if ($vegaUserId && is_numeric($vegaUserId)) {
                $vegaUser = $vegaUsers->get((int) $vegaUserId) ?? $vegaUsers->get((string) $vegaUserId);
                if ($vegaUser) {
                    $userName = $vegaUser->name;
                    $userSurname = $vegaUser->surname ?? '';
                    $localUser = $localUsers->get($vegaUser->email);
                    if ($localUser) {
                        $localUserId = $localUser->id;
                    }
                }
            } elseif ($vegaUserId && $vegaUserId !== 'unknown' && $vegaUserId !== 'anon') {
                // String user_id (ör. 'panel_test') — eşleştirme yok ama kaydet
                $userName = $vegaUserId;
            }

            // Duration hesapla
            $durationMin = null;
            if ($vs->created_at && $vs->updated_at) {
                $start = \Carbon\Carbon::parse($vs->created_at);
                $end = \Carbon\Carbon::parse($vs->updated_at);
                $durationMin = (int) $start->diffInMinutes($end);
                if ($durationMin < 1) $durationMin = null;
            }

            // Oturumu kaydet — module'e göre doğru application_id ata
            $module = $vs->module ?? 'chatbot';
            $appId = match($module) {
                'chatbot' => \App\Models\Application::where('slug', 'way-ai-coach')->value('id') ?? $app->id,
                default   => $app->id, // simulator, lecturer → role-galaxy
            };
            $localSession = VegaSession::updateOrCreate(
                ['external_id' => $extSessionId],
                [
                    'user_id'          => $localUserId,
                    'application_id'   => $appId,
                    'module'           => $module,
                    'user_name'        => $userName ?? 'Öğrenci',
                    'user_surname'     => $userSurname ?? '',
                    'score'            => $vs->score,
                    'duration_minutes' => $durationMin,
                    'summary'          => $vs->sim_state ? json_decode($vs->sim_state, true) : null,
                    'started_at'       => $vs->created_at ? \Carbon\Carbon::parse($vs->created_at) : null,
                    'ended_at'         => $vs->updated_at_ext ? \Carbon\Carbon::parse($vs->updated_at_ext) : null,
                    'synced_at'        => now(),
                ]
            );
            $this->synced++;

            // Mesajları sil ve yeniden yaz
            VegaSessionMessage::where('session_id', $localSession->id)->delete();

            $module = $vs->module ?? 'chatbot';

            if ($module === 'lecturer') {
                // Lecturer mesajları
                $messages = DB::connection('vega_db')
                    ->table('vega_lecturer_messages')
                    ->where('session_id', $vs->id)
                    ->orderBy('created_at', 'asc')
                    ->get();

                foreach ($messages as $idx => $msg) {
                    VegaSessionMessage::create([
                        'session_id'  => $localSession->id,
                        'role'        => $msg->role ?? 'user',
                        'content'     => $msg->content ?? null,
                        'order_index' => $idx,
                    ]);
                }
            } elseif ($module === 'simulator') {
                // Simulator adımları → mesaj olarak kaydet
                $steps = DB::connection('vega_db')
                    ->table('vega_simulator_steps')
                    ->where('session_id', $vs->id)
                    ->orderBy('turn', 'asc')
                    ->get();

                foreach ($steps as $step) {
                    // Narrative/coach_reply → assistant
                    if ($step->node_text || $step->coach_reply) {
                        VegaSessionMessage::create([
                            'session_id'  => $localSession->id,
                            'role'        => 'assistant',
                            'content'     => ($step->node_text ?? '') . ($step->coach_reply ? "\n\n" . $step->coach_reply : ''),
                            'score'       => $step->score_after,
                            'metrics'     => json_encode([
                                'delta'     => $step->delta,
                                'threshold' => $step->threshold_after,
                                'node_id'   => $step->node_id,
                            ]),
                            'options'     => $step->choices ? json_decode($step->choices, true) : null,
                            'order_index' => $step->turn * 2,
                        ]);
                    }
                    // User seçimi
                    if ($step->selected_choice_id || $step->selected_choice_text) {
                        VegaSessionMessage::create([
                            'session_id'  => $localSession->id,
                            'role'        => 'user',
                            'content'     => $step->selected_choice_text ?? "Seçim: {$step->selected_choice_id}",
                            'order_index' => $step->turn * 2 + 1,
                        ]);
                    }
                }
            } elseif ($module === 'chatbot') {
                // Chat mesajları
                $messages = DB::connection('vega_db')
                    ->table('vega_chat_messages')
                    ->where('session_id', $vs->id)
                    ->orderBy('created_at', 'asc')
                    ->get();

                foreach ($messages as $idx => $msg) {
                    $metadata = $msg->metadata ? json_decode($msg->metadata, true) : null;
                    VegaSessionMessage::create([
                        'session_id'  => $localSession->id,
                        'role'        => $msg->role ?? 'user',
                        'content'     => $msg->content ?? null,
                        'order_index' => $idx,
                    ]);
                }
            }
        }

        $bar->finish();
        $this->newLine();
    }
}
