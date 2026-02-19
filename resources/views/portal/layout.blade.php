<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'DopiFuture') — DopiFuture</title>
    <meta name="description" content="@yield('meta_description', 'DopiFuture — Digital Education Platform')">
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
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, var(--gray-950) 0%, #0f172a 50%, var(--gray-900) 100%);
            color: var(--gray-200);
            min-height: 100vh;
            line-height: 1.6;
        }

        /* ─── Header ─────────────────────── */
        .portal-header {
            position: sticky;
            top: 0;
            z-index: 50;
            background: rgba(3, 7, 18, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        }

        .portal-header-inner {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.75rem 1.5rem;
        }

        .portal-logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 700;
            font-size: 1.25rem;
            color: white;
            text-decoration: none;
        }

        .portal-logo-icon {
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, var(--brand-500), var(--brand-700));
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .portal-nav {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        .portal-nav a {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            color: var(--gray-400);
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.2s;
        }

        .portal-nav a:hover {
            color: white;
            background: rgba(255, 255, 255, 0.06);
        }

        .portal-nav a.active {
            color: white;
            background: rgba(59, 130, 246, 0.15);
        }

        /* ─── Main ───────────────────────── */
        .portal-main {
            max-width: 1200px;
            margin: 0 auto;
            padding: 3rem 1.5rem;
        }

        /* ─── Footer ─────────────────────── */
        .portal-footer {
            border-top: 1px solid rgba(255, 255, 255, 0.06);
            padding: 2rem 1.5rem;
            text-align: center;
            color: var(--gray-500);
            font-size: 0.8rem;
        }

        /* ─── Form ───────────────────────── */
        .form-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 16px;
            padding: 2rem;
        }

        .form-label {
            display: block;
            font-size: 0.8rem;
            font-weight: 500;
            color: var(--gray-400);
            margin-bottom: 0.375rem;
        }

        .form-input,
        .form-textarea,
        .form-select {
            width: 100%;
            padding: 0.625rem 0.875rem;
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(255, 255, 255, 0.04);
            color: white;
            font-size: 0.9rem;
            font-family: inherit;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-input:focus,
        .form-textarea:focus,
        .form-select:focus {
            outline: none;
            border-color: var(--brand-500);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }

        .form-input::placeholder,
        .form-textarea::placeholder {
            color: var(--gray-600);
        }

        .form-textarea {
            resize: vertical;
            min-height: 80px;
        }

        .form-error {
            color: #f87171;
            font-size: 0.75rem;
            margin-top: 0.25rem;
        }

        .form-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.25rem;
        }

        @media (max-width: 640px) {
            .form-grid-2 {
                grid-template-columns: 1fr;
            }
        }

        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 2rem;
            border-radius: 10px;
            border: none;
            background: linear-gradient(135deg, var(--brand-600), var(--brand-700));
            color: white;
            font-size: 0.9rem;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--brand-500), var(--brand-600));
            transform: translateY(-1px);
            box-shadow: 0 8px 24px rgba(59, 130, 246, 0.3);
        }

        .alert-success {
            background: rgba(34, 197, 94, 0.1);
            border: 1px solid rgba(34, 197, 94, 0.2);
            color: #4ade80;
            padding: 1rem 1.25rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }

        .hero-glow {
            position: absolute;
            top: -200px;
            left: 50%;
            transform: translateX(-50%);
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.08) 0%, transparent 70%);
            pointer-events: none;
        }

        /* ─── Portal Components ──────────── */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
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

        .badge {
            display: inline-flex;
            padding: 0.2rem 0.6rem;
            border-radius: 6px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .badge-success {
            background: rgba(34, 197, 94, 0.15);
            color: #4ade80;
        }

        .badge-danger {
            background: rgba(239, 68, 68, 0.12);
            color: #f87171;
        }

        .badge-info {
            background: rgba(59, 130, 246, 0.15);
            color: var(--brand-400);
        }

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
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .btn-danger:hover {
            background: rgba(239, 68, 68, 0.2);
        }

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
    </style>
</head>

<body>
    {{-- Header --}}
    <header class="portal-header">
        <div class="portal-header-inner">
            <a href="{{ url('/') }}" class="portal-logo">
                <div class="portal-logo-icon">
                    <svg width="16" height="16" fill="none" stroke="white" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                DopiFuture
            </a>
            <nav class="portal-nav">
                @auth
                    <a href="{{ route('portal.dashboard') }}"
                        class="{{ request()->routeIs('portal.dashboard') ? 'active' : '' }}">{{ app()->getLocale() === 'tr' ? 'Genel Bakış' : 'Dashboard' }}</a>
                    @if(auth()->user()->hasAnyRole(['super-admin', 'admin', 'school-admin']))
                        <a href="{{ route('portal.schools.index') }}"
                            class="{{ request()->routeIs('portal.schools.*') ? 'active' : '' }}">{{ app()->getLocale() === 'tr' ? 'Okullar' : 'Schools' }}</a>
                    @endif
                    @if(auth()->user()->hasAnyRole(['super-admin', 'admin', 'school-admin', 'school-principal', 'teacher']))
                        <a href="{{ route('portal.classes.index') }}"
                            class="{{ request()->routeIs('portal.classes.*') ? 'active' : '' }}">{{ app()->getLocale() === 'tr' ? 'Sınıflar' : 'Classes' }}</a>
                    @endif
                    @if(auth()->user()->hasAnyRole(['super-admin', 'admin', 'school-admin']))
                        <a href="{{ route('portal.users.index') }}"
                            class="{{ request()->routeIs('portal.users.*') ? 'active' : '' }}">{{ app()->getLocale() === 'tr' ? 'Kullanıcılar' : 'Users' }}</a>
                    @endif
                    @if(auth()->user()->hasAnyRole(['super-admin', 'admin', 'license-manager', 'school-admin']))
                        <a href="{{ route('portal.licenses.index') }}"
                            class="{{ request()->routeIs('portal.licenses.*') ? 'active' : '' }}">{{ app()->getLocale() === 'tr' ? 'Lisanslar' : 'Licenses' }}</a>
                    @endif
                    @if(auth()->user()->hasAnyRole(['super-admin', 'admin', 'moderator', 'school-admin', 'school-principal']))
                        <a href="{{ route('portal.reports') }}"
                            class="{{ request()->routeIs('portal.reports') ? 'active' : '' }}">{{ app()->getLocale() === 'tr' ? 'Raporlar' : 'Reports' }}</a>
                    @endif
                    <a href="{{ route('portal.profile') }}"
                        class="{{ request()->routeIs('portal.profile') ? 'active' : '' }}">{{ app()->getLocale() === 'tr' ? 'Profilim' : 'Profile' }}</a>
                    <form action="{{ route('portal.logout') }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="submit"
                            style="background:rgba(239,68,68,0.12);color:#f87171;border:none;padding:0.5rem 1rem;border-radius:8px;font-size:0.875rem;font-weight:500;cursor:pointer;font-family:inherit;">
                            {{ app()->getLocale() === 'tr' ? 'Çıkış' : 'Logout' }}
                        </button>
                    </form>
                @else
                    <a href="{{ route('portal.home') }}"
                        class="{{ request()->routeIs('portal.home') ? 'active' : '' }}">{{ app()->getLocale() === 'tr' ? 'Ana Sayfa' : 'Home' }}</a>
                    <a href="{{ route('portal.solutions') }}"
                        class="{{ request()->routeIs('portal.solutions') ? 'active' : '' }}">{{ app()->getLocale() === 'tr' ? 'Çözümler' : 'Solutions' }}</a>
                    <a href="{{ route('register.create') }}"
                        class="{{ request()->routeIs('register.*') ? 'active' : '' }}">{{ app()->getLocale() === 'tr' ? 'Okul Kaydı' : 'Register' }}</a>
                    <a href="{{ route('portal.contact') }}"
                        class="{{ request()->routeIs('portal.contact') ? 'active' : '' }}">{{ app()->getLocale() === 'tr' ? 'İletişim' : 'Contact' }}</a>
                    <a href="{{ route('portal.login') }}"
                        style="background: rgba(59,130,246,0.15); color: var(--brand-400);">{{ app()->getLocale() === 'tr' ? 'Giriş Yap' : 'Login' }}</a>
                @endauth

                {{-- Language Switcher (end of nav) --}}
                <div style="display: flex; align-items: center; gap: 2px; margin-left: 0.5rem;">
                    <form action="{{ route('portal.switch-locale') }}" method="POST" style="display:inline;">
                        @csrf
                        <input type="hidden" name="locale" value="tr">
                        <button type="submit"
                            style="background: {{ app()->getLocale() === 'tr' ? 'rgba(59,130,246,0.2)' : 'transparent' }}; color: {{ app()->getLocale() === 'tr' ? 'var(--brand-400)' : 'var(--gray-500)' }}; border: 1px solid {{ app()->getLocale() === 'tr' ? 'rgba(59,130,246,0.3)' : 'rgba(255,255,255,0.08)' }}; padding: 0.3rem 0.6rem; border-radius: 6px 0 0 6px; font-size: 0.75rem; font-weight: 600; cursor: pointer; font-family: inherit; transition: all 0.2s;">
                            TR
                        </button>
                    </form>
                    <form action="{{ route('portal.switch-locale') }}" method="POST" style="display:inline;">
                        @csrf
                        <input type="hidden" name="locale" value="en">
                        <button type="submit"
                            style="background: {{ app()->getLocale() === 'en' ? 'rgba(59,130,246,0.2)' : 'transparent' }}; color: {{ app()->getLocale() === 'en' ? 'var(--brand-400)' : 'var(--gray-500)' }}; border: 1px solid {{ app()->getLocale() === 'en' ? 'rgba(59,130,246,0.3)' : 'rgba(255,255,255,0.08)' }}; padding: 0.3rem 0.6rem; border-radius: 0 6px 6px 0; font-size: 0.75rem; font-weight: 600; cursor: pointer; font-family: inherit; transition: all 0.2s;">
                            EN
                        </button>
                    </form>
                </div>
            </nav>
        </div>
    </header>

    {{-- Main Content --}}
    <main class="portal-main" style="position: relative;">
        <div class="hero-glow"></div>
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="portal-footer">
        <p>&copy; {{ date('Y') }} DopiFuture. {{ __('admin.all_rights_reserved') ?? 'All rights reserved.' }}</p>
    </footer>
</body>

</html>