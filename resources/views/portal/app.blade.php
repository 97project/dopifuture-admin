<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') — DopiFuture</title>
    <meta name="description" content="@yield('meta_description', 'DopiFuture Portal')">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --brand-50: #eff6ff;
            --brand-100: #dbeafe;
            --brand-200: #bfdbfe;
            --brand-400: #60a5fa;
            --brand-500: #3b82f6;
            --brand-600: #2563eb;
            --brand-700: #1d4ed8;
            --brand-800: #1e40af;
            --brand-900: #1e3a8a;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
            --gray-950: #030712;
            --sidebar-w: 260px;
            --green-400: #4ade80;
            --green-500: #22c55e;
            --red-400: #f87171;
            --yellow-400: #facc15;
            --purple-400: #c084fc;
            --orange-400: #fb923c;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: var(--gray-950);
            color: var(--gray-200);
            min-height: 100vh;
            line-height: 1.6;
            display: flex;
        }

        /* ─── Sidebar ────────────────────── */
        .sidebar {
            width: var(--sidebar-w);
            min-height: 100vh;
            background: rgba(3, 7, 18, 0.95);
            border-right: 1px solid rgba(255, 255, 255, 0.06);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 40;
            transition: transform 0.3s;
        }

        .sidebar-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .sidebar-logo {
            width: 34px;
            height: 34px;
            background: linear-gradient(135deg, var(--brand-500), var(--brand-700));
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .sidebar-brand {
            font-weight: 700;
            font-size: 1.1rem;
            color: white;
            text-decoration: none;
        }

        /* User info */
        .sidebar-user {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .sidebar-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--brand-600), var(--brand-800));
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.85rem;
            color: white;
            flex-shrink: 0;
        }

        .sidebar-user-info {
            overflow: hidden;
        }

        .sidebar-user-name {
            font-size: 0.85rem;
            font-weight: 600;
            color: white;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sidebar-user-role {
            font-size: 0.7rem;
            color: var(--brand-400);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        /* Nav */
        .sidebar-nav {
            flex: 1;
            padding: 0.75rem 0;
            overflow-y: auto;
        }

        .nav-section {
            padding: 0.5rem 1.5rem 0.3rem;
            font-size: 0.65rem;
            font-weight: 600;
            color: var(--gray-500);
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.6rem 1.5rem;
            color: var(--gray-400);
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }

        .nav-link:hover {
            color: white;
            background: rgba(255, 255, 255, 0.04);
        }

        .nav-link.active {
            color: white;
            background: rgba(59, 130, 246, 0.08);
            border-left-color: var(--brand-500);
        }

        .nav-link svg {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
        }

        .nav-link .badge {
            margin-left: auto;
            background: rgba(59, 130, 246, 0.2);
            color: var(--brand-400);
            font-size: 0.7rem;
            font-weight: 600;
            padding: 0.1rem 0.5rem;
            border-radius: 10px;
        }

        /* Sidebar footer */
        .sidebar-footer {
            padding: 0.75rem 1.5rem;
            border-top: 1px solid rgba(255, 255, 255, 0.06);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .sidebar-footer .lang-btn {
            padding: 0.3rem 0.6rem;
            border-radius: 6px;
            font-size: 0.7rem;
            font-weight: 600;
            cursor: pointer;
            font-family: inherit;
            transition: all 0.2s;
            border: 1px solid rgba(255, 255, 255, 0.08);
            background: transparent;
            color: var(--gray-500);
        }

        .sidebar-footer .lang-btn.active {
            background: rgba(59, 130, 246, 0.2);
            color: var(--brand-400);
            border-color: rgba(59, 130, 246, 0.3);
        }

        .sidebar-footer form {
            display: inline;
        }

        /* ─── Main Content ───────────────── */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-w);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Top bar */
        .topbar {
            position: sticky;
            top: 0;
            z-index: 30;
            background: rgba(3, 7, 18, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
            padding: 0 2rem;
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .topbar-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: white;
        }

        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .topbar-actions .btn-logout {
            background: rgba(239, 68, 68, 0.1);
            color: var(--red-400);
            border: 1px solid rgba(239, 68, 68, 0.2);
            padding: 0.4rem 1rem;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 500;
            cursor: pointer;
            font-family: inherit;
            transition: all 0.2s;
        }

        .topbar-actions .btn-logout:hover {
            background: rgba(239, 68, 68, 0.2);
        }

        /* Content area */
        .content-area {
            flex: 1;
            padding: 2rem;
            max-width: 1400px;
        }

        /* ─── Shared Components ───────────── */
        .page-header {
            margin-bottom: 1.75rem;
        }

        .page-header h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            margin-bottom: 0.25rem;
        }

        .page-header p {
            color: var(--gray-400);
            font-size: 0.9rem;
        }

        /* Stats grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.07);
            border-radius: 14px;
            padding: 1.25rem;
            transition: all 0.3s;
        }

        .stat-card:hover {
            border-color: rgba(59, 130, 246, 0.2);
            transform: translateY(-2px);
        }

        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 0.75rem;
        }

        .stat-value {
            font-size: 1.75rem;
            font-weight: 800;
            color: white;
        }

        .stat-value .sub {
            font-size: 0.9rem;
            color: var(--gray-500);
            font-weight: 500;
        }

        .stat-name {
            font-size: 0.8rem;
            color: var(--gray-500);
            margin-top: 0.15rem;
        }

        /* Data table */
        .data-table-wrap {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.07);
            border-radius: 14px;
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .data-table-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 1.25rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        }

        .data-table-header h3 {
            font-size: 1rem;
            font-weight: 600;
            color: white;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table thead th {
            padding: 0.7rem 1.25rem;
            text-align: left;
            font-size: 0.7rem;
            font-weight: 600;
            color: var(--gray-500);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            background: rgba(255, 255, 255, 0.02);
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        }

        .data-table tbody td {
            padding: 0.75rem 1.25rem;
            font-size: 0.85rem;
            color: var(--gray-300);
            border-bottom: 1px solid rgba(255, 255, 255, 0.04);
        }

        .data-table tbody tr:last-child td {
            border-bottom: none;
        }

        .data-table tbody tr:hover {
            background: rgba(255, 255, 255, 0.02);
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 500;
            cursor: pointer;
            font-family: inherit;
            text-decoration: none;
            transition: all 0.2s;
            border: none;
        }

        .btn-primary {
            background: var(--brand-600);
            color: white;
        }

        .btn-primary:hover {
            background: var(--brand-700);
        }

        .btn-sm {
            padding: 0.35rem 0.75rem;
            font-size: 0.75rem;
        }

        .btn-ghost {
            background: rgba(255, 255, 255, 0.05);
            color: var(--gray-300);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .btn-ghost:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .btn-danger {
            background: rgba(239, 68, 68, 0.1);
            color: var(--red-400);
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .btn-danger:hover {
            background: rgba(239, 68, 68, 0.2);
        }

        /* Badge */
        .badge {
            display: inline-flex;
            padding: 0.2rem 0.6rem;
            border-radius: 6px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .badge-success {
            background: rgba(34, 197, 94, 0.15);
            color: var(--green-400);
        }

        .badge-danger {
            background: rgba(239, 68, 68, 0.12);
            color: var(--red-400);
        }

        .badge-info {
            background: rgba(59, 130, 246, 0.15);
            color: var(--brand-400);
        }

        /* Form */
        .form-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.07);
            border-radius: 14px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-size: 0.8rem;
            font-weight: 500;
            color: var(--gray-400);
            margin-bottom: 0.4rem;
        }

        .form-input,
        .form-select,
        .form-textarea {
            width: 100%;
            padding: 0.6rem 0.875rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            color: white;
            font-size: 0.85rem;
            font-family: inherit;
            transition: all 0.2s;
        }

        .form-input:focus,
        .form-select:focus,
        .form-textarea:focus {
            outline: none;
            border-color: var(--brand-500);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }

        .form-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .form-error {
            color: var(--red-400);
            font-size: 0.75rem;
            margin-top: 0.3rem;
        }

        .form-textarea {
            min-height: 100px;
            resize: vertical;
        }

        /* Alert */
        .alert-success {
            background: rgba(34, 197, 94, 0.1);
            border: 1px solid rgba(34, 197, 94, 0.2);
            color: var(--green-400);
            padding: 0.75rem 1rem;
            border-radius: 10px;
            font-size: 0.85rem;
            margin-bottom: 1.25rem;
        }

        /* Progress bar */
        .progress-bar {
            height: 6px;
            background: rgba(255, 255, 255, 0.06);
            border-radius: 3px;
            overflow: hidden;
        }

        .progress-bar .fill {
            height: 100%;
            border-radius: 3px;
            transition: width 0.5s ease;
        }

        /* Mobile toggle */
        .mobile-toggle {
            display: none;
            padding: 0.5rem;
            border: none;
            background: none;
            color: var(--gray-400);
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .mobile-toggle {
                display: block;
            }

            .content-area {
                padding: 1.25rem;
            }

            .stats-grid {
                grid-template-columns: 1fr 1fr;
            }

            .form-grid-2 {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    @php
        $user = auth()->user();
        $locale = app()->getLocale();
        $isTr = $locale === 'tr';
    @endphp

    {{-- Sidebar --}}
    <aside class="sidebar" id="sidebar">
        {{-- Logo --}}
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <svg width="16" height="16" fill="none" stroke="white" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
            </div>
            <a href="{{ route('portal.dashboard') }}" class="sidebar-brand">DopiFuture</a>
        </div>

        {{-- User --}}
        <div class="sidebar-user">
            <div class="sidebar-avatar">{{ strtoupper(substr($user->name, 0, 1) . substr($user->surname ?? '', 0, 1)) }}
            </div>
            <div class="sidebar-user-info">
                <div class="sidebar-user-name">{{ $user->name }} {{ $user->surname }}</div>
                <div class="sidebar-user-role">{{ $user->roles->first()?->name ?? 'user' }}</div>
            </div>
        </div>

        {{-- Navigation --}}
        <nav class="sidebar-nav">
            <div class="nav-section">{{ $isTr ? 'Ana Menü' : 'Main' }}</div>

            <a href="{{ route('portal.dashboard') }}"
                class="nav-link {{ request()->routeIs('portal.dashboard') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                </svg>
                {{ $isTr ? 'Dashboard' : 'Dashboard' }}
            </a>

            @if($user->hasAnyRole(['super-admin', 'admin', 'license-manager', 'school-admin']))
                <div class="nav-section">{{ $isTr ? 'Yönetim' : 'Management' }}</div>

                @if($user->hasAnyRole(['super-admin', 'admin', 'license-manager', 'school-admin']))
                    <a href="{{ route('portal.schools.index') }}"
                        class="nav-link {{ request()->routeIs('portal.schools.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        {{ $isTr ? 'Okullar' : 'Schools' }}
                    </a>
                @endif

                @if($user->hasAnyRole(['super-admin', 'admin', 'school-admin', 'school-principal', 'teacher']))
                    <a href="{{ route('portal.classes.index') }}"
                        class="nav-link {{ request()->routeIs('portal.classes.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        {{ $isTr ? 'Sınıflar' : 'Classes' }}
                    </a>
                @endif

                @if($user->hasAnyRole(['super-admin', 'admin', 'license-manager', 'school-admin']))
                    <a href="{{ route('portal.users.index') }}"
                        class="nav-link {{ request()->routeIs('portal.users.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        {{ $isTr ? 'Kullanıcılar' : 'Users' }}
                    </a>
                @endif

                @if($user->hasAnyRole(['super-admin', 'admin', 'license-manager', 'school-admin']))
                    <a href="{{ route('portal.licenses.index') }}"
                        class="nav-link {{ request()->routeIs('portal.licenses.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                        </svg>
                        {{ $isTr ? 'Lisanslar' : 'Licenses' }}
                    </a>
                @endif
            @endif

            <div class="nav-section">{{ $isTr ? 'Uygulamalar' : 'Apps' }}</div>
            <a href="{{ route('portal.solutions') }}"
                class="nav-link {{ request()->routeIs('portal.solutions') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2z" />
                </svg>
                {{ $isTr ? 'Uygulamalar' : 'Applications' }}
            </a>

            <div class="nav-section">{{ $isTr ? 'Hesabım' : 'Account' }}</div>
            <a href="{{ route('portal.profile') }}"
                class="nav-link {{ request()->routeIs('portal.profile') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                {{ $isTr ? 'Profilim' : 'Profile' }}
            </a>

            @if($user->hasAnyRole(['super-admin', 'admin']))
                <a href="{{ route('admin.dashboard') }}" class="nav-link">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    {{ $isTr ? 'Admin Paneli' : 'Admin Panel' }}
                </a>
            @endif
        </nav>

        {{-- Footer: Language --}}
        <div class="sidebar-footer">
            <form action="{{ route('portal.switch-locale') }}" method="POST">
                @csrf <input type="hidden" name="locale" value="tr">
                <button type="submit" class="lang-btn {{ $locale === 'tr' ? 'active' : '' }}">TR</button>
            </form>
            <form action="{{ route('portal.switch-locale') }}" method="POST">
                @csrf <input type="hidden" name="locale" value="en">
                <button type="submit" class="lang-btn {{ $locale === 'en' ? 'active' : '' }}">EN</button>
            </form>
            <div style="flex:1"></div>
            <form action="{{ route('portal.logout') }}" method="POST">
                @csrf
                <button type="submit" class="lang-btn" style="color: var(--red-400); border-color: rgba(239,68,68,0.2);"
                    title="{{ $isTr ? 'Çıkış' : 'Logout' }}">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </button>
            </form>
        </div>
    </aside>

    {{-- Main Content --}}
    <div class="main-content">
        {{-- Top Bar --}}
        <header class="topbar">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <button class="mobile-toggle" onclick="document.getElementById('sidebar').classList.toggle('open')">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <span class="topbar-title">@yield('page_title', 'Dashboard')</span>
            </div>
            <div class="topbar-actions">
                <span style="font-size: 0.8rem; color: var(--gray-500);">{{ $user->email }}</span>
            </div>
        </header>

        {{-- Content --}}
        <div class="content-area">
            @if(session('success'))
                <div class="alert-success">
                    <svg style="display:inline; width:16px; height:16px; vertical-align: text-bottom; margin-right: 6px;"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ session('success') }}
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    @yield('scripts')
</body>

</html>