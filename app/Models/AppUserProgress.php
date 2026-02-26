<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppUserProgress extends Model
{
    protected $table = 'app_user_progress';

    protected $fillable = [
        'user_id',
        'application_id',
        'module_type',
        'module_id',
        'module_name',
        'status',
        'score',
        'max_score',
        'duration_seconds',
        'attempts',
        'metadata',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'metadata'     => 'array',
        'score'        => 'decimal:2',
        'max_score'    => 'decimal:2',
        'started_at'   => 'datetime',
        'completed_at' => 'datetime',
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

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeForApp($query, int $applicationId)
    {
        return $query->where('application_id', $applicationId);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('module_type', $type);
    }

    /* ── Helpers ─────────────────────────────────────── */

    public function getCompletionPercentAttribute(): float
    {
        if (!$this->max_score || $this->max_score == 0) {
            return $this->status === 'completed' ? 100 : 0;
        }
        return round(($this->score / $this->max_score) * 100, 1);
    }
}
