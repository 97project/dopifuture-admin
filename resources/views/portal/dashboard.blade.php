@extends('portal.app')
@section('title', __('portal.license_management'))
@section('page-title', __('admin.dashboard'))
@section('content')

    {{-- ═══ HEADER ═══ --}}
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
        <h2 style="font-size:24px; font-weight:700; color:#030719; margin:0; font-family:'Nunito',sans-serif;">
            License Management
        </h2>
        <button type="button" onclick="document.getElementById('addLicenseModal').style.display='flex'"
                style="display:inline-flex; align-items:center; gap:8px; padding:10px 24px; background:#10B981; color:#fff; border:none; border-radius:999px; font-size:14px; font-weight:600; cursor:pointer; font-family:'Nunito',sans-serif;">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add New License
        </button>
    </div>

    {{-- ═══ LICENSE TABLE ═══ --}}
    <div class="dp-card">
        <div style="overflow-x:auto;">
        <table class="dp-table">
            <thead>
                <tr>
                    <th style="width:48px;">{{ __('portal.no_num') }}</th>
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
                @forelse(($data['licenses'] ?? collect()) as $idx => $license)
                @php $st = $license->is_active ? 'active' : 'not_started'; @endphp
                <tr>
                    <td style="color:var(--color-txt-muted);">{{ str_pad($idx + 1, 2, '0', STR_PAD_LEFT) }}</td>
                    <td style="font-weight:500; color:#030719;">{{ $license->school?->name ?? '—' }}</td>
                    <td class="muted">{{ $license->school?->city ?? '—' }}</td>
                    <td>{{ $license->seat_count ?? 0 }}</td>
                    <td>
                        @if($st === 'active')
                            <span class="dp-badge dp-badge-active">
                                <svg width="14" height="14" viewBox="0 0 14 14" fill="none" style="margin-right:4px;"><circle cx="7" cy="7" r="7" fill="#0E9F6E"/><path d="M4 7l2 2 4-4" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                {{ __('portal.active') }}
                            </span>
                        @else
                            <span class="dp-badge" style="background:rgba(107,114,128,0.1);color:#6B7280;">
                                Not Started
                            </span>
                        @endif
                    </td>
                    <td class="muted">{{ $license->starts_at?->format('m/d/Y') ?? '—' }}</td>
                    <td class="muted">{{ $license->expires_at?->format('m/d/Y') ?? '—' }}</td>
                    <td class="muted" style="font-size:12px;">{{ $license->school?->email ?? '—' }}</td>
                    <td style="text-align:right;">
                        <div style="display:flex;gap:6px;justify-content:flex-end;">
                            <a href="{{ route('portal.licenses.show', $license) }}" class="dp-action dp-action-view" title="Detail" style="padding:4px;">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                            <a href="{{ route('portal.licenses.edit', $license) }}" class="dp-action dp-action-edit" title="Edit" style="padding:4px;">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            <form action="{{ route('portal.licenses.destroy', $license) }}" method="POST" style="display:inline;" onsubmit="return confirm('{{ __(\'portal.confirm_delete_license\') }}')">
                                @csrf @method('DELETE')
                                <button type="submit" class="dp-action" title="Delete" style="background:none;border:none;cursor:pointer;color:var(--color-error-red);padding:4px;">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
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

        {{-- Real Pagination --}}
        @if(isset($data['licenses']) && $data['licenses'] instanceof \Illuminate\Pagination\LengthAwarePaginator && $data['licenses']->hasPages())
        <div style="display:flex;justify-content:space-between;align-items:center;padding:16px 20px;font-size:12px;border-top:1px solid var(--color-row-border);">
            @if($data['licenses']->onFirstPage())
                <span style="color:var(--color-txt-muted);cursor:default;">{{ __('portal.previous') }}</span>
            @else
                <a href="{{ $data['licenses']->previousPageUrl() }}" style="color:var(--color-txt);text-decoration:none;">{{ __('portal.previous') }}</a>
            @endif
            <span style="color:var(--color-txt-muted);">Page {{ $data['licenses']->currentPage() }} of {{ $data['licenses']->lastPage() }}</span>
            @if($data['licenses']->hasMorePages())
                <a href="{{ $data['licenses']->nextPageUrl() }}" class="dp-btn" style="font-size:12px;padding:6px 16px;">{{ __('portal.next') }}</a>
            @else
                <span style="color:var(--color-txt-muted);cursor:default;">{{ __('portal.next') }}</span>
            @endif
        </div>
        @endif
    </div>

    {{-- ═══ ADD LICENSE MODAL ═══ --}}
    <div id="addLicenseModal" class="dp-modal-overlay" style="display:none;" onclick="if(event.target===this)this.style.display='none'">
        <div class="dp-modal-card">
            <button type="button" class="dp-modal-close" onclick="document.getElementById('addLicenseModal').style.display='none'">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>

            <div class="dp-modal-title">{{ __('portal.add_new_license') }}</div>
            <p class="dp-modal-subtitle">{{ __('portal.fill_license_details') }}</p>

            <form method="POST" action="{{ route('portal.licenses.store') }}">
                @csrf
                <div style="display:flex;flex-direction:column;gap:16px;margin-bottom:24px;">
                    <div>
                        <label style="display:block;font-size:13px;font-weight:500;color:var(--color-txt);margin-bottom:6px;">{{ __('admin.school_name') }} *</label>
                        <select name="school_id" class="dp-form-input" required>
                            <option value="">{{ __('portal.select_school') }}</option>
                            @foreach(\App\Models\School::orderBy('name')->get() as $school)
                                <option value="{{ $school->id }}">{{ $school->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="display:block;font-size:13px;font-weight:500;color:var(--color-txt);margin-bottom:6px;">{{ __('portal.number_of_seats') }} *</label>
                        <input type="number" name="seat_count" class="dp-form-input" placeholder="0" required min="1">
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                        <div>
                            <label style="display:block;font-size:13px;font-weight:500;color:var(--color-txt);margin-bottom:6px;">{{ __('portal.start_date') }}</label>
                            <input type="date" name="starts_at" class="dp-form-input">
                        </div>
                        <div>
                            <label style="display:block;font-size:13px;font-weight:500;color:var(--color-txt);margin-bottom:6px;">{{ __('portal.end_date') }}</label>
                            <input type="date" name="expires_at" class="dp-form-input">
                        </div>
                    </div>
                </div>

                <div style="display:flex;gap:12px;">
                    <button type="button" class="dp-btn-ghost" style="flex:1;justify-content:center;" onclick="document.getElementById('addLicenseModal').style.display='none'">{{ __('portal.cancel') }}</button>
                    <button type="submit" class="dp-btn" style="flex:1;justify-content:center;">{{ __('admin.save') }}</button>
                </div>
            </form>
        </div>
    </div>

@endsection