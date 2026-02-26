@extends('admin.layouts.app')

@section('title', __('admin.dashboard'))

@section('breadcrumb')
    <div class="flex items-center gap-2">
        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
        </svg>
        <span class="text-gray-900 dark:text-gray-100 font-semibold">{{ __('admin.dashboard') }}</span>
    </div>
@endsection

@section('content')
    <div class="space-y-6">

        {{-- Welcome Section --}}
        <div
            class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-[#13398E] via-[#0B6AB2] to-[#13398E] p-6 lg:p-8 text-white animate-fade-in">
            <div class="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/4"></div>
            <div class="absolute bottom-0 left-1/3 w-40 h-40 bg-white/5 rounded-full translate-y-1/2"></div>
            <div class="absolute top-1/2 right-1/4 w-20 h-20 bg-white/[0.03] rounded-full"></div>

            <div class="relative">
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-blue-200/80 text-sm font-medium">{{ now()->translatedFormat('l, j F Y') }}</span>
                </div>
                <h1 class="text-2xl lg:text-3xl font-bold tracking-tight">
                    {{ __('admin.welcome') }}, {{ auth()->user()->name }}! 👋
                </h1>
                <p class="text-blue-200/80 mt-1.5 text-sm max-w-lg">
                    {{ __('admin.dashboard_description') }}
                </p>
            </div>
        </div>

        {{-- ═══════════════════════════════════════
        Stat Cards — 2 Rows of 4
        ═══════════════════════════════════════ --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Total Users --}}
            <div class="stat-card blue animate-fade-in stagger-1">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                            {{ __('admin.total_users') }}
                        </p>
                        <p class="text-3xl font-extrabold text-gray-900 dark:text-white mt-2 tabular-nums">
                            {{ number_format($stats['users']) }}
                        </p>
                    </div>
                    <div
                        class="w-12 h-12 rounded-xl bg-gradient-to-br from-[#0B6AB2]/10 to-[#13398E]/10 dark:from-[#0B6AB2]/20 dark:to-[#13398E]/20 flex items-center justify-center">
                        <svg class="w-6 h-6 text-[#0B6AB2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-3 flex items-center gap-1.5">
                    <span class="inline-flex items-center gap-0.5 text-xs font-semibold text-emerald-500">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M5 10l7-7m0 0l7 7m-7-7v18" />
                        </svg>
                        {{ $stats['active_users'] > 0 ? round(($stats['active_users'] / max($stats['users'], 1)) * 100) : 0 }}%
                    </span>
                    <span class="text-[11px] text-gray-400">{{ __('admin.stat_active') }}</span>
                </div>
            </div>

            {{-- Active Users --}}
            <div class="stat-card green animate-fade-in stagger-2">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                            {{ __('admin.active_users') }}
                        </p>
                        <p class="text-3xl font-extrabold text-gray-900 dark:text-white mt-2 tabular-nums">
                            {{ number_format($stats['active_users']) }}
                        </p>
                    </div>
                    <div
                        class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-500/10 to-emerald-600/10 dark:from-emerald-500/20 dark:to-emerald-600/20 flex items-center justify-center">
                        <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-3 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse-subtle"></span>
                    <span class="text-[11px] text-gray-400">{{ __('admin.stat_currently_online') }}</span>
                </div>
            </div>

            {{-- Schools --}}
            <div class="stat-card purple animate-fade-in stagger-3">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                            {{ __('admin.schools') }}
                        </p>
                        <p class="text-3xl font-extrabold text-gray-900 dark:text-white mt-2 tabular-nums">
                            {{ number_format($stats['schools']) }}
                        </p>
                    </div>
                    <div
                        class="w-12 h-12 rounded-xl bg-gradient-to-br from-[#13398E]/10 to-[#0A2870]/10 dark:from-[#13398E]/20 dark:to-[#0A2870]/20 flex items-center justify-center">
                        <svg class="w-6 h-6 text-[#13398E]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                </div>
                <div class="mt-3 flex items-center gap-1.5">
                    <span class="text-[11px] text-gray-400">{{ __('admin.stat_registered_schools') }}</span>
                </div>
            </div>

            {{-- Classes --}}
            <div class="stat-card orange animate-fade-in stagger-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                            {{ __('admin.classes') }}
                        </p>
                        <p class="text-3xl font-extrabold text-gray-900 dark:text-white mt-2 tabular-nums">
                            {{ number_format($stats['classes']) }}
                        </p>
                    </div>
                    <div
                        class="w-12 h-12 rounded-xl bg-gradient-to-br from-[#F87D17]/10 to-[#E06810]/10 dark:from-[#F87D17]/20 dark:to-[#E06810]/20 flex items-center justify-center">
                        <svg class="w-6 h-6 text-[#F87D17]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
                        </svg>
                    </div>
                </div>
                <div class="mt-3 flex items-center gap-1.5">
                    <span class="text-[11px] text-gray-400">{{ __('admin.stat_total_classes') }}</span>
                </div>
            </div>
        </div>

        {{-- Row 2 Stats --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Active Licenses --}}
            <div class="stat-card green animate-fade-in stagger-1">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                            {{ __('admin.active_licenses') }}
                        </p>
                        <p class="text-3xl font-extrabold text-gray-900 dark:text-white mt-2 tabular-nums">
                            {{ number_format($stats['licenses']) }}
                        </p>
                    </div>
                    <div
                        class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-500/10 to-emerald-600/10 dark:from-emerald-500/20 dark:to-emerald-600/20 flex items-center justify-center">
                        <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-3 flex items-center gap-1.5">
                    <span class="text-[11px] text-gray-400">{{ __('admin.stat_valid_licenses') }}</span>
                </div>
            </div>

            {{-- Applications --}}
            <div class="stat-card blue animate-fade-in stagger-2">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                            {{ __('admin.applications') }}
                        </p>
                        <p class="text-3xl font-extrabold text-gray-900 dark:text-white mt-2 tabular-nums">
                            {{ number_format($stats['applications']) }}
                        </p>
                    </div>
                    <div
                        class="w-12 h-12 rounded-xl bg-gradient-to-br from-[#0B6AB2]/10 to-[#13398E]/10 dark:from-[#0B6AB2]/20 dark:to-[#13398E]/20 flex items-center justify-center">
                        <svg class="w-6 h-6 text-[#0B6AB2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-3 flex items-center gap-1.5">
                    <span class="text-[11px] text-gray-400">{{ __('admin.stat_active_apps') }}</span>
                </div>
            </div>

            {{-- Pending Requests --}}
            <div class="stat-card {{ $stats['pending_requests'] > 0 ? 'orange' : 'green' }} animate-fade-in stagger-3">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                            {{ __('admin.stat_pending_requests') }}
                        </p>
                        <p class="text-3xl font-extrabold text-gray-900 dark:text-white mt-2 tabular-nums">
                            {{ number_format($stats['pending_requests']) }}
                        </p>
                    </div>
                    <div
                        class="w-12 h-12 rounded-xl bg-gradient-to-br from-[#F87D17]/10 to-[#E06810]/10 dark:from-[#F87D17]/20 dark:to-[#E06810]/20 flex items-center justify-center">
                        <svg class="w-6 h-6 text-[#F87D17]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-3 flex items-center gap-1.5">
                    @if($stats['pending_requests'] > 0)
                        <span class="w-2 h-2 rounded-full bg-[#F87D17] animate-pulse-subtle"></span>
                        <span class="text-[11px] text-[#F87D17] font-medium">{{ __('admin.stat_needs_attention') }}</span>
                    @else
                        <span class="text-[11px] text-gray-400">{{ __('admin.stat_all_processed') }}</span>
                    @endif
                </div>
            </div>

            {{-- Recent Activity --}}
            <div class="stat-card purple animate-fade-in stagger-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                            {{ __('admin.recent_activity') }}
                        </p>
                        <p class="text-3xl font-extrabold text-gray-900 dark:text-white mt-2 tabular-nums">
                            {{ number_format($stats['recent_logs']) }}
                        </p>
                    </div>
                    <div
                        class="w-12 h-12 rounded-xl bg-gradient-to-br from-[#13398E]/10 to-[#0A2870]/10 dark:from-[#13398E]/20 dark:to-[#0A2870]/20 flex items-center justify-center">
                        <svg class="w-6 h-6 text-[#13398E]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-3 flex items-center gap-1.5">
                    <span class="text-[11px] text-gray-400">{{ __('admin.stat_last_24h') }}</span>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════
        Main Content Grid
        ═══════════════════════════════════════ --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- License Utilization (2/3 width) --}}
            <div
                class="lg:col-span-2 bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">{{ __('admin.license_utilization') }}</h3>
                    @can('licenses.view')
                        <a href="{{ route('admin.licenses.index') }}"
                            class="text-xs text-[#0B6AB2] hover:underline font-medium">{{ __('admin.view_all') }} →</a>
                    @endcan
                </div>
                @if($licenseUtilization->count())
                    <div class="space-y-3">
                        @foreach($licenseUtilization as $item)
                            <div class="flex items-center gap-3">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between mb-1">
                                        <span
                                            class="text-xs font-medium text-gray-700 dark:text-gray-300 truncate">{{ $item['school'] }}</span>
                                        <span
                                            class="text-xs font-bold tabular-nums {{ $item['percent'] >= 90 ? 'text-red-500' : ($item['percent'] >= 70 ? 'text-amber-500' : 'text-emerald-500') }}">
                                            {{ $item['used'] }}/{{ $item['total'] }} ({{ $item['percent'] }}%)
                                        </span>
                                    </div>
                                    <div class="w-full bg-gray-100 dark:bg-[#0A1628] rounded-full h-2 overflow-hidden">
                                        <div class="h-full rounded-full transition-all duration-500
                                                                            {{ $item['percent'] >= 90 ? 'bg-gradient-to-r from-red-400 to-red-500' : ($item['percent'] >= 70 ? 'bg-gradient-to-r from-amber-400 to-amber-500' : 'bg-gradient-to-r from-emerald-400 to-emerald-500') }}"
                                            style="width: {{ min($item['percent'], 100) }}%">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-400 text-center py-4">{{ __('admin.no_data') }}</p>
                @endif
            </div>

            {{-- App Usage (1/3 width) --}}
            <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">{{ __('admin.app_usage') }}</h3>
                    @can('applications.view')
                        <a href="{{ route('admin.applications.index') }}"
                            class="text-xs text-[#0B6AB2] hover:underline font-medium">{{ __('admin.view_all') }} →</a>
                    @endcan
                </div>
                @if($appStats->count())
                    <div class="space-y-3">
                        @php $maxUsers = $appStats->max('users_count') ?: 1; @endphp
                        @foreach($appStats as $app)
                            @php
                                $syncPct = $app->users_count > 0 ? round(($app->synced_count / $app->users_count) * 100) : 0;
                            @endphp
                            <a href="{{ route('admin.applications.show', $app) }}" class="flex items-center gap-3 group">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white text-xs font-bold flex-shrink-0 group-hover:scale-110 transition-transform"
                                    style="background: {{ $app->color ?? '#0B6AB2' }}">
                                    {{ strtoupper(substr(is_array($app->name) ? (reset($app->name)) : $app->name, 0, 2)) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex justify-between items-center mb-1">
                                        <span
                                            class="text-xs font-medium text-gray-700 dark:text-gray-300 truncate">{{ is_array($app->name) ? ($app->name[app()->getLocale()] ?? reset($app->name)) : $app->name }}</span>
                                        <div class="flex items-center gap-1.5">
                                            <span
                                                class="text-xs font-bold text-gray-500 tabular-nums">{{ $app->users_count }}</span>
                                            @if($app->users_count > 0)
                                                <span
                                                    class="text-[9px] px-1 py-0.5 rounded {{ $syncPct >= 80 ? 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600' : ($syncPct >= 50 ? 'bg-amber-50 dark:bg-amber-900/20 text-amber-600' : 'bg-red-50 dark:bg-red-900/20 text-red-500') }} font-bold tabular-nums"
                                                    title="Synced: {{ $app->synced_count }} / Failed: {{ $app->failed_count }} / Pending: {{ $app->pending_count }}">
                                                    {{ $syncPct }}%
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="w-full bg-gray-100 dark:bg-[#0A1628] rounded-full h-1.5 flex overflow-hidden">
                                        @if($app->users_count > 0)
                                            <div class="h-full bg-emerald-500 transition-all duration-500"
                                                style="width: {{ ($app->synced_count / $app->users_count) * 100 }}%"></div>
                                            <div class="h-full bg-red-400 transition-all duration-500"
                                                style="width: {{ ($app->failed_count / $app->users_count) * 100 }}%"></div>
                                            <div class="h-full bg-amber-400 transition-all duration-500"
                                                style="width: {{ ($app->pending_count / $app->users_count) * 100 }}%"></div>
                                        @else
                                            <div class="h-full bg-gray-300 dark:bg-gray-700" style="width: 100%"></div>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-400 text-center py-4">{{ __('admin.no_data') }}</p>
                @endif
            </div>
        </div>

        {{-- ═══════════════════════════════════════
        Bottom Grid
        ═══════════════════════════════════════ --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- Recent Activity --}}
            <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">{{ __('admin.recent_activity') }}</h3>
                    @can('activity_logs.view')
                        <a href="{{ route('admin.activity-logs.index') }}"
                            class="text-xs text-[#0B6AB2] hover:underline font-medium">{{ __('admin.view_all') }} →</a>
                    @endcan
                </div>
                @if($recentActivity->count())
                    <div class="space-y-3">
                        @foreach($recentActivity as $log)
                            <div class="flex items-start gap-3">
                                <div
                                    class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5
                                                                {{ $log->action === 'created' ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600' : '' }}
                                                                {{ $log->action === 'updated' ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-600' : '' }}
                                                                {{ $log->action === 'deleted' ? 'bg-red-100 dark:bg-red-900/30 text-red-600' : '' }}
                                                                {{ !in_array($log->action, ['created', 'updated', 'deleted']) ? 'bg-gray-100 dark:bg-gray-800 text-gray-500' : '' }}">
                                    @if($log->action === 'created')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                    @elseif($log->action === 'updated')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    @elseif($log->action === 'deleted')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    @else
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs text-gray-700 dark:text-gray-300">
                                        <span class="font-semibold">{{ $log->actor?->name ?? 'Sistem' }}</span>
                                        <span class="text-gray-400 mx-1">·</span>
                                        <span
                                            class="capitalize badge {{ $log->action === 'created' ? 'badge-success' : ($log->action === 'deleted' ? 'badge-danger' : 'badge-info') }}">{{ $log->action }}</span>
                                        <span class="text-gray-400 mx-1">·</span>
                                        {{ $log->description ?? $log->model_type }}
                                    </p>
                                    <p class="text-[11px] text-gray-400 mt-0.5">{{ $log->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-400 text-center py-4">{{ __('admin.no_data') }}</p>
                @endif
            </div>

            {{-- Pending Registration Requests --}}
            <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">{{ __('admin.registration_requests') }}</h3>
                    @can('registration_requests.view')
                        <a href="{{ route('admin.registration-requests.index') }}"
                            class="text-xs text-[#0B6AB2] hover:underline font-medium">{{ __('admin.view_all') }} →</a>
                    @endcan
                </div>
                @if($pendingRequests->count())
                    <div class="space-y-3">
                        @foreach($pendingRequests as $req)
                            <div
                                class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-[#0A1628]/60 border border-gray-100 dark:border-[#1A3A5C]/50">
                                <div class="flex items-center gap-3 min-w-0 flex-1">
                                    <div
                                        class="w-8 h-8 rounded-full bg-gradient-to-br from-[#F87D17] to-[#E06810] flex items-center justify-center flex-shrink-0">
                                        <span
                                            class="text-white text-xs font-bold">{{ strtoupper(substr($req->name ?? $req->school_name ?? '?', 0, 1)) }}</span>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs font-semibold text-gray-900 dark:text-white truncate">
                                            {{ $req->name ?? $req->school_name ?? '—' }}
                                        </p>
                                        <p class="text-[11px] text-gray-400 truncate">{{ $req->email ?? '' }} ·
                                            {{ $req->created_at?->diffForHumans() }}
                                        </p>
                                    </div>
                                </div>
                                @can('registration_requests.view')
                                    <a href="{{ route('admin.registration-requests.show', $req) }}"
                                        class="text-xs font-medium text-[#0B6AB2] hover:underline flex-shrink-0 ml-2">
                                        {{ __('admin.review') }}
                                    </a>
                                @endcan
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-6">
                        <svg class="w-10 h-10 text-emerald-200 dark:text-emerald-900/50 mx-auto mb-2" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-sm text-gray-400">{{ __('admin.no_pending_requests') }}</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            @can('users.view')
                <a href="{{ route('admin.users.index') }}"
                    class="group bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5 hover:border-[#0B6AB2]/30 dark:hover:border-[#0B6AB2]/30 hover:shadow-lg hover:shadow-[#0B6AB2]/5 transition-all duration-300">
                    <div class="flex items-center gap-4">
                        <div
                            class="w-10 h-10 rounded-lg bg-[#E8F4F8] dark:bg-[#0B6AB2]/10 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-5 h-5 text-[#0B6AB2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('admin.menu_users') }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ __('admin.quick_manage_users') }}</p>
                        </div>
                        <svg class="w-4 h-4 text-gray-300 dark:text-gray-600 group-hover:text-[#0B6AB2] group-hover:translate-x-1 transition-all"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                </a>
            @endcan

            @can('settings.view')
                <a href="{{ route('admin.settings.index') }}"
                    class="group bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5 hover:border-[#13398E]/30 dark:hover:border-[#13398E]/30 hover:shadow-lg hover:shadow-[#13398E]/5 transition-all duration-300">
                    <div class="flex items-center gap-4">
                        <div
                            class="w-10 h-10 rounded-lg bg-[#E8F4F8] dark:bg-[#13398E]/10 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-5 h-5 text-[#13398E]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('admin.menu_settings') }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ __('admin.quick_panel_settings') }}</p>
                        </div>
                        <svg class="w-4 h-4 text-gray-300 dark:text-gray-600 group-hover:text-[#13398E] group-hover:translate-x-1 transition-all"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                </a>
            @endcan

            @can('activity_logs.view')
                <a href="{{ route('admin.activity-logs.index') }}"
                    class="group bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5 hover:border-[#F87D17]/30 dark:hover:border-[#F87D17]/30 hover:shadow-lg hover:shadow-[#F87D17]/5 transition-all duration-300">
                    <div class="flex items-center gap-4">
                        <div
                            class="w-10 h-10 rounded-lg bg-[#FFF3E0] dark:bg-[#F87D17]/10 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-5 h-5 text-[#F87D17]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('admin.menu_activity_logs') }}
                            </p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ __('admin.quick_recent_activity') }}</p>
                        </div>
                        <svg class="w-4 h-4 text-gray-300 dark:text-gray-600 group-hover:text-[#F87D17] group-hover:translate-x-1 transition-all"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                </a>
            @endcan
        </div>
    </div>
@endsection