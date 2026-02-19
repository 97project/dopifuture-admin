<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class Form extends Model
{
    use LogsActivity;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'fields',
        'notification_emails',
        'success_message',
        'is_active',
        'requires_captcha',
    ];

    protected function casts(): array
    {
        return [
            'fields' => 'array',
            'notification_emails' => 'array',
            'success_message' => 'array',
            'is_active' => 'boolean',
            'requires_captcha' => 'boolean',
        ];
    }

    public function submissions()
    {
        return $this->hasMany(FormSubmission::class);
    }

    public function unreadSubmissions()
    {
        return $this->hasMany(FormSubmission::class)->where('is_read', false);
    }

    public function getTranslation(string $field, ?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        $value = $this->$field;
        return is_array($value) ? ($value[$locale] ?? $value[config('app.fallback_locale', 'tr')] ?? '') : ($value ?? '');
    }
}
