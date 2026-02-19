@extends('admin.layouts.app')
@section('title', __('admin.edit_translation'))
@section('content')
    <div class="space-y-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">{{ __('admin.edit_translation') }}</h1>
        <form action="{{ route('admin.translations.update', $translation) }}" method="POST"
            class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-6 space-y-5">
            @csrf @method('PUT')
            <div class="p-3 bg-gray-50 dark:bg-[#0A1628] rounded-lg">
                <span class="text-sm text-gray-500">{{ $translation->group }}.{{ $translation->key }}</span>
            </div>
            @foreach($languages as $lang)
                <div>
                    <label for="value_{{ $lang->code }}"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ $lang->native_name }} ({{ $lang->code }})
                    </label>
                    <textarea id="value_{{ $lang->code }}" name="values[{{ $lang->code }}]" rows="2"
                        class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628] text-sm focus:ring-[#0B6AB2] focus:border-[#0B6AB2]">{{ old("values.{$lang->code}", $allTranslations[$lang->code]->value ?? '') }}</textarea>
                </div>
            @endforeach
            <div class="flex items-center gap-3 pt-4 border-t border-gray-100 dark:border-[#1A3A5C]">
                <button type="submit"
                    class="px-6 py-2 bg-[#0B6AB2] hover:bg-[#13398E] text-white text-sm font-medium rounded-lg transition-colors">{{ __('admin.save') }}</button>
                <a href="{{ route('admin.translations.index') }}"
                    class="px-6 py-2 text-sm text-gray-600 hover:text-gray-900 transition-colors">{{ __('admin.cancel') }}</a>
            </div>
        </form>
    </div>
@endsection