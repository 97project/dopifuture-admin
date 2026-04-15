@extends('portal.app')
@section('title', $class->exists ? 'Edit Class' : 'New Class')
@section('page-title', $class->exists ? 'Edit Class' : 'New Class')

@section('content')
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
        <div style="font-size:18px;font-weight:600;">{{ $class->exists ? 'Edit Class' : 'New Class' }}</div>
        <a href="{{ route('portal.classes.index') }}" class="dp-btn-ghost">← Back to Classes</a>
    </div>

    <div>
        <form action="{{ $class->exists ? route('portal.classes.update', $class) : route('portal.classes.store') }}" method="POST">
            @csrf
            @if($class->exists) @method('PUT') @endif

            <div class="dp-card">
                <div class="dp-form-group">
                    <label class="dp-form-label">{{ __('admin.school_name') }} *</label>
                    <select name="school_id" class="dp-form-select" required>
                        <option value="">Select</option>
                        @foreach($schools as $school)
                            <option value="{{ $school->id }}" {{ old('school_id', $class->school_id) == $school->id ? 'selected' : '' }}>{{ $school->name }}</option>
                        @endforeach
                    </select>
                    @error('school_id') <p class="dp-form-error">{{ $message }}</p> @enderror
                </div>
                <div class="dp-form-grid" style="margin-bottom:16px;">
                    <div>
                        <label class="dp-form-label">{{ __('portal.class_name') }} *</label>
                        <input type="text" name="name" value="{{ old('name', $class->name) }}" required class="dp-form-input" placeholder="e.g. 10-A">
                        @error('name') <p class="dp-form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="dp-form-label">{{ __('portal.grade_level') }}</label>
                        <input type="text" name="grade_level" value="{{ old('grade_level', $class->grade_level) }}" class="dp-form-input" placeholder="10">
                    </div>
                </div>
                <div class="dp-form-group">
                    <label class="dp-form-label">{{ __('portal.academic_year') }}</label>
                    <select name="academic_year" class="dp-form-select">
                        <option value="">Select</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year }}" {{ old('academic_year', $class->academic_year) === $year ? 'selected' : '' }}>{{ $year }}</option>
                        @endforeach
                    </select>
                </div>
                @if($class->exists)
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" {{ $class->is_active ? 'checked' : '' }} style="accent-color:var(--primary);">
                        <span class="dp-form-label" style="margin:0;">{{ __('portal.active') }}</span>
                    </label>
                @endif
            </div>

            <div style="display:flex;gap:12px;align-items:center;margin-top:16px;">
                <button type="submit" class="dp-btn">{{ __('admin.save') }}</button>
                <a href="{{ route('portal.classes.index') }}" class="dp-btn-ghost">{{ __('portal.cancel') }}</a>
            </div>
        </form>

        @if($class->exists)
            <form action="{{ route('portal.classes.destroy', $class) }}" method="POST" style="margin-top:24px;padding-top:24px;border-top:1px solid var(--color-row-border);" onsubmit="return confirm('Are you sure you want to delete this class? This action cannot be undone.')">
                @csrf @method('DELETE')
                <button type="submit" style="background:var(--color-error-red, #e33131);color:#fff;border:none;border-radius:8px;padding:8px 16px;font-size:13px;cursor:pointer;">Delete This Class</button>
            </form>
        @endif
    </div>
@endsection