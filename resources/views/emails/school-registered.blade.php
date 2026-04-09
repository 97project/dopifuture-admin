@extends('emails.layout', ['locale' => $locale, 'subject' => __('mail.school_registered_subject', [], $locale)])

@section('content')
<h1>{{ __('mail.school_registered_title', [], $locale) }} 🏫</h1>
<p class="subtitle" style="font-size: 14px; color: #64748b; margin: 0 0 24px 0; line-height: 1.6;">{{ __('mail.school_registered_subtitle', ['name' => $adminName], $locale) }}</p>

<p>{{ __('mail.school_registered_body', [], $locale) }}</p>

<div class="info-card" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin: 20px 0;">
    <p class="label" style="font-size: 11px; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; margin: 0 0 3px 0;">{{ __('mail.school_name_label', [], $locale) }}</p>
    <p class="value" style="font-size: 15px; font-weight: 600; color: #1e293b; margin: 0 0 14px 0;">{{ $school->name }}</p>

    @if($school->city)
    <p class="label" style="font-size: 11px; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; margin: 0 0 3px 0;">{{ __('mail.school_location_label', [], $locale) }}</p>
    <p class="value" style="font-size: 15px; font-weight: 600; color: #1e293b; margin: 0 0 14px 0;">{{ collect([$school->city, $school->state, $school->country])->filter()->join(', ') }}</p>
    @endif

    @if($school->email)
    <p class="label" style="font-size: 11px; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; margin: 0 0 3px 0;">{{ __('mail.school_email_label', [], $locale) }}</p>
    <p class="value" style="font-size: 15px; font-weight: 600; color: #1e293b; margin: 0;">{{ $school->email }}</p>
    @endif
</div>

<div class="cta-wrap" style="text-align: center; margin: 28px 0;">
    <a href="https://dopifuture.97.team/admin/schools/{{ $school->id }}" class="cta-btn" style="display: inline-block; background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: #ffffff !important; font-size: 15px; font-weight: 700; text-decoration: none; padding: 13px 40px; border-radius: 50px;">
        {{ __('mail.school_view_button', [], $locale) }}
    </a>
</div>

<p>{{ __('mail.school_registered_next', [], $locale) }}</p>
<p>{{ __('mail.regards', [], $locale) }},<br><strong>DopiFuture</strong></p>
@endsection
