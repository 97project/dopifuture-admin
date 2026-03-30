@extends('admin.layouts.auth')

@section('title', __('admin.login'))

@section('content')
    {{-- Mobile logo (visible only on small screens) --}}
    <div class="lg:hidden text-center mb-8 flex flex-col items-center">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-white/5 rounded-2xl backdrop-blur-md border border-white/10 shadow-lg shadow-blue-800/10 mb-4">
            <img src="{{ asset('images/dopifuture-logo-gorsel.png') }}" alt="DopiFuture" class="w-10 h-10 object-contain drop-shadow-md">
        </div>
        <img src="{{ asset('images/dopifuture-logo-yazi.png') }}" alt="DopiFuture" class="h-8 object-contain drop-shadow-md" style="filter: invert(1) brightness(100);">
    </div>

    {{-- Login Card --}}
    <div class="relative">
        {{-- Glow effect behind card — blue tones --}}
        <div
            class="absolute -inset-1 bg-gradient-to-r from-[#0B6AB2]/20 via-[#13398E]/20 to-[#0B6AB2]/20 rounded-3xl blur-xl opacity-60">
        </div>

        <div class="relative bg-[#0E2442]/80 backdrop-blur-xl border border-[#1A3A5C]/80 rounded-2xl p-8 shadow-2xl">
            {{-- Header --}}
            <div class="text-center mb-8">
                <h2 class="text-2xl font-bold text-white tracking-tight">
                    {{ __('admin.login_welcome') }}
                </h2>
                <p class="text-blue-300/60 mt-2 text-sm">
                    {{ __('admin.login_subtitle') }}
                </p>
            </div>

            <form action="{{ route('admin.login.submit') }}" method="POST" class="space-y-5">
                @csrf

                {{-- Email --}}
                <div class="space-y-1.5">
                    <label for="email" class="block text-sm font-medium text-blue-200/80">
                        {{ __('admin.email') }}
                    </label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-blue-400/40 group-focus-within:text-[#0B6AB2] transition-colors"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                            placeholder="admin@dopifuture.com"
                            class="w-full pl-11 pr-4 py-3 rounded-xl bg-[#0A1628]/60 border border-[#1A3A5C]/60 text-white placeholder-blue-400/30 focus:border-[#0B6AB2] focus:ring-0 input-glow outline-none transition-all duration-300 text-sm @error('email') border-red-500/50 @enderror">
                    </div>
                    @error('email')
                        <p class="text-xs text-red-400 flex items-center gap-1 mt-1">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                    clip-rule="evenodd" />
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Password --}}
                <div class="space-y-1.5">
                    <label for="password" class="block text-sm font-medium text-blue-200/80">
                        {{ __('admin.password') }}
                    </label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-blue-400/40 group-focus-within:text-[#0B6AB2] transition-colors"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <input type="password" id="password" name="password" required placeholder="••••••••"
                            class="w-full pl-11 pr-12 py-3 rounded-xl bg-[#0A1628]/60 border border-[#1A3A5C]/60 text-white placeholder-blue-400/30 focus:border-[#0B6AB2] focus:ring-0 input-glow outline-none transition-all duration-300 text-sm @error('password') border-red-500/50 @enderror">
                        <button type="button" onclick="togglePassword()"
                            class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-blue-400/40 hover:text-blue-200 transition-colors">
                            <svg id="eye-open" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg id="eye-closed" class="w-5 h-5 hidden" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="text-xs text-red-400 flex items-center gap-1 mt-1">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                    clip-rule="evenodd" />
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Remember Me --}}
                <div class="flex items-center justify-between">
                    <label for="remember" class="flex items-center gap-2 cursor-pointer group">
                        <input type="checkbox" id="remember" name="remember"
                            class="w-4 h-4 rounded border-[#1A3A5C] bg-[#0A1628] text-[#0B6AB2] focus:ring-[#0B6AB2]/30 focus:ring-offset-0 cursor-pointer">
                        <span class="text-sm text-blue-300/60 group-hover:text-blue-200 transition-colors">
                            {{ __('admin.remember_me') }}
                        </span>
                    </label>
                </div>

                {{-- reCAPTCHA --}}
                @error('recaptcha')
                    <p class="text-xs text-red-400 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                clip-rule="evenodd" />
                        </svg>
                        {{ $message }}
                    </p>
                @enderror

                {{-- Submit Button — Blue gradient matching Doping Hafıza --}}
                <button type="submit"
                    class="relative w-full py-3 px-4 rounded-xl text-white font-semibold text-sm overflow-hidden group transition-all duration-300 hover:shadow-lg hover:shadow-blue-700/25 active:scale-[0.98]">
                    {{-- Button gradient background --}}
                    <div
                        class="absolute inset-0 bg-gradient-to-r from-[#13398E] via-[#0B6AB2] to-[#13398E] bg-[length:200%_100%] group-hover:animate-gradient transition-all duration-300">
                    </div>
                    {{-- Shine effect --}}
                    <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                        <div
                            class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-700">
                        </div>
                    </div>
                    <span class="relative flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                        </svg>
                        {{ __('admin.login') }}
                    </span>
                </button>
            </form>

            {{-- Security badge --}}
            <div class="mt-6 flex items-center justify-center gap-2 text-blue-400/40 text-xs">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                {{ __('admin.login_ssl_badge') }}
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const eyeOpen = document.getElementById('eye-open');
            const eyeClosed = document.getElementById('eye-closed');
            if (input.type === 'password') {
                input.type = 'text';
                eyeOpen.classList.add('hidden');
                eyeClosed.classList.remove('hidden');
            } else {
                input.type = 'password';
                eyeOpen.classList.remove('hidden');
                eyeClosed.classList.add('hidden');
            }
        }
    </script>
@endsection