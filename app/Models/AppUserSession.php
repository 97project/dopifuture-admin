<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppUserSession extends Model
{
    protected $table = 'app_user_sessions';

    protected $fillable = [
        'user_id',
        'application_id',
        'session_type',
        'external_session_id',
        'session_name',
        'started_at',
        'ended_at',
        'duration_seconds',
        'score',
        'metadata',
    ];

    protected $casts = [
        'metadata'   => 'array',
        'score'      => 'decimal:2',
        'started_at' => 'datetime',
        'ended_at'   => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    /* ── Scopes ─────────────────────────────────────── */

    public function scopeForApp($query, int $applicationId)
    {
        return $query->where('application_id', $applicationId);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('session_type', $type);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('started_at', '>=', now()->subDays($days));
    }
}
