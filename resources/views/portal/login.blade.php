@extends('portal.layout')
@section('title', __('portal.login_title'))
@section('meta_description', __('portal.login_meta'))

@section('content')
    <div style="max-width: 440px; margin: 0 auto;">

        {{-- Hero --}}
        <div style="text-align: center; margin-bottom: 2.5rem;">
            <div style="margin-bottom: 1.25rem;">
                <img src="{{ asset('images/dopifuture-logo-gorsel.png') }}" alt="DopiFuture Icon" style="width: 72px; height: 72px; min-width: 55px; min-height: 55px; object-fit: contain; margin: 0 auto; display: block;" />
            </div>
            <div style="margin-bottom: 0.5rem;">
                <img src="{{ asset('images/dopifuture-logo-yazi.png') }}" alt="DopiFuture" style="height: 36px; object-fit: contain; margin: 0 auto; display: block; filter: invert(1) brightness(100);" />
            </div>
            <p style="color: var(--gray-400); font-size: 0.95rem;">
                {{ __('portal.login_subtitle') }}
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
                        placeholder="{{ __('portal.email_placeholder') }}">
                </div>

                {{-- Password --}}
                <div style="margin-bottom: 1.5rem;">
                    <label class="form-label">{{ __('admin.password') }}</label>
                    <input type="password" name="password" required class="form-input"
                        placeholder="{{ __('portal.password_placeholder') }}">
                </div>

                {{-- Remember Me --}}
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.75rem;">
                    <label
                        style="display: flex; align-items: center; gap: 0.5rem; color: var(--gray-400); font-size: 0.875rem; cursor: pointer;">
                        <input type="checkbox" name="remember" value="1"
                            style="accent-color: var(--brand-500); width: 16px; height: 16px;">
                        {{ __('portal.remember_me') }}
                    </label>
                </div>

                {{-- Submit --}}
                <button type="submit" class="btn-primary" style="width: 100%; justify-content: center;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                    </svg>
                    {{ __('portal.sign_in') }}
                </button>
            </form>
        </div>

        {{-- Links --}}
        <div style="margin-top: 1.75rem; text-align: center; display: flex; flex-direction: column; gap: 0.75rem;">
            <a href="{{ route('register.create') }}"
                style="color: var(--brand-400); font-size: 0.875rem; text-decoration: none;">
                {{ __('portal.want_to_register') }}
            </a>
            <a href="{{ url('/admin/login') }}" style="color: var(--gray-500); font-size: 0.8rem; text-decoration: none;">
                {{ __('portal.admin_login') }}
            </a>
        </div>
    </div>
@endsection