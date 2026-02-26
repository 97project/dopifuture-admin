<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_user_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->string('session_type')->comment('simulation|lecture|chat|practice');
            $table->string('external_session_id')->nullable();
            $table->string('session_name')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->decimal('score', 8, 2)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'application_id']);
            $table->index('session_type');
            $table->index('started_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_user_sessions');
    }
};
