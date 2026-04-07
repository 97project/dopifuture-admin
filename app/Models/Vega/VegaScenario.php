<?php

namespace App\Models\Vega;

use Illuminate\Database\Eloquent\Model;

class VegaScenario extends Model
{
    protected $table = 'vega_scenarios';
    protected $guarded = [];

    protected $casts = [
        'metadata'  => 'array',
        'synced_at' => 'datetime',
    ];
}
