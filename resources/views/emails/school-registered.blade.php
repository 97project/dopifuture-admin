@extends('emails.layout', ['locale' => $locale, 'subject' => __('mail.school_registered_subject', [], $locale)])

@section('content')
<h1>{{ __('mail.school_registered_title', [], $locale) }} 🏫</h1>
<p class="subtitle">{{ __('mail.school_registered_subtitle', ['name' => $adminName], $locale) }}</p>

<p>{{ __('mail.school_registered_body', [], $locale) }}</p>

<div class="info-card">
    <p class="label">{{ __('mail.school_name_label', [], $locale) }}</p>
    <p class="value">{{ $school->name }}</p>

    @if($school->city)
    <p class="label">{{ __('mail.school_location_label', [], $locale) }}</p>
    <p class="value">{{ collect([$school->city, $school->state, $school->country])->filter()->join(', ') }}</p>
    @endif

    @if($school->email)
    <p class="label">{{ __('mail.school_email_label', [], $locale) }}</p>
    <p class="value">{{ $school->email }}</p>
    @endif
</div>

<div class="cta-wrap">
    <a href="{{ config('app.url') }}/admin/schools/{{ $school->id }}" class="cta-btn">
        {{ __('mail.school_view_button', [], $locale) }}
    </a>
</div>

<p>{{ __('mail.school_registered_next', [], $locale) }}</p>
<p>{{ __('mail.regards', [], $locale) }},<br><strong>{{ config('app.name') }}</strong></p>
@endsection
