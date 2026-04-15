@extends('portal.app')
@section('title', 'Role Galaxy — ' . ($student->name ?? ''))
@section('page-title', 'Role Galaxy — ' . ($student->name ?? '') . ' ' . ($student->surname ?? ''))

@section('content')
@php
    // Full 12-scenario config matching mobile RoleGalaxyScreen.js
    $allScenarios = \App\Services\VegaReportService::getScenarioConfig();
    $breakdownMap = $scenarioBreakdown->keyBy('scenario');
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
        <a href="{{ route('portal.reports.student', $student) }}?app=role-galaxy" class="dp-btn-ghost" style="font-size:12px;">← Full Report</a>
        <a href="{{ route('portal.reports.app', 'role-galaxy') }}" class="dp-btn-ghost" style="font-size:12px;">← All Students</a>
    </div>
</div>

{{-- ═══ APP BAR ═══ --}}
<div style="display:flex;align-items:center;gap:10px;margin-bottom:20px;">
    <div style="width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,#f093fb,#f5576c);display:flex;align-items:center;justify-content:center;font-size:18px;">🌟</div>
    <div style="font-size:18px;font-weight:700;">Role Galaxy</div>
    <span class="dp-badge dp-badge-active" style="margin-left:auto;">{{ $stats['total_sessions'] }} Sessions</span>
</div>

{{-- ═══ STAT CARDS ═══ --}}
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:12px;margin-bottom:24px;">
    <div class="dp-stat-card" style="background:linear-gradient(135deg,#667eea,#764ba2);">
        <div class="s-value">{{ $stats['total_sessions'] }}</div>
        <div class="s-label">{{ __('portal.total_simulations') }}</div>
    </div>
    <div class="dp-stat-card" style="background:linear-gradient(135deg,#43e97b,#38f9d7);">
        <div class="s-value">{{ $stats['completed'] }}</div>
        <div class="s-label">{{ __('portal.completed') }}</div>
    </div>
    <div class="dp-stat-card" style="background:linear-gradient(135deg,#f093fb,#f5576c);">
        <div class="s-value">{{ $stats['avg_score'] ? number_format($stats['avg_score'], 1) : '-' }}</div>
        <div class="s-label">{{ __('portal.avg_score') }}</div>
    </div>
    <div class="dp-stat-card" style="background:linear-gradient(135deg,#4facfe,#00f2fe);">
        <div class="s-value">{{ \App\Services\ReportService::formatDuration($stats['total_duration']) }}</div>
        <div class="s-label">{{ __('portal.total_duration') }}</div>
    </div>
    <div class="dp-stat-card" style="background:linear-gradient(135deg,#fa709a,#fee140);">
        <div class="s-value">{{ $scenarioBreakdown->count() }}/{{ count($allScenarios) }}</div>
        <div class="s-label">{{ __('portal.scenarios_explored') }}</div>
    </div>
</div>

{{-- ═══ SCENARIO GALAXY — ALL 12 scenarios, matching mobile RoleGalaxyScreen.js card grid ═══ --}}
<div class="dp-card" style="margin-bottom:24px;">
    <div class="dp-card-title" style="display:flex;align-items:center;gap:8px;margin-bottom:20px;">
        <span style="font-size:20px;">🎮</span> Scenario Galaxy
        <span style="font-size:12px;color:var(--color-txt-muted);margin-left:auto;">{{ $scenarioBreakdown->count() }} of {{ count($allScenarios) }} explored</span>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:14px;">
        @foreach($allScenarios as $scenarioKey => $cfg)
            @php
                $sb = $breakdownMap[$scenarioKey] ?? null;
                $hasPlayed = $sb && $sb['count'] > 0;
                $count = $sb ? $sb['count'] : 0;
                $completed = $sb ? ($sb['completed'] ?? 0) : 0;
                $avgScore = $sb ? ($sb['avg_score'] ?? null) : null;
                $completionRate = $count > 0 ? round(($completed / $count) * 100) : 0;
            @endphp
            <div style="background:#1e293b;border-radius:16px;padding:18px;position:relative;overflow:hidden;transition:all .25s ease;{{ !$hasPlayed ? 'opacity:0.45;' : '' }}" onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 8px 24px rgba(0,0,0,0.3)'" onmouseout="this.style.transform='none';this.style.boxShadow='none'">
                {{-- Played/Not-yet status indicator (matching mobile circleCheck vs notYetIcon) --}}
                <div style="position:absolute;top:10px;right:10px;">
                    @if($hasPlayed)
                        <div style="width:24px;height:24px;border-radius:50%;background:linear-gradient(135deg,#43e97b,#38f9d7);display:flex;align-items:center;justify-content:center;font-size:12px;">✓</div>
                    @else
                        <div style="width:24px;height:24px;border-radius:50%;background:rgba(148,163,184,0.2);display:flex;align-items:center;justify-content:center;font-size:12px;color:#475569;">✗</div>
                    @endif
                </div>

                {{-- Scenario icon --}}
                <div style="font-size:40px;margin-bottom:10px;">{{ $cfg['icon'] }}</div>

                {{-- Scenario title --}}
                <div style="font-size:15px;font-weight:700;color:#fff;margin-bottom:4px;">{{ $cfg['label'] }}</div>

                {{-- Category badge --}}
                <div style="display:inline-block;padding:2px 8px;border-radius:4px;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;background:{{ $cfg['color'] }}20;color:{{ $cfg['color'] }};margin-bottom:12px;">
                    {{ ucfirst(str_replace(['2', '_'], ['', ' '], $cfg['category'])) }}
                </div>

                @if($hasPlayed)
                    {{-- Stats row --}}
                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:6px;text-align:center;">
                        <div>
                            <div style="font-size:18px;font-weight:800;color:#fff;">{{ $count }}</div>
                            <div style="font-size:9px;color:#62748E;text-transform:uppercase;">{{ __('portal.sessions') }}</div>
                        </div>
                        <div>
                            <div style="font-size:18px;font-weight:800;color:#43e97b;">{{ $completed }}</div>
                            <div style="font-size:9px;color:#62748E;text-transform:uppercase;">Done</div>
                        </div>
                        <div>
                            <div style="font-size:18px;font-weight:800;color:{{ $cfg['color'] }};">{{ $avgScore ? round($avgScore) : '-' }}</div>
                            <div style="font-size:9px;color:#62748E;text-transform:uppercase;">{{ __('portal.score') }}</div>
                        </div>
                    </div>
                    {{-- Progress bar --}}
                    <div style="margin-top:10px;height:4px;background:rgba(255,255,255,0.1);border-radius:2px;overflow:hidden;">
                        <div style="height:100%;width:{{ $completionRate }}%;background:linear-gradient(90deg,{{ $cfg['color'] }},{{ $cfg['color'] }}cc);border-radius:2px;transition:width .3s;"></div>
                    </div>
                @else
                    <div style="text-align:center;padding:8px 0;font-size:11px;color:#475569;font-weight:500;">Not yet explored</div>
                @endif
            </div>
        @endforeach
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
            <th>{{ __('admin.date') }}</th><th>Scenario</th><th>{{ __('admin.status') }}</th>
            <th>{{ __('portal.score') }}</th><th>Steps</th><th>{{ __('portal.duration') }}</th><th></th>
        </tr></thead>
        <tbody>
        @foreach($sessions->take(30) as $s)
            @php
                $cfg = $scenarioConfig[$s->scenario] ?? null;
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
                    @if($cfg)
                        <span style="display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:6px;font-size:11px;font-weight:500;background:{{ $cfg['color'] }}15;color:{{ $cfg['color'] }};">
                            {{ $cfg['icon'] }} {{ $cfg['label'] ?? $s->scenario }}
                        </span>
                    @else
                        <span class="dp-badge dp-badge-inactive">{{ $s->scenario ?? '-' }}</span>
                    @endif
                </td>
                <td><span class="dp-badge {{ $statusClass }}">{{ ucfirst(strtolower($s->status ?? 'Unknown')) }}</span></td>
                <td>
                    @if($s->score !== null)
                        <span class="dp-badge {{ $s->score >= 70 ? 'dp-badge-active' : ($s->score >= 50 ? 'dp-badge-pending' : 'dp-badge-error') }}">{{ number_format($s->score, 1) }}</span>
                    @else - @endif
                </td>
                <td>{{ $s->simulatorSteps->count() }} steps</td>
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
    <div style="font-size:32px;margin-bottom:8px;">🌟</div>
    <p style="color:var(--text-muted);">No Role Galaxy simulation data yet.</p>
</div>
@endif
@endsection
