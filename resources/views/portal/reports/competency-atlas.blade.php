@extends('portal.app')
@section('title', ($isTr ?? false) ? 'Yetkinlik Atlası' : 'Competency Atlas')
@section('page-title', ($isTr ?? false) ? 'Yetkinlik Atlası' : 'Competency Atlas')
@php $isTr = app()->getLocale() === 'tr'; @endphp

@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
    <div>
        <h2 style="font-size:20px;font-weight:700;margin:0;">🧠 {{ $isTr ? 'Yetkinlik Atlası' : 'Competency Atlas' }}</h2>
        <p style="font-size:13px;color:var(--text-muted);margin:4px 0 0;">{{ $isTr ? '12 yetkinlik alanında öğrenci performansı' : 'Student performance across 12 competency areas' }}</p>
    </div>
    <a href="{{ url()->previous() }}" class="dp-btn-ghost">← {{ $isTr ? 'Geri' : 'Back' }}</a>
</div>

{{-- Student Header --}}
@if(isset($student))
<div class="dp-card" style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
    <div style="width:44px;height:44px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--primary-deep));color:#fff;display:flex;align-items:center;justify-content:center;font-size:16px;font-weight:600;">{{ strtoupper(substr($student->name,0,1).substr($student->surname??'',0,1)) }}</div>
    <div>
        <div style="font-weight:600;font-size:15px;">{{ $student->name }} {{ $student->surname }}</div>
        <span style="font-size:12px;color:var(--text-muted);">{{ $student->email }}</span>
    </div>
</div>
@endif

{{-- Radar Chart Preview (CSS-based) --}}
@if(!empty($competencyScores))
<div class="dp-card" style="margin-bottom:20px;">
    <div style="font-size:14px;font-weight:700;margin-bottom:12px;">{{ $isTr ? 'Yetkinlik Özeti' : 'Competency Summary' }}</div>
    <div style="display:flex;flex-wrap:wrap;gap:8px;">
        @foreach($competencyScores as $ck => $cv)
        @php $cPct = min(100, max(0, $cv['score'])); @endphp
        <div style="flex:1;min-width:120px;max-width:180px;">
            <div style="display:flex;align-items:center;gap:6px;margin-bottom:4px;">
                <span style="font-size:14px;">{{ $cv['icon'] }}</span>
                <span style="font-size:10px;font-weight:600;color:#374151;">{{ $cv['name'] }}</span>
            </div>
            <div style="height:6px;background:#f1f1f1;border-radius:3px;overflow:hidden;">
                <div style="height:100%;width:{{ $cPct }}%;background:{{ $cv['color'] }};border-radius:3px;transition:width 0.5s;"></div>
            </div>
            <div style="font-size:9px;color:var(--text-muted);margin-top:2px;text-align:right;">{{ round($cPct) }}%</div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- 12 Competency Cards --}}
@php
$competencies = [
    ['key' => 'emotional', 'icon' => '❤️', 'color' => '#EF4444',
     'name' => $isTr ? 'Duygusal Zeka' : 'Emotional Intelligence',
     'desc' => $isTr ? 'Empati, duygu yönetimi ve sosyal farkındalık becerileri' : 'Empathy, emotion management and social awareness skills'],
    ['key' => 'community', 'icon' => '👥', 'color' => '#3B82F6',
     'name' => $isTr ? 'Topluluk & İşbirliği' : 'Community & Collaboration',
     'desc' => $isTr ? 'Takım çalışması, liderlik ve topluluk katılımı' : 'Teamwork, leadership and community participation'],
    ['key' => 'nature', 'icon' => '🌿', 'color' => '#10B981',
     'name' => $isTr ? 'Doğa & Tarım' : 'Nature & Agriculture',
     'desc' => $isTr ? 'Doğa bilinci, sürdürülebilirlik ve çevre koruma' : 'Environmental awareness, sustainability and conservation'],
    ['key' => 'art', 'icon' => '🎨', 'color' => '#8B5CF6',
     'name' => $isTr ? 'Sanat & Yaratıcılık' : 'Art & Creativity',
     'desc' => $isTr ? 'Yaratıcı düşünme, estetik duyarlılık ve sanatsal ifade' : 'Creative thinking, aesthetic sensitivity and artistic expression'],
    ['key' => 'technology', 'icon' => '🤖', 'color' => '#6366F1',
     'name' => $isTr ? 'Teknoloji & AI' : 'Technology & AI',
     'desc' => $isTr ? 'Dijital okuryazarlık, yapay zeka anlayışı ve teknoloji kullanımı' : 'Digital literacy, AI understanding and technology use'],
    ['key' => 'science', 'icon' => '🪐', 'color' => '#0EA5E9',
     'name' => $isTr ? 'Bilim & Kuantum' : 'Science & Quantum',
     'desc' => $isTr ? 'Bilimsel düşünme, merak ve keşif becerisi' : 'Scientific thinking, curiosity and discovery skills'],
    ['key' => 'language', 'icon' => '💬', 'color' => '#F59E0B',
     'name' => $isTr ? 'Dil & Kültür' : 'Language & Culture',
     'desc' => $isTr ? 'Dil becerileri, kültürlerarası iletişim ve farkındalık' : 'Language skills, intercultural communication and awareness'],
    ['key' => 'critical', 'icon' => '💡', 'color' => '#F97316',
     'name' => $isTr ? 'Eleştirel Düşünme' : 'Critical Thinking',
     'desc' => $isTr ? 'Analitik düşünme, problem çözme ve mantıksal akıl yürütme' : 'Analytical thinking, problem solving and logical reasoning'],
    ['key' => 'philosophy', 'icon' => '📖', 'color' => '#EC4899',
     'name' => $isTr ? 'Felsefe & Hikaye' : 'Philosophy & Story',
     'desc' => $isTr ? 'Felsefi sorgulama, öykü anlatımı ve anlam arayışı' : 'Philosophical inquiry, storytelling and meaning-making'],
    ['key' => 'body', 'icon' => '🚶', 'color' => '#14B8A6',
     'name' => $isTr ? 'Beden & Hareket' : 'Body & Movement',
     'desc' => $isTr ? 'Fiziksel aktivite, beden farkındalığı ve motor beceriler' : 'Physical activity, body awareness and motor skills'],
    ['key' => 'wellbeing', 'icon' => '🌸', 'color' => '#D946EF',
     'name' => $isTr ? 'İyi Oluş & Farkındalık' : 'Wellbeing & Mindfulness',
     'desc' => $isTr ? 'Ruhsal sağlık, farkındalık ve stres yönetimi' : 'Mental health, mindfulness and stress management'],
    ['key' => 'future', 'icon' => '🚀', 'color' => '#4364F7',
     'name' => $isTr ? 'Geleceğe Hazırlık' : 'Future Preparation',
     'desc' => $isTr ? 'Kariyer planlama, girişimcilik ve gelecek vizyonu' : 'Career planning, entrepreneurship and future vision'],
];
@endphp

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(250px,1fr));gap:14px;">
    @foreach($competencies as $comp)
    @php $cScore = $competencyScores[$comp['key']]['score'] ?? 0; @endphp
    <div class="dp-card" style="padding:18px;border-top:3px solid {{ $comp['color'] }};transition:transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform=''">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
            <div style="width:36px;height:36px;border-radius:10px;background:{{ $comp['color'] }}15;display:flex;align-items:center;justify-content:center;font-size:18px;">{{ $comp['icon'] }}</div>
            <div style="flex:1;">
                <div style="font-size:13px;font-weight:700;color:#111;">{{ $comp['name'] }}</div>
            </div>
            <div style="font-size:18px;font-weight:800;color:{{ $comp['color'] }};">{{ $cScore }}<span style="font-size:10px;color:var(--text-muted);">/100</span></div>
        </div>
        <p style="font-size:11px;color:var(--text-muted);line-height:1.5;margin:0 0 10px;">{{ $comp['desc'] }}</p>
        <div style="height:6px;background:#f1f1f1;border-radius:3px;overflow:hidden;">
            <div style="height:100%;width:{{ min(100, $cScore) }}%;background:{{ $comp['color'] }};border-radius:3px;transition:width 0.5s ease;"></div>
        </div>
    </div>
    @endforeach
</div>
@endsection
