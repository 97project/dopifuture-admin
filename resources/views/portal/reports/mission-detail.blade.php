@extends('portal.app')
@section('title', ($isTr ?? false) ? 'Görev Detay' : 'Mission Detail')
@section('page-title', 'Mission WAY')
@php $isTr = app()->getLocale() === 'tr'; @endphp

@section('content')

    {{-- ═══ BACK BUTTON — Figma node 1251-18401 ═══ --}}
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
        <a href="{{ route('portal.reports.app', 'mission-way') }}" style="display:flex;align-items:center;gap:6px;text-decoration:none;color:var(--color-txt-muted);font-size:13px;">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            {{ $isTr ? 'Geri Dön' : 'Back' }}
        </a>
        <span style="font-size:18px;font-weight:600;">{{ $mission->title ?? 'Keşfet - Dijital Harita' }}</span>
    </div>

    {{-- ═══ MISSION INFO CARD ═══ --}}
    <div class="dp-card" style="margin-bottom:20px;">
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;">
            <div>
                <span style="font-size:12px;color:var(--color-txt-muted);">{{ $isTr ? 'Durum' : 'Status' }}</span>
                <div style="margin-top:4px;">
                    <span class="dp-badge dp-badge-success">{{ $mission->status ?? ($isTr ? 'Tamamlandı' : 'Completed') }}</span>
                </div>
            </div>
            <div>
                <span style="font-size:12px;color:var(--color-txt-muted);">{{ $isTr ? 'Zorluk' : 'Difficulty' }}</span>
                <div style="margin-top:4px;">
                    <span class="dp-badge" style="background:#DBEAFE;color:#2563EB;">{{ $mission->difficulty ?? 'Easy' }}</span>
                </div>
            </div>
            <div>
                <span style="font-size:12px;color:var(--color-txt-muted);">{{ $isTr ? 'Oluşturulma' : 'Created' }}</span>
                <div style="margin-top:4px;font-size:13px;font-weight:500;">{{ $mission->created ?? '28.02.2026' }}</div>
            </div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
        {{-- LEFT: Student performance --}}
        <div class="dp-card" style="padding:0;">
            <div style="padding:16px 20px;border-bottom:1px solid var(--color-row-border);font-weight:600;font-size:14px;">
                {{ $isTr ? 'Öğrenci Performansı' : 'Student Performance' }}
            </div>
            <div style="overflow-x:auto;">
            <table class="dp-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>{{ $isTr ? 'Öğrenci' : 'Students' }}</th>
                        <th>{{ $isTr ? 'Sınıf' : 'Grade' }}</th>
                        <th>{{ $isTr ? 'Tamamlanan' : 'Total Completed' }}</th>
                        <th style="color:#ef4444;">❤️ {{ $isTr ? 'Sağlık' : 'Health Point' }}</th>
                        <th style="color:#3b82f6;">📦 {{ $isTr ? 'Kaynak' : 'Resource Point' }}</th>
                        <th style="color:#8b5cf6;">⚖️ {{ $isTr ? 'Etik' : 'Ethics Point' }}</th>
                        <th style="color:#22c55e;">🔄 {{ $isTr ? 'Adaptasyon' : 'Adaptation Point' }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $i => $s)
                    <tr>
                        <td class="muted">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</td>
                        <td>
                            <div class="dp-td-avatar">
                                <div class="av">{{ strtoupper(substr($s->name,0,1).substr($s->surname,0,1)) }}</div>
                                <span style="font-weight:500;">{{ $s->name }} {{ $s->surname }}</span>
                            </div>
                        </td>
                        <td>{{ $s->grade }}</td>
                        <td>{{ $s->completed }}</td>
                        <td>{{ $s->health }}/{{ $s->total_missions }}</td>
                        <td>{{ $s->resource }}/{{ $s->total_missions }}</td>
                        <td>{{ $s->ethics }}/{{ $s->total_missions }}</td>
                        <td>{{ $s->adaptation }}/{{ $s->total_missions }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
            {{-- Pagination --}}
            <div style="display:flex;justify-content:space-between;align-items:center;padding:16px 20px;font-size:12px;">
                <span style="color:var(--color-txt-muted);cursor:default;">{{ $isTr ? 'Önceki' : 'Previous' }}</span>
                <span style="color:var(--color-txt-muted);">{{ $isTr ? 'Sayfa' : 'Page' }} 1 {{ $isTr ? '/' : 'of' }} 3</span>
                <a href="#" class="dp-btn" style="font-size:12px;padding:6px 16px;">{{ $isTr ? 'Sonraki' : 'Next' }}</a>
            </div>
        </div>

        {{-- RIGHT: Mission details --}}
        <div>
            <div class="dp-card" style="margin-bottom:16px;">
                <div style="font-weight:600;font-size:14px;margin-bottom:12px;">{{ $isTr ? 'Görev Bilgileri' : 'Mission Info' }}</div>
                <div style="font-size:13px;color:var(--color-txt-muted);line-height:1.6;margin-bottom:16px;">
                    {{ $mission->description ?? ($isTr ? 'Bu görevde öğrenciler dijital harita oluşturma sürecini öğrenecek ve pratik yapacaklar.' : 'In this mission students will learn digital mapping.') }}
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div style="background:var(--color-input-bg);padding:12px;border-radius:8px;">
                        <div style="font-size:11px;color:var(--color-txt-muted);">{{ $isTr ? 'Tamamlanma Oranı' : 'Completion Rate' }}</div>
                        <div style="font-size:20px;font-weight:700;color:var(--color-primary);">{{ $mission->completion_rate ?? '72' }}%</div>
                    </div>
                    <div style="background:var(--color-input-bg);padding:12px;border-radius:8px;">
                        <div style="font-size:11px;color:var(--color-txt-muted);">{{ $isTr ? 'Ort. Puan' : 'Avg Score' }}</div>
                        <div style="font-size:20px;font-weight:700;color:var(--color-primary);">{{ $mission->avg_score ?? '74.3' }}</div>
                    </div>
                </div>
            </div>
            <div class="dp-card">
                <div style="font-weight:600;font-size:14px;margin-bottom:12px;">{{ $isTr ? 'Son Aktiviteler' : 'Recent Activities' }}</div>
                @foreach([['Elif Demir', '2 saat önce', 'Tamamlandı'], ['Ahmet Çelik', '3 saat önce', 'Devam Ediyor'], ['Fatma Şahin', '5 saat önce', 'Tamamlandı']] as $act)
                <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid var(--color-row-border);">
                    <span style="font-size:13px;font-weight:500;">{{ $act[0] }}</span>
                    <span style="font-size:12px;color:var(--color-txt-muted);">{{ $act[1] }}</span>
                    <span class="dp-badge {{ $act[2] === 'Tamamlandı' ? 'dp-badge-success' : 'dp-badge-warning' }}">{{ $act[2] }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

@endsection
