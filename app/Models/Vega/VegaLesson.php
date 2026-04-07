<?php

namespace App\Models\Vega;

use Illuminate\Database\Eloquent\Model;

class VegaLesson extends Model
{
    protected $table = 'vega_lessons';
    protected $guarded = [];

    protected $casts = [
        'metadata'  => 'array',
        'synced_at' => 'datetime',
    ];
}
