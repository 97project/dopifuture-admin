@extends('emails.layout', ['locale' => $locale, 'subject' => __('mail.account_deletion_subject', [], $locale)])

@section('content')
<h1>{{ __('mail.account_deletion_title', [], $locale) }} ⚠️</h1>
<p class="subtitle">{{ __('mail.account_deletion_greeting', ['name' => $user->name], $locale) }}</p>

<p>{{ __('mail.account_deletion_body', [], $locale) }}</p>

<div class="cta-wrap">
    <a href="{{ $confirmationUrl }}" class="cta-btn" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); box-shadow: 0 4px 15px rgba(239,68,68,0.4);">
        {{ __('mail.account_deletion_button', [], $locale) }}
    </a>
</div>

<div class="warning">
    {{ __('mail.account_deletion_warning', [], $locale) }}
</div>

<p>{{ __('mail.regards', [], $locale) }},<br><strong>{{ config('app.name') }}</strong></p>
@endsection