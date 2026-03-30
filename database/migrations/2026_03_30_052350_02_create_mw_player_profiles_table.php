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
        Schema::create('mw_player_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('player_id')->unique();
            $table->integer('total_score')->default(0);
            $table->integer('total_simulations_completed')->default(0);
            $table->integer('total_play_time_minutes')->default(0);
            $table->unsignedBigInteger('last_completed_simulation_id')->nullable();
            $table->json('achievements')->nullable();
            $table->json('statistics')->nullable();
            $table->json('metric_stats')->nullable();
            $table->timestamp('deactivated_at')->nullable();
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->foreign('player_id')->references('id')->on('mw_players')->onDelete('cascade');
            $table->foreign('last_completed_simulation_id', 'fk_mw_pp_lcs')->references('id')->on('ref_simulations')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mw_player_profiles');
    }
};
