@extends('portal.app')
@section('title', __('admin.reports'))
@section('content')
    <div class="page-header">
        <h1>{{ __('admin.reports') }}</h1>
        <p>{{ __('portal.reports_subtitle') }}
        </p>
    </div>

    {{-- App User Distribution --}}
    @if(isset($appStats) && $appStats->count())
        <div class="data-table-wrap">
            <div class="data-table-header">
                <h3>
                    <svg width="18" height="18" fill="none" stroke="var(--brand-400)" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Application User Distribution
                </h3>
            </div>
            <div style="padding: 1.5rem;">
                @php $maxUsers = $appStats->max('users_count') ?: 1; @endphp
                @foreach($appStats as $app)
                    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                        <span
                            style="width: 160px; font-size: 0.9rem; color: white; font-weight: 500;">{{ $app->getTranslation('name') }}</span>
                        <div class="progress-bar" style="flex: 1; height: 10px;">
                            <div class="fill"
                                style="width: {{ ($app->users_count / $maxUsers) * 100 }}%; background: linear-gradient(90deg, var(--brand-600), var(--brand-400));">
                            </div>
                        </div>
                        <span
                            style="font-size: 0.85rem; color: var(--gray-300); min-width: 50px; text-align: right; font-weight: 600;">{{ $app->users_count }}
                            users</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Per-Application User Details --}}
    @if(isset($appDetails) && $appDetails->count())
        @foreach($appDetails as $appDetail)
            @if($appDetail->users->count())
                <div class="data-table-wrap">
                    <div class="data-table-header">
                        <h3>
                            <svg width="18" height="18" fill="none" stroke="#a78bfa" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                            {{ $appDetail->getTranslation('name') }}
                            <span style="font-weight: 400; font-size: 0.8rem; color: var(--gray-400); margin-left: 0.5rem;">
                                ({{ $appDetail->users->count() }} users)
                            </span>
                        </h3>
                    </div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>{{ __('admin.name') }}</th>
                                <th>{{ __('admin.email') }}</th>
                                <th>{{ __('admin.status') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($appDetail->users as $appUser)
                                <tr>
                                    <td style="color: white; font-weight: 500;">{{ $appUser->name }} {{ $appUser->surname }}</td>
                                    <td>{{ $appUser->email }}</td>
                                    <td>
                                        <span class="badge {{ $appUser->status === 'active' ? 'badge-success' : 'badge-danger' }}">
                                            {{ $appUser->status === 'active' ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('portal.users.show', $appUser) }}"
                                            class="btn btn-ghost btn-sm">{{ __('portal.detail') }}</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        @endforeach
    @endif

    {{-- License Utilization --}}
    @if(isset($licenseStats) && $licenseStats->count())
        <div class="data-table-wrap">
            <div class="data-table-header">
                <h3>
                    <svg width="18" height="18" fill="none" stroke="#fbbf24" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    License Utilization Rates
                </h3>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ __('admin.school_name') }}</th>
                        <th>{{ __('portal.capacity') }}</th>
                        <th>{{ __('portal.used_seats') }}</th>
                        <th>{{ __('portal.remaining_seats') }}</th>
                        <th>{{ __('portal.rate') }}</th>
                        <th>{{ __('portal.expiry_date') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($licenseStats as $lic)
                        @php $total = $lic->totalSeats();
                        $pct = $total > 0 ? round(($lic->used_seats / $total) * 100) : 0; @endphp
                        <tr>
                            <td style="font-weight: 500; color: white;">{{ $lic->school?->name ?? '—' }}</td>
                            <td>{{ $lic->totalSeats() }}</td>
                            <td>{{ $lic->used_seats }}</td>
                            <td style="color: {{ $lic->availableSeats() > 0 ? '#4ade80' : '#f87171' }}; font-weight: 600;">
                                {{ $lic->availableSeats() }}
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <div class="progress-bar" style="flex: 1; max-width: 80px;">
                                        <div class="fill"
                                            style="width: {{ $pct }}%; background: {{ $pct >= 90 ? '#f87171' : ($pct >= 70 ? '#fbbf24' : '#4ade80') }};">
                                        </div>
                                    </div>
                                    <span
                                        style="font-size: 0.75rem; font-weight: 600; color: {{ $pct >= 90 ? '#f87171' : ($pct >= 70 ? '#fbbf24' : '#4ade80') }};">%{{ $pct }}</span>
                                </div>
                            </td>
                            <td style="font-size: 0.8rem; color: var(--gray-500);">{{ $lic->expires_at?->format('d.m.Y') ?? '—' }}
                            </td>
                            <td>
                                <a href="{{ route('portal.licenses.show', $lic) }}"
                                    class="btn btn-ghost btn-sm">{{ __('portal.detail') }}</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- School Student/Teacher Counts --}}
    @if(isset($schoolStats) && $schoolStats->count())
        <div class="data-table-wrap">
            <div class="data-table-header">
                <h3>
                    <svg width="18" height="18" fill="none" stroke="#4ade80" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    Per-School Distribution
                </h3>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ __('admin.school_name') }}</th>
                        <th>{{ __('portal.nav_students') }}</th>
                        <th>{{ __('portal.nav_teachers') }}</th>
                        <th>{{ __('admin.classes') }}</th>
                        <th>{{ __('portal.total_users') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($schoolStats as $school)
                        <tr>
                            <td style="font-weight: 500; color: white;">{{ $school->name }}</td>
                            <td>{{ $school->students_count }}</td>
                            <td>{{ $school->teachers_count }}</td>
                            <td>{{ $school->classes_count }}</td>
                            <td>{{ $school->users_count }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection