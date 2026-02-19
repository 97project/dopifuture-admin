@extends('admin.layouts.app')

@section('title', __('admin.faqs'))

@section('breadcrumb')
    <div class="flex items-center gap-2 text-sm">
        <a href="{{ route('admin.dashboard') }}"
            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">{{ __('admin.dashboard') }}</a>
        <svg class="w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-gray-900 dark:text-gray-100 font-semibold">{{ __('admin.faqs') }}</span>
    </div>
@endsection

@section('content')
    <div class="space-y-6">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div
                class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-4 text-center">
                <p class="text-2xl font-extrabold text-[#13398E]">{{ $stats['total_categories'] }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ __('admin.categories') }}</p>
            </div>
            <div
                class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-4 text-center">
                <p class="text-2xl font-extrabold text-[#0B6AB2]">{{ $stats['total_faqs'] }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ __('admin.total_faqs') }}</p>
            </div>
            <div
                class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-4 text-center">
                <p class="text-2xl font-extrabold text-emerald-500">{{ $stats['active_faqs'] }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ __('admin.active') }}</p>
            </div>
        </div>

        <div class="flex items-center justify-end gap-2">
            @can('faqs.create')
                <a href="{{ route('admin.faqs.create-category') }}"
                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 border border-gray-200 dark:border-[#1A3A5C] rounded-lg text-xs font-medium hover:bg-gray-50 dark:hover:bg-[#0A1628] transition">
                    📁 {{ __('admin.new_category') }}
                </a>
                <a href="{{ route('admin.faqs.create') }}"
                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-[#0B6AB2] text-white rounded-lg text-xs font-medium hover:bg-[#13398E] transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    {{ __('admin.new_faq') }}
                </a>
            @endcan
        </div>

        @forelse($categories as $category)
            <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] overflow-hidden">
                <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100 dark:border-[#1A3A5C]">
                    <div class="flex items-center gap-3">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white">{{ $category->getTranslation('name') }}</h3>
                        <span
                            class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 dark:bg-blue-900/20 text-blue-600">{{ $category->faqs->count() }}
                            FAQ</span>
                        @if(!$category->is_active)
                            <span
                                class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-50 dark:bg-gray-900/20 text-gray-500">{{ __('admin.inactive') }}</span>
                        @endif
                    </div>
                    <div class="flex items-center gap-2">
                        @can('faqs.create')<a href="{{ route('admin.faqs.create', ['category_id' => $category->id]) }}"
                        class="text-xs text-[#0B6AB2] hover:underline">+ {{ __('admin.add') }}</a>@endcan
                        @can('faqs.edit')<a href="{{ route('admin.faqs.edit-category', $category) }}"
                        class="text-xs text-gray-500 hover:underline">{{ __('admin.edit') }}</a>@endcan
                    </div>
                </div>
                @if($category->faqs->count())
                    <div class="divide-y divide-gray-50 dark:divide-[#1A3A5C]/50">
                        @foreach($category->faqs as $faq)
                            <div
                                class="px-5 py-3 flex items-center justify-between hover:bg-gray-50/50 dark:hover:bg-[#0A1628]/30 transition">
                                <div class="flex items-center gap-3">
                                    @if(!$faq->is_active)<span class="w-2 h-2 rounded-full bg-gray-300"></span>@else<span
                                    class="w-2 h-2 rounded-full bg-emerald-500"></span>@endif
                                    <span class="text-sm text-gray-900 dark:text-white">{{ $faq->getTranslation('question') }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    @can('faqs.edit')<a href="{{ route('admin.faqs.edit', $faq) }}"
                                    class="text-xs text-gray-500 hover:underline">{{ __('admin.edit') }}</a>@endcan
                                    @can('faqs.delete')
                                        <form action="{{ route('admin.faqs.destroy', $faq) }}" method="POST"
                                            onsubmit="return confirm('{{ __('admin.confirm') }}?')">@csrf @method('DELETE')
                                            <button class="text-xs text-red-500 hover:underline">{{ __('admin.delete') }}</button>
                                        </form>
                                    @endcan
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="px-5 py-6 text-center text-gray-400 text-sm">{{ __('admin.no_data') }}</p>
                @endif
            </div>
        @empty
            <div
                class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-8 text-center text-gray-400">
                {{ __('admin.no_data') }}</div>
        @endforelse
    </div>
@endsection