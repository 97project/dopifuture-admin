<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WsTool extends Model
{
    protected $table = 'ws_tools';

    protected $fillable = [
        'application_id', 'name', 'description', 'icon_url',
        'website_url', 'category', 'synced_at',
    ];

    protected $casts = [
        'synced_at' => 'datetime',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}
