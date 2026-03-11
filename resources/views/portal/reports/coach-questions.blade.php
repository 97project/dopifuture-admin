@extends('portal.app')
@section('title', ($isTr ?? false) ? 'AI Koç Geri Bildirimi' : 'AI Coach Feedback')
@section('page-title', 'WAY AI Coach')
@php $isTr = app()->getLocale() === 'tr'; @endphp

@section('content')

    {{-- ═══ Figma F-73: AI Coach Feedback — vertical timeline ═══ --}}
    <div style="max-width:640px;margin:0 auto;">

        {{-- Header --}}
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:28px;">
            <div style="width:32px;height:32px;border-radius:8px;background:#8B5CF6;display:flex;align-items:center;justify-content:center;">
                <svg width="18" height="18" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
            </div>
            <span style="font-size:18px;font-weight:700;color:#111;font-family:'Nunito',sans-serif;">{{ $isTr ? 'AI Koç Geri Bildirimi' : 'AI Coach Feedback' }}</span>
        </div>

        {{-- Session Info Bar --}}
        <div style="display:flex;gap:16px;align-items:center;padding:12px 16px;background:#F3F0FF;border-radius:10px;margin-bottom:20px;">
            @if(!empty($student))
            <div style="display:flex;align-items:center;gap:8px;">
                <img src="https://ui-avatars.com/api/?name={{ urlencode(($student->name ?? '') . ' ' . ($student->surname ?? '')) }}&size=32&background=8B5CF6&color=fff&rounded=true&bold=true&font-size=0.4" alt="" style="width:28px;height:28px;border-radius:50%;">
                <span style="font-size:12px;font-weight:600;color:#111;">{{ $student->name ?? '' }} {{ $student->surname ?? '' }}</span>
            </div>
            @endif
            @if(!empty($sessionDuration))
            <div style="display:flex;align-items:center;gap:4px;">
                <svg width="14" height="14" fill="none" stroke="#8B5CF6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span style="font-size:11px;color:#6B7280;">{{ $sessionDuration }}</span>
            </div>
            @endif
            <div style="display:flex;align-items:center;gap:4px;">
                <svg width="14" height="14" fill="none" stroke="#8B5CF6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span style="font-size:11px;color:#6B7280;">{{ count($questions) }} {{ $isTr ? 'Soru' : 'Questions' }}</span>
            </div>
        </div>
        <div style="position:relative;">
            {{-- Vertical connector line --}}
            <div style="position:absolute;left:18px;top:36px;bottom:36px;width:2px;background:#E5E7EB;"></div>

            @foreach($questions as $qi => $q)
            @php
                $colors = ['#22C55E', '#3B82F6', '#F59E0B', '#8B5CF6', '#EF4444'];
                $color = $colors[$qi % count($colors)];
                $score = $q->score ?? 0;
                $maxScore = $q->max_score ?? 1;
                $percent = $maxScore > 0 ? ($score / $maxScore) * 100 : 0;
            @endphp
            <div style="position:relative;padding-left:52px;margin-bottom:32px;">
                {{-- Step number circle --}}
                <div style="position:absolute;left:6px;top:0;width:24px;height:24px;border-radius:50%;background:{{ $color }};color:#fff;font-size:12px;font-weight:700;display:flex;align-items:center;justify-content:center;z-index:1;">
                    {{ $qi + 1 }}
                </div>

                {{-- Question text --}}
                <div style="font-size:14px;font-weight:600;color:#111;line-height:1.5;margin-bottom:10px;">{{ $q->question }}</div>

                {{-- Progress bar + Score --}}
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:16px;">
                    <div style="flex:1;height:8px;background:#E5E7EB;border-radius:4px;overflow:hidden;">
                        <div style="height:100%;width:{{ $percent }}%;background:linear-gradient(90deg,#22C55E,#84CC16);border-radius:4px;"></div>
                    </div>
                    <span style="font-size:13px;font-weight:700;color:#111;">{{ $score }}/{{ $maxScore }}</span>
                </div>

                {{-- Your Answer --}}
                <div style="margin-bottom:12px;">
                    <div style="font-size:11px;font-weight:600;color:#9CA3AF;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:6px;">{{ $isTr ? 'CEVABINIZ' : 'YOUR ANSWER' }}</div>
                    <p style="font-size:13px;color:#374151;line-height:1.6;margin:0;font-style:italic;background:#F8FAFC;border-radius:8px;padding:12px 14px;">{{ $q->answer ?? '-' }}</p>
                </div>

                {{-- Feedback --}}
                <div style="display:flex;gap:8px;align-items:flex-start;">
                    <div style="width:16px;height:16px;border-radius:50%;background:#8B5CF6;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:2px;">
                        <svg width="8" height="8" fill="white" viewBox="0 0 24 24"><path d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                    </div>
                    <p style="font-size:12px;color:#6B7280;line-height:1.6;margin:0;">
                        <strong style="color:#8B5CF6;">Feedback:</strong> {{ $q->feedback ?? '-' }}
                    </p>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Back button --}}
        <div style="text-align:center;margin-top:16px;">
            <a href="{{ route('portal.reports.app', 'way-ai-coach') }}" style="display:inline-flex;align-items:center;gap:6px;text-decoration:none;color:var(--color-txt-muted);font-size:13px;font-weight:500;padding:10px 24px;border:1px solid #E5E7EB;border-radius:8px;">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                {{ $isTr ? 'Geri Dön' : 'Back' }}
            </a>
        </div>
    </div>

@endsection
