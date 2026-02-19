@extends('admin.layouts.app')

@section('title', __('admin.settings'))

@section('breadcrumb')
    <div class="flex items-center gap-2 text-sm">
        <a href="{{ route('admin.dashboard') }}"
            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">{{ __('admin.dashboard') }}</a>
        <svg class="w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-gray-900 dark:text-gray-100 font-semibold">{{ __('admin.settings') }}</span>
    </div>
@endsection

@section('content')
    <div class="space-y-6">
        <form action="{{ route('admin.settings.update') }}" method="POST">
            @csrf @method('PUT')
            @php $index = 0; @endphp

            @foreach($groups as $groupName => $settings)
                <div
                    class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] overflow-hidden mb-4">
                    <div class="px-5 py-3 border-b border-gray-100 dark:border-[#1A3A5C]">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-gray-900 dark:text-white">
                            {{ __('admin.' . $groupName . '_settings', [], $groupName . ' Settings') }}</h3>
                    </div>
                    <div class="p-5 space-y-4">
                        @foreach($settings as $setting)
                            <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                                <div class="sm:w-1/3">
                                    <label
                                        class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $setting->description ?? $setting->key }}</label>
                                    <input type="hidden" name="settings[{{ $index }}][group]" value="{{ $setting->group }}">
                                    <input type="hidden" name="settings[{{ $index }}][key]" value="{{ $setting->key }}">
                                </div>
                                <div class="sm:w-2/3">
                                    @if($setting->type === 'boolean')
                                        <select name="settings[{{ $index }}][value]"
                                            class="w-full rounded-lg border-gray-200 dark:border-[#1A3A5C] dark:bg-[#0A1628] text-sm focus:ring-[#0B6AB2] focus:border-[#0B6AB2]">
                                            <option value="1" {{ $setting->value == '1' ? 'selected' : '' }}>{{ __('admin.yes') }}
                                            </option>
                                            <option value="0" {{ $setting->value != '1' ? 'selected' : '' }}>{{ __('admin.no') }}
                                            </option>
                                        </select>
                                    @elseif($setting->is_encrypted)
                                        <input type="password" name="settings[{{ $index }}][value]" placeholder="••••••"
                                            class="w-full rounded-lg border-gray-200 dark:border-[#1A3A5C] dark:bg-[#0A1628] text-sm focus:ring-[#0B6AB2] focus:border-[#0B6AB2]">
                                    @else
                                        <input type="text" name="settings[{{ $index }}][value]"
                                            value="{{ $setting->is_encrypted ? '' : $setting->value }}"
                                            class="w-full rounded-lg border-gray-200 dark:border-[#1A3A5C] dark:bg-[#0A1628] text-sm focus:ring-[#0B6AB2] focus:border-[#0B6AB2]">
                                    @endif
                                </div>
                            </div>
                            @php $index++; @endphp
                        @endforeach
                    </div>
                </div>
            @endforeach

            <div class="flex justify-end">
                <button type="submit"
                    class="px-6 py-2.5 bg-[#0B6AB2] hover:bg-[#13398E] text-white text-sm font-medium rounded-lg transition">{{ __('admin.save') }}</button>
            </div>
        </form>
    </div>
@endsection