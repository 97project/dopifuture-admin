@extends('admin.layouts.app')

@section('title', __('admin.permissions'))

@section('breadcrumb')
    <div class="flex items-center gap-2 text-sm">
        <a href="{{ route('admin.dashboard') }}"
            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">{{ __('admin.dashboard') }}</a>
        <svg class="w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-gray-900 dark:text-gray-100 font-semibold">{{ __('admin.permissions') }}</span>
    </div>
@endsection

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ __('admin.permissions') }}</h1>
                <p class="text-xs text-gray-400 mt-1">
                    {{ __('admin.rep_edit_permissions') }}
                </p>
            </div>
            <form action="{{ route('admin.permissions.sync') }}" method="POST">@csrf
                <button type="submit"
                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-[#0B6AB2] text-white rounded-lg text-xs font-medium hover:bg-[#13398E] transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    {{ __('admin.sync_permissions') }}
                </button>
            </form>
        </div>

        @if(session('success'))
            <div
                class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400 px-4 py-3 rounded-lg text-sm">
                {{ session('success') }}</div>
        @endif

        @foreach($permissions as $module => $perms)
            <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] overflow-hidden">
                <div class="px-5 py-3 border-b border-gray-100 dark:border-[#1A3A5C] flex items-center justify-between">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-900 dark:text-white">{{ $module }}</h3>
                    <span
                        class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 dark:bg-blue-900/20 text-blue-600">{{ count($perms) }}</span>
                </div>
                <div class="divide-y divide-gray-50 dark:divide-[#1A3A5C]/50">
                    @foreach($perms as $perm)
                        <form action="{{ route('admin.permissions.update', $perm) }}" method="POST"
                            class="px-5 py-2.5 flex items-center gap-4 hover:bg-gray-50/50 dark:hover:bg-[#0A1628]/30 transition {{ $perm->is_deprecated ? 'opacity-40' : '' }}">
                            @csrf @method('PUT')
                            <div class="w-48 flex-shrink-0">
                                <code
                                    class="text-[10px] px-1.5 py-0.5 rounded bg-gray-100 dark:bg-[#0A1628] text-gray-600 dark:text-gray-400">{{ $perm->name }}</code>
                                @if($perm->is_deprecated) <span class="ml-1 text-[10px] text-red-500">⚠</span> @endif
                            </div>
                            <input type="text" name="alias_tr" value="{{ $perm->alias_tr }}" placeholder="TR alias"
                                class="flex-1 px-3 py-1.5 text-sm rounded-lg border-gray-200 dark:border-[#1A3A5C] dark:bg-[#0A1628] focus:ring-[#0B6AB2] focus:border-[#0B6AB2]">
                            <input type="text" name="alias_en" value="{{ $perm->alias_en }}" placeholder="EN alias"
                                class="flex-1 px-3 py-1.5 text-sm rounded-lg border-gray-200 dark:border-[#1A3A5C] dark:bg-[#0A1628] focus:ring-[#0B6AB2] focus:border-[#0B6AB2]">
                            <button type="submit"
                                class="px-3 py-1.5 text-[10px] font-bold bg-[#0B6AB2]/10 text-[#0B6AB2] rounded-lg hover:bg-[#0B6AB2]/20 transition">{{ __('admin.save') }}</button>
                        </form>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
@endsection