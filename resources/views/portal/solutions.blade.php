@extends('portal.layout')
@section('title', 'Solutions')
@section('meta_description', 'DopiFuture digital education applications')

@section('content')
    <style>
        .solutions-hero {
            text-align: center;
            padding: 2rem 0 3rem;
        }

        .solutions-hero h1 {
            font-size: 2.5rem;
            font-weight: 800;
            color: white;
            letter-spacing: -0.025em;
            margin-bottom: 0.75rem;
        }

        .solutions-hero p {
            color: var(--gray-400);
            font-size: 1.05rem;
            max-width: 480px;
            margin: 0 auto;
        }

        .apps-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1.5rem;
        }

        .app-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.07);
            border-radius: 20px;
            padding: 2rem;
            transition: all 0.35s;
            position: relative;
            overflow: hidden;
        }

        .app-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            border-radius: 20px 20px 0 0;
            transition: height 0.3s;
        }

        .app-card:hover {
            border-color: rgba(255, 255, 255, 0.15);
            transform: translateY(-4px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.4);
        }

        .app-card:hover::before {
            height: 4px;
        }

        .app-icon-wrap {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.25rem;
            font-size: 1.5rem;
        }

        .app-card h3 {
            font-size: 1.2rem;
            font-weight: 700;
            color: white;
            margin-bottom: 0.5rem;
        }

        .app-card p {
            font-size: 0.9rem;
            color: var(--gray-400);
            line-height: 1.7;
        }

        .app-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            margin-top: 1rem;
            padding: 0.35rem 0.75rem;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .empty-state {
            text-align: center;
            padding: 4rem 0;
            color: var(--gray-500);
        }
    </style>

    {{-- Hero --}}
    <section class="solutions-hero">
        <h1>{{ 'Digital Education Solutions' }}</h1>
        <p>{{ 'Discover applications tailored to your needs and integrate them into your school.' }}
        </p>
    </section>

    {{-- Applications Grid --}}
    @if($applications->count())
        <div class="apps-grid">
            @foreach($applications as $app)
                <div class="app-card" style="--app-color: {{ $app->color ?? '#3b82f6' }};">
                    <style>
                        .app-card:nth-child({{ $loop->iteration }})::before {
                            background:
                                {{ $app->color ?? '#3b82f6' }}
                            ;
                        }
                    </style>
                    <div class="app-icon-wrap" style="background: {{ $app->color ?? '#3b82f6' }}20;">
                        @if($app->icon)
                            <span
                                style="color: {{ $app->color ?? '#3b82f6' }};">@include('admin.partials._app_icon', ['icon' => $app->icon, 'class' => 'w-6 h-6'])</span>
                        @else
                            <svg width="24" height="24" fill="none" stroke="{{ $app->color ?? '#3b82f6' }}" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                        @endif
                    </div>
                    <h3>{{ $app->getTranslation('name') }}</h3>
                    <p>{{ $app->getTranslation('description') }}</p>
                    <div class="app-badge"
                        style="background: {{ $app->color ?? '#3b82f6' }}15; color: {{ $app->color ?? '#3b82f6' }};">
                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        {{ 'Active' }}
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="empty-state">
            <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                style="margin: 0 auto 1rem; display: block;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
            {{ 'No applications available yet.' }}
        </div>
    @endif

    {{-- CTA --}}
    <div
        style="text-align: center; margin-top: 3rem; padding: 2rem; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.06); border-radius: 16px;">
        <p style="color: var(--gray-400); margin-bottom: 1rem;">
            {{ 'Want to use these applications?' }}
        </p>
        <a href="{{ route('register.create') }}" class="btn-primary">
            {{ 'Register My School' }}
        </a>
    </div>
@endsection