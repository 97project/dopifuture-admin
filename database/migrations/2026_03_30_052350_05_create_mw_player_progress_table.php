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
        Schema::create('mw_player_progress', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('player_id');
            $table->unsignedBigInteger('simulation_session_id');
            $table->unsignedBigInteger('simulation_version_id');
            $table->unsignedBigInteger('current_path_id')->nullable();
            $table->integer('current_score')->default(0);
            $table->json('current_metrics')->nullable();
            // From 2026-03-11-01 modify user progress for assignment: 'assignment_id' usually. Let me check later, I will add assignment_id.
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('deactivated_at')->nullable();
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->index('player_id', 'idx_mw_plyr_prog_p');
            $table->index('simulation_session_id', 'idx_mw_plyr_prog_s');
            $table->index('simulation_version_id', 'idx_mw_plyr_prog_v');
            $table->index('current_path_id', 'idx_mw_plyr_prog_cp');
            
            $table->foreign('player_id', 'fk_mw_plyr_prog_p')->references('id')->on('mw_players')->onDelete('cascade');
            $table->foreign('simulation_session_id', 'fk_mw_plyr_prog_s')->references('id')->on('mw_simulation_sessions')->onDelete('cascade');
            $table->foreign('simulation_version_id', 'fk_mw_plyr_prog_v')->references('id')->on('ref_simulation_versions')->onDelete('cascade');
            $table->foreign('current_path_id', 'fk_mw_plyr_prog_cp')->references('id')->on('ref_simulation_paths')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mw_player_progress');
    }
};
