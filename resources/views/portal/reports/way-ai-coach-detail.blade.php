@extends('portal.app')
@section('title', 'WAY AI Coach — ' . ($student->name ?? ''))
@section('page-title', 'WAY AI Coach — ' . ($student->name ?? '') . ' ' . ($student->surname ?? ''))

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
        <a href="{{ route('portal.reports.student', $student) }}?app=way-ai-coach" class="dp-btn-ghost" style="font-size:12px;">← Full Report</a>
        <a href="{{ route('portal.reports.app', 'way-ai-coach') }}" class="dp-btn-ghost" style="font-size:12px;">← All Students</a>
    </div>
</div>

{{-- ═══ APP BAR ═══ --}}
<div style="display:flex;align-items:center;gap:10px;margin-bottom:20px;">
    <div style="width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,#667eea,#764ba2);display:flex;align-items:center;justify-content:center;font-size:18px;">🤖</div>
    <div style="font-size:18px;font-weight:700;">WAY AI Coach</div>
    <span class="dp-badge dp-badge-active" style="margin-left:auto;">{{ $stats['total_sessions'] }} Sessions</span>
</div>

{{-- ═══ STAT CARDS — Matching vega-dopi VEGA AI Stats + Module Distribution ═══ --}}
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:12px;margin-bottom:24px;">
    <div class="dp-stat-card" style="background:linear-gradient(135deg,#667eea,#764ba2);">
        <div class="s-value">{{ $stats['total_sessions'] }}</div>
        <div class="s-label">Total Sessions</div>
    </div>
    <div class="dp-stat-card" style="background:linear-gradient(135deg,#3b82f6,#1d4ed8);">
        <div class="s-value">{{ $stats['lecturer'] }}</div>
        <div class="s-label">Lecturer</div>
    </div>
    <div class="dp-stat-card" style="background:linear-gradient(135deg,#f59e0b,#d97706);">
        <div class="s-value">{{ $stats['chatbot'] }}</div>
        <div class="s-label">Chatbot</div>
    </div>
    <div class="dp-stat-card" style="background:linear-gradient(135deg,#f093fb,#f5576c);">
        <div class="s-value">{{ $stats['avg_score'] ? number_format($stats['avg_score'], 1) : '-' }}</div>
        <div class="s-label">Avg Score</div>
    </div>
    <div class="dp-stat-card" style="background:linear-gradient(135deg,#4facfe,#00f2fe);">
        <div class="s-value">{{ \App\Services\ReportService::formatDuration($stats['total_duration']) }}</div>
        <div class="s-label">Total Duration</div>
    </div>
    <div class="dp-stat-card" style="background:linear-gradient(135deg,#43e97b,#38f9d7);">
        <div class="s-value">{{ $stats['total_messages'] }}</div>
        <div class="s-label">Total Messages</div>
    </div>
</div>

{{-- ═══ WINGS POINTS — Matching mobile "My Wings" screen with 12 bird categories ═══ --}}
@if(($wingsPoints['total_wings'] ?? 0) > 0)
<div class="dp-card" style="margin-bottom:24px;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
        <div style="display:flex;align-items:center;gap:10px;">
            <span style="font-size:24px;">🦅</span>
            <div>
                <div style="font-size:16px;font-weight:700;">My Wings</div>
                <div style="font-size:12px;color:var(--color-txt-muted);">Achievement points from WAY AI Coach</div>
            </div>
        </div>
        <div style="background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;padding:8px 18px;border-radius:20px;font-weight:700;font-size:18px;">
            {{ $wingsPoints['total_wings'] }} pts
        </div>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:10px;">
        @foreach($wingsPoints['categories'] as $wing)
        <div style="background:var(--color-input-bg);border-radius:10px;padding:12px;text-align:center;transition:transform .2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
            <div style="font-size:24px;margin-bottom:4px;">{{ $wing['emoji'] }}</div>
            <div style="font-size:12px;font-weight:600;margin-bottom:2px;">{{ $wing['label'] }}</div>
            <div style="font-size:18px;font-weight:700;color:var(--color-primary);">{{ $wing['total_score'] }}</div>
            <div style="font-size:10px;color:var(--color-txt-muted);">{{ $wing['sessions'] }} session{{ $wing['sessions'] !== 1 ? 's' : '' }}</div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- ═══ THEME DISTRIBUTION — Matching mobile WayAICoachScreen 13 theme cards ═══ --}}
@if($themeBreakdown->count())
<div class="dp-card" style="margin-bottom:24px;">
    <div class="dp-card-title" style="display:flex;align-items:center;gap:8px;">
        <span style="font-size:20px;">🎯</span> Theme Distribution
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:10px;">
        @foreach($themeBreakdown as $tb)
            @php
                $cfg = $themeConfig[$tb['theme']] ?? ['label' => ucfirst(str_replace('_', ' ', $tb['theme'])), 'color' => '#94a3b8'];
                $themeEmojis = [
                    'emotional' => '❤️', 'future' => '🚀', 'well_being' => '🌱', 'body_movement' => '🏃',
                    'critical_thinking' => '🧠', 'language' => '💬', 'community' => '🤝', 'nature' => '🌿',
                    'art' => '🎨', 'philosophy' => '📖', 'technology' => '💻', 'science' => '🔬', 'free_format' => '✏️',
                ];
                $emoji = $themeEmojis[$tb['theme']] ?? '🌟';
            @endphp
            <div style="background:var(--color-input-bg);border-radius:10px;padding:14px;border-left:4px solid {{ $cfg['color'] ?? '#94a3b8' }};transition:transform .2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">
                    <span style="font-size:20px;">{{ $emoji }}</span>
                    <div style="font-weight:600;font-size:13px;">{{ $cfg['label'] ?? $tb['theme'] }}</div>
                </div>
                <div style="display:flex;gap:12px;font-size:11px;color:var(--text-muted);">
                    <span style="font-weight:600;color:var(--color-txt);">{{ $tb['count'] }} sessions</span>
                    <span>{{ implode(', ', array_map('ucfirst', $tb['modules'])) }}</span>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endif

{{-- ═══ MODULE DISTRIBUTION — Matching vega-dopi admin module distribution ═══ --}}
<div class="dp-card" style="margin-bottom:24px;">
    <div class="dp-card-title" style="display:flex;align-items:center;gap:8px;">
        <span style="font-size:20px;">📊</span> Module Distribution
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
        <div style="text-align:center;padding:20px;background:var(--color-input-bg);border-radius:12px;">
            <div style="width:48px;height:48px;border-radius:12px;background:linear-gradient(135deg,#3b82f6,#1d4ed8);display:flex;align-items:center;justify-content:center;font-size:20px;margin:0 auto 10px;">📘</div>
            <div style="font-size:28px;font-weight:700;color:var(--color-txt);">{{ $stats['lecturer'] }}</div>
            <div style="font-size:13px;color:var(--text-muted);font-weight:500;">Lecturer Sessions</div>
        </div>
        <div style="text-align:center;padding:20px;background:var(--color-input-bg);border-radius:12px;">
            <div style="width:48px;height:48px;border-radius:12px;background:linear-gradient(135deg,#f59e0b,#d97706);display:flex;align-items:center;justify-content:center;font-size:20px;margin:0 auto 10px;">💬</div>
            <div style="font-size:28px;font-weight:700;color:var(--color-txt);">{{ $stats['chatbot'] }}</div>
            <div style="font-size:13px;color:var(--text-muted);font-weight:500;">Chatbot Sessions</div>
        </div>
    </div>
</div>

{{-- ═══ SESSION HISTORY — Matching vega-dopi admin sessions table with type + theme badges ═══ --}}
@if($sessions->count())
<div class="dp-card">
    <div class="dp-card-title" style="display:flex;align-items:center;gap:8px;">
        <span style="font-size:20px;">🕐</span> Session History
    </div>
    <table class="dp-table">
        <thead><tr>
            <th>Date</th><th>Module</th><th>Theme</th>
            <th>Status</th><th>Score</th><th>Messages</th><th>Duration</th><th></th>
        </tr></thead>
        <tbody>
        @foreach($sessions->take(30) as $s)
            @php
                $moduleColor = $s->module === 'lecturer' ? ['bg' => 'rgba(59,130,246,0.1)', 'color' => '#3b82f6'] : ['bg' => 'rgba(245,158,11,0.1)', 'color' => '#f59e0b'];
                $themeCfg = $themeConfig[$s->theme] ?? null;
                $statusClass = match(strtoupper($s->status ?? '')) {
                    'COMPLETED' => 'dp-badge-active',
                    'ACTIVE'    => 'dp-badge-pending',
                    'ENDED'     => 'dp-badge-inactive',
                    default     => 'dp-badge-error',
                };
                $msgCount = ($s->lecturer_messages_count ?? 0) + ($s->chat_messages_count ?? 0);
            @endphp
            <tr>
                <td class="muted">{{ $s->created_at?->format('d.m.Y H:i') }}</td>
                <td>
                    <span class="dp-badge" style="background:{{ $moduleColor['bg'] }};color:{{ $moduleColor['color'] }};">{{ ucfirst($s->module) }}</span>
                </td>
                <td>
                    @if($themeCfg)
                        <span style="display:inline-block;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:500;background:{{ $themeCfg['color'] }}15;color:{{ $themeCfg['color'] }};">{{ $themeCfg['label'] }}</span>
                    @else
                        <span class="muted">{{ ucfirst(str_replace('_', ' ', $s->theme ?? '-')) }}</span>
                    @endif
                </td>
                <td><span class="dp-badge {{ $statusClass }}">{{ ucfirst(strtolower($s->status ?? 'Unknown')) }}</span></td>
                <td>
                    @if($s->score !== null)
                        <span class="dp-badge {{ $s->score >= 70 ? 'dp-badge-active' : ($s->score >= 50 ? 'dp-badge-pending' : 'dp-badge-error') }}">{{ number_format($s->score, 1) }}</span>
                    @else - @endif
                </td>
                <td>{{ $msgCount }}</td>
                <td>{{ $s->duration_seconds ? \App\Services\ReportService::formatDuration($s->duration_seconds) : '-' }}</td>
                <td>
                    <a href="{{ route('portal.reports.session.detail', $s->id) }}" class="dp-btn" style="font-size:11px;padding:4px 10px;">Detail →</a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endif

@if($sessions->isEmpty())
<div class="dp-card" style="text-align:center;padding:48px;">
    <div style="font-size:32px;margin-bottom:8px;">🤖</div>
    <p style="color:var(--text-muted);">No WAY AI Coach session data yet.</p>
</div>
@endif
@endsection
