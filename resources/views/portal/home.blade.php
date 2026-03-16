@extends('portal.app')
@section('title', 'DopiFuture')
@section('page-title', 'DopiFuture')
@section('content')

    {{-- ═══ 2 STAT CARDS — Figma node-id: 1164-17862 ═══ --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px;">
        {{-- Average Login Count — Green like Figma F-59 --}}
        <div style="display:flex;align-items:center;justify-content:space-between;padding:20px 24px;border-radius:16px;background:linear-gradient(135deg,#059669,#10B981);color:#fff;">
            <div style="display:flex;flex-direction:column;gap:4px;">
                <div style="width:40px;height:40px;border-radius:50%;background:rgba(255,255,255,0.2);display:flex;align-items:center;justify-content:center;">
                    <svg width="20" height="20" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <span style="font-size:13px;font-weight:500;opacity:0.9;">Average Login Count</span>
            </div>
            <span style="font-size:36px;font-weight:700;font-family:'Nunito',sans-serif;">{{ $avgLoginCount }}</span>
        </div>
        {{-- Average Login Duration — Blue like Figma F-59 --}}
        <div style="display:flex;align-items:center;justify-content:space-between;padding:20px 24px;border-radius:16px;background:linear-gradient(135deg,#0284C7,#38BDF8);color:#fff;">
            <div style="display:flex;flex-direction:column;gap:4px;">
                <div style="width:40px;height:40px;border-radius:50%;background:rgba(255,255,255,0.2);display:flex;align-items:center;justify-content:center;">
                    <svg width="20" height="20" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <span style="font-size:13px;font-weight:500;opacity:0.9;">Average Login Duration</span>
            </div>
            <span style="font-size:36px;font-weight:700;font-family:'Nunito',sans-serif;">{{ $avgLoginDuration }}</span>
        </div>
    </div>

    {{-- ═══ STUDENT ACTIVITY TABLE ═══ --}}
    <div class="dp-card" style="padding:0;">
        <div style="display:flex;justify-content:space-between;align-items:center;padding:16px 20px;">
            <span style="font-weight:600;font-size:14px;">Student Activities</span>
            <div style="display:flex;gap:8px;align-items:center;">
                <select style="padding:8px 12px;border:1px solid var(--color-row-border);border-radius:8px;background:var(--color-input-bg);font-size:12px;color:var(--color-txt-muted);outline:none;font-family:inherit;">
                    <option>Select Grade</option>
                    @for($g = 1; $g <= 12; $g++)
                    <option value="{{ $g }}">{{ $g }}. Grade</option>
                    @endfor
                </select>
            </div>
        </div>
        <div style="overflow-x:auto;">
        <table class="dp-table">
            <thead>
                <tr>
                    <th style="width:40px;">No</th>
                    <th>Student Name</th>
                    <th>Grade</th>
                    <th>Last Login</th>
                    <th>Total Time Spent</th>
                    <th>Total Uses</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($students as $i => $s)
                <tr>
                    <td class="muted">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</td>
                    <td>
                        <div class="dp-td-avatar">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($s->name . ' ' . $s->surname) }}&size=56&background=random&rounded=true&bold=true&font-size=0.4"
                                 alt="{{ $s->name }}" style="width:28px;height:28px;border-radius:50%;object-fit:cover;flex-shrink:0;">
                            <span style="font-weight:500;color:#030719;">{{ $s->name }} {{ $s->surname }}</span>
                        </div>
                    </td>
                    <td>{{ $s->grade }}</td>
                    <td class="muted">{{ $s->last_login }}</td>
                    <td class="muted">{{ $s->total_time }}</td>
                    <td>{{ $s->total_uses }}</td>
                    <td>
                        <div style="display:flex;gap:16px;align-items:center;white-space:nowrap;">
                            <button style="background:none;border:none;cursor:pointer;color:#A0A0A0;padding:0;display:inline-flex;align-items:center;gap:4px;font-size:13px;font-family:inherit;">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                Edit
                            </button>
                            <button style="background:none;border:none;cursor:pointer;color:#003AC9;padding:0;display:inline-flex;align-items:center;gap:4px;font-size:13px;font-family:inherit;">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                Reset Password
                            </button>
                            <button style="background:none;border:none;cursor:pointer;color:#A0A0A0;padding:0;display:inline-flex;align-items:center;gap:4px;font-size:13px;font-family:inherit;">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                Delete
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>

        {{-- Pagination --}}
        <div style="display:flex;justify-content:space-between;align-items:center;padding:16px 20px;border-top:1px solid var(--color-row-border);margin-top:8px;">
            <button style="padding:8px 20px;border:1px solid var(--color-row-border);border-radius:8px;background:#fff;cursor:pointer;font-size:13px;font-weight:500;color:var(--color-txt-sec);font-family:'Nunito',sans-serif;">Previous</button>
            <span style="font-size:13px;color:var(--color-txt-muted);font-family:'Nunito',sans-serif;">Page 1 of 3</span>
            <button style="padding:8px 20px;border:1px solid var(--color-row-border);border-radius:8px;background:#fff;cursor:pointer;font-size:13px;font-weight:500;color:var(--color-txt-sec);font-family:'Nunito',sans-serif;">Next</button>
        </div>
    </div>

@endsection