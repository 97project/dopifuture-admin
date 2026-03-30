<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Align ref table schemas with harvest pipeline requirements.
 * Adds missing columns needed for metric enrichment and display.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ref_metric_definitions — add 'key' alias, 'name', 'unit_label'
        Schema::table('ref_metric_definitions', function (Blueprint $table) {
            if (!Schema::hasColumn('ref_metric_definitions', 'key')) {
                $table->string('key', 50)->nullable()->after('metric_key');
            }
            if (!Schema::hasColumn('ref_metric_definitions', 'name')) {
                $table->string('name', 100)->nullable()->after('key');
            }
            if (!Schema::hasColumn('ref_metric_definitions', 'unit_label')) {
                $table->string('unit_label', 50)->nullable()->after('color');
            }
        });

        // Sync existing metric_key → key for any existing rows
        \DB::statement("UPDATE ref_metric_definitions SET `key` = metric_key WHERE `key` IS NULL AND metric_key IS NOT NULL");

        // ref_metric_band_categories — add 'label', 'scoring_impact'
        Schema::table('ref_metric_band_categories', function (Blueprint $table) {
            if (!Schema::hasColumn('ref_metric_band_categories', 'label')) {
                $table->string('label', 100)->nullable()->after('key');
            }
            if (!Schema::hasColumn('ref_metric_band_categories', 'scoring_impact')) {
                $table->integer('scoring_impact')->default(0)->after('color');
            }
        });

        // ref_simulation_metric_bands — add 'metric_id' (FK to ref_metric_definitions.id)
        Schema::table('ref_simulation_metric_bands', function (Blueprint $table) {
            if (!Schema::hasColumn('ref_simulation_metric_bands', 'metric_id')) {
                $table->unsignedBigInteger('metric_id')->nullable()->after('metric_key');
            }
        });

        // ref_roles — add 'name', 'key', 'icon', 'color'
        Schema::table('ref_roles', function (Blueprint $table) {
            if (!Schema::hasColumn('ref_roles', 'name')) {
                $table->string('name', 100)->nullable()->after('id');
            }
            if (!Schema::hasColumn('ref_roles', 'key')) {
                $table->string('key', 50)->nullable()->after('name');
            }
            if (!Schema::hasColumn('ref_roles', 'icon')) {
                $table->string('icon', 100)->nullable()->after('key');
            }
            if (!Schema::hasColumn('ref_roles', 'color')) {
                $table->string('color', 20)->nullable()->after('icon');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ref_metric_definitions', function (Blueprint $table) {
            $table->dropColumn(['key', 'name', 'unit_label']);
        });
        Schema::table('ref_metric_band_categories', function (Blueprint $table) {
            $table->dropColumn(['label', 'scoring_impact']);
        });
        Schema::table('ref_simulation_metric_bands', function (Blueprint $table) {
            $table->dropColumn(['metric_id']);
        });
        Schema::table('ref_roles', function (Blueprint $table) {
            $table->dropColumn(['name', 'key', 'icon', 'color']);
        });
    }
};
