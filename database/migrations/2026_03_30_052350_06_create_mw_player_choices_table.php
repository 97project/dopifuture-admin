<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('mw_player_choices')) return;
        Schema::create('mw_player_choices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('player_id');
            $table->unsignedBigInteger('simulation_session_id');
            // challenge_id, challenge_option_id, current_path_id, next_path_id dropped by 2026-02-04-02
            
            $table->unsignedBigInteger('previous_path_id')->nullable();
            // added by 2026-02-04-02
            $table->unsignedBigInteger('simulation_path_id')->nullable();
            $table->unsignedBigInteger('selected_path_id')->nullable();
            
            // added by 2026-03-14-02
            $table->unsignedBigInteger('decided_path_id')->nullable();
            
            $table->integer('response_time_seconds')->nullable();
            $table->integer('points_earned')->default(0);
            $table->boolean('is_correct')->nullable();
            // From 2026-02-02-01: added updated_at and updated_by which are covered below.
            
            $table->json('metrics_before')->nullable();
            $table->json('metrics_after')->nullable();
            $table->timestamp('deactivated_at')->nullable();
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->index('player_id', 'idx_mw_plyr_choice_p');
            $table->index('simulation_session_id', 'idx_mw_plyr_choice_ss');
            $table->index('previous_path_id', 'idx_mw_plyr_choice_pp');
            $table->index('simulation_path_id', 'idx_mw_plyr_choice_sp');
            $table->index('selected_path_id', 'idx_mw_plyr_choice_selp');
            $table->index('decided_path_id', 'idx_mw_plyr_choice_decp');

            $table->foreign('player_id', 'fk_mw_plyr_choice_p')->references('id')->on('mw_players')->onDelete('cascade');
            $table->foreign('simulation_session_id', 'fk_mw_plyr_choice_s')->references('id')->on('mw_simulation_sessions')->onDelete('cascade');
            $table->foreign('previous_path_id', 'fk_mw_plyr_choice_pp')->references('id')->on('ref_simulation_paths')->onDelete('set null');
            $table->foreign('simulation_path_id', 'fk_mw_plyr_choice_sp')->references('id')->on('ref_simulation_paths')->onDelete('cascade');
            $table->foreign('selected_path_id', 'fk_mw_plyr_choice_selp')->references('id')->on('ref_simulation_paths')->onDelete('cascade');
            $table->foreign('decided_path_id', 'fk_mw_plyr_choice_decp')->references('id')->on('ref_simulation_paths')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mw_player_choices');
    }
};
