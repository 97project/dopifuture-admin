@extends('portal.app')
@section('title', __('portal.my_profile'))
@section('page-title', __('portal.my_profile'))
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
            <div class="dp-card-title">{{ __('admin.profile') }}</div>
            <form action="{{ route('portal.profile.update') }}" method="POST">
                @csrf @method('PUT')
                <div class="dp-form-group">
                    <label class="dp-form-label">{{ __('admin.name') }} *</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="dp-form-input">
                    @error('name') <p class="dp-form-error">{{ $message }}</p> @enderror
                </div>
                <div class="dp-form-group">
                    <label class="dp-form-label">{{ __('admin.surname') }}</label>
                    <input type="text" name="surname" value="{{ old('surname', $user->surname) }}" class="dp-form-input">
                </div>
                <div class="dp-form-group">
                    <label class="dp-form-label">{{ __('admin.email') }}</label>
                    <input type="email" value="{{ $user->email }}" class="dp-form-input" disabled style="opacity:0.5;cursor:not-allowed;">
                </div>
                <div class="dp-form-group">
                    <label class="dp-form-label">{{ __('admin.phone') }}</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="dp-form-input">
                </div>
                <div class="dp-form-group">
                    <label class="dp-form-label">{{ __('admin.language') }}</label>
                    <select name="locale" class="dp-form-select">
                        @foreach(\App\Models\Language::where('is_active', true)->get() as $lang)
                            <option value="{{ $lang->code }}" {{ old('locale', $user->locale ?? \App\Models\Language::getDefault()->code) === $lang->code ? 'selected' : '' }}>
                                {{ $lang->native_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="dp-btn-submit">{{ __('admin.save') }}</button>
            </form>
        </div>

        <div>
            {{-- Password Change --}}
            <div class="dp-card" style="margin-bottom:20px;">
                <div class="dp-card-title">{{ __('admin.change_password') }}</div>
                <form action="{{ route('portal.profile.update') }}" method="POST">
                    @csrf @method('PUT')
                    <div class="dp-form-group">
                        <label class="dp-form-label">{{ __('admin.current_password') }}</label>
                        <input type="password" name="current_password" class="dp-form-input">
                        @error('current_password') <p class="dp-form-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="dp-form-group">
                        <label class="dp-form-label">{{ __('admin.new_password') }}</label>
                        <input type="password" name="password" class="dp-form-input">
                        @error('password') <p class="dp-form-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="dp-form-group">
                        <label class="dp-form-label">{{ __('admin.confirm_password') }}</label>
                        <input type="password" name="password_confirmation" class="dp-form-input">
                    </div>
                    <button type="submit" class="dp-btn-submit">{{ __('admin.change_password') }}</button>
                </form>
            </div>

            {{-- Role Info --}}
            <div class="dp-card">
                <div class="dp-card-title">{{ __('admin.info') }}</div>
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
                    <span style="font-size:14px;font-weight:500;color:var(--text-secondary);">{{ __('admin.role') }}:</span>
                    @foreach($user->roles as $r)
                        <span class="dp-badge dp-badge-pending">{{ $r->name }}</span>
                    @endforeach
                </div>
                <p style="font-size:13px;color:var(--text-muted);">{{ __('portal.joined') }}: {{ $user->created_at?->format('d.m.Y') }}</p>
            </div>
        </div>
    </div>
@endsection