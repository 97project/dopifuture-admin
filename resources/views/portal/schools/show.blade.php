@extends('portal.layout')
@section('title', ($isTr = app()->getLocale() === 'tr') ? 'Okul Detayı' : 'School Detail')

@section('content')
    @php $isTr = app()->getLocale() === 'tr'; @endphp

    <div class="page-header" style="display: flex; align-items: center; justify-content: space-between;">
        <div>
            <h1>{{ $school->getTranslation('name') }}</h1>
            <p>{{ $isTr ? 'Okul Detayı' : 'School Detail' }}</p>
        </div>
        <a href="{{ route('portal.schools.index') }}" class="btn btn-ghost">← {{ $isTr ? 'Geri' : 'Back' }}</a>
    </div>

    {{-- School Info --}}
    <div class="stats-grid" style="margin-bottom: 2rem;">
        <div class="stat-card">
            <div class="stat-icon" style="background: rgba(59,130,246,0.15);">
                <svg width="20" height="20" fill="none" stroke="var(--brand-400)" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>
            <div class="stat-value">{{ $school->users_count }}</div>
            <div class="stat-name">{{ $isTr ? 'Toplam Kullanıcı' : 'Total Users' }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: rgba(34,197,94,0.15);">
                <svg width="20" height="20" fill="none" stroke="#4ade80" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
            </div>
            <div class="stat-value">{{ $school->classes_count }}</div>
            <div class="stat-name">{{ $isTr ? 'Sınıf' : 'Classes' }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: rgba(251,191,36,0.15);">
                <svg width="20" height="20" fill="none" stroke="#fbbf24" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
            </div>
            <div class="stat-value">{{ $school->licenses_count }}</div>
            <div class="stat-name">{{ $isTr ? 'Lisans' : 'Licenses' }}</div>
        </div>
    </div>

    {{-- General Info --}}
    <div class="form-card" style="margin-bottom: 2rem;">
        <div class="form-grid-2">
            <div>
                <span style="font-size: 0.8rem; color: var(--gray-500);">{{ $isTr ? 'Şehir' : 'City' }}</span>
                <div style="color: white; margin-top: 0.25rem;">{{ $school->city ?? '—' }}</div>
            </div>
            <div>
                <span style="font-size: 0.8rem; color: var(--gray-500);">{{ $isTr ? 'Ülke' : 'Country' }}</span>
                <div style="color: white; margin-top: 0.25rem;">{{ $school->country ?? '—' }}</div>
            </div>
            <div>
                <span style="font-size: 0.8rem; color: var(--gray-500);">E-posta</span>
                <div style="color: white; margin-top: 0.25rem;">{{ $school->email ?? '—' }}</div>
            </div>
            <div>
                <span style="font-size: 0.8rem; color: var(--gray-500);">{{ $isTr ? 'Telefon' : 'Phone' }}</span>
                <div style="color: white; margin-top: 0.25rem;">{{ $school->phone ?? '—' }}</div>
            </div>
            <div>
                <span style="font-size: 0.8rem; color: var(--gray-500);">{{ $isTr ? 'Durum' : 'Status' }}</span>
                <div style="margin-top: 0.25rem;">
                    <span class="badge {{ $school->is_active ? 'badge-success' : 'badge-danger' }}">
                        {{ $school->is_active ? ($isTr ? 'Aktif' : 'Active') : ($isTr ? 'Pasif' : 'Inactive') }}
                    </span>
                </div>
            </div>
            @if($school->website)
                <div>
                    <span style="font-size: 0.8rem; color: var(--gray-500);">Website</span>
                    <div style="margin-top: 0.25rem;">
                        <a href="{{ $school->website }}" target="_blank"
                            style="color: var(--brand-400);">{{ $school->website }}</a>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Classes --}}
    @if($school->classes->count())
        <div class="data-table-wrap" style="margin-bottom: 2rem;">
            <div class="data-table-header">
                <h3>
                    <svg width="18" height="18" fill="none" stroke="var(--brand-400)" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    {{ $isTr ? 'Sınıflar' : 'Classes' }}
                    <span
                        style="font-weight: 400; color: var(--gray-400); margin-left: 0.5rem; font-size: 0.85rem;">({{ $school->classes->count() }})</span>
                </h3>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ $isTr ? 'Sınıf' : 'Class' }}</th>
                        <th>{{ $isTr ? 'Seviye' : 'Grade' }}</th>
                        <th>{{ $isTr ? 'Yıl' : 'Year' }}</th>
                        <th>{{ $isTr ? 'Öğrenci' : 'Students' }}</th>
                        <th>{{ $isTr ? 'Durum' : 'Status' }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($school->classes as $cls)
                        <tr>
                            <td style="color: white; font-weight: 500;">{{ $cls->name }}</td>
                            <td>{{ $cls->grade_level ?? '—' }}</td>
                            <td>{{ $cls->academic_year ?? '—' }}</td>
                            <td>{{ $cls->students_count }}</td>
                            <td>
                                <span class="badge {{ $cls->is_active ? 'badge-success' : 'badge-danger' }}">
                                    {{ $cls->is_active ? ($isTr ? 'Aktif' : 'Active') : ($isTr ? 'Pasif' : 'Inactive') }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('portal.classes.show', $cls) }}"
                                    class="btn btn-ghost btn-sm">{{ $isTr ? 'Detay' : 'Detail' }}</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- Users --}}
    @if($school->users->count())
        <div class="data-table-wrap" style="margin-bottom: 2rem;">
            <div class="data-table-header">
                <h3>
                    <svg width="18" height="18" fill="none" stroke="#a78bfa" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    {{ $isTr ? 'Kullanıcılar' : 'Users' }}
                    <span
                        style="font-weight: 400; color: var(--gray-400); margin-left: 0.5rem; font-size: 0.85rem;">({{ $school->users->count() }})</span>
                </h3>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ $isTr ? 'Ad Soyad' : 'Name' }}</th>
                        <th>E-posta</th>
                        <th>{{ $isTr ? 'Rol' : 'Role' }}</th>
                        <th>{{ $isTr ? 'Durum' : 'Status' }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($school->users as $u)
                        <tr>
                            <td style="color: white; font-weight: 500;">{{ $u->name }} {{ $u->surname }}</td>
                            <td>{{ $u->email }}</td>
                            <td>
                                <span class="badge badge-info">{{ $u->pivot->role ?? '—' }}</span>
                            </td>
                            <td>
                                <span class="badge {{ $u->status === 'active' ? 'badge-success' : 'badge-danger' }}">
                                    {{ $u->status === 'active' ? ($isTr ? 'Aktif' : 'Active') : ($isTr ? 'Pasif' : 'Inactive') }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('portal.users.show', $u) }}"
                                    class="btn btn-ghost btn-sm">{{ $isTr ? 'Detay' : 'Detail' }}</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- Licenses --}}
    @if($school->licenses->count())
        <div class="data-table-wrap">
            <div class="data-table-header">
                <h3>
                    <svg width="18" height="18" fill="none" stroke="#fbbf24" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    {{ $isTr ? 'Lisanslar' : 'Licenses' }}
                    <span
                        style="font-weight: 400; color: var(--gray-400); margin-left: 0.5rem; font-size: 0.85rem;">({{ $school->licenses->count() }})</span>
                </h3>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ $isTr ? 'Toplam' : 'Seats' }}</th>
                        <th>{{ $isTr ? 'Kullanılan' : 'Used' }}</th>
                        <th>{{ $isTr ? 'Kalan' : 'Remaining' }}</th>
                        <th>{{ $isTr ? 'Bitiş' : 'Expiry' }}</th>
                        <th>{{ $isTr ? 'Durum' : 'Status' }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($school->licenses as $lic)
                        <tr>
                            <td style="color: white; font-weight: 500;">{{ $lic->totalSeats() }}</td>
                            <td>{{ $lic->used_seats }}</td>
                            <td style="color: {{ $lic->availableSeats() > 0 ? '#4ade80' : '#f87171' }}; font-weight: 600;">
                                {{ $lic->availableSeats() }}</td>
                            <td style="font-size: 0.8rem; color: var(--gray-500);">{{ $lic->expires_at?->format('d.m.Y') ?? '—' }}
                            </td>
                            <td>
                                <span class="badge {{ $lic->is_active ? 'badge-success' : 'badge-danger' }}">
                                    {{ $lic->is_active ? ($isTr ? 'Aktif' : 'Active') : ($isTr ? 'Pasif' : 'Inactive') }}
                                </span>
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
@endsection