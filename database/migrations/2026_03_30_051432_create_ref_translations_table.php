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
        if (Schema::hasTable('ref_translations')) return;
        Schema::create('ref_translations', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type', 50)->comment('Entity type: simulation, role, simulation_path, challenge_option, objective, info_card, media_asset, metric_definition');
            $table->unsignedBigInteger('entity_id');
            // Assuming we use ref_languages for translations in Mission WAY
            $table->unsignedBigInteger('language_id');
            $table->json('fields')->comment('Translation fields as JSON: {"name": "...", "description": "..."}');
            $table->timestamp('deactivated_at')->nullable();
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->unique(['entity_type', 'entity_id', 'language_id'], 'uk_ref_trans_e_l');
            $table->index(['entity_type', 'entity_id'], 'idx_ref_trans_e');
            $table->index('language_id', 'idx_ref_trans_l');

            // $table->foreign('language_id')->references('id')->on('ref_languages')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ref_translations');
    }
};
