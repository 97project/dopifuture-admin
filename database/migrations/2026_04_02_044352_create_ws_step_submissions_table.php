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
        Schema::create('ws_step_submissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('external_id')->unique()->nullable()->comment('PG startup_step_submission.id');
            $table->unsignedBigInteger('member_id');
            $table->unsignedBigInteger('step_external_id')->nullable();
            
            $table->string('file_name')->nullable();
            $table->text('file_url')->nullable();
            $table->string('file_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            
            $table->text('link_url')->nullable();
            $table->string('link_title')->nullable();
            $table->string('link_platform')->nullable();
            
            $table->string('status')->nullable();
            $table->text('feedback')->nullable();
            $table->integer('points_earned')->nullable();
            
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ws_step_submissions');
    }
};
