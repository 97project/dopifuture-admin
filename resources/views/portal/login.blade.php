@extends('portal.layout')
@section('title', app()->getLocale() === 'tr' ? 'Giriş Yap' : 'Login')
@section('meta_description', 'DopiFuture portal login')

@section('content')
    <div style="max-width: 440px; margin: 0 auto;">

        {{-- Hero --}}
        <div style="text-align: center; margin-bottom: 2.5rem;">
            <div
                style="display: inline-flex; align-items: center; justify-content: center; width: 64px; height: 64px; background: linear-gradient(135deg, var(--brand-500), var(--brand-700)); border-radius: 16px; margin-bottom: 1.25rem;">
                <svg width="28" height="28" fill="none" stroke="white" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
            </div>
            <h1 style="font-size: 2rem; font-weight: 800; color: white; margin-bottom: 0.5rem; letter-spacing: -0.025em;">
                {{ app()->getLocale() === 'tr' ? 'Giriş Yap' : 'Sign In' }}
            </h1>
            <p style="color: var(--gray-400); font-size: 0.95rem;">
                {{ app()->getLocale() === 'tr'
        ? 'DopiFuture hesabınızla giriş yapın.'
        : 'Sign in with your DopiFuture account.' }}
            </p>
        </div>

        {{-- Error Messages --}}
        @if($errors->any())
            <div
                style="background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.2); border-radius: 12px; padding: 0.875rem 1.25rem; margin-bottom: 1.5rem; color: #f87171; font-size: 0.875rem;">
                <svg style="display:inline; width:16px; height:16px; vertical-align: text-bottom; margin-right: 6px;"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ $errors->first('email') }}
            </div>
        @endif

        {{-- Login Form --}}
        <div class="form-card">
            <form action="{{ route('portal.login.submit') }}" method="POST">
                @csrf

                {{-- Email --}}
                <div style="margin-bottom: 1.25rem;">
                    <label class="form-label">{{ __('admin.email') }}</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus class="form-input"
                        placeholder="{{ app()->getLocale() === 'tr' ? 'E-posta adresiniz' : 'Your email address' }}">
                </div>

                {{-- Password --}}
                <div style="margin-bottom: 1.5rem;">
                    <label class="form-label">{{ __('admin.password') }}</label>
                    <input type="password" name="password" required class="form-input"
                        placeholder="{{ app()->getLocale() === 'tr' ? 'Şifreniz' : 'Your password' }}">
                </div>

                {{-- Remember Me --}}
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.75rem;">
                    <label
                        style="display: flex; align-items: center; gap: 0.5rem; color: var(--gray-400); font-size: 0.875rem; cursor: pointer;">
                        <input type="checkbox" name="remember" value="1"
                            style="accent-color: var(--brand-500); width: 16px; height: 16px;">
                        {{ app()->getLocale() === 'tr' ? 'Beni hatırla' : 'Remember me' }}
                    </label>
                </div>

                {{-- Submit --}}
                <button type="submit" class="btn-primary" style="width: 100%; justify-content: center;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                    </svg>
                    {{ app()->getLocale() === 'tr' ? 'Giriş Yap' : 'Sign In' }}
                </button>
            </form>
        </div>

        {{-- Links --}}
        <div style="margin-top: 1.75rem; text-align: center; display: flex; flex-direction: column; gap: 0.75rem;">
            <a href="{{ route('register.create') }}"
                style="color: var(--brand-400); font-size: 0.875rem; text-decoration: none;">
                {{ app()->getLocale() === 'tr' ? 'Okulunuzu kaydetmek ister misiniz?' : 'Want to register your school?' }}
            </a>
            <a href="{{ url('/admin/login') }}" style="color: var(--gray-500); font-size: 0.8rem; text-decoration: none;">
                {{ app()->getLocale() === 'tr' ? 'Yönetici Girişi →' : 'Admin Login →' }}
            </a>
        </div>
    </div>
@endsection