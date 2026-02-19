@props([
    'title' => '',
    'user' => null,
])

@php $user = $user ?? auth()->user(); @endphp

<header class="flex items-center justify-between h-16 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-4 lg:px-8">
    {{-- Mobile menu toggle --}}
    <button id="mobile-menu-toggle" type="button"
        class="lg:hidden p-2 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>

    {{-- Page title --}}
    <h1 class="text-lg font-semibold text-gray-800 dark:text-gray-200 truncate">
        {{ $title ?: $slot }}
    </h1>

    {{-- Right side --}}
    <div class="flex items-center gap-3">
        {{-- Dark mode toggle --}}
        <button onclick="document.documentElement.classList.toggle('dark')"
            class="p-2 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
            <svg class="w-5 h-5 dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
            </svg>
            <svg class="w-5 h-5 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
        </button>

        {{-- User info --}}
        @if($user)
            <div class="flex items-center gap-2">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300 hidden sm:inline">
                    {{ $user->full_name ?? $user->name }}
                </span>
                <div class="w-8 h-8 rounded-full bg-blue-500 text-white flex items-center justify-center text-sm font-bold">
                    {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                </div>
            </div>
        @endif
    </div>
</header>
