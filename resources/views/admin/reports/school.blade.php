@extends('admin.layouts.app')
@section('title', $school->name . ' — ' . __('admin.reports'))
@section('breadcrumb')
    <a href="{{ route('admin.reports.index') }}" class="hover:text-gray-700 dark:hover:text-gray-200">{{ __('admin.reports') }}</a>
    <span class="mx-2">/</span>
    <span>{{ $school->name }}</span>
@endsection
@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">🏫 {{ $school->name }}</h1>
    <p class="text-sm text-gray-500 mt-1">{{ __('admin.rep_school_detailed') }}</p>
</div>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5 text-center">
        <div class="text-3xl font-extrabold text-gray-900 dark:text-white">{{ $overview['total_users'] }}</div>
        <div class="text-xs text-gray-500 mt-1">{{ __('admin.rep_users') }}</div>
    </div>
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5 text-center">
        <div class="text-3xl font-extrabold text-emerald-500">{{ $overview['total_students'] }}</div>
        <div class="text-xs text-gray-500 mt-1">{{ __('admin.rep_students') }}</div>
    </div>
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5 text-center">
        <div class="text-3xl font-extrabold text-blue-500">{{ $school->classes->count() }}</div>
        <div class="text-xs text-gray-500 mt-1">{{ __('admin.rep_classes') }}</div>
    </div>
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5 text-center">
        <div class="text-3xl font-extrabold text-amber-500">{{ $school->licenses->count() }}</div>
        <div class="text-xs text-gray-500 mt-1">{{ __('admin.auto_licenses') }}</div>
    </div>
</div>

{{-- App Performance --}}
<h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('admin.rep_app_perf') }}</h2>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-8">
    @foreach($overview['app_stats'] as $stat)
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
                @php $appCD = $appConnectorData[$stat['app']->slug] ?? ['health' => ['ok' => false], 'synced_count' => 0]; @endphp
                <span class="w-2 h-2 rounded-full {{ ($appCD['health']['ok'] ?? false) ? 'bg-emerald-400' : 'bg-red-400' }}" title="{{ ($appCD['health']['ok'] ?? false) ? 'API OK' : 'API Down' }}"></span>
                <h3 class="font-semibold text-gray-900 dark:text-white">{{ $stat['app']->name }}</h3>
            </div>
            @if(($appCD['synced_count'] ?? 0) > 0)
            <span class="text-[10px] text-gray-400">{{ $appCD['synced_count'] }}/{{ $appCD['total_in_app'] ?? 0 }} sync</span>
            @endif
        </div>
        <div class="grid grid-cols-3 gap-3 text-center">
            <div>
                <div class="text-lg font-bold text-emerald-500">{{ $stat['completed'] }}</div>
                <div class="text-[10px] text-gray-500">{{ __('admin.rep_completed') }}</div>
            </div>
            <div>
                <div class="text-lg font-bold text-amber-500">{{ $stat['in_progress'] }}</div>
                <div class="text-[10px] text-gray-500">{{ __('admin.rep_in_progress') }}</div>
            </div>
            <div>
                <div class="text-lg font-bold text-blue-500">{{ $stat['avg_score'] ? number_format($stat['avg_score'], 1) : '-' }}</div>
                <div class="text-[10px] text-gray-500">{{ __('admin.rep_avg_score') }}</div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Licenses --}}
<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden mb-8">
    <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">📜 {{ __('admin.rep_licenses_purchases') }}</h3>
    </div>
    <table class="w-full">
        <thead>
            <tr class="bg-gray-50 dark:bg-gray-800/50">
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">{{ __('admin.rep_license') }}</th>
                <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase">{{ __('admin.rep_seats') }}</th>
                <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase">{{ __('admin.rep_used') }}</th>
                <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase">{{ __('admin.rep_purchases') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
            @foreach($school->licenses as $lic)
            <tr>
                <td class="px-5 py-3 text-sm font-medium text-gray-900 dark:text-white">
                    {{ $lic->name ?? 'License #'.$lic->id }}
                    <span class="ml-2 px-1.5 py-0.5 text-[10px] font-semibold rounded {{ $lic->is_active ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400' : 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400' }}">
                        {{ $lic->is_active ? (__('admin.rep_active')) : (__('admin.rep_inactive')) }}
                    </span>
                </td>
                <td class="px-5 py-3 text-sm text-gray-500 text-center">{{ $lic->seat_count }}</td>
                <td class="px-5 py-3 text-sm text-center">
                    <span class="{{ $lic->used_seats >= $lic->seat_count ? 'text-red-500 font-bold' : 'text-gray-500' }}">{{ $lic->used_seats }}</span>
                </td>
                <td class="px-5 py-3 text-sm text-gray-500 text-center">{{ $lic->purchases->count() }}</td>
            </tr>
            @foreach($lic->purchases as $purchase)
            <tr class="bg-gray-50/50 dark:bg-gray-800/20">
                <td class="px-5 py-2 text-xs text-gray-400 pl-10">↳ +{{ $purchase->seat_count }} {{ __('admin.rep_seats_sm') }}</td>
                <td class="px-5 py-2 text-xs text-gray-400 text-center">{{ $purchase->created_at->format('d.m.Y') }}</td>
                <td class="px-5 py-2 text-xs text-gray-400 text-center">{{ $purchase->amount ? number_format($purchase->amount, 2).' ₺' : '-' }}</td>
                <td class="px-5 py-2 text-xs text-gray-400 text-center">{{ $purchase->note ?? '-' }}</td>
            </tr>
            @endforeach
            @endforeach
        </tbody>
    </table>
</div>

{{-- Users --}}
<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">👥 {{ __('admin.rep_school_users') }}</h3>
    </div>
    <table class="w-full">
        <thead>
            <tr class="bg-gray-50 dark:bg-gray-800/50">
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">{{ __('admin.rep_name') }}</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Email</th>
                <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase">{{ __('admin.rep_role') }}</th>
                <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
            @foreach($school->users->take(30) as $u)
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30">
                <td class="px-5 py-3 text-sm font-medium text-gray-900 dark:text-white">{{ $u->name }} {{ $u->surname }}</td>
                <td class="px-5 py-3 text-sm text-gray-500">{{ $u->email }}</td>
                <td class="px-5 py-3 text-center">
                    @foreach($u->roles as $r)
                    <span class="px-1.5 py-0.5 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 text-[10px] font-semibold rounded">{{ $r->name }}</span>
                    @endforeach
                </td>
                <td class="px-5 py-3 text-right">
                    <a href="{{ route('admin.reports.student', $u) }}" class="text-xs text-blue-600 dark:text-blue-400 hover:underline">{{ __('admin.rep_report') }} →</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
