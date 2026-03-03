<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Add a temporary plain-text column
        Schema::table('schools', function (Blueprint $table) {
            $table->string('name_plain', 255)->nullable()->after('id');
            $table->string('state', 150)->nullable()->after('country');
        });

        // 2. Copy JSON name → plain text (extract 'tr' value)
        $schools = DB::table('schools')->get(['id', 'name']);
        foreach ($schools as $school) {
            $decoded = json_decode($school->name, true);
            $plainName = is_array($decoded)
                ? ($decoded['tr'] ?? $decoded['en'] ?? 'Unnamed')
                : (string) $school->name;

            DB::table('schools')->where('id', $school->id)->update(['name_plain' => $plainName]);
        }

        // 3. Drop old JSON column and rename plain to name
        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn('name');
        });

        Schema::table('schools', function (Blueprint $table) {
            $table->renameColumn('name_plain', 'name');
        });
    }

    public function down(): void
    {
        // Add back JSON column
        Schema::table('schools', function (Blueprint $table) {
            $table->renameColumn('name', 'name_plain');
        });

        Schema::table('schools', function (Blueprint $table) {
            $table->json('name')->after('id');
        });

        // Copy plain text back to JSON
        $schools = DB::table('schools')->get(['id', 'name_plain']);
        foreach ($schools as $school) {
            $json = json_encode(['tr' => $school->name_plain, 'en' => $school->name_plain]);
            DB::table('schools')->where('id', $school->id)->update(['name' => $json]);
        }

        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn(['name_plain', 'state']);
        });
    }
};
