@extends('portal.app')
@section('title', 'WAY AI Coach — ' . ($student->name ?? ''))
@section('page-title', 'WAY AI Coach — ' . ($student->name ?? '') . ' ' . ($student->surname ?? ''))

@section('content')
@php
    // Theme emojis matching mobile WayAICoachScreen.js icon assets
    $themeEmojis = [
        'emotional'         => '❤️',
        'future'            => '🚀',
        'well_being'        => '🌱',
        'body_movement'     => '🏃',
        'critical_thinking' => '🧠',
        'language'          => '💬',
        'community'         => '🤝',
        'nature'            => '🌿',
        'art'               => '🎨',
        'philosophy'        => '📖',
        'technology'        => '💻',
        'science'           => '🔬',
        'free_format'       => '✏️',
    ];
    // Wings category emojis (bird-based from mobile WingsPointScreen.js)
    $wingsEmojis = [
        'Nature'            => '🐦',
        'Technology'        => '🦅',
        'Emotional'         => '🦜',
        'Future'            => '🕊️',
        'Well-Being'        => '🦢',
        'Body Movement'     => '🦩',
        'Critical Thinking' => '🦉',
        'Art'               => '🐧',
        'Language'          => '🦆',
        'Philosophy'        => '🦚',
        'Community'         => '🐔',
        'Science'           => '🦋',
    ];
@endphp

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

{{-- ═══ STAT CARDS ═══ --}}
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:12px;margin-bottom:24px;">
    <div class="dp-stat-card" style="background:linear-gradient(135deg,#667eea,#764ba2);">
        <div class="s-value">{{ $stats['total_sessions'] }}</div>
        <div class="s-label">{{ __('portal.total_sessions') }}</div>
    </div>
    <div class="dp-stat-card" style="background:linear-gradient(135deg,#3b82f6,#1d4ed8);">
        <div class="s-value">{{ $stats['lecturer'] }}</div>
        <div class="s-label">{{ __('portal.lecturer') }}</div>
    </div>
    <div class="dp-stat-card" style="background:linear-gradient(135deg,#f59e0b,#d97706);">
        <div class="s-value">{{ $stats['chatbot'] }}</div>
        <div class="s-label">{{ __('portal.chatbot') }}</div>
    </div>
    <div class="dp-stat-card" style="background:linear-gradient(135deg,#f093fb,#f5576c);">
        <div class="s-value">{{ $stats['avg_score'] ? number_format($stats['avg_score'], 1) : '-' }}</div>
        <div class="s-label">{{ __('portal.avg_score') }}</div>
    </div>
    <div class="dp-stat-card" style="background:linear-gradient(135deg,#4facfe,#00f2fe);">
        <div class="s-value">{{ \App\Services\ReportService::formatDuration($stats['total_duration']) }}</div>
        <div class="s-label">{{ __('portal.total_duration') }}</div>
    </div>
    <div class="dp-stat-card" style="background:linear-gradient(135deg,#43e97b,#38f9d7);">
        <div class="s-value">{{ $stats['total_messages'] }}</div>
        <div class="s-label">{{ __('portal.total_messages') }}</div>
    </div>
</div>

{{-- ═══ MY WINGS — Matching mobile WingsPointScreen.js 12-bird card grid ═══ --}}
@if(($wingsPoints['total_wings'] ?? 0) > 0)
<div class="dp-card" style="margin-bottom:24px;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
        <div style="display:flex;align-items:center;gap:12px;">
            <div style="width:44px;height:44px;border-radius:50%;background:linear-gradient(135deg,#667eea,#764ba2);display:flex;align-items:center;justify-content:center;font-size:22px;">🦅</div>
            <div>
                <div style="font-size:18px;font-weight:700;">My Wings</div>
                <div style="font-size:12px;color:var(--color-txt-muted);">Achievement points from WAY AI Coach</div>
            </div>
        </div>
        <div style="background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;padding:10px 22px;border-radius:24px;font-weight:700;font-size:20px;box-shadow:0 4px 15px rgba(102,126,234,0.4);">
            🏆 {{ $wingsPoints['total_wings'] }} pts
        </div>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:12px;">
        @foreach($wingsPoints['categories'] as $wing)
        @php
            $wEmoji = $wingsEmojis[$wing['label']] ?? '🐦';
            $wColor = $themeConfig[strtolower(str_replace(['-', ' '], '_', $wing['label']))] ?? null;
            $borderCol = $wColor['color'] ?? '#667eea';
            $isHigh = $wing['total_score'] >= 50;
        @endphp
        <div style="background:var(--color-card-bg);border-radius:14px;padding:16px;text-align:center;border:2px solid {{ $borderCol }}30;transition:all .25s ease;position:relative;overflow:hidden;" onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 8px 20px {{ $borderCol }}20'" onmouseout="this.style.transform='none';this.style.boxShadow='none'">
            <div style="font-size:36px;margin-bottom:8px;">{{ $wEmoji }}</div>
            <div style="font-size:13px;font-weight:600;color:var(--color-txt);margin-bottom:6px;">{{ $wing['label'] }}</div>
            <div style="font-size:26px;font-weight:800;color:{{ $borderCol }};margin-bottom:4px;">{{ $wing['total_score'] }}</div>
            <div style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;{{ $isHigh ? 'background:rgba(96,165,250,0.15);color:#60A5FA;' : 'background:rgba(248,113,113,0.15);color:#F87171;' }}">
                {{ $isHigh ? '▲ High' : '▼ Low' }}
            </div>
            <div style="font-size:10px;color:var(--color-txt-muted);margin-top:4px;">{{ $wing['sessions'] }} session{{ $wing['sessions'] !== 1 ? 's' : '' }}</div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- ═══ THEME DISTRIBUTION — Matching mobile WayAICoachScreen.js 13-theme dark card grid ═══ --}}
<div class="dp-card" style="margin-bottom:24px;">
    <div class="dp-card-title" style="display:flex;align-items:center;gap:8px;margin-bottom:20px;">
        <span style="font-size:20px;">🎯</span> Theme Distribution
        <span style="font-size:12px;color:var(--color-txt-muted);margin-left:auto;">{{ $themeBreakdown->count() }} active themes</span>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:14px;">
        @php
            // Show ALL 13 themes, not just ones with data
            $breakdownMap = $themeBreakdown->keyBy('theme');
        @endphp
        @foreach($themeConfig as $themeKey => $cfg)
            @php
                $td = $breakdownMap[$themeKey] ?? null;
                $count = $td ? $td['count'] : 0;
                $emoji = $themeEmojis[$themeKey] ?? '🌟';
                $isHigh = $count >= 10;
                $isActive = $count > 0;
                $modules = $td ? ($td['modules'] ?? []) : [];
            @endphp
            <div style="background:#1e293b;border-radius:16px;padding:18px;position:relative;overflow:hidden;transition:all .25s ease;{{ !$isActive ? 'opacity:0.5;' : '' }}" onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 8px 24px rgba(0,0,0,0.3)'" onmouseout="this.style.transform='none';this.style.boxShadow='none'">
                {{-- Theme icon --}}
                <div style="font-size:36px;margin-bottom:10px;">{{ $emoji }}</div>
                {{-- Theme title --}}
                <div style="font-size:14px;font-weight:700;color:#fff;margin-bottom:12px;min-height:20px;">{{ $cfg['label'] }}</div>
                {{-- Bottom: Total Interaction + count --}}
                <div style="display:flex;align-items:center;justify-content:space-between;">
                    <div style="font-size:10px;font-weight:600;color:#62748E;text-transform:uppercase;letter-spacing:0.5px;">{{ __('portal.total_interaction') }}</div>
                    <div style="display:inline-flex;align-items:center;gap:6px;border:2px solid {{ $isHigh ? '#60A5FA' : '#F87171' }};border-radius:10px;padding:4px 10px;">
                        <span style="font-size:10px;">{{ $isHigh ? '🟢' : '🔴' }}</span>
                        <span style="font-size:16px;font-weight:700;color:#fff;">{{ $count }}</span>
                    </div>
                </div>
                @if(!empty($modules))
                <div style="margin-top:8px;display:flex;gap:4px;flex-wrap:wrap;">
                    @foreach($modules as $mod)
                        <span style="font-size:10px;padding:2px 8px;border-radius:4px;background:{{ $mod === 'lecturer' ? 'rgba(59,130,246,0.2)' : 'rgba(245,158,11,0.2)' }};color:{{ $mod === 'lecturer' ? '#60A5FA' : '#FBBF24' }};">{{ ucfirst($mod) }}</span>
                    @endforeach
                </div>
                @endif
            </div>
        @endforeach
    </div>
</div>

{{-- ═══ MODULE DISTRIBUTION — Matching vega-dopi admin module distribution ═══ --}}
<div class="dp-card" style="margin-bottom:24px;">
    <div class="dp-card-title" style="display:flex;align-items:center;gap:8px;">
        <span style="font-size:20px;">📊</span> Module Distribution
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
        <div style="text-align:center;padding:24px;background:#1e293b;border-radius:14px;transition:transform .2s;" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='none'">
            <div style="width:52px;height:52px;border-radius:14px;background:linear-gradient(135deg,#3b82f6,#1d4ed8);display:flex;align-items:center;justify-content:center;font-size:22px;margin:0 auto 12px;">📘</div>
            <div style="font-size:32px;font-weight:800;color:#fff;">{{ $stats['lecturer'] }}</div>
            <div style="font-size:13px;color:#94a3b8;font-weight:500;margin-top:4px;">{{ __('portal.lecturer_sessions') }}</div>
        </div>
        <div style="text-align:center;padding:24px;background:#1e293b;border-radius:14px;transition:transform .2s;" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='none'">
            <div style="width:52px;height:52px;border-radius:14px;background:linear-gradient(135deg,#f59e0b,#d97706);display:flex;align-items:center;justify-content:center;font-size:22px;margin:0 auto 12px;">💬</div>
            <div style="font-size:32px;font-weight:800;color:#fff;">{{ $stats['chatbot'] }}</div>
            <div style="font-size:13px;color:#94a3b8;font-weight:500;margin-top:4px;">{{ __('portal.chatbot_sessions') }}</div>
        </div>
    </div>
</div>

{{-- ═══ SESSION HISTORY ═══ --}}
@if($sessions->count())
<div class="dp-card">
    <div class="dp-card-title" style="display:flex;align-items:center;gap:8px;">
        <span style="font-size:20px;">🕐</span> Session History
    </div>
    <table class="dp-table">
        <thead><tr>
            <th>{{ __('admin.date') }}</th><th>{{ __('portal.module') }}</th><th>{{ __('portal.theme') }}</th>
            <th>{{ __('admin.status') }}</th><th>{{ __('portal.score') }}</th><th>{{ __('portal.messages') }}</th><th>{{ __('portal.duration') }}</th><th></th>
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
                        <span style="display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:6px;font-size:11px;font-weight:500;background:{{ $themeCfg['color'] }}15;color:{{ $themeCfg['color'] }};">
                            {{ $themeEmojis[$s->theme] ?? '🌟' }} {{ $themeCfg['label'] }}
                        </span>
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
