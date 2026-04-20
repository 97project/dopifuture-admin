@extends('admin.layouts.app')
@section('title', __('admin.edit_menu') . ': ' . $menu->name)
@section('content')
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('admin.edit_menu') }}: {{ $menu->name }}</h1>
            <a href="{{ route('admin.menus.index') }}" class="text-sm text-gray-500 hover:text-gray-700">←
                {{ __('admin.back') }}</a>
        </div>

        {{-- Menu Settings --}}
        <form action="{{ route('admin.menus.update', $menu) }}" method="POST"
            class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-6 space-y-4">
            @csrf @method('PUT')
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.name') }}
                        *</label>
                    <input type="text" name="name" value="{{ $menu->name }}" required
                        class="w-full px-3 py-2 border border-gray-200 dark:border-[#1A3A5C] rounded-lg bg-white dark:bg-[#0A1628] text-gray-900 dark:text-white">
                </div>
                <div>
                    <label
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.location') }}</label>
                    <select name="location"
                        class="w-full px-3 py-2 border border-gray-200 dark:border-[#1A3A5C] rounded-lg bg-white dark:bg-[#0A1628]">
                        <option value="header" @selected($menu->location === 'header')>Header</option>
                        <option value="footer" @selected($menu->location === 'footer')>Footer</option>
                        <option value="sidebar" @selected($menu->location === 'sidebar')>Sidebar</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <label class="flex items-center gap-2 mb-2">
                        <input type="checkbox" name="is_active" value="1" {{ $menu->is_active ? 'checked' : '' }}
                            class="rounded border-gray-300 text-[#0B6AB2]">
                        <span class="text-sm">{{ __('admin.active') }}</span>
                    </label>
                </div>
            </div>
            <button type="submit"
                class="px-4 py-2 bg-[#0B6AB2] hover:bg-[#13398E] text-white text-sm rounded-lg">{{ __('admin.save') }}</button>
        </form>

        {{-- Menu Items --}}
        <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('admin.menu_items') }}</h3>

            {{-- Existing Items --}}
            <div class="space-y-2 mb-6">
                @forelse($menu->allItems as $item)
                    <div
                        class="flex items-center justify-between px-4 py-2.5 bg-gray-50 dark:bg-[#0A1628] rounded-lg {{ $item->parent_id ? 'ml-8' : '' }}">
                        <div class="flex items-center gap-3">
                            @if($item->icon) <span class="text-gray-400">{{ $item->icon }}</span> @endif
                            <span class="font-medium text-sm">{{ $item->getTranslation('title') }}</span>
                            <span class="text-xs text-gray-400">→ {{ $item->resolved_url ?: $item->url }}</span>
                        </div>
                        <form action="{{ route('admin.menus.items.destroy', [$menu, $item]) }}" method="POST"
                            onsubmit="return confirm('{{ __('admin.confirm') }}?')">@csrf @method('DELETE')
                            <button class="p-1 text-gray-400 hover:text-red-600"><svg class="w-4 h-4" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg></button>
                        </form>
                    </div>
                @empty
                    <p class="text-sm text-gray-400 py-4 text-center">{{ __('admin.no_menu_items') }}</p>
                @endforelse
            </div>

            {{-- Add Item --}}
            <div class="border-t border-gray-100 dark:border-[#1A3A5C] pt-4">
                <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">{{ __('admin.add_menu_item') }}</h4>
                <form action="{{ route('admin.menus.items.store', $menu) }}" method="POST" class="space-y-3">
                    @csrf
                    <div class="grid grid-cols-2 gap-3">
                        @foreach(['tr', 'en'] as $locale)
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('admin.title') }}
                                    ({{ mb_strtoupper($locale) }}) *</label>
                                <input type="text" name="title[{{ $locale }}]" required
                                    class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-[#1A3A5C] rounded-lg bg-white dark:bg-[#0A1628]">
                            </div>
                        @endforeach
                    </div>
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">URL</label>
                            <input type="text" name="url" placeholder="https://..."
                                class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-[#1A3A5C] rounded-lg bg-white dark:bg-[#0A1628]">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Link to</label>
                            <select name="linkable_type"
                                class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-[#1A3A5C] rounded-lg bg-white dark:bg-[#0A1628]">
                                <option value="">Custom URL</option>
                                <option value="page">Page</option>
                                <option value="post">Post</option>
                                <option value="category">Category</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Target</label>
                            <select name="target"
                                class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-[#1A3A5C] rounded-lg bg-white dark:bg-[#0A1628]">
                                <option value="_self">Same tab</option>
                                <option value="_blank">New tab</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit"
                        class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm rounded-lg">{{ __('admin.add') }}</button>
                </form>
            </div>
        </div>
    </div>
@endsection