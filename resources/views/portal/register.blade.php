@extends('portal.layout')
@section('title', __('admin.new_registration_request'))
@section('meta_description', 'Register your school on DopiFuture digital education platform')

@section('content')
    <div style="max-width: 640px; margin: 0 auto;">

        {{-- Hero --}}
        <div style="text-align: center; margin-bottom: 2.5rem;">
            <h1 style="font-size: 2rem; font-weight: 800; color: white; margin-bottom: 0.75rem; letter-spacing: -0.025em;">
                {{ __('admin.new_registration_request') }}
            </h1>
            <p style="color: var(--gray-400); font-size: 1rem; max-width: 420px; margin: 0 auto;">
                {{ app()->getLocale() === 'tr'
        ? 'Okulunuzu DopiFuture platformuna kaydedin. En kısa sürede sizinle iletişime geçeceğiz.'
        : 'Register your school on the DopiFuture platform. We will contact you as soon as possible.' }}
            </p>
        </div>

        {{-- Success Message --}}
        @if(session('success'))
            <div class="alert-success">
                <svg style="display:inline; width:18px; height:18px; vertical-align: text-bottom; margin-right: 6px;"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ session('success') }}
            </div>
        @endif

        {{-- Form --}}
        <div class="form-card">
            <form action="{{ route('register.store') }}" method="POST">
                @csrf

                {{-- School Name + Country --}}
                <div class="form-grid-2" style="margin-bottom: 1.25rem;">
                    <div>
                        <label class="form-label">{{ __('admin.school_name') }} *</label>
                        <input type="text" name="school_name" value="{{ old('school_name') }}" required class="form-input"
                            placeholder="{{ app()->getLocale() === 'tr' ? 'Örn: Atatürk İlkokulu' : 'e.g. Atatürk Primary School' }}">
                        @error('school_name') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">{{ __('admin.country') }}</label>
                        <input type="text" name="country" value="{{ old('country') }}" class="form-input"
                            placeholder="{{ app()->getLocale() === 'tr' ? 'Türkiye' : 'Turkey' }}">
                    </div>
                </div>

                {{-- Contact Name + Surname --}}
                <div class="form-grid-2" style="margin-bottom: 1.25rem;">
                    <div>
                        <label class="form-label">{{ __('admin.contact_name') }} *</label>
                        <input type="text" name="contact_name" value="{{ old('contact_name') }}" required class="form-input"
                            placeholder="{{ app()->getLocale() === 'tr' ? 'Adınız' : 'First name' }}">
                        @error('contact_name') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">{{ __('admin.contact_surname') }} *</label>
                        <input type="text" name="contact_surname" value="{{ old('contact_surname') }}" required
                            class="form-input" placeholder="{{ app()->getLocale() === 'tr' ? 'Soyadınız' : 'Last name' }}">
                        @error('contact_surname') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Email + Phone --}}
                <div class="form-grid-2" style="margin-bottom: 1.25rem;">
                    <div>
                        <label class="form-label">{{ __('admin.email') }} *</label>
                        <input type="email" name="email" value="{{ old('email') }}" required class="form-input"
                            placeholder="admin@school.edu.tr">
                        @error('email') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">{{ __('admin.phone') }}</label>
                        <input type="tel" name="phone" value="{{ old('phone') }}" class="form-input"
                            placeholder="+90 5XX XXX XX XX">
                    </div>
                </div>

                {{-- Notes --}}
                <div style="margin-bottom: 1.5rem;">
                    <label class="form-label">{{ __('admin.notes') }}</label>
                    <textarea name="notes" class="form-textarea"
                        placeholder="{{ app()->getLocale() === 'tr' ? 'Ek bilgi veya talepleriniz...' : 'Additional information or requests...' }}">{{ old('notes') }}</textarea>
                </div>

                {{-- Submit --}}
                <button type="submit" class="btn-primary" style="width: 100%; justify-content: center;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                    {{ app()->getLocale() === 'tr' ? 'Kayıt Talebini Gönder' : 'Submit Registration Request' }}
                </button>
            </form>
        </div>

        {{-- Trust Indicators --}}
        <div
            style="margin-top: 2rem; display: flex; justify-content: center; gap: 2rem; color: var(--gray-500); font-size: 0.8rem;">
            <div style="display: flex; align-items: center; gap: 0.4rem;">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                {{ app()->getLocale() === 'tr' ? 'Güvenli Bağlantı' : 'Secure Connection' }}
            </div>
            <div style="display: flex; align-items: center; gap: 0.4rem;">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                {{ app()->getLocale() === 'tr' ? 'KVKK Uyumlu' : 'GDPR Compliant' }}
            </div>
        </div>
    </div>
@endsection