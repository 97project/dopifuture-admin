@extends('portal.app')
@section('title', ($isTr = app()->getLocale() === 'tr') ? 'Okul Detayı' : 'School Detail')
@section('page-title', $school->name)

@section('content')
    @php $isTr = app()->getLocale() === 'tr'; @endphp

    {{-- Back + Title --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
        <div>
            <div style="font-size:18px;font-weight:600;color:var(--text-primary);">{{ $school->name }}</div>
            <p style="font-size:13px;color:var(--text-muted);margin:4px 0 0;">{{ $isTr ? 'Okul Detayı' : 'School Detail' }}</p>
        </div>
        <a href="{{ route('portal.schools.index') }}" class="dp-btn-ghost">← {{ $isTr ? 'Geri' : 'Back' }}</a>
    </div>

    {{-- Stat Cards --}}
    <div class="dp-stats-grid" style="margin-bottom:20px;">
        <div class="dp-stat-card">
            <div class="s-icon">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <div class="s-value">{{ $school->users_count }}</div>
            <div class="s-label">{{ $isTr ? 'Toplam Kullanıcı' : 'Total Users' }}</div>
        </div>
        <div class="dp-stat-card">
            <div class="s-icon">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            </div>
            <div class="s-value">{{ $school->classes_count }}</div>
            <div class="s-label">{{ $isTr ? 'Sınıf' : 'Classes' }}</div>
        </div>
        <div class="dp-stat-card">
            <div class="s-icon">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </div>
            <div class="s-value">{{ $school->licenses_count }}</div>
            <div class="s-label">{{ $isTr ? 'Lisans' : 'Licenses' }}</div>
        </div>
    </div>

    {{-- General Info --}}
    <div class="dp-card">
        <div class="dp-card-title">{{ $isTr ? 'Genel Bilgiler' : 'General Information' }}</div>
        <div class="dp-form-grid">
            <div>
                <div style="font-size:12px;color:var(--text-muted);margin-bottom:4px;">{{ $isTr ? 'Şehir' : 'City' }}</div>
                <div style="font-weight:500;">{{ $school->city ?? '—' }}</div>
            </div>
            <div>
                <div style="font-size:12px;color:var(--text-muted);margin-bottom:4px;">{{ $isTr ? 'Ülke' : 'Country' }}</div>
                <div style="font-weight:500;">{{ $school->country ?? '—' }}</div>
            </div>
            <div>
                <div style="font-size:12px;color:var(--text-muted);margin-bottom:4px;">E-posta</div>
                <div style="font-weight:500;">{{ $school->email ?? '—' }}</div>
            </div>
            <div>
                <div style="font-size:12px;color:var(--text-muted);margin-bottom:4px;">{{ $isTr ? 'Telefon' : 'Phone' }}</div>
                <div style="font-weight:500;">{{ $school->phone ?? '—' }}</div>
            </div>
            <div>
                <div style="font-size:12px;color:var(--text-muted);margin-bottom:4px;">{{ $isTr ? 'Durum' : 'Status' }}</div>
                <span class="dp-badge {{ $school->is_active ? 'dp-badge-active' : 'dp-badge-inactive' }}">
                    {{ $school->is_active ? ($isTr ? 'Aktif' : 'Active') : ($isTr ? 'Pasif' : 'Inactive') }}
                </span>
            </div>
            @if($school->website)
            <div>
                <div style="font-size:12px;color:var(--text-muted);margin-bottom:4px;">Website</div>
                <a href="{{ $school->website }}" target="_blank" style="color:var(--primary);font-weight:500;">{{ $school->website }}</a>
            </div>
            @endif
        </div>
    </div>

    {{-- Classes Table --}}
    @if($school->classes->count())
    <div class="dp-card">
        <div class="dp-card-title">{{ $isTr ? 'Sınıflar' : 'Classes' }} <span style="font-weight:400;color:var(--text-muted);font-size:14px;">({{ $school->classes->count() }})</span></div>
        <div style="overflow-x:auto;">
        <table class="dp-table">
            <thead><tr>
                <th>{{ $isTr ? 'Sınıf' : 'Class' }}</th>
                <th>{{ $isTr ? 'Seviye' : 'Grade' }}</th>
                <th>{{ $isTr ? 'Yıl' : 'Year' }}</th>
                <th>{{ $isTr ? 'Öğrenci' : 'Students' }}</th>
                <th>{{ $isTr ? 'Durum' : 'Status' }}</th>
                <th style="text-align:right;"></th>
            </tr></thead>
            <tbody>
                @foreach($school->classes as $cls)
                <tr>
                    <td style="font-weight:500;">{{ $cls->name }}</td>
                    <td class="muted">{{ $cls->grade_level ?? '—' }}</td>
                    <td class="muted">{{ $cls->academic_year ?? '—' }}</td>
                    <td>{{ $cls->students_count }}</td>
                    <td><span class="dp-badge {{ $cls->is_active ? 'dp-badge-active' : 'dp-badge-inactive' }}">{{ $cls->is_active ? ($isTr ? 'Aktif' : 'Active') : ($isTr ? 'Pasif' : 'Inactive') }}</span></td>
                    <td style="text-align:right;">
                        <a href="{{ route('portal.classes.show', $cls) }}" class="dp-action dp-action-view"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>
    @endif

    {{-- Users Table --}}
    @if($school->users->count())
    <div class="dp-card">
        <div class="dp-card-title">{{ $isTr ? 'Kullanıcılar' : 'Users' }} <span style="font-weight:400;color:var(--text-muted);font-size:14px;">({{ $school->users->count() }})</span></div>
        <div style="overflow-x:auto;">
        <table class="dp-table">
            <thead><tr>
                <th>{{ $isTr ? 'Ad Soyad' : 'Name' }}</th>
                <th>E-posta</th>
                <th>{{ $isTr ? 'Rol' : 'Role' }}</th>
                <th>{{ $isTr ? 'Durum' : 'Status' }}</th>
                <th style="text-align:right;"></th>
            </tr></thead>
            <tbody>
                @foreach($school->users as $u)
                <tr>
                    <td>
                        <div class="dp-td-avatar">
                            <div class="av">{{ strtoupper(substr($u->name,0,1).substr($u->surname??'',0,1)) }}</div>
                            <span style="font-weight:500;">{{ $u->name }} {{ $u->surname }}</span>
                        </div>
                    </td>
                    <td class="muted">{{ $u->email }}</td>
                    <td><span class="dp-badge dp-badge-pending">{{ $u->pivot->role ?? '—' }}</span></td>
                    <td><span class="dp-badge {{ $u->status === 'active' ? 'dp-badge-active' : 'dp-badge-inactive' }}">{{ $u->status === 'active' ? ($isTr ? 'Aktif' : 'Active') : ($isTr ? 'Pasif' : 'Inactive') }}</span></td>
                    <td style="text-align:right;">
                        <a href="{{ route('portal.users.show', $u) }}" class="dp-action dp-action-view"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>
    @endif

    {{-- Licenses Table --}}
    @if($school->licenses->count())
    <div class="dp-card">
        <div class="dp-card-title">{{ $isTr ? 'Lisanslar' : 'Licenses' }} <span style="font-weight:400;color:var(--text-muted);font-size:14px;">({{ $school->licenses->count() }})</span></div>
        <div style="overflow-x:auto;">
        <table class="dp-table">
            <thead><tr>
                <th>{{ $isTr ? 'Toplam' : 'Seats' }}</th>
                <th>{{ $isTr ? 'Kullanılan' : 'Used' }}</th>
                <th>{{ $isTr ? 'Kalan' : 'Remaining' }}</th>
                <th>{{ $isTr ? 'Bitiş' : 'Expiry' }}</th>
                <th>{{ $isTr ? 'Durum' : 'Status' }}</th>
                <th style="text-align:right;"></th>
            </tr></thead>
            <tbody>
                @foreach($school->licenses as $lic)
                <tr>
                    <td style="font-weight:500;">{{ $lic->totalSeats() }}</td>
                    <td>{{ $lic->used_seats }}</td>
                    <td style="font-weight:600;color:{{ $lic->availableSeats() > 0 ? 'var(--active-green)' : 'var(--error-red)' }};">{{ $lic->availableSeats() }}</td>
                    <td class="muted">{{ $lic->expires_at?->format('d.m.Y') ?? '—' }}</td>
                    <td><span class="dp-badge {{ $lic->is_active ? 'dp-badge-active' : 'dp-badge-error' }}">{{ $lic->is_active ? ($isTr ? 'Aktif' : 'Active') : ($isTr ? 'Pasif' : 'Inactive') }}</span></td>
                    <td style="text-align:right;">
                        <a href="{{ route('portal.licenses.show', $lic) }}" class="dp-action dp-action-view"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>
    @endif
@endsection