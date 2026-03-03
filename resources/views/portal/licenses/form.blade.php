@extends('portal.app')
@section('title', $license->exists ? (app()->getLocale() === 'tr' ? 'Lisans Düzenle' : 'Edit License') : (app()->getLocale() === 'tr' ? 'Yeni Lisans' : 'New License'))
@section('page-title', $license->exists ? (app()->getLocale() === 'tr' ? 'Lisans Düzenle' : 'Edit License') : (app()->getLocale() === 'tr' ? 'Yeni Lisans' : 'New License'))
@php $isTr = app()->getLocale() === 'tr'; @endphp

@section('content')
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
        <div style="font-size:18px;font-weight:600;">{{ $license->exists ? ($isTr ? 'Lisans Düzenle' : 'Edit License') : ($isTr ? 'Yeni Lisans' : 'New License') }}</div>
        <a href="{{ route('portal.licenses.index') }}" class="dp-btn-ghost">← {{ $isTr ? 'Lisanslara Dön' : 'Back to Licenses' }}</a>
    </div>

    <div style="max-width:600px;">
        <form action="{{ $license->exists ? route('portal.licenses.update', $license) : route('portal.licenses.store') }}" method="POST">
            @csrf
            @if($license->exists) @method('PUT') @endif

            <div class="dp-card">
                <div class="dp-form-group">
                    <label class="dp-form-label">{{ $isTr ? 'Okul' : 'School' }} *</label>
                    <select name="school_id" class="dp-form-select" required>
                        <option value="">{{ $isTr ? 'Seçiniz' : 'Select' }}</option>
                        @foreach($schools as $school)
                            <option value="{{ $school->id }}" {{ old('school_id', $license->school_id) == $school->id ? 'selected' : '' }}>{{ $school->name }}</option>
                        @endforeach
                    </select>
                    @error('school_id') <p class="dp-form-error">{{ $message }}</p> @enderror
                </div>
                <div class="dp-form-grid" style="margin-bottom:16px;">
                    <div>
                        <label class="dp-form-label">{{ $isTr ? 'Koltuk Sayısı' : 'Seat Count' }} *</label>
                        <input type="number" name="seat_count" value="{{ old('seat_count', $license->seat_count ?? 50) }}" min="1" required class="dp-form-input">
                        @error('seat_count') <p class="dp-form-error">{{ $message }}</p> @enderror
                    </div>
                    @if($license->exists)
                    <div>
                        <label class="dp-form-label">{{ $isTr ? 'Kullanılan' : 'Used Seats' }}</label>
                        <input type="number" name="used_seats" value="{{ old('used_seats', $license->used_seats) }}" min="0" class="dp-form-input">
                    </div>
                    @endif
                </div>
                <div class="dp-form-grid" style="margin-bottom:16px;">
                    <div>
                        <label class="dp-form-label">{{ $isTr ? 'Başlangıç' : 'Start Date' }} *</label>
                        <input type="date" name="starts_at" value="{{ old('starts_at', $license->starts_at?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required class="dp-form-input">
                        @error('starts_at') <p class="dp-form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="dp-form-label">{{ $isTr ? 'Bitiş' : 'Expiry Date' }} *</label>
                        <input type="date" name="expires_at" value="{{ old('expires_at', $license->expires_at?->format('Y-m-d') ?? now()->addYear()->format('Y-m-d')) }}" required class="dp-form-input">
                        @error('expires_at') <p class="dp-form-error">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="dp-form-group">
                    <label class="dp-form-label">{{ $isTr ? 'Notlar' : 'Notes' }}</label>
                    <textarea name="notes" class="dp-form-textarea" rows="2">{{ old('notes', $license->notes) }}</textarea>
                </div>
                @if($license->exists)
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" {{ $license->is_active ? 'checked' : '' }} style="accent-color:var(--primary);">
                        <span class="dp-form-label" style="margin:0;">{{ $isTr ? 'Aktif' : 'Active' }}</span>
                    </label>
                @endif
            </div>

            <div style="display:flex;gap:12px;align-items:center;margin-top:16px;">
                <button type="submit" class="dp-btn">{{ $isTr ? 'Kaydet' : 'Save' }}</button>
                <a href="{{ route('portal.licenses.index') }}" class="dp-btn-ghost">{{ $isTr ? 'İptal' : 'Cancel' }}</a>
                @if($license->exists)
                    <form action="{{ route('portal.licenses.destroy', $license) }}" method="POST" style="margin-left:auto;" onsubmit="return confirm('{{ $isTr ? 'Silmek istediğinize emin misiniz?' : 'Are you sure?' }}')">
                        @csrf @method('DELETE')
                        <button type="submit" style="background:var(--error-red);color:#fff;border:none;border-radius:8px;padding:8px 16px;font-size:13px;cursor:pointer;">{{ $isTr ? 'Sil' : 'Delete' }}</button>
                    </form>
                @endif
            </div>
        </form>
    </div>
@endsection