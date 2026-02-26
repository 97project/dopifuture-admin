@extends('admin.layouts.app')
@section('title', ($student->name ?? '') . ' — ' . __('admin.reports'))
@section('breadcrumb')
    <a href="{{ route('admin.reports.index') }}" class="hover:text-gray-700 dark:hover:text-gray-200">{{ __('admin.reports') }}</a>
    <span class="mx-2">/</span>
    <span>{{ $student->name }} {{ $student->surname }}</span>
@endsection
@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $student->name }} {{ $student->surname }}</h1>
    <p class="text-sm text-gray-500 mt-1">{{ $student->email }} — {{ app()->getLocale() === 'tr' ? 'Detaylı öğrenci raporu' : 'Detailed student report' }}</p>
</div>

{{-- Student Info --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4">
        <div class="text-xs text-gray-500 mb-1">{{ app()->getLocale() === 'tr' ? 'Roller' : 'Roles' }}</div>
        @foreach($student->roles as $r)<span class="px-2 py-0.5 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 text-xs font-semibold rounded mr-1">{{ $r->name }}</span>@endforeach
    </div>
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4">
        <div class="text-xs text-gray-500 mb-1">{{ app()->getLocale() === 'tr' ? 'Okullar' : 'Schools' }}</div>
        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $student->schools->pluck('name')->join(', ') ?: '-' }}</div>
    </div>
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4">
        <div class="text-xs text-gray-500 mb-1">{{ app()->getLocale() === 'tr' ? 'Sınıflar' : 'Classes' }}</div>
        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $student->classes->pluck('name')->join(', ') ?: '-' }}</div>
    </div>
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4">
        <div class="text-xs text-gray-500 mb-1">{{ app()->getLocale() === 'tr' ? 'Uygulamalar' : 'Applications' }}</div>
        <div class="text-2xl font-extrabold text-blue-500">{{ $student->applications->count() }}</div>
    </div>
</div>

{{-- Per-App Reports --}}
@foreach($reportData as $slug => $appData)
<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 mb-6 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ $appData['app']->name }}</h3>
        <span class="px-2 py-0.5 text-xs font-semibold rounded {{ $appData['stats']['completion_rate'] >= 80 ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400' : ($appData['stats']['completion_rate'] >= 40 ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400' : 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400') }}">
            {{ $appData['stats']['completion_rate'] }}%
        </span>
    </div>
    <div class="p-5">
        {{-- Stats Row --}}
        <div class="grid grid-cols-5 gap-4 text-center mb-4">
            <div><div class="text-xl font-bold text-gray-900 dark:text-white">{{ $appData['stats']['total_modules'] }}</div><div class="text-[10px] text-gray-500">{{ app()->getLocale() === 'tr' ? 'Modül' : 'Modules' }}</div></div>
            <div><div class="text-xl font-bold text-emerald-500">{{ $appData['stats']['completed'] }}</div><div class="text-[10px] text-gray-500">{{ app()->getLocale() === 'tr' ? 'Tamamlanan' : 'Completed' }}</div></div>
            <div><div class="text-xl font-bold text-amber-500">{{ $appData['stats']['in_progress'] }}</div><div class="text-[10px] text-gray-500">{{ app()->getLocale() === 'tr' ? 'Devam Eden' : 'In Progress' }}</div></div>
            <div><div class="text-xl font-bold text-blue-500">{{ $appData['stats']['avg_score'] ? number_format($appData['stats']['avg_score'], 1) : '-' }}</div><div class="text-[10px] text-gray-500">{{ app()->getLocale() === 'tr' ? 'Ort. Puan' : 'Avg Score' }}</div></div>
            <div><div class="text-xl font-bold text-purple-500">{{ $appData['stats']['total_sessions'] }}</div><div class="text-[10px] text-gray-500">{{ app()->getLocale() === 'tr' ? 'Oturum' : 'Sessions' }}</div></div>
        </div>
        <div class="w-full h-1.5 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden mb-5">
            <div class="h-full rounded-full bg-gradient-to-r from-emerald-400 to-cyan-400" style="width:{{ $appData['stats']['completion_rate'] }}%"></div>
        </div>
        {{-- Progress Table --}}
        @if($appData['progress']->count())
        <table class="w-full mb-4">
            <thead><tr class="bg-gray-50 dark:bg-gray-800/50">
                <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500">{{ app()->getLocale() === 'tr' ? 'Modül' : 'Module' }}</th>
                <th class="px-4 py-2 text-xs font-semibold text-gray-500">{{ app()->getLocale() === 'tr' ? 'Tip' : 'Type' }}</th>
                <th class="px-4 py-2 text-xs font-semibold text-gray-500">{{ app()->getLocale() === 'tr' ? 'Durum' : 'Status' }}</th>
                <th class="px-4 py-2 text-xs font-semibold text-gray-500">{{ app()->getLocale() === 'tr' ? 'Puan' : 'Score' }}</th>
                <th class="px-4 py-2 text-xs font-semibold text-gray-500">{{ app()->getLocale() === 'tr' ? 'Tarih' : 'Date' }}</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
            @foreach($appData['progress'] as $p)
            <tr>
                <td class="px-4 py-2 text-xs font-medium text-gray-900 dark:text-white">{{ $p->module_name ?: $p->module_id }}</td>
                <td class="px-4 py-2 text-center"><span class="px-1.5 py-0.5 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 text-[10px] rounded font-semibold">{{ $p->module_type }}</span></td>
                <td class="px-4 py-2 text-center"><span class="px-1.5 py-0.5 text-[10px] rounded font-semibold {{ $p->status === 'completed' ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400' : ($p->status === 'in_progress' ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400' : 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400') }}">{{ $p->status }}</span></td>
                <td class="px-4 py-2 text-xs text-gray-500 text-center">{{ $p->score !== null ? number_format($p->score, 1) : '-' }}</td>
                <td class="px-4 py-2 text-xs text-gray-500 text-center">{{ $p->completed_at ? $p->completed_at->format('d.m.Y H:i') : '-' }}</td>
            </tr>
            @endforeach
            </tbody>
        </table>
        @endif
    </div>
</div>
@endforeach

@if(empty($reportData))
<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-12 text-center">
    <div class="text-4xl mb-3">📭</div>
    <p class="text-gray-400">{{ app()->getLocale() === 'tr' ? 'Henüz rapor verisi yok.' : 'No report data yet.' }}</p>
</div>
@endif
@endsection
