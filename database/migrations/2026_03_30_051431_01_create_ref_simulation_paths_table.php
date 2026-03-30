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
        if (Schema::hasTable('ref_simulation_paths')) return;
        Schema::create('ref_simulation_paths', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('simulation_version_id');
            $table->unsignedBigInteger('parent_path_id')->nullable();
            $table->unsignedBigInteger('media_asset_id')->nullable();
            $table->integer('order_index')->nullable();
            $table->integer('points')->default(0);
            $table->json('metrics')->nullable()->comment('Dynamic metrics as JSON: {"resource": 75, "health": 80, "ethics": 90}');
            $table->string('path_type', 50)->nullable()->comment('Path type: multiple_choice, single_choice, true_false, open_ended, task');
            $table->integer('max_wait_time')->nullable()->comment('Maximum wait time in milliseconds before auto-advancing');
            $table->integer('min_wait_time')->nullable()->comment('Minimum wait time in milliseconds before the host can skip');
            $table->integer('path_points')->nullable();
            $table->boolean('is_ended')->default(false)->comment('Indicates whether this path is an ending path');
            
            $table->timestamp('deactivated_at')->nullable();
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->index('simulation_version_id', 'idx_ref_sim_path_v');
            $table->index('parent_path_id', 'idx_ref_sim_path_p');
            
            $table->foreign('simulation_version_id', 'fk_ref_sim_path_v')->references('id')->on('ref_simulation_versions')->onDelete('cascade');
            $table->foreign('parent_path_id', 'fk_ref_sim_path_p')->references('id')->on('ref_simulation_paths')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ref_simulation_paths');
    }
};
