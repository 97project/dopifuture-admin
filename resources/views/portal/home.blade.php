@extends('portal.layout')
@section('title', 'DopiFuture — ' . (app()->getLocale() === 'tr' ? 'Dijital Eğitim Platformu' : 'Digital Education Platform'))
@section('meta_description', app()->getLocale() === 'tr' ? 'DopiFuture ile okulunuzu geleceğe hazırlayın. Dijital eğitim çözümleri.' : 'Prepare your school for the future with DopiFuture. Digital education solutions.')

@section('content')
    <style>
        .hero {
            text-align: center;
            padding: 4rem 0 3rem;
            position: relative;
        }

        .hero h1 {
            font-size: 3.25rem;
            font-weight: 800;
            color: white;
            letter-spacing: -0.03em;
            line-height: 1.15;
            margin-bottom: 1.25rem;
        }

        .hero h1 span {
            background: linear-gradient(135deg, var(--brand-400), #818cf8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero p {
            font-size: 1.15rem;
            color: var(--gray-400);
            max-width: 560px;
            margin: 0 auto 2rem;
        }

        .hero-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-hero {
            padding: 0.875rem 2rem;
            border-radius: 12px;
            font-size: 0.95rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-hero-primary {
            background: linear-gradient(135deg, var(--brand-600), var(--brand-700));
            color: white;
            border: none;
        }

        .btn-hero-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 32px rgba(59, 130, 246, 0.35);
        }

        .btn-hero-outline {
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: var(--gray-300);
            background: transparent;
        }

        .btn-hero-outline:hover {
            border-color: rgba(255, 255, 255, 0.3);
            background: rgba(255, 255, 255, 0.04);
            color: white;
        }

        /* Stats */
        .stats-bar {
            display: flex;
            justify-content: center;
            gap: 3rem;
            margin: 3rem 0;
            padding: 1.5rem 0;
            border-top: 1px solid rgba(255, 255, 255, 0.06);
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        }

        .stat-item {
            text-align: center;
        }

        .stat-num {
            font-size: 2rem;
            font-weight: 800;
            color: white;
        }

        .stat-label {
            font-size: 0.8rem;
            color: var(--gray-500);
            margin-top: 0.25rem;
        }

        /* Features Grid */
        .features-section {
            padding: 3rem 0;
        }

        .features-section h2 {
            text-align: center;
            font-size: 2rem;
            font-weight: 700;
            color: white;
            margin-bottom: 0.75rem;
        }

        .features-section .subtitle {
            text-align: center;
            color: var(--gray-400);
            margin-bottom: 2.5rem;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
        }

        @media (max-width: 768px) {
            .features-grid {
                grid-template-columns: 1fr;
            }

            .hero h1 {
                font-size: 2rem;
            }

            .stats-bar {
                flex-direction: column;
                gap: 1.5rem;
            }
        }

        .feature-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.07);
            border-radius: 16px;
            padding: 1.75rem;
            transition: all 0.3s;
        }

        .feature-card:hover {
            border-color: rgba(59, 130, 246, 0.3);
            transform: translateY(-3px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.3);
        }

        .feature-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }

        .feature-card h3 {
            font-size: 1.05rem;
            font-weight: 600;
            color: white;
            margin-bottom: 0.5rem;
        }

        .feature-card p {
            font-size: 0.85rem;
            color: var(--gray-400);
            line-height: 1.6;
        }

        /* CTA */
        .cta-section {
            text-align: center;
            padding: 3rem 0;
            margin-top: 2rem;
            background: rgba(59, 130, 246, 0.05);
            border: 1px solid rgba(59, 130, 246, 0.1);
            border-radius: 24px;
        }

        .cta-section h2 {
            font-size: 1.75rem;
            font-weight: 700;
            color: white;
            margin-bottom: 0.75rem;
        }

        .cta-section p {
            color: var(--gray-400);
            margin-bottom: 1.5rem;
        }
    </style>

    {{-- Hero --}}
    <section class="hero">
        <h1>
            @if(app()->getLocale() === 'tr')
                Eğitimde <span>Dijital Dönüşüm</span><br>Burada Başlıyor
            @else
                Digital Transformation in<br><span>Education Starts Here</span>
            @endif
        </h1>
        <p>
            @if(app()->getLocale() === 'tr')
                DopiFuture, okulların dijital eğitim süreçlerini yönetmeleri için tasarlanmış modern bir platformdur.
            @else
                DopiFuture is a modern platform designed for schools to manage their digital education processes.
            @endif
        </p>
        <div class="hero-actions">
            <a href="{{ route('register.create') }}" class="btn-hero btn-hero-primary">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                {{ app()->getLocale() === 'tr' ? 'Hemen Başla' : 'Get Started' }}
            </a>
            <a href="{{ route('portal.solutions') }}" class="btn-hero btn-hero-outline">
                {{ app()->getLocale() === 'tr' ? 'Çözümleri İncele' : 'Explore Solutions' }}
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                </svg>
            </a>
        </div>
    </section>

    {{-- Stats Bar --}}
    <div class="stats-bar">
        <div class="stat-item">
            <div class="stat-num">{{ $appCount }}</div>
            <div class="stat-label">{{ app()->getLocale() === 'tr' ? 'Uygulama' : 'Applications' }}</div>
        </div>
        <div class="stat-item">
            <div class="stat-num">{{ $schoolCount }}+</div>
            <div class="stat-label">{{ app()->getLocale() === 'tr' ? 'Okul' : 'Schools' }}</div>
        </div>
        <div class="stat-item">
            <div class="stat-num">9</div>
            <div class="stat-label">{{ app()->getLocale() === 'tr' ? 'Kullanıcı Rolü' : 'User Roles' }}</div>
        </div>
        <div class="stat-item">
            <div class="stat-num">2</div>
            <div class="stat-label">{{ app()->getLocale() === 'tr' ? 'Dil Desteği' : 'Languages' }}</div>
        </div>
    </div>

    {{-- Features --}}
    <section class="features-section">
        <h2>{{ app()->getLocale() === 'tr' ? 'Neden DopiFuture?' : 'Why DopiFuture?' }}</h2>
        <p class="subtitle">
            {{ app()->getLocale() === 'tr' ? 'Eğitim yönetimi için ihtiyacınız olan her şey tek bir platformda.' : 'Everything you need for education management in a single platform.' }}
        </p>

        <div class="features-grid">
            {{-- Feature 1 --}}
            <div class="feature-card">
                <div class="feature-icon" style="background: rgba(59,130,246,0.15);">
                    <svg width="22" height="22" fill="none" stroke="#60a5fa" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <h3>{{ app()->getLocale() === 'tr' ? 'Okul Yönetimi' : 'School Management' }}</h3>
                <p>{{ app()->getLocale() === 'tr' ? 'Okullarınızı, sınıflarınızı ve personel atamalarınızı merkezi bir panelden yönetin.' : 'Manage your schools, classes, and staff assignments from a centralized panel.' }}
                </p>
            </div>

            {{-- Feature 2 --}}
            <div class="feature-card">
                <div class="feature-icon" style="background: rgba(168,85,247,0.15);">
                    <svg width="22" height="22" fill="none" stroke="#c084fc" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                    </svg>
                </div>
                <h3>{{ app()->getLocale() === 'tr' ? 'Lisans Yönetimi' : 'License Management' }}</h3>
                <p>{{ app()->getLocale() === 'tr' ? 'Kontenjan bazlı lisanslama ile kullanıcı erişimlerini kontrol edin.' : 'Control user access with seat-based licensing and expiry tracking.' }}
                </p>
            </div>

            {{-- Feature 3 --}}
            <div class="feature-card">
                <div class="feature-icon" style="background: rgba(34,197,94,0.15);">
                    <svg width="22" height="22" fill="none" stroke="#4ade80" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <h3>{{ app()->getLocale() === 'tr' ? 'Rol Tabanlı Erişim' : 'Role-Based Access' }}</h3>
                <p>{{ app()->getLocale() === 'tr' ? '9 farklı kullanıcı rolü ile her seviyede güvenli yetkilendirme.' : '9 different user roles with secure authorization at every level.' }}
                </p>
            </div>

            {{-- Feature 4 --}}
            <div class="feature-card">
                <div class="feature-icon" style="background: rgba(251,146,60,0.15);">
                    <svg width="22" height="22" fill="none" stroke="#fb923c" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                </div>
                <h3>{{ app()->getLocale() === 'tr' ? 'Modüler Uygulamalar' : 'Modular Applications' }}</h3>
                <p>{{ app()->getLocale() === 'tr' ? 'İhtiyacınıza göre uygulamaları etkinleştirin ve kullanıcılara atayın.' : 'Enable applications as needed and assign them to users.' }}
                </p>
            </div>

            {{-- Feature 5 --}}
            <div class="feature-card">
                <div class="feature-icon" style="background: rgba(236,72,153,0.15);">
                    <svg width="22" height="22" fill="none" stroke="#f472b6" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" />
                    </svg>
                </div>
                <h3>{{ app()->getLocale() === 'tr' ? 'Çok Dilli Destek' : 'Multi-Language Support' }}</h3>
                <p>{{ app()->getLocale() === 'tr' ? 'Türkçe ve İngilizce tam destek ile uluslararası kullanım.' : 'Full Turkish and English support for international usage.' }}
                </p>
            </div>

            {{-- Feature 6 --}}
            <div class="feature-card">
                <div class="feature-icon" style="background: rgba(56,189,248,0.15);">
                    <svg width="22" height="22" fill="none" stroke="#38bdf8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
                <h3>{{ app()->getLocale() === 'tr' ? 'Detaylı Raporlama' : 'Detailed Reporting' }}</h3>
                <p>{{ app()->getLocale() === 'tr' ? 'Aktivite logları ve kullanım istatistikleriyle okulunuzu izleyin.' : 'Monitor your school with activity logs and usage statistics.' }}
                </p>
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="cta-section">
        <h2>{{ app()->getLocale() === 'tr' ? 'Okulunuzu Dijital Geleceğe Taşıyın' : 'Bring Your School to the Digital Future' }}
        </h2>
        <p>{{ app()->getLocale() === 'tr' ? 'Ücretsiz kayıt olun ve DopiFuture platformunu keşfedin.' : 'Register for free and explore the DopiFuture platform.' }}
        </p>
        <a href="{{ route('register.create') }}" class="btn-hero btn-hero-primary">
            {{ app()->getLocale() === 'tr' ? 'Okulumu Kaydet' : 'Register My School' }}
        </a>
    </section>
@endsection