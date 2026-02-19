@extends('admin.layouts.app')

@section('title', $role->name)

@section('breadcrumb')
    <div class="flex items-center gap-2 text-sm">
        <a href="{{ route('admin.dashboard') }}"
            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">{{ __('admin.dashboard') }}</a>
        <svg class="w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <a href="{{ route('admin.roles.index') }}"
            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">{{ __('admin.roles') }}</a>
        <svg class="w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-gray-900 dark:text-gray-100 font-semibold">{{ $role->name }}</span>
    </div>
@endsection

@section('content')
    <div class="space-y-6">
        <div class="flex items-start justify-between">
            <div class="flex items-center gap-3">
                <div
                    class="w-12 h-12 rounded-xl bg-gradient-to-br from-purple-500 to-purple-700 flex items-center justify-center shadow-lg shadow-purple-500/20">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ $role->name }}</h1>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $role->users_count }} {{ __('admin.users') }} ·
                        {{ $role->permissions_count }} {{ __('admin.permissions') }}</p>
                </div>
            </div>
            @can('update', $role)
                <a href="{{ route('admin.roles.edit', $role) }}"
                    class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-white bg-[#0B6AB2] rounded-lg hover:bg-[#13398E] transition">{{ __('admin.edit') }}</a>
            @endcan
        </div>

        <div x-data="{ activeTab: 'permissions' }">
            <div class="border-b border-gray-200 dark:border-[#1A3A5C]">
                <nav class="flex gap-6 px-1 -mb-px">
                    <button @click="activeTab = 'permissions'"
                        :class="activeTab === 'permissions' ? 'border-[#0B6AB2] text-[#0B6AB2]' : 'border-transparent text-gray-500 hover:text-gray-700'"
                        class="py-3 px-1 text-sm font-medium border-b-2 transition">{{ __('admin.permissions') }}
                        ({{ $role->permissions_count }})</button>
                    <button @click="activeTab = 'users'"
                        :class="activeTab === 'users' ? 'border-[#0B6AB2] text-[#0B6AB2]' : 'border-transparent text-gray-500 hover:text-gray-700'"
                        class="py-3 px-1 text-sm font-medium border-b-2 transition">{{ __('admin.users') }}
                        ({{ $role->users_count }})</button>
                </nav>
            </div>

            <div x-show="activeTab === 'permissions'" class="pt-6 space-y-4">
                @forelse($permissions as $module => $perms)
                    <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-4">
                        <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-2 uppercase tracking-wide">{{ $module }}
                        </h4>
                        <div class="flex flex-wrap gap-2">
                            @foreach($perms as $perm)
                                @php
                                    $action = str_replace($module . '.', '', $perm->name);
                                    $colors = ['create' => 'emerald', 'update' => 'blue', 'delete' => 'red', 'view' => 'gray', 'viewAny' => 'gray', 'export' => 'amber'];
                                    $c = $colors[$action] ?? 'purple';
                                @endphp
                                <span
                                    class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-{{ $c }}-50 dark:bg-{{ $c }}-900/20 text-{{ $c }}-700 dark:text-{{ $c }}-400">
                                    {{ $action }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <p class="text-center text-gray-400 py-8">{{ __('admin.no_data') }}</p>
                @endforelse
            </div>

            <div x-show="activeTab === 'users'" x-cloak class="pt-6">
                <div
                    class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] overflow-hidden">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 dark:border-[#1A3A5C]">
                                <th
                                    class="text-left px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                                    {{ __('admin.name') }}</th>
                                <th
                                    class="text-left px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                                    {{ __('admin.email') }}</th>
                                <th
                                    class="text-center px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                                    {{ __('admin.status') }}</th>
                                <th
                                    class="text-right px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                                    {{ __('admin.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-[#1A3A5C]/50">
                            @forelse($users as $user)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-[#0A1628]/30 transition">
                                    <td class="px-5 py-3 font-medium text-gray-900 dark:text-white">{{ $user->name }}</td>
                                    <td class="px-5 py-3 text-gray-500">{{ $user->email }}</td>
                                    <td class="px-5 py-3 text-center">
                                        @if($user->status === 'active')
                                            <span
                                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600"><span
                                                    class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>{{ __('admin.active') }}</span>
                                        @else
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-50 dark:bg-gray-900/20 text-gray-500">{{ $user->status }}</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 text-right">
                                        <a href="{{ route('admin.users.show', $user) }}"
                                            class="text-xs text-[#0B6AB2] hover:underline">{{ __('admin.view') }}</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-5 py-8 text-center text-gray-400">{{ __('admin.no_data') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    @if($users->hasPages())
                        <div class="px-5 py-3 border-t border-gray-100 dark:border-[#1A3A5C]">{{ $users->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection