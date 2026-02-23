<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('application_user', function (Blueprint $table) {
            $table->timestamp('synced_at')->nullable()->after('granted_at');
            $table->string('sync_status', 20)->default('pending')->after('synced_at');  // pending, synced, failed
            $table->text('sync_error')->nullable()->after('sync_status');
        });
    }

    public function down(): void
    {
        Schema::table('application_user', function (Blueprint $table) {
            $table->dropColumn(['synced_at', 'sync_status', 'sync_error']);
        });
    }
};
