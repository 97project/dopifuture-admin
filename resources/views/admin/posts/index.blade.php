@extends('admin.layouts.app')

@section('title', __('admin.posts'))

@section('breadcrumb')
    <div class="flex items-center gap-2 text-sm">
        <a href="{{ route('admin.dashboard') }}"
            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">{{ __('admin.dashboard') }}</a>
        <svg class="w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-gray-900 dark:text-gray-100 font-semibold">{{ __('admin.posts') }}</span>
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
                <p class="text-2xl font-extrabold text-emerald-500">{{ $stats['published'] }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ __('admin.published') }}</p>
            </div>
            <div
                class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-4 text-center">
                <p class="text-2xl font-extrabold text-amber-500">{{ $stats['draft'] }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ __('admin.draft') }}</p>
            </div>
            <div
                class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-4 text-center">
                <p class="text-2xl font-extrabold text-yellow-500">{{ $stats['featured'] }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ __('admin.featured') }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-4">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-3">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('admin.search') }}..."
                    class="rounded-lg border-gray-200 dark:border-[#1A3A5C] dark:bg-[#0A1628] text-sm focus:ring-[#0B6AB2] focus:border-[#0B6AB2]">
                <select name="status" class="rounded-lg border-gray-200 dark:border-[#1A3A5C] dark:bg-[#0A1628] text-sm">
                    <option value="">{{ __('admin.all_statuses') }}</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>{{ __('admin.draft') }}
                    </option>
                    <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>
                        {{ __('admin.published') }}</option>
                </select>
                <select name="category" class="rounded-lg border-gray-200 dark:border-[#1A3A5C] dark:bg-[#0A1628] text-sm">
                    <option value="">{{ __('admin.all_categories') }}</option>
                    @foreach($categories as $cat)<option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->getTranslation('name') }}</option>@endforeach
                </select>
                <div class="flex gap-2">
                    <button type="submit"
                        class="flex-1 px-4 py-2 bg-[#0B6AB2] text-white rounded-lg text-sm hover:bg-[#13398E] transition">{{ __('admin.filter') }}</button>
                    <a href="{{ route('admin.posts.index') }}"
                        class="px-3 py-2 border border-gray-200 dark:border-[#1A3A5C] rounded-lg text-sm hover:bg-gray-50 dark:hover:bg-[#0A1628] transition">✕</a>
                </div>
            </form>
        </div>

        <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] overflow-hidden">
            <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100 dark:border-[#1A3A5C]">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white">{{ __('admin.posts') }} <span
                        class="text-gray-400 font-normal">({{ $posts->total() }})</span></h3>
                @can('posts.create')
                    <a href="{{ route('admin.posts.create') }}"
                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-[#0B6AB2] text-white rounded-lg text-xs font-medium hover:bg-[#13398E] transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        {{ __('admin.new_post') }}
                    </a>
                @endcan
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-[#1A3A5C]">
                        <th class="text-left px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                            {{ __('admin.title') }}</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                            {{ __('admin.categories') }}</th>
                        <th class="text-center px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                            {{ __('admin.status') }}</th>
                        <th class="text-center px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                            {{ __('admin.views') }}</th>
                        <th class="text-center px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                            {{ __('admin.date') }}</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                            {{ __('admin.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-[#1A3A5C]/50">
                    @forelse($posts as $post)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-[#0A1628]/30 transition">
                            <td class="px-5 py-3">
                                <span
                                    class="font-medium text-gray-900 dark:text-white">{{ $post->getTranslation('title') }}</span>
                                @if($post->is_featured) <span class="ml-1 text-[10px]">⭐</span> @endif
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex flex-wrap gap-1">
                                    @foreach($post->categories->take(3) as $cat)
                                        <span
                                            class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-blue-50 dark:bg-blue-900/20 text-blue-600">{{ $cat->getTranslation('name') }}</span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-5 py-3 text-center">
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $post->status === 'published' ? 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600' : 'bg-amber-50 dark:bg-amber-900/20 text-amber-600' }}">{{ ucfirst($post->status) }}</span>
                            </td>
                            <td class="px-5 py-3 text-center text-xs text-gray-500 tabular-nums">
                                {{ number_format($post->view_count) }}</td>
                            <td class="px-5 py-3 text-center text-xs text-gray-500">{{ $post->created_at->format('d.m.Y') }}
                            </td>
                            <td class="px-5 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @can('posts.edit')<a href="{{ route('admin.posts.edit', $post) }}"
                                    class="text-xs text-gray-500 hover:underline">{{ __('admin.edit') }}</a>@endcan
                                    @can('posts.delete')
                                        <form action="{{ route('admin.posts.destroy', $post) }}" method="POST"
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
            @if($posts->hasPages())
                <div class="px-5 py-3 border-t border-gray-100 dark:border-[#1A3A5C]">{{ $posts->links() }}</div>
            @endif
        </div>
    </div>
@endsection