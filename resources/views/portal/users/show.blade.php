@extends('portal.layout')
@section('title', ($isTr = app()->getLocale() === 'tr') ? 'Kullanıcı Detayı' : 'User Detail')

@section('content')
    @php $isTr = app()->getLocale() === 'tr'; @endphp

    <div class="page-header" style="display: flex; align-items: center; justify-content: space-between;">
        <div>
            <h1>{{ $user->name }} {{ $user->surname }}</h1>
            <p>{{ $user->email }} — {{ $user->roles->pluck('name')->implode(', ') }}</p>
        </div>
        <a href="{{ route('portal.users.index') }}" class="btn btn-ghost">← {{ $isTr ? 'Geri' : 'Back' }}</a>
    </div>

    {{-- User Info --}}
    <div class="form-card" style="margin-bottom: 2rem;">
        <div class="form-grid-2">
            <div>
                <div class="form-label">{{ $isTr ? 'Ad' : 'First Name' }}</div>
                <div style="color: white; font-weight: 500; margin-bottom: 1rem;">{{ $user->name }}</div>
            </div>
            <div>
                <div class="form-label">{{ $isTr ? 'Soyad' : 'Last Name' }}</div>
                <div style="color: white; font-weight: 500; margin-bottom: 1rem;">{{ $user->surname ?? '—' }}</div>
            </div>
            <div>
                <div class="form-label">E-posta</div>
                <div style="color: white; font-weight: 500; margin-bottom: 1rem;">{{ $user->email }}</div>
            </div>
            <div>
                <div class="form-label">{{ $isTr ? 'Rol' : 'Role' }}</div>
                <div style="margin-bottom: 1rem;">
                    @foreach($user->roles as $role)
                        <span class="badge badge-info">{{ $role->name }}</span>
                    @endforeach
                </div>
            </div>
            <div>
                <div class="form-label">{{ $isTr ? 'Durum' : 'Status' }}</div>
                <div style="margin-bottom: 1rem;">
                    <span class="badge {{ $user->status === 'active' ? 'badge-success' : 'badge-danger' }}">
                        {{ $user->status === 'active' ? ($isTr ? 'Aktif' : 'Active') : ($isTr ? 'Pasif' : 'Inactive') }}
                    </span>
                </div>
            </div>
            <div>
                <div class="form-label">{{ $isTr ? 'Kayıt Tarihi' : 'Registration Date' }}</div>
                <div style="color: var(--gray-400); font-weight: 500; margin-bottom: 1rem;">{{ $user->created_at?->format('d.m.Y H:i') }}</div>
            </div>
        </div>
    </div>

    {{-- Schools --}}
    @if($user->schools->count())
        <div class="data-table-wrap">
            <div class="data-table-header">
                <h3>
                    <svg width="18" height="18" fill="none" stroke="var(--brand-400)" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    {{ $isTr ? 'Okullar' : 'Schools' }}
                </h3>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ $isTr ? 'Okul' : 'School' }}</th>
                        <th>{{ $isTr ? 'Rol' : 'Role' }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($user->schools as $school)
                        <tr>
                            <td style="color: white; font-weight: 500;">{{ $school->getTranslation('name') }}</td>
                            <td><span class="badge badge-info">{{ $school->pivot->role }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- Classes --}}
    @if($user->classes->count())
        <div class="data-table-wrap">
            <div class="data-table-header">
                <h3>
                    <svg width="18" height="18" fill="none" stroke="#a78bfa" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    {{ $isTr ? 'Sınıflar' : 'Classes' }}
                </h3>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ $isTr ? 'Sınıf' : 'Class' }}</th>
                        <th>{{ $isTr ? 'Okul' : 'School' }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($user->classes as $class)
                        <tr>
                            <td style="color: white; font-weight: 500;">{{ $class->name }}</td>
                            <td>{{ $class->school?->getTranslation('name') ?? '—' }}</td>
                            <td>
                                <a href="{{ route('portal.classes.show', $class) }}" class="btn btn-ghost btn-sm">{{ $isTr ? 'Detay' : 'Detail' }}</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- Applications --}}
    @if($user->applications->count())
        <div class="data-table-wrap">
            <div class="data-table-header">
                <h3>
                    <svg width="18" height="18" fill="none" stroke="#4ade80" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                    {{ $isTr ? 'Uygulamalar' : 'Applications' }}
                </h3>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ $isTr ? 'Uygulama' : 'Application' }}</th>
                        <th>{{ $isTr ? 'Erişim Tarihi' : 'Granted At' }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($user->applications as $app)
                        <tr>
                            <td style="color: white; font-weight: 500;">{{ $app->getTranslation('name') }}</td>
                            <td style="color: var(--gray-400); font-size: 0.85rem;">{{ $app->pivot->granted_at ? \Carbon\Carbon::parse($app->pivot->granted_at)->format('d.m.Y') : '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- ═══ Per-App Report Tabs ═══ --}}
    @if(isset($reportData) && count($reportData))
        <h2 style="color:white; font-size:1.15rem; font-weight:600; margin:2rem 0 1rem;">📊 {{ $isTr ? 'Uygulama Raporları' : 'Application Reports' }}</h2>

        @foreach($reportData as $slug => $appData)
        <div class="form-card" style="margin-bottom:1.5rem;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
                <h3 style="color:white; font-weight:600; font-size:1rem;">{{ $appData['app']->name }}</h3>
                <span class="badge {{ $appData['stats']['completion_rate'] >= 80 ? 'badge-success' : ($appData['stats']['completion_rate'] >= 40 ? 'badge-info' : 'badge-danger') }}">
                    {{ $appData['stats']['completion_rate'] }}%
                </span>
            </div>

            {{-- Stats --}}
            <div style="display:grid; grid-template-columns:repeat(5,1fr); gap:1rem; text-align:center; margin-bottom:1rem;">
                <div>
                    <div style="font-size:1.25rem; font-weight:700; color:white;">{{ $appData['stats']['total_modules'] }}</div>
                    <div style="font-size:0.7rem; color:var(--gray-500);">{{ $isTr ? 'Modül' : 'Modules' }}</div>
                </div>
                <div>
                    <div style="font-size:1.25rem; font-weight:700; color:#4ade80;">{{ $appData['stats']['completed'] }}</div>
                    <div style="font-size:0.7rem; color:var(--gray-500);">{{ $isTr ? 'Tamamlanan' : 'Completed' }}</div>
                </div>
                <div>
                    <div style="font-size:1.25rem; font-weight:700; color:#fbbf24;">{{ $appData['stats']['in_progress'] }}</div>
                    <div style="font-size:0.7rem; color:var(--gray-500);">{{ $isTr ? 'Devam Eden' : 'In Progress' }}</div>
                </div>
                <div>
                    <div style="font-size:1.25rem; font-weight:700; color:var(--brand-400);">{{ $appData['stats']['avg_score'] ? number_format($appData['stats']['avg_score'], 1) : '-' }}</div>
                    <div style="font-size:0.7rem; color:var(--gray-500);">{{ $isTr ? 'Ort. Puan' : 'Avg Score' }}</div>
                </div>
                <div>
                    <div style="font-size:1.25rem; font-weight:700; color:#a78bfa;">{{ $appData['stats']['total_sessions'] }}</div>
                    <div style="font-size:0.7rem; color:var(--gray-500);">{{ $isTr ? 'Oturum' : 'Sessions' }}</div>
                </div>
            </div>

            <div class="progress-bar" style="margin-bottom:1.25rem;">
                <div class="fill" style="width:{{ $appData['stats']['completion_rate'] }}%; background:linear-gradient(90deg,#4ade80,#22d3ee);"></div>
            </div>

            {{-- Module Progress Table --}}
            @if($appData['progress']->count())
            <div class="data-table-wrap" style="margin-bottom:1rem;">
                <div class="data-table-header"><h3>📋 {{ $isTr ? 'Modül İlerlemesi' : 'Module Progress' }}</h3></div>
                <table class="data-table">
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
                        <td style="color:white; font-weight:500;">{{ $p->module_name ?: $p->module_id }}</td>
                        <td><span class="badge badge-info">{{ $p->module_type }}</span></td>
                        <td><span class="badge {{ $p->status === 'completed' ? 'badge-success' : ($p->status === 'in_progress' ? 'badge-info' : 'badge-danger') }}">{{ $p->status }}</span></td>
                        <td>{{ $p->score !== null ? number_format($p->score, 1) : '-' }}{{ $p->max_score ? '/'.$p->max_score : '' }}</td>
                        <td>{{ $p->attempts }}</td>
                        <td style="font-size:0.8rem;">{{ $p->completed_at ? $p->completed_at->format('d.m.Y H:i') : ($p->started_at ? $p->started_at->format('d.m.Y H:i') : '-') }}</td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            {{-- Session History --}}
            @if($appData['sessions']->count())
            <div class="data-table-wrap">
                <div class="data-table-header"><h3>🕐 {{ $isTr ? 'Oturum Geçmişi' : 'Session History' }}</h3></div>
                <table class="data-table">
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
                        <td style="color:white; font-weight:500;">{{ $s->session_name ?: $s->external_session_id }}</td>
                        <td><span class="badge badge-info">{{ $s->session_type }}</span></td>
                        <td style="font-size:0.8rem;">{{ $s->started_at ? $s->started_at->format('d.m.Y H:i') : '-' }}</td>
                        <td>{{ $s->duration_seconds ? \App\Services\ReportService::formatDuration($s->duration_seconds) : '-' }}</td>
                        <td>{{ $s->score !== null ? number_format($s->score, 1) : '-' }}</td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            @if($appData['progress']->count() === 0 && $appData['sessions']->count() === 0)
            <div style="text-align:center; padding:1.5rem; color:var(--gray-500);">
                {{ $isTr ? 'Bu uygulamada henüz veri yok.' : 'No data for this application yet.' }}
            </div>
            @endif
        </div>
        @endforeach

        <div style="text-align:center; margin-top:1rem;">
            <a href="{{ route('portal.reports.student', $user) }}" class="btn btn-primary">📊 {{ $isTr ? 'Tam Raporu Görüntüle' : 'View Full Report' }}</a>
        </div>
    @endif
@endsection
