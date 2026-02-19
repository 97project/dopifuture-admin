@extends('admin.layouts.app')
@section('title', __('admin.2fa'))
@section('content')
    <div class="max-w-md mx-auto">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 text-center">{{ __('admin.2fa') }}</h1>

        <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-6 space-y-6">
            <p class="text-sm text-gray-600 dark:text-gray-400 text-center">{{ __('admin.scan_qr') }}</p>

            {{-- QR Code --}}
            <div class="flex justify-center">
                <div class="p-4 bg-white rounded-lg">
                    <img src="https://chart.googleapis.com/chart?chs=200x200&cht=qr&chl={{ urlencode($qrUrl) }}&choe=UTF-8"
                        alt="QR Code" width="200" height="200" class="rounded">
                </div>
            </div>

            {{-- Manual Entry Key --}}
            <div class="text-center">
                <p class="text-xs text-gray-500 mb-1">{{ __('admin.manual_entry_key') ?? 'Manuel Giriş Kodu:' }}</p>
                <code
                    class="text-sm font-mono bg-gray-100 dark:bg-[#0A1628] px-3 py-1.5 rounded select-all">{{ $secret }}</code>
            </div>

            {{-- Verify Form --}}
            <form action="{{ route('admin.2fa.enable') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="code"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.enter_code') }}</label>
                    <input type="text" id="code" name="code" required autofocus maxlength="6" autocomplete="one-time-code"
                        class="w-full px-4 py-2.5 rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628] text-center text-lg tracking-widest focus:ring-[#0B6AB2] focus:border-[#0B6AB2] @error('code') border-red-500 @enderror"
                        placeholder="000000">
                    @error('code') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <button type="submit"
                    class="w-full py-2.5 bg-[#0B6AB2] hover:bg-[#13398E] text-white font-medium rounded-lg transition-colors">{{ __('admin.enable') }}</button>
            </form>
        </div>

        <div class="mt-4 text-center">
            <a href="{{ route('admin.profile') }}" class="text-sm text-gray-500 hover:text-gray-700">←
                {{ __('admin.back') }}</a>
        </div>
    </div>
@endsection