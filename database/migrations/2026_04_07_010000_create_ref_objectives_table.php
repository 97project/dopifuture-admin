<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ref_objectives', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('key')->nullable();
            $table->timestamps();
        });

        Schema::create('ref_path_objectives', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('simulation_path_id')->nullable();
            $table->unsignedBigInteger('objective_id')->nullable();
            $table->decimal('target_value', 10, 2)->nullable();
            $table->decimal('weight', 10, 2)->nullable();
            $table->timestamps();

            $table->foreign('simulation_path_id')->references('id')->on('ref_simulation_paths')->nullOnDelete();
            $table->foreign('objective_id')->references('id')->on('ref_objectives')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ref_path_objectives');
        Schema::dropIfExists('ref_objectives');
    }
};
