@extends('portal.app')
@section('title', 'License Detail')
@section('page-title', 'License Detail')

@section('content')
    @php
                $total = $license->totalSeats();
        $pct = $total > 0 ? round(($license->used_seats / $total) * 100) : 0;
        $remaining = $license->availableSeats();
    @endphp

    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
        <div>
            <div style="font-size:18px;font-weight:600;">{{ $license->school?->name ?? '—' }}</div>
            <p style="font-size:13px;color:var(--text-muted);margin:4px 0 0;">License Detail — {{ $license->notes ?? '' }}</p>
        </div>
        <a href="{{ route('portal.licenses.index') }}" class="dp-btn-ghost">← Back</a>
    </div>

    {{-- Stat Cards --}}
    <div class="dp-stats-grid" style="margin-bottom:20px;">
        <div class="dp-stat-card">
            <div class="s-icon"><svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg></div>
            <div class="s-value">{{ $total }}</div>
            <div class="s-label">{{ __('portal.total_seats') }}</div>
        </div>
        <div class="dp-stat-card">
            <div class="s-icon"><svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg></div>
            <div class="s-value">{{ $license->used_seats }}</div>
            <div class="s-label">{{ __('portal.used_seats') }}</div>
        </div>
        <div class="dp-stat-card">
            <div class="s-icon"><svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
            <div class="s-value" style="color:{{ $remaining > 0 ? 'var(--active-green)' : 'var(--error-red)' }};">{{ $remaining }}</div>
            <div class="s-label">{{ __('portal.remaining_seats') }}</div>
        </div>
    </div>

    {{-- Utilization Bar --}}
    <div class="dp-card">
        <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
            <span style="font-size:13px;color:var(--text-muted);">Utilization Rate</span>
            <span style="font-size:13px;font-weight:600;color:{{ $pct >= 90 ? 'var(--error-red)' : ($pct >= 70 ? '#fbbf24' : 'var(--active-green)') }};">%{{ $pct }}</span>
        </div>
        <div class="dp-progress" style="height:12px;">
            <div class="dp-progress-fill" style="width:{{ $pct }}%;{{ $pct >= 90 ? 'background:var(--error-red);' : ($pct >= 70 ? 'background:#fbbf24;' : '') }}"></div>
        </div>
        <div style="display:flex;justify-content:space-between;margin-top:12px;font-size:12px;color:var(--text-muted);">
            <span>Start: {{ $license->starts_at?->format('d.m.Y') ?? '—' }}</span>
            <span>Expiry: {{ $license->expires_at?->format('d.m.Y') ?? '—' }}</span>
        </div>
    </div>

    {{-- Purchase History --}}
    <div class="dp-card">
        <div class="dp-card-title">{{ __('portal.purchase_history') }}</div>
        @if($license->purchases->count())
        <table class="dp-table">
            <thead><tr>
                <th>{{ __('admin.date') }}</th>
                <th>{{ __('portal.total_seats') }}</th>
                <th>Amount</th>
                <th>Notes</th>
            </tr></thead>
            <tbody>
                @foreach($license->purchases as $purchase)
                <tr>
                    <td style="font-weight:500;">{{ $purchase->purchased_at?->format('d.m.Y') }}</td>
                    <td><span class="dp-badge dp-badge-active">+{{ $purchase->seat_count }}</span></td>
                    <td>{{ $purchase->amount ? number_format($purchase->amount, 2) . ' ₺' : '—' }}</td>
                    <td class="muted">{{ $purchase->notes ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div style="padding:32px;text-align:center;color:var(--text-muted);">No purchase history yet.</div>
        @endif
    </div>

    {{-- Add Purchase Form --}}
    @if(auth()->user()->hasAnyRole(['super-admin','admin','license-manager']))
    <div class="dp-card">
        <div class="dp-card-title">{{ __('portal.add_new_purchase') }}</div>
        <form action="{{ route('portal.licenses.add-purchase', $license) }}" method="POST">
            @csrf
            <div class="dp-form-grid">
                <div>
                    <label class="dp-form-label">{{ __('portal.number_of_seats') }} *</label>
                    <input type="number" name="seat_count" class="dp-form-input" min="1" required>
                </div>
                <div>
                    <label class="dp-form-label">Amount (₺)</label>
                    <input type="number" name="amount" class="dp-form-input" step="0.01" min="0">
                </div>
                <div>
                    <label class="dp-form-label">{{ __('portal.purchase_date') }} *</label>
                    <input type="date" name="purchased_at" class="dp-form-input" value="{{ now()->format('Y-m-d') }}" required>
                </div>
                <div>
                    <label class="dp-form-label">Notes</label>
                    <input type="text" name="notes" class="dp-form-input" maxlength="500">
                </div>
            </div>
            <div style="margin-top:16px;">
                <button type="submit" class="dp-btn">{{ __('portal.add_purchase') }}</button>
            </div>
        </form>
    </div>
    @endif

    @if(session('success'))
        <div class="dp-toast">✅ {{ session('success') }}</div>
    @endif
@endsection