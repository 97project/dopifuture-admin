@extends('portal.app')
@section('title', ($isTr ?? false) ? 'Proje Detay' : 'Project Detail')
@section('page-title', 'Startup — Detail')
@php $isTr = app()->getLocale() === 'tr'; @endphp

@section('content')

    {{-- ═══ BACK BUTTON + TITLE — Figma node 1260-29279 ═══ --}}
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
        <a href="{{ route('portal.reports.app', 'way-startup') }}" style="display:flex;align-items:center;gap:6px;text-decoration:none;color:var(--color-txt-muted);font-size:13px;">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <span style="font-size:18px;font-weight:600;">{{ $isTr ? 'Proje Detay' : 'Project Detail' }}</span>
    </div>

    <div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;">

        {{-- ═══ LEFT: STEPS TIMELINE ═══ --}}
        <div>
            @foreach($steps as $i => $step)
            <div style="display:flex;gap:16px;margin-bottom:24px;position:relative;">
                {{-- Step connector line --}}
                @if(!$loop->last)
                <div style="position:absolute;left:18px;top:44px;bottom:-24px;width:2px;background:{{ $step->completed ? '#22c55e' : '#e5e7eb' }};"></div>
                @endif
                {{-- Step icon --}}
                <div style="flex-shrink:0;width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:20px;z-index:1;
                    background:{{ $step->completed ? '#22c55e' : 'var(--color-input-bg)' }};color:{{ $step->completed ? 'white' : 'var(--color-txt-muted)' }};">
                    @if($step->completed) ✓ @else {{ $i + 1 }} @endif
                </div>
                {{-- Step content --}}
                <div class="dp-card" style="flex:1;margin-bottom:0;">
                    <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:8px;">
                        <div>
                            <span style="font-size:12px;color:var(--color-primary);font-weight:600;">Step {{ $i + 1 }}</span>
                            <span class="dp-badge" style="margin-left:8px;background:{{ $step->difficulty === 'Easy' ? '#DBEAFE' : '#FEF3C7' }};color:{{ $step->difficulty === 'Easy' ? '#2563EB' : '#D97706' }};font-size:10px;">{{ $step->difficulty }}</span>
                        </div>
                        <div style="font-size:12px;color:var(--color-txt-muted);">
                            <span style="font-weight:600;color:{{ $step->score >= 100 ? '#22c55e' : 'var(--color-primary)' }};">{{ $step->score }}/200</span>
                        </div>
                    </div>
                    <div style="font-weight:600;font-size:14px;margin-bottom:4px;">{{ $step->title }}</div>
                    <div style="font-size:12px;color:var(--color-txt-muted);margin-bottom:6px;">{{ $isTr ? 'Sorumlu:' : 'Responsible:' }} {{ $step->responsible }}</div>
                    <div style="font-size:12px;color:var(--color-txt-muted);margin-bottom:8px;">AI Score: {{ $step->ai_score }}</div>
                    <div style="display:flex;gap:8px;align-items:center;">
                        <span class="dp-badge {{ $step->completed ? 'dp-badge-success' : 'dp-badge-warning' }}">
                            {{ $step->completed ? ($isTr ? 'Tamamlandı' : 'Completed') : ($isTr ? 'Devam Ediyor' : 'In Progress') }}
                        </span>
                        <a href="#" style="font-size:12px;color:var(--color-primary);text-decoration:none;">{{ $isTr ? 'Soruları Göster' : 'Show Questions' }}</a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- ═══ RIGHT: PROJECT SUMMARY ═══ --}}
        <div>
            {{-- Project name --}}
            <div class="dp-card" style="margin-bottom:16px;">
                <div style="font-size:18px;font-weight:700;margin-bottom:16px;">{{ $project->name ?? 'StudyFund / Fintech' }}</div>
                {{-- Team Summary --}}
                <div style="font-weight:600;font-size:13px;margin-bottom:12px;">{{ $isTr ? 'Takım Özeti' : 'Team Summary' }}</div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:4px;font-size:12px;margin-bottom:4px;">
                    <span style="font-weight:500;">{{ $isTr ? 'Üye' : 'Member' }}</span>
                    <span style="font-weight:500;text-align:right;">{{ $isTr ? 'Sorumlu' : 'Responsible' }}</span>
                </div>
                @foreach($team as $member)
                <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid var(--color-row-border);">
                    <div class="dp-td-avatar">
                        <div class="av" style="width:28px;height:28px;font-size:10px;">{{ strtoupper(substr($member->name,0,1).substr($member->surname,0,1)) }}</div>
                        <span style="font-size:13px;">{{ $member->name }} {{ $member->surname }}</span>
                    </div>
                    <span style="font-size:12px;color:var(--color-primary);">{{ $member->steps }}</span>
                </div>
                @endforeach
            </div>

            {{-- Progress --}}
            <div class="dp-card" style="margin-bottom:16px;">
                <div style="font-size:13px;font-weight:600;color:var(--color-primary);margin-bottom:6px;">
                    {{ $project->steps_completed ?? '6' }}/{{ $project->total_steps ?? '12' }} {{ $isTr ? 'Adım Tamamlandı' : 'Step Completed' }}
                </div>
                <div style="width:100%;height:8px;border-radius:4px;background:#e2e8f0;">
                    <div style="width:{{ ($project->steps_completed ?? 6) / ($project->total_steps ?? 12) * 100 }}%;height:100%;border-radius:4px;background:#22c55e;"></div>
                </div>
            </div>

            {{-- Total Product Score --}}
            <div class="dp-card" style="margin-bottom:16px;">
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <div>
                        <div style="font-size:12px;color:var(--color-txt-muted);">{{ $isTr ? 'Toplam Ürün Puanı' : 'Total Product Score' }}</div>
                        <div style="font-size:22px;font-weight:700;">{{ $project->product_score ?? '120' }} / {{ $project->max_score ?? '2500' }}</div>
                    </div>
                    <span class="dp-badge" style="background:#FEE2E2;color:#DC2626;">{{ $isTr ? 'Sorun Var' : 'Problem in' }} Step 4</span>
                </div>
            </div>

            {{-- Submitted Files --}}
            <div class="dp-card" style="margin-bottom:16px;">
                <div style="font-weight:600;font-size:13px;margin-bottom:12px;">{{ $isTr ? 'Yüklenen Dosyalar' : 'Submitted Files' }}</div>
                <div style="text-align:center;padding:20px;color:var(--color-txt-muted);font-size:12px;">
                    <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:block;margin:0 auto 8px;opacity:0.4;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                    {{ $isTr ? 'Son adımda dosya yükleyebilirsiniz.' : "You'll be able to upload files in the final step." }}
                </div>
            </div>

            {{-- Submitted Links --}}
            <div class="dp-card">
                <div style="font-weight:600;font-size:13px;margin-bottom:12px;">{{ $isTr ? 'Paylaşılan Bağlantılar' : 'Submitted Links' }}</div>
                <div style="text-align:center;padding:20px;color:var(--color-txt-muted);font-size:12px;">
                    <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:block;margin:0 auto 8px;opacity:0.4;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                    {{ $isTr ? 'Son adımda bağlantı paylaşabilirsiniz.' : "You'll be able to add the link in the final step." }}
                </div>
            </div>
        </div>
    </div>

@endsection
