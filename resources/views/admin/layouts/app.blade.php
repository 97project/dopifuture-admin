<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="{{ auth()->user()?->dark_mode ? 'dark' : '' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'DopiFuture') - {{ __('admin.dashboard') }}</title>
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/app.css') }}">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @stack('styles')
</head>

<body class="bg-gray-50 dark:bg-gray-950 text-gray-900 dark:text-gray-100">
    <div class="flex h-screen overflow-hidden" id="app">

        {{-- ═══════════════════════════════════════
        Sidebar
        ═══════════════════════════════════════ --}}
        <aside id="sidebar" class="sidebar hidden lg:flex flex-col w-[260px] min-w-[260px] transition-all duration-300">

            {{-- Logo --}}
            <div class="flex items-center gap-2 h-16 px-5 border-b border-white/5 bg-white/5 backdrop-blur-sm">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 w-full">
                    <img src="{{ asset('images/dopifuture-logo-gorsel.png') }}" alt="DopiFuture" class="w-8 h-8 object-contain drop-shadow-md">
                    <img src="{{ asset('images/dopifuture-logo-yazi.png') }}" alt="DopiFuture" class="h-6 object-contain drop-shadow-sm">
                </a>
            </div>

            {{-- Navigation --}}
            <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-0.5">

                {{-- Dashboard --}}
                <a href="{{ route('admin.dashboard') }}"
                    class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    {{ __('admin.menu_dashboard') }}
                </a>

                {{-- Users --}}
                @can('users.view')
                    <a href="{{ route('admin.users.index') }}"
                        class="sidebar-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        {{ __('admin.menu_users') }}
                    </a>
                @endcan

                {{-- Roles --}}
                @can('roles.view')
                    <a href="{{ route('admin.roles.index') }}"
                        class="sidebar-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                        <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        {{ __('admin.menu_roles') }}
                    </a>
                @endcan

                {{-- Permissions --}}
                @can('roles.view')
                    <a href="{{ route('admin.permissions.index') }}"
                        class="sidebar-link {{ request()->routeIs('admin.permissions.*') ? 'active' : '' }}">
                        <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                        </svg>
                        {{ __('admin.permissions') }}
                    </a>
                @endcan

                {{-- Languages --}}
                @can('languages.view')
                    <a href="{{ route('admin.languages.index') }}"
                        class="sidebar-link {{ request()->routeIs('admin.languages.*') ? 'active' : '' }}">
                        <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" />
                        </svg>
                        {{ __('admin.menu_languages') }}
                    </a>
                @endcan

                {{-- Translations --}}
                @can('translations.view')
                    <a href="{{ route('admin.translations.index') }}"
                        class="sidebar-link {{ request()->routeIs('admin.translations.*') ? 'active' : '' }}">
                        <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                        </svg>
                        {{ __('admin.menu_translations') }}
                    </a>
                @endcan

                {{-- Settings --}}
                @can('settings.view')
                    <a href="{{ route('admin.settings.index') }}"
                        class="sidebar-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                        <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        {{ __('admin.menu_settings') }}
                    </a>
                @endcan

                {{-- Activity Logs --}}
                @can('activity_logs.view')
                    <a href="{{ route('admin.activity-logs.index') }}"
                        class="sidebar-link {{ request()->routeIs('admin.activity-logs.*') ? 'active' : '' }}">
                        <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                        {{ __('admin.menu_activity_logs') }}
                    </a>
                @endcan

                {{-- CMS Section --}}
                @canany(['pages.view', 'posts.view', 'categories.view', 'menus.view'])
                    <div class="sidebar-section">{{ __('admin.menu_cms') }}</div>

                    @can('pages.view')
                        <a href="{{ route('admin.pages.index') }}"
                            class="sidebar-link {{ request()->routeIs('admin.pages.*') ? 'active' : '' }}">
                            <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                            </svg>
                            {{ __('admin.menu_pages') }}
                        </a>
                    @endcan

                    @can('posts.view')
                        <a href="{{ route('admin.posts.index') }}"
                            class="sidebar-link {{ request()->routeIs('admin.posts.*') ? 'active' : '' }}">
                            <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            {{ __('admin.menu_posts') }}
                        </a>
                    @endcan

                    @can('categories.view')
                        <a href="{{ route('admin.categories.index') }}"
                            class="sidebar-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                            <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                            </svg>
                            {{ __('admin.menu_categories') }}
                        </a>
                    @endcan

                    @can('menus.view')
                        <a href="{{ route('admin.menus.index') }}"
                            class="sidebar-link {{ request()->routeIs('admin.menus.*') ? 'active' : '' }}">
                            <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                            {{ __('admin.menu_menus') }}
                        </a>
                    @endcan
                @endcanany

                {{-- DopiFuture Section --}}
                @canany(['applications.view', 'schools.view', 'classes.view', 'licenses.view', 'registration_requests.view'])
                    <div class="sidebar-section">DopiFuture</div>

                    @can('applications.view')
                        <a href="{{ route('admin.applications.index') }}"
                            class="sidebar-link {{ request()->routeIs('admin.applications.*') ? 'active' : '' }}">
                            <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                            {{ __('admin.applications') }}
                        </a>
                    @endcan

                    @can('schools.view')
                        <a href="{{ route('admin.schools.index') }}"
                            class="sidebar-link {{ request()->routeIs('admin.schools.*') ? 'active' : '' }}">
                            <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            {{ __('admin.schools') }}
                        </a>
                    @endcan

                    @can('classes.view')
                        <a href="{{ route('admin.classes.index') }}"
                            class="sidebar-link {{ request()->routeIs('admin.classes.*') ? 'active' : '' }}">
                            <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
                            </svg>
                            {{ __('admin.classes') }}
                        </a>
                    @endcan

                    @can('licenses.view')
                        <a href="{{ route('admin.licenses.index') }}"
                            class="sidebar-link {{ request()->routeIs('admin.licenses.*') ? 'active' : '' }}">
                            <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                            </svg>
                            {{ __('admin.licenses') }}
                        </a>
                    @endcan

                    @can('registration_requests.view')
                        <a href="{{ route('admin.registration-requests.index') }}"
                            class="sidebar-link {{ request()->routeIs('admin.registration-requests.*') ? 'active' : '' }}">
                            <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                            </svg>
                            {{ __('admin.registration_requests') }}
                        </a>
                    @endcan

                    {{-- Seat Requests --}}
                    <a href="{{ route('admin.seat-requests.index') }}"
                        class="sidebar-link {{ request()->routeIs('admin.seat-requests.*') ? 'active' : '' }}">
                        <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                        </svg>
                        Seat Requests
                        @php $pendingCount = \App\Models\SeatRequest::where('status', 'pending')->count(); @endphp
                        @if($pendingCount > 0)
                            <span class="ml-auto inline-flex items-center justify-center w-5 h-5 text-[10px] font-bold rounded-full bg-amber-500 text-white">{{ $pendingCount }}</span>
                        @endif
                    </a>

                    {{-- Reports --}}
                    <a href="{{ route('admin.reports.index') }}"
                        class="sidebar-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                        <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        {{ __('admin.reports') }}
                    </a>
                @endcanany

                {{-- Tools Section --}}
                <div class="sidebar-section">{{ __('admin.menu_tools') }}</div>

                {{-- Media --}}
                @can('media.view')
                    <a href="{{ route('admin.media.index') }}"
                        class="sidebar-link {{ request()->routeIs('admin.media.*') ? 'active' : '' }}">
                        <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                        </svg>
                        {{ __('admin.menu_file_manager') }}
                    </a>
                @endcan

                {{-- FAQs --}}
                @can('faqs.view')
                    <a href="{{ route('admin.faqs.index') }}"
                        class="sidebar-link {{ request()->routeIs('admin.faqs.*', 'admin.faq-categories.*') ? 'active' : '' }}">
                        <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        {{ __('admin.menu_faqs') }}
                    </a>
                @endcan

                {{-- Forms --}}
                @can('forms.view')
                    <a href="{{ route('admin.forms.index') }}"
                        class="sidebar-link {{ request()->routeIs('admin.forms.*') ? 'active' : '' }}">
                        <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        {{ __('admin.menu_forms') }}
                    </a>
                @endcan

                {{-- Notifications --}}
                @can('notifications.view')
                    <a href="{{ route('admin.notifications.index') }}"
                        class="sidebar-link {{ request()->routeIs('admin.notifications.*') || request()->routeIs('admin.notification-templates.*') ? 'active' : '' }}">
                        <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        {{ __('admin.menu_notifications') }}
                    </a>
                @endcan

                {{-- Backups --}}
                @can('backups.view')
                    <a href="{{ route('admin.backups.index') }}"
                        class="sidebar-link {{ request()->routeIs('admin.backups.*') ? 'active' : '' }}">
                        <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                        </svg>
                        {{ __('admin.menu_backups') }}
                    </a>
                @endcan

                {{-- Account Deletions --}}
                @can('users.delete')
                    <a href="{{ route('admin.account-deletions.index') }}"
                        class="sidebar-link {{ request()->routeIs('admin.account-deletions.*') ? 'active' : '' }}">
                        <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        {{ __('admin.menu_account_deletions') }}
                    </a>
                @endcan
            </nav>

            {{-- User Profile Section --}}
            <div class="border-t border-white/5 p-3">
                <a href="{{ route('admin.profile') }}"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-white/5 transition-colors group">
                    @if(auth()->user()?->avatar_url)
                        <img src="{{ auth()->user()->avatar_url }}"
                            class="w-8 h-8 rounded-full object-cover ring-2 ring-white/10" alt="">
                    @else
                        <div
                            class="w-8 h-8 rounded-full bg-gradient-to-br from-[#0B6AB2] to-[#13398E] flex items-center justify-center flex-shrink-0 shadow-sm">
                            <span
                                class="text-white text-xs font-bold">{{ strtoupper(substr(auth()->user()?->name ?? 'A', 0, 1)) }}</span>
                        </div>
                    @endif
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-200 truncate group-hover:text-white transition-colors">
                            {{ auth()->user()?->full_name }}
                        </p>
                        <p class="text-[11px] text-gray-500 truncate">{{ auth()->user()?->email }}</p>
                    </div>
                    <svg class="w-4 h-4 text-gray-500 group-hover:text-gray-300 transition-colors" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>
        </aside>

        {{-- ═══════════════════════════════════════
        Main Content Area
        ═══════════════════════════════════════ --}}
        <div class="flex-1 flex flex-col overflow-hidden">

            {{-- Top Bar --}}
            <header class="topbar h-14 flex items-center justify-between px-4 lg:px-6 z-10">
                {{-- Left: Mobile toggle + Breadcrumb --}}
                <div class="flex items-center gap-3">
                    <button id="sidebarToggle"
                        class="lg:hidden p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <div class="hidden lg:flex items-center text-sm text-gray-500 dark:text-gray-400 font-medium">
                        @yield('breadcrumb')
                    </div>
                </div>

                {{-- Right: Actions --}}
                <div class="flex items-center gap-2">
                    {{-- Language Switcher --}}
                    @php
                        $activeLanguages = \App\Models\Language::active()->ordered()->get();
                        $currentLocale = app()->getLocale();
                    @endphp
                    @if($activeLanguages->count() > 2)
                        {{-- Dropdown for 3+ languages --}}
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" type="button"
                                class="px-2.5 py-1.5 text-[11px] font-bold rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800 transition-all text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 uppercase tracking-wider"
                                title="{{ __('admin.switch_language') }}">
                                {{ strtoupper($currentLocale) }}
                            </button>
                            <div x-show="open" @click.away="open = false" x-transition
                                class="absolute right-0 mt-1 w-40 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg z-50 py-1">
                                @foreach($activeLanguages as $lang)
                                    @if($lang->code !== $currentLocale)
                                        <form action="{{ route('admin.switch-locale') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="locale" value="{{ $lang->code }}">
                                            <button type="submit"
                                                class="w-full px-3 py-2 text-left text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                                {{ $lang->native_name }} ({{ strtoupper($lang->code) }})
                                            </button>
                                        </form>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @elseif($activeLanguages->count() === 2)
                        {{-- Simple toggle for 2 languages --}}
                        @php $otherLang = $activeLanguages->firstWhere('code', '!=', $currentLocale); @endphp
                        <form action="{{ route('admin.switch-locale') }}" method="POST" class="inline">
                            @csrf
                            <input type="hidden" name="locale" value="{{ $otherLang->code }}">
                            <button type="submit"
                                class="px-2.5 py-1.5 text-[11px] font-bold rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800 transition-all text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 uppercase tracking-wider"
                                title="{{ __('admin.switch_language') }}">
                                {{ strtoupper($otherLang->code) }}
                            </button>
                        </form>
                    @endif

                    {{-- Dark Mode Toggle --}}
                    <form action="{{ route('admin.toggle-dark-mode') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit"
                            class="p-2 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition-all hover:text-amber-500 dark:hover:text-amber-400"
                            title="Toggle Dark Mode">
                            <svg class="w-[18px] h-[18px] dark:hidden" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                            </svg>
                            <svg class="w-[18px] h-[18px] hidden dark:block" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </button>
                    </form>

                    {{-- Logout --}}
                    <form action="{{ route('admin.logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit"
                            class="p-2 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-500 transition-all"
                            title="{{ __('admin.logout') }}">
                            <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </button>
                    </form>
                </div>
            </header>

            {{-- Page Content --}}
            <main class="flex-1 overflow-y-auto p-4 lg:p-6">
                {{-- Toast Messages --}}
                @if(session('success'))
                    <div id="toast-success" class="toast toast-success mb-4">
                        <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd" />
                        </svg>
                        <span class="font-medium">{{ session('success') }}</span>
                        <button
                            onclick="this.parentElement.style.animation='fade-in 0.2s reverse forwards';setTimeout(()=>this.parentElement.remove(),200)"
                            class="ml-auto opacity-60 hover:opacity-100 transition-opacity">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                @endif
                @if(session('error'))
                    <div id="toast-error" class="toast toast-error mb-4">
                        <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                clip-rule="evenodd" />
                        </svg>
                        <span class="font-medium">{{ session('error') }}</span>
                        <button
                            onclick="this.parentElement.style.animation='fade-in 0.2s reverse forwards';setTimeout(()=>this.parentElement.remove(),200)"
                            class="ml-auto opacity-60 hover:opacity-100 transition-opacity">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    {{-- Mobile sidebar overlay --}}
    <div id="sidebarOverlay" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-40 hidden lg:hidden"
        onclick="toggleSidebar()"></div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.toggle('hidden');
            sidebar.classList.toggle('fixed');
            sidebar.classList.toggle('z-50');
            sidebar.classList.toggle('inset-y-0');
            sidebar.classList.toggle('left-0');
            overlay.classList.toggle('hidden');
        }
        document.getElementById('sidebarToggle')?.addEventListener('click', toggleSidebar);

        // Auto-dismiss toasts after 5s with animation
        setTimeout(() => {
            ['toast-success', 'toast-error'].forEach(id => {
                const el = document.getElementById(id);
                if (el) {
                    el.style.animation = 'fade-in 0.3s reverse forwards';
                    setTimeout(() => el.remove(), 300);
                }
            });
        }, 5000);

        // Dark mode sync
        if (document.documentElement.classList.contains('dark')) {
            localStorage.setItem('dopifuture_dark', '1');
        } else {
            localStorage.setItem('dopifuture_dark', '0');
        }
    </script>
    @stack('scripts')
</body>

</html>