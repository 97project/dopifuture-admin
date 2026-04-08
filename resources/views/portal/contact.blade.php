@extends('portal.layout')
@section('title', app()->getLocale() === 'tr' ? 'İletişim — DopiFuture' : 'Contact — DopiFuture')
@section('meta_description', app()->getLocale() === 'tr' ? 'DopiFuture ile iletişime geçin' : 'Get in touch with DopiFuture')

@section('content')
<style>
/* ═══════════════════════════════════════════
   CONTACT PAGE
   ═══════════════════════════════════════════ */
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(40px); }
    to   { opacity: 1; transform: translateY(0); }
}
.reveal { opacity: 0; transform: translateY(40px); transition: all 0.8s cubic-bezier(0.16,1,0.3,1); }
.reveal.visible { opacity: 1; transform: translateY(0); }
.reveal-delay-1 { transition-delay: 0.15s; }
.reveal-delay-2 { transition-delay: 0.3s; }

@media (prefers-reduced-motion: reduce) { .reveal { transition: none; opacity: 1; transform: none; } }

/* Hero */
.contact-hero {
    text-align: center; padding: 3rem 0 2.5rem;
}
.contact-hero h1 {
    font-size: 2.75rem; font-weight: 900; color: white;
    letter-spacing: -0.03em; margin-bottom: 0.75rem;
}
.contact-hero h1 span {
    background: linear-gradient(135deg, #60a5fa, #c084fc);
    -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
}
.contact-hero p {
    color: var(--gray-400); font-size: 1.05rem; max-width: 500px; margin: 0 auto; line-height: 1.7;
}

/* Main Grid */
.contact-grid {
    display: grid; grid-template-columns: 1fr 1.3fr; gap: 2.5rem;
    align-items: start; margin-top: 1rem;
}

/* Left Info */
.contact-info-section { display: flex; flex-direction: column; gap: 1.5rem; }

.info-card {
    background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.06);
    border-radius: 18px; padding: 1.75rem; transition: all 0.3s;
}
.info-card:hover {
    border-color: rgba(59,130,246,0.2); transform: translateY(-3px);
    box-shadow: 0 12px 40px rgba(0,0,0,0.2);
}
.info-card-header {
    display: flex; align-items: center; gap: 0.875rem; margin-bottom: 0.75rem;
}
.info-card-icon {
    width: 44px; height: 44px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.info-card-title { font-size: 0.7rem; color: var(--gray-500); text-transform: uppercase; letter-spacing: 0.06em; font-weight: 700; }
.info-card-value { font-size: 0.95rem; color: white; font-weight: 600; }
.info-card-value a { color: var(--brand-400); text-decoration: none; transition: color 0.2s; }
.info-card-value a:hover { color: #93bbfc; text-decoration: underline; }

/* Social Links */
.social-links {
    display: flex; gap: 0.75rem; margin-top: 0.5rem;
}
.social-link {
    width: 44px; height: 44px; border-radius: 12px;
    background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08);
    display: flex; align-items: center; justify-content: center;
    transition: all 0.3s; text-decoration: none;
}
.social-link:hover {
    background: rgba(59,130,246,0.15); border-color: rgba(59,130,246,0.3);
    transform: translateY(-3px);
}
.social-link svg { width: 20px; height: 20px; color: var(--gray-400); transition: color 0.3s; }
.social-link:hover svg { color: var(--brand-400); }

/* Form Card */
.contact-form-card {
    background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.06);
    border-radius: 24px; padding: 2.5rem; position: relative; overflow: hidden;
}
.contact-form-card::before {
    content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
    background: linear-gradient(90deg, var(--brand-500), #8b5cf6, var(--brand-500));
    border-radius: 24px 24px 0 0;
}
.form-title {
    font-size: 1.25rem; font-weight: 800; color: white; margin-bottom: 1.75rem;
    display: flex; align-items: center; gap: 0.6rem;
}
.form-row { margin-bottom: 1.25rem; }
.form-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 1.25rem; }
.form-label-v2 {
    display: block; font-size: 0.8rem; font-weight: 700; color: var(--gray-300);
    margin-bottom: 0.5rem; letter-spacing: 0.01em;
}
.form-input-v2 {
    width: 100%; padding: 0.75rem 1rem; border-radius: 12px;
    border: 1.5px solid rgba(255,255,255,0.08); background: rgba(255,255,255,0.03);
    color: white; font-size: 0.9rem; font-family: inherit; transition: all 0.25s;
}
.form-input-v2:focus {
    outline: none; border-color: var(--brand-500);
    box-shadow: 0 0 0 3px rgba(59,130,246,0.12);
    background: rgba(255,255,255,0.05);
}
.form-input-v2::placeholder { color: var(--gray-600); }
textarea.form-input-v2 { resize: vertical; min-height: 120px; }

.btn-submit {
    display: inline-flex; align-items: center; gap: 0.6rem;
    padding: 0.875rem 2rem; border-radius: 12px; border: none;
    background: linear-gradient(135deg, var(--brand-500), #7c3aed);
    color: white; font-size: 0.95rem; font-weight: 700;
    font-family: inherit; cursor: pointer; transition: all 0.3s; width: 100%;
    justify-content: center;
}
.btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 36px rgba(59,130,246,0.3);
}

/* FAQ */
.faq-section { margin-top: 4rem; }
.faq-title {
    text-align: center; font-size: 2rem; font-weight: 800;
    color: white; margin-bottom: 0.75rem; letter-spacing: -0.02em;
}
.faq-subtitle {
    text-align: center; color: var(--gray-400); font-size: 1rem;
    margin-bottom: 2.5rem; max-width: 480px; margin-left: auto; margin-right: auto;
}
.faq-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }

.faq-item {
    background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.06);
    border-radius: 16px; overflow: hidden; transition: all 0.3s;
}
.faq-item:hover { border-color: rgba(59,130,246,0.15); }
.faq-item.open { border-color: rgba(59,130,246,0.2); }

.faq-question {
    width: 100%; padding: 1.25rem 1.5rem; display: flex;
    align-items: center; justify-content: space-between; gap: 1rem;
    background: none; border: none; color: white; font-size: 0.9rem;
    font-weight: 700; font-family: inherit; cursor: pointer; text-align: left;
    transition: background 0.2s;
}
.faq-question:hover { background: rgba(255,255,255,0.02); }
.faq-chevron {
    width: 20px; height: 20px; flex-shrink: 0;
    transition: transform 0.3s; color: var(--gray-500);
}
.faq-item.open .faq-chevron { transform: rotate(180deg); color: var(--brand-400); }

.faq-answer {
    max-height: 0; overflow: hidden; transition: max-height 0.4s ease, padding 0.3s ease;
}
.faq-item.open .faq-answer { max-height: 300px; }
.faq-answer-inner {
    padding: 0 1.5rem 1.25rem; font-size: 0.88rem;
    color: var(--gray-400); line-height: 1.8;
}

/* Responsive */
@media (max-width: 768px) {
    .contact-grid { grid-template-columns: 1fr; }
    .faq-grid { grid-template-columns: 1fr; }
    .form-grid-2 { grid-template-columns: 1fr; }
    .contact-hero h1 { font-size: 2rem; }
}
</style>

{{-- Hero --}}
<section class="contact-hero">
    <h1 class="reveal">
        @if(app()->getLocale() === 'tr')
            Bize <span>Ulaşın</span>
        @else
            Get in <span>Touch</span>
        @endif
    </h1>
    <p class="reveal reveal-delay-1">
        @if(app()->getLocale() === 'tr')
            Sorularınız veya işbirliği talepleriniz için bizimle iletişime geçin.
        @else
            Reach out to us for questions, collaboration requests, or to schedule a demo.
        @endif
    </p>
</section>

{{-- Main Content --}}
<div class="contact-grid">
    {{-- Left: Info Cards --}}
    <div class="contact-info-section reveal">
        <div class="info-card">
            <div class="info-card-header">
                <div class="info-card-icon" style="background: rgba(59,130,246,0.12);">
                    <svg width="22" height="22" fill="none" stroke="#60a5fa" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </div>
                <div>
                    <div class="info-card-title">{{ __('admin.email') }}</div>
                    <div class="info-card-value"><a href="mailto:info@dopifuture.com">info@dopifuture.com</a></div>
                </div>
            </div>
        </div>

        <div class="info-card">
            <div class="info-card-header">
                <div class="info-card-icon" style="background: rgba(168,85,247,0.12);">
                    <svg width="22" height="22" fill="none" stroke="#c084fc" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div>
                    <div class="info-card-title">{{ app()->getLocale() === 'tr' ? 'Adres' : 'Address' }}</div>
                    <div class="info-card-value">Istanbul, Turkey</div>
                </div>
            </div>
        </div>

        <div class="info-card">
            <div class="info-card-header">
                <div class="info-card-icon" style="background: rgba(34,197,94,0.12);">
                    <svg width="22" height="22" fill="none" stroke="#4ade80" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <div class="info-card-title">{{ app()->getLocale() === 'tr' ? 'Çalışma Saatleri' : 'Working Hours' }}</div>
                    <div class="info-card-value">{{ app()->getLocale() === 'tr' ? 'Pazartesi – Cuma, 09:00 – 18:00' : 'Monday – Friday, 09:00 – 18:00' }}</div>
                </div>
            </div>
        </div>

        {{-- Social Links --}}
        <div style="padding-top: 0.5rem;">
            <div style="font-size: 0.7rem; color: var(--gray-500); text-transform: uppercase; letter-spacing: 0.06em; font-weight: 700; margin-bottom: 0.75rem;">
                {{ app()->getLocale() === 'tr' ? 'Sosyal Medya' : 'Social Media' }}
            </div>
            <div class="social-links">
                <a href="https://linkedin.com/company/dopifuture" target="_blank" class="social-link" title="LinkedIn">
                    <svg fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                </a>
                <a href="https://twitter.com/dopifuture" target="_blank" class="social-link" title="X / Twitter">
                    <svg fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                </a>
                <a href="https://instagram.com/dopifuture" target="_blank" class="social-link" title="Instagram">
                    <svg fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                </a>
            </div>
        </div>
    </div>

    {{-- Right: Form --}}
    <div class="contact-form-card reveal reveal-delay-1">
        <div class="form-title">
            <svg width="22" height="22" fill="none" stroke="var(--brand-400)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
            {{ app()->getLocale() === 'tr' ? 'Mesaj Gönderin' : 'Send a Message' }}
        </div>

        @if(session('success'))
            <div class="alert-success" style="margin-bottom: 1.5rem; border-radius: 12px;">
                <svg style="display:inline; width:18px; height:18px; vertical-align: text-bottom; margin-right:6px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('portal.contact.store') }}" method="POST">
            @csrf
            <div class="form-grid-2">
                <div>
                    <label class="form-label-v2">{{ app()->getLocale() === 'tr' ? 'Adınız' : 'Your Name' }} *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="form-input-v2"
                        placeholder="{{ app()->getLocale() === 'tr' ? 'Ad Soyad' : 'Full name' }}">
                    @error('name') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label-v2">{{ __('admin.email') }} *</label>
                    <input type="email" name="email" value="{{ old('email') }}" required class="form-input-v2"
                        placeholder="you@example.com">
                    @error('email') <p class="form-error">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="form-row">
                <label class="form-label-v2">{{ app()->getLocale() === 'tr' ? 'Konu' : 'Subject' }} *</label>
                <input type="text" name="subject" value="{{ old('subject') }}" required class="form-input-v2"
                    placeholder="{{ app()->getLocale() === 'tr' ? 'Mesajınızın konusu' : 'Subject of your message' }}">
                @error('subject') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div class="form-row">
                <label class="form-label-v2">{{ app()->getLocale() === 'tr' ? 'Mesaj' : 'Message' }} *</label>
                <textarea name="message" rows="5" required class="form-input-v2"
                    placeholder="{{ app()->getLocale() === 'tr' ? 'Mesajınızı buraya yazın...' : 'Write your message here...' }}">{{ old('message') }}</textarea>
                @error('message') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <button type="submit" class="btn-submit">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                </svg>
                {{ app()->getLocale() === 'tr' ? 'Mesaj Gönder' : 'Send Message' }}
            </button>
        </form>
    </div>
</div>

{{-- ═══════════════ FAQ ═══════════════ --}}
<section class="faq-section">
    <h2 class="faq-title reveal">
        @if(app()->getLocale() === 'tr')
            Sıkça Sorulan Sorular
        @else
            Frequently Asked Questions
        @endif
    </h2>
    <p class="faq-subtitle reveal reveal-delay-1">
        @if(app()->getLocale() === 'tr')
            DopiFuture hakkında merak edilen sorular ve yanıtları.
        @else
            Common questions about DopiFuture and their answers.
        @endif
    </p>

    <div class="faq-grid">
        {{-- FAQ 1 --}}
        <div class="faq-item reveal">
            <button class="faq-question" onclick="this.parentElement.classList.toggle('open')">
                <span>{{ app()->getLocale() === 'tr' ? 'DopiFuture hangi yaş gruplarına uygundur?' : 'What age groups is DopiFuture suitable for?' }}</span>
                <svg class="faq-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    {{ app()->getLocale() === 'tr' ? 'DopiFuture, ilkokul, ortaokul ve lise öğrencileri için tasarlanmıştır. Simülasyonlar ve AI koçluk modülleri öğrencinin sınıf seviyesine göre otomatik olarak uyarlanır. Study Space modülü ders bazında konu seçimi ve zorluk seviyesi ayarı sunar.' : 'DopiFuture is designed for elementary, middle, and high school students. Simulations and AI coaching modules automatically adapt to the student\'s grade level. The Study Space module offers subject-specific topic selection and difficulty level adjustment.' }}
                </div>
            </div>
        </div>

        {{-- FAQ 2 --}}
        <div class="faq-item reveal reveal-delay-1">
            <button class="faq-question" onclick="this.parentElement.classList.toggle('open')">
                <span>{{ app()->getLocale() === 'tr' ? 'Uygulamalar tek tek mi yoksa paket olarak mı alınır?' : 'Are apps purchased individually or as a package?' }}</span>
                <svg class="faq-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    {{ app()->getLocale() === 'tr' ? 'DopiFuture modüler bir yapıdadır. Okullar, ihtiyaçlarına göre tek bir uygulama veya tüm ekosistemi kapsayan paketler alabilir. Lisans yönetimi ile her uygulama için ayrı kontenjan ve süre belirlenebilir.' : 'DopiFuture has a modular structure. Schools can license individual applications or choose packages covering the entire ecosystem. The license management system allows setting separate quotas and durations for each application.' }}
                </div>
            </div>
        </div>

        {{-- FAQ 3 --}}
        <div class="faq-item reveal">
            <button class="faq-question" onclick="this.parentElement.classList.toggle('open')">
                <span>{{ app()->getLocale() === 'tr' ? 'Mission Way simülasyonlarında kaç oyuncu gerekli?' : 'How many players are needed in Mission Way simulations?' }}</span>
                <svg class="faq-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    {{ app()->getLocale() === 'tr' ? 'Her simülasyon senaryosunun kendine özgü rol sayısı vardır. Örneğin Earthquake simülasyonu 4 oyuncu gerektirirken, başka senaryolar farklı sayıda rol içerebilir. Sistem, öğretmene doğru sayıda öğrenci seçmesi için otomatik uyarılar gösterir.' : 'Each simulation scenario has its own specific number of roles. For example, the Earthquake simulation requires 4 players, while other scenarios may include different numbers of roles. The system automatically alerts teachers to select the correct number of students.' }}
                </div>
            </div>
        </div>

        {{-- FAQ 4 --}}
        <div class="faq-item reveal reveal-delay-1">
            <button class="faq-question" onclick="this.parentElement.classList.toggle('open')">
                <span>{{ app()->getLocale() === 'tr' ? 'Öğrenciler platforma nasıl erişir?' : 'How do students access the platform?' }}</span>
                <svg class="faq-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    {{ app()->getLocale() === 'tr' ? 'Öğrenciler DopiFuture mobil uygulamasını (iOS/Android) indirerek erişir. Google veya Apple hesabı ile tek tıkla giriş yapabilirler. Öğretmenler ve yöneticiler ise hem web panelden hem mobil uygulamadan sisteme erişir.' : 'Students access via the DopiFuture mobile app (iOS/Android). They can sign in with a single tap using Google or Apple accounts. Teachers and administrators access the system from both the web panel and mobile app.' }}
                </div>
            </div>
        </div>

        {{-- FAQ 5 --}}
        <div class="faq-item reveal">
            <button class="faq-question" onclick="this.parentElement.classList.toggle('open')">
                <span>{{ app()->getLocale() === 'tr' ? 'WAY AI Coach kişiselleştirmeyi nasıl yapıyor?' : 'How does WAY AI Coach personalize content?' }}</span>
                <svg class="faq-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    {{ app()->getLocale() === 'tr' ? 'WAY AI Coach, öğrencinin profil bilgilerini (sınıf seviyesi, ilgi alanları, güçlü/zayıf yönler) kullanarak her konuşmayı kişiselleştirir. Gerçek zamanlı WebSocket bağlantısı ile akıcı sohbet deneyimi sunar ve tüm oturumlar geçmişte saklanır.' : 'WAY AI Coach personalizes every conversation using the student\'s profile (grade level, interests, strengths/weaknesses). It provides a fluid chat experience via real-time WebSocket connection, and all sessions are stored in history for review.' }}
                </div>
            </div>
        </div>

        {{-- FAQ 6 --}}
        <div class="faq-item reveal reveal-delay-1">
            <button class="faq-question" onclick="this.parentElement.classList.toggle('open')">
                <span>{{ app()->getLocale() === 'tr' ? 'Raporlama ve takip imkanları nelerdir?' : 'What reporting and tracking capabilities are available?' }}</span>
                <svg class="faq-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    {{ app()->getLocale() === 'tr' ? 'Öğretmenler her uygulama için ayrı raporlama paneline sahiptir. Mission Way simülasyon skorları, Way Startup Lab adım ilerlemeleri, WAY AI Coach oturum sayıları ve Study Space öğrenme istatistikleri detaylı olarak takip edilir. Görev ataması ve son tarih yönetimi de mevcuttur.' : 'Teachers have separate reporting panels for each application. Mission Way simulation scores, Way Startup Lab step progress, WAY AI Coach session counts, and Study Space learning statistics are tracked in detail. Assignment management and deadline tracking are also available.' }}
                </div>
            </div>
        </div>
    </div>
</section>
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