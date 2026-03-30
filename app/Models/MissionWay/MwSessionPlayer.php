<?php

namespace App\Models\MissionWay;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MwSessionPlayer extends Model
{
    protected $table = 'mw_session_players';
    protected $guarded = ['id'];

    protected $casts = [
        'joined_at' => 'datetime',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(MwSimulationSession::class, 'simulation_session_id');
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(MwPlayer::class, 'player_id');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(RefRole::class, 'role_id');
    }
}
