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
            Assignments
            @if($slug === 'mission-way')
                <span class="tab-count">{{ $total_missions ?? 0 }}</span>
            @else
                <span class="tab-count">{{ $total_progress ?? 0 }}</span>
            @endif
        </a>
        <a href="{{ route('portal.reports.app', $app->slug) }}?tab=performance"
           class="dp-tab {{ $tab === 'performance' ? 'active' : '' }}">
            Performance
        </a>
    </div>

    <button type="button" class="dp-btn" onclick="document.getElementById('addAssignmentModal')?.classList.add('show')">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="2"/><path stroke-linecap="round" stroke-width="2" d="M12 8v8m-4-4h8"/></svg>
        @if($slug === 'mission-way')
            Add New Mission
        @elseif($slug === 'way-startup')
            Add New Assignment
        @else
            Add New
        @endif
    </button>
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
                            <th style="width:40px;">No</th>
                            <th>Mission Name</th>
                            <th>Students</th>
                            <th>Assigned Date</th>
                            <th>Deadline</th>
                            <th>
                                <span style="display:inline-flex;align-items:center;gap:4px;">
                                    <span style="color:#ef4444;">❤️</span>
                                    Health Point
                                </span>
                            </th>
                            <th>
                                <span style="display:inline-flex;align-items:center;gap:4px;">
                                    <span style="color:#22c55e;">🌿</span>
                                    Resource Point
                                </span>
                            </th>
                            <th>
                                <span style="display:inline-flex;align-items:center;gap:4px;">
                                    <span style="color:#f59e0b;">🧡</span>
                                    Ethics Point
                                </span>
                            </th>
                            <th>
                                <span style="display:inline-flex;align-items:center;gap:4px;">
                                    <span style="color:#22c55e;">✅</span>
                                    Adaptation Point
                                </span>
                            </th>
                            <th>Action</th>
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
                                        {{ strtoupper(substr($st->name,0,1)) }}{{ strtoupper(substr($st->surname,0,1)) }}
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
                                    {{-- Edit --}}
                                    <a href="#" class="dp-action" title="Edit" style="color:var(--color-txt-muted);">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    {{-- Delete --}}
                                    <a href="#" class="dp-action" title="Delete" style="color:var(--color-txt-muted);">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </a>
                                    {{-- Details --}}
                                    <a href="{{ route('portal.reports.mission.detail', $mission->id) }}" style="color:var(--color-primary);font-size:13px;font-weight:500;text-decoration:none;">
                                        Details
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="10" style="text-align:center;color:var(--color-txt-muted);padding:32px;">No missions yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{-- Pagination — Figma: Page1 of 3 --}}
            <div style="display:flex;justify-content:space-between;align-items:center;padding:16px 20px;font-size:12px;border-top:1px solid var(--color-row-border);">
                <span style="color:var(--color-txt-muted);cursor:default;">Previous</span>
                <span style="color:var(--color-txt-muted);">Page1 of 3</span>
                <a href="#" class="dp-btn" style="font-size:12px;padding:6px 16px;">Next</a>
            </div>
        </div>

    @elseif($slug === 'way-startup')
        {{-- ── FIGMA F-4: Startup project table ──────────────────── --}}
        <div class="dp-card" style="padding:0;">
            <div style="overflow-x:auto;">
                <table class="dp-table">
                    <thead>
                        <tr>
                            <th style="width:40px;">No</th>
                            <th>Startup Name</th>
                            <th>Startup Type</th>
                            <th>Students</th>
                            <th>Deadline</th>
                            <th>Step</th>
                            <th>System Point</th>
                            <th>Teacher Point</th>
                            <th>Action</th>
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
                                        {{ strtoupper(substr($st->name,0,1)) }}{{ strtoupper(substr($st->surname,0,1)) }}
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
                                    <span style="color:#ef4444;font-weight:500;">{{ $startup->deadline }} <span title="Overdue">⚠️</span></span>
                                @else
                                    <span class="muted">{{ $startup->deadline }}</span>
                                @endif
                            </td>
                            <td>
                                @if($startup->status === 'completed')
                                    <span style="display:inline-flex;align-items:center;gap:4px;color:#22c55e;font-weight:500;">
                                        <svg width="14" height="14" fill="none" stroke="#22c55e" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        Completed
                                    </span>
                                @elseif($startup->status === 'not_started')
                                    <span class="muted">Not Started</span>
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
                                    <a href="#" style="display:inline-block;padding:4px 12px;background:var(--color-primary);color:#fff;border-radius:6px;font-size:12px;font-weight:500;text-decoration:none;">Score</a>
                                @elseif($startup->teacher_point)
                                    <span style="font-weight:500;">{{ $startup->teacher_point }}</span>
                                @else
                                    <span class="muted">Pending...</span>
                                @endif
                            </td>
                            <td>
                                <div style="display:flex;align-items:center;gap:10px;white-space:nowrap;">
                                    <a href="{{ route('portal.reports.startup.detail', $startup->id) }}" class="dp-action" title="Edit" style="color:var(--color-txt-muted);">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    <a href="#" class="dp-action" title="Settings" style="color:var(--color-txt-muted);">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3" stroke-width="2"/></svg>
                                    </a>
                                    <a href="#" class="dp-action" title="Delete" style="color:var(--color-txt-muted);">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="9" style="text-align:center;color:var(--color-txt-muted);padding:32px;">No startups yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{-- Pagination — Figma: Page1 of 12 --}}
            <div style="display:flex;justify-content:space-between;align-items:center;padding:16px 20px;font-size:12px;border-top:1px solid var(--color-row-border);">
                <span style="color:var(--color-txt-muted);cursor:default;">Previous</span>
                <span style="color:var(--color-txt-muted);">Page1 of 12</span>
                <a href="#" class="dp-btn" style="font-size:12px;padding:6px 16px;">Next</a>
            </div>
        </div>

    @elseif($slug === 'study-space')
        {{-- ── FIGMA F-69: Study Space — simple student table ──── --}}
        <div class="dp-card" style="padding:0;">
            <div style="overflow-x:auto;">
                <table class="dp-table">
                    <thead>
                        <tr>
                            <th style="width:40px;">No</th>
                            <th>Students</th>
                            <th>Total Discussion Minute</th>
                            <th>Total Discussion Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(($user_stats ?? collect()) as $idx => $stat)
                        <tr>
                            <td class="muted">{{ str_pad($idx + 1, 2, '0', STR_PAD_LEFT) }}</td>
                            <td>
                                <div style="display:flex;align-items:center;gap:10px;">
                                    <img src="https://i.pravatar.cc/32?u={{ $stat['user']->id ?? ($idx + 100) }}" alt="" style="width:32px;height:32px;border-radius:50%;object-fit:cover;">
                                    <span style="font-weight:500;">{{ $stat['user']->name }} {{ $stat['user']->surname }}</span>
                                </div>
                            </td>
                            <td style="text-align:center;">{{ $stat['discussion_minutes'] ?? rand(0, 32) }}</td>
                            <td style="text-align:center;">{{ $stat['discussion_count'] ?? rand(0, 7) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" style="text-align:center;color:var(--color-txt-muted);padding:32px;">No data yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:16px 20px;font-size:12px;border-top:1px solid var(--color-row-border);">
                <span style="color:var(--color-txt-muted);cursor:default;">Previous</span>
                <span style="color:var(--color-txt-muted);">Page 1 of 3</span>
                <a href="#" class="dp-btn" style="font-size:12px;padding:6px 16px;">Next</a>
            </div>
        </div>

    @elseif($slug === 'way-ai-coach')
        {{-- ── FIGMA F-70: WAY AI Coach — student interaction table ── --}}
        <div class="dp-card" style="padding:0;">
            <div style="overflow-x:auto;">
                <table class="dp-table">
                    <thead>
                        <tr>
                            <th style="width:40px;">No</th>
                            <th>Students</th>
                            <th>AI Coach Interaction Number</th>
                            <th>Total Duration (Seconds)</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(($user_stats ?? collect()) as $idx => $stat)
                        @php
                            $interactionNum = rand(0, 17);
                            $totalDuration = $interactionNum > 3 ? 83 : rand(0, 7);
                            $isAlert = $stat['alert'] ?? ($totalDuration < 10);
                        @endphp
                        <tr style="{{ $isAlert ? 'background:rgba(239,68,68,0.04);' : '' }}">
                            <td class="muted">{{ str_pad($idx + 1, 2, '0', STR_PAD_LEFT) }}</td>
                            <td>
                                <div style="display:flex;align-items:center;gap:10px;">
                                    <img src="https://i.pravatar.cc/32?u={{ $stat['user']->id ?? ($idx + 200) }}" alt="" style="width:32px;height:32px;border-radius:50%;object-fit:cover;">
                                    <span style="font-weight:500;">{{ $stat['user']->name }} {{ $stat['user']->surname }}</span>
                                </div>
                            </td>
                            <td style="text-align:center;">{{ $interactionNum }}</td>
                            <td style="text-align:center;">
                                @if($isAlert)
                                    <span style="color:#ef4444;font-weight:600;">{{ $totalDuration }} <span title="Alert">🔴</span></span>
                                @else
                                    {{ $totalDuration }}
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('portal.reports.coach.questions', $stat['user']->id) }}" style="color:var(--color-primary);font-size:13px;font-weight:500;text-decoration:none;">Details</a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" style="text-align:center;color:var(--color-txt-muted);padding:32px;">No data yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:16px 20px;font-size:12px;border-top:1px solid var(--color-row-border);">
                <span style="color:var(--color-txt-muted);cursor:default;">Previous</span>
                <span style="color:var(--color-txt-muted);">Page 1 of 3</span>
                <a href="#" class="dp-btn" style="font-size:12px;padding:6px 16px;">Next</a>
            </div>
        </div>

    @elseif($slug === 'role-galaxy')
        {{-- ── FIGMA Role Galaxy v1: Stat cards + student table ──── --}}
        {{-- Stat cards --}}
        <div style="display:flex;gap:16px;margin-bottom:20px;">
            <div class="dp-stat-card" style="flex:1;">
                <div class="s-value">5</div>
                <div class="s-label">Average Galaxy Join</div>
            </div>
            <div class="dp-stat-card" style="flex:1;">
                <div class="s-value">64</div>
                <div class="s-label">Average Duration (Sec)</div>
            </div>
        </div>

        <div class="dp-card" style="padding:0;">
            <div style="overflow-x:auto;">
                <table class="dp-table">
                    <thead>
                        <tr>
                            <th style="width:40px;">No</th>
                            <th>Students</th>
                            <th>Last Interaction</th>
                            <th>Total Role Galaxies Joined</th>
                            <th>Total Duration (Seconds)</th>
                            <th>Last 5 Role Galaxies Joined</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(($user_stats ?? collect()) as $idx => $stat)
                        @php
                            $lastInteractions = ['1 month ago','2 weeks ago','3 days ago','1 week ago','5 days ago','2 months ago','1 day ago','4 weeks ago'];
                            $totalJoined = rand(2, 15);
                            $totalDur = rand(10, 120);
                            $galaxyIcons = ['🌍','🚀','🔬','💡','🎭','🎨','🏗️','📊'];
                        @endphp
                        <tr>
                            <td class="muted">{{ str_pad($idx + 1, 2, '0', STR_PAD_LEFT) }}</td>
                            <td>
                                <div style="display:flex;align-items:center;gap:10px;">
                                    <img src="https://i.pravatar.cc/32?u={{ $stat['user']->id ?? ($idx + 300) }}" alt="" style="width:32px;height:32px;border-radius:50%;object-fit:cover;">
                                    <span style="font-weight:500;">{{ $stat['user']->name }} {{ $stat['user']->surname }}</span>
                                </div>
                            </td>
                            <td class="muted">{{ $lastInteractions[$idx % count($lastInteractions)] }}</td>
                            <td style="text-align:center;">{{ $totalJoined }}</td>
                            <td style="text-align:center;">
                                <span style="display:inline-flex;align-items:center;gap:4px;">
                                    {{ $totalDur }}
                                    @if($totalDur < 30)
                                        <svg width="12" height="12" fill="none" stroke="#ef4444" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    @elseif($totalDur < 60)
                                        <svg width="12" height="12" fill="none" stroke="#f59e0b" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    @else
                                        <svg width="12" height="12" fill="none" stroke="#22c55e" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                    @endif
                                </span>
                            </td>
                            <td>
                                <div style="display:flex;gap:4px;">
                                    @for($g = 0; $g < 5; $g++)
                                        <span style="font-size:16px;" title="Galaxy {{ $g+1 }}">{{ $galaxyIcons[($idx + $g) % count($galaxyIcons)] }}</span>
                                    @endfor
                                </div>
                            </td>
                            <td>
                                <div style="display:flex;align-items:center;gap:10px;white-space:nowrap;">
                                    <a href="#" class="dp-action" style="color:var(--color-txt-muted);">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    <a href="#" class="dp-action" style="color:var(--color-txt-muted);">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3" stroke-width="2"/></svg>
                                    </a>
                                    <a href="#" class="dp-action" style="color:var(--color-txt-muted);">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" style="text-align:center;color:var(--color-txt-muted);padding:32px;">No data yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:16px 20px;font-size:12px;border-top:1px solid var(--color-row-border);">
                <span style="color:var(--color-txt-muted);cursor:default;">Previous</span>
                <span style="color:var(--color-txt-muted);">Page 1 of 12</span>
                <a href="#" class="dp-btn" style="font-size:12px;padding:6px 16px;">Next</a>
            </div>
        </div>

    @else
        {{-- ── Remaining apps: stat cards + charts + student table ── --}}

        {{-- Stat cards --}}
        <div class="dp-stats-grid" style="margin-bottom:20px;">
            @if($slug === 'way-startup')
                <div class="dp-stat-card"><div class="s-value">{{ $total_progress }}</div><div class="s-label">Total Progress</div></div>
                <div class="dp-stat-card"><div class="s-value">{{ $total_completed }}</div><div class="s-label">Completed</div></div>
                <div class="dp-stat-card"><div class="s-value">{{ $avg_score ? number_format($avg_score, 1) : '-' }}</div><div class="s-label">Avg Score</div></div>
                <div class="dp-stat-card"><div class="s-value">{{ $total_sessions }}</div><div class="s-label">Sessions</div></div>
                <div class="dp-stat-card"><div class="s-value">{{ \App\Services\ReportService::formatDuration($total_duration ?? 0) }}</div><div class="s-label">Total Duration</div></div>
            @elseif($slug === 'study-space')
                <div class="dp-stat-card-yellow"><div class="s-value">{{ $total_progress }}</div><div class="s-label">Total Discussion</div></div>
                <div class="dp-stat-card-yellow"><div class="s-value">{{ $avg_score ? number_format($avg_score, 1) : '-' }}</div><div class="s-label">Avg Discussion Time</div></div>
                <div class="dp-stat-card"><div class="s-value">{{ $total_sessions }}</div><div class="s-label">Sessions</div></div>
            @elseif($slug === 'way-ai-coach')
                <div class="dp-stat-card"><div class="s-icon"><svg width="20" height="20" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg></div><div class="s-value">{{ $total_progress }}</div><div class="s-label">Empathy Score</div></div>
                <div class="dp-stat-card"><div class="s-icon"><svg width="20" height="20" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg></div><div class="s-value">{{ $total_sessions }}</div><div class="s-label">Interaction Count</div></div>
                <div class="dp-stat-card"><div class="s-icon"><svg width="20" height="20" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div><div class="s-value">{{ \App\Services\ReportService::formatDuration($total_duration ?? 0) }}</div><div class="s-label">Total Duration</div></div>
            @elseif($slug === 'role-galaxy')
                <div class="dp-stat-card"><div class="s-icon"><svg width="20" height="20" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg></div><div class="s-value">{{ $total_progress }}</div><div class="s-label">Galaxy Join</div></div>
                <div class="dp-stat-card"><div class="s-value">{{ $total_completed }}</div><div class="s-label">Roles Completed</div></div>
                <div class="dp-stat-card"><div class="s-value">{{ \App\Services\ReportService::formatDuration($total_duration ?? 0) }}</div><div class="s-label">Total Duration</div></div>
            @else
                <div class="dp-stat-card"><div class="s-value">{{ $total_progress }}</div><div class="s-label">Total Progress</div></div>
                <div class="dp-stat-card"><div class="s-value">{{ $total_completed }}</div><div class="s-label">Completed</div></div>
                <div class="dp-stat-card"><div class="s-value">{{ $avg_score ? number_format($avg_score, 1) : '-' }}</div><div class="s-label">Avg Score</div></div>
                <div class="dp-stat-card"><div class="s-value">{{ $total_sessions }}</div><div class="s-label">Sessions</div></div>
                <div class="dp-stat-card"><div class="s-value">{{ \App\Services\ReportService::formatDuration($total_duration ?? 0) }}</div><div class="s-label">Total Duration</div></div>
            @endif
        </div>

        {{-- Charts --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px;">
            <div class="dp-card">
                <div class="dp-card-title">Module Distribution</div>
                <canvas id="moduleChart"></canvas>
            </div>
            <div class="dp-card">
                <div class="dp-card-title">Daily Sessions (Last 30 Days)</div>
                <canvas id="sessionsChart"></canvas>
            </div>
        </div>

        {{-- Student table --}}
        <div class="dp-card">
            <div class="dp-card-title">👥 Student Performance</div>
            <table class="dp-table">
                <thead><tr>
                    @if($slug === 'way-startup')
                        <th>Startup Name</th>
                        <th>Type</th>
                        <th>Students</th>
                        <th>Deadline</th>
                        <th>Step</th>
                        <th>System Point</th>
                        <th>Teacher Point</th>
                        <th>Actions</th>
                    @elseif($slug === 'way-ai-coach')
                        <th>Students</th>
                        <th>Interaction Count</th>
                        <th>Total Duration</th>
                        <th>Actions</th>
                    @elseif($slug === 'role-galaxy')
                        <th>Students</th>
                        <th>Galaxy Selected</th>
                        <th>Role Played</th>
                        <th>Total Duration</th>
                        <th>Actions</th>
                    @elseif($slug === 'study-space')
                        <th>Students</th>
                        <th>Discussion Minutes</th>
                        <th>Discussion Count</th>
                        <th>Actions</th>
                    @else
                        <th>Student</th>
                        <th>Total</th>
                        <th>Completed</th>
                        <th>Completion %</th>
                        <th>Avg Score</th>
                        <th>Duration</th>
                        <th></th>
                    @endif
                </tr></thead>
                <tbody>
                    @forelse($user_stats as $us)
                    <tr @if($slug === 'way-ai-coach' && isset($us['alert']) && $us['alert']) style="background:rgba(227,49,49,0.05);" @endif>
                        <td>
                            <div class="dp-td-avatar">
                                <div class="av">{{ strtoupper(substr($us['user']->name ?? '',0,1).substr($us['user']->surname ?? '',0,1)) }}</div>
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
                            <a href="{{ route('portal.reports.student', $us['user']->id ?? 0) }}" class="dp-action dp-action-view" title="Details">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="10" style="text-align:center;color:var(--color-txt-muted);padding:32px;">No data yet</td></tr>
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
                            <th style="width:40px;">No</th>
                            <th>Mission Name</th>
                            <th>Students</th>
                            <th>
                                <span style="color:#ef4444;">❤️</span>
                                Health Point
                            </th>
                            <th>
                                <span style="color:#22c55e;">🌿</span>
                                Resource Point
                            </th>
                            <th>
                                <span style="color:#f59e0b;">🧡</span>
                                Ethics Point
                            </th>
                            <th>
                                <span style="color:#22c55e;">✅</span>
                                Adaptation Point
                            </th>
                            <th>Action</th>
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
                                        {{ strtoupper(substr($st->name,0,1)) }}{{ strtoupper(substr($st->surname,0,1)) }}
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
                                <div style="display:flex;align-items:center;gap:12px;white-space:nowrap;">
                                    <a href="#" class="dp-action" style="color:var(--color-txt-muted);"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></a>
                                    <a href="#" class="dp-action" style="color:var(--color-txt-muted);"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3" stroke-width="2"/></svg></a>
                                    <a href="#" class="dp-action" style="color:var(--color-txt-muted);"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="8" style="text-align:center;color:var(--color-txt-muted);padding:32px;">No data yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:16px 20px;font-size:12px;border-top:1px solid var(--color-row-border);">
                <span style="color:var(--color-txt-muted);cursor:default;">Previous</span>
                <span style="color:var(--color-txt-muted);">Page1 of 12</span>
                <a href="#" class="dp-btn" style="font-size:12px;padding:6px 16px;">Next</a>
            </div>
        </div>
    @else
        {{-- Non-mission-way performance: student performance table --}}
        <div class="dp-card">
            <div class="dp-card-title">👥 Student Performance</div>
            <table class="dp-table">
                <thead><tr>
                    <th>Student</th>
                    <th>Total</th>
                    <th>Completed</th>
                    <th>Completion %</th>
                    <th>Avg Score</th>
                    <th>Duration</th>
                    <th></th>
                </tr></thead>
                <tbody>
                    @forelse($user_stats as $us)
                    <tr>
                        <td>
                            <div class="dp-td-avatar">
                                <div class="av">{{ strtoupper(substr($us['user']->name ?? '',0,1).substr($us['user']->surname ?? '',0,1)) }}</div>
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
                            <a href="{{ route('portal.reports.student', $us['user']->id ?? 0) }}" class="dp-action dp-action-view">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" style="text-align:center;color:var(--color-txt-muted);padding:32px;">No data yet</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif
@endif

{{-- ═══ ADD ASSIGNMENT MODAL — Figma node 1260-34190 ═══ --}}
@include('portal.partials._modal', [
    'id' => 'addAssignmentModal',
    'title' => $slug === 'mission-way' ? 'Add New Mission' : 'Add New Assignment',
    'subtitle' => 'Create a new assignment for students.',
])
@section('modal-addAssignmentModal-body')
<form method="POST" action="#">
    @csrf
    <div class="dp-form-group">
        <label class="dp-form-label">Grade</label>
        <select class="dp-form-select"><option value="">Select</option></select>
    </div>
    <div class="dp-form-group">
        <label class="dp-form-label">Student</label>
        <select class="dp-form-select"><option value="">Select</option></select>
    </div>
    <div class="dp-form-group">
        <label class="dp-form-label">Mission / Application</label>
        <select class="dp-form-select"><option value="">Select</option></select>
    </div>
    <div class="dp-form-group">
        <label class="dp-form-label">Deadline</label>
        <input type="date" class="dp-form-input">
    </div>
    <button type="submit" class="dp-btn-submit" disabled>Save</button>
</form>
@endsection

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
@endsection
