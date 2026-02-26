@extends('admin.layouts.app')
@section('title', __('admin.reports'))

@section('breadcrumb')
    <span>{{ __('admin.reports') }}</span>
@endsection

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">📊 {{ __('admin.reports') }}</h1>
    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ app()->getLocale() === 'tr' ? 'Sistem geneli uygulama ve okul raporları' : 'System-wide application and school reports' }}</p>
</div>

{{-- Overview Stats --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5">
        <div class="text-3xl font-extrabold text-gray-900 dark:text-white">{{ $overview['total_users'] }}</div>
        <div class="text-xs text-gray-500 mt-1">{{ app()->getLocale() === 'tr' ? 'Toplam Kullanıcı' : 'Total Users' }}</div>
    </div>
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5">
        <div class="text-3xl font-extrabold text-emerald-500">{{ $overview['total_students'] }}</div>
        <div class="text-xs text-gray-500 mt-1">{{ app()->getLocale() === 'tr' ? 'Toplam Öğrenci' : 'Total Students' }}</div>
    </div>
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5">
        <div class="text-3xl font-extrabold text-blue-500">{{ $apps->count() }}</div>
        <div class="text-xs text-gray-500 mt-1">{{ app()->getLocale() === 'tr' ? 'Uygulama' : 'Applications' }}</div>
    </div>
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5">
        <div class="text-3xl font-extrabold text-amber-500">{{ $schools->count() }}</div>
        <div class="text-xs text-gray-500 mt-1">{{ app()->getLocale() === 'tr' ? 'Okul' : 'Schools' }}</div>
    </div>
</div>

{{-- Charts --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">{{ app()->getLocale() === 'tr' ? 'Uygulama Kullanımı' : 'Application Usage' }}</h3>
        <canvas id="appUsageChart" height="200"></canvas>
    </div>
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">{{ app()->getLocale() === 'tr' ? 'Tamamlanma Durumu' : 'Completion Status' }}</h3>
        <canvas id="completionChart" height="200"></canvas>
    </div>
</div>

{{-- App Cards --}}
<h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ app()->getLocale() === 'tr' ? 'Uygulama Performansı' : 'Application Performance' }}</h2>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-8">
    @foreach($overview['app_stats'] as $stat)
    <a href="{{ route('admin.reports.app', $stat['app']->slug) }}" class="block bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5 hover:border-blue-400 dark:hover:border-blue-600 transition-all group">
        <div class="flex justify-between items-center mb-3">
            <h3 class="font-semibold text-gray-900 dark:text-white group-hover:text-blue-500 transition-colors">{{ $stat['app']->name }}</h3>
            <span class="px-2 py-0.5 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 text-xs font-semibold rounded-md">{{ $stat['total_users'] }} {{ app()->getLocale() === 'tr' ? 'kullanıcı' : 'users' }}</span>
        </div>
        <div class="grid grid-cols-3 gap-3 text-center">
            <div>
                <div class="text-lg font-bold text-emerald-500">{{ $stat['completed'] }}</div>
                <div class="text-[10px] text-gray-500">{{ app()->getLocale() === 'tr' ? 'Tamamlanan' : 'Completed' }}</div>
            </div>
            <div>
                <div class="text-lg font-bold text-amber-500">{{ $stat['in_progress'] }}</div>
                <div class="text-[10px] text-gray-500">{{ app()->getLocale() === 'tr' ? 'Devam Eden' : 'In Progress' }}</div>
            </div>
            <div>
                <div class="text-lg font-bold text-blue-500">{{ $stat['avg_score'] ? number_format($stat['avg_score'], 1) : '-' }}</div>
                <div class="text-[10px] text-gray-500">{{ app()->getLocale() === 'tr' ? 'Ort. Puan' : 'Avg Score' }}</div>
            </div>
        </div>
        @if($stat['total_progress'] > 0)
        <div class="w-full h-1.5 bg-gray-100 dark:bg-gray-800 rounded-full mt-3 overflow-hidden">
            <div class="h-full rounded-full bg-gradient-to-r from-emerald-400 to-cyan-400" style="width: {{ round(($stat['completed'] / max($stat['total_progress'],1))*100) }}%"></div>
        </div>
        @endif
    </a>
    @endforeach
</div>

{{-- Schools Table --}}
<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">🏫 {{ app()->getLocale() === 'tr' ? 'Okul Dağılımı' : 'School Distribution' }}</h3>
    </div>
    <table class="w-full">
        <thead>
            <tr class="bg-gray-50 dark:bg-gray-800/50">
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ app()->getLocale() === 'tr' ? 'Okul' : 'School' }}</th>
                <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ app()->getLocale() === 'tr' ? 'Kullanıcı' : 'Users' }}</th>
                <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ app()->getLocale() === 'tr' ? 'Sınıf' : 'Classes' }}</th>
                <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ app()->getLocale() === 'tr' ? 'Lisans' : 'Licenses' }}</th>
                <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
            @foreach($schools as $s)
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors">
                <td class="px-5 py-3 text-sm font-medium text-gray-900 dark:text-white">{{ $s->name }}</td>
                <td class="px-5 py-3 text-sm text-gray-500 dark:text-gray-400 text-center">{{ $s->users_count }}</td>
                <td class="px-5 py-3 text-sm text-gray-500 dark:text-gray-400 text-center">{{ $s->classes_count }}</td>
                <td class="px-5 py-3 text-sm text-gray-500 dark:text-gray-400 text-center">{{ $s->licenses_count }}</td>
                <td class="px-5 py-3 text-right">
                    <a href="{{ route('admin.reports.school', $s) }}" class="text-xs font-medium text-blue-600 dark:text-blue-400 hover:underline">{{ app()->getLocale() === 'tr' ? 'Rapor' : 'Report' }} →</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
const isDark = document.documentElement.classList.contains('dark');
Chart.defaults.color = isDark ? '#9ca3af' : '#6b7280';
Chart.defaults.borderColor = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)';

new Chart(document.getElementById('appUsageChart'), {
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
            { label: '{{ app()->getLocale() === "tr" ? "Tamamlanan" : "Completed" }}', data: {!! json_encode($overview['app_stats']->pluck('completed')) !!}, backgroundColor: isDark ? 'rgba(74,222,128,0.7)' : 'rgba(34,197,94,0.7)', borderRadius: 6 },
            { label: '{{ app()->getLocale() === "tr" ? "Devam Eden" : "In Progress" }}', data: {!! json_encode($overview['app_stats']->pluck('in_progress')) !!}, backgroundColor: isDark ? 'rgba(59,130,246,0.7)' : 'rgba(37,99,235,0.7)', borderRadius: 6 },
        ]
    },
    options: { responsive: true, scales: { y: { beginAtZero: true } }, plugins: { legend: { position: 'bottom', labels: { padding: 15, usePointStyle: true } } } }
});
</script>
@endpush
