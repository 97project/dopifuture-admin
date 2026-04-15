@extends('portal.app')
@section('title', __('admin.classes'))
@section('page-title', __('admin.classes'))

@section('content')
    <div class="dp-card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:12px;">
            <div>
                <div class="dp-card-title" style="margin-bottom:4px;">{{ __('admin.classes') }}</div>
                <p style="font-size:13px;color:var(--text-muted);margin:0;">View and manage school classes.</p>
            </div>
            <div style="display:flex;gap:8px;align-items:center;">
                <form style="display:flex;gap:8px;">
                    <div class="dp-search" style="width:220px;">
                        <svg width="14" height="14" fill="none" stroke="var(--text-muted)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search class...">
                    </div>
                    <button type="submit" class="dp-btn-ghost">{{ __('portal.search') }}</button>
                </form>
                @if(auth()->user()->hasAnyRole(['super-admin','admin','license-manager','school-admin','school-principal']))
                    <a href="{{ route('portal.classes.create') }}" class="dp-btn">+ New Class</a>
                @endif
            </div>
        </div>

        <div style="overflow-x:auto;">
        <table class="dp-table">
            <thead>
                <tr>
                    <th>{{ __('portal.class') }}</th>
                    <th>{{ __('admin.school_name') }}</th>
                    <th>{{ __('portal.grade') }}</th>
                    <th>{{ __('portal.year') }}</th>
                    <th>{{ __('portal.nav_students') }}</th>
                    <th>{{ __('admin.status') }}</th>
                    <th style="text-align:right;">{{ __('admin.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($classes as $cls)
                    <tr>
                        <td style="font-weight:500;">{{ $cls->name }}</td>
                        <td class="muted">{{ $cls->school?->name ?? '—' }}</td>
                        <td>{{ $cls->grade_level ?? '—' }}</td>
                        <td class="muted">{{ $cls->academic_year ?? '—' }}</td>
                        <td>
                            <span style="font-weight:600;color:var(--primary);">{{ $cls->students_count }}</span>
                        </td>
                        <td>
                            @if($cls->is_active)
                                <span class="dp-badge dp-badge-active">{{ __('portal.active') }}</span>
                            @else
                                <span class="dp-badge dp-badge-inactive">{{ __('portal.inactive') }}</span>
                            @endif
                        </td>
                        <td style="text-align:right;">
                            <div style="display:flex;gap:4px;justify-content:flex-end;">
                                <a href="{{ route('portal.classes.show', $cls) }}" class="dp-action dp-action-view" title="Detail">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                <a href="{{ route('portal.classes.edit', $cls) }}" class="dp-action dp-action-edit" title="Edit">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                @if(auth()->user()->hasAnyRole(['school-admin','school-principal']))
                                <form action="{{ route('portal.classes.destroy', $cls) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete class {{ $cls->name }}? This cannot be undone.')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="dp-action" title="Delete" style="background:none;border:none;cursor:pointer;color:var(--color-error-red);padding:4px;">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align:center;padding:40px;color:var(--text-muted);">
                            No classes found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    @if($classes->hasPages())
        <div class="dp-pagination">{{ $classes->links() }}</div>
    @endif
@endsection