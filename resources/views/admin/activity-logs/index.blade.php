@extends('admin.layouts.app')

@section('title', __('admin.activity_logs'))

@section('breadcrumb')
    <div class="flex items-center gap-2 text-sm">
        <a href="{{ route('admin.dashboard') }}"
            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">{{ __('admin.dashboard') }}</a>
        <svg class="w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-gray-900 dark:text-gray-100 font-semibold">{{ __('admin.activity_logs') }}</span>
    </div>
@endsection

@section('content')
    <div class="space-y-6">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div
                class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-4 text-center">
                <p class="text-2xl font-extrabold text-[#0B6AB2]">{{ $stats['today'] }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ __('admin.today') }}</p>
            </div>
            <div
                class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-4 text-center">
                <p class="text-2xl font-extrabold text-[#13398E]">{{ $stats['week'] }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ __('admin.last_7_days') }}</p>
            </div>
            <div
                class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-4 text-center">
                <p class="text-2xl font-extrabold text-purple-500">{{ $stats['month'] }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ __('admin.last_30_days') }}</p>
            </div>
            <div
                class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-4 text-center">
                <p class="text-2xl font-extrabold text-gray-500">{{ $stats['total'] }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ __('admin.total') }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-4">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-6 gap-3">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('admin.search') }}..."
                    class="rounded-lg border-gray-200 dark:border-[#1A3A5C] dark:bg-[#0A1628] text-sm focus:ring-[#0B6AB2] focus:border-[#0B6AB2]">
                <select name="module" class="rounded-lg border-gray-200 dark:border-[#1A3A5C] dark:bg-[#0A1628] text-sm">
                    <option value="">{{ __('admin.all_modules') }}</option>
                    @foreach($modules as $m)<option value="{{ $m }}" {{ request('module') === $m ? 'selected' : '' }}>
                    {{ ucfirst($m) }}</option>@endforeach
                </select>
                <select name="action" class="rounded-lg border-gray-200 dark:border-[#1A3A5C] dark:bg-[#0A1628] text-sm">
                    <option value="">{{ __('admin.all_actions') }}</option>
                    @foreach($actions as $a)<option value="{{ $a }}" {{ request('action') === $a ? 'selected' : '' }}>
                    {{ ucfirst($a) }}</option>@endforeach
                </select>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                    class="rounded-lg border-gray-200 dark:border-[#1A3A5C] dark:bg-[#0A1628] text-sm">
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                    class="rounded-lg border-gray-200 dark:border-[#1A3A5C] dark:bg-[#0A1628] text-sm">
                <div class="flex gap-2">
                    <button type="submit"
                        class="flex-1 px-4 py-2 bg-[#0B6AB2] text-white rounded-lg text-sm hover:bg-[#13398E] transition">{{ __('admin.filter') }}</button>
                    <a href="{{ route('admin.activity-logs.index') }}"
                        class="px-3 py-2 border border-gray-200 dark:border-[#1A3A5C] rounded-lg text-sm hover:bg-gray-50 dark:hover:bg-[#0A1628] transition">✕</a>
                </div>
            </form>
        </div>

        <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] overflow-hidden">
            <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100 dark:border-[#1A3A5C]">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white">{{ __('admin.activity_logs') }} <span
                        class="text-gray-400 font-normal">({{ $logs->total() }})</span></h3>
                @can('export', App\Models\ActivityLog::class)
                    <a href="{{ route('admin.activity-logs.export', request()->query()) }}"
                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 border border-gray-200 dark:border-[#1A3A5C] rounded-lg text-xs font-medium hover:bg-gray-50 dark:hover:bg-[#0A1628] transition">
                        📥 {{ __('admin.export_csv') }}
                    </a>
                @endcan
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-[#1A3A5C]">
                        <th class="text-left px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                            {{ __('admin.action') }}</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                            {{ __('admin.module') }}</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                            {{ __('admin.user') }}</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                            {{ __('admin.description') }}</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                            {{ __('admin.date') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-[#1A3A5C]/50">
                    @forelse($logs as $log)
                        @php
                            $actionColors = [
                                'created' => 'emerald',
                                'updated' => 'blue',
                                'deleted' => 'red',
                                'login' => 'amber',
                                'logout' => 'gray',
                                'permissions_synced' => 'purple',
                            ];
                            $ac = $actionColors[$log->action] ?? 'gray';
                        @endphp
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-[#0A1628]/30 transition">
                            <td class="px-5 py-3">
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-{{ $ac }}-50 dark:bg-{{ $ac }}-900/20 text-{{ $ac }}-600">{{ $log->action }}</span>
                            </td>
                            <td class="px-5 py-3 text-xs text-gray-500 font-mono">{{ $log->module }}</td>
                            <td class="px-5 py-3 text-xs text-gray-900 dark:text-white">{{ $log->actor?->name ?? 'System' }}
                            </td>
                            <td class="px-5 py-3 text-xs text-gray-500 max-w-[200px] truncate">{{ $log->description ?? '—' }}
                            </td>
                            <td class="px-5 py-3 text-right text-xs text-gray-400 tabular-nums">
                                {{ $log->created_at?->format('d.m.Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-gray-400">{{ __('admin.no_data') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if($logs->hasPages())
                <div class="px-5 py-3 border-t border-gray-100 dark:border-[#1A3A5C]">{{ $logs->links() }}</div>
            @endif
        </div>
    </div>
@endsection