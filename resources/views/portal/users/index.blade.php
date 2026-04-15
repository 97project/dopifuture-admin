@extends('portal.app')
@section('title', __('portal.nav_students'))
@section('page-title', __('admin.dashboard'))
@php
    $currentRole = request('role', 'student');
@endphp

@section('content')

    {{-- ═══ 3 STAT CARDS — Figma 1158-14034: horizontal icon-left value-right ═══ --}}
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:20px;">
        {{-- Total Licence — Green --}}
        <div style="display:flex;align-items:center;justify-content:space-between;padding:20px 24px;border-radius:16px;background:linear-gradient(135deg,#059669,#10B981);color:#fff;">
            <div style="display:flex;flex-direction:column;gap:4px;">
                <div style="width:40px;height:40px;border-radius:50%;background:rgba(255,255,255,0.2);display:flex;align-items:center;justify-content:center;">
                    <svg width="20" height="20" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <span style="font-size:13px;font-weight:500;opacity:0.9;">{{ __('portal.total_licenses') }}</span>
            </div>
            <span style="font-size:36px;font-weight:700;font-family:'Nunito',sans-serif;">{{ $licenseStats->totalLicence ?? 0 }}</span>
        </div>
        {{-- Used Licence — Blue --}}
        <div style="display:flex;align-items:center;justify-content:space-between;padding:20px 24px;border-radius:16px;background:linear-gradient(135deg,#0284C7,#38BDF8);color:#fff;">
            <div style="display:flex;flex-direction:column;gap:4px;">
                <div style="width:40px;height:40px;border-radius:50%;background:rgba(255,255,255,0.2);display:flex;align-items:center;justify-content:center;">
                    <svg width="20" height="20" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
                <span style="font-size:13px;font-weight:500;opacity:0.9;">{{ __('portal.used_seats') }}</span>
            </div>
            <span style="font-size:36px;font-weight:700;font-family:'Nunito',sans-serif;">{{ $licenseStats->usedLicence ?? 0 }}</span>
        </div>
        {{-- Licence Duration — Orange --}}
        <div style="display:flex;align-items:center;justify-content:space-between;padding:20px 24px;border-radius:16px;background:linear-gradient(135deg,#EA580C,#FB923C);color:#fff;">
            <div style="display:flex;flex-direction:column;gap:4px;">
                <div style="width:40px;height:40px;border-radius:50%;background:rgba(255,255,255,0.2);display:flex;align-items:center;justify-content:center;">
                    <svg width="20" height="20" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <span style="font-size:13px;font-weight:500;opacity:0.9;">{{ __('portal.license_duration') }}</span>
            </div>
            <span style="font-size:28px;font-weight:700;font-family:'Nunito',sans-serif;">{{ $licenseStats->licenceDuration ?? '-' }}</span>
        </div>
    </div>

    {{-- ═══ TAB BAR ═══ --}}
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
        <div class="dp-tabs">
            <a href="{{ route('portal.users.index', ['role' => 'student']) }}"
               class="dp-tab {{ $currentRole === 'student' ? 'active' : '' }}">
                Students List
                <span class="tab-count">{{ $studentCount }}</span>
            </a>
            <a href="{{ route('portal.users.index', ['role' => 'teacher']) }}"
               class="dp-tab {{ $currentRole === 'teacher' ? 'active' : '' }}">
                Teachers List
                <span class="tab-count">{{ $teacherCount }}</span>
            </a>
        </div>

        <div style="display:flex;gap:8px;">
            <a href="{{ route('portal.users.import-form') }}" class="dp-btn-ghost">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                CSV Import
            </a>
            <button type="button" onclick="document.getElementById('addUserModal').style.display='flex'"
                    style="display:inline-flex;align-items:center;gap:8px;padding:10px 24px;background:#10B981;color:#fff;border:none;border-radius:999px;font-size:14px;font-weight:600;cursor:pointer;font-family:'Nunito',sans-serif;">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="2"/><path stroke-linecap="round" stroke-width="2" d="M12 8v8m-4-4h8"/></svg>
                {{ $currentRole === 'teacher' ? __('portal.add_new_teacher') : __('portal.add_new_student') }}
            </button>
        </div>
    </div>

    {{-- ═══ DATA TABLE ═══ --}}
    <div class="dp-card" style="padding:0;">
        <div style="overflow-x:auto;">
        <table class="dp-table">
            <thead>
                <tr>
                    <th style="width:40px;">{{ __('portal.no_num') }}</th>
                    <th>{{ $currentRole === 'student' ? __('portal.student_name') : __('portal.teacher_name') }}</th>
                    <th>{{ __('admin.email') }}</th>
                    <th>{{ $currentRole === 'student' ? 'Class & Teacher' : 'Assigned Classes' }}</th>
                    <th>{{ __('admin.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $i => $u)
                <tr>
                    <td class="muted">{{ str_pad(($users->currentPage()-1)*$users->perPage()+$i+1, 2, '0', STR_PAD_LEFT) }}</td>
                    <td>
                        <div class="dp-td-avatar">
                            <div class="av" style="width:28px;height:28px;font-size:10px;">{{ strtoupper(substr($u->name ?? '', 0, 1) . substr($u->surname ?? '', 0, 1)) }}</div>
                            <a href="{{ route('portal.users.show', $u) }}" style="font-weight:500;color:#030719;text-decoration:none;">{{ $u->name }} {{ $u->surname }}</a>
                        </div>
                    </td>
                    <td class="muted">{{ $u->email }}</td>
                    <td>
                        @if($currentRole === 'student')
                            @if($u->classes->count() > 0)
                                @foreach($u->classes as $c)
                                    <div style="font-weight:600;color:var(--color-txt);font-size:13px;">{{ $c->name }}</div>
                                    @if($c->teachers->count() > 0)
                                        <div style="font-size:12px;color:var(--color-txt-muted);display:flex;align-items:center;gap:4px;">
                                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg> 
                                            {{ $c->teachers->pluck('full_name')->join(', ') }}
                                        </div>
                                    @endif
                                @endforeach
                            @else
                                <span class="muted">—</span>
                            @endif
                        @else
                            @if($u->classes->count() > 0)
                                <div style="display:flex;flex-wrap:wrap;gap:4px;">
                                    @foreach($u->classes as $c)
                                        <span class="dp-badge" style="background:#f3f4f6;color:#374151;font-size:11px;padding:2px 6px;">{{ $c->name }}</span>
                                    @endforeach
                                </div>
                            @else
                                <span class="muted">—</span>
                            @endif
                        @endif
                    </td>
                    <td>
                        <div style="display:flex;gap:16px;align-items:center;white-space:nowrap;">
                            <a href="{{ route('portal.reports.student', $u) }}" style="background:none;border:none;cursor:pointer;color:#667eea;padding:0;display:inline-flex;align-items:center;gap:4px;font-size:13px;text-decoration:none;font-weight:500;">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                Report
                            </a>
                            <a href="{{ route('portal.users.edit', $u) }}" style="background:none;border:none;cursor:pointer;color:#A0A0A0;padding:0;display:inline-flex;align-items:center;gap:4px;font-size:13px;text-decoration:none;">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                Edit
                            </a>
                            <button type="button" onclick="openResetModal({{ $u->id }})"
                                    style="background:none;border:none;cursor:pointer;color:#003AC9;padding:0;display:inline-flex;align-items:center;gap:4px;font-size:13px;">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                Reset Password
                            </button>
                            <button type="button" onclick="openDeleteModal({{ $u->id }})"
                                    style="background:none;border:none;cursor:pointer;color:#E33131;padding:0;display:inline-flex;align-items:center;gap:4px;font-size:13px;">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                Delete
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align:center;padding:40px;color:var(--color-txt-muted);">
                        No users found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>

        {{-- Pagination --}}
        @if($users->hasPages())
        <div style="display:flex;justify-content:space-between;align-items:center;padding:16px 20px;font-size:12px;">
            @if($users->onFirstPage())
                <span style="color:var(--color-txt-muted);cursor:default;">{{ __('portal.previous') }}</span>
            @else
                <a href="{{ $users->previousPageUrl() }}" style="color:var(--color-txt);text-decoration:none;">{{ __('portal.previous') }}</a>
            @endif
            <span style="color:var(--color-txt-muted);">{{ __('portal.page') }} {{ $users->currentPage() }} {{ __('portal.of') }} {{ $users->lastPage() }}</span>
            @if($users->hasMorePages())
                <a href="{{ $users->nextPageUrl() }}" class="dp-btn" style="font-size:12px;padding:6px 16px;">{{ __('portal.next') }}</a>
            @else
                <span style="color:var(--color-txt-muted);cursor:default;">{{ __('portal.next') }}</span>
            @endif
        </div>
        @endif
    </div>

    {{-- ═══ ADD USER MODAL ═══ --}}
    <div id="addUserModal" class="dp-modal-overlay" style="display:{{ $errors->any() ? 'flex' : 'none' }};" onclick="if(event.target===this)this.style.display='none'">
        <div class="dp-modal-card">
            <button type="button" class="dp-modal-close" onclick="document.getElementById('addUserModal').style.display='none'">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>

            <div class="dp-modal-title">{{ $currentRole === 'teacher' ? __('portal.add_new_teacher') : __('portal.add_new_student') }}</div>
            <p class="dp-modal-subtitle">Fill in the details below to add a new {{ $currentRole }}.</p>

            @if($errors->any())
            <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:12px 16px;margin-bottom:16px;">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;">
                    <svg width="16" height="16" fill="none" stroke="#dc2626" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <strong style="font-size:13px;color:#dc2626;">Please fix the errors below:</strong>
                </div>
                @foreach($errors->all() as $err)
                    <div style="font-size:12px;color:#b91c1c;padding-left:24px;">• {{ $err }}</div>
                @endforeach
            </div>
            @endif

            <form method="POST" action="{{ route('portal.users.store') }}">
                @csrf
                <input type="hidden" name="role" value="{{ $currentRole }}">
                @if(auth()->user()->schools->count())
                    <input type="hidden" name="school_id" value="{{ auth()->user()->schools->first()->id }}">
                @endif

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
                    <div>
                        <label style="display:block;font-size:13px;font-weight:500;color:var(--color-txt);margin-bottom:6px;">{{ __('admin.name') }} *</label>
                        <input type="text" name="name" value="{{ old('name') }}" class="dp-form-input" placeholder="{{ __('portal.enter_first_name') }}" required style="{{ $errors->has('name') ? 'border-color:#ef4444;' : '' }}">
                        @error('name') <p style="font-size:11px;color:#ef4444;margin:4px 0 0 0;">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label style="display:block;font-size:13px;font-weight:500;color:var(--color-txt);margin-bottom:6px;">{{ __('admin.surname') }}</label>
                        <input type="text" name="surname" value="{{ old('surname') }}" class="dp-form-input" placeholder="{{ __('portal.enter_last_name') }}">
                    </div>
                </div>

                <div style="margin-bottom:16px;">
                    <label style="display:block;font-size:13px;font-weight:500;color:var(--color-txt);margin-bottom:6px;">{{ __('admin.email') }} *</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="dp-form-input" placeholder="{{ __('portal.email_placeholder') }}" required style="{{ $errors->has('email') ? 'border-color:#ef4444;' : '' }}">
                    @error('email') <p style="font-size:11px;color:#ef4444;margin:4px 0 0 0;">{{ $message }}</p> @enderror
                </div>

                <div style="margin-bottom:16px;">
                    <label style="display:block;font-size:13px;font-weight:500;color:var(--color-txt);margin-bottom:6px;">Password *</label>
                    <input type="password" name="password" class="dp-form-input" placeholder="{{ __('portal.min_6_chars') }}" required minlength="6" style="{{ $errors->has('password') ? 'border-color:#ef4444;' : '' }}">
                    @error('password') <p style="font-size:11px;color:#ef4444;margin:4px 0 0 0;">{{ $message }}</p> @enderror
                </div>

                <button type="submit" class="dp-btn" style="width:100%;justify-content:center;padding:14px;">
                    Save Information
                </button>
            </form>
        </div>
    </div>

    {{-- ═══ RESET PASSWORD CONFIRM MODAL ═══ --}}
    <div id="resetPasswordModal" class="dp-modal-overlay" style="display:none;" onclick="if(event.target===this)closeModal('resetPasswordModal')">
        <div class="dp-modal-card" style="max-width:440px;">
            <button type="button" class="dp-modal-close" onclick="closeModal('resetPasswordModal')">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
            <div style="text-align:center;margin-bottom:16px;">
                <div style="width:56px;height:56px;border-radius:50%;background:rgba(59,130,246,0.1);display:flex;align-items:center;justify-content:center;margin:0 auto 12px;">
                    <svg width="28" height="28" fill="none" stroke="#3B82F6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </div>
                <div class="dp-modal-title">{{ __('portal.reset_password') }}</div>
                <p class="dp-modal-subtitle">{{ __('portal.confirm_reset_password_msg') }}</p>
            </div>
            {{-- User info card (populated via AJAX) --}}
            <div id="resetUserInfo" style="background:rgba(59,130,246,0.04);border:1px solid rgba(59,130,246,0.15);border-radius:12px;padding:14px 16px;margin-bottom:16px;display:none;">
                <div style="display:flex;align-items:center;gap:12px;margin-bottom:8px;">
                    <img id="resetUserAvatar" src="" alt="" style="width:36px;height:36px;border-radius:50%;">
                    <div>
                        <div id="resetUserName" style="font-weight:600;font-size:14px;color:var(--color-txt);"></div>
                        <div id="resetUserEmail" style="font-size:12px;color:var(--color-txt-muted);"></div>
                    </div>
                </div>
                <div style="display:flex;gap:16px;font-size:12px;color:var(--color-txt-muted);">
                    <span>Role: <strong id="resetUserRole" style="color:var(--color-txt);"></strong></span>
                    <span>School: <strong id="resetUserSchool" style="color:var(--color-txt);"></strong></span>
                </div>
            </div>
            <div id="resetLoading" style="text-align:center;padding:20px;color:var(--color-txt-muted);font-size:13px;">Loading...</div>
            <form id="resetPasswordForm" method="POST" action="">
                @csrf
                <div style="display:flex;gap:12px;">
                    <button type="button" onclick="closeModal('resetPasswordModal')"
                            style="flex:1;padding:12px;border-radius:10px;border:1px solid rgba(0,0,0,0.1);background:#fff;color:#333;font-size:14px;font-weight:600;cursor:pointer;font-family:'Nunito',sans-serif;">
                        Cancel
                    </button>
                    <button type="submit" id="resetSubmitBtn"
                            style="flex:1;padding:12px;border-radius:10px;border:none;background:#3B82F6;color:#fff;font-size:14px;font-weight:600;cursor:pointer;font-family:'Nunito',sans-serif;">
                        Reset Password
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ═══ DELETE CONFIRM MODAL ═══ --}}
    <div id="deleteUserModal" class="dp-modal-overlay" style="display:none;" onclick="if(event.target===this)closeModal('deleteUserModal')">
        <div class="dp-modal-card" style="max-width:440px;">
            <button type="button" class="dp-modal-close" onclick="closeModal('deleteUserModal')">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
            <div style="text-align:center;margin-bottom:16px;">
                <div style="width:56px;height:56px;border-radius:50%;background:rgba(239,68,68,0.1);display:flex;align-items:center;justify-content:center;margin:0 auto 12px;">
                    <svg width="28" height="28" fill="none" stroke="#EF4444" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </div>
                <div class="dp-modal-title" style="color:#DC2626;">{{ __('portal.delete_user') }}</div>
                <p class="dp-modal-subtitle">{{ __('portal.confirm_delete_user_msg') }}</p>
            </div>
            {{-- User info card (populated via AJAX) --}}
            <div id="deleteUserInfo" style="background:rgba(239,68,68,0.04);border:1px solid rgba(239,68,68,0.15);border-radius:12px;padding:14px 16px;margin-bottom:16px;display:none;">
                <div style="display:flex;align-items:center;gap:12px;margin-bottom:8px;">
                    <img id="deleteUserAvatar" src="" alt="" style="width:36px;height:36px;border-radius:50%;">
                    <div>
                        <div id="deleteUserName" style="font-weight:600;font-size:14px;color:var(--color-txt);"></div>
                        <div id="deleteUserEmail" style="font-size:12px;color:var(--color-txt-muted);"></div>
                    </div>
                </div>
                <div style="display:flex;gap:16px;font-size:12px;color:var(--color-txt-muted);">
                    <span>Role: <strong id="deleteUserRole" style="color:var(--color-txt);"></strong></span>
                    <span>School: <strong id="deleteUserSchool" style="color:var(--color-txt);"></strong></span>
                </div>
            </div>
            <div id="deleteLoading" style="text-align:center;padding:20px;color:var(--color-txt-muted);font-size:13px;">Loading...</div>
            <form id="deleteUserForm" method="POST" action="">
                @csrf @method('DELETE')
                <div style="display:flex;gap:12px;">
                    <button type="button" onclick="closeModal('deleteUserModal')"
                            style="flex:1;padding:12px;border-radius:10px;border:1px solid rgba(0,0,0,0.1);background:#fff;color:#333;font-size:14px;font-weight:600;cursor:pointer;font-family:'Nunito',sans-serif;">
                        Cancel
                    </button>
                    <button type="submit" id="deleteSubmitBtn"
                            style="flex:1;padding:12px;border-radius:10px;border:none;background:#DC2626;color:#fff;font-size:14px;font-weight:600;cursor:pointer;font-family:'Nunito',sans-serif;">
                        Delete
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function closeModal(id) {
        document.getElementById(id).style.display = 'none';
    }

    function fetchUserAndShow(userId, mode) {
        var modalId = mode === 'reset' ? 'resetPasswordModal' : 'deleteUserModal';
        var infoId  = mode === 'reset' ? 'resetUserInfo' : 'deleteUserInfo';
        var loadId  = mode === 'reset' ? 'resetLoading' : 'deleteLoading';

        // Show modal with loading state
        document.getElementById(infoId).style.display = 'none';
        document.getElementById(loadId).style.display = 'block';
        document.getElementById(modalId).style.display = 'flex';

        // Set form action
        if (mode === 'reset') {
            document.getElementById('resetPasswordForm').action = '/users/' + userId + '/reset-password';
        } else {
            document.getElementById('deleteUserForm').action = '/users/' + userId;
        }

        // Fetch user data from backend
        fetch('/users/' + userId + '/json', {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(r) { return r.json(); })
        .then(function(u) {
            var prefix = mode === 'reset' ? 'reset' : 'delete';
            var fullName = u.name + (u.surname ? ' ' + u.surname : '');
            document.getElementById(prefix + 'UserName').textContent = fullName;
            document.getElementById(prefix + 'UserEmail').textContent = u.email;
            document.getElementById(prefix + 'UserRole').textContent = u.role;
            document.getElementById(prefix + 'UserSchool').textContent = u.school;
            // Generate initials avatar via canvas
            var canvas = document.createElement('canvas'); canvas.width = 72; canvas.height = 72;
            var ctx = canvas.getContext('2d');
            var grad = ctx.createLinearGradient(0, 0, 72, 72); grad.addColorStop(0, '#4364F7'); grad.addColorStop(1, '#1a237e');
            ctx.fillStyle = grad; ctx.beginPath(); ctx.arc(36, 36, 36, 0, Math.PI * 2); ctx.fill();
            ctx.fillStyle = '#fff'; ctx.font = 'bold 24px Nunito,sans-serif'; ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
            var initials = fullName.split(' ').map(function(w){ return w.charAt(0); }).join('').substring(0,2).toUpperCase();
            ctx.fillText(initials, 36, 36);
            document.getElementById(prefix + 'UserAvatar').src = canvas.toDataURL();
            document.getElementById(loadId).style.display = 'none';
            document.getElementById(infoId).style.display = 'block';
        })
        .catch(function() {
            document.getElementById(loadId).textContent = '{{ __("portal.failed_load_user") }}';
        });
    }

    function openResetModal(userId) {
        fetchUserAndShow(userId, 'reset');
    }

    function openDeleteModal(userId) {
        fetchUserAndShow(userId, 'delete');
    }
    </script>

@endsection