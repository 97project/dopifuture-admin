@extends('admin.layouts.app')

@section('title', __('admin.menus'))

@section('breadcrumb')
    <div class="flex items-center gap-2 text-sm">
        <a href="{{ route('admin.dashboard') }}"
            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">{{ __('admin.dashboard') }}</a>
        <svg class="w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-gray-900 dark:text-gray-100 font-semibold">{{ __('admin.menus') }}</span>
    </div>
@endsection

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-end">
            @can('menus.create')
                <a href="{{ route('admin.menus.create') }}"
                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-[#0B6AB2] text-white rounded-lg text-xs font-medium hover:bg-[#13398E] transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    {{ __('admin.new_menu') }}
                </a>
            @endcan
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($menus as $menu)
                <div
                    class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5 hover:shadow-lg hover:border-[#0B6AB2]/30 transition-all">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-bold text-gray-900 dark:text-white">{{ $menu->name }}</h3>
                        @if($menu->is_active)
                            <span
                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600"><span
                                    class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>{{ __('admin.active') }}</span>
                        @else
                            <span
                                class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-50 dark:bg-gray-900/20 text-gray-500">{{ __('admin.inactive') }}</span>
                        @endif
                    </div>
                    <div class="flex items-center gap-4 text-xs text-gray-500 mb-4">
                        <span>📍 <span class="font-medium capitalize">{{ $menu->location }}</span></span>
                        <span
                            class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 dark:bg-blue-900/20 text-blue-600">{{ $menu->all_items_count }}
                            {{ __('admin.items') }}</span>
                    </div>
                    <div class="flex gap-2">
                        @can('menus.edit')
                            <a href="{{ route('admin.menus.edit', $menu) }}"
                                class="flex-1 text-center px-3 py-2 bg-[#0B6AB2]/10 text-[#0B6AB2] hover:bg-[#0B6AB2]/20 text-xs font-medium rounded-lg transition">{{ __('admin.edit') }}</a>
                        @endcan
                        @can('menus.delete')
                            <form action="{{ route('admin.menus.destroy', $menu) }}" method="POST"
                                onsubmit="return confirm('{{ __('admin.confirm') }}?')">@csrf @method('DELETE')
                                <button
                                    class="px-3 py-2 bg-red-50 dark:bg-red-900/20 text-red-500 hover:bg-red-100 text-xs font-medium rounded-lg transition">{{ __('admin.delete') }}</button>
                            </form>
                        @endcan
                    </div>
                </div>
            @empty
                <div class="col-span-3 py-12 text-center text-gray-400">{{ __('admin.no_data') }}</div>
            @endforelse
        </div>
    </div>
@endsection