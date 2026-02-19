<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('group', 50)->index();
            $table->string('key', 100);
            $table->text('value')->nullable();
            $table->string('type', 20)->default('string');
            $table->boolean('is_encrypted')->default(false);
            $table->boolean('is_translatable')->default(false);
            $table->json('options')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();

            $table->unique(['group', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
