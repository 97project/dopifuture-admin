@extends('portal.layout')
@section('title', $editUser->exists ? (app()->getLocale() === 'tr' ? 'Kullanıcı Düzenle' : 'Edit User') : (app()->getLocale() === 'tr' ? 'Yeni Kullanıcı' : 'New User'))
@php $isTr = app()->getLocale() === 'tr'; @endphp

@section('content')
    <div class="page-header">
        <h1>{{ $editUser->exists ? ($isTr ? 'Kullanıcı Düzenle' : 'Edit User') : ($isTr ? 'Yeni Kullanıcı' : 'New User') }}
        </h1>
        <p><a href="{{ route('portal.users.index') }}" style="color: var(--brand-400); text-decoration: none;">←
                {{ $isTr ? 'Kullanıcılara Dön' : 'Back to Users' }}</a></p>
    </div>

    <div style="max-width: 600px;">
        <form action="{{ $editUser->exists ? route('portal.users.update', $editUser) : route('portal.users.store') }}"
            method="POST">
            @csrf
            @if($editUser->exists) @method('PUT') @endif

            <div class="form-card" style="margin-bottom: 1.5rem;">
                <div class="form-grid-2" style="margin-bottom: 1rem;">
                    <div>
                        <label class="form-label">{{ __('admin.name') }} *</label>
                        <input type="text" name="name" value="{{ old('name', $editUser->name) }}" required
                            class="form-input">
                        @error('name') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">{{ __('admin.surname') }}</label>
                        <input type="text" name="surname" value="{{ old('surname', $editUser->surname) }}"
                            class="form-input">
                    </div>
                </div>
                <div style="margin-bottom: 1rem;">
                    <label class="form-label">E-posta *</label>
                    <input type="email" name="email" value="{{ old('email', $editUser->email) }}" required
                        class="form-input">
                    @error('email') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div style="margin-bottom: 1rem;">
                    <label class="form-label">{{ $isTr ? 'Şifre' : 'Password' }} {{ $editUser->exists ? '' : '*' }}</label>
                    <input type="password" name="password" class="form-input" {{ $editUser->exists ? '' : 'required' }}
                        placeholder="{{ $editUser->exists ? ($isTr ? 'Değiştirmek için doldurun' : 'Fill to change') : '' }}">
                    @error('password') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div class="form-grid-2" style="margin-bottom: 1rem;">
                    <div>
                        <label class="form-label">{{ $isTr ? 'Rol' : 'Role' }} *</label>
                        <select name="role" class="form-select" required>
                            @foreach($roles as $role)
                                <option value="{{ $role }}" {{ old('role', $editUser->roles->first()?->name) === $role ? 'selected' : '' }}>{{ $role }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if($editUser->exists)
                        <div>
                            <label class="form-label">{{ $isTr ? 'Durum' : 'Status' }} *</label>
                            <select name="status" class="form-select" required>
                                <option value="active" {{ $editUser->status === 'active' ? 'selected' : '' }}>
                                    {{ $isTr ? 'Aktif' : 'Active' }}</option>
                                <option value="inactive" {{ $editUser->status === 'inactive' ? 'selected' : '' }}>
                                    {{ $isTr ? 'Pasif' : 'Inactive' }}</option>
                            </select>
                        </div>
                    @endif
                </div>
                @if(!$editUser->exists && isset($schools) && $schools->count())
                    <div style="margin-bottom: 1rem;">
                        <label class="form-label">{{ $isTr ? 'Okula Ata' : 'Assign to School' }}</label>
                        <select name="school_id" class="form-select">
                            <option value="">{{ $isTr ? 'Seçiniz (opsiyonel)' : 'Select (optional)' }}</option>
                            @foreach($schools as $school)
                                <option value="{{ $school->id }}">{{ $school->getTranslation('name') }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
            </div>

            <div style="display: flex; gap: 0.75rem; align-items: center;">
                <button type="submit" class="btn-primary"
                    style="padding: 0.65rem 2rem;">{{ $isTr ? 'Kaydet' : 'Save' }}</button>
                <a href="{{ route('portal.users.index') }}" class="btn btn-ghost">{{ $isTr ? 'İptal' : 'Cancel' }}</a>
                @if($editUser->exists)
                    <form action="{{ route('portal.users.destroy', $editUser) }}" method="POST" style="margin-left: auto;"
                        onsubmit="return confirm('{{ $isTr ? 'Silmek istediğinize emin misiniz?' : 'Are you sure?' }}')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger">{{ $isTr ? 'Sil' : 'Delete' }}</button>
                    </form>
                @endif
            </div>
        </form>
    </div>
@endsection