@extends('portal.app')
@section('title', 'License Management')
@section('content')

    {{-- ═══ HEADER — Figma F-51: Back arrow + "Lisans Yönetimi" + "⊕ Add New License" ═══ --}}
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
        <h2 style="font-size:24px; font-weight:700; color:#030719; margin:0; font-family:'Nunito',sans-serif;">
            License Management
        </h2>
        <button onclick="document.getElementById('addLicenseModal').classList.add('show')"
                style="display:inline-flex; align-items:center; gap:8px; padding:10px 24px; background:#10B981; color:#fff; border:none; border-radius:999px; font-size:14px; font-weight:600; cursor:pointer; transition:background 0.2s; font-family:'Nunito',sans-serif;">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add New License
        </button>
    </div>

    {{-- ═══ LICENSE TABLE — Figma F-51: 1117-25324 ═══ --}}
    <div class="dp-card">
        <div style="overflow-x:auto;">
        <table class="dp-table">
            <thead>
                <tr>
                    <th style="width:48px;">No</th>
                    <th>School Name</th>
                    <th>Country/State</th>
                    <th>Total Licenses</th>
                    <th>Status</th>
                    <th>Purchase Date</th>
                    <th>License Duration</th>
                    <th>E-mail</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse(($data['licenses'] ?? []) as $idx => $license)
                @php $st = $license['status'] ?? 'active'; @endphp
                <tr class="{{ in_array($st, ['cancelled','expired']) ? 'dp-row-cancelled' : '' }}">
                    <td style="color:var(--color-txt-muted); font-weight:400;">{{ str_pad($idx + 1, 2, '0', STR_PAD_LEFT) }}</td>
                    <td style="font-weight:500; color:#030719;">{{ $license['school'] ?? '' }}</td>
                    <td class="muted">{{ $license['location'] ?? '—' }}</td>
                    <td>{{ $license['total'] ?? 0 }}</td>
                    <td>
                        @if($st === 'active')
                            <span class="dp-badge dp-badge-active">
                                <svg width="14" height="14" viewBox="0 0 14 14" fill="none" style="margin-right:4px;"><circle cx="7" cy="7" r="7" fill="#0E9F6E"/><path d="M4 7l2 2 4-4" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                Active
                            </span>
                        @elseif($st === 'not_started')
                            <span class="dp-badge" style="background:rgba(107,114,128,0.1);color:#6B7280;">
                                <svg width="14" height="14" fill="none" viewBox="0 0 14 14" style="margin-right:4px;"><circle cx="7" cy="7" r="6" stroke="#6B7280" stroke-width="1.5"/><path d="M7 4v3l2 2" stroke="#6B7280" stroke-width="1.5" stroke-linecap="round"/></svg>
                                Not Started
                            </span>
                        @elseif($st === 'cancelled')
                            <span class="dp-badge" style="background:rgba(239,68,68,0.1);color:#EF4444;">
                                <svg width="14" height="14" viewBox="0 0 14 14" fill="none" style="margin-right:4px;"><circle cx="7" cy="7" r="7" fill="#EF4444"/><path d="M5 5l4 4M9 5l-4 4" stroke="white" stroke-width="1.5" stroke-linecap="round"/></svg>
                                Cancelled
                            </span>
                        @elseif($st === 'expired')
                            <span class="dp-badge" style="background:rgba(245,158,11,0.1);color:#F59E0B;">
                                <svg width="14" height="14" fill="none" viewBox="0 0 14 14" style="margin-right:4px;"><circle cx="7" cy="7" r="6" stroke="#F59E0B" stroke-width="1.5"/><path d="M7 4v4" stroke="#F59E0B" stroke-width="1.5" stroke-linecap="round"/><circle cx="7" cy="10" r="0.75" fill="#F59E0B"/></svg>
                                Expired
                            </span>
                        @endif
                    </td>
                    <td class="muted">{{ $license['purchase_date'] ?? '—' }}</td>
                    <td class="muted">{{ $license['duration'] ?? '—' }}</td>
                    <td class="muted" style="font-size:12px;">{{ $license['email'] ?? '—' }}</td>
                    <td style="text-align:right;">
                        <div style="display:flex;gap:8px;justify-content:flex-end;">
                            <button class="dp-action-icon" title="Edit">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>
                            <button class="dp-action-icon dp-action-primary" title="Details">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </button>
                            <button class="dp-action-icon dp-action-delete" title="Delete">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
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

        {{-- Pagination — Figma F-51: "Previous / Page1of 12 / Next" ═══ --}}
        <div style="display:flex; justify-content:space-between; align-items:center; padding:16px 0 0; border-top:1px solid var(--color-row-border); margin-top:8px;">
            <button style="padding:8px 20px; border:1px solid var(--color-row-border); border-radius:8px; background:#fff; cursor:pointer; font-size:13px; font-weight:500; color:var(--color-txt-sec); font-family:'Nunito',sans-serif;">
                Previous
            </button>
            <span style="font-size:13px; color:var(--color-txt-muted); font-family:'Nunito',sans-serif;">
                Page1of 12
            </span>
            <button style="padding:8px 20px; border:1px solid var(--color-row-border); border-radius:8px; background:#fff; cursor:pointer; font-size:13px; font-weight:500; color:var(--color-txt-sec); font-family:'Nunito',sans-serif;">
                Next
            </button>
        </div>
    </div>

    {{-- ═══ ADD LICENSE MODAL — Figma F-52: 1151-12665 ═══ --}}
    <div class="dp-modal-overlay" id="addLicenseModal">
        <div class="dp-modal" style="max-width:500px;">
            <div class="dp-modal-header">
                <h3>Add New License</h3>
                <button class="dp-modal-close" onclick="document.getElementById('addLicenseModal').classList.remove('show')">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="dp-modal-body" style="display:flex;flex-direction:column;gap:16px;">
                <div>
                    <label class="dp-label">School Name</label>
                    <input class="dp-input" type="text" placeholder="Enter school name">
                </div>
                <div>
                    <label class="dp-label">Country/State</label>
                    <input class="dp-input" type="text" placeholder="Enter country/state">
                </div>
                <div>
                    <label class="dp-label">Number of Licenses</label>
                    <input class="dp-input" type="number" placeholder="0">
                </div>
                <div>
                    <label class="dp-label">License Duration</label>
                    <input class="dp-input" type="text" placeholder="e.g. 12/31/2026">
                </div>
                <div>
                    <label class="dp-label">E-mail</label>
                    <input class="dp-input" type="email" placeholder="Enter email">
                </div>
            </div>
            <div class="dp-modal-footer">
                <button type="button" class="dp-btn dp-btn-secondary" onclick="document.getElementById('addLicenseModal').classList.remove('show')">
                    Cancel
                </button>
                <button type="button" class="dp-btn dp-btn-primary">
                    Save
                </button>
            </div>
        </div>
    </div>

@endsection