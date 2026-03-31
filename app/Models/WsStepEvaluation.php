<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WsStepEvaluation extends Model
{
    protected $table = 'ws_step_evaluations';

    protected $fillable = [
        'external_id', 'step_id', 'member_id', 'attempt',
        'ai_total_score', 'ai_max_score', 'ai_coins',
        'ai_overall_feedback', 'status', 'ai_evaluated_at', 'synced_at',
    ];

    protected $casts = [
        'ai_evaluated_at' => 'datetime',
        'synced_at'       => 'datetime',
    ];

    public function step(): BelongsTo
    {
        return $this->belongsTo(WsStep::class, 'step_id');
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(WsMember::class, 'member_id');
    }
}
