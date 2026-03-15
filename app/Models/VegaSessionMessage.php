<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VegaSessionMessage extends Model
{
    protected $table = 'vega_session_messages';

    protected $fillable = [
        'session_id', 'role', 'content', 'question', 'answer',
        'score', 'max_score', 'feedback', 'metrics', 'options', 'order_index',
    ];

    protected $casts = [
        'metrics' => 'array',
        'options' => 'array',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(VegaSession::class, 'session_id');
    }

    public function isAssistant(): bool
    {
        return $this->role === 'assistant';
    }

    public function isUser(): bool
    {
        return $this->role === 'user';
    }
}
