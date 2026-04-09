<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Connectors\VegaConnector;
use Illuminate\Support\Facades\DB;

class MigrateToVegaIds extends Command
{
    protected $signature = 'migrate:vega-ids {--dry-run : Sadece haritalamayı göster, DB değiştirme}';
    protected $description = 'Migrates local DopiFuture Primary Keys (id) to Master Vega Auth IDs.';

    /**
     * Tables referencing users.id — format: [table => column]
     * We update each of these when a user's ID changes.
     */
    private const FK_MAP = [
        ['api_keys', 'user_id'],
        ['application_user', 'user_id'],
        ['application_user', 'granted_by'],
        ['app_user_data', 'user_id'],
        ['app_user_data', 'external_user_id'],
        ['app_user_progress', 'user_id'],
        ['app_user_sessions', 'user_id'],
        ['class_user', 'user_id'],
        ['licenses', 'user_id'],
        ['media', 'uploaded_by'],
        ['notification_logs', 'sent_by'],
        ['pages', 'author_id'],
        ['posts', 'author_id'],
        ['school_user', 'user_id'],
        ['seat_requests', 'user_id'],
        ['seat_requests', 'reviewed_by'],
        ['sessions', 'user_id'],
        ['user_devices', 'user_id'],
        ['vega_sessions', 'user_id'],
        ['ws_members', 'user_id'],
        ['mw_players', 'user_id'],
    ];

    /**
     * Polymorphic tables — format: [table, id_column, type_column]
     */
    private const POLY_MAP = [
        ['model_has_permissions', 'model_id', 'model_type'],
        ['model_has_roles', 'model_id', 'model_type'],
        ['personal_access_tokens', 'tokenable_id', 'tokenable_type'],
        ['notifications', 'notifiable_id', 'notifiable_type'],
    ];

    public function handle()
    {
        $this->warn("⚠️  DİKKAT: Bu işlem tüm veritabanı Foreign Key'lerini by-pass eder ve root ID'leri kaydırır!");

        $connector = app(VegaConnector::class);
        $dryRun = $this->option('dry-run');

        // Fetch users except admins (role names use hyphens: 'super-admin', 'admin')
        $users = User::whereDoesntHave('roles', function ($q) {
            $q->whereIn('name', ['super-admin', 'admin']);
        })->get();

        $this->info("Toplam taşınacak kullanıcı: " . $users->count());

        // ── Phase 1: Collect Vega IDs ──────────────────────────────
        $mappings = []; // oldId => newVegaId
        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        foreach ($users as $user) {
            $vegaResult = $connector->getUser($user);
            if (!$vegaResult) {
                $sync = $connector->syncUser($user);
                $vegaResult = $sync['response'] ?? null;
            }

            $vegaId = $vegaResult['id'] ?? $vegaResult['user']['id'] ?? null;

            if ($vegaId && (int) $user->id !== (int) $vegaId) {
                $mappings[(int) $user->id] = (int) $vegaId;
            }
            $bar->advance();
        }
        $bar->finish();
        $this->newLine();

        $this->info("ID değişecek kullanıcı: " . count($mappings));

        if (empty($mappings)) {
            $this->info("✅ Tüm kullanıcılar zaten Vega ID'leriyle eşleşiyor.");
            return;
        }

        // Show mapping table
        $rows = [];
        foreach ($mappings as $old => $new) {
            $u = $users->firstWhere('id', $old);
            $rows[] = [$old, $new, $u?->email ?? '?'];
        }
        $this->table(['Eski ID', 'Yeni Vega ID', 'E-posta'], $rows);

        if ($dryRun) {
            $this->warn("--dry-run aktif, veritabanı değiştirilmedi.");
            return;
        }

        // ── Phase 2: Batch SQL ile geçiş ──────────────────────────
        $offset = 100_000_000;

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        try {
            DB::beginTransaction();

            // Step A: Move ALL old IDs to temp offset (prevents collision)
            $this->info("Aşama 1/3: Geçici offset (+$offset) alanına taşınıyor...");
            foreach ($mappings as $oldId => $newId) {
                $tempId = $oldId + $offset;
                DB::table('users')->where('id', $oldId)->update(['id' => $tempId]);
                foreach (self::FK_MAP as [$table, $col]) {
                    $this->safeExec("UPDATE `$table` SET `$col` = $tempId WHERE `$col` = $oldId");
                }
                foreach (self::POLY_MAP as [$table, $idCol, $typeCol]) {
                    $this->safeExec("UPDATE `$table` SET `$idCol` = $tempId WHERE `$idCol` = $oldId AND `$typeCol` = 'App\\\\Models\\\\User'");
                }
            }
            $this->info("   ✓ Offset tamamlandı.");

            // Step B: Move from temp offset to final Vega ID
            $this->info("Aşama 2/3: Vega ID'lerine taşınıyor...");
            foreach ($mappings as $oldId => $newId) {
                $tempId = $oldId + $offset;
                DB::table('users')->where('id', $tempId)->update(['id' => $newId]);
                foreach (self::FK_MAP as [$table, $col]) {
                    $this->safeExec("UPDATE `$table` SET `$col` = $newId WHERE `$col` = $tempId");
                }
                foreach (self::POLY_MAP as [$table, $idCol, $typeCol]) {
                    $this->safeExec("UPDATE `$table` SET `$idCol` = $newId WHERE `$idCol` = $tempId AND `$typeCol` = 'App\\\\Models\\\\User'");
                }
            }
            $this->info("   ✓ Vega ID ataması tamamlandı.");

            // Step C: Update external services (Mission Way & Way Startup)
            $this->info("Aşama 3/3: Dış servislerde (Mission Way / Way Startup) userId güncelleniyor...");
            $this->updateExternalServices($mappings, $users);

            DB::commit();
            $this->info("✅ BAŞARILI! Tüm ID'ler Vega Master ID'leriyle değiştirildi.");
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error("❌ HATA! Geri alınıyor: " . $e->getMessage());
            $this->error($e->getTraceAsString());
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    /**
     * Run a raw SQL statement, silently ignoring missing-table errors.
     */
    private function safeExec(string $sql): void
    {
        try {
            DB::statement($sql);
        } catch (\PDOException $e) {
            // 1146 = table not found, 1054 = unknown column — skip gracefully
            if (!str_contains($e->getMessage(), '1146') && !str_contains($e->getMessage(), '1054')) {
                throw $e;
            }
        }
    }

    /**
     * DELETE old player/member by OLD userId, then CREATE new with Vega userId.
     * Bu sayede tüm 4 sistem (Panel26, Vega, MissionWay, WayStartup) aynı userId'yi taşır.
     */
    private function updateExternalServices(array $mappings, $users): void
    {
        $mwConnector = app(\App\Connectors\MissionWayConnector::class);
        $wsConnector = app(\App\Connectors\WayStartupConnector::class);

        foreach ($mappings as $oldId => $newId) {
            $user = $users->firstWhere('id', $oldId) ?? User::find($newId);
            if (!$user) continue;

            // ── Mission Way: DELETE eski userId → CREATE yeni Vega userId ──
            try {
                // 1. DELETE by old userId
                $dummyOld = new User(['id' => $oldId, 'email' => $user->email]);
                $dummyOld->id = $oldId;
                $deleted = $mwConnector->removeUser($dummyOld);

                // 2. CREATE with new Vega userId
                $user->id = $newId;
                $result = $mwConnector->syncUser($user);

                $status = ($deleted && ($result['success'] ?? false)) ? '✓' : '⚠️';
                $this->line("   MW: {$user->email} → DEL({$oldId}) + CREATE({$newId}) {$status}");
            } catch (\Throwable $e) {
                $this->warn("   MW: {$user->email} → HATA: " . $e->getMessage());
            }

            // ── Way Startup: DELETE eski userId → CREATE yeni Vega userId ──
            try {
                // 1. DELETE by old userId
                $dummyOld = new User(['id' => $oldId, 'email' => $user->email]);
                $dummyOld->id = $oldId;
                $deleted = $wsConnector->removeUser($dummyOld);

                // 2. CREATE with new Vega userId
                $user->id = $newId;
                $result = $wsConnector->syncUser($user);

                $status = ($deleted && ($result['success'] ?? false)) ? '✓' : '⚠️';
                $this->line("   WS: {$user->email} → DEL({$oldId}) + CREATE({$newId}) {$status}");
            } catch (\Throwable $e) {
                $this->warn("   WS: {$user->email} → HATA: " . $e->getMessage());
            }
        }
    }
}
