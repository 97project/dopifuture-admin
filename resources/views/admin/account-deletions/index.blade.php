@extends('admin.layouts.app')

@section('title', __('admin.account_deletions'))

@section('breadcrumb')
    <div class="flex items-center gap-2 text-sm">
        <a href="{{ route('admin.dashboard') }}"
            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">{{ __('admin.dashboard') }}</a>
        <svg class="w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-gray-900 dark:text-gray-100 font-semibold">{{ __('admin.account_deletions') }}</span>
    </div>
@endsection

@section('content')
    <div class="space-y-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div
                class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-4 text-center">
                <p class="text-2xl font-extrabold text-[#13398E]">{{ $stats['total'] ?? 0 }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ __('admin.total_deleted') }}</p>
            </div>
            <div
                class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-4 text-center">
                <p class="text-2xl font-extrabold text-red-500">{{ $stats['pending'] ?? 0 }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ __('admin.pending_deletion') }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-4">
            <form method="GET" class="flex gap-3">
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="{{ __('admin.search') }} ({{ __('admin.name') }}, {{ __('admin.email') }})..."
                    class="flex-1 rounded-lg border-gray-200 dark:border-[#1A3A5C] dark:bg-[#0A1628] text-sm focus:ring-[#0B6AB2] focus:border-[#0B6AB2]">
                <button type="submit"
                    class="px-4 py-2 bg-[#0B6AB2] text-white rounded-lg text-sm hover:bg-[#13398E] transition">{{ __('admin.search') }}</button>
                <a href="{{ route('admin.account-deletions.index') }}"
                    class="px-3 py-2 border border-gray-200 dark:border-[#1A3A5C] rounded-lg text-sm hover:bg-gray-50 dark:hover:bg-[#0A1628] transition">✕</a>
            </form>
        </div>

        <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100 dark:border-[#1A3A5C]">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white">{{ __('admin.account_deletions') }} <span
                        class="text-gray-400 font-normal">({{ $deletedUsers->total() }})</span></h3>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-[#1A3A5C]">
                        <th class="text-left px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">ID</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                            {{ __('admin.name') }}</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                            {{ __('admin.email') }}</th>
                        <th class="text-center px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                            {{ __('admin.deleted_at') }}</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                            {{ __('admin.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-[#1A3A5C]/50">
                    @forelse($deletedUsers as $user)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-[#0A1628]/30 transition">
                            <td class="px-5 py-3 text-xs font-mono text-gray-400">#{{ $user->id }}</td>
                            <td class="px-5 py-3 font-medium text-gray-900 dark:text-white">{{ $user->name }}</td>
                            <td class="px-5 py-3 text-gray-500">{{ $user->email }}</td>
                            <td class="px-5 py-3 text-center text-xs text-gray-500">{{ $user->deleted_at->format('d.m.Y H:i') }}
                            </td>
                            <td class="px-5 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <form action="{{ route('admin.account-deletions.restore', $user->id) }}" method="POST"
                                        class="inline">@csrf
                                        <button type="submit"
                                            class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 hover:bg-emerald-100 transition">{{ __('admin.restore') }}</button>
                                    </form>
                                    <form action="{{ route('admin.account-deletions.force-delete', $user->id) }}" method="POST"
                                        class="inline" onsubmit="return confirm('{{ __('admin.confirm_permanent_delete') }}')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                            class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-red-50 dark:bg-red-900/20 text-red-500 hover:bg-red-100 transition">{{ __('admin.permanent_delete') }}</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-gray-400">{{ __('admin.no_deleted_accounts') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if($deletedUsers->hasPages())
                <div class="px-5 py-3 border-t border-gray-100 dark:border-[#1A3A5C]">{{ $deletedUsers->links() }}</div>
            @endif
        </div>
    </div>
@endsection