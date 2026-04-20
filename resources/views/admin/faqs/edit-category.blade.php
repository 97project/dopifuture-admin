@extends('admin.layouts.app')
@section('title', __('admin.edit_faq_category'))
@section('content')
    <div class="space-y-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('admin.edit_faq_category') }}</h1>
        <form action="{{ route('admin.faq-categories.update', $category) }}" method="POST"
            class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-6 space-y-4">
            @csrf @method('PUT')
            @foreach(['tr', 'en'] as $locale)
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.name') }}
                        ({{ mb_strtoupper($locale) }}) *</label>
                    <input type="text" name="name[{{ $locale }}]"
                        value="{{ old("name.{$locale}", $category->name[$locale] ?? '') }}" required
                        class="w-full px-3 py-2 border border-gray-200 dark:border-[#1A3A5C] rounded-lg bg-white dark:bg-[#0A1628] text-gray-900 dark:text-white">
                </div>
            @endforeach
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.sort_order') }}</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $category->sort_order) }}"
                        class="w-full px-3 py-2 border border-gray-200 dark:border-[#1A3A5C] rounded-lg bg-white dark:bg-[#0A1628]">
                </div>
                <div class="flex items-end">
                    <label class="flex items-center gap-2 mb-2">
                        <input type="checkbox" name="is_active" value="1" {{ $category->is_active ? 'checked' : '' }}
                            class="rounded border-gray-300 text-[#0B6AB2]">
                        <span class="text-sm">{{ __('admin.active') }}</span>
                    </label>
                </div>
            </div>
            <div class="flex gap-3 pt-4 border-t border-gray-100 dark:border-[#1A3A5C]">
                <button type="submit"
                    class="px-6 py-2.5 bg-[#0B6AB2] hover:bg-[#13398E] text-white font-medium rounded-lg">{{ __('admin.save') }}</button>
                <a href="{{ route('admin.faqs.index') }}"
                    class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-[#0A1628] text-gray-700 dark:text-gray-300 font-medium rounded-lg">{{ __('admin.cancel') }}</a>
            </div>
        </form>
    </div>
@endsection