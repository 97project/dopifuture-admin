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
        if (Schema::hasTable('ref_info_cards')) return;
        Schema::create('ref_info_cards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('simulation_path_id');
            $table->unsignedBigInteger('role_id');
            $table->integer('display_order')->nullable();
            $table->unsignedBigInteger('icon_asset_id')->nullable();
            $table->timestamp('deactivated_at')->nullable();
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->unique(['simulation_path_id', 'role_id', 'display_order'], 'uk_ref_info_card_p_r_o');
            $table->index('simulation_path_id', 'idx_ref_info_card_p');
            $table->index('role_id', 'idx_ref_info_card_r');

            $table->foreign('simulation_path_id', 'fk_ref_info_card_p')->references('id')->on('ref_simulation_paths')->onDelete('cascade');
            // $table->foreign('role_id')->references('id')->on('ref_roles')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ref_info_cards');
    }
};
