<?php

namespace App\Connectors;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Way Startup — Startup Member API
 *
 * POST /v1/startup/members → Üye oluştur
 */
class WayStartupConnector implements AppConnectorInterface
{
    private string $baseUrl;
    private string $apiKey;
    private int $timeout;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('connectors.way_startup.base_url'), '/');
        $this->apiKey = config('connectors.way_startup.api_key');
        $this->timeout = config('connectors.way_startup.timeout', 10);
    }

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
            $response = Http::timeout($this->timeout)
                ->withHeaders(['x-api-key' => $this->apiKey])
                ->post("{$this->baseUrl}/v1/startup/members", $payload);

            // 400 duplicate → başarılı kabul et
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

    /**
     * Güncelleme — henüz PUT endpoint yok
     */
    public function updateUser(User $user): array
    {
        // PUT endpoint geldiğinde burada implemente edilecek
        return $this->syncUser($user);
    }

    /**
     * Silme — henüz DELETE endpoint yok
     */
    public function removeUser(User $user): bool
    {
        Log::channel('daily')->info('[WayStartup] DELETE endpoint henüz mevcut değil', [
            'userId' => $user->id,
        ]);
        return true;
    }

    /**
     * Veri çekme — henüz GET endpoint yok
     */
    public function getUser(User $user): ?array
    {
        Log::channel('daily')->info('[WayStartup] GET endpoint henüz mevcut değil', [
            'userId' => $user->id,
        ]);
        return null;
    }

    private function isDuplicateError($response): bool
    {
        $body = $response->json('message', '');
        return str_contains(strtolower($body), 'already exists');
    }
}
