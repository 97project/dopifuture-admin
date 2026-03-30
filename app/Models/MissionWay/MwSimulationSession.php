<?php

namespace App\Models\MissionWay;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MwSimulationSession extends Model
{
    protected $table = 'mw_simulation_sessions';
    protected $guarded = ['id'];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'abandoned_at' => 'datetime',
        'final_metrics' => 'array',
    ];

    public function version(): BelongsTo
    {
        return $this->belongsTo(RefSimulationVersion::class, 'simulation_version_id');
    }

    public function finalPath(): BelongsTo
    {
        return $this->belongsTo(RefSimulationPath::class, 'final_path_id');
    }

    public function players(): HasMany
    {
        return $this->hasMany(MwSessionPlayer::class, 'simulation_session_id');
    }
}
