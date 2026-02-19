@extends('admin.layouts.app')
@section('title', isset($application) ? __('admin.edit_application') : __('admin.new_application'))
@section('content')
    <div class="space-y-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
            {{ isset($application) ? __('admin.edit_application') : __('admin.new_application') }}
        </h1>

        @php
            $nameData = $nameData ?? ['tr' => '', 'en' => ''];
            $descData = $descData ?? ['tr' => '', 'en' => ''];
        @endphp

        <form
            action="{{ isset($application) ? route('admin.applications.update', $application) : route('admin.applications.store') }}"
            method="POST"
            class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-6 space-y-5">
            @csrf
            @if(isset($application)) @method('PUT') @endif

            {{-- Slug --}}
            <div>
                <label for="slug"
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.app_slug') }}
                    *</label>
                <input type="text" id="slug" name="slug" value="{{ old('slug', $application->slug ?? '') }}" required
                    maxlength="100" pattern="[a-z0-9\-]+"
                    class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628] text-sm focus:ring-[#0B6AB2] focus:border-[#0B6AB2] @error('slug') border-red-500 @enderror">
                @error('slug') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Name TR/EN --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="name_tr"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.name') }} (TR)
                        *</label>
                    <input type="text" id="name_tr" name="name_tr" value="{{ old('name_tr', $nameData['tr'] ?? '') }}"
                        required
                        class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628] text-sm focus:ring-[#0B6AB2] focus:border-[#0B6AB2]">
                </div>
                <div>
                    <label for="name_en"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.name') }} (EN)
                        *</label>
                    <input type="text" id="name_en" name="name_en" value="{{ old('name_en', $nameData['en'] ?? '') }}"
                        required
                        class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628] text-sm focus:ring-[#0B6AB2] focus:border-[#0B6AB2]">
                </div>
            </div>

            {{-- Description TR/EN --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="description_tr"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.description') }}
                        (TR)</label>
                    <textarea id="description_tr" name="description_tr" rows="3"
                        class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628] text-sm focus:ring-[#0B6AB2] focus:border-[#0B6AB2]">{{ old('description_tr', $descData['tr'] ?? '') }}</textarea>
                </div>
                <div>
                    <label for="description_en"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.description') }}
                        (EN)</label>
                    <textarea id="description_en" name="description_en" rows="3"
                        class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628] text-sm focus:ring-[#0B6AB2] focus:border-[#0B6AB2]">{{ old('description_en', $descData['en'] ?? '') }}</textarea>
                </div>
            </div>

            {{-- Icon, Color, Connector --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <div>
                    <label for="icon"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.app_icon') }}</label>
                    <input type="text" id="icon" name="icon" value="{{ old('icon', $application->icon ?? '') }}"
                        placeholder="rocket, star..."
                        class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628] text-sm">
                </div>
                <div>
                    <label for="color"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.app_color') }}</label>
                    <input type="color" id="color" name="color" value="{{ old('color', $application->color ?? '#3B82F6') }}"
                        class="w-full h-[38px] px-1 py-1 rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628]">
                </div>
                <div>
                    <label for="sort_order"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.sort_order_label') }}</label>
                    <input type="number" id="sort_order" name="sort_order"
                        value="{{ old('sort_order', $application->sort_order ?? 0) }}"
                        class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628] text-sm">
                </div>
            </div>

            {{-- Connector Class --}}
            <div>
                <label for="connector_class"
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.app_connector') }}</label>
                <input type="text" id="connector_class" name="connector_class"
                    value="{{ old('connector_class', $application->connector_class ?? '') }}"
                    placeholder="App\Connectors\MissionWayConnector"
                    class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628] text-sm font-mono">
            </div>

            {{-- Active --}}
            <div>
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-[#0B6AB2]" {{ old('is_active', $application->is_active ?? true) ? 'checked' : '' }}>
                    {{ __('admin.active') }}
                </label>
            </div>

            <div class="flex items-center gap-3 pt-4 border-t border-gray-100 dark:border-[#1A3A5C]">
                <button type="submit"
                    class="px-6 py-2 bg-[#0B6AB2] hover:bg-[#13398E] text-white text-sm font-medium rounded-lg transition-colors">{{ __('admin.save') }}</button>
                <a href="{{ route('admin.applications.index') }}"
                    class="px-6 py-2 text-sm text-gray-600 hover:text-gray-900 transition-colors">{{ __('admin.cancel') }}</a>
            </div>
        </form>
    </div>
@endsection