@extends('portal.layout')
@section('title', app()->getLocale() === 'tr' ? 'Profilim' : 'My Profile')
@php $isTr = app()->getLocale() === 'tr';
$user = auth()->user(); @endphp

@section('content')
    <div class="page-header">
        <h1>{{ $isTr ? 'Profilim' : 'My Profile' }}</h1>
        <p>{{ $isTr ? 'Kişisel bilgilerinizi ve şifrenizi güncelleyin.' : 'Update your personal information and password.' }}
        </p>
    </div>

    @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; max-width: 900px;">
        {{-- Personal Info --}}
        <div class="form-card">
            <h3 style="font-size: 1rem; font-weight: 600; color: white; margin-bottom: 1.25rem;">
                {{ $isTr ? 'Kişisel Bilgiler' : 'Personal Details' }}</h3>
            <form action="{{ route('portal.profile.update') }}" method="POST">
                @csrf @method('PUT')
                <div style="margin-bottom: 1rem;">
                    <label class="form-label">{{ __('admin.name') }} *</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="form-input">
                    @error('name') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div style="margin-bottom: 1rem;">
                    <label class="form-label">{{ __('admin.surname') }}</label>
                    <input type="text" name="surname" value="{{ old('surname', $user->surname) }}" class="form-input">
                </div>
                <div style="margin-bottom: 1rem;">
                    <label class="form-label">E-posta</label>
                    <input type="email" value="{{ $user->email }}" class="form-input" disabled
                        style="opacity: 0.5; cursor: not-allowed;">
                </div>
                <div style="margin-bottom: 1rem;">
                    <label class="form-label">{{ $isTr ? 'Telefon' : 'Phone' }}</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="form-input">
                </div>
                <div style="margin-bottom: 1rem;">
                    <label class="form-label">{{ $isTr ? 'Dil' : 'Language' }}</label>
                    <select name="locale" class="form-select">
                        <option value="tr" {{ old('locale', $user->locale) === 'tr' ? 'selected' : '' }}>Türkçe</option>
                        <option value="en" {{ old('locale', $user->locale) === 'en' ? 'selected' : '' }}>English</option>
                    </select>
                </div>
                <button type="submit" class="btn-primary" style="width: 100%;">{{ $isTr ? 'Güncelle' : 'Update' }}</button>
            </form>
        </div>

        {{-- Password --}}
        <div>
            <div class="form-card" style="margin-bottom: 1.5rem;">
                <h3 style="font-size: 1rem; font-weight: 600; color: white; margin-bottom: 1.25rem;">
                    {{ $isTr ? 'Şifre Değiştir' : 'Change Password' }}</h3>
                <form action="{{ route('portal.profile.update') }}" method="POST">
                    @csrf @method('PUT')
                    <div style="margin-bottom: 1rem;">
                        <label class="form-label">{{ $isTr ? 'Mevcut Şifre' : 'Current Password' }}</label>
                        <input type="password" name="current_password" class="form-input">
                        @error('current_password') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label class="form-label">{{ $isTr ? 'Yeni Şifre' : 'New Password' }}</label>
                        <input type="password" name="password" class="form-input">
                        @error('password') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label class="form-label">{{ $isTr ? 'Şifre Tekrar' : 'Confirm Password' }}</label>
                        <input type="password" name="password_confirmation" class="form-input">
                    </div>
                    <button type="submit" class="btn-primary"
                        style="width: 100%;">{{ $isTr ? 'Şifreyi Değiştir' : 'Change Password' }}</button>
                </form>
            </div>

            {{-- Role Info --}}
            <div class="form-card">
                <h3 style="font-size: 1rem; font-weight: 600; color: white; margin-bottom: 0.75rem;">
                    {{ $isTr ? 'Hesap Bilgileri' : 'Account Info' }}</h3>
                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                    <span class="form-label" style="margin: 0;">{{ $isTr ? 'Rol:' : 'Role:' }}</span>
                    @foreach($user->roles as $r)
                        <span class="badge badge-info">{{ $r->name }}</span>
                    @endforeach
                </div>
                <p style="font-size: 0.8rem; color: var(--gray-500);">{{ $isTr ? 'Kayıt:' : 'Joined:' }}
                    {{ $user->created_at?->format('d.m.Y') }}</p>
            </div>
        </div>
    </div>
@endsection