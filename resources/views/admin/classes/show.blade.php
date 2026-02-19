@extends('admin.layouts.app')

@section('title', $class->name)

@section('breadcrumb')
    <div class="flex items-center gap-2 text-sm">
        <a href="{{ route('admin.dashboard') }}"
            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">{{ __('admin.dashboard') }}</a>
        <svg class="w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <a href="{{ route('admin.classes.index') }}"
            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">{{ __('admin.classes') }}</a>
        <svg class="w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-gray-900 dark:text-gray-100 font-semibold">{{ $class->name }}</span>
    </div>
@endsection

@section('content')
    <div class="space-y-6">
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ $class->name }}</h1>
                <div class="flex items-center gap-3 mt-1 text-xs text-gray-400">
                    @if($class->school) <span>📍 {{ $class->school->name }}</span> @endif
                    @if($class->grade_level) <span>• {{ $class->grade_level }}</span> @endif
                    @if($class->academic_year) <span>• {{ $class->academic_year }}</span> @endif
                </div>
            </div>
            @can('update', $class)
                <a href="{{ route('admin.classes.edit', $class) }}"
                    class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-white bg-[#0B6AB2] rounded-lg hover:bg-[#13398E] transition">{{ __('admin.edit') }}</a>
            @endcan
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
            <div
                class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-4 text-center">
                <p class="text-2xl font-extrabold text-[#0B6AB2]">{{ $class->students->count() }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ __('admin.students') }}</p>
            </div>
            <div
                class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-4 text-center">
                <p class="text-2xl font-extrabold text-emerald-500">{{ $class->teachers->count() }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ __('admin.teachers') }}</p>
            </div>
            <div
                class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-4 text-center">
                <p class="text-2xl font-extrabold {{ $class->is_active ? 'text-emerald-500' : 'text-red-500' }}">
                    {{ $class->is_active ? __('admin.active') : __('admin.inactive') }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ __('admin.status') }}</p>
            </div>
        </div>

        <div x-data="{ activeTab: 'students' }">
            <div class="border-b border-gray-200 dark:border-[#1A3A5C]">
                <nav class="flex gap-6 px-1 -mb-px">
                    <button @click="activeTab = 'students'"
                        :class="activeTab === 'students' ? 'border-[#0B6AB2] text-[#0B6AB2]' : 'border-transparent text-gray-500 hover:text-gray-700'"
                        class="py-3 px-1 text-sm font-medium border-b-2 transition">{{ __('admin.students') }}
                        ({{ $class->students->count() }})</button>
                    <button @click="activeTab = 'teachers'"
                        :class="activeTab === 'teachers' ? 'border-[#0B6AB2] text-[#0B6AB2]' : 'border-transparent text-gray-500 hover:text-gray-700'"
                        class="py-3 px-1 text-sm font-medium border-b-2 transition">{{ __('admin.teachers') }}
                        ({{ $class->teachers->count() }})</button>
                </nav>
            </div>

            @foreach(['students', 'teachers'] as $type)
                <div x-show="activeTab === '{{ $type }}'" {{ $type === 'teachers' ? 'x-cloak' : '' }} class="pt-6">
                    <div
                        class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] overflow-hidden">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-100 dark:border-[#1A3A5C]">
                                    <th
                                        class="text-left px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                                        {{ __('admin.name') }}</th>
                                    <th
                                        class="text-left px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                                        {{ __('admin.email') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 dark:divide-[#1A3A5C]/50">
                                @forelse($class->$type as $user)
                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-[#0A1628]/30 transition">
                                        <td class="px-5 py-3 font-medium text-gray-900 dark:text-white">{{ $user->name }}</td>
                                        <td class="px-5 py-3 text-gray-500">{{ $user->email }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="px-5 py-8 text-center text-gray-400">{{ __('admin.no_data') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection