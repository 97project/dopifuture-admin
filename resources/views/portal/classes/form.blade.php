@extends('portal.layout')
@section('title', $class->exists ? (app()->getLocale() === 'tr' ? 'Sınıf Düzenle' : 'Edit Class') : (app()->getLocale() === 'tr' ? 'Yeni Sınıf' : 'New Class'))
@php $isTr = app()->getLocale() === 'tr'; @endphp

@section('content')
    <div class="page-header">
        <h1>{{ $class->exists ? ($isTr ? 'Sınıf Düzenle' : 'Edit Class') : ($isTr ? 'Yeni Sınıf' : 'New Class') }}</h1>
        <p><a href="{{ route('portal.classes.index') }}" style="color: var(--brand-400); text-decoration: none;">←
                {{ $isTr ? 'Sınıflara Dön' : 'Back to Classes' }}</a></p>
    </div>

    <div style="max-width: 600px;">
        <form action="{{ $class->exists ? route('portal.classes.update', $class) : route('portal.classes.store') }}"
            method="POST">
            @csrf
            @if($class->exists) @method('PUT') @endif

            <div class="form-card" style="margin-bottom: 1.5rem;">
                <div style="margin-bottom: 1rem;">
                    <label class="form-label">{{ $isTr ? 'Okul' : 'School' }} *</label>
                    <select name="school_id" class="form-select" required>
                        <option value="">{{ $isTr ? 'Seçiniz' : 'Select' }}</option>
                        @foreach($schools as $school)
                            <option value="{{ $school->id }}" {{ old('school_id', $class->school_id) == $school->id ? 'selected' : '' }}>{{ $school->getTranslation('name') }}</option>
                        @endforeach
                    </select>
                    @error('school_id') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div class="form-grid-2" style="margin-bottom: 1rem;">
                    <div>
                        <label class="form-label">{{ $isTr ? 'Sınıf Adı' : 'Class Name' }} *</label>
                        <input type="text" name="name" value="{{ old('name', $class->name) }}" required class="form-input"
                            placeholder="{{ $isTr ? 'Ör: 10-A' : 'e.g. 10-A' }}">
                        @error('name') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">{{ $isTr ? 'Seviye' : 'Grade Level' }}</label>
                        <input type="text" name="grade_level" value="{{ old('grade_level', $class->grade_level) }}"
                            class="form-input" placeholder="10">
                    </div>
                </div>
                <div style="margin-bottom: 1rem;">
                    <label class="form-label">{{ $isTr ? 'Akademik Yıl' : 'Academic Year' }}</label>
                    <input type="text" name="academic_year"
                        value="{{ old('academic_year', $class->academic_year ?? '2025-2026') }}" class="form-input">
                </div>
                @if($class->exists)
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" {{ $class->is_active ? 'checked' : '' }}
                            style="accent-color: var(--brand-500);">
                        <span class="form-label" style="margin: 0;">{{ $isTr ? 'Aktif' : 'Active' }}</span>
                    </label>
                @endif
            </div>

            <div style="display: flex; gap: 0.75rem; align-items: center;">
                <button type="submit" class="btn-primary"
                    style="padding: 0.65rem 2rem;">{{ $isTr ? 'Kaydet' : 'Save' }}</button>
                <a href="{{ route('portal.classes.index') }}" class="btn btn-ghost">{{ $isTr ? 'İptal' : 'Cancel' }}</a>
                @if($class->exists)
                    <form action="{{ route('portal.classes.destroy', $class) }}" method="POST" style="margin-left: auto;"
                        onsubmit="return confirm('{{ $isTr ? 'Silmek istediğinize emin misiniz?' : 'Are you sure?' }}')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger">{{ $isTr ? 'Sil' : 'Delete' }}</button>
                    </form>
                @endif
            </div>
        </form>
    </div>
@endsection