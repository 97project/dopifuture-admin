<?php

namespace App\Models\MissionWay;

use Illuminate\Database\Eloquent\Model;

class RefTranslation extends Model
{
    protected $table = 'ref_translations';
    protected $guarded = ['id'];

    protected $casts = [
        'fields' => 'array',
    ];
}
