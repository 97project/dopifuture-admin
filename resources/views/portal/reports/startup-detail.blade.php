@extends('portal.app')
@section('title', ($isTr ?? false) ? 'Proje Detay' : 'Project Detail')
@section('page-title', 'Startup — Detail')
@section('content')

    {{-- ═══ STEP ICONS — Figma 684-17330 exported assets ═══ --}}
    @php
        $stepIcons = [
            'step1_team_formation.png',
            'step2_idea_generation.png',
            'step3_user_research.png',
            'step4_benchmark.png',
            'step5_ideation.png',
            'step6_scope_definition.png',
            'step7_prototype_design.png',
            'step8_prototype_validation.png',
        ];

        // Step status glow colors (matches Figma: blue=normal, green=complete, red=problem, orange=inprogress)
        $stepGlows = ['#4364F7','#4364F7','#4364F7','#EF4444','#4364F7','#4364F7','#F59E0B','#9CA3AF'];
    @endphp

    {{-- ═══ BACK BUTTON + TITLE — Figma node 684-17330 ═══ --}}
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
        <a href="{{ route('portal.reports.app', 'way-startup') }}" style="display:flex;align-items:center;gap:6px;text-decoration:none;color:var(--color-txt-muted);font-size:13px;">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <span style="font-size:18px;font-weight:600;">Project Detail</span>
    </div>

    <div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;align-items:start;">

        {{-- ═══ LEFT: ZIGZAG STEPS ROADMAP — Figma 671px container ═══ --}}
        <div style="position:relative;padding:10px 0;width:671px; margin:0 auto;">
            @foreach($steps as $i => $step)
            @php
                $isLeft = ($i % 2 === 0);
                $glowColor = $stepGlows[$i % count($stepGlows)] ?? '#4364F7';
                $scoreColor = $step->score >= 100 ? '#22c55e' : ($step->score >= 50 ? '#f59e0b' : '#ef4444');
                $iconFile = $stepIcons[$i] ?? $stepIcons[0];
                $diffBg = match($step->difficulty) { 'Easy' => '#f1f7ed', 'Medium' => '#FEF3C7', default => '#FEE2E2' };
                $diffBorder = match($step->difficulty) { 'Easy' => '#2ebc15', 'Medium' => '#D97706', default => '#DC2626' };
                $diffColor = match($step->difficulty) { 'Easy' => '#2ebc15', 'Medium' => '#D97706', default => '#DC2626' };
            @endphp

            {{-- Step card — Figma: 256px wide in 671px container = 38% --}}
            <div style="display:flex;align-items:center;gap:16px;position:relative;z-index:1;
                width:38%;{{ $isLeft ? '' : 'margin-left:62%;' }}">

                {{-- Icon: 121x93 --}}
                <div style="flex-shrink:0;position:relative;width:121px;height:93px;">
                    <img src="/assets/dopifuture/step-icons/{{ $iconFile }}" alt="Step {{ $i+1 }}"
                         style="width:121px;height:93px;object-fit:contain;display:block;">
                    @if($step->completed)
                    <div style="position:absolute;top:0;left:0;width:34px;height:34px;border-radius:50%;
                        background:#22c55e;display:flex;align-items:center;justify-content:center;
                        box-shadow:0 2px 4px rgba(34,197,94,0.4);border:2px solid white;z-index:2;">
                        <svg width="12" height="12" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    @endif
                    <div style="position:absolute;top:64px;left:30px;width:66px;
                        background:{{ $scoreColor }};padding:4px 8px;border-radius:999px;
                        font-size:12px;font-weight:700;font-family:'Nunito',sans-serif;white-space:nowrap;
                        color:#312804;text-align:center;
                        box-shadow:0 1.38px 0 0 {{ $scoreColor === '#22c55e' ? '#16a34a' : '#c59f0e' }};
                        border:0.69px solid {{ $scoreColor === '#22c55e' ? '#16a34a' : '#c59f0e' }};
                        z-index:1;">
                        {{ $step->score }}/200
                    </div>
                </div>

                {{-- Content --}}
                <div style="flex:1;display:flex;flex-direction:column;gap:6px;justify-content:center;min-width:0;">
                    <div style="display:flex;flex-direction:column;gap:4px;">
                        <div style="display:flex;flex-direction:column;gap:4px;">
                            <div style="display:flex;align-items:flex-end;gap:8px;">
                                <span style="font-size:14px;color:#496df7;font-weight:500;font-family:'Nunito',sans-serif;line-height:18px;white-space:nowrap;">Step {{ $i + 1 }}</span>
                                <span style="display:inline-flex;align-items:center;padding:2px 8px;border-radius:999px;
                                    font-size:12px;font-weight:400;font-family:'Nunito',sans-serif;line-height:16px;white-space:nowrap;
                                    background:{{ $diffBg }};border:1px solid {{ $diffBorder }};color:{{ $diffColor }};">
                                    {{ $step->difficulty }}
                                </span>
                            </div>
                            <div style="font-weight:700;font-size:14px;color:#030719;font-family:'Nunito',sans-serif;line-height:18px;">{{ $step->title }}</div>
                        </div>
                        <div style="display:flex;align-items:center;gap:6px;">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($step->responsible) }}&size=40&background=random&rounded=true&bold=true&font-size=0.4"
                                 alt="{{ $step->responsible }}"
                                 style="width:20px;height:20px;border-radius:50%;object-fit:cover;flex-shrink:0;">
                            <span style="font-size:12px;font-weight:400;color:#030719;font-family:'Nunito',sans-serif;line-height:18px;">{{ $step->responsible }}</span>
                        </div>
                    </div>
                    <div style="display:inline-flex;align-items:center;gap:4px;padding:4px 8px;border-radius:999px;font-size:12px;font-weight:700;font-family:'Nunito',sans-serif;line-height:16px;width:fit-content;
                        {{ $step->completed ? 'background:#DEF7EC;border:1px solid #0E9F6E;color:#0E9F6E;' : 'background:#FEF3C7;border:1px solid #D97706;color:#D97706;' }}">
                        @if($step->completed)
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="8" fill="#0E9F6E"/><path d="M5 8l2 2 4-4" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        @else
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="7" stroke="#D97706" stroke-width="1.5"/><path d="M8 5v3l2 2" stroke="#D97706" stroke-width="1.5" stroke-linecap="round"/></svg>
                        @endif
                        {{ $step->completed ? 'Completed' : 'In Progress' }}
                    </div>
                </div>
            </div>

            {{-- Arrow BETWEEN rows — Figma: 187px wide = 28% of 671, positioned in the gap --}}
            @if(!$loop->last)
            <div style="margin:-35px 0;position:relative;z-index:0;top:45px;
                {{ $isLeft ? 'padding-left:30%;' : 'padding-right:30%;display:flex;justify-content:flex-end;' }}">
                <img src="/assets/dopifuture/{{ $isLeft ? 'arrow-right' : 'arrow-left' }}.svg"
                     alt="" style="width:189px;height:67px;">
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
                <div style="font-weight:600;font-size:13px;margin-bottom:12px;">Team Summary</div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:4px;font-size:12px;margin-bottom:4px;">
                    <span style="font-weight:500;">Member</span>
                    <span style="font-weight:500;text-align:right;">Responsible</span>
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
                    {{ $project->steps_completed ?? '6' }}/{{ $project->total_steps ?? '12' }} Step Completed
                </div>
                <div style="width:100%;height:8px;border-radius:4px;background:#e2e8f0;">
                    <div style="width:{{ ($project->steps_completed ?? 6) / ($project->total_steps ?? 12) * 100 }}%;height:100%;border-radius:4px;background:var(--color-primary);"></div>
                </div>
            </div>

            {{-- Total Product Score --}}
            <div class="dp-card" style="margin-bottom:16px;">
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <div>
                        <div style="font-size:12px;color:var(--color-txt-muted);">Total Product Score</div>
                        <div style="font-size:22px;font-weight:700;">{{ $project->product_score ?? '120' }} / {{ $project->max_score ?? '2500' }}</div>
                    </div>
                    <span style="display:inline-flex;align-items:center;gap:4px;background:#FEE2E2;color:#DC2626;padding:4px 12px;border-radius:20px;font-size:11px;font-weight:600;">
                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        Problem in Step 4
                    </span>
                </div>
            </div>

            {{-- Submitted Files — Figma shows real file list --}}
            <div class="dp-card" style="margin-bottom:16px;">
                <div style="font-weight:600;font-size:13px;margin-bottom:12px;">Submitted Files</div>
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

            {{-- Submitted Links --}}
            <div class="dp-card">
                <div style="font-weight:600;font-size:13px;margin-bottom:12px;">Submitted Links</div>
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
