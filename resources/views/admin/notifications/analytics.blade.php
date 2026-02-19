@extends('admin.layouts.app')

@section('title', __('admin.notif_analytics'))

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
        <span class="text-gray-900 dark:text-gray-100 font-semibold">{{ __('admin.notif_analytics') }}</span>
    </div>
@endsection

@section('content')
    <div class="space-y-5">

        {{-- Header Bar with Tab Nav --}}
        <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C]">
            {{-- Stat Cards Row --}}
            <div class="grid grid-cols-4 border-b border-gray-100 dark:border-[#1A3A5C]">
                @php
                    $statCards = [
                        ['value' => $stats['total_sent'], 'label' => __('admin.notif_stat_total_sent'), 'icon' => '📤', 'color' => 'blue'],
                        ['value' => $stats['total_recipients'], 'label' => __('admin.notif_stat_recipients'), 'icon' => '👥', 'color' => 'green'],
                        ['value' => $stats['total_read'], 'label' => __('admin.notif_stat_read'), 'icon' => '👁️', 'color' => 'purple'],
                        ['value' => $readRate . '%', 'label' => __('admin.notif_stat_read_rate'), 'icon' => '📊', 'color' => 'orange'],
                    ];
                @endphp
                @foreach ($statCards as $i => $card)
                    <div class="px-5 py-4 {{ !$loop->last ? 'border-r border-gray-100 dark:border-[#1A3A5C]' : '' }}">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-sm">{{ $card['icon'] }}</span>
                            <div class="text-xl font-black text-gray-900 dark:text-white">
                                {{ is_numeric($card['value']) ? number_format($card['value']) : $card['value'] }}</div>
                        </div>
                        <div class="text-[10px] text-gray-400 uppercase tracking-wider font-semibold">{{ $card['label'] }}</div>
                    </div>
                @endforeach
            </div>

            {{-- Tab Navigation --}}
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
                    class="px-4 py-2.5 text-xs font-medium text-gray-400 hover:text-gray-600 border-b-2 border-transparent hover:border-gray-300 transition flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ __('admin.notif_history') }}
                </a>
                <a href="{{ route('admin.notifications.analytics') }}"
                    class="px-4 py-2.5 text-xs font-semibold border-b-2 border-[#0B6AB2] text-[#0B6AB2] flex items-center gap-1.5">
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

        {{-- Main Analytics Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

            {{-- Channel Breakdown --}}
            <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5">
                <h3 class="text-xs font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <span
                        class="w-6 h-6 rounded-lg bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center text-[10px]">📡</span>
                    {{ __('admin.notif_by_channel') }}
                </h3>
                @php
                    $channels = ['database' => ['💾', __('admin.notif_channel_database'), '#0B6AB2'], 'fcm' => ['📱', __('admin.notif_channel_push'), '#10b981'], 'mail' => ['✉️', __('admin.notif_channel_email'), '#F87D17']];
                    $chTotal = max(array_sum($stats['by_channel']), 1);
                @endphp
                <div class="space-y-3">
                    @foreach ($channels as $ch => [$icon, $label, $color])
                        @php $val = $stats['by_channel'][$ch] ?? 0;
                        $pct = round(($val / $chTotal) * 100); @endphp
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm">{{ $icon }}</span>
                                    <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">{{ $label }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span
                                        class="text-xs font-bold text-gray-900 dark:text-white">{{ number_format($val) }}</span>
                                    <span class="text-[9px] text-gray-400 font-medium">({{ $pct }}%)</span>
                                </div>
                            </div>
                            <div class="w-full bg-gray-100 dark:bg-[#0A1628] rounded-full h-2 overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-700 ease-out"
                                    style="width: {{ $pct }}%; background: {{ $color }};">
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if ($stats['total_sent'] == 0)
                    <div class="mt-4 text-center py-3 bg-gray-50 dark:bg-[#0A1628]/60 rounded-lg">
                        <p class="text-[10px] text-gray-400">{{ __('admin.notif_no_data_yet') }}</p>
                    </div>
                @endif
            </div>

            {{-- Target Breakdown --}}
            <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5">
                <h3 class="text-xs font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <span
                        class="w-6 h-6 rounded-lg bg-purple-50 dark:bg-purple-900/20 flex items-center justify-center text-[10px]">🎯</span>
                    {{ __('admin.notif_by_target') }}
                </h3>
                @php
                    $targets = ['all' => ['🌐', __('admin.all_users'), '#0B6AB2'], 'role' => ['🔑', __('admin.notif_by_role'), '#8b5cf6'], 'school' => ['🏫', __('admin.notif_by_school'), '#f59e0b'], 'selected' => ['👤', __('admin.selected_users'), '#10b981']];
                    $tTotal = max(array_sum($stats['by_target']), 1);
                @endphp
                <div class="space-y-3">
                    @foreach ($targets as $t => [$icon, $label, $color])
                        @php $val = $stats['by_target'][$t] ?? 0;
                        $pct = round(($val / $tTotal) * 100); @endphp
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm">{{ $icon }}</span>
                                    <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">{{ $label }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span
                                        class="text-xs font-bold text-gray-900 dark:text-white">{{ number_format($val) }}</span>
                                    <span class="text-[9px] text-gray-400 font-medium">({{ $pct }}%)</span>
                                </div>
                            </div>
                            <div class="w-full bg-gray-100 dark:bg-[#0A1628] rounded-full h-2 overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-700 ease-out"
                                    style="width: {{ $pct }}%; background: {{ $color }};">
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if ($stats['total_sent'] == 0)
                    <div class="mt-4 text-center py-3 bg-gray-50 dark:bg-[#0A1628]/60 rounded-lg">
                        <p class="text-[10px] text-gray-400">{{ __('admin.notif_no_data_yet') }}</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- 7-Day Trend Chart --}}
        <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5">
            <h3 class="text-xs font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <span
                    class="w-6 h-6 rounded-lg bg-green-50 dark:bg-green-900/20 flex items-center justify-center text-[10px]">📈</span>
                {{ __('admin.notif_last_7_days') }}
            </h3>

            @php
                $days = [];
                for ($i = 6; $i >= 0; $i--) {
                    $date = now()->subDays($i)->format('Y-m-d');
                    $days[$date] = $stats['last_7_days'][$date] ?? 0;
                }
                $maxVal = max(max(array_values($days)), 1);
            @endphp

            <div class="flex items-end gap-2 h-40">
                @foreach ($days as $date => $count)
                    @php
                        $pct = round(($count / $maxVal) * 100);
                        $minH = $count > 0 ? max($pct, 8) : 4;
                        $dayName = \Carbon\Carbon::parse($date)->locale(app()->getLocale())->isoFormat('ddd');
                        $dayNum = \Carbon\Carbon::parse($date)->format('d');
                        $isToday = $date === now()->format('Y-m-d');
                    @endphp
                    <div class="flex-1 flex flex-col items-center gap-1.5" title="{{ $date }}: {{ $count }}">
                        <span
                            class="text-[9px] font-bold text-gray-500 {{ $count > 0 ? '' : 'opacity-50' }}">{{ $count }}</span>
                        <div class="w-full rounded-t-md transition-all duration-500 ease-out relative group"
                            style="height: {{ $minH }}%; background: {{ $isToday ? 'linear-gradient(to top, #13398E, #0B6AB2)' : ($count > 0 ? 'linear-gradient(to top, #0B6AB2, #5bb3e8)' : '#e5e7eb') }};">
                            {{-- Tooltip --}}
                            <div
                                class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-900 text-white text-[9px] font-medium px-2 py-1 rounded-md opacity-0 group-hover:opacity-100 transition whitespace-nowrap pointer-events-none z-10">
                                {{ \Carbon\Carbon::parse($date)->format('d.m.Y') }}: {{ $count }}
                                {{ __('admin.notif_stat_total_sent') }}
                            </div>
                        </div>
                        <div class="text-center">
                            <div class="text-[10px] font-bold {{ $isToday ? 'text-[#0B6AB2]' : 'text-gray-500' }}">{{ $dayNum }}
                            </div>
                            <div
                                class="text-[8px] uppercase tracking-wider {{ $isToday ? 'text-[#0B6AB2] font-bold' : 'text-gray-400' }}">
                                {{ $dayName }}</div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if ($stats['total_sent'] == 0)
                <div class="mt-4 text-center py-3 bg-gray-50 dark:bg-[#0A1628]/60 rounded-lg">
                    <p class="text-[10px] text-gray-400">{{ __('admin.notif_chart_empty') }}</p>
                    <a href="{{ route('admin.notifications.index') }}"
                        class="text-[#0B6AB2] font-semibold text-[10px] hover:underline mt-1 inline-block">{{ __('admin.notif_send_first') }}</a>
                </div>
            @endif
        </div>

        {{-- Recent Notifications Table --}}
        <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] overflow-hidden">
            <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100 dark:border-[#1A3A5C]">
                <h3 class="text-xs font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <span
                        class="w-6 h-6 rounded-lg bg-orange-50 dark:bg-orange-900/20 flex items-center justify-center text-[10px]">🔔</span>
                    {{ __('admin.notif_recent') }}
                </h3>
                <a href="{{ route('admin.notifications.history') }}"
                    class="text-[10px] font-semibold text-[#0B6AB2] hover:underline">{{ __('admin.view_all') }} →</a>
            </div>
            @php $recentLogs = \App\Models\NotificationLog::with('sender')->latest()->take(5)->get(); @endphp
            @if ($recentLogs->count())
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-gray-50 dark:divide-[#1A3A5C]/50">
                        @foreach ($recentLogs as $log)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-[#0A1628]/30 transition">
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-2">
                                        <div
                                            class="w-6 h-6 rounded-lg bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center flex-shrink-0">
                                            <svg class="w-3 h-3 text-blue-500" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                            </svg>
                                        </div>
                                        <span
                                            class="text-xs font-semibold text-gray-700 dark:text-gray-300 truncate">{{ Str::limit($log->title, 40) }}</span>
                                    </div>
                                </td>
                                <td class="px-3 py-3 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        @foreach ($log->channels as $ch)
                                            <span
                                                class="text-[10px]">{{ $ch === 'database' ? '💾' : ($ch === 'fcm' ? '📱' : '✉️') }}</span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-3 py-3 text-center">
                                    <span class="text-[10px] font-bold text-blue-600">{{ $log->recipients_count }}</span>
                                </td>
                                <td class="px-5 py-3 text-right text-[10px] text-gray-400">{{ $log->created_at->diffForHumans() }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="px-5 py-8 text-center text-[10px] text-gray-400">{{ __('admin.notif_no_history') }}</div>
            @endif
        </div>
    </div>
@endsection