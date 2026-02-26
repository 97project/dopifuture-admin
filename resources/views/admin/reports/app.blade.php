@extends('admin.layouts.app')
@section('title', $app->name . ' — ' . __('admin.reports'))
@section('breadcrumb')
    <a href="{{ route('admin.reports.index') }}" class="hover:text-gray-700 dark:hover:text-gray-200">{{ __('admin.reports') }}</a>
    <span class="mx-2">/</span>
    <span>{{ $app->name }}</span>
@endsection
@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $app->name }}</h1>
    <p class="text-sm text-gray-500 mt-1">{{ app()->getLocale() === 'tr' ? 'Detaylı uygulama raporu' : 'Detailed application report' }}</p>
</div>

<div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4 text-center">
        <div class="text-2xl font-extrabold text-gray-900 dark:text-white">{{ $total_progress }}</div>
        <div class="text-xs text-gray-500 mt-1">{{ app()->getLocale() === 'tr' ? 'Toplam İlerleme' : 'Total Progress' }}</div>
    </div>
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4 text-center">
        <div class="text-2xl font-extrabold text-emerald-500">{{ $total_completed }}</div>
        <div class="text-xs text-gray-500 mt-1">{{ app()->getLocale() === 'tr' ? 'Tamamlanan' : 'Completed' }}</div>
    </div>
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4 text-center">
        <div class="text-2xl font-extrabold text-blue-500">{{ $avg_score ? number_format($avg_score, 1) : '-' }}</div>
        <div class="text-xs text-gray-500 mt-1">{{ app()->getLocale() === 'tr' ? 'Ort. Puan' : 'Avg Score' }}</div>
    </div>
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4 text-center">
        <div class="text-2xl font-extrabold text-amber-500">{{ $total_sessions }}</div>
        <div class="text-xs text-gray-500 mt-1">{{ app()->getLocale() === 'tr' ? 'Oturum' : 'Sessions' }}</div>
    </div>
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4 text-center">
        <div class="text-2xl font-extrabold text-purple-500">{{ \App\Services\ReportService::formatDuration($total_duration ?? 0) }}</div>
        <div class="text-xs text-gray-500 mt-1">{{ app()->getLocale() === 'tr' ? 'Toplam Süre' : 'Total Duration' }}</div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5">
        <h3 class="text-sm font-semibold mb-4 text-gray-900 dark:text-white">{{ app()->getLocale() === 'tr' ? 'Modül Dağılımı' : 'Module Distribution' }}</h3>
        <canvas id="moduleChart" height="200"></canvas>
    </div>
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5">
        <h3 class="text-sm font-semibold mb-4 text-gray-900 dark:text-white">{{ app()->getLocale() === 'tr' ? 'Günlük Oturumlar' : 'Daily Sessions' }}</h3>
        <canvas id="sessionsChart" height="200"></canvas>
    </div>
</div>

{{-- Student Performance --}}
<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">👥 {{ app()->getLocale() === 'tr' ? 'Öğrenci Performansı' : 'Student Performance' }}</h3>
    </div>
    <table class="w-full">
        <thead>
            <tr class="bg-gray-50 dark:bg-gray-800/50">
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">{{ app()->getLocale() === 'tr' ? 'Öğrenci' : 'Student' }}</th>
                <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase">{{ app()->getLocale() === 'tr' ? 'Tamamlanma' : 'Completion' }}</th>
                <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase">{{ app()->getLocale() === 'tr' ? 'Puan' : 'Score' }}</th>
                <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase">{{ app()->getLocale() === 'tr' ? 'Süre' : 'Duration' }}</th>
                <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
            @forelse($user_stats as $us)
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30">
                <td class="px-5 py-3 text-sm font-medium text-gray-900 dark:text-white">{{ $us['user']->name ?? '' }} {{ $us['user']->surname ?? '' }}</td>
                <td class="px-5 py-3 text-center">
                    <div class="flex items-center justify-center gap-2">
                        <div class="w-16 h-1.5 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
                            <div class="h-full rounded-full {{ $us['completion_rate'] >= 80 ? 'bg-emerald-400' : ($us['completion_rate'] >= 40 ? 'bg-amber-400' : 'bg-red-400') }}" style="width:{{ $us['completion_rate'] }}%"></div>
                        </div>
                        <span class="text-xs text-gray-500">{{ $us['completion_rate'] }}%</span>
                    </div>
                </td>
                <td class="px-5 py-3 text-sm text-gray-500 text-center">{{ $us['avg_score'] ? number_format($us['avg_score'], 1) : '-' }}</td>
                <td class="px-5 py-3 text-sm text-gray-500 text-center">{{ \App\Services\ReportService::formatDuration($us['total_duration'] ?? 0) }}</td>
                <td class="px-5 py-3 text-right">
                    @if($us['user'])<a href="{{ route('admin.reports.student', $us['user']) }}" class="text-xs text-blue-600 dark:text-blue-400 hover:underline">{{ app()->getLocale() === 'tr' ? 'Detay' : 'Detail' }} →</a>@endif
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="px-5 py-8 text-center text-gray-400 text-sm">{{ app()->getLocale() === 'tr' ? 'Henüz veri yok' : 'No data yet' }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
const isDark=document.documentElement.classList.contains('dark');
Chart.defaults.color=isDark?'#9ca3af':'#6b7280';
Chart.defaults.borderColor=isDark?'rgba(255,255,255,0.06)':'rgba(0,0,0,0.06)';
@if($module_stats->count())
new Chart(document.getElementById('moduleChart'),{type:'doughnut',data:{labels:{!!json_encode($module_stats->keys())!!},datasets:[{data:{!!json_encode($module_stats->pluck('total')->values())!!},backgroundColor:['#3b82f6','#8b5cf6','#f59e0b','#ef4444','#10b981'],borderWidth:0}]},options:{responsive:true,plugins:{legend:{position:'bottom',labels:{padding:12,usePointStyle:true}}}}});
@endif
@if($sessions_by_day->count())
new Chart(document.getElementById('sessionsChart'),{type:'line',data:{labels:{!!json_encode($sessions_by_day->keys())!!},datasets:[{label:'Sessions',data:{!!json_encode($sessions_by_day->values())!!},borderColor:'#3b82f6',backgroundColor:isDark?'rgba(59,130,246,0.1)':'rgba(59,130,246,0.05)',fill:true,tension:0.4,pointRadius:3}]},options:{responsive:true,scales:{y:{beginAtZero:true}},plugins:{legend:{display:false}}}});
@endif
</script>
@endpush
