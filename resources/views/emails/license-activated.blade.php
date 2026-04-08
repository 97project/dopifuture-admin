@extends('emails.layout', ['locale' => $locale, 'subject' => __('mail.license_activated_subject', [], $locale)])

@section('content')
<h1>{{ __('mail.license_activated_title', [], $locale) }} 🎉</h1>
<p class="subtitle">{{ __('mail.license_activated_subtitle', ['school' => $school->name], $locale) }}</p>

<p>{{ __('mail.license_activated_body', [], $locale) }}</p>

{{-- Stats --}}
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin: 24px 0;">
<tr>
    <td width="48%" style="background: linear-gradient(135deg, #06B6D4 0%, #3B82F6 100%); border-radius: 12px; padding: 20px; text-align: center;">
        <p style="font-size: 32px; font-weight: 800; color: #ffffff; margin: 0;">{{ $license->seat_count }}</p>
        <p style="font-size: 12px; color: rgba(255,255,255,0.8); margin: 4px 0 0 0; text-transform: uppercase;">{{ __('mail.total_seats', [], $locale) }}</p>
    </td>
    <td width="4%">&nbsp;</td>
    <td width="48%" style="background: linear-gradient(135deg, #8B5CF6 0%, #6366F1 100%); border-radius: 12px; padding: 20px; text-align: center;">
        <p style="font-size: 32px; font-weight: 800; color: #ffffff; margin: 0;">{{ \Carbon\Carbon::parse($license->expires_at)->format('d/m/Y') }}</p>
        <p style="font-size: 12px; color: rgba(255,255,255,0.8); margin: 4px 0 0 0; text-transform: uppercase;">{{ __('mail.valid_until', [], $locale) }}</p>
    </td>
</tr>
</table>

<div class="info-card">
    <p class="label">{{ __('mail.school_name_label', [], $locale) }}</p>
    <p class="value">{{ $school->name }}</p>

    <p class="label">{{ __('mail.license_period_label', [], $locale) }}</p>
    <p class="value">{{ \Carbon\Carbon::parse($license->starts_at)->format('d/m/Y') }} — {{ \Carbon\Carbon::parse($license->expires_at)->format('d/m/Y') }}</p>
</div>

<div class="cta-wrap">
    <a href="{{ config('app.url') }}/login" class="cta-btn">
        {{ __('mail.start_using_button', [], $locale) }}
    </a>
</div>

<p>{{ __('mail.license_activated_support', [], $locale) }}</p>
<p>{{ __('mail.regards', [], $locale) }},<br><strong>{{ config('app.name') }}</strong></p>
@endsection
