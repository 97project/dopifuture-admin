@extends('portal.app')
@section('title', 'My Profile')
@section('page-title', 'My Profile')
@php $user = auth()->user(); @endphp

@section('content')
    {{-- Figma §4.8: Profile Avatar --}}
    <div style="text-align:center;margin-bottom:24px;">
        <div class="dp-profile-avatar">
            {{ strtoupper(substr($user->name,0,1) . substr($user->surname ?? '',0,1)) }}
        </div>
        <h2 style="font-size:24px;font-weight:700;color:var(--color-txt);margin:0;">{{ $user->name }} {{ $user->surname ?? '' }}</h2>
        <p style="font-size:14px;color:var(--color-txt-muted);margin-top:4px;">
            @foreach($user->roles as $r) {{ $r->name }} @endforeach
        </p>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
        {{-- Personal Info --}}
        <div class="dp-card">
            <div class="dp-card-title">Personal Details</div>
            <form action="{{ route('portal.profile.update') }}" method="POST">
                @csrf @method('PUT')
                <div class="dp-form-group">
                    <label class="dp-form-label">First Name *</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="dp-form-input">
                    @error('name') <p class="dp-form-error">{{ $message }}</p> @enderror
                </div>
                <div class="dp-form-group">
                    <label class="dp-form-label">Last Name</label>
                    <input type="text" name="surname" value="{{ old('surname', $user->surname) }}" class="dp-form-input">
                </div>
                <div class="dp-form-group">
                    <label class="dp-form-label">E-mail</label>
                    <input type="email" value="{{ $user->email }}" class="dp-form-input" disabled style="opacity:0.5;cursor:not-allowed;">
                </div>
                <div class="dp-form-group">
                    <label class="dp-form-label">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="dp-form-input">
                </div>
                <div class="dp-form-group">
                    <label class="dp-form-label">Language</label>
                    <select name="locale" class="dp-form-select">
                        <option value="en" {{ old('locale', $user->locale ?? 'en') === 'en' ? 'selected' : '' }}>English</option>
                        <option value="tr" {{ old('locale', $user->locale ?? 'en') === 'tr' ? 'selected' : '' }}>Turkish</option>
                    </select>
                </div>
                <button type="submit" class="dp-btn-submit">Update</button>
            </form>
        </div>

        <div>
            {{-- Password Change --}}
            <div class="dp-card" style="margin-bottom:20px;">
                <div class="dp-card-title">Change Password</div>
                <form action="{{ route('portal.profile.update') }}" method="POST">
                    @csrf @method('PUT')
                    <div class="dp-form-group">
                        <label class="dp-form-label">Current Password</label>
                        <input type="password" name="current_password" class="dp-form-input">
                        @error('current_password') <p class="dp-form-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="dp-form-group">
                        <label class="dp-form-label">New Password</label>
                        <input type="password" name="password" class="dp-form-input">
                        @error('password') <p class="dp-form-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="dp-form-group">
                        <label class="dp-form-label">Confirm Password</label>
                        <input type="password" name="password_confirmation" class="dp-form-input">
                    </div>
                    <button type="submit" class="dp-btn-submit">Change Password</button>
                </form>
            </div>

            {{-- Role Info --}}
            <div class="dp-card">
                <div class="dp-card-title">Account Info</div>
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
                    <span style="font-size:14px;font-weight:500;color:var(--text-secondary);">Role:</span>
                    @foreach($user->roles as $r)
                        <span class="dp-badge dp-badge-pending">{{ $r->name }}</span>
                    @endforeach
                </div>
                <p style="font-size:13px;color:var(--text-muted);">Joined: {{ $user->created_at?->format('d.m.Y') }}</p>
            </div>
        </div>
    </div>
@endsection