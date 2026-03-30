<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add missing columns to ref_simulations, mw_players etc.
 * that HarvestAppData + Controller rely on but weren't in the 1:1 parity schema.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ref_simulations: name, description, difficulty (alias of difficulty_level)
        if (Schema::hasTable('ref_simulations')) {
            Schema::table('ref_simulations', function (Blueprint $table) {
                if (!Schema::hasColumn('ref_simulations', 'name')) {
                    $table->string('name', 255)->nullable()->after('id');
                }
                if (!Schema::hasColumn('ref_simulations', 'description')) {
                    $table->text('description')->nullable()->after('name');
                }
                if (!Schema::hasColumn('ref_simulations', 'difficulty')) {
                    $table->string('difficulty', 50)->nullable()->after('description');
                }
                if (!Schema::hasColumn('ref_simulations', 'background_image_path')) {
                    $table->string('background_image_path', 500)->nullable()->after('background_image');
                }
            });
        }

        // ref_simulation_versions: version_code, is_default
        if (Schema::hasTable('ref_simulation_versions')) {
            Schema::table('ref_simulation_versions', function (Blueprint $table) {
                if (!Schema::hasColumn('ref_simulation_versions', 'version_code')) {
                    $table->string('version_code', 50)->nullable()->after('version_number');
                }
                if (!Schema::hasColumn('ref_simulation_versions', 'is_default')) {
                    $table->boolean('is_default')->default(false)->after('is_active');
                }
            });
        }

        // ref_simulation_paths: wait_time_min, wait_time_max (aliases for min/max_wait_time)
        if (Schema::hasTable('ref_simulation_paths')) {
            Schema::table('ref_simulation_paths', function (Blueprint $table) {
                if (!Schema::hasColumn('ref_simulation_paths', 'wait_time_min')) {
                    $table->integer('wait_time_min')->nullable()->after('min_wait_time');
                }
                if (!Schema::hasColumn('ref_simulation_paths', 'wait_time_max')) {
                    $table->integer('wait_time_max')->nullable()->after('wait_time_min');
                }
            });
        }

        // mw_players: make username and email nullable for harvest flexibility
        if (Schema::hasTable('mw_players')) {
            Schema::table('mw_players', function (Blueprint $table) {
                if (!Schema::hasColumn('mw_players', 'avatar_id')) {
                    $table->unsignedBigInteger('avatar_id')->nullable()->after('avatar_media_id');
                }
                if (!Schema::hasColumn('mw_players', 'language_id')) {
                    $table->unsignedBigInteger('language_id')->nullable()->after('preferred_language_id');
                }
            });
        }

        // mw_player_profiles: games_played, games_won
        if (Schema::hasTable('mw_player_profiles')) {
            Schema::table('mw_player_profiles', function (Blueprint $table) {
                if (!Schema::hasColumn('mw_player_profiles', 'games_played')) {
                    $table->integer('games_played')->default(0)->after('total_simulations_completed');
                }
                if (!Schema::hasColumn('mw_player_profiles', 'games_won')) {
                    $table->integer('games_won')->default(0)->after('games_played');
                }
            });
        }

        // mw_simulation_sessions: make session_code nullable (harvest may not have it)
        if (Schema::hasTable('mw_simulation_sessions') && Schema::hasColumn('mw_simulation_sessions', 'session_code')) {
            // Change session_code from NOT NULL to nullable
            DB::statement('ALTER TABLE mw_simulation_sessions MODIFY session_code VARCHAR(20) NULL');
        }
    }

    public function down(): void
    {
        // Reverse is not critical for production
    }
};
