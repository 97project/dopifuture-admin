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
        Schema::create('ref_simulation_metric_bands', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('simulation_version_id');
            $table->string('metric_key', 50);
            $table->unsignedBigInteger('category_id');
            $table->decimal('min_value', 10, 2)->nullable();
            $table->decimal('max_value', 10, 2)->nullable();
            $table->integer('order_index')->nullable();
            $table->timestamp('deactivated_at')->nullable();
            $table->timestamps();
            $table->string('created_by', 255)->default('-1');
            $table->string('updated_by', 255)->default('-1');

            $table->foreign('category_id')->references('id')->on('ref_metric_band_categories');
            $table->index(['simulation_version_id', 'metric_key'], 'idx_ref_sim_metric_band_v_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ref_simulation_metric_bands');
    }
};
