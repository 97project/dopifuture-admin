<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('admin.login')) - DopiFuture</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'system-ui', '-apple-system', 'sans-serif'] },
                    colors: {
                        doping: {
                            navy: '#13398E',
                            blue: '#0B6AB2',
                            orange: '#F87D17',
                            light: '#E8F4F8',
                            dark: '#0A1628',
                        }
                    }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <style>
        @keyframes gradient-shift {

            0%,
            100% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }
        }

        @keyframes float-up {
            0% {
                transform: translateY(100vh) scale(0);
                opacity: 0;
            }

            10% {
                opacity: 1;
            }

            90% {
                opacity: 0.6;
            }

            100% {
                transform: translateY(-10vh) scale(1);
                opacity: 0;
            }
        }

        @keyframes pulse-ring {
            0% {
                transform: scale(0.8);
                opacity: 0.5;
            }

            50% {
                transform: scale(1.05);
                opacity: 0.3;
            }

            100% {
                transform: scale(0.8);
                opacity: 0.5;
            }
        }

        @keyframes slide-up {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fade-in-slow {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .animate-gradient {
            animation: gradient-shift 8s ease infinite;
            background-size: 200% 200%;
        }

        .animate-slide-up {
            animation: slide-up 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .animate-fade-slow {
            animation: fade-in-slow 1s ease forwards;
        }

        .particle {
            position: absolute;
            border-radius: 50%;
            animation: float-up linear infinite;
            pointer-events: none;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.15);
        }

        .input-glow:focus {
            box-shadow: 0 0 0 3px rgba(11, 106, 178, 0.3), 0 0 20px rgba(11, 106, 178, 0.1);
        }
    </style>
</head>

<body class="min-h-screen flex overflow-hidden">
    {{-- Left Panel: Doping Hafıza Blue Gradient --}}
    <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden">
        {{-- Animated gradient background — navy to blue --}}
        <div class="absolute inset-0 animate-gradient"
            style="background: linear-gradient(135deg, #0A1628 0%, #13398E 30%, #0B6AB2 55%, #13398E 80%, #0A1628 100%);">
        </div>

        {{-- Floating particles --}}
        <div class="absolute inset-0 overflow-hidden">
            <div class="particle bg-sky-400/20"
                style="width:6px;height:6px;left:10%;animation-duration:12s;animation-delay:0s"></div>
            <div class="particle bg-blue-400/20"
                style="width:4px;height:4px;left:20%;animation-duration:15s;animation-delay:2s"></div>
            <div class="particle bg-cyan-400/20"
                style="width:8px;height:8px;left:35%;animation-duration:10s;animation-delay:4s"></div>
            <div class="particle bg-sky-300/20"
                style="width:5px;height:5px;left:50%;animation-duration:13s;animation-delay:1s"></div>
            <div class="particle bg-blue-300/20"
                style="width:7px;height:7px;left:65%;animation-duration:14s;animation-delay:3s"></div>
            <div class="particle bg-cyan-300/20"
                style="width:4px;height:4px;left:80%;animation-duration:11s;animation-delay:5s"></div>
            <div class="particle bg-blue-400/20"
                style="width:6px;height:6px;left:90%;animation-duration:16s;animation-delay:2s"></div>
        </div>

        {{-- Decorative rings --}}
        <div class="absolute top-20 -left-20 w-72 h-72 border border-white/5 rounded-full"
            style="animation: pulse-ring 6s ease-in-out infinite"></div>
        <div class="absolute bottom-20 -right-10 w-96 h-96 border border-white/5 rounded-full"
            style="animation: pulse-ring 8s ease-in-out infinite 2s"></div>
        <div
            class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] border border-white/[0.03] rounded-full">
        </div>

        {{-- Content --}}
        <div class="relative z-10 flex flex-col items-center justify-center w-full px-12 animate-fade-slow">
            {{-- Logo mark --}}
            <div class="mb-8 relative">
                <div
                    class="w-20 h-20 rounded-2xl bg-gradient-to-br from-[#0B6AB2] to-[#13398E] flex items-center justify-center shadow-2xl shadow-blue-800/25 rotate-3 hover:rotate-0 transition-transform duration-500">
                    <span class="text-white text-3xl font-black tracking-tighter">DF</span>
                </div>
                <div
                    class="absolute -inset-1 bg-gradient-to-br from-[#0B6AB2] to-[#13398E] rounded-2xl blur-xl opacity-30">
                </div>
            </div>

            <h1 class="text-4xl font-extrabold text-white tracking-tight text-center">
                Dopi<span
                    class="bg-gradient-to-r from-[#F87D17] to-[#FFB347] bg-clip-text text-transparent">Future</span>
            </h1>
            <p class="text-blue-200/70 mt-3 text-center text-lg max-w-sm leading-relaxed">
                {{ __('admin.auth_description') }}
            </p>

            {{-- Feature pills --}}
            <div class="mt-10 flex flex-wrap gap-3 justify-center">
                <div
                    class="glass-card px-4 py-2 rounded-full text-xs text-blue-100 font-medium flex items-center gap-2">
                    <svg class="w-3.5 h-3.5 text-emerald-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                            clip-rule="evenodd" />
                    </svg>
                    {{ __('admin.login_pill_schools') }}
                </div>
                <div
                    class="glass-card px-4 py-2 rounded-full text-xs text-blue-100 font-medium flex items-center gap-2">
                    <svg class="w-3.5 h-3.5 text-sky-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                            clip-rule="evenodd" />
                    </svg>
                    {{ __('admin.login_pill_licenses') }}
                </div>
                <div
                    class="glass-card px-4 py-2 rounded-full text-xs text-blue-100 font-medium flex items-center gap-2">
                    <svg class="w-3.5 h-3.5 text-[#F87D17]" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                            clip-rule="evenodd" />
                    </svg>
                    {{ __('admin.login_pill_progress') }}
                </div>
                <div
                    class="glass-card px-4 py-2 rounded-full text-xs text-blue-100 font-medium flex items-center gap-2">
                    <svg class="w-3.5 h-3.5 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                            clip-rule="evenodd" />
                    </svg>
                    {{ __('admin.login_pill_apps') }}
                </div>
            </div>

            {{-- Bottom attribution --}}
            <div class="absolute bottom-8 text-blue-300/40 text-xs">
                &copy; {{ date('Y') }} DopiFuture — All rights reserved
            </div>
        </div>
    </div>

    {{-- Right Panel: Login Form --}}
    <div class="w-full lg:w-1/2 flex items-center justify-center bg-[#0A1628] relative">
        {{-- Subtle background pattern --}}
        <div class="absolute inset-0 opacity-[0.02]"
            style="background-image: radial-gradient(circle at 1px 1px, white 1px, transparent 0); background-size: 40px 40px;">
        </div>

        <div class="w-full max-w-md px-8 animate-slide-up relative z-10">
            @yield('content')
        </div>
    </div>
</body>

</html>