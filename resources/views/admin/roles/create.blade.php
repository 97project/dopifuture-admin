@extends('admin.layouts.app')
@section('title', isset($role) ? __('admin.edit_role') : __('admin.new_role'))
@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">{{ isset($role) ? __('admin.edit_role') . ': ' . $role->name : __('admin.new_role') }}</h1>

    <form action="{{ isset($role) ? route('admin.roles.update', $role) : route('admin.roles.store') }}" method="POST"
          class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-6 space-y-5">
        @csrf
        @if(isset($role)) @method('PUT') @endif

        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.name') }} *</label>
            <input type="text" id="name" name="name" value="{{ old('name', $role->name ?? '') }}" required
                   class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628] text-sm focus:ring-[#0B6AB2] focus:border-[#0B6AB2] @error('name') border-red-500 @enderror">
            @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">{{ __('admin.permissions') }}</label>
            @foreach($permissions as $module => $perms)
            <div class="mb-4">
                <h4 class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">{{ $module }}</h4>
                <div class="flex flex-wrap gap-3">
                    @foreach($perms as $perm)
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="permissions[]" value="{{ $perm->id }}" class="rounded border-gray-300 text-[#0B6AB2] focus:ring-[#0B6AB2]"
                               {{ in_array($perm->id, old('permissions', $rolePermissions ?? [])) ? 'checked' : '' }}>
                        {{ $perm->name }}
                    </label>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>

        <div class="flex items-center gap-3 pt-4 border-t border-gray-100 dark:border-[#1A3A5C]">
            <button type="submit" class="px-6 py-2 bg-[#0B6AB2] hover:bg-[#13398E] text-white text-sm font-medium rounded-lg transition-colors">{{ __('admin.save') }}</button>
            <a href="{{ route('admin.roles.index') }}" class="px-6 py-2 text-sm text-gray-600 hover:text-gray-900 transition-colors">{{ __('admin.cancel') }}</a>
        </div>
    </form>
</div>
@endsection
