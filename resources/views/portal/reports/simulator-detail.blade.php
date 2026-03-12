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
