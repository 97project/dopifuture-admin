<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'DopiFuture')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'system-ui', '-apple-system', 'sans-serif'] },
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/app.css') }}">
    @stack('styles')
</head>

<body
    class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md mx-auto px-4">
        {{-- Logo --}}
        <div class="text-center mb-8">
            <a href="/" class="text-3xl font-bold text-blue-600 dark:text-blue-400 tracking-tight">
                Dopi<span class="text-red-500">Future</span>
            </a>
        </div>

        {{-- Toast Messages --}}
        @if(session('success'))
            <div
                class="mb-4 p-4 rounded-lg bg-green-50 dark:bg-green-900/50 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-300 text-sm">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div
                class="mb-4 p-4 rounded-lg bg-red-50 dark:bg-red-900/50 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 text-sm">
                {{ session('error') }}
            </div>
        @endif

        {{-- Content Card --}}
        <div
            class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6 sm:p-8">
            @yield('content')
        </div>

        {{-- Footer --}}
        <div class="text-center mt-6 text-xs text-gray-400 dark:text-gray-600">
            &copy; {{ date('Y') }} DopiFuture. {{ __('admin.all_rights_reserved') }}
        </div>
    </div>

    @stack('scripts')
</body>

</html>