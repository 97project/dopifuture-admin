<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->string('title', 200);
            $table->text('body');
            $table->json('channels');
            $table->enum('target_type', ['all', 'role', 'school', 'selected'])->default('all');
            $table->json('target_data')->nullable();
            $table->unsignedInteger('recipients_count')->default(0);
            $table->unsignedInteger('read_count')->default(0);
            $table->foreignId('template_id')->nullable()->constrained('notification_templates')->nullOnDelete();
            $table->foreignId('sent_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index('created_at');
            $table->index('sent_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
