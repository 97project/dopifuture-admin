@extends('portal.app')
@section('title', $class->exists ? (app()->getLocale() === 'tr' ? 'Sınıf Düzenle' : 'Edit Class') : (app()->getLocale() === 'tr' ? 'Yeni Sınıf' : 'New Class'))
@section('page-title', $class->exists ? (app()->getLocale() === 'tr' ? 'Sınıf Düzenle' : 'Edit Class') : (app()->getLocale() === 'tr' ? 'Yeni Sınıf' : 'New Class'))
@php $isTr = app()->getLocale() === 'tr'; @endphp

@section('content')
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
        <div style="font-size:18px;font-weight:600;">{{ $class->exists ? ($isTr ? 'Sınıf Düzenle' : 'Edit Class') : ($isTr ? 'Yeni Sınıf' : 'New Class') }}</div>
        <a href="{{ route('portal.classes.index') }}" class="dp-btn-ghost">← {{ $isTr ? 'Sınıflara Dön' : 'Back to Classes' }}</a>
    </div>

    <div style="max-width:600px;">
        <form action="{{ $class->exists ? route('portal.classes.update', $class) : route('portal.classes.store') }}" method="POST">
            @csrf
            @if($class->exists) @method('PUT') @endif

            <div class="dp-card">
                <div class="dp-form-group">
                    <label class="dp-form-label">{{ $isTr ? 'Okul' : 'School' }} *</label>
                    <select name="school_id" class="dp-form-select" required>
                        <option value="">{{ $isTr ? 'Seçiniz' : 'Select' }}</option>
                        @foreach($schools as $school)
                            <option value="{{ $school->id }}" {{ old('school_id', $class->school_id) == $school->id ? 'selected' : '' }}>{{ $school->name }}</option>
                        @endforeach
                    </select>
                    @error('school_id') <p class="dp-form-error">{{ $message }}</p> @enderror
                </div>
                <div class="dp-form-grid" style="margin-bottom:16px;">
                    <div>
                        <label class="dp-form-label">{{ $isTr ? 'Sınıf Adı' : 'Class Name' }} *</label>
                        <input type="text" name="name" value="{{ old('name', $class->name) }}" required class="dp-form-input" placeholder="{{ $isTr ? 'Ör: 10-A' : 'e.g. 10-A' }}">
                        @error('name') <p class="dp-form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="dp-form-label">{{ $isTr ? 'Seviye' : 'Grade Level' }}</label>
                        <input type="text" name="grade_level" value="{{ old('grade_level', $class->grade_level) }}" class="dp-form-input" placeholder="10">
                    </div>
                </div>
                <div class="dp-form-group">
                    <label class="dp-form-label">{{ $isTr ? 'Akademik Yıl' : 'Academic Year' }}</label>
                    <input type="text" name="academic_year" value="{{ old('academic_year', $class->academic_year ?? '2025-2026') }}" class="dp-form-input">
                </div>
                @if($class->exists)
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" {{ $class->is_active ? 'checked' : '' }} style="accent-color:var(--primary);">
                        <span class="dp-form-label" style="margin:0;">{{ $isTr ? 'Aktif' : 'Active' }}</span>
                    </label>
                @endif
            </div>

            <div style="display:flex;gap:12px;align-items:center;margin-top:16px;">
                <button type="submit" class="dp-btn">{{ $isTr ? 'Kaydet' : 'Save' }}</button>
                <a href="{{ route('portal.classes.index') }}" class="dp-btn-ghost">{{ $isTr ? 'İptal' : 'Cancel' }}</a>
                @if($class->exists)
                    <form action="{{ route('portal.classes.destroy', $class) }}" method="POST" style="margin-left:auto;" onsubmit="return confirm('{{ $isTr ? 'Silmek istediğinize emin misiniz?' : 'Are you sure?' }}')">
                        @csrf @method('DELETE')
                        <button type="submit" style="background:var(--error-red);color:#fff;border:none;border-radius:8px;padding:8px 16px;font-size:13px;cursor:pointer;">{{ $isTr ? 'Sil' : 'Delete' }}</button>
                    </form>
                @endif
            </div>
        </form>
    </div>
@endsection