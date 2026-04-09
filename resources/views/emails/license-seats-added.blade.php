@extends('emails.layout', ['locale' => $locale, 'subject' => __('mail.seats_added_subject', [], $locale)])

@section('content')
<h1>{{ __('mail.seats_added_title', [], $locale) }} ➕</h1>
<p class="subtitle">{{ __('mail.seats_added_subtitle', ['school' => $school->name], $locale) }}</p>

<p>{{ __('mail.seats_added_body', ['count' => $addedSeats], $locale) }}</p>

{{-- Stats --}}
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin: 20px 0;">
<tr>
    <td class="stat-cell" width="31%" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 12px; padding: 16px 8px; text-align: center;">
        <p style="font-size: 24px; font-weight: 800; color: #ffffff; margin: 0;">+{{ $addedSeats }}</p>
        <p style="font-size: 10px; color: rgba(255,255,255,0.85); margin: 4px 0 0 0; text-transform: uppercase;">{{ __('mail.new_seats', [], $locale) }}</p>
    </td>
    <td class="stat-spacer" width="3%">&nbsp;</td>
    <td class="stat-cell" width="31%" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); border-radius: 12px; padding: 16px 8px; text-align: center;">
        <p style="font-size: 24px; font-weight: 800; color: #ffffff; margin: 0;">{{ $newTotal }}</p>
        <p style="font-size: 10px; color: rgba(255,255,255,0.85); margin: 4px 0 0 0; text-transform: uppercase;">{{ __('mail.total_seats', [], $locale) }}</p>
    </td>
    <td class="stat-spacer" width="3%">&nbsp;</td>
    <td class="stat-cell" width="31%" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); border-radius: 12px; padding: 16px 8px; text-align: center;">
        <p style="font-size: 24px; font-weight: 800; color: #ffffff; margin: 0;">{{ $newTotal - $usedSeats }}</p>
        <p style="font-size: 10px; color: rgba(255,255,255,0.85); margin: 4px 0 0 0; text-transform: uppercase;">{{ __('mail.available_seats', [], $locale) }}</p>
    </td>
</tr>
</table>

<div class="cta-wrap" style="text-align: center; margin: 28px 0;">
    <a href="https://dopifuture.97.team/users?role=student" class="cta-btn" style="display: inline-block; background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: #ffffff !important; font-size: 15px; font-weight: 700; text-decoration: none; padding: 13px 40px; border-radius: 50px;">
        {{ __('mail.add_students_button', [], $locale) }}
    </a>
</div>

<p>{{ __('mail.regards', [], $locale) }},<br><strong>DopiFuture</strong></p>
@endsection
