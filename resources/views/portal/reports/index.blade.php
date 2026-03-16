@extends('portal.app')
@section('title', __('admin.reports'))
@section('page-title', __('admin.reports'))

@section('content')
<div style="font-size:18px;font-weight:600;margin-bottom:4px;">📊 {{ __('admin.reports') }}</div>
<p style="font-size:13px;color:var(--text-muted);margin-bottom:20px;">Application usage statistics and student progress reports</p>

@if(session('success'))
    <div class="dp-toast">✅ {{ session('success') }}</div>
@endif

{{-- School Admin / Principal View --}}
@if(isset($overview))
    <div class="dp-stats-grid" style="margin-bottom:20px;">
        <div class="dp-stat-card">
            <div class="s-icon"><svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg></div>
            <div class="s-value">{{ $overview['total_users'] }}</div>
            <div class="s-label">Total Users</div>
        </div>
        <div class="dp-stat-card">
            <div class="s-icon"><svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg></div>
            <div class="s-value">{{ $overview['total_students'] }}</div>
            <div class="s-label">Total Students</div>
        </div>
    </div>

    {{-- App Performance Cards --}}
    <div style="font-size:16px;font-weight:600;margin-bottom:12px;">Application Performance</div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:16px;margin-bottom:24px;">
        @foreach($overview['app_stats'] as $stat)
        <a href="{{ route('portal.reports.app', $stat['app']->slug) }}" style="text-decoration:none;">
            <div class="dp-card" style="cursor:pointer;transition:box-shadow .2s;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                    <div style="font-weight:600;font-size:15px;">{{ $stat['app']->name }}</div>
                    <span class="dp-badge dp-badge-pending">{{ $stat['total_users'] }} users</span>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;text-align:center;">
                    <div>
                        <div style="font-size:20px;font-weight:700;color:var(--active-green);">{{ $stat['completed'] }}</div>
                        <div style="font-size:11px;color:var(--text-muted);">Completed</div>
                    </div>
                    <div>
                        <div style="font-size:20px;font-weight:700;color:#fbbf24;">{{ $stat['in_progress'] }}</div>
                        <div style="font-size:11px;color:var(--text-muted);">In Progress</div>
                    </div>
                    <div>
                        <div style="font-size:20px;font-weight:700;color:var(--primary);">{{ $stat['avg_score'] ? number_format($stat['avg_score'], 1) : '-' }}</div>
                        <div style="font-size:11px;color:var(--text-muted);">Avg Score</div>
                    </div>
                </div>
                @if($stat['total_progress'] > 0)
                <div class="dp-progress" style="margin-top:12px;">
                    <div class="dp-progress-fill" style="width:{{ round(($stat['completed'] / max($stat['total_progress'],1))*100) }}%;"></div>
                </div>
                @endif
            </div>
        </a>
        @endforeach
    </div>

    {{-- Charts --}}
    <div class="dp-card" style="margin-bottom:24px;">
        <div class="dp-card-title">Usage Distribution</div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:32px;">
            <div><canvas id="appUsersChart"></canvas></div>
            <div><canvas id="completionChart"></canvas></div>
        </div>
    </div>
@endif

{{-- Teacher View --}}
@if(isset($myClasses))
    <div style="font-size:16px;font-weight:600;margin-bottom:12px;">My Classes</div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px;margin-bottom:24px;">
        @foreach($myClasses as $class)
        <a href="{{ route('portal.reports.class', $class) }}" style="text-decoration:none;">
            <div class="dp-card" style="cursor:pointer;">
                <div style="font-weight:600;font-size:15px;">{{ $class->name }}</div>
                <div style="color:var(--text-muted);font-size:12px;margin-top:4px;">{{ $class->school->name ?? '' }}</div>
                <div style="margin-top:12px;font-size:28px;font-weight:800;color:var(--primary);">{{ $class->students_count }}</div>
                <div style="font-size:12px;color:var(--text-muted);">Students</div>
            </div>
        </a>
        @endforeach
    </div>
@endif

{{-- Student View --}}
@if(isset($studentReport))
    <div style="font-size:16px;font-weight:600;margin-bottom:12px;">My Progress</div>
    @foreach($studentReport as $slug => $appData)
    <div class="dp-card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
            <div class="dp-card-title" style="margin-bottom:0;">{{ $appData['app']->name }}</div>
            <span class="dp-badge {{ $appData['stats']['completion_rate'] >= 80 ? 'dp-badge-active' : ($appData['stats']['completion_rate'] >= 40 ? 'dp-badge-pending' : 'dp-badge-error') }}">{{ $appData['stats']['completion_rate'] }}%</span>
        </div>
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;text-align:center;">
            <div>
                <div style="font-size:20px;font-weight:700;">{{ $appData['stats']['total_modules'] }}</div>
                <div style="font-size:11px;color:var(--text-muted);">Modules</div>
            </div>
            <div>
                <div style="font-size:20px;font-weight:700;color:var(--active-green);">{{ $appData['stats']['completed'] }}</div>
                <div style="font-size:11px;color:var(--text-muted);">Completed</div>
            </div>
            <div>
                <div style="font-size:20px;font-weight:700;color:var(--primary);">{{ $appData['stats']['avg_score'] ? number_format($appData['stats']['avg_score'], 1) : '-' }}</div>
                <div style="font-size:11px;color:var(--text-muted);">Avg Score</div>
            </div>
            <div>
                <div style="font-size:20px;font-weight:700;color:#fbbf24;">{{ $appData['stats']['total_sessions'] }}</div>
                <div style="font-size:11px;color:var(--text-muted);">Sessions</div>
            </div>
        </div>
        <div class="dp-progress" style="margin-top:16px;">
            <div class="dp-progress-fill" style="width:{{ $appData['stats']['completion_rate'] }}%;"></div>
        </div>
    </div>
    @endforeach
@endif
@endsection

@section('scripts')
@if(isset($overview))
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
Chart.defaults.color = '#8B8D97';
Chart.defaults.borderColor = 'rgba(0,0,0,0.06)';

new Chart(document.getElementById('appUsersChart'), {
    type: 'doughnut',
    data: {
        labels: {!! json_encode($overview['app_stats']->pluck('app.name')) !!},
        datasets: [{ data: {!! json_encode($overview['app_stats']->pluck('total_users')) !!}, backgroundColor: ['#4364F7','#8b5cf6','#f59e0b','#ef4444','#10b981','#ec4899'], borderWidth: 0 }]
    },
    options: { responsive: true, plugins: { legend: { position: 'bottom', labels: { padding: 15, usePointStyle: true } } } }
});

new Chart(document.getElementById('completionChart'), {
    type: 'bar',
    data: {
        labels: {!! json_encode($overview['app_stats']->pluck('app.name')) !!},
        datasets: [
            { label: 'Completed', data: {!! json_encode($overview['app_stats']->pluck('completed')) !!}, backgroundColor: 'rgba(74,222,128,0.7)', borderRadius: 6 },
            { label: 'In Progress', data: {!! json_encode($overview['app_stats']->pluck('in_progress')) !!}, backgroundColor: 'rgba(67,100,247,0.7)', borderRadius: 6 },
        ]
    },
    options: { responsive: true, scales: { y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.04)' } } }, plugins: { legend: { position: 'bottom', labels: { padding: 15, usePointStyle: true } } } }
});
</script>
@endif
@endsection
