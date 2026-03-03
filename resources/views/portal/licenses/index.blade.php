@extends('portal.app')
@section('title', 'Lisans Yönetimi')
@section('page-title', 'Lisans Yönetimi')
@php $isTr = app()->getLocale() === 'tr'; @endphp

@section('content')
    <div class="dp-card">
        {{-- Header: Search + Add New License --}}
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
            <div class="dp-search" style="width:280px;">
                <svg width="16" height="16" fill="none" stroke="var(--color-txt-muted)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ $isTr ? 'Ara...' : 'Search' }}">
            </div>
            @if(auth()->user()->hasAnyRole(['super-admin','admin','license-manager','school-admin']))
                <a href="{{ route('portal.licenses.create') }}" class="dp-btn">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    {{ $isTr ? 'Yeni Lisans Ekle' : 'Add New License' }}
                </a>
            @endif
        </div>

        {{-- License Table — Figma node 1117-25324 --}}
        <div style="overflow-x:auto;">
        <table class="dp-table">
            <thead>
                <tr>
                    <th style="width:40px;">No</th>
                    <th>{{ $isTr ? 'Okul Adı' : 'School Name' }}</th>
                    <th>{{ $isTr ? 'Şehir/İlçe' : 'Country/State' }}</th>
                    <th>{{ $isTr ? 'Toplam Lisans' : 'Total Licenses' }}</th>
                    <th>{{ $isTr ? 'Durum' : 'Status' }}</th>
                    <th>{{ $isTr ? 'Satın Alma Tarihi' : 'Purchase Date' }}</th>
                    <th>{{ $isTr ? 'Lisans Süresi' : 'License Duration' }}</th>
                    <th>E-mail</th>
                    <th style="text-align:right;">{{ $isTr ? 'İşlemler' : 'Actions' }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($licenses as $idx => $lic)
                    @php
                        $isCancelled = ($lic->status ?? '') === 'cancelled';
                        $isExpired = ($lic->status ?? '') === 'expired';
                    @endphp
                    <tr @if($isCancelled || $isExpired) style="background:rgba(227,49,49,0.04);" @endif>
                        <td style="color:var(--color-txt-muted);">{{ str_pad($lic->id ?? ($idx + 1), 2, '0', STR_PAD_LEFT) }}</td>
                        <td style="font-weight:500;">{{ $lic->school_name ?? ($lic->school?->name ?? '—') }}</td>
                        <td class="muted">{{ $lic->city ?? '—' }}</td>
                        <td>{{ $lic->total_licenses ?? ($lic->seat_count ?? 0) }}</td>
                        <td>
                            @php $st = $lic->status ?? ($lic->is_active ? 'active' : 'not_started'); @endphp
                            @if($st === 'active')
                                <span class="dp-badge dp-badge-active">
                                    <svg width="12" height="12" fill="currentColor" viewBox="0 0 24 24" style="margin-right:4px;"><circle cx="12" cy="12" r="6"/></svg>
                                    {{ $isTr ? 'Aktif' : 'Active' }}
                                </span>
                            @elseif($st === 'cancelled')
                                <span class="dp-badge dp-badge-error">
                                    <svg width="12" height="12" fill="currentColor" viewBox="0 0 24 24" style="margin-right:4px;"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 15v-2h2v2h-2zm0-4V7h2v6h-2z"/></svg>
                                    {{ $isTr ? 'İptal' : 'Cancelled' }}
                                </span>
                            @elseif($st === 'expired')
                                <span class="dp-badge dp-badge-error">{{ $isTr ? 'Süresi Dolmuş' : 'Expired' }}</span>
                            @else
                                <span class="dp-badge dp-badge-inactive">
                                    <svg width="12" height="12" fill="currentColor" viewBox="0 0 24 24" style="margin-right:4px;"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" opacity=".3"/></svg>
                                    {{ $isTr ? 'Başlamadı' : 'Not Started' }}
                                </span>
                            @endif
                        </td>
                        <td class="muted">{{ $lic->purchase_date ?? ($lic->starts_at?->format('m/d/Y') ?? '—') }}</td>
                        <td class="muted">{{ $lic->license_duration ?? ($lic->expires_at?->format('m/d/Y') ?? '—') }}</td>
                        <td class="muted">{{ $lic->email ?? '—' }}</td>
                        <td style="text-align:right;">
                            <div style="display:flex;gap:6px;justify-content:flex-end;">
                                {{-- Edit --}}
                                <button class="dp-action dp-action-edit" title="{{ $isTr ? 'Düzenle' : 'Edit' }}" style="background:none;border:none;cursor:pointer;color:var(--color-txt-muted);padding:4px;">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                {{-- Activate/Deactivate --}}
                                <button class="dp-action" title="{{ $isTr ? 'Durumu Değiştir' : 'Toggle Status' }}" style="background:none;border:none;cursor:pointer;color:var(--color-primary);padding:4px;">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                </button>
                                {{-- Delete --}}
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
    </div>

    {{-- Pagination — Figma style: Previous | Page X of Y | Next --}}
    @if($licenses->hasPages())
    <div style="display:flex;justify-content:space-between;align-items:center;padding:16px 0;font-size:12px;">
        @if($licenses->onFirstPage())
            <span style="color:var(--color-txt-muted);cursor:default;">{{ $isTr ? 'Önceki' : 'Previous' }}</span>
        @else
            <a href="{{ $licenses->previousPageUrl() }}" style="color:var(--color-txt);text-decoration:none;">{{ $isTr ? 'Önceki' : 'Previous' }}</a>
        @endif

        <span style="color:var(--color-txt-muted);">{{ $isTr ? 'Sayfa' : 'Page' }}{{ $licenses->currentPage() }} {{ $isTr ? '/' : 'of' }} {{ $licenses->lastPage() }}</span>

        @if($licenses->hasMorePages())
            <a href="{{ $licenses->nextPageUrl() }}" class="dp-btn" style="font-size:12px;padding:6px 16px;">{{ $isTr ? 'Sonraki' : 'Next' }}</a>
        @else
            <span style="color:var(--color-txt-muted);cursor:default;">{{ $isTr ? 'Sonraki' : 'Next' }}</span>
        @endif
    </div>
    @endif
@endsection