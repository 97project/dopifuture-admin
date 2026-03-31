<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WsStep extends Model
{
    protected $table = 'ws_steps';

    protected $fillable = [
        'external_id', 'simulation_id', 'name', 'difficulty', 'skill',
        'responsible_name', 'points', 'max_score', 'ai_score',
        'order_index', 'tools', 'questions', 'synced_at',
    ];

    protected $casts = [
        'tools'     => 'array',
        'questions' => 'array',
        'synced_at' => 'datetime',
    ];

    public function simulation(): BelongsTo
    {
        return $this->belongsTo(WsSimulation::class, 'simulation_id');
    }

    public function stepQuestions(): HasMany
    {
        return $this->hasMany(WsStepQuestion::class, 'step_id')->orderBy('sort_order');
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(WsStepEvaluation::class, 'step_id');
    }
}
