<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Uygulama-bazlı kalıcı tablolar.
 * API çağrılarını ortadan kaldırmak için tüm dış veri yerel DB'de saklanır.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ═══════════════════════════════════════════
        //  MissionWay Tabloları (mw_)
        // ═══════════════════════════════════════════

        Schema::create('mw_simulations', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('external_id')->unique();
            $table->foreignId('application_id')->constrained('applications');
            $table->string('name');
            $table->string('difficulty_level', 20)->nullable();
            $table->string('status', 20)->default('active');
            $table->text('description')->nullable();
            $table->string('cover_image')->nullable();
            $table->unsignedSmallInteger('min_players')->default(1);
            $table->unsignedSmallInteger('max_players')->default(5);
            $table->unsignedSmallInteger('estimated_duration')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('synced_at')->useCurrent();
            $table->timestamps();
        });

        Schema::create('mw_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('external_id')->unique();
            $table->foreignId('simulation_id')->constrained('mw_simulations')->cascadeOnDelete();
            $table->unsignedInteger('simulation_version_id')->nullable()->index();
            $table->string('session_code', 50)->nullable();
            $table->string('status', 20)->default('waiting');
            $table->integer('final_score')->nullable();
            $table->json('final_metrics')->nullable();
            $table->json('player_choices')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('synced_at')->useCurrent();
            $table->timestamps();
        });

        Schema::create('mw_players', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('external_id')->unique();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('application_id')->constrained('applications');
            $table->string('name');
            $table->string('surname')->nullable();
            $table->string('email')->nullable();
            $table->string('username')->nullable();
            $table->json('profile_data')->nullable();
            $table->timestamp('synced_at')->useCurrent();
            $table->timestamps();
        });

        Schema::create('mw_session_players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('mw_sessions')->cascadeOnDelete();
            $table->foreignId('player_id')->constrained('mw_players')->cascadeOnDelete();
            $table->string('role', 100)->nullable();
            $table->string('grade', 20)->nullable();
            $table->unsignedSmallInteger('completed_decisions')->default(0);
            $table->unsignedSmallInteger('total_decisions')->default(0);
            $table->smallInteger('health_metric')->default(0);
            $table->smallInteger('resource_metric')->default(0);
            $table->smallInteger('ethics_metric')->default(0);
            $table->smallInteger('adaptation_metric')->default(0);
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();

            $table->unique(['session_id', 'player_id']);
        });

        Schema::create('mw_simulation_paths', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('external_id');
            $table->unsignedInteger('simulation_version_id')->index();
            $table->unsignedInteger('parent_path_id')->nullable();
            $table->string('path_type', 30)->default('narrative');
            $table->unsignedSmallInteger('order_index')->default(0);
            $table->smallInteger('points')->default(0);
            $table->json('metrics')->nullable();
            $table->json('translations')->nullable();
            $table->boolean('is_ended')->default(false);
            $table->timestamp('synced_at')->useCurrent();
            $table->timestamps();

            $table->unique(['simulation_version_id', 'external_id']);
        });

        // ═══════════════════════════════════════════
        //  WayStartup Tabloları (ws_)
        // ═══════════════════════════════════════════

        Schema::create('ws_simulations', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('external_id')->unique();
            $table->foreignId('application_id')->constrained('applications');
            $table->string('name');
            $table->string('type', 50)->nullable();
            $table->string('category', 50)->nullable();
            $table->string('status', 20)->default('active');
            $table->json('metadata')->nullable();
            $table->timestamp('synced_at')->useCurrent();
            $table->timestamps();
        });

        Schema::create('ws_steps', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('external_id')->unique();
            $table->foreignId('simulation_id')->constrained('ws_simulations')->cascadeOnDelete();
            $table->string('name');
            $table->string('difficulty', 30)->nullable();
            $table->string('skill', 100)->nullable();
            $table->string('responsible_name')->nullable();
            $table->unsignedInteger('points')->default(0);
            $table->unsignedInteger('max_score')->default(150);
            $table->unsignedInteger('ai_score')->nullable();
            $table->unsignedSmallInteger('order_index')->default(0);
            $table->json('tools')->nullable();
            $table->json('questions')->nullable();
            $table->timestamp('synced_at')->useCurrent();
            $table->timestamps();
        });

        Schema::create('ws_tools', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('applications');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('icon_url')->nullable();
            $table->string('website_url')->nullable();
            $table->string('category', 50)->nullable();
            $table->timestamp('synced_at')->useCurrent();
            $table->timestamps();
        });

        Schema::create('ws_members', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('external_id')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('application_id')->constrained('applications');
            $table->integer('points')->default(0);
            $table->json('step_progress')->nullable();
            $table->json('step_evaluations')->nullable();
            $table->json('step_submissions')->nullable();
            $table->timestamp('synced_at')->useCurrent();
            $table->timestamps();

            $table->unique(['user_id', 'application_id']);
        });

        // ═══════════════════════════════════════════
        //  Vega / Way AI Coach Tabloları (vega_)
        // ═══════════════════════════════════════════

        Schema::create('vega_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('external_id')->unique();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('application_id')->constrained('applications');
            $table->string('module', 20)->default('lecturer');
            $table->string('user_name')->nullable();
            $table->string('user_surname')->nullable();
            $table->integer('score')->nullable();
            $table->unsignedInteger('duration_minutes')->nullable();
            $table->json('summary')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamp('synced_at')->useCurrent();
            $table->timestamps();

            $table->index(['user_id', 'module']);
        });

        Schema::create('vega_session_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('vega_sessions')->cascadeOnDelete();
            $table->string('role', 20)->default('user');
            $table->text('content')->nullable();
            $table->text('question')->nullable();
            $table->text('answer')->nullable();
            $table->smallInteger('score')->nullable();
            $table->smallInteger('max_score')->nullable();
            $table->text('feedback')->nullable();
            $table->json('metrics')->nullable();
            $table->json('options')->nullable();
            $table->unsignedSmallInteger('order_index')->default(0);
            $table->timestamps();

            $table->index('session_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vega_session_messages');
        Schema::dropIfExists('vega_sessions');
        Schema::dropIfExists('ws_members');
        Schema::dropIfExists('ws_tools');
        Schema::dropIfExists('ws_steps');
        Schema::dropIfExists('ws_simulations');
        Schema::dropIfExists('mw_simulation_paths');
        Schema::dropIfExists('mw_session_players');
        Schema::dropIfExists('mw_players');
        Schema::dropIfExists('mw_sessions');
        Schema::dropIfExists('mw_simulations');
    }
};
