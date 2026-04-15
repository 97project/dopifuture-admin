@extends('portal.app')
@section('title', __('portal.license_management'))
@section('page-title', 'License Management')
@section('content')
    <div class="dp-card">
        {{-- Header: Search + Add New License --}}
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
            <div class="dp-search" style="width:280px;">
                <svg width="16" height="16" fill="none" stroke="var(--color-txt-muted)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('portal.search') }}">
            </div>
            @if(auth()->user()->hasAnyRole(['super-admin','admin','license-manager','school-admin']))
                <a href="{{ route('portal.licenses.create') }}" class="dp-btn">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add New License
                </a>
            @endif
        </div>

        {{-- License Table — Figma node 1117-25324 --}}
        <div style="overflow-x:auto;">
        <table class="dp-table">
            <thead>
                <tr>
                    <th style="width:40px;">{{ __('portal.no_num') }}</th>
                    <th>{{ __('admin.school_name') }}</th>
                    <th>{{ __('portal.country_state') }}</th>
                    <th>{{ __('portal.total_licenses') }}</th>
                    <th>{{ __('admin.status') }}</th>
                    <th>{{ __('portal.purchase_date') }}</th>
                    <th>{{ __('portal.license_duration') }}</th>
                    <th>{{ __('admin.email') }}</th>
                    <th style="text-align:right;">{{ __('admin.actions') }}</th>
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
                                    Active
                                </span>
                            @elseif($st === 'cancelled')
                                <span class="dp-badge dp-badge-error">
                                    <svg width="12" height="12" fill="currentColor" viewBox="0 0 24 24" style="margin-right:4px;"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 15v-2h2v2h-2zm0-4V7h2v6h-2z"/></svg>
                                    Cancelled
                                </span>
                            @elseif($st === 'expired')
                                <span class="dp-badge dp-badge-error">{{ __('portal.expired') }}</span>
                            @else
                                <span class="dp-badge dp-badge-inactive">
                                    <svg width="12" height="12" fill="currentColor" viewBox="0 0 24 24" style="margin-right:4px;"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" opacity=".3"/></svg>
                                    Not Started
                                </span>
                            @endif
                        </td>
                        <td class="muted">{{ $lic->purchase_date ?? ($lic->starts_at?->format('m/d/Y') ?? '—') }}</td>
                        <td class="muted">{{ $lic->license_duration ?? ($lic->expires_at?->format('m/d/Y') ?? '—') }}</td>
                        <td class="muted">{{ $lic->email ?? '—' }}</td>
                        <td style="text-align:right;">
                            <div style="display:flex;gap:6px;justify-content:flex-end;">
                                {{-- Detail --}}
                                <a href="{{ route('portal.licenses.show', $lic) }}" class="dp-action dp-action-view" title="{{ __('portal.detail') }}" style="padding:4px;">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                {{-- Edit --}}
                                <a href="{{ route('portal.licenses.edit', $lic) }}" class="dp-action dp-action-edit" title="{{ __('admin.edit') }}" style="padding:4px;">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                {{-- Delete --}}
                                <form action="{{ route('portal.licenses.destroy', $lic) }}" method="POST" style="display:inline;" onsubmit="return confirm('{{ __(\'portal.confirm_delete_license\') }}')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="dp-action" title="{{ __('admin.delete') }}" style="background:none;border:none;cursor:pointer;color:var(--color-error-red);padding:4px;">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align:center;padding:40px;color:var(--color-txt-muted);">
                            No licenses found.
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
            <span style="color:var(--color-txt-muted);cursor:default;">{{ __('portal.previous') }}</span>
        @else
            <a href="{{ $licenses->previousPageUrl() }}" style="color:var(--color-txt);text-decoration:none;">{{ __('portal.previous') }}</a>
        @endif

        <span style="color:var(--color-txt-muted);">Page{{ $licenses->currentPage() }} {{ __('portal.of') }} {{ $licenses->lastPage() }}</span>

        @if($licenses->hasMorePages())
            <a href="{{ $licenses->nextPageUrl() }}" class="dp-btn" style="font-size:12px;padding:6px 16px;">{{ __('portal.next') }}</a>
        @else
            <span style="color:var(--color-txt-muted);cursor:default;">{{ __('portal.next') }}</span>
        @endif
    </div>
    @endif
@endsection