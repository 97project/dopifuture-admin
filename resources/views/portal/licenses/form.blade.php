@extends('portal.app')
@section('title', $license->exists ? 'Edit License' : 'Add New License')
@section('page-title', $license->exists ? 'Edit License' : 'Add New License')
@section('content')
    {{-- ═══ Figma F-72: Add New Licence form — centered card ═══ --}}
    <div style="max-width:640px;margin:0 auto;">

        {{-- Title + Subtitle --}}
        <h2 style="font-size:24px;font-weight:700;margin:0 0 6px 0;color:#111;font-family:'Nunito',sans-serif;">
            {{ $license->exists ? 'Edit License' : 'Add New License' }}
        </h2>
        <p style="font-size:14px;color:#6B7280;margin:0 0 28px 0;">
            {{ $license->exists ? 'Update the license details below.' : 'Fill in the details below to create a new license.' }}
        </p>

        <form action="{{ $license->exists ? route('portal.licenses.update', $license) : route('portal.licenses.store') }}" method="POST">
            @csrf
            @if($license->exists) @method('PUT') @endif

            {{-- School Name --}}
            <div style="margin-bottom:20px;">
                <label style="font-size:14px;font-weight:600;color:#111;display:block;margin-bottom:6px;">School Name</label>
                <select name="school_id" style="width:100%;padding:14px 16px;border:1px solid #E5E7EB;border-radius:12px;background:#F8FAFC;font-size:14px;color:#374151;outline:none;font-family:inherit;appearance:none;" required>
                    <option value="">Select School</option>
                    @foreach($schools as $school)
                        <option value="{{ $school->id }}" {{ old('school_id', $license->school_id) == $school->id ? 'selected' : '' }}>{{ $school->name }}</option>
                    @endforeach
                </select>
                @error('school_id') <p style="color:#EF4444;font-size:12px;margin-top:4px;">{{ $message }}</p> @enderror
            </div>

            {{-- Country / State — side by side --}}
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px;">
                <div>
                    <label style="font-size:14px;font-weight:600;color:#111;display:block;margin-bottom:6px;">Country</label>
                    <select name="country" style="width:100%;padding:14px 16px;border:1px solid #E5E7EB;border-radius:12px;background:#F8FAFC;font-size:14px;color:#9CA3AF;outline:none;font-family:inherit;">
                        <option value="">Please select</option>
                        <option value="TR" {{ old('country', $license->country ?? '') === 'TR' ? 'selected' : '' }}>Turkey</option>
                        <option value="US" {{ old('country', $license->country ?? '') === 'US' ? 'selected' : '' }}>United States</option>
                    </select>
                </div>
                <div>
                    <label style="font-size:14px;font-weight:600;color:#111;display:block;margin-bottom:6px;">State</label>
                    <select name="state" style="width:100%;padding:14px 16px;border:1px solid #E5E7EB;border-radius:12px;background:#F8FAFC;font-size:14px;color:#9CA3AF;outline:none;font-family:inherit;">
                        <option value="">Please select</option>
                    </select>
                </div>
            </div>

            {{-- Products Checklist — Figma exact --}}
            <div style="margin-bottom:24px;">
                <label style="font-size:14px;font-weight:600;color:#111;display:block;margin-bottom:12px;">Which products would you like to add?</label>
                @foreach(['mission_way' => 'Mission WAY', 'startup' => 'Startup', 'role_galaxy' => 'Role Galaxy', 'study_space' => 'Study Space', 'way_ai_coach' => 'WAY AI Coach'] as $key => $label)
                <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 0;border-bottom:1px solid #F3F4F6;">
                    <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-size:15px;font-weight:500;color:#111;">
                        <input type="checkbox" name="products[]" value="{{ $key }}"
                            {{ in_array($key, old('products', $license->products ?? [])) ? 'checked' : '' }}
                            style="width:22px;height:22px;border-radius:6px;accent-color:#3B82F6;cursor:pointer;">
                        {{ $label }}
                    </label>
                    <select name="product_counts[{{ $key }}]" style="padding:8px 12px;border:1px solid #E5E7EB;border-radius:8px;background:#fff;font-size:13px;color:#9CA3AF;outline:none;font-family:inherit;">
                        <option value="">Number</option>
                        @for($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}" {{ old("product_counts.$key", $license->{"count_$key"} ?? '') == $i ? 'selected' : '' }}>{{ $i }}</option>
                        @endfor
                    </select>
                </div>
                @endforeach
            </div>

            {{-- License Duration / E-mail — side by side --}}
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:28px;">
                <div>
                    <label style="font-size:14px;font-weight:600;color:#111;display:block;margin-bottom:6px;">License Duration</label>
                    <select name="duration" style="width:100%;padding:14px 16px;border:1px solid #E5E7EB;border-radius:12px;background:#F8FAFC;font-size:14px;color:#9CA3AF;outline:none;font-family:inherit;">
                        <option value="">Please select</option>
                        <option value="6" {{ old('duration', $license->duration ?? '') == 6 ? 'selected' : '' }}>6 Months</option>
                        <option value="12" {{ old('duration', $license->duration ?? '') == 12 ? 'selected' : '' }}>12 Months</option>
                        <option value="24" {{ old('duration', $license->duration ?? '') == 24 ? 'selected' : '' }}>24 Months</option>
                    </select>
                </div>
                <div>
                    <label style="font-size:14px;font-weight:600;color:#111;display:block;margin-bottom:6px;">E-mail</label>
                    <input type="email" name="email" value="{{ old('email', $license->email ?? '') }}" placeholder="name@example.com"
                        style="width:100%;padding:14px 16px;border:1px solid #E5E7EB;border-radius:12px;background:#F8FAFC;font-size:14px;color:#374151;outline:none;font-family:inherit;box-sizing:border-box;">
                </div>
            </div>

            {{-- Full-width blue Submit Button --}}
            <button type="submit" style="width:100%;padding:16px;background:#1E3A8A;color:#fff;border:none;border-radius:12px;font-size:16px;font-weight:600;cursor:pointer;font-family:'Nunito',sans-serif;transition:background 0.2s;"
                onmouseover="this.style.background='#1E40AF'" onmouseout="this.style.background='#1E3A8A'">
                Save Changes
            </button>
        </form>
    </div>
@endsection