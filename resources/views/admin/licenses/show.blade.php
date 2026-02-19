@extends('admin.layouts.app')

@section('title', $license->school?->name . ' — ' . __('admin.license'))

@section('breadcrumb')
    <div class="flex items-center gap-2 text-sm">
        <a href="{{ route('admin.dashboard') }}"
            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">{{ __('admin.dashboard') }}</a>
        <svg class="w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <a href="{{ route('admin.licenses.index') }}"
            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">{{ __('admin.licenses') }}</a>
        <svg class="w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span
            class="text-gray-900 dark:text-gray-100 font-semibold">{{ $license->school?->name ?? '#' . $license->id }}</span>
    </div>
@endsection

@section('content')
    @php
        $total = $license->totalSeats();
        $used = $license->used_seats;
        $pct = $total > 0 ? round(($used / $total) * 100) : 0;
    @endphp
    <div class="space-y-6">
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">
                    {{ $license->school?->name ?? __('admin.license') . ' #' . $license->id }}</h1>
                <div class="flex items-center gap-3 mt-1 text-xs text-gray-400">
                    @if($license->is_active && (!$license->expires_at || $license->expires_at->isFuture()))
                        <span
                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600"><span
                                class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>{{ __('admin.active') }}</span>
                    @elseif($license->expires_at && $license->expires_at->isPast())
                        <span
                            class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-50 dark:bg-red-900/20 text-red-500">{{ __('admin.expired') }}</span>
                    @else
                        <span
                            class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-50 dark:bg-gray-900/20 text-gray-500">{{ __('admin.inactive') }}</span>
                    @endif
                    <span>{{ __('admin.created') }}: {{ $license->created_at?->format('d.m.Y') }}</span>
                </div>
            </div>
            @can('update', $license)
                <a href="{{ route('admin.licenses.edit', $license) }}"
                    class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-white bg-[#0B6AB2] rounded-lg hover:bg-[#13398E] transition">{{ __('admin.edit') }}</a>
            @endcan
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div
                class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-4 text-center">
                <p class="text-2xl font-extrabold text-[#0B6AB2]">{{ $total }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ __('admin.total_seats') }}</p>
            </div>
            <div
                class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-4 text-center">
                <p class="text-2xl font-extrabold text-[#13398E]">{{ $used }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ __('admin.used_seats') }}</p>
            </div>
            <div
                class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-4 text-center">
                <p class="text-2xl font-extrabold {{ $total - $used > 0 ? 'text-emerald-500' : 'text-red-500' }}">
                    {{ $total - $used }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ __('admin.available') }}</p>
            </div>
            <div
                class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-4 text-center">
                <p
                    class="text-2xl font-extrabold {{ $pct >= 90 ? 'text-red-500' : ($pct >= 70 ? 'text-amber-500' : 'text-emerald-500') }}">
                    %{{ $pct }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ __('admin.utilization') }}</p>
            </div>
        </div>

        {{-- Utilization Bar --}}
        <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('admin.utilization') }}</span>
                <span
                    class="text-sm font-bold {{ $pct >= 90 ? 'text-red-500' : ($pct >= 70 ? 'text-amber-500' : 'text-emerald-500') }}">{{ $used }}/{{ $total }}</span>
            </div>
            <div class="w-full bg-gray-100 dark:bg-[#0A1628] rounded-full h-3 overflow-hidden">
                <div class="h-full rounded-full transition-all {{ $pct >= 90 ? 'bg-red-500' : ($pct >= 70 ? 'bg-amber-500' : 'bg-emerald-500') }}"
                    style="width: {{ min($pct, 100) }}%"></div>
            </div>
        </div>

        <div x-data="{ activeTab: 'details' }">
            <div class="border-b border-gray-200 dark:border-[#1A3A5C]">
                <nav class="flex gap-6 px-1 -mb-px">
                    <button @click="activeTab = 'details'"
                        :class="activeTab === 'details' ? 'border-[#0B6AB2] text-[#0B6AB2]' : 'border-transparent text-gray-500 hover:text-gray-700'"
                        class="py-3 px-1 text-sm font-medium border-b-2 transition">{{ __('admin.details') }}</button>
                    @if($license->purchases && $license->purchases->count())
                        <button @click="activeTab = 'purchases'"
                            :class="activeTab === 'purchases' ? 'border-[#0B6AB2] text-[#0B6AB2]' : 'border-transparent text-gray-500 hover:text-gray-700'"
                            class="py-3 px-1 text-sm font-medium border-b-2 transition">{{ __('admin.purchases') }}
                            ({{ $license->purchases->count() }})</button>
                    @endif
                </nav>
            </div>

            <div x-show="activeTab === 'details'" class="pt-6">
                <div
                    class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] overflow-hidden">
                    <dl class="divide-y divide-gray-50 dark:divide-[#1A3A5C]/50">
                        <div class="grid grid-cols-3 px-5 py-3">
                            <dt class="text-xs font-semibold uppercase tracking-wider text-gray-400">
                                {{ __('admin.school') }}</dt>
                            <dd class="col-span-2 text-sm text-gray-900 dark:text-white">
                                @if($license->school)
                                    <a href="{{ route('admin.schools.show', $license->school) }}"
                                        class="text-[#0B6AB2] hover:underline">{{ $license->school->name }}</a>
                                @else — @endif
                            </dd>
                        </div>
                        <div class="grid grid-cols-3 px-5 py-3">
                            <dt class="text-xs font-semibold uppercase tracking-wider text-gray-400">
                                {{ __('admin.base_seats') }}</dt>
                            <dd class="col-span-2 text-sm text-gray-900 dark:text-white">{{ $license->seat_count }}</dd>
                        </div>
                        <div class="grid grid-cols-3 px-5 py-3">
                            <dt class="text-xs font-semibold uppercase tracking-wider text-gray-400">
                                {{ __('admin.starts_at') }}</dt>
                            <dd class="col-span-2 text-sm text-gray-900 dark:text-white">
                                {{ $license->starts_at?->format('d.m.Y') ?? '—' }}</dd>
                        </div>
                        <div class="grid grid-cols-3 px-5 py-3">
                            <dt class="text-xs font-semibold uppercase tracking-wider text-gray-400">
                                {{ __('admin.expires_at') }}</dt>
                            <dd class="col-span-2 text-sm text-gray-900 dark:text-white">
                                {{ $license->expires_at?->format('d.m.Y') ?? '—' }}</dd>
                        </div>
                        <div class="grid grid-cols-3 px-5 py-3">
                            <dt class="text-xs font-semibold uppercase tracking-wider text-gray-400">{{ __('admin.admin') }}
                            </dt>
                            <dd class="col-span-2 text-sm text-gray-900 dark:text-white">{{ $license->admin?->name ?? '—' }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            @if($license->purchases && $license->purchases->count())
                <div x-show="activeTab === 'purchases'" x-cloak class="pt-6">
                    <div
                        class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] overflow-hidden">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-100 dark:border-[#1A3A5C]">
                                    <th
                                        class="text-left px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                                        {{ __('admin.date') }}</th>
                                    <th
                                        class="text-center px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                                        {{ __('admin.seats') }}</th>
                                    <th
                                        class="text-left px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                                        {{ __('admin.notes') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 dark:divide-[#1A3A5C]/50">
                                @foreach($license->purchases as $purchase)
                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-[#0A1628]/30 transition">
                                        <td class="px-5 py-3 text-gray-500 text-xs">
                                            {{ $purchase->created_at?->format('d.m.Y H:i') }}</td>
                                        <td class="px-5 py-3 text-center"><span
                                                class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-blue-50 dark:bg-blue-900/20 text-blue-600">+{{ $purchase->seat_count }}</span>
                                        </td>
                                        <td class="px-5 py-3 text-gray-500 text-xs">{{ $purchase->notes ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection