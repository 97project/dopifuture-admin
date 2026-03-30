<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Drop unique index on session_code — multiple sessions can share the same code
 * (e.g., when retried or reused).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mw_simulation_sessions', function (Blueprint $table) {
            // Drop unique constraint if exists
            try {
                $table->dropUnique('mw_simulation_sessions_session_code_unique');
            } catch (\Throwable $e) {
                // May not exist with this exact name
            }
        });

        // Fallback: raw SQL
        try {
            \DB::statement('ALTER TABLE mw_simulation_sessions DROP INDEX mw_simulation_sessions_session_code_unique');
        } catch (\Throwable $e) {}
    }

    public function down(): void
    {
        // Don't re-add — it causes data integrity issues
    }
};
