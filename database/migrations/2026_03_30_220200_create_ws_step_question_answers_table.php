<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ws_step_question_answers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('external_id')->unique()->nullable();
            $table->unsignedBigInteger('question_id');
            $table->unsignedBigInteger('member_id');
            $table->integer('attempt')->default(1);
            $table->text('text_answer')->nullable();
            $table->integer('ai_score')->default(0);
            $table->integer('ai_max_score')->default(0);
            $table->text('ai_feedback')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->foreign('question_id')->references('id')->on('ws_step_questions')->onDelete('cascade');
            $table->foreign('member_id')->references('id')->on('ws_members')->onDelete('cascade');
            $table->index(['question_id', 'member_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ws_step_question_answers');
    }
};
