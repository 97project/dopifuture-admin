@extends('admin.layouts.app')
@section('title', isset($language) ? __('admin.edit_language') : __('admin.new_language'))
@section('content')
    <div class="space-y-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
            {{ isset($language) ? __('admin.edit_language') : __('admin.new_language') }}
        </h1>
        <form action="{{ isset($language) ? route('admin.languages.update', $language) : route('admin.languages.store') }}"
            method="POST"
            class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-6 space-y-5">
            @csrf
            @if(isset($language)) @method('PUT') @endif
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="code"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.language_code') }}
                        *</label>
                    <input type="text" id="code" name="code" value="{{ old('code', $language->code ?? '') }}" required
                        maxlength="5"
                        class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628] text-sm focus:ring-[#0B6AB2] focus:border-[#0B6AB2] @error('code') border-red-500 @enderror">
                    @error('code') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="direction"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.direction_label') }}
                        *</label>
                    <select id="direction" name="direction"
                        class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628] text-sm">
                        <option value="ltr" {{ old('direction', $language->direction ?? 'ltr') === 'ltr' ? 'selected' : '' }}>
                            {{ __('admin.ltr') }}
                        </option>
                        <option value="rtl" {{ old('direction', $language->direction ?? '') === 'rtl' ? 'selected' : '' }}>
                            {{ __('admin.rtl') }}
                        </option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="name"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.name') }}
                        *</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $language->name ?? '') }}" required
                        class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628] text-sm focus:ring-[#0B6AB2] focus:border-[#0B6AB2]">
                </div>
                <div>
                    <label for="native_name"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.native_name_label') }}
                        *</label>
                    <input type="text" id="native_name" name="native_name"
                        value="{{ old('native_name', $language->native_name ?? '') }}" required
                        class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628] text-sm focus:ring-[#0B6AB2] focus:border-[#0B6AB2]">
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <div>
                    <label for="sort_order"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.sort_order_label') }}</label>
                    <input type="number" id="sort_order" name="sort_order"
                        value="{{ old('sort_order', $language->sort_order ?? 0) }}"
                        class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628] text-sm">
                </div>
                <div class="flex items-end gap-4">
                    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1"
                            class="rounded border-gray-300 text-[#0B6AB2]" {{ old('is_active', $language->is_active ?? true) ? 'checked' : '' }}> {{ __('admin.active') }}</label>
                    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_default" value="1"
                            class="rounded border-gray-300 text-[#0B6AB2]" {{ old('is_default', $language->is_default ?? false) ? 'checked' : '' }}> {{ __('admin.is_default') }}</label>
                </div>
                <div>
                    <label for="fallback_code"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.fallback_code') }}</label>
                    <input type="text" id="fallback_code" name="fallback_code"
                        value="{{ old('fallback_code', $language->fallback_code ?? '') }}" maxlength="5"
                        class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628] text-sm">
                </div>
            </div>
            <div class="flex items-center gap-3 pt-4 border-t border-gray-100 dark:border-[#1A3A5C]">
                <button type="submit"
                    class="px-6 py-2 bg-[#0B6AB2] hover:bg-[#13398E] text-white text-sm font-medium rounded-lg transition-colors">{{ __('admin.save') }}</button>
                <a href="{{ route('admin.languages.index') }}"
                    class="px-6 py-2 text-sm text-gray-600 hover:text-gray-900 transition-colors">{{ __('admin.cancel') }}</a>
            </div>
        </form>
    </div>
@endsection