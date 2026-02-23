<?php

namespace App\Connectors;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Vega Main App Connector
 *
 * Tek API üzerinden 3 alt özellik sunar:
 *   - Role Galaxy   (Senaryo Simülasyonu)
 *   - Way AI Coach  (Yapay Zeka Eğitmen)
 *   - Study Space   (Soru-Cevap & Sohbet)
 *
 * Admin Endpoints (API Key ile):
 *   GET    /api/v1/health              → Servis sağlığı
 *   GET    /api/v1/users               → Kullanıcı listesi
 *   GET    /api/v1/users/{id}          → Kullanıcı detay (roller, profil, premium)
 *   PUT    /api/v1/users/{id}          → Kullanıcı güncelle
 *   DELETE /api/v1/users/{id}          → Kullanıcı sil
 *   POST   /api/v1/register            → Kullanıcı oluştur
 *   POST   /api/v1/user/ban            → Kullanıcı engelle
 *   POST   /api/v1/user/unban          → Engel kaldır
 *   GET    /api/v1/sessions/lecturer/{id}    → Way AI Coach oturum detayı
 *   GET    /api/v1/sessions/simulator/{id}   → Role Galaxy oturum detayı
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

    /* ═══════════════════════════════════════════════════
     *  Interface Methods (AppConnectorInterface)
     * ═══════════════════════════════════════════════════ */

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
                return ['success' => true, 'response' => $existing, 'error' => null];
            }

            // 2) Yoksa register ile oluştur
            $password = 'Vg' . bin2hex(random_bytes(4)) . '!9';
            $payload = [
                'name' => $user->name ?? 'User',
                'surname' => $user->surname ?? '',
                'email' => $user->email,
                'password' => $password,
                'password_confirmation' => $password,
            ];

            $response = $this->request('POST', '/api/v1/register', $payload);

            if ($response->successful()) {
                Log::channel('daily')->info('[Vega] Kullanıcı oluşturuldu', [
                    'userId' => $user->id,
                    'email' => $user->email,
                ]);
                return ['success' => true, 'response' => $response->json(), 'error' => null];
            }

            // 422 duplicate email → zaten varsa başarılı say
            if ($response->status() === 422) {
                $errors = $response->json('errors', []);
                if (isset($errors['email'])) {
                    Log::channel('daily')->info('[Vega] Kullanıcı zaten kayıtlı (422)', [
                        'userId' => $user->id,
                    ]);
                    return ['success' => true, 'response' => $response->json(), 'error' => null];
                }
            }

            Log::channel('daily')->error('[Vega] syncUser hatası', [
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
            Log::channel('daily')->error('[Vega] syncUser bağlantı hatası', [
                'userId' => $user->id,
                'message' => $e->getMessage(),
            ]);
            return ['success' => false, 'response' => null, 'error' => $e->getMessage()];
        }
    }

    /**
     * Kullanıcı bilgilerini Vega'da güncelle.
     * PUT /api/v1/users/{vegaId}
     */
    public function updateUser(User $user): array
    {
        try {
            $existing = $this->findByEmail($user->email);
            if (!$existing) {
                // Kullanıcı yoksa sync ile oluştur
                return $this->syncUser($user);
            }

            $vegaId = $existing['id'];
            $payload = [
                'name' => $user->name ?? $existing['name'],
                'surname' => $user->surname ?? $existing['surname'] ?? '',
            ];

            $response = $this->request('PUT', "/api/v1/users/{$vegaId}", $payload);

            if ($response->successful()) {
                Log::channel('daily')->info('[Vega] Kullanıcı güncellendi', [
                    'userId' => $user->id,
                    'vegaId' => $vegaId,
                ]);
                return ['success' => true, 'response' => $response->json(), 'error' => null];
            }

            Log::channel('daily')->error('[Vega] updateUser hatası', [
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
            Log::channel('daily')->error('[Vega] updateUser bağlantı hatası', [
                'userId' => $user->id,
                'message' => $e->getMessage(),
            ]);
            return ['success' => false, 'response' => null, 'error' => $e->getMessage()];
        }
    }

    /**
     * Kullanıcıyı Vega'da ban'la (soft-delete).
     * POST /api/v1/user/ban
     */
    public function removeUser(User $user): bool
    {
        try {
            $existing = $this->findByEmail($user->email);
            if (!$existing) {
                Log::channel('daily')->info('[Vega] Silinecek kullanıcı bulunamadı', [
                    'userId' => $user->id,
                ]);
                return true;
            }

            $response = $this->request('POST', '/api/v1/user/ban', [
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
            Log::channel('daily')->error('[Vega] getUser hatası', [
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

    /* ═══════════════════════════════════════════════════
     *  Extended Methods (Admin Panel Raporlama)
     * ═══════════════════════════════════════════════════ */

    /**
     * Vega API sağlık kontrolü.
     * GET /api/v1/health
     *
     * @return array{ok: bool, service: ?string, error: ?string}
     */
    public function getHealth(): array
    {
        try {
            $response = $this->request('GET', '/api/v1/health');

            if ($response->successful()) {
                return [
                    'ok' => true,
                    'service' => $response->json('service'),
                    'error' => null,
                ];
            }

            return ['ok' => false, 'service' => null, 'error' => "HTTP {$response->status()}"];
        } catch (\Throwable $e) {
            return ['ok' => false, 'service' => null, 'error' => $e->getMessage()];
        }
    }

    /**
     * Vega'daki tüm kullanıcıları listele.
     * GET /api/v1/users?page={page}
     *
     * @return array{success: bool, data: array, pagination: array, error: ?string}
     */
    public function listRemoteUsers(int $page = 1, int $perPage = 50): array
    {
        try {
            $response = $this->request('GET', '/api/v1/users', [
                'page' => $page,
                'per_page' => $perPage,
            ]);

            if ($response->successful()) {
                $data = $response->json('data', []);
                return [
                    'success' => true,
                    'data' => $data['users'] ?? [],
                    'pagination' => [
                        'current_page' => $data['current_page'] ?? 1,
                        'per_page' => $data['per_page'] ?? $perPage,
                        'total' => $data['total'] ?? 0,
                        'last_page' => $data['last_page'] ?? 1,
                    ],
                    'error' => null,
                ];
            }

            return ['success' => false, 'data' => [], 'pagination' => [], 'error' => "HTTP {$response->status()}"];
        } catch (\Throwable $e) {
            return ['success' => false, 'data' => [], 'pagination' => [], 'error' => $e->getMessage()];
        }
    }

    /**
     * Vega'dan kullanıcı detayını ID ile getir.
     * GET /api/v1/users/{vegaId}
     *
     * @return array|null  User verisi (roller, profil, premium dahil), yoksa null
     */
    public function getRemoteUserById(int $vegaId): ?array
    {
        try {
            $response = $this->request('GET', "/api/v1/users/{$vegaId}");

            if ($response->successful()) {
                return $response->json('data');
            }

            return null;
        } catch (\Throwable $e) {
            Log::channel('daily')->error('[Vega] getRemoteUserById hatası', [
                'vegaId' => $vegaId,
                'message' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Kullanıcının oturum geçmişini getir.
     * Lecturer (Way AI Coach) ve Simulator (Role Galaxy) oturumları.
     *
     * @param  int    $vegaUserId   Vega tarafındaki kullanıcı ID
     * @param  string $type         'lecturer' | 'simulator' | 'all'
     * @return array{success: bool, sessions: array, error: ?string}
     */
    public function getUserSessions(int $vegaUserId, string $type = 'all'): array
    {
        $sessions = [];

        try {
            // Lecturer (Way AI Coach) oturumları
            if (in_array($type, ['all', 'lecturer'])) {
                $response = $this->request('GET', "/api/v1/lecturer/sessions", [
                    'user_id' => $vegaUserId,
                ]);
                if ($response->successful()) {
                    $lecturerData = $response->json('data', $response->json('sessions', []));
                    foreach ((is_array($lecturerData) ? $lecturerData : []) as $s) {
                        $s['module'] = 'lecturer';
                        $s['app_name'] = 'Way AI Coach';
                        $sessions[] = $s;
                    }
                }
            }

            // Simulator (Role Galaxy) oturumları
            if (in_array($type, ['all', 'simulator'])) {
                // Simulator'ın ayrı list endpoint'i yok,
                // ama sessions/{module}/{session_id} ile tekil çekilebilir.
                // Şimdilik sessions overview kullanılabilir.
            }

            return ['success' => true, 'sessions' => $sessions, 'error' => null];
        } catch (\Throwable $e) {
            Log::channel('daily')->error('[Vega] getUserSessions hatası', [
                'vegaUserId' => $vegaUserId,
                'message' => $e->getMessage(),
            ]);
            return ['success' => false, 'sessions' => [], 'error' => $e->getMessage()];
        }
    }

    /**
     * Tek bir oturum detayını getir.
     *
     * @param  string $sessionId  Vega oturum ID (sess_xxx)
     * @param  string $module     'lecturer' | 'simulator'
     * @return array|null
     */
    public function getSessionDetail(string $sessionId, string $module = 'lecturer'): ?array
    {
        try {
            $path = match ($module) {
                'simulator' => "/api/v1/sessions/simulator/{$sessionId}",
                default => "/api/v1/sessions/lecturer/{$sessionId}",
            };

            $response = $this->request('GET', $path);

            if ($response->successful()) {
                return $response->json('data', $response->json());
            }

            return null;
        } catch (\Throwable $e) {
            Log::channel('daily')->error('[Vega] getSessionDetail hatası', [
                'sessionId' => $sessionId,
                'module' => $module,
                'message' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Kullanıcının yasağını kaldır.
     * POST /api/v1/user/unban
     */
    public function unbanUser(User $user): bool
    {
        try {
            $existing = $this->findByEmail($user->email);
            if (!$existing) {
                return false;
            }

            $response = $this->request('POST', '/api/v1/user/unban', [
                'user_id' => $existing['id'],
            ]);

            if ($response->successful()) {
                Log::channel('daily')->info('[Vega] Kullanıcı yasağı kaldırıldı', [
                    'userId' => $user->id,
                    'vegaId' => $existing['id'],
                ]);
                return true;
            }

            Log::channel('daily')->error('[Vega] Unban hatası', [
                'userId' => $user->id,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return false;
        } catch (\Throwable $e) {
            Log::channel('daily')->error('[Vega] Unban bağlantı hatası', [
                'userId' => $user->id,
                'message' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /* ═══════════════════════════════════════════════════
     *  Internal Helpers
     * ═══════════════════════════════════════════════════ */

    /**
     * Vega API'ye HTTP isteği gönder.
     * Tüm metotlar bu merkezi fonksiyonu kullanır.
     */
    private function request(string $method, string $path, array $data = [])
    {
        $http = Http::timeout($this->timeout)->withHeaders([
            'X-API-KEY' => $this->apiKey,
            'Accept' => 'application/json',
        ]);

        $url = "{$this->baseUrl}{$path}";

        return match (strtoupper($method)) {
            'GET' => $http->get($url, $data),
            'POST' => $http->post($url, $data),
            'PUT' => $http->put($url, $data),
            'DELETE' => $http->delete($url, $data),
            default => $http->get($url, $data),
        };
    }

    /**
     * Vega API'den tüm kullanıcıları çek ve email ile eşleştir.
     * Sayfalı arama — ilk sayfadan başlar, eşleşme bulunana kadar devam eder.
     */
    private function findByEmail(string $email): ?array
    {
        $page = 1;
        $maxPages = 10; // safety limit

        while ($page <= $maxPages) {
            $response = $this->request('GET', '/api/v1/users', [
                'page' => $page,
                'per_page' => 50,
            ]);

            if (!$response->successful()) {
                return null;
            }

            $data = $response->json('data', []);
            $users = $data['users'] ?? [];

            foreach ($users as $vegaUser) {
                if (isset($vegaUser['email']) && mb_strtolower($vegaUser['email']) === mb_strtolower($email)) {
                    return $vegaUser;
                }
            }

            $lastPage = $data['last_page'] ?? 1;
            if ($page >= $lastPage) {
                break;
            }

            $page++;
        }

        return null;
    }
}
