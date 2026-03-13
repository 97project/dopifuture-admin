@extends('portal.app')
@section('title', $app->name . ' — ' . __('admin.reports'))
@section('page-title', $app->name)

@section('content')
@php
    $isTr = app()->getLocale() === 'tr';
    $tab = request('tab', 'assignment');
    $slug = $app->slug;
@endphp

{{-- ═══ TAB BAR — Figma F-38: Assignments [24] | Performance ═══ --}}
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
    <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
        <div class="dp-tabs" style="border-bottom:none;margin-bottom:0;">
            <a href="{{ route('portal.reports.app', $app->slug) }}?tab=assignment{{ request('class_id') ? '&class_id='.request('class_id') : '' }}"
               class="dp-tab {{ $tab === 'assignment' ? 'active' : '' }}">
                {{ $isTr ? 'Görevler' : 'Assignments' }}
                @if($slug === 'mission-way')
                    <span class="tab-count">{{ $total_missions ?? 0 }}</span>
                @else
                    <span class="tab-count">{{ $total_progress ?? 0 }}</span>
                @endif
            </a>
            <a href="{{ route('portal.reports.app', $app->slug) }}?tab=performance{{ request('class_id') ? '&class_id='.request('class_id') : '' }}"
               class="dp-tab {{ $tab === 'performance' ? 'active' : '' }}">
                {{ $isTr ? 'Performans' : 'Performance' }}
            </a>
        </div>

        {{-- 5.2: Class filter dropdown --}}
        @if(isset($classes) && $classes->count())
        <select onchange="window.location='{{ route('portal.reports.app', $app->slug) }}?tab={{ $tab }}&class_id='+this.value"
                style="padding:6px 12px;border-radius:8px;border:1px solid var(--color-row-border,#e5e7eb);font-size:12px;font-family:inherit;background:#fff;cursor:pointer;color:var(--color-txt,#030719);">
            <option value="">{{ $isTr ? 'Tüm Sınıflar' : 'All Classes' }}</option>
            @foreach($classes as $cls)
            <option value="{{ $cls->id }}" {{ request('class_id') == $cls->id ? 'selected' : '' }}>{{ $cls->name }}</option>
            @endforeach
        </select>
        @endif
    </div>

    <button type="button" class="dp-btn" onclick="document.getElementById('addAssignmentModal')?.classList.add('show')">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="2"/><path stroke-linecap="round" stroke-width="2" d="M12 8v8m-4-4h8"/></svg>
        @if($slug === 'mission-way')
            {{ $isTr ? 'Yeni Görev Ekle' : 'Add New Mission' }}
        @elseif($slug === 'way-startup')
            {{ $isTr ? 'Yeni Atama Ekle' : 'Add New Assignment' }}
        @else
            {{ $isTr ? 'Yeni Ekle' : 'Add New' }}
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
                            <th>{{ $isTr ? 'Görev Adı' : 'Mission Name' }}</th>
                            <th>{{ $isTr ? 'Öğrenciler' : 'Students' }}</th>
                            <th>{{ $isTr ? 'Atanma Tarihi' : 'Assigned Date' }}</th>
                            <th>{{ $isTr ? 'Son Tarih' : 'Deadline' }}</th>
                            <th>
                                <span style="display:inline-flex;align-items:center;gap:4px;">
                                    <span style="color:#ef4444;">❤️</span>
                                    {{ $isTr ? 'Sağlık Puanı' : 'Health Point' }}
                                </span>
                            </th>
                            <th>
                                <span style="display:inline-flex;align-items:center;gap:4px;">
                                    <span style="color:#22c55e;">🌿</span>
                                    {{ $isTr ? 'Kaynak Puanı' : 'Resource Point' }}
                                </span>
                            </th>
                            <th>
                                <span style="display:inline-flex;align-items:center;gap:4px;">
                                    <span style="color:#f59e0b;">🧡</span>
                                    {{ $isTr ? 'Etik Puanı' : 'Ethics Point' }}
                                </span>
                            </th>
                            <th>
                                <span style="display:inline-flex;align-items:center;gap:4px;">
                                    <span style="color:#22c55e;">✅</span>
                                    {{ $isTr ? 'Adaptasyon Puanı' : 'Adaptation Point' }}
                                </span>
                            </th>
                            <th>{{ $isTr ? 'İşlemler' : 'Action' }}</th>
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
                                    {{-- Details (only active action — edit/delete managed via API) --}}
                                    <a href="{{ route('portal.reports.mission.detail', $mission->id) }}" style="color:var(--color-primary);font-size:13px;font-weight:500;text-decoration:none;">
                                        {{ $isTr ? 'Detay' : 'Details' }}
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="10" style="text-align:center;color:var(--color-txt-muted);padding:32px;">{{ $isTr ? 'Henüz görev yok' : 'No missions yet' }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{-- Pagination --}}
            <div style="display:flex;justify-content:space-between;align-items:center;padding:16px 20px;font-size:12px;border-top:1px solid var(--color-row-border);">
                <span style="color:var(--color-txt-muted);">{{ ($missions ?? collect())->count() }} {{ $isTr ? 'görev' : 'missions' }}</span>
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
                            <th>{{ $isTr ? 'Startup Adı' : 'Startup Name' }}</th>
                            <th>{{ $isTr ? 'Startup Türü' : 'Startup Type' }}</th>
                            <th>{{ $isTr ? 'Öğrenciler' : 'Students' }}</th>
                            <th>{{ $isTr ? 'Son Tarih' : 'Deadline' }}</th>
                            <th>{{ $isTr ? 'Adım' : 'Step' }}</th>
                            <th>{{ $isTr ? 'Sistem Puanı' : 'System Point' }}</th>
                            <th>{{ $isTr ? 'Öğretmen Puanı' : 'Teacher Point' }}</th>
                            <th>{{ $isTr ? 'İşlemler' : 'Action' }}</th>
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
                                    {{-- Details (only active action — settings/delete managed via API) --}}
                                    <a href="{{ route('portal.reports.startup.detail', $startup->id) }}" style="color:var(--color-primary);font-size:13px;font-weight:500;text-decoration:none;">
                                        {{ $isTr ? 'Detay' : 'Details' }}
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="9" style="text-align:center;color:var(--color-txt-muted);padding:32px;">{{ $isTr ? 'Henüz startup yok' : 'No startups yet' }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{-- Pagination --}}
            <div style="display:flex;justify-content:space-between;align-items:center;padding:16px 20px;font-size:12px;border-top:1px solid var(--color-row-border);">
                <span style="color:var(--color-txt-muted);">{{ ($startups ?? collect())->count() }} {{ $isTr ? 'proje' : 'projects' }}</span>
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
                            <th>{{ $isTr ? 'Öğrenciler' : 'Students' }}</th>
                            <th>{{ $isTr ? 'Toplam Tartışma Süresi (dk)' : 'Total Discussion Minute' }}</th>
                            <th>{{ $isTr ? 'Toplam Tartışma Sayısı' : 'Total Discussion Count' }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(($user_stats ?? collect()) as $idx => $stat)
                        <tr>
                            <td class="muted">{{ str_pad($idx + 1, 2, '0', STR_PAD_LEFT) }}</td>
                            <td>
                                <div style="display:flex;align-items:center;gap:10px;">
                                    <div style="width:32px;height:32px;border-radius:50%;background:{{ ['#4364F7','#8b5cf6','#f59e0b','#ef4444','#10b981'][$idx % 5] }};color:#fff;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:600;">{{ strtoupper(substr($stat['user']->name ?? '',0,1)) }}{{ strtoupper(substr($stat['user']->surname ?? '',0,1)) }}</div>
                                    <span style="font-weight:500;">{{ $stat['user']->name }} {{ $stat['user']->surname }}</span>
                                </div>
                            </td>
                            <td style="text-align:center;">{{ $stat['total_duration'] ?? 0 }}</td>
                            <td style="text-align:center;">{{ $stat['total_sessions'] ?? $stat['total'] ?? 0 }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" style="text-align:center;color:var(--color-txt-muted);padding:32px;">{{ $isTr ? 'Henüz veri yok' : 'No data yet' }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:16px 20px;font-size:12px;border-top:1px solid var(--color-row-border);">
                <span style="color:var(--color-txt-muted);">{{ ($user_stats ?? collect())->count() }} {{ $isTr ? 'öğrenci' : 'students' }}</span>
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
                            <th>{{ $isTr ? 'Öğrenciler' : 'Students' }}</th>
                            <th>{{ $isTr ? 'AI Coach Etkileşim Sayısı' : 'AI Coach Interaction Number' }}</th>
                            <th>{{ $isTr ? 'Toplam Süre (Saniye)' : 'Total Duration (Seconds)' }}</th>
                            <th>{{ $isTr ? 'İşlemler' : 'Action' }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(($user_stats ?? collect()) as $idx => $stat)
                        @php
                            $interactionCount = $stat['session_count'] ?? $stat['total_sessions'] ?? $stat['total'] ?? 0;
                            $durationSecs = $stat['total_duration'] ?? 0;
                            $isAlert = $stat['alert'] ?? ($interactionCount == 0);
                        @endphp
                        <tr style="{{ $isAlert ? 'background:rgba(239,68,68,0.04);' : '' }}">
                            <td class="muted">{{ str_pad($idx + 1, 2, '0', STR_PAD_LEFT) }}</td>
                            <td>
                                <div style="display:flex;align-items:center;gap:10px;">
                                    <div style="width:32px;height:32px;border-radius:50%;background:{{ ['#4364F7','#8b5cf6','#f59e0b','#ef4444','#10b981'][$idx % 5] }};color:#fff;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:600;">{{ strtoupper(substr($stat['user']->name ?? '',0,1)) }}{{ strtoupper(substr($stat['user']->surname ?? '',0,1)) }}</div>
                                    <span style="font-weight:500;">{{ $stat['user']->name }} {{ $stat['user']->surname }}</span>
                                </div>
                            </td>
                            <td style="text-align:center;">{{ $interactionCount }}</td>
                            <td style="text-align:center;">
                                @if($isAlert)
                                    <span style="color:#ef4444;font-weight:600;">{{ \App\Services\ReportService::formatDuration($durationSecs) }} <span title="Alert">🔴</span></span>
                                @else
                                    {{ \App\Services\ReportService::formatDuration($durationSecs) }}
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('portal.reports.coach.questions', $stat['user']->id) }}" style="color:var(--color-primary);font-size:13px;font-weight:500;text-decoration:none;">{{ $isTr ? 'Detay' : 'Details' }}</a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" style="text-align:center;color:var(--color-txt-muted);padding:32px;">{{ $isTr ? 'Henüz veri yok' : 'No data yet' }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:16px 20px;font-size:12px;border-top:1px solid var(--color-row-border);">
                <span style="color:var(--color-txt-muted);">{{ ($user_stats ?? collect())->count() }} {{ $isTr ? 'öğrenci' : 'students' }}</span>
            </div>
        </div>

    @elseif($slug === 'role-galaxy')
        {{-- ── FIGMA Role Galaxy v1: Stat cards + student table ──── --}}
        {{-- Stat cards — real aggregated data --}}
        @php
            $rgStats = $user_stats ?? collect();
            $avgJoined = $rgStats->count() > 0 ? round($rgStats->avg('simulator_count') ?? $rgStats->avg('total') ?? 0) : 0;
            $avgDuration = $rgStats->count() > 0 ? round($rgStats->avg('total_duration') ?? 0) : 0;
        @endphp
        <div style="display:flex;gap:16px;margin-bottom:20px;">
            <div class="dp-stat-card" style="flex:1;">
                <div class="s-value">{{ $avgJoined }}</div>
                <div class="s-label">{{ $isTr ? 'Ort. Galaxy Katılımı' : 'Average Galaxy Join' }}</div>
            </div>
            <div class="dp-stat-card" style="flex:1;">
                <div class="s-value">{{ $avgDuration }}</div>
                <div class="s-label">{{ $isTr ? 'Ort. Süre (Saniye)' : 'Average Duration (Sec)' }}</div>
            </div>
        </div>

        <div class="dp-card" style="padding:0;">
            <div style="overflow-x:auto;">
                <table class="dp-table">
                    <thead>
                        <tr>
                            <th style="width:40px;">No</th>
                            <th>{{ $isTr ? 'Öğrenciler' : 'Students' }}</th>
                            <th>{{ $isTr ? 'Toplam Katılım' : 'Total Role Galaxies Joined' }}</th>
                            <th>{{ $isTr ? 'Toplam Süre (Saniye)' : 'Total Duration (Seconds)' }}</th>
                            <th>{{ $isTr ? 'İşlemler' : 'Action' }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(($user_stats ?? collect()) as $idx => $stat)
                        @php
                            $totalJoined = $stat['simulator_count'] ?? $stat['total'] ?? 0;
                            $totalDur = $stat['total_duration'] ?? 0;
                        @endphp
                        <tr>
                            <td class="muted">{{ str_pad($idx + 1, 2, '0', STR_PAD_LEFT) }}</td>
                            <td>
                                <div style="display:flex;align-items:center;gap:10px;">
                                    <div style="width:32px;height:32px;border-radius:50%;background:{{ ['#4364F7','#8b5cf6','#f59e0b','#ef4444','#10b981'][$idx % 5] }};color:#fff;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:600;">{{ strtoupper(substr($stat['user']->name ?? '',0,1)) }}{{ strtoupper(substr($stat['user']->surname ?? '',0,1)) }}</div>
                                    <span style="font-weight:500;">{{ $stat['user']->name }} {{ $stat['user']->surname }}</span>
                                </div>
                            </td>
                            <td style="text-align:center;">{{ $totalJoined }}</td>
                            <td style="text-align:center;">
                                <span style="display:inline-flex;align-items:center;gap:4px;">
                                    {{ \App\Services\ReportService::formatDuration($totalDur) }}
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
                                <div style="display:flex;align-items:center;gap:10px;white-space:nowrap;">
                                    @if($stat['user'])
                                    <a href="{{ route('portal.reports.student', $stat['user']->id) }}" style="color:var(--color-primary);font-size:13px;font-weight:500;text-decoration:none;">{{ $isTr ? 'Detay' : 'Details' }}</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" style="text-align:center;color:var(--color-txt-muted);padding:32px;">{{ $isTr ? 'Henüz veri yok' : 'No data yet' }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:16px 20px;font-size:12px;border-top:1px solid var(--color-row-border);">
                <span style="color:var(--color-txt-muted);">{{ ($user_stats ?? collect())->count() }} {{ $isTr ? 'öğrenci' : 'students' }}</span>
            </div>
        </div>

    @else
        {{-- ── Remaining apps: stat cards + charts + student table ── --}}

        {{-- Stat cards --}}
        <div class="dp-stats-grid" style="margin-bottom:20px;">
            @if($slug === 'way-startup')
                <div class="dp-stat-card"><div class="s-value">{{ $total_progress }}</div><div class="s-label">{{ $isTr ? 'Toplam İlerleme' : 'Total Progress' }}</div></div>
                <div class="dp-stat-card"><div class="s-value">{{ $total_completed }}</div><div class="s-label">{{ $isTr ? 'Tamamlanan' : 'Completed' }}</div></div>
                <div class="dp-stat-card"><div class="s-value">{{ $avg_score ? number_format($avg_score, 1) : '-' }}</div><div class="s-label">{{ $isTr ? 'Ort. Puan' : 'Avg Score' }}</div></div>
                <div class="dp-stat-card"><div class="s-value">{{ $total_sessions }}</div><div class="s-label">{{ $isTr ? 'Oturumlar' : 'Sessions' }}</div></div>
                <div class="dp-stat-card"><div class="s-value">{{ \App\Services\ReportService::formatDuration($total_duration ?? 0) }}</div><div class="s-label">{{ $isTr ? 'Toplam Süre' : 'Total Duration' }}</div></div>
            @elseif($slug === 'study-space')
                <div class="dp-stat-card-yellow"><div class="s-value">{{ $total_progress }}</div><div class="s-label">{{ $isTr ? 'Toplam Tartışma' : 'Total Discussion' }}</div></div>
                <div class="dp-stat-card-yellow"><div class="s-value">{{ $avg_score ? number_format($avg_score, 1) : '-' }}</div><div class="s-label">{{ $isTr ? 'Ort. Tartışma Süresi' : 'Avg Discussion Time' }}</div></div>
                <div class="dp-stat-card"><div class="s-value">{{ $total_sessions }}</div><div class="s-label">{{ $isTr ? 'Oturumlar' : 'Sessions' }}</div></div>
            @elseif($slug === 'way-ai-coach')
                <div class="dp-stat-card"><div class="s-icon"><svg width="20" height="20" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg></div><div class="s-value">{{ $total_progress }}</div><div class="s-label">{{ $isTr ? 'Empati Skoru' : 'Empathy Score' }}</div></div>
                <div class="dp-stat-card"><div class="s-icon"><svg width="20" height="20" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg></div><div class="s-value">{{ $total_sessions }}</div><div class="s-label">{{ $isTr ? 'Etkileşim Sayısı' : 'Interaction Count' }}</div></div>
                <div class="dp-stat-card"><div class="s-icon"><svg width="20" height="20" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div><div class="s-value">{{ \App\Services\ReportService::formatDuration($total_duration ?? 0) }}</div><div class="s-label">{{ $isTr ? 'Toplam Süre' : 'Total Duration' }}</div></div>
            @elseif($slug === 'role-galaxy')
                <div class="dp-stat-card"><div class="s-icon"><svg width="20" height="20" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg></div><div class="s-value">{{ $total_progress }}</div><div class="s-label">{{ $isTr ? 'Galaksi Katılımı' : 'Galaxy Join' }}</div></div>
                <div class="dp-stat-card"><div class="s-value">{{ $total_completed }}</div><div class="s-label">{{ $isTr ? 'Tamamlanan Roller' : 'Roles Completed' }}</div></div>
                <div class="dp-stat-card"><div class="s-value">{{ \App\Services\ReportService::formatDuration($total_duration ?? 0) }}</div><div class="s-label">{{ $isTr ? 'Toplam Süre' : 'Total Duration' }}</div></div>
            @else
                <div class="dp-stat-card"><div class="s-value">{{ $total_progress }}</div><div class="s-label">{{ $isTr ? 'Toplam İlerleme' : 'Total Progress' }}</div></div>
                <div class="dp-stat-card"><div class="s-value">{{ $total_completed }}</div><div class="s-label">{{ $isTr ? 'Tamamlanan' : 'Completed' }}</div></div>
                <div class="dp-stat-card"><div class="s-value">{{ $avg_score ? number_format($avg_score, 1) : '-' }}</div><div class="s-label">{{ $isTr ? 'Ort. Puan' : 'Avg Score' }}</div></div>
                <div class="dp-stat-card"><div class="s-value">{{ $total_sessions }}</div><div class="s-label">{{ $isTr ? 'Oturumlar' : 'Sessions' }}</div></div>
                <div class="dp-stat-card"><div class="s-value">{{ \App\Services\ReportService::formatDuration($total_duration ?? 0) }}</div><div class="s-label">{{ $isTr ? 'Toplam Süre' : 'Total Duration' }}</div></div>
            @endif
        </div>

        {{-- Charts --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px;">
            <div class="dp-card">
                <div class="dp-card-title">{{ $isTr ? 'Modül Dağılımı' : 'Module Distribution' }}</div>
                <canvas id="moduleChart"></canvas>
            </div>
            <div class="dp-card">
                <div class="dp-card-title">{{ $isTr ? 'Günlük Oturumlar (Son 30 Gün)' : 'Daily Sessions (Last 30 Days)' }}</div>
                <canvas id="sessionsChart"></canvas>
            </div>
        </div>

        {{-- Student table --}}
        <div class="dp-card">
            <div class="dp-card-title">👥 {{ $isTr ? 'Öğrenci Performansı' : 'Student Performance' }}</div>
            <table class="dp-table">
                <thead><tr>
                    @if($slug === 'way-startup')
                        <th>{{ $isTr ? 'Startup Adı' : 'Startup Name' }}</th>
                        <th>{{ $isTr ? 'Tür' : 'Type' }}</th>
                        <th>{{ $isTr ? 'Öğrenciler' : 'Students' }}</th>
                        <th>{{ $isTr ? 'Son Tarih' : 'Deadline' }}</th>
                        <th>{{ $isTr ? 'Adım' : 'Step' }}</th>
                        <th>{{ $isTr ? 'Sistem Puanı' : 'System Point' }}</th>
                        <th>{{ $isTr ? 'Öğretmen Puanı' : 'Teacher Point' }}</th>
                        <th>{{ $isTr ? 'İşlemler' : 'Actions' }}</th>
                    @elseif($slug === 'way-ai-coach')
                        <th>{{ $isTr ? 'Öğrenci' : 'Students' }}</th>
                        <th>{{ $isTr ? 'Etkileşim Sayısı' : 'Interaction Count' }}</th>
                        <th>{{ $isTr ? 'Toplam Süre' : 'Total Duration' }}</th>
                        <th>{{ $isTr ? 'İşlemler' : 'Actions' }}</th>
                    @elseif($slug === 'role-galaxy')
                        <th>{{ $isTr ? 'Öğrenci' : 'Students' }}</th>
                        <th>{{ $isTr ? 'Seçilen Galaksi' : 'Galaxy Selected' }}</th>
                        <th>{{ $isTr ? 'Oynanan Rol' : 'Role Played' }}</th>
                        <th>{{ $isTr ? 'Toplam Süre' : 'Total Duration' }}</th>
                        <th>{{ $isTr ? 'İşlemler' : 'Actions' }}</th>
                    @elseif($slug === 'study-space')
                        <th>{{ $isTr ? 'Öğrenci' : 'Students' }}</th>
                        <th>{{ $isTr ? 'Tartışma Süresi (dk)' : 'Discussion Minutes' }}</th>
                        <th>{{ $isTr ? 'Tartışma Sayısı' : 'Discussion Count' }}</th>
                        <th>{{ $isTr ? 'İşlemler' : 'Actions' }}</th>
                    @else
                        <th>{{ $isTr ? 'Öğrenci' : 'Student' }}</th>
                        <th>{{ $isTr ? 'Toplam' : 'Total' }}</th>
                        <th>{{ $isTr ? 'Tamamlanan' : 'Completed' }}</th>
                        <th>{{ $isTr ? 'Tamamlanma %' : 'Completion %' }}</th>
                        <th>{{ $isTr ? 'Ort. Puan' : 'Avg Score' }}</th>
                        <th>{{ $isTr ? 'Süre' : 'Duration' }}</th>
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
                            <a href="{{ route('portal.reports.student', $us['user']->id ?? 0) }}" class="dp-action dp-action-view" title="{{ $isTr ? 'Detay' : 'Details' }}">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="10" style="text-align:center;color:var(--color-txt-muted);padding:32px;">{{ $isTr ? 'Henüz veri yok' : 'No data yet' }}</td></tr>
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
                            <th>{{ $isTr ? 'Görev Adı' : 'Mission Name' }}</th>
                            <th>{{ $isTr ? 'Öğrenciler' : 'Students' }}</th>
                            <th>
                                <span style="color:#ef4444;">❤️</span>
                                {{ $isTr ? 'Sağlık Puanı' : 'Health Point' }}
                            </th>
                            <th>
                                <span style="color:#22c55e;">🌿</span>
                                {{ $isTr ? 'Kaynak Puanı' : 'Resource Point' }}
                            </th>
                            <th>
                                <span style="color:#f59e0b;">🧡</span>
                                {{ $isTr ? 'Etik Puanı' : 'Ethics Point' }}
                            </th>
                            <th>
                                <span style="color:#22c55e;">✅</span>
                                {{ $isTr ? 'Adaptasyon Puanı' : 'Adaptation Point' }}
                            </th>
                            <th>{{ $isTr ? 'İşlemler' : 'Action' }}</th>
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
                                    {{-- Details only — edit/settings/delete managed via API --}}
                                    <a href="{{ route('portal.reports.mission.detail', $mission->id) }}" style="color:var(--color-primary);font-size:13px;font-weight:500;text-decoration:none;">{{ $isTr ? 'Detay' : 'Details' }}</a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="8" style="text-align:center;color:var(--color-txt-muted);padding:32px;">{{ $isTr ? 'Henüz veri yok' : 'No data yet' }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:16px 20px;font-size:12px;border-top:1px solid var(--color-row-border);">
                <span style="color:var(--color-txt-muted);">{{ ($missions ?? collect())->count() }} {{ $isTr ? 'görev' : 'missions' }}</span>
            </div>
        </div>
    @else
        {{-- Non-mission-way performance: student performance table --}}
        <div class="dp-card">
            <div class="dp-card-title">👥 {{ $isTr ? 'Öğrenci Performansı' : 'Student Performance' }}</div>
            <table class="dp-table">
                <thead><tr>
                    <th>{{ $isTr ? 'Öğrenci' : 'Student' }}</th>
                    <th>{{ $isTr ? 'Toplam' : 'Total' }}</th>
                    <th>{{ $isTr ? 'Tamamlanan' : 'Completed' }}</th>
                    <th>{{ $isTr ? 'Tamamlanma %' : 'Completion %' }}</th>
                    <th>{{ $isTr ? 'Ort. Puan' : 'Avg Score' }}</th>
                    <th>{{ $isTr ? 'Süre' : 'Duration' }}</th>
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
                    <tr><td colspan="7" style="text-align:center;color:var(--color-txt-muted);padding:32px;">{{ $isTr ? 'Henüz veri yok' : 'No data yet' }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif
@endif

{{-- ═══ ADD ASSIGNMENT MODAL — Figma node 1260-34190 ═══ --}}
@include('portal.partials._modal', [
    'id' => 'addAssignmentModal',
    'title' => $slug === 'mission-way' ? ($isTr ? 'Yeni Görev Ekle' : 'Add New Mission') : ($isTr ? 'Yeni Atama Ekle' : 'Add New Assignment'),
    'subtitle' => $isTr ? 'Öğrenci için yeni görev oluşturun.' : 'Create a new assignment for students.',
])
@section('modal-addAssignmentModal-body')
<div style="text-align:center;padding:24px;">
    <div style="font-size:32px;margin-bottom:12px;">🚧</div>
    <p style="color:var(--color-txt-muted);font-size:14px;margin:0;">{{ $isTr ? 'Görev atamaları uygulama tarafından yönetilmektedir. Görevleri ilgili uygulamadan oluşturabilirsiniz.' : 'Assignments are managed by the application. You can create assignments from the respective app.' }}</p>
</div>
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
    data: { labels: {!! json_encode(($sessions_by_day ?? collect())->keys()) !!}, datasets: [{ label: '{{ $isTr ? "Oturumlar" : "Sessions" }}', data: {!! json_encode(($sessions_by_day ?? collect())->values()) !!}, borderColor: '#4364F7', backgroundColor: 'rgba(67,100,247,0.1)', fill: true, tension: 0.4, pointRadius: 3 }] },
    options: { responsive: true, scales: { y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.04)' } }, x: { grid: { display: false }, ticks: { maxRotation: 45 } } }, plugins: { legend: { display: false } } }
});
}
@endif
</script>
@endif
@endsection
