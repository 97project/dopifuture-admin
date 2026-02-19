@extends('admin.layouts.app')
@section('title', isset($school) ? __('admin.edit_school') : __('admin.new_school'))
@section('content')
        <div class="space-y-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
                        {{ isset($school) ? __('admin.edit_school') : __('admin.new_school') }}
                </h1>
                @php $nameData = $nameData ?? ['tr' => '', 'en' => '']; @endphp
                <form action="{{ isset($school) ? route('admin.schools.update', $school) : route('admin.schools.store') }}"
                        method="POST"
                        class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-6 space-y-5">
                        @csrf @if(isset($school)) @method('PUT') @endif
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                <div><label class="block text-sm font-medium mb-1">{{ __('admin.name') }} (TR) *</label><input
                                                type="text" name="name_tr" value="{{ old('name_tr', $nameData['tr'] ?? '') }}"
                                                required
                                                class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628] text-sm">
                                </div>
                                <div><label class="block text-sm font-medium mb-1">{{ __('admin.name') }} (EN) *</label><input
                                                type="text" name="name_en" value="{{ old('name_en', $nameData['en'] ?? '') }}"
                                                required
                                                class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628] text-sm">
                                </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                <div><label class="block text-sm font-medium mb-1">{{ __('admin.country') }}</label><input
                                                type="text" name="country" value="{{ old('country', $school->country ?? '') }}"
                                                class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628] text-sm">
                                </div>
                                <div><label class="block text-sm font-medium mb-1">{{ __('admin.city') }}</label><input
                                                type="text" name="city" value="{{ old('city', $school->city ?? '') }}"
                                                class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628] text-sm">
                                </div>
                        </div>
                        <div><label class="block text-sm font-medium mb-1">{{ __('admin.address') }}</label><textarea
                                        name="address" rows="2"
                                        class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628] text-sm">{{ old('address', $school->address ?? '') }}</textarea>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                                <div><label class="block text-sm font-medium mb-1">{{ __('admin.phone') }}</label><input
                                                type="text" name="phone" value="{{ old('phone', $school->phone ?? '') }}"
                                                class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628] text-sm">
                                </div>
                                <div><label class="block text-sm font-medium mb-1">{{ __('admin.email') }}</label><input
                                                type="email" name="email" value="{{ old('email', $school->email ?? '') }}"
                                                class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628] text-sm">
                                </div>
                                <div><label class="block text-sm font-medium mb-1">{{ __('admin.website') }}</label><input
                                                type="url" name="website" value="{{ old('website', $school->website ?? '') }}"
                                                class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628] text-sm">
                                </div>
                        </div>
                        <div><label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1"
                                                class="rounded border-gray-300 text-[#0B6AB2]" {{ old('is_active', $school->is_active ?? true) ? 'checked' : '' }}>
                                        {{ __('admin.active') }}</label></div>
                        <div class="flex items-center gap-3 pt-4 border-t"><button type="submit"
                                        class="px-6 py-2 bg-[#0B6AB2] hover:bg-[#13398E] text-white text-sm font-medium rounded-lg">{{ __('admin.save') }}</button><a
                                        href="{{ route('admin.schools.index') }}"
                                        class="px-6 py-2 text-sm text-gray-600 hover:text-gray-900">{{ __('admin.cancel') }}</a>
                        </div>
                </form>
        </div>
@endsection