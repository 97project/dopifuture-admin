@extends('portal.app')
@section('title', $editUser->exists ? 'Edit User' : 'New User')
@section('page-title', $editUser->exists ? 'Edit User' : 'New User')

@section('content')
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
        <div style="font-size:18px;font-weight:600;">{{ $editUser->exists ? 'Edit User' : 'New User' }}</div>
        <a href="{{ route('portal.users.index') }}" class="dp-btn-ghost">← Back to Users</a>
    </div>

    <div style="max-width:600px;">
        <form action="{{ $editUser->exists ? route('portal.users.update', $editUser) : route('portal.users.store') }}" method="POST">
            @csrf
            @if($editUser->exists) @method('PUT') @endif

            <div class="dp-card">
                <div class="dp-form-grid" style="margin-bottom:16px;">
                    <div>
                        <label class="dp-form-label">First Name *</label>
                        <input type="text" name="name" value="{{ old('name', $editUser->name) }}" required class="dp-form-input">
                        @error('name') <p class="dp-form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="dp-form-label">Last Name</label>
                        <input type="text" name="surname" value="{{ old('surname', $editUser->surname) }}" class="dp-form-input">
                    </div>
                </div>
                <div class="dp-form-group">
                    <label class="dp-form-label">E-mail *</label>
                    <input type="email" name="email" value="{{ old('email', $editUser->email) }}" required class="dp-form-input">
                    @error('email') <p class="dp-form-error">{{ $message }}</p> @enderror
                </div>
                <div class="dp-form-group">
                    <label class="dp-form-label">Password {{ $editUser->exists ? '' : '*' }}</label>
                    <input type="password" name="password" class="dp-form-input" {{ $editUser->exists ? '' : 'required' }} placeholder="{{ $editUser->exists ? 'Fill to change' : '' }}">
                    @error('password') <p class="dp-form-error">{{ $message }}</p> @enderror
                </div>
                <div class="dp-form-grid" style="margin-bottom:16px;">
                    <div>
                        <label class="dp-form-label">Role *</label>
                        <select name="role" class="dp-form-select" required>
                            @foreach($roles as $role)
                                <option value="{{ $role }}" {{ old('role', $editUser->roles->first()?->name) === $role ? 'selected' : '' }}>{{ $role }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if($editUser->exists)
                    <div>
                        <label class="dp-form-label">Status *</label>
                        <select name="status" class="dp-form-select" required>
                            <option value="active" {{ $editUser->status === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ $editUser->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    @endif
                </div>
                @if(!$editUser->exists && isset($schools) && $schools->count())
                <div class="dp-form-group">
                    <label class="dp-form-label">Assign to School</label>
                    <select name="school_id" class="dp-form-select">
                        <option value="">Select (optional)</option>
                        @foreach($schools as $school)
                            <option value="{{ $school->id }}">{{ $school->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                @if(!$editUser->exists && isset($classes) && $classes->count())
                <div class="dp-form-group">
                    <label class="dp-form-label">Assign to Class</label>
                    <select name="class_id" class="dp-form-select">
                        <option value="">Select (optional)</option>
                        @foreach($classes as $cls)
                            <option value="{{ $cls->id }}">{{ $cls->name }} — {{ $cls->school?->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
            </div>

            <div style="display:flex;gap:12px;align-items:center;margin-top:16px;">
                <button type="submit" class="dp-btn">Save</button>
                <a href="{{ route('portal.users.index') }}" class="dp-btn-ghost">Cancel</a>
                @if($editUser->exists)
                    <form action="{{ route('portal.users.destroy', $editUser) }}" method="POST" style="margin-left:auto;" onsubmit="return confirm('Are you sure you want to delete this user?')">
                        @csrf @method('DELETE')
                        <button type="submit" style="background:var(--color-error-red);color:#fff;border:none;border-radius:8px;padding:8px 16px;font-size:13px;cursor:pointer;">Delete</button>
                    </form>
                @endif
            </div>
        </form>
    </div>
@endsection