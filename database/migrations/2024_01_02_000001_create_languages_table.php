<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            $table->string('code', 5)->unique();
            $table->string('name', 50);
            $table->string('native_name', 50);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->string('fallback_code', 5)->nullable();
            $table->enum('direction', ['ltr', 'rtl'])->default('ltr');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('languages');
    }
};
