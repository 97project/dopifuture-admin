@extends('admin.layouts.app')
@section('title', isset($class) ? __('admin.edit_class') : __('admin.new_class'))
@section('content')
    <div class="space-y-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
            {{ isset($class) ? __('admin.edit_class') : __('admin.new_class') }}
        </h1>
        <form action="{{ isset($class) ? route('admin.classes.update', $class) : route('admin.classes.store') }}"
            method="POST"
            class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-6 space-y-5">
            @csrf @if(isset($class)) @method('PUT') @endif
            <div><label class="block text-sm font-medium mb-1">{{ __('admin.school') }} *</label>
                <select name="school_id" required
                    class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628] text-sm">
                    <option value="">— {{ __('admin.select') }} —</option>
                    @foreach($schools as $s)<option value="{{ $s->id }}" {{ old('school_id', $class->school_id ?? '') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>@endforeach
                </select>
            </div>
            <div><label class="block text-sm font-medium mb-1">{{ __('admin.name') }} *</label><input type="text"
                    name="name" value="{{ old('name', $class->name ?? '') }}" required
                    class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628] text-sm">
            </div>
            <div class="grid grid-cols-2 gap-5">
                <div><label class="block text-sm font-medium mb-1">{{ __('admin.grade_level') }}</label><input type="text"
                        name="grade_level" value="{{ old('grade_level', $class->grade_level ?? '') }}" placeholder="5A"
                        class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628] text-sm">
                </div>
                <div><label class="block text-sm font-medium mb-1">{{ __('admin.academic_year') }}</label><input type="text"
                        name="academic_year" value="{{ old('academic_year', $class->academic_year ?? '') }}"
                        placeholder="2025-2026"
                        class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628] text-sm">
                </div>
            </div>
            <div><label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1"
                        class="rounded border-gray-300 text-[#0B6AB2]" {{ old('is_active', $class->is_active ?? true) ? 'checked' : '' }}> {{ __('admin.active') }}</label></div>
            <div class="flex items-center gap-3 pt-4 border-t"><button type="submit"
                    class="px-6 py-2 bg-[#0B6AB2] hover:bg-[#13398E] text-white text-sm font-medium rounded-lg">{{ __('admin.save') }}</button><a
                    href="{{ route('admin.classes.index') }}"
                    class="px-6 py-2 text-sm text-gray-600 hover:text-gray-900">{{ __('admin.cancel') }}</a></div>
        </form>
    </div>
@endsection