<?php

namespace App\Services;

use App\Connectors\VegaConnector;
use App\Connectors\WayStartupConnector;
use App\Models\Application;
use App\Models\AppUserData;
use App\Models\AppUserProgress;
use App\Models\AppUserSession;
use App\Models\User;
use App\Models\VegaSession;
use App\Models\VegaSessionMessage;
use App\Models\WsMember;
use Illuminate\Support\Facades\Log;

/**
 * Connector verilerini çekip DB'ye normalize eden servis.
 * Her connector'ın getUserReport() çıktısını parse eder.
 */
class ConnectorSyncService
{
    /**
     * Tek kullanıcı, tek uygulama sync.
     */
    public function syncUserData(User $user, Application $app): bool
    {
        $connector = $app->getConnector();
        if (!$connector) {
            return false;
        }

        try {
            $report = $connector->getUserReport($user);
            if (!$report || !($report['success'] ?? false)) {
                Log::channel('daily')->warning('[ConnectorSync] Report failed', [
                    'user_id' => $user->id,
                    'app'     => $app->slug,
                    'error'   => $report['error'] ?? 'unknown',
                ]);
                return false;
            }

            $data = $report['data'] ?? [];

            // 1. Ham veriyi app_user_data'ya kaydet
            $externalId = $data['player_id'] ?? $data['member_id'] ?? $data['vega_id'] ?? null;
            AppUserData::updateOrCreate(
                ['user_id' => $user->id, 'application_id' => $app->id],
                [
                    'connector_type'   => class_basename($connector),
                    'external_user_id' => $externalId,
                    'external_data'    => $data,
                    'synced_at'        => now(),
                ]
            );

            // 2. Connector tipine göre normalize et
            $connectorClass = class_basename($connector);
            match ($connectorClass) {
                'MissionWayConnector' => $this->parseMissionWayData($user, $app, $data),
                'WayStartupConnector' => $this->parseWayStartupData($user, $app, $data, $connector),
                'VegaConnector'       => $this->parseVegaData($user, $app, $data, $connector),
                default               => null,
            };

            return true;
        } catch (\Throwable $e) {
            Log::channel('daily')->error('[ConnectorSync] Exception', [
                'user_id' => $user->id,
                'app'     => $app->slug,
                'message' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Bir uygulamadaki tüm kullanıcıları sync et.
     */
    public function syncAllUsersForApp(Application $app): array
    {
        $users = $app->users;
        $results = ['success' => 0, 'failed' => 0, 'total' => $users->count()];

        foreach ($users as $user) {
            if ($this->syncUserData($user, $app)) {
                $results['success']++;
            } else {
                $results['failed']++;
            }
        }

        return $results;
    }

    /**
     * Bir kullanıcının tüm uygulamalarını sync et.
     */
    public function syncAllAppsForUser(User $user): array
    {
        $apps = $user->applications()->active()->get();
        $results = ['success' => 0, 'failed' => 0, 'total' => $apps->count()];

        foreach ($apps as $app) {
            if ($this->syncUserData($user, $app)) {
                $results['success']++;
            } else {
                $results['failed']++;
            }
        }

        return $results;
    }

    /* ──────────────────────────────────────────────────
     * Connector-Specific Parsers
     * ────────────────────────────────────────────────── */

    /**
     * MissionWay: sessions → app_user_sessions, progress → app_user_progress
     */
    private function parseMissionWayData(User $user, Application $app, array $data): void
    {
        // Sessions
        foreach (($data['sessions'] ?? []) as $session) {
            AppUserSession::updateOrCreate(
                [
                    'user_id'             => $user->id,
                    'application_id'      => $app->id,
                    'external_session_id' => $session['id'] ?? $session['sessionId'] ?? null,
                ],
                [
                    'session_type'     => 'simulation',
                    'session_name'     => $session['simulationName'] ?? $session['name'] ?? null,
                    'started_at'       => isset($session['startedAt']) ? \Carbon\Carbon::parse($session['startedAt']) : null,
                    'ended_at'         => isset($session['endedAt']) ? \Carbon\Carbon::parse($session['endedAt']) : null,
                    'duration_seconds' => $session['durationSeconds'] ?? $session['duration'] ?? null,
                    'score'            => $session['score'] ?? null,
                    'metadata'         => $session,
                ]
            );
        }

        // Progress records
        foreach (($data['progress'] ?? []) as $prog) {
            AppUserProgress::updateOrCreate(
                [
                    'user_id'        => $user->id,
                    'application_id' => $app->id,
                    'module_type'    => 'simulation',
                    'module_id'      => $prog['id'] ?? $prog['simulationId'] ?? null,
                ],
                [
                    'module_name'      => $prog['simulationName'] ?? $prog['name'] ?? null,
                    'status'           => $this->normalizeStatus($prog['status'] ?? null),
                    'score'            => $prog['score'] ?? null,
                    'max_score'        => $prog['maxScore'] ?? 100,
                    'duration_seconds' => $prog['totalDuration'] ?? null,
                    'attempts'         => $prog['attempts'] ?? $prog['playCount'] ?? 0,
                    'started_at'       => isset($prog['startedAt']) ? \Carbon\Carbon::parse($prog['startedAt']) : null,
                    'completed_at'     => isset($prog['completedAt']) ? \Carbon\Carbon::parse($prog['completedAt']) : null,
                    'metadata'         => $prog,
                ]
            );
        }
    }

    /**
     * WayStartup: progress → app_user_progress, step_progress → app_user_progress (step type)
     */
    private function parseWayStartupData(User $user, Application $app, array $data, $connector = null): void
    {
        // Simulation progress
        foreach (($data['progress'] ?? []) as $prog) {
            AppUserProgress::updateOrCreate(
                [
                    'user_id'        => $user->id,
                    'application_id' => $app->id,
                    'module_type'    => 'simulation',
                    'module_id'      => $prog['simulationId'] ?? $prog['id'] ?? null,
                ],
                [
                    'module_name'      => $prog['simulationName'] ?? $prog['name'] ?? null,
                    'status'           => $this->normalizeStatus($prog['status'] ?? null),
                    'score'            => $prog['score'] ?? null,
                    'max_score'        => $prog['maxScore'] ?? 100,
                    'duration_seconds' => $prog['duration'] ?? null,
                    'attempts'         => $prog['attempts'] ?? 0,
                    'started_at'       => isset($prog['startedAt']) ? \Carbon\Carbon::parse($prog['startedAt']) : null,
                    'completed_at'     => isset($prog['completedAt']) ? \Carbon\Carbon::parse($prog['completedAt']) : null,
                    'metadata'         => $prog,
                ]
            );
        }

        // Step progress
        foreach (($data['step_progress'] ?? []) as $step) {
            AppUserProgress::updateOrCreate(
                [
                    'user_id'        => $user->id,
                    'application_id' => $app->id,
                    'module_type'    => 'step',
                    'module_id'      => $step['stepId'] ?? $step['id'] ?? null,
                ],
                [
                    'module_name'  => $step['stepName'] ?? $step['name'] ?? null,
                    'status'       => $this->normalizeStatus($step['status'] ?? null),
                    'score'        => $step['score'] ?? null,
                    'max_score'    => $step['maxScore'] ?? null,
                    'completed_at' => isset($step['completedAt']) ? \Carbon\Carbon::parse($step['completedAt']) : null,
                    'metadata'     => $step,
                ]
            );
        }

        // Simulations with per-user progress
        foreach (($data['simulations_with_progress'] ?? []) as $sim) {
            $simProgress = $sim['progress'] ?? null;
            AppUserProgress::updateOrCreate(
                [
                    'user_id'        => $user->id,
                    'application_id' => $app->id,
                    'module_type'    => 'simulation_overview',
                    'module_id'      => $sim['id'] ?? null,
                ],
                [
                    'module_name'      => $sim['name'] ?? null,
                    'status'           => $this->normalizeStatus($simProgress['status'] ?? null),
                    'score'            => $simProgress['completionPercentage'] ?? null,
                    'max_score'        => 100,
                    'attempts'         => $simProgress['currentStep'] ?? 0,
                    'metadata'         => $sim,
                ]
            );
        }

        // ── WsMember: üye bilgisi + step_progress + evaluations + submissions ──
        if ($connector instanceof WayStartupConnector) {
            try {
                $memberData = $connector->getMemberByUserId((string) $user->id);
                $memberId = $memberData['id'] ?? null;

                if ($memberId) {
                    $stepProgress = [];
                    try { $stepProgress = $connector->getUserStepProgress($memberId); } catch (\Throwable $e) {}

                    $evaluations = [];
                    try { $evaluations = $connector->getStepQuestionEvaluations($memberId); } catch (\Throwable $e) {}

                    // Step submissions — tüm simülasyonlar için
                    $submissions = [];
                    foreach (($data['progress'] ?? []) as $prog) {
                        $simId = $prog['simulationId'] ?? $prog['id'] ?? null;
                        if ($simId) {
                            try {
                                $subs = $connector->getStepSubmissions($simId);
                                if (is_array($subs)) $submissions = array_merge($submissions, $subs);
                            } catch (\Throwable $e) {}
                        }
                    }

                    WsMember::updateOrCreate(
                        ['user_id' => $user->id, 'application_id' => $app->id],
                        [
                            'external_id'      => $memberId,
                            'points'           => $memberData['points'] ?? $memberData['totalPoints'] ?? 0,
                            'step_progress'    => is_array($stepProgress) ? $stepProgress : [],
                            'step_evaluations' => is_array($evaluations) ? $evaluations : [],
                            'step_submissions' => $submissions,
                            'synced_at'        => now(),
                        ]
                    );
                }
            } catch (\Throwable $e) {
                Log::channel('daily')->warning('[ConnectorSync] WsMember sync failed', [
                    'user_id' => $user->id, 'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Vega: sessions → app_user_sessions (lecturer/simulator types)
     */
    private function parseVegaData(User $user, Application $app, array $data, $connector = null): void
    {
        foreach (($data['sessions'] ?? []) as $session) {
            $module = $session['module'] ?? 'unknown';
            $extSessionId = $session['id'] ?? $session['sessionId'] ?? null;

            AppUserSession::updateOrCreate(
                [
                    'user_id'             => $user->id,
                    'application_id'      => $app->id,
                    'external_session_id' => $extSessionId,
                ],
                [
                    'session_type'     => $module,
                    'session_name'     => $session['title'] ?? $session['name'] ?? null,
                    'started_at'       => isset($session['startedAt']) ? \Carbon\Carbon::parse($session['startedAt']) : null,
                    'ended_at'         => isset($session['endedAt']) ? \Carbon\Carbon::parse($session['endedAt']) : null,
                    'duration_seconds' => $session['duration'] ?? $session['durationSeconds'] ?? null,
                    'score'            => $session['score'] ?? null,
                    'metadata'         => $session,
                ]
            );

            // Progress kaydı
            AppUserProgress::updateOrCreate(
                [
                    'user_id'        => $user->id,
                    'application_id' => $app->id,
                    'module_type'    => $module,
                    'module_id'      => $extSessionId,
                ],
                [
                    'module_name'      => $session['title'] ?? $session['name'] ?? null,
                    'status'           => 'completed',
                    'score'            => $session['score'] ?? null,
                    'duration_seconds' => $session['duration'] ?? $session['durationSeconds'] ?? null,
                    'completed_at'     => isset($session['endedAt']) ? \Carbon\Carbon::parse($session['endedAt']) : null,
                    'metadata'         => $session,
                ]
            );

            // ── VegaSession + Messages: oturum detaylarını kalıcı tablolara yaz ──
            if ($connector instanceof VegaConnector && $extSessionId) {
                try {
                    // Module'e göre doğru detay çek
                    $detailModule = match ($module) {
                        'lecturer' => 'lecturer',
                        'simulator' => 'simulator',
                        default => 'all',
                    };
                    $detail = $connector->getSessionDetail((string) $extSessionId, $detailModule);

                    if ($detail) {
                        $durationMin = null;
                        if (!empty($detail['startedAt']) && !empty($detail['endedAt'])) {
                            $start = \Carbon\Carbon::parse($detail['startedAt']);
                            $end = \Carbon\Carbon::parse($detail['endedAt']);
                            $durationMin = (int) $start->diffInMinutes($end);
                        } elseif (!empty($detail['duration'])) {
                            $durationMin = (int) $detail['duration'];
                        }

                        $vegaSession = VegaSession::updateOrCreate(
                            ['external_id' => (string) $extSessionId],
                            [
                                'user_id'          => $user->id,
                                'application_id'   => $app->id,
                                'module'           => $module,
                                'user_name'        => $detail['userName'] ?? $detail['user']['name'] ?? $user->name,
                                'user_surname'     => $detail['userSurname'] ?? $detail['user']['surname'] ?? $user->surname ?? '',
                                'score'            => $detail['score'] ?? $session['score'] ?? null,
                                'duration_minutes' => $durationMin,
                                'summary'          => ($module === 'simulator') ? ($detail['data'] ?? $detail) : null,
                                'started_at'       => isset($detail['startedAt']) ? \Carbon\Carbon::parse($detail['startedAt']) : null,
                                'ended_at'         => isset($detail['endedAt']) ? \Carbon\Carbon::parse($detail['endedAt']) : null,
                                'synced_at'        => now(),
                            ]
                        );

                        // Mesajları kaydet
                        $messages = $detail['messages'] ?? $detail['history'] ?? [];
                        if ($module === 'simulator') {
                            $messages = $detail['data']['turns'] ?? $detail['data']['steps'] ?? $messages;
                        }
                        // Eski mesajları sil, yenileri yaz
                        VegaSessionMessage::where('session_id', $vegaSession->id)->delete();

                        foreach ($messages as $idx => $msg) {
                            $role = $msg['role'] ?? $msg['sender'] ?? 'user';
                            VegaSessionMessage::create([
                                'session_id'  => $vegaSession->id,
                                'role'        => $role,
                                'content'     => $msg['content'] ?? $msg['text'] ?? null,
                                'question'    => $msg['question'] ?? null,
                                'answer'      => $msg['studentAnswer'] ?? $msg['answer'] ?? null,
                                'score'       => $msg['score'] ?? null,
                                'max_score'   => $msg['maxScore'] ?? null,
                                'feedback'    => $msg['aiFeedback'] ?? $msg['feedback'] ?? null,
                                'metrics'     => $msg['healthMetric'] ?? $msg['health'] ?? null
                                    ? [
                                        'health'     => $msg['healthMetric'] ?? $msg['health'] ?? 0,
                                        'resource'   => $msg['resourceMetric'] ?? $msg['resource'] ?? 0,
                                        'ethics'     => $msg['ethicsMetric'] ?? $msg['ethics'] ?? 0,
                                        'adaptation' => $msg['adaptationMetric'] ?? $msg['adaptation'] ?? 0,
                                    ] : null,
                                'options'     => $msg['options'] ?? null,
                                'order_index' => $idx,
                            ]);
                        }
                    }
                } catch (\Throwable $e) {
                    Log::channel('daily')->warning('[ConnectorSync] VegaSession detail sync failed', [
                        'session_id' => $extSessionId, 'error' => $e->getMessage(),
                    ]);
                }
            }
        }
    }

    /* ── Helpers ─────────────────────────────────────── */

    private function normalizeStatus(?string $raw): string
    {
        if (!$raw) {
            return 'not_started';
        }

        $raw = strtolower(trim($raw));

        return match (true) {
            in_array($raw, ['completed', 'done', 'finished', 'passed']) => 'completed',
            in_array($raw, ['in_progress', 'started', 'active', 'playing']) => 'in_progress',
            default => 'not_started',
        };
    }
}
