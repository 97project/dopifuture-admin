<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WsMember extends Model
{
    protected $table = 'ws_members';

    protected $fillable = [
        'external_id', 'user_id', 'application_id', 'points',
        'step_progress', 'step_evaluations', 'step_submissions', 'synced_at',
    ];

    protected $casts = [
        'step_progress'    => 'array',
        'step_evaluations' => 'array',
        'step_submissions' => 'array',
        'synced_at'        => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    /**
     * step_progress JSON array'inden belirli step'in ilerlemesini bul.
     */
    public function getStepProgressFor(int $stepExternalId): ?array
    {
        foreach (($this->step_progress ?? []) as $sp) {
            if (($sp['stepId'] ?? $sp['step_id'] ?? null) == $stepExternalId) {
                return $sp;
            }
        }
        return null;
    }
}
