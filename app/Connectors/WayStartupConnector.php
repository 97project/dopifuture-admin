<?php

namespace App\Connectors;

use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Way Startup — Full API Connector
 *
 * Members:
 *   POST   /v1/startup/members                → Üye oluştur
 *   GET    /v1/startup/members/by-user/:id    → Üye getir
 *   PATCH  /v1/startup/members/:id            → Üye güncelle
 *   DELETE /v1/startup/members/:id            → Üye sil
 *
 * Simulations, Steps, Progress:
 *   GET /v1/startup/simulations, /v1/startup/steps,
 *       /v1/startup/userprogress, /v1/startup/userstepprogress
 *
 * Health:
 *   GET /health/simple
 */
class WayStartupConnector extends BaseConnector implements AppConnectorInterface
{
    protected string $logPrefix = 'WayStartup';

    public function __construct()
    {
        parent::__construct('way_startup');
    }

    /* ─── Interface: syncUser ──────────────────────────── */

    /**
     * POST /v1/startup/members
     */
    public function syncUser(User $user): array
    {
        $payload = [
            'userId' => (string) $user->id,
            'name' => $user->full_name,
            'email' => $user->email,
            'avatarUrl' => $user->avatar_url ?? '',
            'points' => 0,
        ];

        try {
            $response = $this->apiPost('/v1/startup/members', $payload);

            if ($response->status() === 400 && $this->isDuplicateError($response)) {
                Log::channel('daily')->info('[WayStartup] Üye zaten mevcut', [
                    'userId' => $user->id,
                    'response' => $response->json(),
                ]);
                return ['success' => true, 'response' => $response->json(), 'error' => null];
            }

            if ($response->successful()) {
                Log::channel('daily')->info('[WayStartup] Üye senkronlandı', [
                    'userId' => $user->id,
                ]);
                return ['success' => true, 'response' => $response->json(), 'error' => null];
            }

            Log::channel('daily')->error('[WayStartup] Senkron hatası', [
                'userId' => $user->id,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'success' => false,
                'response' => $response->json(),
                'error' => "HTTP {$response->status()}: {$response->body()}",
            ];
        } catch (\Throwable $e) {
            Log::channel('daily')->error('[WayStartup] Bağlantı hatası', [
                'userId' => $user->id,
                'message' => $e->getMessage(),
            ]);

            return ['success' => false, 'response' => null, 'error' => $e->getMessage()];
        }
    }

    /* ─── Interface: updateUser ─────────────────────────── */

    public function updateUser(User $user): array
    {
        return $this->syncUser($user);
    }

    /* ─── Interface: removeUser ─────────────────────────── */

    /**
     * DELETE /v1/startup/members/{id}
     * Önce by-user'dan member id bulunur, sonra silinir.
     */
    public function removeUser(User $user): bool
    {
        try {
            // Önce member bilgisini al
            $member = $this->getUser($user);
            $memberId = $member['id'] ?? null;

            if (!$memberId) {
                Log::channel('daily')->info('[WayStartup] Silinecek üye bulunamadı', ['userId' => $user->id]);
                return true; // zaten yok
            }

            $response = $this->apiDelete("/v1/startup/members/{$memberId}");

            if ($response->status() === 200 || $response->status() === 204 || $response->status() === 404) {
                Log::channel('daily')->info('[WayStartup] Üye silindi', [
                    'userId' => $user->id,
                    'memberId' => $memberId,
                ]);
                return true;
            }

            Log::channel('daily')->error('[WayStartup] Silme hatası', [
                'userId' => $user->id,
                'status' => $response->status(),
            ]);
            return false;
        } catch (\Throwable $e) {
            Log::channel('daily')->error('[WayStartup] Silme bağlantı hatası', [
                'userId' => $user->id,
                'message' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /* ─── Interface: getUser ────────────────────────────── */

    /**
     * GET /v1/startup/members/by-user/{userId}
     */
    public function getUser(User $user): ?array
    {
        try {
            return $this->apiGet("/v1/startup/members/by-user/{$user->id}") ?: null;
        } catch (\Throwable $e) {
            Log::channel('daily')->error('[WayStartup] GET üye hatası', [
                'userId' => $user->id,
                'message' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /* ─── Interface: getUserReport ──────────────────────── */

    /**
     * Kullanıcının WayStartup detaylı raporunu getir.
     * Member + simülasyonlar + ilerleme + adım ilerlemesi.
     */
    public function getUserReport(User $user): ?array
    {
        try {
            $member = $this->getUser($user);

            if (!$member) {
                return [
                    'success' => false,
                    'data' => [],
                    'error' => 'WayStartup\'ta kullanıcı bulunamadı',
                ];
            }

            $memberId = $member['id'] ?? null;
            $progress = [];
            $stepProgress = [];

            if ($memberId) {
                $progress = $this->getUserProgress($memberId);
                $stepProgress = $this->getUserStepProgress($memberId);
            }

            return [
                'success' => true,
                'data' => [
                    'member' => $member,
                    'member_id' => $memberId,
                    'progress' => $progress,
                    'step_progress' => $stepProgress,
                    'progress_count' => count($progress),
                    'completed_steps' => collect($stepProgress)->where('status', 'completed')->count(),
                    'total_steps' => count($stepProgress),
                ],
                'error' => null,
            ];
        } catch (\Throwable $e) {
            Log::channel('daily')->error('[WayStartup] getUserReport hatası', [
                'userId' => $user->id,
                'message' => $e->getMessage(),
            ]);
            return [
                'success' => false,
                'data' => [],
                'error' => $e->getMessage(),
            ];
        }
    }

    /* ─── Simulations ──────────────────────────────────── */

    public function getSimulations(array $params = []): ?array
    {
        return $this->apiGet('/v1/startup/simulations', $params);
    }

    public function getSimulationsWithProgress(): ?array
    {
        return $this->apiGet('/v1/startup/simulations/with-progress');
    }

    public function getSimulation(int $id): ?array
    {
        return $this->apiGet("/v1/startup/simulations/{$id}");
    }

    /* ─── Steps ────────────────────────────────────────── */

    public function getSteps(int $simulationId): ?array
    {
        $result = $this->apiGet("/v1/startup/steps/simulation/{$simulationId}");
        return is_array($result) ? $result : [];
    }

    /* ─── User Progress ────────────────────────────────── */

    public function getUserProgress(int $memberId): ?array
    {
        $result = $this->apiGet("/v1/startup/userprogress/member/{$memberId}");
        return is_array($result) ? $result : [];
    }

    public function getUserProgressForSimulation(int $memberId, int $simulationId): ?array
    {
        return $this->apiGet("/v1/startup/userprogress/member/{$memberId}/simulation/{$simulationId}");
    }

    /* ─── User Step Progress ───────────────────────────── */

    public function getUserStepProgress(int $memberId): ?array
    {
        $result = $this->apiGet("/v1/startup/userstepprogress/member/{$memberId}");
        return is_array($result) ? $result : [];
    }

    public function getUserStepProgressForStep(int $memberId, int $stepId): ?array
    {
        return $this->apiGet("/v1/startup/userstepprogress/member/{$memberId}/step/{$stepId}");
    }
}
