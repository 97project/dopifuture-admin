<?php

namespace App\Console\Commands;

use App\Models\Application;
use App\Models\AppUserData;
use App\Models\User;
use App\Services\ConnectorSyncService;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * Periyodik veri toplama komutu.
 *
 * Son sync'ten bu yana geçen sürede değişen verileri çeker.
 * Scheduler ile otomatik çalışır.
 *
 * Usage:
 *   php artisan harvest:user-data               → tüm kullanıcılar (incremental)
 *   php artisan harvest:user-data --user=5      → tek kullanıcı (full)
 *   php artisan harvest:user-data --app=vega    → tek uygulama
 *   php artisan harvest:user-data --force       → incremental'i atla, hepsini çek
 */
class HarvestUserData extends Command
{
    protected $signature = 'harvest:user-data
                            {--user= : User ID (optional)}
                            {--app= : App slug (optional)}
                            {--force : Force full sync, ignore last synced_at}';

    protected $description = 'Kullanıcıların dış API verilerini periyodik olarak çek ve DB\'ye kaydet';

    public function handle(ConnectorSyncService $syncService): int
    {
        $startTime = now();
        $interval = (int) env('SYNC_INTERVAL_MINUTES', 60);
        $cutoff = $this->option('force') ? null : now()->subMinutes($interval);

        $this->info("📊 Veri toplama başlıyor" . ($cutoff ? " (son {$interval}dk'dan eski)" : ' (full)'));
        $this->newLine();

        // Hangi uygulamalar?
        if ($appSlug = $this->option('app')) {
            $apps = Application::where('slug', $appSlug)->active()->get();
        } else {
            $apps = Application::active()->whereNotNull('connector_class')->ordered()->get();
        }

        if ($apps->isEmpty()) {
            $this->warn('Aktif uygulama bulunamadı.');
            return self::SUCCESS;
        }

        // Hangi kullanıcılar?
        if ($userId = $this->option('user')) {
            $users = User::where('id', $userId)->get();
        } else {
            $users = $this->getUsersNeedingSync($apps, $cutoff);
        }

        $this->info("📋 {$apps->count()} uygulama × {$users->count()} kullanıcı");
        $this->newLine();

        $total = ['success' => 0, 'failed' => 0, 'skipped' => 0];

        foreach ($apps as $app) {
            $appName = $app->getTranslation('name');
            $this->comment("  → {$appName}");

            $connector = $app->resolveConnector();
            if (!$connector) {
                $this->warn("    ⏭️ Connector yok, atlanıyor");
                continue;
            }

            $appUsers = $users->filter(fn($u) => $app->users()->where('user_id', $u->id)->exists());

            if ($appUsers->isEmpty()) {
                $this->line("    📭 Bu uygulama için sync gerekli kullanıcı yok");
                continue;
            }

            $bar = $this->output->createProgressBar($appUsers->count());
            $bar->setFormat('    %current%/%max% [%bar%] %percent:3s%% — %message%');
            $bar->setMessage('Başlıyor...');

            foreach ($appUsers as $user) {
                $bar->setMessage($user->email);

                try {
                    if ($syncService->syncUserData($user, $app)) {
                        $total['success']++;
                    } else {
                        $total['failed']++;
                    }
                } catch (\Throwable $e) {
                    $total['failed']++;
                }

                $bar->advance();
            }

            $bar->setMessage('Tamam ✅');
            $bar->finish();
            $this->newLine();
        }

        $this->newLine();
        $this->table(
            ['Metrik', 'Değer'],
            [
                ['Başarılı', $total['success']],
                ['Başarısız', $total['failed']],
                ['Toplam', $total['success'] + $total['failed']],
            ]
        );

        $elapsed = now()->diffInSeconds($startTime);
        $this->info("Tamamlandı: {$elapsed}s");

        return self::SUCCESS;
    }

    /**
     * Sync'e ihtiyaç duyan kullanıcıları belirle.
     * Cutoff zamanından önce sync edilen veya hiç sync edilmemiş kullanıcılar.
     */
    private function getUsersNeedingSync($apps, ?Carbon $cutoff): \Illuminate\Database\Eloquent\Collection
    {
        if (!$cutoff) {
            // Force mode — tüm kullanıcılar
            $userIds = collect();
            foreach ($apps as $app) {
                $userIds = $userIds->merge($app->users()->pluck('users.id'));
            }
            return User::whereIn('id', $userIds->unique())->get();
        }

        // Incremental — son sync'ten beri geçen süreye bak
        $userIds = collect();
        foreach ($apps as $app) {
            // app_user_data tablosunda synced_at eski olanlar
            $staleUserIds = AppUserData::where('application_id', $app->id)
                ->where('synced_at', '<', $cutoff)
                ->pluck('user_id');

            // Hiç sync edilmemiş kullanıcılar
            $neverSyncedIds = $app->users()
                ->whereNotIn('users.id', AppUserData::where('application_id', $app->id)->pluck('user_id'))
                ->pluck('users.id');

            $userIds = $userIds->merge($staleUserIds)->merge($neverSyncedIds);
        }

        return User::whereIn('id', $userIds->unique())->get();
    }
}
