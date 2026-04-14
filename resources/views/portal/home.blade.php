@extends('portal.layout')
@section('title', 'DopiFuture — ' . (__('portal.home_title')))
@section('meta_description', __('portal.home_meta'))

@section('content')
<style>
/* ═══════════════════════════════════════════
   ANIMATIONS
   ═══════════════════════════════════════════ */
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(40px); }
    to   { opacity: 1; transform: translateY(0); }
}
@keyframes float {
    0%, 100% { transform: translateY(0px) rotate(0deg); }
    50%      { transform: translateY(-18px) rotate(2deg); }
}
@keyframes pulse-glow {
    0%, 100% { box-shadow: 0 0 20px rgba(59,130,246,0.3); }
    50%      { box-shadow: 0 0 60px rgba(59,130,246,0.5), 0 0 120px rgba(139,92,246,0.2); }
}
@keyframes gradient-shift {
    0%   { background-position: 0% 50%; }
    50%  { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}
@keyframes orbit {
    from { transform: rotate(0deg) translateX(120px) rotate(0deg); }
    to   { transform: rotate(360deg) translateX(120px) rotate(-360deg); }
}
@keyframes shimmer {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}
@keyframes countUp {
    from { opacity: 0; transform: scale(0.5); }
    to   { opacity: 1; transform: scale(1); }
}

.reveal { opacity: 0; transform: translateY(40px); transition: all 0.8s cubic-bezier(0.16, 1, 0.3, 1); }
.reveal.visible { opacity: 1; transform: translateY(0); }
.reveal-delay-1 { transition-delay: 0.1s; }
.reveal-delay-2 { transition-delay: 0.2s; }
.reveal-delay-3 { transition-delay: 0.3s; }
.reveal-delay-4 { transition-delay: 0.4s; }

@media (prefers-reduced-motion: reduce) {
    .reveal { transition: none; opacity: 1; transform: none; }
    * { animation-duration: 0.01ms !important; }
}

/* ═══════════════════════════════════════════
   SECTION SPACING
   ═══════════════════════════════════════════ */
.section { padding: 5rem 0; position: relative; }
.section-title {
    text-align: center; font-size: 2.25rem; font-weight: 800;
    color: white; letter-spacing: -0.03em; margin-bottom: 0.75rem;
}
.section-subtitle {
    text-align: center; color: var(--gray-400); font-size: 1.05rem;
    max-width: 560px; margin: 0 auto 3rem; line-height: 1.7;
}
.section-divider {
    width: 60px; height: 4px; border-radius: 2px; margin: 0 auto 3rem;
    background: linear-gradient(90deg, var(--brand-500), #8b5cf6);
}

/* ═══════════════════════════════════════════
   HERO
   ═══════════════════════════════════════════ */
.hero {
    text-align: center; padding: 5rem 0 3rem; position: relative;
    overflow: hidden; min-height: 560px; display: flex;
    flex-direction: column; align-items: center; justify-content: center;
}
.hero-bg-orbs {
    position: absolute; inset: 0; pointer-events: none; overflow: hidden;
}
.hero-orb {
    position: absolute; border-radius: 50%; filter: blur(80px); opacity: 0.15;
}
.hero-orb-1 { width: 400px; height: 400px; background: #3b82f6; top: -100px; left: -80px; animation: float 8s ease-in-out infinite; }
.hero-orb-2 { width: 300px; height: 300px; background: #8b5cf6; top: 50px; right: -50px; animation: float 10s ease-in-out infinite 2s; }
.hero-orb-3 { width: 250px; height: 250px; background: #06b6d4; bottom: -50px; left: 30%; animation: float 12s ease-in-out infinite 4s; }

.hero-logo {
    width: 160px; height: 160px; margin: 0 auto 2rem; position: relative;
    animation: float 6s ease-in-out infinite;
}
.hero-logo img { width: 100%; height: 100%; object-fit: contain; filter: drop-shadow(0 8px 32px rgba(59,130,246,0.3)); }
.hero-logo::after {
    content: ''; position: absolute; inset: -20px;
    background: radial-gradient(circle, rgba(59,130,246,0.12) 0%, transparent 70%);
    border-radius: 50%; z-index: -1;
}

.hero h1 {
    font-size: 3.75rem; font-weight: 900; color: white;
    letter-spacing: -0.04em; line-height: 1.1; margin-bottom: 1.5rem;
    position: relative; z-index: 1;
}
.hero h1 .gradient-text {
    background: linear-gradient(135deg, #60a5fa, #818cf8, #c084fc, #60a5fa);
    background-size: 300% 300%;
    -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    background-clip: text; animation: gradient-shift 6s ease infinite;
}
.hero .tagline {
    font-size: 1.2rem; color: var(--gray-400); max-width: 600px;
    margin: 0 auto 2.5rem; line-height: 1.8; position: relative; z-index: 1;
}
.hero-actions {
    display: flex; gap: 1rem; justify-content: center;
    flex-wrap: wrap; position: relative; z-index: 1;
}
.btn-hero {
    padding: 1rem 2.25rem; border-radius: 14px; font-size: 1rem;
    font-weight: 700; text-decoration: none; transition: all 0.35s;
    display: inline-flex; align-items: center; gap: 0.6rem;
    font-family: inherit;
}
.btn-hero-primary {
    background: linear-gradient(135deg, var(--brand-500), #7c3aed);
    color: white; border: none; animation: pulse-glow 3s ease-in-out infinite;
}
.btn-hero-primary:hover {
    transform: translateY(-3px) scale(1.03);
    box-shadow: 0 16px 48px rgba(59,130,246,0.4);
}
.btn-hero-outline {
    border: 1.5px solid rgba(255,255,255,0.15); color: var(--gray-300);
    background: rgba(255,255,255,0.03); backdrop-filter: blur(8px);
}
.btn-hero-outline:hover {
    border-color: rgba(255,255,255,0.35); background: rgba(255,255,255,0.08);
    color: white; transform: translateY(-2px);
}

/* Floating mini icons around hero */
.hero-float-icons { position: absolute; inset: 0; pointer-events: none; z-index: 0; }
.hero-float-icon {
    position: absolute; width: 48px; height: 48px; border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.08);
    animation: float 7s ease-in-out infinite;
}

/* ═══════════════════════════════════════════
   STATS BAR
   ═══════════════════════════════════════════ */
.stats-bar {
    display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem;
    padding: 2.5rem 0; border-top: 1px solid rgba(255,255,255,0.06);
    border-bottom: 1px solid rgba(255,255,255,0.06);
}
.stat-item {
    text-align: center; padding: 1.5rem 1rem;
    background: rgba(255,255,255,0.02); border-radius: 16px;
    border: 1px solid rgba(255,255,255,0.05);
    transition: all 0.3s;
}
.stat-item:hover {
    background: rgba(255,255,255,0.04);
    border-color: rgba(59,130,246,0.2);
    transform: translateY(-4px);
}
.stat-num {
    font-size: 2.75rem; font-weight: 900; color: white;
    line-height: 1; margin-bottom: 0.5rem;
    background: linear-gradient(135deg, white 40%, var(--brand-400));
    -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    background-clip: text;
}
.stat-label { font-size: 0.85rem; color: var(--gray-400); font-weight: 600; }
.stat-icon { font-size: 1.5rem; margin-bottom: 0.5rem; }

/* ═══════════════════════════════════════════
   APP SHOWCASE
   ═══════════════════════════════════════════ */
.app-showcase-grid {
    display: flex; flex-direction: column; gap: 2rem;
}
.app-showcase-card {
    display: grid; grid-template-columns: 1fr 1fr; gap: 3rem;
    align-items: center; padding: 3rem;
    background: rgba(255,255,255,0.02);
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 24px; position: relative;
    overflow: hidden; transition: all 0.4s;
}
.app-showcase-card:hover {
    border-color: rgba(255,255,255,0.12);
    transform: translateY(-4px);
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
}
.app-showcase-card::before {
    content: ''; position: absolute; top: 0; left: 0; right: 0;
    height: 3px; border-radius: 24px 24px 0 0;
}
.app-showcase-card:nth-child(even) { direction: rtl; }
.app-showcase-card:nth-child(even) > * { direction: ltr; }

.app-showcase-visual {
    display: flex; flex-direction: column; align-items: center;
    justify-content: center; gap: 1.5rem;
}
.app-showcase-logo {
    width: 140px; height: 140px; object-fit: contain;
    filter: drop-shadow(0 8px 24px rgba(0,0,0,0.3));
    transition: transform 0.4s;
}
.app-showcase-card:hover .app-showcase-logo { transform: scale(1.08) rotate(-2deg); }

.app-showcase-card-img {
    width: 100%; max-width: 380px; border-radius: 16px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.3);
}

.app-showcase-content { display: flex; flex-direction: column; gap: 1rem; }
.app-showcase-content h3 {
    font-size: 1.75rem; font-weight: 800; color: white; letter-spacing: -0.02em;
}
.app-showcase-content p {
    font-size: 0.95rem; color: var(--gray-400); line-height: 1.8;
}
.app-feature-chips {
    display: flex; flex-wrap: wrap; gap: 0.5rem; margin-top: 0.5rem;
}
.app-chip {
    padding: 0.4rem 0.9rem; border-radius: 20px; font-size: 0.75rem;
    font-weight: 700; letter-spacing: 0.02em;
    border: 1px solid rgba(255,255,255,0.1);
    background: rgba(255,255,255,0.04);
}

/* ═══════════════════════════════════════════
   FEATURES GRID
   ═══════════════════════════════════════════ */
.features-grid {
    display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem;
}
.feature-card {
    background: rgba(255,255,255,0.02);
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 20px; padding: 2rem; transition: all 0.4s;
    position: relative; overflow: hidden;
}
.feature-card::after {
    content: ''; position: absolute; top: -50%; left: -50%;
    width: 200%; height: 200%; opacity: 0;
    background: radial-gradient(circle, rgba(59,130,246,0.06) 0%, transparent 60%);
    transition: opacity 0.4s;
}
.feature-card:hover::after { opacity: 1; }
.feature-card:hover {
    border-color: rgba(59,130,246,0.25);
    transform: translateY(-6px);
    box-shadow: 0 16px 48px rgba(0,0,0,0.3);
}
.feature-icon-wrap {
    width: 52px; height: 52px; border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    margin-bottom: 1.25rem; position: relative; z-index: 1;
}
.feature-card h3 {
    font-size: 1.1rem; font-weight: 700; color: white;
    margin-bottom: 0.6rem; position: relative; z-index: 1;
}
.feature-card p {
    font-size: 0.88rem; color: var(--gray-400);
    line-height: 1.7; position: relative; z-index: 1;
}

/* ═══════════════════════════════════════════
   PHILOSOPHY / VISION
   ═══════════════════════════════════════════ */
.vision-section {
    text-align: center; padding: 5rem 2rem;
    background: rgba(59,130,246,0.03);
    border: 1px solid rgba(59,130,246,0.08);
    border-radius: 32px; position: relative; overflow: hidden;
}
.vision-section::before {
    content: ''; position: absolute; top: -100px; left: 50%;
    transform: translateX(-50%); width: 400px; height: 400px;
    background: radial-gradient(circle, rgba(139,92,246,0.08) 0%, transparent 70%);
    pointer-events: none;
}
.vision-quote {
    font-size: 1.75rem; font-weight: 700; color: white;
    max-width: 700px; margin: 0 auto 1.5rem; line-height: 1.5;
    letter-spacing: -0.02em; position: relative; z-index: 1;
}
.vision-author {
    font-size: 0.95rem; color: var(--brand-400); font-weight: 600;
    position: relative; z-index: 1;
}

/* ═══════════════════════════════════════════
   CTA
   ═══════════════════════════════════════════ */
.cta-section {
    text-align: center; padding: 4rem 2rem;
    background: linear-gradient(135deg, rgba(59,130,246,0.08), rgba(139,92,246,0.08));
    border: 1px solid rgba(59,130,246,0.15);
    border-radius: 28px; position: relative; overflow: hidden;
}
.cta-section::before {
    content: ''; position: absolute; top: 0; left: 0; right: 0;
    height: 2px;
    background: linear-gradient(90deg, transparent, var(--brand-500), #8b5cf6, transparent);
}
.cta-section h2 {
    font-size: 2rem; font-weight: 800; color: white;
    margin-bottom: 0.75rem; letter-spacing: -0.02em;
}
.cta-section p {
    color: var(--gray-400); margin-bottom: 2rem; font-size: 1.05rem;
}
.cta-section .btn-hero-primary { animation: pulse-glow 2.5s ease-in-out infinite; }

/* ═══════════════════════════════════════════
   RESPONSIVE
   ═══════════════════════════════════════════ */
@media (max-width: 968px) {
    .app-showcase-card { grid-template-columns: 1fr; gap: 2rem; padding: 2rem; }
    .app-showcase-card:nth-child(even) { direction: ltr; }
    .features-grid { grid-template-columns: repeat(2, 1fr); }
    .stats-bar { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 640px) {
    .hero h1 { font-size: 2.25rem; }
    .hero-logo { width: 120px; height: 120px; }
    .features-grid { grid-template-columns: 1fr; }
    .stats-bar { grid-template-columns: 1fr 1fr; gap: 1rem; }
    .stat-num { font-size: 2rem; }
    .section-title { font-size: 1.75rem; }
    .vision-quote { font-size: 1.25rem; }
    .hero-float-icons { display: none; }
}
</style>

{{-- ═══════════════ HERO ═══════════════ --}}
<section class="hero">
    <div class="hero-bg-orbs">
        <div class="hero-orb hero-orb-1"></div>
        <div class="hero-orb hero-orb-2"></div>
        <div class="hero-orb hero-orb-3"></div>
    </div>

    {{-- Floating mini-icons --}}
    <div class="hero-float-icons">
        <div class="hero-float-icon" style="top:15%; left:8%; background:rgba(255,137,4,0.12); animation-delay:0s;">
            <img src="{{ asset('images/apps/mission-way.png') }}" alt="" style="width:30px; height:30px; object-fit:contain;">
        </div>
        <div class="hero-float-icon" style="top:25%; right:10%; background:rgba(141,101,224,0.12); animation-delay:1.5s;">
            <img src="{{ asset('images/apps/role-galaxy.png') }}" alt="" style="width:30px; height:30px; object-fit:contain;">
        </div>
        <div class="hero-float-icon" style="bottom:20%; left:12%; background:rgba(67,172,255,0.12); animation-delay:3s;">
            <img src="{{ asset('images/apps/startup-lab.png') }}" alt="" style="width:30px; height:30px; object-fit:contain;">
        </div>
        <div class="hero-float-icon" style="bottom:30%; right:6%; background:rgba(90,199,128,0.12); animation-delay:4.5s;">
            <img src="{{ asset('images/apps/study-space.png') }}" alt="" style="width:30px; height:30px; object-fit:contain;">
        </div>
    </div>

    <div class="hero-logo">
        <img src="{{ asset('images/apps/dopifuture-hero.png') }}" alt="DopiFuture">
    </div>

    <h1>
        {!! __('portal.hero_title') !!}
    </h1>

    <p class="tagline">
        {{ __('portal.hero_tagline') }}
    </p>

    <div class="hero-actions">
        <a href="{{ route('register.create') }}" class="btn-hero btn-hero-primary">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
            {{ __('portal.hero_btn_start') }}
        </a>
        <a href="{{ route('portal.solutions') }}" class="btn-hero btn-hero-outline">
            {{ __('portal.hero_btn_explore') }}
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
            </svg>
        </a>
    </div>
</section>

{{-- ═══════════════ STATS BAR ═══════════════ --}}
<div class="stats-bar reveal">
    <div class="stat-item">
        <div class="stat-icon">🚀</div>
        <div class="stat-num" data-count="{{ $appCount }}">{{ $appCount }}</div>
        <div class="stat-label">{{ __('portal.stat_apps') }}</div>
    </div>
    <div class="stat-item">
        <div class="stat-icon">🏫</div>
        <div class="stat-num" data-count="{{ $schoolCount }}">{{ $schoolCount }}+</div>
        <div class="stat-label">{{ __('portal.stat_schools') }}</div>
    </div>
    <div class="stat-item">
        <div class="stat-icon">🎓</div>
        <div class="stat-num" data-count="{{ $studentCount }}">{{ $studentCount }}+</div>
        <div class="stat-label">{{ __('portal.stat_students') }}</div>
    </div>
    <div class="stat-item">
        <div class="stat-icon">🎮</div>
        <div class="stat-num" data-count="{{ $simulationCount }}">{{ $simulationCount }}</div>
        <div class="stat-label">{{ __('portal.stat_sims') }}</div>
    </div>
</div>

{{-- ═══════════════ APP SHOWCASE ═══════════════ --}}
<section class="section">
    <h2 class="section-title reveal">
        {{ __('portal.eco_title') }}
    </h2>
    <p class="section-subtitle reveal reveal-delay-1">
        {{ __('portal.eco_subtitle') }}
    </p>
    <div class="section-divider reveal reveal-delay-2"></div>

    <div class="app-showcase-grid">

        {{-- MISSION WAY --}}
        <div class="app-showcase-card reveal" style="--app-color: #FF8904;">
            <style>.app-showcase-card:nth-child(1)::before { background: linear-gradient(90deg, #FF8904, #FFB347); }</style>
            <div class="app-showcase-visual">
                <img class="app-showcase-logo" src="{{ asset('images/apps/mission-way.png') }}" alt="Mission Way">
                <img class="app-showcase-card-img" src="{{ asset('images/apps/mission-way-card.png') }}" alt="Mission Way Card">
            </div>
            <div class="app-showcase-content">
                <h3 style="color: #FFB347;">Mission Way</h3>
                <p>
                    {{ __('portal.mw_desc') }}
                </p>
                <div class="app-feature-chips">
                    <span class="app-chip" style="border-color: rgba(255,137,4,0.3); color: #FFB347;">🎮 Multiplayer</span>
                    <span class="app-chip" style="border-color: rgba(255,137,4,0.3); color: #FFB347;">🎯 Role-Based</span>
                    <span class="app-chip" style="border-color: rgba(255,137,4,0.3); color: #FFB347;">📊 Real-time Scoring</span>
                    <span class="app-chip" style="border-color: rgba(255,137,4,0.3); color: #FFB347;">🏆 Assignments</span>
                </div>
            </div>
        </div>

        {{-- WAY STARTUP LAB --}}
        <div class="app-showcase-card reveal" style="--app-color: #43ACFF;">
            <style>.app-showcase-card:nth-child(2)::before { background: linear-gradient(90deg, #43ACFF, #7CC8FF); }</style>
            <div class="app-showcase-visual">
                <img class="app-showcase-logo" src="{{ asset('images/apps/startup-lab.png') }}" alt="Way Startup Lab">
                <img class="app-showcase-card-img" src="{{ asset('images/apps/startup-lab-card.png') }}" alt="Way Startup Lab Card">
            </div>
            <div class="app-showcase-content">
                <h3 style="color: #7CC8FF;">Way Startup Lab</h3>
                <p>
                    {{ __('portal.startup_desc') }}
                </p>
                <div class="app-feature-chips">
                    <span class="app-chip" style="border-color: rgba(67,172,255,0.3); color: #7CC8FF;">🧠 AI Evaluation</span>
                    <span class="app-chip" style="border-color: rgba(67,172,255,0.3); color: #7CC8FF;">📁 File Upload</span>
                    <span class="app-chip" style="border-color: rgba(67,172,255,0.3); color: #7CC8FF;">⭐ Points System</span>
                    <span class="app-chip" style="border-color: rgba(67,172,255,0.3); color: #7CC8FF;">📈 Step-by-Step</span>
                </div>
            </div>
        </div>

        {{-- ROLE GALAXY --}}
        <div class="app-showcase-card reveal" style="--app-color: #8D65E0;">
            <style>.app-showcase-card:nth-child(3)::before { background: linear-gradient(90deg, #8D65E0, #B794F4); }</style>
            <div class="app-showcase-visual">
                <img class="app-showcase-logo" src="{{ asset('images/apps/role-galaxy.png') }}" alt="Role Galaxy">
                <img class="app-showcase-card-img" src="{{ asset('images/apps/role-galaxy-card.png') }}" alt="Role Galaxy Card">
            </div>
            <div class="app-showcase-content">
                <h3 style="color: #B794F4;">Role Galaxy</h3>
                <p>
                    {{ __('portal.role_desc') }}
                </p>
                <div class="app-feature-chips">
                    <span class="app-chip" style="border-color: rgba(141,101,224,0.3); color: #B794F4;">🤖 AI-Generated</span>
                    <span class="app-chip" style="border-color: rgba(141,101,224,0.3); color: #B794F4;">🌌 Branching Stories</span>
                    <span class="app-chip" style="border-color: rgba(141,101,224,0.3); color: #B794F4;">👔 Career Exploration</span>
                </div>
            </div>
        </div>

        {{-- WAY AI COACH --}}
        <div class="app-showcase-card reveal" style="--app-color: #ED84E4;">
            <style>.app-showcase-card:nth-child(4)::before { background: linear-gradient(90deg, #ED84E4, #F5A3EE); }</style>
            <div class="app-showcase-visual">
                <img class="app-showcase-logo" src="{{ asset('images/apps/way-ai-coach.png') }}" alt="WAY AI Coach">
                <img class="app-showcase-card-img" src="{{ asset('images/apps/way-ai-coach-card.png') }}" alt="WAY AI Coach Card">
            </div>
            <div class="app-showcase-content">
                <h3 style="color: #F5A3EE;">WAY AI Coach</h3>
                <p>
                    {{ __('portal.coach_desc') }}
                </p>
                <div class="app-feature-chips">
                    <span class="app-chip" style="border-color: rgba(237,132,228,0.3); color: #F5A3EE;">💬 Real-time Chat</span>
                    <span class="app-chip" style="border-color: rgba(237,132,228,0.3); color: #F5A3EE;">🎯 Personalized</span>
                    <span class="app-chip" style="border-color: rgba(237,132,228,0.3); color: #F5A3EE;">📡 WebSocket</span>
                </div>
            </div>
        </div>

        {{-- STUDY SPACE --}}
        <div class="app-showcase-card reveal" style="--app-color: #5AC780;">
            <style>.app-showcase-card:nth-child(5)::before { background: linear-gradient(90deg, #5AC780, #86EFAC); }</style>
            <div class="app-showcase-visual">
                <img class="app-showcase-logo" src="{{ asset('images/apps/study-space.png') }}" alt="Study Space">
                <img class="app-showcase-card-img" src="{{ asset('images/apps/study-space-card.png') }}" alt="Study Space Card">
            </div>
            <div class="app-showcase-content">
                <h3 style="color: #86EFAC;">Study Space</h3>
                <p>
                    {{ __('portal.study_desc') }}
                </p>
                <div class="app-feature-chips">
                    <span class="app-chip" style="border-color: rgba(90,199,128,0.3); color: #86EFAC;">📚 Subject-Based</span>
                    <span class="app-chip" style="border-color: rgba(90,199,128,0.3); color: #86EFAC;">🎓 Grade-Level</span>
                    <span class="app-chip" style="border-color: rgba(90,199,128,0.3); color: #86EFAC;">💡 Interactive</span>
                    <span class="app-chip" style="border-color: rgba(90,199,128,0.3); color: #86EFAC;">📝 Session History</span>
                </div>
            </div>
        </div>

    </div>
</section>

{{-- ═══════════════ WHY DOPIFUTURE ═══════════════ --}}
<section class="section">
    <h2 class="section-title reveal">
        {{ __('portal.why_title') }}
    </h2>
    <p class="section-subtitle reveal reveal-delay-1">
        {{ __('portal.why_subtitle') }}
    </p>
    <div class="section-divider reveal reveal-delay-2"></div>

    <div class="features-grid">
        <div class="feature-card reveal">
            <div class="feature-icon-wrap" style="background: rgba(59,130,246,0.12);">
                <svg width="24" height="24" fill="none" stroke="#60a5fa" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </div>
            <h3>{{ __('portal.feat1_title') }}</h3>
            <p>{{ __('portal.feat1_desc') }}</p>
        </div>

        <div class="feature-card reveal reveal-delay-1">
            <div class="feature-icon-wrap" style="background: rgba(168,85,247,0.12);">
                <svg width="24" height="24" fill="none" stroke="#c084fc" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
            </div>
            <h3>{{ __('portal.feat2_title') }}</h3>
            <p>{{ __('portal.feat2_desc') }}</p>
        </div>

        <div class="feature-card reveal reveal-delay-2">
            <div class="feature-icon-wrap" style="background: rgba(34,197,94,0.12);">
                <svg width="24" height="24" fill="none" stroke="#4ade80" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </div>
            <h3>{{ __('portal.feat3_title') }}</h3>
            <p>{{ __('portal.feat3_desc') }}</p>
        </div>

        <div class="feature-card reveal reveal-delay-1">
            <div class="feature-icon-wrap" style="background: rgba(251,146,60,0.12);">
                <svg width="24" height="24" fill="none" stroke="#fb923c" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </div>
            <h3>{{ __('portal.feat4_title') }}</h3>
            <p>{{ __('portal.feat4_desc') }}</p>
        </div>

        <div class="feature-card reveal reveal-delay-2">
            <div class="feature-icon-wrap" style="background: rgba(56,189,248,0.12);">
                <svg width="24" height="24" fill="none" stroke="#38bdf8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            </div>
            <h3>{{ __('portal.feat5_title') }}</h3>
            <p>{{ __('portal.feat5_desc') }}</p>
        </div>

        <div class="feature-card reveal reveal-delay-3">
            <div class="feature-icon-wrap" style="background: rgba(236,72,153,0.12);">
                <svg width="24" height="24" fill="none" stroke="#f472b6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/></svg>
            </div>
            <h3>{{ __('portal.feat6_title') }}</h3>
            <p>{{ __('portal.feat6_desc') }}</p>
        </div>
    </div>
</section>

{{-- ═══════════════ VISION ═══════════════ --}}
<section class="section">
    <div class="vision-section reveal">
        <div style="font-size: 3rem; margin-bottom: 1.5rem;">✨</div>
        <blockquote class="vision-quote">
            {{ __('portal.vision_quote') }}
        </blockquote>
        <p class="vision-author">— DopiFuture {{ __('portal.vision_author') }}</p>
    </div>
</section>

{{-- ═══════════════ CTA ═══════════════ --}}
<section class="cta-section reveal">
    <h2>
        {{ __('portal.cta_title') }}
    </h2>
    <p>
        {{ __('portal.cta_desc') }}
    </p>
    <a href="{{ route('register.create') }}" class="btn-hero btn-hero-primary">
        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
        </svg>
        {{ __('portal.cta_btn') }}
    </a>
</section>
@endsection

@section('scripts')
<script>
// IntersectionObserver for scroll-triggered reveal
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