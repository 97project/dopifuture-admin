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
                    <button @click="activeTab = 'purchases'"
                        :class="activeTab === 'purchases' ? 'border-[#0B6AB2] text-[#0B6AB2]' : 'border-transparent text-gray-500 hover:text-gray-700'"
                        class="py-3 px-1 text-sm font-medium border-b-2 transition">{{ __('admin.purchase_history') }}
                        <span class="ml-1 inline-flex items-center justify-center w-5 h-5 rounded-full text-[10px] font-bold bg-blue-100 dark:bg-blue-900/30 text-blue-600">{{ $license->purchases->count() + 1 }}</span>
                    </button>
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
                        @if($license->notes)
                        <div class="grid grid-cols-3 px-5 py-3">
                            <dt class="text-xs font-semibold uppercase tracking-wider text-gray-400">{{ __('admin.notes') }}</dt>
                            <dd class="col-span-2 text-sm text-gray-500">{{ $license->notes }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>
            </div>

            <div x-show="activeTab === 'purchases'" x-cloak class="pt-6 space-y-5">
                {{-- Add Purchase Form --}}
                @can('update', $license)
                <div x-data="{ showForm: false }">
                    <button @click="showForm = !showForm"
                        class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-white bg-[#0B6AB2] rounded-lg hover:bg-[#13398E] transition mb-4">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        {{ __('admin.add_purchase') }}
                    </button>
                    <div x-show="showForm" x-cloak x-transition
                        class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5 mb-5">
                        <form action="{{ route('admin.licenses.add-purchase', $license) }}" method="POST">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 mb-1">{{ __('admin.seat_count') }} *</label>
                                    <input type="number" name="seat_count" min="1" required placeholder="50"
                                        class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628] text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 mb-1">{{ __('admin.amount') }}</label>
                                    <input type="number" name="amount" step="0.01" min="0" placeholder="0.00"
                                        class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628] text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 mb-1">{{ __('admin.date') }}</label>
                                    <input type="date" name="purchased_at" value="{{ date('Y-m-d') }}"
                                        class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628] text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 mb-1">{{ __('admin.notes') }}</label>
                                    <input type="text" name="notes" placeholder="{{ __('admin.purchase_note_placeholder') }}"
                                        class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628] text-sm">
                                </div>
                            </div>
                            <div class="flex justify-end mt-4">
                                <button type="submit"
                                    class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition">
                                    {{ __('admin.save') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                @endcan

                {{-- Purchase History Timeline --}}
                <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 dark:border-[#1A3A5C]">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white">{{ __('admin.purchase_history') }}</h3>
                        <p class="text-xs text-gray-400 mt-0.5">{{ __('admin.purchase_history_desc') }}</p>
                    </div>
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 dark:border-[#1A3A5C]">
                                <th class="text-left px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                                    {{ __('admin.date') }}</th>
                                <th class="text-left px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                                    {{ __('admin.description') }}</th>
                                <th class="text-center px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                                    {{ __('admin.seats') }}</th>
                                <th class="text-right px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                                    {{ __('admin.amount') }}</th>
                                <th class="text-right px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                                    {{ __('admin.running_total') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-[#1A3A5C]/50">
                            {{-- Initial license creation --}}
                            @php $runningTotal = $license->seat_count; @endphp
                            <tr class="bg-blue-50/30 dark:bg-blue-900/10">
                                <td class="px-5 py-3 text-xs text-gray-500">
                                    {{ $license->created_at?->format('d.m.Y H:i') }}
                                </td>
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 dark:bg-blue-900/30">
                                            <svg class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        </span>
                                        <span class="text-xs font-medium text-gray-700 dark:text-gray-300">{{ __('admin.initial_license') }}</span>
                                    </div>
                                </td>
                                <td class="px-5 py-3 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-blue-50 dark:bg-blue-900/20 text-blue-600">{{ $license->seat_count }}</span>
                                </td>
                                <td class="px-5 py-3 text-right text-xs text-gray-400">—</td>
                                <td class="px-5 py-3 text-right text-xs font-bold text-gray-700 dark:text-gray-300">{{ $runningTotal }}</td>
                            </tr>
                            {{-- Subsequent purchases --}}
                            @foreach($license->purchases->sortBy('created_at') as $purchase)
                                @php $runningTotal += $purchase->seat_count; @endphp
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-[#0A1628]/30 transition">
                                    <td class="px-5 py-3 text-xs text-gray-500">
                                        {{ $purchase->purchased_at?->format('d.m.Y') ?? $purchase->created_at?->format('d.m.Y H:i') }}
                                    </td>
                                    <td class="px-5 py-3">
                                        <div class="flex items-center gap-2">
                                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-emerald-100 dark:bg-emerald-900/30">
                                                <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                            </span>
                                            <span class="text-xs text-gray-600 dark:text-gray-400">{{ $purchase->notes ?? __('admin.seat_addition') }}</span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3 text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600">+{{ $purchase->seat_count }}</span>
                                    </td>
                                    <td class="px-5 py-3 text-right text-xs text-gray-500">
                                        @if($purchase->amount)
                                            {{ number_format($purchase->amount, 2) }} ₺
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 text-right text-xs font-bold text-gray-700 dark:text-gray-300">{{ $runningTotal }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="border-t-2 border-gray-200 dark:border-[#1A3A5C] bg-gray-50/50 dark:bg-[#0A1628]/30">
                                <td colspan="2" class="px-5 py-3 text-xs font-bold uppercase text-gray-500">{{ __('admin.total') }}</td>
                                <td class="px-5 py-3 text-center text-xs font-bold text-gray-900 dark:text-white">{{ $total }}</td>
                                <td class="px-5 py-3 text-right text-xs font-bold text-gray-900 dark:text-white">
                                    @if($license->purchases->sum('amount') > 0)
                                        {{ number_format($license->purchases->sum('amount'), 2) }} ₺
                                    @else — @endif
                                </td>
                                <td class="px-5 py-3 text-right text-xs font-bold text-[#0B6AB2]">{{ $total }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection