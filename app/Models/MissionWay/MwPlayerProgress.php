<?php

namespace App\Models\MissionWay;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MwPlayerProgress extends Model
{
    protected $table = 'mw_player_progress';
    protected $guarded = ['id'];

    protected $casts = [
        'current_metrics' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function player(): BelongsTo
    {
        return $this->belongsTo(MwPlayer::class, 'player_id');
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(MwSimulationSession::class, 'simulation_session_id');
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(RefSimulationVersion::class, 'simulation_version_id');
    }

    public function currentPath(): BelongsTo
    {
        return $this->belongsTo(RefSimulationPath::class, 'current_path_id');
    }
}
