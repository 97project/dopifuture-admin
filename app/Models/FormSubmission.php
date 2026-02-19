<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormSubmission extends Model
{
    protected $fillable = [
        'form_id',
        'data',
        'ip_address',
        'user_agent',
        'is_read',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'is_read' => 'boolean',
        ];
    }

    public function form()
    {
        return $this->belongsTo(Form::class);
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }
}
