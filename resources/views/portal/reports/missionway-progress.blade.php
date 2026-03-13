@extends('portal.app')
@section('title', 'Mission Way — İlerleme Matrisi')
@section('page-title', 'Mission Way — Okul Geneli İlerleme')

@section('content')
@php $isTr = app()->getLocale() === 'tr'; @endphp

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
    <div>
        <h2 style="font-size:20px;font-weight:700;margin:0;">🎯 {{ $isTr ? 'Mission Way İlerleme Matrisi' : 'Mission Way Progress Matrix' }}</h2>
        <p style="font-size:13px;color:var(--text-muted);margin:4px 0 0;">{{ $isTr ? 'Öğrenci × Simülasyon ilerleme durumu' : 'Student × Simulation progress overview' }}</p>
    </div>
    <a href="{{ route('portal.reports.app', ['app' => 'mission-way']) }}" class="dp-btn-ghost">← {{ $isTr ? 'Geri' : 'Back' }}</a>
</div>

@if(count($simulations) === 0)
<div class="dp-card" style="text-align:center;padding:48px;">
    <div style="font-size:32px;margin-bottom:8px;">📭</div>
    <p style="color:var(--text-muted);">{{ $isTr ? 'Henüz simülasyon verisi bulunamadı.' : 'No simulation data found yet.' }}</p>
</div>
@else
<div class="dp-card" style="overflow-x:auto;">
    <table class="dp-table" style="min-width:700px;">
        <thead>
            <tr>
                <th style="position:sticky;left:0;background:var(--bg-card,#fff);z-index:2;min-width:160px;">{{ $isTr ? 'Öğrenci' : 'Student' }}</th>
                @foreach($simulations as $sim)
                <th style="text-align:center;font-size:11px;max-width:100px;white-space:normal;line-height:1.2;">
                    {{ $sim['name'] ?? $sim['title'] ?? 'Sim #' . ($sim['id'] ?? '?') }}
                </th>
                @endforeach
                <th style="text-align:center;">{{ $isTr ? 'Toplam' : 'Total' }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($progressMatrix as $row)
            @php
                $student = $row['student'];
                $progress = $row['progress'] ?? [];
                $completedCount = 0;
            @endphp
            <tr>
                <td style="position:sticky;left:0;background:var(--bg-card,#fff);z-index:1;font-weight:500;">
                    <div style="display:flex;align-items:center;gap:8px;">
                        <div style="width:28px;height:28px;border-radius:50%;background:linear-gradient(135deg,#4364F7,#6C63FF);color:#fff;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:600;">
                            {{ strtoupper(substr($student->name ?? '',0,1) . substr($student->surname ?? '',0,1)) }}
                        </div>
                        <span style="font-size:13px;">{{ $student->name }} {{ $student->surname }}</span>
                    </div>
                </td>
                @foreach($simulations as $sim)
                @php
                    // Match by simulation ID or version IDs
                    $simId = $sim['id'] ?? null;
                    $found = false;
                    $score = null;
                    $completed = false;
                    foreach ($progress as $svId => $p) {
                        // Check if this progress matches this simulation
                        if (($p['simulationId'] ?? null) == $simId || ($p['simulation_id'] ?? null) == $simId) {
                            $found = true;
                            $score = $p['currentScore'] ?? $p['current_score'] ?? null;
                            $completed = !empty($p['completedAt'] ?? $p['completed_at'] ?? null);
                            break;
                        }
                    }
                    // Also try matching by simulationVersionId presence
                    if (!$found && !empty($progress)) {
                        // If simulations have version IDs, try loose match
                        foreach ($progress as $svId => $p) {
                            $found = true;
                            $score = $p['currentScore'] ?? $p['current_score'] ?? null;
                            $completed = !empty($p['completedAt'] ?? $p['completed_at'] ?? null);
                            break;
                        }
                    }
                    if ($completed) $completedCount++;
                @endphp
                <td style="text-align:center;">
                    @if($completed)
                        <span style="display:inline-block;padding:2px 8px;border-radius:999px;background:#10B981;color:#fff;font-size:11px;font-weight:600;">
                            {{ $score !== null ? $score : '✓' }}
                        </span>
                    @elseif($found)
                        <span style="display:inline-block;padding:2px 8px;border-radius:999px;background:#F59E0B;color:#fff;font-size:11px;font-weight:600;">
                            {{ $score !== null ? $score : '…' }}
                        </span>
                    @else
                        <span style="color:var(--text-muted);font-size:11px;">—</span>
                    @endif
                </td>
                @endforeach
                <td style="text-align:center;font-weight:600;">
                    <span style="color:{{ $completedCount > 0 ? '#10B981' : 'var(--text-muted)' }};">{{ $completedCount }}/{{ count($simulations) }}</span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- Summary Stats --}}
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:16px;margin-top:20px;">
    <div class="dp-card" style="text-align:center;padding:20px;">
        <div style="font-size:28px;font-weight:800;color:#4364F7;">{{ count($students) }}</div>
        <div style="font-size:12px;color:var(--text-muted);margin-top:4px;">{{ $isTr ? 'Öğrenci' : 'Students' }}</div>
    </div>
    <div class="dp-card" style="text-align:center;padding:20px;">
        <div style="font-size:28px;font-weight:800;color:#10B981;">{{ count($simulations) }}</div>
        <div style="font-size:12px;color:var(--text-muted);margin-top:4px;">{{ $isTr ? 'Simülasyon' : 'Simulations' }}</div>
    </div>
    <div class="dp-card" style="text-align:center;padding:20px;">
        @php
            $totalCompleted = 0;
            $totalPossible = count($students) * count($simulations);
            foreach ($progressMatrix as $row) {
                foreach ($row['progress'] ?? [] as $p) {
                    if (!empty($p['completedAt'] ?? $p['completed_at'] ?? null)) $totalCompleted++;
                }
            }
            $overallRate = $totalPossible > 0 ? round(($totalCompleted / $totalPossible) * 100) : 0;
        @endphp
        <div style="font-size:28px;font-weight:800;color:#8B5CF6;">%{{ $overallRate }}</div>
        <div style="font-size:12px;color:var(--text-muted);margin-top:4px;">{{ $isTr ? 'Genel Tamamlanma' : 'Overall Completion' }}</div>
    </div>
</div>
@endif

@endsection
