<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_user_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->string('module_type')->comment('simulation|session|step|lecture|chatbot');
            $table->string('module_id')->nullable()->comment('External module ID');
            $table->string('module_name')->nullable();
            $table->string('status')->default('not_started')->comment('not_started|in_progress|completed');
            $table->decimal('score', 8, 2)->nullable();
            $table->decimal('max_score', 8, 2)->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->json('metadata')->nullable()->comment('Extra connector-specific data');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'application_id']);
            $table->index(['application_id', 'module_type']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_user_progress');
    }
};
