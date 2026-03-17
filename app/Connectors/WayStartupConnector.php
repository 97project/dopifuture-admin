<?php

namespace App\Connectors;

use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Way Startup — Full API Connector
 *
 * Backend: https://way-backend.dopingtech.net
 * Auth: Authorization: Bearer <API_KEY>
 *
 * Members:
 *   POST   /v1/startup/members                → Üye oluştur
 *   GET    /v1/startup/members/by-user/:id    → Üye getir
 *   PATCH  /v1/startup/members/:id            → Üye güncelle
 *   DELETE /v1/startup/members/:id            → Üye sil
 *
 * Simulations (auth genişletme gerekli):
 *   GET /v1/startup/simulations               → Simülasyon listesi
 *   GET /v1/startup/simulations/{id}          → Simülasyon detayı
 *   GET /v1/startup/simulations/with-progress → İlerleme ile birlikte
 *
 * Steps (auth genişletme gerekli):
 *   GET /v1/startup/steps/simulation/{id}     → Simülasyon adımları
 *
 * User Progress (auth genişletme gerekli):
 *   GET /v1/startup/userprogress/member/{memberId}                        → Simülasyon bazlı ilerleme
 *   GET /v1/startup/userprogress/member/{memberId}/simulation/{simId}     → Tek simülasyon ilerlemesi
 *
 * User Step Progress (auth genişletme gerekli):
 *   GET /v1/startup/userstepprogress/member/{memberId}                    → Adım bazlı ilerleme
 *   GET /v1/startup/userstepprogress/member/{memberId}/step/{stepId}      → Tek adım ilerlemesi
 *
 * Health:
 *   GET /health/simple                        → Servis sağlığı
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
     *
     * Request:
     *   { "userId": "1", "name": "Admin User", "email": "admin@x.com", "avatarUrl": "", "points": 0 }
     *
     * Response 201:
     *   {
     *     "id": 213,
     *     "userId": "1",
     *     "name": "Admin User",
     *     "email": "admin@dopifuture.com",
     *     "avatarUrl": "",
     *     "points": 0,
     *     "createdBy": "api",
     *     "createdAt": "2026-02-23T11:03:41.812Z",
     *     "updatedAt": "2026-03-10T12:07:17.000Z"
     *   }
     *
     * Response 400 (duplicate):
     *   { "message": "User already exists", "error": "Bad Request", "statusCode": 400 }
     */
    public function syncUser(User $user): array
    {
        $fullName = trim($user->full_name ?? '') ?: 'Öğrenci';

        $payload = [
            'userId' => (string) $user->id,
            'name' => $fullName,
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
     * DELETE /v1/startup/members/{memberId}
     * Önce by-user'dan member id bulunur, sonra silinir.
     *
     * Response 200/204: (başarılı silme)
     * Response 404: (zaten yok)
     */
    public function removeUser(User $user): bool
    {
        try {
            $member = $this->getUser($user);
            $memberId = $member['id'] ?? null;

            if (!$memberId) {
                Log::channel('daily')->info('[WayStartup] Silinecek üye bulunamadı', ['userId' => $user->id]);
                return true;
            }

            $response = $this->apiDelete("/v1/startup/members/{$memberId}");

            if (in_array($response->status(), [200, 204, 404, 401])) {
                Log::channel('daily')->info('[WayStartup] Üye silindi', [
                    'userId'   => $user->id,
                    'memberId' => $memberId,
                    'status'   => $response->status(),
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
     *
     * Response 200:
     *   {
     *     "id": 213,
     *     "userId": "1",
     *     "name": "Admin User",
     *     "email": "admin@dopifuture.com",
     *     "avatarUrl": "",
     *     "points": 450,
     *     "createdBy": "api",
     *     "createdAt": "2026-02-23T11:03:41.812Z",
     *     "updatedAt": "2026-03-10T12:07:17.000Z"
     *   }
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
            $simulationsWithProgress = [];

            if ($memberId) {
                $progress = $this->getUserProgress($memberId);
                $stepProgress = $this->getUserStepProgress($memberId);
                // Per-user simulation completion percentages
                $simulationsWithProgress = $this->getSimulationsWithProgress($user->id) ?? [];
                if (isset($simulationsWithProgress['data'])) {
                    $simulationsWithProgress = $simulationsWithProgress['data'];
                }
            }

            return [
                'success' => true,
                'data' => [
                    'member' => $member,
                    'member_id' => $memberId,
                    'progress' => $progress,
                    'step_progress' => $stepProgress,
                    'simulations_with_progress' => $simulationsWithProgress,
                    'progress_count' => count($progress),
                    'completed_steps' => collect($stepProgress)->where('status', 'completed')->count(),
                    'total_steps' => count($stepProgress),
                    'simulations_count' => count($simulationsWithProgress),
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

    /* ═══════════════════════════════════════════════════════
     *  Simulations — GET /v1/startup/simulations
     * ═══════════════════════════════════════════════════════ */

    /**
     * GET /v1/startup/simulations
     *
     * Query: ?limit=25&page=1
     *
     * Response 200:
     *   {
     *     "data": [
     *       {
     *         "id": 1,
     *         "name": "SmartClass",
     *         "description": "Eğitim teknolojisi girişimi simülasyonu",
     *         "totalStep": 12,
     *         "iconUrl": "https://way-backend.dopingtech.net/public/icons/edtech.png",
     *         "backgroundColorCode": "#E3F2FD",
     *         "colorCode": "#1565C0",
     *         "createdBy": "admin",
     *         "createdAt": "2026-01-10T08:00:00.000Z",
     *         "updatedAt": "2026-02-15T12:00:00.000Z"
     *       }
     *     ],
     *     "count": 1, "total": 15, "page": 1, "pageCount": 1
     *   }
     */
    public function getSimulations(array $params = []): ?array
    {
        return $this->apiGet('/v1/startup/simulations', $params);
    }

    /**
     * GET /v1/startup/simulations/with-progress
     *
     * Response 200:
     *   {
     *     "data": [
     *       {
     *         "id": 1,
     *         "name": "SmartClass",
     *         "description": "...",
     *         "totalStep": 12,
     *         "iconUrl": "...",
     *         "backgroundColorCode": "#E3F2FD",
     *         "colorCode": "#1565C0",
     *         "progress": {
     *           "memberId": 213,
     *           "currentStep": 6,
     *           "completionPercentage": 50.00,
     *           "status": "in_progress"
     *         },
     *         "createdAt": "2026-01-10T08:00:00.000Z"
     *       }
     *     ]
     *   }
     */
    public function getSimulationsWithProgress(?int $userId = null): ?array
    {
        $params = [];
        if ($userId) {
            $params['userId'] = $userId;
        }
        return $this->apiGet('/v1/startup/simulations/with-progress', $params);
    }

    /**
     * GET /v1/startup/simulations/{id}
     *
     * Response 200: StartupSimulationEntity tek obje
     */
    public function getSimulation(int $id): ?array
    {
        return $this->apiGet("/v1/startup/simulations/{$id}");
    }

    /* ═══════════════════════════════════════════════════════
     *  Steps — GET /v1/startup/steps
     * ═══════════════════════════════════════════════════════ */

    /**
     * GET /v1/startup/steps/simulation/{simulationId}
     *
     * Response 200:
     *   [
     *     {
     *       "id": 1,
     *       "simulationId": 1,
     *       "stepNumber": 1,
     *       "name": "Team Formation & Roles",
     *       "description": "Ekip oluşturma ve rol dağılımı",
     *       "taskDescription": "Ekibinizi oluşturun ve her üyeye rol atayın...",
     *       "suggestedDuration": 30,
     *       "difficulty": "easy",            // easy|medium|hard
     *       "sortOrder": 1,
     *       "isLocked": false,
     *       "iconUrl": "https://...",
     *       "points": 150,
     *       "hasFileUpload": false,
     *       "skill": "Liderlik, İletişim",
     *       "createdBy": "admin",
     *       "createdAt": "2026-01-10T08:00:00.000Z"
     *     }
     *   ]
     */
    public function getSteps(int $simulationId): ?array
    {
        $result = $this->apiGet("/v1/startup/steps/simulation/{$simulationId}");
        return is_array($result) ? $result : [];
    }

    /* ═══════════════════════════════════════════════════════
     *  User Progress — GET /v1/startup/userprogress
     * ═══════════════════════════════════════════════════════ */

    /**
     * GET /v1/startup/userprogress/member/{memberId}
     *
     * Response 200:
     *   [
     *     {
     *       "id": 1,
     *       "memberId": 213,
     *       "simulationId": 1,
     *       "currentStep": 6,
     *       "completionPercentage": 50.00,
     *       "status": "in_progress",         // not_started|in_progress|completed
     *       "createdBy": "system",
     *       "createdAt": "2026-02-25T10:00:00.000Z",
     *       "updatedAt": "2026-03-08T14:00:00.000Z"
     *     }
     *   ]
     */
    public function getUserProgress(int $memberId): ?array
    {
        $result = $this->apiGet("/v1/startup/userprogress/member/{$memberId}");
        return is_array($result) ? $result : [];
    }

    /**
     * GET /v1/startup/userprogress/member/{memberId}/simulation/{simulationId}
     *
     * Response 200: UserProgressEntity tek obje
     * Not: API key auth altında bazı sub-path'lerde 401 verilebilir.
     */
    public function getUserProgressForSimulation(int $memberId, int $simulationId): ?array
    {
        $result = $this->apiGet("/v1/startup/userprogress/member/{$memberId}/simulation/{$simulationId}");
        // Sub-path 401 ise parent path deneyelim ve filtreleyelim
        if ($result === null) {
            $all = $this->getUserProgress($memberId);
            if (is_array($all)) {
                foreach ($all as $p) {
                    if (($p['simulationId'] ?? null) == $simulationId) {
                        return $p;
                    }
                }
            }
        }
        return $result;
    }

    /* ═══════════════════════════════════════════════════════
     *  User Step Progress — GET /v1/startup/userstepprogress
     * ═══════════════════════════════════════════════════════ */

    /**
     * GET /v1/startup/userstepprogress/member/{memberId}
     *
     * Response 200:
     *   [
     *     {
     *       "id": 1,
     *       "memberId": 213,
     *       "stepId": 1,
     *       "status": "completed",           // locked|in_progress|completed
     *       "startedAt": "2026-02-25T10:00:00.000Z",
     *       "completedAt": "2026-02-25T10:30:00.000Z",
     *       "earnedPoint": 150,
     *       "earnedCoin": 50,
     *       "createdBy": "system",
     *       "createdAt": "2026-02-25T10:00:00.000Z"
     *     }
     *   ]
     */
    public function getUserStepProgress(int $memberId): ?array
    {
        $result = $this->apiGet("/v1/startup/userstepprogress/member/{$memberId}");
        return is_array($result) ? $result : [];
    }

    /**
     * GET /v1/startup/userstepprogress/member/{memberId}/step/{stepId}
     *
     * Response 200: UserStepProgressEntity tek obje
     * Not: API key auth altında bazı sub-path'lerde 401 verilebilir.
     */
    public function getUserStepProgressForStep(int $memberId, int $stepId): ?array
    {
        $result = $this->apiGet("/v1/startup/userstepprogress/member/{$memberId}/step/{$stepId}");
        // Sub-path 401 fallback: parent endpoint'ten filtrele
        if ($result === null) {
            $all = $this->getUserStepProgress($memberId);
            if (is_array($all)) {
                foreach ($all as $sp) {
                    if (($sp['stepId'] ?? null) == $stepId) {
                        return $sp;
                    }
                }
            }
        }
        return $result;
    }

    /* ═══════════════════════════════════════════════════════
     *  Step Submissions — GET /v1/startup/step-submissions
     * ═══════════════════════════════════════════════════════ */

    /**
     * GET /v1/startup/step-submissions/simulation/{simulationId}
     *
     * Response 200:
     *   [
     *     {
     *       "id": 1,
     *       "memberId": 213,
     *       "stepId": 3,
     *       "fileName": "business_plan.pdf",
     *       "fileUrl": "https://...",
     *       "fileType": "application/pdf",
     *       "fileSize": 245000,
     *       "linkUrl": "https://canva.com/design/abc",
     *       "linkTitle": "Sunum Tasarımı",
     *       "linkPlatform": "canva",
     *       "textContent": null,
     *       "status": "approved",
     *       "feedback": "İyi bir iş planı hazırlanmış.",
     *       "pointsEarned": 120,
     *       "submittedAt": "2026-02-25T10:00:00.000Z"
     *     }
     *   ]
     */
    public function getStepSubmissions(int $simulationId): array
    {
        // Önce simulation-bazlı endpoint dene
        $result = $this->apiGet("/v1/startup/step-submissions/simulation/{$simulationId}");
        if (is_array($result) && !empty($result)) {
            return $result;
        }
        // Fallback: genel endpoint ile filtrele
        $result = $this->apiGet('/v1/startup/step-submissions', [
            'filter' => "step.simulationId||eq||{$simulationId}",
            'limit' => 200,
        ]);
        if (isset($result['data'])) {
            return $result['data'];
        }
        return is_array($result) ? $result : [];
    }

    /* ═══════════════════════════════════════════════════════
     *  Step Question Evaluations — AI Değerlendirme
     * ═══════════════════════════════════════════════════════ */

    /**
     * GET /v1/startup/step-question-evaluations?filter=memberId||eq||{mid}
     *
     * Response 200:
     *   [
     *     {
     *       "id": 1,
     *       "stepId": 3,
     *       "memberId": 213,
     *       "attempt": 1,
     *       "aiTotalScore": 85,
     *       "aiMaxScore": 100,
     *       "aiCoins": 50,
     *       "aiOverallFeedback": "Harika bir çalışma...",
     *       "status": "EVALUATED"
     *     }
     *   ]
     */
    public function getStepQuestionEvaluations(int $memberId, ?int $stepId = null): array
    {
        $filter = "memberId||eq||{$memberId}";
        if ($stepId) {
            $filter .= "&filter=stepId||eq||{$stepId}";
        }
        $result = $this->apiGet('/v1/startup/step-question-evaluations', [
            'filter' => $filter,
            'limit' => 200,
        ]);
        if (isset($result['data'])) {
            return $result['data'];
        }
        return is_array($result) ? $result : [];
    }

    /* ═══════════════════════════════════════════════════════
     *  Step Questions — Soru Metni + Max Skor
     * ═══════════════════════════════════════════════════════ */

    /**
     * GET /v1/startup/step-questions?filter=stepId||eq||{stepId}
     *
     * Response 200:
     *   [
     *     {
     *       "id": 1,
     *       "stepId": 3,
     *       "questionText": "İş modelinizi açıklayın...",
     *       "maxScore": 20,
     *       "sortOrder": 1,
     *       "isRequired": true
     *     }
     *   ]
     */
    public function getStepQuestions(int $stepId): array
    {
        $result = $this->apiGet('/v1/startup/step-questions', [
            'filter' => "stepId||eq||{$stepId}",
            'limit' => 50,
        ]);
        if (isset($result['data'])) {
            return $result['data'];
        }
        return is_array($result) ? $result : [];
    }

    /* ═══════════════════════════════════════════════════════
     *  Step Tools + Tools — Araç Tavsiyeleri
     * ═══════════════════════════════════════════════════════ */

    /**
     * GET /v1/startup/step-tools?filter=stepId||eq||{stepId}
     *
     * Response 200:
     *   [
     *     {
     *       "id": 1,
     *       "stepId": 3,
     *       "toolId": 5,
     *       "isRecommended": true,
     *       "customNote": "Sunum için kullanın",
     *       "tool": {
     *         "id": 5,
     *         "name": "Canva",
     *         "description": "Grafik tasarım aracı",
     *         "iconUrl": "https://...",
     *         "websiteUrl": "https://canva.com",
     *         "category": "design",
     *         "toolType": "web"
     *       }
     *     }
     *   ]
     */
    public function getStepTools(int $stepId): array
    {
        $result = $this->apiGet('/v1/startup/step-tools', [
            'filter' => "stepId||eq||{$stepId}",
            'limit' => 50,
        ]);
        if (isset($result['data'])) {
            return $result['data'];
        }
        return is_array($result) ? $result : [];
    }

    /**
     * GET /v1/startup/tools
     *
     * Tüm araçlar listesi (tool reference table).
     */
    public function getTools(): array
    {
        $result = $this->apiGet('/v1/startup/tools', ['limit' => 100]);
        if (isset($result['data'])) {
            return $result['data'];
        }
        return is_array($result) ? $result : [];
    }

    /* ═══════════════════════════════════════════════════════
     *  Members — GET /v1/startup/members
     * ═══════════════════════════════════════════════════════ */

    /**
     * GET /v1/startup/members/by-user/{userId}
     *
     * Response 200:
     *   {
     *     "id": 213,
     *     "userId": "42",
     *     "name": "Ali",
     *     "email": "ali@okul.com",
     *     "avatarUrl": null,
     *     "points": 450
     *   }
     */
    public function getMemberByUserId(string $userId): ?array
    {
        $result = $this->apiGet("/v1/startup/members/by-user/{$userId}");
        return is_array($result) ? $result : null;
    }

    /**
     * GET /v1/startup/members/{id}
     */
    public function getMember(int $memberId): ?array
    {
        return $this->apiGet("/v1/startup/members/{$memberId}");
    }
}
