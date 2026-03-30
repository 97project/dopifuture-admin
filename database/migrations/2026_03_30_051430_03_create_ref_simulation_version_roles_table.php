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
        if (Schema::hasTable('ref_simulation_version_roles')) return;
        Schema::create('ref_simulation_version_roles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('simulation_version_id');
            // Panel26 might have a roles table, but Mission Way has a specific `ref_role` or they use `roles`. 
            // Wait, Panel26 has Spatie/Permission roles. However, in Mission WAY, roles mean "Oyuncu Rolü" like CEO, CTO, vs.
            // I should use integer role_id and probably create a ref_roles table later or remove the foreign key constraint. Let's just create ref_role table.
            $table->unsignedBigInteger('role_id');
            $table->integer('priority_order')->nullable();
            $table->timestamp('deactivated_at')->nullable();
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->unique(['simulation_version_id', 'role_id'], 'uk_ref_sim_version_role_v_r');
            $table->index('simulation_version_id', 'idx_ref_sim_version_role_v');
            $table->index('role_id', 'idx_ref_sim_version_role_r');
            
            $table->foreign('simulation_version_id', 'fk_ref_svr_v')->references('id')->on('ref_simulation_versions')->onDelete('cascade');
            // $table->foreign('role_id')->references('id')->on('ref_roles')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ref_simulation_version_roles');
    }
};
