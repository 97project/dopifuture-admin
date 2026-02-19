@extends('admin.layouts.app')
@section('title', __('admin.new_template'))
@section('content')
    <div class="space-y-6">
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('admin.notification-templates.index') }}" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('admin.new_template') }}</h1>
        </div>

        <form action="{{ route('admin.notification-templates.store') }}" method="POST"
            class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-6 space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.key') }}</label>
                <input type="text" name="key" value="{{ old('key') }}" required
                    class="w-full rounded-lg border-gray-200 dark:border-[#1A3A5C] dark:bg-[#0A1628] dark:text-white text-sm"
                    placeholder="e.g. welcome, new_post">
                @error('key') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            @foreach (config('app.available_locales', ['tr', 'en']) as $locale)
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.title') }} ({{ strtoupper($locale) }})</label>
                    <input type="text" name="title[{{ $locale }}]" value="{{ old("title.$locale") }}" required
                        class="w-full rounded-lg border-gray-200 dark:border-[#1A3A5C] dark:bg-[#0A1628] dark:text-white text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.body') }} ({{ strtoupper($locale) }})</label>
                    <textarea name="body[{{ $locale }}]" rows="3" required
                        class="w-full rounded-lg border-gray-200 dark:border-[#1A3A5C] dark:bg-[#0A1628] dark:text-white text-sm">{{ old("body.$locale") }}</textarea>
                </div>
            @endforeach

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.channels') }}</label>
                <div class="flex gap-4">
                    @foreach (['database', 'fcm', 'mail'] as $channel)
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="channels[]" value="{{ $channel }}" class="rounded border-gray-300"
                                {{ in_array($channel, old('channels', ['database'])) ? 'checked' : '' }}>
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ ucfirst($channel) }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div>
                <label class="inline-flex items-center">
                    <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300"
                        {{ old('is_active', true) ? 'checked' : '' }}>
                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ __('admin.active') }}</span>
                </label>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="px-6 py-2 bg-[#0B6AB2] hover:bg-[#13398E] text-white text-sm rounded-lg">{{ __('admin.save') }}</button>
                <a href="{{ route('admin.notification-templates.index') }}"
                    class="px-6 py-2 bg-gray-100 dark:bg-[#0A1628] hover:bg-gray-200 text-gray-700 dark:text-gray-300 text-sm rounded-lg">{{ __('admin.cancel') }}</a>
            </div>
        </form>
    </div>
@endsection
