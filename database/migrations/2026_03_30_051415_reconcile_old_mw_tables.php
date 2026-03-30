<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Eski Mart 13 migrasyonundaki MW tablolarını (mw_simulations, mw_sessions, mw_simulation_paths)
 * kaldırarak yeni 1:1 PostgreSQL parite şemasına (ref_simulations, mw_simulation_sessions, ref_simulation_paths)
 * geçişi mümkün kılar.
 *
 * mw_players ve mw_session_players tablolarının şemaları güncellenir (DROP + CREATE).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        // 1. Eski MW tabloları kaldır (yeni tablolar farklı isimlerde: ref_simulations, mw_simulation_sessions, ref_simulation_paths)
        Schema::dropIfExists('mw_simulation_paths');
        Schema::dropIfExists('mw_session_players');
        Schema::dropIfExists('mw_sessions');
        Schema::dropIfExists('mw_simulations');
        Schema::dropIfExists('mw_players');

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        // Rollback is not supported — old schema was incompatible with 1:1 PostgreSQL parity
    }
};
