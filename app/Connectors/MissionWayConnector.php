<?php

namespace App\Connectors;

use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Mission Way — Full API Connector
 *
 * Backend: https://way-backend.dopingtech.net
 * Auth: Authorization: Bearer <API_KEY>
 *
 * Player Compositions:
 *   POST   /v1/player-compositions              → Oyuncu oluştur
 *   GET    /v1/player-compositions/by-user/:id   → Oyuncu getir
 *   DELETE /v1/player-compositions/by-user/:id   → Oyuncu sil
 *
 * Simulations (auth genişletme gerekli):
 *   GET /v1/simulations                → Simülasyon listesi
 *   GET /v1/simulations/{id}           → Simülasyon detayı
 *
 * Simulation Sessions (auth genişletme gerekli):
 *   GET /v1/simulation-sessions        → Oturum listesi
 *   GET /v1/simulation-sessions/{id}   → Oturum detayı
 *
 * Session Players (auth genişletme gerekli):
 *   GET /v1/session-players/by-session/{sessionId}  → Oturumdaki oyuncular
 *   GET /v1/session-players/by-player/{playerId}    → Oyuncunun oturumları
 *
 * Players (auth genişletme gerekli):
 *   GET /v1/players                    → Oyuncu listesi
 *   GET /v1/players/{id}               → Oyuncu detayı
 *
 * Player Profiles (auth genişletme gerekli):
 *   GET /v1/player-profiles/by-player/{playerId}  → Profil istatistikleri
 *
 * Player Progress (auth genişletme gerekli):
 *   GET /v1/player-progresses          → İlerleme kayıtları
 *
 * Health:
 *   GET /health/simple                 → Servis sağlığı
 */
class MissionWayConnector extends BaseConnector implements AppConnectorInterface
{
    protected string $logPrefix = 'MissionWay';

    public function __construct()
    {
        parent::__construct('mission_way');
    }

    /**
     * Public wrapper for apiGet — used by harvest bulk operations.
     */
    public function apiGetPublic(string $path, array $params = []): ?array
    {
        return $this->apiGet($path, $params);
    }

    /* ─── Interface: syncUser ──────────────────────────── */

    /**
     * POST /v1/player-compositions
     *
     * Request:
     *   { "userId": 1, "username": "1-admin", "email": "x@y.com", "name": "Ahmet", "surname": "Yılmaz" }
     *
     * Response 201:
     *   { "player": { "id": 8, "username": "1-admin", "email": "x@y.com", "userId": "1", ... } }
     *
     * Response 400 (duplicate):
     *   { "message": "Username already exists: 1-admin", "error": "Bad Request", "statusCode": 400 }
     */
    public function syncUser(User $user, ?string $plainPassword = null): array
    {
        $name = is_array($user->name) ? ($user->name[app()->getLocale()] ?? reset($user->name) ?: 'User') : ($user->name ?? 'User');
        $surname = is_array($user->surname) ? ($user->surname[app()->getLocale()] ?? reset($user->surname) ?: '') : ($user->surname ?? '');

        // Boş name/surname API 400 hatası verir — fallback koy
        $name = trim($name) ?: 'Öğrenci';
        $surname = trim($surname) ?: 'Öğrenci';

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

    /**
     * MissionWay API'sinde güncelleme endpoint'i yok (PATCH/PUT mevcut değil).
     * Kullanıcı varlığı doğrulanır, yoksa oluşturulur.
     * Not: MissionWay kullanıcıları userId ile eşleştirir, ad/email değişikliği
     * işlevselliği etkilemez.
     */
    public function updateUser(User $user): array
    {
        try {
            // Kullanıcı uzak sistemde mevcut mu kontrol et
            $existing = $this->getUser($user);

            if ($existing) {
                Log::channel('daily')->info('[MissionWay] Kullanıcı mevcut (güncelleme API yok)', [
                    'userId' => $user->id,
                ]);
                return ['success' => true, 'response' => $existing, 'error' => null];
            }

            // Kullanıcı yoksa oluştur
            Log::channel('daily')->info('[MissionWay] Kullanıcı bulunamadı, oluşturuluyor', [
                'userId' => $user->id,
            ]);
            return $this->syncUser($user);
        } catch (\Throwable $e) {
            Log::channel('daily')->error('[MissionWay] updateUser hatası', [
                'userId' => $user->id,
                'message' => $e->getMessage(),
            ]);
            return ['success' => false, 'response' => null, 'error' => $e->getMessage()];
        }
    }

    /* ─── Interface: removeUser ─────────────────────────── */

    /**
     * DELETE /v1/player-compositions/by-user/{userId}
     *
     * Response 204: (boş — başarılı silme)
     * Response 404: (zaten yok)
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
     *
     * Response 200:
     *   {
     *     "player": {
     *       "id": 8,
     *       "username": "1-admin",
     *       "email": "admin@panel26.com",
     *       "userId": "1",
     *       "name": "Admin",
     *       "surname": "User",
     *       "avatarMediaId": null,
     *       "preferredLanguageId": null,
     *       "createdAt": "2026-02-23T11:03:41.812Z",
     *       "updatedAt": "2026-03-05T23:09:27.000Z"
     *     }
     *   }
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

    /* ═══════════════════════════════════════════════════════
     *  Simulations — GET /v1/simulations
     * ═══════════════════════════════════════════════════════ */

    /**
     * GET /v1/simulations
     *
     * Query: ?limit=25&page=1&filter=difficultyLevel||eq||easy
     *
     * Response 200:
     *   {
     *     "data": [
     *       {
     *         "id": 1,
     *         "difficultyLevel": "easy",      // easy|medium|hard
     *         "estimatedDuration": 45,         // dakika
     *         "minPlayers": 2,
     *         "maxPlayers": 5,
     *         "coverImageAssetId": 12,
     *         "createdBy": "admin",
     *         "updatedBy": "admin",
     *         "deactivatedAt": null,
     *         "createdAt": "2026-01-15T10:00:00.000Z",
     *         "updatedAt": "2026-02-20T14:30:00.000Z"
     *       }
     *     ],
     *     "count": 1,
     *     "total": 10,
     *     "page": 1,
     *     "pageCount": 1
     *   }
     */
    public function getSimulations(array $params = []): ?array
    {
        return $this->apiGet('/v1/simulations', $params);
    }

    /**
     * GET /v1/simulations/{id}
     *
     * Response 200: SimulationEntity tek obje (data sarmalı yok)
     */
    public function getSimulation(int $id, array $params = []): ?array
    {
        return $this->apiGet("/v1/simulations/{$id}", $params);
    }

    /* ═══════════════════════════════════════════════════════
     *  Simulation Sessions — GET /v1/simulation-sessions
     * ═══════════════════════════════════════════════════════ */

    /**
     * GET /v1/simulation-sessions
     *
     * Query: ?limit=25&page=1&filter=simulationVersionId||eq||5
     *
     * Response 200:
     *   {
     *     "data": [
     *       {
     *         "id": 1,
     *         "simulationVersionId": 5,
     *         "sessionCode": "ABC123",
     *         "status": "completed",       // waiting|active|completed|cancelled
     *         "startedAt": "2026-02-10T09:00:00.000Z",
     *         "completedAt": "2026-02-10T09:45:00.000Z",
     *         "finalPathId": 3,
     *         "finalScore": 85,
     *         "finalMetrics": {
     *           "health": 75,
     *           "resource": 60,
     *           "ethics": 90,
     *           "adaptation": 80
     *         },
     *         "createdBy": "system",
     *         "createdAt": "2026-02-10T09:00:00.000Z"
     *       }
     *     ],
     *     "count": 1, "total": 50, "page": 1, "pageCount": 2
     *   }
     */
    public function getSimulationSessions(array $params = []): ?array
    {
        return $this->apiGet('/v1/simulation-sessions', $params);
    }

    /**
     * GET /v1/simulation-sessions/{id}
     *
     * Response 200: SimulationSessionEntity tek obje
     */
    public function getSimulationSession(int $id, array $params = []): ?array
    {
        return $this->apiGet("/v1/simulation-sessions/{$id}", $params);
    }

    /* ═══════════════════════════════════════════════════════
     *  Session Players — GET /v1/session-players
     * ═══════════════════════════════════════════════════════ */

    /**
     * GET /v1/session-players/by-session/{sessionId}
     *
     * Response 200:
     *   [
     *     {
     *       "id": 1,
     *       "simulationSessionId": 10,
     *       "playerId": 8,
     *       "roleId": 3,
     *       "joinedAt": "2026-02-10T09:00:00.000Z",
     *       "createdBy": "system",
     *       "createdAt": "2026-02-10T09:00:00.000Z"
     *     }
     *   ]
     */
    public function getSessionPlayers(int $sessionId): ?array
    {
        return $this->apiGet("/v1/session-players/by-session/{$sessionId}");
    }

    /**
     * GET /v1/session-players/by-player/{playerId}
     *
     * Response 200: SessionPlayerEntity[] — oyuncunun katıldığı tüm oturumlar
     */
    public function getPlayerSessions(int $playerId): ?array
    {
        $result = $this->apiGet("/v1/session-players/by-player/{$playerId}");
        return is_array($result) ? $result : [];
    }

    /* ═══════════════════════════════════════════════════════
     *  Players — GET /v1/players
     * ═══════════════════════════════════════════════════════ */

    /**
     * GET /v1/players
     *
     * Query: ?limit=25&page=1
     *
     * Response 200:
     *   {
     *     "data": [
     *       {
     *         "id": 8,
     *         "username": "1-admin",
     *         "email": "admin@panel26.com",
     *         "name": "Admin",
     *         "surname": "User",
     *         "userId": 1,
     *         "avatarMediaId": null,
     *         "preferredLanguageId": null,
     *         "createdAt": "2026-02-23T11:03:41.812Z"
     *       }
     *     ],
     *     "count": 1, "total": 54, "page": 1, "pageCount": 3
     *   }
     */
    public function getPlayers(array $params = []): ?array
    {
        return $this->apiGet('/v1/players', $params);
    }

    /**
     * GET /v1/players/{id}
     *
     * Response 200: PlayerEntity tek obje
     */
    public function getPlayer(int $id, array $params = []): ?array
    {
        return $this->apiGet("/v1/players/{$id}", $params);
    }

    /* ═══════════════════════════════════════════════════════
     *  Player Profiles — GET /v1/player-profiles
     * ═══════════════════════════════════════════════════════ */

    /**
     * GET /v1/player-profiles/by-player/{playerId}
     *
     * Response 200:
     *   {
     *     "id": 1,
     *     "playerId": 8,
     *     "totalScore": 450,
     *     "totalSimulationsCompleted": 5,
     *     "totalPlayTimeMinutes": 230,
     *     "lastCompletedSimulationId": 3,
     *     "achievements": [ { "type": "first_sim", "earnedAt": "..." } ],
     *     "statistics": {
     *       "avgScore": 78.5,
     *       "bestScore": 95,
     *       "avgHealthMetric": 72,
     *       "avgResourceMetric": 65,
     *       "avgEthicsMetric": 80,
     *       "avgAdaptationMetric": 70
     *     },
     *     "createdAt": "2026-02-23T11:03:41.812Z"
     *   }
     */
    public function getPlayerProfile(int $playerId): ?array
    {
        return $this->apiGet("/v1/player-profiles/by-player/{$playerId}");
    }

    /* ═══════════════════════════════════════════════════════
     *  Player Progress — GET /v1/player-progresses
     * ═══════════════════════════════════════════════════════ */

    /**
     * GET /v1/player-progresses
     *
     * Query: ?limit=25&page=1&filter=playerId||eq||8
     *
     * Response 200:
     *   {
     *     "data": [
     *       {
     *         "id": 1,
     *         "playerId": 8,
     *         "simulationSessionId": 10,
     *         "simulationVersionId": 5,
     *         "currentPathId": 3,
     *         "currentScore": 85,
     *         "currentMetrics": {
     *           "health": 75,
     *           "resource": 60,
     *           "ethics": 90,
     *           "adaptation": 80
     *         },
     *         "startedAt": "2026-02-10T09:00:00.000Z",
     *         "completedAt": "2026-02-10T09:45:00.000Z"
     *       }
     *     ],
     *     "count": 1, "total": 15, "page": 1, "pageCount": 1
     *   }
     */
    public function getPlayerProgressList(array $params = []): ?array
    {
        $result = $this->apiGet('/v1/player-progresses', $params);
        if (isset($result['data'])) {
            return $result['data'];
        }
        return is_array($result) ? $result : [];
    }

    /* ═══════════════════════════════════════════════════════
     *  Simulation Paths — GET /v1/simulation-paths
     * ═══════════════════════════════════════════════════════ */

    /**
     * GET /v1/simulation-paths?filter=simulationVersionId||eq||{versionId}
     *
     * Response 200:
     *   {
     *     "data": [
     *       {
     *         "id": 1,
     *         "simulationVersionId": 5,
     *         "parentPathId": null,
     *         "mediaAssetId": null,
     *         "orderIndex": 1,
     *         "points": 10,
     *         "metrics": { "health": 5, "resource": -3, "ethics": 8, "adaptation": 2 },
     *         "pathType": "decision",
     *         "maxWaitTime": 60,
     *         "pathPoints": 10,
     *         "isEnded": false,
     *         "translations": { "narrative": "...", "question": "...", "optionText": "..." }
     *       }
     *     ]
     *   }
     */
    public function getSimulationPaths(int $simulationVersionId, int $page = 1): array
    {
        $result = $this->apiGet('/v1/simulation-paths', [
            'filter' => "simulationVersionId||eq||{$simulationVersionId}",
            'limit' => 200,
            'page'  => $page,
        ]);
        if (isset($result['data'])) {
            return $result['data'];
        }
        return is_array($result) ? $result : [];
    }

    /* ═══════════════════════════════════════════════════════
     *  Player Choices — GET /v1/player-choices
     * ═══════════════════════════════════════════════════════ */

    /**
     * GET /v1/player-choices?filter=simulationSessionId||eq||{sessionId}
     *
     * Response 200:
     *   {
     *     "data": [
     *       {
     *         "id": 1,
     *         "playerId": 8,
     *         "simulationSessionId": 10,
     *         "simulationPathId": 3,
     *         "selectedPathId": 5,
     *         "previousPathId": 2,
     *         "isCorrect": true,
     *         "pointsEarned": 10,
     *         "responseTimeSeconds": 15,
     *         "metricsBefore": { "health": 70, "resource": 55, "ethics": 80, "adaptation": 65 },
     *         "metricsAfter": { "health": 75, "resource": 52, "ethics": 88, "adaptation": 67 }
     *       }
     *     ]
     *   }
     */
    public function getPlayerChoices(int $sessionId): array
    {
        $result = $this->apiGet('/v1/player-choices', [
            'filter' => "simulationSessionId||eq||{$sessionId}",
            'limit' => 200,
        ]);
        if (isset($result['data'])) {
            return $result['data'];
        }
        return is_array($result) ? $result : [];
    }

    /* ═══════════════════════════════════════════════════════
     *  Metric Definitions — GET /v1/metric-definitions
     * ═══════════════════════════════════════════════════════ */

    /**
     * GET /v1/metric-definitions
     *
     * Response 200:
     *   {
     *     "data": [
     *       {
     *         "id": 1,
     *         "key": "health",
     *         "name": "Health",
     *         "icon": "❤️",
     *         "color": "#EF4444",
     *         "unitLabel": "HP",
     *         "createdAt": "2026-01-10T08:00:00.000Z"
     *       }
     *     ]
     *   }
     */
    public function getMetricDefinitions(array $params = []): ?array
    {
        $result = $this->apiGet('/v1/metric-definitions', array_merge(['limit' => 100], $params));
        if (isset($result['data'])) {
            return $result['data'];
        }
        return is_array($result) ? $result : [];
    }

    /* ═══════════════════════════════════════════════════════
     *  Simulation Versions — GET /v1/simulation-versions/simulation/:id
     * ═══════════════════════════════════════════════════════ */

    /**
     * GET /v1/simulation-versions/simulation/:simulationId
     * Returns array of version objects for a given simulation.
     */
    public function getSimulationVersions(int $simulationId): array
    {
        $result = $this->apiGet("/v1/simulation-versions/simulation/{$simulationId}");
        if (isset($result['data'])) {
            return $result['data'];
        }
        return is_array($result) ? $result : [];
    }

    /* ═══════════════════════════════════════════════════════
     *  Metric Band Categories — GET /v1/metric-band-categories
     * ═══════════════════════════════════════════════════════ */

    /**
     * GET /v1/metric-band-categories
     *
     * Response 200:
     *   {
     *     "data": [
     *       {
     *         "id": 1,
     *         "key": "critical",
     *         "label": "Critical",
     *         "color": "#EF4444",
     *         "scoringImpact": -2,
     *         "createdAt": "2026-01-10T08:00:00.000Z"
     *       }
     *     ]
     *   }
     */
    public function getMetricBandCategories(array $params = []): ?array
    {
        $result = $this->apiGet('/v1/metric-band-categories', array_merge(['limit' => 100], $params));
        if (isset($result['data'])) {
            return $result['data'];
        }
        return is_array($result) ? $result : [];
    }

    /* ═══════════════════════════════════════════════════════
     *  Simulation Metric Bands — GET /v1/simulation-metric-bands
     * ═══════════════════════════════════════════════════════ */

    /**
     * GET /v1/simulation-metric-bands?filter=simulationVersionId||eq||{versionId}
     *
     * Response 200:
     *   {
     *     "data": [
     *       {
     *         "id": 1,
     *         "simulationVersionId": 5,
     *         "metricId": 1,
     *         "categoryId": 2,
     *         "minValue": 0,
     *         "maxValue": 30,
     *         "createdAt": "2026-01-10T08:00:00.000Z"
     *       }
     *     ]
     *   }
     */
    public function getSimulationMetricBands(int $simulationVersionId): array
    {
        $result = $this->apiGet('/v1/simulation-metric-bands', [
            'filter' => "simulationVersionId||eq||{$simulationVersionId}",
            'limit' => 200,
        ]);
        if (isset($result['data'])) {
            return $result['data'];
        }
        return is_array($result) ? $result : [];
    }

    /* ═══════════════════════════════════════════════════════
     *  Assignments — GET /v1/assignments
     * ═══════════════════════════════════════════════════════ */

    /**
     * GET /v1/assignments
     *
     * Response 200:
     *   {
     *     "data": [
     *       {
     *         "id": 1,
     *         "simulationId": 3,
     *         "simulationSessionId": 10,
     *         "grade": "A",
     *         "deadline": "2026-04-15T23:59:59.000Z",
     *         "status": "active",
     *         "createdBy": "admin",
     *         "createdAt": "2026-02-10T09:00:00.000Z"
     *       }
     *     ]
     *   }
     */
    public function getAssignments(array $params = []): ?array
    {
        $result = $this->apiGet('/v1/assignments', array_merge(['limit' => 200], $params));
        if (isset($result['data'])) {
            return $result['data'];
        }
        return is_array($result) ? $result : [];
    }

    /**
     * GET /v1/assignments/{id}/players
     * Assignment'a atanmış player ID listesi.
     */
    public function getAssignmentPlayers(int $assignmentId): array
    {
        $result = $this->apiGet("/v1/assignments/{$assignmentId}/players");
        return is_array($result) ? $result : [];
    }

    /**
     * POST /v1/assignments
     * Yeni assignment oluşturur.
     *
     * @param array $data {
     *   simulationId: int (required),
     *   userIds: int[] (required) — backend user IDs,
     *   deadline?: string (ISO 8601)
     * }
     */
    public function createAssignment(array $data): \Illuminate\Http\Client\Response
    {
        return $this->apiPost('/v1/assignments', $data);
    }

    /**
     * DELETE /v1/assignments/{id}/members/{memberId}
     * Assignment'tan bir üyeyi/oyuncuyu çıkarır.
     */
    public function removeAssignmentMember(int $assignmentId, int $memberId): \Illuminate\Http\Client\Response
    {
        return $this->apiDelete("/v1/assignments/{$assignmentId}/members/{$memberId}");
    }

    /* ═══════════════════════════════════════════════════════
     *  Roles — GET /v1/roles
     * ═══════════════════════════════════════════════════════ */

    /**
     * GET /v1/roles
     *
     * Response 200:
     *   {
     *     "data": [
     *       {
     *         "id": 1,
     *         "name": "Diplomat",
     *         "createdAt": "2026-01-10T08:00:00.000Z"
     *       }
     *     ]
     *   }
     */
    public function getRoles(array $params = []): ?array
    {
        $result = $this->apiGet('/v1/roles', array_merge(['limit' => 100], $params));
        if (isset($result['data'])) {
            return $result['data'];
        }
        return is_array($result) ? $result : [];
    }

    /* ═══════════════════════════════════════════════════════
     *  Translations — GET /v1/translations
     * ═══════════════════════════════════════════════════════ */

    /**
     * GET /v1/translations?filter=entityType||eq||simulation_path&filter=entityId||eq||{id}
     */
    public function getTranslations(array $params = []): ?array
    {
        $result = $this->apiGet('/v1/translations', array_merge(['limit' => 500], $params));
        if (isset($result['data'])) {
            return $result['data'];
        }
        return is_array($result) ? $result : [];
    }

    /**
     * Belirli bir entity'nin çevirilerini getirir.
     */
    public function getTranslationsForEntity(string $entityType, int $entityId): array
    {
        return $this->getTranslations([
            'filter' => "entityType||eq||{$entityType}",
            'limit' => 200,
        ]) ?? [];
    }

    /* ═══════════════════════════════════════════════════════
     *  Info Cards — GET /v1/info-cards
     * ═══════════════════════════════════════════════════════ */

    /**
     * GET /v1/info-cards?filter=simulationPathId||eq||{pathId}
     */
    public function getInfoCards(int $simulationPathId): array
    {
        $result = $this->apiGet('/v1/info-cards', [
            'filter' => "simulationPathId||eq||{$simulationPathId}",
            'limit' => 50,
        ]);
        if (isset($result['data'])) {
            return $result['data'];
        }
        return is_array($result) ? $result : [];
    }

    /* ═══════════════════════════════════════════════════════
     *  Languages — GET /v1/languages
     * ═══════════════════════════════════════════════════════ */

    public function getLanguages(array $params = []): array
    {
        $result = $this->apiGet('/v1/languages', array_merge(['limit' => 100], $params));
        return isset($result['data']) ? $result['data'] : (is_array($result) ? $result : []);
    }

    public function getLanguage(int $id): ?array
    {
        return $this->apiGet("/v1/languages/{$id}");
    }

    /* ═══════════════════════════════════════════════════════
     *  Objectives — GET /v1/objectives
     * ═══════════════════════════════════════════════════════ */

    public function getObjectives(array $params = []): array
    {
        $result = $this->apiGet('/v1/objectives', array_merge(['limit' => 200], $params));
        return isset($result['data']) ? $result['data'] : (is_array($result) ? $result : []);
    }

    public function getObjective(int $id): ?array
    {
        return $this->apiGet("/v1/objectives/{$id}");
    }

    /* ═══════════════════════════════════════════════════════
     *  Path Objectives — GET /v1/path-objectives
     * ═══════════════════════════════════════════════════════ */

    public function getPathObjectives(array $params = []): array
    {
        $result = $this->apiGet('/v1/path-objectives', array_merge(['limit' => 500], $params));
        return isset($result['data']) ? $result['data'] : (is_array($result) ? $result : []);
    }

    public function getPathObjective(int $id): ?array
    {
        return $this->apiGet("/v1/path-objectives/{$id}");
    }

    /* ═══════════════════════════════════════════════════════
     *  Media Assets — GET /v1/media-assets
     * ═══════════════════════════════════════════════════════ */

    public function getMediaAssets(array $params = []): array
    {
        $result = $this->apiGet('/v1/media-assets', array_merge(['limit' => 200], $params));
        return isset($result['data']) ? $result['data'] : (is_array($result) ? $result : []);
    }

    public function getMediaAsset(int $id): ?array
    {
        return $this->apiGet("/v1/media-assets/{$id}");
    }

    /* ═══════════════════════════════════════════════════════
     *  Media Asset Files — GET /v1/media-asset-files
     * ═══════════════════════════════════════════════════════ */

    public function getMediaAssetFiles(array $params = []): array
    {
        $result = $this->apiGet('/v1/media-asset-files', array_merge(['limit' => 200], $params));
        return isset($result['data']) ? $result['data'] : (is_array($result) ? $result : []);
    }

    /* ═══════════════════════════════════════════════════════
     *  Simulation Version Roles — GET /v1/simulation-version-roles
     * ═══════════════════════════════════════════════════════ */

    public function getSimVersionRoles(array $params = []): array
    {
        $result = $this->apiGet('/v1/simulation-version-roles', array_merge(['limit' => 200], $params));
        return isset($result['data']) ? $result['data'] : (is_array($result) ? $result : []);
    }

    /* ═══════════════════════════════════════════════════════
     *  SimulationWing — GET /v1/simulation-wing/*
     * ═══════════════════════════════════════════════════════ */

    /**
     * GET /v1/simulation-wing/stats
     * Genel oturum ve simülasyon istatistikleri.
     */
    public function getSimulationWingStats(): ?array
    {
        return $this->apiGet('/v1/simulation-wing/stats');
    }

    /**
     * GET /v1/simulation-wing/sessions
     * SimulationWing oturum listesi.
     */
    public function getSimulationWingSessions(array $params = []): ?array
    {
        return $this->apiGet('/v1/simulation-wing/sessions', $params);
    }

    /* ═══════════════════════════════════════════════════════
     *  Player Compositions — GET /v1/player-compositions (liste)
     * ═══════════════════════════════════════════════════════ */

    /**
     * GET /v1/player-compositions
     * Tüm player composition kayıtları (sayfalı).
     */
    public function getPlayerCompositions(array $params = []): array
    {
        $result = $this->apiGet('/v1/player-compositions', array_merge(['limit' => 100], $params));
        return isset($result['data']) ? $result['data'] : (is_array($result) ? $result : []);
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
