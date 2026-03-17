<?php

namespace App\Jobs;

use App\Models\Application;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Kullanıcı bilgileri güncellendiğinde (edit, reset password)
 * tüm aktif uygulamalardaki connector'larda updateUser() çağırır.
 *
 * Connector class deduplication: Aynı connector (ör. VegaConnector)
 * birden fazla app'te kullanılıyorsa sadece 1 kez çağrılır.
 */
class UpdateUserInAppsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        public User $user
    ) {}

    public function handle(): void
    {
        Log::info('[UpdateUserInApps] Starting update sync', [
            'user_id' => $this->user->id,
            'email'   => $this->user->email,
        ]);

        $apps = Application::active()->get();
        $seen    = [];
        $success = 0;
        $failed  = 0;
        $skipped = 0;

        foreach ($apps as $app) {
            $connector = $app->getConnector();
            if (!$connector) {
                continue;
            }

            // Aynı connector class'ı tekrar çağırma
            $class = get_class($connector);
            if (isset($seen[$class])) {
                $skipped++;
                continue;
            }
            $seen[$class] = true;

            try {
                $result = $connector->updateUser($this->user);
                if ($result['success'] ?? false) {
                    $success++;
                } else {
                    $failed++;
                    Log::warning('[UpdateUserInApps] Update failed', [
                        'user_id' => $this->user->id,
                        'app'     => $app->slug,
                        'error'   => $result['error'] ?? 'unknown',
                    ]);
                }
            } catch (\Throwable $e) {
                $failed++;
                Log::error('[UpdateUserInApps] Exception', [
                    'user_id' => $this->user->id,
                    'app'     => $app->slug,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        Log::info('[UpdateUserInApps] Completed', [
            'user_id' => $this->user->id,
            'success' => $success,
            'failed'  => $failed,
            'skipped' => $skipped,
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('[UpdateUserInApps] Job failed permanently', [
            'user_id' => $this->user->id,
            'error'   => $exception->getMessage(),
        ]);
    }
}
