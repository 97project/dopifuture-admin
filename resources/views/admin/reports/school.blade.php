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
    <p class="text-sm text-gray-500 mt-1">{{ app()->getLocale() === 'tr' ? 'Okul detaylı raporu' : 'School detailed report' }}</p>
</div>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5 text-center">
        <div class="text-3xl font-extrabold text-gray-900 dark:text-white">{{ $overview['total_users'] }}</div>
        <div class="text-xs text-gray-500 mt-1">{{ app()->getLocale() === 'tr' ? 'Kullanıcı' : 'Users' }}</div>
    </div>
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5 text-center">
        <div class="text-3xl font-extrabold text-emerald-500">{{ $overview['total_students'] }}</div>
        <div class="text-xs text-gray-500 mt-1">{{ app()->getLocale() === 'tr' ? 'Öğrenci' : 'Students' }}</div>
    </div>
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5 text-center">
        <div class="text-3xl font-extrabold text-blue-500">{{ $school->classes->count() }}</div>
        <div class="text-xs text-gray-500 mt-1">{{ app()->getLocale() === 'tr' ? 'Sınıf' : 'Classes' }}</div>
    </div>
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5 text-center">
        <div class="text-3xl font-extrabold text-amber-500">{{ $school->licenses->count() }}</div>
        <div class="text-xs text-gray-500 mt-1">{{ app()->getLocale() === 'tr' ? 'Lisans' : 'Licenses' }}</div>
    </div>
</div>

{{-- App Performance --}}
<h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ app()->getLocale() === 'tr' ? 'Uygulama Performansı' : 'Application Performance' }}</h2>
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
    </div>
    @endforeach
</div>

{{-- Licenses --}}
<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden mb-8">
    <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">📜 {{ app()->getLocale() === 'tr' ? 'Lisanslar ve Satın Almalar' : 'Licenses & Purchases' }}</h3>
    </div>
    <table class="w-full">
        <thead>
            <tr class="bg-gray-50 dark:bg-gray-800/50">
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">{{ app()->getLocale() === 'tr' ? 'Lisans' : 'License' }}</th>
                <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase">{{ app()->getLocale() === 'tr' ? 'Kontenjan' : 'Seats' }}</th>
                <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase">{{ app()->getLocale() === 'tr' ? 'Kullanılan' : 'Used' }}</th>
                <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase">{{ app()->getLocale() === 'tr' ? 'Satın Alma' : 'Purchases' }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
            @foreach($school->licenses as $lic)
            <tr>
                <td class="px-5 py-3 text-sm font-medium text-gray-900 dark:text-white">
                    {{ $lic->name ?? 'License #'.$lic->id }}
                    <span class="ml-2 px-1.5 py-0.5 text-[10px] font-semibold rounded {{ $lic->is_active ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400' : 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400' }}">
                        {{ $lic->is_active ? (app()->getLocale() === 'tr' ? 'Aktif' : 'Active') : (app()->getLocale() === 'tr' ? 'Pasif' : 'Inactive') }}
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
                <td class="px-5 py-2 text-xs text-gray-400 pl-10">↳ +{{ $purchase->seat_count }} {{ app()->getLocale() === 'tr' ? 'kontenjan' : 'seats' }}</td>
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
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">👥 {{ app()->getLocale() === 'tr' ? 'Okul Kullanıcıları' : 'School Users' }}</h3>
    </div>
    <table class="w-full">
        <thead>
            <tr class="bg-gray-50 dark:bg-gray-800/50">
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">{{ app()->getLocale() === 'tr' ? 'Ad Soyad' : 'Name' }}</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Email</th>
                <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase">{{ app()->getLocale() === 'tr' ? 'Rol' : 'Role' }}</th>
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
                    <a href="{{ route('admin.reports.student', $u) }}" class="text-xs text-blue-600 dark:text-blue-400 hover:underline">{{ app()->getLocale() === 'tr' ? 'Rapor' : 'Report' }} →</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
