@extends('admin.layouts.app')

@section('title', __('admin.user_details') . ': ' . $user->full_name)

@section('breadcrumb')
    <div class="flex items-center gap-2 text-sm">
        <a href="{{ route('admin.dashboard') }}" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">{{ __('admin.dashboard') }}</a>
        <svg class="w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('admin.users.index') }}" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">{{ __('admin.users') }}</a>
        <svg class="w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-900 dark:text-gray-100 font-semibold">{{ $user->full_name }}</span>
    </div>
@endsection

@section('content')
<div class="space-y-6">

    {{-- ═══ HERO HEADER ═══ --}}
    <div class="relative overflow-hidden bg-gradient-to-br from-[#0B6AB2] via-[#13398E] to-[#0A1628] rounded-2xl p-6 sm:p-8">
        <div class="absolute inset-0 opacity-10">
            <svg width="100%" height="100%"><defs><pattern id="grid" width="32" height="32" patternUnits="userSpaceOnUse"><path d="M 32 0 L 0 0 0 32" fill="none" stroke="white" stroke-width="0.5"/></pattern></defs><rect width="100%" height="100%" fill="url(#grid)"/></svg>
        </div>
        <div class="relative flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            {{-- Left: Avatar + Info --}}
            <div class="flex items-center gap-5">
                <div class="relative">
                    @if($user->avatar_url)
                    <img src="{{ $user->avatar_url }}" class="w-20 h-20 rounded-2xl object-cover ring-4 ring-white/20 shadow-xl" alt="">
                    @else
                    <div class="w-20 h-20 rounded-2xl bg-white/10 backdrop-blur flex items-center justify-center ring-4 ring-white/20 shadow-xl">
                        <span class="text-white text-2xl font-bold">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}{{ mb_strtoupper(mb_substr($user->surname ?? '', 0, 1)) }}</span>
                    </div>
                    @endif
                    @php $sc = ['active' => 'bg-emerald-400', 'banned' => 'bg-red-400', 'inactive' => 'bg-gray-400'][$user->status] ?? 'bg-gray-400'; @endphp
                    <span class="absolute -bottom-1 -right-1 w-5 h-5 {{ $sc }} rounded-full border-[3px] border-[#13398E]" title="{{ ucfirst($user->status) }}"></span>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-white">{{ $user->full_name }}</h1>
                    <p class="text-blue-200/80 text-sm mt-0.5">{{ $user->email }}</p>
                    <div class="flex flex-wrap items-center gap-2 mt-2">
                        @php $statusColors = ['active' => 'bg-emerald-400/20 text-emerald-300', 'banned' => 'bg-red-400/20 text-red-300', 'inactive' => 'bg-gray-400/20 text-gray-300']; @endphp
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold {{ $statusColors[$user->status] ?? 'bg-gray-400/20 text-gray-300' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $sc }}"></span>{{ ucfirst($user->status) }}
                        </span>
                        @foreach($user->roles as $role)
                        <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-white/10 text-white/90">{{ $role->name }}</span>
                        @endforeach
                        @if($user->hasTwoFactorEnabled())
                        <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-400/20 text-emerald-300">🔒 2FA</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Right: Quick Actions --}}
            <div class="flex flex-wrap items-center gap-2">
                @can('users.edit')
                <a href="{{ route('admin.users.edit', $user) }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white/10 hover:bg-white/20 backdrop-blur text-white rounded-xl text-xs font-semibold transition border border-white/10">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    {{ __('admin.edit') }}
                </a>
                @endcan
                @can('users.delete')
                @if($user->id !== auth()->id())
                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('{{ __('admin.confirm_delete') }}')">
                    @csrf @method('DELETE')
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 bg-red-500/20 hover:bg-red-500/30 backdrop-blur text-red-200 rounded-xl text-xs font-semibold transition border border-red-400/20">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        {{ __('admin.delete') }}
                    </button>
                </form>
                @endif
                @endcan
            </div>
        </div>

        {{-- Stats Row --}}
        <div class="relative grid grid-cols-2 sm:grid-cols-4 gap-3 mt-6">
            @foreach([
                ['label' => 'Sessions', 'value' => $stats['sessions'], 'icon' => 'M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z'],
                ['label' => __('admin.devices'), 'value' => $stats['devices'], 'icon' => 'M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z'],
                ['label' => 'API Keys', 'value' => $stats['apiKeys'], 'icon' => 'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4'],
                ['label' => __('admin.audit_log'), 'value' => $stats['logs'], 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
            ] as $stat)
            <div class="bg-white/5 backdrop-blur rounded-xl p-3 border border-white/10">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-300/60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $stat['icon'] }}"/></svg>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-blue-200/50">{{ $stat['label'] }}</span>
                </div>
                <p class="text-xl font-bold text-white mt-1">{{ number_format($stat['value']) }}</p>
            </div>
            @endforeach
        </div>
    </div>

    {{-- ═══ TABS ═══ --}}
    @php
        $tabs = [
            'profile'  => ['label' => __('admin.profile'),    'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
            'security' => ['label' => __('admin.security'),   'icon' => 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z'],
            'roles'    => ['label' => __('admin.roles_permissions'), 'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
        ];

        // Role-based tabs
        $userRoleNames = $user->roles->pluck('name')->toArray();
        $schoolRoles = ['teacher', 'school-admin', 'school-principal', 'student'];
        $hasSchoolRole = count(array_intersect($userRoleNames, $schoolRoles)) > 0;

        if ($hasSchoolRole) {
            $tabs['schools'] = ['label' => 'Okullar', 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'];
        }

        if (in_array('teacher', $userRoleNames) || in_array('student', $userRoleNames)) {
            $tabs['classes'] = ['label' => 'Sınıflar', 'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'];
        }

        $appSlugs = $user->applications()->pluck('slug')->toArray();
        $appDefs = [
            'mission-way'  => ['label' => 'Mission Way',  'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
            'way-startup'  => ['label' => 'Way Startup',  'icon' => 'M13 10V3L4 14h7v7l9-11h-7z'],
            'role-galaxy'  => ['label' => 'Role Galaxy',  'icon' => 'M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
            'way-ai-coach' => ['label' => 'Way AI Coach', 'icon' => 'M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
            'study-space'  => ['label' => 'Study Space',  'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
        ];
        foreach ($appDefs as $slug => $def) {
            if (in_array($slug, $appSlugs)) {
                $tabs["app_{$slug}"] = $def;
            }
        }

        $tabs['api_keys'] = ['label' => __('admin.api_keys'), 'icon' => 'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4'];
        $tabs['devices']  = ['label' => __('admin.devices'),  'icon' => 'M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z'];
        $tabs['audit']    = ['label' => __('admin.audit_log'),'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'];
    @endphp
    <div class="flex gap-1 bg-gray-100/80 dark:bg-[#0A1628] rounded-xl p-1.5 overflow-x-auto">
        @foreach($tabs as $key => $t)
        <a href="{{ route('admin.users.show', ['user' => $user, 'tab' => $key]) }}"
           class="flex items-center gap-2 px-4 py-2 text-xs font-semibold rounded-lg transition whitespace-nowrap {{ $tab === $key ? 'bg-white dark:bg-[#0E2442] text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $t['icon'] }}"/></svg>
            {{ $t['label'] }}
        </a>
        @endforeach
    </div>

    {{-- ═══ TAB CONTENT ═══ --}}

    @if($tab === 'profile')
    {{-- PROFILE TAB — Read-only View --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left: Profile Info --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Personal Information Card --}}
            <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C]">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-[#1A3A5C] flex items-center justify-between">
                    <h2 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-[#0B6AB2]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        {{ __('admin.profile') }}
                    </h2>
                    @can('users.edit')
                    <a href="{{ route('admin.users.edit', $user) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-[#0B6AB2]/10 hover:bg-[#0B6AB2]/20 text-[#0B6AB2] rounded-lg text-xs font-semibold transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        {{ __('admin.edit') }}
                    </a>
                    @endcan
                </div>
                <div class="p-6">
                    {{-- Avatar + Name --}}
                    <div class="flex items-center gap-5 mb-6">
                        @if($user->avatar_url)
                        <img src="{{ $user->avatar_url }}" class="w-20 h-20 rounded-2xl object-cover border-2 border-gray-100 dark:border-[#1A3A5C] shadow-sm" alt="">
                        @else
                        <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-[#0B6AB2] to-[#13398E] flex items-center justify-center shadow-sm">
                            <span class="text-white text-2xl font-bold">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}{{ mb_strtoupper(mb_substr($user->surname ?? '', 0, 1)) }}</span>
                        </div>
                        @endif
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ $user->full_name }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ $user->email }}</p>
                            <div class="flex flex-wrap items-center gap-2 mt-2">
                                @php $statusColors = ['active' => 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600', 'banned' => 'bg-red-50 dark:bg-red-900/20 text-red-600', 'inactive' => 'bg-gray-100 dark:bg-gray-800 text-gray-500']; @endphp
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold {{ $statusColors[$user->status] ?? 'bg-gray-100 text-gray-500' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ ['active' => 'bg-emerald-500', 'banned' => 'bg-red-500', 'inactive' => 'bg-gray-400'][$user->status] ?? 'bg-gray-400' }}"></span>
                                    {{ ucfirst($user->status) }}
                                </span>
                                @foreach($user->roles as $role)
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400">{{ $role->name }}</span>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Info Grid --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-5">
                        @foreach([
                            ['icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z', 'label' => __('admin.name'), 'value' => $user->name],
                            ['icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z', 'label' => __('admin.surname'), 'value' => $user->surname ?? '—'],
                            ['icon' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z', 'label' => __('admin.email'), 'value' => $user->email],
                            ['icon' => 'M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z', 'label' => __('admin.phone'), 'value' => $user->phone ?? '—'],
                            ['icon' => 'M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129', 'label' => __('admin.locale'), 'value' => $user->locale === 'tr' ? '🇹🇷 Türkçe' : '🇬🇧 English'],
                            ['icon' => 'M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064', 'label' => __('admin.timezone'), 'value' => $user->timezone ?? '—'],
                        ] as $field)
                        <div class="flex items-start gap-3">
                            <div class="w-9 h-9 rounded-lg bg-gray-50 dark:bg-[#0A1628] flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $field['icon'] }}"/></svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-0.5">{{ $field['label'] }}</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $field['value'] }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Footer --}}
                <div class="px-6 py-3 border-t border-gray-100 dark:border-[#1A3A5C] bg-gray-50/50 dark:bg-[#0A1628]/30 rounded-b-xl">
                    <p class="text-[10px] text-gray-400">ID: {{ $user->id }} · {{ __('admin.date') }}: {{ $user->created_at->format('d.m.Y H:i') }} · {{ __('admin.last_login') }}: {{ $user->last_login_at?->format('d.m.Y H:i') ?? '—' }}</p>
                </div>
            </div>

            {{-- Schools & Classes Quick Access (role-based) --}}
            @php
                $profileRoles = $user->roles->pluck('name')->toArray();
                $hasSchoolRelation = count(array_intersect($profileRoles, ['teacher', 'school-admin', 'school-principal', 'student'])) > 0;
            @endphp
            @if($hasSchoolRelation)
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- Schools Preview --}}
                @if($user->schools->count())
                <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            Okullar
                        </h3>
                        <a href="{{ route('admin.users.show', ['user' => $user, 'tab' => 'schools']) }}" class="text-[10px] font-bold text-[#0B6AB2] hover:underline">Tümünü Gör →</a>
                    </div>
                    <div class="space-y-2">
                        @foreach($user->schools->take(3) as $school)
                        <div class="flex items-center gap-3 p-2.5 bg-gray-50 dark:bg-[#0A1628] rounded-lg">
                            <div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/></svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $school->name }}</p>
                                <p class="text-[10px] text-gray-400">{{ ucfirst($school->pivot->role ?? '') }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Classes Preview --}}
                @if($user->classes->count())
                <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253"/></svg>
                            Sınıflar
                        </h3>
                        <a href="{{ route('admin.users.show', ['user' => $user, 'tab' => 'classes']) }}" class="text-[10px] font-bold text-[#0B6AB2] hover:underline">Tümünü Gör →</a>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @foreach($user->classes->take(8) as $class)
                        <span class="px-3 py-1.5 bg-gray-50 dark:bg-[#0A1628] border border-gray-100 dark:border-[#1A3A5C] rounded-lg text-xs font-medium text-gray-700 dark:text-gray-300">
                            {{ $class->name }}
                        </span>
                        @endforeach
                        @if($user->classes->count() > 8)
                        <span class="px-3 py-1.5 bg-[#0B6AB2]/10 rounded-lg text-xs font-bold text-[#0B6AB2]">+{{ $user->classes->count() - 8 }}</span>
                        @endif
                    </div>
                </div>
                @endif
            </div>
            @endif
        </div>

        {{-- Right: Info Sidebar --}}
        <div class="space-y-4">
            {{-- Account Stats --}}
            <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5">
                <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-4">{{ __('admin.user_details') }}</h3>
                <div class="space-y-3">
                    @foreach([
                        ['icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', 'label' => __('admin.date'), 'value' => $user->created_at->format('d.m.Y H:i')],
                        ['icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'label' => __('admin.last_login'), 'value' => $user->last_login_at?->format('d.m.Y H:i') ?? '—'],
                        ['icon' => 'M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9', 'label' => __('admin.ip_address'), 'value' => $user->last_login_ip ?? '—'],
                    ] as $info)
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-gray-100 dark:bg-[#0A1628] flex items-center justify-center flex-shrink-0">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $info['icon'] }}"/></svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">{{ $info['label'] }}</p>
                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $info['value'] }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- 2FA Status --}}
            <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5">
                <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3">{{ __('admin.2fa') }}</h3>
                @if($user->hasTwoFactorEnabled())
                <div class="flex items-center gap-3 p-3 bg-emerald-50 dark:bg-emerald-900/10 rounded-lg">
                    <div class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-900/20 flex items-center justify-center"><span class="text-emerald-600 text-sm">🔒</span></div>
                    <div>
                        <p class="text-sm font-semibold text-emerald-700 dark:text-emerald-400">{{ __('admin.active') }}</p>
                        <p class="text-[10px] text-emerald-600/60">{{ $user->two_factor_confirmed_at?->format('d.m.Y') }}</p>
                    </div>
                </div>
                @else
                <div class="flex items-center gap-3 p-3 bg-amber-50 dark:bg-amber-900/10 rounded-lg">
                    <div class="w-8 h-8 rounded-full bg-amber-100 dark:bg-amber-900/20 flex items-center justify-center"><span class="text-amber-600 text-sm">⚠️</span></div>
                    <div>
                        <p class="text-sm font-semibold text-amber-700 dark:text-amber-400">{{ __('admin.inactive') }}</p>
                        <p class="text-[10px] text-amber-600/60">2FA henüz etkinleştirilmedi</p>
                    </div>
                </div>
                @endif
            </div>

            {{-- Roles --}}
            <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5">
                <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3">{{ __('admin.roles') }}</h3>
                <div class="flex flex-wrap gap-2">
                    @forelse($user->roles as $role)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 rounded-lg text-xs font-bold">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        {{ $role->name }}
                    </span>
                    @empty
                    <p class="text-xs text-gray-400">{{ __('admin.no_data') }}</p>
                    @endforelse
                </div>
            </div>

            {{-- Permissions Preview --}}
            <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5">
                <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3">{{ __('admin.permissions') }}</h3>
                <div class="flex flex-wrap gap-1">
                    @forelse($user->getAllPermissions()->take(12) as $perm)
                    <span class="px-2 py-0.5 bg-gray-100 dark:bg-[#0A1628] text-gray-500 dark:text-gray-400 rounded text-[10px] font-medium">{{ $perm->name }}</span>
                    @empty
                    <p class="text-xs text-gray-400">{{ __('admin.no_data') }}</p>
                    @endforelse
                    @if($user->getAllPermissions()->count() > 12)
                    <a href="{{ route('admin.users.show', ['user' => $user, 'tab' => 'roles']) }}" class="px-2 py-0.5 bg-[#0B6AB2]/10 text-[#0B6AB2] rounded text-[10px] font-bold">+{{ $user->getAllPermissions()->count() - 12 }}</a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @elseif($tab === 'security')
    @include('admin.users._tab_security')

    @elseif($tab === 'roles')
    @include('admin.users._tab_roles')

    @elseif($tab === 'api_keys')
    @include('admin.users._tab_api_keys')

    @elseif($tab === 'devices')
    @include('admin.users._tab_devices')

    @elseif($tab === 'schools')
    @include('admin.users._tab_schools')

    @elseif($tab === 'classes')
    @include('admin.users._tab_classes')

    @elseif(str_starts_with($tab, 'app_'))
    @include('admin.users._tab_app', ['appSlug' => str_replace('app_', '', $tab)])

    @elseif($tab === 'audit')
    @include('admin.users._tab_audit')

    @endif

</div>
@endsection