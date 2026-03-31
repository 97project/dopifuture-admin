<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ws_step_questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('external_id')->unique();
            $table->unsignedBigInteger('step_id');
            $table->text('question_text');
            $table->integer('max_score')->default(0);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_required')->default(true);
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->foreign('step_id')->references('id')->on('ws_steps')->onDelete('cascade');
            $table->index('step_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ws_step_questions');
    }
};
