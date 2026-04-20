@extends('portal.app')
@section('title', __('admin.dashboard'))
@section('content')
    <div style="margin-bottom:24px;">
        <h2 style="font-size:24px;font-weight:700;color:#030719;margin:0 0 4px;font-family:'Nunito',sans-serif;">
            {{ __('portal.welcome_user', ['name' => $user->name]) }}
        </h2>
        <p style="font-size:14px;color:var(--color-txt-muted);margin:0;">
            {{ __('portal.teacher_subtitle') }}
        </p>
    </div>

    {{-- Stat Cards --}}
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:24px;">
        <div class="dp-card" style="padding:20px;text-align:center;">
            <div style="font-size:28px;font-weight:800;color:#4364F7;">{{ $classes->count() }}</div>
            <div style="font-size:13px;color:var(--color-txt-muted);margin-top:4px;">{{ __('admin.classes') }}</div>
        </div>
        <div class="dp-card" style="padding:20px;text-align:center;">
            <div style="font-size:28px;font-weight:800;color:#8B5CF6;">{{ $classes->sum('students_count') }}</div>
            <div style="font-size:13px;color:var(--color-txt-muted);margin-top:4px;">{{ __('portal.total_students') }}</div>
        </div>
        <div class="dp-card" style="padding:20px;text-align:center;">
            <div style="font-size:28px;font-weight:800;color:#10B981;">{{ $recentStudents->count() }}</div>
            <div style="font-size:13px;color:var(--color-txt-muted);margin-top:4px;">{{ __('portal.recent') }}</div>
        </div>
    </div>

    {{-- Classes --}}
    <div class="dp-card" style="margin-bottom:24px;">
        <div class="dp-card-title" style="padding:20px 24px 12px;">{{ __('portal.my_classes') }}</div>
        @if($classes->count())
        <table class="dp-table">
            <thead><tr>
                <th>{{ __('portal.class_name') }}</th>
                <th>{{ __('admin.school_name') }}</th>
                <th>{{ __('portal.nav_students') }}</th>
                <th style="text-align:right;"></th>
            </tr></thead>
            <tbody>
                @foreach($classes as $class)
                <tr>
                    <td style="font-weight:500;">{{ $class->name }}</td>
                    <td class="muted">{{ $class->school?->name }}</td>
                    <td><span class="dp-badge dp-badge-active">{{ $class->students_count }}</span></td>
                    <td style="text-align:right;">
                        <div style="display:flex;gap:8px;justify-content:flex-end;">
                            <a href="{{ route('portal.classes.show', $class) }}" class="dp-action dp-action-view">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                            <a href="{{ route('portal.reports.class', $class) }}" class="dp-action" style="color:#4364F7;">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                            </a>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div style="padding:32px;text-align:center;color:var(--text-muted);">{{ __('portal.no_classes_assigned') }}</div>
        @endif
    </div>

    {{-- Recent Students --}}
    <div class="dp-card">
        <div class="dp-card-title" style="padding:20px 24px 12px;">{{ __('portal.recently_added_students') }}</div>
        @if($recentStudents->count())
        <table class="dp-table">
            <thead><tr>
                <th>{{ __('admin.name') }}</th>
                <th>{{ __('admin.email') }}</th>
                <th style="text-align:right;"></th>
            </tr></thead>
            <tbody>
                @foreach($recentStudents as $student)
                <tr>
                    <td>
                        <div class="dp-td-avatar">
                            <div class="av">{{ strtoupper(substr($student->name,0,1).substr($student->surname??'',0,1)) }}</div>
                            <span style="font-weight:500;">{{ $student->name }} {{ $student->surname }}</span>
                        </div>
                    </td>
                    <td class="muted">{{ $student->email }}</td>
                    <td style="text-align:right;">
                        <a href="{{ route('portal.reports.student', $student) }}" class="dp-action" style="color:#4364F7;" title="{{ __('portal.report') }}">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div style="padding:32px;text-align:center;color:var(--text-muted);">{{ __('portal.no_students_yet') }}</div>
        @endif
    </div>
@endsection
