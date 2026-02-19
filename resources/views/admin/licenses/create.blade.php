@extends('admin.layouts.app')
@section('title', isset($license) ? __('admin.edit_license') : __('admin.new_license'))
@section('content')
    <div class="space-y-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
            {{ isset($license) ? __('admin.edit_license') : __('admin.new_license') }}
        </h1>
        <form action="{{ isset($license) ? route('admin.licenses.update', $license) : route('admin.licenses.store') }}"
            method="POST"
            class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-6 space-y-5">
            @csrf @if(isset($license)) @method('PUT') @endif
            <div><label class="block text-sm font-medium mb-1">{{ __('admin.school') }} *</label>
                <select name="school_id" required
                    class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628] text-sm">
                    <option value="">— {{ __('admin.select') }} —</option>
                    @foreach($schools as $s)<option value="{{ $s->id }}" {{ old('school_id', $license->school_id ?? '') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>@endforeach
                </select>
            </div>
            <div><label class="block text-sm font-medium mb-1">{{ __('admin.seat_count') }} *</label><input type="number"
                    name="seat_count" value="{{ old('seat_count', $license->seat_count ?? '') }}" required min="1"
                    class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628] text-sm">
            </div>
            <div class="grid grid-cols-2 gap-5">
                <div><label class="block text-sm font-medium mb-1">{{ __('admin.starts_at') }}</label><input type="date"
                        name="starts_at"
                        value="{{ old('starts_at', optional($license->starts_at ?? null)?->format('Y-m-d')) }}"
                        class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628] text-sm">
                </div>
                <div><label class="block text-sm font-medium mb-1">{{ __('admin.expires_at') }}</label><input type="date"
                        name="expires_at"
                        value="{{ old('expires_at', optional($license->expires_at ?? null)?->format('Y-m-d')) }}"
                        class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628] text-sm">
                </div>
            </div>
            <div><label class="block text-sm font-medium mb-1">{{ __('admin.notes') }}</label><textarea name="notes"
                    rows="3"
                    class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628] text-sm">{{ old('notes', $license->notes ?? '') }}</textarea>
            </div>
            <div><label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1"
                        class="rounded border-gray-300 text-[#0B6AB2]" {{ old('is_active', $license->is_active ?? true) ? 'checked' : '' }}> {{ __('admin.active') }}</label></div>
            <div class="flex items-center gap-3 pt-4 border-t"><button type="submit"
                    class="px-6 py-2 bg-[#0B6AB2] hover:bg-[#13398E] text-white text-sm font-medium rounded-lg">{{ __('admin.save') }}</button><a
                    href="{{ route('admin.licenses.index') }}"
                    class="px-6 py-2 text-sm text-gray-600 hover:text-gray-900">{{ __('admin.cancel') }}</a></div>
        </form>
    </div>
@endsection