@extends('portal.layout')
@section('title', $school->exists ? (app()->getLocale() === 'tr' ? 'Okul Düzenle' : 'Edit School') : (app()->getLocale() === 'tr' ? 'Yeni Okul' : 'New School'))
@php $isTr = app()->getLocale() === 'tr'; @endphp

@section('content')
    <div class="page-header">
        <h1>{{ $school->exists ? ($isTr ? 'Okul Düzenle' : 'Edit School') : ($isTr ? 'Yeni Okul' : 'New School') }}</h1>
        <p><a href="{{ route('portal.schools.index') }}" style="color: var(--brand-400); text-decoration: none;">←
                {{ $isTr ? 'Okullara Dön' : 'Back to Schools' }}</a></p>
    </div>

    <div style="max-width: 700px;">
        <form action="{{ $school->exists ? route('portal.schools.update', $school) : route('portal.schools.store') }}"
            method="POST">
            @csrf
            @if($school->exists) @method('PUT') @endif

            <div class="form-card" style="margin-bottom: 1.5rem;">
                <div class="form-grid-2" style="margin-bottom: 1rem;">
                    <div>
                        <label class="form-label">{{ $isTr ? 'Okul Adı (TR)' : 'School Name (TR)' }} *</label>
                        <input type="text" name="name_tr"
                            value="{{ old('name_tr', $school->getTranslation('name', 'tr', false)) }}" required
                            class="form-input">
                        @error('name_tr') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">{{ $isTr ? 'Okul Adı (EN)' : 'School Name (EN)' }}</label>
                        <input type="text" name="name_en"
                            value="{{ old('name_en', $school->getTranslation('name', 'en', false)) }}" class="form-input">
                    </div>
                </div>
                <div class="form-grid-2" style="margin-bottom: 1rem;">
                    <div>
                        <label class="form-label">{{ $isTr ? 'Ülke' : 'Country' }}</label>
                        <input type="text" name="country" value="{{ old('country', $school->country) }}" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">{{ $isTr ? 'Şehir' : 'City' }}</label>
                        <input type="text" name="city" value="{{ old('city', $school->city) }}" class="form-input">
                    </div>
                </div>
                <div class="form-grid-2" style="margin-bottom: 1rem;">
                    <div>
                        <label class="form-label">{{ $isTr ? 'Telefon' : 'Phone' }}</label>
                        <input type="text" name="phone" value="{{ old('phone', $school->phone) }}" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">E-posta</label>
                        <input type="email" name="email" value="{{ old('email', $school->email) }}" class="form-input">
                    </div>
                </div>
                <div style="margin-bottom: 1rem;">
                    <label class="form-label">{{ $isTr ? 'Adres' : 'Address' }}</label>
                    <textarea name="address" class="form-textarea"
                        rows="2">{{ old('address', $school->address) }}</textarea>
                </div>
                <div style="margin-bottom: 1rem;">
                    <label class="form-label">Website</label>
                    <input type="url" name="website" value="{{ old('website', $school->website) }}" class="form-input"
                        placeholder="https://">
                </div>
                @if($school->exists)
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" {{ $school->is_active ? 'checked' : '' }}
                            style="accent-color: var(--brand-500);">
                        <span class="form-label" style="margin: 0;">{{ $isTr ? 'Aktif' : 'Active' }}</span>
                    </label>
                @endif
            </div>

            <div style="display: flex; gap: 0.75rem; align-items: center;">
                <button type="submit" class="btn-primary"
                    style="padding: 0.65rem 2rem;">{{ $isTr ? 'Kaydet' : 'Save' }}</button>
                <a href="{{ route('portal.schools.index') }}" class="btn btn-ghost">{{ $isTr ? 'İptal' : 'Cancel' }}</a>
                @if($school->exists)
                    <form action="{{ route('portal.schools.destroy', $school) }}" method="POST" style="margin-left: auto;"
                        onsubmit="return confirm('{{ $isTr ? 'Silmek istediğinize emin misiniz?' : 'Are you sure?' }}')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger">{{ $isTr ? 'Sil' : 'Delete' }}</button>
                    </form>
                @endif
            </div>
        </form>
    </div>
@endsection