@extends('portal.app')
@section('title', 'Role Galaxy — ' . ($student->name ?? ''))
@section('page-title', 'Role Galaxy — ' . ($student->name ?? '') . ' ' . ($student->surname ?? ''))

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
        <div class="s-label">Total Simulations</div>
    </div>
    <div class="dp-stat-card" style="background:linear-gradient(135deg,#43e97b,#38f9d7);">
        <div class="s-value">{{ $stats['completed'] }}</div>
        <div class="s-label">Completed</div>
    </div>
    <div class="dp-stat-card" style="background:linear-gradient(135deg,#f093fb,#f5576c);">
        <div class="s-value">{{ $stats['avg_score'] ? number_format($stats['avg_score'], 1) : '-' }}</div>
        <div class="s-label">Avg Score</div>
    </div>
    <div class="dp-stat-card" style="background:linear-gradient(135deg,#4facfe,#00f2fe);">
        <div class="s-value">{{ \App\Services\ReportService::formatDuration($stats['total_duration']) }}</div>
        <div class="s-label">Total Duration</div>
    </div>
    <div class="dp-stat-card" style="background:linear-gradient(135deg,#fa709a,#fee140);">
        <div class="s-value">{{ $scenarioBreakdown->count() }}</div>
        <div class="s-label">Scenarios Explored</div>
    </div>
</div>

{{-- ═══ SCENARIO DISTRIBUTION — Matching mobile RoleGalaxyScreen 12 scenario cards ═══ --}}
@if($scenarioBreakdown->count())
<div class="dp-card" style="margin-bottom:24px;">
    <div class="dp-card-title" style="display:flex;align-items:center;gap:8px;">
        <span style="font-size:20px;">🎮</span> Scenario Distribution
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px;">
        @foreach($scenarioBreakdown as $sb)
            @php
                $cfg = $scenarioConfig[$sb['scenario']] ?? ['icon' => '🌟', 'label' => ucfirst(str_replace('_', ' ', $sb['scenario'])), 'color' => '#94a3b8'];
                $completionRate = $sb['count'] > 0 ? round(($sb['completed'] / $sb['count']) * 100) : 0;
            @endphp
            <div style="background:var(--color-input-bg);border-radius:12px;padding:16px;border-left:4px solid {{ $cfg['color'] }};transition:transform .2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
                    <span style="font-size:24px;">{{ $cfg['icon'] }}</span>
                    <div style="font-weight:600;font-size:14px;">{{ $cfg['label'] ?? $sb['scenario'] }}</div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;font-size:11px;color:var(--text-muted);">
                    <div>
                        <div style="font-size:18px;font-weight:700;color:var(--color-txt);">{{ $sb['count'] }}</div>
                        <div>Sessions</div>
                    </div>
                    <div>
                        <div style="font-size:18px;font-weight:700;color:var(--active-green);">{{ $sb['completed'] }}</div>
                        <div>Completed</div>
                    </div>
                    <div>
                        <div style="font-size:18px;font-weight:700;color:var(--primary);">{{ $sb['avg_score'] ? round($sb['avg_score']) : '-' }}</div>
                        <div>Avg Score</div>
                    </div>
                </div>
                <div class="dp-progress" style="margin-top:8px;height:4px;">
                    <div class="dp-progress-fill" style="width:{{ $completionRate }}%;"></div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endif

{{-- ═══ SESSION HISTORY — Matching vega-dopi admin sessions table ═══ --}}
@if($sessions->count())
<div class="dp-card">
    <div class="dp-card-title" style="display:flex;align-items:center;gap:8px;">
        <span style="font-size:20px;">🕐</span> Session History
    </div>
    <table class="dp-table">
        <thead><tr>
            <th>Date</th><th>Scenario</th><th>Status</th>
            <th>Score</th><th>Steps</th><th>Duration</th><th></th>
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
