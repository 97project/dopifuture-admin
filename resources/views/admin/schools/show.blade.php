@extends('admin.layouts.app')

@section('title', $school->name)

@section('breadcrumb')
    <div class="flex items-center gap-2 text-sm">
        <a href="{{ route('admin.dashboard') }}"
            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">{{ __('admin.dashboard') }}</a>
        <svg class="w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <a href="{{ route('admin.schools.index') }}"
            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">{{ __('admin.schools') }}</a>
        <svg class="w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-gray-900 dark:text-gray-100 font-semibold">{{ $school->name }}</span>
    </div>
@endsection

@section('content')
    <div class="space-y-6">

        {{-- Header --}}
        <div class="flex items-start justify-between">
            <div class="flex items-center gap-4">
                <div
                    class="w-14 h-14 rounded-xl bg-gradient-to-br from-[#13398E] to-[#0B6AB2] flex items-center justify-center shadow-lg shadow-blue-800/20">
                    <span class="text-white text-xl font-bold">{{ strtoupper(mb_substr($school->name, 0, 2)) }}</span>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ $school->name }}</h1>
                    <div class="flex items-center gap-3 mt-1">
                        @if($school->city) <span class="text-xs text-gray-400">📍 {{ $school->city }}</span> @endif
                        @if($school->is_active)
                            <span
                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600">
                                <span
                                    class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>{{ __('admin.active') }}
                            </span>
                        @else
                            <span
                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-50 dark:bg-red-900/20 text-red-500">{{ __('admin.inactive') }}</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2">
                @can('update', $school)
                    <a href="{{ route('admin.schools.edit', $school) }}"
                        class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-white bg-[#0B6AB2] rounded-lg hover:bg-[#13398E] transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        {{ __('admin.edit') }}
                    </a>
                @endcan
            </div>
        </div>

        {{-- Stat Cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
            <div
                class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-4 text-center">
                <p class="text-2xl font-extrabold text-[#0B6AB2]">{{ $school->students->count() }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ __('admin.students') }}</p>
            </div>
            <div
                class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-4 text-center">
                <p class="text-2xl font-extrabold text-emerald-500">{{ $school->teachers->count() }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ __('admin.teachers') }}</p>
            </div>
            <div
                class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-4 text-center">
                <p class="text-2xl font-extrabold text-[#F87D17]">{{ $school->classes->count() }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ __('admin.classes') }}</p>
            </div>
            <div
                class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-4 text-center">
                <p class="text-2xl font-extrabold text-[#13398E]">{{ $school->licenses->count() }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ __('admin.licenses') }}</p>
            </div>
            <div
                class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-4 text-center">
                <p class="text-2xl font-extrabold text-purple-500">
                    {{ $school->admins->count() + $school->principals->count() }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ __('admin.administrators') }}</p>
            </div>
        </div>

        {{-- Tab Navigation --}}
        <div x-data="{ activeTab: '{{ request('tab', 'general') }}' }">
            <div class="border-b border-gray-200 dark:border-[#1A3A5C]">
                <nav class="flex gap-6 px-1 -mb-px" role="tablist">
                    @foreach(['general' => __('admin.general'), 'classes' => __('admin.classes'), 'users' => __('admin.users'), 'licenses' => __('admin.licenses'), 'applications' => 'Uygulamalar'] as $tab => $label)
                        <button @click="activeTab = '{{ $tab }}'"
                            :class="activeTab === '{{ $tab }}' ? 'border-[#0B6AB2] text-[#0B6AB2] dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'"
                            class="py-3 px-1 text-sm font-medium border-b-2 transition" role="tab">{{ $label }}</button>
                    @endforeach
                </nav>
            </div>

            {{-- General Tab --}}
            <div x-show="activeTab === 'general'" class="pt-6 space-y-4">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div
                        class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5 space-y-3">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-3">{{ __('admin.contact_info') }}</h3>
                        @foreach([
                                                    __('admin.email') => $school->email,
                                                    __('admin.phone') => $school->phone,
                                                    __('admin.website') => $school->website,
                                                    __('admin.address') => $school->address,
                                                    __('admin.country') => $school->country,
                                                    __('admin.state') => $school->state,
                                                    __('admin.city') => $school->city,
                                                ] as $label => $value)

                            <                   div class="flex justify-between py-2 border-b border-gray-50 dark:border-[#1A3A5C]/50 last:border-0">
                                                    <span class="text-xs font-medium text-gray-500">{{ $label }}</span>
                                                    <span class="text-xs text-gray-900 dark:text-white text-right">{{ $value ?? '—' }}</span>
                                                </di
                            v                       >
                        @endforeach
                    </div>

                    <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5 space-y-3">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-3">{{ __('admin.overview') }}</h3>
                        @foreach([
                                    __('admin.created_at') => $school->created_at?->format('d.m.Y H:i'),
                                    __('admin.updated_at') => $school->updated_at?->format('d.m.Y H:i'),
                                    __('admin.status') => $school->is_active ? __('admin.active') : __('admin.inactive'),
                                ] as $label => $value)

                               <div class="flex justify-between py-2 border-b border-gray-50 dark:border-[#1A3A5C]/50 last:border-0">
                                    <span class="text-xs font-medium text-gray-500">{{ $label }}</span>
                                    <span class="text-xs text-gray-900 dark:text-white">{{ $value ?? '—' }}</span>
                                </div>
                        @endforeach


                                                    </div>


                                                </div>


                                                                           </div>



            {{-- Classes Tab --}}
            <div x-show="activeTab === 'classes'" x-cloak class="pt-6">
                <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] overflow-hidden">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 dark:bo
    r                                   der-[#1A3A5C]">
                                <th class="text-left px-5 py-3 text-xs 
    f                                   ont-semibold uppercase tracking-wider text-gray-400">{{ __('admin.class_name') }}</th>
                                <th class="text-center px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">{{ __('admin.students') }}</th>
                                <th class="text-center px-5 py-3 text-xs font-semibold 
                                       uppercase tracking-wider text-gray-400">{{ __('admin.teachers') }}</th>
                                <th class="text-right px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">{{ __('admin.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody c
                                lass="divide-y divide-gray-50 dark:divide-[#1A3A5C]/50">

                                                    @forelse($school->classes as $class)
                                                        <tr class="hover:bg-gray-50/50 dark:hover:bg-[#0A1628]/30 transition">
                                                            <td class="px-5 py-3 font-medium text-gray-900 dark:text-white">{{ $class->name }}</td>
                                                            <td class="px-5 py-3 text-center"><span class="badge badge-info">{{ $class->students->count() }}</span></td>
                                                            <td class="px-5 py-3 text-center"><span class="badge badge-success">{{ $class->teachers->count() }}</span></td>
                                                            <td class="px-5 py-3 text-right">
                                                                <a href="{{ route('admin.classes.show', $class) }}" class="text-xs text-[#0B6AB2] hover:underline">{{ __('admin.view') }}</a>
                                                            </td>
                                                        </tr>
                                                    @empty
                                <tr><td colspan="4" class="px-5 py-8 text-center text-gray-400">{{ __('admin.no_data') }}</td></tr>
                            @endforelse

                                                   </tbody>
                    </table>
                </div>
            </div>

            {{-- Users Tab --}}

            <div x-show="activeTab ==
                                   = 'users'" x-cloak class="pt-6 space-y-4">
                @foreach([
                        ['label' => __('admin.administrators'), 'users' => $school->admins, 'color' => 'purple'],
                        ['label' => __('admin.principals'), 'users' => $school->principals, 'color' => 'blue'],
                        ['label' => __('admin.teachers'), 'users' => $school->teachers, 'color' => 'emerald'],
                        ['label' => __('admin.students'), 'users' => $school->students, 'color' => 'orange'],
                    ] as $group)
                    <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-sm font-bold text-gray-900 dark:text-white">{{ $group['label'] }} <span class="text-gray-400 font-normal">({{ $group['users']->count() }})</span></h4>
                    </div>
                        @if($group['users']->count())
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                                @foreach($group['users']->take(12) as $user)
                                    <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-50 dark:bg-[#0A1628]/40">
                                        <div class="w-7 h-7 rounded-full bg-{{ $group['color'] }}-100 dark:bg-{{ $group['color'] }}-900/20 flex items-center justify-center flex-shrink-0">
                                            <span class="text-{{ $group['color'] }}-600 text-[10px] font-bold">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-xs font-medium text-gray-900 dark:text-white truncate">{{ $user->name }}</p>
                                            <p class="text-[10px] text-gray-400 truncate">{{ $user->email }}</p>

                                       </div>
                                    </div>
                                @endforeach
                            </div>
                            @if($group['users']->count() > 12)

                                                        <p class="t
                                   ext-xs text-gray-400 mt-2">+{{ $group['users']->count() - 12 }} {{ __('admin.more') }}...</p
                                    >
                            @endif


                           @else


                                                                            <p class="text-
                                                           xs text-gray-400 text-center py-3">{{ __('admin.no_data') }}</p>

                                                    @endif
                    </div>
                @endforeach
            </div>

        {{-- Licenses Tab --}}
        <div x-show="activeTab === 'licenses'" x-cloak class="pt-6">
            <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] overflow-hidden">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 dark:border-[#1A3A5C]">
                                <th class="text-left px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">{{ __('admin.license') }}</th>
                                <th class="text-center px-5 py
                                       -3 text-xs font-semibold uppercase tracking-wider text-gray-400">{{ __('admin.capacity') }}</th>
                                <th class="text-center px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">{{ __('admin.utilization') }}</th>
                                <th class="text-center px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">{{ __('admin.expires') }}</th>
                                <th class="text-center px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">{{ __('admin.status') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-[#1A3A5C]/50">

                                                                       @forelse($school->licenses as $license)
                                                                        @php
                                                                            $total = $license->totalSeats();
                                                                            $used = $license->used_seats;
                                                                            $pct = $total > 0 ? round(($used / $total) * 100) : 0;
                                                                        @endphp
                                                                        <tr class="hover:bg-gray-50/50 dark:hover:bg-[#0A1628]/30 transition">
                                                                        <td class="px-5 py-3">
                                                                                <span class="font-medium text-gray-900 dark:text-white">#{{ $license->id }}</span>
                                                                            @if($license->notes) <span class="text-xs text-gray-400 ml-1">{{ Str::limit($license->notes, 30) }}</span> @endif
                                                                            </td>
                                                                        <td class="px-5 py-3 text-center font-medium tabular-nums">{{ $used }}/{{ $total }}</td>
                                                                            <td class="px-5 py-3">
                                                                                <div class="flex items-center gap-2 justify-center">
                                                                                    <div class="w-20 bg-gray-100 dark:bg-[#0A1628] rounded-full h-2 overflow-hidden">
                                                                                        <div class="h-full rounded-full {{ $pct >= 90 ? 'bg-red-500' : ($pct >= 70 ? 'bg-amber-500' : 'bg-emerald-500') }}" style="width: {{ min($pct, 100) }}%"></div>

                                                                                    </div>

                                                                                                            <span class="text-xs font-bold {{ $pct >= 90 ? 'text-red-500' : ($pct >= 70 ? 'text-amber-500' : 'text-emerald-500') }} tabular-nums">{{ $pct }}%</span>
                                                                                </div>
                                                                            </td>
                                                                            <td class="px-5 py-3 text-center text-xs text-gray-500">{{ $license->expires_at?->format('d.m.Y') ?? '—' }}</td>
                                                                            <td class="px-5 py-3 text-center">
                                                                                @if($license->is_active && !$license->isExpired())
                                                                                    <span class="badge badge-success">{{ __('admin.active') }}</span>
                                                                                @elseif($license->isExpired())
                                                                                    <span class="badge badge-danger">{{ __('admin.expired') }}</span>
                                                                                @else
                                                                                    <span class="badge badge-warning">{{ __('admin.inactive') }}</span>
                                                                                @endif
                                                                            </td>
                                                                        </tr>
                                                                    @empty
                                <tr><td colspan="5" class="px-5 py-8 text-center text-gray-400">{{ __('admin.no_data') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        {{-- Applications Tab --}}
        <div x-show="activeTab === 'applications'" x-cloak class="pt-6">
            @if(!empty($appSyncStats) && count($appSyncStats) > 0)
                <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] overflow-hidden">
                    <div class="px-5 py-3 border-b border-gray-100 dark:border-[#1A3A5C]">
                        <h4 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <svg class="w-4 h-4 text-[#0B6AB2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6z" />
                            </svg>
                            Öğrenci Uygulama Durumu
                        </h4>
                    </div>
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 dark:border-[#1A3A5C]">
                                <th class="text-left px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">Uygulama</th>
                                <th class="text-center px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">Atanmış</th>
                                <th class="text-center px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">Senkron</th>
                                <th class="text-center px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">Başarısız</th>
                                <th class="text-center px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">Oran</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-[#1A3A5C]/50">
                            @foreach($appSyncStats as $appStat)
                                @php
                                    $appName = is_array($appStat->name) ? ($appStat->name[app()->getLocale()] ?? reset($appStat->name)) : $appStat->name;
                                    $pct = $appStat->school_total > 0 ? round(($appStat->school_synced / $appStat->school_total) * 100) : 0;
                                @endphp
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-[#0A1628]/30 transition">
                                    <td class="px-5 py-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-7 h-7 rounded-lg flex items-center justify-center text-white text-[10px] font-bold" style="background: {{ $appStat->color ?? '#0B6AB2' }}">
                                                {{ strtoupper(substr(is_array($appStat->name) ? reset($appStat->name) : $appStat->name, 0, 2)) }}
                                            </div>
                                            <div>
                                                <a href="{{ route('admin.applications.show', $appStat) }}" class="font-medium text-gray-900 dark:text-white hover:text-[#0B6AB2] transition">{{ $appName }}</a>
                                                <p class="text-[10px] text-gray-400">{{ $appStat->slug }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3 text-center font-semibold tabular-nums">{{ $appStat->school_total }}</td>
                                    <td class="px-5 py-3 text-center text-emerald-600 font-semibold tabular-nums">{{ $appStat->school_synced }}</td>
                                    <td class="px-5 py-3 text-center text-red-500 font-semibold tabular-nums">{{ $appStat->school_failed }}</td>
                                    <td class="px-5 py-3">
                                        <div class="flex items-center gap-2 justify-center">
                                            <div class="w-16 bg-gray-100 dark:bg-[#0A1628] rounded-full h-2 overflow-hidden">
                                                <div class="h-full rounded-full {{ $pct >= 80 ? 'bg-emerald-500' : ($pct >= 50 ? 'bg-amber-500' : 'bg-red-500') }}" style="width: {{ min($pct, 100) }}%"></div>
                                            </div>
                                            <span class="text-xs font-bold {{ $pct >= 80 ? 'text-emerald-500' : ($pct >= 50 ? 'text-amber-500' : 'text-red-500') }} tabular-nums">{{ $pct }}%</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-12 text-center">
                    <div class="w-14 h-14 rounded-2xl bg-gray-100 dark:bg-[#0A1628] flex items-center justify-center mx-auto mb-3">
                        <svg class="w-7 h-7 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6z" />
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-gray-400">Bu okulun öğrencilerine henüz uygulama atanmamış</p>
                </div>
            @endif
        </div>

        </div>
    </div>
@endsection