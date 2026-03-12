@extends('portal.app')
@section('title', app()->getLocale() === 'tr' ? 'Genel Bakış' : 'Dashboard')

@php $isTr = app()->getLocale() === 'tr'; @endphp

@section('content')

@if(($data['mode'] ?? 'admin') === 'school')
    {{-- ═══════════════════════════════════════════════════════════════
         SCHOOL ADMIN MODE — Tek Lisans Kartı + Kredi Geçmişi
    ═══════════════════════════════════════════════════════════════ --}}

    @php
        $license = $data['license'] ?? null;
        $school  = $data['school'] ?? null;
        $totalSeats = $license ? $license->totalSeats() : 0;
        $usedSeats  = $license?->used_seats ?? 0;
        $usagePercent = $totalSeats > 0 ? round(($usedSeats / $totalSeats) * 100) : 0;
    @endphp

    {{-- ── School Info Header ── --}}
    <div style="margin-bottom:24px;">
        <h2 style="font-size:24px;font-weight:700;color:#030719;margin:0 0 4px;font-family:'Nunito',sans-serif;">
            {{ $school->name ?? '—' }}
        </h2>
        <p style="font-size:14px;color:var(--color-txt-muted);margin:0;">
            {{ $school->city ?? '' }}{{ $school->city && $school->country ? ', ' : '' }}{{ $school->country ?? '' }}
        </p>
    </div>

    {{-- ── Stat Cards Row ── --}}
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px;">
        <div class="dp-card" style="padding:20px;text-align:center;">
            <div style="font-size:28px;font-weight:800;color:#4364F7;">{{ $school->students_count ?? 0 }}</div>
            <div style="font-size:13px;color:var(--color-txt-muted);margin-top:4px;">{{ $isTr ? 'Öğrenci' : 'Students' }}</div>
        </div>
        <div class="dp-card" style="padding:20px;text-align:center;">
            <div style="font-size:28px;font-weight:800;color:#8B5CF6;">{{ $school->teachers_count ?? 0 }}</div>
            <div style="font-size:13px;color:var(--color-txt-muted);margin-top:4px;">{{ $isTr ? 'Öğretmen' : 'Teachers' }}</div>
        </div>
        <div class="dp-card" style="padding:20px;text-align:center;">
            <div style="font-size:28px;font-weight:800;color:#10B981;">{{ $school->classes_count ?? 0 }}</div>
            <div style="font-size:13px;color:var(--color-txt-muted);margin-top:4px;">{{ $isTr ? 'Sınıf' : 'Classes' }}</div>
        </div>
        <div class="dp-card" style="padding:20px;text-align:center;">
            <div style="font-size:28px;font-weight:800;color:#F59E0B;">{{ $school->users_count ?? 0 }}</div>
            <div style="font-size:13px;color:var(--color-txt-muted);margin-top:4px;">{{ $isTr ? 'Toplam Kullanıcı' : 'Total Users' }}</div>
        </div>
    </div>

    {{-- License Expiry Warning --}}
    @if(($data['licenseWarning'] ?? null) === 'critical')
    <div style="padding:14px 20px;border-radius:12px;background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.2);color:#DC2626;font-size:14px;font-weight:500;margin-bottom:16px;display:flex;align-items:center;gap:8px;">
        🔴 {{ $isTr ? 'Lisans süreniz 7 gün içinde dolacak!' : 'License expires within 7 days!' }}
        <span style="font-weight:400;margin-left:auto;">{{ $license->expires_at?->format('d.m.Y') }}</span>
    </div>
    @elseif(($data['licenseWarning'] ?? null) === 'warning')
    <div style="padding:14px 20px;border-radius:12px;background:rgba(245,158,11,0.1);border:1px solid rgba(245,158,11,0.2);color:#D97706;font-size:14px;font-weight:500;margin-bottom:16px;display:flex;align-items:center;gap:8px;">
        🟡 {{ $isTr ? 'Lisans süreniz 30 gün içinde dolacak.' : 'License expires within 30 days.' }}
        <span style="font-weight:400;margin-left:auto;">{{ $license->expires_at?->format('d.m.Y') }}</span>
    </div>
    @endif

    {{-- App Widgets --}}
    @if(($data['appWidgets'] ?? collect())->count())
    <div class="dp-card" style="margin-bottom:24px;padding:20px 24px;">
        <div style="font-size:15px;font-weight:600;color:#030719;margin-bottom:16px;">{{ $isTr ? 'Uygulama Durumu' : 'Application Status' }}</div>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:12px;">
            @foreach($data['appWidgets'] as $widget)
            <div style="padding:16px;border-radius:12px;border:1px solid var(--color-row-border);text-align:center;">
                <div style="font-size:14px;font-weight:600;color:#030719;margin-bottom:8px;">{{ $widget->name }}</div>
                <div style="font-size:22px;font-weight:800;color:{{ $widget->color ?? '#4364F7' }};">{{ $widget->synced }}<span style="font-size:13px;font-weight:400;color:var(--color-txt-muted);">/{{ $widget->total }}</span></div>
                <div style="font-size:11px;color:var(--color-txt-muted);margin-top:4px;">{{ $isTr ? 'senkron' : 'synced' }}</div>
                @if($widget->failed > 0)
                <div style="font-size:11px;color:#EF4444;margin-top:4px;">{{ $widget->failed }} {{ $isTr ? 'başarısız' : 'failed' }}</div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

    @if($license)
    {{-- ── License Info Card ── --}}
    <div class="dp-card" style="margin-bottom:24px;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;padding:24px;">
            <div>
                <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
                    <div style="width:48px;height:48px;border-radius:12px;background:linear-gradient(135deg,#4364F7,#6C63FF);display:flex;align-items:center;justify-content:center;">
                        <svg width="24" height="24" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <div>
                        <h3 style="font-size:18px;font-weight:700;color:#030719;margin:0;font-family:'Nunito',sans-serif;">
                            {{ $isTr ? 'Lisans Bilgileri' : 'License Information' }}
                        </h3>
                        <span style="font-size:12px;padding:4px 12px;border-radius:999px;font-weight:600;
                            {{ $license->is_active && !$license->isExpired()
                                ? 'background:rgba(16,185,129,0.1);color:#10B981;'
                                : 'background:rgba(239,68,68,0.1);color:#EF4444;' }}">
                            {{ $license->is_active && !$license->isExpired()
                                ? ($isTr ? '✅ Aktif' : '✅ Active')
                                : ($license->isExpired() ? ($isTr ? '⏰ Süresi Dolmuş' : '⏰ Expired') : ($isTr ? '❌ Pasif' : '❌ Inactive')) }}
                        </span>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px 32px;font-size:14px;">
                    <div>
                        <span style="color:var(--color-txt-muted);">{{ $isTr ? 'Başlangıç:' : 'Start:' }}</span>
                        <strong style="color:#030719;">{{ $license->starts_at?->format('d.m.Y') ?? '—' }}</strong>
                    </div>
                    <div>
                        <span style="color:var(--color-txt-muted);">{{ $isTr ? 'Bitiş:' : 'Expiry:' }}</span>
                        <strong style="color:#030719;">{{ $license->expires_at?->format('d.m.Y') ?? '—' }}</strong>
                    </div>
                    <div>
                        <span style="color:var(--color-txt-muted);">{{ $isTr ? 'Toplam Koltuk:' : 'Total Seats:' }}</span>
                        <strong style="color:#030719;">{{ $totalSeats }}</strong>
                    </div>
                    <div>
                        <span style="color:var(--color-txt-muted);">{{ $isTr ? 'Kullanılan:' : 'Used:' }}</span>
                        <strong style="color:#030719;">{{ $usedSeats }}</strong>
                        <span style="color:var(--color-txt-muted);font-size:12px;margin-left:4px;">
                            ({{ $isTr ? 'Kalan:' : 'Remaining:' }} <strong style="color:#10B981;">{{ $license->availableSeats() }}</strong>)
                        </span>
                    </div>
                </div>
            </div>

            {{-- Circular Usage Gauge --}}
            <div style="flex-shrink:0;text-align:center;margin-left:24px;">
                <div style="position:relative;width:100px;height:100px;">
                    <svg width="100" height="100" viewBox="0 0 100 100">
                        <circle cx="50" cy="50" r="42" fill="none" stroke="rgba(0,0,0,0.06)" stroke-width="8"/>
                        <circle cx="50" cy="50" r="42" fill="none" stroke="{{ $usagePercent >= 90 ? '#EF4444' : ($usagePercent >= 70 ? '#F59E0B' : '#4364F7') }}"
                                stroke-width="8" stroke-linecap="round"
                                stroke-dasharray="{{ $usagePercent * 2.64 }} {{ (100 - $usagePercent) * 2.64 }}"
                                transform="rotate(-90 50 50)"/>
                    </svg>
                    <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;flex-direction:column;">
                        <span style="font-size:22px;font-weight:800;color:#030719;">{{ $usagePercent }}%</span>
                        <span style="font-size:10px;color:var(--color-txt-muted);">{{ $isTr ? 'Doluluk' : 'Usage' }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Seat Usage Progress Bar --}}
        <div style="padding:0 24px 24px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;font-size:12px;">
                <span style="color:var(--color-txt-muted);">{{ $isTr ? 'Koltuk Kullanımı' : 'Seat Usage' }}</span>
                <span style="font-weight:600;color:#030719;">{{ $usedSeats }} / {{ $totalSeats }}</span>
            </div>
            <div style="height:8px;background:rgba(0,0,0,0.06);border-radius:999px;overflow:hidden;">
                <div style="height:100%;border-radius:999px;transition:width 0.6s ease;
                    width:{{ $usagePercent }}%;
                    background:{{ $usagePercent >= 90 ? 'linear-gradient(90deg,#EF4444,#DC2626)' : ($usagePercent >= 70 ? 'linear-gradient(90deg,#F59E0B,#D97706)' : 'linear-gradient(90deg,#4364F7,#6C63FF)') }};"></div>
            </div>
        </div>

        @if($license->notes)
        <div style="padding:0 24px 24px;font-size:13px;color:var(--color-txt-muted);">
            <strong>{{ $isTr ? 'Not:' : 'Note:' }}</strong> {{ $license->notes }}
        </div>
        @endif
    </div>

    {{-- ── Credit / Purchase History ── --}}
    <div class="dp-card">
        <div style="display:flex;justify-content:space-between;align-items:center;padding:20px 24px 16px;">
            <h3 style="font-size:16px;font-weight:700;color:#030719;margin:0;font-family:'Nunito',sans-serif;">
                💳 {{ $isTr ? 'Kredi Geçmişi' : 'Credit History' }}
            </h3>
            @if(auth()->user()->hasAnyRole(['super-admin', 'admin', 'license-manager']))
            <a href="{{ route('portal.licenses.add-purchase', $license) }}"
               onclick="event.preventDefault(); document.getElementById('addCreditModal').style.display='flex';"
               style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;background:#10B981;color:#fff;border:none;border-radius:999px;font-size:13px;font-weight:600;cursor:pointer;font-family:'Nunito',sans-serif;text-decoration:none;">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                {{ $isTr ? 'Kredi Ekle' : 'Add Credit' }}
            </a>
            @endif
        </div>

        <div style="overflow-x:auto;">
        <table class="dp-table">
            <thead>
                <tr>
                    <th style="width:48px;">No</th>
                    <th>{{ $isTr ? 'Koltuk Sayısı' : 'Seats' }}</th>
                    <th>{{ $isTr ? 'Tutar' : 'Amount' }}</th>
                    <th>{{ $isTr ? 'Tarih' : 'Date' }}</th>
                    <th>{{ $isTr ? 'Not' : 'Note' }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($license->purchases as $idx => $purchase)
                <tr>
                    <td style="color:var(--color-txt-muted);">{{ str_pad($idx + 1, 2, '0', STR_PAD_LEFT) }}</td>
                    <td>
                        <span style="font-weight:600;color:#10B981;">+{{ $purchase->seat_count }}</span>
                        <span style="font-size:12px;color:var(--color-txt-muted);margin-left:4px;">{{ $isTr ? 'koltuk' : 'seats' }}</span>
                    </td>
                    <td style="font-weight:500;">{{ $purchase->amount ? number_format($purchase->amount, 2) . ' ₺' : '—' }}</td>
                    <td class="muted">{{ $purchase->purchased_at?->format('d.m.Y') ?? '—' }}</td>
                    <td class="muted" style="font-size:12px;">{{ $purchase->notes ?? '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align:center;padding:32px;color:var(--color-txt-muted);">
                        {{ $isTr ? 'Henüz kredi satın alımı yok.' : 'No credit purchases yet.' }}
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>

        @if($license->purchases->count() > 0)
        <div style="padding:12px 24px;border-top:1px solid var(--color-row-border);font-size:13px;color:var(--color-txt-muted);">
            {{ $isTr ? 'Toplam' : 'Total' }}: <strong style="color:#030719;">{{ $license->purchases->sum('seat_count') }} {{ $isTr ? 'ek koltuk' : 'extra seats' }}</strong>
            @if($license->purchases->sum('amount') > 0)
                · <strong style="color:#030719;">{{ number_format($license->purchases->sum('amount'), 2) }} ₺</strong>
            @endif
        </div>
        @endif
    </div>

    {{-- Add Credit Modal --}}
    <div id="addCreditModal" style="display:none;position:fixed;inset:0;z-index:1000;align-items:center;justify-content:center;background:rgba(0,0,0,0.5);" onclick="if(event.target===this)this.style.display='none'">
        <div style="background:#fff;border-radius:16px;padding:32px;width:100%;max-width:420px;box-shadow:0 20px 60px rgba(0,0,0,0.2);">
            <h3 style="font-size:18px;font-weight:700;margin:0 0 20px;font-family:'Nunito',sans-serif;">
                {{ $isTr ? 'Kredi Ekle' : 'Add Credit' }}
            </h3>
            <form method="POST" action="{{ route('portal.licenses.add-purchase', $license) }}">
                @csrf
                <div style="margin-bottom:16px;">
                    <label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;color:#030719;">{{ $isTr ? 'Koltuk Sayısı' : 'Seat Count' }}</label>
                    <input type="number" name="seat_count" min="1" required style="width:100%;padding:10px 14px;border:1px solid #E5E7EB;border-radius:10px;font-size:14px;">
                </div>
                <div style="margin-bottom:16px;">
                    <label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;color:#030719;">{{ $isTr ? 'Tutar (₺)' : 'Amount (₺)' }}</label>
                    <input type="number" name="amount" step="0.01" min="0" style="width:100%;padding:10px 14px;border:1px solid #E5E7EB;border-radius:10px;font-size:14px;">
                </div>
                <div style="margin-bottom:16px;">
                    <label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;color:#030719;">{{ $isTr ? 'Tarih' : 'Date' }}</label>
                    <input type="date" name="purchased_at" value="{{ now()->format('Y-m-d') }}" style="width:100%;padding:10px 14px;border:1px solid #E5E7EB;border-radius:10px;font-size:14px;">
                </div>
                <div style="margin-bottom:20px;">
                    <label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;color:#030719;">{{ $isTr ? 'Not' : 'Note' }}</label>
                    <input type="text" name="notes" style="width:100%;padding:10px 14px;border:1px solid #E5E7EB;border-radius:10px;font-size:14px;">
                </div>
                <div style="display:flex;gap:12px;justify-content:flex-end;">
                    <button type="button" onclick="document.getElementById('addCreditModal').style.display='none'"
                            style="padding:10px 20px;border:1px solid #E5E7EB;border-radius:10px;background:#fff;cursor:pointer;font-size:14px;font-weight:500;">
                        {{ $isTr ? 'İptal' : 'Cancel' }}
                    </button>
                    <button type="submit" style="padding:10px 24px;background:#4364F7;color:#fff;border:none;border-radius:10px;cursor:pointer;font-size:14px;font-weight:600;">
                        {{ $isTr ? 'Kaydet' : 'Save' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    @else
    {{-- No license yet --}}
    <div class="dp-card" style="padding:48px;text-align:center;">
        <div style="font-size:48px;margin-bottom:16px;">📋</div>
        <h3 style="font-size:18px;font-weight:700;color:#030719;margin:0 0 8px;font-family:'Nunito',sans-serif;">
            {{ $isTr ? 'Henüz lisans tanımlanmamış' : 'No license assigned yet' }}
        </h3>
        <p style="font-size:14px;color:var(--color-txt-muted);margin:0;">
            {{ $isTr ? 'Yöneticinize başvurun.' : 'Please contact your administrator.' }}
        </p>
    </div>
    @endif

@else
    {{-- ═══════════════════════════════════════════════════════════════
         SUPER ADMIN MODE — Tüm Lisanslar Tablosu
    ═══════════════════════════════════════════════════════════════ --}}

    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
        <h2 style="font-size:24px;font-weight:700;color:#030719;margin:0;font-family:'Nunito',sans-serif;">
            {{ $isTr ? 'Lisans Yönetimi' : 'License Management' }}
        </h2>
        <a href="{{ route('portal.licenses.create') }}"
                style="display:inline-flex;align-items:center;gap:8px;padding:10px 24px;background:#10B981;color:#fff;border:none;border-radius:999px;font-size:14px;font-weight:600;cursor:pointer;font-family:'Nunito',sans-serif;text-decoration:none;">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            {{ $isTr ? 'Yeni Lisans Ekle' : 'Add New License' }}
        </a>
    </div>

    <div class="dp-card">
        <div style="overflow-x:auto;">
        <table class="dp-table">
            <thead>
                <tr>
                    <th style="width:48px;">No</th>
                    <th>{{ $isTr ? 'Okul Adı' : 'School Name' }}</th>
                    <th>{{ $isTr ? 'Koltuk' : 'Seats' }}</th>
                    <th>{{ $isTr ? 'Kullanılan' : 'Used' }}</th>
                    <th>{{ $isTr ? 'Durum' : 'Status' }}</th>
                    <th>{{ $isTr ? 'Başlangıç' : 'Start' }}</th>
                    <th>{{ $isTr ? 'Bitiş' : 'Expiry' }}</th>
                    <th style="text-align:right;">{{ $isTr ? 'İşlemler' : 'Actions' }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse(($data['licenses'] ?? []) as $idx => $license)
                @php $isExpired = $license->isExpired(); @endphp
                <tr class="{{ $isExpired || !$license->is_active ? 'dp-row-cancelled' : '' }}">
                    <td style="color:var(--color-txt-muted);">{{ str_pad($data['licenses']->firstItem() + $idx, 2, '0', STR_PAD_LEFT) }}</td>
                    <td style="font-weight:500;color:#030719;">{{ $license->school->name ?? '—' }}</td>
                    <td>{{ $license->totalSeats() }}</td>
                    <td>
                        <span style="font-weight:500;">{{ $license->used_seats }}</span>
                        <span style="font-size:11px;color:var(--color-txt-muted);"> / {{ $license->totalSeats() }}</span>
                    </td>
                    <td>
                        @if($license->is_active && !$isExpired)
                            <span class="dp-badge dp-badge-active">
                                <svg width="14" height="14" viewBox="0 0 14 14" fill="none" style="margin-right:4px;"><circle cx="7" cy="7" r="7" fill="#0E9F6E"/><path d="M4 7l2 2 4-4" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                {{ $isTr ? 'Aktif' : 'Active' }}
                            </span>
                        @elseif($isExpired)
                            <span class="dp-badge" style="background:rgba(245,158,11,0.1);color:#F59E0B;">⏰ {{ $isTr ? 'Süresi Dolmuş' : 'Expired' }}</span>
                        @else
                            <span class="dp-badge" style="background:rgba(239,68,68,0.1);color:#EF4444;">❌ {{ $isTr ? 'Pasif' : 'Inactive' }}</span>
                        @endif
                    </td>
                    <td class="muted">{{ $license->starts_at?->format('d.m.Y') ?? '—' }}</td>
                    <td class="muted">{{ $license->expires_at?->format('d.m.Y') ?? '—' }}</td>
                    <td style="text-align:right;">
                        <div style="display:flex;gap:8px;justify-content:flex-end;">
                            <a href="{{ route('portal.licenses.show', $license) }}" class="dp-action-icon dp-action-primary" title="{{ $isTr ? 'Detay' : 'Details' }}">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                            <a href="{{ route('portal.licenses.edit', $license) }}" class="dp-action-icon" title="{{ $isTr ? 'Düzenle' : 'Edit' }}">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align:center;padding:40px;color:var(--color-txt-muted);">
                        {{ $isTr ? 'Henüz lisans bulunamadı.' : 'No licenses found.' }}
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>

        @if(($data['licenses'] ?? collect())->hasPages())
        <div style="display:flex;justify-content:space-between;align-items:center;padding:16px 24px 0;border-top:1px solid var(--color-row-border);margin-top:8px;">
            @if($data['licenses']->onFirstPage())
                <span style="padding:8px 20px;border:1px solid var(--color-row-border);border-radius:8px;font-size:13px;font-weight:500;color:var(--color-txt-muted);opacity:0.5;">{{ $isTr ? 'Önceki' : 'Previous' }}</span>
            @else
                <a href="{{ $data['licenses']->previousPageUrl() }}" style="padding:8px 20px;border:1px solid var(--color-row-border);border-radius:8px;font-size:13px;font-weight:500;color:var(--color-txt-sec);text-decoration:none;">{{ $isTr ? 'Önceki' : 'Previous' }}</a>
            @endif
            <span style="font-size:13px;color:var(--color-txt-muted);">{{ $isTr ? 'Sayfa' : 'Page' }} {{ $data['licenses']->currentPage() }} / {{ $data['licenses']->lastPage() }}</span>
            @if($data['licenses']->hasMorePages())
                <a href="{{ $data['licenses']->nextPageUrl() }}" style="padding:8px 20px;border:1px solid var(--color-row-border);border-radius:8px;font-size:13px;font-weight:500;color:var(--color-txt-sec);text-decoration:none;">{{ $isTr ? 'Sonraki' : 'Next' }}</a>
            @else
                <span style="padding:8px 20px;border:1px solid var(--color-row-border);border-radius:8px;font-size:13px;font-weight:500;color:var(--color-txt-muted);opacity:0.5;">{{ $isTr ? 'Sonraki' : 'Next' }}</span>
            @endif
        </div>
        @endif
    </div>
@endif

@endsection