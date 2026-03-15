@extends('portal.app')
@section('title', ($isTr ?? false) ? 'Soru Detayı' : 'Question Detail')
@section('page-title', 'WAY AI Coach — Questions')
@php $isTr = app()->getLocale() === 'tr'; @endphp

@section('content')

    {{-- ═══ BACK BUTTON — Figma node 1285-16888 ═══ --}}
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
        <a href="{{ route('portal.reports.app', 'way-ai-coach') }}" style="display:flex;align-items:center;gap:6px;text-decoration:none;color:var(--color-txt-muted);font-size:13px;">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            {{ $isTr ? 'Geri Dön' : 'Back' }}
        </a>
        <span style="font-size:18px;font-weight:600;">{{ $student->name ?? 'Ahmet Çelik' }} — {{ $isTr ? 'Soru Cevapları' : 'Question Answers' }}</span>
    </div>

    {{-- ═══ QUESTIONS GRID ═══ --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
        @foreach($questions as $qi => $q)
        <div style="background:#E9EEFF;border-radius:12px;padding:12px;position:relative;">
            {{-- Question badge --}}
            <div style="display:inline-flex;align-items:center;gap:6px;background:var(--color-primary);color:white;border-radius:20px;padding:6px 16px;font-size:13px;font-weight:600;margin-bottom:12px;">
                Question {{ $qi + 1 }}
            </div>

            {{-- Question text --}}
            <div style="font-size:14px;font-weight:600;margin-bottom:12px;">{{ $q->question }}</div>

            {{-- Answer options --}}
            @foreach($q->options as $oi => $option)
            <div style="display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:8px;margin-bottom:6px;font-size:13px;
                {{ $option->selected ? 'background:#dcfce7;border:1px solid #22c55e;' : 'background:transparent;' }}">
                @if($option->correct)
                    <div style="width:22px;height:22px;border-radius:50%;background:#f59e0b;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg width="12" height="12" fill="white" viewBox="0 0 24 24"><path d="M12 2L2 22h20L12 2zm0 4l6.93 12H5.07L12 6z"/></svg>
                    </div>
                @elseif($option->selected)
                    <div style="width:22px;height:22px;border-radius:50%;background:#22c55e;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg width="12" height="12" fill="white" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg>
                    </div>
                @else
                    <div style="width:22px;height:22px;border-radius:50%;background:#d1d5db;flex-shrink:0;"></div>
                @endif
                <span style="{{ $option->selected ? 'font-weight:600;color:#16a34a;' : '' }}">{{ $option->text }}</span>
            </div>
            @endforeach

            {{-- Point Cards --}}
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:16px;">
                <div style="background:#ef4444;border-radius:8px;padding:10px 14px;display:flex;align-items:center;gap:8px;">
                    <span style="font-size:18px;">❤️</span>
                    <span style="color:white;font-size:11px;font-weight:600;text-transform:uppercase;">Health Point:</span>
                    <span style="color:white;font-size:18px;font-weight:700;margin-left:auto;">{{ $q->health }}</span>
                </div>
                <div style="background:#3b82f6;border-radius:8px;padding:10px 14px;display:flex;align-items:center;gap:8px;">
                    <span style="font-size:18px;">📦</span>
                    <span style="color:white;font-size:11px;font-weight:600;text-transform:uppercase;">Resource Point:</span>
                    <span style="color:white;font-size:18px;font-weight:700;margin-left:auto;">{{ $q->resource }}</span>
                </div>
                <div style="background:#8b5cf6;border-radius:8px;padding:10px 14px;display:flex;align-items:center;gap:8px;">
                    <span style="font-size:18px;">⚖️</span>
                    <span style="color:white;font-size:11px;font-weight:600;text-transform:uppercase;">Ethics Point:</span>
                    <span style="color:white;font-size:18px;font-weight:700;margin-left:auto;">{{ $q->ethics }}</span>
                </div>
                <div style="background:#22c55e;border-radius:8px;padding:10px 14px;display:flex;align-items:center;gap:8px;">
                    <span style="font-size:18px;">🔄</span>
                    <span style="color:white;font-size:11px;font-weight:600;text-transform:uppercase;">Adaptation Point:</span>
                    <span style="color:white;font-size:18px;font-weight:700;margin-left:auto;">{{ $q->adaptation }}</span>
                </div>
            </div>
        </div>
        @endforeach
    </div>

@endsection
