@extends('portal.layout')
@section('title', __('admin.reports'))

@section('content')
<div class="page-header">
    <h1>📊 {{ __('admin.reports') }}</h1>
    <p>{{ app()->getLocale() === 'tr' ? 'Uygulama kullanım istatistikleri ve öğrenci ilerleme raporları' : 'Application usage statistics and student progress reports' }}</p>
</div>

@if(session('success'))
    <div class="alert-success">{{ session('success') }}</div>
@endif

{{-- School Admin / Principal View --}}
@if(isset($overview))
    {{-- Overview Stats --}}
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background: rgba(59,130,246,0.15);">
                <svg width="20" height="20" fill="none" stroke="#60a5fa" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <div class="stat-value">{{ $overview['total_users'] }}</div>
            <div class="stat-name">{{ app()->getLocale() === 'tr' ? 'Toplam Kullanıcı' : 'Total Users' }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: rgba(34,197,94,0.15);">
                <svg width="20" height="20" fill="none" stroke="#4ade80" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            </div>
            <div class="stat-value">{{ $overview['total_students'] }}</div>
            <div class="stat-name">{{ app()->getLocale() === 'tr' ? 'Toplam Öğrenci' : 'Total Students' }}</div>
        </div>
    </div>

    {{-- App Performance Cards --}}
    <h2 style="color:white; font-size:1.15rem; font-weight:600; margin-bottom:1rem;">{{ app()->getLocale() === 'tr' ? 'Uygulama Performansı' : 'Application Performance' }}</h2>
    <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(320px,1fr)); gap:1rem; margin-bottom:2rem;">
        @foreach($overview['app_stats'] as $stat)
        <a href="{{ route('portal.reports.app', $stat['app']->slug) }}" style="text-decoration:none;">
            <div class="stat-card" style="cursor:pointer;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
                    <h3 style="color:white; font-size:1rem; font-weight:600;">{{ $stat['app']->name }}</h3>
                    <span class="badge badge-info">{{ $stat['total_users'] }} {{ app()->getLocale() === 'tr' ? 'kullanıcı' : 'users' }}</span>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:0.75rem; text-align:center;">
                    <div>
                        <div style="font-size:1.25rem; font-weight:700; color:#4ade80;">{{ $stat['completed'] }}</div>
                        <div style="font-size:0.7rem; color:var(--gray-500);">{{ app()->getLocale() === 'tr' ? 'Tamamlanan' : 'Completed' }}</div>
                    </div>
                    <div>
                        <div style="font-size:1.25rem; font-weight:700; color:#fbbf24;">{{ $stat['in_progress'] }}</div>
                        <div style="font-size:0.7rem; color:var(--gray-500);">{{ app()->getLocale() === 'tr' ? 'Devam Eden' : 'In Progress' }}</div>
                    </div>
                    <div>
                        <div style="font-size:1.25rem; font-weight:700; color:var(--brand-400);">{{ $stat['avg_score'] ? number_format($stat['avg_score'], 1) : '-' }}</div>
                        <div style="font-size:0.7rem; color:var(--gray-500);">{{ app()->getLocale() === 'tr' ? 'Ort. Puan' : 'Avg Score' }}</div>
                    </div>
                </div>
                @if($stat['total_progress'] > 0)
                <div class="progress-bar" style="margin-top:0.75rem;">
                    <div class="fill" style="width:{{ round(($stat['completed'] / max($stat['total_progress'],1))*100) }}%; background:linear-gradient(90deg,#4ade80,#22d3ee);"></div>
                </div>
                @endif
            </div>
        </a>
        @endforeach
    </div>

    {{-- Chart Section --}}
    <div class="form-card" style="margin-bottom:2rem;">
        <h3 style="color:white; font-size:1rem; font-weight:600; margin-bottom:1rem;">{{ app()->getLocale() === 'tr' ? 'Kullanım Dağılımı' : 'Usage Distribution' }}</h3>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:2rem;">
            <div><canvas id="appUsersChart"></canvas></div>
            <div><canvas id="completionChart"></canvas></div>
        </div>
    </div>
@endif

{{-- Teacher View --}}
@if(isset($myClasses))
    <h2 style="color:white; font-size:1.15rem; font-weight:600; margin-bottom:1rem;">{{ app()->getLocale() === 'tr' ? 'Sınıflarım' : 'My Classes' }}</h2>
    <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:1rem; margin-bottom:2rem;">
        @foreach($myClasses as $class)
        <a href="{{ route('portal.reports.class', $class) }}" style="text-decoration:none;">
            <div class="stat-card" style="cursor:pointer;">
                <h3 style="color:white; font-weight:600;">{{ $class->name }}</h3>
                <div style="color:var(--gray-400); font-size:0.8rem; margin-top:0.25rem;">{{ $class->school->name ?? '' }}</div>
                <div style="margin-top:0.75rem; font-size:1.5rem; font-weight:800; color:var(--brand-400);">{{ $class->students_count }}</div>
                <div style="font-size:0.75rem; color:var(--gray-500);">{{ app()->getLocale() === 'tr' ? 'Öğrenci' : 'Students' }}</div>
            </div>
        </a>
        @endforeach
    </div>
@endif

{{-- Student View --}}
@if(isset($studentReport))
    <h2 style="color:white; font-size:1.15rem; font-weight:600; margin-bottom:1rem;">{{ app()->getLocale() === 'tr' ? 'İlerleme Durumum' : 'My Progress' }}</h2>
    @foreach($studentReport as $slug => $appData)
        <div class="form-card" style="margin-bottom:1.5rem;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
                <h3 style="color:white; font-weight:600;">{{ $appData['app']->name }}</h3>
                <span class="badge {{ $appData['stats']['completion_rate'] >= 80 ? 'badge-success' : ($appData['stats']['completion_rate'] >= 40 ? 'badge-info' : 'badge-danger') }}">
                    {{ $appData['stats']['completion_rate'] }}%
                </span>
            </div>
            <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:1rem; text-align:center;">
                <div>
                    <div style="font-size:1.25rem; font-weight:700; color:white;">{{ $appData['stats']['total_modules'] }}</div>
                    <div style="font-size:0.7rem; color:var(--gray-500);">{{ app()->getLocale() === 'tr' ? 'Modül' : 'Modules' }}</div>
                </div>
                <div>
                    <div style="font-size:1.25rem; font-weight:700; color:#4ade80;">{{ $appData['stats']['completed'] }}</div>
                    <div style="font-size:0.7rem; color:var(--gray-500);">{{ app()->getLocale() === 'tr' ? 'Tamamlanan' : 'Completed' }}</div>
                </div>
                <div>
                    <div style="font-size:1.25rem; font-weight:700; color:var(--brand-400);">{{ $appData['stats']['avg_score'] ? number_format($appData['stats']['avg_score'], 1) : '-' }}</div>
                    <div style="font-size:0.7rem; color:var(--gray-500);">{{ app()->getLocale() === 'tr' ? 'Ort. Puan' : 'Avg Score' }}</div>
                </div>
                <div>
                    <div style="font-size:1.25rem; font-weight:700; color:#fbbf24;">{{ $appData['stats']['total_sessions'] }}</div>
                    <div style="font-size:0.7rem; color:var(--gray-500);">{{ app()->getLocale() === 'tr' ? 'Oturum' : 'Sessions' }}</div>
                </div>
            </div>
            <div class="progress-bar" style="margin-top:1rem;">
                <div class="fill" style="width:{{ $appData['stats']['completion_rate'] }}%; background:linear-gradient(90deg,#4ade80,#22d3ee);"></div>
            </div>
        </div>
    @endforeach
@endif
@endsection

@section('scripts')
@if(isset($overview))
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
const chartDefaults = { color: '#9ca3af', borderColor: 'rgba(255,255,255,0.06)' };
Chart.defaults.color = chartDefaults.color;
Chart.defaults.borderColor = chartDefaults.borderColor;

new Chart(document.getElementById('appUsersChart'), {
    type: 'doughnut',
    data: {
        labels: {!! json_encode($overview['app_stats']->pluck('app.name')) !!},
        datasets: [{
            data: {!! json_encode($overview['app_stats']->pluck('total_users')) !!},
            backgroundColor: ['#3b82f6','#8b5cf6','#f59e0b','#ef4444','#10b981','#ec4899'],
            borderWidth: 0,
        }]
    },
    options: { responsive: true, plugins: { legend: { position: 'bottom', labels: { padding: 15, usePointStyle: true } } } }
});

new Chart(document.getElementById('completionChart'), {
    type: 'bar',
    data: {
        labels: {!! json_encode($overview['app_stats']->pluck('app.name')) !!},
        datasets: [
            { label: '{{ app()->getLocale() === "tr" ? "Tamamlanan" : "Completed" }}', data: {!! json_encode($overview['app_stats']->pluck('completed')) !!}, backgroundColor: 'rgba(74,222,128,0.7)', borderRadius: 6 },
            { label: '{{ app()->getLocale() === "tr" ? "Devam Eden" : "In Progress" }}', data: {!! json_encode($overview['app_stats']->pluck('in_progress')) !!}, backgroundColor: 'rgba(59,130,246,0.7)', borderRadius: 6 },
        ]
    },
    options: { responsive: true, scales: { y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.04)' } } }, plugins: { legend: { position: 'bottom', labels: { padding: 15, usePointStyle: true } } } }
});
</script>
@endif
@endsection
