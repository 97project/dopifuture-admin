@extends('portal.app')
@section('title', __('portal.study_space') . ' — ' . ($student->name ?? ''))
@section('page-title', __('portal.study_space') . ' — ' . ($student->name ?? '') . ' ' . ($student->surname ?? ''))

@section('content')
{{-- ═══ PROFILE MINI-HEADER ═══ --}}
<div class="dp-card" style="display:flex;align-items:center;justify-content:space-between;padding:20px 24px;">
    <div style="display:flex;align-items:center;gap:14px;">
        <div class="dp-profile-avatar" style="width:44px;height:44px;font-size:16px;">{{ strtoupper(substr($student->name,0,1).substr($student->surname??'',0,1)) }}</div>
        <div>
            <div style="font-size:18px;font-weight:700;">{{ $student->name }} {{ $student->surname }}</div>
            <div style="font-size:12px;color:var(--text-muted);">{{ $student->email }}</div>
        </div>
    </div>
    <div style="display:flex;gap:8px;">
        <a href="{{ route('portal.reports.student', $student) }}?app=study-space" class="dp-btn-ghost" style="font-size:12px;">← {{ __('portal.full_report') }}</a>
        <a href="{{ route('portal.reports.app', 'study-space') }}" class="dp-btn-ghost" style="font-size:12px;">← {{ __('portal.all_students') }}</a>
    </div>
</div>

{{-- ═══ APP BAR ═══ --}}
<div style="display:flex;align-items:center;gap:10px;margin-bottom:20px;">
    <div style="width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,#f59e0b,#d97706);display:flex;align-items:center;justify-content:center;font-size:18px;">📚</div>
    <div style="font-size:18px;font-weight:700;">Study Space</div>
    <span class="dp-badge dp-badge-active" style="margin-left:auto;">{{ $stats['total_sessions'] }} {{ __('portal.sessions') }}</span>
</div>

{{-- ═══ STAT CARDS ═══ --}}
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:12px;margin-bottom:24px;">
    <div class="dp-stat-card" style="background:linear-gradient(135deg,#f59e0b,#d97706);">
        <div class="s-value">{{ $stats['total_sessions'] }}</div>
        <div class="s-label">{{ __('portal.total_sessions') }}</div>
    </div>
    <div class="dp-stat-card" style="background:linear-gradient(135deg,#43e97b,#38f9d7);">
        <div class="s-value">{{ $stats['total_messages'] }}</div>
        <div class="s-label">{{ __('portal.total_messages') }}</div>
    </div>
    <div class="dp-stat-card" style="background:linear-gradient(135deg,#4facfe,#00f2fe);">
        <div class="s-value">{{ \App\Services\ReportService::formatDuration($stats['total_duration']) }}</div>
        <div class="s-label">{{ __('portal.total_duration') }}</div>
    </div>
</div>

{{-- ═══ TOPIC DISTRIBUTION — Matching dark card grid from WAY AI Coach ═══ --}}
@if($themeBreakdown->count())
<div class="dp-card" style="margin-bottom:24px;">
    <div class="dp-card-title" style="display:flex;align-items:center;gap:8px;margin-bottom:20px;">
        <span style="font-size:20px;">📊</span> {{ __('portal.topic_distribution') }}
        <span style="font-size:12px;color:var(--color-txt-muted);margin-left:auto;">{{ $themeBreakdown->count() }} {{ __('portal.active_topics') }}</span>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:14px;">
        @php
            $topicEmojis = [
                'emotional' => '❤️', 'future' => '🚀', 'well_being' => '🌱', 'body_movement' => '🏃',
                'critical_thinking' => '🧠', 'language' => '💬', 'community' => '🤝', 'nature' => '🌿',
                'art' => '🎨', 'philosophy' => '📖', 'technology' => '💻', 'science' => '🔬', 'free_format' => '✏️',
            ];
        @endphp
        @foreach($themeBreakdown as $tb)
            @php
                $cfg = $themeConfig[$tb['theme']] ?? ['label' => ucfirst(str_replace('_', ' ', $tb['theme'])), 'color' => '#94a3b8'];
                $emoji = $topicEmojis[$tb['theme']] ?? '📚';
                $isHigh = $tb['count'] >= 10;
            @endphp
            <div style="background:#1e293b;border-radius:16px;padding:18px;transition:all .25s ease;" onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 8px 24px rgba(0,0,0,0.3)'" onmouseout="this.style.transform='none';this.style.boxShadow='none'">
                <div style="font-size:36px;margin-bottom:10px;">{{ $emoji }}</div>
                <div style="font-size:14px;font-weight:700;color:#fff;margin-bottom:12px;">{{ $cfg['label'] ?? $tb['theme'] }}</div>
                <div style="display:flex;align-items:center;justify-content:space-between;">
                    <div style="font-size:10px;font-weight:600;color:#62748E;text-transform:uppercase;letter-spacing:0.5px;">{{ __('portal.sessions') }}</div>
                    <div style="display:inline-flex;align-items:center;gap:6px;border:2px solid {{ $isHigh ? '#60A5FA' : '#F87171' }};border-radius:10px;padding:4px 10px;">
                        <span style="font-size:10px;">{{ $isHigh ? '🟢' : '🔴' }}</span>
                        <span style="font-size:16px;font-weight:700;color:#fff;">{{ $tb['count'] }}</span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endif

{{-- ═══ SESSION HISTORY ═══ --}}
@if($sessions->count())
<div class="dp-card">
    <div class="dp-card-title" style="display:flex;align-items:center;gap:8px;">
        <span style="font-size:20px;">💬</span> {{ __('portal.chat_session_history') }}
    </div>
    <table class="dp-table">
        <thead><tr>
            <th>{{ __('admin.date') }}</th><th>{{ __('portal.theme') }}</th>
            <th>{{ __('admin.status') }}</th><th>{{ __('portal.messages') }}</th><th>{{ __('portal.duration') }}</th><th></th>
        </tr></thead>
        <tbody>
        @foreach($sessions->take(30) as $s)
            @php
                $themeCfg = $themeConfig[$s->theme] ?? null;
                $statusClass = match(strtoupper($s->status ?? '')) {
                    'COMPLETED' => 'dp-badge-active',
                    'ACTIVE'    => 'dp-badge-pending',
                    'ENDED'     => 'dp-badge-inactive',
                    default     => 'dp-badge-error',
                };
            @endphp
            <tr>
                <td class="muted">{{ $s->created_at?->format('d.m.Y H:i') }}</td>
                <td>
                    @if($themeCfg)
                        <span style="display:inline-block;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:500;background:{{ $themeCfg['color'] }}15;color:{{ $themeCfg['color'] }};">{{ $themeCfg['label'] }}</span>
                    @else
                        <span class="muted">{{ ucfirst(str_replace('_', ' ', $s->theme ?? '-')) }}</span>
                    @endif
                </td>
                <td><span class="dp-badge {{ $statusClass }}">{{ ucfirst(strtolower($s->status ?? __('portal.unknown'))) }}</span></td>
                <td>{{ $s->chat_messages_count ?? 0 }}</td>
                <td>{{ $s->duration_seconds ? \App\Services\ReportService::formatDuration($s->duration_seconds) : '-' }}</td>
                <td>
                    <a href="{{ route('portal.reports.session.detail', $s->id) }}" class="dp-btn" style="font-size:11px;padding:4px 10px;">{{ __('portal.detail') }} →</a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endif

@if($sessions->isEmpty())
<div class="dp-card" style="text-align:center;padding:48px;">
    <div style="font-size:32px;margin-bottom:8px;">📚</div>
    <p style="color:var(--text-muted);">{{ __('portal.no_data_yet') }}</p>
</div>
@endif
@endsection
