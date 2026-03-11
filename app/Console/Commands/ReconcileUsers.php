<?php

namespace App\Console\Commands;

use App\Models\Application;
use App\Services\CrossAppSyncService;
use Illuminate\Console\Command;

/**
 * Cross-app kullanıcı reconciliation komutu.
 *
 * Usage:
 *   php artisan sync:reconcile                → tüm kullanıcıları reconcile et
 *   php artisan sync:reconcile --user=5       → tek kullanıcı
 *   php artisan sync:reconcile --discover     → uzak app'lerden kullanıcı keşfet
 *   php artisan sync:reconcile --app=vega     → tek app discover
 */
class ReconcileUsers extends Command
{
    protected $signature = 'sync:reconcile
                            {--user= : User ID to reconcile}
                            {--discover : Discover remote users from external apps}
                            {--app= : App slug for discovery (optional)}';

    protected $description = 'Cross-app kullanıcı reconciliation — eksik kullanıcıları tamamla';

    public function handle(CrossAppSyncService $service): int
    {
        $startTime = now();

        // ─── Single user ───
        if ($userId = $this->option('user')) {
            $user = \App\Models\User::findOrFail($userId);
            $this->info("🔄 Reconcile: {$user->name} ({$user->email})");

            $results = $service->reconcileUser($user);
            $this->showUserResults($results);

            return self::SUCCESS;
        }

        // ─── Discovery mode ───
        if ($this->option('discover')) {
            return $this->runDiscovery($service);
        }

        // ─── Full reconcile ───
        $this->info('🔄 Tüm kullanıcılar reconcile ediliyor...');
        $this->newLine();

        $summary = $service->reconcileAllUsers(function ($user, $result, $current, $total) {
            $statuses = collect($result)->map(fn($r) => match ($r['status']) {
                'exists'  => '✅',
                'created' => '🆕',
                'failed'  => '❌',
                'error'   => '💥',
                default   => '⏭️',
            });

            $this->line(sprintf(
                '  [%d/%d] %-30s %s',
                $current,
                $total,
                $user->email,
                $statuses->map(fn($icon, $app) => "{$app}:{$icon}")->implode(' ')
            ));
        });

        $this->newLine();
        $this->table(
            ['Metrik', 'Değer'],
            [
                ['Toplam kullanıcı', $summary['total']],
                ['Mevcut (exists)', $summary['exists']],
                ['Oluşturulan (created)', $summary['created']],
                ['Başarısız (failed)', $summary['failed']],
                ['Hata sayısı', count($summary['errors'])],
            ]
        );

        if (!empty($summary['errors'])) {
            $this->newLine();
            $this->warn('⚠️ Hatalar:');
            foreach (array_slice($summary['errors'], 0, 10) as $err) {
                $this->error("  User #{$err['user_id']} → {$err['app']}: {$err['error']}");
            }
            if (count($summary['errors']) > 10) {
                $this->comment('  ... ve ' . (count($summary['errors']) - 10) . ' hata daha');
            }
        }

        $elapsed = now()->diffInSeconds($startTime);
        $this->info("Tamamlandı: {$elapsed}s");

        return self::SUCCESS;
    }

    private function runDiscovery(CrossAppSyncService $service): int
    {
        if ($appSlug = $this->option('app')) {
            $apps = Application::where('slug', $appSlug)->get();
        } else {
            $apps = Application::active()->whereNotNull('connector_class')->ordered()->get();
        }

        foreach ($apps as $app) {
            $appName = $app->getTranslation('name');
            $this->info("🔍 Discovery: {$appName}");

            $result = $service->discoverRemoteUsers($app);

            $this->table(
                ['Metrik', 'Değer'],
                [
                    ['Eşleşen', $result['matched']],
                    ['Orphan (panelde yok)', $result['orphaned']],
                    ['Hata', count($result['errors'])],
                ]
            );

            if (!empty($result['orphan_list'])) {
                $this->warn('  Orphan kullanıcılar:');
                foreach (array_slice($result['orphan_list'], 0, 10) as $orphan) {
                    $this->line("    📧 {$orphan['email']} — {$orphan['name']}");
                }
                if (count($result['orphan_list']) > 10) {
                    $this->comment('    ... ve ' . (count($result['orphan_list']) - 10) . ' orphan daha');
                }
            }

            $this->newLine();
        }

        return self::SUCCESS;
    }

    private function showUserResults(array $results): void
    {
        $rows = [];
        foreach ($results as $app => $r) {
            $icon = match ($r['status']) {
                'exists'  => '✅ Mevcut',
                'created' => '🆕 Oluşturuldu',
                'failed'  => '❌ Başarısız',
                'error'   => '💥 Hata',
                default   => '⏭️ Atlandı',
            };
            $rows[] = [$app, $icon, $r['external_id'] ?? $r['error'] ?? '—'];
        }

        $this->table(['App', 'Durum', 'Detay'], $rows);
    }
}
