<?php

namespace App\Models\Vega;

use Illuminate\Database\Eloquent\Model;

class VegaWing extends Model
{
    protected $table = 'vega_wings';
    protected $guarded = [];

    protected $casts = [
        'metadata'  => 'array',
        'synced_at' => 'datetime',
    ];
}
