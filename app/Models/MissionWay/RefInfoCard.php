<?php

namespace App\Models\MissionWay;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RefInfoCard extends Model
{
    protected $table = 'ref_info_cards';
    protected $guarded = ['id'];

    public function simulationPath(): BelongsTo
    {
        return $this->belongsTo(RefSimulationPath::class, 'simulation_path_id');
    }
}
