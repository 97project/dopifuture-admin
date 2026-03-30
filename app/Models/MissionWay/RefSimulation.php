<?php

namespace App\Models\MissionWay;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RefSimulation extends Model
{
    protected $table = 'ref_simulations';
    protected $guarded = ['id'];

    public function versions(): HasMany
    {
        return $this->hasMany(RefSimulationVersion::class, 'simulation_id');
    }
}
