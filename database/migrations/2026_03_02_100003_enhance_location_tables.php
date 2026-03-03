<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->char('iso3', 3)->nullable()->after('code');
            $table->string('phonecode', 20)->nullable()->after('iso3');
            $table->string('native', 150)->nullable()->after('phonecode');
            $table->string('emoji', 10)->nullable()->after('native');
        });

        Schema::table('states', function (Blueprint $table) {
            $table->string('state_code', 10)->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->dropColumn(['iso3', 'phonecode', 'native', 'emoji']);
        });
        Schema::table('states', function (Blueprint $table) {
            $table->dropColumn('state_code');
        });
    }
};
