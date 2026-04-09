<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * SoftDeletes kaldırıldı — tüm tablolardaki deleted_at kolonlarını
     * ve soft-deleted kayıtları temizler.
     */
    public function up(): void
    {
        $tables = ['users', 'posts', 'pages'];

        foreach ($tables as $table) {
            if (Schema::hasColumn($table, 'deleted_at')) {
                // Önce soft-deleted kayıtları kalıcı olarak sil
                DB::table($table)->whereNotNull('deleted_at')->delete();

                Schema::table($table, function (Blueprint $t) {
                    $t->dropColumn('deleted_at');
                });
            }
        }
    }

    public function down(): void
    {
        $tables = ['users', 'posts', 'pages'];

        foreach ($tables as $table) {
            if (!Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->softDeletes();
                });
            }
        }
    }
};
