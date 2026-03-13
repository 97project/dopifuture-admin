@extends('portal.app')
@section('title', app()->getLocale() === 'tr' ? 'Kullanıcılar' : 'Users')
@section('page-title', 'Administration')
@php
    $isTr = app()->getLocale() === 'tr';
    $currentRole = request('role', 'student');
    $studentCount = $users->total();
    $teacherCount = $users->total();
@endphp

@section('content')

    {{-- ═══ 3 STAT CARDS — Figma 1158-14034: horizontal icon-left value-right ═══ --}}
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:20px;">
        {{-- Total Licence — Green --}}
        <div style="display:flex;align-items:center;justify-content:space-between;padding:20px 24px;border-radius:16px;background:linear-gradient(135deg,#059669,#10B981);color:#fff;">
            <div style="display:flex;flex-direction:column;gap:4px;">
                <div style="width:40px;height:40px;border-radius:50%;background:rgba(255,255,255,0.2);display:flex;align-items:center;justify-content:center;">
                    <svg width="20" height="20" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <span style="font-size:13px;font-weight:500;opacity:0.9;">{{ $isTr ? 'Toplam Lisans' : 'Total Licence' }}</span>
            </div>
            <span style="font-size:36px;font-weight:700;font-family:'Nunito',sans-serif;">{{ $licenseStats->totalLicence ?? 0 }}</span>
        </div>
        {{-- Used Licence — Blue --}}
        <div style="display:flex;align-items:center;justify-content:space-between;padding:20px 24px;border-radius:16px;background:linear-gradient(135deg,#0284C7,#38BDF8);color:#fff;">
            <div style="display:flex;flex-direction:column;gap:4px;">
                <div style="width:40px;height:40px;border-radius:50%;background:rgba(255,255,255,0.2);display:flex;align-items:center;justify-content:center;">
                    <svg width="20" height="20" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
                <span style="font-size:13px;font-weight:500;opacity:0.9;">{{ $isTr ? 'Kullanılan Lisans' : 'Used Licence' }}</span>
            </div>
            <span style="font-size:36px;font-weight:700;font-family:'Nunito',sans-serif;">{{ $licenseStats->usedLicence ?? 0 }}</span>
        </div>
        {{-- Licence Duration — Orange --}}
        <div style="display:flex;align-items:center;justify-content:space-between;padding:20px 24px;border-radius:16px;background:linear-gradient(135deg,#EA580C,#FB923C);color:#fff;">
            <div style="display:flex;flex-direction:column;gap:4px;">
                <div style="width:40px;height:40px;border-radius:50%;background:rgba(255,255,255,0.2);display:flex;align-items:center;justify-content:center;">
                    <svg width="20" height="20" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <span style="font-size:13px;font-weight:500;opacity:0.9;">{{ $isTr ? 'Lisans Süresi' : 'Licence Duration' }}</span>
            </div>
            <span style="font-size:28px;font-weight:700;font-family:'Nunito',sans-serif;">{{ $licenseStats->licenceDuration ?? '-' }}</span>
        </div>
    </div>

    {{-- ═══ TAB BAR — Figma node-id: 1158-14034 ═══ --}}
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
        <div class="dp-tabs">
            <a href="{{ route('portal.users.index', ['role' => 'student']) }}"
               class="dp-tab {{ $currentRole === 'student' ? 'active' : '' }}">
                {{ $isTr ? 'Öğrenci Listesi' : 'Students List' }}
                <span class="tab-count">{{ $studentCount }}</span>
            </a>
            <a href="{{ route('portal.users.index', ['role' => 'teacher']) }}"
               class="dp-tab {{ $currentRole === 'teacher' ? 'active' : '' }}">
                {{ $isTr ? 'Öğretmen Listesi' : 'Teachers List' }}
                <span class="tab-count">{{ $teacherCount }}</span>
            </a>
        </div>

        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <a href="{{ route('portal.users.import.form') }}"
               style="display:inline-flex;align-items:center;gap:8px;padding:10px 24px;background:#4364F7;color:#fff;border:none;border-radius:999px;font-size:14px;font-weight:600;cursor:pointer;font-family:'Nunito',sans-serif;text-decoration:none;">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                {{ $isTr ? 'CSV Yükle' : 'CSV Import' }}
            </a>
            <button type="button" onclick="document.getElementById('addUserModal').style.display='flex'"
                    style="display:inline-flex;align-items:center;gap:8px;padding:10px 24px;background:#10B981;color:#fff;border:none;border-radius:999px;font-size:14px;font-weight:600;cursor:pointer;font-family:'Nunito',sans-serif;">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="2"/><path stroke-linecap="round" stroke-width="2" d="M12 8v8m-4-4h8"/></svg>
                {{ $currentRole === 'teacher' ? ($isTr ? 'Yeni Öğretmen Ekle' : 'Add New Teacher') : ($isTr ? 'Yeni Öğrenci Ekle' : 'Add New Student') }}
            </button>
        </div>
    </div>

    {{-- ═══ DATA TABLE ═══ --}}
    <div class="dp-card" style="padding:0;">
        <div style="overflow-x:auto;">
        <table class="dp-table">
            <thead>
                <tr>
                    <th style="width:40px;">No</th>
                    <th>{{ $currentRole === 'student' ? ($isTr ? 'Öğrenci Adı' : 'Student Name') : ($isTr ? 'Öğretmen Adı' : 'Teacher Name') }}</th>
                    <th>E-mail</th>
                    <th>{{ $currentRole === 'student' ? ($isTr ? 'Sınıf' : 'Grade') : ($isTr ? 'Branş' : 'Branch') }}</th>
                    <th>{{ $isTr ? 'İşlemler' : 'Actions' }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $i => $u)
                <tr>
                    <td class="muted">{{ str_pad(($users->currentPage()-1)*$users->perPage()+$i+1, 2, '0', STR_PAD_LEFT) }}</td>
                    <td>
                        <div class="dp-td-avatar">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($u->name . ' ' . ($u->surname ?? '')) }}&size=56&background=random&rounded=true&bold=true&font-size=0.4"
                                 alt="{{ $u->name }}" style="width:28px;height:28px;border-radius:50%;object-fit:cover;flex-shrink:0;">
                            <span style="font-weight:500;color:#030719;">{{ $u->name }} {{ $u->surname }}</span>
                        </div>
                    </td>
                    <td class="muted">{{ $u->email }}</td>
                    <td>{{ $u->grade ?? $u->branch ?? '—' }}</td>
                    <td>
                        <div style="display:flex;gap:16px;align-items:center;white-space:nowrap;">
                            <button style="background:none;border:none;cursor:pointer;color:#A0A0A0;padding:0;display:inline-flex;align-items:center;gap:4px;font-size:13px;">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                {{ $isTr ? 'Düzenle' : 'Edit' }}
                            </button>
                            <button style="background:none;border:none;cursor:pointer;color:#003AC9;padding:0;display:inline-flex;align-items:center;gap:4px;font-size:13px;">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                {{ $isTr ? 'Şifre Sıfırla' : 'Reset Password' }}
                            </button>
                            <button style="background:none;border:none;cursor:pointer;color:#A0A0A0;padding:0;display:inline-flex;align-items:center;gap:4px;font-size:13px;">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                {{ $isTr ? 'Sil' : 'Delete' }}
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align:center;padding:40px;color:var(--color-txt-muted);">
                        {{ $isTr ? 'Kullanıcı bulunamadı.' : 'No users found.' }}
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>

        {{-- Figma Pagination --}}
        @if($users->hasPages())
        <div style="display:flex;justify-content:space-between;align-items:center;padding:16px 20px;font-size:12px;">
            @if($users->onFirstPage())
                <span style="color:var(--color-txt-muted);cursor:default;">{{ $isTr ? 'Önceki' : 'Previous' }}</span>
            @else
                <a href="{{ $users->previousPageUrl() }}" style="color:var(--color-txt);text-decoration:none;">{{ $isTr ? 'Önceki' : 'Previous' }}</a>
            @endif
            <span style="color:var(--color-txt-muted);">{{ $isTr ? 'Sayfa' : 'Page' }} {{ $users->currentPage() }} {{ $isTr ? '/' : 'of' }} {{ $users->lastPage() }}</span>
            @if($users->hasMorePages())
                <a href="{{ $users->nextPageUrl() }}" class="dp-btn" style="font-size:12px;padding:6px 16px;">{{ $isTr ? 'Sonraki' : 'Next' }}</a>
            @else
                <span style="color:var(--color-txt-muted);cursor:default;">{{ $isTr ? 'Sonraki' : 'Next' }}</span>
            @endif
        </div>
        @endif
    </div>

    {{-- ═══ ADD USER MODAL — Figma node-id: 1164-16486 ═══ --}}
    <div id="addUserModal" class="dp-modal-overlay" style="display:none;" onclick="if(event.target===this)this.style.display='none'">
        <div class="dp-modal-card">
            <button type="button" class="dp-modal-close" onclick="document.getElementById('addUserModal').style.display='none'">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>

            <div class="dp-modal-title">{{ $currentRole === 'teacher' ? ($isTr ? 'Yeni Öğretmen Ekle' : 'Add New Teacher') : ($isTr ? 'Yeni Öğrenci Ekle' : 'Add New Student') }}</div>
            <p class="dp-modal-subtitle">{{ $isTr ? 'Bilgileri girin ve kaydedin.' : 'Fill in the details below to add a new ' . $currentRole . '.' }}</p>

            <form method="POST" action="{{ route('portal.users.store') }}">
                @csrf
                <input type="hidden" name="role" value="{{ $currentRole }}">

                <div style="margin-bottom:16px;">
                    <label style="display:block;font-size:13px;font-weight:500;color:var(--color-txt);margin-bottom:6px;">
                        {{ $currentRole === 'teacher' ? ($isTr ? 'Öğretmen Adı Soyadı' : 'Teacher Full Name') : ($isTr ? 'Öğrenci Adı Soyadı' : 'Student Full Name') }}
                    </label>
                    <input type="text" name="name" class="dp-form-input" placeholder="{{ $isTr ? 'Ad soyad girin' : 'Enter full name' }}" required>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:24px;">
                    <div>
                        <label style="display:block;font-size:13px;font-weight:500;color:var(--color-txt);margin-bottom:6px;">E-mail</label>
                        <input type="email" name="email" class="dp-form-input" placeholder="name@example.com" required>
                    </div>
                    <div>
                        <label style="display:block;font-size:13px;font-weight:500;color:var(--color-txt);margin-bottom:6px;">
                            {{ $currentRole === 'student' ? ($isTr ? 'Sınıf' : 'Grade') : ($isTr ? 'Branş' : 'Branch') }}
                        </label>
                        @if($currentRole === 'student')
                        <select name="grade" class="dp-form-select">
                            <option value="">{{ $isTr ? 'Seçin' : 'Please select' }}</option>
                            @for($g = 1; $g <= 12; $g++)
                            <option value="{{ $g }}">{{ $g }}</option>
                            @endfor
                        </select>
                        @else
                        <input type="text" name="branch" class="dp-form-input" placeholder="{{ $isTr ? 'Branş girin' : 'Enter branch' }}">
                        @endif
                    </div>
                </div>

                <button type="submit" class="dp-btn" style="width:100%;justify-content:center;padding:14px;">
                    {{ $isTr ? 'Bilgileri Kaydet' : 'Save Information' }}
                </button>
            </form>
        </div>
    </div>

@endsection