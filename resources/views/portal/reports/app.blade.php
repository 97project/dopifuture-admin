@extends('portal.layout')
@section('title', $app->name . ' — ' . __('admin.reports'))

@section('content')
<div class="page-header">
    <div style="display:flex; align-items:center; gap:1rem;">
        <a href="{{ route('portal.reports') }}" class="btn btn-ghost btn-sm">← {{ app()->getLocale() === 'tr' ? 'Geri' : 'Back' }}</a>
        <div>
            <h1>{{ $app->name }}</h1>
            <p>{{ app()->getLocale() === 'tr' ? 'Detaylı uygulama raporu' : 'Detailed application report' }}</p>
        </div>
    </div>
</div>

{{-- Summary Stats --}}
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value">{{ $total_progress }}</div>
        <div class="stat-name">{{ app()->getLocale() === 'tr' ? 'Toplam İlerleme' : 'Total Progress' }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-value" style="color:#4ade80;">{{ $total_completed }}</div>
        <div class="stat-name">{{ app()->getLocale() === 'tr' ? 'Tamamlanan' : 'Completed' }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-value" style="color:var(--brand-400);">{{ $avg_score ? number_format($avg_score, 1) : '-' }}</div>
        <div class="stat-name">{{ app()->getLocale() === 'tr' ? 'Ort. Puan' : 'Avg Score' }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-value" style="color:#fbbf24;">{{ $total_sessions }}</div>
        <div class="stat-name">{{ app()->getLocale() === 'tr' ? 'Oturumlar' : 'Sessions' }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-value" style="color:#a78bfa;">{{ \App\Services\ReportService::formatDuration($total_duration ?? 0) }}</div>
        <div class="stat-name">{{ app()->getLocale() === 'tr' ? 'Toplam Süre' : 'Total Duration' }}</div>
    </div>
</div>

{{-- Charts --}}
<div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem; margin-bottom:2rem;">
    <div class="form-card">
        <h3 style="color:white; font-size:0.95rem; font-weight:600; margin-bottom:1rem;">{{ app()->getLocale() === 'tr' ? 'Modül Dağılımı' : 'Module Distribution' }}</h3>
        <canvas id="moduleChart"></canvas>
    </div>
    <div class="form-card">
        <h3 style="color:white; font-size:0.95rem; font-weight:600; margin-bottom:1rem;">{{ app()->getLocale() === 'tr' ? 'Günlük Oturumlar (Son 30 Gün)' : 'Daily Sessions (Last 30 Days)' }}</h3>
        <canvas id="sessionsChart"></canvas>
    </div>
</div>

{{-- Student Performance Table --}}
<div class="data-table-wrap">
    <div class="data-table-header">
        <h3>👥 {{ app()->getLocale() === 'tr' ? 'Öğrenci Performansı' : 'Student Performance' }}</h3>
    </div>
    <table class="data-table">
        <thead>
            <tr>
                <th>{{ app()->getLocale() === 'tr' ? 'Öğrenci' : 'Student' }}</th>
                <th>{{ app()->getLocale() === 'tr' ? 'Toplam' : 'Total' }}</th>
                <th>{{ app()->getLocale() === 'tr' ? 'Tamamlanan' : 'Completed' }}</th>
                <th>{{ app()->getLocale() === 'tr' ? 'Tamamlanma %' : 'Completion %' }}</th>
                <th>{{ app()->getLocale() === 'tr' ? 'Ort. Puan' : 'Avg Score' }}</th>
                <th>{{ app()->getLocale() === 'tr' ? 'Süre' : 'Duration' }}</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($user_stats as $us)
            <tr>
                <td style="color:white; font-weight:500;">{{ $us['user']->name ?? '' }} {{ $us['user']->surname ?? '' }}</td>
                <td>{{ $us['total'] }}</td>
                <td>{{ $us['completed'] }}</td>
                <td>
                    <div style="display:flex; align-items:center; gap:0.5rem;">
                        <div class="progress-bar" style="width:60px;">
                            <div class="fill" style="width:{{ $us['completion_rate'] }}%; background:{{ $us['completion_rate'] >= 80 ? '#4ade80' : ($us['completion_rate'] >= 40 ? '#fbbf24' : '#f87171') }};"></div>
                        </div>
                        <span style="font-size:0.8rem;">{{ $us['completion_rate'] }}%</span>
                    </div>
                </td>
                <td>{{ $us['avg_score'] ? number_format($us['avg_score'], 1) : '-' }}</td>
                <td>{{ \App\Services\ReportService::formatDuration($us['total_duration'] ?? 0) }}</td>
                <td>
                    @if($us['user'])
                    <a href="{{ route('portal.reports.student', $us['user']) }}" class="btn btn-ghost btn-sm">{{ app()->getLocale() === 'tr' ? 'Detay' : 'Detail' }}</a>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="7" style="text-align:center; color:var(--gray-500); padding:2rem;">{{ app()->getLocale() === 'tr' ? 'Henüz veri yok' : 'No data yet' }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
Chart.defaults.color = '#9ca3af';
Chart.defaults.borderColor = 'rgba(255,255,255,0.06)';

@if($module_stats->count())
new Chart(document.getElementById('moduleChart'), {
    type: 'doughnut',
    data: {
        labels: {!! json_encode($module_stats->keys()) !!},
        datasets: [{
            data: {!! json_encode($module_stats->pluck('total')->values()) !!},
            backgroundColor: ['#3b82f6','#8b5cf6','#f59e0b','#ef4444','#10b981'],
            borderWidth: 0
        }]
    },
    options: { responsive: true, plugins: { legend: { position: 'bottom', labels: { padding: 12, usePointStyle: true } } } }
});
@endif

@if($sessions_by_day->count())
new Chart(document.getElementById('sessionsChart'), {
    type: 'line',
    data: {
        labels: {!! json_encode($sessions_by_day->keys()) !!},
        datasets: [{
            label: '{{ app()->getLocale() === "tr" ? "Oturumlar" : "Sessions" }}',
            data: {!! json_encode($sessions_by_day->values()) !!},
            borderColor: '#3b82f6',
            backgroundColor: 'rgba(59,130,246,0.1)',
            fill: true,
            tension: 0.4,
            pointRadius: 3,
        }]
    },
    options: { responsive: true, scales: { y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.04)' } }, x: { grid: { display: false }, ticks: { maxRotation: 45 } } }, plugins: { legend: { display: false } } }
});
@endif
</script>
@endsection
