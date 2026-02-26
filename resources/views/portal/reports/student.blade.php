@extends('portal.layout')
@section('title', ($student->name ?? '') . ' — ' . __('admin.reports'))

@section('content')
<div class="page-header">
    <div style="display:flex; align-items:center; gap:1rem;">
        <a href="{{ route('portal.reports') }}" class="btn btn-ghost btn-sm">← {{ app()->getLocale() === 'tr' ? 'Geri' : 'Back' }}</a>
        <div>
            <h1>{{ $student->name }} {{ $student->surname }}</h1>
            <p>{{ $student->email }} — {{ app()->getLocale() === 'tr' ? 'Detaylı öğrenci raporu' : 'Detailed student report' }}</p>
        </div>
    </div>
</div>

{{-- Student Info --}}
<div class="form-card" style="margin-bottom:1.5rem;">
    <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(150px,1fr)); gap:1rem; text-align:center;">
        <div>
            <div style="font-size:0.75rem; color:var(--gray-500);">{{ app()->getLocale() === 'tr' ? 'Roller' : 'Roles' }}</div>
            <div style="margin-top:0.25rem;">@foreach($student->roles as $r)<span class="badge badge-info" style="margin-right:0.25rem;">{{ $r->name }}</span>@endforeach</div>
        </div>
        <div>
            <div style="font-size:0.75rem; color:var(--gray-500);">{{ app()->getLocale() === 'tr' ? 'Okullar' : 'Schools' }}</div>
            <div style="margin-top:0.25rem; color:white; font-weight:500;">{{ $student->schools->pluck('name')->join(', ') ?: '-' }}</div>
        </div>
        <div>
            <div style="font-size:0.75rem; color:var(--gray-500);">{{ app()->getLocale() === 'tr' ? 'Sınıflar' : 'Classes' }}</div>
            <div style="margin-top:0.25rem; color:white; font-weight:500;">{{ $student->classes->pluck('name')->join(', ') ?: '-' }}</div>
        </div>
        <div>
            <div style="font-size:0.75rem; color:var(--gray-500);">{{ app()->getLocale() === 'tr' ? 'Uygulamalar' : 'Applications' }}</div>
            <div style="margin-top:0.25rem; color:white; font-weight:500;">{{ $student->applications->count() }}</div>
        </div>
    </div>
</div>

{{-- App Tabs --}}
<div style="display:flex; gap:0.5rem; margin-bottom:1.5rem; flex-wrap:wrap;">
    @foreach($apps as $a)
        @php $active = isset($reportData[$a->slug]); @endphp
        <span class="btn {{ $active ? 'btn-primary' : 'btn-ghost' }}" style="font-size:0.8rem; pointer-events:none;">
            {{ $a->name }}
            @if($active)
                <span style="background:rgba(255,255,255,0.2); padding:0.1rem 0.4rem; border-radius:4px; font-size:0.7rem; margin-left:0.25rem;">{{ $reportData[$a->slug]['stats']['completion_rate'] }}%</span>
            @endif
        </span>
    @endforeach
</div>

{{-- Each App Report --}}
@foreach($reportData as $slug => $appData)
<div class="form-card" style="margin-bottom:1.5rem;">
    <h3 style="color:white; font-weight:600; font-size:1.1rem; margin-bottom:1rem; display:flex; align-items:center; gap:0.5rem;">
        {{ $appData['app']->name }}
        <span class="badge {{ $appData['stats']['completion_rate'] >= 80 ? 'badge-success' : ($appData['stats']['completion_rate'] >= 40 ? 'badge-info' : 'badge-danger') }}">
            {{ $appData['stats']['completion_rate'] }}%
        </span>
    </h3>

    {{-- Stats --}}
    <div style="display:grid; grid-template-columns:repeat(5,1fr); gap:1rem; text-align:center; margin-bottom:1.25rem;">
        <div>
            <div style="font-size:1.5rem; font-weight:700; color:white;">{{ $appData['stats']['total_modules'] }}</div>
            <div style="font-size:0.7rem; color:var(--gray-500);">{{ app()->getLocale() === 'tr' ? 'Modül' : 'Modules' }}</div>
        </div>
        <div>
            <div style="font-size:1.5rem; font-weight:700; color:#4ade80;">{{ $appData['stats']['completed'] }}</div>
            <div style="font-size:0.7rem; color:var(--gray-500);">{{ app()->getLocale() === 'tr' ? 'Tamamlanan' : 'Completed' }}</div>
        </div>
        <div>
            <div style="font-size:1.5rem; font-weight:700; color:#fbbf24;">{{ $appData['stats']['in_progress'] }}</div>
            <div style="font-size:0.7rem; color:var(--gray-500);">{{ app()->getLocale() === 'tr' ? 'Devam Eden' : 'In Progress' }}</div>
        </div>
        <div>
            <div style="font-size:1.5rem; font-weight:700; color:var(--brand-400);">{{ $appData['stats']['avg_score'] ? number_format($appData['stats']['avg_score'], 1) : '-' }}</div>
            <div style="font-size:0.7rem; color:var(--gray-500);">{{ app()->getLocale() === 'tr' ? 'Ort. Puan' : 'Avg Score' }}</div>
        </div>
        <div>
            <div style="font-size:1.5rem; font-weight:700; color:#a78bfa;">{{ $appData['stats']['total_sessions'] }}</div>
            <div style="font-size:0.7rem; color:var(--gray-500);">{{ app()->getLocale() === 'tr' ? 'Oturum' : 'Sessions' }}</div>
        </div>
    </div>

    <div class="progress-bar" style="margin-bottom:1.5rem;">
        <div class="fill" style="width:{{ $appData['stats']['completion_rate'] }}%; background:linear-gradient(90deg,#4ade80,#22d3ee);"></div>
    </div>

    {{-- Progress Table --}}
    @if($appData['progress']->count())
    <div class="data-table-wrap" style="margin-bottom:1rem;">
        <div class="data-table-header"><h3>📋 {{ app()->getLocale() === 'tr' ? 'Modül İlerlemesi' : 'Module Progress' }}</h3></div>
        <table class="data-table">
            <thead><tr>
                <th>{{ app()->getLocale() === 'tr' ? 'Modül' : 'Module' }}</th><th>{{ app()->getLocale() === 'tr' ? 'Tip' : 'Type' }}</th>
                <th>{{ app()->getLocale() === 'tr' ? 'Durum' : 'Status' }}</th><th>{{ app()->getLocale() === 'tr' ? 'Puan' : 'Score' }}</th>
                <th>{{ app()->getLocale() === 'tr' ? 'Deneme' : 'Attempts' }}</th><th>{{ app()->getLocale() === 'tr' ? 'Tarih' : 'Date' }}</th>
            </tr></thead>
            <tbody>
            @foreach($appData['progress'] as $p)
            <tr>
                <td style="color:white; font-weight:500;">{{ $p->module_name ?: $p->module_id }}</td>
                <td><span class="badge badge-info">{{ $p->module_type }}</span></td>
                <td><span class="badge {{ $p->status === 'completed' ? 'badge-success' : ($p->status === 'in_progress' ? 'badge-info' : 'badge-danger') }}">{{ $p->status }}</span></td>
                <td>{{ $p->score !== null ? number_format($p->score, 1) : '-' }}{{ $p->max_score ? '/'.$p->max_score : '' }}</td>
                <td>{{ $p->attempts }}</td>
                <td>{{ $p->completed_at ? $p->completed_at->format('d.m.Y H:i') : ($p->started_at ? $p->started_at->format('d.m.Y H:i') : '-') }}</td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Sessions Table --}}
    @if($appData['sessions']->count())
    <div class="data-table-wrap">
        <div class="data-table-header"><h3>🕐 {{ app()->getLocale() === 'tr' ? 'Oturum Geçmişi' : 'Session History' }}</h3></div>
        <table class="data-table">
            <thead><tr>
                <th>{{ app()->getLocale() === 'tr' ? 'Oturum' : 'Session' }}</th><th>{{ app()->getLocale() === 'tr' ? 'Tip' : 'Type' }}</th>
                <th>{{ app()->getLocale() === 'tr' ? 'Başlangıç' : 'Start' }}</th><th>{{ app()->getLocale() === 'tr' ? 'Süre' : 'Duration' }}</th>
                <th>{{ app()->getLocale() === 'tr' ? 'Puan' : 'Score' }}</th>
            </tr></thead>
            <tbody>
            @foreach($appData['sessions']->take(15) as $s)
            <tr>
                <td style="color:white; font-weight:500;">{{ $s->session_name ?: $s->external_session_id }}</td>
                <td><span class="badge badge-info">{{ $s->session_type }}</span></td>
                <td>{{ $s->started_at ? $s->started_at->format('d.m.Y H:i') : '-' }}</td>
                <td>{{ $s->duration_seconds ? \App\Services\ReportService::formatDuration($s->duration_seconds) : '-' }}</td>
                <td>{{ $s->score !== null ? number_format($s->score, 1) : '-' }}</td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
@endforeach

@if(empty($reportData))
<div class="form-card" style="text-align:center; padding:3rem;">
    <div style="font-size:2rem; margin-bottom:0.5rem;">📭</div>
    <p style="color:var(--gray-500);">{{ app()->getLocale() === 'tr' ? 'Henüz rapor verisi yok. Veriler uygulama senkronizasyonu sonrası burada görünecektir.' : 'No report data yet. Data will appear after application sync.' }}</p>
</div>
@endif
@endsection
