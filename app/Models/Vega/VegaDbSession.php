<?php

declare(strict_types=1);

namespace App\Models\Vega;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Vega remote DB — vega_sessions tablosu.
 * Read-only kullanım: Panel26 portal raporları için.
 *
 * module: 'simulator' (Role Galaxy), 'lecturer' (Way AI Coach), 'chatbot' (Study Space)
 */
class VegaDbSession extends Model
{
    protected $connection = 'vega_db';
    protected $table = 'vega_sessions';

    protected $casts = [
        'meta'           => 'array',
        'sim_state'      => 'array',
        'score'          => 'integer',
        'message_count'  => 'integer',
        'created_at_ext' => 'datetime',
        'updated_at_ext' => 'datetime',
    ];

    /* ─── Relationships ──────────────────────────────── */

    public function simulatorSteps(): HasMany
    {
        return $this->hasMany(VegaDbSimulatorStep::class, 'session_id');
    }

    public function lecturerMessages(): HasMany
    {
        return $this->hasMany(VegaDbLecturerMessage::class, 'session_id');
    }

    public function chatMessages(): HasMany
    {
        return $this->hasMany(VegaDbChatMessage::class, 'session_id');
    }

    /* ─── Scopes ─────────────────────────────────────── */

    public function scopeSimulator($query)
    {
        return $query->where('module', 'simulator');
    }

    public function scopeLecturer($query)
    {
        return $query->where('module', 'lecturer');
    }

    public function scopeChatbot($query)
    {
        return $query->where('module', 'chatbot');
    }

    public function scopeForUser($query, string $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForUsers($query, array $userIds)
    {
        return $query->whereIn('user_id', $userIds);
    }

    /* ─── Computed ───────────────────────────────────── */

    public function getStepCountAttribute(): int
    {
        return $this->simulatorSteps()->count();
    }

    public function getLecturerMessageCountAttribute(): int
    {
        return $this->lecturerMessages()->count();
    }

    public function getChatMessageCountAttribute(): int
    {
        return $this->chatMessages()->count();
    }

    /**
     * Compute session duration in seconds.
     * Priority: updated_at - created_at (if different),
     * then fallback to last message timestamp - created_at.
     */
    public function getDurationSecondsAttribute(): int
    {
        if (!$this->created_at) {
            return 0;
        }

        // Primary: updated_at - created_at (if timestamps differ)
        if ($this->updated_at && $this->updated_at->ne($this->created_at)) {
            $diff = (int)abs($this->updated_at->diffInSeconds($this->created_at));
            if ($diff > 0) {
                return $diff;
            }
        }

        // Fallback: last message/step timestamp - created_at
        $lastTimestamp = $this->getLastActivityTimestamp();
        if ($lastTimestamp) {
            return (int)abs($lastTimestamp->diffInSeconds($this->created_at));
        }

        return 0;
    }

    /**
     * Get the timestamp of the last activity (message or step) in this session.
     */
    protected function getLastActivityTimestamp(): ?\Carbon\Carbon
    {
        // Check simulator steps
        if ($this->relationLoaded('simulatorSteps') && $this->simulatorSteps->isNotEmpty()) {
            $last = $this->simulatorSteps->sortByDesc('created_at')->first();
            return $last?->created_at;
        }

        // Check lecturer messages
        if ($this->relationLoaded('lecturerMessages') && $this->lecturerMessages->isNotEmpty()) {
            $last = $this->lecturerMessages->sortByDesc('created_at')->first();
            return $last?->created_at;
        }

        // Check chat messages
        if ($this->relationLoaded('chatMessages') && $this->chatMessages->isNotEmpty()) {
            $last = $this->chatMessages->sortByDesc('created_at')->first();
            return $last?->created_at;
        }

        // Lazy load based on module type
        $module = $this->module ?? '';
        $lastMessage = match ($module) {
            'simulator' => $this->simulatorSteps()->latest('created_at')->first(),
            'lecturer'  => $this->lecturerMessages()->latest('created_at')->first(),
            'chatbot'   => $this->chatMessages()->latest('created_at')->first(),
            default     => null,
        };

        return $lastMessage?->created_at;
    }
}
