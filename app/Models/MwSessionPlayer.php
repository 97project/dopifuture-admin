<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MwSessionPlayer extends Model
{
    protected $table = 'mw_session_players';

    protected $fillable = [
        'session_id', 'player_id', 'role', 'grade',
        'completed_decisions', 'total_decisions',
        'health_metric', 'resource_metric', 'ethics_metric', 'adaptation_metric',
        'joined_at',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(MwSession::class, 'session_id');
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(MwPlayer::class, 'player_id');
    }
}
