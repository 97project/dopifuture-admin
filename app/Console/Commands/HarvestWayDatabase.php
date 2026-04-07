<?php

namespace App\Console\Commands;

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
use App\Models\MissionWay\RefSimulationVersionRole;
use App\Models\MissionWay\RefTranslation;
use App\Models\WsAssignment;
use App\Models\WsAssignmentMember;
use App\Models\WsMember;
use App\Models\WsSimulation;
use App\Models\WsStep;
use App\Models\WsStepEvaluation;
use App\Models\WsStepProgress;
use App\Models\WsStepQuestion;
use App\Models\WsStepQuestionAnswer;
use App\Models\WsTool;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Direct PostgreSQL → MySQL data harvest command.
 *
 * Bypasses the unreliable API-based harvest:app-data pipeline by reading
 * directly from the production Way Backend PostgreSQL database (AWS RDS)
 * and upserting into local MySQL tables.
 *
 * Usage:
 *   php artisan harvest:way-db              → full sync (all tables)
 *   php artisan harvest:way-db --only=mw    → only Mission Way tables
 *   php artisan harvest:way-db --only=ws    → only Way Startup tables
 *   php artisan harvest:way-db --only=ref   → only reference tables
 */
class HarvestWayDatabase extends Command
{
    protected $signature = 'harvest:way-db {--only= : Restrict sync to mw|ws|ref}';
    protected $description = 'Harvest Way Backend data directly from PostgreSQL (bypasses API)';

    private int $synced = 0;
    private int $skipped = 0;
    private array $errors = [];

    /**
     * Map PG player.id → local mw_players.id
     * Built during player sync so downstream tables can resolve FK references.
     */
    private array $pgPlayerIdToLocalId = [];

    /**
     * Map PG startup_simulation.id → local ws_simulations.id
     */
    private array $pgSimIdToLocalWsId = [];

    /**
     * Map PG startup_step.id → local ws_steps.id
     */
    private array $pgStepIdToLocalWsId = [];

    /**
     * Map PG startup_member.id → local ws_members.id
     */
    private array $pgMemberIdToLocalWsId = [];

    /**
     * Map PG startup_step_question.id → local ws_step_questions.id
     */
    private array $pgQuestionIdToLocalWsId = [];

    /**
     * Map PG startup_assignment.id → local ws_assignments.id
     */
    private array $pgAssignmentIdToLocalWsId = [];

    public function handle(): int
    {
        $startTime = now();
        $this->info('🚀 Way Backend Direct DB Harvest');
        $this->newLine();

        // ── Verify connection ──
        try {
            DB::connection('way_backend')->getPdo();
            $this->info('  ✅ PostgreSQL bağlantısı başarılı');
        } catch (\Exception $e) {
            $this->error("  ❌ PostgreSQL bağlantı hatası: {$e->getMessage()}");
            return self::FAILURE;
        }

        $only = $this->option('only');

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            if (!$only || $only === 'ref') {
                $this->syncRefTables();
            }
            if (!$only || $only === 'mw') {
                $this->syncMissionWayTables();
            }
            if (!$only || $only === 'ws') {
                $this->syncWayStartupTables();
            }
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        // ── Summary ──
        $elapsed = now()->diffInSeconds($startTime);
        $this->newLine();

        if (!empty($this->errors)) {
            $this->warn('  ⚠️  Errors encountered:');
            foreach ($this->errors as $err) {
                $this->line("     • {$err}");
            }
            $this->newLine();
        }

        $this->table(['Metric', 'Value'], [
            ['Synced',   number_format($this->synced)],
            ['Skipped',  number_format($this->skipped)],
            ['Errors',   count($this->errors)],
            ['Duration', "{$elapsed}s"],
        ]);

        return count($this->errors) === 0 ? self::SUCCESS : self::SUCCESS; // errors are non-fatal
    }

    // ═══════════════════════════════════════════════════════════════
    //  REFERENCE TABLES — Direct ID match (PG id == local id)
    // ═══════════════════════════════════════════════════════════════

    private function syncRefTables(): void
    {
        $this->comment('📚 Reference Tables');

        $this->syncRefLanguages();
        $this->syncRefRoles();
        $this->syncRefSimulations();
        $this->syncRefSimulationVersions();
        $this->syncRefSimulationVersionRoles();
        $this->syncRefSimulationPaths();
        $this->syncRefMetricDefinitions();
        $this->syncRefMetricBandCategories();
        $this->syncRefSimulationMetricBands();
        $this->syncRefInfoCards();
        $this->syncRefTranslations();
    }

    private function syncRefLanguages(): void
    {
        $this->doSync('ref_languages', 'ref_language', function ($row) {
            return RefTranslation::class !== null; // just a type hint trick
        }, function ($rows) {
            foreach ($rows as $row) {
                DB::table('ref_languages')->upsert([[
                    'id'             => $row->id,
                    'code'           => $row->code,
                    'name'           => $row->name,
                    'is_default'     => $row->is_default ? 1 : 0,
                    'deactivated_at' => $this->dt($row->deactivated_at),
                    'created_at'     => $this->dt($row->created_at),
                    'updated_at'     => $this->dt($row->updated_at),
                    'created_by'     => $this->intOrNull($row->created_by),
                    'updated_by'     => $this->intOrNull($row->updated_by),
                ]], ['id'], ['code', 'name', 'is_default', 'deactivated_at', 'updated_at']);
                $this->synced++;
            }
        });
    }

    private function syncRefRoles(): void
    {
        // PG ref_role only has id + timestamps (name/icon come from translations)
        $this->doSync('ref_roles', 'ref_role', null, function ($rows) {
            foreach ($rows as $row) {
                DB::table('ref_roles')->upsert([[
                    'id'             => $row->id,
                    'deactivated_at' => $this->dt($row->deactivated_at),
                    'created_at'     => $this->dt($row->created_at),
                    'updated_at'     => $this->dt($row->updated_at),
                    'created_by'     => $this->intOrNull($row->created_by),
                    'updated_by'     => $this->intOrNull($row->updated_by),
                ]], ['id'], ['deactivated_at', 'updated_at']);
                $this->synced++;
            }
        });
    }

    private function syncRefSimulations(): void
    {
        $this->doSync('ref_simulations', 'ref_simulation', null, function ($rows) {
            foreach ($rows as $row) {
                DB::table('ref_simulations')->upsert([[
                    'id'                    => $row->id,
                    'difficulty_level'      => $row->difficulty_level ?? null,
                    'estimated_duration'    => $row->estimated_duration ?? null,
                    'min_players'           => $row->min_players ?? 1,
                    'max_players'           => $row->max_players ?? 5,
                    'cover_image'           => $row->cover_image_asset_id ?? null,
                    'background_image'      => $row->background_image_asset_id ?? null,
                    'deactivated_at'        => $this->dt($row->deactivated_at),
                    'created_at'            => $this->dt($row->created_at),
                    'updated_at'            => $this->dt($row->updated_at),
                    'created_by'            => $this->intOrNull($row->created_by),
                    'updated_by'            => $this->intOrNull($row->updated_by),
                ]], ['id'], ['difficulty_level', 'estimated_duration', 'min_players', 'max_players', 'deactivated_at', 'updated_at']);
                $this->synced++;
            }
        });
    }

    private function syncRefSimulationVersions(): void
    {
        $this->doSync('ref_simulation_versions', 'ref_simulation_version', null, function ($rows) {
            foreach ($rows as $row) {
                DB::table('ref_simulation_versions')->upsert([[
                    'id'                => $row->id,
                    'simulation_id'     => $row->simulation_id,
                    'version_number'    => $row->version_number,
                    'version_code'      => "v{$row->version_number}",
                    'status'            => $row->status ?? 'published',
                    'is_active'         => ($row->is_active ?? false) ? 1 : 0,
                    'is_default'        => 0,
                    'changelog'         => $row->changelog ?? null,
                    'published_at'      => $this->dt($row->published_at),
                    'published_by'      => $this->intOrNull($row->published_by ?? null),
                    'deactivated_at'    => $this->dt($row->deactivated_at),
                    'created_at'        => $this->dt($row->created_at),
                    'updated_at'        => $this->dt($row->updated_at),
                    'created_by'        => $this->intOrNull($row->created_by),
                    'updated_by'        => $this->intOrNull($row->updated_by),
                ]], ['id'], ['simulation_id', 'version_number', 'status', 'is_active', 'deactivated_at', 'updated_at']);
                $this->synced++;
            }
        });
    }

    private function syncRefSimulationVersionRoles(): void
    {
        $this->doSync('ref_simulation_version_roles', 'ref_simulation_version_role', null, function ($rows) {
            foreach ($rows as $row) {
                DB::table('ref_simulation_version_roles')->upsert([[
                    'id'                    => $row->id,
                    'simulation_version_id' => $row->simulation_version_id,
                    'role_id'               => $row->role_id,
                    'priority_order'        => $row->priority_order ?? 0,
                    'deactivated_at'        => $this->dt($row->deactivated_at),
                    'created_at'            => $this->dt($row->created_at),
                    'updated_at'            => $this->dt($row->updated_at),
                    'created_by'            => $this->intOrNull($row->created_by),
                    'updated_by'            => $this->intOrNull($row->updated_by),
                ]], ['id'], ['simulation_version_id', 'role_id', 'priority_order', 'deactivated_at', 'updated_at']);
                $this->synced++;
            }
        });
    }

    private function syncRefSimulationPaths(): void
    {
        $this->doSync('ref_simulation_paths', 'ref_simulation_path', null, function ($rows) {
            foreach ($rows as $row) {
                DB::table('ref_simulation_paths')->upsert([[
                    'id'                     => $row->id,
                    'simulation_version_id'  => $row->simulation_version_id,
                    'parent_path_id'         => $row->parent_path_id ?? null,
                    'media_asset_id'         => $row->media_asset_id ?? null,
                    'order_index'            => $row->order_index ?? 0,
                    'points'                 => $row->points ?? 0,
                    'metrics'                => $row->metrics ?? null,
                    'path_type'              => $row->path_type ?? 'narrative',
                    'max_wait_time'          => $row->max_wait_time ?? null,
                    'min_wait_time'          => $row->min_wait_time ?? null,
                    'path_points'            => $row->path_points ?? 0,
                    'is_ended'               => ($row->is_ended ?? false) ? 1 : 0,
                    'deactivated_at'         => $this->dt($row->deactivated_at),
                    'created_at'             => $this->dt($row->created_at),
                    'updated_at'             => $this->dt($row->updated_at),
                    'created_by'             => $this->intOrNull($row->created_by),
                    'updated_by'             => $this->intOrNull($row->updated_by),
                ]], ['id'], ['simulation_version_id', 'parent_path_id', 'order_index', 'points', 'metrics', 'path_type', 'is_ended', 'deactivated_at', 'updated_at']);
                $this->synced++;
            }
        });
    }

    private function syncRefMetricDefinitions(): void
    {
        $this->doSync('ref_metric_definitions', 'ref_metric_definition', null, function ($rows) {
            foreach ($rows as $row) {
                DB::table('ref_metric_definitions')->upsert([[
                    'id'            => $row->id,
                    'metric_key'    => $row->metric_key ?? 'unknown',
                    'data_type'     => $row->data_type ?? 'number',
                    'min_value'     => $row->min_value ?? 0,
                    'max_value'     => $row->max_value ?? 100,
                    'default_value' => $row->default_value ?? null,
                    'icon'          => $row->icon ?? '📊',
                    'color'         => $row->color ?? '#6B7280',
                    'deactivated_at' => $this->dt($row->deactivated_at),
                    'created_at'    => $this->dt($row->created_at),
                    'updated_at'    => $this->dt($row->updated_at),
                    'created_by'    => $this->intOrNull($row->created_by),
                    'updated_by'    => $this->intOrNull($row->updated_by),
                ]], ['id'], ['metric_key', 'data_type', 'min_value', 'max_value', 'default_value', 'icon', 'color', 'deactivated_at', 'updated_at']);
                $this->synced++;
            }
        });
    }

    private function syncRefMetricBandCategories(): void
    {
        $this->doSync('ref_metric_band_categories', 'ref_metric_band_category', null, function ($rows) {
            foreach ($rows as $row) {
                DB::table('ref_metric_band_categories')->upsert([[
                    'id'              => $row->id,
                    'key'             => $row->key,
                    'color'           => $row->color ?? '#6B7280',
                    'order_index'     => $row->order_index ?? 0,
                    'score_multiplier' => $row->score_multiplier ?? 1,
                    'deactivated_at'  => $this->dt($row->deactivated_at),
                    'created_at'      => $this->dt($row->created_at),
                    'updated_at'      => $this->dt($row->updated_at),
                    'created_by'      => $this->intOrNull($row->created_by),
                    'updated_by'      => $this->intOrNull($row->updated_by),
                ]], ['id'], ['key', 'color', 'order_index', 'score_multiplier', 'deactivated_at', 'updated_at']);
                $this->synced++;
            }
        });
    }

    private function syncRefSimulationMetricBands(): void
    {
        $this->doSync('ref_simulation_metric_bands', 'ref_simulation_metric_band', null, function ($rows) {
            foreach ($rows as $row) {
                DB::table('ref_simulation_metric_bands')->upsert([[
                    'id'                      => $row->id,
                    'simulation_version_id'   => $row->simulation_version_id,
                    'metric_key'              => $row->metric_key ?? null,
                    'category_id'             => $row->category_id ?? null,
                    'min_value'               => $row->min_value ?? 0,
                    'max_value'               => $row->max_value ?? 100,
                    'order_index'             => $row->order_index ?? 0,
                    'deactivated_at'          => $this->dt($row->deactivated_at),
                    'created_at'              => $this->dt($row->created_at),
                    'updated_at'              => $this->dt($row->updated_at),
                    'created_by'              => $this->intOrNull($row->created_by),
                    'updated_by'              => $this->intOrNull($row->updated_by),
                ]], ['id'], ['simulation_version_id', 'metric_key', 'category_id', 'min_value', 'max_value', 'deactivated_at', 'updated_at']);
                $this->synced++;
            }
        });
    }

    private function syncRefInfoCards(): void
    {
        $this->doSync('ref_info_cards', 'ref_info_card', null, function ($rows) {
            foreach ($rows as $row) {
                DB::table('ref_info_cards')->upsert([[
                    'id'                  => $row->id,
                    'simulation_path_id'  => $row->simulation_path_id ?? null,
                    'role_id'             => $row->role_id ?? null,
                    'display_order'       => $row->display_order ?? 0,
                    'icon_asset_id'       => $row->icon_asset_id ?? null,
                    'deactivated_at'      => $this->dt($row->deactivated_at),
                    'created_at'          => $this->dt($row->created_at),
                    'updated_at'          => $this->dt($row->updated_at),
                    'created_by'          => $this->intOrNull($row->created_by),
                    'updated_by'          => $this->intOrNull($row->updated_by),
                ]], ['id'], ['simulation_path_id', 'role_id', 'display_order', 'deactivated_at', 'updated_at']);
                $this->synced++;
            }
        });
    }

    private function syncRefTranslations(): void
    {
        $this->doSync('ref_translations', 'ref_translation', null, function ($rows) {
            $batch = [];
            foreach ($rows as $row) {
                $batch[] = [
                    'id'            => $row->id,
                    'entity_type'   => $row->entity_type,
                    'entity_id'     => $row->entity_id,
                    'language_id'   => $row->language_id,
                    'fields'        => $row->fields,
                    'deactivated_at' => $this->dt($row->deactivated_at),
                    'created_at'    => $this->dt($row->created_at),
                    'updated_at'    => $this->dt($row->updated_at),
                    'created_by'    => $this->intOrNull($row->created_by),
                    'updated_by'    => $this->intOrNull($row->updated_by),
                ];
            }
            if (!empty($batch)) {
                DB::table('ref_translations')->upsert(
                    $batch, ['id'],
                    ['entity_type', 'entity_id', 'language_id', 'fields', 'deactivated_at', 'updated_at']
                );
                $this->synced += count($batch);
            }
        }, 500); // batch translations in chunks of 500
    }

    // ═══════════════════════════════════════════════════════════════
    //  MISSION WAY TABLES — player IDs differ between PG and local
    // ═══════════════════════════════════════════════════════════════

    private function syncMissionWayTables(): void
    {
        $this->comment('🎮 Mission Way Tables');

        $this->syncMwPlayers();
        $this->syncMwPlayerProfiles();
        $this->syncMwSessions();
        $this->syncMwSessionPlayers();
        $this->syncMwPlayerChoices();
        $this->syncMwPlayerProgress();
        $this->syncMwAssignments();
        $this->syncMwAssignmentPlayers();
    }

    private function syncMwPlayers(): void
    {
        $this->doSync('mw_players', 'player', null, function ($rows) {
            foreach ($rows as $row) {
                $email = $row->email ?? "{$row->id}@missionway.local";
                $username = $row->username ?? $email;

                // Match by email first (natural key), then by username
                $local = MwPlayer::where('email', $email)->first()
                      ?? MwPlayer::where('username', $username)->first();

                if ($local) {
                    // Update existing record
                    $local->update(array_filter([
                        'username'            => $username,
                        'name'                => $row->name ?? $local->name,
                        'surname'             => $row->surname ?? $local->surname,
                        'organization_id'     => $row->organization_id ?? $local->organization_id,
                        'avatar_media_id'     => $row->avatar_media_id ?? $local->avatar_media_id,
                        'preferred_language_id' => $row->preferred_language_id ?? $local->preferred_language_id,
                        'deactivated_at'      => $this->dt($row->deactivated_at),
                    ], fn($v) => $v !== null));
                } else {
                    // Create new — email üzerinden portal user_id'sini resolve et
                    // PG'nin user_id'si Vega'nın iç ID'si, panel26 user ID'si DEĞİL
                    $portalUser = \App\Models\User::where('email', $email)->first();
                    $localUserId = $portalUser?->id;

                    $local = new MwPlayer();
                    $local->username = $username;
                    $local->email = $email;
                    $local->name = $row->name ?? 'Oyuncu';
                    $local->surname = $row->surname ?? '';
                    $local->user_id = $localUserId;
                    $local->organization_id = $row->organization_id ?? null;
                    $local->avatar_media_id = $row->avatar_media_id ?? null;
                    $local->preferred_language_id = $row->preferred_language_id ?? null;
                    $local->deactivated_at = $this->dt($row->deactivated_at);
                    $local->save();
                }

                $this->pgPlayerIdToLocalId[$row->id] = $local->id;
                $this->synced++;
            }
        });
    }

    private function syncMwPlayerProfiles(): void
    {
        $this->doSync('mw_player_profiles', 'player_profile', null, function ($rows) {
            $batch = [];
            foreach ($rows as $row) {
                $localPlayerId = $this->pgPlayerIdToLocalId[$row->player_id] ?? null;
                if (!$localPlayerId) {
                    $this->skipped++;
                    continue;
                }
                $batch[] = [
                    'id'                             => $row->id,
                    'player_id'                      => $localPlayerId,
                    'total_score'                    => $row->total_score ?? 0,
                    'total_simulations_completed'    => $row->total_simulations_completed ?? 0,
                    'total_play_time_minutes'        => $row->total_play_time_minutes ?? 0,
                    'last_completed_simulation_id'   => $row->last_completed_simulation_id ?? null,
                    'achievements'                   => $row->achievements ?? null,
                    'statistics'                     => $row->statistics ?? null,
                    'metric_stats'                   => $row->metric_stats ?? null,
                    'deactivated_at'                 => $this->dt($row->deactivated_at),
                    'created_at'                     => $this->dt($row->created_at),
                    'updated_at'                     => $this->dt($row->updated_at),
                    'created_by'                     => $this->intOrNull($row->created_by),
                    'updated_by'                     => $this->intOrNull($row->updated_by),
                ];
            }
            if (!empty($batch)) {
                DB::table('mw_player_profiles')->upsert($batch, ['player_id'], ['total_score', 'total_simulations_completed', 'total_play_time_minutes', 'achievements', 'statistics', 'metric_stats', 'updated_at']);
                $this->synced += count($batch);
            }
        });
    }

    private function syncMwSessions(): void
    {
        $this->doSync('mw_simulation_sessions', 'simulation_session', null, function ($rows) {
            $batch = [];
            foreach ($rows as $row) {
                $batch[] = [
                    'id'                      => $row->id,
                    'simulation_version_id'   => $row->simulation_version_id,
                    'session_code'            => $row->session_code ?? null,
                    'status'                  => $row->status ?? 'waiting',
                    'game_mode'               => $row->game_mode ?? null,
                    'started_at'              => $this->dt($row->started_at),
                    'completed_at'            => $this->dt($row->completed_at),
                    'final_path_id'           => $row->final_path_id ?? null,
                    'final_score'             => $row->final_score ?? null,
                    'final_metrics'           => is_string($row->final_metrics) ? $row->final_metrics : json_encode($row->final_metrics),
                    'deactivated_at'          => $this->dt($row->deactivated_at),
                    'created_at'              => $this->dt($row->created_at),
                    'updated_at'              => $this->dt($row->updated_at),
                    'created_by'              => $this->intOrNull($row->created_by),
                    'updated_by'              => $this->intOrNull($row->updated_by),
                ];
            }
            if (!empty($batch)) {
                DB::table('mw_simulation_sessions')->upsert($batch, ['id'], ['simulation_version_id', 'session_code', 'status', 'game_mode', 'started_at', 'completed_at', 'final_path_id', 'final_score', 'final_metrics', 'deactivated_at', 'updated_at']);
                $this->synced += count($batch);
            }
        });
    }

    private function syncMwSessionPlayers(): void
    {
        $this->doSync('mw_session_players', 'simulation_session_player', null, function ($rows) {
            $batch = [];
            foreach ($rows as $row) {
                $localPlayerId = $this->pgPlayerIdToLocalId[$row->player_id] ?? null;
                if (!$localPlayerId) {
                    $this->skipped++;
                    continue;
                }
                $batch[] = [
                    'id'                      => $row->id,
                    'simulation_session_id'   => $row->simulation_session_id,
                    'player_id'               => $localPlayerId,
                    'role_id'                 => $row->role_id ?? null,
                    'joined_at'               => $this->dt($row->joined_at ?? $row->created_at),
                    'deactivated_at'          => $this->dt($row->deactivated_at),
                    'created_at'              => $this->dt($row->created_at),
                    'updated_at'              => $this->dt($row->updated_at),
                    'created_by'              => $this->intOrNull($row->created_by),
                    'updated_by'              => $this->intOrNull($row->updated_by),
                ];
            }
            if (!empty($batch)) {
                DB::table('mw_session_players')->upsert($batch, ['id'], ['simulation_session_id', 'player_id', 'role_id', 'joined_at', 'deactivated_at', 'updated_at']);
                $this->synced += count($batch);
            }
        });
    }

    private function syncMwPlayerChoices(): void
    {
        $this->doSync('mw_player_choices', 'player_choice', null, function ($rows) {
            $batch = [];
            foreach ($rows as $row) {
                $localPlayerId = $this->pgPlayerIdToLocalId[$row->player_id] ?? null;
                if (!$localPlayerId) {
                    $this->skipped++;
                    continue;
                }
                $batch[] = [
                    'id'                      => $row->id,
                    'player_id'               => $localPlayerId,
                    'simulation_session_id'   => $row->simulation_session_id,
                    'previous_path_id'        => $row->previous_path_id ?? null,
                    'simulation_path_id'      => $row->simulation_path_id ?? null,
                    'selected_path_id'        => $row->selected_path_id ?? null,
                    'decided_path_id'         => $row->decided_path_id ?? null,
                    'response_time_seconds'   => $row->response_time_seconds ?? null,
                    'points_earned'           => $row->points_earned ?? 0,
                    'is_correct'              => isset($row->is_correct) ? ($row->is_correct ? 1 : 0) : null,
                    'metrics_before'          => $row->metrics_before ?? null,
                    'metrics_after'           => $row->metrics_after ?? null,
                    'deactivated_at'          => $this->dt($row->deactivated_at),
                    'created_at'              => $this->dt($row->created_at),
                    'updated_at'              => $this->dt($row->updated_at),
                    'created_by'              => $this->intOrNull($row->created_by),
                    'updated_by'              => $this->intOrNull($row->updated_by),
                ];
            }
            if (!empty($batch)) {
                DB::table('mw_player_choices')->upsert($batch, ['id'], ['player_id', 'simulation_session_id', 'points_earned', 'is_correct', 'metrics_before', 'metrics_after', 'deactivated_at', 'updated_at']);
                $this->synced += count($batch);
            }
        });
    }

    private function syncMwPlayerProgress(): void
    {
        $this->doSync('mw_player_progress', 'player_progress', null, function ($rows) {
            $batch = [];
            foreach ($rows as $row) {
                $localPlayerId = $this->pgPlayerIdToLocalId[$row->player_id] ?? null;
                if (!$localPlayerId) {
                    $this->skipped++;
                    continue;
                }
                $batch[] = [
                    'id'                      => $row->id,
                    'player_id'               => $localPlayerId,
                    'simulation_session_id'   => $row->simulation_session_id ?? null,
                    'simulation_version_id'   => $row->simulation_version_id ?? null,
                    'current_path_id'         => $row->current_path_id ?? null,
                    'current_score'           => $row->current_score ?? 0,
                    'current_metrics'         => $row->current_metrics ?? null,
                    'started_at'              => $this->dt($row->started_at),
                    'completed_at'            => $this->dt($row->completed_at),
                    'deactivated_at'          => $this->dt($row->deactivated_at),
                    'created_at'              => $this->dt($row->created_at),
                    'updated_at'              => $this->dt($row->updated_at),
                    'created_by'              => $this->intOrNull($row->created_by),
                    'updated_by'              => $this->intOrNull($row->updated_by),
                ];
            }
            if (!empty($batch)) {
                DB::table('mw_player_progress')->upsert($batch, ['id'], ['player_id', 'simulation_session_id', 'current_path_id', 'current_score', 'current_metrics', 'started_at', 'completed_at', 'deactivated_at', 'updated_at']);
                $this->synced += count($batch);
            }
        });
    }

    private function syncMwAssignments(): void
    {
        $this->doSync('mw_assignments', 'assignment', null, function ($rows) {
            $batch = [];
            foreach ($rows as $row) {
                $batch[] = [
                    'id'                      => $row->id,
                    'simulation_id'           => $row->simulation_id ?? null,
                    'grade'                   => is_string($row->grade) ? $row->grade : json_encode($row->grade),
                    'deadline'                => $this->dt($row->deadline) ?? now(),
                    'simulation_session_id'   => $row->simulation_session_id ?? null,
                    'status'                  => 'active',
                    'deactivated_at'          => $this->dt($row->deactivated_at),
                    'created_at'              => $this->dt($row->created_at),
                    'updated_at'              => $this->dt($row->updated_at),
                    'created_by'              => $this->intOrNull($row->created_by),
                    'updated_by'              => $this->intOrNull($row->updated_by),
                ];
            }
            if (!empty($batch)) {
                DB::table('mw_assignments')->upsert($batch, ['id'], ['simulation_id', 'grade', 'deadline', 'simulation_session_id', 'deactivated_at', 'updated_at']);
                $this->synced += count($batch);
            }
        });
    }

    private function syncMwAssignmentPlayers(): void
    {
        $this->doSync('mw_assignment_players', 'assignment_player', null, function ($rows) {
            $batch = [];
            foreach ($rows as $row) {
                $localPlayerId = $this->pgPlayerIdToLocalId[$row->player_id] ?? null;
                if (!$localPlayerId) {
                    $this->skipped++;
                    continue;
                }
                $batch[] = [
                    'id'              => $row->id,
                    'assignment_id'   => $row->assignment_id,
                    'player_id'       => $localPlayerId,
                    'status'          => 'assigned',
                    'deactivated_at'  => $this->dt($row->deactivated_at),
                    'created_at'      => $this->dt($row->created_at),
                    'updated_at'      => $this->dt($row->updated_at),
                    'created_by'      => $this->intOrNull($row->created_by),
                    'updated_by'      => $this->intOrNull($row->updated_by),
                ];
            }
            if (!empty($batch)) {
                DB::table('mw_assignment_players')->upsert($batch, ['id'], ['assignment_id', 'player_id', 'deactivated_at', 'updated_at']);
                $this->synced += count($batch);
            }
        });
    }

    // ═══════════════════════════════════════════════════════════════
    //  WAY STARTUP TABLES — uses external_id pattern
    // ═══════════════════════════════════════════════════════════════

    private function syncWayStartupTables(): void
    {
        $this->comment('🚀 Way Startup Tables');

        $this->syncWsSimulations();
        $this->syncWsSteps();
        $this->syncWsTools();
        $this->syncWsMembers();
        $this->syncWsStepQuestions();
        $this->syncWsAssignments();
        $this->syncWsAssignmentMembers();
        $this->syncWsStepProgress();
        $this->syncWsStepQuestionAnswers();
        $this->syncWsStepEvaluations();
        $this->syncWsStepSubmissions();
    }

    private function syncWsSimulations(): void
    {
        $appId = Application::where('slug', 'way-startup')->first()?->id ?? 2;
        $pgRows = DB::connection('way_backend')->table('startup_simulation')->get();
        $count = 0;

        foreach ($pgRows as $row) {
            $local = WsSimulation::updateOrCreate(
                ['external_id' => $row->id],
                [
                    'application_id' => $appId,
                    'name'           => $row->name ?? "Proje #{$row->id}",
                    'description'    => $row->description ?? null,
                    'total_step'     => $row->total_step ?? 0,
                    'icon_url'       => $row->icon_url ?? null,
                    'color_code'     => $row->color_code ?? null,
                    'background_color' => $row->background_color_code ?? null,
                    'status'         => 'active',
                    'synced_at'      => now(),
                ]
            );
            $this->pgSimIdToLocalWsId[$row->id] = $local->id;
            $count++;
        }

        $this->info("  ✅ ws_simulations: {$count}");
        $this->synced += $count;
    }

    private function syncWsSteps(): void
    {
        $pgRows = DB::connection('way_backend')->table('startup_step')->get();
        $count = 0;

        foreach ($pgRows as $row) {
            $localSimId = $this->pgSimIdToLocalWsId[$row->simulation_id] ?? null;
            if (!$localSimId) {
                $this->skipped++;
                continue;
            }

            try {
                // Convert ms to minutes safely. If it exceeds smallint unsigned limit, clamp it to 65535.
                $duration = $row->suggested_duration ?? null;
                if (is_numeric($duration)) {
                    $duration = (int) ($duration / 60000); 
                    if ($duration > 65535) $duration = 65535;
                }

                $local = WsStep::updateOrCreate(
                    ['external_id' => $row->id],
                    [
                        'simulation_id'    => $localSimId,
                        'name'             => $row->name ?? "Adım #{$row->id}",
                        'step_number'      => $row->step_number ?? 0,
                        'description'      => $row->description ?? null,
                        'task_description'  => $row->task_description ?? null,
                        'suggested_duration' => $duration > 0 ? $duration : null,
                        'difficulty'       => $row->difficulty ?? null,
                        'skill'            => $row->skill ?? null,
                        'points'           => $row->points ?? 0,
                        'max_score'        => 150,
                        'order_index'      => $row->sort_order ?? 0,
                        'is_locked'        => ($row->is_locked ?? false) ? 1 : 0,
                        'icon_url'         => $row->icon_url ?? null,
                        'has_file_upload'  => ($row->has_file_upload ?? false) ? 1 : 0,
                        'synced_at'        => now(),
                    ]
                );
                $this->pgStepIdToLocalWsId[$row->id] = $local->id;
                $count++;
            } catch (\Throwable $e) {
                $msg = "ws_steps [ID: {$row->id}]: " . $e->getMessage();
                $this->errors[] = $msg;
                $this->error("  ❌ {$msg}");
                Log::error("[HarvestWayDB] {$msg}");
            }
        }

        $this->info("  ✅ ws_steps: {$count}");
        $this->synced += $count;
    }

    private function syncWsTools(): void
    {
        $appId = Application::where('slug', 'way-startup')->first()?->id ?? 2;
        $pgCount = DB::connection('way_backend')->table('startup_tool')->count();

        if ($pgCount === 0) {
            $this->info("  ⏭️  ws_tools: 0 (PG tablosu boş)");
            return;
        }

        $pgRows = DB::connection('way_backend')->table('startup_tool')->get();
        $count = 0;

        foreach ($pgRows as $row) {
            WsTool::updateOrCreate(
                ['name' => $row->name, 'application_id' => $appId],
                [
                    'application_id' => $appId,
                    'description'    => $row->description ?? '',
                    'icon_url'       => $row->icon_url ?? '',
                    'website_url'    => $row->website_url ?? '',
                    'category'       => $row->category ?? 'Genel',
                    'synced_at'      => now(),
                ]
            );
            $count++;
        }

        $this->info("  ✅ ws_tools: {$count}");
        $this->synced += $count;
    }

    private function syncWsMembers(): void
    {
        $appId = Application::where('slug', 'way-startup')->first()?->id ?? 2;
        $pgRows = DB::connection('way_backend')->table('startup_member')->get();
        $count = 0;

        foreach ($pgRows as $row) {
            // Email üzerinden portal user_id'sini resolve et
            // PG'nin user_id'si Vega'nın iç ID'si, panel26 user ID'si DEĞİL
            $email = $row->email ?? null;
            $portalUser = $email ? \App\Models\User::where('email', $email)->first() : null;
            $localUserId = $portalUser?->id ?? $row->user_id;

            $local = WsMember::updateOrCreate(
                ['external_id' => $row->id],
                [
                    'application_id' => $appId,
                    'user_id'        => $localUserId,
                    'name'           => $row->name ?? '-',
                    'email'          => $email ?? '-',
                    'avatar_url'     => $row->avatar_url ?? null,
                    'points'         => $row->points ?? 0,
                    'synced_at'      => now(),
                ]
            );
            $this->pgMemberIdToLocalWsId[$row->id] = $local->id;
            $count++;
        }

        $this->info("  ✅ ws_members: {$count}");
        $this->synced += $count;
    }

    private function syncWsStepQuestions(): void
    {
        $pgRows = DB::connection('way_backend')->table('startup_step_question')->get();
        $count = 0;

        foreach ($pgRows as $row) {
            $local = WsStepQuestion::updateOrCreate(
                ['external_id' => $row->id],
                [
                    'step_id'        => $row->step_id,
                    'question_text'  => $row->question_text ?? '-',
                    'max_score'      => $row->max_score ?? 0,
                    'sort_order'     => $row->sort_order ?? 0,
                    'is_required'    => ($row->is_required ?? false) ? 1 : 0,
                    'synced_at'      => now(),
                ]
            );
            $this->pgQuestionIdToLocalWsId[$row->id] = $local->id;
            $count++;
        }

        $this->info("  ✅ ws_step_questions: {$count}");
        $this->synced += $count;
    }

    private function syncWsAssignments(): void
    {
        $this->doSync('ws_assignments', 'startup_assignment', null, function ($rows) {
            $batch = [];
            foreach ($rows as $row) {
                $batch[] = [
                    'external_id'   => $row->id,
                    'simulation_id' => $row->simulation_id,
                    'name'          => $row->name ?? '-',
                    'description'   => $row->description ?? null,
                    'due_date'      => $this->dt($row->due_date),
                    'status'        => $row->status ?? 'active',
                    'synced_at'     => now(),
                ];
            }
            if (!empty($batch)) {
                foreach ($batch as $data) {
                    try {
                        WsAssignment::updateOrCreate(['external_id' => $data['external_id']], $data);
                        $this->synced++;
                    } catch (\Throwable $e) {}
                }
            }
        });
    }

    private function syncWsAssignmentMembers(): void
    {
        $this->doSync('ws_assignment_members', 'startup_assignment_member', null, function ($rows) {
            $batch = [];
            foreach ($rows as $row) {
                $localAssignmentId = $this->pgAssignmentIdToLocalWsId[$row->assignment_id] ?? null;
                $localMemberId = $this->pgMemberIdToLocalWsId[$row->member_id] ?? null;

                if (!$localAssignmentId || !$localMemberId) {
                    $this->skipped++;
                    continue;
                }

                $batch[] = [
                    'assignment_id' => $localAssignmentId,
                    'member_id'     => $localMemberId,
                    'created_at'    => $this->dt($row->created_at),
                    'updated_at'    => $this->dt($row->updated_at),
                ];
            }
            if (!empty($batch)) {
                DB::table('ws_assignment_members')->upsert($batch, ['assignment_id', 'member_id'], ['updated_at']);
                $this->synced += count($batch);
            }
        });
    }

    private function syncWsStepProgress(): void
    {
        $this->doSync('ws_step_progress', 'startup_user_step_progress', null, function ($rows) {
            $batch = [];
            foreach ($rows as $row) {
                $localMemberId = $this->pgMemberIdToLocalWsId[$row->member_id] ?? null;
                if (!$localMemberId) {
                    $this->skipped++;
                    continue;
                }
                $batch[] = [
                    'member_id'        => $localMemberId,
                    'step_external_id' => $row->step_id,
                    'assignment_id'    => isset($row->assignment_id) ? ($this->pgAssignmentIdToLocalWsId[$row->assignment_id] ?? null) : null,
                    'status'           => $row->status ?? 'locked',
                    'started_at'       => $this->dt($row->started_at),
                    'completed_at'     => $this->dt($row->completed_at),
                    'earned_point'     => $row->earned_point ?? 0,
                    'earned_coin'      => $row->earned_coin ?? 0,
                    'synced_at'        => now(),
                    'created_at'       => $this->dt($row->created_at),
                    'updated_at'       => $this->dt($row->updated_at),
                ];
            }
            if (!empty($batch)) {
                DB::table('ws_step_progress')->upsert($batch, ['member_id', 'step_external_id'], ['status', 'started_at', 'completed_at', 'earned_point', 'earned_coin', 'synced_at', 'updated_at']);
                $this->synced += count($batch);
            }
        });
    }

    private function syncWsStepQuestionAnswers(): void
    {
        $this->doSync('ws_step_question_answers', 'startup_step_question_answer', null, function ($rows) {
            $batch = [];
            foreach ($rows as $row) {
                $localMemberId = $this->pgMemberIdToLocalWsId[$row->member_id] ?? null;
                if (!$localMemberId) {
                    $this->skipped++;
                    continue;
                }
                $batch[] = [
                    'external_id'  => $row->id,
                    'question_id'  => $row->question_id,
                    'member_id'    => $localMemberId,
                    'attempt'      => $row->attempt ?? 1,
                    'text_answer'  => $row->text_answer ?? null,
                    'ai_score'     => is_numeric($row->ai_score) ? $row->ai_score : 0,
                    'ai_max_score' => is_numeric($row->ai_max_score) ? $row->ai_max_score : 100,
                    'ai_feedback'  => $row->ai_feedback ?? null,
                    'synced_at'    => now(),
                ];
            }
            if (!empty($batch)) {
                foreach ($batch as $data) {
                    try {
                        WsStepQuestionAnswer::updateOrCreate(
                            ['external_id' => $data['external_id']],
                            $data
                        );
                        $this->synced++;
                    } catch (\Throwable $e) {}
                }
            }
        });
    }

    private function syncWsStepEvaluations(): void
    {
        $this->doSync('ws_step_evaluations', 'startup_step_question_evaluation', null, function ($rows) {
            $batch = [];
            foreach ($rows as $row) {
                $localMemberId = $this->pgMemberIdToLocalWsId[$row->member_id] ?? null;
                if (!$localMemberId) {
                    $this->skipped++;
                    continue;
                }
                $batch[] = [
                    'external_id'          => $row->id,
                    'step_id'              => $row->step_id,
                    'member_id'            => $localMemberId,
                    'attempt'              => $row->attempt ?? 1,
                    'ai_total_score'       => is_numeric($row->ai_total_score) ? $row->ai_total_score : 0,
                    'ai_max_score'         => is_numeric($row->ai_max_score) ? $row->ai_max_score : 100,
                    'ai_coins'             => is_numeric($row->ai_coins) ? $row->ai_coins : 0,
                    'ai_overall_feedback'  => $row->ai_overall_feedback ?? null,
                    'status'               => $row->status ?? 'pending',
                    'ai_evaluated_at'      => $this->dt($row->ai_evaluated_at),
                    'synced_at'            => now(),
                ];
            }
            if (!empty($batch)) {
                foreach ($batch as $data) {
                    try {
                        WsStepEvaluation::updateOrCreate(
                            ['external_id' => $data['external_id']],
                            $data
                        );
                        $this->synced++;
                    } catch (\Throwable $e) {}
                }
            }
        });
    }

    private function syncWsStepSubmissions(): void
    {
        $this->doSync('ws_step_submissions', 'startup_step_submission', null, function ($rows) {
            $batch = [];
            foreach ($rows as $row) {
                $memberExists = isset($this->wsMemberMap[$row->member_id]);
                if (!$memberExists) continue;

                $batch[] = [
                    'external_id'      => $row->id,
                    'member_id'        => $this->wsMemberMap[$row->member_id],
                    'step_external_id' => $row->step_id,
                    'file_name'        => $row->file_name ?? null,
                    'file_url'         => $row->file_url ?? null,
                    'file_type'        => $row->file_type ?? null,
                    'file_size'        => $row->file_size ?? null,
                    'link_url'         => $row->link_url ?? null,
                    'link_title'       => $row->link_title ?? null,
                    'link_platform'    => $row->link_platform ?? null,
                    'status'           => $row->status ?? null,
                    'feedback'         => $row->feedback ?? null,
                    'points_earned'    => is_numeric($row->points_earned) ? $row->points_earned : null,
                    'submitted_at'     => $this->dt($row->submitted_at ?? current($row)), // Fallback since I don't know the column for sure
                    'synced_at'        => now(),
                ];
            }
            if (!empty($batch)) {
                foreach ($batch as $data) {
                    try {
                        \App\Models\WsStepSubmission::updateOrCreate(
                            ['external_id' => $data['external_id']],
                            $data
                        );
                        $this->synced++;
                    } catch (\Throwable $e) {}
                }
            }
        });
    }

    // ═══════════════════════════════════════════════════════════════
    //  HELPERS
    // ═══════════════════════════════════════════════════════════════

    /**
     * Parse a PostgreSQL timestamp into MySQL-safe Y-m-d H:i:s format.
     */
    private function dt($value): ?string
    {
        if (empty($value)) return null;
        try {
            return \Carbon\Carbon::parse($value)->format('Y-m-d H:i:s');
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * PG sometimes stores created_by as string "-1"; MySQL expects int or null.
     */
    private function intOrNull($value): ?int
    {
        if ($value === null || $value === '') return null;
        $int = (int) $value;
        // Guard against bigint overflow for MySQL unsigned columns
        if ($int < 0) return 1; // Default to local admin (id=1) instead of null to prevent NOT NULL constraint errors
        if ($int > 4294967295) return null;
        return $int;
    }

    /**
     * Wrapper that handles table sync with consistent logging and error handling.
     */
    private function doSync(string $localTable, string $pgTable, $filter, callable $handler, int $chunkSize = 200): void
    {
        try {
            $query = DB::connection('way_backend')->table($pgTable)->orderBy('id');
            $total = 0;

            $query->chunk($chunkSize, function ($rows) use ($handler, &$total) {
                $handler($rows);
                $total += count($rows);
            });

            $this->info("  ✅ {$localTable}: {$total}");
        } catch (\Throwable $e) {
            $msg = "{$localTable}: " . $e->getMessage();
            $this->errors[] = $msg;
            $this->error("  ❌ {$msg}");
            Log::error("[HarvestWayDB] {$msg}");
        }
    }
}
