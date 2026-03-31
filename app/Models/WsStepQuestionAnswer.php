<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WsStepQuestionAnswer extends Model
{
    protected $table = 'ws_step_question_answers';

    protected $fillable = [
        'external_id', 'question_id', 'member_id', 'attempt',
        'text_answer', 'ai_score', 'ai_max_score', 'ai_feedback', 'synced_at',
    ];

    protected $casts = [
        'synced_at' => 'datetime',
    ];

    public function question(): BelongsTo
    {
        return $this->belongsTo(WsStepQuestion::class, 'question_id');
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(WsMember::class, 'member_id');
    }
}
