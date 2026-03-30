<?php

namespace App\Models\MissionWay;

use Illuminate\Database\Eloquent\Model;

class RefSimulationVersionRole extends Model
{
    protected $table = 'ref_simulation_version_roles';
    protected $guarded = ['id'];

    public function version()
    {
        return $this->belongsTo(RefSimulationVersion::class, 'simulation_version_id');
    }

    public function role()
    {
        return $this->belongsTo(RefRole::class, 'role_id');
    }
}
