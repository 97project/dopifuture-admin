@extends('admin.layouts.app')
@section('title', __('admin.new_category'))
@section('content')
    <div class="space-y-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('admin.new_category') }}</h1>

        <form action="{{ route('admin.categories.store') }}" method="POST"
            class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-6 space-y-6">
            @csrf
            <input type="hidden" name="type" value="{{ $type }}">

            @foreach(['tr', 'en'] as $locale)
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.name') }}
                        ({{ strtoupper($locale) }}) *</label>
                    <input type="text" name="name[{{ $locale }}]" value="{{ old("name.{$locale}") }}" required
                        class="w-full px-3 py-2 border border-gray-200 dark:border-[#1A3A5C] rounded-lg bg-white dark:bg-[#0A1628] text-gray-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.description') }}
                        ({{ strtoupper($locale) }})</label>
                    <textarea name="description[{{ $locale }}]" rows="2"
                        class="w-full px-3 py-2 border border-gray-200 dark:border-[#1A3A5C] rounded-lg bg-white dark:bg-[#0A1628]">{{ old("description.{$locale}") }}</textarea>
                </div>
            @endforeach

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Slug</label>
                    <input type="text" name="slug" value="{{ old('slug') }}"
                        class="w-full px-3 py-2 border border-gray-200 dark:border-[#1A3A5C] rounded-lg bg-white dark:bg-[#0A1628]"
                        placeholder="auto">
                </div>
                <div>
                    <label
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.parent') }}</label>
                    <select name="parent_id"
                        class="w-full px-3 py-2 border border-gray-200 dark:border-[#1A3A5C] rounded-lg bg-white dark:bg-[#0A1628]">
                        <option value="">—</option>
                        @foreach($parents as $parent)
                            <option value="{{ $parent->id }}">{{ $parent->getTranslation('name') }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.sort_order') }}</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}"
                        class="w-full px-3 py-2 border border-gray-200 dark:border-[#1A3A5C] rounded-lg bg-white dark:bg-[#0A1628]">
                </div>
            </div>

            <label class="flex items-center gap-2">
                <input type="checkbox" name="is_active" value="1" checked class="rounded border-gray-300 text-[#0B6AB2]">
                <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('admin.active') }}</span>
            </label>

            <div class="flex gap-3 pt-4 border-t border-gray-100 dark:border-[#1A3A5C]">
                <button type="submit"
                    class="px-6 py-2.5 bg-[#0B6AB2] hover:bg-[#13398E] text-white font-medium rounded-lg">{{ __('admin.save') }}</button>
                <a href="{{ route('admin.categories.index', ['type' => $type]) }}"
                    class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-[#0A1628] text-gray-700 dark:text-gray-300 font-medium rounded-lg">{{ __('admin.cancel') }}</a>
            </div>
        </form>
    </div>
@endsection