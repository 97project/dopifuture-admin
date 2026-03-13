@extends('portal.app')
@section('title', ($student->name ?? '') . ' — ' . __('admin.reports'))
@section('page-title', ($student->name ?? '') . ' ' . ($student->surname ?? '') . ' — Rapor')

@section('content')
@php $isTr = app()->getLocale() === 'tr'; @endphp

@php
    $vegaProfile = $connectorProfiles['vega'] ?? $connectorProfiles['way-ai-coach'] ?? [];
    $photoUrl = $vegaProfile['profile']['photo_url'] ?? $vegaProfile['profile']['profilePhoto'] ?? null;
    $grade = $vegaProfile['profile']['grade'] ?? $vegaProfile['profile']['level'] ?? null;
    $premiumStatus = $vegaProfile['profile']['premium_status'] ?? $vegaProfile['profile']['isPremium'] ?? null;
    $onboardingDone = $vegaProfile['profile']['onboarding_completed'] ?? $vegaProfile['profile']['onboardingCompleted'] ?? null;
@endphp
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
    <div style="display:flex;align-items:center;gap:12px;">
        @if($photoUrl)
        <img src="{{ $photoUrl }}" alt="{{ $student->name }}" style="width:48px;height:48px;border-radius:50%;object-fit:cover;border:2px solid var(--primary);">
        @else
        <div style="width:48px;height:48px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--primary-deep));color:#fff;display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:600;">{{ strtoupper(substr($student->name,0,1).substr($student->surname??'',0,1)) }}</div>
        @endif
        <div>
            <div style="font-size:18px;font-weight:600;display:flex;align-items:center;gap:8px;">
                {{ $student->name }} {{ $student->surname }}
                @if($premiumStatus)
                <span style="font-size:10px;padding:2px 8px;background:linear-gradient(135deg,#F59E0B,#D97706);color:#fff;border-radius:999px;font-weight:700;">💎 Premium</span>
                @endif
                @if($grade)
                <span style="font-size:10px;padding:2px 8px;background:#EEF2FF;color:#4364F7;border-radius:999px;font-weight:600;">{{ $isTr ? 'Seviye' : 'Grade' }}: {{ $grade }}</span>
                @endif
                @if($onboardingDone)
                <span style="font-size:10px;padding:2px 8px;background:#ECFDF5;color:#10B981;border-radius:999px;">✅ Onboarding</span>
                @endif
            </div>
            <p style="font-size:13px;color:var(--text-muted);margin:2px 0 0;">{{ $student->email }} — {{ $isTr ? 'Detaylı öğrenci raporu' : 'Detailed student report' }}</p>
        </div>
    </div>
    <a href="{{ route('portal.reports') }}" class="dp-btn-ghost">← {{ $isTr ? 'Geri' : 'Back' }}</a>
</div>

{{-- Student Info Card --}}
<div class="dp-card">
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:16px;text-align:center;">
        <div>
            <div style="font-size:12px;color:var(--text-muted);">{{ $isTr ? 'Roller' : 'Roles' }}</div>
            <div style="margin-top:4px;">@foreach($student->roles as $r)<span class="dp-badge dp-badge-pending" style="margin-right:4px;">{{ $r->name }}</span>@endforeach</div>
        </div>
        <div>
            <div style="font-size:12px;color:var(--text-muted);">{{ $isTr ? 'Okullar' : 'Schools' }}</div>
            <div style="margin-top:4px;font-weight:500;">{{ $student->schools->pluck('name')->join(', ') ?: '-' }}</div>
        </div>
        <div>
            <div style="font-size:12px;color:var(--text-muted);">{{ $isTr ? 'Sınıflar' : 'Classes' }}</div>
            <div style="margin-top:4px;font-weight:500;">{{ $student->classes->pluck('name')->join(', ') ?: '-' }}</div>
        </div>
        <div>
            <div style="font-size:12px;color:var(--text-muted);">{{ $isTr ? 'Uygulamalar' : 'Applications' }}</div>
            <div style="margin-top:4px;font-weight:500;">{{ $student->applications->count() }}</div>
        </div>
    </div>
</div>

{{-- Connector API Profile Cards (MissionWay, WayStartup, Vega) --}}
@if(!empty($connectorProfiles))
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px;margin-bottom:20px;">
    @foreach($connectorProfiles as $slug => $profile)
    <div class="dp-card" style="padding:20px;">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;">
            <div style="width:36px;height:36px;border-radius:10px;background:{{ $slug === 'mission-way' ? 'linear-gradient(135deg,#4364F7,#6C63FF)' : ($slug === 'way-startup' ? 'linear-gradient(135deg,#10B981,#059669)' : 'linear-gradient(135deg,#8B5CF6,#6366F1)') }};display:flex;align-items:center;justify-content:center;">
                @if($slug === 'mission-way')
                    <svg width="18" height="18" fill="white" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                @elseif($slug === 'way-startup')
                    <svg width="18" height="18" fill="white" viewBox="0 0 24 24"><path d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                @else
                    <svg width="18" height="18" fill="white" viewBox="0 0 24 24"><path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                @endif
            </div>
            <div style="font-weight:700;font-size:15px;color:#030719;">
                {{ $slug === 'mission-way' ? 'Mission Way' : ($slug === 'way-startup' ? 'Way Startup' : 'Way AI Coach') }}
            </div>
        </div>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;text-align:center;">
            @if($slug === 'mission-way')
                <div>
                    <div style="font-size:22px;font-weight:800;color:#4364F7;">{{ number_format($profile['total_score'] ?? 0) }}</div>
                    <div style="font-size:11px;color:var(--text-muted);margin-top:2px;">{{ $isTr ? 'Toplam Puan' : 'Total Score' }}</div>
                </div>
                <div>
                    <div style="font-size:22px;font-weight:800;color:#10B981;">{{ $profile['simulations_completed'] ?? 0 }}</div>
                    <div style="font-size:11px;color:var(--text-muted);margin-top:2px;">{{ $isTr ? 'Tamamlanan' : 'Completed' }}</div>
                </div>
                <div>
                    <div style="font-size:22px;font-weight:800;color:#8B5CF6;">{{ $profile['play_time_minutes'] ?? 0 }}<span style="font-size:12px;">dk</span></div>
                    <div style="font-size:11px;color:var(--text-muted);margin-top:2px;">{{ $isTr ? 'Oyun Süresi' : 'Play Time' }}</div>
                </div>
            @elseif($slug === 'way-startup')
                <div>
                    <div style="font-size:22px;font-weight:800;color:#10B981;">{{ number_format($profile['points'] ?? 0) }}</div>
                    <div style="font-size:11px;color:var(--text-muted);margin-top:2px;">{{ $isTr ? 'Puan' : 'Points' }}</div>
                </div>
                <div>
                    <div style="font-size:22px;font-weight:800;color:#4364F7;">{{ $profile['completed_steps'] ?? 0 }}<span style="font-size:14px;color:var(--text-muted);">/{{ $profile['total_steps'] ?? 0 }}</span></div>
                    <div style="font-size:11px;color:var(--text-muted);margin-top:2px;">{{ $isTr ? 'Adım' : 'Steps' }}</div>
                </div>
                <div>
                    <div style="font-size:22px;font-weight:800;color:#F59E0B;">{{ $profile['tasks_remaining'] ?? 0 }}</div>
                    <div style="font-size:11px;color:var(--text-muted);margin-top:2px;">{{ $isTr ? 'Kalan Görev' : 'Remaining' }}</div>
                </div>
            @else
                {{-- Vega / Way AI Coach --}}
                <div>
                    <div style="font-size:22px;font-weight:800;color:#8B5CF6;">{{ $profile['session_count'] ?? 0 }}</div>
                    <div style="font-size:11px;color:var(--text-muted);margin-top:2px;">{{ $isTr ? 'Toplam Oturum' : 'Sessions' }}</div>
                </div>
                <div>
                    <div style="font-size:22px;font-weight:800;color:#F59E0B;">{{ $profile['simulator_count'] ?? 0 }}</div>
                    <div style="font-size:11px;color:var(--text-muted);margin-top:2px;">{{ $isTr ? 'Simülatör' : 'Simulator' }}</div>
                </div>
                {{-- Mini Donut Chart (Faz 4 — Sessions Overview) --}}
                @php
                    $simC = $profile['simulator_count'] ?? 0;
                    $lecC = $profile['lecturer_count'] ?? 0;
                    $chatC = $profile['chatbot_count'] ?? 0;
                    $donutTotal = max(1, $simC + $lecC + $chatC);
                    $simPct = round(($simC / $donutTotal) * 100);
                    $lecPct = round(($lecC / $donutTotal) * 100);
                    $chatPct = 100 - $simPct - $lecPct;
                @endphp
                @if($donutTotal > 1)
                <div style="text-align:center;">
                    <div style="width:44px;height:44px;border-radius:50%;background:conic-gradient(#F59E0B 0% {{ $simPct }}%, #10B981 {{ $simPct }}% {{ $simPct + $lecPct }}%, #3B82F6 {{ $simPct + $lecPct }}% 100%);display:inline-flex;align-items:center;justify-content:center;">
                        <div style="width:26px;height:26px;border-radius:50%;background:var(--bg-card,#fff);"></div>
                    </div>
                    <div style="font-size:9px;color:var(--text-muted);margin-top:2px;">{{ $isTr ? 'Dağılım' : 'Split' }}</div>
                </div>
                @endif
                <div>
                    <div style="font-size:22px;font-weight:800;color:#10B981;">{{ $profile['lecturer_count'] ?? 0 }}</div>
                    <div style="font-size:11px;color:var(--text-muted);margin-top:2px;">{{ $isTr ? 'Öğretmen' : 'Lecturer' }}</div>
                </div>
                @if(($profile['chatbot_count'] ?? 0) > 0)
                <div>
                    <div style="font-size:22px;font-weight:800;color:#3B82F6;">{{ $profile['chatbot_count'] }}</div>
                    <div style="font-size:11px;color:var(--text-muted);margin-top:2px;">{{ $isTr ? 'Sohbet' : 'Chatbot' }}</div>
                </div>
                @endif
                {{-- Vega: detail links per sub-app --}}
                @php
                    $vegaSlugs = ['role-galaxy','way-ai-coach','study-space'];
                    $vegaRoutes = [
                        'role-galaxy' => ['route' => 'portal.reports.simulator.detail', 'label' => '🎭 Role Galaxy', 'icon' => 'Simülatör'],
                        'way-ai-coach' => ['route' => 'portal.reports.coach.questions', 'label' => '🤖 AI Coach', 'icon' => 'Coach'],
                        'study-space' => ['route' => 'portal.reports.chatbot.detail', 'label' => '💬 Study Space', 'icon' => 'Chatbot'],
                    ];
                @endphp
            </div>
            <div style="margin-top:10px;display:flex;gap:8px;flex-wrap:wrap;">
                @foreach($vegaRoutes as $vs => $vr)
                @if(Route::has($vr['route']))
                <a href="{{ route($vr['route'], ['id' => $student->id]) }}" style="font-size:10px;padding:4px 10px;background:#f3f4f6;border-radius:999px;text-decoration:none;color:#4364F7;font-weight:600;">
                    {{ $vr['label'] }} → {{ $isTr ? 'Detay' : 'Detail' }}
                </a>
                @endif
                @endforeach
            </div>
            @endif
        </div>
        {{-- WayStartup: per-simulation progress cards --}}
        @if($slug === 'way-startup' && !empty($profile['simulations_with_progress']))
        <div style="margin-top:14px;padding-top:14px;border-top:1px solid var(--color-row-border,#eee);">
            <div style="font-size:11px;font-weight:600;color:var(--text-muted);margin-bottom:8px;">🚀 {{ $isTr ? 'Simülasyon İlerlemesi' : 'Simulation Progress' }}</div>
            <div style="display:flex;flex-direction:column;gap:8px;">
                @foreach($profile['simulations_with_progress'] as $simProg)
                @php
                    $simName = $simProg['name'] ?? $simProg['title'] ?? 'Simülasyon';
                    $prog = $simProg['progress'] ?? [];
                    $pct = $prog['completionPercentage'] ?? $prog['completion_percentage'] ?? 0;
                    $status = $prog['status'] ?? 'not_started';
                    $currentStep = $prog['currentStep'] ?? $prog['current_step'] ?? 0;
                    $totalStep = $simProg['totalStep'] ?? $simProg['total_step'] ?? 0;
                    $statusColor = match($status) {
                        'completed' => '#10B981',
                        'in_progress' => '#F59E0B',
                        default => '#94A3B8',
                    };
                    $statusLabel = match($status) {
                        'completed' => $isTr ? 'Tamamlandı' : 'Completed',
                        'in_progress' => $isTr ? 'Devam Ediyor' : 'In Progress',
                        default => $isTr ? 'Başlanmadı' : 'Not Started',
                    };
                @endphp
                <div style="padding:8px 10px;background:rgba(16,185,129,0.04);border-radius:8px;border:1px solid rgba(16,185,129,0.1);">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
                        <span style="font-size:12px;font-weight:600;">{{ $simName }}</span>
                        <span style="font-size:10px;padding:2px 6px;border-radius:999px;background:{{ $statusColor }};color:#fff;">{{ $statusLabel }}</span>
                    </div>
                    <div style="display:flex;align-items:center;gap:8px;">
                        <div style="flex:1;height:4px;background:#f1f1f1;border-radius:2px;overflow:hidden;">
                            <div style="height:100%;width:{{ min(100, $pct) }}%;background:{{ $statusColor }};border-radius:2px;transition:width 0.3s;"></div>
                        </div>
                        <span style="font-size:10px;font-weight:600;color:{{ $statusColor }};min-width:30px;">%{{ round($pct) }}</span>
                    </div>
                    @if($currentStep > 0 && $totalStep > 0)
                    <div style="font-size:9px;color:var(--text-muted);margin-top:2px;">{{ $isTr ? 'Adım' : 'Step' }} {{ $currentStep }}/{{ $totalStep }}</div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif
        {{-- MissionWay: avg metric bars --}}
        @if($slug === 'mission-way' && ($profile['avg_health'] ?? $profile['avg_resource'] ?? $profile['avg_ethics'] ?? $profile['avg_adaptation'] ?? null) !== null)
        <div style="margin-top:14px;padding-top:14px;border-top:1px solid var(--color-row-border,#eee);">
            <div style="font-size:11px;font-weight:600;color:var(--text-muted);margin-bottom:8px;">{{ $isTr ? 'Ortalama Metrikler' : 'Avg Metrics' }}</div>
            @php $metrics = [['key'=>'avg_health','label'=>$isTr?'Sağlık':'Health','color'=>'#EF4444'],['key'=>'avg_resource','label'=>$isTr?'Kaynak':'Resource','color'=>'#3B82F6'],['key'=>'avg_ethics','label'=>$isTr?'Etik':'Ethics','color'=>'#8B5CF6'],['key'=>'avg_adaptation','label'=>$isTr?'Adaptasyon':'Adapt.','color'=>'#F59E0B']]; @endphp
            @foreach($metrics as $m)
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;">
                <div style="width:65px;font-size:10px;color:var(--text-muted);">{{ $m['label'] }}</div>
                <div style="flex:1;height:6px;background:#f1f1f1;border-radius:3px;overflow:hidden;">
                    <div style="height:100%;width:{{ min(100, $profile[$m['key']] ?? 0) }}%;background:{{ $m['color'] }};border-radius:3px;"></div>
                </div>
                <div style="width:28px;font-size:10px;font-weight:600;text-align:right;">{{ $profile[$m['key']] ?? 0 }}</div>
            </div>
            @endforeach
        </div>
        @endif
        {{-- MissionWay: achievements badges --}}
        @if($slug === 'mission-way' && !empty($profile['achievements']))
        <div style="margin-top:14px;padding-top:14px;border-top:1px solid var(--color-row-border,#eee);">
            <div style="font-size:11px;font-weight:600;color:var(--text-muted);margin-bottom:8px;">🏆 {{ $isTr ? 'Başarı Rozetleri' : 'Achievements' }} ({{ count($profile['achievements']) }})</div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                @foreach($profile['achievements'] as $ach)
                @php
                    $achName = is_array($ach) ? ($ach['name'] ?? $ach['title'] ?? $ach['type'] ?? '') : $ach;
                    $achType = is_array($ach) ? ($ach['type'] ?? '') : '';
                    $achDate = is_array($ach) ? ($ach['earnedAt'] ?? $ach['earned_at'] ?? null) : null;
                    $achIcon = match(strtolower($achType)) {
                        'first_sim', 'first_simulation' => '🎮',
                        'first_win', 'winner' => '🥇',
                        'streak', 'hot_streak' => '🔥',
                        'perfect', 'perfect_score' => '💎',
                        'team', 'teamwork' => '🤝',
                        'speed', 'fast' => '⚡',
                        'ethics', 'ethical' => '⚖️',
                        default => '🏅',
                    };
                @endphp
                <div style="display:inline-flex;align-items:center;gap:6px;padding:6px 12px;background:linear-gradient(135deg,rgba(67,100,247,0.08),rgba(139,92,246,0.08));border-radius:999px;font-size:11px;font-weight:500;color:#4364F7;" title="{{ $achDate ? ($isTr ? 'Kazanıldı: ' : 'Earned: ') . \Carbon\Carbon::parse($achDate)->format('d.m.Y') : '' }}">
                    {{ $achIcon }} {{ $achName }}
                    @if($achDate)
                    <span style="font-size:9px;color:var(--text-muted);font-weight:400;">{{ \Carbon\Carbon::parse($achDate)->format('d.m.Y') }}</span>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
    @endforeach
</div>
@endif

{{-- MissionWay: Senaryo Bazlı Rapor Kartları --}}
@php $mwScenarios = ($connectorProfiles['mission-way']['scenario_breakdown'] ?? []); @endphp
@if(count($mwScenarios) > 0)
<div style="margin-bottom:20px;">
    <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">
        <span style="font-size:16px;">🎭</span>
        <span style="font-size:15px;font-weight:700;">{{ $isTr ? 'Senaryo Performansı' : 'Scenario Performance' }}</span>
        <span style="font-size:11px;color:var(--text-muted);background:var(--bg-subtle,#f5f5f5);padding:2px 8px;border-radius:999px;">{{ count($mwScenarios) }} {{ $isTr ? 'senaryo' : 'scenarios' }}</span>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:12px;">
        @foreach($mwScenarios as $sKey => $sc)
        <div class="dp-card" style="padding:16px;border-left:3px solid {{ $sc['color'] }};">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px;">
                <span style="font-size:20px;">{{ $sc['icon'] }}</span>
                <span style="font-size:13px;font-weight:700;color:#111;">{{ $sc['name'] }}</span>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;">
                <div>
                    <div style="font-size:18px;font-weight:800;color:{{ $sc['color'] }};">{{ $sc['sessions'] }}</div>
                    <div style="font-size:10px;color:var(--text-muted);">{{ $isTr ? 'Oturum' : 'Sessions' }}</div>
                </div>
                <div>
                    <div style="font-size:18px;font-weight:800;color:{{ $sc['color'] }};">{{ $sc['avg_score'] }}</div>
                    <div style="font-size:10px;color:var(--text-muted);">{{ $isTr ? 'Ort. Skor' : 'Avg Score' }}</div>
                </div>
                <div>
                    <div style="font-size:14px;font-weight:700;color:#6B7280;">{{ $sc['total_time'] }}<span style="font-size:10px;">dk</span></div>
                    <div style="font-size:10px;color:var(--text-muted);">{{ $isTr ? 'Süre' : 'Time' }}</div>
                </div>
                <div>
                    @if($sc['last_played'])
                    <div style="font-size:11px;font-weight:600;color:#6B7280;">{{ \Carbon\Carbon::parse($sc['last_played'])->format('d.m') }}</div>
                    <div style="font-size:10px;color:var(--text-muted);">{{ $isTr ? 'Son' : 'Last' }}</div>
                    @else
                    <div style="font-size:11px;color:var(--text-muted);">—</div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- Yetkinlik Atlası Linki --}}
<div style="margin-bottom:20px;">
    <a href="{{ route('portal.reports.competency.atlas', $student->id) }}" class="dp-btn" style="display:inline-flex;align-items:center;gap:6px;font-size:13px;">
        🧠 {{ $isTr ? 'Yetkinlik Atlası (12 Alan)' : 'Competency Atlas (12 Areas)' }} →
    </a>
</div>

{{-- ═══ Eksik #6: Lecturer (AI Coach) Oturum Listesi ═══ --}}
@php $lecSessions = []; foreach($connectorProfiles as $cp) { $lecSessions = $cp['lecturer_sessions'] ?? []; if(!empty($lecSessions)) break; } @endphp
@if(count($lecSessions) > 0)
<div class="dp-card" style="margin-bottom:20px;">
    <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">
        <span style="font-size:16px;">🤖</span>
        <span style="font-size:14px;font-weight:700;">{{ $isTr ? 'AI Coach Oturum Geçmişi' : 'AI Coach Session History' }}</span>
        <span style="font-size:10px;color:var(--text-muted);background:#f5f5f5;padding:2px 8px;border-radius:999px;">{{ count($lecSessions) }} {{ $isTr ? 'oturum' : 'sessions' }}</span>
    </div>
    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;font-size:12px;">
            <thead>
                <tr style="border-bottom:2px solid #E5E7EB;">
                    <th style="text-align:left;padding:6px 8px;color:var(--text-muted);font-weight:600;">{{ $isTr ? 'Konu' : 'Topic' }}</th>
                    <th style="text-align:center;padding:6px 8px;color:var(--text-muted);font-weight:600;">{{ $isTr ? 'Mesaj' : 'Messages' }}</th>
                    <th style="text-align:center;padding:6px 8px;color:var(--text-muted);font-weight:600;">{{ $isTr ? 'Süre' : 'Duration' }}</th>
                    <th style="text-align:right;padding:6px 8px;color:var(--text-muted);font-weight:600;">{{ $isTr ? 'Tarih' : 'Date' }}</th>
                    <th style="text-align:right;padding:6px 8px;"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($lecSessions as $ls)
                <tr style="border-bottom:1px solid #F3F4F6;">
                    <td style="padding:8px;font-weight:500;color:#111;">{{ $ls['app_name'] ?? $ls['scenario_name'] ?? ($isTr ? 'AI Coach Oturumu' : 'AI Coach Session') }}</td>
                    <td style="padding:8px;text-align:center;color:#6B7280;">{{ $ls['message_count'] ?? $ls['messageCount'] ?? '-' }}</td>
                    <td style="padding:8px;text-align:center;color:#6B7280;">{{ $ls['duration'] ?? '-' }} {{ $isTr ? 'dk' : 'min' }}</td>
                    <td style="padding:8px;text-align:right;color:#9CA3AF;font-size:11px;">{{ isset($ls['created_at']) ? \Carbon\Carbon::parse($ls['created_at'])->format('d.m.Y H:i') : '-' }}</td>
                    <td style="padding:8px;text-align:right;">
                        @if(Route::has('portal.reports.coach.questions'))
                        <a href="{{ route('portal.reports.coach.questions', ['id' => $ls['id'] ?? $ls['session_id'] ?? '']) }}" style="font-size:10px;color:#4364F7;text-decoration:none;font-weight:600;">{{ $isTr ? 'Detay →' : 'Detail →' }}</a>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- ═══ Eksik #5: Chat (Study Space) Oturum Listesi ═══ --}}
@php $chatSessions = []; foreach($connectorProfiles as $cp) { $chatSessions = $cp['chatbot_sessions'] ?? []; if(!empty($chatSessions)) break; } @endphp
@if(count($chatSessions) > 0)
<div class="dp-card" style="margin-bottom:20px;">
    <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">
        <span style="font-size:16px;">💬</span>
        <span style="font-size:14px;font-weight:700;">{{ $isTr ? 'Study Space Sohbet Geçmişi' : 'Study Space Chat History' }}</span>
        <span style="font-size:10px;color:var(--text-muted);background:#f5f5f5;padding:2px 8px;border-radius:999px;">{{ count($chatSessions) }}</span>
    </div>
    <div style="display:flex;flex-direction:column;gap:8px;">
        @foreach($chatSessions as $cs)
        <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 12px;background:#F8FAFC;border-radius:10px;border:1px solid #E5E7EB;">
            <div style="display:flex;align-items:center;gap:10px;">
                <div style="width:32px;height:32px;border-radius:8px;background:#EFF6FF;display:flex;align-items:center;justify-content:center;font-size:14px;">📖</div>
                <div>
                    <div style="font-size:12px;font-weight:600;color:#111;">{{ $cs['thread_name'] ?? $cs['app_name'] ?? ($isTr ? 'Sohbet' : 'Chat') }}</div>
                    <div style="font-size:10px;color:#9CA3AF;">{{ $cs['message_count'] ?? $cs['messageCount'] ?? '?' }} {{ $isTr ? 'mesaj' : 'messages' }} · {{ isset($cs['created_at']) ? \Carbon\Carbon::parse($cs['created_at'])->format('d.m.Y') : '' }}</div>
                </div>
            </div>
            @if(Route::has('portal.reports.chatbot.detail'))
            <a href="{{ route('portal.reports.chatbot.detail', ['id' => $cs['id'] ?? $cs['session_id'] ?? '']) }}" style="font-size:10px;color:#3B82F6;text-decoration:none;font-weight:600;">{{ $isTr ? 'Detay →' : 'Detail →' }}</a>
            @endif
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- ═══ Eksik #13: MissionWay Senaryo Seçim Geçmişi ═══ --}}
@php
    $mwProfile = $connectorProfiles['mission-way'] ?? [];
    $mwSessions = $mwProfile['sessions'] ?? [];
    $scenarioHistory = [];
    foreach ($mwScenarios ?? [] as $sKey => $sc) {
        if ($sc['sessions'] > 0) {
            $scenarioHistory[] = array_merge($sc, ['key' => $sKey, 'last' => $sc['last_played']]);
        }
    }
@endphp
@if(count($scenarioHistory) > 0)
<div class="dp-card" style="margin-bottom:20px;">
    <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">
        <span style="font-size:16px;">🏠</span>
        <span style="font-size:14px;font-weight:700;">{{ $isTr ? 'Oynanan Senaryolar' : 'Played Scenarios' }}</span>
    </div>
    <div style="display:flex;flex-wrap:wrap;gap:10px;">
        @foreach($scenarioHistory as $sh)
        <div style="display:flex;align-items:center;gap:8px;padding:10px 14px;background:{{ $sh['color'] }}08;border:1px solid {{ $sh['color'] }}30;border-radius:10px;">
            <span style="font-size:20px;">{{ $sh['icon'] }}</span>
            <div>
                <div style="font-size:12px;font-weight:700;color:{{ $sh['color'] }};">{{ $sh['name'] }}</div>
                <div style="font-size:10px;color:#6B7280;">{{ $sh['sessions'] }} {{ $isTr ? 'kez oynandı' : 'times played' }} · {{ $sh['last'] ? \Carbon\Carbon::parse($sh['last'])->format('d.m.Y') : '' }}</div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- App Tabs --}}
<div style="display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap;">
    @foreach($apps as $a)
        @php $active = isset($reportData[$a->slug]); @endphp
        <span class="{{ $active ? 'dp-btn' : 'dp-btn-ghost' }}" style="font-size:13px;pointer-events:none;">
            {{ $a->name }}
            @if($active)
                <span style="background:rgba(255,255,255,0.2);padding:2px 6px;border-radius:4px;font-size:11px;margin-left:4px;">{{ $reportData[$a->slug]['stats']['completion_rate'] }}%</span>
            @endif
        </span>
    @endforeach
</div>

{{-- Each App Report --}}
@foreach($reportData as $slug => $appData)
<div class="dp-card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
        <div class="dp-card-title" style="margin-bottom:0;">{{ $appData['app']->name }}</div>
        <span class="dp-badge {{ $appData['stats']['completion_rate'] >= 80 ? 'dp-badge-active' : ($appData['stats']['completion_rate'] >= 40 ? 'dp-badge-pending' : 'dp-badge-error') }}">{{ $appData['stats']['completion_rate'] }}%</span>
    </div>

    <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:16px;text-align:center;margin-bottom:16px;">
        <div>
            <div style="font-size:24px;font-weight:700;">{{ $appData['stats']['total_modules'] }}</div>
            <div style="font-size:11px;color:var(--text-muted);">{{ $isTr ? 'Modül' : 'Modules' }}</div>
        </div>
        <div>
            <div style="font-size:24px;font-weight:700;color:var(--active-green);">{{ $appData['stats']['completed'] }}</div>
            <div style="font-size:11px;color:var(--text-muted);">{{ $isTr ? 'Tamamlanan' : 'Completed' }}</div>
        </div>
        <div>
            <div style="font-size:24px;font-weight:700;color:#fbbf24;">{{ $appData['stats']['in_progress'] }}</div>
            <div style="font-size:11px;color:var(--text-muted);">{{ $isTr ? 'Devam Eden' : 'In Progress' }}</div>
        </div>
        <div>
            <div style="font-size:24px;font-weight:700;color:var(--primary);">{{ $appData['stats']['avg_score'] ? number_format($appData['stats']['avg_score'], 1) : '-' }}</div>
            <div style="font-size:11px;color:var(--text-muted);">{{ $isTr ? 'Ort. Puan' : 'Avg Score' }}</div>
        </div>
        <div>
            <div style="font-size:24px;font-weight:700;color:#a78bfa;">{{ $appData['stats']['total_sessions'] }}</div>
            <div style="font-size:11px;color:var(--text-muted);">{{ $isTr ? 'Oturum' : 'Sessions' }}</div>
        </div>
    </div>

    <div class="dp-progress" style="margin-bottom:20px;">
        <div class="dp-progress-fill" style="width:{{ $appData['stats']['completion_rate'] }}%;"></div>
    </div>

    {{-- Module Progress --}}
    @if($appData['progress']->count())
    <div class="dp-card-title" style="font-size:14px;">📋 {{ $isTr ? 'Modül İlerlemesi' : 'Module Progress' }}</div>
    <table class="dp-table">
        <thead><tr>
            <th>{{ $isTr ? 'Modül' : 'Module' }}</th><th>{{ $isTr ? 'Tip' : 'Type' }}</th>
            <th>{{ $isTr ? 'Durum' : 'Status' }}</th><th>{{ $isTr ? 'Puan' : 'Score' }}</th>
            <th>{{ $isTr ? 'Deneme' : 'Attempts' }}</th><th>{{ $isTr ? 'Tarih' : 'Date' }}</th>
        </tr></thead>
        <tbody>
        @foreach($appData['progress'] as $p)
        <tr>
            <td style="font-weight:500;">{{ $p->module_name ?: $p->module_id }}</td>
            <td><span class="dp-badge dp-badge-pending">{{ $p->module_type }}</span></td>
            <td><span class="dp-badge {{ $p->status === 'completed' ? 'dp-badge-active' : ($p->status === 'in_progress' ? 'dp-badge-pending' : 'dp-badge-error') }}">{{ $p->status }}</span></td>
            <td>{{ $p->score !== null ? number_format($p->score, 1) : '-' }}{{ $p->max_score ? '/'.$p->max_score : '' }}</td>
            <td>{{ $p->attempts }}</td>
            <td class="muted">{{ $p->completed_at ? $p->completed_at->format('d.m.Y H:i') : ($p->started_at ? $p->started_at->format('d.m.Y H:i') : '-') }}</td>
        </tr>
        @endforeach
        </tbody>
    </table>
    @endif

    {{-- Sessions --}}
    @if($appData['sessions']->count())
    <div class="dp-card-title" style="font-size:14px;margin-top:16px;">🕐 {{ $isTr ? 'Oturum Geçmişi' : 'Session History' }}</div>
    <table class="dp-table">
        <thead><tr>
            <th>{{ $isTr ? 'Oturum' : 'Session' }}</th><th>{{ $isTr ? 'Tip' : 'Type' }}</th>
            <th>{{ $isTr ? 'Başlangıç' : 'Start' }}</th><th>{{ $isTr ? 'Süre' : 'Duration' }}</th>
            <th>{{ $isTr ? 'Puan' : 'Score' }}</th>
        </tr></thead>
        <tbody>
        @foreach($appData['sessions']->take(15) as $s)
        <tr>
            <td style="font-weight:500;">{{ $s->session_name ?: $s->external_session_id }}</td>
            <td><span class="dp-badge dp-badge-pending">{{ $s->session_type }}</span></td>
            <td class="muted">{{ $s->started_at ? $s->started_at->format('d.m.Y H:i') : '-' }}</td>
            <td>{{ $s->duration_seconds ? \App\Services\ReportService::formatDuration($s->duration_seconds) : '-' }}</td>
            <td>{{ $s->score !== null ? number_format($s->score, 1) : '-' }}</td>
        </tr>
        @endforeach
        </tbody>
    </table>
    @endif
</div>
@endforeach

@if(empty($reportData))
<div class="dp-card" style="text-align:center;padding:48px;">
    <div style="font-size:32px;margin-bottom:8px;">📭</div>
    <p style="color:var(--text-muted);">{{ $isTr ? 'Henüz rapor verisi yok. Veriler uygulama senkronizasyonu sonrası burada görünecektir.' : 'No report data yet. Data will appear after application sync.' }}</p>
</div>
@endif
@endsection
