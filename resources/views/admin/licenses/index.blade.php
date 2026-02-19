@extends('admin.layouts.app')

@section('title', __('admin.licenses'))

@section('breadcrumb')
    <div class="flex items-center gap-2 text-sm">
        <a href="{{ route('admin.dashboard') }}"
            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">{{ __('admin.dashboard') }}</a>
        <svg class="w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-gray-900 dark:text-gray-100 font-semibold">{{ __('admin.licenses') }}</span>
    </div>
@endsection

@section('content')
    <div class="space-y-6">

        {{-- Stats --}}
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
            @php
                $utilPct = $stats['total_seats'] > 0 ? round(($stats['used_seats'] / $stats['total_seats']) * 100) : 0;
            @endphp
            <div
                class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-4 text-center">
                <p class="text-2xl font-extrabold text-[#13398E]">{{ $stats['total'] }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ __('admin.total') }}</p>
            </div>
            <div
                class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-4 text-center">
                <p class="text-2xl font-extrabold text-emerald-500">{{ $stats['active'] }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ __('admin.active') }}</p>
            </div>
            <div
                class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-4 text-center">
                <p class="text-2xl font-extrabold text-red-500">{{ $stats['expired'] }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ __('admin.expired') }}</p>
            </div>
            <div
                class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-4 text-center">
                <p class="text-2xl font-extrabold text-[#0B6AB2]">{{ $stats['total_seats'] }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ __('admin.total_seats') }}</p>
            </div>
            <div
                class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-4 text-center">
                <p
                    class="text-2xl font-extrabold {{ $utilPct >= 90 ? 'text-red-500' : ($utilPct >= 70 ? 'text-amber-500' : 'text-emerald-500') }}">
                    %{{ $utilPct }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ __('admin.utilization') }}</p>
            </div>
        </div>

        {{-- Filters --}}
        <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-4">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-3">
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="{{ __('admin.search_school') }}..."
                    class="rounded-lg border-gray-200 dark:border-[#1A3A5C] dark:bg-[#0A1628] text-sm focus:ring-[#0B6AB2] focus:border-[#0B6AB2]">
                <select name="school_id" class="rounded-lg border-gray-200 dark:border-[#1A3A5C] dark:bg-[#0A1628] text-sm">
                    <option value="">{{ __('admin.all_schools') }}</option>
                    @foreach($schools as $school)<option value="{{ $school->id }}" {{ request('school_id') == $school->id ? 'selected' : '' }}>{{ $school->name }}</option>@endforeach
                </select>
                <select name="status" class="rounded-lg border-gray-200 dark:border-[#1A3A5C] dark:bg-[#0A1628] text-sm">
                    <option value="">{{ __('admin.all_statuses') }}</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{{ __('admin.active') }}
                    </option>
                    <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>{{ __('admin.expired') }}
                    </option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>
                        {{ __('admin.inactive') }}</option>
                </select>
                <div class="flex gap-2">
                    <button type="submit"
                        class="flex-1 px-4 py-2 bg-[#0B6AB2] text-white rounded-lg text-sm hover:bg-[#13398E] transition">{{ __('admin.filter') }}</button>
                    <a href="{{ route('admin.licenses.index') }}"
                        class="px-3 py-2 border border-gray-200 dark:border-[#1A3A5C] rounded-lg text-sm hover:bg-gray-50 dark:hover:bg-[#0A1628] transition">✕</a>
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] overflow-hidden">
            <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100 dark:border-[#1A3A5C]">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white">{{ __('admin.licenses') }} <span
                        class="text-gray-400 font-normal">({{ $licenses->total() }})</span></h3>
                @can('create', App\Models\License::class)
                    <a href="{{ route('admin.licenses.create') }}"
                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-[#0B6AB2] text-white rounded-lg text-xs font-medium hover:bg-[#13398E] transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        {{ __('admin.add') }}
                    </a>
                @endcan
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-[#1A3A5C]">
                        <th class="text-left px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                            {{ __('admin.school') }}</th>
                        <th class="text-center px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                            {{ __('admin.capacity') }}</th>
                        <th class="text-center px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                            {{ __('admin.utilization') }}</th>
                        <th class="text-center px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                            {{ __('admin.expires') }}</th>
                        <th class="text-center px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                            {{ __('admin.status') }}</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                            {{ __('admin.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-[#1A3A5C]/50">
                    @forelse($licenses as $license)
                        @php
                            $total = $license->totalSeats();
                            $used = $license->used_seats;
                            $pct = $total > 0 ? round(($used / $total) * 100) : 0;
                        @endphp
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-[#0A1628]/30 transition">
                            <td class="px-5 py-3 font-medium text-gray-900 dark:text-white">{{ $license->school?->name ?? '—' }}
                            </td>
                            <td class="px-5 py-3 text-center font-medium tabular-nums">{{ $used }}/{{ $total }}</td>
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2 justify-center">
                                    <div class="w-20 bg-gray-100 dark:bg-[#0A1628] rounded-full h-2 overflow-hidden">
                                        <div class="h-full rounded-full {{ $pct >= 90 ? 'bg-red-500' : ($pct >= 70 ? 'bg-amber-500' : 'bg-emerald-500') }}"
                                            style="width: {{ min($pct, 100) }}%"></div>
                                    </div>
                                    <span
                                        class="text-xs font-bold {{ $pct >= 90 ? 'text-red-500' : ($pct >= 70 ? 'text-amber-500' : 'text-emerald-500') }} tabular-nums">{{ $pct }}%</span>
                                </div>
                            </td>
                            <td class="px-5 py-3 text-center text-xs text-gray-500">
                                {{ $license->expires_at?->format('d.m.Y') ?? '—' }}</td>
                            <td class="px-5 py-3 text-center">
                                @if($license->is_active && (!$license->expires_at || $license->expires_at->isFuture()))
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600">{{ __('admin.active') }}</span>
                                @elseif($license->expires_at && $license->expires_at->isPast())
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-50 dark:bg-red-900/20 text-red-500">{{ __('admin.expired') }}</span>
                                @else
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-50 dark:bg-gray-900/20 text-gray-500">{{ __('admin.inactive') }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.licenses.show', $license) }}"
                                        class="text-xs text-[#0B6AB2] hover:underline">{{ __('admin.view') }}</a>
                                    @can('update', $license)<a href="{{ route('admin.licenses.edit', $license) }}"
                                    class="text-xs text-gray-500 hover:underline">{{ __('admin.edit') }}</a>@endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-gray-400">{{ __('admin.no_data') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if($licenses->hasPages())
                <div class="px-5 py-3 border-t border-gray-100 dark:border-[#1A3A5C]">{{ $licenses->links() }}</div>
            @endif
        </div>
    </div>
@endsection