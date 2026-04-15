@extends('portal.app')
@section('title', 'User Detail')
@section('page-title', $user->name . ' ' . ($user->surname ?? ''))

@section('content')

    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
        <div style="display:flex;align-items:center;gap:12px;">
            <div style="width:48px;height:48px;border-radius:50%;background:linear-gradient(135deg,var(--color-primary),var(--color-primary-deep));color:#fff;display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:600;">{{ strtoupper(substr($user->name,0,1).substr($user->surname??'',0,1)) }}</div>
            <div>
                <div style="font-size:18px;font-weight:600;">{{ $user->name }} {{ $user->surname }}</div>
                <p style="font-size:13px;color:var(--color-txt-muted);margin:2px 0 0;">{{ $user->email }}</p>
            </div>
        </div>
        <div style="display:flex;gap:8px;">
            <a href="{{ route('portal.users.edit', $user) }}" class="dp-btn">{{ __('admin.edit') }}</a>
            <a href="{{ route('portal.users.index') }}" class="dp-btn-ghost">← Back</a>
        </div>
    </div>

    {{-- User Info --}}
    <div class="dp-card">
        <div class="dp-card-title">{{ __('portal.user_info') }}</div>
        <div class="dp-form-grid">
            <div>
                <div style="font-size:12px;color:var(--color-txt-muted);margin-bottom:4px;">First Name</div>
                <div style="font-weight:500;">{{ $user->name }}</div>
            </div>
            <div>
                <div style="font-size:12px;color:var(--color-txt-muted);margin-bottom:4px;">{{ __('admin.surname') }}</div>
                <div style="font-weight:500;">{{ $user->surname ?? '—' }}</div>
            </div>
            <div>
                <div style="font-size:12px;color:var(--color-txt-muted);margin-bottom:4px;">{{ __('admin.email') }}</div>
                <div style="font-weight:500;">{{ $user->email }}</div>
            </div>
            <div>
                <div style="font-size:12px;color:var(--color-txt-muted);margin-bottom:4px;">{{ __('admin.role') }}</div>
                <div>@foreach($user->roles as $role)<span class="dp-badge dp-badge-pending" style="margin-right:4px;">{{ $role->name }}</span>@endforeach</div>
            </div>
            <div>
                <div style="font-size:12px;color:var(--color-txt-muted);margin-bottom:4px;">{{ __('admin.status') }}</div>
                <span class="dp-badge {{ $user->status === 'active' ? 'dp-badge-active' : 'dp-badge-inactive' }}">{{ $user->status === 'active' ? 'Active' : 'Inactive' }}</span>
            </div>
            <div>
                <div style="font-size:12px;color:var(--color-txt-muted);margin-bottom:4px;">Registration Date</div>
                <div style="font-weight:500;">{{ $user->created_at?->format('Y-m-d H:i') }}</div>
            </div>
        </div>
    </div>

    {{-- Schools --}}
    @if($user->schools->count())
    <div class="dp-card">
        <div class="dp-card-title">{{ __('admin.schools') }}</div>
        <table class="dp-table">
            <thead><tr><th>{{ __('admin.school_name') }}</th><th>{{ __('admin.role') }}</th></tr></thead>
            <tbody>
                @foreach($user->schools as $school)
                <tr>
                    <td style="font-weight:500;">{{ $school->name }}</td>
                    <td><span class="dp-badge dp-badge-pending">{{ $school->pivot->role }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Classes --}}
    @if($user->classes->count())
    <div class="dp-card">
        <div class="dp-card-title">{{ __('admin.classes') }}</div>
        <table class="dp-table">
            <thead><tr><th>{{ __('portal.class') }}</th><th>{{ __('admin.school_name') }}</th><th></th></tr></thead>
            <tbody>
                @foreach($user->classes as $class)
                <tr>
                    <td style="font-weight:500;">{{ $class->name }}</td>
                    <td class="muted">{{ $class->school?->name ?? '—' }}</td>
                    <td style="text-align:right;"><a href="{{ route('portal.classes.show', $class) }}" class="dp-action dp-action-view"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Applications --}}
    @if($user->applications->count())
    <div class="dp-card">
        <div class="dp-card-title">{{ __('portal.applications') }}</div>
        <table class="dp-table">
            <thead><tr><th>{{ __('portal.application') }}</th><th>Granted At</th></tr></thead>
            <tbody>
                @foreach($user->applications as $app)
                <tr>
                    <td style="font-weight:500;">{{ $app->getTranslation('name') }}</td>
                    <td class="muted">{{ $app->pivot->granted_at ? \Carbon\Carbon::parse($app->pivot->granted_at)->format('Y-m-d') : '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Per-App Report Tabs --}}
    @if(isset($reportData) && count($reportData))
    <div style="font-size:16px;font-weight:600;margin:24px 0 12px;">📊 Application Reports</div>

    @foreach($reportData as $slug => $appData)
    <div class="dp-card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
            <div class="dp-card-title" style="margin-bottom:0;">{{ $appData['app']->name }}</div>
            <span class="dp-badge {{ $appData['stats']['completion_rate'] >= 80 ? 'dp-badge-active' : ($appData['stats']['completion_rate'] >= 40 ? 'dp-badge-pending' : 'dp-badge-error') }}">{{ $appData['stats']['completion_rate'] }}%</span>
        </div>

        <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:16px;text-align:center;margin-bottom:16px;">
            <div>
                <div style="font-size:20px;font-weight:700;">{{ $appData['stats']['total_modules'] }}</div>
                <div style="font-size:11px;color:var(--color-txt-muted);">Modules</div>
            </div>
            <div>
                <div style="font-size:20px;font-weight:700;color:var(--color-active-green);">{{ $appData['stats']['completed'] }}</div>
                <div style="font-size:11px;color:var(--color-txt-muted);">{{ __('portal.completed') }}</div>
            </div>
            <div>
                <div style="font-size:20px;font-weight:700;color:#fbbf24;">{{ $appData['stats']['in_progress'] }}</div>
                <div style="font-size:11px;color:var(--color-txt-muted);">In Progress</div>
            </div>
            <div>
                <div style="font-size:20px;font-weight:700;color:var(--color-primary);">{{ $appData['stats']['avg_score'] ? number_format($appData['stats']['avg_score'], 1) : '-' }}</div>
                <div style="font-size:11px;color:var(--color-txt-muted);">{{ __('portal.avg_score') }}</div>
            </div>
            <div>
                <div style="font-size:20px;font-weight:700;color:#a78bfa;">{{ $appData['stats']['total_sessions'] }}</div>
                <div style="font-size:11px;color:var(--color-txt-muted);">{{ __('portal.sessions') }}</div>
            </div>
        </div>

        <div class="dp-progress" style="margin-bottom:16px;">
            <div class="dp-progress-fill" style="width:{{ $appData['stats']['completion_rate'] }}%;"></div>
        </div>

        {{-- Module Progress --}}
        @if($appData['progress']->count())
        <div class="dp-card-title" style="font-size:14px;">📋 Module Progress</div>
        <table class="dp-table">
            <thead><tr>
                <th>{{ __('portal.module') }}</th><th>{{ __('portal.type') }}</th><th>{{ __('admin.status') }}</th><th>{{ __('portal.score') }}</th><th>{{ __('portal.attempts') }}</th><th>{{ __('admin.date') }}</th>
            </tr></thead>
            <tbody>
                @foreach($appData['progress'] as $p)
                @php $pObj = (object) $p; @endphp
                <tr>
                    <td style="font-weight:500;">{{ $pObj->module_name ?? $pObj->module_id ?? '-' }}</td>
                    <td><span class="dp-badge dp-badge-pending">{{ $pObj->module_type ?? 'module' }}</span></td>
                    <td><span class="dp-badge {{ ($pObj->status ?? '') === 'completed' ? 'dp-badge-active' : (($pObj->status ?? '') === 'in_progress' ? 'dp-badge-pending' : 'dp-badge-error') }}">{{ $pObj->status ?? 'unknown' }}</span></td>
                    <td>{{ isset($pObj->score) && $pObj->score !== null ? number_format((float)$pObj->score, 1) : '-' }}{{ !empty($pObj->max_score) ? '/'.$pObj->max_score : '' }}</td>
                    <td>{{ $pObj->attempts ?? 0 }}</td>
                    <td class="muted">
                        @php
                            $cAt = $pObj->completed_at ?? null;
                            $sAt = $pObj->started_at ?? null;
                            if ($cAt) echo $cAt instanceof \Carbon\Carbon ? $cAt->format('Y-m-d H:i') : \Carbon\Carbon::parse($cAt)->format('Y-m-d H:i');
                            elseif ($sAt) echo $sAt instanceof \Carbon\Carbon ? $sAt->format('Y-m-d H:i') : \Carbon\Carbon::parse($sAt)->format('Y-m-d H:i');
                            else echo '-';
                        @endphp
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        {{-- Session History --}}
        @if($appData['sessions']->count())
        <div class="dp-card-title" style="font-size:14px;margin-top:16px;">🕐 Session History</div>
        <table class="dp-table">
            <thead><tr>
                <th>{{ __('portal.session') }}</th><th>{{ __('portal.type') }}</th><th>Start</th><th>{{ __('portal.duration') }}</th><th>{{ __('portal.score') }}</th>
            </tr></thead>
            <tbody>
                @foreach($appData['sessions']->take(10) as $s)
                @php $sObj = (object) $s; @endphp
                <tr>
                    <td style="font-weight:500;">{{ $sObj->session_name ?? $sObj->external_session_id ?? '-' }}</td>
                    <td><span class="dp-badge dp-badge-pending">{{ $sObj->session_type ?? 'session' }}</span></td>
                    <td class="muted">
                        @php
                            $stAt = $sObj->started_at ?? null;
                            if ($stAt) echo $stAt instanceof \Carbon\Carbon ? $stAt->format('Y-m-d H:i') : \Carbon\Carbon::parse($stAt)->format('Y-m-d H:i');
                            else echo '-';
                        @endphp
                    </td>
                    <td>{{ !empty($sObj->duration_seconds) ? \App\Services\ReportService::formatDuration((int)$sObj->duration_seconds) : '-' }}</td>
                    <td>{{ isset($sObj->score) && $sObj->score !== null ? number_format((float)$sObj->score, 1) : '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        @if($appData['progress']->count() === 0 && $appData['sessions']->count() === 0)
        <div style="text-align:center;padding:24px;color:var(--color-txt-muted);">No data for this application yet.</div>
        @endif
    </div>
    @endforeach

    <div style="text-align:center;margin-top:16px;">
        <a href="{{ route('portal.reports.student', $user) }}" class="dp-btn">📊 View Full Report</a>
    </div>
    @endif
@endsection
