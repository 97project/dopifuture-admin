@extends('portal.layout')
@section('title', ($isTr = app()->getLocale() === 'tr') ? 'Sınıf Detayı' : 'Class Detail')

@section('content')
    @php $isTr = app()->getLocale() === 'tr'; @endphp

    <div class="page-header" style="display: flex; align-items: center; justify-content: space-between;">
        <div>
            <h1>{{ $class->name }}</h1>
            <p>{{ $class->school?->getTranslation('name') }} — {{ $isTr ? 'Seviye' : 'Grade' }}:
                {{ $class->grade_level ?? '—' }}</p>
        </div>
        <a href="{{ route('portal.classes.index') }}" class="btn btn-ghost">← {{ $isTr ? 'Geri' : 'Back' }}</a>
    </div>

    {{-- Class Info --}}
    <div class="stats-grid" style="margin-bottom: 2rem;">
        <div class="stat-card">
            <div class="stat-icon" style="background: rgba(236,72,153,0.15);">
                <svg width="20" height="20" fill="none" stroke="#f472b6" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            </div>
            <div class="stat-value">{{ $class->students->count() }}</div>
            <div class="stat-name">{{ $isTr ? 'Öğrenci' : 'Students' }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: rgba(14,165,233,0.15);">
                <svg width="20" height="20" fill="none" stroke="#38bdf8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="stat-value">{{ $class->teachers->count() }}</div>
            <div class="stat-name">{{ $isTr ? 'Öğretmen' : 'Teachers' }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: rgba(251,191,36,0.15);">
                <svg width="20" height="20" fill="none" stroke="#fbbf24" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
            <div class="stat-value" style="font-size: 1rem;">{{ $class->academic_year ?? '—' }}</div>
            <div class="stat-name">{{ $isTr ? 'Akademik Yıl' : 'Academic Year' }}</div>
        </div>
    </div>

    {{-- Students --}}
    <div class="data-table-wrap">
        <div class="data-table-header">
            <h3>
                <svg width="18" height="18" fill="none" stroke="#f472b6" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                {{ $isTr ? 'Öğrenciler' : 'Students' }}
            </h3>
        </div>
        @if($class->students->count())
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ $isTr ? 'Ad Soyad' : 'Name' }}</th>
                        <th>E-posta</th>
                        <th>{{ $isTr ? 'Durum' : 'Status' }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($class->students as $student)
                        <tr>
                            <td style="color: white; font-weight: 500;">{{ $student->name }} {{ $student->surname }}</td>
                            <td>{{ $student->email }}</td>
                            <td>
                                <span class="badge {{ $student->status === 'active' ? 'badge-success' : 'badge-danger' }}">
                                    {{ $student->status === 'active' ? ($isTr ? 'Aktif' : 'Active') : ($isTr ? 'Pasif' : 'Inactive') }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('portal.users.show', $student) }}"
                                    class="btn btn-ghost btn-sm">{{ $isTr ? 'Detay' : 'Detail' }}</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div style="padding: 2rem; text-align: center; color: var(--gray-500);">
                {{ $isTr ? 'Bu sınıfta henüz öğrenci yok.' : 'No students in this class yet.' }}
            </div>
        @endif
    </div>

    {{-- Teachers --}}
    <div class="data-table-wrap">
        <div class="data-table-header">
            <h3>
                <svg width="18" height="18" fill="none" stroke="#38bdf8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ $isTr ? 'Öğretmenler' : 'Teachers' }}
            </h3>
        </div>
        @if($class->teachers->count())
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ $isTr ? 'Ad Soyad' : 'Name' }}</th>
                        <th>E-posta</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($class->teachers as $teacher)
                        <tr>
                            <td style="color: white; font-weight: 500;">{{ $teacher->name }} {{ $teacher->surname }}</td>
                            <td>{{ $teacher->email }}</td>
                            <td>
                                <a href="{{ route('portal.users.show', $teacher) }}"
                                    class="btn btn-ghost btn-sm">{{ $isTr ? 'Detay' : 'Detail' }}</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div style="padding: 2rem; text-align: center; color: var(--gray-500);">
                {{ $isTr ? 'Bu sınıfa henüz öğretmen atanmamış.' : 'No teachers assigned to this class yet.' }}
            </div>
        @endif
    </div>
@endsection