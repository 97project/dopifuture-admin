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
        Schema::create('ref_simulations', function (Blueprint $table) {
            $table->id();
            $table->string('difficulty_level', 20)->nullable()->comment('Difficulty: easy, medium, hard, expert');
            $table->integer('estimated_duration')->nullable()->comment('Estimated duration in minutes');
            $table->integer('min_players')->nullable()->comment('Minimum number of players');
            $table->integer('max_players')->nullable()->comment('Maximum number of players');
            $table->integer('cover_image')->nullable()->comment('Media asset file id for cover image');
            $table->integer('background_image')->nullable()->comment('Media asset file id for background image');
            $table->timestamp('deactivated_at')->nullable();
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ref_simulations');
    }
};
