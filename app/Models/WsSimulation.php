<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WsSimulation extends Model
{
    protected $table = 'ws_simulations';

    protected $fillable = [
        'external_id', 'application_id', 'name', 'type', 'category',
        'status', 'metadata', 'synced_at',
    ];

    protected $casts = [
        'metadata'  => 'array',
        'synced_at' => 'datetime',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function steps(): HasMany
    {
        return $this->hasMany(WsStep::class, 'simulation_id')->orderBy('order_index');
    }
}
