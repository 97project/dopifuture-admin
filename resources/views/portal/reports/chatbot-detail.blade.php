@extends('portal.app')
@section('title', ($isTr ?? false) ? 'Chatbot Detay' : 'Chatbot Detail')
@section('page-title', 'Study Space')
@php $isTr = app()->getLocale() === 'tr'; @endphp

@section('content')

    {{-- Study Space Chatbot Session Detail --}}
    <div style="max-width:680px;margin:0 auto;">

        {{-- Header --}}
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:20px;">
            <a href="{{ route('portal.reports.app', 'study-space') }}" style="display:flex;align-items:center;gap:6px;text-decoration:none;color:var(--color-txt-muted);font-size:13px;">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div style="width:32px;height:32px;border-radius:8px;background:linear-gradient(135deg,#8B5CF6,#6366F1);display:flex;align-items:center;justify-content:center;">
                <svg width="18" height="18" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
            </div>
            <span style="font-size:18px;font-weight:700;color:#111;font-family:'Nunito',sans-serif;">{{ $isTr ? 'Chatbot Oturum Detayı' : 'Chatbot Session Detail' }}</span>
        </div>

        {{-- Session Info --}}
        <div style="display:flex;gap:16px;align-items:center;padding:12px 16px;background:#F5F3FF;border-radius:10px;margin-bottom:24px;">
            @if(!empty($student))
            <div style="display:flex;align-items:center;gap:8px;">
                <img src="https://ui-avatars.com/api/?name={{ urlencode(($student->name ?? '') . ' ' . ($student->surname ?? '')) }}&size=32&background=8B5CF6&color=fff&rounded=true&bold=true&font-size=0.4" alt="" style="width:28px;height:28px;border-radius:50%;">
                <span style="font-size:12px;font-weight:600;color:#111;">{{ $student->name ?? '' }} {{ $student->surname ?? '' }}</span>
            </div>
            @endif
            <div style="display:flex;align-items:center;gap:4px;">
                <svg width="14" height="14" fill="none" stroke="#8B5CF6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                <span style="font-size:11px;color:#6B7280;">{{ count($messages ?? []) }} {{ $isTr ? 'Mesaj' : 'Messages' }}</span>
            </div>
            @if(!empty($sessionDate))
            <div style="display:flex;align-items:center;gap:4px;">
                <svg width="14" height="14" fill="none" stroke="#8B5CF6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span style="font-size:11px;color:#6B7280;">{{ $sessionDate }}</span>
            </div>
            @endif
        </div>

        {{-- Chat Messages — Thread Grouped by Date --}}
        @php
            // 5.5: Group messages into threads by date
            $threads = collect($messages ?? [])->groupBy(function ($msg) {
                $ts = $msg['created_at'] ?? $msg['timestamp'] ?? null;
                return $ts ? \Carbon\Carbon::parse($ts)->format('Y-m-d') : 'unknown';
            });
        @endphp

        <div style="display:flex;flex-direction:column;gap:8px;">
            @forelse($threads as $dateKey => $threadMessages)
                {{-- Thread Date Separator --}}
                <div style="display:flex;align-items:center;gap:12px;margin:12px 0 4px;">
                    <div style="flex:1;height:1px;background:#E5E7EB;"></div>
                    <span style="padding:4px 12px;border-radius:999px;background:#F5F3FF;color:#8B5CF6;font-size:10px;font-weight:600;white-space:nowrap;">
                        @if($dateKey === 'unknown')
                            {{ $isTr ? 'Tarih Bilinmiyor' : 'Unknown Date' }}
                        @else
                            📅 {{ \Carbon\Carbon::parse($dateKey)->format('d.m.Y') }}
                        @endif
                        · {{ $threadMessages->count() }} {{ $isTr ? 'mesaj' : 'msg' }}
                    </span>
                    <div style="flex:1;height:1px;background:#E5E7EB;"></div>
                </div>

                @foreach($threadMessages as $msg)
                @php
                    $isUser = ($msg['role'] ?? 'user') === 'user';
                @endphp
                <div style="display:flex;gap:10px;{{ $isUser ? '' : 'flex-direction:row-reverse;' }}">
                    {{-- Avatar --}}
                    <div style="flex-shrink:0;width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;
                        {{ $isUser ? 'background:#EEF2FF;' : 'background:linear-gradient(135deg,#8B5CF6,#6366F1);' }}">
                        @if($isUser)
                            <svg width="14" height="14" fill="none" stroke="#6366F1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        @else
                            <svg width="14" height="14" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                        @endif
                    </div>

                    {{-- Message Bubble --}}
                    <div style="flex:1;max-width:80%;padding:12px 16px;border-radius:12px;font-size:13px;line-height:1.6;
                        {{ $isUser ? 'background:#EEF2FF;color:#111;border-bottom-left-radius:4px;' : 'background:#F5F3FF;color:#374151;border-bottom-right-radius:4px;' }}">
                        <div style="font-size:10px;font-weight:600;color:#9CA3AF;margin-bottom:4px;">
                            {{ $isUser ? ($isTr ? 'Öğrenci' : 'Student') : 'AI' }}
                            @if(!empty($msg['created_at']))
                                · {{ \Carbon\Carbon::parse($msg['created_at'])->format('H:i') }}
                            @endif
                        </div>
                        {{ $msg['content'] ?? $msg['text'] ?? '-' }}
                    </div>
                </div>
                @endforeach
            @empty
                <div style="text-align:center;padding:40px 20px;color:var(--color-txt-muted);">
                    <div style="width:48px;height:48px;margin:0 auto 12px;border-radius:50%;background:#F5F3FF;display:flex;align-items:center;justify-content:center;">
                        <svg width="24" height="24" fill="none" stroke="#8B5CF6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                    </div>
                    <p style="font-size:14px;font-weight:500;">{{ $isTr ? 'Henüz mesaj bulunamadı' : 'No messages found' }}</p>
                    <p style="font-size:12px;margin-top:4px;">{{ $isTr ? 'Bu oturumda henüz sohbet geçmişi yok' : 'No chat history for this session yet' }}</p>
                </div>
            @endforelse
        </div>

        {{-- Back button --}}
        <div style="text-align:center;margin-top:24px;">
            <a href="{{ route('portal.reports.app', 'study-space') }}" style="display:inline-flex;align-items:center;gap:6px;text-decoration:none;color:var(--color-txt-muted);font-size:13px;font-weight:500;padding:10px 24px;border:1px solid #E5E7EB;border-radius:8px;">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                {{ $isTr ? 'Geri Dön' : 'Back' }}
            </a>
        </div>
    </div>

@endsection
