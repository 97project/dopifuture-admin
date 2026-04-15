@extends('portal.app')
@section('title', __('admin.reports'))
@section('page-title', __('admin.reports'))

@section('content')
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
    <div>
        <div style="font-size:22px;font-weight:700;color:var(--text-main);">📊 Command Center</div>
        <p style="font-size:13px;color:var(--text-muted);margin-top:2px;">Advanced cross-platform intelligence and gamified reporting</p>
    </div>
</div>

@if(session('success'))
    <div class="dp-toast">✅ {{ session('success') }}</div>
@endif

{{-- Student View (Keeping it minimal as requested, unchanged) --}}
@if(isset($studentReport))
    <div style="font-size:16px;font-weight:600;margin-bottom:12px;">My Progress</div>
    @foreach($studentReport as $slug => $appData)
    <div class="dp-card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
            <div class="dp-card-title" style="margin-bottom:0;">{{ $appData['app']->name }}</div>
            <span class="dp-badge {{ $appData['stats']['completion_rate'] >= 80 ? 'dp-badge-active' : ($appData['stats']['completion_rate'] >= 40 ? 'dp-badge-pending' : 'dp-badge-error') }}">{{ $appData['stats']['completion_rate'] }}%</span>
        </div>
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;text-align:center;">
            <div>
                <div style="font-size:20px;font-weight:700;">{{ $appData['stats']['total_modules'] }}</div>
                <div style="font-size:11px;color:var(--text-muted);">Modules</div>
            </div>
            <div>
                <div style="font-size:20px;font-weight:700;color:var(--active-green);">{{ $appData['stats']['completed'] }}</div>
                <div style="font-size:11px;color:var(--text-muted);">{{ __('portal.completed') }}</div>
            </div>
            <div>
                <div style="font-size:20px;font-weight:700;color:var(--primary);">{{ $appData['stats']['avg_score'] ? number_format($appData['stats']['avg_score'], 1) : '-' }}</div>
                <div style="font-size:11px;color:var(--text-muted);">{{ __('portal.avg_score') }}</div>
            </div>
            <div>
                <div style="font-size:20px;font-weight:700;color:#fbbf24;">{{ $appData['stats']['total_sessions'] }}</div>
                <div style="font-size:11px;color:var(--text-muted);">{{ __('portal.sessions') }}</div>
            </div>
        </div>
        <div class="dp-progress" style="margin-top:16px;">
            <div class="dp-progress-fill" style="width:{{ $appData['stats']['completion_rate'] }}%;"></div>
        </div>
    </div>
    @endforeach
@endif


{{-- Admin & Teacher Advanced Dashboard --}}
@if(isset($radarMetrics))

    {{-- TOP ROW: Global Metrics & Radar --}}
    <div style="display:{{ isset($myClasses) ? 'block' : 'grid' }}; {{ isset($myClasses) ? '' : 'grid-template-columns:1fr 340px;gap:24px;' }} margin-bottom:24px;">
        
        {{-- App Performance Summary Cards --}}
        <div style="margin-bottom:24px;">
            @if(isset($overview))
            <div class="dp-card" style="margin-bottom:24px;">
                <div class="dp-card-title" style="margin-bottom:16px;">Platform At A Glance</div>
                @php
                    $appStats = collect($overview['app_stats']);
                    $sumCompleted = $appStats->sum('completed');
                    $sumInProgress = $appStats->sum('in_progress');
                    $sumProgress = $appStats->sum('total_progress');
                    $avgScore = $appStats->whereNotNull('avg_score')->where('avg_score', '>', 0)->avg('avg_score');
                    $globalRate = $sumProgress > 0 ? round(($sumCompleted / $sumProgress) * 100, 1) : 0;
                    $topScore = $leaderboards['top_score'][0]['score'] ?? 0;
                @endphp
                <div style="display:grid;grid-template-columns:repeat(7, 1fr);gap:16px;">
                    {{-- 1. Active Students --}}
                    <div style="background:linear-gradient(135deg,rgba(67,100,247,0.05),transparent);padding:16px;border-radius:12px;border:1px solid rgba(67,100,247,0.1);text-align:center;">
                        <div style="color:var(--primary);margin-bottom:8px;"><svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin:0 auto;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg></div>
                        <div style="font-size:24px;font-weight:800;color:var(--text-main);line-height:1;">{{ $overview['total_students'] }}</div>
                        <div style="font-size:11px;color:var(--text-muted);margin-top:4px;font-weight:600;text-transform:uppercase;">{{ __('portal.nav_students') }}</div>
                    </div>
                    
                    {{-- 2. Total Completions --}}
                    <div style="background:linear-gradient(135deg,rgba(16,185,129,0.05),transparent);padding:16px;border-radius:12px;border:1px solid rgba(16,185,129,0.1);text-align:center;">
                        <div style="color:#10b981;margin-bottom:8px;"><svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin:0 auto;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
                        <div style="font-size:24px;font-weight:800;color:var(--text-main);line-height:1;">{{ $sumCompleted }}</div>
                        <div style="font-size:11px;color:var(--text-muted);margin-top:4px;font-weight:600;text-transform:uppercase;">{{ __('portal.completed') }}</div>
                    </div>

                    {{-- 3. Active Sessions --}}
                    <div style="background:linear-gradient(135deg,rgba(245,158,11,0.05),transparent);padding:16px;border-radius:12px;border:1px solid rgba(245,158,11,0.1);text-align:center;">
                        <div style="color:#f59e0b;margin-bottom:8px;"><svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin:0 auto;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
                        <div style="font-size:24px;font-weight:800;color:var(--text-main);line-height:1;">{{ $sumInProgress }}</div>
                        <div style="font-size:11px;color:var(--text-muted);margin-top:4px;font-weight:600;text-transform:uppercase;">In Progress</div>
                    </div>

                    {{-- 4. Total Engagement --}}
                    <div style="background:linear-gradient(135deg,rgba(139,92,246,0.05),transparent);padding:16px;border-radius:12px;border:1px solid rgba(139,92,246,0.1);text-align:center;">
                        <div style="color:#8b5cf6;margin-bottom:8px;"><svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin:0 auto;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
                        <div style="font-size:24px;font-weight:800;color:var(--text-main);line-height:1;">{{ $sumProgress }}</div>
                        <div style="font-size:11px;color:var(--text-muted);margin-top:4px;font-weight:600;text-transform:uppercase;">Total Plays</div>
                    </div>

                    {{-- 5. Platform Avg Score --}}
                    <div style="background:linear-gradient(135deg,rgba(236,72,153,0.05),transparent);padding:16px;border-radius:12px;border:1px solid rgba(236,72,153,0.1);text-align:center;">
                        <div style="color:#ec4899;margin-bottom:8px;"><svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin:0 auto;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg></div>
                        <div style="font-size:24px;font-weight:800;color:var(--text-main);line-height:1;">{{ $avgScore ? number_format($avgScore, 1) : '-' }}</div>
                        <div style="font-size:11px;color:var(--text-muted);margin-top:4px;font-weight:600;text-transform:uppercase;">{{ __('portal.avg_score') }}</div>
                    </div>

                    {{-- 6. Completion Rate --}}
                    <div style="background:linear-gradient(135deg,rgba(14,165,233,0.05),transparent);padding:16px;border-radius:12px;border:1px solid rgba(14,165,233,0.1);text-align:center;">
                        <div style="color:#0ea5e9;margin-bottom:8px;"><svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin:0 auto;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg></div>
                        <div style="font-size:24px;font-weight:800;color:var(--text-main);line-height:1;">%{{ $globalRate }}</div>
                        <div style="font-size:11px;color:var(--text-muted);margin-top:4px;font-weight:600;text-transform:uppercase;">Win Rate</div>
                    </div>

                    {{-- 7. Top Score --}}
                    <div style="background:linear-gradient(135deg,rgba(244,63,94,0.05),transparent);padding:16px;border-radius:12px;border:1px solid rgba(244,63,94,0.1);text-align:center;">
                        <div style="color:#f43f5e;margin-bottom:8px;"><svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin:0 auto;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg></div>
                        <div style="font-size:24px;font-weight:800;color:var(--text-main);line-height:1;">{{ $topScore }}</div>
                        <div style="font-size:11px;color:var(--text-muted);margin-top:4px;font-weight:600;text-transform:uppercase;">Max Score</div>
                    </div>
                </div>
            </div>
            @endif

            @if(isset($myClasses))
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px;margin-bottom:20px;">
                @foreach($myClasses as $class)
                <a href="{{ route('portal.reports.class', $class) }}" style="text-decoration:none;">
                    <div class="dp-card" style="cursor:pointer;padding:16px;">
                        <div style="font-weight:600;font-size:15px;color:var(--text-main);">{{ $class->name }}</div>
                        <div style="margin-top:8px;font-size:24px;font-weight:800;color:var(--primary);">{{ $class->students_count }} <span style="font-size:12px;color:var(--text-muted);font-weight:500;">{{ __('portal.nav_students') }}</span></div>
                    </div>
                </a>
                @endforeach
            </div>
            @endif

            <div class="dp-card" style="padding:0;overflow:hidden;">
                <div class="dp-card-title" style="padding:20px 20px 0;">Platforms Overview</div>
                <div style="padding:20px;">
                    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:16px;">
                        @foreach($overview['app_stats'] ?? [] as $stat)
                        <a href="{{ route('portal.reports.app', $stat['app']->slug) }}" style="text-decoration:none;">
                            <div style="border:1px solid rgba(0,0,0,0.06);border-radius:12px;padding:16px;background:var(--color-card-bg);transition:box-shadow .2s;height:100%;">
                                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                                    <div style="font-weight:600;font-size:14px;color:var(--text-main);">{{ $stat['app']->name }}</div>
                                    <span style="font-size:11px;padding:4px 8px;background:rgba(67,100,247,0.1);color:var(--primary);border-radius:20px;font-weight:600;">{{ $stat['total_users'] }} Users</span>
                                </div>
                                <div style="display:flex;gap:12px;align-items:end;">
                                    <div style="font-size:24px;font-weight:800;color:var(--text-main);line-height:1;">{{ $stat['completed'] }}</div>
                                    <div style="font-size:12px;color:var(--text-muted);margin-bottom:2px;">{{ __('portal.completed') }}</div>
                                </div>
                                @if($stat['total_progress'] > 0)
                                <div class="dp-progress" style="margin-top:12px;height:6px;">
                                    <div class="dp-progress-fill" style="width:{{ round(($stat['completed'] / max($stat['total_progress'],1))*100) }}%;background:linear-gradient(90deg,var(--primary),#8b5cf6);"></div>
                                </div>
                                @endif
                            </div>
                        </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Radar Chart Widget (Moved beneath overview or beside apps depending on layout) --}}
        @if(!isset($myClasses))
        <div class="dp-card" style="display:flex;flex-direction:column;grid-column: 2; grid-row: 1 / span 2;">
            <div class="dp-card-title">{{ __('portal.global_skill_matrix') }}</div>
            <div style="flex:1;position:relative;display:flex;align-items:center;justify-content:center;min-height:260px;">
                <canvas id="globalRadarChart"></canvas>
            </div>
            <div style="margin-top:16px;font-size:12px;color:var(--text-muted);text-align:center;line-height:1.5;">
                Highlights the global average of Mission WAY attributes across the current scope.
            </div>
        </div>
        @endif
    </div>


    {{-- MIDDLE ROW: Leaderboards & Activity Feed --}}
    <div style="display:grid;grid-template-columns:2fr 1fr;gap:24px;margin-bottom:24px;">
        
        {{-- Gamified Leaderboards --}}
        <div class="dp-card">
            <div class="dp-card-title" style="display:flex;align-items:center;gap:8px;">
                🚀 Top Achievers
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:24px;">
                {{-- Global Score Top --}}
                <div>
                    <div style="font-size:13px;font-weight:700;color:var(--text-muted);margin-bottom:12px;text-transform:uppercase;letter-spacing:0.5px;">⭐ Highest Global Score</div>
                    @forelse($leaderboards['top_score'] ?? [] as $i => $row)
                        <div style="display:flex;align-items:center;gap:12px;padding:8px 0;{{ !$loop->last ? 'border-bottom:1px solid rgba(0,0,0,0.04);' : '' }}">
                            <div style="width:24px;height:24px;border-radius:50%;background:{{ $i==0 ? '#fbbf24' : ($i==1 ? '#9ca3af' : ($i==2 ? '#d97706' : 'rgba(0,0,0,0.05)')) }};color:{{ $i<3 ? '#fff' : 'var(--text-muted)' }};display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;">
                                {{ $i+1 }}
                            </div>
                            <div style="flex:1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                <div style="font-size:13px;font-weight:600;color:var(--text-main);">{{ $row['user']->name ?? 'Unknown' }} {{ $row['user']->surname ?? '' }}</div>
                            </div>
                            <div style="font-weight:700;font-size:14px;color:var(--text-main);">{{ $row['score'] }}</div>
                        </div>
                    @empty
                        <div style="font-size:13px;color:var(--text-muted);">No data yet.</div>
                    @endforelse
                </div>

                {{-- Ethics Top --}}
                <div>
                    <div style="font-size:13px;font-weight:700;color:var(--text-muted);margin-bottom:12px;text-transform:uppercase;letter-spacing:0.5px;">🧡 Most Ethical</div>
                    @forelse($leaderboards['top_ethics'] ?? [] as $i => $row)
                        <div style="display:flex;align-items:center;gap:12px;padding:8px 0;{{ !$loop->last ? 'border-bottom:1px solid rgba(0,0,0,0.04);' : '' }}">
                            <div style="width:24px;height:24px;border-radius:50%;background:{{ $i==0 ? '#f59e0b' : 'rgba(245,158,11,0.1)' }};color:{{ $i==0 ? '#fff' : '#f59e0b' }};display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;">
                                {{ $i+1 }}
                            </div>
                            <div style="flex:1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                <div style="font-size:13px;font-weight:600;color:var(--text-main);">{{ $row['user']->name ?? 'Unknown' }} {{ $row['user']->surname ?? '' }}</div>
                            </div>
                            <div style="font-weight:700;font-size:14px;color:var(--text-main);">{{ $row['ethics'] }}</div>
                        </div>
                    @empty
                        <div style="font-size:13px;color:var(--text-muted);">No data yet.</div>
                    @endforelse
                </div>

                {{-- Adaptation Top --}}
                <div>
                    <div style="font-size:13px;font-weight:700;color:var(--text-muted);margin-bottom:12px;text-transform:uppercase;letter-spacing:0.5px;">✅ Most Adaptable</div>
                    @forelse($leaderboards['top_adaptation'] ?? [] as $i => $row)
                        <div style="display:flex;align-items:center;gap:12px;padding:8px 0;{{ !$loop->last ? 'border-bottom:1px solid rgba(0,0,0,0.04);' : '' }}">
                            <div style="width:24px;height:24px;border-radius:50%;background:{{ $i==0 ? '#10b981' : 'rgba(16,185,129,0.1)' }};color:{{ $i==0 ? '#fff' : '#10b981' }};display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;">
                                {{ $i+1 }}
                            </div>
                            <div style="flex:1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                <div style="font-size:13px;font-weight:600;color:var(--text-main);">{{ $row['user']->name ?? 'Unknown' }} {{ $row['user']->surname ?? '' }}</div>
                            </div>
                            <div style="font-weight:700;font-size:14px;color:var(--text-main);">{{ $row['adaptation'] }}</div>
                        </div>
                    @empty
                        <div style="font-size:13px;color:var(--text-muted);">No data yet.</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Activity Feed --}}
        <div class="dp-card" style="display:flex;flex-direction:column;max-height:400px;">
            <div class="dp-card-title">{{ __('portal.live_activity_feed') }}</div>
            <div style="flex:1;overflow-y:auto;padding-right:8px;" class="custom-scrollbar">
                <style>
                    .feed-item { position: relative; padding-left: 24px; padding-bottom: 16px; }
                    .feed-item:not(:last-child)::before {
                        content: ''; position: absolute; left: 5px; top: 12px; bottom: 0; width: 2px;
                        background: rgba(0,0,0,0.05);
                    }
                    .feed-dot {
                        position: absolute; left: 0; top: 4px; width: 12px; height: 12px;
                        border-radius: 50%; border: 2px solid #fff; box-shadow: 0 0 0 1px rgba(0,0,0,0.1);
                    }
                    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
                    .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.1); border-radius: 4px; }
                </style>
                @forelse($recentActivities ?? [] as $act)
                    <div class="feed-item">
                        <div class="feed-dot" style="background: {{ $act['color'] }};"></div>
                        <div style="font-size:13px;line-height:1.4;">
                            <span style="font-weight:700;color:var(--text-main);">{{ $act['student']->name ?? 'Unknown' }} {{ $act['student']->surname ?? '' }}</span>
                            <span style="color:var(--text-muted);">{{ $act['action'] }}</span>
                            <span style="font-weight:600;color:{{ $act['color'] }};">{{ $act['app'] }}</span>
                        </div>
                        <div style="font-size:11px;color:var(--text-muted);margin-top:2px;display:flex;justify-content:space-between;">
                            <span>{{ $act['detail'] }}</span>
                            <span>{{ $act['date']->diffForHumans() }}</span>
                        </div>
                    </div>
                @empty
                    <div style="text-align:center;padding:32px 0;color:var(--text-muted);font-size:13px;">No recent activities found.</div>
                @endforelse
            </div>
        </div>

    </div>

    {{-- BOTTOM ROW: Deeper Analytics --}}
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:24px;margin-bottom:24px;">
        {{-- Activity Trend --}}
        <div class="dp-card" style="display:flex;flex-direction:column;">
            <div class="dp-card-title" style="margin-bottom:8px;">30-Day Activity Trend</div>
            <div style="flex:1;position:relative;min-height:220px;">
                <canvas id="usageTrendChart"></canvas>
            </div>
        </div>

        {{-- Score Distribution --}}
        <div class="dp-card" style="display:flex;flex-direction:column;">
            <div class="dp-card-title" style="margin-bottom:8px;">Score Distribution</div>
            <div style="flex:1;position:relative;min-height:220px;">
                <canvas id="scoreDistChart"></canvas>
            </div>
        </div>

        {{-- Module Popularity --}}
        <div class="dp-card" style="display:flex;flex-direction:column;">
            <div class="dp-card-title" style="margin-bottom:8px;">Most Popular Modules</div>
            <div style="flex:1;position:relative;min-height:220px;">
                <canvas id="popularityChart"></canvas>
            </div>
        </div>
    </div>

    {{-- PHASE 3: PER-APP DEEP DASHBOARDS --}}
    @if(isset($perAppDashboards))
        <div style="margin-top: 48px; border-top: 2px dashed rgba(0,0,0,0.05); padding-top: 32px;">
            <h2 style="font-size: 20px; font-weight: 800; color: var(--text-main); margin-bottom: 24px;">Deep Dive: Per-App Dashboards</h2>
            @foreach($perAppDashboards as $dashboard)
                @php $slug = $dashboard['app']->slug; @endphp
                <div class="dp-card" style="margin-bottom: 40px; border-top: 4px solid var(--primary);">
                    <div class="dp-card-title" style="font-size: 18px; margin-bottom: 16px;">{{ $dashboard['app']->name }}</div>
                    
                    {{-- 3 Charts Row --}}
                    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:24px;margin-bottom:24px;background:rgba(0,0,0,0.02);padding:16px;border-radius:12px;">
                        <div style="background:#fff;padding:12px;border-radius:8px;border:1px solid rgba(0,0,0,0.04);">
                            <div style="font-size:12px;font-weight:700;color:var(--text-muted);text-align:center;margin-bottom:8px;">Completion Status</div>
                            <div style="position:relative;height:180px;"><canvas id="chart_doughnut_{{ $slug }}"></canvas></div>
                        </div>
                        <div style="background:#fff;padding:12px;border-radius:8px;border:1px solid rgba(0,0,0,0.04);">
                            <div style="font-size:12px;font-weight:700;color:var(--text-muted);text-align:center;margin-bottom:8px;">Score Distribution</div>
                            <div style="position:relative;height:180px;"><canvas id="chart_bar_{{ $slug }}"></canvas></div>
                        </div>
                        <div style="background:#fff;padding:12px;border-radius:8px;border:1px solid rgba(0,0,0,0.04);">
                            <div style="font-size:12px;font-weight:700;color:var(--text-muted);text-align:center;margin-bottom:8px;">Daily Volatility</div>
                            <div style="position:relative;height:180px;"><canvas id="chart_line_{{ $slug }}"></canvas></div>
                        </div>
                    </div>

                    {{-- 3 Lists Row --}}
                    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:24px;">
                        
                        {{-- Top Performers --}}
                        <div style="border:1px solid rgba(0,0,0,0.05);border-radius:12px;padding:16px;">
                            <div style="font-size:13px;font-weight:800;color:var(--primary);margin-bottom:12px;">🏆 Top Performers</div>
                            @forelse($dashboard['lists']['top'] ?? [] as $tRow)
                                <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid rgba(0,0,0,0.03);font-size:12px;">
                                    <span style="font-weight:600;color:var(--text-main);">{{ $tRow['name'] }}</span>
                                    <span style="font-weight:600;color:var(--text-muted);">{{ $tRow['detail'] }}</span>
                                </div>
                            @empty
                                <div style="font-size:12px;color:var(--text-muted);padding:8px 0;text-align:center;">Insufficient Data</div>
                            @endforelse
                        </div>

                        {{-- Needs Attention --}}
                        <div style="border:1px solid rgba(0,0,0,0.05);border-radius:12px;padding:16px;">
                            <div style="font-size:13px;font-weight:800;color:#f43f5e;margin-bottom:12px;">⚠️ Needs Attention</div>
                            @forelse($dashboard['lists']['needs_attention'] ?? [] as $aRow)
                                <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid rgba(0,0,0,0.03);font-size:12px;">
                                    <span style="font-weight:600;color:var(--text-main);">{{ $aRow['name'] }}</span>
                                    <span style="color:#f43f5e;">{{ $aRow['detail'] }}</span>
                                </div>
                            @empty
                                <div style="font-size:12px;color:var(--text-muted);padding:8px 0;text-align:center;">All Good!</div>
                            @endforelse
                        </div>

                        {{-- Recent Activity --}}
                        <div style="border:1px solid rgba(0,0,0,0.05);border-radius:12px;padding:16px;">
                            <div style="font-size:13px;font-weight:800;color:#10b981;margin-bottom:12px;">⚡ Recent Highlights</div>
                            @forelse($dashboard['lists']['recent'] ?? [] as $rRow)
                                <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid rgba(0,0,0,0.03);font-size:12px;gap:8px;">
                                    <div style="font-weight:600;color:var(--text-main);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $rRow['user'] }} <span style="font-weight:400;color:var(--text-muted);">{{ $rRow['action'] }}</span></div>
                                    <span style="color:var(--text-muted);white-space:nowrap;">{{ $rRow['date'] }}</span>
                                </div>
                            @empty
                                <div style="font-size:12px;color:var(--text-muted);padding:8px 0;text-align:center;">No recent logs.</div>
                            @endforelse
                        </div>
                        
                    </div>
                </div>
            @endforeach
        </div>
    @endif

@endif

@endsection

@section('scripts')
@if(isset($overview))
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
Chart.defaults.color = '#8B8D97';
Chart.defaults.borderColor = 'rgba(0,0,0,0.06)';
Chart.defaults.font.family = "'Nunito', sans-serif";

@if(isset($radarMetrics))
new Chart(document.getElementById('globalRadarChart'), {
    type: 'radar',
    data: {
        labels: ['Health 🧬', 'Resource 🍃', 'Ethics ⚖️', 'Adaptation 🧩'],
        datasets: [{
            label: 'Global Class/School Averages',
            data: [
                {{ $radarMetrics['health'] ?? 0 }},
                {{ $radarMetrics['resource'] ?? 0 }},
                {{ $radarMetrics['ethics'] ?? 0 }},
                {{ $radarMetrics['adaptation'] ?? 0 }}
            ],
            backgroundColor: 'rgba(67, 100, 247, 0.2)',
            borderColor: '#4364F7',
            pointBackgroundColor: '#4364F7',
            pointBorderColor: '#fff',
            pointHoverBackgroundColor: '#fff',
            pointHoverBorderColor: '#4364F7',
            borderWidth: 2,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            r: {
                angleLines: { color: 'rgba(0,0,0,0.05)' },
                grid: { color: 'rgba(0,0,0,0.05)' },
                pointLabels: { font: { size: 12, weight: 'bold' } },
                ticks: { display: false, min: 0, max: 100 }
            }
        },
        plugins: { legend: { display: false } }
    }
});

// Usage Trend (Line)
@if(isset($usageTrend))
const utCtx = document.getElementById('usageTrendChart').getContext('2d');
const gradientBlue = utCtx.createLinearGradient(0, 0, 0, 300);
gradientBlue.addColorStop(0, 'rgba(67, 100, 247, 0.4)');
gradientBlue.addColorStop(1, 'rgba(67, 100, 247, 0)');

new Chart(utCtx, {
    type: 'line',
    data: {
        labels: {!! json_encode($usageTrend['labels']) !!},
        datasets: [{
            label: 'Completions',
            data: {!! json_encode($usageTrend['data']) !!},
            borderColor: '#4364F7',
            backgroundColor: gradientBlue,
            borderWidth: 3,
            fill: true,
            tension: 0.4,
            pointRadius: 0,
            pointHoverRadius: 6
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { display:false },
            y: { beginAtZero:true, display:false }
        }
    }
});
@endif

// Score Distribution (Histogram)
@if(isset($scoreDistribution))
new Chart(document.getElementById('scoreDistChart'), {
    type: 'bar',
    data: {
        labels: {!! json_encode($scoreDistribution['labels']) !!},
        datasets: [{
            label: 'Students',
            data: {!! json_encode($scoreDistribution['data']) !!},
            backgroundColor: '#10b981',
            borderRadius: 6,
            barPercentage: 0.7
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { display: false } },
            y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)', borderDash: [5,5] } }
        }
    }
});
@endif

// Module Popularity (Horizontal Bar)
@if(isset($modulePopularity))
new Chart(document.getElementById('popularityChart'), {
    type: 'bar',
    data: {
        labels: {!! json_encode($modulePopularity['labels']) !!},
        datasets: [{
            label: 'Plays',
            data: {!! json_encode($modulePopularity['data']) !!},
            backgroundColor: 'rgba(139, 92, 246, 0.8)',
            borderRadius: 4
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { beginAtZero: true, display: false },
            y: { grid: { display: false } }
        }
    }
});
@endif

@if(isset($perAppDashboards))
const perAppArray = {!! json_encode($perAppDashboards) !!};
perAppArray.forEach(function(dash) {
    const slug = dash.app.slug;
    
    // 1. Doughnut
    const dCanvas = document.getElementById('chart_doughnut_' + slug);
    if(dCanvas) {
        new Chart(dCanvas, {
            type: 'doughnut',
            data: {
                labels: dash.charts.completion.labels,
                datasets: [{
                    data: dash.charts.completion.data,
                    backgroundColor: ['#10b981', 'rgba(0,0,0,0.05)'],
                    borderWidth: 0,
                    cutout: '70%'
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10 } } } }
            }
        });
    }

    // 2. Bar
    const bCanvas = document.getElementById('chart_bar_' + slug);
    if(bCanvas) {
        new Chart(bCanvas, {
            type: 'bar',
            data: {
                labels: dash.charts.scores.labels,
                datasets: [{
                    label: 'Students',
                    data: dash.charts.scores.data,
                    backgroundColor: ['#10b981', '#f59e0b', '#f43f5e'],
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { x: { grid: { display: false }, ticks: { font: { size: 10 } } }, y: { display: false } }
            }
        });
    }

    // 3. Line
    const lCanvas = document.getElementById('chart_line_' + slug);
    if(lCanvas) {
        new Chart(lCanvas, {
            type: 'line',
            data: {
                labels: dash.charts.activity.labels,
                datasets: [{
                    label: 'Activity',
                    data: dash.charts.activity.data,
                    borderColor: '#8b5cf6',
                    borderWidth: 2,
                    tension: 0.3,
                    pointRadius: 2
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { x: { display: false }, y: { display: false } }
            }
        });
    }
});
@endif

@endif
</script>
@endif
@endsection
