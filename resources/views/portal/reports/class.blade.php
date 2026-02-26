@extends('portal.layout')
@section('title', $class->name . ' — ' . __('admin.reports'))

@section('content')
<div class="page-header">
    <div style="display:flex; align-items:center; gap:1rem;">
        <a href="{{ route('portal.reports') }}" class="btn btn-ghost btn-sm">← {{ app()->getLocale() === 'tr' ? 'Geri' : 'Back' }}</a>
        <div>
            <h1>{{ $class->name }}</h1>
            <p>{{ $class->school->name ?? '' }} — {{ app()->getLocale() === 'tr' ? 'Sınıf raporu' : 'Class report' }}</p>
        </div>
    </div>
</div>

{{-- Class Info --}}
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value">{{ $class->students->count() }}</div>
        <div class="stat-name">{{ app()->getLocale() === 'tr' ? 'Öğrenci' : 'Students' }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ $class->teachers->count() }}</div>
        <div class="stat-name">{{ app()->getLocale() === 'tr' ? 'Öğretmen' : 'Teachers' }}</div>
    </div>
</div>

{{-- App Filter --}}
<div style="display:flex; gap:0.5rem; margin-bottom:1.5rem; flex-wrap:wrap;">
    <a href="{{ route('portal.reports.class', $class) }}" class="btn {{ !$selectedApp ? 'btn-primary' : 'btn-ghost' }} btn-sm">
        {{ app()->getLocale() === 'tr' ? 'Tümü' : 'All' }}
    </a>
    @foreach($apps as $a)
    <a href="{{ route('portal.reports.class.app', [$class, $a->slug]) }}" class="btn {{ $selectedApp && $selectedApp->id === $a->id ? 'btn-primary' : 'btn-ghost' }} btn-sm">
        {{ $a->name }}
    </a>
    @endforeach
</div>

{{-- Single App Detailed View --}}
@if($selectedApp && isset($reportData['app']))
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value">{{ $reportData['total_progress'] ?? 0 }}</div>
            <div class="stat-name">{{ app()->getLocale() === 'tr' ? 'Toplam İlerleme' : 'Total Progress' }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color:#4ade80;">{{ $reportData['total_completed'] ?? 0 }}</div>
            <div class="stat-name">{{ app()->getLocale() === 'tr' ? 'Tamamlanan' : 'Completed' }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color:var(--brand-400);">{{ isset($reportData['avg_score']) && $reportData['avg_score'] ? number_format($reportData['avg_score'], 1) : '-' }}</div>
            <div class="stat-name">{{ app()->getLocale() === 'tr' ? 'Ort. Puan' : 'Avg Score' }}</div>
        </div>
    </div>

    {{-- Student Table --}}
    @if(isset($reportData['user_stats']) && count($reportData['user_stats']))
    <div class="data-table-wrap">
        <div class="data-table-header"><h3>👥 {{ app()->getLocale() === 'tr' ? 'Öğrenci Performansı' : 'Student Performance' }}</h3></div>
        <table class="data-table">
            <thead><tr>
                <th>{{ app()->getLocale() === 'tr' ? 'Öğrenci' : 'Student' }}</th>
                <th>{{ app()->getLocale() === 'tr' ? 'Tamamlanma' : 'Completion' }}</th>
                <th>{{ app()->getLocale() === 'tr' ? 'Puan' : 'Score' }}</th>
                <th></th>
            </tr></thead>
            <tbody>
            @foreach($reportData['user_stats'] as $us)
            <tr>
                <td style="color:white; font-weight:500;">{{ $us['user']->name ?? '' }} {{ $us['user']->surname ?? '' }}</td>
                <td>
                    <div style="display:flex; align-items:center; gap:0.5rem;">
                        <div class="progress-bar" style="width:80px;">
                            <div class="fill" style="width:{{ $us['completion_rate'] }}%; background:{{ $us['completion_rate'] >= 80 ? '#4ade80' : '#fbbf24' }};"></div>
                        </div>
                        {{ $us['completion_rate'] }}%
                    </div>
                </td>
                <td>{{ $us['avg_score'] ? number_format($us['avg_score'], 1) : '-' }}</td>
                <td><a href="{{ route('portal.reports.student', $us['user']) }}" class="btn btn-ghost btn-sm">{{ app()->getLocale() === 'tr' ? 'Detay' : 'Detail' }}</a></td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @endif
@else
    {{-- All Apps Summary --}}
    <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:1rem; margin-bottom:2rem;">
    @foreach($reportData as $r)
        <div class="stat-card">
            <h3 style="color:white; font-weight:600; margin-bottom:0.75rem;">{{ $r['app']->name }}</h3>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.5rem;">
                <div>
                    <div style="font-size:1.25rem; font-weight:700; color:#4ade80;">{{ $r['completed'] }}<span style="color:var(--gray-500); font-size:0.8rem; font-weight:400;">/{{ $r['total_progress'] }}</span></div>
                    <div style="font-size:0.7rem; color:var(--gray-500);">{{ app()->getLocale() === 'tr' ? 'Tamamlanan' : 'Completed' }}</div>
                </div>
                <div>
                    <div style="font-size:1.25rem; font-weight:700; color:var(--brand-400);">{{ $r['avg_score'] ? number_format($r['avg_score'], 1) : '-' }}</div>
                    <div style="font-size:0.7rem; color:var(--gray-500);">{{ app()->getLocale() === 'tr' ? 'Ort. Puan' : 'Avg Score' }}</div>
                </div>
            </div>
            <div class="progress-bar" style="margin-top:0.75rem;">
                <div class="fill" style="width:{{ $r['completion_rate'] }}%; background:linear-gradient(90deg,#4ade80,#22d3ee);"></div>
            </div>
        </div>
    @endforeach
    </div>

    {{-- Students List --}}
    <div class="data-table-wrap">
        <div class="data-table-header"><h3>👥 {{ app()->getLocale() === 'tr' ? 'Sınıf Öğrencileri' : 'Class Students' }}</h3></div>
        <table class="data-table">
            <thead><tr>
                <th>{{ app()->getLocale() === 'tr' ? 'Öğrenci' : 'Student' }}</th>
                <th>Email</th>
                <th></th>
            </tr></thead>
            <tbody>
            @foreach($class->students as $s)
            <tr>
                <td style="color:white; font-weight:500;">{{ $s->name }} {{ $s->surname }}</td>
                <td>{{ $s->email }}</td>
                <td><a href="{{ route('portal.reports.student', $s) }}" class="btn btn-ghost btn-sm">{{ app()->getLocale() === 'tr' ? 'Rapor' : 'Report' }}</a></td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endif
@endsection
