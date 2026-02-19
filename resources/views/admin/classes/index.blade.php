@extends('admin.layouts.app')

@section('title', __('admin.classes'))

@section('breadcrumb')
    <div class="flex items-center gap-2 text-sm">
        <a href="{{ route('admin.dashboard') }}"
            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">{{ __('admin.dashboard') }}</a>
        <svg class="w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-gray-900 dark:text-gray-100 font-semibold">{{ __('admin.classes') }}</span>
    </div>
@endsection

@section('content')
    <div class="space-y-6">

        {{-- Stats --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div
                class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5 flex items-center gap-4">
                <div class="w-11 h-11 rounded-lg bg-[#13398E]/10 flex items-center justify-center"><svg
                        class="w-5 h-5 text-[#13398E]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0H5m14 0h2M5 21H3m4-4h4m4 0h.01" />
                    </svg></div>
                <div>
                    <p class="text-2xl font-extrabold text-gray-900 dark:text-white">{{ $stats['total'] }}</p>
                    <p class="text-xs text-gray-400">{{ __('admin.total_classes') }}</p>
                </div>
            </div>
            <div
                class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5 flex items-center gap-4">
                <div class="w-11 h-11 rounded-lg bg-[#0B6AB2]/10 flex items-center justify-center"><svg
                        class="w-5 h-5 text-[#0B6AB2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg></div>
                <div>
                    <p class="text-2xl font-extrabold text-gray-900 dark:text-white">{{ $stats['total_students'] }}</p>
                    <p class="text-xs text-gray-400">{{ __('admin.total_students') }}</p>
                </div>
            </div>
            <div
                class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5 flex items-center gap-4">
                <div class="w-11 h-11 rounded-lg bg-emerald-500/10 flex items-center justify-center"><svg
                        class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg></div>
                <div>
                    <p class="text-2xl font-extrabold text-gray-900 dark:text-white">{{ $stats['total_teachers'] }}</p>
                    <p class="text-xs text-gray-400">{{ __('admin.total_teachers') }}</p>
                </div>
            </div>
        </div>

        {{-- Filters --}}
        <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-4">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-3">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('admin.search') }}..."
                    class="rounded-lg border-gray-200 dark:border-[#1A3A5C] dark:bg-[#0A1628] text-sm focus:ring-[#0B6AB2] focus:border-[#0B6AB2]">
                <select name="school_id" class="rounded-lg border-gray-200 dark:border-[#1A3A5C] dark:bg-[#0A1628] text-sm">
                    <option value="">{{ __('admin.all_schools') }}</option>
                    @foreach($schools as $school)<option value="{{ $school->id }}" {{ request('school_id') == $school->id ? 'selected' : '' }}>{{ $school->name }}</option>@endforeach
                </select>
                <select name="grade_level"
                    class="rounded-lg border-gray-200 dark:border-[#1A3A5C] dark:bg-[#0A1628] text-sm">
                    <option value="">{{ __('admin.all_grades') }}</option>
                    @foreach($grades as $g)<option value="{{ $g }}" {{ request('grade_level') == $g ? 'selected' : '' }}>
                    {{ $g }}</option>@endforeach
                </select>
                <select name="academic_year"
                    class="rounded-lg border-gray-200 dark:border-[#1A3A5C] dark:bg-[#0A1628] text-sm">
                    <option value="">{{ __('admin.all_years') }}</option>
                    @foreach($years as $y)<option value="{{ $y }}" {{ request('academic_year') == $y ? 'selected' : '' }}>
                    {{ $y }}</option>@endforeach
                </select>
                <div class="flex gap-2">
                    <button type="submit"
                        class="flex-1 px-4 py-2 bg-[#0B6AB2] text-white rounded-lg text-sm hover:bg-[#13398E] transition">{{ __('admin.filter') }}</button>
                    <a href="{{ route('admin.classes.index') }}"
                        class="px-3 py-2 border border-gray-200 dark:border-[#1A3A5C] rounded-lg text-sm hover:bg-gray-50 dark:hover:bg-[#0A1628] transition">✕</a>
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] overflow-hidden">
            <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100 dark:border-[#1A3A5C]">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white">{{ __('admin.classes') }} <span
                        class="text-gray-400 font-normal">({{ $classes->total() }})</span></h3>
                @can('create', App\Models\SchoolClass::class)
                    <a href="{{ route('admin.classes.create') }}"
                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-[#0B6AB2] text-white rounded-lg text-xs font-medium hover:bg-[#13398E] transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        {{ __('admin.add') }}
                    </a>
                @endcan
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-[#1A3A5C]">
                        @php $s = request('sort');
                        $d = request('dir'); @endphp
                        @foreach(['name' => __('admin.class_name'), 'students_count' => __('admin.students'), 'teachers_count' => __('admin.teachers')] as $col => $label)
                            <th class="text-left px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => $col, 'dir' => ($s === $col && $d === 'asc') ? 'desc' : 'asc']) }}"
                                    class="inline-flex items-center gap-1 hover:text-gray-700 dark:hover:text-gray-200">
                                    {{ $label }}
                                    @if($s === $col) <svg class="w-3 h-3 {{ $d === 'asc' ? '' : 'rotate-180' }}"
                                        fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                                    </svg> @endif
                                </a>
                            </th>
                        @endforeach
                        <th class="text-left px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                            {{ __('admin.school') }}</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                            {{ __('admin.grade') }}</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                            {{ __('admin.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-[#1A3A5C]/50">
                    @forelse($classes as $class)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-[#0A1628]/30 transition">
                            <td class="px-5 py-3 font-medium text-gray-900 dark:text-white">{{ $class->name }}</td>
                            <td class="px-5 py-3"><span
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-blue-50 dark:bg-blue-900/20 text-blue-600">{{ $class->students_count }}</span>
                            </td>
                            <td class="px-5 py-3"><span
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600">{{ $class->teachers_count }}</span>
                            </td>
                            <td class="px-5 py-3 text-xs text-gray-500">{{ $class->school?->name ?? '—' }}</td>
                            <td class="px-5 py-3 text-xs text-gray-500">{{ $class->grade_level ?? '—' }}</td>
                            <td class="px-5 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.classes.show', $class) }}"
                                        class="text-xs text-[#0B6AB2] hover:underline">{{ __('admin.view') }}</a>
                                    @can('update', $class)<a href="{{ route('admin.classes.edit', $class) }}"
                                    class="text-xs text-gray-500 hover:underline">{{ __('admin.edit') }}</a>@endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-gray-400">{{ __('admin.no_data') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if($classes->hasPages())
                <div class="px-5 py-3 border-t border-gray-100 dark:border-[#1A3A5C]">{{ $classes->links() }}</div>
            @endif
        </div>
    </div>
@endsection