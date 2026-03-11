<?php

namespace App\Services;

use App\Models\Application;
use App\Models\AppUserData;
use App\Models\AppUserProgress;
use App\Models\AppUserSession;
use App\Models\User;
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
                'WayStartupConnector' => $this->parseWayStartupData($user, $app, $data),
                'VegaConnector'       => $this->parseVegaData($user, $app, $data),
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
    private function parseWayStartupData(User $user, Application $app, array $data): void
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

        // Simulations with per-user progress (completion %)
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
    }

    /**
     * Vega: sessions → app_user_sessions (lecturer/simulator types)
     */
    private function parseVegaData(User $user, Application $app, array $data): void
    {
        foreach (($data['sessions'] ?? []) as $session) {
            $module = $session['module'] ?? 'unknown';

            AppUserSession::updateOrCreate(
                [
                    'user_id'             => $user->id,
                    'application_id'      => $app->id,
                    'external_session_id' => $session['id'] ?? $session['sessionId'] ?? null,
                ],
                [
                    'session_type'     => $module, // lecturer, simulator, chatbot
                    'session_name'     => $session['title'] ?? $session['name'] ?? null,
                    'started_at'       => isset($session['startedAt']) ? \Carbon\Carbon::parse($session['startedAt']) : null,
                    'ended_at'         => isset($session['endedAt']) ? \Carbon\Carbon::parse($session['endedAt']) : null,
                    'duration_seconds' => $session['duration'] ?? $session['durationSeconds'] ?? null,
                    'score'            => $session['score'] ?? null,
                    'metadata'         => $session,
                ]
            );

            // Her oturumu progress olarak da kaydet
            AppUserProgress::updateOrCreate(
                [
                    'user_id'        => $user->id,
                    'application_id' => $app->id,
                    'module_type'    => $module,
                    'module_id'      => $session['id'] ?? $session['sessionId'] ?? null,
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
