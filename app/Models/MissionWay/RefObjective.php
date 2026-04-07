<?php

namespace App\Models\MissionWay;

use Illuminate\Database\Eloquent\Model;

class RefObjective extends Model
{
    protected $table = 'ref_objectives';
    public $incrementing = false;
    protected $guarded = [];

    public function pathObjectives()
    {
        return $this->hasMany(RefPathObjective::class, 'objective_id');
    }
}
