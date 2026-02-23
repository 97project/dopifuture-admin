<?php

namespace App\Connectors;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Mission Way — Player Composition API
 *
 * POST   /v1/player-compositions              → Oyuncu oluştur
 * GET    /v1/player-compositions/by-user/:id   → Oyuncu getir
 * DELETE /v1/player-compositions/by-user/:id   → Oyuncu sil
 */
class MissionWayConnector implements AppConnectorInterface
{
    private string $baseUrl;
    private string $apiKey;
    private int $timeout;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('connectors.mission_way.base_url'), '/');
        $this->apiKey = config('connectors.mission_way.api_key');
        $this->timeout = config('connectors.mission_way.timeout', 10);
    }

    /**
     * POST /v1/player-compositions
     */
    public function syncUser(User $user): array
    {
        $payload = [
            'userId' => $user->id,
            'username' => $user->id . '-' . $this->slugify($user->name),
            'email' => $user->email,
            'name' => $user->name,
            'surname' => $user->surname ?? '',
        ];

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders(['x-api-key' => $this->apiKey])
                ->post("{$this->baseUrl}/v1/player-compositions", $payload);

            // 400 duplicate → başarılı kabul et (zaten var)
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

    /**
     * Güncelleme — şimdilik syncUser ile aynı (PUT endpoint gelince ayrılır)
     */
    public function updateUser(User $user): array
    {
        return $this->syncUser($user);
    }

    /**
     * DELETE /v1/player-compositions/by-user/{userId}
     */
    public function removeUser(User $user): bool
    {
        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders(['x-api-key' => $this->apiKey])
                ->delete("{$this->baseUrl}/v1/player-compositions/by-user/{$user->id}");

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

    /**
     * GET /v1/player-compositions/by-user/{userId}
     */
    public function getUser(User $user): ?array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders(['x-api-key' => $this->apiKey])
                ->get("{$this->baseUrl}/v1/player-compositions/by-user/{$user->id}");

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Throwable $e) {
            Log::channel('daily')->error('[MissionWay] GET hatası', [
                'userId' => $user->id,
                'message' => $e->getMessage(),
            ]);
            return null;
        }
    }

    private function isDuplicateError($response): bool
    {
        $body = $response->json('message', '');
        return str_contains(strtolower($body), 'already exists');
    }

    private function slugify(string $name): string
    {
        $slug = mb_strtolower(trim($name));
        $slug = preg_replace('/\s+/', '', $slug);
        $slug = preg_replace('/[^a-z0-9]/', '', $slug);
        return $slug ?: 'user';
    }

    public static function isReady(): bool
    {
        return true;
    }
}
