<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ws_step_evaluations')) return;
        Schema::create('ws_step_evaluations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('external_id')->unique()->nullable();
            $table->unsignedBigInteger('step_id');
            $table->unsignedBigInteger('member_id');
            $table->integer('attempt')->default(1);
            $table->integer('ai_total_score')->default(0);
            $table->integer('ai_max_score')->default(0);
            $table->integer('ai_coins')->default(0);
            $table->text('ai_overall_feedback')->nullable();
            $table->string('status')->default('PENDING'); // PENDING, COMPLETED, FAILED
            $table->timestamp('ai_evaluated_at')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->foreign('step_id')->references('id')->on('ws_steps')->onDelete('cascade');
            $table->foreign('member_id')->references('id')->on('ws_members')->onDelete('cascade');
            $table->index(['step_id', 'member_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ws_step_evaluations');
    }
};
