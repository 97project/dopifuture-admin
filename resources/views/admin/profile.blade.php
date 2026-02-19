@extends('admin.layouts.app')
@section('title', __('admin.profile'))
@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('admin.profile') }}</h1>

    {{-- Profile Info --}}
    <form action="{{ route('admin.profile.update') }}" method="POST" enctype="multipart/form-data"
          class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-6 space-y-5">
        @csrf @method('PUT')
        <div class="flex items-center gap-4 mb-4">
            @if(auth()->user()->avatar_url)
                <img src="{{ auth()->user()->avatar_url }}" class="w-16 h-16 rounded-full object-cover" alt="">
            @else
                <div class="w-16 h-16 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center">
                    <span class="text-[#0B6AB2] dark:text-[#0B6AB2] text-xl font-bold">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                </div>
            @endif
            <input type="file" name="avatar" accept="image/*" class="text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.name') }}</label>
                <input type="text" id="name" name="name" value="{{ auth()->user()->name }}" required
                       class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628] text-sm focus:ring-[#0B6AB2] focus:border-[#0B6AB2]">
            </div>
            <div>
                <label for="surname" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.surname') }}</label>
                <input type="text" id="surname" name="surname" value="{{ auth()->user()->surname }}"
                       class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628] text-sm focus:ring-[#0B6AB2] focus:border-[#0B6AB2]">
            </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.email') }}</label>
                <input type="email" id="email" name="email" value="{{ auth()->user()->email }}" required
                       class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628] text-sm focus:ring-[#0B6AB2] focus:border-[#0B6AB2] @error('email') border-red-500 @enderror">
                @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.phone') }}</label>
                <input type="text" id="phone" name="phone" value="{{ auth()->user()->phone }}"
                       class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628] text-sm focus:ring-[#0B6AB2] focus:border-[#0B6AB2]">
            </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <label for="locale" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.locale') }}</label>
                <select id="locale" name="locale" class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628] text-sm">
                    <option value="tr" {{ auth()->user()->locale === 'tr' ? 'selected' : '' }}>Türkçe</option>
                    <option value="en" {{ auth()->user()->locale === 'en' ? 'selected' : '' }}>English</option>
                </select>
            </div>
            <div>
                <label for="timezone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.timezone') }}</label>
                <input type="text" id="timezone" name="timezone" value="{{ auth()->user()->timezone }}"
                       class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628] text-sm focus:ring-[#0B6AB2] focus:border-[#0B6AB2]">
            </div>
        </div>
        <button type="submit" class="px-6 py-2 bg-[#0B6AB2] hover:bg-[#13398E] text-white text-sm font-medium rounded-lg transition-colors">{{ __('admin.save') }}</button>
    </form>

    {{-- Change Password --}}
    <form action="{{ route('admin.profile.password') }}" method="POST"
          class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-6 space-y-5">
        @csrf @method('PUT')
        <h2 class="text-lg font-semibold">{{ __('admin.change_password') }}</h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            <div>
                <label for="current_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.current_password') }}</label>
                <input type="password" id="current_password" name="current_password" required
                       class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628] text-sm focus:ring-[#0B6AB2] focus:border-[#0B6AB2] @error('current_password') border-red-500 @enderror">
                @error('current_password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.new_password') }}</label>
                <input type="password" id="password" name="password" required
                       class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628] text-sm focus:ring-[#0B6AB2] focus:border-[#0B6AB2] @error('password') border-red-500 @enderror">
                @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.password_confirmation') }}</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required
                       class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628] text-sm focus:ring-[#0B6AB2] focus:border-[#0B6AB2]">
            </div>
        </div>
        <button type="submit" class="px-6 py-2 bg-[#0B6AB2] hover:bg-[#13398E] text-white text-sm font-medium rounded-lg transition-colors">{{ __('admin.save') }}</button>
    </form>

    {{-- 2FA Section --}}
    <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-6">
        <h2 class="text-lg font-semibold mb-4">{{ __('admin.2fa') }}</h2>
        @if(auth()->user()->hasTwoFactorEnabled())
            <div class="flex items-center gap-3 p-4 bg-green-50 dark:bg-green-900/50 rounded-lg mb-4">
                <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                <span class="text-green-700 dark:text-green-300 font-medium">{{ __('admin.active') }}</span>
            </div>
            <div class="flex gap-3">
                <form action="{{ route('admin.2fa.disable') }}" method="POST">@csrf
                    <button class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm rounded-lg">{{ __('admin.disable') }}</button>
                </form>
                <form action="{{ route('admin.2fa.regenerate-recovery') }}" method="POST">@csrf
                    <button class="px-4 py-2 bg-gray-200 dark:bg-[#0A1628] hover:bg-gray-300 text-sm rounded-lg">{{ __('admin.regenerate_codes') }}</button>
                </form>
            </div>
            @if(isset($recoveryCodes))
            <div class="mt-4 p-4 bg-yellow-50 dark:bg-yellow-900/50 rounded-lg">
                <p class="text-sm text-yellow-700 dark:text-yellow-300 font-medium mb-2">{{ __('admin.recovery_codes') }}</p>
                <div class="grid grid-cols-2 gap-1">
                    @foreach($recoveryCodes as $code)
                    <code class="text-xs bg-white dark:bg-[#0E2442]/50 px-2 py-1 rounded">{{ $code }}</code>
                    @endforeach
                </div>
            </div>
            @endif
        @else
            <p class="text-gray-500 mb-4">{{ __('admin.2fa_description') ?? '2FA güvenlik katmanı etkinleştirilmemiş.' }}</p>
            <a href="{{ route('admin.2fa.setup') }}" class="inline-flex px-4 py-2 bg-[#0B6AB2] hover:bg-[#13398E] text-white text-sm rounded-lg">{{ __('admin.enable') }}</a>
        @endif
    </div>
</div>
@endsection
