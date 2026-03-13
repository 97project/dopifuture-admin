@extends('portal.app')
@section('title', ($isTr = app()->getLocale() === 'tr') ? 'Simülatör Oturum Detayı' : 'Simulator Session Detail')
@section('page-title', $isTr ? 'Simülatör Oturum Detayı' : 'Simulator Session Detail')

@section('content')
@php $isTr = app()->getLocale() === 'tr'; @endphp

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
    <div>
        <div style="font-size:18px;font-weight:600;">🎮 {{ $isTr ? 'Simülatör Oturum' : 'Simulator Session' }} #{{ Str::limit($sessionId, 8) }}</div>
        @if($student)
        <p style="font-size:13px;color:var(--text-muted);margin:4px 0 0;">{{ $student->name }} {{ $student->surname }}</p>
        @endif
    </div>
    <a href="{{ url()->previous() }}" class="dp-btn-ghost">← {{ $isTr ? 'Geri' : 'Back' }}</a>
</div>

{{-- Summary Card --}}
<div class="dp-card" style="margin-bottom:20px;">
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));gap:16px;text-align:center;">
        <div>
            <div style="font-size:22px;font-weight:800;color:#4364F7;">{{ $summary['score'] ?? $summary['total_score'] ?? '-' }}</div>
            <div style="font-size:11px;color:var(--text-muted);margin-top:2px;">{{ $isTr ? 'Toplam Puan' : 'Total Score' }}</div>
        </div>
        <div>
            <div style="font-size:22px;font-weight:800;color:#10B981;">{{ count($turns) }}</div>
            <div style="font-size:11px;color:var(--text-muted);margin-top:2px;">{{ $isTr ? 'Turn Sayısı' : 'Turns' }}</div>
        </div>
        <div>
            @php
                $threshold = $summary['threshold'] ?? $summary['final_threshold'] ?? $summary['threshold_after'] ?? null;
                $thresholdColor = match(true) {
                    $threshold === 'prosperity' || $threshold === 'refah' => '#10B981',
                    $threshold === 'balance' || $threshold === 'denge' => '#3B82F6',
                    $threshold === 'crisis' || $threshold === 'kriz' => '#F59E0B',
                    $threshold === 'disaster' || $threshold === 'felaket' => '#EF4444',
                    default => '#6B7280',
                };
                $thresholdLabel = match(true) {
                    $threshold === 'prosperity' || $threshold === 'refah' => $isTr ? '✅ Refah' : '✅ Prosperity',
                    $threshold === 'balance' || $threshold === 'denge' => $isTr ? '⚖️ Denge' : '⚖️ Balance',
                    $threshold === 'crisis' || $threshold === 'kriz' => $isTr ? '⚠️ Kriz' : '⚠️ Crisis',
                    $threshold === 'disaster' || $threshold === 'felaket' => $isTr ? '🔴 Felaket' : '🔴 Disaster',
                    default => $threshold ?? '-',
                };
            @endphp
            <div style="font-size:18px;font-weight:700;color:{{ $thresholdColor }};">{{ $thresholdLabel }}</div>
            <div style="font-size:11px;color:var(--text-muted);margin-top:2px;">{{ $isTr ? 'Son Durum' : 'Final Status' }}</div>
        </div>
        <div>
            <div style="font-size:22px;font-weight:800;color:#8B5CF6;">{{ $summary['duration_seconds'] ?? $summary['duration'] ?? '-' }}</div>
            <div style="font-size:11px;color:var(--text-muted);margin-top:2px;">{{ $isTr ? 'Süre (s)' : 'Duration (s)' }}</div>
        </div>
    </div>
</div>

{{-- Metric Evolution Chart (Faz 6 — SVG inline line chart) --}}
@if(count($turns) > 1)
@php
    $metricKeys = ['health' => '#EF4444', 'resource' => '#10B981', 'ethics' => '#F59E0B', 'adaptation' => '#3B82F6'];
    $turnCount = count($turns);
    $chartW = 620;
    $chartH = 140;
    $padX = 30;
    $padY = 10;
    $usableW = $chartW - $padX * 2;
    $usableH = $chartH - $padY * 2;
    $polylines = [];
    foreach ($metricKeys as $mk => $mc) {
        $points = [];
        foreach ($turns as $ti => $t) {
            $mv = $t['metrics'][$mk] ?? $t['metrics_after'][$mk] ?? $t['delta'][$mk] ?? null;
            if ($mv === null && isset($t['score_after'])) $mv = $t['score_after'];
            $mv = $mv ?? 50;
            $x = $padX + ($ti / max(1, $turnCount - 1)) * $usableW;
            $y = $padY + (1 - min(100, max(0, $mv)) / 100) * $usableH;
            $points[] = round($x, 1) . ',' . round($y, 1);
        }
        $polylines[$mk] = ['color' => $mc, 'points' => implode(' ', $points)];
    }
@endphp
<div class="dp-card" style="margin-bottom:20px;">
    <div class="dp-card-title" style="margin-bottom:12px;">📈 {{ $isTr ? 'Metrik Evrimi (Turn Bazlı)' : 'Metric Evolution (Per Turn)' }}</div>
    <div style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:8px;">
        @foreach($metricKeys as $mk => $mc)
        <span style="font-size:10px;display:flex;align-items:center;gap:4px;">
            <span style="width:10px;height:3px;background:{{ $mc }};border-radius:2px;"></span>
            {{ ucfirst($mk) }}
        </span>
        @endforeach
    </div>
    <div style="overflow-x:auto;">
        <svg viewBox="0 0 {{ $chartW }} {{ $chartH }}" style="width:100%;max-width:{{ $chartW }}px;height:auto;">
            {{-- Grid lines --}}
            @for($gi = 0; $gi <= 4; $gi++)
            <line x1="{{ $padX }}" y1="{{ $padY + ($gi / 4) * $usableH }}" x2="{{ $chartW - $padX }}" y2="{{ $padY + ($gi / 4) * $usableH }}" stroke="#E5E7EB" stroke-width="0.5" />
            <text x="{{ $padX - 4 }}" y="{{ $padY + ($gi / 4) * $usableH + 3 }}" font-size="8" fill="#9CA3AF" text-anchor="end">{{ 100 - $gi * 25 }}</text>
            @endfor
            {{-- Polylines --}}
            @foreach($polylines as $pk => $pl)
            <polyline points="{{ $pl['points'] }}" fill="none" stroke="{{ $pl['color'] }}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            @endforeach
            {{-- Turn labels --}}
            @foreach($turns as $ti => $t)
            @if($turnCount <= 20 || $ti % max(1, intval($turnCount / 10)) === 0)
            <text x="{{ $padX + ($ti / max(1, $turnCount - 1)) * $usableW }}" y="{{ $chartH - 2 }}" font-size="7" fill="#9CA3AF" text-anchor="middle">T{{ $ti + 1 }}</text>
            @endif
            @endforeach
        </svg>
    </div>
</div>
@endif

{{-- Turns Timeline --}}
@if(count($turns) > 0)
<div class="dp-card">
    <div class="dp-card-title">📋 {{ $isTr ? 'Turn Bazlı Detay' : 'Turn-by-Turn Detail' }}</div>
    @foreach($turns as $idx => $turn)
    @php
        $turnThreshold = $turn['threshold_after'] ?? $turn['threshold'] ?? null;
        $turnThColor = match(true) {
            $turnThreshold === 'prosperity' || $turnThreshold === 'refah' => '#10B981',
            $turnThreshold === 'balance' || $turnThreshold === 'denge' => '#3B82F6',
            $turnThreshold === 'crisis' || $turnThreshold === 'kriz' => '#F59E0B',
            $turnThreshold === 'disaster' || $turnThreshold === 'felaket' => '#EF4444',
            default => '#6B7280',
        };
        $scoreChange = $turn['score_change'] ?? $turn['delta_score'] ?? 0;
        $choices = $turn['choices'] ?? [];
        $choiceMade = $turn['choice_made'] ?? $turn['selected_choice'] ?? null;
    @endphp
    <div style="padding:16px 0;{{ $idx > 0 ? 'border-top:1px solid var(--color-row-border,#eee);' : '' }}">
        <div style="display:flex;align-items:flex-start;gap:12px;margin-bottom:8px;">
            <div style="flex-shrink:0;width:32px;height:32px;border-radius:50%;background:{{ $turnThColor }};color:#fff;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;">{{ $idx + 1 }}</div>
            <div style="flex:1;">
                {{-- Narrative --}}
                @if($turn['narrative'] ?? $turn['text'] ?? $turn['description'] ?? null)
                <div style="font-size:14px;color:#030719;line-height:1.5;margin-bottom:8px;">
                    {{ $turn['narrative'] ?? $turn['text'] ?? $turn['description'] }}
                </div>
                @endif

                {{-- Choices --}}
                @if(count($choices) > 0)
                <div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:8px;">
                    @foreach($choices as $ci => $choice)
                    @php $isSelected = ($choiceMade !== null && (is_numeric($choiceMade) ? $ci == $choiceMade : ($choice['id'] ?? $ci) == $choiceMade)); @endphp
                    <span style="padding:6px 12px;border-radius:8px;font-size:12px;border:1px solid {{ $isSelected ? $turnThColor : '#E5E7EB' }};background:{{ $isSelected ? $turnThColor.'15' : '#F9FAFB' }};color:{{ $isSelected ? $turnThColor : '#6B7280' }};font-weight:{{ $isSelected ? '600' : '400' }};">
                        {{ is_array($choice) ? ($choice['text'] ?? $choice['label'] ?? json_encode($choice)) : $choice }}
                        @if($isSelected) ✓ @endif
                    </span>
                    @endforeach
                </div>
                @endif

                {{-- Score & Metrics Row --}}
                <div style="display:flex;gap:16px;align-items:center;font-size:12px;">
                    @if($scoreChange != 0)
                    <span style="font-weight:600;color:{{ $scoreChange > 0 ? '#10B981' : '#EF4444' }};">
                        {{ $scoreChange > 0 ? '+' : '' }}{{ $scoreChange }} {{ $isTr ? 'puan' : 'pts' }}
                    </span>
                    @endif

                    @if($turn['score_after'] ?? null)
                    <span style="color:var(--text-muted);">{{ $isTr ? 'Toplam:' : 'Total:' }} <strong>{{ $turn['score_after'] }}</strong></span>
                    @endif

                    @if($turnThreshold)
                    <span style="color:{{ $turnThColor }};font-weight:500;">{{ $turnThreshold }}</span>
                    @endif

                    {{-- Delta metrics --}}
                    @if($turn['delta'] ?? null)
                    @foreach((array)$turn['delta'] as $mk => $mv)
                    <span style="color:var(--text-muted);font-size:11px;">{{ $mk }}: <strong style="color:{{ $mv > 0 ? '#10B981' : ($mv < 0 ? '#EF4444' : 'inherit') }}">{{ $mv > 0 ? '+' : '' }}{{ $mv }}</strong></span>
                    @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@else
<div class="dp-card" style="text-align:center;padding:48px;">
    <div style="font-size:32px;margin-bottom:8px;">📭</div>
    <p style="color:var(--text-muted);">{{ $isTr ? 'Turn verisi bulunamadı.' : 'No turn data found.' }}</p>
</div>
@endif
@endsection
