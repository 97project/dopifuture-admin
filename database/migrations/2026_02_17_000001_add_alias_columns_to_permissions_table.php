<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->string('alias_tr')->nullable()->after('guard_name');
            $table->string('alias_en')->nullable()->after('alias_tr');
            $table->boolean('is_deprecated')->default(false)->after('alias_en');
        });
    }

    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropColumn(['alias_tr', 'alias_en', 'is_deprecated']);
        });
    }
};
