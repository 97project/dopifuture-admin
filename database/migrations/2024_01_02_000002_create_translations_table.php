<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->string('group', 100)->index();
            $table->string('key', 255);
            $table->string('locale', 5)->index();
            $table->text('value')->nullable();
            $table->timestamps();

            $table->unique(['group', 'key', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('translations');
    }
};
