<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WsStepQuestion extends Model
{
    protected $table = 'ws_step_questions';

    protected $fillable = [
        'external_id', 'step_id', 'question_text', 'max_score',
        'sort_order', 'is_required', 'synced_at',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'synced_at'   => 'datetime',
    ];

    public function step(): BelongsTo
    {
        return $this->belongsTo(WsStep::class, 'step_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(WsStepQuestionAnswer::class, 'question_id');
    }
}
