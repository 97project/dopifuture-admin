@extends('portal.app')
@section('title', $class->name . ' — ' . __('admin.reports'))
@section('page-title', $class->name . ' — Rapor')

@section('content')
@php $isTr = app()->getLocale() === 'tr'; @endphp

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
    <div>
        <div style="font-size:18px;font-weight:600;">{{ $class->name }}</div>
        <p style="font-size:13px;color:var(--text-muted);margin:4px 0 0;">{{ $class->school->name ?? '' }} — {{ $isTr ? 'Sınıf raporu' : 'Class report' }}</p>
    </div>
    <a href="{{ route('portal.reports') }}" class="dp-btn-ghost">← {{ $isTr ? 'Geri' : 'Back' }}</a>
</div>

{{-- Stats --}}
<div class="dp-stats-grid" style="margin-bottom:20px;">
    <div class="dp-stat-card"><div class="s-value">{{ $class->students->count() }}</div><div class="s-label">{{ $isTr ? 'Öğrenci' : 'Students' }}</div></div>
    <div class="dp-stat-card"><div class="s-value">{{ $class->teachers->count() }}</div><div class="s-label">{{ $isTr ? 'Öğretmen' : 'Teachers' }}</div></div>
</div>

{{-- App Filter Tabs --}}
<div style="display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap;">
    <a href="{{ route('portal.reports.class', $class) }}" class="{{ !$selectedApp ? 'dp-btn' : 'dp-btn-ghost' }}" style="font-size:13px;">{{ $isTr ? 'Tümü' : 'All' }}</a>
    @foreach($apps as $a)
    <a href="{{ route('portal.reports.class.app', [$class, $a->slug]) }}" class="{{ $selectedApp && $selectedApp->id === $a->id ? 'dp-btn' : 'dp-btn-ghost' }}" style="font-size:13px;">{{ $a->name }}</a>
    @endforeach
</div>

{{-- Single App Detailed View --}}
@if($selectedApp && isset($reportData['app']))
    <div class="dp-stats-grid" style="margin-bottom:20px;">
        <div class="dp-stat-card"><div class="s-value">{{ $reportData['total_progress'] ?? 0 }}</div><div class="s-label">{{ $isTr ? 'Toplam İlerleme' : 'Total Progress' }}</div></div>
        <div class="dp-stat-card"><div class="s-value" style="color:var(--active-green);">{{ $reportData['total_completed'] ?? 0 }}</div><div class="s-label">{{ $isTr ? 'Tamamlanan' : 'Completed' }}</div></div>
        <div class="dp-stat-card"><div class="s-value" style="color:var(--primary);">{{ isset($reportData['avg_score']) && $reportData['avg_score'] ? number_format($reportData['avg_score'], 1) : '-' }}</div><div class="s-label">{{ $isTr ? 'Ort. Puan' : 'Avg Score' }}</div></div>
    </div>

    @if(isset($reportData['user_stats']) && count($reportData['user_stats']))
    <div class="dp-card">
        <div class="dp-card-title">👥 {{ $isTr ? 'Öğrenci Performansı' : 'Student Performance' }}</div>
        <table class="dp-table">
            <thead><tr>
                <th>{{ $isTr ? 'Öğrenci' : 'Student' }}</th>
                <th>{{ $isTr ? 'Tamamlanma' : 'Completion' }}</th>
                <th>{{ $isTr ? 'Puan' : 'Score' }}</th>
                <th></th>
            </tr></thead>
            <tbody>
            @foreach($reportData['user_stats'] as $us)
            <tr>
                <td>
                    <div class="dp-td-avatar">
                        <div class="av">{{ strtoupper(substr($us['user']->name??'',0,1).substr($us['user']->surname??'',0,1)) }}</div>
                        <span style="font-weight:500;">{{ $us['user']->name ?? '' }} {{ $us['user']->surname ?? '' }}</span>
                    </div>
                </td>
                <td>
                    <div style="display:flex;align-items:center;gap:8px;">
                        <div class="dp-progress" style="width:80px;"><div class="dp-progress-fill" style="width:{{ $us['completion_rate'] }}%;{{ $us['completion_rate'] < 40 ? 'background:#fbbf24;' : '' }}"></div></div>
                        {{ $us['completion_rate'] }}%
                    </div>
                </td>
                <td>{{ $us['avg_score'] ? number_format($us['avg_score'], 1) : '-' }}</td>
                <td><a href="{{ route('portal.reports.student', $us['user']) }}" class="dp-action dp-action-view"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></a></td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @endif
@else
    {{-- All Apps Summary --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px;margin-bottom:24px;">
    @foreach($reportData as $r)
        <div class="dp-card">
            <div style="font-weight:600;font-size:15px;margin-bottom:12px;">{{ $r['app']->name }}</div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                <div>
                    <div style="font-size:20px;font-weight:700;color:var(--active-green);">{{ $r['completed'] }}<span style="color:var(--text-muted);font-size:12px;font-weight:400;">/{{ $r['total_progress'] }}</span></div>
                    <div style="font-size:11px;color:var(--text-muted);">{{ $isTr ? 'Tamamlanan' : 'Completed' }}</div>
                </div>
                <div>
                    <div style="font-size:20px;font-weight:700;color:var(--primary);">{{ $r['avg_score'] ? number_format($r['avg_score'], 1) : '-' }}</div>
                    <div style="font-size:11px;color:var(--text-muted);">{{ $isTr ? 'Ort. Puan' : 'Avg Score' }}</div>
                </div>
            </div>
            <div class="dp-progress" style="margin-top:12px;"><div class="dp-progress-fill" style="width:{{ $r['completion_rate'] }}%;"></div></div>
        </div>
    @endforeach
    </div>

    {{-- Students List --}}
    <div class="dp-card">
        <div class="dp-card-title">👥 {{ $isTr ? 'Sınıf Öğrencileri' : 'Class Students' }}</div>
        <table class="dp-table">
            <thead><tr><th>{{ $isTr ? 'Öğrenci' : 'Student' }}</th><th>Email</th><th></th></tr></thead>
            <tbody>
            @foreach($class->students as $s)
            <tr>
                <td>
                    <div class="dp-td-avatar">
                        <div class="av">{{ strtoupper(substr($s->name,0,1).substr($s->surname??'',0,1)) }}</div>
                        <span style="font-weight:500;">{{ $s->name }} {{ $s->surname }}</span>
                    </div>
                </td>
                <td class="muted">{{ $s->email }}</td>
                <td><a href="{{ route('portal.reports.student', $s) }}" class="dp-action dp-action-view"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></a></td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endif
@endsection
