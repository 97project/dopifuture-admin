@extends('portal.app')
@section('title', ($module === 'simulator' ? 'Simulation' : ($module === 'lecturer' ? 'Way AI Coach' : 'Study Space')) . ' Detail')
@section('page-title', ($module === 'simulator' ? '🎮 Simulation' : ($module === 'lecturer' ? '💬 Way AI Coach' : '🤖 Study Space')) . ' — Session Detail')

@section('styles')
@if($module !== 'simulator')
<link rel="stylesheet" href="{{ asset('css/whatsapp-chat.css') }}">
@endif
<style>
/* ── Simulator Timeline ──────────────────────── */
.sim-timeline { position: relative; padding-left: 24px; }
.sim-timeline::before {
    content: ''; position: absolute; left: 11px; top: 0; bottom: 0;
    width: 2px; background: linear-gradient(180deg, var(--primary), var(--primary-deep));
}
.sim-timeline-item {
    position: relative; margin-bottom: 16px; padding: 16px 20px;
    background: var(--card-bg, #fff); border-radius: 12px;
    border: 1px solid var(--border-color, #e5e7eb);
    transition: box-shadow 0.2s;
}
.sim-timeline-item:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
.sim-timeline-item::before {
    content: ''; position: absolute; left: -19px; top: 20px;
    width: 10px; height: 10px; border-radius: 50%;
    background: var(--primary); border: 2px solid var(--card-bg, #fff);
}
.sim-timeline-item.ended::before { background: #ef4444; }
.sim-timeline-item.active { border-color: var(--primary); box-shadow: 0 0 0 2px rgba(102,126,234,0.15); }

/* Score Display */
.score-display {
    width: 100px; height: 100px; border-radius: 50%; margin: 0 auto;
    display: flex; align-items: center; justify-content: center;
    font-size: 32px; font-weight: 700; color: #fff;
}
.score-high { background: linear-gradient(135deg, #22c55e, #16a34a); }
.score-medium { background: linear-gradient(135deg, #f59e0b, #d97706); }
.score-low { background: linear-gradient(135deg, #ef4444, #dc2626); }

/* Score Delta */
.score-delta { font-weight: 600; font-size: 13px; padding: 2px 8px; border-radius: 4px; }
.score-delta.positive { color: #22c55e; background: rgba(34,197,94,0.1); }
.score-delta.negative { color: #ef4444; background: rgba(239,68,68,0.1); }

/* Choice Cards */
.choice-card {
    padding: 10px 14px; margin: 4px 0; border-radius: 8px; font-size: 13px;
    border: 1px solid var(--border-color, #e5e7eb); background: var(--bg-secondary, #f9fafb);
    display: flex; align-items: center; gap: 8px; transition: all 0.2s;
}
.choice-card.selected {
    background: rgba(34,197,94,0.1); border-color: #22c55e; color: #16a34a;
}

/* Coach Advice */
.coach-advice {
    margin-top: 10px; padding: 10px 14px; background: rgba(34,197,94,0.06);
    border-radius: 8px; border-left: 3px solid #22c55e;
}
.coach-advice-title { font-size: 11px; font-weight: 600; color: #16a34a; margin-bottom: 4px; }

/* Playback Modal */
.playback-overlay {
    display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0,0,0,0.6); z-index: 1000; align-items: center; justify-content: center;
}
.playback-overlay.active { display: flex; }
.playback-modal {
    background: var(--card-bg, #fff); border-radius: 16px; width: 700px; max-width: 90vw;
    max-height: 80vh; overflow: hidden; box-shadow: 0 25px 50px rgba(0,0,0,0.25);
}
.playback-header {
    background: linear-gradient(135deg, var(--primary), var(--primary-deep));
    color: #fff; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;
}
.playback-body { padding: 20px; overflow-y: auto; max-height: 50vh; }
.playback-controls {
    padding: 12px 20px; border-top: 1px solid var(--border-color, #e5e7eb);
    display: flex; justify-content: center; gap: 12px; align-items: center;
}
.playback-progress { height: 4px; background: #e5e7eb; border-radius: 2px; }
.playback-progress-fill { height: 100%; background: var(--primary); border-radius: 2px; transition: width 0.3s; }

/* Threshold Badges */
.threshold-refah { background: #dcfce7; color: #166534; }
.threshold-denge { background: #dbeafe; color: #1e40af; }
.threshold-kriz { background: #fed7aa; color: #9a3412; }
.threshold-felaket { background: #fecaca; color: #991b1b; }

/* Audio Text Details */
details summary { cursor: pointer; font-size: 12px; color: var(--text-muted); }
details summary:hover { color: var(--text-primary); }

/* Dark mode adaptation for WhatsApp chat */
.whatsapp-chat-container { border-radius: 0 0 12px 12px; }
</style>
@endsection

@section('content')
{{-- Header --}}
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
    <div>
        <div style="font-size:18px;font-weight:600;">
            @if($module === 'simulator')
                🎮 Simulation Detail
            @elseif($module === 'lecturer')
                💬 Way AI Coach Session
            @else
                🤖 Study Space Chat
            @endif
        </div>
        <p style="font-size:13px;color:var(--text-muted);margin:2px 0 0;">
            {{ $session->external_session_id }}
            @if($student)
                — {{ $student->name }} {{ $student->surname }}
            @endif
        </p>
    </div>
    <div style="display:flex;gap:8px;">
        @if($module === 'simulator')
            <button id="playback-open-btn" class="dp-btn" style="font-size:13px;" onclick="document.getElementById('playbackOverlay').classList.add('active')">
                ▶ Play
            </button>
        @endif
        @if($student)
            <a href="{{ route('portal.reports.student', $student) }}" class="dp-btn-ghost">← Student Report</a>
        @else
            <a href="{{ route('portal.reports') }}" class="dp-btn-ghost">← Back</a>
        @endif
    </div>
</div>

<div style="display:grid;grid-template-columns:350px 1fr;gap:20px;">
    {{-- LEFT SIDEBAR: Session Info --}}
    <div>
        {{-- Session Info Card --}}
        <div class="dp-card">
            <div class="dp-card-title" style="font-size:14px;">📋 Session Info</div>
            <table style="width:100%;font-size:13px;">
                @if($module === 'simulator')
                <tr>
                    <td style="padding:6px 0;color:var(--text-muted);width:100px;">Scenario</td>
                    <td style="padding:6px 0;font-weight:500;">
                        <span class="dp-badge dp-badge-pending">{{ $session->scenario ?? '-' }}</span>
                    </td>
                </tr>
                @endif
                @if($module === 'lecturer')
                <tr>
                    <td style="padding:6px 0;color:var(--text-muted);width:100px;">{{ __('portal.subject') }}</td>
                    <td style="padding:6px 0;font-weight:500;">
                        <span class="dp-badge dp-badge-pending">{{ $session->subject ?? '-' }}</span>
                    </td>
                </tr>
                <tr>
                    <td style="padding:6px 0;color:var(--text-muted);">Sub Topic</td>
                    <td style="padding:6px 0;">{{ $session->topic ?? '-' }}</td>
                </tr>
                <tr>
                    <td style="padding:6px 0;color:var(--text-muted);">Theme</td>
                    <td style="padding:6px 0;">
                        @if($session->theme)
                            <span class="dp-badge dp-badge-pending">{{ $session->theme }}</span>
                        @else - @endif
                    </td>
                </tr>
                @endif
                <tr>
                    <td style="padding:6px 0;color:var(--text-muted);">{{ __('admin.status') }}</td>
                    <td style="padding:6px 0;">
                        @php
                            $statusClass = match(strtoupper($session->status ?? '')) {
                                'ACTIVE' => 'dp-badge-active',
                                'COMPLETED', 'ENDED' => 'dp-badge-pending',
                                default => 'dp-badge-error',
                            };
                            $statusLabel = match(strtoupper($session->status ?? '')) {
                                'ACTIVE' => 'Active',
                                'COMPLETED' => 'Completed',
                                'ENDED' => 'Ended',
                                'ABANDONED' => 'Abandoned',
                                default => $session->status ?? '-',
                            };
                        @endphp
                        <span class="dp-badge {{ $statusClass }}">{{ $statusLabel }}</span>
                    </td>
                </tr>
                @if($module !== 'simulator' && $session->language)
                <tr>
                    <td style="padding:6px 0;color:var(--text-muted);">Language</td>
                    <td style="padding:6px 0;">
                        @php
                            $langLabels = ['tr' => '🇹🇷 Turkish', 'en' => '🇬🇧 English', 'de' => '🇩🇪 German', 'es' => '🇪🇸 Spanish'];
                        @endphp
                        <span class="dp-badge dp-badge-pending">{{ $langLabels[$session->language] ?? strtoupper($session->language) }}</span>
                    </td>
                </tr>
                @endif
                <tr>
                    <td style="padding:6px 0;color:var(--text-muted);">
                        @if($module === 'simulator') Steps @else Messages @endif
                    </td>
                    <td style="padding:6px 0;font-weight:600;">
                        @if($module === 'simulator')
                            {{ $session->simulatorSteps->count() }} steps
                        @elseif($module === 'lecturer')
                            {{ $session->lecturerMessages->count() }} messages
                        @else
                            {{ $session->chatMessages->count() }} messages
                        @endif
                    </td>
                </tr>
                @if($session->thread_id)
                <tr>
                    <td style="padding:6px 0;color:var(--text-muted);">Thread</td>
                    <td style="padding:6px 0;"><code style="font-size:11px;">{{ Str::limit($session->thread_id, 15) }}</code></td>
                </tr>
                @endif
                <tr>
                    <td style="padding:6px 0;color:var(--text-muted);">Started</td>
                    <td style="padding:6px 0;">{{ $session->created_at?->format('d.m.Y H:i') ?? '-' }}</td>
                </tr>
            </table>
        </div>

        @if($module === 'simulator')
        {{-- Score Display --}}
        <div class="dp-card" style="text-align:center;">
            @php
                $score = $session->score ?? 0;
                $scoreClass = $score >= 70 ? 'score-high' : ($score >= 50 ? 'score-medium' : 'score-low');
            @endphp
            <div class="score-display {{ $scoreClass }}">{{ $session->score ?? '-' }}</div>
            <div style="margin-top:8px;">
                @if($session->threshold)
                    <span class="dp-badge threshold-{{ $session->threshold }}" style="padding:4px 12px;font-size:13px;">
                        {{ ucfirst($session->threshold) }}
                    </span>
                @endif
            </div>
        </div>
        @endif

        @if($module !== 'simulator')
        {{-- Token Usage --}}
        <div class="dp-card" style="text-align:center;">
            <div style="font-size:11px;color:var(--text-muted);margin-bottom:4px;">Token Usage</div>
            <div style="font-size:28px;font-weight:700;color:var(--primary);">{{ number_format($totalTokens) }}</div>
            <div style="font-size:11px;color:var(--text-muted);">Estimated Total</div>
        </div>
        @endif
    </div>

    {{-- RIGHT MAIN AREA --}}
    <div>
        @if($module === 'simulator')
            {{-- SIMULATOR: Score Chart --}}
            @if(count($chartData) > 0)
            <div class="dp-card">
                <div class="dp-card-title" style="font-size:14px;">📈 Score Chart</div>
                <div style="height:220px;"><canvas id="scoreChart"></canvas></div>
            </div>
            @endif

            {{-- SIMULATOR: Steps Timeline --}}
            <div class="dp-card">
                <div class="dp-card-title" style="font-size:14px;">🎮 Simulation Timeline ({{ $session->simulatorSteps->count() }} steps)</div>
                <div class="sim-timeline">
                    @foreach($session->simulatorSteps->sortBy('turn') as $step)
                    <div class="sim-timeline-item {{ $step->ended ? 'ended' : '' }}" id="step-{{ $step->turn }}">
                        {{-- Header --}}
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                                <span style="font-weight:600;font-size:13px;color:var(--primary);">Turn {{ $step->turn }}</span>
                                <span style="font-size:11px;color:var(--text-muted);background:var(--bg-secondary,#f1f5f9);padding:2px 6px;border-radius:4px;">{{ $step->node_id }}</span>
                                @if($step->delta != 0)
                                    <span class="score-delta {{ $step->delta > 0 ? 'positive' : 'negative' }}">
                                        {{ $step->delta > 0 ? '+' : '' }}{{ $step->delta }}
                                    </span>
                                @endif
                                @if($step->threshold_after)
                                    <span class="dp-badge threshold-{{ $step->threshold_after }}" style="font-size:10px;">
                                        {{ ucfirst($step->threshold_after) }}
                                    </span>
                                @endif
                            </div>
                            <span style="padding:2px 8px;border-radius:4px;font-size:11px;font-weight:500;background:var(--bg-secondary,#f1f5f9);color:var(--text-primary);">
                                Score: {{ $step->score_after }}
                            </span>
                        </div>

                        {{-- Node Text --}}
                        @if($step->node_intro)
                            <div style="font-size:12px;color:var(--text-muted);margin-bottom:4px;">
                                <strong>Intro:</strong> {{ Str::limit($step->node_intro, 200) }}
                            </div>
                        @endif
                        @if($step->node_text)
                            <p style="font-size:13px;line-height:1.6;margin:0 0 8px;">{{ Str::limit($step->node_text, 400) }}</p>
                        @endif

                        {{-- Question --}}
                        @if($step->node_question)
                            <div style="background:rgba(245,158,11,0.08);border-left:3px solid #f59e0b;padding:8px 12px;border-radius:0 6px 6px 0;margin-bottom:8px;">
                                <strong style="font-size:12px;color:#d97706;">❓ Question:</strong>
                                <span style="font-size:13px;">{{ $step->node_question }}</span>
                            </div>
                        @endif

                        {{-- Choices --}}
                        @if($step->choices && is_array($step->choices))
                            <div style="margin:8px 0;">
                                <div style="font-size:12px;font-weight:600;color:var(--text-muted);margin-bottom:4px;">Choices:</div>
                                @foreach($step->choices as $choice)
                                    @php $isSelected = ($step->selected_choice_id ?? null) == ($choice['id'] ?? null); @endphp
                                    <div class="choice-card {{ $isSelected ? 'selected' : '' }}">
                                        @if($isSelected)
                                            <span style="color:#22c55e;font-weight:700;">✓</span>
                                        @else
                                            <span style="color:#d1d5db;">○</span>
                                        @endif
                                        <span>
                                            <span style="font-weight:500;margin-right:4px;">{{ $choice['id'] ?? '' }}</span>
                                            {{ Str::limit($choice['text'] ?? '', 100) }}
                                        </span>
                                        @if(isset($choice['impact']))
                                            <span class="score-delta {{ ($choice['impact'] ?? 0) > 0 ? 'positive' : 'negative' }}" style="margin-left:auto;">
                                                {{ ($choice['impact'] ?? 0) > 0 ? '+' : '' }}{{ $choice['impact'] }}
                                            </span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        {{-- Coach Reply --}}
                        @if($step->coach_reply)
                            <div class="coach-advice">
                                <div class="coach-advice-title">🧑‍🏫 Coach Advice</div>
                                <div style="font-size:13px;line-height:1.5;">{{ $step->coach_reply }}</div>
                            </div>
                            @if($step->audio_coach_reply && $step->audio_coach_reply !== $step->coach_reply)
                                <details style="margin-top:6px;">
                                    <summary>🔊 Audio Text</summary>
                                    <div style="margin-top:4px;padding:6px 10px;background:var(--bg-secondary,#f9fafb);border-radius:6px;font-size:12px;">
                                        {{ Str::limit($step->audio_coach_reply, 200) }}
                                    </div>
                                </details>
                            @endif
                        @endif

                        {{-- End Marker --}}
                        @if($step->ended)
                            <div style="margin-top:8px;padding:6px 10px;background:rgba(239,68,68,0.08);border-radius:6px;font-size:12px;color:#dc2626;font-weight:500;">
                                ⏹ Simulation ended at this step.
                            </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>

        @elseif($module === 'lecturer')
            {{-- LECTURER: WhatsApp-Style Chat --}}
            <div class="dp-card" style="padding:0;overflow:hidden;border-radius:12px;">
                <div class="whatsapp-chat-header">
                    <div class="chat-avatar"><span style="font-size:1.5rem;">🧑‍🏫</span></div>
                    <div class="chat-info">
                        <h4 style="margin:0;font-size:1.1rem;font-weight:600;color:#fff;">WAY AI Coach</h4>
                        <small style="opacity:0.85;">
                            {{ $session->subject ?? 'General' }}
                            @if($session->topic) • {{ $session->topic }} @endif
                        </small>
                    </div>
                    <div class="chat-header-stats">
                        <div class="chat-header-stat">
                            <div class="stat-value">{{ $session->lecturerMessages->count() }}</div>
                            <div class="stat-label">{{ __('portal.messages') }}</div>
                        </div>
                    </div>
                </div>
                <div class="whatsapp-chat-container">
                    @php $lastDate = null; @endphp
                    @forelse($session->lecturerMessages as $message)
                        @php
                            $messageDate = $message->created_at_ext ?? $message->created_at;
                            $currentDate = $messageDate?->format('d.m.Y');
                        @endphp
                        @if($lastDate != $currentDate)
                            <div class="chat-date-separator">
                                <span>{{ $messageDate?->format('d M Y') ?? 'No Date' }}</span>
                            </div>
                            @php $lastDate = $currentDate; @endphp
                        @endif
                        <div class="message-row {{ $message->role == 'user' ? 'outgoing' : 'incoming' }}">
                            <div class="whatsapp-bubble {{ $message->role == 'user' ? 'outgoing' : 'incoming' }}">
                                <div class="bubble-author">
                                    @if($message->role == 'user')
                                        👤 Student
                                    @else
                                        🤖 WAY AI Coach
                                    @endif
                                </div>
                                <div class="bubble-content markdown-content" data-content="{{ $message->content }}">
                                    {{ $message->content }}
                                </div>
                                @if($message->role == 'assistant' && ($message->score !== null || $message->theme_message))
                                    <div style="margin-top:4px;font-size:11px;">
                                        @if($message->score !== null)
                                            <span class="dp-badge {{ $message->score == 1 ? 'dp-badge-active' : 'dp-badge-pending' }}" style="font-size:10px;">
                                                Score: {{ $message->score }}
                                            </span>
                                        @endif
                                        @if($message->theme_message)
                                            <span class="dp-badge dp-badge-pending" style="font-size:10px;">{{ $message->theme_message }}</span>
                                        @endif
                                    </div>
                                @endif
                                @if($message->role == 'assistant' && $message->audio_text && $message->audio_text !== $message->content)
                                    <details style="margin-top:4px;">
                                        <summary>🔊 Audio Metni</summary>
                                        <div style="margin-top:4px;padding:4px 8px;background:rgba(255,255,255,0.5);border-radius:4px;font-size:11px;">
                                            {{ Str::limit($message->audio_text, 200) }}
                                        </div>
                                    </details>
                                @endif
                                <div class="bubble-time">
                                    {{ $messageDate?->format('H:i') ?? '--:--' }}
                                    @if($message->role == 'user')
                                        <span style="color:#53bdeb;font-size:0.8rem;">✓✓</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="empty-chat-state">
                            <div style="font-size:4rem;opacity:0.4;margin-bottom:20px;">💬</div>
                            <h5 style="margin-bottom:8px;">No messages yet</h5>
                            <p style="font-size:0.9rem;color:var(--text-muted);">This session has no messages yet.</p>
                        </div>
                    @endforelse
                </div>
            </div>

        @else
            {{-- CHATBOT: WhatsApp-Style Chat --}}
            <div class="dp-card" style="padding:0;overflow:hidden;border-radius:12px;">
                <div class="whatsapp-chat-header" style="background:linear-gradient(135deg, #9C27B0, #7B1FA2) !important;">
                    <div class="chat-avatar"><span style="font-size:1.5rem;">🤖</span></div>
                    <div class="chat-info">
                        <h4 style="margin:0;font-size:1.1rem;font-weight:600;color:#fff;">Study Space AI</h4>
                        <small style="opacity:0.85;">Real-Time WebSocket Chat</small>
                    </div>
                    <div class="chat-header-stats">
                        <div class="chat-header-stat">
                            <div class="stat-value">{{ $session->chatMessages->count() }}</div>
                            <div class="stat-label">{{ __('portal.messages') }}</div>
                        </div>
                    </div>
                </div>
                <div class="whatsapp-chat-container">
                    @php $lastDate = null; @endphp
                    @forelse($session->chatMessages as $message)
                        @php
                            $messageDate = $message->created_at;
                            $currentDate = $messageDate?->format('d.m.Y');
                        @endphp
                        @if($lastDate != $currentDate)
                            <div class="chat-date-separator">
                                <span>{{ $messageDate?->format('d M Y') ?? 'No Date' }}</span>
                            </div>
                            @php $lastDate = $currentDate; @endphp
                        @endif
                        <div class="message-row {{ $message->role == 'user' ? 'outgoing' : 'incoming' }}">
                            <div class="whatsapp-bubble {{ $message->role == 'user' ? 'outgoing' : 'incoming' }}">
                                <div class="bubble-author">
                                    @if($message->role == 'user')
                                        👤 Student
                                    @else
                                        🤖 Study Space AI
                                    @endif
                                </div>
                                @if($message->image_url)
                                    <div style="margin-bottom:6px;">
                                        <a href="{{ $message->image_url }}" target="_blank">
                                            <img src="{{ $message->image_url }}" style="max-width:300px;border-radius:8px;cursor:pointer;" onerror="this.style.display='none'">
                                        </a>
                                        <div style="font-size:10px;color:var(--text-muted);margin-top:2px;">🖼 AI Generated Image</div>
                                    </div>
                                @endif
                                <div class="bubble-content markdown-content" data-content="{{ $message->content }}">
                                    {{ $message->content }}
                                </div>
                                @if($message->role == 'assistant' && $message->audio_text && $message->audio_text !== $message->content)
                                    <details style="margin-top:4px;">
                                        <summary>🔊 Audio Text</summary>
                                        <div style="margin-top:4px;padding:4px 8px;background:rgba(255,255,255,0.5);border-radius:4px;font-size:11px;">
                                            {{ Str::limit($message->audio_text, 200) }}
                                        </div>
                                    </details>
                                @endif
                                <div class="bubble-time">
                                    {{ $messageDate?->format('H:i') ?? '--:--' }}
                                    @if($message->role == 'user')
                                        <span style="color:#53bdeb;font-size:0.8rem;">✓✓</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="empty-chat-state">
                            <div style="font-size:4rem;opacity:0.4;margin-bottom:20px;">🤖</div>
                            <h5 style="margin-bottom:8px;">No messages yet</h5>
                            <p style="font-size:0.9rem;color:var(--text-muted);">This chat session has no messages yet.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        @endif
    </div>
</div>

{{-- Back Button --}}
<div style="margin-top:20px;display:flex;justify-content:space-between;">
    @if($student)
        <a href="{{ route('portal.reports.student', $student) }}" class="dp-btn-ghost">← Back to Student Report</a>
    @else
        <a href="{{ route('portal.reports') }}" class="dp-btn-ghost">← Back to Reports</a>
    @endif
</div>

{{-- Playback Modal (Simulator only) --}}
@if($module === 'simulator')
<div class="playback-overlay" id="playbackOverlay">
    <div class="playback-modal">
        <div class="playback-header">
            <div style="font-weight:600;">▶ Simulation Playback</div>
            <button onclick="document.getElementById('playbackOverlay').classList.remove('active')" style="background:none;border:none;color:#fff;font-size:18px;cursor:pointer;">✕</button>
        </div>
        <div style="text-align:center;padding:16px;">
            <div class="score-display" id="playback-score" style="margin:0 auto;">-</div>
            <div class="playback-progress" style="margin-top:12px;">
                <div class="playback-progress-fill" id="playback-progress"></div>
            </div>
            <div style="font-size:12px;color:var(--text-muted);margin-top:4px;">
                Turn <span id="playback-turn">0</span> / {{ $session->simulatorSteps->count() - 1 }}
            </div>
        </div>
        <div class="playback-body" id="playback-content">
            <p style="text-align:center;color:var(--text-muted);">Click ▶ to start playback</p>
        </div>
        <div class="playback-controls">
            <button id="playback-prev" class="dp-btn-ghost" style="font-size:18px;">⏮</button>
            <button id="playback-play" class="dp-btn" style="font-size:16px;padding:8px 20px;">▶</button>
            <button id="playback-next" class="dp-btn-ghost" style="font-size:18px;">⏭</button>
        </div>
    </div>
</div>
@endif
@endsection

@section('scripts')
@if($module === 'simulator')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Score Chart
    const chartData = @json($chartData);
    if (chartData.length > 0) {
        new Chart(document.getElementById('scoreChart'), {
            type: 'line',
            data: {
                labels: chartData.map(d => 'Turn ' + d.turn),
                datasets: [{
                    label: 'Score',
                    data: chartData.map(d => d.score),
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    fill: true,
                    tension: 0.3,
                    pointRadius: 4,
                    pointBackgroundColor: '#667eea',
                    borderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { min: 0, max: 100, grid: { color: '#f1f5f9' } }, x: { grid: { display: false } } },
                plugins: { legend: { display: false } }
            }
        });
    }

    // Playback
    const steps = @json($session->simulatorSteps->sortBy('turn')->values());
    let currentStep = 0;
    let isPlaying = false;
    let playInterval = null;

    function updatePlayback() {
        const step = steps[currentStep];
        const total = steps.length;
        const progress = ((currentStep + 1) / total) * 100;
        document.getElementById('playback-turn').textContent = step.turn;
        document.getElementById('playback-progress').style.width = progress + '%';
        const scoreEl = document.getElementById('playback-score');
        scoreEl.textContent = step.score_after;
        scoreEl.className = 'score-display ' + (step.score_after >= 70 ? 'score-high' : (step.score_after >= 50 ? 'score-medium' : 'score-low'));

        let html = '<h6 style="font-weight:600;margin-bottom:8px;">Turn ' + step.turn + ' — ' + (step.node_id || '') + '</h6>';
        if (step.node_text) html += '<p style="font-size:13px;line-height:1.5;">' + escapeHtml(step.node_text).substring(0, 300) + '...</p>';
        if (step.selected_choice_id && step.choices) {
            const sel = step.choices.find(c => c.id === step.selected_choice_id);
            if (sel) html += '<div style="padding:8px 12px;background:rgba(34,197,94,0.1);border-radius:6px;margin:8px 0;font-size:13px;"><strong>Choice:</strong> ' + escapeHtml(sel.text || sel.id) + '</div>';
        }
        if (step.coach_reply) html += '<div class="coach-advice"><div class="coach-advice-title">🧑‍🏫 Coach</div><div style="font-size:13px;">' + escapeHtml(step.coach_reply).substring(0, 200) + '...</div></div>';
        document.getElementById('playback-content').innerHTML = html;

        // Highlight timeline item
        document.querySelectorAll('.sim-timeline-item').forEach(el => el.classList.remove('active'));
        const timelineItem = document.getElementById('step-' + step.turn);
        if (timelineItem) {
            timelineItem.classList.add('active');
            timelineItem.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    function escapeHtml(str) {
        if (!str) return '';
        const div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    document.getElementById('playback-play').addEventListener('click', function() {
        if (isPlaying) {
            clearInterval(playInterval);
            this.textContent = '▶';
            isPlaying = false;
        } else {
            this.textContent = '⏸';
            isPlaying = true;
            playInterval = setInterval(() => {
                if (currentStep < steps.length - 1) { currentStep++; updatePlayback(); }
                else { clearInterval(playInterval); document.getElementById('playback-play').textContent = '▶'; isPlaying = false; }
            }, 2000);
        }
    });
    document.getElementById('playback-prev').addEventListener('click', () => { if (currentStep > 0) { currentStep--; updatePlayback(); } });
    document.getElementById('playback-next').addEventListener('click', () => { if (currentStep < steps.length - 1) { currentStep++; updatePlayback(); } });

    document.getElementById('playbackOverlay').addEventListener('click', function(e) {
        if (e.target === this) this.classList.remove('active');
    });
});
</script>
@else
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Markdown rendering for AI messages
    document.querySelectorAll('.whatsapp-bubble.incoming .markdown-content').forEach(function(el) {
        const content = el.getAttribute('data-content') || el.textContent;
        try { el.innerHTML = marked.parse(content); } catch(e) {}
    });
    // Auto-scroll to bottom
    const chatContainer = document.querySelector('.whatsapp-chat-container');
    if (chatContainer) chatContainer.scrollTop = chatContainer.scrollHeight;
});
</script>
@endif
@endsection
