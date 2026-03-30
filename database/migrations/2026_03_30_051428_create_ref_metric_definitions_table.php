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
        Schema::create('ref_metric_definitions', function (Blueprint $table) {
            $table->id();
            $table->string('metric_key', 50)->unique()->comment('Unique metric key (e.g., resource, health, ethics)');
            $table->string('data_type', 20)->default('integer')->comment('Data type: integer, decimal, percentage, boolean');
            $table->decimal('min_value', 10, 2)->nullable()->comment('Minimum allowed value');
            $table->decimal('max_value', 10, 2)->nullable()->comment('Maximum allowed value');
            $table->decimal('default_value', 10, 2)->nullable()->comment('Default starting value');
            $table->string('icon', 100)->nullable()->comment('Icon identifier for UI');
            $table->string('color', 20)->nullable()->comment('Color code for UI (e.g., #FF5733)');
            $table->timestamp('deactivated_at')->nullable();
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ref_metric_definitions');
    }
};
