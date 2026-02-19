<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegistrationRequest extends Model
{
    protected $fillable = [
        'school_name',
        'country',
        'contact_name',
        'contact_surname',
        'email',
        'phone',
        'notes',
        'status',
        'admin_notes',
    ];

    /* ─── Scopes ────────────────────────────────── */

    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeNew($query)
    {
        return $query->where('status', 'new');
    }
}
