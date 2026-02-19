@extends('portal.layout')
@section('title', ($isTr = app()->getLocale() === 'tr') ? 'Lisans Detayı' : 'License Detail')

@section('content')
    @php
        $isTr = app()->getLocale() === 'tr';
        $total = $license->totalSeats();
        $pct = $total > 0 ? round(($license->used_seats / $total) * 100) : 0;
        $remaining = $license->availableSeats();
    @endphp

    <div class="page-header" style="display: flex; align-items: center; justify-content: space-between;">
        <div>
            <h1>{{ $license->school?->getTranslation('name') ?? '—' }}</h1>
            <p>{{ $isTr ? 'Lisans Detayı' : 'License Detail' }} — {{ $license->notes ?? '' }}</p>
        </div>
        <a href="{{ route('portal.licenses.index') }}" class="btn btn-ghost">← {{ $isTr ? 'Geri' : 'Back' }}</a>
    </div>

    {{-- License Stats --}}
    <div class="stats-grid" style="margin-bottom: 2rem;">
        <div class="stat-card">
            <div class="stat-icon" style="background: rgba(34,197,94,0.15);">
                <svg width="20" height="20" fill="none" stroke="#4ade80" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
            </div>
            <div class="stat-value">{{ $license->totalSeats() }}</div>
            <div class="stat-name">{{ $isTr ? 'Toplam Kontenjan' : 'Total Seats' }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: rgba(59,130,246,0.15);">
                <svg width="20" height="20" fill="none" stroke="var(--brand-400)" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>
            <div class="stat-value">{{ $license->used_seats }}</div>
            <div class="stat-name">{{ $isTr ? 'Kullanılan Hak' : 'Used Seats' }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: rgba(251,191,36,0.15);">
                <svg width="20" height="20" fill="none" stroke="#fbbf24" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="stat-value" style="color: {{ $remaining > 0 ? '#4ade80' : '#f87171' }};">{{ $remaining }}</div>
            <div class="stat-name">{{ $isTr ? 'Kalan Hak' : 'Remaining Seats' }}</div>
        </div>
    </div>

    {{-- Utilization Bar --}}
    <div class="form-card" style="margin-bottom: 2rem;">
        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
            <span
                style="font-size: 0.85rem; color: var(--gray-400);">{{ $isTr ? 'Doluluk Oranı' : 'Utilization Rate' }}</span>
            <span
                style="font-size: 0.85rem; font-weight: 600; color: {{ $pct >= 90 ? '#f87171' : ($pct >= 70 ? '#fbbf24' : '#4ade80') }};">%{{ $pct }}</span>
        </div>
        <div class="progress-bar" style="height: 12px; border-radius: 6px;">
            <div class="fill"
                style="width: {{ $pct }}%; background: {{ $pct >= 90 ? '#f87171' : ($pct >= 70 ? '#fbbf24' : '#4ade80') }}; border-radius: 6px;">
            </div>
        </div>
        <div
            style="display: flex; justify-content: space-between; margin-top: 0.75rem; font-size: 0.8rem; color: var(--gray-500);">
            <span>{{ $isTr ? 'Başlangıç' : 'Start' }}: {{ $license->starts_at?->format('d.m.Y') ?? '—' }}</span>
            <span>{{ $isTr ? 'Bitiş' : 'Expiry' }}: {{ $license->expires_at?->format('d.m.Y') ?? '—' }}</span>
        </div>
    </div>

    {{-- Purchases History --}}
    <div class="data-table-wrap">
        <div class="data-table-header">
            <h3>
                <svg width="18" height="18" fill="none" stroke="#a78bfa" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                {{ $isTr ? 'Alım Geçmişi' : 'Purchase History' }}
            </h3>
        </div>
        @if($license->purchases->count())
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ $isTr ? 'Tarih' : 'Date' }}</th>
                        <th>{{ $isTr ? 'Kontenjan' : 'Seats' }}</th>
                        <th>{{ $isTr ? 'Tutar' : 'Amount' }}</th>
                        <th>{{ $isTr ? 'Not' : 'Notes' }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($license->purchases as $purchase)
                        <tr>
                            <td style="color: white;">{{ $purchase->purchased_at?->format('d.m.Y') }}</td>
                            <td><span class="badge badge-success">+{{ $purchase->seat_count }}</span></td>
                            <td>{{ $purchase->amount ? number_format($purchase->amount, 2) . ' ₺' : '—' }}</td>
                            <td style="font-size: 0.8rem; color: var(--gray-400);">{{ $purchase->notes ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div style="padding: 2rem; text-align: center; color: var(--gray-500);">
                {{ $isTr ? 'Henüz alım kaydı yok.' : 'No purchase history yet.' }}
            </div>
        @endif
    </div>

    {{-- Add Purchase Form (admin only) --}}
    @if(auth()->user()->hasAnyRole(['super-admin', 'admin', 'license-manager']))
        <div class="form-card" style="margin-top: 2rem;">
            <h3 style="color: white; margin-bottom: 1rem; font-size: 1rem;">
                {{ $isTr ? 'Yeni Alım Ekle' : 'Add New Purchase' }}
            </h3>
            <form action="{{ route('portal.licenses.add-purchase', $license) }}" method="POST">
                @csrf
                <div class="form-grid-2">
                    <div>
                        <label class="form-label">{{ $isTr ? 'Kontenjan Sayısı' : 'Seat Count' }} *</label>
                        <input type="number" name="seat_count" class="form-input" min="1" required>
                    </div>
                    <div>
                        <label class="form-label">{{ $isTr ? 'Tutar (₺)' : 'Amount (₺)' }}</label>
                        <input type="number" name="amount" class="form-input" step="0.01" min="0">
                    </div>
                    <div>
                        <label class="form-label">{{ $isTr ? 'Alım Tarihi' : 'Purchase Date' }} *</label>
                        <input type="date" name="purchased_at" class="form-input" value="{{ now()->format('Y-m-d') }}" required>
                    </div>
                    <div>
                        <label class="form-label">{{ $isTr ? 'Not' : 'Notes' }}</label>
                        <input type="text" name="notes" class="form-input" maxlength="500">
                    </div>
                </div>
                <div style="margin-top: 1.25rem;">
                    <button type="submit" class="btn-primary">
                        {{ $isTr ? 'Alım Ekle' : 'Add Purchase' }}
                    </button>
                </div>
            </form>
        </div>
    @endif

    @if(session('success'))
        <div class="alert-success" style="margin-top: 1.5rem;">{{ session('success') }}</div>
    @endif
@endsection