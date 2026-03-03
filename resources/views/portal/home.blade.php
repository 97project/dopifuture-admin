@extends('portal.app')
@section('title', 'DopiFuture')
@section('page-title', 'DopiFuture')
@php $isTr = app()->getLocale() === 'tr'; @endphp

@section('content')

    {{-- ═══ 2 STAT CARDS — Figma node-id: 1164-17862 ═══ --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px;">
        {{-- Average Login Count --}}
        <div class="dp-stat-card">
            <div class="s-icon">
                <svg width="20" height="20" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="s-value">{{ $avgLoginCount }}</div>
            <div class="s-label">{{ $isTr ? 'Ortalama Giriş Sayısı' : 'Average Login Count' }}</div>
        </div>

        {{-- Average Login Duration --}}
        <div class="dp-stat-card" style="background:linear-gradient(135deg, #6366F1, #8B5CF6);">
            <div class="s-icon">
                <svg width="20" height="20" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="s-value">{{ $avgLoginDuration }}</div>
            <div class="s-label">{{ $isTr ? 'Ortalama Giriş Süresi' : 'Average Login Duration' }}</div>
        </div>
    </div>

    {{-- ═══ STUDENT ACTIVITY TABLE ═══ --}}
    <div class="dp-card" style="padding:0;">
        <div style="display:flex;justify-content:space-between;align-items:center;padding:16px 20px;">
            <span style="font-weight:600;font-size:14px;">{{ $isTr ? 'Öğrenci Aktiviteleri' : 'Student Activities' }}</span>
            <div style="display:flex;gap:8px;align-items:center;">
                <select style="padding:8px 12px;border:1px solid var(--color-row-border);border-radius:8px;background:var(--color-input-bg);font-size:12px;color:var(--color-txt-muted);outline:none;font-family:inherit;">
                    <option>{{ $isTr ? 'Sınıf Seç' : 'Select Grade' }}</option>
                    @for($g = 1; $g <= 12; $g++)
                    <option value="{{ $g }}">{{ $g }}. {{ $isTr ? 'Sınıf' : 'Grade' }}</option>
                    @endfor
                </select>
            </div>
        </div>
        <div style="overflow-x:auto;">
        <table class="dp-table">
            <thead>
                <tr>
                    <th style="width:40px;">No</th>
                    <th>{{ $isTr ? 'Öğrenci Adı' : 'Student Name' }}</th>
                    <th>{{ $isTr ? 'Sınıf' : 'Grade' }}</th>
                    <th>{{ $isTr ? 'Son Giriş' : 'Last Login' }}</th>
                    <th>{{ $isTr ? 'Toplam Süre' : 'Total Time Spent' }}</th>
                    <th>{{ $isTr ? 'Toplam Kullanım' : 'Total Uses' }}</th>
                    <th>{{ $isTr ? 'İşlemler' : 'Actions' }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($students as $i => $s)
                <tr>
                    <td class="muted">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</td>
                    <td>
                        <div class="dp-td-avatar">
                            <div class="av">{{ strtoupper(substr($s->name,0,1).substr($s->surname,0,1)) }}</div>
                            <span style="font-weight:500;">{{ $s->name }} {{ $s->surname }}</span>
                        </div>
                    </td>
                    <td>{{ $s->grade }}</td>
                    <td class="muted">{{ $s->last_login }}</td>
                    <td class="muted">{{ $s->total_time }}</td>
                    <td>{{ $s->total_uses }}</td>
                    <td>
                        <div style="display:flex;gap:12px;align-items:center;white-space:nowrap;">
                            <button class="dp-action dp-action-edit" style="background:none;border:none;cursor:pointer;color:var(--color-txt-muted);padding:4px;display:inline-flex;align-items:center;gap:4px;font-size:12px;font-family:inherit;">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                {{ $isTr ? 'Düzenle' : 'Edit' }}
                            </button>
                            <button class="dp-action dp-action-view" style="background:none;border:none;cursor:pointer;color:var(--color-primary);padding:4px;display:inline-flex;align-items:center;gap:4px;font-size:12px;font-family:inherit;">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                {{ $isTr ? 'Şifre Sıfırla' : 'Reset Password' }}
                            </button>
                            <button class="dp-action" style="background:none;border:none;cursor:pointer;color:var(--color-error-red);padding:4px;display:inline-flex;align-items:center;gap:4px;font-size:12px;font-family:inherit;">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                {{ $isTr ? 'Sil' : 'Delete' }}
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>

        {{-- Pagination --}}
        <div style="display:flex;justify-content:space-between;align-items:center;padding:16px 20px;font-size:12px;">
            <span style="color:var(--color-txt-muted);cursor:default;">{{ $isTr ? 'Önceki' : 'Previous' }}</span>
            <span style="color:var(--color-txt-muted);">{{ $isTr ? 'Sayfa' : 'Page' }} 1 {{ $isTr ? '/' : 'of' }} 3</span>
            <a href="#" class="dp-btn" style="font-size:12px;padding:6px 16px;">{{ $isTr ? 'Sonraki' : 'Next' }}</a>
        </div>
    </div>

@endsection