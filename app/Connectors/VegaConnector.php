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
    public function syncUser(User $user, ?string $plainPassword = null): array
    {
        try {
            // 1) Email ile kullanıcıyı ara
            $existing = $this->findByEmail($user->email);
            if ($existing) {
                Log::channel('daily')->info('[Vega] Kullanıcı zaten mevcut', [
                    'userId' => $user->id,
                    'vegaId' => $existing['id'] ?? null,
                ]);

                // Ensure premium and b2b status via direct DB connection
                try {
                    \Illuminate\Support\Facades\DB::connection('vega_db')->table('users')
                        ->where('email', $user->email)
                        ->update([
                            'is_premium' => 1,
                            'is_lifetime_premium' => 1,
                            'login_type' => 'dopifuture',
                        ]);
                } catch (\Exception $dbEx) {
                    Log::channel('daily')->error('[Vega] DB update error (existing)', ['email' => $user->email, 'error' => $dbEx->getMessage()]);
                }

                return ['success' => true, 'response' => $existing, 'error' => null];
            }

            // 2) Yoksa register ile oluştur
            // Manuel şifre verilmişse onu kullan, yoksa rastgele üret
            $rawPassword = $plainPassword ?: ('Vg' . bin2hex(random_bytes(4)) . '!9');
            $name = trim($user->name ?? '') ?: 'Öğrenci';
            $surname = trim($user->surname ?? '') ?: 'Öğrenci';
            
            $payload = [
                'name' => $name,
                'surname' => $surname,
                'email' => $user->email,
                'password' => $rawPassword,
                'password_confirmation' => $rawPassword,
            ];

            $response = $this->request('POST', '/api/v1/register', $payload);

            if ($response->successful()) {
                Log::channel('daily')->info('[Vega] Kullanıcı oluşturuldu', [
                    'userId' => $user->id,
                    'email' => $user->email,
                ]);

                // Ensure premium and b2b status via direct DB connection
                try {
                    \Illuminate\Support\Facades\DB::connection('vega_db')->table('users')
                        ->where('email', $user->email)
                        ->update([
                            'is_premium' => 1,
                            'is_lifetime_premium' => 1,
                            'login_type' => 'dopifuture',
                        ]);
                } catch (\Exception $dbEx) {
                    Log::channel('daily')->error('[Vega] DB update error (new)', ['email' => $user->email, 'error' => $dbEx->getMessage()]);
                }

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
     * Kullanıcıyı Vega'dan sil.
     * DELETE /api/v1/users/{vegaId}
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

            $vegaId = $existing['id'];
            $response = $this->request('DELETE', "/api/v1/users/{$vegaId}");

            if ($response->successful() || $response->status() === 404) {
                Log::channel('daily')->info('[Vega] Kullanıcı silindi', [
                    'userId' => $user->id,
                    'vegaId' => $vegaId,
                    'status' => $response->status(),
                ]);
                return true;
            }

            Log::channel('daily')->error('[Vega] Silme hatası', [
                'userId' => $user->id,
                'vegaId' => $vegaId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return false;
        } catch (\Throwable $e) {
            Log::channel('daily')->error('[Vega] Silme bağlantı hatası', [
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

    /**
     * Kullanıcının Vega üzerindeki detaylı raporunu getir.
     * 1. Email ile Vega kullanıcısını bul → Vega ID
     * 2. Vega profilini getir (roller, premium, istatistik)
     * 3. Oturum geçmişini getir (lecturer + simulator)
     */
    public function getUserReport(User $user): ?array
    {
        try {
            // 1. Email ile Vega kullanıcısını bul
            $vegaUser = $this->findByEmail($user->email);
            if (!$vegaUser) {
                return [
                    'success' => false,
                    'data' => [],
                    'error' => 'Kullanıcı Vega sisteminde bulunamadı',
                ];
            }

            $vegaId = $vegaUser['id'] ?? null;
            if (!$vegaId) {
                return [
                    'success' => false,
                    'data' => ['remote_user' => $vegaUser],
                    'error' => 'Vega kullanıcı ID bulunamadı',
                ];
            }

            // 2. Detaylı profil
            $profile = $this->getRemoteUserById($vegaId);

            // 3. Oturum geçmişi (lecturer + simulator)
            $sessionsResult = $this->getUserSessions($vegaId, 'all');
            $sessions = $sessionsResult['sessions'] ?? [];

            // 4. Her oturumun detayını çek (feedback, transkript, skor)
            $enrichedSessions = [];
            foreach ($sessions as $session) {
                $sessionId = $session['id'] ?? $session['sessionId'] ?? null;
                $module = $session['module'] ?? 'lecturer';

                if ($sessionId) {
                    $detail = $this->getSessionDetail((string) $sessionId, $module);
                    if ($detail) {
                        $session['detail'] = $detail;
                    }
                }
                $enrichedSessions[] = $session;
            }

            return [
                'success' => true,
                'data' => [
                    'vega_id' => $vegaId,
                    'profile' => $profile ?? $vegaUser,
                    'sessions' => $enrichedSessions,
                    'session_count' => count($enrichedSessions),
                    'modules' => [
                        'lecturer' => collect($enrichedSessions)->where('module', 'lecturer')->count(),
                        'simulator' => collect($enrichedSessions)->where('module', 'simulator')->count(),
                    ],
                    'has_details' => collect($enrichedSessions)->whereNotNull('detail')->count(),
                ],
                'error' => null,
            ];
        } catch (\Throwable $e) {
            Log::channel('daily')->error('[Vega] getUserReport hatası', [
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

    /**
     * Kullanıcının tüm modüllerdeki oturum özetini getir.
     * GET /api/v1/sessions/overview?user_id={userId}
     *
     * @return array{total_sessions: int, simulator_count: int, lecturer_count: int, chatbot_count: int}
     */
    public function getSessionsOverview(int $vegaUserId): array
    {
        try {
            $response = $this->request('GET', '/api/v1/sessions/overview', [
                'user_id' => $vegaUserId,
            ]);

            if ($response->successful()) {
                $data = $response->json('data', $response->json());
                return [
                    'total_sessions'  => $data['total_sessions'] ?? 0,
                    'simulator_count' => $data['simulator_count'] ?? 0,
                    'lecturer_count'  => $data['lecturer_count'] ?? 0,
                    'chatbot_count'   => $data['chatbot_count'] ?? 0,
                ];
            }

            return ['total_sessions' => 0, 'simulator_count' => 0, 'lecturer_count' => 0, 'chatbot_count' => 0];
        } catch (\Throwable $e) {
            Log::channel('daily')->error('[Vega] getSessionsOverview hatası', [
                'vegaUserId' => $vegaUserId,
                'message' => $e->getMessage(),
            ]);
            return ['total_sessions' => 0, 'simulator_count' => 0, 'lecturer_count' => 0, 'chatbot_count' => 0];
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
     * Vega API'den kullanıcıyı email ile ara.
     * Önce search parametresi ile dene, desteklenmiyorsa sayfalı fallback.
     * Sonuçlar 10 dakika cache'lenir (bulk sync performansı için).
     */
    private function findByEmail(string $email): ?array
    {
        $cacheKey = 'vega.user.email.' . md5(strtolower($email));

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 600, function () use ($email) {
            // 1) En Hızlı ve Kesin Yöntem: Direct DB Connection
            try {
                $dbUser = \Illuminate\Support\Facades\DB::connection('vega_db')
                    ->table('users')
                    ->where('email', $email)
                    ->first();
                    
                if ($dbUser) {
                    return json_decode(json_encode($dbUser), true);
                }
            } catch (\Exception $dbEx) {
                Log::channel('daily')->error('[Vega] DB findByEmail error, falling back to API', ['email' => $email, 'error' => $dbEx->getMessage()]);
            }

            // 2) Fallback: API Search Parameters
            try {
                $response = $this->request('GET', '/api/v1/users', [
                    'search' => $email,
                    'per_page' => 5,
                ]);

                if ($response->successful()) {
                    $data = $response->json('data', []);
                    $users = $data['users'] ?? $data;

                    if (is_array($users)) {
                        foreach ($users as $vegaUser) {
                            if (isset($vegaUser['email']) && mb_strtolower($vegaUser['email']) === mb_strtolower($email)) {
                                return $vegaUser;
                            }
                        }
                    }
                }
            } catch (\Throwable $e) {
                // Ignore fallback error
            }

            return null;
        });
    }

    /* ═══════════════════════════════════════════════════════
     *  Ban Yönetimi — POST /api/v1/user/ban
     * ═══════════════════════════════════════════════════════ */

    /**
     * POST /api/v1/user/ban
     * Kullanıcıyı Vega platformunda engeller.
     */
    public function banUser(User $user): bool
    {
        try {
            $vegaUser = $this->findByEmail($user->email);
            if (!$vegaUser || !isset($vegaUser['id'])) {
                Log::channel('daily')->warning('[Vega] Ban: kullanıcı bulunamadı', ['email' => $user->email]);
                return false;
            }

            $response = $this->request('POST', '/api/v1/user/ban', [
                'user_id' => $vegaUser['id'],
            ]);

            if ($response->successful()) {
                Log::channel('daily')->info('[Vega] Kullanıcı engellendi', ['vegaId' => $vegaUser['id']]);
                return true;
            }

            Log::channel('daily')->error('[Vega] Ban hatası', [
                'vegaId' => $vegaUser['id'],
                'status' => $response->status(),
            ]);
            return false;
        } catch (\Throwable $e) {
            Log::channel('daily')->error('[Vega] banUser hatası', ['message' => $e->getMessage()]);
            return false;
        }
    }

    /* ═══════════════════════════════════════════════════════
     *  Lecturer — GET /api/v1/lecturer/lessons
     * ═══════════════════════════════════════════════════════ */

    /**
     * GET /api/v1/lecturer/lessons
     * WAY AI Coach ders kataloğu.
     */
    public function getLecturerLessons(): array
    {
        try {
            $response = $this->request('GET', '/api/v1/lecturer/lessons');
            if ($response->successful()) {
                $data = $response->json();
                return $data['data'] ?? $data['lessons'] ?? (is_array($data) ? $data : []);
            }
            return [];
        } catch (\Throwable $e) {
            Log::channel('daily')->error('[Vega] getLecturerLessons hatası', ['message' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * GET /api/v1/lecturer/lessons/{lessonId}
     * Tekil ders detayı.
     */
    public function getLecturerLesson(int $lessonId): ?array
    {
        try {
            $response = $this->request('GET', "/api/v1/lecturer/lessons/{$lessonId}");
            if ($response->successful()) {
                $data = $response->json();
                return $data['data'] ?? $data;
            }
            return null;
        } catch (\Throwable $e) {
            Log::channel('daily')->error('[Vega] getLecturerLesson hatası', ['message' => $e->getMessage()]);
            return null;
        }
    }

    /* ═══════════════════════════════════════════════════════
     *  Simulator — GET /api/v1/simulator/scenarios
     * ═══════════════════════════════════════════════════════ */

    /**
     * GET /api/v1/simulator/scenarios
     * Role Galaxy senaryo kataloğu.
     */
    public function getSimulatorScenarios(): array
    {
        try {
            $response = $this->request('GET', '/api/v1/simulator/scenarios');
            if ($response->successful()) {
                $data = $response->json();
                return $data['data'] ?? $data['scenarios'] ?? (is_array($data) ? $data : []);
            }
            return [];
        } catch (\Throwable $e) {
            Log::channel('daily')->error('[Vega] getSimulatorScenarios hatası', ['message' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * GET /api/v1/simulator/scenarios/{scenarioId}
     * Tekil senaryo detayı.
     */
    public function getSimulatorScenario(int $scenarioId): ?array
    {
        try {
            $response = $this->request('GET', "/api/v1/simulator/scenarios/{$scenarioId}");
            if ($response->successful()) {
                $data = $response->json();
                return $data['data'] ?? $data;
            }
            return null;
        } catch (\Throwable $e) {
            Log::channel('daily')->error('[Vega] getSimulatorScenario hatası', ['message' => $e->getMessage()]);
            return null;
        }
    }

    /* ═══════════════════════════════════════════════════════
     *  WayWing — GET /api/v1/wings
     * ═══════════════════════════════════════════════════════ */

    /**
     * GET /api/v1/wings
     * Tüm kanatlar/rozetler listesi.
     */
    public function getWings(): array
    {
        try {
            $response = $this->request('GET', '/api/v1/wings');
            if ($response->successful()) {
                $data = $response->json();
                return $data['data'] ?? $data['wings'] ?? (is_array($data) ? $data : []);
            }
            return [];
        } catch (\Throwable $e) {
            Log::channel('daily')->error('[Vega] getWings hatası', ['message' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * GET /api/v1/wings/by-ids
     * Belirli ID'lere göre kanatları getirir.
     */
    public function getWingsByIds(array $ids): array
    {
        try {
            $response = $this->request('GET', '/api/v1/wings/by-ids', [
                'ids' => implode(',', $ids),
            ]);
            if ($response->successful()) {
                $data = $response->json();
                return $data['data'] ?? (is_array($data) ? $data : []);
            }
            return [];
        } catch (\Throwable $e) {
            Log::channel('daily')->error('[Vega] getWingsByIds hatası', ['message' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * GET /api/v1/wings/points
     * Kanat puan istatistikleri.
     */
    public function getWingPoints(): array
    {
        try {
            $response = $this->request('GET', '/api/v1/wings/points');
            if ($response->successful()) {
                $data = $response->json();
                return $data['data'] ?? (is_array($data) ? $data : []);
            }
            return [];
        } catch (\Throwable $e) {
            Log::channel('daily')->error('[Vega] getWingPoints hatası', ['message' => $e->getMessage()]);
            return [];
        }
    }

    /* ═══════════════════════════════════════════════════════
     *  Chat / Study Space — GET /api/v1/chat/*
     * ═══════════════════════════════════════════════════════ */

    /**
     * GET /api/v1/chat/sessions
     * Chat oturumlarını listeler (API key ile çalışırsa).
     */
    public function getChatSessions(array $params = []): array
    {
        try {
            $response = $this->request('GET', '/api/v1/chat/sessions', $params);
            if ($response->successful()) {
                $data = $response->json();
                return $data['data'] ?? $data['sessions'] ?? (is_array($data) ? $data : []);
            }
            Log::channel('daily')->debug('[Vega] getChatSessions: API key ile erişilemedi', [
                'status' => $response->status(),
            ]);
            return [];
        } catch (\Throwable $e) {
            Log::channel('daily')->warning('[Vega] getChatSessions hatası', ['message' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * GET /api/v1/chat/session/{sessionId}
     * Tekil chat oturum detayı.
     */
    public function getChatSession(string $sessionId): ?array
    {
        try {
            $response = $this->request('GET', "/api/v1/chat/session/{$sessionId}");
            if ($response->successful()) {
                $data = $response->json();
                return $data['data'] ?? $data;
            }
            return null;
        } catch (\Throwable $e) {
            Log::channel('daily')->warning('[Vega] getChatSession hatası', ['message' => $e->getMessage()]);
            return null;
        }
    }

    /* ═══════════════════════════════════════════════════════
     *  Premium — GET /api/v1/premium/status
     * ═══════════════════════════════════════════════════════ */

    /**
     * GET /api/v1/premium/status
     * Kullanıcının premium durumunu sorgular (okuma amaçlı).
     */
    public function getPremiumStatus(): ?array
    {
        try {
            $response = $this->request('GET', '/api/v1/premium/status');
            if ($response->successful()) {
                $data = $response->json();
                return $data['data'] ?? $data;
            }
            return null;
        } catch (\Throwable $e) {
            Log::channel('daily')->error('[Vega] getPremiumStatus hatası', ['message' => $e->getMessage()]);
            return null;
        }
    }
}
