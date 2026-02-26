<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\ConnectorSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Bir kullanıcının tüm aktif uygulamalarıyla connector verilerini senkronize eden job.
 */
class SyncUserToAppsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        public User $user
    ) {}

    public function handle(ConnectorSyncService $syncService): void
    {
        Log::channel('daily')->info('[SyncUserToAppsJob] Starting sync', [
            'user_id' => $this->user->id,
            'email' => $this->user->email,
        ]);

        $results = $syncService->syncAllAppsForUser($this->user);

        Log::channel('daily')->info('[SyncUserToAppsJob] Completed', [
            'user_id' => $this->user->id,
            'success' => $results['success'],
            'failed' => $results['failed'],
            'total' => $results['total'],
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::channel('daily')->error('[SyncUserToAppsJob] Failed', [
            'user_id' => $this->user->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
