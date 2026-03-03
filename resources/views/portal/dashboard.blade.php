@extends('portal.app')
@section('title', app()->getLocale() === 'tr' ? 'Lisans Yönetimi' : 'License Management')


@php $isTr = app()->getLocale() === 'tr'; @endphp

@section('content')

    {{-- ═══ HEADER — Figma F-51: "Lisans Yönetimi" + "+ Add New License" button ═══ --}}
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
        <h2 style="font-size:24px; font-weight:700; color:var(--color-txt-dark); margin:0;">
            {{ $isTr ? 'Lisans Yönetimi' : 'License Management' }}
        </h2>
        <button onclick="document.getElementById('addLicenseModal').classList.add('show')"
                style="display:inline-flex; align-items:center; gap:8px; padding:10px 20px; background:#10B981; color:#fff; border:none; border-radius:8px; font-size:14px; font-weight:600; cursor:pointer; transition:background 0.2s;">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            {{ $isTr ? 'Yeni Lisans Ekle' : 'Add New License' }}
        </button>
    </div>

    {{-- ═══ LICENSE TABLE — Figma F-51: 1117-25324 ═══ --}}
    <div class="dp-card">
        {{-- Search --}}
        <div style="margin-bottom:16px;">
            <div class="dp-search" style="width:280px;">
                <svg width="16" height="16" fill="none" stroke="var(--color-txt-muted)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" placeholder="{{ $isTr ? 'Ara...' : 'Search...' }}">
            </div>
        </div>

        <div style="overflow-x:auto;">
        <table class="dp-table">
            <thead>
                <tr>
                    <th style="width:40px;">No</th>
                    <th>{{ $isTr ? 'Okul Adı' : 'School Name' }}</th>
                    <th>{{ $isTr ? 'Ülke/Şehir' : 'Country/State' }}</th>
                    <th>{{ $isTr ? 'Toplam Lisans' : 'Total Licenses' }}</th>
                    <th>{{ $isTr ? 'Durum' : 'Status' }}</th>
                    <th>{{ $isTr ? 'Satın Alma Tarihi' : 'Purchase Date' }}</th>
                    <th>{{ $isTr ? 'Lisans Süresi' : 'License Duration' }}</th>
                    <th>{{ $isTr ? 'E-posta' : 'E-mail' }}</th>
                    <th style="text-align:right;">{{ $isTr ? 'İşlemler' : 'Actions' }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse(($data['licenses'] ?? []) as $idx => $license)
                <tr style="{{ ($license['status'] ?? '') === 'cancelled' ? 'background:rgba(239,68,68,0.04);' : '' }}">
                    <td style="color:var(--color-txt-muted);">{{ str_pad($idx + 1, 2, '0', STR_PAD_LEFT) }}</td>
                    <td style="font-weight:500;">{{ $license['school'] ?? '' }}</td>
                    <td class="muted">{{ $license['location'] ?? '—' }}</td>
                    <td>{{ $license['total'] ?? 0 }}</td>
                    <td>
                        @php $st = $license['status'] ?? 'active'; @endphp
                        @if($st === 'active')
                            <span class="dp-badge dp-badge-active">
                                <svg width="12" height="12" fill="currentColor" viewBox="0 0 24 24" style="margin-right:4px;"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Active
                            </span>
                        @elseif($st === 'not_started')
                            <span class="dp-badge" style="background:rgba(107,114,128,0.1);color:#6B7280;">
                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-right:4px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Not Started
                            </span>
                        @elseif($st === 'cancelled')
                            <span class="dp-badge" style="background:rgba(239,68,68,0.1);color:#EF4444;">
                                <svg width="12" height="12" fill="currentColor" viewBox="0 0 24 24" style="margin-right:4px;"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 15l-5-5 1.41-1.41L11 14.17l7.59-7.59L20 8l-9 9z"/></svg>
                                Cancelled
                            </span>
                        @elseif($st === 'expired')
                            <span class="dp-badge" style="background:rgba(245,158,11,0.1);color:#F59E0B;">Expired</span>
                        @endif
                    </td>
                    <td class="muted">{{ $license['purchase_date'] ?? '—' }}</td>
                    <td class="muted">{{ $license['duration'] ?? '—' }}</td>
                    <td class="muted" style="font-size:13px;">{{ $license['email'] ?? '—' }}</td>
                    <td style="text-align:right;">
                        <div style="display:flex;gap:6px;justify-content:flex-end;">
                            <button class="dp-action dp-action-edit" title="{{ $isTr ? 'Düzenle' : 'Edit' }}" style="background:none;border:none;cursor:pointer;color:var(--color-txt-muted);padding:4px;">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>
                            <button class="dp-action" title="{{ $isTr ? 'Detay' : 'Details' }}" style="background:none;border:none;cursor:pointer;color:var(--color-primary);padding:4px;">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </button>
                            <button class="dp-action" title="{{ $isTr ? 'Sil' : 'Delete' }}" style="background:none;border:none;cursor:pointer;color:var(--color-error-red);padding:4px;">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="text-align:center;padding:40px;color:var(--color-txt-muted);">
                        {{ $isTr ? 'Henüz lisans bulunamadı.' : 'No licenses found.' }}
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>

        {{-- Pagination — Figma F-51 ═══ --}}
        <div style="display:flex; justify-content:space-between; align-items:center; padding:16px 0 0; border-top:1px solid var(--color-border); margin-top:16px;">
            <button style="padding:8px 16px; border:1px solid var(--color-border); border-radius:6px; background:#fff; cursor:pointer; font-size:13px; color:var(--color-txt-muted);">
                {{ $isTr ? 'Önceki' : 'Previous' }}
            </button>
            <span style="font-size:13px; color:var(--color-txt-muted);">
                {{ $isTr ? 'Sayfa 1 / 12' : 'Page 1 of 12' }}
            </span>
            <button style="padding:8px 16px; border:1px solid var(--color-border); border-radius:6px; background:#fff; cursor:pointer; font-size:13px; color:var(--color-txt-muted);">
                {{ $isTr ? 'Sonraki' : 'Next' }}
            </button>
        </div>
    </div>

    {{-- ═══ ADD LICENSE MODAL — Figma F-52: 1151-12665 ═══ --}}
    <div class="dp-modal-overlay" id="addLicenseModal">
        <div class="dp-modal" style="max-width:500px;">
            <div class="dp-modal-header">
                <h3>{{ $isTr ? 'Yeni Lisans Ekle' : 'Add New License' }}</h3>
                <button class="dp-modal-close" onclick="document.getElementById('addLicenseModal').classList.remove('show')">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="dp-modal-body" style="display:flex;flex-direction:column;gap:16px;">
                <div>
                    <label class="dp-label">{{ $isTr ? 'Okul Adı' : 'School Name' }}</label>
                    <input class="dp-input" type="text" placeholder="{{ $isTr ? 'Okul adı girin' : 'Enter school name' }}">
                </div>
                <div>
                    <label class="dp-label">{{ $isTr ? 'Ülke/Şehir' : 'Country/State' }}</label>
                    <input class="dp-input" type="text" placeholder="{{ $isTr ? 'Ülke/şehir girin' : 'Enter country/state' }}">
                </div>
                <div>
                    <label class="dp-label">{{ $isTr ? 'Lisans Sayısı' : 'Number of Licenses' }}</label>
                    <input class="dp-input" type="number" placeholder="0">
                </div>
                <div>
                    <label class="dp-label">{{ $isTr ? 'Lisans Süresi' : 'License Duration' }}</label>
                    <input class="dp-input" type="text" placeholder="{{ $isTr ? 'Örn: 12/31/2026' : 'e.g. 12/31/2026' }}">
                </div>
                <div>
                    <label class="dp-label">{{ $isTr ? 'E-posta' : 'E-mail' }}</label>
                    <input class="dp-input" type="email" placeholder="{{ $isTr ? 'E-posta girin' : 'Enter email' }}">
                </div>
            </div>
            <div class="dp-modal-footer">
                <button type="button" class="dp-btn dp-btn-secondary" onclick="document.getElementById('addLicenseModal').classList.remove('show')">
                    {{ $isTr ? 'İptal' : 'Cancel' }}
                </button>
                <button type="button" class="dp-btn dp-btn-primary">
                    {{ $isTr ? 'Kaydet' : 'Save' }}
                </button>
            </div>
        </div>
    </div>

@endsection