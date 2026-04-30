@extends('portal.app')
@section('title', $app->name . ' — ' . __('admin.reports'))
@section('page-title', $app->name)

@section('content')
@php
        $tab = request('tab', 'assignment');
    $slug = $app->slug;
@endphp

{{-- ═══ TAB BAR — Figma F-38: Assignments [24] | Performance ═══ --}}
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
    <div class="dp-tabs" style="border-bottom:none;margin-bottom:0;">
        <a href="{{ route('portal.reports.app', $app->slug) }}?tab=assignment"
           class="dp-tab {{ $tab === 'assignment' ? 'active' : '' }}">
            {{ __('admin.menu_assignments') }}
            @if($slug === 'mission-way')
                <span class="tab-count">{{ $total_missions ?? 0 }}</span>
            @else
                <span class="tab-count">{{ $total_progress ?? 0 }}</span>
            @endif
        </a>
        <a href="{{ route('portal.reports.app', $app->slug) }}?tab=performance"
           class="dp-tab {{ $tab === 'performance' ? 'active' : '' }}">
            {{ __('portal.student_performance') }}
        </a>
    </div>

    @if(!in_array($slug, ['role-galaxy', 'study-space', 'way-ai-coach']))
    <button type="button" class="dp-btn" onclick="document.getElementById('addAssignmentModal')?.classList.add('show')">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="2"/><path stroke-linecap="round" stroke-width="2" d="M12 8v8m-4-4h8"/></svg>
        @if($slug === 'mission-way')
            {{ __('portal.add_new') }}
        @elseif($slug === 'way-startup')
            {{ __('portal.add_new') }}
        @else
            {{ __('portal.add_new') }}
        @endif
    </button>
    @endif
</div>

@if($tab === 'assignment')
    {{-- ═══════════════════════════════════════════════════════════════
         ASSIGNMENTS TAB — Figma F-38
         Mission WAY: Table with mission rows (name, students, dates, points)
         Other apps: Stat cards + charts + student table (legacy)
    ═══════════════════════════════════════════════════════════════ --}}

    @if($slug === 'mission-way')
        {{-- ── FIGMA F-38: Mission table ─────────────────────────── --}}
        <div class="dp-card" style="padding:0;">
            <div style="overflow-x:auto;">
                <table class="dp-table">
                    <thead>
                        <tr>
                            <th style="width:40px;">{{ __('portal.no_num') }}</th>
                            <th>{{ __('portal.mission_name') }}</th>
                            <th>{{ __('portal.nav_students') }}</th>
                            <th>{{ __('portal.assigned_date') }}</th>
                            <th>{{ __('portal.deadline') }}</th>
                            <th>
                                <span style="display:inline-flex;align-items:center;gap:4px;">
                                    <span style="color:#ef4444;">❤️</span>
                                    {{ __('portal.health_point') }}
                                </span>
                            </th>
                            <th>
                                <span style="display:inline-flex;align-items:center;gap:4px;">
                                    <span style="color:#22c55e;">🌿</span>
                                    {{ __('portal.resource_point') }}
                                </span>
                            </th>
                            <th>
                                <span style="display:inline-flex;align-items:center;gap:4px;">
                                    <span style="color:#f59e0b;">🧡</span>
                                    {{ __('portal.ethics_point') }}
                                </span>
                            </th>
                            <th>
                                <span style="display:inline-flex;align-items:center;gap:4px;">
                                    <span style="color:#22c55e;">✅</span>
                                    {{ __('portal.adaptation_point') }}
                                </span>
                            </th>
                            <th>{{ __('portal.action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(($missions ?? collect()) as $mission)
                        <tr>
                            <td class="muted">{{ str_pad($mission->id, 2, '0', STR_PAD_LEFT) }}</td>
                            <td style="font-weight:500;">{{ $mission->name }}</td>
                            <td>
                                {{-- Avatar stack like Figma --}}
                                <div style="display:flex;align-items:center;">
                                    @foreach($mission->students->take(4) as $si => $st)
                                    <div style="width:30px;height:30px;border-radius:50%;background:{{ ['#4364F7','#8b5cf6','#f59e0b','#ef4444','#10b981'][$si % 5] }};color:#fff;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:600;border:2px solid var(--color-card-bg);margin-left:{{ $si > 0 ? '-8px' : '0' }};position:relative;z-index:{{ 10 - $si }};" title="{{ $st->name }} {{ $st->surname }}">
                                        {{ mb_strtoupper(mb_substr($st->name,0,1)) }}{{ mb_strtoupper(mb_substr($st->surname,0,1)) }}
                                    </div>
                                    @endforeach
                                    @if($mission->students->count() > 4)
                                    <div style="width:30px;height:30px;border-radius:50%;background:var(--color-input-bg);color:var(--color-txt-muted);display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:600;border:2px solid var(--color-card-bg);margin-left:-8px;position:relative;z-index:1;">
                                        +{{ $mission->students->count() - 4 }}
                                    </div>
                                    @endif
                                </div>
                            </td>
                            <td class="muted">{{ $mission->assigned_date }}</td>
                            <td class="muted">{{ $mission->deadline }}</td>
                            <td>
                                @if($mission->health_point !== null)
                                    <span style="display:inline-flex;align-items:center;gap:4px;font-weight:500;">
                                        {{ $mission->health_point }}
                                        @if($mission->health_trend === 'up')
                                            <svg width="12" height="12" fill="none" stroke="#22c55e" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                        @else
                                            <svg width="12" height="12" fill="none" stroke="#ef4444" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                        @endif
                                    </span>
                                @else
                                    <span class="muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($mission->resource_point !== null)
                                    <span style="display:inline-flex;align-items:center;gap:4px;font-weight:500;">
                                        {{ $mission->resource_point }}
                                        @if($mission->resource_trend === 'up')
                                            <svg width="12" height="12" fill="none" stroke="#22c55e" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                        @else
                                            <svg width="12" height="12" fill="none" stroke="#ef4444" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                        @endif
                                    </span>
                                @else
                                    <span class="muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($mission->ethics_point !== null)
                                    <span style="display:inline-flex;align-items:center;gap:4px;font-weight:500;">
                                        {{ $mission->ethics_point }}
                                        @if($mission->ethics_trend === 'up')
                                            <svg width="12" height="12" fill="none" stroke="#22c55e" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                        @else
                                            <svg width="12" height="12" fill="none" stroke="#ef4444" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                        @endif
                                    </span>
                                @else
                                    <span class="muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($mission->adaptation_point !== null)
                                    <span style="display:inline-flex;align-items:center;gap:4px;font-weight:500;">
                                        {{ $mission->adaptation_point }}
                                        @if($mission->adaptation_trend === 'up')
                                            <svg width="12" height="12" fill="none" stroke="#22c55e" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                        @else
                                            <svg width="12" height="12" fill="none" stroke="#ef4444" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                        @endif
                                    </span>
                                @else
                                    <span class="muted">-</span>
                                @endif
                            </td>
                            <td>
                                <div style="display:flex;align-items:center;gap:12px;white-space:nowrap;">
                                    {{-- Details --}}
                                    <a href="{{ route('portal.reports.mission.detail', $mission->id) }}" class="dp-action dp-action-view" title="{{ __('portal.view_details') }}">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="10" style="text-align:center;color:var(--color-txt-muted);padding:32px;">{{ __('portal.no_missions_yet') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{-- Pagination --}}
            <div style="display:flex;justify-content:space-between;align-items:center;padding:16px 20px;font-size:12px;border-top:1px solid var(--color-row-border);">
                <span style="color:var(--color-txt-muted);">{{ ($missions ?? collect())->count() }} {{ __('portal.missions') }}</span>
                <span style="color:var(--color-txt-muted);">{{ ($missions ?? collect())->count() }} {{ __('portal.total') }}</span>
            </div>
        </div>

    @elseif($slug === 'way-startup')
        {{-- ── FIGMA F-4: Startup project table ──────────────────── --}}
        <div class="dp-card" style="padding:0;">
            <div style="overflow-x:auto;">
                <table class="dp-table">
                    <thead>
                        <tr>
                            <th style="width:40px;">{{ __('portal.no_num') }}</th>
                            <th>{{ __('portal.startup_name') }}</th>
                            <th>{{ __('portal.startup_type') }}</th>
                            <th>{{ __('portal.nav_students') }}</th>
                            <th>{{ __('portal.deadline') }}</th>
                            <th>{{ __('portal.step') }}</th>
                            <th>{{ __('portal.system_point') }}</th>
                            <th>{{ __('portal.teacher_point') }}</th>
                            <th>{{ __('portal.action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(($startups ?? collect()) as $startup)
                        <tr>
                            <td class="muted">{{ str_pad($startup->id, 2, '0', STR_PAD_LEFT) }}</td>
                            <td>
                                <span style="display:inline-flex;align-items:center;gap:6px;font-weight:500;">
                                    <span>{{ $startup->type_icon }}</span>
                                    {{ $startup->name }}
                                </span>
                            </td>
                            <td class="muted">{{ $startup->type }}</td>
                            <td>
                                <div style="display:flex;align-items:center;">
                                    @foreach($startup->students->take(3) as $si => $st)
                                    <div style="width:28px;height:28px;border-radius:50%;background:{{ ['#4364F7','#8b5cf6','#f59e0b','#ef4444','#10b981'][$si % 5] }};color:#fff;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:600;border:2px solid var(--color-card-bg);margin-left:{{ $si > 0 ? '-6px' : '0' }};position:relative;z-index:{{ 10 - $si }};" title="{{ $st->name }} {{ $st->surname }}">
                                        {{ mb_strtoupper(mb_substr($st->name,0,1)) }}{{ mb_strtoupper(mb_substr($st->surname,0,1)) }}
                                    </div>
                                    @endforeach
                                    @if($startup->students->count() > 3)
                                    <div style="width:28px;height:28px;border-radius:50%;background:var(--color-input-bg);color:var(--color-txt-muted);display:flex;align-items:center;justify-content:center;font-size:9px;font-weight:600;border:2px solid var(--color-card-bg);margin-left:-6px;position:relative;z-index:1;">
                                        +{{ $startup->students->count() - 3 }}
                                    </div>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if($startup->deadline_overdue)
                                    <span style="color:#ef4444;font-weight:500;">{{ $startup->deadline }} <span title="{{ __('portal.overdue') }}">⚠️</span></span>
                                @else
                                    <span class="muted">{{ $startup->deadline }}</span>
                                @endif
                            </td>
                            <td>
                                @if($startup->status === 'completed')
                                    <span style="display:inline-flex;align-items:center;gap:4px;color:#22c55e;font-weight:500;">
                                        <svg width="14" height="14" fill="none" stroke="#22c55e" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        {{ __('portal.completed') }}
                                    </span>
                                @elseif($startup->status === 'not_started')
                                    <span class="muted">{{ __('portal.not_started') }}</span>
                                @else
                                    <div style="display:flex;align-items:center;gap:8px;">
                                        <div style="width:60px;height:6px;border-radius:3px;background:var(--color-input-bg);overflow:hidden;">
                                            <div style="height:100%;border-radius:3px;background:var(--color-primary);width:{{ ($startup->step_completed / max($startup->step_total,1)) * 100 }}%;"></div>
                                        </div>
                                        <span style="font-size:12px;color:var(--color-txt-muted);">{{ $startup->step_completed }}/{{ $startup->step_total }}</span>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <span style="display:inline-flex;align-items:center;gap:4px;font-weight:500;">
                                    {{ $startup->system_point }}/{{ $startup->max_point }}
                                    @if($startup->system_point > 1000)
                                        @if($startup->system_point >= 1300)
                                            <svg width="12" height="12" fill="none" stroke="#22c55e" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                        @else
                                            <svg width="12" height="12" fill="none" stroke="#f59e0b" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                        @endif
                                    @elseif($startup->system_point > 0)
                                        <svg width="12" height="12" fill="none" stroke="#22c55e" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                    @endif
                                </span>
                            </td>
                            <td>
                                @if($startup->teacher_point === 'Score')
                                    <span style="display:inline-block;padding:4px 12px;background:var(--color-primary);color:#fff;border-radius:6px;font-size:12px;font-weight:500;">{{ __('portal.score') }}</span>
                                @elseif($startup->teacher_point)
                                    <span style="font-weight:500;">{{ $startup->teacher_point }}</span>
                                @else
                                    <span class="muted">{{ __('portal.in_progress') }}...</span>
                                @endif
                            </td>
                            <td>
                                <div style="display:flex;align-items:center;gap:10px;white-space:nowrap;">
                                    <a href="{{ route('portal.reports.startup.detail', $startup->id) }}" class="dp-action dp-action-view" title="{{ __('portal.view_report') }}">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="9" style="text-align:center;color:var(--color-txt-muted);padding:32px;">{{ __('portal.no_startups_yet') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{-- Pagination --}}
            <div style="display:flex;justify-content:space-between;align-items:center;padding:16px 20px;font-size:12px;border-top:1px solid var(--color-row-border);">
                <span style="color:var(--color-txt-muted);">{{ ($startups ?? collect())->count() }} {{ __('portal.way_startup') }}</span>
                <span style="color:var(--color-txt-muted);">{{ ($startups ?? collect())->count() }} {{ __('portal.total') }}</span>
            </div>
        </div>

    @elseif($slug === 'study-space')
        {{-- ── FIGMA F-69: Study Space — simple student table ──── --}}
        <div class="dp-card" style="padding:0;">
            <div style="overflow-x:auto;">
                <table class="dp-table">
                    <thead>
                        <tr>
                            <th style="width:40px;">{{ __('portal.no_num') }}</th>
                            <th>{{ __('portal.nav_students') }}</th>
                            <th>{{ __('portal.total_discussion_min') }}</th>
                            <th>{{ __('portal.total_discussion_count') }}</th>
                            <th>{{ __('portal.action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(($user_stats ?? collect()) as $idx => $stat)
                        <tr>
                            <td class="muted">{{ str_pad($idx + 1, 2, '0', STR_PAD_LEFT) }}</td>
                            <td>
                                <div style="display:flex;align-items:center;gap:10px;">
                                    <div class="av" style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--primary-deep));color:#fff;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:600;">{{ mb_strtoupper(mb_substr($stat['user']->name ?? '', 0, 1) . mb_substr($stat['user']->surname ?? '', 0, 1)) }}</div>
                                    <span style="font-weight:500;">{{ $stat['user']->name }} {{ $stat['user']->surname }}</span>
                                </div>
                            </td>
                            <td style="text-align:center;">{{ $stat['discussion_minutes'] }}</td>
                            <td style="text-align:center;">{{ $stat['discussion_count'] }}</td>
                            <td>
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <a href="{{ route('portal.reports.student.study-space', $stat['user']->id) }}" class="dp-action dp-action-view" title="{{ __('portal.study_space_detail') }}">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    <a href="{{ route('portal.reports.student.study-space', $stat['user']->id) }}" style="color:var(--color-primary);font-size:12px;font-weight:500;text-decoration:none;">{{ __('portal.view_details') }} →</a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" style="text-align:center;color:var(--color-txt-muted);padding:32px;">{{ __('portal.no_data_yet') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:16px 20px;font-size:12px;border-top:1px solid var(--color-row-border);">
                <span style="color:var(--color-txt-muted);">{{ ($user_stats ?? collect())->count() }} {{ __('portal.nav_students') }}</span>
                <span style="color:var(--color-txt-muted);">{{ ($user_stats ?? collect())->count() }} {{ __('portal.total') }}</span>
            </div>
        </div>

    @elseif($slug === 'way-ai-coach')
        {{-- ── FIGMA F-70: WAY AI Coach — student interaction table ── --}}
        <div class="dp-card" style="padding:0;">
            <div style="overflow-x:auto;">
                <table class="dp-table">
                    <thead>
                        <tr>
                            <th style="width:40px;">{{ __('portal.no_num') }}</th>
                            <th>{{ __('portal.nav_students') }}</th>
                            <th>{{ __('portal.ai_coach_interaction_num') }}</th>
                            <th>{{ __('portal.total_duration') }}</th>
                            <th>{{ __('portal.action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(($user_stats ?? collect()) as $idx => $stat)
                        @php
                            $isAlert = $stat['alert'] ?? false;
                        @endphp
                        <tr style="{{ $isAlert ? 'background:rgba(239,68,68,0.04);' : '' }}">
                            <td class="muted">{{ str_pad($idx + 1, 2, '0', STR_PAD_LEFT) }}</td>
                            <td>
                                <div style="display:flex;align-items:center;gap:10px;">
                                    <div class="av" style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--primary-deep));color:#fff;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:600;">{{ mb_strtoupper(mb_substr($stat['user']->name ?? '', 0, 1) . mb_substr($stat['user']->surname ?? '', 0, 1)) }}</div>
                                    <span style="font-weight:500;">{{ $stat['user']->name }} {{ $stat['user']->surname }}</span>
                                </div>
                            </td>
                            <td style="text-align:center;">{{ $stat['interaction_count'] }}</td>
                            <td style="text-align:center;">
                                @if($isAlert)
                                    <span style="color:#ef4444;font-weight:600;">{{ \App\Services\ReportService::formatDuration($stat['total_duration'] ?? 0) }} <span title="{{ __('portal.alert') }}">🔴</span></span>
                                @else
                                    {{ \App\Services\ReportService::formatDuration($stat['total_duration'] ?? 0) }}
                                @endif
                            </td>
                            <td>
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <a href="{{ route('portal.reports.student.way-ai-coach', $stat['user']->id) }}" class="dp-action dp-action-view" title="{{ __('portal.way_ai_coach_detail') }}">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    <a href="{{ route('portal.reports.student.way-ai-coach', $stat['user']->id) }}" style="color:var(--color-primary);font-size:12px;font-weight:500;text-decoration:none;">{{ __('portal.view_details') }} →</a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" style="text-align:center;color:var(--color-txt-muted);padding:32px;">{{ __('portal.no_data_yet') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:16px 20px;font-size:12px;border-top:1px solid var(--color-row-border);">
                <span style="color:var(--color-txt-muted);">{{ ($user_stats ?? collect())->count() }} {{ __('portal.nav_students') }}</span>
                <span style="color:var(--color-txt-muted);">{{ ($user_stats ?? collect())->count() }} {{ __('portal.total') }}</span>
            </div>
        </div>

    @elseif($slug === 'role-galaxy')
        {{-- ── FIGMA Role Galaxy v1: Stat cards + student table ──── --}}
        {{-- Stat cards --}}
        <div style="display:flex;gap:16px;margin-bottom:20px;">
            <div class="dp-stat-card" style="flex:1;">
                <div class="s-value">{{ $avg_galaxy_join ?? 0 }}</div>
                <div class="s-label">{{ __('portal.avg_galaxy_join') }}</div>
            </div>
            <div class="dp-stat-card" style="flex:1;">
                <div class="s-value">{{ \App\Services\ReportService::formatDuration($avg_duration_sec ?? 0) }}</div>
                <div class="s-label">{{ __('portal.avg_duration') }}</div>
            </div>
        </div>

        <div class="dp-card" style="padding:0;">
            <div style="overflow-x:auto;">
                <table class="dp-table">
                    <thead>
                        <tr>
                            <th style="width:40px;">{{ __('portal.no_num') }}</th>
                            <th>{{ __('portal.nav_students') }}</th>
                            <th>{{ __('portal.last_interaction') }}</th>
                            <th>{{ __('portal.total_galaxies_joined') }}</th>
                            <th>{{ __('portal.total_duration') }}</th>
                            <th>{{ __('portal.last_5_galaxies') }}</th>
                            <th>{{ __('portal.action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(($user_stats ?? collect()) as $idx => $stat)
                        <tr>
                            <td class="muted">{{ str_pad($idx + 1, 2, '0', STR_PAD_LEFT) }}</td>
                            <td>
                                <div style="display:flex;align-items:center;gap:10px;">
                                    <div class="av" style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--primary-deep));color:#fff;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:600;">{{ mb_strtoupper(mb_substr($stat['user']->name ?? '', 0, 1) . mb_substr($stat['user']->surname ?? '', 0, 1)) }}</div>
                                    <span style="font-weight:500;">{{ $stat['user']->name }} {{ $stat['user']->surname }}</span>
                                </div>
                            </td>
                            <td class="muted">{{ $stat['last_interaction'] }}</td>
                            <td style="text-align:center;">{{ $stat['total_joined'] }}</td>
                            <td style="text-align:center;">
                                @php $totalDur = $stat['total_duration']; @endphp
                                <span style="display:inline-flex;align-items:center;gap:4px;">
                                    {{ \App\Services\ReportService::formatDuration($totalDur) }}
                                    @if($totalDur < 60)
                                        <svg width="12" height="12" fill="none" stroke="#ef4444" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    @elseif($totalDur < 300)
                                        <svg width="12" height="12" fill="none" stroke="#f59e0b" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    @else
                                        <svg width="12" height="12" fill="none" stroke="#22c55e" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                    @endif
                                </span>
                            </td>
                            <td>
                                <div style="display:flex;gap:4px;">
                                    @foreach(($stat['last_scenarios'] ?? []) as $icon)
                                        <span style="font-size:16px;">{{ $icon }}</span>
                                    @endforeach
                                </div>
                            </td>
                            <td>
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <a href="{{ route('portal.reports.student.role-galaxy', $stat['user']->id) }}" class="dp-action dp-action-view" title="{{ __('portal.role_galaxy_detail') }}">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    <a href="{{ route('portal.reports.student.role-galaxy', $stat['user']->id) }}" style="color:var(--color-primary);font-size:12px;font-weight:500;text-decoration:none;">{{ __('portal.view_details') }} →</a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" style="text-align:center;color:var(--color-txt-muted);padding:32px;">{{ __('portal.no_data_yet') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:16px 20px;font-size:12px;border-top:1px solid var(--color-row-border);">
                <span style="color:var(--color-txt-muted);">{{ ($user_stats ?? collect())->count() }} {{ __('portal.nav_students') }}</span>
                <span style="color:var(--color-txt-muted);">{{ ($user_stats ?? collect())->count() }} {{ __('portal.total') }}</span>
            </div>
        </div>

    @else
        {{-- ── Remaining apps: stat cards + charts + student table ── --}}

        {{-- Stat cards --}}
        <div class="dp-stats-grid" style="margin-bottom:20px;">
            @if($slug === 'way-startup')
                <div class="dp-stat-card"><div class="s-value">{{ $total_progress }}</div><div class="s-label">{{ __('portal.total_progress') }}</div></div>
                <div class="dp-stat-card"><div class="s-value">{{ $total_completed }}</div><div class="s-label">{{ __('portal.completed') }}</div></div>
                <div class="dp-stat-card"><div class="s-value">{{ $avg_score ? number_format($avg_score, 1) : '-' }}</div><div class="s-label">{{ __('portal.avg_score') }}</div></div>
                <div class="dp-stat-card"><div class="s-value">{{ $total_sessions }}</div><div class="s-label">{{ __('portal.sessions') }}</div></div>
                <div class="dp-stat-card"><div class="s-value">{{ \App\Services\ReportService::formatDuration($total_duration ?? 0) }}</div><div class="s-label">{{ __('portal.total_duration') }}</div></div>
            @elseif($slug === 'study-space')
                <div class="dp-stat-card-yellow"><div class="s-value">{{ $total_progress }}</div><div class="s-label">{{ __('portal.total_discussion') }}</div></div>
                <div class="dp-stat-card-yellow"><div class="s-value">{{ $avg_score ? number_format($avg_score, 1) : '-' }}</div><div class="s-label">{{ __('portal.avg_discussion_time') }}</div></div>
                <div class="dp-stat-card"><div class="s-value">{{ $total_sessions }}</div><div class="s-label">{{ __('portal.sessions') }}</div></div>
            @elseif($slug === 'way-ai-coach')
                <div class="dp-stat-card"><div class="s-icon"><svg width="20" height="20" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg></div><div class="s-value">{{ $total_progress }}</div><div class="s-label">{{ __('portal.empathy_score') }}</div></div>
                <div class="dp-stat-card"><div class="s-icon"><svg width="20" height="20" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg></div><div class="s-value">{{ $total_sessions }}</div><div class="s-label">{{ __('portal.interaction_count') }}</div></div>
                <div class="dp-stat-card"><div class="s-icon"><svg width="20" height="20" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div><div class="s-value">{{ \App\Services\ReportService::formatDuration($total_duration ?? 0) }}</div><div class="s-label">{{ __('portal.total_duration') }}</div></div>
            @elseif($slug === 'role-galaxy')
                <div class="dp-stat-card"><div class="s-icon"><svg width="20" height="20" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg></div><div class="s-value">{{ $total_progress }}</div><div class="s-label">{{ __('portal.galaxy_join') }}</div></div>
                <div class="dp-stat-card"><div class="s-value">{{ $total_completed }}</div><div class="s-label">{{ __('portal.roles_completed') }}</div></div>
                <div class="dp-stat-card"><div class="s-value">{{ \App\Services\ReportService::formatDuration($total_duration ?? 0) }}</div><div class="s-label">{{ __('portal.total_duration') }}</div></div>
            @else
                <div class="dp-stat-card"><div class="s-value">{{ $total_progress }}</div><div class="s-label">{{ __('portal.total_progress') }}</div></div>
                <div class="dp-stat-card"><div class="s-value">{{ $total_completed }}</div><div class="s-label">{{ __('portal.completed') }}</div></div>
                <div class="dp-stat-card"><div class="s-value">{{ $avg_score ? number_format($avg_score, 1) : '-' }}</div><div class="s-label">{{ __('portal.avg_score') }}</div></div>
                <div class="dp-stat-card"><div class="s-value">{{ $total_sessions }}</div><div class="s-label">{{ __('portal.sessions') }}</div></div>
                <div class="dp-stat-card"><div class="s-value">{{ \App\Services\ReportService::formatDuration($total_duration ?? 0) }}</div><div class="s-label">{{ __('portal.total_duration') }}</div></div>
            @endif
        </div>

        {{-- Charts --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px;">
            <div class="dp-card">
                <div class="dp-card-title">{{ __('portal.module_distribution') }}</div>
                <canvas id="moduleChart"></canvas>
            </div>
            <div class="dp-card">
                <div class="dp-card-title">{{ __('portal.daily_sessions_30') }}</div>
                <canvas id="sessionsChart"></canvas>
            </div>
        </div>

        {{-- Student table --}}
        <div class="dp-card">
            <div class="dp-card-title">👥 {{ __('portal.student_performance') }}</div>
            <table class="dp-table">
                <thead><tr>
                    @if($slug === 'way-startup')
                        <th>{{ __('portal.startup_name') }}</th>
                        <th>{{ __('portal.type') }}</th>
                        <th>{{ __('portal.nav_students') }}</th>
                        <th>{{ __('portal.deadline') }}</th>
                        <th>{{ __('portal.step') }}</th>
                        <th>{{ __('portal.system_point') }}</th>
                        <th>{{ __('portal.teacher_point') }}</th>
                        <th>{{ __('admin.actions') }}</th>
                    @elseif($slug === 'way-ai-coach')
                        <th>{{ __('portal.nav_students') }}</th>
                        <th>{{ __('portal.interaction_count') }}</th>
                        <th>{{ __('portal.total_duration') }}</th>
                        <th>{{ __('admin.actions') }}</th>
                    @elseif($slug === 'role-galaxy')
                        <th>{{ __('portal.nav_students') }}</th>
                        <th>{{ __('portal.galaxy_selected') }}</th>
                        <th>{{ __('portal.role_played') }}</th>
                        <th>{{ __('portal.total_duration') }}</th>
                        <th>{{ __('admin.actions') }}</th>
                    @elseif($slug === 'study-space')
                        <th>{{ __('portal.nav_students') }}</th>
                        <th>{{ __('portal.discussion_minutes') }}</th>
                        <th>{{ __('portal.discussion_count') }}</th>
                        <th>{{ __('admin.actions') }}</th>
                    @else
                        <th>{{ __('portal.student') }}</th>
                        <th>{{ __('portal.total') }}</th>
                        <th>{{ __('portal.completed') }}</th>
                        <th>{{ __('portal.completion_pct') }}</th>
                        <th>{{ __('portal.avg_score') }}</th>
                        <th>{{ __('portal.duration') }}</th>
                        <th></th>
                    @endif
                </tr></thead>
                <tbody>
                    @forelse($user_stats as $us)
                    <tr @if($slug === 'way-ai-coach' && isset($us['alert']) && $us['alert']) style="background:rgba(227,49,49,0.05);" @endif>
                        <td>
                            <div class="dp-td-avatar">
                                <div class="av">{{ mb_strtoupper(mb_substr($us['user']->name ?? '',0,1).mb_substr($us['user']->surname ?? '',0,1)) }}</div>
                                <span style="font-weight:500;">{{ $us['user']->name ?? '' }} {{ $us['user']->surname ?? '' }}</span>
                            </div>
                        </td>
                        @if($slug === 'way-startup')
                            <td>{{ $us['startup_type'] ?? '-' }}</td>
                            <td>{{ $us['total'] }}</td>
                            <td class="muted">{{ $us['deadline'] ?? '-' }}</td>
                            <td>
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <div class="dp-progress" style="width:60px;"><div class="dp-progress-fill" style="width:{{ $us['completion_rate'] }}%;"></div></div>
                                    <span style="font-size:11px;">{{ $us['completed'] }}/{{ $us['total'] }}</span>
                                </div>
                            </td>
                            <td><span class="dp-badge dp-badge-pending">{{ $us['avg_score'] ? number_format($us['avg_score'],1) : '-' }}</span></td>
                            <td>{{ $us['teacher_score'] ?? '-' }}</td>
                        @elseif($slug === 'way-ai-coach')
                            <td @if(isset($us['alert']) && $us['alert']) style="color:var(--color-error-red);font-weight:600;" @endif>{{ $us['total'] }}</td>
                            <td class="muted">{{ \App\Services\ReportService::formatDuration($us['total_duration'] ?? 0) }}</td>
                        @elseif($slug === 'role-galaxy')
                            <td>{{ $us['galaxy_selected'] ?? '-' }}</td>
                            <td>{{ $us['role_played'] ?? '-' }}</td>
                            <td class="muted">{{ \App\Services\ReportService::formatDuration($us['total_duration'] ?? 0) }}</td>
                        @elseif($slug === 'study-space')
                            <td>{{ $us['discussion_minutes'] ?? '-' }}</td>
                            <td>{{ $us['discussion_count'] ?? $us['total'] }}</td>
                        @else
                            <td>{{ $us['total'] }}</td>
                            <td>{{ $us['completed'] }}</td>
                            <td>
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <div class="dp-progress" style="width:60px;"><div class="dp-progress-fill" style="width:{{ $us['completion_rate'] }}%;"></div></div>
                                    <span style="font-size:12px;">{{ $us['completion_rate'] }}%</span>
                                </div>
                            </td>
                            <td>{{ $us['avg_score'] ? number_format($us['avg_score'], 1) : '-' }}</td>
                            <td class="muted">{{ \App\Services\ReportService::formatDuration($us['total_duration'] ?? 0) }}</td>
                        @endif
                        <td>
                            @if($us['user'])
                            @php
                                $perAppRouteMap = [
                                    'role-galaxy' => 'portal.reports.student.role-galaxy',
                                    'way-ai-coach' => 'portal.reports.student.way-ai-coach',
                                    'study-space' => 'portal.reports.student.study-space',
                                ];
                                $detailRoute = isset($perAppRouteMap[$slug]) ? route($perAppRouteMap[$slug], $us['user']->id) : route('portal.reports.student', $us['user']->id);
                            @endphp
                            <div style="display:flex;align-items:center;gap:8px;">
                                <a href="{{ $detailRoute }}" class="dp-action dp-action-view" title="{{ __('portal.view_details') }}">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                <a href="{{ $detailRoute }}" style="color:var(--color-primary);font-size:12px;font-weight:500;text-decoration:none;">{{ __('portal.view_details') }} →</a>
                            </div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="10" style="text-align:center;color:var(--color-txt-muted);padding:32px;">{{ __('portal.no_data_yet') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif

@else
    {{-- ═══════════════════════════════════════════════════════════════
         PERFORMANCE TAB — Figma F-63
         All apps: Table with gamification score columns
    ═══════════════════════════════════════════════════════════════ --}}

    @if($slug === 'mission-way')
        {{-- Figma F-63: Performance Report table --}}
        <div class="dp-card" style="padding:0;">
            <div style="overflow-x:auto;">
                <table class="dp-table">
                    <thead>
                        <tr>
                            <th style="width:40px;">{{ __('portal.no_num') }}</th>
                            <th>{{ __('portal.mission_name') }}</th>
                            <th>{{ __('portal.nav_students') }}</th>
                            <th>
                                <span style="color:#ef4444;">❤️</span>
                                {{ __('portal.health_point') }}
                            </th>
                            <th>
                                <span style="color:#22c55e;">🌿</span>
                                {{ __('portal.resource_point') }}
                            </th>
                            <th>
                                <span style="color:#f59e0b;">🧡</span>
                                {{ __('portal.ethics_point') }}
                            </th>
                            <th>
                                <span style="color:#22c55e;">✅</span>
                                {{ __('portal.adaptation_point') }}
                            </th>
                            <th>{{ __('portal.action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(($missions ?? collect()) as $mission)
                        <tr>
                            <td class="muted">{{ str_pad($mission->id, 2, '0', STR_PAD_LEFT) }}</td>
                            <td style="font-weight:500;">{{ $mission->name }}</td>
                            <td>
                                <div style="display:flex;align-items:center;">
                                    @foreach($mission->students->take(4) as $si => $st)
                                    <div style="width:30px;height:30px;border-radius:50%;background:{{ ['#4364F7','#8b5cf6','#f59e0b','#ef4444','#10b981'][$si % 5] }};color:#fff;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:600;border:2px solid var(--color-card-bg);margin-left:{{ $si > 0 ? '-8px' : '0' }};position:relative;z-index:{{ 10 - $si }};">
                                        {{ mb_strtoupper(mb_substr($st->name,0,1)) }}{{ mb_strtoupper(mb_substr($st->surname,0,1)) }}
                                    </div>
                                    @endforeach
                                </div>
                            </td>
                            <td>
                                @if($mission->health_point !== null)
                                    <span style="font-weight:500;">{{ $mission->health_point }}
                                        @if($mission->health_trend === 'up')<span style="color:#22c55e;">↑</span>@else<span style="color:#ef4444;">↓</span>@endif
                                    </span>
                                @else <span class="muted">-</span> @endif
                            </td>
                            <td>
                                @if($mission->resource_point !== null)
                                    <span style="font-weight:500;">{{ $mission->resource_point }}
                                        @if($mission->resource_trend === 'up')<span style="color:#22c55e;">↑</span>@else<span style="color:#ef4444;">↓</span>@endif
                                    </span>
                                @else <span class="muted">-</span> @endif
                            </td>
                            <td>
                                @if($mission->ethics_point !== null)
                                    <span style="font-weight:500;">{{ $mission->ethics_point }}
                                        @if($mission->ethics_trend === 'up')<span style="color:#22c55e;">↑</span>@else<span style="color:#ef4444;">↓</span>@endif
                                    </span>
                                @else <span class="muted">-</span> @endif
                            </td>
                            <td>
                                @if($mission->adaptation_point !== null)
                                    <span style="font-weight:500;">{{ $mission->adaptation_point }}
                                        @if($mission->adaptation_trend === 'up')<span style="color:#22c55e;">↑</span>@else<span style="color:#ef4444;">↓</span>@endif
                                    </span>
                                @else <span class="muted">-</span> @endif
                            </td>
                            <td>
                                <a href="{{ route('portal.reports.mission.detail', $mission->id) }}" style="color:var(--color-primary);font-size:13px;font-weight:500;text-decoration:none;">{{ __('portal.view_details') }}</a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="8" style="text-align:center;color:var(--color-txt-muted);padding:32px;">{{ __('portal.no_data_yet') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:16px 20px;font-size:12px;border-top:1px solid var(--color-row-border);">
                <span style="color:var(--color-txt-muted);">{{ ($missions ?? collect())->count() }} {{ __('portal.missions') }}</span>
                <span style="color:var(--color-txt-muted);">{{ ($missions ?? collect())->count() }} {{ __('portal.total') }}</span>
            </div>
        </div>
    @else
        {{-- Non-mission-way performance: student performance table --}}
        <div class="dp-card">
            <div class="dp-card-title">👥 {{ __('portal.student_performance') }}</div>
            <table class="dp-table">
                <thead><tr>
                    <th>{{ __('portal.student') }}</th>
                    <th>{{ __('portal.total') }}</th>
                    <th>{{ __('portal.completed') }}</th>
                    <th>{{ __('portal.completion_pct') }}</th>
                    <th>{{ __('portal.avg_score') }}</th>
                    <th>{{ __('portal.duration') }}</th>
                    <th></th>
                </tr></thead>
                <tbody>
                    @forelse($user_stats as $us)
                    <tr>
                        <td>
                            <div class="dp-td-avatar">
                                <div class="av">{{ mb_strtoupper(mb_substr($us['user']->name ?? '',0,1).mb_substr($us['user']->surname ?? '',0,1)) }}</div>
                                <span style="font-weight:500;">{{ $us['user']->name ?? '' }} {{ $us['user']->surname ?? '' }}</span>
                            </div>
                        </td>
                        <td>{{ $us['total'] }}</td>
                        <td>{{ $us['completed'] }}</td>
                        <td>
                            <div style="display:flex;align-items:center;gap:8px;">
                                <div class="dp-progress" style="width:60px;"><div class="dp-progress-fill" style="width:{{ $us['completion_rate'] }}%;"></div></div>
                                <span style="font-size:12px;">{{ $us['completion_rate'] }}%</span>
                            </div>
                        </td>
                        <td>{{ $us['avg_score'] ? number_format($us['avg_score'], 1) : '-' }}</td>
                        <td class="muted">{{ \App\Services\ReportService::formatDuration($us['total_duration'] ?? 0) }}</td>
                        <td>
                            @if($us['user'])
                            <div style="display:flex;align-items:center;gap:8px;">
                                <a href="{{ route('portal.reports.student', $us['user']->id ?? 0) }}?app={{ $slug }}" class="dp-action dp-action-view" title="{{ __('portal.student_report') }}">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                <a href="{{ route('portal.reports.student', $us['user']->id ?? 0) }}?app={{ $slug }}" style="color:var(--color-primary);font-size:12px;font-weight:500;text-decoration:none;">{{ __('portal.view_details') }} →</a>
                            </div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" style="text-align:center;color:var(--color-txt-muted);padding:32px;">{{ __('portal.no_data_yet') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif
@endif

{{-- ═══ API CATALOG DATA — Anlık API Verileri ═══ --}}

{{-- ── Wings / Rozetler (tüm Vega apps) ────────────────── --}}
@if(!empty($wings) && $wings->count() > 0)
<div class="dp-card" style="margin-top:24px;">
    <div class="dp-card-title">🦋 {{ __('portal.way_wings_badges') }}</div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:12px;margin-top:12px;">
        @foreach($wings as $wing)
        <div style="display:flex;align-items:center;gap:10px;padding:12px;border-radius:10px;background:var(--color-input-bg);border:1px solid var(--color-row-border);">
            @if(!empty($wing['iconUrl'] ?? $wing['icon']))
                <img src="{{ $wing['iconUrl'] ?? $wing['icon'] }}" alt="" style="width:36px;height:36px;border-radius:8px;">
            @else
                <div style="width:36px;height:36px;border-radius:8px;background:linear-gradient(135deg,#8B5CF6,#06B6D4);display:flex;align-items:center;justify-content:center;font-size:16px;">🦋</div>
            @endif
            <div>
                <div style="font-weight:600;font-size:13px;">{{ $wing['name'] ?? $wing['title'] ?? __('portal.wing') }}</div>
                <div style="font-size:11px;color:var(--color-txt-muted);">{{ $wing['pointsRequired'] ?? $wing['points'] ?? 0 }} {{ __('portal.pts') }}</div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- ── Lessons / Ders Kataloğu (way-ai-coach) ────────── --}}
@if(!empty($lessons) && $lessons->count() > 0)
<div class="dp-card" style="margin-top:24px;">
    <div class="dp-card-title">📚 {{ __('portal.lesson_catalog') }}</div>
    <div style="overflow-x:auto;margin-top:12px;">
        <table class="dp-table">
            <thead>
                <tr>
                    <th style="width:40px;">#</th>
                    <th>{{ __('portal.title') }}</th>
                    <th>{{ __('portal.category') }}</th>
                    <th>{{ __('portal.difficulty') }}</th>
                    <th>{{ __('portal.duration') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($lessons as $i => $lesson)
                <tr>
                    <td class="muted">{{ $i + 1 }}</td>
                    <td style="font-weight:500;">
                        <div style="display:flex;align-items:center;gap:8px;">
                            @if(!empty($lesson['iconUrl'] ?? $lesson['icon']))
                                <img src="{{ $lesson['iconUrl'] ?? $lesson['icon'] }}" alt="" style="width:28px;height:28px;border-radius:6px;">
                            @endif
                            {{ $lesson['title'] ?? $lesson['name'] ?? __('portal.lesson') }}
                        </div>
                    </td>
                    <td><span class="dp-badge dp-badge-pending">{{ $lesson['category'] ?? $lesson['type'] ?? '-' }}</span></td>
                    <td class="muted">{{ $lesson['difficulty'] ?? $lesson['level'] ?? '-' }}</td>
                    <td class="muted">{{ ($lesson['duration'] ?? $lesson['durationMinutes'] ?? '-') }} {{ __('portal.min') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- ── Scenarios / Senaryolar (role-galaxy) ─────────── --}}
@if(!empty($scenarios) && $scenarios->count() > 0)
<div class="dp-card" style="margin-top:24px;">
    <div class="dp-card-title">🎮 {{ __('portal.scenario_catalog') }}</div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:14px;margin-top:12px;">
        @foreach($scenarios as $scenario)
        <div style="padding:16px;border-radius:12px;background:var(--color-input-bg);border:1px solid var(--color-row-border);transition:transform 0.15s ease;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
                @if(!empty($scenario['iconUrl'] ?? $scenario['icon']))
                    <img src="{{ $scenario['iconUrl'] ?? $scenario['icon'] }}" alt="" style="width:32px;height:32px;border-radius:8px;">
                @else
                    <div style="width:32px;height:32px;border-radius:8px;background:linear-gradient(135deg,#4364F7,#8B5CF6);display:flex;align-items:center;justify-content:center;font-size:14px;">🎭</div>
                @endif
                <div style="font-weight:600;font-size:13px;">{{ $scenario['title'] ?? $scenario['name'] ?? __('portal.scenario') }}</div>
            </div>
            @if(!empty($scenario['description']))
                <div style="font-size:11px;color:var(--color-txt-muted);line-height:1.4;">{{ Str::limit($scenario['description'], 80) }}</div>
            @endif
            <div style="display:flex;gap:8px;margin-top:8px;">
                @if(!empty($scenario['category'] ?? $scenario['type']))
                    <span class="dp-badge dp-badge-pending" style="font-size:10px;">{{ $scenario['category'] ?? $scenario['type'] }}</span>
                @endif
                @if(!empty($scenario['difficulty'] ?? $scenario['level']))
                    <span class="dp-badge" style="font-size:10px;background:rgba(139,92,246,0.1);color:#8b5cf6;">{{ $scenario['difficulty'] ?? $scenario['level'] }}</span>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- ── Objectives / Hedefler (mission-way) ──────────── --}}
@if(!empty($objectives) && $objectives->count() > 0)
<div class="dp-card" style="margin-top:24px;">
    <div class="dp-card-title">🎯 {{ __('portal.simulation_objectives') }}</div>
    <div style="overflow-x:auto;margin-top:12px;">
        <table class="dp-table">
            <thead>
                <tr>
                    <th style="width:40px;">#</th>
                    <th>{{ __('admin.name') }}</th>
                    <th>{{ __('portal.key') }}</th>
                    <th>{{ __('portal.description') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($objectives as $i => $obj)
                <tr>
                    <td class="muted">{{ $i + 1 }}</td>
                    <td style="font-weight:500;">{{ $obj['name'] ?? $obj['title'] ?? __('portal.objective') }}</td>
                    <td><code style="font-size:11px;background:var(--color-input-bg);padding:2px 6px;border-radius:4px;">{{ $obj['key'] ?? $obj['slug'] ?? '-' }}</code></td>
                    <td class="muted" style="max-width:300px;">{{ Str::limit($obj['description'] ?? '-', 60) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- ── Media Assets (mission-way) (GİZLENDİ) ──────────────────── --}}
{{-- 
@if(!empty($mediaAssets) && $mediaAssets->count() > 0)
<div class="dp-card" style="margin-top:24px;">
    ... (Kullanılmayan/Çalışmayan medya assetleri bloğu)
</div>
@endif 
--}}

{{-- ── SimulationWing Stats (mission-way) ──────────── --}}
@if(!empty($simWingStats))
<div class="dp-card" style="margin-top:24px;">
    <div class="dp-card-title">📊 {{ __('portal.simulation_wing_stats') }}</div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:12px;margin-top:12px;">
        @foreach((array)$simWingStats as $key => $value)
            @if(!is_array($value))
            <div class="dp-stat-card">
                <div class="s-value">{{ is_numeric($value) ? number_format($value) : $value }}</div>
                <div class="s-label">{{ Str::headline($key) }}</div>
            </div>
            @endif
        @endforeach
    </div>
</div>
@endif

{{-- ── Simulation Version Roles (mission-way) (GİZLENDİ) ────── --}}
{{-- 
@if(!empty($simVersionRoles) && $simVersionRoles->count() > 0)
<div class="dp-card" style="margin-top:24px;">
    <div class="dp-card-title">🎭 {{ __('portal.version_roles') }}</div>
    <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:12px;">
        @foreach($simVersionRoles as $role)
        <span style="display:inline-flex;align-items:center;gap:6px;padding:6px 14px;border-radius:20px;background:var(--color-input-bg);border:1px solid var(--color-row-border);font-size:12px;font-weight:500;">
            🎭 {{ $role['name'] ?? $role['roleName'] ?? 'Role #' . ($role['id'] ?? '') }}
        </span>
        @endforeach
    </div>
</div>
@endif 
--}}

{{-- ── Languages (mission-way) ─────────────────────── --}}
@if(!empty($languages) && $languages->count() > 0)
<div class="dp-card" style="margin-top:24px;">
    <div class="dp-card-title">🌐 {{ __('portal.supported_languages') }}</div>
    <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:12px;">
        @foreach($languages as $lang)
        <span style="display:inline-flex;align-items:center;gap:6px;padding:6px 14px;border-radius:20px;background:linear-gradient(135deg,rgba(6,182,212,0.1),rgba(139,92,246,0.1));border:1px solid rgba(6,182,212,0.2);font-size:12px;font-weight:500;">
            🌍 {{ $lang['name'] ?? $lang['code'] ?? 'Language' }}
        </span>
        @endforeach
    </div>
</div>
@endif

{{-- Assignment modal is rendered inline below in @section('scripts') --}}

@endsection

@section('scripts')
@if($tab !== 'assignment' || $slug !== 'mission-way')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
Chart.defaults.color = '#8B8D97';
Chart.defaults.borderColor = 'rgba(0,0,0,0.06)';
@if(($module_stats ?? collect())->count())
if (document.getElementById('moduleChart')) {
new Chart(document.getElementById('moduleChart'), {
    type: 'doughnut',
    data: { labels: {!! json_encode(($module_stats ?? collect())->keys()) !!}, datasets: [{ data: {!! json_encode(($module_stats ?? collect())->pluck('total')->values()) !!}, backgroundColor: ['#4364F7','#8b5cf6','#f59e0b','#ef4444','#10b981'], borderWidth: 0 }] },
    options: { responsive: true, plugins: { legend: { position: 'bottom', labels: { padding: 12, usePointStyle: true } } } }
});
}
@endif
@if(($sessions_by_day ?? collect())->count())
if (document.getElementById('sessionsChart')) {
new Chart(document.getElementById('sessionsChart'), {
    type: 'line',
    data: { labels: {!! json_encode(($sessions_by_day ?? collect())->keys()) !!}, datasets: [{ label: 'Sessions', data: {!! json_encode(($sessions_by_day ?? collect())->values()) !!}, borderColor: '#4364F7', backgroundColor: 'rgba(67,100,247,0.1)', fill: true, tension: 0.4, pointRadius: 3 }] },
    options: { responsive: true, scales: { y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.04)' } }, x: { grid: { display: false }, ticks: { maxRotation: 45 } } }, plugins: { legend: { display: false } } }
});
}
@endif
</script>
@endif

{{-- ═══ Add Assignment / Mission Modal — Figma Design ═══ --}}
@if(!in_array($slug, ['role-galaxy', 'study-space', 'way-ai-coach']))
<div id="addAssignmentModal" class="figma-modal-overlay" onclick="if(event.target===this)this.classList.remove('show')">
    <div class="figma-modal" onclick="event.stopPropagation()">
        {{-- Close button --}}
        <button type="button" class="figma-modal-close" onclick="document.getElementById('addAssignmentModal').classList.remove('show')">
            <svg width="20" height="20" fill="none" stroke="#6B7280" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>

        {{-- Title --}}
        <h3 class="figma-modal-title">
            @if($slug === 'mission-way')
                {{ __('portal.add_new') }}
            @else
                {{ __('portal.add_new') }}
            @endif
        </h3>
        <p class="figma-modal-subtitle">{{ __('portal.assignment_subtitle') }}</p>

        @if($slug === 'mission-way')
        {{-- ═══ MISSION WAY FORM ═══ --}}
        <form action="{{ route('portal.assignments.mw.store') }}" method="POST" id="mwAssignForm">
            @csrf

            {{-- Grade --}}
            <div class="figma-field">
                <label class="figma-label">{{ __('portal.grade') }}</label>
                <select name="grade" class="figma-select" id="mwGradeSelect">
                    <option value="">{{ __('portal.please_select') }}</option>
                    @foreach(range(1, 12) as $g)
                        <option value="{{ $g }}">{{ $g }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Student (multi-select — MW requires exact player count) --}}
            <div class="figma-field">
                <label class="figma-label">{{ __('portal.student') }} <span id="mwSelectedCount" style="font-weight:500;color:#3B5BDB;font-size:12px;"></span></label>
                <div class="figma-student-list" id="mwStudentList">
                    @forelse($mw_students ?? [] as $student)
                    <label class="figma-student-item">
                        <input type="checkbox" name="user_ids[]" value="{{ $student->id }}" class="mw-student-cb" data-grade="{{ $student->grade ?? '' }}">
                        <span>{{ $student->name }} {{ $student->surname }}</span>
                    </label>
                    @empty
                    <div style="padding:16px;text-align:center;color:#9CA3AF;font-size:13px;">{{ __('portal.no_mw_students') }}</div>
                    @endforelse
                </div>
                <div id="mwCountWarning" class="figma-warning" style="display:none;">
                    ⚠️ <span id="mwCountWarningText"></span>
                </div>
            </div>

            {{-- Mission (simulation) --}}
            <div class="figma-field">
                <label class="figma-label">{{ __('portal.mission') }}</label>
                <select name="simulation_id" required class="figma-select" id="mwSimSelect">
                    <option value="" data-role-count="0">{{ __('portal.please_select') }}</option>
                    @foreach($mw_simulations ?? [] as $sim)
                        <option value="{{ $sim->id }}" data-role-count="{{ $sim->role_count }}">{{ $sim->name }}</option>
                    @endforeach
                </select>
                <div id="mwRoleHint" class="figma-hint" style="display:none;">
                    ℹ️ This mission requires exactly <strong id="mwRoleCount">4</strong> players.
                </div>
            </div>

            {{-- Deadline --}}
            <div class="figma-field">
                <label class="figma-label">{{ __('portal.deadline') }}</label>
                <input type="date" name="deadline" required min="{{ now()->format('Y-m-d') }}" class="figma-input" placeholder="{{ __('portal.please_select') }}">
            </div>

            <button type="submit" id="mwSubmitBtn" class="figma-submit-btn">
                Save Information
            </button>
        </form>

        @else
        {{-- ═══ WAY STARTUP FORM ═══ --}}
        <form action="{{ route('portal.assignments.ws.store') }}" method="POST" id="wsAssignForm">
            @csrf

            {{-- Grade --}}
            <div class="figma-field">
                <label class="figma-label">{{ __('portal.grade') }}</label>
                <select name="grade" class="figma-select" id="wsGradeSelect">
                    <option value="">{{ __('portal.please_select') }}</option>
                    @foreach(range(1, 12) as $g)
                        <option value="{{ $g }}">{{ $g }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Student (checkbox list — same design as MW) --}}
            <div class="figma-field">
                <label class="figma-label">{{ __('portal.student') }} <span id="wsSelectedCount" style="font-weight:500;color:#3B5BDB;font-size:12px;"></span></label>
                <div class="figma-student-list" id="wsStudentList">
                    @forelse($ws_students ?? [] as $student)
                    <label class="figma-student-item">
                        <input type="checkbox" name="user_ids[]" value="{{ $student->id }}" class="ws-student-cb" data-grade="{{ $student->grade ?? '' }}">
                        <span>{{ $student->name }} {{ $student->surname }}</span>
                    </label>
                    @empty
                    <div style="padding:16px;text-align:center;color:#9CA3AF;font-size:13px;">{{ __('portal.no_ws_students') }}</div>
                    @endforelse
                </div>
            </div>

            {{-- Business (simulation) --}}
            <div class="figma-field">
                <label class="figma-label">{{ __('portal.business') }}</label>
                <select name="simulation_id" required class="figma-select">
                    <option value="">{{ __('portal.please_select') }}</option>
                    @foreach($ws_simulations ?? [] as $sim)
                        <option value="{{ $sim->id }}">{{ $sim->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Deadline --}}
            <div class="figma-field">
                <label class="figma-label">{{ __('portal.deadline') }}</label>
                <input type="date" name="due_date" required min="{{ now()->format('Y-m-d') }}" class="figma-input" placeholder="{{ __('portal.please_select') }}">
            </div>

            <button type="submit" class="figma-submit-btn">
                Save Information
            </button>
        </form>
        @endif
    </div>
</div>

{{-- ═══ Success / Error Toast ═══ --}}
@if(session('success'))
<div id="successToast" class="figma-toast figma-toast-success">
    <span class="figma-toast-icon">✅</span>
    <span>{{ session('success') }}</span>
    <button onclick="this.parentElement.remove()" class="figma-toast-close">✕</button>
</div>
@endif
@if($errors->any())
<div id="errorToast" class="figma-toast figma-toast-error">
    <span class="figma-toast-icon">⚠️</span>
    <span>{{ $errors->first() }}</span>
    <button onclick="this.parentElement.remove()" class="figma-toast-close">✕</button>
</div>
@endif

<style>
/* ═══ Figma Modal Styles ═══ */
.figma-modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 9999;
    background: rgba(0, 0, 0, 0.35);
    backdrop-filter: blur(4px);
    align-items: center;
    justify-content: center;
}
.figma-modal-overlay.show {
    display: flex !important;
    animation: figmaFadeIn 0.25s ease;
}
@keyframes figmaFadeIn { from { opacity:0; transform:scale(0.96); } to { opacity:1; transform:scale(1); } }

.figma-modal {
    background: #fff;
    border-radius: 16px;
    padding: 32px;
    max-width: 460px;
    width: 92%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 24px 64px rgba(0, 0, 0, 0.18);
    position: relative;
}

.figma-modal-close {
    position: absolute;
    top: 20px;
    right: 20px;
    background: none;
    border: none;
    cursor: pointer;
    padding: 4px;
    border-radius: 6px;
    transition: background 0.15s;
}
.figma-modal-close:hover { background: #F3F4F6; }

.figma-modal-title {
    font-size: 20px;
    font-weight: 800;
    color: #111827;
    margin: 0 0 4px 0;
    font-family: 'Nunito', sans-serif;
    letter-spacing: -0.3px;
}

.figma-modal-subtitle {
    font-size: 13px;
    color: #6B7280;
    margin: 0 0 24px 0;
    font-family: 'Nunito', sans-serif;
}

.figma-field {
    margin-bottom: 18px;
}

.figma-label {
    display: block;
    font-size: 14px;
    font-weight: 700;
    color: #111827;
    margin-bottom: 8px;
    font-family: 'Nunito', sans-serif;
}

.figma-select,
.figma-input {
    width: 100%;
    padding: 12px 14px;
    border: 1.5px solid #E5E7EB;
    border-radius: 10px;
    font-size: 14px;
    color: #111827;
    background: #F9FAFB;
    font-family: 'Nunito', sans-serif;
    transition: border-color 0.2s, box-shadow 0.2s;
    appearance: auto;
    -webkit-appearance: auto;
    outline: none;
    box-sizing: border-box;
}
.figma-select:focus,
.figma-input:focus {
    border-color: #3B5BDB;
    box-shadow: 0 0 0 3px rgba(59, 91, 219, 0.08);
    background: #fff;
}

.figma-hint {
    margin-top: 6px;
    padding: 8px 12px;
    background: #EFF6FF;
    border: 1px solid #BFDBFE;
    border-radius: 8px;
    font-size: 12px;
    color: #1E40AF;
    font-family: 'Nunito', sans-serif;
}

.figma-submit-btn {
    width: 100%;
    padding: 14px;
    background: linear-gradient(135deg, #3B5BDB 0%, #2B4ACB 100%);
    color: #fff;
    border: none;
    border-radius: 10px;
    font-size: 15px;
    font-weight: 700;
    font-family: 'Nunito', sans-serif;
    cursor: pointer;
    transition: transform 0.15s, box-shadow 0.15s;
    margin-top: 6px;
}
.figma-submit-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(59, 91, 219, 0.35);
}
.figma-submit-btn:active {
    transform: translateY(0);
}

/* ═══ Toast Notification ═══ */
.figma-toast {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 10000;
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px 20px;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 600;
    font-family: 'Nunito', sans-serif;
    box-shadow: 0 8px 30px rgba(0,0,0,0.15);
    animation: toastSlideIn 0.4s ease;
    max-width: 400px;
}
.figma-toast-success {
    background: #10B981;
    color: #fff;
}
.figma-toast-error {
    background: #EF4444;
    color: #fff;
}
.figma-toast-icon { font-size: 16px; }
.figma-toast-close {
    background: none;
    border: none;
    color: rgba(255,255,255,0.8);
    font-size: 16px;
    cursor: pointer;
    margin-left: 8px;
    padding: 0 2px;
}
.figma-toast-close:hover { color: #fff; }

@keyframes toastSlideIn {
    from { transform: translateY(20px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

/* ═══ MW Student Checkbox List ═══ */
.figma-student-list {
    max-height: 180px;
    overflow-y: auto;
    border: 1.5px solid #E5E7EB;
    border-radius: 10px;
    padding: 6px;
    background: #F9FAFB;
}
.figma-student-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 10px;
    cursor: pointer;
    font-size: 14px;
    font-family: 'Nunito', sans-serif;
    color: #111827;
    border-radius: 6px;
    transition: background 0.1s;
}
.figma-student-item:hover { background: #EFF6FF; }
.figma-student-item input[type="checkbox"] { accent-color: #3B5BDB; width: 16px; height: 16px; }

.figma-warning {
    margin-top: 6px;
    padding: 8px 12px;
    background: #FEF3C7;
    border: 1px solid #FCD34D;
    border-radius: 8px;
    font-size: 12px;
    color: #92400E;
    font-family: 'Nunito', sans-serif;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {

    // ═══ WS: Grade → Student filter (checkbox list — same as MW) ═══
    (function() {
        var gradeSelect = document.getElementById('wsGradeSelect');
        var studentList = document.getElementById('wsStudentList');
        if (!gradeSelect || !studentList) return;

        var allItems = Array.from(studentList.querySelectorAll('.figma-student-item'));

        gradeSelect.addEventListener('change', function() {
            var grade = this.value;
            allItems.forEach(function(item) {
                var cb = item.querySelector('input[type="checkbox"]');
                var cbGrade = cb ? cb.getAttribute('data-grade') : '';
                if (!grade || cbGrade === grade || !cbGrade) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                    if (cb) cb.checked = false;
                }
            });
            updateWsSelectedCount();
        });
    })();

    // ═══ WS: Selected count display ═══
    function updateWsSelectedCount() {
        var count = document.querySelectorAll('.ws-student-cb:checked').length;
        var el = document.getElementById('wsSelectedCount');
        if (el) el.textContent = count > 0 ? '— ' + count + ' selected' : '';
    }
    document.querySelectorAll('.ws-student-cb').forEach(function(cb) {
        cb.addEventListener('change', updateWsSelectedCount);
    });

    // WS form submit validation
    var wsForm = document.getElementById('wsAssignForm');
    if (wsForm) {
        wsForm.addEventListener('submit', function(e) {
            var checked = document.querySelectorAll('.ws-student-cb:checked').length;
            if (checked === 0) {
                e.preventDefault();
                alert('{{ __("portal.select_at_least_one_student") }}');
            }
        });
    }

    // ═══ MW: Grade → Student filter (checkbox list) ═══
    (function() {
        var gradeSelect = document.getElementById('mwGradeSelect');
        var studentList = document.getElementById('mwStudentList');
        if (!gradeSelect || !studentList) return;

        var allItems = Array.from(studentList.querySelectorAll('.figma-student-item'));

        gradeSelect.addEventListener('change', function() {
            var grade = this.value;
            allItems.forEach(function(item) {
                var cb = item.querySelector('input[type="checkbox"]');
                var cbGrade = cb ? cb.getAttribute('data-grade') : '';
                if (!grade || cbGrade === grade || !cbGrade) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                    if (cb) cb.checked = false; // Uncheck hidden
                }
            });
            updateMwUI();
        });
    })();

    // ═══ MW: Role count validation ═══
    var simSelect = document.getElementById('mwSimSelect');
    var hint = document.getElementById('mwRoleHint');
    var roleCountEl = document.getElementById('mwRoleCount');
    var selectedCountEl = document.getElementById('mwSelectedCount');
    var warningEl = document.getElementById('mwCountWarning');
    var warningText = document.getElementById('mwCountWarningText');
    var submitBtn = document.getElementById('mwSubmitBtn');

    function getRequiredCount() {
        if (!simSelect) return 0;
        var opt = simSelect.options[simSelect.selectedIndex];
        return parseInt(opt.getAttribute('data-role-count') || '0');
    }

    function getCheckedCount() {
        return document.querySelectorAll('.mw-student-cb:checked').length;
    }

    function updateMwUI() {
        var required = getRequiredCount();
        var checked = getCheckedCount();

        // Hint
        if (hint) {
            if (required > 0) {
                hint.style.display = 'block';
                roleCountEl.textContent = required;
            } else {
                hint.style.display = 'none';
            }
        }

        // Selected count
        if (selectedCountEl) {
            selectedCountEl.textContent = checked > 0 ? '— ' + checked + ' selected' : '';
        }

        // Warning
        if (warningEl && warningText) {
            if (required > 0 && checked > 0 && checked !== required) {
                warningEl.style.display = 'block';
                warningText.textContent = '{{ __("portal.you_selected") }} ' + checked + ' students, this mission requires exactly ' + required + ' players.';
                if (submitBtn) submitBtn.style.opacity = '0.5';
            } else {
                warningEl.style.display = 'none';
                if (submitBtn) submitBtn.style.opacity = '1';
            }
        }
    }

    if (simSelect) {
        simSelect.addEventListener('change', updateMwUI);
    }

    var checkboxes = document.querySelectorAll('.mw-student-cb');
    checkboxes.forEach(function(cb) { cb.addEventListener('change', updateMwUI); });

    // MW form submit validation
    var mwForm = document.getElementById('mwAssignForm');
    if (mwForm) {
        mwForm.addEventListener('submit', function(e) {
            var required = getRequiredCount();
            var checked = getCheckedCount();
            if (checked === 0) {
                e.preventDefault();
                alert('{{ __("portal.select_at_least_one_student") }}');
                return;
            }
            if (required > 0 && checked !== required) {
                e.preventDefault();
                alert('This mission requires exactly ' + required + ' players. You selected ' + checked + '.');
            }
        });
    }

    // ═══ Auto-dismiss toasts ═══
    var toasts = document.querySelectorAll('.figma-toast');
    toasts.forEach(function(t) {
        setTimeout(function() {
            t.style.animation = 'toastSlideIn 0.3s ease reverse forwards';
            setTimeout(function() { t.remove(); }, 300);
        }, 5000);
    });
});
</script>

@if($errors->any())
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var modal = document.getElementById('addAssignmentModal');
        if (modal) modal.classList.add('show');
    });
</script>
@endif
@endif

@endsection
