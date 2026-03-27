<?php

declare(strict_types=1);

namespace App\Models\Vega;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Vega remote DB — vega_simulator_steps tablosu.
 * Role Galaxy simülasyonlarının adım adım verileri.
 */
class VegaDbSimulatorStep extends Model
{
    protected $connection = 'vega_db';
    protected $table = 'vega_simulator_steps';

    public $timestamps = false;

    protected $casts = [
        'choices'        => 'array',
        'turn'           => 'integer',
        'delta'          => 'integer',
        'score_after'    => 'integer',
        'ended'          => 'boolean',
        'created_at_ext' => 'datetime',
        'created_at'     => 'datetime',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(VegaDbSession::class, 'session_id');
    }

    /**
     * Seçilen tercihi choices dizisinden çıkar.
     */
    public function getSelectedChoiceAttribute(): ?array
    {
        if (!$this->choices || !$this->selected_choice_id) {
            return null;
        }

        foreach ($this->choices as $choice) {
            if (($choice['id'] ?? null) === $this->selected_choice_id) {
                return $choice;
            }
        }

        return null;
    }
}
