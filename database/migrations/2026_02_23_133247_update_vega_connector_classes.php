<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Role Galaxy, Way AI Coach, Study Space → VegaConnector'a geçiş.
     * 3 placeholder connector tek Vega API ile çalışacak.
     */
    public function up(): void
    {
        // connector_class güncelle
        DB::table('applications')
            ->whereIn('slug', ['role-galaxy', 'way-ai-coach', 'study-space'])
            ->update(['connector_class' => 'App\\Connectors\\VegaConnector']);

        // Eski failed kayıtları pending'e çevir (temiz başlangıç)
        $appIds = DB::table('applications')
            ->whereIn('slug', ['role-galaxy', 'way-ai-coach', 'study-space'])
            ->pluck('id');

        DB::table('application_user')
            ->whereIn('application_id', $appIds)
            ->where('sync_status', 'failed')
            ->update([
                'sync_status' => 'pending',
                'sync_error' => null,
            ]);
    }

    /**
     * Geri al — eski placeholder connector'lara dön.
     */
    public function down(): void
    {
        $map = [
            'role-galaxy' => 'App\\Connectors\\RoleGalaxyConnector',
            'way-ai-coach' => 'App\\Connectors\\WayAiCoachConnector',
            'study-space' => 'App\\Connectors\\StudySpaceConnector',
        ];

        foreach ($map as $slug => $class) {
            DB::table('applications')
                ->where('slug', $slug)
                ->update(['connector_class' => $class]);
        }
    }
};
