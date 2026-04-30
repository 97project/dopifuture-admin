<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\Vega\VegaDbChatMessage;
use App\Models\Vega\VegaDbLecturerMessage;
use App\Models\Vega\VegaDbSession;
use App\Models\Vega\VegaDbUser;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Report data service for Vega remote DB.
 * Maps Panel26 users to Vega users by email,
 * then queries the vega_db connection for real data.
 */
class VegaReportService
{
    /**
     * Maps a collection of Panel26 user IDs to Vega user IDs.
     *
     * @param  Collection  $panelUserIds  Panel26 user IDs
     * @return array  [panel_user_id => vega_user_id, ...]
     */
    public function resolveVegaUserIds(Collection $panelUserIds): array
    {
        if ($panelUserIds->isEmpty()) {
            return [];
        }

        $cacheKey = 'vega_user_map_' . md5($panelUserIds->sort()->implode(','));

        return Cache::remember($cacheKey, 300, function () use ($panelUserIds) {
            // Fetch Panel26 user emails
            $panelUsers = User::whereIn('id', $panelUserIds)
                ->whereNotNull('email')
                ->pluck('email', 'id');

            if ($panelUsers->isEmpty()) {
                return [];
            }

            // Map emails to Vega IDs
            $vegaMap = VegaDbUser::mapEmailsToIds($panelUsers->values()->toArray());

            // panel_user_id => vega_user_id
            $result = [];
            foreach ($panelUsers as $panelId => $email) {
                if (isset($vegaMap[$email])) {
                    $result[$panelId] = (string) $vegaMap[$email];
                }
            }

            return $result;
        });
    }

    /**
     * Resolves a single Panel26 user to their Vega user ID.
     * If cache returns null (user not found), bypasses cache for a direct lookup
     * to handle recently synced users.
     */
    public function resolveVegaUserId(int $panelUserId): ?string
    {
        $map = $this->resolveVegaUserIds(collect([$panelUserId]));
        $result = $map[$panelUserId] ?? null;

        // Cache may contain stale "not found" — bypass with direct DB lookup
        if ($result === null) {
            $email = User::where('id', $panelUserId)->value('email');
            if ($email) {
                $vegaMap = VegaDbUser::mapEmailsToIds([$email]);
                $result = isset($vegaMap[$email]) ? (string) $vegaMap[$email] : null;

                // If found via direct lookup, invalidate cache so next batch includes this user
                if ($result !== null) {
                    $this->clearVegaUserCache();
                }
            }
        }

        return $result;
    }

    /**
     * Vega kullanıcı eşleşme cache'ini temizler.
     * Yeni kullanıcı sync'i sonrası çağrılmalıdır.
     */
    public function clearVegaUserCache(): void
    {
        // Pattern-based cache flush for all vega_user_map keys
        // Since we use md5-based keys, we clear all matching pattern
        $cacheStore = Cache::getStore();
        if (method_exists($cacheStore, 'flush')) {
            // For array/file drivers — flush is too aggressive, use tags or specific keys
        }

        // Targeted approach: clear all keys starting with vega_user_map_
        // For Redis/Memcached this uses tags; for file cache we forget known patterns
        try {
            // Flush all cached user maps — they'll be rebuilt on next request
            $prefix = 'vega_user_map_';
            if ($cacheStore instanceof \Illuminate\Cache\FileStore) {
                // File store doesn't support pattern deletion, clear entire cache section
                Cache::flush();
            } elseif (method_exists($cacheStore, 'connection')) {
                // Redis — use pattern scan
                $connection = $cacheStore->connection();
                $cachePrefix = config('cache.prefix', '') . ':';
                $keys = $connection->keys($cachePrefix . $prefix . '*');
                if (!empty($keys)) {
                    $connection->del($keys);
                }
            } else {
                // Fallback — clear entire cache
                Cache::flush();
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('[VegaReportService] Cache clear failed: ' . $e->getMessage());
            // Non-critical — cache will expire naturally in 5 minutes
        }
    }

    /* ─── Role Galaxy (simulator) ────────────────────── */

    /**
     * Generates Role Galaxy report data.
     *
     * @param  array   $vegaUserMap  [panel_user_id => vega_user_id]
     * @param  Collection  $panelUsers  Panel26 User models (id keyed)
     * @return array  All variables expected by the view
     */
    public function getRoleGalaxyReport(array $vegaUserMap, Collection $panelUsers): array
    {
        $vegaUserIds = array_values($vegaUserMap);

        if (empty($vegaUserIds)) {
            return $this->emptyReportData();
        }

        // Overall statistics — eager-load steps for duration computation
        $sessions = VegaDbSession::simulator()
            ->forUsers($vegaUserIds)
            ->with('simulatorSteps:id,session_id,created_at')
            ->get();

        $totalSessions = $sessions->count();
        $completedSessions = $sessions->where('status', 'COMPLETED')->count();
        $totalDuration = $sessions->sum(fn($s) => $s->duration_seconds);
        $avgScore = (float) $sessions->whereNotNull('score')->avg('score');

        // Per-user stats
        $perUser = $sessions->groupBy('user_id');
        $avgJoin = $perUser->count() > 0
            ? round($perUser->map->count()->avg())
            : 0;
        $avgDuration = $totalSessions > 0
            ? round($totalDuration / $totalSessions)
            : 0;

        // Scenario config — matching mobile RoleGalaxyScreen.js 12 scenarios
        $scenarioConfig = [
            'village_life_v1'     => ['icon' => '🏡', 'color' => '#F472B6', 'category' => 'community'],
            'world_traveler_v1'   => ['icon' => '✈️', 'color' => '#A78BFA', 'category' => 'language'],
            'novaris_v1'          => ['icon' => '🚀', 'color' => '#22D3EE', 'category' => 'technology'],
            'biolab_v1'           => ['icon' => '🔬', 'color' => '#94A3B8', 'category' => 'science'],
            'what_if_v1'          => ['icon' => '🧠', 'color' => '#FB923C', 'category' => 'criticalThinking'],
            'lost_egg_v1'         => ['icon' => '🌿', 'color' => '#4ADE80', 'category' => 'nature'],
            'balance_garden_v1'   => ['icon' => '🌱', 'color' => '#34D399', 'category' => 'wellBeing'],
            'plan_tomorrow_v1'    => ['icon' => '🌟', 'color' => '#60A5FA', 'category' => 'future'],
            'heart_bridge_v1'     => ['icon' => '❤️', 'color' => '#F87171', 'category' => 'emotional'],
            'dream_workshop_v1'   => ['icon' => '🎨', 'color' => '#FB7185', 'category' => 'art'],
            'trace_center_v1'     => ['icon' => '📌', 'color' => '#818CF8', 'category' => 'criticalThinking2'],
            'movement_island_v1'  => ['icon' => '🏃', 'color' => '#FBBF24', 'category' => 'bodyMovement'],
        ];

        $userStats = collect();
        $vegaIdToPanelId = array_flip($vegaUserMap);

        foreach ($perUser as $vegaUserId => $userSessions) {
            $panelUserId = $vegaIdToPanelId[$vegaUserId] ?? null;
            $panelUser = $panelUserId ? ($panelUsers[$panelUserId] ?? null) : null;
            if (!$panelUser) continue;

            $userTotalDuration = $userSessions->sum(fn($s) => $s->duration_seconds);
            $lastSession = $userSessions->sortByDesc('created_at')->first();
            $lastInteraction = $lastSession?->created_at?->diffForHumans();

            // Last 5 scenario icons
            $lastScenarios = $userSessions->sortByDesc('created_at')
                ->take(5)
                ->map(fn($s) => $scenarioConfig[$s->scenario]['icon'] ?? '🌟')
                ->values()
                ->toArray();

            $userStats->push([
                'user'             => $panelUser,
                'last_interaction' => $lastInteraction ?? '-',
                'total_joined'     => $userSessions->count(),
                'total_duration'   => $userTotalDuration,
                'last_scenarios'   => $lastScenarios,
                // Performance tab fields
                'total'            => $userSessions->count(),
                'completed'        => $userSessions->where('status', 'COMPLETED')->count(),
                'completion_rate'  => $userSessions->count() > 0
                    ? round(($userSessions->where('status', 'COMPLETED')->count() / $userSessions->count()) * 100, 1)
                    : 0,
                'avg_score'        => $userSessions->whereNotNull('score')->avg('score'),
                'galaxy_selected'  => $userSessions->pluck('scenario')->unique()->map(fn($s) => ucfirst($s ?? ''))->implode(', '),
                'role_played'      => $userSessions->where('status', 'COMPLETED')->count() . ' session',
            ]);
        }

        return [
            'total_progress'   => $totalSessions,
            'total_completed'  => $completedSessions,
            'total_sessions'   => $totalSessions,
            'total_duration'   => $totalDuration,
            'avg_score'        => $avgScore ? round($avgScore, 1) : null,
            'avg_galaxy_join'  => $avgJoin,
            'avg_duration_sec' => $avgDuration,
            'user_stats'       => $userStats,
            'module_stats'     => collect(),
            'sessions_by_day'  => $this->getSessionsByDay($vegaUserIds, 'simulator'),
            'recent_sessions'  => collect(),
        ];
    }

    /* ─── Way AI Coach (chatbot) ─────────────────────── */

    /**
     * Generates Way AI Coach report data.
     *
     * Mobil uygulama eşleşmesi: WAY AI Coach = chatbot modülü
     * (genel yapay zeka sohbet asistanı)
     */
    public function getWayAiCoachReport(array $vegaUserMap, Collection $panelUsers): array
    {
        $vegaUserIds = array_values($vegaUserMap);

        if (empty($vegaUserIds)) {
            return $this->emptyReportData();
        }

        $sessions = VegaDbSession::chatbot()
            ->forUsers($vegaUserIds)
            ->withCount('chatMessages')
            ->with('chatMessages:id,session_id,created_at')
            ->get();

        $totalSessions = $sessions->count();
        $totalDuration = $sessions->sum(fn($s) => $s->duration_seconds);
        $totalMessages = $sessions->sum('chat_messages_count');

        // Per-user stats
        $perUser = $sessions->groupBy('user_id');
        $vegaIdToPanelId = array_flip($vegaUserMap);

        // Average interaction and duration (for alert threshold)
        $avgInteractionPerUser = $perUser->count() > 0
            ? $perUser->map(fn($items) => $items->sum('chat_messages_count'))->avg()
            : 0;

        $userStats = collect();

        foreach ($perUser as $vegaUserId => $userSessions) {
            $panelUserId = $vegaIdToPanelId[$vegaUserId] ?? null;
            $panelUser = $panelUserId ? ($panelUsers[$panelUserId] ?? null) : null;
            if (!$panelUser) continue;

            $userInteractions = $userSessions->sum('chat_messages_count');
            $userDuration = $userSessions->sum(fn($s) => $s->duration_seconds);

            // Alert: flag low-interaction users
            $isAlert = $userInteractions < max(1, $avgInteractionPerUser * 0.3);

            $userStats->push([
                'user'              => $panelUser,
                'interaction_count' => $userInteractions,
                'total_duration'    => $userDuration,
                'alert'             => $isAlert,
                // Performance tab fields
                'total'             => $userInteractions,
                'completed'         => $userSessions->where('status', 'COMPLETED')->count(),
                'completion_rate'   => $userSessions->count() > 0
                    ? round(($userSessions->where('status', 'COMPLETED')->count() / $userSessions->count()) * 100, 1)
                    : 0,
                'avg_score'         => null,
            ]);
        }

        return [
            'total_progress'   => $totalMessages,
            'total_completed'  => $sessions->where('status', 'COMPLETED')->count(),
            'total_sessions'   => $totalSessions,
            'total_duration'   => $totalDuration,
            'avg_score'        => null,
            'user_stats'       => $userStats,
            'module_stats'     => collect(),
            'sessions_by_day'  => $this->getSessionsByDay($vegaUserIds, 'chatbot'),
            'recent_sessions'  => collect(),
        ];
    }

    /* ─── Study Space (lecturer) ─────────────────────── */

    /**
     * Generates Study Space report data.
     *
     * Mobil uygulama eşleşmesi: Study Space = lecturer modülü
     * (ders/konu bazlı çalışma — subject, topic, theme bilgileri içerir)
     * POST /lecturer/start → "Start Study Space lecturer"
     */
    public function getStudySpaceReport(array $vegaUserMap, Collection $panelUsers): array
    {
        $vegaUserIds = array_values($vegaUserMap);

        if (empty($vegaUserIds)) {
            return $this->emptyReportData();
        }

        $sessions = VegaDbSession::lecturer()
            ->forUsers($vegaUserIds)
            ->withCount('lecturerMessages')
            ->with('lecturerMessages:id,session_id,created_at,score')
            ->get();

        $totalSessions = $sessions->count();
        $totalDuration = $sessions->sum(fn($s) => $s->duration_seconds);
        $totalMessages = $sessions->sum('lecturer_messages_count');

        // Per-user stats — ders/konu bazlı gruplandırma
        $perUser = $sessions->groupBy('user_id');
        $vegaIdToPanelId = array_flip($vegaUserMap);

        $userStats = collect();

        foreach ($perUser as $vegaUserId => $userSessions) {
            $panelUserId = $vegaIdToPanelId[$vegaUserId] ?? null;
            $panelUser = $panelUserId ? ($panelUsers[$panelUserId] ?? null) : null;
            if (!$panelUser) continue;

            $userDuration = $userSessions->sum(fn($s) => $s->duration_seconds);
            $userDurationMinutes = (int) round($userDuration / 60);
            $userMessages = $userSessions->sum('lecturer_messages_count');

            // Konu bazlı dağılım (hangi dersleri çalıştığı)
            $subjects = $userSessions->pluck('subject')->filter()->unique()->values()->toArray();
            $topics = $userSessions->pluck('topic')->filter()->unique()->values()->toArray();
            $themes = $userSessions->pluck('theme')->filter()->unique()->values()->toArray();

            // Empathy/puan ortalaması — lecturer mesaj puanları
            $sessionIds = $userSessions->pluck('id');
            $userAvgScore = VegaDbLecturerMessage::whereIn('session_id', $sessionIds)
                ->whereNotNull('score')
                ->avg('score');

            $userStats->push([
                'user'               => $panelUser,
                'discussion_minutes' => $userDurationMinutes,
                'discussion_count'   => $userSessions->count(),
                'message_count'      => $userMessages,
                'subjects'           => $subjects,
                'topics'             => $topics,
                'themes'             => $themes,
                'avg_score'          => $userAvgScore ? round((float) $userAvgScore, 1) : null,
                // Performance tab fields
                'total'              => $userSessions->count(),
                'completed'          => $userSessions->where('status', 'COMPLETED')->count(),
                'completion_rate'    => $userSessions->count() > 0
                    ? round(($userSessions->where('status', 'COMPLETED')->count() / $userSessions->count()) * 100, 1)
                    : 0,
                'total_duration'     => $userDuration,
            ]);
        }

        // Ortalama ders süresi (dakika)
        $avgLessonTime = $totalSessions > 0
            ? round($totalDuration / $totalSessions / 60, 1)
            : 0;

        // Genel empathy score
        $empathyScore = VegaDbLecturerMessage::whereIn(
            'session_id',
            $sessions->pluck('id')
        )->whereNotNull('score')->avg('score');

        return [
            'total_progress'   => $totalSessions,
            'total_completed'  => $sessions->where('status', 'COMPLETED')->count(),
            'total_sessions'   => $totalSessions,
            'total_duration'   => $totalDuration,
            'avg_score'        => $empathyScore ? round((float) $empathyScore, 1) : $avgLessonTime,
            'user_stats'       => $userStats,
            'module_stats'     => collect(),
            'sessions_by_day'  => $this->getSessionsByDay($vegaUserIds, 'lecturer'),
            'recent_sessions'  => collect(),
        ];
    }

    /* ─── Coach Feedback ─────────────────────────────── */

    /**
     * Generates question/answer/score/feedback data from
     * a user's WAY AI Coach chatbot sessions.
     *
     * Mobil eşleşme: WAY AI Coach = chatbot modülü
     * User messages are treated as questions,
     * assistant messages as feedback.
     */
    public function getCoachFeedback(?string $vegaUserId): Collection
    {
        if (!$vegaUserId) {
            return collect();
        }

        // Fetch all chatbot sessions for the user (WAY AI Coach = chatbot)
        $sessions = VegaDbSession::chatbot()
            ->forUser($vegaUserId)
            ->orderByDesc('created_at')
            ->get();

        if ($sessions->isEmpty()) {
            return collect();
        }

        // Fetch all messages, ordered
        $allMessages = VegaDbChatMessage::whereIn('session_id', $sessions->pluck('id'))
            ->orderBy('session_id')
            ->orderBy('created_at')
            ->get();

        // Pair user messages as questions, assistant messages as feedback
        $questions = collect();
        $pendingQuestion = null;

        foreach ($allMessages as $msg) {
            if ($msg->role === 'user') {
                // If previous question was left unanswered, add it
                if ($pendingQuestion !== null) {
                    $questions->push($pendingQuestion);
                }
                $pendingQuestion = (object) [
                    'question'  => $msg->content,
                    'answer'    => $msg->content,
                    'score'     => null,
                    'max_score' => 20,
                    'feedback'  => null,
                ];
            } elseif ($msg->role === 'assistant' && $pendingQuestion !== null) {
                $pendingQuestion->feedback = $msg->content;
                $pendingQuestion->score = $msg->metadata['score'] ?? (int) min(20, max(0, mb_strlen($msg->content) > 50 ? 15 : 10));
                $questions->push($pendingQuestion);
                $pendingQuestion = null;
            }
        }

        // If last question was left unanswered
        if ($pendingQuestion !== null) {
            $questions->push($pendingQuestion);
        }

        return $questions;
    }

    /* ─── Session Detail Drilldown (Tier 1) ─────────── */

    /**
     * Returns detail data for a single session.
     * module = simulator → step timeline
     * module = lecturer  → message list
     * module = chatbot   → message list
     */
    public function getSessionDetail(int $sessionId): ?array
    {
        $session = VegaDbSession::find($sessionId);
        if (!$session) {
            return null;
        }

        $result = [
            'session_id' => $session->id,
            'module'     => $session->module,
            'status'     => $session->status,
            'score'      => $session->score,
            'threshold'  => $session->threshold,
            'scenario'   => $session->scenario,
            'subject'    => $session->subject,
            'topic'      => $session->topic,
            'theme'      => $session->theme,
            'title'      => $session->title,
            'created_at' => $session->created_at?->format('d.m.Y H:i'),
        ];

        switch ($session->module) {
            case 'simulator':
                $steps = $session->simulatorSteps()
                    ->orderBy('turn')
                    ->get()
                    ->map(fn($step) => [
                        'turn'               => $step->turn,
                        'node_text'          => $step->node_text,
                        'node_question'      => $step->node_question,
                        'choices'            => $step->choices,
                        'selected_choice_id' => $step->selected_choice_id,
                        'selected_choice'    => $step->selected_choice,
                        'coach_reply'        => $step->coach_reply,
                        'delta'              => $step->delta,
                        'score_after'        => $step->score_after,
                        'threshold_after'    => $step->threshold_after,
                        'ended'              => $step->ended,
                    ]);
                $result['steps'] = $steps;
                break;

            case 'lecturer':
                $messages = $session->lecturerMessages()
                    ->orderBy('created_at_ext')
                    ->orderBy('created_at')
                    ->get()
                    ->map(fn($msg) => [
                        'role'          => $msg->role,
                        'content'       => $msg->content,
                        'audio_text'    => $msg->audio_text,
                        'score'         => $msg->score,
                        'theme_message' => $msg->theme_message,
                        'images'        => $msg->images,
                        'created_at'    => $msg->created_at?->format('H:i'),
                    ]);
                $result['messages'] = $messages;
                break;

            case 'chatbot':
                $messages = $session->chatMessages()
                    ->orderBy('created_at')
                    ->get()
                    ->map(fn($msg) => [
                        'role'       => $msg->role,
                        'content'    => $msg->content,
                        'image_url'  => $msg->image_url,
                        'audio_text' => $msg->audio_text,
                        'created_at' => $msg->created_at?->format('H:i'),
                    ]);
                $result['messages'] = $messages;
                break;
        }

        return $result;
    }

    /* ─── Score Trend (Tier 3) ───────────────────────── */

    /**
     * Returns last N simulator session scores for a user.
     */
    public function getScoreTrend(string $vegaUserId, int $limit = 10): array
    {
        return VegaDbSession::simulator()
            ->forUser($vegaUserId)
            ->whereNotNull('score')
            ->orderBy('created_at')
            ->limit($limit)
            ->get()
            ->map(fn($s) => [
                'label' => $s->created_at?->format('d.m'),
                'score' => $s->score,
            ])
            ->toArray();
    }

    /* ─── Scenario & Theme Breakdown (Tier 2) ────────── */

    /**
     * Per-scenario breakdown for a user.
     */
    public function getScenarioBreakdown(string $vegaUserId): Collection
    {
        return VegaDbSession::simulator()
            ->forUser($vegaUserId)
            ->get()
            ->groupBy('scenario')
            ->map(fn($items, $scenario) => [
                'scenario'  => $scenario ?: 'unknown',
                'count'     => $items->count(),
                'completed' => $items->where('status', 'COMPLETED')->count(),
                'avg_score' => $items->whereNotNull('score')->avg('score'),
                'last_played' => $items->max('created_at'),
            ])
            ->values();
    }

    /**
     * Per-theme chatbot/lecturer breakdown for a user.
     */
    public function getThemeBreakdown(string $vegaUserId): Collection
    {
        $sessions = VegaDbSession::forUser($vegaUserId)
            ->whereIn('module', ['lecturer', 'chatbot'])
            ->get();

        return $sessions->groupBy('theme')
            ->map(fn($items, $theme) => [
                'theme'   => $theme ?: 'unknown',
                'count'   => $items->count(),
                'modules' => $items->pluck('module')->unique()->values()->toArray(),
            ])
            ->values();
    }

    /**
     * Wings Points — per-theme accumulated scores across all apps.
     * Maps to mobile's "My Wings" screen with 12 theme categories.
     */
    public function getWingsPoints(string $vegaUserId): array
    {
        $sessions = VegaDbSession::forUser($vegaUserId)
            ->whereNotNull('score')
            ->whereNotNull('theme')
            ->get(['theme', 'score', 'module']);

        $themeConfig = self::THEME_CONFIG;

        $wings = $sessions->groupBy('theme')
            ->map(function ($items, $theme) use ($themeConfig) {
                $cfg = $themeConfig[$theme] ?? ['label' => ucfirst($theme), 'emoji' => '🌟'];
                return [
                    'theme'       => $theme,
                    'label'       => $cfg['label'],
                    'emoji'       => $cfg['emoji'],
                    'total_score' => (int)$items->sum('score'),
                    'sessions'    => $items->count(),
                ];
            })
            ->sortByDesc('total_score')
            ->values();

        return [
            'total_wings' => $wings->sum('total_score'),
            'categories'  => $wings->toArray(),
        ];
    }

    /* ─── Helpers ─────────────────────────────────────── */

    /**
     * Daily session counts for the last 30 days.
     */
    private function getSessionsByDay(array $vegaUserIds, string $module): Collection
    {
        $raw = VegaDbSession::where('module', $module)
            ->forUsers($vegaUserIds)
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date');

        // Fill empty days with zero for 30-day range
        $result = collect();
        for ($d = 29; $d >= 0; $d--) {
            $date = now()->subDays($d)->format('Y-m-d');
            $result[$date] = $raw[$date] ?? 0;
        }

        return $result;
    }

    /**
     * Empty report data when no Vega user match exists.
     */
    private function emptyReportData(): array
    {
        return [
            'total_progress'   => 0,
            'total_completed'  => 0,
            'total_sessions'   => 0,
            'total_duration'   => 0,
            'avg_score'        => null,
            'user_stats'       => collect(),
            'module_stats'     => collect(),
            'sessions_by_day'  => collect(),
            'recent_sessions'  => collect(),
        ];
    }

    /* ─── Dashboard Summary ──────────────────────────── */

    /**
     * Aggregated summary data for all 3 apps (for dashboard cards).
     */
    public function getDashboardSummary(array $vegaUserIds): array
    {
        if (empty($vegaUserIds)) {
            return [
                'role_galaxy'  => ['sessions' => 0, 'avg_score' => null, 'active_students' => 0],
                'study_space'  => ['sessions' => 0, 'active_students' => 0, 'total_messages' => 0],
                'way_ai_coach' => ['sessions' => 0, 'active_students' => 0, 'total_messages' => 0],
            ];
        }

        // Role Galaxy (simulator)
        $simSessions = VegaDbSession::simulator()->forUsers($vegaUserIds)->get();
        $simAvgScore = $simSessions->whereNotNull('score')->avg('score');

        // Study Space (lecturer) — ders/konu bazlı çalışma
        $lecSessions = VegaDbSession::lecturer()->forUsers($vegaUserIds)->withCount('lecturerMessages')->get();
        $lecMessages = $lecSessions->sum('lecturer_messages_count');

        // WAY AI Coach (chatbot) — genel AI sohbet asistanı
        $chatSessions = VegaDbSession::chatbot()->forUsers($vegaUserIds)->withCount('chatMessages')->get();
        $chatMessages = $chatSessions->sum('chat_messages_count');

        return [
            'role_galaxy' => [
                'sessions'        => $simSessions->count(),
                'avg_score'       => $simAvgScore ? round((float) $simAvgScore, 1) : null,
                'active_students' => $simSessions->pluck('user_id')->unique()->count(),
                'completed'       => $simSessions->where('status', 'COMPLETED')->count(),
            ],
            'study_space' => [
                'sessions'        => $lecSessions->count(),
                'active_students' => $lecSessions->pluck('user_id')->unique()->count(),
                'total_messages'  => $lecMessages,
            ],
            'way_ai_coach' => [
                'sessions'        => $chatSessions->count(),
                'active_students' => $chatSessions->pluck('user_id')->unique()->count(),
                'total_messages'  => $chatMessages,
            ],
        ];
    }

    /* ─── Config Accessors ───────────────────────────── */

    /** 13-theme config matching mobile themeKeys.js */
    public const THEME_CONFIG = [
        'emotional'         => ['label' => 'Emotional',         'color' => '#F87171'],
        'future'            => ['label' => 'Future',            'color' => '#60A5FA'],
        'well_being'        => ['label' => 'Well-Being',        'color' => '#34D399'],
        'body_movement'     => ['label' => 'Body Movement',     'color' => '#FBBF24'],
        'critical_thinking' => ['label' => 'Critical Thinking', 'color' => '#818CF8'],
        'language'          => ['label' => 'Language',           'color' => '#A78BFA'],
        'community'         => ['label' => 'Community',         'color' => '#F472B6'],
        'nature'            => ['label' => 'Nature',            'color' => '#4ADE80'],
        'art'               => ['label' => 'Art',               'color' => '#FB7185'],
        'philosophy'        => ['label' => 'Philosophy',        'color' => '#94A3B8'],
        'technology'        => ['label' => 'Technology',        'color' => '#22D3EE'],
        'science'           => ['label' => 'Science',           'color' => '#6366f1'],
        'free_format'       => ['label' => 'Free Format',       'color' => '#9ca3af'],
    ];

    /** 12-scenario config matching mobile RoleGalaxyScreen.js */
    public static function getScenarioConfig(): array
    {
        return [
            'village_life_v1'     => ['icon' => '🏡', 'label' => 'Village Life',      'color' => '#F472B6', 'category' => 'community'],
            'world_traveler_v1'   => ['icon' => '✈️', 'label' => 'World Traveler',    'color' => '#A78BFA', 'category' => 'language'],
            'novaris_v1'          => ['icon' => '🚀', 'label' => 'Novaris',            'color' => '#22D3EE', 'category' => 'technology'],
            'biolab_v1'           => ['icon' => '🔬', 'label' => 'BioLab',             'color' => '#94A3B8', 'category' => 'science'],
            'what_if_v1'          => ['icon' => '🧠', 'label' => 'What If?',           'color' => '#FB923C', 'category' => 'criticalThinking'],
            'lost_egg_v1'         => ['icon' => '🌿', 'label' => 'Lost Egg',           'color' => '#4ADE80', 'category' => 'nature'],
            'balance_garden_v1'   => ['icon' => '🌱', 'label' => 'Balance Garden',     'color' => '#34D399', 'category' => 'wellBeing'],
            'plan_tomorrow_v1'    => ['icon' => '🌟', 'label' => 'Plan Tomorrow',      'color' => '#60A5FA', 'category' => 'future'],
            'heart_bridge_v1'     => ['icon' => '❤️', 'label' => 'Heart Bridge',      'color' => '#F87171', 'category' => 'emotional'],
            'dream_workshop_v1'   => ['icon' => '🎨', 'label' => 'Dream Workshop',     'color' => '#FB7185', 'category' => 'art'],
            'trace_center_v1'     => ['icon' => '📌', 'label' => 'Trace Center',       'color' => '#818CF8', 'category' => 'criticalThinking2'],
            'movement_island_v1'  => ['icon' => '🏃', 'label' => 'Movement Island',    'color' => '#FBBF24', 'category' => 'bodyMovement'],
        ];
    }
}
