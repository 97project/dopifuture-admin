@extends('portal.app')
@section('title', ($isTr = app()->getLocale() === 'tr') ? 'Araç Kütüphanesi' : 'Tools Catalog')
@section('page-title', $isTr ? 'Araç Kütüphanesi' : 'Tools Catalog')

@section('content')
@php $isTr = app()->getLocale() === 'tr'; @endphp

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
    <div>
        <div style="font-size:18px;font-weight:600;">🧰 {{ $isTr ? 'Araç Kütüphanesi' : 'Tools Catalog' }}</div>
        <p style="font-size:13px;color:var(--text-muted);margin:4px 0 0;">{{ $isTr ? 'Way Startup simülasyonlarında önerilen araçlar' : 'Recommended tools from Way Startup simulations' }}</p>
    </div>
    <a href="{{ route('portal.reports') }}" class="dp-btn-ghost">← {{ $isTr ? 'Geri' : 'Back' }}</a>
</div>

@if($grouped->count())
@foreach($grouped as $category => $catTools)
<div class="dp-card" style="margin-bottom:20px;">
    <div class="dp-card-title">{{ $category }}</div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:12px;">
        @foreach($catTools as $tool)
        <div style="padding:16px;border:1px solid var(--color-row-border,#eee);border-radius:12px;">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;">
                @if($tool->icon_url)
                <img src="{{ $tool->icon_url }}" alt="{{ $tool->name }}" style="width:32px;height:32px;border-radius:8px;object-fit:cover;">
                @else
                <div style="width:32px;height:32px;border-radius:8px;background:#4364F7;display:flex;align-items:center;justify-content:center;">
                    <svg width="16" height="16" fill="white" viewBox="0 0 24 24"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6-3.3 3.3-1.6-1.6a1 1 0 0 0-1.4 0l-5.3 5.3a1 1 0 1 0 1.4 1.4l4.6-4.6 1.6 1.6a1 1 0 0 0 1.4 0l4-4 1.6 1.6a1 1 0 0 0 1.7-.7V6a1 1 0 0 0-1-1h-4.6a1 1 0 0 0-.7 1.7l.6.6z"/></svg>
                </div>
                @endif
                <div>
                    <div style="font-size:14px;font-weight:600;color:#030719;">{{ $tool->name }}</div>
                </div>
            </div>
            @if($tool->description)
            <p style="font-size:12px;color:var(--text-muted);margin:0 0 8px;line-height:1.4;">{{ Str::limit($tool->description, 120) }}</p>
            @endif
            @if($tool->website_url)
            <a href="{{ $tool->website_url }}" target="_blank" style="font-size:11px;color:#4364F7;text-decoration:none;">🔗 {{ $isTr ? 'Web sitesi' : 'Website' }} →</a>
            @endif
        </div>
        @endforeach
    </div>
</div>
@endforeach
@else
<div class="dp-card" style="text-align:center;padding:48px;">
    <div style="font-size:48px;margin-bottom:16px;">🧰</div>
    <p style="color:var(--text-muted);">{{ $isTr ? 'Araç verisi bulunamadı.' : 'No tools data found.' }}</p>
</div>
@endif
@endsection
