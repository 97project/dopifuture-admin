@extends('portal.layout')
@section('title', $license->exists ? (app()->getLocale() === 'tr' ? 'Lisans Düzenle' : 'Edit License') : (app()->getLocale() === 'tr' ? 'Yeni Lisans' : 'New License'))
@php $isTr = app()->getLocale() === 'tr'; @endphp

@section('content')
    <div class="page-header">
        <h1>{{ $license->exists ? ($isTr ? 'Lisans Düzenle' : 'Edit License') : ($isTr ? 'Yeni Lisans' : 'New License') }}
        </h1>
        <p><a href="{{ route('portal.licenses.index') }}" style="color: var(--brand-400); text-decoration: none;">←
                {{ $isTr ? 'Lisanslara Dön' : 'Back to Licenses' }}</a></p>
    </div>

    <div style="max-width: 600px;">
        <form action="{{ $license->exists ? route('portal.licenses.update', $license) : route('portal.licenses.store') }}"
            method="POST">
            @csrf
            @if($license->exists) @method('PUT') @endif

            <div class="form-card" style="margin-bottom: 1.5rem;">
                <div style="margin-bottom: 1rem;">
                    <label class="form-label">{{ $isTr ? 'Okul' : 'School' }} *</label>
                    <select name="school_id" class="form-select" required>
                        <option value="">{{ $isTr ? 'Seçiniz' : 'Select' }}</option>
                        @foreach($schools as $school)
                            <option value="{{ $school->id }}" {{ old('school_id', $license->school_id) == $school->id ? 'selected' : '' }}>{{ $school->getTranslation('name') }}</option>
                        @endforeach
                    </select>
                    @error('school_id') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div class="form-grid-2" style="margin-bottom: 1rem;">
                    <div>
                        <label class="form-label">{{ $isTr ? 'Koltuk Sayısı' : 'Seat Count' }} *</label>
                        <input type="number" name="seat_count" value="{{ old('seat_count', $license->seat_count ?? 50) }}"
                            min="1" required class="form-input">
                        @error('seat_count') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    @if($license->exists)
                        <div>
                            <label class="form-label">{{ $isTr ? 'Kullanılan' : 'Used Seats' }}</label>
                            <input type="number" name="used_seats" value="{{ old('used_seats', $license->used_seats) }}" min="0"
                                class="form-input">
                        </div>
                    @endif
                </div>
                <div class="form-grid-2" style="margin-bottom: 1rem;">
                    <div>
                        <label class="form-label">{{ $isTr ? 'Başlangıç' : 'Start Date' }} *</label>
                        <input type="date" name="starts_at"
                            value="{{ old('starts_at', $license->starts_at?->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
                            required class="form-input">
                        @error('starts_at') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">{{ $isTr ? 'Bitiş' : 'Expiry Date' }} *</label>
                        <input type="date" name="expires_at"
                            value="{{ old('expires_at', $license->expires_at?->format('Y-m-d') ?? now()->addYear()->format('Y-m-d')) }}"
                            required class="form-input">
                        @error('expires_at') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div style="margin-bottom: 1rem;">
                    <label class="form-label">{{ $isTr ? 'Notlar' : 'Notes' }}</label>
                    <textarea name="notes" class="form-textarea" rows="2">{{ old('notes', $license->notes) }}</textarea>
                </div>
                @if($license->exists)
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" {{ $license->is_active ? 'checked' : '' }}
                            style="accent-color: var(--brand-500);">
                        <span class="form-label" style="margin: 0;">{{ $isTr ? 'Aktif' : 'Active' }}</span>
                    </label>
                @endif
            </div>

            <div style="display: flex; gap: 0.75rem; align-items: center;">
                <button type="submit" class="btn-primary"
                    style="padding: 0.65rem 2rem;">{{ $isTr ? 'Kaydet' : 'Save' }}</button>
                <a href="{{ route('portal.licenses.index') }}" class="btn btn-ghost">{{ $isTr ? 'İptal' : 'Cancel' }}</a>
                @if($license->exists)
                    <form action="{{ route('portal.licenses.destroy', $license) }}" method="POST" style="margin-left: auto;"
                        onsubmit="return confirm('{{ $isTr ? 'Silmek istediğinize emin misiniz?' : 'Are you sure?' }}')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger">{{ $isTr ? 'Sil' : 'Delete' }}</button>
                    </form>
                @endif
            </div>
        </form>
    </div>
@endsection