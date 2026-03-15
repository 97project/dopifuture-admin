<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MwSession extends Model
{
    protected $table = 'mw_sessions';

    protected $fillable = [
        'external_id', 'simulation_id', 'simulation_version_id', 'session_code',
        'status', 'final_score', 'final_metrics', 'player_choices',
        'started_at', 'completed_at', 'synced_at',
    ];

    protected $casts = [
        'final_metrics'  => 'array',
        'player_choices' => 'array',
        'started_at'     => 'datetime',
        'completed_at'   => 'datetime',
        'synced_at'      => 'datetime',
    ];

    public function simulation(): BelongsTo
    {
        return $this->belongsTo(MwSimulation::class, 'simulation_id');
    }

    public function sessionPlayers(): HasMany
    {
        return $this->hasMany(MwSessionPlayer::class, 'session_id');
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }
}
