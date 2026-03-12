@extends('portal.app')
@section('title', ($isTr = app()->getLocale() === 'tr') ? 'Sınıf Detayı' : 'Class Detail')
@section('page-title', $class->name)

@section('content')
    @php $isTr = app()->getLocale() === 'tr'; @endphp

    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
        <div>
            <div style="font-size:18px;font-weight:600;">{{ $class->name }}</div>
            <p style="font-size:13px;color:var(--text-muted);margin:4px 0 0;">{{ $class->school?->name }} — {{ $isTr ? 'Seviye' : 'Grade' }}: {{ $class->grade_level ?? '—' }}</p>
        </div>
        <a href="{{ route('portal.classes.index') }}" class="dp-btn-ghost">← {{ $isTr ? 'Geri' : 'Back' }}</a>
    </div>

    {{-- Stats --}}
    <div class="dp-stats-grid" style="margin-bottom:20px;">
        <div class="dp-stat-card">
            <div class="s-icon"><svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg></div>
            <div class="s-value">{{ $class->students->count() }}</div>
            <div class="s-label">{{ $isTr ? 'Öğrenci' : 'Students' }}</div>
        </div>
        <div class="dp-stat-card">
            <div class="s-icon"><svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
            <div class="s-value">{{ $class->teachers->count() }}</div>
            <div class="s-label">{{ $isTr ? 'Öğretmen' : 'Teachers' }}</div>
        </div>
        <div class="dp-stat-card">
            <div class="s-icon"><svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></div>
            <div class="s-value" style="font-size:16px;">{{ $class->academic_year ?? '—' }}</div>
            <div class="s-label">{{ $isTr ? 'Akademik Yıl' : 'Academic Year' }}</div>
        </div>
    </div>

    {{-- Students --}}
    <div class="dp-card">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:0 0 12px;">
            <div class="dp-card-title" style="margin-bottom:0;">{{ $isTr ? 'Öğrenciler' : 'Students' }} <span style="font-weight:400;color:var(--text-muted);font-size:14px;">({{ $class->students->count() }})</span></div>
            @if($canManage && $availableStudents->count())
            <form action="{{ route('portal.classes.add-student', $class) }}" method="POST" style="display:flex;gap:8px;align-items:center;">
                @csrf
                <select name="user_id" class="dp-input" style="min-width:220px;padding:6px 10px;font-size:13px;" required>
                    <option value="">{{ $isTr ? 'Öğrenci seç...' : 'Select student...' }}</option>
                    @foreach($availableStudents as $s)
                    <option value="{{ $s->id }}">{{ $s->name }} {{ $s->surname }} ({{ $s->email }})</option>
                    @endforeach
                </select>
                <button type="submit" class="dp-btn" style="padding:6px 14px;font-size:13px;">+</button>
            </form>
            @endif
        </div>
        @if($class->students->count())
        <table class="dp-table">
            <thead><tr>
                <th>{{ $isTr ? 'Ad Soyad' : 'Name' }}</th>
                <th>E-posta</th>
                <th>{{ $isTr ? 'Durum' : 'Status' }}</th>
                <th style="text-align:right;"></th>
            </tr></thead>
            <tbody>
                @foreach($class->students as $student)
                <tr>
                    <td>
                        <div class="dp-td-avatar">
                            <div class="av">{{ strtoupper(substr($student->name,0,1).substr($student->surname??'',0,1)) }}</div>
                            <span style="font-weight:500;">{{ $student->name }} {{ $student->surname }}</span>
                        </div>
                    </td>
                    <td class="muted">{{ $student->email }}</td>
                    <td><span class="dp-badge {{ $student->status === 'active' ? 'dp-badge-active' : 'dp-badge-inactive' }}">{{ $student->status === 'active' ? ($isTr ? 'Aktif' : 'Active') : ($isTr ? 'Pasif' : 'Inactive') }}</span></td>
                    <td style="text-align:right;">
                        <div style="display:flex;gap:6px;justify-content:flex-end;">
                            <a href="{{ route('portal.users.show', $student) }}" class="dp-action dp-action-view"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></a>
                            @if($canManage)
                            <form action="{{ route('portal.classes.remove-student', [$class, $student]) }}" method="POST" onsubmit="return confirm('{{ $isTr ? 'Çıkarmak istediğinize emin misiniz?' : 'Are you sure?' }}')">
                                @csrf @method('DELETE')
                                <button type="submit" class="dp-action" style="color:var(--color-danger);border:none;background:none;cursor:pointer;padding:4px;" title="{{ $isTr ? 'Sınıftan çıkar' : 'Remove' }}"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div style="padding:32px;text-align:center;color:var(--text-muted);">{{ $isTr ? 'Bu sınıfta henüz öğrenci yok.' : 'No students in this class yet.' }}</div>
        @endif
    </div>

    {{-- Teachers --}}
    <div class="dp-card">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:0 0 12px;">
            <div class="dp-card-title" style="margin-bottom:0;">{{ $isTr ? 'Öğretmenler' : 'Teachers' }} <span style="font-weight:400;color:var(--text-muted);font-size:14px;">({{ $class->teachers->count() }})</span></div>
            @if($canManage && $availableTeachers->count())
            <form action="{{ route('portal.classes.add-teacher', $class) }}" method="POST" style="display:flex;gap:8px;align-items:center;">
                @csrf
                <select name="user_id" class="dp-input" style="min-width:220px;padding:6px 10px;font-size:13px;" required>
                    <option value="">{{ $isTr ? 'Öğretmen seç...' : 'Select teacher...' }}</option>
                    @foreach($availableTeachers as $t)
                    <option value="{{ $t->id }}">{{ $t->name }} {{ $t->surname }} ({{ $t->email }})</option>
                    @endforeach
                </select>
                <button type="submit" class="dp-btn" style="padding:6px 14px;font-size:13px;">+</button>
            </form>
            @endif
        </div>
        @if($class->teachers->count())
        <table class="dp-table">
            <thead><tr>
                <th>{{ $isTr ? 'Ad Soyad' : 'Name' }}</th>
                <th>E-posta</th>
                <th style="text-align:right;"></th>
            </tr></thead>
            <tbody>
                @foreach($class->teachers as $teacher)
                <tr>
                    <td>
                        <div class="dp-td-avatar">
                            <div class="av">{{ strtoupper(substr($teacher->name,0,1).substr($teacher->surname??'',0,1)) }}</div>
                            <span style="font-weight:500;">{{ $teacher->name }} {{ $teacher->surname }}</span>
                        </div>
                    </td>
                    <td class="muted">{{ $teacher->email }}</td>
                    <td style="text-align:right;">
                        <div style="display:flex;gap:6px;justify-content:flex-end;">
                            <a href="{{ route('portal.users.show', $teacher) }}" class="dp-action dp-action-view"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></a>
                            @if($canManage)
                            <form action="{{ route('portal.classes.remove-teacher', [$class, $teacher]) }}" method="POST" onsubmit="return confirm('{{ $isTr ? 'Çıkarmak istediğinize emin misiniz?' : 'Are you sure?' }}')">
                                @csrf @method('DELETE')
                                <button type="submit" class="dp-action" style="color:var(--color-danger);border:none;background:none;cursor:pointer;padding:4px;" title="{{ $isTr ? 'Sınıftan çıkar' : 'Remove' }}"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div style="padding:32px;text-align:center;color:var(--text-muted);">{{ $isTr ? 'Bu sınıfa henüz öğretmen atanmamış.' : 'No teachers assigned to this class yet.' }}</div>
        @endif
    </div>
@endsection