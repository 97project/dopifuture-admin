@extends('portal.app')
@section('title', ($isTr ?? false) ? 'Proje Detay' : 'Project Detail')
@section('page-title', 'Startup — Detail')
@php $isTr = app()->getLocale() === 'tr'; @endphp

@section('content')

    {{-- ═══ BACK BUTTON + TITLE — Figma node 684-17330 ═══ --}}
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
        <a href="{{ route('portal.reports.app', 'way-startup') }}" style="display:flex;align-items:center;gap:6px;text-decoration:none;color:var(--color-txt-muted);font-size:13px;">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <span style="font-size:18px;font-weight:600;">{{ $isTr ? 'Proje Detay' : 'Project Detail' }}</span>
    </div>

    <div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;align-items:start;">

        {{-- ═══ LEFT: ZIGZAG STEPS ROADMAP — Figma style ═══ --}}
        <div style="position:relative;padding:20px 0;">
            @foreach($steps as $i => $step)
            @php
                $isLeft = ($i % 2 === 0);
                $stepColors = ['#4364F7','#8b5cf6','#f59e0b','#ef4444','#10b981','#6366f1','#ec4899','#14b8a6'];
                $stepColor = $stepColors[$i % count($stepColors)];
                $scoreColor = $step->score >= 100 ? '#22c55e' : ($step->score >= 50 ? '#f59e0b' : '#ef4444');
            @endphp
            <div style="display:flex;align-items:flex-start;gap:24px;margin-bottom:16px;
                {{ $isLeft ? '' : 'flex-direction:row-reverse;' }}">

                {{-- Step circle icon --}}
                <div style="flex-shrink:0;position:relative;">
                    <div style="width:80px;height:80px;border-radius:50%;background:linear-gradient(135deg, {{ $stepColor }}, {{ $stepColor }}cc);
                        display:flex;align-items:center;justify-content:center;box-shadow:0 4px 12px {{ $stepColor }}40;position:relative;">
                        @if($step->completed)
                            <svg width="32" height="32" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        @else
                            <span style="color:white;font-size:24px;font-weight:700;">{{ $i + 1 }}</span>
                        @endif
                    </div>
                    {{-- Score pill below circle --}}
                    <div style="position:absolute;bottom:-8px;left:50%;transform:translateX(-50%);
                        background:{{ $scoreColor }};color:white;padding:2px 10px;border-radius:10px;
                        font-size:11px;font-weight:700;white-space:nowrap;">
                        {{ $step->score }}/200
                    </div>
                </div>

                {{-- Step info --}}
                <div style="flex:1;padding-top:4px;{{ $isLeft ? '' : 'text-align:right;' }}">
                    <div style="margin-bottom:4px;">
                        <span style="font-size:12px;color:var(--color-primary);font-weight:700;">Step {{ $i + 1 }}</span>
                        <span style="display:inline-block;margin-{{ $isLeft ? 'left' : 'right' }}:8px;padding:2px 8px;border-radius:10px;font-size:10px;font-weight:600;
                            background:{{ $step->difficulty === 'Easy' ? '#DBEAFE' : '#FEF3C7' }};
                            color:{{ $step->difficulty === 'Easy' ? '#2563EB' : '#D97706' }};">
                            {{ $step->difficulty }}
                        </span>
                    </div>
                    <div style="font-weight:600;font-size:15px;margin-bottom:4px;">{{ $step->title }}</div>
                    <div style="display:flex;align-items:center;gap:6px;font-size:12px;color:var(--color-txt-muted);margin-bottom:6px;
                        {{ $isLeft ? '' : 'justify-content:flex-end;' }}">
                        <div style="width:20px;height:20px;border-radius:50%;background:{{ $stepColor }};color:white;display:flex;align-items:center;justify-content:center;font-size:8px;font-weight:600;">
                            {{ strtoupper(substr($step->responsible,0,1)) }}
                        </div>
                        {{ $step->responsible }}
                    </div>
                    <div style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:10px;font-size:11px;font-weight:600;
                        {{ $step->completed ? 'background:#D1FAE5;color:#059669;' : 'background:#FEF3C7;color:#D97706;' }}">
                        @if($step->completed)
                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        @else
                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3"/></svg>
                        @endif
                        {{ $step->completed ? ($isTr ? 'Tamamlandı' : 'Completed') : ($isTr ? 'Devam Ediyor' : 'In Progress') }}
                    </div>
                </div>
            </div>

            {{-- Curved dashed connector line between steps --}}
            @if(!$loop->last)
            <div style="display:flex;justify-content:center;margin:-8px 0;">
                <svg width="200" height="40" viewBox="0 0 200 40" fill="none" style="opacity:0.3;">
                    @if($isLeft)
                        <path d="M60 0 C60 20, 140 20, 140 40" stroke="var(--color-txt-muted)" stroke-width="2" stroke-dasharray="4,4"/>
                        <polygon points="136,36 140,40 144,36" fill="var(--color-txt-muted)"/>
                    @else
                        <path d="M140 0 C140 20, 60 20, 60 40" stroke="var(--color-txt-muted)" stroke-width="2" stroke-dasharray="4,4"/>
                        <polygon points="56,36 60,40 64,36" fill="var(--color-txt-muted)"/>
                    @endif
                </svg>
            </div>
            @endif
            @endforeach
        </div>

        {{-- ═══ RIGHT: PROJECT SUMMARY ═══ --}}
        <div style="position:sticky;top:80px;">
            {{-- Project name + Team --}}
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
                        <div class="av" style="width:32px;height:32px;font-size:11px;">{{ strtoupper(substr($member->name,0,1).substr($member->surname,0,1)) }}</div>
                        <span style="font-size:13px;font-weight:500;">{{ $member->name }} {{ $member->surname }}</span>
                    </div>
                    <span style="font-size:12px;color:var(--color-primary);font-weight:500;">{{ $member->steps }}</span>
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
                    <span style="display:inline-flex;align-items:center;gap:4px;background:#FEE2E2;color:#DC2626;padding:4px 12px;border-radius:20px;font-size:11px;font-weight:600;">
                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        {{ $isTr ? 'Sorun Var' : 'Problem in' }} Step 4
                    </span>
                </div>
            </div>

            {{-- Submitted Files — Figma shows real file list --}}
            <div class="dp-card" style="margin-bottom:16px;">
                <div style="font-weight:600;font-size:13px;margin-bottom:12px;">{{ $isTr ? 'Yüklenen Dosyalar' : 'Submitted Files' }}</div>
                @php
                    $mockFiles = [
                        ['step' => 1, 'name' => 'prototype_demo.mp4', 'size' => '2.4 MB'],
                        ['step' => 2, 'name' => 'prototype_demo.mp4', 'size' => '2.4 MB'],
                        ['step' => 5, 'name' => 'prototype_demo.mp4', 'size' => '2.4 MB'],
                    ];
                @endphp
                @foreach($mockFiles as $file)
                <div style="margin-bottom:12px;">
                    <div style="display:inline-block;padding:3px 10px;border-radius:6px;font-size:10px;font-weight:600;background:#DBEAFE;color:#2563EB;margin-bottom:6px;">
                        Step {{ $file['step'] }}
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 12px;background:var(--color-input-bg);border-radius:10px;">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div style="width:36px;height:36px;border-radius:8px;background:var(--color-primary);display:flex;align-items:center;justify-content:center;">
                                <svg width="16" height="16" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            </div>
                            <div>
                                <div style="font-size:13px;font-weight:500;">{{ $file['name'] }}</div>
                                <div style="font-size:11px;color:var(--color-txt-muted);">{{ $file['size'] }}</div>
                            </div>
                        </div>
                        <a href="#" style="color:var(--color-txt-muted);">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        </a>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Submitted Links — Figma shows real link list --}}
            <div class="dp-card">
                <div style="font-weight:600;font-size:13px;margin-bottom:12px;">{{ $isTr ? 'Paylaşılan Bağlantılar' : 'Submitted Links' }}</div>
                @php
                    $mockLinks = [
                        ['step' => 3, 'url' => 'https://drive.google.com/file/demo'],
                        ['step' => 4, 'url' => 'https://drive.google.com/file/demo'],
                    ];
                @endphp
                @foreach($mockLinks as $link)
                <div style="margin-bottom:12px;">
                    <div style="display:inline-block;padding:3px 10px;border-radius:6px;font-size:10px;font-weight:600;background:#FEF3C7;color:#D97706;margin-bottom:6px;">
                        Step {{ $link['step'] }}
                    </div>
                    <div style="display:flex;align-items:center;gap:10px;padding:10px 12px;background:var(--color-input-bg);border-radius:10px;">
                        <div style="width:36px;height:36px;border-radius:8px;background:#F59E0B;display:flex;align-items:center;justify-content:center;">
                            <svg width="16" height="16" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                        </div>
                        <span style="font-size:12px;color:var(--color-primary);word-break:break-all;">{{ $link['url'] }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

@endsection
