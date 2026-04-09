@extends('emails.layout', ['locale' => $locale, 'subject' => __('mail.account_deletion_subject', [], $locale)])

@section('content')
<h1>{{ __('mail.account_deletion_title', [], $locale) }} ⚠️</h1>
<p class="subtitle" style="font-size: 14px; color: #64748b; margin: 0 0 24px 0; line-height: 1.6;">{{ __('mail.account_deletion_greeting', ['name' => $user->name], $locale) }}</p>

<p>{{ __('mail.account_deletion_body', [], $locale) }}</p>

<div class="cta-wrap" style="text-align: center; margin: 28px 0;">
    <a href="{{ $confirmationUrl }}" class="cta-btn" style="display: inline-block; background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: #ffffff !important; font-size: 15px; font-weight: 700; text-decoration: none; padding: 13px 40px; border-radius: 50px;">
        {{ __('mail.account_deletion_button', [], $locale) }}
    </a>
</div>

<div class="warning" style="background: #fefce8; border-left: 4px solid #eab308; border-radius: 0 8px 8px 0; padding: 12px 16px; margin: 20px 0; font-size: 13px; color: #854d0e; line-height: 1.5;">
    {{ __('mail.account_deletion_warning', [], $locale) }}
</div>

<p>{{ __('mail.regards', [], $locale) }},<br><strong>DopiFuture</strong></p>
@endsection