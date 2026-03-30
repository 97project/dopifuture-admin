<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add missing status/created_by columns to assignment tables.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mw_assignments', function (Blueprint $table) {
            if (!Schema::hasColumn('mw_assignments', 'status')) {
                $table->string('status', 20)->default('active')->after('deadline');
            }
        });

        Schema::table('mw_assignment_players', function (Blueprint $table) {
            if (!Schema::hasColumn('mw_assignment_players', 'status')) {
                $table->string('status', 20)->default('assigned')->after('player_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('mw_assignments', function (Blueprint $table) {
            $table->dropColumn(['status']);
        });
        Schema::table('mw_assignment_players', function (Blueprint $table) {
            $table->dropColumn(['status']);
        });
    }
};
