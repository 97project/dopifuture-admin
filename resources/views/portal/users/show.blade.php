@extends('portal.app')
@section('title', ($isTr = app()->getLocale() === 'tr') ? 'Kullanıcı Detayı' : 'User Detail')
@section('page-title', $user->name . ' ' . ($user->surname ?? ''))

@section('content')
    @php $isTr = app()->getLocale() === 'tr'; @endphp

    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
        <div style="display:flex;align-items:center;gap:12px;">
            <div style="width:48px;height:48px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--primary-deep));color:#fff;display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:600;">{{ strtoupper(substr($user->name,0,1).substr($user->surname??'',0,1)) }}</div>
            <div>
                <div style="font-size:18px;font-weight:600;">{{ $user->name }} {{ $user->surname }}</div>
                <p style="font-size:13px;color:var(--text-muted);margin:2px 0 0;">{{ $user->email }}</p>
            </div>
        </div>
        <a href="{{ route('portal.users.index') }}" class="dp-btn-ghost">← {{ $isTr ? 'Geri' : 'Back' }}</a>
    </div>

    {{-- User Info --}}
    <div class="dp-card">
        <div class="dp-card-title">{{ $isTr ? 'Kullanıcı Bilgileri' : 'User Information' }}</div>
        <div class="dp-form-grid">
            <div>
                <div style="font-size:12px;color:var(--text-muted);margin-bottom:4px;">{{ $isTr ? 'Ad' : 'First Name' }}</div>
                <div style="font-weight:500;">{{ $user->name }}</div>
            </div>
            <div>
                <div style="font-size:12px;color:var(--text-muted);margin-bottom:4px;">{{ $isTr ? 'Soyad' : 'Last Name' }}</div>
                <div style="font-weight:500;">{{ $user->surname ?? '—' }}</div>
            </div>
            <div>
                <div style="font-size:12px;color:var(--text-muted);margin-bottom:4px;">E-posta</div>
                <div style="font-weight:500;">{{ $user->email }}</div>
            </div>
            <div>
                <div style="font-size:12px;color:var(--text-muted);margin-bottom:4px;">{{ $isTr ? 'Rol' : 'Role' }}</div>
                <div>@foreach($user->roles as $role)<span class="dp-badge dp-badge-pending" style="margin-right:4px;">{{ $role->name }}</span>@endforeach</div>
            </div>
            <div>
                <div style="font-size:12px;color:var(--text-muted);margin-bottom:4px;">{{ $isTr ? 'Durum' : 'Status' }}</div>
                <span class="dp-badge {{ $user->status === 'active' ? 'dp-badge-active' : 'dp-badge-inactive' }}">{{ $user->status === 'active' ? ($isTr ? 'Aktif' : 'Active') : ($isTr ? 'Pasif' : 'Inactive') }}</span>
            </div>
            <div>
                <div style="font-size:12px;color:var(--text-muted);margin-bottom:4px;">{{ $isTr ? 'Kayıt Tarihi' : 'Registration Date' }}</div>
                <div style="font-weight:500;">{{ $user->created_at?->format('d.m.Y H:i') }}</div>
            </div>
        </div>
    </div>

    {{-- Schools --}}
    @if($user->schools->count())
    <div class="dp-card">
        <div class="dp-card-title">{{ $isTr ? 'Okullar' : 'Schools' }}</div>
        <table class="dp-table">
            <thead><tr><th>{{ $isTr ? 'Okul' : 'School' }}</th><th>{{ $isTr ? 'Rol' : 'Role' }}</th></tr></thead>
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
        <div class="dp-card-title">{{ $isTr ? 'Sınıflar' : 'Classes' }}</div>
        <table class="dp-table">
            <thead><tr><th>{{ $isTr ? 'Sınıf' : 'Class' }}</th><th>{{ $isTr ? 'Okul' : 'School' }}</th><th></th></tr></thead>
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
        <div class="dp-card-title">{{ $isTr ? 'Uygulamalar' : 'Applications' }}</div>
        <table class="dp-table">
            <thead><tr><th>{{ $isTr ? 'Uygulama' : 'Application' }}</th><th>{{ $isTr ? 'Erişim Tarihi' : 'Granted At' }}</th></tr></thead>
            <tbody>
                @foreach($user->applications as $app)
                <tr>
                    <td style="font-weight:500;">{{ $app->getTranslation('name') }}</td>
                    <td class="muted">{{ $app->pivot->granted_at ? \Carbon\Carbon::parse($app->pivot->granted_at)->format('d.m.Y') : '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Per-App Report Tabs --}}
    @if(isset($reportData) && count($reportData))
    <div style="font-size:16px;font-weight:600;margin:24px 0 12px;">📊 {{ $isTr ? 'Uygulama Raporları' : 'Application Reports' }}</div>

    @foreach($reportData as $slug => $appData)
    <div class="dp-card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
            <div class="dp-card-title" style="margin-bottom:0;">{{ $appData['app']->name }}</div>
            <span class="dp-badge {{ $appData['stats']['completion_rate'] >= 80 ? 'dp-badge-active' : ($appData['stats']['completion_rate'] >= 40 ? 'dp-badge-pending' : 'dp-badge-error') }}">{{ $appData['stats']['completion_rate'] }}%</span>
        </div>

        <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:16px;text-align:center;margin-bottom:16px;">
            <div>
                <div style="font-size:20px;font-weight:700;">{{ $appData['stats']['total_modules'] }}</div>
                <div style="font-size:11px;color:var(--text-muted);">{{ $isTr ? 'Modül' : 'Modules' }}</div>
            </div>
            <div>
                <div style="font-size:20px;font-weight:700;color:var(--active-green);">{{ $appData['stats']['completed'] }}</div>
                <div style="font-size:11px;color:var(--text-muted);">{{ $isTr ? 'Tamamlanan' : 'Completed' }}</div>
            </div>
            <div>
                <div style="font-size:20px;font-weight:700;color:#fbbf24;">{{ $appData['stats']['in_progress'] }}</div>
                <div style="font-size:11px;color:var(--text-muted);">{{ $isTr ? 'Devam Eden' : 'In Progress' }}</div>
            </div>
            <div>
                <div style="font-size:20px;font-weight:700;color:var(--primary);">{{ $appData['stats']['avg_score'] ? number_format($appData['stats']['avg_score'], 1) : '-' }}</div>
                <div style="font-size:11px;color:var(--text-muted);">{{ $isTr ? 'Ort. Puan' : 'Avg Score' }}</div>
            </div>
            <div>
                <div style="font-size:20px;font-weight:700;color:#a78bfa;">{{ $appData['stats']['total_sessions'] }}</div>
                <div style="font-size:11px;color:var(--text-muted);">{{ $isTr ? 'Oturum' : 'Sessions' }}</div>
            </div>
        </div>

        <div class="dp-progress" style="margin-bottom:16px;">
            <div class="dp-progress-fill" style="width:{{ $appData['stats']['completion_rate'] }}%;"></div>
        </div>

        {{-- Module Progress --}}
        @if($appData['progress']->count())
        <div class="dp-card-title" style="font-size:14px;">📋 {{ $isTr ? 'Modül İlerlemesi' : 'Module Progress' }}</div>
        <table class="dp-table">
            <thead><tr>
                <th>{{ $isTr ? 'Modül' : 'Module' }}</th>
                <th>{{ $isTr ? 'Tip' : 'Type' }}</th>
                <th>{{ $isTr ? 'Durum' : 'Status' }}</th>
                <th>{{ $isTr ? 'Puan' : 'Score' }}</th>
                <th>{{ $isTr ? 'Deneme' : 'Attempts' }}</th>
                <th>{{ $isTr ? 'Tarih' : 'Date' }}</th>
            </tr></thead>
            <tbody>
                @foreach($appData['progress'] as $p)
                <tr>
                    <td style="font-weight:500;">{{ $p->module_name ?: $p->module_id }}</td>
                    <td><span class="dp-badge dp-badge-pending">{{ $p->module_type }}</span></td>
                    <td><span class="dp-badge {{ $p->status === 'completed' ? 'dp-badge-active' : ($p->status === 'in_progress' ? 'dp-badge-pending' : 'dp-badge-error') }}">{{ $p->status }}</span></td>
                    <td>{{ $p->score !== null ? number_format($p->score, 1) : '-' }}{{ $p->max_score ? '/'.$p->max_score : '' }}</td>
                    <td>{{ $p->attempts }}</td>
                    <td class="muted">{{ $p->completed_at ? $p->completed_at->format('d.m.Y H:i') : ($p->started_at ? $p->started_at->format('d.m.Y H:i') : '-') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        {{-- Session History --}}
        @if($appData['sessions']->count())
        <div class="dp-card-title" style="font-size:14px;margin-top:16px;">🕐 {{ $isTr ? 'Oturum Geçmişi' : 'Session History' }}</div>
        <table class="dp-table">
            <thead><tr>
                <th>{{ $isTr ? 'Oturum' : 'Session' }}</th>
                <th>{{ $isTr ? 'Tip' : 'Type' }}</th>
                <th>{{ $isTr ? 'Başlangıç' : 'Start' }}</th>
                <th>{{ $isTr ? 'Süre' : 'Duration' }}</th>
                <th>{{ $isTr ? 'Puan' : 'Score' }}</th>
            </tr></thead>
            <tbody>
                @foreach($appData['sessions']->take(10) as $s)
                <tr>
                    <td style="font-weight:500;">{{ $s->session_name ?: $s->external_session_id }}</td>
                    <td><span class="dp-badge dp-badge-pending">{{ $s->session_type }}</span></td>
                    <td class="muted">{{ $s->started_at ? $s->started_at->format('d.m.Y H:i') : '-' }}</td>
                    <td>{{ $s->duration_seconds ? \App\Services\ReportService::formatDuration($s->duration_seconds) : '-' }}</td>
                    <td>{{ $s->score !== null ? number_format($s->score, 1) : '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        @if($appData['progress']->count() === 0 && $appData['sessions']->count() === 0)
        <div style="text-align:center;padding:24px;color:var(--text-muted);">{{ $isTr ? 'Bu uygulamada henüz veri yok.' : 'No data for this application yet.' }}</div>
        @endif
    </div>
    @endforeach

    <div style="text-align:center;margin-top:16px;">
        <a href="{{ route('portal.reports.student', $user) }}" class="dp-btn">📊 {{ $isTr ? 'Tam Raporu Görüntüle' : 'View Full Report' }}</a>
    </div>
    @endif
@endsection
