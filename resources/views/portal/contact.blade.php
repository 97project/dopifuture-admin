@extends('portal.layout')
@section('title', app()->getLocale() === 'tr' ? 'İletişim' : 'Contact')

@section('content')
    <style>
        .contact-grid {
            display: grid;
            grid-template-columns: 1fr 1.2fr;
            gap: 2.5rem;
            align-items: start;
        }

        @media (max-width: 768px) {
            .contact-grid {
                grid-template-columns: 1fr;
            }
        }

        .contact-info h1 {
            font-size: 2rem;
            font-weight: 800;
            color: white;
            margin-bottom: 0.75rem;
            letter-spacing: -0.025em;
        }

        .contact-info>p {
            color: var(--gray-400);
            margin-bottom: 2rem;
        }

        .info-item {
            display: flex;
            align-items: flex-start;
            gap: 0.875rem;
            margin-bottom: 1.5rem;
        }

        .info-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: rgba(59, 130, 246, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .info-label {
            font-size: 0.75rem;
            color: var(--gray-500);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.2rem;
        }

        .info-value {
            color: white;
            font-size: 0.95rem;
        }

        .info-value a {
            color: var(--brand-400);
            text-decoration: none;
        }

        .info-value a:hover {
            text-decoration: underline;
        }
    </style>

    <div class="contact-grid">
        {{-- Left: Info --}}
        <div class="contact-info">
            <h1>{{ app()->getLocale() === 'tr' ? 'Bizimle İletişime Geçin' : 'Get in Touch' }}</h1>
            <p>{{ app()->getLocale() === 'tr' ? 'Sorularınız veya işbirliği talepleriniz için bizimle iletişime geçin.' : 'Contact us for questions or collaboration requests.' }}
            </p>

            <div class="info-item">
                <div class="info-icon">
                    <svg width="20" height="20" fill="none" stroke="#60a5fa" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <div class="info-label">{{ __('admin.email') }}</div>
                    <div class="info-value"><a href="mailto:info@dopifuture.com">info@dopifuture.com</a></div>
                </div>
            </div>

            <div class="info-item">
                <div class="info-icon">
                    <svg width="20" height="20" fill="none" stroke="#60a5fa" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <div>
                    <div class="info-label">{{ app()->getLocale() === 'tr' ? 'Adres' : 'Address' }}</div>
                    <div class="info-value">{{ app()->getLocale() === 'tr' ? 'İstanbul, Türkiye' : 'Istanbul, Turkey' }}
                    </div>
                </div>
            </div>

            <div class="info-item">
                <div class="info-icon">
                    <svg width="20" height="20" fill="none" stroke="#60a5fa" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <div class="info-label">{{ app()->getLocale() === 'tr' ? 'Çalışma Saatleri' : 'Working Hours' }}</div>
                    <div class="info-value">
                        {{ app()->getLocale() === 'tr' ? 'Pazartesi - Cuma, 09:00 - 18:00' : 'Monday - Friday, 09:00 - 18:00' }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Right: Form --}}
        <div class="form-card">
            @if(session('success'))
                <div class="alert-success" style="margin-bottom: 1.25rem;">
                    <svg style="display:inline; width:18px; height:18px; vertical-align: text-bottom; margin-right: 6px;"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('portal.contact.store') }}" method="POST">
                @csrf
                <div class="form-grid-2" style="margin-bottom: 1.25rem;">
                    <div>
                        <label class="form-label">{{ app()->getLocale() === 'tr' ? 'İsim' : 'Name' }} *</label>
                        <input type="text" name="name" value="{{ old('name') }}" required class="form-input"
                            placeholder="{{ app()->getLocale() === 'tr' ? 'Adınız Soyadınız' : 'Full name' }}">
                        @error('name') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">{{ __('admin.email') }} *</label>
                        <input type="email" name="email" value="{{ old('email') }}" required class="form-input"
                            placeholder="you@example.com">
                        @error('email') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div style="margin-bottom: 1.25rem;">
                    <label class="form-label">{{ app()->getLocale() === 'tr' ? 'Konu' : 'Subject' }} *</label>
                    <input type="text" name="subject" value="{{ old('subject') }}" required class="form-input"
                        placeholder="{{ app()->getLocale() === 'tr' ? 'Mesajınızın konusu' : 'Subject of your message' }}">
                    @error('subject') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <label class="form-label">{{ app()->getLocale() === 'tr' ? 'Mesaj' : 'Message' }} *</label>
                    <textarea name="message" rows="5" required class="form-textarea"
                        placeholder="{{ app()->getLocale() === 'tr' ? 'Mesajınızı buraya yazın...' : 'Write your message here...' }}">{{ old('message') }}</textarea>
                    @error('message') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <button type="submit" class="btn-primary" style="width: 100%; justify-content: center;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                    {{ app()->getLocale() === 'tr' ? 'Mesajı Gönder' : 'Send Message' }}
                </button>
            </form>
        </div>
    </div>
@endsection