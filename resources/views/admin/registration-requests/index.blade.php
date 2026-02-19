@extends('admin.layouts.app')

@section('title', __('admin.registration_requests'))

@section('breadcrumb')
    <div class="flex items-center gap-2 text-sm">
        <a href="{{ route('admin.dashboard') }}"
            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">{{ __('admin.dashboard') }}</a>
        <svg class="w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-gray-900 dark:text-gray-100 font-semibold">{{ __('admin.registration_requests') }}</span>
    </div>
@endsection

@section('content')
    <div class="space-y-6">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div
                class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-4 text-center">
                <p class="text-2xl font-extrabold text-[#13398E]">{{ $stats['total'] }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ __('admin.total') }}</p>
            </div>
            <div
                class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-4 text-center">
                <p class="text-2xl font-extrabold text-amber-500">{{ $stats['pending'] }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ __('admin.pending') }}</p>
            </div>
            <div
                class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-4 text-center">
                <p class="text-2xl font-extrabold text-emerald-500">{{ $stats['approved'] }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ __('admin.approved') }}</p>
            </div>
            <div
                class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-4 text-center">
                <p class="text-2xl font-extrabold text-red-500">{{ $stats['rejected'] }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ __('admin.rejected') }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-4">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('admin.search') }}..."
                    class="rounded-lg border-gray-200 dark:border-[#1A3A5C] dark:bg-[#0A1628] text-sm focus:ring-[#0B6AB2] focus:border-[#0B6AB2]">
                <select name="status" class="rounded-lg border-gray-200 dark:border-[#1A3A5C] dark:bg-[#0A1628] text-sm">
                    <option value="">{{ __('admin.all_statuses') }}</option>
                    <option value="new" {{ request('status') === 'new' ? 'selected' : '' }}>{{ __('admin.new') }}</option>
                    <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>
                        {{ __('admin.processing') }}</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>
                        {{ __('admin.approved') }}</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>
                        {{ __('admin.rejected') }}</option>
                </select>
                <div class="flex gap-2">
                    <button type="submit"
                        class="flex-1 px-4 py-2 bg-[#0B6AB2] text-white rounded-lg text-sm hover:bg-[#13398E] transition">{{ __('admin.filter') }}</button>
                    <a href="{{ route('admin.registration-requests.index') }}"
                        class="px-3 py-2 border border-gray-200 dark:border-[#1A3A5C] rounded-lg text-sm hover:bg-gray-50 dark:hover:bg-[#0A1628] transition">✕</a>
                </div>
            </form>
        </div>

        <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-[#1A3A5C]">
                        <th class="text-left px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                            {{ __('admin.school_name') }}</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                            {{ __('admin.contact') }}</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                            {{ __('admin.email') }}</th>
                        <th class="text-center px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                            {{ __('admin.status') }}</th>
                        <th class="text-center px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                            {{ __('admin.date') }}</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                            {{ __('admin.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-[#1A3A5C]/50">
                    @forelse($requests as $req)
                        @php
                            $statusColors = [
                                'new' => 'amber',
                                'pending' => 'amber',
                                'processing' => 'blue',
                                'approved' => 'emerald',
                                'rejected' => 'red',
                            ];
                            $sc = $statusColors[$req->status] ?? 'gray';
                        @endphp
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-[#0A1628]/30 transition">
                            <td class="px-5 py-3 font-medium text-gray-900 dark:text-white">{{ $req->school_name ?? '—' }}</td>
                            <td class="px-5 py-3 text-gray-500">{{ $req->contact_name ?? '—' }}</td>
                            <td class="px-5 py-3 text-gray-500">{{ $req->email ?? '—' }}</td>
                            <td class="px-5 py-3 text-center">
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-{{ $sc }}-50 dark:bg-{{ $sc }}-900/20 text-{{ $sc }}-600">{{ ucfirst($req->status) }}</span>
                            </td>
                            <td class="px-5 py-3 text-center text-xs text-gray-500">{{ $req->created_at?->format('d.m.Y') }}
                            </td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('admin.registration-requests.show', $req) }}"
                                    class="text-xs text-[#0B6AB2] hover:underline">{{ __('admin.view') }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-gray-400">{{ __('admin.no_data') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if($requests->hasPages())
                <div class="px-5 py-3 border-t border-gray-100 dark:border-[#1A3A5C]">{{ $requests->links() }}</div>
            @endif
        </div>
    </div>
@endsection