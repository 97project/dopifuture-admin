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
        if (Schema::hasTable('ref_simulation_versions')) return;
        Schema::create('ref_simulation_versions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('simulation_id');
            $table->integer('version_number');
            $table->string('status', 20)->default('draft')->comment('Status: draft, review, published, archived');
            $table->boolean('is_active')->default(false);
            $table->text('changelog')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->unsignedBigInteger('published_by')->nullable();
            $table->timestamp('deactivated_at')->nullable();
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->unique(['simulation_id', 'version_number'], 'uk_ref_sim_version_sim_num');
            $table->index('status', 'idx_ref_sim_version_status');
            $table->index(['simulation_id', 'is_active'], 'idx_ref_sim_version_active');
            $table->foreign('simulation_id')->references('id')->on('ref_simulations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ref_simulation_versions');
    }
};
