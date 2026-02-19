@extends('admin.layouts.app')

@section('title', $application->name)

@section('breadcrumb')
    <div class="flex items-center gap-2 text-sm">
        <a href="{{ route('admin.dashboard') }}"
            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">{{ __('admin.dashboard') }}</a>
        <svg class="w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <a href="{{ route('admin.applications.index') }}"
            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">{{ __('admin.applications') }}</a>
        <svg class="w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-gray-900 dark:text-gray-100 font-semibold">{{ $application->name }}</span>
    </div>
@endsection

@section('content')
    <div class="space-y-6">
        <div class="flex items-start justify-between">
            <div class="flex items-center gap-4">
                @if($application->icon)
                    <div class="w-14 h-14 rounded-xl flex items-center justify-center text-2xl shadow-lg"
                        style="background: {{ $application->color ?? '#0B6AB2' }}20">
                        {{ $application->icon }}
                    </div>
                @endif
                <div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ $application->name }}</h1>
                    <div class="flex items-center gap-3 mt-1 text-xs text-gray-400">
                        <span class="font-mono">{{ $application->slug }}</span>
                        @if($application->is_active)
                            <span
                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600"><span
                                    class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>{{ __('admin.active') }}</span>
                        @else
                            <span
                                class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-50 dark:bg-gray-900/20 text-gray-500">{{ __('admin.inactive') }}</span>
                        @endif
                    </div>
                </div>
            </div>
            @can('update', $application)
                <a href="{{ route('admin.applications.edit', $application) }}"
                    class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-white bg-[#0B6AB2] rounded-lg hover:bg-[#13398E] transition">{{ __('admin.edit') }}</a>
            @endcan
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
            <div
                class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-4 text-center">
                <p class="text-2xl font-extrabold text-[#0B6AB2]">{{ $application->users_count }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ __('admin.total_users') }}</p>
            </div>
            <div
                class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-4 text-center">
                <p class="text-2xl font-extrabold text-gray-900 dark:text-white">{{ $application->sort_order }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ __('admin.sort_order') }}</p>
            </div>
            <div
                class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-4 text-center">
                <p class="text-2xl font-extrabold {{ $application->is_active ? 'text-emerald-500' : 'text-red-500' }}">
                    {{ $application->is_active ? __('admin.active') : __('admin.inactive') }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ __('admin.status') }}</p>
            </div>
        </div>

        @if($application->description)
            <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-2">{{ __('admin.description') }}</h3>
                <p class="text-sm text-gray-500 leading-relaxed">{{ $application->description }}</p>
            </div>
        @endif

        @if($application->connector_class)
            <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-2">{{ __('admin.connector') }}</h3>
                <code
                    class="text-xs font-mono text-[#0B6AB2] bg-blue-50 dark:bg-blue-900/20 px-2 py-1 rounded">{{ $application->connector_class }}</code>
            </div>
        @endif

        <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100 dark:border-[#1A3A5C]">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white">{{ __('admin.users') }}
                    ({{ $application->users->count() }})</h3>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-[#1A3A5C]">
                        <th class="text-left px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                            {{ __('admin.name') }}</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                            {{ __('admin.email') }}</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                            {{ __('admin.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-[#1A3A5C]/50">
                    @forelse($application->users->take(50) as $user)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-[#0A1628]/30 transition">
                            <td class="px-5 py-3 font-medium text-gray-900 dark:text-white">{{ $user->name }}</td>
                            <td class="px-5 py-3 text-gray-500">{{ $user->email }}</td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('admin.users.show', $user) }}"
                                    class="text-xs text-[#0B6AB2] hover:underline">{{ __('admin.view') }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-5 py-8 text-center text-gray-400">{{ __('admin.no_data') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection