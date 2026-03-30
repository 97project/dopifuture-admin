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
        if (Schema::hasTable('mw_simulation_sessions')) return;
        Schema::create('mw_simulation_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('simulation_version_id');
            $table->string('session_code', 20)->unique()->comment('Unique session code for joining (e.g., "ABC123")');
            $table->string('status', 20)->default('waiting');
            $table->string('game_mode', 20)->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedBigInteger('final_path_id')->nullable();
            $table->integer('final_score')->default(0);
            $table->json('final_metrics')->nullable();
            $table->timestamp('abandoned_at')->nullable()->comment('When the session was abandoned'); // From 2026-03-18-01
            $table->timestamp('deactivated_at')->nullable();
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->index('simulation_version_id', 'idx_mw_sim_sssn_v');
            $table->index('status', 'idx_mw_sim_sssn_s');
            
            $table->foreign('simulation_version_id', 'fk_mw_sim_sssn_v')->references('id')->on('ref_simulation_versions')->onDelete('restrict');
            $table->foreign('final_path_id', 'fk_mw_sim_sssn_fp')->references('id')->on('ref_simulation_paths')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mw_simulation_sessions');
    }
};
