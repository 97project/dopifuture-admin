@extends('admin.layouts.app')

@section('title', __('admin.new_user'))

@section('breadcrumb')
<a href="{{ route('admin.dashboard') }}" class="hover:text-gray-700 dark:hover:text-gray-200">{{ __('admin.dashboard') }}</a>
<span class="mx-2">/</span>
<a href="{{ route('admin.users.index') }}" class="hover:text-gray-700 dark:hover:text-gray-200">{{ __('admin.users') }}</a>
<span class="mx-2">/</span>
<span class="text-gray-900 dark:text-gray-100 font-medium">{{ __('admin.new_user') }}</span>
@endsection

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">{{ __('admin.new_user') }}</h1>

    <form action="{{ route('admin.users.store') }}" method="POST" enctype="multipart/form-data"
          class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-6 space-y-5">
        @csrf

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.name') }} *</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required
                       class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628] text-sm focus:ring-[#0B6AB2] focus:border-[#0B6AB2] @error('name') border-red-500 @enderror">
                @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="surname" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.surname') }}</label>
                <input type="text" id="surname" name="surname" value="{{ old('surname') }}"
                       class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628] text-sm focus:ring-[#0B6AB2] focus:border-[#0B6AB2]">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.email') }} *</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required
                       class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628] text-sm focus:ring-[#0B6AB2] focus:border-[#0B6AB2] @error('email') border-red-500 @enderror">
                @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.phone') }}</label>
                <input type="text" id="phone" name="phone" value="{{ old('phone') }}"
                       class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628] text-sm focus:ring-[#0B6AB2] focus:border-[#0B6AB2]">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.password') }} *</label>
                <input type="password" id="password" name="password" required
                       class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628] text-sm focus:ring-[#0B6AB2] focus:border-[#0B6AB2] @error('password') border-red-500 @enderror">
                @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.password_confirmation') }} *</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required
                       class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628] text-sm focus:ring-[#0B6AB2] focus:border-[#0B6AB2]">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            <div>
                <label for="locale" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.locale') }}</label>
                <select id="locale" name="locale" class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628] text-sm">
                    <option value="tr" {{ old('locale') === 'tr' ? 'selected' : '' }}>Türkçe</option>
                    <option value="en" {{ old('locale') === 'en' ? 'selected' : '' }}>English</option>
                </select>
            </div>
            <div>
                <label for="timezone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.timezone') }}</label>
                <input type="text" id="timezone" name="timezone" value="{{ old('timezone', 'Europe/Istanbul') }}"
                       class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628] text-sm focus:ring-[#0B6AB2] focus:border-[#0B6AB2]">
            </div>
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.status') }}</label>
                <select id="status" name="status" class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628] text-sm">
                    <option value="active">{{ __('admin.active') }}</option>
                    <option value="inactive">{{ __('admin.inactive') }}</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.roles') }}</label>
            <div class="flex flex-wrap gap-3">
                @foreach($roles as $role)
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="roles[]" value="{{ $role->id }}" class="rounded border-gray-300 text-[#0B6AB2] focus:ring-[#0B6AB2]"
                           {{ in_array($role->id, old('roles', [])) ? 'checked' : '' }}>
                    {{ $role->name }}
                </label>
                @endforeach
            </div>
        </div>

        <div>
            <label for="avatar" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.avatar') }}</label>
            <input type="file" id="avatar" name="avatar" accept="image/*"
                   class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 dark:file:bg-blue-900/50 dark:file:text-blue-300 hover:file:bg-blue-100">
            @error('avatar') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-center gap-3 pt-4 border-t border-gray-100 dark:border-[#1A3A5C]">
            <button type="submit" class="px-6 py-2 bg-[#0B6AB2] hover:bg-[#13398E] text-white text-sm font-medium rounded-lg transition-colors">
                {{ __('admin.save') }}
            </button>
            <a href="{{ route('admin.users.index') }}" class="px-6 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                {{ __('admin.cancel') }}
            </a>
        </div>
    </form>
</div>
@endsection
