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
        if (Schema::hasTable('mw_session_players')) return;
        Schema::create('mw_session_players', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('simulation_session_id');
            $table->unsignedBigInteger('player_id');
            $table->unsignedBigInteger('role_id');
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamp('deactivated_at')->nullable();
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->unique(['simulation_session_id', 'player_id'], 'uk_mw_session_player');
            $table->index('simulation_session_id', 'idx_mw_ssn_plyr_s');
            $table->index('player_id', 'idx_mw_ssn_plyr_p');
            $table->index('role_id', 'idx_mw_ssn_plyr_r');

            $table->foreign('simulation_session_id', 'fk_mw_ssn_plyr_s')->references('id')->on('mw_simulation_sessions')->onDelete('cascade');
            $table->foreign('player_id', 'fk_mw_ssn_plyr_p')->references('id')->on('mw_players')->onDelete('cascade');
            // $table->foreign('role_id', 'fk_mw_ssn_plyr_r')->references('id')->on('ref_roles')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mw_session_players');
    }
};
