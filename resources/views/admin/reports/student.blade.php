@extends('admin.layouts.app')
@section('title', ($student->name ?? '') . ' — ' . __('admin.reports'))
@section('breadcrumb')
    <a href="{{ route('admin.reports.index') }}" class="hover:text-gray-700 dark:hover:text-gray-200">{{ __('admin.reports') }}</a>
    <span class="mx-2">/</span>
    <span>{{ $student->name }} {{ $student->surname }}</span>
@endsection
@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $student->name }} {{ $student->surname }}</h1>
    <p class="text-sm text-gray-500 mt-1">{{ $student->email }} — {{ __('admin.rep_student_detailed') }}</p>
</div>

{{-- Student Info --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4">
        <div class="text-xs text-gray-500 mb-1">{{ __('admin.rep_roles') }}</div>
        @foreach($student->roles as $r)<span class="px-2 py-0.5 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 text-xs font-semibold rounded mr-1">{{ $r->name }}</span>@endforeach
    </div>
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4">
        <div class="text-xs text-gray-500 mb-1">{{ __('admin.rep_schools') }}</div>
        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $student->schools->pluck('name')->join(', ') ?: '-' }}</div>
    </div>
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4">
        <div class="text-xs text-gray-500 mb-1">{{ __('admin.rep_classes_pl') }}</div>
        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $student->classes->pluck('name')->join(', ') ?: '-' }}</div>
    </div>
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4">
        <div class="text-xs text-gray-500 mb-1">{{ __('admin.rep_applications_pl') }}</div>
        <div class="text-2xl font-extrabold text-blue-500">{{ $student->applications->count() }}</div>
    </div>
</div>

{{-- Per-App Reports --}}
@foreach($reportData as $slug => $appData)
<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 mb-6 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ $appData['app']->name }}</h3>
        <span class="px-2 py-0.5 text-xs font-semibold rounded {{ $appData['stats']['completion_rate'] >= 80 ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400' : ($appData['stats']['completion_rate'] >= 40 ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400' : 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400') }}">
            {{ $appData['stats']['completion_rate'] }}%
        </span>
    </div>
    <div class="p-5">
        {{-- Stats Row --}}
        <div class="grid grid-cols-5 gap-4 text-center mb-4">
            <div><div class="text-xl font-bold text-gray-900 dark:text-white">{{ $appData['stats']['total_modules'] }}</div><div class="text-[10px] text-gray-500">{{ __('admin.auto_modules') }}</div></div>
            <div><div class="text-xl font-bold text-emerald-500">{{ $appData['stats']['completed'] }}</div><div class="text-[10px] text-gray-500">{{ __('admin.rep_completed') }}</div></div>
            <div><div class="text-xl font-bold text-amber-500">{{ $appData['stats']['in_progress'] }}</div><div class="text-[10px] text-gray-500">{{ __('admin.rep_in_progress') }}</div></div>
            <div><div class="text-xl font-bold text-blue-500">{{ $appData['stats']['avg_score'] ? number_format($appData['stats']['avg_score'], 1) : '-' }}</div><div class="text-[10px] text-gray-500">{{ __('admin.rep_avg_score') }}</div></div>
            <div><div class="text-xl font-bold text-purple-500">{{ $appData['stats']['total_sessions'] }}</div><div class="text-[10px] text-gray-500">{{ __('admin.rep_sessions') }}</div></div>
        </div>
        <div class="w-full h-1.5 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden mb-5">
            <div class="h-full rounded-full bg-gradient-to-r from-emerald-400 to-cyan-400" style="width:{{ $appData['stats']['completion_rate'] }}%"></div>
        </div>
        {{-- Progress Table --}}
        @if($appData['progress']->count())
        <table class="w-full mb-4">
            <thead><tr class="bg-gray-50 dark:bg-gray-800/50">
                <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500">{{ __('admin.rep_module') }}</th>
                <th class="px-4 py-2 text-xs font-semibold text-gray-500">{{ __('admin.rep_type') }}</th>
                <th class="px-4 py-2 text-xs font-semibold text-gray-500">{{ __('admin.rep_status') }}</th>
                <th class="px-4 py-2 text-xs font-semibold text-gray-500">{{ __('admin.auto_score') }}</th>
                <th class="px-4 py-2 text-xs font-semibold text-gray-500">{{ __('admin.rep_date') }}</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
            @foreach($appData['progress'] as $p)
            <tr>
                <td class="px-4 py-2 text-xs font-medium text-gray-900 dark:text-white">{{ $p->module_name ?: $p->module_id }}</td>
                <td class="px-4 py-2 text-center"><span class="px-1.5 py-0.5 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 text-[10px] rounded font-semibold">{{ $p->module_type }}</span></td>
                <td class="px-4 py-2 text-center"><span class="px-1.5 py-0.5 text-[10px] rounded font-semibold {{ $p->status === 'completed' ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400' : ($p->status === 'in_progress' ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400' : 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400') }}">{{ $p->status }}</span></td>
                <td class="px-4 py-2 text-xs text-gray-500 text-center">{{ $p->score !== null ? number_format($p->score, 1) : '-' }}</td>
                <td class="px-4 py-2 text-xs text-gray-500 text-center">{{ $p->completed_at ? $p->completed_at->format('d.m.Y H:i') : '-' }}</td>
            </tr>
            @endforeach
            </tbody>
        </table>
        @endif
    </div>
</div>
@endforeach

@if(empty($reportData))
<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-12 text-center">
    <div class="text-4xl mb-3">📭</div>
    <p class="text-gray-400">{{ __('admin.rep_no_report_data') }}</p>
</div>
@endif

{{-- Connector Profiles (Portal Parity) --}}
@if(!empty($connectorProfiles))
<h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 mt-8">🔌 {{ __('admin.rep_connector_data') }}</h2>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-8">
    @foreach($connectorProfiles as $slug => $cp)
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5">
        <h3 class="font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
            {{ ucfirst(str_replace('-', ' ', $slug)) }}
        </h3>

        @if(($cp['type'] ?? '') === 'missionway')
        <div class="grid grid-cols-3 gap-3 text-center mb-3">
            <div><div class="text-lg font-bold text-blue-500">{{ number_format($cp['total_score'] ?? 0) }}</div><div class="text-[10px] text-gray-500">{{ __('admin.auto_score') }}</div></div>
            <div><div class="text-lg font-bold text-emerald-500">{{ $cp['simulations_completed'] ?? 0 }}</div><div class="text-[10px] text-gray-500">{{ __('admin.rep_completed') }}</div></div>
            <div><div class="text-lg font-bold text-purple-500">{{ $cp['play_time_minutes'] ?? 0 }}dk</div><div class="text-[10px] text-gray-500">{{ __('admin.rep_time') }}</div></div>
        </div>
        @if(!empty($cp['achievements']))
        <div class="flex flex-wrap gap-1 mt-2">
            @foreach(array_slice($cp['achievements'], 0, 6) as $ach)
            <span class="px-1.5 py-0.5 bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400 text-[10px] rounded">🏆 {{ $ach['name'] ?? 'Achievement' }}</span>
            @endforeach
        </div>
        @endif
        {{-- Scenario Breakdown --}}
        @if(!empty($cp['scenario_breakdown']))
        <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-800">
            <div class="text-[10px] font-semibold text-gray-400 mb-2">🎭 {{ __('admin.rep_scenario_perf') }}</div>
            <div class="grid grid-cols-2 gap-2">
                @foreach($cp['scenario_breakdown'] as $sKey => $sc)
                <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-50 dark:bg-gray-800/50" style="border-left:2px solid {{ $sc['color'] }}">
                    <span class="text-sm">{{ $sc['icon'] }}</span>
                    <div class="min-w-0">
                        <div class="text-[10px] font-bold text-gray-900 dark:text-white truncate">{{ $sc['name'] }}</div>
                        <div class="text-[9px] text-gray-400">{{ $sc['sessions'] }} oturum · ort {{ $sc['avg_score'] }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        @elseif(($cp['type'] ?? '') === 'waystartup')
        <div class="grid grid-cols-3 gap-3 text-center mb-3">
            <div><div class="text-lg font-bold text-emerald-500">{{ number_format($cp['points'] ?? 0) }}</div><div class="text-[10px] text-gray-500">{{ __('admin.rep_points') }}</div></div>
            <div><div class="text-lg font-bold text-blue-500">{{ $cp['completed_steps'] ?? 0 }}/{{ $cp['total_steps'] ?? 0 }}</div><div class="text-[10px] text-gray-500">{{ __('admin.rep_steps') }}</div></div>
            <div>
                @php $pct = ($cp['total_steps'] ?? 0) > 0 ? round(($cp['completed_steps'] ?? 0) / $cp['total_steps'] * 100) : 0; @endphp
                <div class="text-lg font-bold text-amber-500">%{{ $pct }}</div>
                <div class="text-[10px] text-gray-500">{{ __('admin.rep_progress') }}</div>
            </div>
        </div>

        @elseif(($cp['type'] ?? '') === 'vega')
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center mb-3">
            <div><div class="text-lg font-bold text-purple-500">{{ $cp['session_count'] ?? 0 }}</div><div class="text-[10px] text-gray-500">{{ __('admin.rep_sessions') }}</div></div>
            <div><div class="text-lg font-bold text-amber-500">{{ $cp['simulator_count'] ?? 0 }}</div><div class="text-[10px] text-gray-500">Simulator</div></div>
            <div><div class="text-lg font-bold text-emerald-500">{{ $cp['lecturer_count'] ?? 0 }}</div><div class="text-[10px] text-gray-500">Lecturer</div></div>
            <div><div class="text-lg font-bold text-blue-500">{{ $cp['chatbot_count'] ?? 0 }}</div><div class="text-[10px] text-gray-500">Chatbot</div></div>
        </div>
        {{-- Profile badges --}}
        @if(!empty($cp['profile']))
        <div class="flex flex-wrap gap-1.5 mb-2">
            @if($cp['profile']['grade'] ?? $cp['profile']['level'] ?? null)
            <span class="px-2 py-0.5 bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 text-[10px] rounded-full font-medium">Grade: {{ $cp['profile']['grade'] ?? $cp['profile']['level'] }}</span>
            @endif
            @if($cp['profile']['isPremium'] ?? $cp['profile']['premium_status'] ?? false)
            <span class="px-2 py-0.5 bg-amber-50 dark:bg-amber-900/20 text-amber-600 text-[10px] rounded-full font-medium">💎 Premium</span>
            @endif
            @if($cp['profile']['onboardingCompleted'] ?? $cp['profile']['onboarding_completed'] ?? false)
            <span class="px-2 py-0.5 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 text-[10px] rounded-full font-medium">✅ Onboarding</span>
            @endif
        </div>
        @endif
        {{-- Lecturer Sessions --}}
        @if(!empty($cp['lecturer_sessions']))
        <div class="mt-2 pt-2 border-t border-gray-100 dark:border-gray-800">
            <div class="text-[10px] font-semibold text-gray-400 mb-1">🤖 AI Coach ({{ count($cp['lecturer_sessions']) }})</div>
            @foreach(array_slice($cp['lecturer_sessions'], 0, 5) as $ls)
            <div class="flex items-center justify-between py-1 text-[10px]">
                <span class="text-gray-700 dark:text-gray-300">{{ $ls['app_name'] ?? 'Session' }}</span>
                <span class="text-gray-400">{{ isset($ls['created_at']) ? \Carbon\Carbon::parse($ls['created_at'])->format('d.m') : '' }}</span>
            </div>
            @endforeach
        </div>
        @endif
        {{-- Chatbot Sessions --}}
        @if(!empty($cp['chatbot_sessions']))
        <div class="mt-2 pt-2 border-t border-gray-100 dark:border-gray-800">
            <div class="text-[10px] font-semibold text-gray-400 mb-1">💬 Study Space ({{ count($cp['chatbot_sessions']) }})</div>
            @foreach(array_slice($cp['chatbot_sessions'], 0, 5) as $cs)
            <div class="flex items-center justify-between py-1 text-[10px]">
                <span class="text-gray-700 dark:text-gray-300">{{ $cs['thread_name'] ?? $cs['app_name'] ?? 'Chat' }}</span>
                <span class="text-gray-400">{{ isset($cs['created_at']) ? \Carbon\Carbon::parse($cs['created_at'])->format('d.m') : '' }}</span>
            </div>
            @endforeach
        </div>
        @endif
        @endif
    </div>
    @endforeach
</div>
@endif
@endsection
