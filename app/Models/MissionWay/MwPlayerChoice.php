<?php

namespace App\Models\MissionWay;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MwPlayerChoice extends Model
{
    protected $table = 'mw_player_choices';
    protected $guarded = ['id'];

    protected $casts = [
        'is_correct' => 'boolean',
        'metrics_before' => 'array',
        'metrics_after' => 'array',
    ];

    public function player(): BelongsTo
    {
        return $this->belongsTo(MwPlayer::class, 'player_id');
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(MwSimulationSession::class, 'simulation_session_id');
    }

    public function previousPath(): BelongsTo
    {
        return $this->belongsTo(RefSimulationPath::class, 'previous_path_id');
    }

    public function simulationPath(): BelongsTo
    {
        return $this->belongsTo(RefSimulationPath::class, 'simulation_path_id');
    }

    public function selectedPath(): BelongsTo
    {
        return $this->belongsTo(RefSimulationPath::class, 'selected_path_id');
    }

    public function decidedPath(): BelongsTo
    {
        return $this->belongsTo(RefSimulationPath::class, 'decided_path_id');
    }
}
