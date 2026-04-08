@extends('emails.layout', ['locale' => $locale, 'subject' => __('mail.seats_added_subject', [], $locale)])

@section('content')
<h1>{{ __('mail.seats_added_title', [], $locale) }} ➕</h1>
<p class="subtitle">{{ __('mail.seats_added_subtitle', ['school' => $school->name], $locale) }}</p>

<p>{{ __('mail.seats_added_body', ['count' => $addedSeats], $locale) }}</p>

{{-- Stats --}}
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin: 24px 0;">
<tr>
    <td width="31%" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 12px; padding: 18px; text-align: center;">
        <p style="font-size: 28px; font-weight: 800; color: #ffffff; margin: 0;">+{{ $addedSeats }}</p>
        <p style="font-size: 11px; color: rgba(255,255,255,0.8); margin: 4px 0 0 0; text-transform: uppercase;">{{ __('mail.new_seats', [], $locale) }}</p>
    </td>
    <td width="3%">&nbsp;</td>
    <td width="31%" style="background: linear-gradient(135deg, #06B6D4 0%, #3B82F6 100%); border-radius: 12px; padding: 18px; text-align: center;">
        <p style="font-size: 28px; font-weight: 800; color: #ffffff; margin: 0;">{{ $newTotal }}</p>
        <p style="font-size: 11px; color: rgba(255,255,255,0.8); margin: 4px 0 0 0; text-transform: uppercase;">{{ __('mail.total_seats', [], $locale) }}</p>
    </td>
    <td width="3%">&nbsp;</td>
    <td width="31%" style="background: linear-gradient(135deg, #8B5CF6 0%, #6366F1 100%); border-radius: 12px; padding: 18px; text-align: center;">
        <p style="font-size: 28px; font-weight: 800; color: #ffffff; margin: 0;">{{ $newTotal - $usedSeats }}</p>
        <p style="font-size: 11px; color: rgba(255,255,255,0.8); margin: 4px 0 0 0; text-transform: uppercase;">{{ __('mail.available_seats', [], $locale) }}</p>
    </td>
</tr>
</table>

<div class="cta-wrap">
    <a href="https://dopifuture.97.team/users?role=student" class="cta-btn">
        {{ __('mail.add_students_button', [], $locale) }}
    </a>
</div>

<p>{{ __('mail.regards', [], $locale) }},<br><strong>{{ config('app.name') }}</strong></p>
@endsection
