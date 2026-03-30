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
        Schema::create('mw_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('simulation_id')->nullable();
            $table->string('grade', 100)->nullable();
            $table->timestamp('deadline');
            $table->unsignedBigInteger('simulation_session_id')->nullable();
            $table->timestamp('deactivated_at')->nullable();
            $table->timestamps();
            $table->string('created_by', 255)->default('-1');
            $table->string('updated_by', 255)->nullable();

            $table->index('deadline', 'idx_mw_assign_deadline');
            
            $table->foreign('simulation_id', 'fk_mw_assign_sim')->references('id')->on('ref_simulations')->onDelete('cascade');
            $table->foreign('simulation_session_id', 'fk_mw_assign_ssn')->references('id')->on('mw_simulation_sessions')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mw_assignments');
    }
};
