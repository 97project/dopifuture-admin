@extends('portal.app')
@section('title', __('admin.dashboard'))
@section('content')
    <div style="margin-bottom:24px;">
        <h2 style="font-size:24px;font-weight:700;color:#030719;margin:0 0 4px;font-family:'Nunito',sans-serif;">
            {{ __('portal.hello_user', ['name' => $user->name]) }} 👋
        </h2>
        <p style="font-size:14px;color:var(--color-txt-muted);margin:0;">
            {{ __('portal.student_subtitle') }}
        </p>
    </div>

    {{-- Class Info --}}
    @if($user->classes->count())
    <div class="dp-card" style="margin-bottom:24px;padding:20px 24px;">
        <div style="font-size:15px;font-weight:600;color:#030719;margin-bottom:12px;">{{ __('portal.my_classes') }}</div>
        <div style="display:flex;gap:12px;flex-wrap:wrap;">
            @foreach($user->classes as $cls)
            <div style="padding:10px 16px;background:rgba(67,100,247,0.08);border-radius:10px;font-size:13px;font-weight:500;color:#4364F7;">
                {{ $cls->name }}
                <span style="color:var(--color-txt-muted);font-weight:400;">— {{ $cls->school?->name }}</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- App Cards --}}
    <div style="font-size:16px;font-weight:600;color:#030719;margin-bottom:16px;">{{ __('portal.my_apps') }}</div>
    @if($appStats->count())
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:16px;margin-bottom:24px;">
        @foreach($appStats as $app)
        <div class="dp-card" style="padding:24px;text-align:center;">
            <div style="width:48px;height:48px;border-radius:12px;background:{{ $app->color ?? '#4364F7' }};display:flex;align-items:center;justify-content:center;margin:0 auto 12px;">
                @if($app->icon)
                <i class="{{ $app->icon }}" style="color:#fff;font-size:20px;"></i>
                @else
                <svg width="24" height="24" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                @endif
            </div>
            <div style="font-size:15px;font-weight:600;color:#030719;">{{ $app->name }}</div>
            <div style="margin-top:8px;">
                @if($app->sync_status === 'synced')
                <span class="dp-badge dp-badge-active">✅ {{ __('portal.active') }}</span>
                @elseif($app->sync_status === 'failed')
                <span class="dp-badge" style="background:rgba(239,68,68,0.1);color:#EF4444;">❌ {{ __('portal.failed') }}</span>
                @else
                <span class="dp-badge" style="background:rgba(245,158,11,0.1);color:#F59E0B;">⏳ {{ __('portal.pending') }}</span>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="dp-card" style="padding:48px;text-align:center;">
        <div style="font-size:48px;margin-bottom:16px;">📱</div>
        <p style="color:var(--color-txt-muted);">{{ __('portal.no_apps_assigned') }}</p>
    </div>
    @endif

    {{-- Quick Links --}}
    <div class="dp-card" style="padding:20px 24px;">
        <div style="display:flex;gap:16px;flex-wrap:wrap;">
            <a href="{{ route('portal.reports.student', $user) }}" class="dp-btn" style="text-decoration:none;">
                📊 {{ __('portal.view_my_report') }}
            </a>
            <a href="{{ route('portal.profile') }}" class="dp-btn-ghost" style="text-decoration:none;">
                👤 {{ __('portal.my_profile') }}
            </a>
        </div>
    </div>
@endsection
