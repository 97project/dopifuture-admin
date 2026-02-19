@extends('portal.layout')
@section('title', app()->getLocale() === 'tr' ? 'Genel Bakış' : 'Dashboard')
@php $isTr = app()->getLocale() === 'tr'; @endphp

@section('content')
    <div class="page-header">
        <h1>{{ $isTr ? 'Hoş Geldiniz' : 'Welcome' }}, {{ auth()->user()->name }} 👋</h1>
        <p>{{ $isTr ? 'Portal genel bakışı ve özet istatistikler.' : 'Portal overview and summary statistics.' }}</p>
    </div>

    {{-- Stat Cards --}}
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background: rgba(139,92,246,0.15);">
                <svg width="20" height="20" fill="none" stroke="#a78bfa" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
            </div>
            <div class="stat-value">{{ $data['totalClasses'] ?? 0 }}</div>
            <div class="stat-name">{{ $isTr ? 'Toplam Sınıf' : 'Total Classes' }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: rgba(34,197,94,0.15);">
                <svg width="20" height="20" fill="none" stroke="#4ade80" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>
            <div class="stat-value">{{ $data['totalUsers'] ?? 0 }}</div>
            <div class="stat-name">{{ $isTr ? 'Toplam Kullanıcı' : 'Total Users' }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: rgba(251,191,36,0.15);">
                <svg width="20" height="20" fill="none" stroke="#fbbf24" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
            </div>
            <div class="stat-value">{{ $data['activeLicenses'] ?? 0 }} <span class="sub">/
                    {{ $data['totalLicenses'] ?? 0 }}</span></div>
            <div class="stat-name">{{ $isTr ? 'Aktif Lisans' : 'Active Licenses' }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: rgba(236,72,153,0.15);">
                <svg width="20" height="20" fill="none" stroke="#f472b6" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            </div>
            <div class="stat-value">{{ $data['totalStudents'] ?? 0 }}</div>
            <div class="stat-name">{{ $isTr ? 'Öğrenci' : 'Students' }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: rgba(14,165,233,0.15);">
                <svg width="20" height="20" fill="none" stroke="#38bdf8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="stat-value">{{ $data['totalTeachers'] ?? 0 }}</div>
            <div class="stat-name">{{ $isTr ? 'Öğretmen' : 'Teachers' }}</div>
        </div>
    </div>

    {{-- App Usage --}}
    @if(isset($data['appStats']) && $data['appStats']->count())
        <div class="data-table-wrap">
            <div class="data-table-header">
                <h3>
                    <svg width="18" height="18" fill="none" stroke="var(--brand-400)" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                    {{ $isTr ? 'Uygulama Kullanımı' : 'Application Usage' }}
                </h3>
            </div>
            <div style="padding: 1.25rem;">
                @php $maxAppUsers = $data['appStats']->max('users_count') ?: 1; @endphp
                @foreach($data['appStats'] as $app)
                    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.75rem;">
                        <span
                            style="width: 140px; font-size: 0.85rem; color: var(--gray-300); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $app->getTranslation('name') }}</span>
                        <div class="progress-bar" style="flex: 1; height: 8px;">
                            <div class="fill"
                                style="width: {{ ($app->users_count / $maxAppUsers) * 100 }}%; background: linear-gradient(90deg, var(--brand-500), var(--brand-400));">
                            </div>
                        </div>
                        <span
                            style="font-size: 0.8rem; color: var(--gray-500); min-width: 40px; text-align: right;">{{ $app->users_count }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- License Utilization --}}
    @if(isset($data['licenseStats']) && $data['licenseStats']->count())
        <div class="data-table-wrap">
            <div class="data-table-header">
                <h3>
                    <svg width="18" height="18" fill="none" stroke="#fbbf24" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    {{ $isTr ? 'Lisans Durumu' : 'License Status' }}
                </h3>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ $isTr ? 'Okul' : 'School' }}</th>
                        <th>{{ $isTr ? 'Doluluk' : 'Utilization' }}</th>
                        <th>{{ $isTr ? 'Kalan Hak' : 'Remaining' }}</th>
                        <th>{{ $isTr ? 'Bitiş' : 'Expiry' }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['licenseStats'] as $lic)
                        @php
                            $total = $lic->totalSeats();
                            $pct = $total > 0 ? round(($lic->used_seats / $total) * 100) : 0;
                            $remaining = $lic->availableSeats();
                        @endphp
                        <tr>
                            <td style="font-weight: 500; color: white;">
                                {{ $lic->school?->getTranslation('name') ?? ($lic->notes ?: '—') }}
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 0.75rem;">
                                    <div class="progress-bar" style="flex:1; max-width: 100px;">
                                        <div class="fill"
                                            style="width: {{ $pct }}%; background: {{ $pct >= 90 ? '#f87171' : ($pct >= 70 ? '#fbbf24' : '#4ade80') }};">
                                        </div>
                                    </div>
                                    <span
                                        style="font-size: 0.75rem; color: var(--gray-400);">{{ $lic->used_seats }}/{{ $lic->totalSeats() }}</span>
                                </div>
                            </td>
                            <td style="font-weight: 600; color: {{ $remaining > 0 ? '#4ade80' : '#f87171' }};">
                                {{ $remaining }}
                            </td>
                            <td style="font-size: 0.8rem; color: var(--gray-500);">{{ $lic->expires_at?->format('d.m.Y') ?? '—' }}
                            </td>
                            <td>
                                <a href="{{ route('portal.licenses.show', $lic) }}"
                                    class="btn btn-ghost btn-sm">{{ $isTr ? 'Detay' : 'Detail' }}</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- School Distribution --}}
    @if(isset($data['schoolDistribution']) && $data['schoolDistribution']->count())
        <div class="data-table-wrap">
            <div class="data-table-header">
                <h3>
                    <svg width="18" height="18" fill="none" stroke="#4ade80" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    {{ $isTr ? 'Okul Dağılımı' : 'School Distribution' }}
                </h3>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ $isTr ? 'Okul' : 'School' }}</th>
                        <th>{{ $isTr ? 'Kullanıcı' : 'Users' }}</th>
                        <th>{{ $isTr ? 'Sınıf' : 'Classes' }}</th>
                        <th>{{ $isTr ? 'Lisans' : 'Licenses' }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['schoolDistribution'] as $school)
                        <tr>
                            <td style="font-weight: 500; color: white;">{{ $school->getTranslation('name') }}</td>
                            <td>{{ $school->users_count }}</td>
                            <td>{{ $school->classes_count }}</td>
                            <td>{{ $school->licenses_count }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- Recent Users --}}
    @if(isset($data['recentUsers']) && $data['recentUsers']->count())
        <div class="data-table-wrap">
            <div class="data-table-header">
                <h3>
                    <svg width="18" height="18" fill="none" stroke="#a78bfa" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ $isTr ? 'Son Eklenen Kullanıcılar' : 'Recent Users' }}
                </h3>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ $isTr ? 'Ad Soyad' : 'Name' }}</th>
                        <th>E-posta</th>
                        <th>{{ $isTr ? 'Tarih' : 'Date' }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['recentUsers'] as $ru)
                        <tr>
                            <td style="color: white;">{{ $ru->name }} {{ $ru->surname }}</td>
                            <td>{{ $ru->email }}</td>
                            <td style="font-size: 0.8rem; color: var(--gray-500);">{{ $ru->created_at?->format('d.m.Y H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection