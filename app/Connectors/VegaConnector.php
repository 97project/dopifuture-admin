<?php

namespace App\Connectors;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Vega Main App Connector
 *
 * Tek API üzerinden 3 alt özellik sunar:
 *   - Role Galaxy
 *   - Way AI Coach
 *   - Study Space
 *
 * Admin Endpoints (API Key ile):
 *   GET    /api/v1/users          → Kullanıcı listesi
 *   GET    /api/v1/users/{id}     → Kullanıcı detay
 *   POST   /api/v1/register       → Kullanıcı oluştur
 *   PUT    /api/v1/profile         → Profil güncelle (Bearer)
 *   POST   /api/v1/user/ban       → Kullanıcı engelle
 *   POST   /api/v1/user/unban     → Engel kaldır
 */
class VegaConnector implements AppConnectorInterface
{
    private string $baseUrl;
    private string $apiKey;
    private int $timeout;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('connectors.vega.base_url'), '/');
        $this->apiKey = config('connectors.vega.api_key') ?? '';
        $this->timeout = config('connectors.vega.timeout', 15);
    }

    /* ─── Interface Methods ──────────────────────────── */

    /**
     * Kullanıcıyı Vega'ya senkronla.
     * 1. Email ile ara → varsa success dön
     * 2. Yoksa register endpoint'i ile oluştur
     */
    public function syncUser(User $user): array
    {
        try {
            // 1) Email ile kullanıcıyı ara
            $existing = $this->findByEmail($user->email);
            if ($existing) {
                Log::channel('daily')->info('[Vega] Kullanıcı zaten mevcut', [
                    'userId' => $user->id,
                    'vegaId' => $existing['id'] ?? null,
                ]);
                return [
                    'success' => true,
                    'response' => $existing,
                    'error' => null,
                ];
            }

            // 2) Yoksa register ile oluştur
            $password = 'Vg' . bin2hex(random_bytes(4)) . '!9'; // büyük/küçük/rakam/özel
            $payload = [
                'name' => $user->name ?? 'User',
                'surname' => $user->surname ?? '',
                'email' => $user->email,
                'password' => $password,
                'password_confirmation' => $password,
            ];

            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'X-API-KEY' => $this->apiKey,
                    'Accept' => 'application/json',
                ])
                ->post("{$this->baseUrl}/api/v1/register", $payload);

            if ($response->successful()) {
                Log::channel('daily')->info('[Vega] Kullanıcı oluşturuldu', [
                    'userId' => $user->id,
                    'email' => $user->email,
                ]);
                return [
                    'success' => true,
                    'response' => $response->json(),
                    'error' => null,
                ];
            }

            // 422 duplicate email → zaten varsa başarılı say
            if ($response->status() === 422) {
                $errors = $response->json('errors', []);
                if (isset($errors['email'])) {
                    Log::channel('daily')->info('[Vega] Kullanıcı zaten kayıtlı (422)', [
                        'userId' => $user->id,
                    ]);
                    return [
                        'success' => true,
                        'response' => $response->json(),
                        'error' => null,
                    ];
                }
            }

            Log::channel('daily')->error('[Vega] Sync hatası', [
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
            Log::channel('daily')->error('[Vega] Bağlantı hatası', [
                'userId' => $user->id,
                'message' => $e->getMessage(),
            ]);

            return ['success' => false, 'response' => null, 'error' => $e->getMessage()];
        }
    }

    /**
     * Kullanıcı bilgilerini güncelle — şimdilik syncUser ile aynı.
     * Vega API'de admin PUT endpoint açılırsa ayrıştırılır.
     */
    public function updateUser(User $user): array
    {
        return $this->syncUser($user);
    }

    /**
     * Kullanıcıyı Vega'da ban'la (soft-delete).
     */
    public function removeUser(User $user): bool
    {
        try {
            $existing = $this->findByEmail($user->email);
            if (!$existing) {
                Log::channel('daily')->info('[Vega] Silinecek kullanıcı bulunamadı', [
                    'userId' => $user->id,
                ]);
                return true; // zaten yoksa sorun yok
            }

            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'X-API-KEY' => $this->apiKey,
                    'Accept' => 'application/json',
                ])
                ->post("{$this->baseUrl}/api/v1/user/ban", [
                    'user_id' => $existing['id'],
                ]);

            if ($response->successful()) {
                Log::channel('daily')->info('[Vega] Kullanıcı banlandı', [
                    'userId' => $user->id,
                    'vegaId' => $existing['id'],
                ]);
                return true;
            }

            Log::channel('daily')->error('[Vega] Ban hatası', [
                'userId' => $user->id,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return false;
        } catch (\Throwable $e) {
            Log::channel('daily')->error('[Vega] Ban bağlantı hatası', [
                'userId' => $user->id,
                'message' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Vega'dan kullanıcı bilgisi getir (email ile ara).
     */
    public function getUser(User $user): ?array
    {
        try {
            return $this->findByEmail($user->email);
        } catch (\Throwable $e) {
            Log::channel('daily')->error('[Vega] GET hatası', [
                'userId' => $user->id,
                'message' => $e->getMessage(),
            ]);
            return null;
        }
    }

    public static function isReady(): bool
    {
        return !empty(config('connectors.vega.api_key'));
    }

    /* ─── Internal Helpers ───────────────────────────── */

    /**
     * Vega API'den tüm kullanıcıları çek ve email ile eşleştir.
     */
    private function findByEmail(string $email): ?array
    {
        $response = Http::timeout($this->timeout)
            ->withHeaders([
                'X-API-KEY' => $this->apiKey,
                'Accept' => 'application/json',
            ])
            ->get("{$this->baseUrl}/api/v1/users");

        if (!$response->successful()) {
            return null;
        }

        $data = $response->json('data', []);
        $users = is_array($data) ? $data : [];

        foreach ($users as $vegaUser) {
            if (isset($vegaUser['email']) && mb_strtolower($vegaUser['email']) === mb_strtolower($email)) {
                return $vegaUser;
            }
        }

        return null;
    }
}
