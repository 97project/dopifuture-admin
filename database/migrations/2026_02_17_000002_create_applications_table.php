<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 100)->unique();
            $table->json('name');             // {"tr": "...", "en": "..."}
            $table->json('description')->nullable();
            $table->string('icon')->nullable();
            $table->string('color', 20)->nullable();
            $table->string('connector_class')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
