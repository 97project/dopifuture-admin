@extends('portal.app')
@section('title', 'School Hierarchy')
@section('page-title', 'School Hierarchy')

@section('content')
@php
        $user = auth()->user();
    $schools = $user->schools()->with(['classes' => fn($q) => $q->withCount(['users as student_count' => fn($r) => $r->whereHas('roles', fn($r2) => $r2->where('name', 'student'))])])->get();
    $apps = \App\Models\Application::active()->ordered()->get();
@endphp

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
    <div>
        <h2 style="font-size:20px;font-weight:700;margin:0;">🏫 School Hierarchy</h2>
        <p style="font-size:13px;color:var(--text-muted);margin:4px 0 0;">School → Class → Student tree view</p>
    </div>
</div>

@if($schools->isEmpty())
<div class="dp-card" style="text-align:center;padding:48px;">
    <div style="font-size:32px;margin-bottom:8px;">📭</div>
    <p style="color:var(--text-muted);">No associated schools found.</p>
</div>
@else
@foreach($schools as $school)
<div class="dp-card" style="margin-bottom:16px;overflow:hidden;">
    {{-- School Header --}}
    <div style="padding:16px 20px;background:linear-gradient(135deg,#0B6AB2 0%,#13398E 100%);color:#fff;cursor:pointer;" onclick="this.parentElement.querySelector('.hierarchy-body').classList.toggle('expanded')">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <div>
                <span style="font-size:16px;font-weight:700;">🏫 {{ $school->name }}</span>
                <span style="font-size:12px;opacity:0.8;margin-left:8px;">{{ $school->classes->count() }} classes</span>
            </div>
            <span style="font-size:20px;transition:transform 0.2s;">▼</span>
        </div>
    </div>

    {{-- Classes --}}
    <div class="hierarchy-body expanded">
        @foreach($school->classes as $class)
        <div style="border-bottom:1px solid var(--color-row-border,#eee);">
            <div style="padding:12px 20px 12px 32px;display:flex;align-items:center;justify-content:space-between;cursor:pointer;" onclick="this.nextElementSibling?.classList.toggle('expanded')">
                <div style="display:flex;align-items:center;gap:8px;">
                    <span style="font-size:16px;">📚</span>
                    <span style="font-weight:600;font-size:14px;">{{ $class->name }}</span>
                    <span style="font-size:11px;color:var(--text-muted);background:var(--bg-subtle,#f5f5f5);padding:2px 8px;border-radius:999px;">{{ $class->student_count }} students</span>
                </div>
                <a href="{{ route('portal.reports.class', $class->id) }}" style="font-size:11px;color:#0B6AB2;text-decoration:none;font-weight:600;">
                    Report →
                </a>
            </div>

            {{-- Students (collapsed by default) --}}
            <div class="hierarchy-students">
                @php $students = $class->users()->whereHas('roles', fn($q) => $q->where('name', 'student'))->select('users.id','users.name','users.surname','users.email')->take(20)->get(); @endphp
                @foreach($students as $s)
                <div style="padding:6px 20px 6px 52px;display:flex;align-items:center;justify-content:space-between;font-size:13px;border-top:1px solid var(--color-row-border,#f0f0f0);">
                    <div style="display:flex;align-items:center;gap:6px;">
                        <span style="width:22px;height:22px;border-radius:50%;background:#E8F0FE;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;color:#0B6AB2;">{{ strtoupper(substr($s->name ?? 'S', 0, 1)) }}</span>
                        <span style="font-weight:500;">{{ $s->name }} {{ $s->surname }}</span>
                        <span style="font-size:11px;color:var(--text-muted);">{{ $s->email }}</span>
                    </div>
                    <a href="{{ route('portal.reports.student', $s->id) }}" style="font-size:10px;color:#0B6AB2;text-decoration:none;">Detail →</a>
                </div>
                @endforeach
                @if($class->student_count > 20)
                <div style="padding:6px 52px;font-size:11px;color:var(--text-muted);">+{{ $class->student_count - 20 }} more…</div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>
@endforeach
@endif

<style>
.hierarchy-body { max-height: 0; overflow: hidden; transition: max-height 0.3s ease; }
.hierarchy-body.expanded { max-height: 9999px; }
.hierarchy-students { max-height: 0; overflow: hidden; transition: max-height 0.3s ease; }
.hierarchy-students.expanded { max-height: 9999px; }
</style>
@endsection
