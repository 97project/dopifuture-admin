@extends('emails.layout', ['locale' => $locale, 'subject' => __('mail.welcome_subject', [], $locale)])

@section('content')
<h1>{{ __('mail.welcome_title', [], $locale) }} 🎓</h1>
<p class="subtitle" style="font-size: 14px; color: #64748b; margin: 0 0 24px 0; line-height: 1.6;">{{ __('mail.welcome_subtitle', ['name' => $user->name], $locale) }}</p>

<p>{{ __('mail.welcome_body', [], $locale) }}</p>

<div class="info-card" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin: 20px 0;">
    <p class="label" style="font-size: 11px; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; margin: 0 0 3px 0;">{{ __('mail.email_label', [], $locale) }}</p>
    <p class="value" style="font-size: 15px; font-weight: 600; color: #1e293b; margin: 0 0 14px 0;">{{ $user->email }}</p>

    <p class="label" style="font-size: 11px; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; margin: 0 0 3px 0;">{{ __('mail.password_label', [], $locale) }}</p>
    <p class="value" style="font-size: 15px; font-weight: 600; color: #1e293b; margin: 0;">
        <span class="password" style="font-family: 'Courier New', Courier, monospace; background: #0f172a; color: #38bdf8; padding: 8px 14px; border-radius: 8px; display: inline-block; font-size: 16px; letter-spacing: 2px;">{{ $plainPassword }}</span>
    </p>
</div>

<div class="cta-wrap" style="text-align: center; margin: 28px 0;">
    <a href="{{ $loginUrl }}" class="cta-btn" style="display: inline-block; background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: #ffffff !important; font-size: 15px; font-weight: 700; text-decoration: none; padding: 13px 40px; border-radius: 50px;">{{ __('mail.login_button', [], $locale) }}</a>
</div>

<div class="warning" style="background: #fefce8; border-left: 4px solid #eab308; border-radius: 0 8px 8px 0; padding: 12px 16px; margin: 20px 0; font-size: 13px; color: #854d0e; line-height: 1.5;">
    ⚠️ {{ __('mail.welcome_warning', [], $locale) }}
</div>

<p>{{ __('mail.welcome_support', [], $locale) }}</p>
<p>{{ __('mail.regards', [], $locale) }},<br><strong>DopiFuture</strong></p>
@endsection
