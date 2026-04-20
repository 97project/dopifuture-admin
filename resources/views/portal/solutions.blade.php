@extends('portal.layout')
@section('title', __('portal.sol_title'))
@section('meta_description', __('portal.sol_meta'))

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
        {!! __('portal.sol_hero_title') !!}
    </h1>
    <p class="reveal reveal-delay-1">
        {{ __('portal.sol_hero_desc') }}
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
            {{ __('portal.sol_mw_desc') }}
        </p>
        <div class="solution-features">
            <div class="solution-feat"><div class="solution-feat-dot" style="background:#FFB347;"></div>{{ __('portal.sol_f1_1') }}</div>
            <div class="solution-feat"><div class="solution-feat-dot" style="background:#FFB347;"></div>{{ __('portal.sol_f1_2') }}</div>
            <div class="solution-feat"><div class="solution-feat-dot" style="background:#FFB347;"></div>{{ __('portal.sol_f1_3') }}</div>
            <div class="solution-feat"><div class="solution-feat-dot" style="background:#FFB347;"></div>{{ __('portal.sol_f1_4') }}</div>
        </div>
        <div class="solution-chips">
            <span class="sol-chip" style="border-color: rgba(255,137,4,0.3); color:#FFB347;">🎮 {{ __('portal.chip_multiplayer') }}</span>
            <span class="sol-chip" style="border-color: rgba(255,137,4,0.3); color:#FFB347;">🎯 {{ __('portal.chip_scenario') }}</span>
            <span class="sol-chip" style="border-color: rgba(255,137,4,0.3); color:#FFB347;">📊 {{ __('portal.chip_analytics') }}</span>
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
            {{ __('portal.sol_su_desc') }}
        </p>
        <div class="solution-features">
            <div class="solution-feat"><div class="solution-feat-dot" style="background:#7CC8FF;"></div>{{ __('portal.sol_f2_1') }}</div>
            <div class="solution-feat"><div class="solution-feat-dot" style="background:#7CC8FF;"></div>{{ __('portal.sol_f2_2') }}</div>
            <div class="solution-feat"><div class="solution-feat-dot" style="background:#7CC8FF;"></div>{{ __('portal.sol_f2_3') }}</div>
            <div class="solution-feat"><div class="solution-feat-dot" style="background:#7CC8FF;"></div>{{ __('portal.sol_f2_4') }}</div>
        </div>
        <div class="solution-chips">
            <span class="sol-chip" style="border-color: rgba(67,172,255,0.3); color:#7CC8FF;">🧠 {{ __('portal.chip_ai_eval') }}</span>
            <span class="sol-chip" style="border-color: rgba(67,172,255,0.3); color:#7CC8FF;">📈 {{ __('portal.chip_progressive') }}</span>
            <span class="sol-chip" style="border-color: rgba(67,172,255,0.3); color:#7CC8FF;">📁 {{ __('portal.chip_portfolio') }}</span>
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
            {{ __('portal.sol_rg_desc') }}
        </p>
        <div class="solution-features">
            <div class="solution-feat"><div class="solution-feat-dot" style="background:#B794F4;"></div>{{ __('portal.sol_f3_1') }}</div>
            <div class="solution-feat"><div class="solution-feat-dot" style="background:#B794F4;"></div>{{ __('portal.sol_f3_2') }}</div>
            <div class="solution-feat"><div class="solution-feat-dot" style="background:#B794F4;"></div>{{ __('portal.sol_f3_3') }}</div>
            <div class="solution-feat"><div class="solution-feat-dot" style="background:#B794F4;"></div>{{ __('portal.sol_f3_4') }}</div>
        </div>
        <div class="solution-chips">
            <span class="sol-chip" style="border-color: rgba(141,101,224,0.3); color:#B794F4;">🤖 {{ __('portal.chip_ai_gen') }}</span>
            <span class="sol-chip" style="border-color: rgba(141,101,224,0.3); color:#B794F4;">🌌 {{ __('portal.chip_branching') }}</span>
            <span class="sol-chip" style="border-color: rgba(141,101,224,0.3); color:#B794F4;">👔 {{ __('portal.chip_career') }}</span>
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
            {{ __('portal.sol_coach_desc') }}
        </p>
        <div class="solution-features">
            <div class="solution-feat"><div class="solution-feat-dot" style="background:#F5A3EE;"></div>{{ __('portal.sol_f4_1') }}</div>
            <div class="solution-feat"><div class="solution-feat-dot" style="background:#F5A3EE;"></div>{{ __('portal.sol_f4_2') }}</div>
            <div class="solution-feat"><div class="solution-feat-dot" style="background:#F5A3EE;"></div>{{ __('portal.sol_f4_3') }}</div>
            <div class="solution-feat"><div class="solution-feat-dot" style="background:#F5A3EE;"></div>{{ __('portal.sol_f4_4') }}</div>
        </div>
        <div class="solution-chips">
            <span class="sol-chip" style="border-color: rgba(237,132,228,0.3); color:#F5A3EE;">💬 {{ __('portal.chip_realtime') }}</span>
            <span class="sol-chip" style="border-color: rgba(237,132,228,0.3); color:#F5A3EE;">🎯 {{ __('portal.chip_personalized') }}</span>
            <span class="sol-chip" style="border-color: rgba(237,132,228,0.3); color:#F5A3EE;">🧠 {{ __('portal.chip_ai_powered') }}</span>
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
            {{ __('portal.sol_study_desc') }}
        </p>
        <div class="solution-features">
            <div class="solution-feat"><div class="solution-feat-dot" style="background:#86EFAC;"></div>{{ __('portal.sol_f5_1') }}</div>
            <div class="solution-feat"><div class="solution-feat-dot" style="background:#86EFAC;"></div>{{ __('portal.sol_f5_2') }}</div>
            <div class="solution-feat"><div class="solution-feat-dot" style="background:#86EFAC;"></div>{{ __('portal.sol_f5_3') }}</div>
            <div class="solution-feat"><div class="solution-feat-dot" style="background:#86EFAC;"></div>{{ __('portal.sol_f5_4') }}</div>
        </div>
        <div class="solution-chips">
            <span class="sol-chip" style="border-color: rgba(90,199,128,0.3); color:#86EFAC;">📚 {{ __('portal.chip_subject') }}</span>
            <span class="sol-chip" style="border-color: rgba(90,199,128,0.3); color:#86EFAC;">🎓 {{ __('portal.chip_adaptive') }}</span>
            <span class="sol-chip" style="border-color: rgba(90,199,128,0.3); color:#86EFAC;">💡 {{ __('portal.chip_interactive') }}</span>
        </div>
    </div>
</div>

{{-- CTA --}}
<div class="platform-overview reveal" style="margin-top: 3rem;">
    <h2>
        {{ __('portal.sol_cta_title') }}
    </h2>
    <p>
        {{ __('portal.sol_cta_desc') }}
    </p>
    <a href="{{ route('register.create') }}" class="btn-cta">
        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
        </svg>
        {{ __('portal.hero_btn_start') }}
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