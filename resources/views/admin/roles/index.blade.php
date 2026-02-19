@extends('admin.layouts.app')

@section('title', __('admin.roles'))

@section('breadcrumb')
    <div class="flex items-center gap-2 text-sm">
        <a href="{{ route('admin.dashboard') }}"
            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">{{ __('admin.dashboard') }}</a>
        <svg class="w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-gray-900 dark:text-gray-100 font-semibold">{{ __('admin.roles') }}</span>
    </div>
@endsection

@section('content')
    <div class="space-y-6">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div
                class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5 flex items-center gap-4">
                <div class="w-11 h-11 rounded-lg bg-purple-500/10 flex items-center justify-center"><svg
                        class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg></div>
                <div>
                    <p class="text-2xl font-extrabold text-gray-900 dark:text-white">{{ $stats['total'] }}</p>
                    <p class="text-xs text-gray-400">{{ __('admin.total_roles') }}</p>
                </div>
            </div>
            <div
                class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5 flex items-center gap-4">
                <div class="w-11 h-11 rounded-lg bg-[#0B6AB2]/10 flex items-center justify-center"><svg
                        class="w-5 h-5 text-[#0B6AB2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg></div>
                <div>
                    <p class="text-2xl font-extrabold text-gray-900 dark:text-white">{{ $stats['total_permissions'] }}</p>
                    <p class="text-xs text-gray-400">{{ __('admin.total_permissions') }}</p>
                </div>
            </div>
            <div
                class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5 flex items-center gap-4">
                <div class="w-11 h-11 rounded-lg bg-emerald-500/10 flex items-center justify-center"><svg
                        class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2" />
                    </svg></div>
                <div>
                    <p class="text-2xl font-extrabold text-gray-900 dark:text-white">{{ $stats['total_assigned_users'] }}
                    </p>
                    <p class="text-xs text-gray-400">{{ __('admin.assigned_users') }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-4">
            <form method="GET" class="flex gap-3">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('admin.search') }}..."
                    class="flex-1 rounded-lg border-gray-200 dark:border-[#1A3A5C] dark:bg-[#0A1628] text-sm focus:ring-[#0B6AB2] focus:border-[#0B6AB2]">
                <button type="submit"
                    class="px-4 py-2 bg-[#0B6AB2] text-white rounded-lg text-sm hover:bg-[#13398E] transition">{{ __('admin.search') }}</button>
            </form>
        </div>

        <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] overflow-hidden">
            <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100 dark:border-[#1A3A5C]">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white">{{ __('admin.roles') }}</h3>
                <div class="flex items-center gap-2">
                    @can('syncPermissions', Spatie\Permission\Models\Role::class)
                        <form method="POST" action="{{ route('admin.roles.sync-permissions') }}" class="inline">
                            @csrf
                            <button type="submit"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 border border-gray-200 dark:border-[#1A3A5C] rounded-lg text-xs font-medium hover:bg-gray-50 dark:hover:bg-[#0A1628] transition">🔄
                                {{ __('admin.sync') }}</button>
                        </form>
                    @endcan
                    @can('create', Spatie\Permission\Models\Role::class)
                        <a href="{{ route('admin.roles.create') }}"
                            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-[#0B6AB2] text-white rounded-lg text-xs font-medium hover:bg-[#13398E] transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            {{ __('admin.add') }}
                        </a>
                    @endcan
                </div>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-[#1A3A5C]">
                        <th class="text-left px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                            {{ __('admin.role') }}</th>
                        <th class="text-center px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                            {{ __('admin.users') }}</th>
                        <th class="text-center px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                            {{ __('admin.permissions') }}</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                            {{ __('admin.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-[#1A3A5C]/50">
                    @forelse($roles as $role)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-[#0A1628]/30 transition">
                            <td class="px-5 py-3">
                                <span class="inline-flex items-center gap-2 font-medium text-gray-900 dark:text-white">
                                    <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                                    {{ $role->name }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-center"><span
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-blue-50 dark:bg-blue-900/20 text-blue-600">{{ $role->users_count }}</span>
                            </td>
                            <td class="px-5 py-3 text-center"><span
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-purple-50 dark:bg-purple-900/20 text-purple-600">{{ $role->permissions_count }}</span>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.roles.show', $role) }}"
                                        class="text-xs text-[#0B6AB2] hover:underline">{{ __('admin.view') }}</a>
                                    @can('update', $role)<a href="{{ route('admin.roles.edit', $role) }}"
                                    class="text-xs text-gray-500 hover:underline">{{ __('admin.edit') }}</a>@endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-8 text-center text-gray-400">{{ __('admin.no_data') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if($roles->hasPages())
                <div class="px-5 py-3 border-t border-gray-100 dark:border-[#1A3A5C]">{{ $roles->links() }}</div>
            @endif
        </div>
    </div>
@endsection