@extends('portal.app')
@section('title', 'Study Space — ' . ($student->name ?? ''))
@section('page-title', 'Study Space — ' . ($student->name ?? '') . ' ' . ($student->surname ?? ''))

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
        <a href="{{ route('portal.reports.student', $student) }}?app=study-space" class="dp-btn-ghost" style="font-size:12px;">← Full Report</a>
        <a href="{{ route('portal.reports.app', 'study-space') }}" class="dp-btn-ghost" style="font-size:12px;">← All Students</a>
    </div>
</div>

{{-- ═══ APP BAR ═══ --}}
<div style="display:flex;align-items:center;gap:10px;margin-bottom:20px;">
    <div style="width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,#f59e0b,#d97706);display:flex;align-items:center;justify-content:center;font-size:18px;">📚</div>
    <div style="font-size:18px;font-weight:700;">Study Space</div>
    <span class="dp-badge dp-badge-active" style="margin-left:auto;">{{ $stats['total_sessions'] }} Sessions</span>
</div>

{{-- ═══ STAT CARDS ═══ --}}
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:12px;margin-bottom:24px;">
    <div class="dp-stat-card" style="background:linear-gradient(135deg,#f59e0b,#d97706);">
        <div class="s-value">{{ $stats['total_sessions'] }}</div>
        <div class="s-label">Total Sessions</div>
    </div>
    <div class="dp-stat-card" style="background:linear-gradient(135deg,#43e97b,#38f9d7);">
        <div class="s-value">{{ $stats['total_messages'] }}</div>
        <div class="s-label">Total Messages</div>
    </div>
    <div class="dp-stat-card" style="background:linear-gradient(135deg,#4facfe,#00f2fe);">
        <div class="s-value">{{ \App\Services\ReportService::formatDuration($stats['total_duration']) }}</div>
        <div class="s-label">Total Duration</div>
    </div>
</div>

{{-- ═══ THEME DISTRIBUTION ═══ --}}
@if($themeBreakdown->count())
<div class="dp-card" style="margin-bottom:24px;">
    <div class="dp-card-title" style="display:flex;align-items:center;gap:8px;">
        <span style="font-size:20px;">📋</span> Topic Distribution
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(170px,1fr));gap:10px;">
        @foreach($themeBreakdown as $tb)
            @php
                $cfg = $themeConfig[$tb['theme']] ?? ['label' => ucfirst(str_replace('_', ' ', $tb['theme'])), 'color' => '#94a3b8'];
                $topicEmojis = [
                    'emotional' => '❤️', 'future' => '🚀', 'well_being' => '🌱', 'body_movement' => '🏃',
                    'critical_thinking' => '🧠', 'language' => '💬', 'community' => '🤝', 'nature' => '🌿',
                    'art' => '🎨', 'philosophy' => '📖', 'technology' => '💻', 'science' => '🔬', 'free_format' => '✏️',
                ];
                $emoji = $topicEmojis[$tb['theme']] ?? '📚';
            @endphp
            <div style="background:var(--color-input-bg);border-radius:10px;padding:14px;border-left:3px solid {{ $cfg['color'] ?? '#94a3b8' }};transition:transform .2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
                <div style="display:flex;align-items:center;gap:6px;margin-bottom:4px;">
                    <span style="font-size:18px;">{{ $emoji }}</span>
                    <div style="font-weight:600;font-size:13px;">{{ $cfg['label'] ?? $tb['theme'] }}</div>
                </div>
                <div style="font-size:18px;font-weight:700;color:var(--color-primary);">{{ $tb['count'] }}</div>
                <div style="font-size:10px;color:var(--text-muted);">session{{ $tb['count'] != 1 ? 's' : '' }}</div>
            </div>
        @endforeach
    </div>
</div>
@endif

{{-- ═══ SESSION HISTORY ═══ --}}
@if($sessions->count())
<div class="dp-card">
    <div class="dp-card-title" style="display:flex;align-items:center;gap:8px;">
        <span style="font-size:20px;">🕐</span> Chat Session History
    </div>
    <table class="dp-table">
        <thead><tr>
            <th>Date</th><th>Theme</th>
            <th>Status</th><th>Messages</th><th>Duration</th><th></th>
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
                <td><span class="dp-badge {{ $statusClass }}">{{ ucfirst(strtolower($s->status ?? 'Unknown')) }}</span></td>
                <td>{{ $s->chat_messages_count ?? 0 }}</td>
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
    <div style="font-size:32px;margin-bottom:8px;">📚</div>
    <p style="color:var(--text-muted);">No Study Space session data yet.</p>
</div>
@endif
@endsection
