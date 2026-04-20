@extends('admin.layouts.app')

@section('title', __('admin.schools'))

@section('breadcrumb')
    <div class="flex items-center gap-2 text-sm">
        <a href="{{ route('admin.dashboard') }}"
            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">{{ __('admin.dashboard') }}</a>
        <svg class="w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-gray-900 dark:text-gray-100 font-semibold">{{ __('admin.schools') }}</span>
    </div>
@endsection

@section('content')
    <div class="space-y-6">

        {{-- Stats --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-4">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">{{ __('admin.total') }}</p>
                <p class="text-2xl font-extrabold text-gray-900 dark:text-white mt-1 tabular-nums">
                    {{ number_format($stats['total']) }}</p>
            </div>
            <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-4">
                <p class="text-xs font-semibold uppercase tracking-wider text-emerald-500">{{ __('admin.active') }}</p>
                <p class="text-2xl font-extrabold text-gray-900 dark:text-white mt-1 tabular-nums">
                    {{ number_format($stats['active']) }}</p>
            </div>
            <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-4">
                <p class="text-xs font-semibold uppercase tracking-wider text-red-400">{{ __('admin.inactive') }}</p>
                <p class="text-2xl font-extrabold text-gray-900 dark:text-white mt-1 tabular-nums">
                    {{ number_format($stats['inactive']) }}</p>
            </div>
            <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-4">
                <p class="text-xs font-semibold uppercase tracking-wider text-[#0B6AB2]">{{ __('admin.total_students') }}
                </p>
                <p class="text-2xl font-extrabold text-gray-900 dark:text-white mt-1 tabular-nums">
                    {{ number_format($stats['total_students']) }}</p>
            </div>
        </div>

        {{-- Filters & Actions --}}
        <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-4">
            <form method="GET" action="{{ route('admin.schools.index') }}" class="flex flex-wrap items-center gap-3">
                {{-- Search --}}
                <div class="flex-1 min-w-[200px]">
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="{{ __('admin.search') }}..."
                        class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-gray-50 dark:bg-[#0A1628] text-gray-900 dark:text-gray-100 focus:border-[#0B6AB2] focus:ring-1 focus:ring-[#0B6AB2] outline-none transition">
                </div>

                {{-- City filter --}}
                <select name="city"
                    class="px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-gray-50 dark:bg-[#0A1628] text-gray-900 dark:text-gray-100 focus:border-[#0B6AB2] outline-none min-w-[140px]">
                    <option value="">{{ __('admin.all_cities') }}</option>
                    @foreach($cities as $city)
                        <option value="{{ $city }}" {{ request('city') == $city ? 'selected' : '' }}>{{ $city }}</option>
                    @endforeach
                </select>

                {{-- Status filter --}}
                <select name="status"
                    class="px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-gray-50 dark:bg-[#0A1628] text-gray-900 dark:text-gray-100 focus:border-[#0B6AB2] outline-none min-w-[120px]">
                    <option value="">{{ __('admin.all_statuses') }}</option>
                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>{{ __('admin.active') }}</option>
                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>{{ __('admin.inactive') }}</option>
                </select>

                <button type="submit"
                    class="px-4 py-2 text-sm font-medium text-white bg-[#0B6AB2] rounded-lg hover:bg-[#13398E] transition">
                    {{ __('admin.filter') }}
                </button>

                @if(request()->hasAny(['search', 'city', 'status']))
                    <a href="{{ route('admin.schools.index') }}"
                        class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 transition">{{ __('admin.clear') }}</a>
                @endif

                <div class="ml-auto">
                    @can('create', App\Models\School::class)
                        <a href="{{ route('admin.schools.create') }}"
                            class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-white bg-emerald-500 rounded-lg hover:bg-emerald-600 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            {{ __('admin.add_school') }}
                        </a>
                    @endcan
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-[#1A3A5C]">
                            @php
                                $sortIcon = function ($col) {
                                    $dir = request('sort') === $col && request('dir') === 'asc' ? 'desc' : 'asc';
                                    $active = request('sort') === $col;
                                    return ['dir' => $dir, 'active' => $active];
                                };
                            @endphp
                            <th class="text-left px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'name', 'dir' => $sortIcon('name')['dir']]) }}"
                                    class="hover:text-[#0B6AB2] inline-flex items-center gap-1">
                                    {{ __('admin.name') }}
                                    @if($sortIcon('name')['active']) <svg class="w-3 h-3" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path d="{{ request('dir') === 'asc' ? 'M5 10l5-5 5 5' : 'M5 10l5 5 5-5' }}" />
                                    </svg> @endif
                                </a>
                            </th>
                            <th class="text-left px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'city', 'dir' => $sortIcon('city')['dir']]) }}"
                                    class="hover:text-[#0B6AB2] inline-flex items-center gap-1">
                                    {{ __('admin.city') }}
                                    @if($sortIcon('city')['active']) <svg class="w-3 h-3" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path d="{{ request('dir') === 'asc' ? 'M5 10l5-5 5 5' : 'M5 10l5 5 5-5' }}" />
                                    </svg> @endif
                                </a>
                            </th>
                            <th class="text-center px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                                {{ __('admin.students') }}</th>
                            <th class="text-center px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                                {{ __('admin.teachers') }}</th>
                            <th class="text-center px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                                {{ __('admin.classes') }}</th>
                            <th class="text-center px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                                {{ __('admin.status') }}</th>
                            <th class="text-right px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                                {{ __('admin.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-[#1A3A5C]/50">
                        @forelse($schools as $school)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-[#0A1628]/30 transition-colors">
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-9 h-9 rounded-lg bg-gradient-to-br from-[#13398E] to-[#0B6AB2] flex items-center justify-center flex-shrink-0">
                                            <span
                                                class="text-white text-xs font-bold">{{ mb_strtoupper(mb_substr($school->name, 0, 2)) }}</span>
                                        </div>
                                        <div class="min-w-0">
                                            <a href="{{ route('admin.schools.show', $school) }}"
                                                class="font-semibold text-gray-900 dark:text-white hover:text-[#0B6AB2] transition truncate block">{{ $school->name }}</a>
                                            @if($school->email)
                                            <p class="text-[11px] text-gray-400 truncate">{{ $school->email }}</p> @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-3 text-gray-600 dark:text-gray-400">{{ $school->city ?? '—' }}</td>
                                <td class="px-5 py-3 text-center">
                                    <span
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400">
                                        {{ $school->students_count }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-center">
                                    <span
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400">
                                        {{ $school->teachers_count }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-center">
                                    <span
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-purple-50 dark:bg-purple-900/20 text-purple-600 dark:text-purple-400">
                                        {{ $school->classes_count }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-center">
                                    @if($school->is_active)
                                        <span
                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>{{ __('admin.active') }}
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-red-50 dark:bg-red-900/20 text-red-500">
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-400"></span>{{ __('admin.inactive') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('admin.schools.show', $school) }}"
                                            class="p-1.5 rounded-lg hover:bg-[#E8F4F8] dark:hover:bg-[#0B6AB2]/10 text-gray-400 hover:text-[#0B6AB2] transition"
                                            title="{{ __('admin.view') }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </a>
                                        @can('update', $school)
                                            <a href="{{ route('admin.schools.edit', $school) }}"
                                                class="p-1.5 rounded-lg hover:bg-amber-50 dark:hover:bg-amber-900/10 text-gray-400 hover:text-amber-500 transition"
                                                title="{{ __('admin.edit') }}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </a>
                                        @endcan
                                        @can('delete', $school)
                                            <form action="{{ route('admin.schools.destroy', $school) }}" method="POST"
                                                onsubmit="return confirm('{{ __('admin.confirm_delete') }}')">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                    class="p-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/10 text-gray-400 hover:text-red-500 transition"
                                                    title="{{ __('admin.delete') }}">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-10 text-center text-gray-400">{{ __('admin.no_data') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($schools->hasPages())
                <div class="px-5 py-3 border-t border-gray-100 dark:border-[#1A3A5C]">
                    {{ $schools->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection