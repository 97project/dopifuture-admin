<?php

namespace App\Services;

use App\Connectors\VegaConnector;
use App\Models\Application;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Cross-App Kullanıcı Senkronizasyon Servisi.
 *
 * 1) reconcileUser()       → Tek user'ı tüm app'lerde kontrol et, eksik olanı oluştur
 * 2) reconcileAllUsers()   → Paneldeki tüm öğrencileri toplu reconcile
 * 3) discoverRemoteUsers() → App'teki kullanıcıları panele çek (reverse discovery)
 */
class CrossAppSyncService
{
    private ApplicationSyncService $appSync;
    private ConnectorSyncService $dataSyncService;

    public function __construct(ApplicationSyncService $appSync, ConnectorSyncService $dataSyncService)
    {
        $this->appSync = $appSync;
        $this->dataSyncService = $dataSyncService;
    }

    /* ═══════════════════════════════════════════════════════
     *  1) reconcileUser — Tek kullanıcıyı tüm app'lerde kontrol + tamamla
     * ═══════════════════════════════════════════════════════ */

    /**
     * Paneldeki bir kullanıcının tüm aktif uygulamalarda var olup olmadığını kontrol et.
     * Yoksa → syncUser ile oluştur.
     * Varsa → pivot güncelle.
     *
     * @return array{app_slug => ['status' => 'exists'|'created'|'failed', ...]}
     */
    public function reconcileUser(User $user): array
    {
        $apps = Application::active()
            ->whereNotNull('connector_class')
            ->ordered()
            ->get();

        $results = [];

        foreach ($apps as $app) {
            $connector = $app->resolveConnector();
            if (!$connector) {
                $results[$app->slug] = ['status' => 'no_connector'];
                continue;
            }

            try {
                // 1. Uzak sistemde var mı kontrol et
                $remoteUser = $connector->getUser($user);

                if ($remoteUser) {
                    // Zaten var — pivot güncelle
                    $this->ensurePivot($user, $app, 'synced');
                    $results[$app->slug] = [
                        'status'      => 'exists',
                        'external_id' => $remoteUser['id'] ?? $remoteUser['player']['id'] ?? null,
                    ];
                } else {
                    // Yok — oluştur
                    $syncResult = $this->appSync->syncUserToApp($user, $app);
                    $results[$app->slug] = [
                        'status' => $syncResult['success'] ? 'created' : 'failed',
                        'error'  => $syncResult['error'] ?? null,
                    ];
                }
            } catch (\Throwable $e) {
                $results[$app->slug] = [
                    'status' => 'error',
                    'error'  => $e->getMessage(),
                ];
                Log::channel('daily')->error('[CrossAppSync] reconcileUser hatası', [
                    'user_id' => $user->id,
                    'app'     => $app->slug,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        return $results;
    }

    /* ═══════════════════════════════════════════════════════
     *  2) reconcileAllUsers — Toplu tarama
     * ═══════════════════════════════════════════════════════ */

    /**
     * Paneldeki tüm öğrencileri tüm app'lerde kontrol et ve eksikleri tamamla.
     *
     * @param  callable|null $onProgress  fn(User $user, array $result, int $current, int $total)
     * @return array{total, exists, created, failed, errors}
     */
    public function reconcileAllUsers(?callable $onProgress = null): array
    {
        // Sadece öğrenci rolündeki kullanıcıları al (Spatie: 'name' kolonu)
        $users = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['student', 'ogrenci']);
        })->get();

        // Eğer student role yoksa tüm non-admin kullanıcıları al
        if ($users->isEmpty()) {
            $users = User::whereDoesntHave('roles', function ($q) {
                $q->where('name', 'admin');
            })->get();
        }

        $summary = [
            'total'   => $users->count(),
            'exists'  => 0,
            'created' => 0,
            'failed'  => 0,
            'errors'  => [],
        ];

        foreach ($users as $i => $user) {
            $result = $this->reconcileUser($user);

            foreach ($result as $appSlug => $r) {
                match ($r['status']) {
                    'exists' => $summary['exists']++,
                    'created' => $summary['created']++,
                    default => $summary['failed']++,
                };

                if (in_array($r['status'], ['failed', 'error'])) {
                    $summary['errors'][] = [
                        'user_id' => $user->id,
                        'app'     => $appSlug,
                        'error'   => $r['error'] ?? $r['status'],
                    ];
                }
            }

            if ($onProgress) {
                $onProgress($user, $result, $i + 1, $summary['total']);
            }
        }

        Log::channel('daily')->info('[CrossAppSync] Toplu reconcile tamamlandı', $summary);

        return $summary;
    }

    /* ═══════════════════════════════════════════════════════
     *  3) discoverRemoteUsers — Reverse pull
     * ═══════════════════════════════════════════════════════ */

    /**
     * Uzak app'teki kullanıcıları panele çek.
     * Email eşleştirmesi ile mevcut panel kullanıcılarına bağla.
     * Eşleşmeyen → orphan olarak logla.
     *
     * @return array{matched, orphaned, errors}
     */
    public function discoverRemoteUsers(Application $app): array
    {
        $connector = $app->resolveConnector();
        if (!$connector) {
            return ['matched' => 0, 'orphaned' => 0, 'errors' => ['No connector']];
        }

        $results = ['matched' => 0, 'orphaned' => 0, 'orphan_list' => [], 'errors' => []];

        try {
            $remoteUsers = $this->fetchAllRemoteUsers($connector, $app);

            foreach ($remoteUsers as $remote) {
                $email = $remote['email'] ?? null;
                if (!$email) continue;

                $localUser = User::where('email', $email)->first();

                if ($localUser) {
                    // Eşleşti — pivot oluştur + veri sync
                    $this->ensurePivot($localUser, $app, 'synced');

                    // Veriyi de çek
                    $this->dataSyncService->syncUserData($localUser, $app);

                    $results['matched']++;
                } else {
                    // Orphan — panelde yok
                    $results['orphaned']++;
                    $results['orphan_list'][] = [
                        'email' => $email,
                        'name'  => ($remote['name'] ?? '') . ' ' . ($remote['surname'] ?? ''),
                        'id'    => $remote['id'] ?? null,
                    ];
                }
            }
        } catch (\Throwable $e) {
            $results['errors'][] = $e->getMessage();
            Log::channel('daily')->error('[CrossAppSync] discoverRemoteUsers hatası', [
                'app'     => $app->slug,
                'message' => $e->getMessage(),
            ]);
        }

        Log::channel('daily')->info("[CrossAppSync] Discovery: {$app->slug}", $results);

        return $results;
    }

    /* ═══════════════════════════════════════════════════════
     *  Private Helpers
     * ═══════════════════════════════════════════════════════ */

    /**
     * Connector tipine göre tüm uzak kullanıcıları çek.
     */
    private function fetchAllRemoteUsers($connector, Application $app): array
    {
        $users = [];

        if ($connector instanceof VegaConnector) {
            // Vega: paginated /api/v1/users
            $page = 1;
            do {
                $r = $connector->listRemoteUsers($page, 50);
                $data = $r['data'] ?? [];
                $pageUsers = $data['users'] ?? [];
                $users = array_merge($users, $pageUsers);
                $lastPage = $data['last_page'] ?? 1;
                $page++;
            } while ($page <= $lastPage);
        } elseif ($app->slug === 'mission-way' || str_contains(class_basename($connector), 'MissionWay')) {
            // MissionWay: paginated /v1/players
            $page = 1;
            do {
                $r = $connector->getPlayers(['page' => $page, 'limit' => 50]);
                $pageUsers = $r['data'] ?? $r ?? [];
                if (is_array($pageUsers)) {
                    $users = array_merge($users, $pageUsers);
                }
                $total = $r['total'] ?? 0;
                $pageCount = $r['pageCount'] ?? 1;
                $page++;
            } while ($page <= $pageCount);

            // Players'ın email'i olmayabilir, compositions'dan email çekelim
            foreach ($users as &$u) {
                if (empty($u['email']) && !empty($u['userId'])) {
                    // userId üzerinden panel user'ı bul
                    $localUser = User::find($u['userId']);
                    if ($localUser) {
                        $u['email'] = $localUser->email;
                    }
                }
            }
            unset($u);
        } else {
            // WayStartup: member list endpoint yok
            // Panel kullanıcıları üzerinden getUser() ile kontrol
            // Bu durumda discoverRemoteUsers yerine reconcileAllUsers kullan
            Log::channel('daily')->info("[CrossAppSync] {$app->slug}: Member list endpoint yok, reconcile kullanın");
        }

        return $users;
    }

    /**
     * Kullanıcı-uygulama pivot ilişkisini garanti et.
     */
    private function ensurePivot(User $user, Application $app, string $status = 'synced'): void
    {
        if (!$app->users()->where('user_id', $user->id)->exists()) {
            $app->users()->attach($user->id, [
                'granted_at'  => now(),
                'sync_status' => $status,
                'synced_at'   => now(),
            ]);
        } else {
            $app->users()->updateExistingPivot($user->id, [
                'sync_status' => $status,
                'synced_at'   => now(),
            ]);
        }
    }
}
