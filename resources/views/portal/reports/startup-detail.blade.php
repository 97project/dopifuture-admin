@extends('portal.app')
@section('title', ($isTr ?? false) ? 'Proje Detay' : 'Project Detail')
@section('page-title', 'Startup â€” Detail')
@section('content')

    {{-- â•â•â• STEP ICONS â€” Figma 684-17330 exported assets â•â•â• --}}
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

    {{-- â•â•â• BACK BUTTON + TITLE â€” Figma node 684-17330 â•â•â• --}}
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
        <a href="{{ route('portal.reports.app', 'way-startup') }}" style="display:flex;align-items:center;gap:6px;text-decoration:none;color:var(--color-txt-muted);font-size:13px;">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <span style="font-size:18px;font-weight:600;">Project Detail</span>
    </div>

    <div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;align-items:start;">

        {{-- â•â•â• LEFT: ZIGZAG STEPS ROADMAP â€” Figma 671px container â•â•â• --}}
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

            {{-- Step card â€” Figma: 256px wide in 671px container = 38% --}}
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
                            <div style="width:20px;height:20px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--primary-deep));color:#fff;display:flex;align-items:center;justify-content:center;font-size:8px;font-weight:600;flex-shrink:0;">{{ strtoupper(substr($step->responsible ?? '', 0, 2)) }}</div>
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

            {{-- â•â•â• AI COACH FEEDBACK â€” per step (matching TaskReviewScreen.tsx) â•â•â• --}}
            @if($step->completed && ($step->overall_feedback || $step->questions->count() > 0))
            <div style="width:90%;margin:12px auto 8px;{{ $isLeft ? 'margin-left:5%' : 'margin-left:5%' }}">
                <div class="dp-card" style="border:1px solid #E8EDF5;padding:16px;">
                    {{-- Score badge --}}
                    <div style="display:flex;align-items:center;justify-content:center;gap:6px;padding:10px 16px;background:#ECFDF5;border-radius:12px;border-bottom:3px solid #34D399;margin-bottom:14px;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="#FBBF24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        <span style="font-size:18px;font-weight:800;color:#060B17;">Score:</span>
                        <span style="font-size:18px;font-weight:600;color:#060B17;">{{ $step->ai_score }}/{{ $step->ai_max_score }}</span>
                    </div>

                    {{-- Overall feedback --}}
                    @if($step->overall_feedback)
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--color-primary)" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                        <span style="font-size:14px;font-weight:700;color:#060B17;">AI Coach Feedback</span>
                    </div>
                    <div style="background:#EEF2FF;border-radius:12px;padding:14px;border-left:3px solid var(--color-primary);margin-bottom:14px;">
                        <p style="font-size:14px;color:#374151;line-height:22px;margin:0;">{{ $step->overall_feedback }}</p>
                    </div>
                    @endif

                    {{-- Question timeline â€” matches TaskReviewScreen QuestionItem --}}
                    @if($step->questions->count() > 0)
                    <div style="background:#fff;border:1px solid #E8EDF5;border-radius:12px;padding:14px;">
                        @foreach($step->questions as $qi => $q)
                        <div style="display:flex;gap:14px;">
                            {{-- Left: number badge + vertical line --}}
                            <div style="display:flex;flex-direction:column;align-items:center;width:28px;flex-shrink:0;">
                                <div style="width:28px;height:28px;border-radius:14px;background:var(--color-primary);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                    <span style="font-size:12px;font-weight:700;color:#fff;">{{ $qi + 1 }}</span>
                                </div>
                                @if(!$loop->last)
                                <div style="flex:1;width:2px;background:#E2E8F0;margin-top:4px;"></div>
                                @endif
                            </div>

                            {{-- Right: question content --}}
                            <div style="flex:1;padding-bottom:{{ $loop->last ? '0' : '14px' }};">
                                {{-- Question text --}}
                                <div style="font-size:14px;font-weight:700;color:#060B17;line-height:20px;margin-bottom:8px;">{{ $q->question_text }}</div>

                                {{-- Progress bar (score) --}}
                                @php
                                    $qPct = $q->ai_max_score > 0 ? min(($q->ai_score / $q->ai_max_score), 1) * 100 : 0;
                                @endphp
                                <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
                                    <div style="flex:1;height:16px;background:#F1F5F9;border-radius:999px;overflow:hidden;">
                                        <div style="width:{{ round($qPct) }}%;height:100%;border-radius:999px;background:linear-gradient(to bottom, #D2EB1E, #80B302);"></div>
                                    </div>
                                    <span style="font-size:12px;font-weight:700;color:#94C109;flex-shrink:0;">{{ $q->ai_score }}/{{ $q->ai_max_score }}</span>
                                </div>

                                {{-- User answer --}}
                                @if($q->user_answer)
                                <div style="background:#F8FAFF;border-radius:10px;padding:10px;margin-bottom:8px;">
                                    <div style="font-size:10px;font-weight:700;color:#94A3B8;letter-spacing:0.5px;margin-bottom:4px;">YOUR ANSWER</div>
                                    <div style="font-size:13px;color:#374151;line-height:20px;font-style:italic;">{{ $q->user_answer }}</div>
                                </div>
                                @endif

                                {{-- AI feedback --}}
                                @if($q->ai_feedback)
                                <div style="display:flex;gap:8px;align-items:flex-start;">
                                    <svg width="16" height="16" style="flex-shrink:0;margin-top:2px;" viewBox="0 0 24 24" fill="none" stroke="var(--color-primary)" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                                    <p style="font-size:13px;color:#374151;line-height:20px;margin:0;">
                                        <span style="font-weight:700;color:var(--color-primary);">Feedback: </span>{{ $q->ai_feedback }}
                                    </p>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Arrow BETWEEN rows â€” Figma: 187px wide = 28% of 671, positioned in the gap --}}
            @if(!$loop->last)
            <div style="margin:-35px 0;position:relative;z-index:0;top:45px;
                {{ $isLeft ? 'padding-left:30%;' : 'padding-right:30%;display:flex;justify-content:flex-end;' }}">
                <img src="/assets/dopifuture/{{ $isLeft ? 'arrow-right' : 'arrow-left' }}.svg"
                     alt="" style="width:189px;height:67px;">
            </div>
            @endif

            @endforeach
        </div>

        {{-- â•â•â• RIGHT: PROJECT SUMMARY â•â•â• --}}
        <div style="position:sticky;top:80px;">
            {{-- Project name + Team --}}
            <div class="dp-card" style="margin-bottom:16px;">
                <div style="font-size:18px;font-weight:700;margin-bottom:16px;">{{ $project->name ?? 'Project' }}</div>
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
                    {{ $project->steps_completed }}/{{ $project->total_steps }} Step Completed
                </div>
                <div style="width:100%;height:8px;border-radius:4px;background:#e2e8f0;">
                    <div style="width:{{ ($project->total_steps ?? 0) > 0 ? (($project->steps_completed ?? 0) / $project->total_steps * 100) : 0 }}%;height:100%;border-radius:4px;background:var(--color-primary);"></div>
                </div>
            </div>

            {{-- Total Product Score --}}
            <div class="dp-card" style="margin-bottom:16px;">
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <div>
                        <div style="font-size:12px;color:var(--color-txt-muted);">Total Product Score</div>
                        <div style="font-size:22px;font-weight:700;">{{ $project->product_score }} / {{ $project->max_score }}</div>
                    </div>
                </div>
            </div>

            {{-- â•â•â• RANKING â€” matching reference app RankingScreen.tsx / mockRankingData â•â•â• --}}
            {{-- TODO: Replace with real API data when backend ranking endpoint is available --}}
            <div class="dp-card" style="margin-bottom:16px;">
                <div style="font-weight:600;font-size:13px;margin-bottom:12px;display:flex;align-items:center;gap:6px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--color-primary)" stroke-width="2"><path d="M8 21h8m-4-4v4m-4-8l4-8 4 8m1-4h1a2 2 0 0 1 0 4h-1m-10 0H6a2 2 0 0 1 0-4h1"/></svg>
                    Ranking
                </div>
                @foreach($rankings ?? [] as $rank)
                <div style="display:flex;align-items:center;gap:10px;padding:8px 10px;border-radius:10px;margin-bottom:4px;
                    {{ $rank->is_current ? 'background:linear-gradient(135deg, rgba(67,100,247,0.08), rgba(67,100,247,0.04));border:1px solid rgba(67,100,247,0.2);' : 'background:var(--color-input-bg);' }}">
                    <div style="width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;flex-shrink:0;
                        {{ $rank->rank <= 3
                            ? 'background:linear-gradient(135deg,#FBBF24,#F59E0B);color:#fff;'
                            : 'background:#F1F5F9;color:#64748B;' }}">
                        {{ $rank->rank }}
                    </div>
                    <div style="flex:1;">
                        <div style="font-size:13px;font-weight:{{ $rank->is_current ? '700' : '500' }};color:{{ $rank->is_current ? 'var(--color-primary)' : '#060B17' }};">{{ $rank->name }}</div>
                    </div>
                    <div style="font-size:13px;font-weight:600;color:{{ $rank->is_current ? 'var(--color-primary)' : '#64748B' }};">{{ number_format($rank->score) }}</div>
                </div>
                @endforeach
            </div>

            {{-- Submitted Files â€” Figma shows real file list --}}
            <div class="dp-card" style="margin-bottom:16px;">
                <div style="font-weight:600;font-size:13px;margin-bottom:12px;">Submitted Files</div>
                @forelse($files ?? [] as $file)
                <div style="margin-bottom:12px;">
                    <div style="display:flex;align-items:center;gap:6px;margin-bottom:6px;">
                        <div style="display:inline-block;padding:3px 10px;border-radius:6px;font-size:10px;font-weight:600;background:#DBEAFE;color:#2563EB;">
                            Step {{ $file['step'] ?? '-' }}
                        </div>
                        @if($file['status'] ?? null)
                        @php
                            $statusBg = match($file['status']) { 'approved' => '#DEF7EC', 'rejected' => '#FEE2E2', default => '#FEF3C7' };
                            $statusColor = match($file['status']) { 'approved' => '#0E9F6E', 'rejected' => '#DC2626', default => '#D97706' };
                        @endphp
                        <span style="display:inline-block;padding:3px 10px;border-radius:6px;font-size:10px;font-weight:600;background:{{ $statusBg }};color:{{ $statusColor }};">
                            {{ ucfirst($file['status']) }}
                        </span>
                        @endif
                        @if($file['points_earned'] ?? null)
                        <span style="display:inline-block;padding:3px 8px;border-radius:6px;font-size:10px;font-weight:600;background:#ECFDF5;color:#059669;">
                            +{{ $file['points_earned'] }} pts
                        </span>
                        @endif
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 12px;background:var(--color-input-bg);border-radius:10px;">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div style="width:36px;height:36px;border-radius:8px;background:var(--color-primary);display:flex;align-items:center;justify-content:center;">
                                <svg width="16" height="16" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 2v6h6"/></svg>
                            </div>
                            <div>
                                <div style="font-size:13px;font-weight:500;">{{ $file['name'] ?? 'file' }}</div>
                                <div style="font-size:11px;color:var(--color-txt-muted);">{{ $file['size'] ?? '' }}</div>
                            </div>
                        </div>
                        @if(!empty($file['url']))
                        <a href="{{ $file['url'] }}" target="_blank" style="color:var(--color-txt-muted);cursor:pointer;" title="Download">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        </a>
                        @endif
                    </div>
                    @if($file['feedback'] ?? null)
                    <div style="margin-top:6px;padding:8px 12px;background:#F8FAFF;border-radius:8px;font-size:12px;color:#374151;line-height:18px;">
                        <span style="font-weight:600;color:var(--color-primary);">Feedback: </span>{{ $file['feedback'] }}
                    </div>
                    @endif
                </div>
                @empty
                <div style="text-align:center;padding:16px;color:var(--color-txt-muted);font-size:13px;">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin:0 auto 8px;display:block;opacity:0.4;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    No files submitted yet
                </div>
                @endforelse
            </div>

            {{-- Submitted Links --}}
            <div class="dp-card">
                <div style="font-weight:600;font-size:13px;margin-bottom:12px;">Submitted Links</div>
                @forelse($links ?? [] as $link)
                <div style="margin-bottom:12px;">
                    <div style="display:flex;align-items:center;gap:6px;margin-bottom:6px;">
                        <div style="display:inline-block;padding:3px 10px;border-radius:6px;font-size:10px;font-weight:600;background:#FEF3C7;color:#D97706;">
                            Step {{ $link['step'] ?? '-' }}
                        </div>
                        @if($link['platform'] ?? null)
                        <span style="display:inline-block;padding:3px 8px;border-radius:6px;font-size:10px;font-weight:600;background:#EDE9FE;color:#7C3AED;">
                            {{ ucfirst($link['platform']) }}
                        </span>
                        @endif
                        @if($link['status'] ?? null)
                        @php
                            $lStatusBg = match($link['status']) { 'approved' => '#DEF7EC', 'rejected' => '#FEE2E2', default => '#FEF3C7' };
                            $lStatusColor = match($link['status']) { 'approved' => '#0E9F6E', 'rejected' => '#DC2626', default => '#D97706' };
                        @endphp
                        <span style="display:inline-block;padding:3px 10px;border-radius:6px;font-size:10px;font-weight:600;background:{{ $lStatusBg }};color:{{ $lStatusColor }};">
                            {{ ucfirst($link['status']) }}
                        </span>
                        @endif
                    </div>
                    <div style="display:flex;align-items:center;gap:10px;padding:10px 12px;background:var(--color-input-bg);border-radius:10px;">
                        <div style="width:36px;height:36px;border-radius:8px;background:#F59E0B;display:flex;align-items:center;justify-content:center;">
                            <svg width="16" height="16" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                        </div>
                        <div style="flex:1;min-width:0;">
                            @if($link['title'] ?? null)
                            <div style="font-size:13px;font-weight:500;color:#060B17;">{{ $link['title'] }}</div>
                            @endif
                            <a href="{{ $link['url'] }}" target="_blank" style="font-size:12px;color:var(--color-primary);word-break:break-all;">{{ $link['url'] }}</a>
                        </div>
                    </div>
                </div>
                @empty
                <div style="text-align:center;padding:16px;color:var(--color-txt-muted);font-size:13px;">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin:0 auto 8px;display:block;opacity:0.4;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101"/></svg>
                    No links submitted yet
                </div>
                @endforelse
            </div>
        </div>
    </div>

@endsection
