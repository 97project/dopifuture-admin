@extends('portal.app')
@section('title', 'Dashboard')
@section('page-title', 'Administration')
@section('content')

    {{-- ═══ SCHOOL OVERVIEW HEADER ═══ --}}
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
        <div>
            <h2 style="font-size:24px;font-weight:700;color:#030719;margin:0;font-family:'Nunito',sans-serif;">
                {{ $school->name ?? 'My School' }}
            </h2>
            <p style="font-size:14px;color:var(--color-txt-muted);margin:4px 0 0;">
                {{ $school->city ?? '' }}{{ $school->country ? ', ' . $school->country : '' }}
            </p>
        </div>
        <button type="button" onclick="document.getElementById('seatRequestModal').style.display='flex'"
                style="display:inline-flex;align-items:center;gap:8px;padding:10px 24px;background:linear-gradient(135deg,#4364F7,#003AC9);color:#fff;border:none;border-radius:12px;font-size:14px;font-weight:600;cursor:pointer;font-family:'Nunito',sans-serif;box-shadow:0 4px 14px rgba(67,100,247,0.3);transition:transform 0.15s,box-shadow 0.15s;"
                onmouseover="this.style.transform='translateY(-1px)';this.style.boxShadow='0 6px 20px rgba(67,100,247,0.4)'"
                onmouseout="this.style.transform='';this.style.boxShadow='0 4px 14px rgba(67,100,247,0.3)'">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Request Additional Seats
        </button>
    </div>

    {{-- ═══ 4 QUICK STAT CARDS ═══ --}}
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px;">
        {{-- Total Students --}}
        <div style="background:#fff;border-radius:16px;padding:20px 24px;box-shadow:0 1px 3px rgba(0,0,0,0.06);border:1px solid #f0f0f5;">
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;">
                <div style="width:44px;height:44px;border-radius:12px;background:linear-gradient(135deg,#059669,#10B981);display:flex;align-items:center;justify-content:center;">
                    <svg width="22" height="22" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
                <div>
                    <div style="font-size:28px;font-weight:700;color:#030719;line-height:1;">{{ $school->students_count ?? 0 }}</div>
                    <div style="font-size:13px;color:var(--color-txt-muted);font-weight:500;">Students</div>
                </div>
            </div>
            <a href="{{ route('portal.users.index', ['role' => 'student']) }}" style="font-size:12px;color:#4364F7;text-decoration:none;font-weight:500;">View all →</a>
        </div>

        {{-- Total Teachers --}}
        <div style="background:#fff;border-radius:16px;padding:20px 24px;box-shadow:0 1px 3px rgba(0,0,0,0.06);border:1px solid #f0f0f5;">
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;">
                <div style="width:44px;height:44px;border-radius:12px;background:linear-gradient(135deg,#0284C7,#38BDF8);display:flex;align-items:center;justify-content:center;">
                    <svg width="22" height="22" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V9a2 2 0 012-2h2a2 2 0 012 2v9a2 2 0 01-2 2h-2z"/></svg>
                </div>
                <div>
                    <div style="font-size:28px;font-weight:700;color:#030719;line-height:1;">{{ $school->teachers_count ?? 0 }}</div>
                    <div style="font-size:13px;color:var(--color-txt-muted);font-weight:500;">Teachers</div>
                </div>
            </div>
            <a href="{{ route('portal.users.index', ['role' => 'teacher']) }}" style="font-size:12px;color:#4364F7;text-decoration:none;font-weight:500;">View all →</a>
        </div>

        {{-- Total Classes --}}
        <div style="background:#fff;border-radius:16px;padding:20px 24px;box-shadow:0 1px 3px rgba(0,0,0,0.06);border:1px solid #f0f0f5;">
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;">
                <div style="width:44px;height:44px;border-radius:12px;background:linear-gradient(135deg,#7C3AED,#A78BFA);display:flex;align-items:center;justify-content:center;">
                    <svg width="22" height="22" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <div>
                    <div style="font-size:28px;font-weight:700;color:#030719;line-height:1;">{{ $school->classes_count ?? 0 }}</div>
                    <div style="font-size:13px;color:var(--color-txt-muted);font-weight:500;">Classes</div>
                </div>
            </div>
            <a href="{{ route('portal.classes.index') }}" style="font-size:12px;color:#4364F7;text-decoration:none;font-weight:500;">View all →</a>
        </div>

        {{-- Available Seats --}}
        @php
            $totalSeats = $license->seat_count ?? 0;
            $usedSeats  = $license->used_seats ?? 0;
            $remaining  = max(0, $totalSeats - $usedSeats);
            $pct = $totalSeats > 0 ? round(($usedSeats / $totalSeats) * 100) : 0;
        @endphp
        <div style="background:#fff;border-radius:16px;padding:20px 24px;box-shadow:0 1px 3px rgba(0,0,0,0.06);border:1px solid #f0f0f5;">
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;">
                <div style="width:44px;height:44px;border-radius:12px;background:linear-gradient(135deg,#EA580C,#FB923C);display:flex;align-items:center;justify-content:center;">
                    <svg width="22" height="22" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <div>
                    <div style="font-size:28px;font-weight:700;color:#030719;line-height:1;">{{ $remaining }}</div>
                    <div style="font-size:13px;color:var(--color-txt-muted);font-weight:500;">Available Seats</div>
                </div>
            </div>
            <div style="font-size:12px;color:var(--color-txt-muted);">{{ $usedSeats }} / {{ $totalSeats }} used ({{ $pct }}%)</div>
        </div>
    </div>

    {{-- ═══ VEGA APP ACTIVITY CARDS ═══ --}}
    @if(isset($vegaSummary))
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:24px;">
        {{-- Role Galaxy --}}
        <div style="background:linear-gradient(135deg,#ec4899,#f472b6);border-radius:16px;padding:20px 24px;color:#fff;box-shadow:0 4px 14px rgba(236,72,153,0.25);position:relative;overflow:hidden;">
            <div style="position:absolute;right:-10px;top:-10px;font-size:64px;opacity:0.15;">🎮</div>
            <div style="font-size:15px;font-weight:700;margin-bottom:12px;">Role Galaxy</div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                <div>
                    <div style="font-size:24px;font-weight:700;">{{ $vegaSummary['role_galaxy']['sessions'] }}</div>
                    <div style="font-size:11px;opacity:0.85;">Simulations</div>
                </div>
                <div>
                    <div style="font-size:24px;font-weight:700;">{{ $vegaSummary['role_galaxy']['avg_score'] ?? '-' }}</div>
                    <div style="font-size:11px;opacity:0.85;">Avg Score</div>
                </div>
                <div>
                    <div style="font-size:18px;font-weight:600;">{{ $vegaSummary['role_galaxy']['active_students'] }}</div>
                    <div style="font-size:11px;opacity:0.85;">Active Students</div>
                </div>
                <div>
                    <div style="font-size:18px;font-weight:600;">{{ $vegaSummary['role_galaxy']['completed'] ?? 0 }}</div>
                    <div style="font-size:11px;opacity:0.85;">Completed</div>
                </div>
            </div>
            <a href="{{ route('portal.reports.app', 'role-galaxy') }}" style="display:inline-block;margin-top:12px;font-size:12px;color:#fff;text-decoration:none;font-weight:600;opacity:0.9;">View Details →</a>
        </div>

        {{-- Study Space --}}
        <div style="background:linear-gradient(135deg,#9333ea,#a855f7);border-radius:16px;padding:20px 24px;color:#fff;box-shadow:0 4px 14px rgba(147,51,234,0.25);position:relative;overflow:hidden;">
            <div style="position:absolute;right:-10px;top:-10px;font-size:64px;opacity:0.15;">🤖</div>
            <div style="font-size:15px;font-weight:700;margin-bottom:12px;">Study Space</div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                <div>
                    <div style="font-size:24px;font-weight:700;">{{ $vegaSummary['study_space']['sessions'] }}</div>
                    <div style="font-size:11px;opacity:0.85;">Discussions</div>
                </div>
                <div>
                    <div style="font-size:24px;font-weight:700;">{{ number_format($vegaSummary['study_space']['total_messages']) }}</div>
                    <div style="font-size:11px;opacity:0.85;">Messages</div>
                </div>
                <div>
                    <div style="font-size:18px;font-weight:600;">{{ $vegaSummary['study_space']['active_students'] }}</div>
                    <div style="font-size:11px;opacity:0.85;">Active Students</div>
                </div>
            </div>
            <a href="{{ route('portal.reports.app', 'study-space') }}" style="display:inline-block;margin-top:12px;font-size:12px;color:#fff;text-decoration:none;font-weight:600;opacity:0.9;">View Details →</a>
        </div>

        {{-- WAY AI Coach --}}
        <div style="background:linear-gradient(135deg,#0369a1,#38bdf8);border-radius:16px;padding:20px 24px;color:#fff;box-shadow:0 4px 14px rgba(3,105,161,0.25);position:relative;overflow:hidden;">
            <div style="position:absolute;right:-10px;top:-10px;font-size:64px;opacity:0.15;">💬</div>
            <div style="font-size:15px;font-weight:700;margin-bottom:12px;">WAY AI Coach</div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                <div>
                    <div style="font-size:24px;font-weight:700;">{{ $vegaSummary['way_ai_coach']['sessions'] }}</div>
                    <div style="font-size:11px;opacity:0.85;">Sessions</div>
                </div>
                <div>
                    <div style="font-size:24px;font-weight:700;">{{ number_format($vegaSummary['way_ai_coach']['total_messages']) }}</div>
                    <div style="font-size:11px;opacity:0.85;">Messages</div>
                </div>
                <div>
                    <div style="font-size:18px;font-weight:600;">{{ $vegaSummary['way_ai_coach']['active_students'] }}</div>
                    <div style="font-size:11px;opacity:0.85;">Active Students</div>
                </div>
            </div>
            <a href="{{ route('portal.reports.app', 'way-ai-coach') }}" style="display:inline-block;margin-top:12px;font-size:12px;color:#fff;text-decoration:none;font-weight:600;opacity:0.9;">View Details →</a>
        </div>
    </div>
    @endif

    {{-- ═══ LICENSE STATUS + RECENT STUDENTS ═══ --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px;">

        {{-- License Status Card --}}
        <div class="dp-card">
            <div class="dp-card-title" style="display:flex;justify-content:space-between;align-items:center;">
                <span>License Status</span>
                @if($license && $license->is_active)
                    <span class="dp-badge dp-badge-active" style="font-size:11px;">Active</span>
                @elseif($license)
                    <span class="dp-badge" style="background:rgba(239,68,68,0.1);color:#EF4444;font-size:11px;">Expired</span>
                @else
                    <span class="dp-badge" style="background:rgba(107,114,128,0.1);color:#6B7280;font-size:11px;">No License</span>
                @endif
            </div>

            @if($license)
                {{-- Seat usage bar --}}
                <div style="margin:16px 0;">
                    <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px;">
                        <span style="color:var(--color-txt-sec);font-weight:500;">Seat Usage</span>
                        <span style="color:var(--color-txt-muted);">{{ $usedSeats }} / {{ $totalSeats }}</span>
                    </div>
                    <div style="height:10px;background:#E5E7EB;border-radius:999px;overflow:hidden;">
                        <div style="height:100%;width:{{ $pct }}%;background:linear-gradient(90deg,#059669,#10B981);border-radius:999px;transition:width 0.5s;"></div>
                    </div>
                </div>

                @if($licenseWarning === 'critical')
                    <div style="padding:10px 14px;background:rgba(239,68,68,0.08);border-radius:8px;font-size:13px;color:#DC2626;margin-bottom:12px;">
                        ⚠️ License expires in {{ now()->diffInDays($license->expires_at) }} days!
                    </div>
                @elseif($licenseWarning === 'warning')
                    <div style="padding:10px 14px;background:rgba(245,158,11,0.08);border-radius:8px;font-size:13px;color:#D97706;margin-bottom:12px;">
                        ⏳ License expires in {{ now()->diffInDays($license->expires_at) }} days.
                    </div>
                @endif

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;font-size:13px;">
                    <div>
                        <span style="color:var(--color-txt-muted);">Start Date</span>
                        <div style="font-weight:500;color:var(--color-txt);">{{ $license->starts_at?->format('M d, Y') ?? '—' }}</div>
                    </div>
                    <div>
                        <span style="color:var(--color-txt-muted);">Expiry Date</span>
                        <div style="font-weight:500;color:var(--color-txt);">{{ $license->expires_at?->format('M d, Y') ?? '—' }}</div>
                    </div>
                    <div>
                        <span style="color:var(--color-txt-muted);">Total Seats</span>
                        <div style="font-weight:500;color:var(--color-txt);">{{ $totalSeats }}</div>
                    </div>
                    <div>
                        <span style="color:var(--color-txt-muted);">Available</span>
                        <div style="font-weight:500;color:{{ $remaining > 0 ? '#059669' : '#EF4444' }};">{{ $remaining }}</div>
                    </div>
                </div>
            @else
                <p style="color:var(--color-txt-muted);padding:20px 0;text-align:center;font-size:14px;">
                    No active license found. Contact your administrator.
                </p>
            @endif
        </div>

        {{-- Recently Added Students --}}
        <div class="dp-card">
            <div class="dp-card-title" style="display:flex;justify-content:space-between;align-items:center;">
                <span>Recently Added Students</span>
                <a href="{{ route('portal.users.index', ['role' => 'student']) }}" style="font-size:12px;color:#4364F7;text-decoration:none;font-weight:500;">View all →</a>
            </div>

            @forelse($recentStudents as $s)
            <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 0;{{ !$loop->last ? 'border-bottom:1px solid #f3f4f6;' : '' }}">
                <div style="display:flex;align-items:center;gap:10px;">
                    <div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--primary-deep));color:#fff;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:600;flex-shrink:0;">{{ strtoupper(substr($s->name ?? '', 0, 1) . substr($s->surname ?? '', 0, 1)) }}</div>
                    <div>
                        <div style="font-size:13px;font-weight:500;color:var(--color-txt);">{{ $s->name }} {{ $s->surname }}</div>
                        <div style="font-size:12px;color:var(--color-txt-muted);">{{ $s->email }}</div>
                    </div>
                </div>
                <span style="font-size:11px;color:var(--color-txt-muted);">{{ $s->created_at?->diffForHumans() }}</span>
            </div>
            @empty
            <p style="color:var(--color-txt-muted);padding:20px 0;text-align:center;font-size:14px;">
                No students yet. <a href="{{ route('portal.users.create', ['role' => 'student']) }}" style="color:#4364F7;">Add your first student →</a>
            </p>
            @endforelse
        </div>
    </div>

    {{-- ═══ SEAT REQUESTS HISTORY ═══ --}}
    @if($seatRequests->count() > 0)
    <div class="dp-card">
        <div class="dp-card-title">My Seat Requests</div>
        <table class="dp-table" style="font-size:13px;">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Seats Requested</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Admin Response</th>
                </tr>
            </thead>
            <tbody>
                @foreach($seatRequests as $req)
                <tr>
                    <td class="muted">{{ $req->created_at?->format('M d, Y') }}</td>
                    <td style="font-weight:600;">+{{ $req->requested_seats }}</td>
                    <td class="muted">{{ Str::limit($req->reason, 50) }}</td>
                    <td>
                        @if($req->status === 'pending')
                            <span class="dp-badge" style="background:rgba(245,158,11,0.1);color:#D97706;">Pending</span>
                        @elseif($req->status === 'approved')
                            <span class="dp-badge dp-badge-active">Approved</span>
                        @else
                            <span class="dp-badge" style="background:rgba(239,68,68,0.1);color:#EF4444;">Rejected</span>
                        @endif
                    </td>
                    <td class="muted">{{ $req->admin_notes ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- ═══ SEAT REQUEST MODAL ═══ --}}
    <div id="seatRequestModal" class="dp-modal-overlay" style="display:none;" onclick="if(event.target===this)this.style.display='none'">
        <div class="dp-modal-card">
            <button type="button" class="dp-modal-close" onclick="document.getElementById('seatRequestModal').style.display='none'">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>

            <div class="dp-modal-title">Request Additional Seats</div>
            <p class="dp-modal-subtitle">Submit a request to your administrator for additional student seats.</p>

            <form method="POST" action="{{ route('portal.seat-requests.store') }}">
                @csrf
                <input type="hidden" name="school_id" value="{{ $school->id }}">
                <div style="display:flex;flex-direction:column;gap:16px;margin-bottom:24px;">
                    <div>
                        <label style="display:block;font-size:13px;font-weight:500;color:var(--color-txt);margin-bottom:6px;">Number of Additional Seats *</label>
                        <input type="number" name="requested_seats" class="dp-form-input" placeholder="e.g. 50" required min="1">
                    </div>
                    <div>
                        <label style="display:block;font-size:13px;font-weight:500;color:var(--color-txt);margin-bottom:6px;">Reason / Notes</label>
                        <textarea name="reason" class="dp-form-input" rows="3" placeholder="Explain why you need additional seats..."></textarea>
                    </div>
                </div>

                <div style="display:flex;gap:12px;">
                    <button type="button" class="dp-btn-ghost" style="flex:1;justify-content:center;" onclick="document.getElementById('seatRequestModal').style.display='none'">Cancel</button>
                    <button type="submit" class="dp-btn" style="flex:1;justify-content:center;">Submit Request</button>
                </div>
            </form>
        </div>
    </div>

@endsection
