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
        if (Schema::hasTable('mw_assignment_players')) return;
        Schema::create('mw_assignment_players', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assignment_id');
            $table->unsignedBigInteger('player_id');
            $table->timestamp('deactivated_at')->nullable();
            $table->timestamps();
            $table->string('created_by', 255)->default('-1');
            $table->string('updated_by', 255)->nullable();

            $table->index('assignment_id', 'idx_mw_assp_ass_id');
            $table->index('player_id', 'idx_mw_assp_p_id');

            $table->foreign('assignment_id', 'fk_mw_assp_ass')->references('id')->on('mw_assignments')->onDelete('restrict');
            $table->foreign('player_id', 'fk_mw_assp_plyr')->references('id')->on('mw_players')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mw_assignment_players');
    }
};
