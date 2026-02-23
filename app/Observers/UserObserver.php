<?php

namespace App\Observers;

use App\Models\User;
use App\Services\ApplicationSyncService;
use Illuminate\Support\Facades\Log;

/**
 * User Observer — kullanıcı güncellendiğinde veya silindiğinde
 * tüm atanmış uygulamalara otomatik yansıtır.
 */
class UserObserver
{
    public function __construct(
        private ApplicationSyncService $syncService
    ) {
    }

    /**
     * Kullanıcı güncellendiğinde tüm app'lere yansıt.
     */
    public function updated(User $user): void
    {
        // Sadece senkronla ilgili alanlar değiştiyse tetikle
        $syncFields = ['name', 'surname', 'email', 'avatar_path', 'avatar_disk'];
        $changedFields = array_keys($user->getChanges());
        $relevantChanges = array_intersect($changedFields, $syncFields);

        if (empty($relevantChanges)) {
            return;
        }

        try {
            $this->syncService->updateUserInAllApps($user);
        } catch (\Throwable $e) {
            Log::channel('daily')->error('[UserObserver] Güncelleme senkron hatası', [
                'userId' => $user->id,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Kullanıcı silindiğinde (soft-delete) tüm app'lerden sil.
     */
    public function deleted(User $user): void
    {
        try {
            $this->syncService->removeUserFromAllApps($user);
        } catch (\Throwable $e) {
            Log::channel('daily')->error('[UserObserver] Silme senkron hatası', [
                'userId' => $user->id,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
