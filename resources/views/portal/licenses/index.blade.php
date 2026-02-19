@extends('portal.layout')
@section('title', app()->getLocale() === 'tr' ? 'Lisanslar' : 'Licenses')
@php $isTr = app()->getLocale() === 'tr'; @endphp

@section('content')
    <div class="page-header">
        <h1>{{ $isTr ? 'Lisanslar' : 'Licenses' }}</h1>
        <p>{{ $isTr ? 'Okullara ait lisansları görüntüleyin ve yönetin.' : 'View and manage school licenses.' }}</p>
    </div>

    <div
        style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.75rem;">
        <form style="display: flex; gap: 0.5rem;">
            <input type="text" name="search" value="{{ request('search') }}"
                placeholder="{{ $isTr ? 'Okul ara...' : 'Search school...' }}" class="form-input" style="width: 260px;">
            <button type="submit" class="btn btn-ghost">{{ $isTr ? 'Ara' : 'Search' }}</button>
        </form>
        @if(auth()->user()->hasAnyRole(['super-admin', 'admin', 'license-manager']))
            <a href="{{ route('portal.licenses.create') }}" class="btn-primary"
                style="padding: 0.6rem 1.5rem; font-size: 0.85rem;">
                + {{ $isTr ? 'Yeni Lisans' : 'New License' }}
            </a>
        @endif
    </div>

    <div class="data-table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>{{ $isTr ? 'Okul' : 'School' }}</th>
                    <th>{{ $isTr ? 'Kapasite' : 'Seats' }}</th>
                    <th>{{ $isTr ? 'Doluluk' : 'Usage' }}</th>
                    <th>{{ $isTr ? 'Başlangıç' : 'Start' }}</th>
                    <th>{{ $isTr ? 'Bitiş' : 'Expiry' }}</th>
                    <th>{{ $isTr ? 'Durum' : 'Status' }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($licenses as $lic)
                    @php
                        $pct = $lic->totalSeats() > 0 ? round(($lic->used_seats / $lic->totalSeats()) * 100) : 0;
                        $barColor = $pct >= 90 ? '#f87171' : ($pct >= 70 ? '#fbbf24' : '#4ade80');
                    @endphp
                    <tr>
                        <td style="font-weight: 500; color: white;">{{ $lic->school?->getTranslation('name') ?? '—' }}</td>
                        <td>{{ $lic->totalSeats() }}</td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <div class="progress-bar" style="flex:1; max-width: 60px;">
                                    <div class="fill" style="width: {{ $pct }}%; background: {{ $barColor }};"></div>
                                </div>
                                <span
                                    style="font-size: 0.75rem; color: var(--gray-400);">{{ $lic->used_seats }}/{{ $lic->totalSeats() }}</span>
                            </div>
                        </td>
                        <td style="font-size: 0.8rem; color: var(--gray-500);">{{ $lic->starts_at?->format('d.m.Y') ?? '—' }}
                        </td>
                        <td style="font-size: 0.8rem; color: var(--gray-500);">{{ $lic->expires_at?->format('d.m.Y') ?? '—' }}
                        </td>
                        <td>
                            @if($lic->is_active)
                                <span class="badge badge-success">{{ $isTr ? 'Aktif' : 'Active' }}</span>
                            @else
                                <span class="badge badge-danger">{{ $isTr ? 'Pasif' : 'Inactive' }}</span>
                            @endif
                        </td>
                        <td style="text-align: right; display: flex; gap: 0.25rem; justify-content: flex-end;">
                            <a href="{{ route('portal.licenses.show', $lic) }}"
                                class="btn btn-sm btn-ghost">{{ $isTr ? 'Detay' : 'Detail' }}</a>
                            @if(auth()->user()->hasAnyRole(['super-admin', 'admin', 'license-manager']))
                                <a href="{{ route('portal.licenses.edit', $lic) }}"
                                    class="btn btn-sm btn-ghost">{{ $isTr ? 'Düzenle' : 'Edit' }}</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 2.5rem; color: var(--gray-500);">
                            {{ $isTr ? 'Henüz lisans bulunamadı.' : 'No licenses found.' }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($licenses->hasPages())
        <div style="display: flex; justify-content: center; margin-top: 1rem;">{{ $licenses->links() }}</div>
    @endif
@endsection