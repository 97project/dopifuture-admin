<?php

namespace App\Console\Commands;

use App\Models\Application;
use App\Models\User;
use App\Services\ConnectorSyncService;
use Illuminate\Console\Command;

/**
 * Connector verilerini toplu olarak senkronize eden artisan komutu.
 *
 * Usage:
 *   php artisan sync:connector-data              → tüm uygulamalar, tüm kullanıcılar
 *   php artisan sync:connector-data --app=vega   → sadece bir uygulama
 *   php artisan sync:connector-data --user=5     → sadece bir kullanıcı
 */
class SyncConnectorData extends Command
{
    protected $signature = 'sync:connector-data
                            {--app= : Application slug to sync (optional)}
                            {--user= : User ID to sync (optional)}';

    protected $description = 'Sync connector data from external APIs to database tables';

    public function handle(ConnectorSyncService $syncService): int
    {
        $startTime = now();

        // Single user mode
        if ($userId = $this->option('user')) {
            $user = User::findOrFail($userId);
            $this->info("Syncing user #{$user->id}: {$user->email}");

            $results = $syncService->syncAllAppsForUser($user);
            $this->printResults($results);
            return self::SUCCESS;
        }

        // Single app mode
        if ($appSlug = $this->option('app')) {
            $app = Application::where('slug', $appSlug)->firstOrFail();
            $this->info("Syncing app: {$app->name}");

            $results = $syncService->syncAllUsersForApp($app);
            $this->printResults($results);
            return self::SUCCESS;
        }

        // Full sync — all apps
        $apps = Application::active()->ordered()->get();
        $this->info("Full sync: {$apps->count()} apps");

        $totalResults = ['success' => 0, 'failed' => 0, 'total' => 0];
        $bar = $this->output->createProgressBar($apps->count());

        foreach ($apps as $app) {
            $this->newLine();
            $this->comment("  → {$app->name}...");

            $results = $syncService->syncAllUsersForApp($app);
            $totalResults['success'] += $results['success'];
            $totalResults['failed'] += $results['failed'];
            $totalResults['total'] += $results['total'];

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->printResults($totalResults);

        $elapsed = now()->diffInSeconds($startTime);
        $this->info("Tamamlandı: {$elapsed}s");

        return self::SUCCESS;
    }

    private function printResults(array $results): void
    {
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total', $results['total']],
                ['Success', $results['success']],
                ['Failed', $results['failed']],
            ]
        );
    }
}
