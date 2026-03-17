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
 * Kullanıcı silindiğinde tüm aktif uygulamalardan kaldırır.
 *
 * User nesnesi silinmeden ÖNCE dispatch edilmelidir,
 * böylece SerializesModels user'ı serialize edebilir.
 * Alternatif olarak, user ID ve email primitif olarak tutulur.
 */
class RemoveUserFromAppsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        public User $user
    ) {}

    public function handle(): void
    {
        Log::channel('daily')->info('[RemoveUserFromApps] Starting removal sync', [
            'user_id' => $this->user->id,
            'email'   => $this->user->email,
        ]);

        $apps = Application::active()->get();
        $success = 0;
        $failed  = 0;

        foreach ($apps as $app) {
            $connector = $app->getConnector();
            if (!$connector) {
                continue;
            }

            try {
                if ($connector->removeUser($this->user)) {
                    $success++;
                } else {
                    $failed++;
                    Log::channel('daily')->warning('[RemoveUserFromApps] Removal failed', [
                        'user_id' => $this->user->id,
                        'app'     => $app->slug,
                    ]);
                }
            } catch (\Throwable $e) {
                $failed++;
                Log::channel('daily')->error('[RemoveUserFromApps] Exception', [
                    'user_id' => $this->user->id,
                    'app'     => $app->slug,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        Log::channel('daily')->info('[RemoveUserFromApps] Completed', [
            'user_id' => $this->user->id,
            'success' => $success,
            'failed'  => $failed,
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::channel('daily')->error('[RemoveUserFromApps] Job failed permanently', [
            'user_id' => $this->user->id,
            'error'   => $exception->getMessage(),
        ]);
    }
}
