<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Vega Lecturer Lessons
        Schema::create('vega_lessons', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('external_id')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('category')->nullable();
            $table->string('difficulty')->nullable();
            $table->unsignedInteger('duration_minutes')->nullable();
            $table->text('icon_url')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
        });

        // Vega Simulator Scenarios
        Schema::create('vega_scenarios', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('external_id')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('category')->nullable();
            $table->string('difficulty')->nullable();
            $table->text('icon_url')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
        });

        // Vega WayWing Badges
        Schema::create('vega_wings', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('external_id')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('icon_url')->nullable();
            $table->unsignedInteger('points_required')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vega_wings');
        Schema::dropIfExists('vega_scenarios');
        Schema::dropIfExists('vega_lessons');
    }
};
