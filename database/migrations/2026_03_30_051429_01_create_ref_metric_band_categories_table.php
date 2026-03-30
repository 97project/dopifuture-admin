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
        if (Schema::hasTable('ref_metric_band_categories')) return;
        Schema::create('ref_metric_band_categories', function (Blueprint $table) {
            $table->id();
            $table->string('key', 50)->unique()->comment('Machine-readable key e.g. good, average, poor');
            $table->string('color', 20)->nullable()->comment('Default UI color for this category');
            $table->integer('order_index')->nullable();
            $table->decimal('score_multiplier', 5, 2)->nullable()->default(1.0)->comment('E.g. 1.0, 0.5, 0.0');
            $table->timestamp('deactivated_at')->nullable();
            $table->timestamps();
            $table->string('created_by', 255)->default('-1');
            $table->string('updated_by', 255)->default('-1');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ref_metric_band_categories');
    }
};
