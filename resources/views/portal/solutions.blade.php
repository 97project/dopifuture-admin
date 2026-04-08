@extends('portal.layout')
@section('title', app()->getLocale() === 'tr' ? 'Çözümlerimiz — DopiFuture' : 'Solutions — DopiFuture')
@section('meta_description', app()->getLocale() === 'tr' ? 'DopiFuture dijital eğitim uygulamaları' : 'DopiFuture digital education applications')

@section('content')
<style>
/* ═══════════════════════════════════════════
   SOLUTIONS PAGE
   ═══════════════════════════════════════════ */
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(40px); }
    to   { opacity: 1; transform: translateY(0); }
}
@keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-12px); } }
.reveal { opacity: 0; transform: translateY(40px); transition: all 0.8s cubic-bezier(0.16,1,0.3,1); }
.reveal.visible { opacity: 1; transform: translateY(0); }
.reveal-delay-1 { transition-delay: 0.15s; }
.reveal-delay-2 { transition-delay: 0.3s; }

@media (prefers-reduced-motion: reduce) { .reveal { transition: none; opacity: 1; transform: none; } }

/* Hero */
.solutions-hero {
    text-align: center; padding: 4rem 0 3rem; position: relative;
}
.solutions-hero h1 {
    font-size: 3rem; font-weight: 900; color: white;
    letter-spacing: -0.035em; margin-bottom: 1rem; line-height: 1.15;
}
.solutions-hero h1 span {
    background: linear-gradient(135deg, #60a5fa, #c084fc);
    -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
}
.solutions-hero p {
    color: var(--gray-400); font-size: 1.1rem; max-width: 600px;
    margin: 0 auto; line-height: 1.8;
}

/* App Logo Parade */
.logo-parade {
    display: flex; justify-content: center; gap: 2.5rem;
    flex-wrap: wrap; margin: 3rem 0; padding: 2rem 0;
    border-top: 1px solid rgba(255,255,255,0.05);
    border-bottom: 1px solid rgba(255,255,255,0.05);
}
.logo-parade-item {
    width: 64px; height: 64px; border-radius: 16px;
    background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06);
    display: flex; align-items: center; justify-content: center;
    padding: 10px; transition: all 0.35s;
}
.logo-parade-item:hover {
    transform: scale(1.15) translateY(-4px);
    border-color: rgba(255,255,255,0.15);
    box-shadow: 0 8px 32px rgba(0,0,0,0.3);
}
.logo-parade-item img { width: 100%; height: 100%; object-fit: contain; }

/* Full-width showcase cards */
.solution-card {
    display: grid; grid-template-columns: 320px 1fr; gap: 3rem;
    align-items: center; padding: 3rem;
    background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.06);
    border-radius: 24px; margin-bottom: 2rem; position: relative;
    overflow: hidden; transition: all 0.4s;
}
.solution-card::before {
    content: ''; position: absolute; top: 0; left: 0; right: 0;
    height: 3px; border-radius: 24px 24px 0 0;
}
.solution-card:hover {
    border-color: rgba(255,255,255,0.12); transform: translateY(-4px);
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
}
.solution-card:nth-child(even) { grid-template-columns: 1fr 320px; }
.solution-card:nth-child(even) .solution-visual { order: 2; }

.solution-visual {
    display: flex; flex-direction: column; align-items: center;
    gap: 1.5rem; text-align: center;
}
.solution-logo {
    width: 140px; height: 140px; object-fit: contain;
    filter: drop-shadow(0 8px 24px rgba(0,0,0,0.3));
    transition: transform 0.4s; animation: float 5s ease-in-out infinite;
}
.solution-card:hover .solution-logo { transform: scale(1.08); }
.solution-card-banner {
    width: 100%; max-width: 300px; border-radius: 16px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.3);
}

.solution-content h3 {
    font-size: 1.75rem; font-weight: 800; margin-bottom: 1rem;
    letter-spacing: -0.02em;
}
.solution-content .desc {
    font-size: 0.95rem; color: var(--gray-400); line-height: 1.8;
    margin-bottom: 1.25rem;
}

/* Feature list */
.solution-features {
    display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;
    margin-bottom: 1.25rem;
}
.solution-feat {
    display: flex; align-items: center; gap: 0.5rem;
    font-size: 0.85rem; color: var(--gray-300);
}
.solution-feat-dot {
    width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0;
}

/* Chips */
.solution-chips {
    display: flex; flex-wrap: wrap; gap: 0.5rem;
}
.sol-chip {
    padding: 0.35rem 0.85rem; border-radius: 20px; font-size: 0.75rem;
    font-weight: 700; border: 1px solid rgba(255,255,255,0.1);
    background: rgba(255,255,255,0.04);
}

/* Platform Overview */
.platform-overview {
    text-align: center; padding: 4rem 2rem; margin-top: 2rem;
    background: linear-gradient(135deg, rgba(59,130,246,0.06), rgba(139,92,246,0.06));
    border: 1px solid rgba(59,130,246,0.1); border-radius: 32px;
    position: relative; overflow: hidden;
}
.platform-overview::before {
    content: ''; position: absolute; top: -80px; left: 50%;
    transform: translateX(-50%); width: 400px; height: 400px;
    background: radial-gradient(circle, rgba(59,130,246,0.06) 0%, transparent 70%);
    pointer-events: none;
}
.platform-overview h2 {
    font-size: 2rem; font-weight: 800; color: white;
    margin-bottom: 0.75rem; position: relative; z-index: 1;
}
.platform-overview p {
    color: var(--gray-400); margin-bottom: 2rem; font-size: 1rem;
    max-width: 500px; margin-left: auto; margin-right: auto;
    position: relative; z-index: 1;
}
.btn-cta {
    display: inline-flex; align-items: center; gap: 0.5rem;
    padding: 1rem 2rem; border-radius: 14px; font-size: 1rem;
    font-weight: 700; text-decoration: none; border: none;
    background: linear-gradient(135deg, var(--brand-500), #7c3aed);
    color: white; transition: all 0.3s; position: relative; z-index: 1;
    font-family: inherit;
}
.btn-cta:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 40px rgba(59,130,246,0.35);
}

/* Responsive */
@media (max-width: 768px) {
    .solution-card,
    .solution-card:nth-child(even) { grid-template-columns: 1fr; gap: 2rem; padding: 2rem; }
    .solution-card:nth-child(even) .solution-visual { order: 0; }
    .solution-features { grid-template-columns: 1fr; }
    .solutions-hero h1 { font-size: 2rem; }
    .logo-parade { gap: 1.25rem; }
    .logo-parade-item { width: 48px; height: 48px; border-radius: 12px; padding: 6px; }
}
</style>

{{-- Hero --}}
<section class="solutions-hero">
    <h1 class="reveal">
        @if(app()->getLocale() === 'tr')
            Beş Güçlü Uygulama,<br><span>Tek Ekosistem</span>
        @else
            Five Powerful Apps,<br><span>One Ecosystem</span>
        @endif
    </h1>
    <p class="reveal reveal-delay-1">
        @if(app()->getLocale() === 'tr')
            DopiFuture, simülasyon tabanlı oyunlardan AI koçluğa, girişimcilik laboratuvarından interaktif öğrenme alanlarına kadar kapsamlı bir eğitim ekosistemi sunar.
        @else
            DopiFuture offers a comprehensive education ecosystem — from simulation-based games to AI coaching, entrepreneurship labs, and interactive learning spaces.
        @endif
    </p>
</section>

{{-- Logo Parade --}}
<div class="logo-parade reveal">
    <div class="logo-parade-item"><img src="{{ asset('images/apps/mission-way.png') }}" alt="Mission Way"></div>
    <div class="logo-parade-item"><img src="{{ asset('images/apps/startup-lab.png') }}" alt="Way Startup Lab"></div>
    <div class="logo-parade-item"><img src="{{ asset('images/apps/role-galaxy.png') }}" alt="Role Galaxy"></div>
    <div class="logo-parade-item"><img src="{{ asset('images/apps/way-ai-coach.png') }}" alt="WAY AI Coach"></div>
    <div class="logo-parade-item"><img src="{{ asset('images/apps/study-space.png') }}" alt="Study Space"></div>
</div>

{{-- ═══════════════ APP CARDS ═══════════════ --}}

{{-- MISSION WAY --}}
<div class="solution-card reveal" style="--col: #FF8904;">
    <style>.solution-card:nth-child(1 of .solution-card)::before { background: linear-gradient(90deg, #FF8904, #FFB347); }</style>
    <div class="solution-visual">
        <img class="solution-logo" src="{{ asset('images/apps/mission-way.png') }}" alt="Mission Way">
        <img class="solution-card-banner" src="{{ asset('images/apps/mission-way-card.png') }}" alt="Mission Way">
    </div>
    <div class="solution-content">
        <h3 style="color: #FFB347;">Mission Way</h3>
        <p class="desc">
            @if(app()->getLocale() === 'tr')
                Multiplayer simülasyon tabanlı karar verme platformu. Öğrenciler deprem müdahalesi, diplomatik kriz yönetimi, çevre politikaları gibi gerçekçi senaryolarda ekip olarak rol dağılımı yapar ve kritik kararlar alır. Her karar dallanma hikayesi oluşturur ve nihai skor hesaplanır.
            @else
                A multiplayer simulation-based decision-making platform. Students team up for realistic scenarios like earthquake response, diplomatic crisis management, and environmental policies. They assign roles, make critical decisions that branch the story, and earn a final score.
            @endif
        </p>
        <div class="solution-features">
            <div class="solution-feat"><div class="solution-feat-dot" style="background:#FFB347;"></div>{{ app()->getLocale() === 'tr' ? '4 oyunculu multiplayer oturumlar' : '4-player multiplayer sessions' }}</div>
            <div class="solution-feat"><div class="solution-feat-dot" style="background:#FFB347;"></div>{{ app()->getLocale() === 'tr' ? 'Rol bazlı karar mekanizması' : 'Role-based decision mechanism' }}</div>
            <div class="solution-feat"><div class="solution-feat-dot" style="background:#FFB347;"></div>{{ app()->getLocale() === 'tr' ? 'Gerçek zamanlı skor tablosu' : 'Real-time score tracking' }}</div>
            <div class="solution-feat"><div class="solution-feat-dot" style="background:#FFB347;"></div>{{ app()->getLocale() === 'tr' ? 'Öğretmen görev ataması' : 'Teacher assignment management' }}</div>
        </div>
        <div class="solution-chips">
            <span class="sol-chip" style="border-color: rgba(255,137,4,0.3); color:#FFB347;">🎮 Multiplayer</span>
            <span class="sol-chip" style="border-color: rgba(255,137,4,0.3); color:#FFB347;">🎯 Scenario-Based</span>
            <span class="sol-chip" style="border-color: rgba(255,137,4,0.3); color:#FFB347;">📊 Analytics</span>
        </div>
    </div>
</div>

{{-- WAY STARTUP LAB --}}
<div class="solution-card reveal" style="--col: #43ACFF;">
    <style>.solution-card:nth-child(2 of .solution-card)::before { background: linear-gradient(90deg, #43ACFF, #7CC8FF); }</style>
    <div class="solution-visual">
        <img class="solution-logo" src="{{ asset('images/apps/startup-lab.png') }}" alt="Way Startup Lab">
        <img class="solution-card-banner" src="{{ asset('images/apps/startup-lab-card.png') }}" alt="Way Startup Lab">
    </div>
    <div class="solution-content">
        <h3 style="color: #7CC8FF;">Way Startup Lab</h3>
        <p class="desc">
            @if(app()->getLocale() === 'tr')
                Girişimcilik eğitiminin dijital laboratuvarı. Öğrenciler fikir geliştirmeden pitch sunumuna kadar bir startup yolculuğunu deneyimler. Her adımda dosya yükleyebilir, yapay zekâ değerlendirmesi alabilir ve puan kazanabilir. Zorluk seviyeleri ve kilitli adımlar ile progresif öğrenme.
            @else
                The digital laboratory for entrepreneurship education. Students experience the startup journey from ideation to pitch deck. Each step allows file uploads, AI-powered evaluations, and point earnings. Progressive learning with difficulty levels and locked steps.
            @endif
        </p>
        <div class="solution-features">
            <div class="solution-feat"><div class="solution-feat-dot" style="background:#7CC8FF;"></div>{{ app()->getLocale() === 'tr' ? 'Adım adım girişimcilik müfredatı' : 'Step-by-step entrepreneurship curriculum' }}</div>
            <div class="solution-feat"><div class="solution-feat-dot" style="background:#7CC8FF;"></div>{{ app()->getLocale() === 'tr' ? 'AI destekli değerlendirme' : 'AI-powered evaluation' }}</div>
            <div class="solution-feat"><div class="solution-feat-dot" style="background:#7CC8FF;"></div>{{ app()->getLocale() === 'tr' ? 'Dosya yükleme & portfolio' : 'File upload & portfolio' }}</div>
            <div class="solution-feat"><div class="solution-feat-dot" style="background:#7CC8FF;"></div>{{ app()->getLocale() === 'tr' ? 'Zorluk seviyeli görevler' : 'Difficulty-leveled tasks' }}</div>
        </div>
        <div class="solution-chips">
            <span class="sol-chip" style="border-color: rgba(67,172,255,0.3); color:#7CC8FF;">🧠 AI Evaluation</span>
            <span class="sol-chip" style="border-color: rgba(67,172,255,0.3); color:#7CC8FF;">📈 Progressive</span>
            <span class="sol-chip" style="border-color: rgba(67,172,255,0.3); color:#7CC8FF;">📁 Portfolio</span>
        </div>
    </div>
</div>

{{-- ROLE GALAXY --}}
<div class="solution-card reveal" style="--col: #8D65E0;">
    <style>.solution-card:nth-child(3 of .solution-card)::before { background: linear-gradient(90deg, #8D65E0, #B794F4); }</style>
    <div class="solution-visual">
        <img class="solution-logo" src="{{ asset('images/apps/role-galaxy.png') }}" alt="Role Galaxy">
        <img class="solution-card-banner" src="{{ asset('images/apps/role-galaxy-card.png') }}" alt="Role Galaxy">
    </div>
    <div class="solution-content">
        <h3 style="color: #B794F4;">Role Galaxy</h3>
        <p class="desc">
            @if(app()->getLocale() === 'tr')
                Yapay zekâ ile desteklenen kariyer keşif simülatörü. Öğrenciler mühendis, doktor, avukat, girişimci gibi rollere bürünerek gerçekçi iş senaryolarında kararlar alır. Her seçim farklı bir hikaye dalı oluşturur — böylece her deneyim benzersiz ve öğreticidir.
            @else
                An AI-powered career discovery simulator. Students step into roles such as engineer, doctor, lawyer, and entrepreneur, making decisions in realistic work scenarios. Each choice creates a different story branch — making every experience unique and educational.
            @endif
        </p>
        <div class="solution-features">
            <div class="solution-feat"><div class="solution-feat-dot" style="background:#B794F4;"></div>{{ app()->getLocale() === 'tr' ? 'AI tarafından dinamik hikaye dalları' : 'AI-generated dynamic story branches' }}</div>
            <div class="solution-feat"><div class="solution-feat-dot" style="background:#B794F4;"></div>{{ app()->getLocale() === 'tr' ? '10+ farklı meslek senaryosu' : '10+ different career scenarios' }}</div>
            <div class="solution-feat"><div class="solution-feat-dot" style="background:#B794F4;"></div>{{ app()->getLocale() === 'tr' ? 'Karar ağacı ve sonuç analizi' : 'Decision tree & outcome analysis' }}</div>
            <div class="solution-feat"><div class="solution-feat-dot" style="background:#B794F4;"></div>{{ app()->getLocale() === 'tr' ? 'Tekrarlanabilir farklı sonuçlar' : 'Replayable with different outcomes' }}</div>
        </div>
        <div class="solution-chips">
            <span class="sol-chip" style="border-color: rgba(141,101,224,0.3); color:#B794F4;">🤖 AI-Generated</span>
            <span class="sol-chip" style="border-color: rgba(141,101,224,0.3); color:#B794F4;">🌌 Branching</span>
            <span class="sol-chip" style="border-color: rgba(141,101,224,0.3); color:#B794F4;">👔 Career</span>
        </div>
    </div>
</div>

{{-- WAY AI COACH --}}
<div class="solution-card reveal" style="--col: #ED84E4;">
    <style>.solution-card:nth-child(4 of .solution-card)::before { background: linear-gradient(90deg, #ED84E4, #F5A3EE); }</style>
    <div class="solution-visual">
        <img class="solution-logo" src="{{ asset('images/apps/way-ai-coach.png') }}" alt="WAY AI Coach">
        <img class="solution-card-banner" src="{{ asset('images/apps/way-ai-coach-card.png') }}" alt="WAY AI Coach">
    </div>
    <div class="solution-content">
        <h3 style="color: #F5A3EE;">WAY AI Coach</h3>
        <p class="desc">
            @if(app()->getLocale() === 'tr')
                Kişiselleştirilmiş yapay zekâ koçluk platformu. Her öğrencinin ilgi alanları, güçlü ve zayıf yönleri dikkate alınarak özelleştirilmiş sohbetler oluşturur. Laravel WebSocket altyapısı ile gerçek zamanlı iletişim ve akıcı sohbet deneyimi sunar.
            @else
                A personalized AI coaching platform. Creates customized conversations based on each student's interests, strengths, and areas for growth. Delivers real-time communication and a fluid chat experience via Laravel WebSocket infrastructure.
            @endif
        </p>
        <div class="solution-features">
            <div class="solution-feat"><div class="solution-feat-dot" style="background:#F5A3EE;"></div>{{ app()->getLocale() === 'tr' ? 'Profil bazlı kişiselleştirme' : 'Profile-based personalization' }}</div>
            <div class="solution-feat"><div class="solution-feat-dot" style="background:#F5A3EE;"></div>{{ app()->getLocale() === 'tr' ? 'Gerçek zamanlı WebSocket sohbet' : 'Real-time WebSocket chat' }}</div>
            <div class="solution-feat"><div class="solution-feat-dot" style="background:#F5A3EE;"></div>{{ app()->getLocale() === 'tr' ? 'Oturum geçmişi ve takip' : 'Session history & tracking' }}</div>
            <div class="solution-feat"><div class="solution-feat-dot" style="background:#F5A3EE;"></div>{{ app()->getLocale() === 'tr' ? 'Çok dilli AI asistan' : 'Multilingual AI assistant' }}</div>
        </div>
        <div class="solution-chips">
            <span class="sol-chip" style="border-color: rgba(237,132,228,0.3); color:#F5A3EE;">💬 Real-time</span>
            <span class="sol-chip" style="border-color: rgba(237,132,228,0.3); color:#F5A3EE;">🎯 Personalized</span>
            <span class="sol-chip" style="border-color: rgba(237,132,228,0.3); color:#F5A3EE;">🧠 AI-Powered</span>
        </div>
    </div>
</div>

{{-- STUDY SPACE --}}
<div class="solution-card reveal" style="--col: #5AC780;">
    <style>.solution-card:nth-child(5 of .solution-card)::before { background: linear-gradient(90deg, #5AC780, #86EFAC); }</style>
    <div class="solution-visual">
        <img class="solution-logo" src="{{ asset('images/apps/study-space.png') }}" alt="Study Space">
        <img class="solution-card-banner" src="{{ asset('images/apps/study-space-card.png') }}" alt="Study Space">
    </div>
    <div class="solution-content">
        <h3 style="color: #86EFAC;">Study Space</h3>
        <p class="desc">
            @if(app()->getLocale() === 'tr')
                Yapay zekâ destekli öğretim asistanı platformu. Öğrenciler istedikleri konuda soru sorabilir, ders seçebilir, sınıf seviyesine göre özelleştirilmiş interaktif dersler alabilir. Detaylı oturum geçmişi ile öğrenme yolculuğunu takip eder.
            @else
                An AI-powered teaching assistant platform. Students can ask questions on any topic, select subjects, and take interactive lessons customized to their grade level. Tracks the learning journey with detailed session history.
            @endif
        </p>
        <div class="solution-features">
            <div class="solution-feat"><div class="solution-feat-dot" style="background:#86EFAC;"></div>{{ app()->getLocale() === 'tr' ? 'Konu & ders bazlı öğrenme' : 'Subject & topic-based learning' }}</div>
            <div class="solution-feat"><div class="solution-feat-dot" style="background:#86EFAC;"></div>{{ app()->getLocale() === 'tr' ? 'Sınıf seviyesi ayarı' : 'Grade-level adjustment' }}</div>
            <div class="solution-feat"><div class="solution-feat-dot" style="background:#86EFAC;"></div>{{ app()->getLocale() === 'tr' ? 'İnteraktif soru-cevap' : 'Interactive Q&A sessions' }}</div>
            <div class="solution-feat"><div class="solution-feat-dot" style="background:#86EFAC;"></div>{{ app()->getLocale() === 'tr' ? 'Detaylı oturum geçmişi' : 'Detailed session history' }}</div>
        </div>
        <div class="solution-chips">
            <span class="sol-chip" style="border-color: rgba(90,199,128,0.3); color:#86EFAC;">📚 Subject-Based</span>
            <span class="sol-chip" style="border-color: rgba(90,199,128,0.3); color:#86EFAC;">🎓 Adaptive</span>
            <span class="sol-chip" style="border-color: rgba(90,199,128,0.3); color:#86EFAC;">💡 Interactive</span>
        </div>
    </div>
</div>

{{-- CTA --}}
<div class="platform-overview reveal" style="margin-top: 3rem;">
    <h2>
        @if(app()->getLocale() === 'tr')
            Tüm Uygulamaları Keşfedin
        @else
            Explore All Applications
        @endif
    </h2>
    <p>
        @if(app()->getLocale() === 'tr')
            Ücretsiz kayıt olun ve DopiFuture ekosisteminin tüm gücünü okulunuza entegre edin.
        @else
            Register for free and integrate the full power of the DopiFuture ecosystem into your school.
        @endif
    </p>
    <a href="{{ route('register.create') }}" class="btn-cta">
        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
        </svg>
        {{ app()->getLocale() === 'tr' ? 'Hemen Başla' : 'Get Started Free' }}
    </a>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });
    document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
});
</script>
@endsection