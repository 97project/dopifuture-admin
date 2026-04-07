<?php

namespace App\Models\MissionWay;

use Illuminate\Database\Eloquent\Model;

class RefPathObjective extends Model
{
    protected $table = 'ref_path_objectives';
    public $incrementing = false;
    protected $guarded = [];

    public function simulationPath()
    {
        return $this->belongsTo(RefSimulationPath::class, 'simulation_path_id');
    }

    public function objective()
    {
        return $this->belongsTo(RefObjective::class, 'objective_id');
    }
}
