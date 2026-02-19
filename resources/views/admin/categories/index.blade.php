@extends('admin.layouts.app')

@section('title', __('admin.categories'))

@section('breadcrumb')
    <div class="flex items-center gap-2 text-sm">
        <a href="{{ route('admin.dashboard') }}"
            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">{{ __('admin.dashboard') }}</a>
        <svg class="w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-gray-900 dark:text-gray-100 font-semibold">{{ __('admin.categories') }}</span>
    </div>
@endsection

@section('content')
    <div class="space-y-6">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div
                class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-4 text-center">
                <p class="text-2xl font-extrabold text-[#13398E]">{{ $stats['total'] }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ __('admin.total') }}</p>
            </div>
            <div
                class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-4 text-center">
                <p class="text-2xl font-extrabold text-emerald-500">{{ $stats['active'] }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ __('admin.active') }}</p>
            </div>
            <div
                class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-4 text-center">
                <p class="text-2xl font-extrabold text-red-500">{{ $stats['inactive'] }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ __('admin.inactive') }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-4">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('admin.search') }}..."
                    class="rounded-lg border-gray-200 dark:border-[#1A3A5C] dark:bg-[#0A1628] text-sm focus:ring-[#0B6AB2] focus:border-[#0B6AB2]">
                <input type="hidden" name="type" value="{{ $type }}">
                <select name="type" class="rounded-lg border-gray-200 dark:border-[#1A3A5C] dark:bg-[#0A1628] text-sm">
                    <option value="post" {{ $type === 'post' ? 'selected' : '' }}>Post</option>
                    <option value="page" {{ $type === 'page' ? 'selected' : '' }}>Page</option>
                    <option value="faq" {{ $type === 'faq' ? 'selected' : '' }}>FAQ</option>
                </select>
                <div class="flex gap-2">
                    <button type="submit"
                        class="flex-1 px-4 py-2 bg-[#0B6AB2] text-white rounded-lg text-sm hover:bg-[#13398E] transition">{{ __('admin.filter') }}</button>
                    <a href="{{ route('admin.categories.index', ['type' => $type]) }}"
                        class="px-3 py-2 border border-gray-200 dark:border-[#1A3A5C] rounded-lg text-sm hover:bg-gray-50 dark:hover:bg-[#0A1628] transition">✕</a>
                </div>
            </form>
        </div>

        <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] overflow-hidden">
            <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100 dark:border-[#1A3A5C]">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white">{{ __('admin.categories') }} —
                    {{ ucfirst($type) }} <span class="text-gray-400 font-normal">({{ $categories->total() }})</span></h3>
                @can('categories.create')
                    <a href="{{ route('admin.categories.create', ['type' => $type]) }}"
                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-[#0B6AB2] text-white rounded-lg text-xs font-medium hover:bg-[#13398E] transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        {{ __('admin.add') }}
                    </a>
                @endcan
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-[#1A3A5C]">
                        <th class="text-left px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                            {{ __('admin.name') }}</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">Slug
                        </th>
                        <th class="text-center px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                            {{ __('admin.children') }}</th>
                        <th class="text-center px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                            {{ __('admin.sort_order') }}</th>
                        <th class="text-center px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                            {{ __('admin.status') }}</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                            {{ __('admin.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-[#1A3A5C]/50">
                    @forelse($categories as $category)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-[#0A1628]/30 transition">
                            <td class="px-5 py-3 font-medium text-gray-900 dark:text-white">
                                {{ $category->getTranslation('name') }}</td>
                            <td class="px-5 py-3 text-xs text-gray-500 font-mono">{{ $category->slug }}</td>
                            <td class="px-5 py-3 text-center">
                                @if($category->children->count())
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-blue-50 dark:bg-blue-900/20 text-blue-600">{{ $category->children->count() }}</span>
                                @else <span class="text-gray-300">—</span> @endif
                            </td>
                            <td class="px-5 py-3 text-center text-xs text-gray-500">{{ $category->sort_order }}</td>
                            <td class="px-5 py-3 text-center">
                                @if($category->is_active)
                                    <span
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600"><span
                                            class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>{{ __('admin.active') }}</span>
                                @else
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-50 dark:bg-gray-900/20 text-gray-500">{{ __('admin.inactive') }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @can('categories.edit')<a href="{{ route('admin.categories.edit', $category) }}"
                                    class="text-xs text-gray-500 hover:underline">{{ __('admin.edit') }}</a>@endcan
                                    @can('categories.delete')
                                        <form action="{{ route('admin.categories.destroy', $category) }}" method="POST"
                                            onsubmit="return confirm('{{ __('admin.confirm') }}?')">@csrf @method('DELETE')
                                            <button class="text-xs text-red-500 hover:underline">{{ __('admin.delete') }}</button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-gray-400">{{ __('admin.no_data') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if($categories->hasPages())
                <div class="px-5 py-3 border-t border-gray-100 dark:border-[#1A3A5C]">{{ $categories->links() }}</div>
            @endif
        </div>
    </div>
@endsection