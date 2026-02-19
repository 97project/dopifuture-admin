<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // User ↔ School pivot
        Schema::create('school_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role', 30)->default('student');   // school-admin, principal, teacher, student
            $table->timestamps();
            $table->unique(['school_id', 'user_id']);
        });

        // User ↔ Class pivot
        Schema::create('class_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role', 20)->default('student');   // teacher, student
            $table->timestamps();
            $table->unique(['class_id', 'user_id']);
        });

        // User ↔ Application pivot
        Schema::create('application_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('applications')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('granted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('granted_at')->nullable();
            $table->timestamps();
            $table->unique(['application_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_user');
        Schema::dropIfExists('class_user');
        Schema::dropIfExists('school_user');
    }
};
