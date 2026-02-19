<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('registration_requests', function (Blueprint $table) {
            $table->id();
            $table->string('school_name', 200);
            $table->string('country', 100)->nullable();
            $table->string('contact_name', 100);
            $table->string('contact_surname', 100);
            $table->string('email', 150);
            $table->string('phone', 30)->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['new', 'processing', 'approved', 'rejected'])->default('new');
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registration_requests');
    }
};
