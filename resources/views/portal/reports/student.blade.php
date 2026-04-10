@extends('portal.app')
@section('title', ($student->name ?? '') . ' — ' . __('admin.reports'))
@section('page-title', ($student->name ?? '') . ' ' . ($student->surname ?? '') . ' — Report')

@section('content')
@php
    $selectedApp = request('app');
@endphp

{{-- ═══ PROFILE HEADER — Matching vega-dopi users/detail.blade.php ═══ --}}
<div class="dp-card" style="text-align:center;padding:32px 24px 24px;">
    <div class="dp-profile-avatar">{{ strtoupper(substr($student->name,0,1).substr($student->surname??'',0,1)) }}</div>
    <div style="font-size:22px;font-weight:700;margin-bottom:4px;">{{ $student->name }} {{ $student->surname }}</div>
    <div style="font-size:13px;color:var(--text-muted);margin-bottom:12px;">{{ $student->email }}</div>

    <div style="display:flex;justify-content:center;gap:8px;flex-wrap:wrap;margin-bottom:16px;">
        @foreach($student->roles as $r)
            <span class="dp-badge dp-badge-active">{{ $r->name }}</span>
        @endforeach
        @if($student->schools->isNotEmpty())
            <span class="dp-badge dp-badge-pending">{{ $student->schools->pluck('name')->join(', ') }}</span>
        @endif
        @if($student->classes->isNotEmpty())
            <span class="dp-badge" style="background:rgba(168,85,247,0.1);color:#7c3aed;">{{ $student->classes->pluck('name')->join(', ') }}</span>
        @endif
    </div>

    <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('portal.reports') }}" class="dp-btn-ghost" style="font-size:12px;">
        ← Back to List
    </a>
</div>

{{-- ═══ SUMMARY STAT CARDS — Matching vega-dopi user-stat-card pattern ═══ --}}
@php
    $totalSessions = 0;
    $totalCompleted = 0;
    $totalScore = 0;
    $scoreCount = 0;
    $totalDuration = 0;

    foreach ($reportData as $slug => $appData) {
        $totalSessions += $appData['stats']['total_sessions'] ?? 0;
        $totalCompleted += $appData['stats']['completed'] ?? 0;
        $totalDuration += $appData['sessions']->sum('duration_seconds');
        if ($appData['stats']['avg_score']) {
            $totalScore += $appData['stats']['avg_score'];
            $scoreCount++;
        }
    }
    $avgScore = $scoreCount > 0 ? round($totalScore / $scoreCount, 1) : null;
@endphp

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:12px;margin-bottom:24px;">
    <div class="dp-stat-card" style="background:linear-gradient(135deg,#667eea,#764ba2);">
        <div class="s-value">{{ $totalSessions }}</div>
        <div class="s-label">Total Sessions</div>
    </div>
    <div class="dp-stat-card" style="background:linear-gradient(135deg,#43e97b,#38f9d7);">
        <div class="s-value">{{ $totalCompleted }}</div>
        <div class="s-label">Completed</div>
    </div>
    <div class="dp-stat-card" style="background:linear-gradient(135deg,#f093fb,#f5576c);">
        <div class="s-value">{{ $avgScore ?? '-' }}</div>
        <div class="s-label">Avg Score</div>
    </div>
    <div class="dp-stat-card" style="background:linear-gradient(135deg,#4facfe,#00f2fe);">
        <div class="s-value">{{ \App\Services\ReportService::formatDuration($totalDuration) }}</div>
        <div class="s-label">Total Duration</div>
    </div>
    <div class="dp-stat-card" style="background:linear-gradient(135deg,#fa709a,#fee140);">
        <div class="s-value">{{ count($reportData) }}</div>
        <div class="s-label">Active Apps</div>
    </div>
</div>

{{-- ═══ WINGS POINTS — Matching mobile "My Wings" screen ═══ --}}
@if(($wingsPoints['total_wings'] ?? 0) > 0)
<div class="dp-card" style="margin-bottom:24px;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
        <div style="display:flex;align-items:center;gap:10px;">
            <span style="font-size:24px;">🦅</span>
            <div>
                <div style="font-size:16px;font-weight:700;">My Wings</div>
                <div style="font-size:12px;color:var(--color-txt-muted);">Achievement points across all apps</div>
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

{{-- ═══ WING BADGES (API Catalog) ═══ --}}
@if(!empty($wingBadges) && $wingBadges->count() > 0)
<div class="dp-card" style="margin-bottom:24px;">
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;">
        <span style="font-size:24px;">🦋</span>
        <div>
            <div style="font-size:16px;font-weight:700;">Available Wing Badges</div>
            <div style="font-size:12px;color:var(--color-txt-muted);">All collectible badges from the platform</div>
        </div>
        @if(!empty($premiumStatus))
        <div style="margin-left:auto;">
            <span style="display:inline-flex;align-items:center;gap:6px;padding:6px 14px;border-radius:20px;background:linear-gradient(135deg,#F59E0B,#EF4444);color:#fff;font-size:12px;font-weight:600;">
                ⭐ {{ is_array($premiumStatus) ? ($premiumStatus['plan'] ?? ($premiumStatus['status'] ?? 'Premium')) : ($premiumStatus === true ? 'Premium' : $premiumStatus) }}
            </span>
        </div>
        @endif
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));gap:10px;">
        @foreach($wingBadges as $badge)
        <div style="background:var(--color-input-bg);border-radius:10px;padding:12px;text-align:center;transition:transform .15s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
            @if(!empty($badge['iconUrl'] ?? $badge['icon']))
                <img src="{{ $badge['iconUrl'] ?? $badge['icon'] }}" alt="" style="width:40px;height:40px;border-radius:8px;margin-bottom:6px;">
            @else
                <div style="width:40px;height:40px;border-radius:8px;background:linear-gradient(135deg,#8B5CF6,#06B6D4);display:flex;align-items:center;justify-content:center;font-size:18px;margin:0 auto 6px;">🦋</div>
            @endif
            <div style="font-size:11px;font-weight:600;">{{ $badge['name'] ?? $badge['title'] ?? 'Badge' }}</div>
            <div style="font-size:10px;color:var(--color-txt-muted);">{{ $badge['pointsRequired'] ?? $badge['points'] ?? 0 }} pts</div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- ═══ ENRICHMENT (Score Trend + Breakdowns — loaded via AJAX) ═══ --}}
<div id="enrichment-section"></div>

{{-- ═══ APP TABS — Click to filter ═══ --}}
<div class="dp-tabs" style="margin-bottom:20px;">
    <a class="dp-tab {{ !$selectedApp ? 'active' : '' }}" href="{{ route('portal.reports.student', $student->id) }}" style="cursor:pointer;">
        All Apps
    </a>
    @foreach($apps as $a)
        @php $hasData = isset($reportData[$a->slug]); @endphp
        @if($hasData)
            @php
                $cr = $reportData[$a->slug]['stats']['completion_rate'];
                $sess = $reportData[$a->slug]['stats']['total_sessions'] ?? 0;
            @endphp
            <a class="dp-tab {{ $selectedApp === $a->slug ? 'active' : '' }}" href="{{ route('portal.reports.student', $student->id) }}?app={{ $a->slug }}" style="cursor:pointer;">
                {{ $a->name }}
                @if($cr > 0)
                    <span class="tab-count">{{ $cr }}%</span>
                @elseif($sess > 0)
                    <span class="tab-count" style="background:rgba(59,130,246,0.1);color:#3b82f6;">{{ $sess }} sessions</span>
                @else
                    <span class="tab-count" style="background:rgba(148,163,184,0.1);color:#94a3b8;">No activity</span>
                @endif
            </a>
        @endif
    @endforeach
</div>

{{-- ═══ PER-APP REPORT CARDS ═══ --}}
@foreach($reportData as $slug => $appData)
@if(!$selectedApp || $selectedApp === $slug)
<div class="dp-card" id="app-{{ $slug }}">
    {{-- Card Header --}}
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
        <div style="display:flex;align-items:center;gap:10px;">
            @php
                $appColors = [
                    'role-galaxy' => ['bg' => 'linear-gradient(135deg,#f093fb,#f5576c)', 'icon' => '🌟'],
                    'study-space' => ['bg' => 'linear-gradient(135deg,#f59e0b,#d97706)', 'icon' => '📚'],
                    'way-ai-coach' => ['bg' => 'linear-gradient(135deg,#667eea,#764ba2)', 'icon' => '🤖'],
                    'mission-way' => ['bg' => 'linear-gradient(135deg,#43e97b,#38f9d7)', 'icon' => '🎯'],
                    'way-startup' => ['bg' => 'linear-gradient(135deg,#4facfe,#00f2fe)', 'icon' => '🚀'],
                ];
                $ac = $appColors[$slug] ?? ['bg' => 'linear-gradient(135deg,var(--primary),var(--primary-deep))', 'icon' => '📋'];
            @endphp
            <div style="width:36px;height:36px;border-radius:10px;background:{{ $ac['bg'] }};display:flex;align-items:center;justify-content:center;font-size:18px;">{{ $ac['icon'] }}</div>
            <div class="dp-card-title" style="margin-bottom:0;">{{ $appData['app']->name }}</div>
        </div>
        <span class="dp-badge {{ $appData['stats']['completion_rate'] >= 80 ? 'dp-badge-active' : ($appData['stats']['completion_rate'] >= 40 ? 'dp-badge-pending' : 'dp-badge-error') }}">{{ $appData['stats']['completion_rate'] }}%</span>
    </div>

    {{-- App Stats Row --}}
    <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:12px;text-align:center;margin-bottom:16px;">
        <div>
            <div style="font-size:22px;font-weight:700;">{{ $appData['stats']['total_modules'] }}</div>
            <div style="font-size:11px;color:var(--text-muted);">Modules</div>
        </div>
        <div>
            <div style="font-size:22px;font-weight:700;color:var(--active-green);">{{ $appData['stats']['completed'] }}</div>
            <div style="font-size:11px;color:var(--text-muted);">Completed</div>
        </div>
        <div>
            <div style="font-size:22px;font-weight:700;color:#fbbf24;">{{ $appData['stats']['in_progress'] }}</div>
            <div style="font-size:11px;color:var(--text-muted);">In Progress</div>
        </div>
        <div>
            <div style="font-size:22px;font-weight:700;color:var(--primary);">{{ $appData['stats']['avg_score'] ? number_format($appData['stats']['avg_score'], 1) : '-' }}</div>
            <div style="font-size:11px;color:var(--text-muted);">Avg Score</div>
        </div>
        <div>
            <div style="font-size:22px;font-weight:700;color:#a78bfa;">{{ $appData['stats']['total_sessions'] }}</div>
            <div style="font-size:11px;color:var(--text-muted);">Sessions</div>
        </div>
    </div>

    <div class="dp-progress" style="margin-bottom:20px;">
        <div class="dp-progress-fill" style="width:{{ $appData['stats']['completion_rate'] }}%;"></div>
    </div>

    {{-- Module Progress --}}
    @if($appData['progress']->count())
    <div class="dp-card-title" style="font-size:14px;">Module Progress</div>
    <table class="dp-table">
        <thead><tr>
            <th>Module</th><th>Type</th>
            <th>Status</th><th>Score</th>
            <th>Attempts</th><th>Date</th>
        </tr></thead>
        <tbody>
        @foreach($appData['progress'] as $p)
        @php $pObj = (object) $p; @endphp
        <tr>
            <td style="font-weight:500;">{{ $pObj->module_name ?? $pObj->module_id ?? '-' }}</td>
            <td>
                @php
                    $typeColors = [
                        'simulator' => ['bg' => 'rgba(240,147,251,0.1)', 'color' => '#d946ef'],
                        'lecturer'  => ['bg' => 'rgba(59,130,246,0.1)', 'color' => '#3b82f6'],
                        'chatbot'   => ['bg' => 'rgba(245,158,11,0.1)', 'color' => '#f59e0b'],
                    ];
                    $mType = $pObj->module_type ?? 'module';
                    $tc = $typeColors[$mType] ?? ['bg' => 'rgba(40,68,225,0.1)', 'color' => 'var(--primary)'];
                @endphp
                <span class="dp-badge" style="background:{{ $tc['bg'] }};color:{{ $tc['color'] }};">{{ ucfirst($mType) }}</span>
            </td>
            <td>
                @php
                    $statusValue = $pObj->status ?? 'not_started';
                    $statusMap = [
                        'completed'   => ['class' => 'dp-badge-active', 'label' => 'Completed'],
                        'in_progress' => ['class' => 'dp-badge-pending', 'label' => 'In Progress'],
                        'not_started' => ['class' => 'dp-badge-inactive', 'label' => 'Not Started'],
                    ];
                    $sm = $statusMap[$statusValue] ?? ['class' => 'dp-badge-error', 'label' => ucfirst($statusValue)];
                @endphp
                <span class="dp-badge {{ $sm['class'] }}">{{ $sm['label'] }}</span>
            </td>
            <td>{{ isset($pObj->score) && $pObj->score !== null ? number_format((float)$pObj->score, 1) : '-' }}{{ !empty($pObj->max_score) ? '/'.$pObj->max_score : '' }}</td>
            <td>{{ $pObj->attempts ?? 0 }}</td>
            <td class="muted">
                @php
                    $cAt = data_get($p, 'completed_at');
                    $sAt = data_get($p, 'started_at');
                    if ($cAt) echo $cAt instanceof \Carbon\Carbon ? $cAt->format('d.m.Y H:i') : \Carbon\Carbon::parse($cAt)->format('d.m.Y H:i');
                    elseif ($sAt) echo $sAt instanceof \Carbon\Carbon ? $sAt->format('d.m.Y H:i') : \Carbon\Carbon::parse($sAt)->format('d.m.Y H:i');
                    else echo '-';
                @endphp
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
    @endif

    {{-- Sessions --}}
    @if($appData['sessions']->count())
    <div class="dp-card-title" style="font-size:14px;margin-top:16px;">Session History</div>
    <table class="dp-table" id="session-table-{{ $slug }}">
        <thead><tr>
            <th>Session</th><th>Type</th>
            <th>Status</th><th>Started</th><th>Duration</th>
            <th>Score</th><th>Threshold</th>
            <th style="width:80px;"></th>
        </tr></thead>
        <tbody>
        @foreach($appData['sessions']->take(20) as $s)
        @php
            $sessionDbId = $s->vega_session_id ?? null;
        @endphp
        <tr>
            <td style="font-weight:500;">{{ $s->session_name ?: $s->external_session_id }}</td>
            <td>
                @php
                    $tc = $typeColors[$s->session_type] ?? ['bg' => 'rgba(40,68,225,0.1)', 'color' => 'var(--primary)'];
                @endphp
                <span class="dp-badge" style="background:{{ $tc['bg'] }};color:{{ $tc['color'] }};">{{ ucfirst($s->session_type) }}</span>
            </td>
            <td>
                @if(!empty($s->status))
                    @php
                        $statusUpper = strtoupper($s->status);
                        $statusClass = match($statusUpper) {
                            'ACTIVE' => 'dp-badge-active',
                            'COMPLETED' => 'dp-badge-active',
                            'ENDED' => 'dp-badge-inactive',
                            'ABANDONED' => 'dp-badge-error',
                            default => 'dp-badge-inactive',
                        };
                        $statusLabel = match($statusUpper) {
                            'ACTIVE' => 'Active',
                            'COMPLETED' => 'Completed',
                            'ENDED' => 'Ended',
                            'ABANDONED' => 'Abandoned',
                            default => $s->status,
                        };
                    @endphp
                    <span class="dp-badge {{ $statusClass }}">{{ $statusLabel }}</span>
                @else
                    -
                @endif
            </td>
            <td class="muted">{{ $s->started_at ? $s->started_at->format('d.m.Y H:i') : '-' }}</td>
            <td>{{ $s->duration_seconds ? \App\Services\ReportService::formatDuration($s->duration_seconds) : '-' }}</td>
            <td>{{ $s->score !== null ? number_format($s->score, 1) : '-' }}</td>
            <td>
                @if(!empty($s->threshold))
                    @php
                        // Threshold config: Turkish keys → English labels with colors
                        $thresholdMap = [
                            'refah'   => ['label' => 'Prosperity', 'bg' => '#dcfce7', 'text' => '#166534'],
                            'denge'   => ['label' => 'Balance',    'bg' => '#dbeafe', 'text' => '#1e40af'],
                            'kriz'    => ['label' => 'Crisis',     'bg' => '#fed7aa', 'text' => '#9a3412'],
                            'felaket' => ['label' => 'Disaster',   'bg' => '#fecaca', 'text' => '#991b1b'],
                        ];
                        $th = $thresholdMap[$s->threshold] ?? ['label' => ucfirst($s->threshold), 'bg' => '#f3f4f6', 'text' => '#374151'];
                    @endphp
                    <span style="display:inline-block;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:500;background:{{ $th['bg'] }};color:{{ $th['text'] }};">{{ $th['label'] }}</span>
                @else
                    -
                @endif
            </td>
            <td>
                @if($sessionDbId)
                    <a href="{{ route('portal.reports.session.detail', $sessionDbId) }}" class="dp-btn" style="font-size:11px;padding:4px 10px;">
                        Detail →
                    </a>
                @endif
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
    @endif
</div>
@endif
@endforeach

@if(empty($reportData))
<div class="dp-card" style="text-align:center;padding:48px;">
    <div style="font-size:32px;margin-bottom:8px;">📭</div>
    <p style="color:var(--text-muted);">No report data yet. Data will appear after application sync.</p>
</div>
@endif
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ── Enrichment Data (Scenario Breakdown + Theme Breakdown + Score Trend) ──
    const enrichmentUrl = "{{ route('portal.reports.student.enrichment', $student) }}";

    function ucfirst(str) {
        if (!str) return '';
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    fetch(enrichmentUrl)
        .then(r => r.json())
        .then(data => {
            let html = '';

            // Score Trend Chart
            if (data.score_trend && data.score_trend.length >= 2) {
                html += '<div class="dp-card" style="margin-bottom:16px;">';
                html += '<div class="dp-card-title" style="font-size:14px;">Score Trend (Recent Simulations)</div>';
                html += '<div style="height:200px;"><canvas id="scoreTrendChart"></canvas></div>';
                html += '</div>';
            }

            // Scenario Breakdown
            if (data.scenario_breakdown && data.scenario_breakdown.length > 0) {
                html += '<div class="dp-card" style="margin-bottom:16px;">';
                html += '<div class="dp-card-title" style="font-size:14px;">Scenario Breakdown (Role Galaxy)</div>';
                html += '<div style="display:flex;flex-wrap:wrap;gap:8px;">';

                const scenarioColors = {
                    village_life: '#F472B6', world_traveler: '#A78BFA', novaris: '#22D3EE',
                    biolab: '#94A3B8', what_if: '#FB923C', lost_egg: '#4ADE80',
                    balance_garden: '#34D399', plan_tomorrow: '#60A5FA', heart_bridge: '#F87171',
                    dream_workshop: '#FB7185', trace_center: '#818CF8', movement_island: '#FBBF24'
                };

                data.scenario_breakdown.forEach(s => {
                    const color = scenarioColors[s.scenario] || '#94a3b8';
                    const avgScore = s.avg_score ? Math.round(s.avg_score) : '-';
                    html += '<div style="background:#fff;border:1px solid #e5e7eb;border-radius:8px;padding:10px 14px;min-width:160px;flex:1;">';
                    html += '<div style="display:flex;align-items:center;gap:6px;margin-bottom:4px;">';
                    html += '<span style="width:8px;height:8px;border-radius:50%;background:' + color + ';display:inline-block;"></span>';
                    html += '<span style="font-weight:600;font-size:13px;">' + ucfirst(s.scenario.replace(/_/g, ' ')) + '</span>';
                    html += '</div>';
                    html += '<div style="display:flex;gap:12px;font-size:11px;color:var(--text-muted);">';
                    html += '<span>' + s.count + ' sessions</span>';
                    html += '<span>' + s.completed + ' completed</span>';
                    html += '<span>Avg: ' + avgScore + '</span>';
                    html += '</div>';
                    html += '</div>';
                });
                html += '</div></div>';
            }

            // Theme Breakdown
            if (data.theme_breakdown && data.theme_breakdown.length > 0) {
                html += '<div class="dp-card" style="margin-bottom:16px;">';
                html += '<div class="dp-card-title" style="font-size:14px;">Theme Breakdown (Way AI Coach & Study Space)</div>';
                html += '<div style="display:flex;flex-wrap:wrap;gap:8px;">';

                const themeColors = {
                    emotional: '#F87171', future: '#60A5FA', well_being: '#34D399',
                    body_movement: '#FBBF24', critical_thinking: '#818CF8', language: '#A78BFA',
                    community: '#F472B6', nature: '#4ADE80', art: '#FB7185',
                    philosophy: '#94A3B8', technology: '#22D3EE', science: '#6366f1',
                    free_format: '#9ca3af'
                };

                data.theme_breakdown.forEach(t => {
                    const color = themeColors[t.theme] || '#94a3b8';
                    html += '<div style="background:#fff;border:1px solid #e5e7eb;border-radius:8px;padding:8px 12px;display:flex;align-items:center;gap:8px;">';
                    html += '<span style="width:8px;height:8px;border-radius:50%;background:' + color + ';display:inline-block;"></span>';
                    html += '<span style="font-weight:500;font-size:13px;">' + ucfirst(t.theme.replace(/_/g, ' ')) + '</span>';
                    html += '<span class="dp-badge dp-badge-pending" style="font-size:10px;">' + t.count + '</span>';
                    html += '<span style="font-size:10px;color:var(--text-muted);">' + t.modules.join(', ') + '</span>';
                    html += '</div>';
                });
                html += '</div></div>';
            }

            document.getElementById('enrichment-section').innerHTML = html;

            // Chart.js render
            if (data.score_trend && data.score_trend.length >= 2) {
                setTimeout(() => {
                    const ctx = document.getElementById('scoreTrendChart');
                    if (ctx) {
                        new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: data.score_trend.map(d => d.label),
                                datasets: [{
                                    label: 'Score',
                                    data: data.score_trend.map(d => d.score),
                                    borderColor: '#667eea',
                                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                                    fill: true, tension: 0.4,
                                    pointRadius: 4, pointBackgroundColor: '#667eea', borderWidth: 2,
                                }]
                            },
                            options: {
                                responsive: true, maintainAspectRatio: false,
                                plugins: { legend: { display: false } },
                                scales: {
                                    y: { beginAtZero: true, max: 100, grid: { color: '#f1f5f9' } },
                                    x: { grid: { display: false } }
                                }
                            }
                        });
                    }
                }, 100);
            }
        })
        .catch(err => console.warn('Enrichment data could not be loaded:', err));
});
</script>
@endsection
