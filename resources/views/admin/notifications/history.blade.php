@extends('admin.layouts.app')

@section('title', __('admin.notif_history'))

@section('breadcrumb')
    <div class="flex items-center gap-2 text-sm">
        <a href="{{ route('admin.dashboard') }}"
            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">{{ __('admin.dashboard') }}</a>
        <svg class="w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <a href="{{ route('admin.notifications.index') }}"
            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">{{ __('admin.notifications') }}</a>
        <svg class="w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-gray-900 dark:text-gray-100 font-semibold">{{ __('admin.notif_history') }}</span>
    </div>
@endsection

@section('content')
    <div class="space-y-5">

        {{-- Header Bar with Tab Nav --}}
        <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C]">
            {{-- Summary Stats --}}
            <div class="grid grid-cols-3 border-b border-gray-100 dark:border-[#1A3A5C]">
                <div class="px-5 py-3 border-r border-gray-100 dark:border-[#1A3A5C]">
                    <div class="text-lg font-bold text-gray-900 dark:text-white">{{ number_format($logs->total()) }}</div>
                    <div class="text-[10px] text-gray-400 uppercase tracking-wider font-semibold">
                        {{ __('admin.notif_total_logs') }}</div>
                </div>
                <div class="px-5 py-3 border-r border-gray-100 dark:border-[#1A3A5C]">
                    @php $todayCount = \App\Models\NotificationLog::whereDate('created_at', today())->count(); @endphp
                    <div class="text-lg font-bold text-gray-900 dark:text-white">{{ $todayCount }}</div>
                    <div class="text-[10px] text-gray-400 uppercase tracking-wider font-semibold">
                        {{ __('admin.notif_today') }}</div>
                </div>
                <div class="px-5 py-3">
                    @php $weekCount = \App\Models\NotificationLog::where('created_at', '>=', now()->subWeek())->count(); @endphp
                    <div class="text-lg font-bold text-gray-900 dark:text-white">{{ $weekCount }}</div>
                    <div class="text-[10px] text-gray-400 uppercase tracking-wider font-semibold">
                        {{ __('admin.notif_this_week') }}</div>
                </div>
            </div>

            {{-- Tab Navigation (same as index) --}}
            <div class="flex items-center gap-1 px-2">
                <a href="{{ route('admin.notifications.index') }}"
                    class="px-4 py-2.5 text-xs font-medium text-gray-400 hover:text-gray-600 border-b-2 border-transparent hover:border-gray-300 transition flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                    {{ __('admin.notif_compose') }}
                </a>
                <a href="{{ route('admin.notifications.history') }}"
                    class="px-4 py-2.5 text-xs font-semibold border-b-2 border-[#0B6AB2] text-[#0B6AB2] flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ __('admin.notif_history') }}
                </a>
                <a href="{{ route('admin.notifications.analytics') }}"
                    class="px-4 py-2.5 text-xs font-medium text-gray-400 hover:text-gray-600 border-b-2 border-transparent hover:border-gray-300 transition flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    {{ __('admin.notif_analytics') }}
                </a>
                <div class="ml-auto py-2">
                    <a href="{{ route('admin.notification-templates.index') }}"
                        class="inline-flex items-center gap-1 px-3 py-1.5 border border-gray-200 dark:border-[#1A3A5C] rounded-lg text-[10px] font-medium hover:bg-gray-50 dark:hover:bg-[#0A1628] transition">
                        📋 {{ __('admin.templates') }}
                    </a>
                </div>
            </div>
        </div>

        {{-- Filters --}}
        <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-4">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-3 items-end">
                <div>
                    <label
                        class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1.5">{{ __('admin.notif_channels') }}</label>
                    <select name="channel"
                        class="w-full rounded-lg border-gray-200 dark:border-[#1A3A5C] dark:bg-[#0A1628] text-xs focus:ring-[#0B6AB2] focus:border-[#0B6AB2]">
                        <option value="">{{ __('admin.all') }}</option>
                        <option value="database" {{ request('channel') === 'database' ? 'selected' : '' }}>💾
                            {{ __('admin.notif_channel_database') }}</option>
                        <option value="fcm" {{ request('channel') === 'fcm' ? 'selected' : '' }}>📱
                            {{ __('admin.notif_channel_push') }}</option>
                        <option value="mail" {{ request('channel') === 'mail' ? 'selected' : '' }}>✉️
                            {{ __('admin.notif_channel_email') }}</option>
                    </select>
                </div>
                <div>
                    <label
                        class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1.5">{{ __('admin.target_audience') }}</label>
                    <select name="target_type"
                        class="w-full rounded-lg border-gray-200 dark:border-[#1A3A5C] dark:bg-[#0A1628] text-xs focus:ring-[#0B6AB2] focus:border-[#0B6AB2]">
                        <option value="">{{ __('admin.all') }}</option>
                        <option value="all" {{ request('target_type') === 'all' ? 'selected' : '' }}>🌐
                            {{ __('admin.all_users') }}</option>
                        <option value="role" {{ request('target_type') === 'role' ? 'selected' : '' }}>🔑
                            {{ __('admin.notif_by_role') }}</option>
                        <option value="school" {{ request('target_type') === 'school' ? 'selected' : '' }}>🏫
                            {{ __('admin.notif_by_school') }}</option>
                        <option value="selected" {{ request('target_type') === 'selected' ? 'selected' : '' }}>👤
                            {{ __('admin.selected_users') }}</option>
                    </select>
                </div>
                <div>
                    <label
                        class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1.5">{{ __('admin.date_from') }}</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                        class="w-full rounded-lg border-gray-200 dark:border-[#1A3A5C] dark:bg-[#0A1628] text-xs focus:ring-[#0B6AB2] focus:border-[#0B6AB2]">
                </div>
                <div>
                    <label
                        class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1.5">{{ __('admin.date_to') }}</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                        class="w-full rounded-lg border-gray-200 dark:border-[#1A3A5C] dark:bg-[#0A1628] text-xs focus:ring-[#0B6AB2] focus:border-[#0B6AB2]">
                </div>
                <div class="flex gap-2">
                    <button type="submit"
                        class="flex-1 px-4 py-2 bg-[#0B6AB2] text-white rounded-lg text-xs font-semibold hover:bg-[#13398E] transition">{{ __('admin.filter') }}</button>
                    <a href="{{ route('admin.notifications.history') }}"
                        class="px-3 py-2 border border-gray-200 dark:border-[#1A3A5C] rounded-lg text-xs hover:bg-gray-50 dark:hover:bg-[#0A1628] transition">✕</a>
                </div>
            </form>
        </div>

        {{-- Log Table --}}
        <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] overflow-hidden">
            <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100 dark:border-[#1A3A5C]">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white">{{ __('admin.notif_history') }}
                    <span class="text-gray-400 font-normal">({{ $logs->total() }})</span>
                </h3>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-[#1A3A5C]">
                        <th class="text-left px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                            {{ __('admin.title') }}</th>
                        <th class="text-center px-3 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                            {{ __('admin.notif_channels') }}</th>
                        <th class="text-center px-3 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                            {{ __('admin.target_audience') }}</th>
                        <th class="text-center px-3 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                            {{ __('admin.notif_stat_recipients') }}</th>
                        <th class="text-center px-3 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                            {{ __('admin.notif_sender') }}</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                            {{ __('admin.date') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-[#1A3A5C]/50">
                    @forelse ($logs as $log)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-[#0A1628]/30 transition group">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2.5">
                                    <div
                                        class="w-7 h-7 rounded-lg bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                        </svg>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-xs font-bold text-gray-900 dark:text-white truncate max-w-[200px]">
                                            {{ $log->title }}</div>
                                        <div class="text-[10px] text-gray-400 truncate max-w-[200px]">
                                            {{ Str::limit($log->body, 50) }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-3 py-3 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    @foreach ($log->channels as $ch)
                                        <span
                                            class="w-6 h-6 rounded-lg flex items-center justify-center text-[10px]
                                                        {{ $ch === 'database' ? 'bg-blue-50 dark:bg-blue-900/20' : ($ch === 'fcm' ? 'bg-green-50 dark:bg-green-900/20' : 'bg-orange-50 dark:bg-orange-900/20') }}">
                                            {{ $ch === 'database' ? '💾' : ($ch === 'fcm' ? '📱' : '✉️') }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-3 py-3 text-center">
                                @php
                                    $targetMap = ['all' => ['🌐', 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600'], 'role' => ['🔑', 'bg-purple-50 dark:bg-purple-900/20 text-purple-600'], 'school' => ['🏫', 'bg-yellow-50 dark:bg-yellow-900/20 text-yellow-600'], 'selected' => ['👤', 'bg-blue-50 dark:bg-blue-900/20 text-blue-600']];
                                    $tm = $targetMap[$log->target_type] ?? ['❓', 'bg-gray-50 text-gray-600'];
                                @endphp
                                <span
                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold {{ $tm[1] }}">
                                    {{ $tm[0] }} {{ $log->getTargetLabel() }}
                                </span>
                            </td>
                            <td class="px-3 py-3 text-center">
                                <span
                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 dark:bg-blue-900/20 text-blue-600">
                                    👥 {{ number_format($log->recipients_count) }}
                                </span>
                            </td>
                            <td class="px-3 py-3 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <div class="w-5 h-5 rounded-full bg-[#0B6AB2]/10 flex items-center justify-center">
                                        <span
                                            class="text-[#0B6AB2] text-[8px] font-bold">{{ strtoupper(substr($log->sender?->name ?? 'S', 0, 1)) }}</span>
                                    </div>
                                    <span class="text-[10px] text-gray-500">{{ $log->sender?->name ?? 'System' }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <div class="text-[10px] text-gray-500">{{ $log->created_at->format('d.m.Y') }}</div>
                                <div class="text-[9px] text-gray-400">{{ $log->created_at->format('H:i') }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <div
                                        class="w-14 h-14 rounded-2xl bg-gray-100 dark:bg-[#0A1628] flex items-center justify-center mb-3">
                                        <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                        </svg>
                                    </div>
                                    <p class="text-xs font-semibold text-gray-400">{{ __('admin.notif_no_history') }}</p>
                                    <p class="text-[10px] text-gray-300 mt-1">{{ __('admin.notif_no_history_desc') }}</p>
                                    <a href="{{ route('admin.notifications.index') }}"
                                        class="mt-3 inline-flex items-center gap-1 px-3 py-1.5 bg-[#0B6AB2] text-white rounded-lg text-[10px] font-semibold hover:bg-[#13398E] transition">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                        </svg>
                                        {{ __('admin.notif_send_first') }}
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if ($logs->hasPages())
                <div class="px-5 py-3 border-t border-gray-100 dark:border-[#1A3A5C]">{{ $logs->links() }}</div>
            @endif
        </div>
    </div>
@endsection