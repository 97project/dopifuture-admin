@extends('portal.app')
@section('title', ($isTr ?? false) ? 'Görev Detay' : 'Mission Detail')
@section('page-title', 'Mission Detail')
@section('content')

    {{-- ═══ HERO BANNER — Figma F-62: dark bg image with mission title ═══ --}}
    <div style="position:relative;border-radius:16px;overflow:hidden;margin-bottom:24px;min-height:300px;background:url('{{ $mission->image ?? 'https://images.unsplash.com/photo-1573648952826-b4f5e09c7370?w=1200&h=400&fit=crop' }}') center/cover no-repeat;">
        <div style="position:absolute;inset:0;background:linear-gradient(180deg,rgba(0,0,0,0.3) 0%,rgba(0,0,0,0.6) 100%);"></div>
        <div style="position:relative;z-index:1;padding:24px 28px;">
            {{-- Mission Title --}}
            <h2 style="color:#fff;font-size:22px;font-weight:700;margin:0 0 20px 0;font-family:'Nunito',sans-serif;">{{ $mission->title ?? 'After the Earthquake' }}</h2>

            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;">
                {{-- Students Section --}}
                <div style="background:rgba(255,255,255,0.9);border-radius:12px;padding:16px;">
                    <div style="font-size:14px;font-weight:700;margin-bottom:12px;color:#111;">Students</div>
                    @foreach($students as $s)
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--primary-deep));color:#fff;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:600;flex-shrink:0;">{{ strtoupper(substr($s->name ?? '', 0, 1) . substr($s->surname ?? '', 0, 1)) }}</div>
                            <span style="font-size:13px;font-weight:500;color:#111;">{{ $s->name }} {{ $s->surname }}</span>
                        </div>
                        <span style="font-size:12px;font-weight:600;color:#6366F1;">{{ $s->role ?? 'Diplomat' }}</span>
                    </div>
                    @endforeach
                </div>

                {{-- Result Section --}}
                <div style="background:rgba(255,255,255,0.9);border-radius:12px;padding:16px;">
                    <div style="font-size:14px;font-weight:700;margin-bottom:12px;color:#111;">Result</div>
                    <p style="font-size:13px;line-height:1.7;color:#374151;margin:0;">{{ $mission->result ?? 'The people willingly carry stones and repair walls with the belief that "salvation is near." However, the difference between the reinforcement time (35 min) and the door endurance time determines the lifespan of the lie. If reinforcement does not arrive on time, the people will open the doors.' }}</p>
                </div>

                {{-- Overall Score Section --}}
                <div style="background:rgba(230,235,255,0.95);border-radius:12px;padding:16px;">
                    <div style="font-size:14px;font-weight:700;margin-bottom:12px;color:#111;">Overall Score</div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                        <div style="background:rgba(255,255,255,0.8);border-radius:8px;padding:10px;text-align:center;">
                            <div style="font-size:10px;font-weight:600;color:#EF4444;text-transform:uppercase;margin-bottom:4px;">❤️ HEALTH POINT:</div>
                            <div style="font-size:24px;font-weight:800;color:#111;">{{ $mission->health ?? 75 }}</div>
                        </div>
                        <div style="background:rgba(255,255,255,0.8);border-radius:8px;padding:10px;text-align:center;">
                            <div style="font-size:10px;font-weight:600;color:#3B82F6;text-transform:uppercase;margin-bottom:4px;">📦 RESOURCE POINT:</div>
                            <div style="font-size:24px;font-weight:800;color:#111;">{{ $mission->resource ?? 40 }} <span style="font-size:16px;">👎</span></div>
                        </div>
                        <div style="background:rgba(255,255,255,0.8);border-radius:8px;padding:10px;text-align:center;">
                            <div style="font-size:10px;font-weight:600;color:#22C55E;text-transform:uppercase;margin-bottom:4px;">⚖️ ETHICS POINT:</div>
                            <div style="font-size:24px;font-weight:800;color:#111;">{{ $mission->ethics ?? 85 }} <span style="font-size:16px;">👍</span></div>
                        </div>
                        <div style="background:rgba(255,255,255,0.8);border-radius:8px;padding:10px;text-align:center;">
                            <div style="font-size:10px;font-weight:600;color:#8B5CF6;text-transform:uppercase;margin-bottom:4px;">✅ ADAPTATION POINT:</div>
                            <div style="font-size:24px;font-weight:800;color:#111;">{{ $mission->adaptation ?? 100 }} <span style="font-size:16px;">👍</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══ GROUP FLOW — Figma F-62: question cards with dashed arrow connectors ═══ --}}
    <h3 style="font-size:18px;font-weight:700;margin-bottom:16px;font-family:'Nunito',sans-serif;">Group Flow</h3>

    <div style="display:flex;gap:0;align-items:flex-start;overflow-x:auto;padding-bottom:16px;">
        @foreach($questions as $qi => $q)
        <div style="flex:0 0 380px;position:relative;">
            {{-- Question Card --}}
            <div style="background:#fff;border:1px solid #E5E7EB;border-radius:12px;padding:16px;margin:0 8px;">
                {{-- Question Badge + Unanimity Rate --}}
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
                    <span style="background:{{ $qi === 0 ? '#22C55E' : ($qi === 1 ? '#3B82F6' : '#EF4444') }};color:#fff;padding:6px 16px;border-radius:20px;font-size:12px;font-weight:600;">
                        Question {{ $qi + 1 }}
                    </span>
                    <span style="font-size:12px;color:#6B7280;">Unanimity Rate: <strong style="color:#111;">{{ $q->unanimity ?? '75' }}/100</strong></span>
                </div>

                {{-- Question Text --}}
                <div style="font-size:13px;font-weight:600;color:#111;margin-bottom:12px;">{{ $q->question }}</div>

                {{-- Answer Options --}}
                @foreach($q->options as $oi => $option)
                <div style="display:flex;align-items:center;gap:10px;padding:8px 10px;border-radius:8px;margin-bottom:4px;font-size:12px;
                    {{ $option->selected ? 'background:#DBEAFE;border:1.5px solid #3B82F6;' : '' }}">
                    <span style="width:22px;height:22px;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;color:#fff;flex-shrink:0;
                        background:{{ $oi === 0 ? '#F59E0B' : ($oi === 1 ? '#22C55E' : '#EF4444') }};">
                        {{ chr(65 + $oi) }}
                    </span>
                    <span style="{{ $option->selected ? 'font-weight:600;color:#1D4ED8;' : 'color:#374151;' }}">{{ $option->text }}</span>
                </div>
                @endforeach

                {{-- 4 Point Cards --}}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;margin-top:12px;">
                    <div style="background:linear-gradient(135deg,#FCA5A5,#EF4444);border-radius:8px;padding:8px 10px;display:flex;align-items:center;gap:6px;">
                        <span style="font-size:14px;">❤️</span>
                        <span style="color:#fff;font-size:9px;font-weight:600;text-transform:uppercase;">Health<br>Point:</span>
                        <span style="color:#fff;font-size:18px;font-weight:700;margin-left:auto;">{{ $q->health ?? 45 }}</span>
                        <span style="font-size:12px;">👎</span>
                    </div>
                    <div style="background:linear-gradient(135deg,#93C5FD,#3B82F6);border-radius:8px;padding:8px 10px;display:flex;align-items:center;gap:6px;">
                        <span style="font-size:14px;">📦</span>
                        <span style="color:#fff;font-size:9px;font-weight:600;text-transform:uppercase;">Resource<br>Point:</span>
                        <span style="color:#fff;font-size:18px;font-weight:700;margin-left:auto;">{{ $q->resource ?? 70 }}</span>
                    </div>
                    <div style="background:linear-gradient(135deg,#A7F3D0,#22C55E);border-radius:8px;padding:8px 10px;display:flex;align-items:center;gap:6px;">
                        <span style="font-size:14px;">⚖️</span>
                        <span style="color:#fff;font-size:9px;font-weight:600;text-transform:uppercase;">Ethics<br>Point:</span>
                        <span style="color:#fff;font-size:18px;font-weight:700;margin-left:auto;">{{ $q->ethics ?? 65 }}</span>
                    </div>
                    <div style="background:linear-gradient(135deg,#C4B5FD,#8B5CF6);border-radius:8px;padding:8px 10px;display:flex;align-items:center;gap:6px;">
                        <span style="font-size:14px;">✅</span>
                        <span style="color:#fff;font-size:9px;font-weight:600;text-transform:uppercase;">Adaptation<br>Point:</span>
                        <span style="color:#fff;font-size:18px;font-weight:700;margin-left:auto;">{{ $q->adaptation ?? 100 }}</span>
                        <span style="font-size:12px;">👍</span>
                    </div>
                </div>
            </div>

            {{-- Dashed Arrow Connector (except last) --}}
            @if(!$loop->last)
            <div style="position:absolute;top:50%;right:-16px;z-index:2;">
                <svg width="32" height="32" viewBox="0 0 32 32" fill="none">
                    <path d="M4 16 L24 16" stroke="#3B82F6" stroke-width="2" stroke-dasharray="4 3"/>
                    <path d="M20 10 L28 16 L20 22" stroke="#3B82F6" stroke-width="2" fill="none" stroke-dasharray="4 3"/>
                </svg>
            </div>
            @endif
        </div>
        @endforeach
    </div>

@endsection
