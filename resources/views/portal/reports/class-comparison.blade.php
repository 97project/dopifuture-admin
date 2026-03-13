@extends('portal.app')
@section('title', 'Sınıf Karşılaştırma')
@section('page-title', 'Sınıf Performans Karşılaştırma')

@section('content')
@php $isTr = app()->getLocale() === 'tr'; @endphp

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
    <div>
        <h2 style="font-size:20px;font-weight:700;margin:0;">📊 {{ $isTr ? 'Sınıf Performans Karşılaştırma' : 'Class Performance Comparison' }}</h2>
        <p style="font-size:13px;color:var(--text-muted);margin:4px 0 0;">{{ $isTr ? 'Tüm sınıfların uygulama bazlı performans matrisi' : 'All classes application-level performance matrix' }}</p>
    </div>
    <a href="{{ route('portal.reports') }}" class="dp-btn-ghost">← {{ $isTr ? 'Geri' : 'Back' }}</a>
</div>

@if(count($matrix) === 0)
<div class="dp-card" style="text-align:center;padding:48px;">
    <div style="font-size:32px;margin-bottom:8px;">📭</div>
    <p style="color:var(--text-muted);">{{ $isTr ? 'Henüz karşılaştırılacak sınıf verisi yok.' : 'No class data available for comparison.' }}</p>
</div>
@else
<div class="dp-card" style="overflow-x:auto;">
    <table class="dp-table" style="min-width:600px;">
        <thead>
            <tr>
                <th style="position:sticky;left:0;background:var(--bg-card,#fff);z-index:2;min-width:140px;">{{ $isTr ? 'Sınıf' : 'Class' }}</th>
                <th style="text-align:center;">{{ $isTr ? 'Öğrenci' : 'Students' }}</th>
                @foreach($apps as $app)
                <th style="text-align:center;font-size:11px;min-width:100px;">
                    {{ $app->name }}
                    <div style="font-size:9px;color:var(--text-muted);">{{ $isTr ? 'Tamamlanma' : 'Completion' }}</div>
                </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($matrix as $row)
            @php $class = $row['class']; @endphp
            <tr>
                <td style="position:sticky;left:0;background:var(--bg-card,#fff);z-index:1;font-weight:600;">
                    <a href="{{ route('portal.reports.class', $class->id) }}" style="color:inherit;text-decoration:none;">{{ $class->name }}</a>
                </td>
                <td style="text-align:center;font-weight:500;">{{ $class->student_count ?? 0 }}</td>
                @foreach($apps as $app)
                @php
                    $stat = $row['apps'][$app->slug] ?? ['completion_rate' => 0, 'avg_score' => null];
                    $rate = $stat['completion_rate'];
                    $rateColor = $rate >= 80 ? '#10B981' : ($rate >= 40 ? '#F59E0B' : '#EF4444');
                @endphp
                <td style="text-align:center;">
                    <div style="display:flex;flex-direction:column;align-items:center;gap:4px;">
                        <div style="width:50px;height:6px;background:#f1f1f1;border-radius:3px;overflow:hidden;">
                            <div style="height:100%;width:{{ $rate }}%;background:{{ $rateColor }};border-radius:3px;"></div>
                        </div>
                        <span style="font-size:12px;font-weight:700;color:{{ $rateColor }};">%{{ $rate }}</span>
                        @if($stat['avg_score'] !== null)
                        <span style="font-size:10px;color:var(--text-muted);">Ø {{ $stat['avg_score'] }}</span>
                        @endif
                    </div>
                </td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- Best / Worst Highlights --}}
@php
    $bestClass = null;
    $worstClass = null;
    $bestRate = -1;
    $worstRate = 101;
    foreach ($matrix as $row) {
        $avgRate = 0;
        $cnt = 0;
        foreach ($row['apps'] as $s) {
            if ($s['total'] > 0) {
                $avgRate += $s['completion_rate'];
                $cnt++;
            }
        }
        $avg = $cnt > 0 ? $avgRate / $cnt : 0;
        if ($avg > $bestRate) { $bestRate = $avg; $bestClass = $row['class']->name; }
        if ($avg < $worstRate) { $worstRate = $avg; $worstClass = $row['class']->name; }
    }
@endphp
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:20px;">
    <div class="dp-card" style="padding:20px;border-left:4px solid #10B981;">
        <div style="font-size:11px;font-weight:600;color:var(--text-muted);margin-bottom:4px;">🏆 {{ $isTr ? 'En İyi Performans' : 'Best Performance' }}</div>
        <div style="font-size:18px;font-weight:700;color:#10B981;">{{ $bestClass ?? '-' }}</div>
        <div style="font-size:12px;color:var(--text-muted);">{{ $isTr ? 'Ortalama tamamlanma' : 'Avg completion' }}: %{{ round($bestRate) }}</div>
    </div>
    <div class="dp-card" style="padding:20px;border-left:4px solid #EF4444;">
        <div style="font-size:11px;font-weight:600;color:var(--text-muted);margin-bottom:4px;">⚠️ {{ $isTr ? 'Gelişime Açık' : 'Needs Improvement' }}</div>
        <div style="font-size:18px;font-weight:700;color:#EF4444;">{{ $worstClass ?? '-' }}</div>
        <div style="font-size:12px;color:var(--text-muted);">{{ $isTr ? 'Ortalama tamamlanma' : 'Avg completion' }}: %{{ round($worstRate) }}</div>
    </div>
</div>
@endif

@endsection
