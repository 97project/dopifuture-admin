<?php

namespace App\Connectors;

use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Mission Way — Full API Connector
 *
 * Player Compositions:
 *   POST   /v1/player-compositions              → Oyuncu oluştur
 *   GET    /v1/player-compositions/by-user/:id   → Oyuncu getir
 *   DELETE /v1/player-compositions/by-user/:id   → Oyuncu sil
 *
 * Simulations, Sessions, Players, Progress, Profiles:
 *   GET /v1/simulations, /v1/simulation-sessions, /v1/players,
 *       /v1/player-profiles, /v1/player-progresses, /v1/session-players
 *
 * Health:
 *   GET /health/simple
 */
class MissionWayConnector extends BaseConnector implements AppConnectorInterface
{
    protected string $logPrefix = 'MissionWay';

    public function __construct()
    {
        parent::__construct('mission_way');
    }

    /* ─── Interface: syncUser ──────────────────────────── */

    /**
     * POST /v1/player-compositions
     */
    public function syncUser(User $user): array
    {
        $name = is_array($user->name) ? ($user->name[app()->getLocale()] ?? reset($user->name) ?: 'User') : ($user->name ?? 'User');
        $surname = is_array($user->surname) ? ($user->surname[app()->getLocale()] ?? reset($user->surname) ?: '') : ($user->surname ?? '');

        $payload = [
            'userId' => $user->id,
            'username' => $user->id . '-' . $this->slugify($name),
            'email' => $user->email,
            'name' => $name,
            'surname' => $surname,
        ];

        try {
            $response = $this->apiPost('/v1/player-compositions', $payload);

            if ($response->status() === 400 && $this->isDuplicateError($response)) {
                Log::channel('daily')->info('[MissionWay] Kullanıcı zaten mevcut', [
                    'userId' => $user->id,
                    'response' => $response->json(),
                ]);
                return ['success' => true, 'response' => $response->json(), 'error' => null];
            }

            if ($response->successful()) {
                Log::channel('daily')->info('[MissionWay] Kullanıcı senkronlandı', [
                    'userId' => $user->id,
                ]);
                return ['success' => true, 'response' => $response->json(), 'error' => null];
            }

            Log::channel('daily')->error('[MissionWay] Senkron hatası', [
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
            Log::channel('daily')->error('[MissionWay] Bağlantı hatası', [
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
     * DELETE /v1/player-compositions/by-user/{userId}
     */
    public function removeUser(User $user): bool
    {
        try {
            $response = $this->apiDelete("/v1/player-compositions/by-user/{$user->id}");

            if ($response->status() === 204 || $response->status() === 404) {
                Log::channel('daily')->info('[MissionWay] Kullanıcı silindi', ['userId' => $user->id]);
                return true;
            }

            Log::channel('daily')->error('[MissionWay] Silme hatası', [
                'userId' => $user->id,
                'status' => $response->status(),
            ]);
            return false;
        } catch (\Throwable $e) {
            Log::channel('daily')->error('[MissionWay] Silme bağlantı hatası', [
                'userId' => $user->id,
                'message' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /* ─── Interface: getUser ────────────────────────────── */

    /**
     * GET /v1/player-compositions/by-user/{userId}
     */
    public function getUser(User $user): ?array
    {
        try {
            return $this->apiGet("/v1/player-compositions/by-user/{$user->id}") ?: null;
        } catch (\Throwable $e) {
            Log::channel('daily')->error('[MissionWay] GET hatası', [
                'userId' => $user->id,
                'message' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /* ─── Interface: getUserReport ──────────────────────── */

    /**
     * Kullanıcının MissionWay detaylı raporunu getir.
     * Composition + player + profile + sessions + progress birleşimi.
     */
    public function getUserReport(User $user): ?array
    {
        try {
            $composition = $this->getUser($user);

            if (!$composition) {
                return [
                    'success' => false,
                    'data' => [],
                    'error' => 'MissionWay\'de kullanıcı verisi bulunamadı',
                ];
            }

            // Composition'dan player bilgilerini çek
            $playerId = $composition['player']['id'] ?? $composition['playerId'] ?? null;

            $profile = null;
            $sessions = [];
            $progress = [];

            if ($playerId) {
                $profile = $this->getPlayerProfile($playerId);
                $sessions = $this->getPlayerSessions($playerId);
                $progress = $this->getPlayerProgressList(['filter' => "playerId||eq||{$playerId}"]);
            }

            return [
                'success' => true,
                'data' => [
                    'composition' => $composition,
                    'player_id' => $playerId,
                    'profile' => $profile,
                    'sessions' => $sessions,
                    'session_count' => count($sessions),
                    'progress' => $progress,
                ],
                'error' => null,
            ];
        } catch (\Throwable $e) {
            Log::channel('daily')->error('[MissionWay] getUserReport hatası', [
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
        return $this->apiGet('/v1/simulations', $params);
    }

    public function getSimulation(int $id, array $params = []): ?array
    {
        return $this->apiGet("/v1/simulations/{$id}", $params);
    }

    /* ─── Simulation Sessions ──────────────────────────── */

    public function getSimulationSessions(array $params = []): ?array
    {
        return $this->apiGet('/v1/simulation-sessions', $params);
    }

    public function getSimulationSession(int $id, array $params = []): ?array
    {
        return $this->apiGet("/v1/simulation-sessions/{$id}", $params);
    }

    /* ─── Session Players ──────────────────────────────── */

    public function getSessionPlayers(int $sessionId): ?array
    {
        return $this->apiGet("/v1/session-players/by-session/{$sessionId}");
    }

    public function getPlayerSessions(int $playerId): ?array
    {
        $result = $this->apiGet("/v1/session-players/by-player/{$playerId}");
        return is_array($result) ? $result : [];
    }

    /* ─── Players ──────────────────────────────────────── */

    public function getPlayers(array $params = []): ?array
    {
        return $this->apiGet('/v1/players', $params);
    }

    public function getPlayer(int $id, array $params = []): ?array
    {
        return $this->apiGet("/v1/players/{$id}", $params);
    }

    /* ─── Player Profiles ──────────────────────────────── */

    public function getPlayerProfile(int $playerId): ?array
    {
        return $this->apiGet("/v1/player-profiles/by-player/{$playerId}");
    }

    /* ─── Player Progress ──────────────────────────────── */

    public function getPlayerProgressList(array $params = []): ?array
    {
        $result = $this->apiGet('/v1/player-progresses', $params);
        // API bazen data[] wrapper kullanır
        if (isset($result['data'])) {
            return $result['data'];
        }
        return is_array($result) ? $result : [];
    }

    /* ─── Helpers ──────────────────────────────────────── */

    private function slugify(mixed $name): string
    {
        if (is_array($name)) {
            $name = reset($name) ?: 'user';
        }
        $slug = mb_strtolower(trim((string) $name));
        $slug = preg_replace('/\s+/', '', $slug);
        $slug = preg_replace('/[^a-z0-9]/', '', $slug);
        return $slug ?: 'user';
    }
}
