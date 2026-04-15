@extends('portal.app')
@section('title', 'Application Status')
@section('page-title', 'Application Status')

@section('content')
<div style="margin-bottom:24px;">
    <h2 style="font-size:24px;font-weight:700;color:#030719;margin:0 0 4px;font-family:'Nunito',sans-serif;">
        Application Status
    </h2>
    <p style="font-size:14px;color:var(--color-txt-muted);margin:0;">
        Your school application sync and connectivity status
    </p>
</div>

@if($apps->count())
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:20px;">
    @foreach($apps as $app)
    <div class="dp-card" style="padding:24px;">
        {{-- Header --}}
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
            <div style="width:44px;height:44px;border-radius:12px;background:{{ $app->color ?? '#4364F7' }};display:flex;align-items:center;justify-content:center;">
                @if($app->icon)
                <i class="{{ $app->icon }}" style="color:#fff;font-size:18px;"></i>
                @else
                <svg width="22" height="22" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                @endif
            </div>
            <div style="flex:1;">
                <div style="font-size:16px;font-weight:600;color:#030719;">{{ $app->name }}</div>
                <div style="font-size:12px;color:var(--color-txt-muted);">{{ $app->connector_type }}</div>
            </div>
            {{-- Health Badge --}}
            @if($app->health === 'healthy')
            <span style="padding:4px 10px;border-radius:999px;font-size:11px;font-weight:600;background:rgba(16,185,129,0.1);color:#10B981;">✅ Healthy</span>
            @elseif($app->health === 'down')
            <span style="padding:4px 10px;border-radius:999px;font-size:11px;font-weight:600;background:rgba(239,68,68,0.1);color:#EF4444;">❌ Down</span>
            @elseif($app->health === 'error')
            <span style="padding:4px 10px;border-radius:999px;font-size:11px;font-weight:600;background:rgba(245,158,11,0.1);color:#F59E0B;">⚠️ Error</span>
            @endif
        </div>

        {{-- Stats Grid --}}
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:8px;text-align:center;margin-bottom:16px;">
            <div>
                <div style="font-size:20px;font-weight:800;color:#030719;">{{ $app->total_users }}</div>
                <div style="font-size:10px;color:var(--color-txt-muted);">Total</div>
            </div>
            <div>
                <div style="font-size:20px;font-weight:800;color:#10B981;">{{ $app->synced }}</div>
                <div style="font-size:10px;color:var(--color-txt-muted);">Synced</div>
            </div>
            <div>
                <div style="font-size:20px;font-weight:800;color:#F59E0B;">{{ $app->pending }}</div>
                <div style="font-size:10px;color:var(--color-txt-muted);">{{ __('portal.pending') }}</div>
            </div>
            <div>
                <div style="font-size:20px;font-weight:800;color:#EF4444;">{{ $app->failed }}</div>
                <div style="font-size:10px;color:var(--color-txt-muted);">Failed</div>
            </div>
        </div>

        {{-- Sync Progress Bar --}}
        <div>
            <div style="display:flex;justify-content:space-between;font-size:11px;color:var(--color-txt-muted);margin-bottom:4px;">
                <span>{{ __('portal.sync_progress') }}</span>
                <span style="font-weight:600;color:#030719;">{{ $app->sync_percent }}%</span>
            </div>
            <div style="height:6px;background:rgba(0,0,0,0.06);border-radius:999px;overflow:hidden;">
                <div style="height:100%;border-radius:999px;background:{{ $app->sync_percent >= 90 ? '#10B981' : ($app->sync_percent >= 50 ? '#F59E0B' : '#EF4444') }};width:{{ $app->sync_percent }}%;transition:width 0.6s;"></div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@else
<div class="dp-card" style="text-align:center;padding:48px;">
    <div style="font-size:48px;margin-bottom:16px;">📱</div>
    <h3 style="font-size:18px;font-weight:700;margin:0 0 8px;">No applications found</h3>
    <p style="color:var(--color-txt-muted);">No users from your school are assigned to any application yet.</p>
</div>
@endif
@endsection
