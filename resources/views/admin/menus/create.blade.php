@extends('admin.layouts.app')
@section('title', __('admin.new_menu'))
@section('content')
    <div class="space-y-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('admin.new_menu') }}</h1>

        <form action="{{ route('admin.menus.store') }}" method="POST"
            class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.name') }}
                    *</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                    class="w-full px-3 py-2 border border-gray-200 dark:border-[#1A3A5C] rounded-lg bg-white dark:bg-[#0A1628] text-gray-900 dark:text-white">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.location') }}
                    *</label>
                <select name="location"
                    class="w-full px-3 py-2 border border-gray-200 dark:border-[#1A3A5C] rounded-lg bg-white dark:bg-[#0A1628]">
                    <option value="header">Header</option>
                    <option value="footer">Footer</option>
                    <option value="sidebar">Sidebar</option>
                </select>
            </div>
            <label class="flex items-center gap-2">
                <input type="checkbox" name="is_active" value="1" checked class="rounded border-gray-300 text-[#0B6AB2]">
                <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('admin.active') }}</span>
            </label>
            <div class="flex gap-3 pt-4 border-t border-gray-100 dark:border-[#1A3A5C]">
                <button type="submit"
                    class="px-6 py-2.5 bg-[#0B6AB2] hover:bg-[#13398E] text-white font-medium rounded-lg">{{ __('admin.save') }}</button>
                <a href="{{ route('admin.menus.index') }}"
                    class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-[#0A1628] text-gray-700 dark:text-gray-300 font-medium rounded-lg">{{ __('admin.cancel') }}</a>
            </div>
        </form>
    </div>
@endsection