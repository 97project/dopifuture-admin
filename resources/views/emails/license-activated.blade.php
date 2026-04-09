@extends('emails.layout', ['locale' => $locale, 'subject' => __('mail.license_activated_subject', [], $locale)])

@section('content')
<h1>{{ __('mail.license_activated_title', [], $locale) }} 🎉</h1>
<p class="subtitle">{{ __('mail.license_activated_subtitle', ['school' => $school->name], $locale) }}</p>

<p>{{ __('mail.license_activated_body', [], $locale) }}</p>

{{-- Stats --}}
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin: 20px 0;">
<tr>
    <td class="stat-cell" width="48%" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); border-radius: 12px; padding: 18px; text-align: center;">
        <p style="font-size: 28px; font-weight: 800; color: #ffffff; margin: 0;">{{ $license->seat_count }}</p>
        <p style="font-size: 11px; color: rgba(255,255,255,0.85); margin: 4px 0 0 0; text-transform: uppercase; letter-spacing: 0.5px;">{{ __('mail.total_seats', [], $locale) }}</p>
    </td>
    <td class="stat-spacer" width="4%">&nbsp;</td>
    <td class="stat-cell" width="48%" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); border-radius: 12px; padding: 18px; text-align: center;">
        <p style="font-size: 28px; font-weight: 800; color: #ffffff; margin: 0;">{{ \Carbon\Carbon::parse($license->expires_at)->format('d/m/Y') }}</p>
        <p style="font-size: 11px; color: rgba(255,255,255,0.85); margin: 4px 0 0 0; text-transform: uppercase; letter-spacing: 0.5px;">{{ __('mail.valid_until', [], $locale) }}</p>
    </td>
</tr>
</table>

<div class="info-card" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin: 20px 0;">
    <p class="label" style="font-size: 11px; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; margin: 0 0 3px 0;">{{ __('mail.school_name_label', [], $locale) }}</p>
    <p class="value" style="font-size: 15px; font-weight: 600; color: #1e293b; margin: 0 0 14px 0;">{{ $school->name }}</p>

    <p class="label" style="font-size: 11px; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; margin: 0 0 3px 0;">{{ __('mail.license_period_label', [], $locale) }}</p>
    <p class="value" style="font-size: 15px; font-weight: 600; color: #1e293b; margin: 0;">{{ \Carbon\Carbon::parse($license->starts_at)->format('d/m/Y') }} — {{ \Carbon\Carbon::parse($license->expires_at)->format('d/m/Y') }}</p>
</div>

<div class="cta-wrap" style="text-align: center; margin: 28px 0;">
    <a href="https://dopifuture.97.team/login" class="cta-btn" style="display: inline-block; background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: #ffffff !important; font-size: 15px; font-weight: 700; text-decoration: none; padding: 13px 40px; border-radius: 50px;">
        {{ __('mail.start_using_button', [], $locale) }}
    </a>
</div>

<p>{{ __('mail.license_activated_support', [], $locale) }}</p>
<p>{{ __('mail.regards', [], $locale) }},<br><strong>DopiFuture</strong></p>
@endsection
