<?php

namespace App\Services;

use App\Connectors\AppConnectorInterface;
use App\Models\Application;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Uygulama senkronizasyon servisi.
 *
 * Kullanıcı yaşam döngüsü işlemlerini tüm uygulamalara yansıtır.
 */
class ApplicationSyncService
{
    /**
     * Kullanıcıyı belirli bir uygulamaya senkronla.
     */
    public function syncUserToApp(User $user, Application $app): array
    {
        $connector = $app->resolveConnector();

        if (!$connector) {
            return ['success' => false, 'error' => 'Connector bulunamadı: ' . $app->slug];
        }

        $result = $connector->syncUser($user);

        // Pivot güncelle
        $this->updatePivotSync($user, $app, $result);

        return $result;
    }

    /**
     * Kullanıcıyı atandığı TÜM uygulamalara senkronla.
     */
    public function syncUserToAllApps(User $user): array
    {
        $results = [];
        $apps = $user->applications()->get();

        foreach ($apps as $app) {
            $results[$app->slug] = $this->syncUserToApp($user, $app);
        }

        return $results;
    }

    /**
     * Kullanıcı güncellendiğinde tüm uygulamalara yansıt.
     */
    public function updateUserInAllApps(User $user): array
    {
        $results = [];
        $apps = $user->applications()->get();

        foreach ($apps as $app) {
            $connector = $app->resolveConnector();
            if (!$connector) {
                $results[$app->slug] = ['success' => false, 'error' => 'Connector yok'];
                continue;
            }

            $result = $connector->updateUser($user);
            $this->updatePivotSync($user, $app, $result);
            $results[$app->slug] = $result;
        }

        return $results;
    }

    /**
     * Kullanıcıyı tüm uygulamalardan sil.
     */
    public function removeUserFromAllApps(User $user): array
    {
        $results = [];
        $apps = $user->applications()->get();

        foreach ($apps as $app) {
            $connector = $app->resolveConnector();
            if (!$connector) {
                $results[$app->slug] = false;
                continue;
            }

            $results[$app->slug] = $connector->removeUser($user);
        }

        return $results;
    }

    /**
     * Kullanıcıyı belirli bir uygulamadan sil (uzak sistem + pivot).
     */
    public function removeUserFromApp(User $user, Application $app): bool
    {
        $connector = $app->resolveConnector();

        if ($connector) {
            $connector->removeUser($user);
        }

        // Pivot'tan çıkar
        $user->applications()->detach($app->id);

        return true;
    }

    /**
     * Bir uygulamanın TÜM kullanıcılarını toplu senkronla.
     */
    public function syncAllUsersForApp(Application $app): array
    {
        $connector = $app->resolveConnector();

        if (!$connector) {
            return ['success' => false, 'error' => 'Connector bulunamadı'];
        }

        $users = $app->users()->get();
        $results = ['total' => $users->count(), 'synced' => 0, 'failed' => 0, 'errors' => []];

        foreach ($users as $user) {
            $result = $connector->syncUser($user);
            $this->updatePivotSync($user, $app, $result);

            if ($result['success']) {
                $results['synced']++;
            } else {
                $results['failed']++;
                $results['errors'][] = ['userId' => $user->id, 'error' => $result['error']];
            }
        }

        Log::channel('daily')->info("[AppSync] Toplu senkron tamamlandı: {$app->slug}", $results);

        return $results;
    }

    /**
     * Yeni kullanıcıyı tüm aktif uygulamalara ata ve senkronla.
     */
    public function assignUserToAllActiveApps(User $user): array
    {
        $apps = Application::where('is_active', true)
            ->whereNotNull('connector_class')
            ->get();

        $results = [];

        foreach ($apps as $app) {
            // Pivot'a ekle (zaten yoksa)
            if (!$app->users()->where('user_id', $user->id)->exists()) {
                $app->users()->attach($user->id, [
                    'granted_by' => auth()->id(),
                    'granted_at' => now(),
                    'sync_status' => 'pending',
                ]);
            }

            // Senkronla
            $results[$app->slug] = $this->syncUserToApp($user, $app);
        }

        Log::channel('daily')->info('[AppSync] Kullanıcı tüm app\'lere atandı', [
            'userId' => $user->id,
            'appCount' => count($results),
        ]);

        return $results;
    }

    /**
     * Harici sistemden kullanıcı verisini getir.
     */
    public function getRemoteUser(User $user, Application $app): ?array
    {
        $connector = $app->resolveConnector();
        return $connector?->getUser($user);
    }

    /**
     * Pivot tablosundaki senkron durumunu güncelle.
     */
    private function updatePivotSync(User $user, Application $app, array $result): void
    {
        $pivotData = [
            'sync_status' => $result['success'] ? 'synced' : 'failed',
            'synced_at' => $result['success'] ? now() : null,
            'sync_error' => $result['error'] ?? null,
        ];

        $user->applications()->updateExistingPivot($app->id, $pivotData);
    }
}
