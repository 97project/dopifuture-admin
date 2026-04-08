@extends('emails.layout', ['locale' => $locale, 'subject' => __('mail.welcome_subject', [], $locale)])

@section('content')
<h1>{{ __('mail.welcome_title', [], $locale) }} 🎓</h1>
<p class="subtitle">{{ __('mail.welcome_subtitle', ['name' => $user->name], $locale) }}</p>

<p>{{ __('mail.welcome_body', [], $locale) }}</p>

<div class="info-card">
    <p class="label">{{ __('mail.email_label', [], $locale) }}</p>
    <p class="value">{{ $user->email }}</p>

    <p class="label">{{ __('mail.password_label', [], $locale) }}</p>
    <p class="value"><span class="password">{{ $plainPassword }}</span></p>
</div>

<div class="cta-wrap">
    <a href="{{ $loginUrl }}" class="cta-btn">{{ __('mail.login_button', [], $locale) }}</a>
</div>

<div class="warning">
    ⚠️ {{ __('mail.welcome_warning', [], $locale) }}
</div>

<p>{{ __('mail.welcome_support', [], $locale) }}</p>
<p>{{ __('mail.regards', [], $locale) }},<br><strong>{{ config('app.name') }}</strong></p>
@endsection
