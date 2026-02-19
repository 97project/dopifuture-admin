<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationLog extends Model
{
    protected $fillable = [
        'title',
        'body',
        'channels',
        'target_type',
        'target_data',
        'recipients_count',
        'read_count',
        'template_id',
        'sent_by',
    ];

    protected function casts(): array
    {
        return [
            'channels' => 'array',
            'target_data' => 'array',
            'recipients_count' => 'integer',
            'read_count' => 'integer',
        ];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(NotificationTemplate::class, 'template_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    public function getTargetLabel(): string
    {
        return match ($this->target_type) {
            'all' => __('admin.all_users'),
            'role' => implode(', ', $this->target_data ?? []),
            'school' => implode(', ', $this->target_data ?? []),
            'selected' => __('admin.selected_users') . ' (' . count($this->target_data ?? []) . ')',
            default => '-',
        };
    }
}
