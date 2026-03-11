@extends('admin.layouts.app')

@section('title', $user->full_name . ' — ' . $application->name . ' Raporu')

@section('breadcrumb')
    <div class="flex items-center gap-2 text-sm">
        <a href="{{ route('admin.dashboard') }}"
            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition">{{ __('admin.dashboard') }}</a>
        <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <a href="{{ route('admin.applications.index') }}"
            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition">{{ __('admin.applications') }}</a>
        <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <a href="{{ route('admin.applications.show', [$application, 'tab' => 'report']) }}"
            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition">{{ $application->name }}</a>
        <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-gray-900 dark:text-gray-100 font-semibold">{{ $user->full_name }}</span>
    </div>
@endsection

@section('content')
    <div class="space-y-6">

        {{-- ═══ HEADER ═══ --}}
        <div class="flex items-start justify-between">
            <div class="flex items-center gap-4">
                <div
                    class="w-14 h-14 rounded-xl bg-gradient-to-br from-[#0B6AB2] to-[#13398E] flex items-center justify-center text-white text-xl font-bold shadow-lg">
                    {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}{{ mb_strtoupper(mb_substr($user->surname ?? '', 0, 1)) }}
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ $user->full_name }}</h1>
                    <div class="flex items-center gap-3 mt-1">
                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $user->email }}</span>
                        <span class="text-xs px-2 py-0.5 rounded font-medium"
                            style="background: {{ $application->color ?? '#0B6AB2' }}15; color: {{ $application->color ?? '#0B6AB2' }}">
                            {{ $application->name }}
                        </span>
                    </div>
                </div>
            </div>
            <a href="{{ route('admin.applications.show', [$application, 'tab' => 'report']) }}"
                class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Geri Dön
            </a>
        </div>

        {{-- ═══ RAPOR İÇERİĞİ ═══ --}}
        @if(!$report)
            {{-- Rapor çekilemedi --}}
            <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl border border-gray-200 dark:border-gray-700 p-8 text-center">
                <svg class="w-10 h-10 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="text-sm font-bold text-gray-700 dark:text-gray-300">Rapor Verisi Yok</h3>
                <p class="text-xs text-gray-500 mt-1">Bu uygulama için rapor endpoint'i henüz aktif değil veya kullanıcı verisi
                    bulunamadı.</p>
            </div>

        @elseif(!($report['success'] ?? false))
            {{-- Hata --}}
            <div class="bg-red-50 dark:bg-red-900/10 rounded-xl border border-red-200 dark:border-red-800 p-6">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-red-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                    <div>
                        <h3 class="text-sm font-bold text-red-800 dark:text-red-300">Rapor Çekilemedi</h3>
                        <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $report['error'] ?? 'Bilinmeyen hata' }}</p>
                    </div>
                </div>
            </div>

        @else
            {{-- Başarılı rapor --}}
            @php $data = $report['data'] ?? []; @endphp

            {{-- ── VEGA RAPORU ── --}}
            @if($connectorType === 'vega')

                @php
                    // getUserReport() produces a flat sessions[] with 'module' field.
                    // Derive per-slug arrays for the slug-specific sections below.
                    $allSessions = collect($data['sessions'] ?? []);
                    $data['simulator_sessions'] = $allSessions->where('module', 'simulator')->values()->toArray();
                    $data['lecturer_sessions']  = $allSessions->where('module', 'lecturer')->values()->toArray();
                    $data['chatbot_sessions']   = $allSessions->filter(fn($s) => !in_array($s['module'] ?? '', ['simulator', 'lecturer']))->values()->toArray();
                @endphp
                {{-- Vega Profil Kartı --}}
                @if(!empty($data['profile']))
                    <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                            <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Vega Profili
                            <span class="text-[10px] font-mono text-gray-400">(ID: {{ $data['vega_id'] ?? '?' }})</span>
                        </h3>

                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            @foreach(collect($data['profile'])->take(12) as $key => $value)
                                @if(!is_array($value) && !is_object($value))
                                    <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-3">
                                        <p class="text-[10px] text-gray-500 mb-0.5">{{ ucfirst(str_replace('_', ' ', $key)) }}</p>
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">
                                            @if(is_bool($value))
                                                {{ $value ? 'Evet' : 'Hayır' }}
                                            @elseif(is_null($value))
                                                <span class="text-gray-300">-</span>
                                            @else
                                                {{ $value }}
                                            @endif
                                        </p>
                                    </div>
                                @endif
                            @endforeach
                        </div>

                        {{-- İç içe veri (roller, ayarlar vs.) --}}
                        @foreach(collect($data['profile'])->filter(fn($v) => is_array($v)) as $key => $arr)
                            <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                                <p class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                    {{ ucfirst(str_replace('_', ' ', $key)) }}
                                </p>
                                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-3">
                                    <pre
                                        class="text-xs text-gray-600 dark:text-gray-400 whitespace-pre-wrap overflow-auto max-h-40">{{ json_encode($arr, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Oturum İstatistikleri --}}
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5 text-center">
                        <p class="text-2xl font-extrabold text-gray-900 dark:text-white">{{ $data['session_count'] ?? 0 }}</p>
                        <p class="text-xs text-gray-500 mt-1">Toplam Oturum</p>
                    </div>
                    <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5 text-center">
                        <p class="text-2xl font-extrabold text-purple-600">{{ $data['modules']['lecturer'] ?? 0 }}</p>
                        <p class="text-xs text-gray-500 mt-1">AI Coach</p>
                    </div>
                    <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5 text-center">
                        <p class="text-2xl font-extrabold text-indigo-600">{{ $data['modules']['simulator'] ?? 0 }}</p>
                        <p class="text-xs text-gray-500 mt-1">Role Galaxy</p>
                    </div>
                    <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5 text-center">
                        <p class="text-2xl font-extrabold text-amber-600">{{ $data['modules']['chatbot'] ?? $data['modules']['all'] ?? 0 }}</p>
                        <p class="text-xs text-gray-500 mt-1">Study Space</p>
                    </div>
                </div>

                {{-- Slug Bazlı Detay — Role Galaxy Simülasyonları --}}
                @if(!empty($data['simulator_sessions']))
                    <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                            <span class="w-6 h-6 rounded-lg bg-indigo-500 flex items-center justify-center text-white text-[10px]">🌌</span>
                            Role Galaxy — Simülasyon Geçmişi
                        </h3>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="text-left border-b border-gray-100 dark:border-gray-700">
                                        <th class="pb-2 font-semibold text-gray-500 text-xs">Senaryo</th>
                                        <th class="pb-2 font-semibold text-gray-500 text-xs text-center">Skor</th>
                                        <th class="pb-2 font-semibold text-gray-500 text-xs text-center">Seviye</th>
                                        <th class="pb-2 font-semibold text-gray-500 text-xs text-right">Tarih</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                                    @foreach(collect($data['simulator_sessions'])->take(15) as $simSes)
                                        @php
                                            $simScore = $simSes['score'] ?? $simSes['currentScore'] ?? 0;
                                            if ($simScore >= 80) { $thBg = 'bg-emerald-100 text-emerald-700'; $thLabel = 'Refah'; }
                                            elseif ($simScore >= 60) { $thBg = 'bg-blue-100 text-blue-700'; $thLabel = 'Denge'; }
                                            elseif ($simScore >= 40) { $thBg = 'bg-amber-100 text-amber-700'; $thLabel = 'Kriz'; }
                                            else { $thBg = 'bg-red-100 text-red-700'; $thLabel = 'Felaket'; }
                                        @endphp
                                        <tr>
                                            <td class="py-2 text-xs text-gray-900 dark:text-white">{{ $simSes['scenario_name'] ?? $simSes['app_name'] ?? 'Simülasyon' }}</td>
                                            <td class="py-2 text-center text-xs font-bold">{{ $simScore }}</td>
                                            <td class="py-2 text-center">
                                                <span class="text-[10px] px-2 py-0.5 rounded-full font-medium {{ $thBg }}">{{ $thLabel }}</span>
                                            </td>
                                            <td class="py-2 text-right text-[10px] text-gray-400">
                                                {{ isset($simSes['created_at']) ? \Carbon\Carbon::parse($simSes['created_at'])->format('d.m.Y H:i') : '-' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                {{-- Slug Bazlı Detay — WAY AI Coach Mesajları --}}
                @if(!empty($data['lecturer_sessions']))
                    <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                            <span class="w-6 h-6 rounded-lg bg-purple-500 flex items-center justify-center text-white text-[10px]">🎓</span>
                            WAY AI Coach — Oturum Detayı
                        </h3>
                        <div class="space-y-2">
                            @foreach(collect($data['lecturer_sessions'])->take(10) as $lecSes)
                                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-3 flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
                                            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                                        </div>
                                        <div>
                                            <p class="text-xs font-medium text-gray-900 dark:text-white">{{ $lecSes['app_name'] ?? 'AI Coach Oturumu' }}</p>
                                            <p class="text-[10px] text-gray-400">{{ $lecSes['message_count'] ?? 0 }} mesaj · {{ $lecSes['duration'] ?? '-' }} dk</p>
                                        </div>
                                    </div>
                                    <span class="text-[10px] text-gray-400">
                                        {{ isset($lecSes['created_at']) ? \Carbon\Carbon::parse($lecSes['created_at'])->format('d.m.Y') : '-' }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Slug Bazlı Detay — Study Space Chatbot --}}
                @if(!empty($data['chatbot_sessions']))
                    <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                            <span class="w-6 h-6 rounded-lg bg-amber-500 flex items-center justify-center text-white text-[10px]">📖</span>
                            Study Space — Chatbot Oturumları
                        </h3>
                        <div class="space-y-2">
                            @foreach(collect($data['chatbot_sessions'])->take(10) as $cbSes)
                                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-3 flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                                            <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                                        </div>
                                        <div>
                                            <p class="text-xs font-medium text-gray-900 dark:text-white">{{ $cbSes['thread_name'] ?? 'Chatbot' }}</p>
                                            <p class="text-[10px] text-gray-400">{{ $cbSes['message_count'] ?? 0 }} mesaj</p>
                                        </div>
                                    </div>
                                    <span class="text-[10px] text-gray-400">
                                        {{ isset($cbSes['created_at']) ? \Carbon\Carbon::parse($cbSes['created_at'])->format('d.m.Y') : '-' }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Oturum Listesi --}}
                @if(!empty($data['sessions']))
                    <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4">Oturum Geçmişi</h3>

                        <div class="space-y-3">
                            @foreach(collect($data['sessions'])->take(20) as $session)
                                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4 flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-10 h-10 rounded-xl flex items-center justify-center
                                                                                    {{ ($session['module'] ?? '') === 'lecturer' ? 'bg-purple-100 dark:bg-purple-900/30' : 'bg-indigo-100 dark:bg-indigo-900/30' }}">
                                            @if(($session['module'] ?? '') === 'lecturer')
                                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                                                </svg>
                                            @else
                                                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            @endif
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ $session['app_name'] ?? ucfirst($session['module'] ?? 'Bilinmiyor') }}
                                            </p>
                                            <p class="text-[10px] text-gray-400">
                                                {{ $session['id'] ?? $session['session_id'] ?? '-' }}
                                                @if(isset($session['created_at']))
                                                    — {{ \Carbon\Carbon::parse($session['created_at'])->format('d.m.Y H:i') }}
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        @if(isset($session['status']))
                                            <span
                                                class="text-xs px-2 py-0.5 rounded-full font-medium
                                                                                                {{ $session['status'] === 'completed' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">
                                                {{ ucfirst($session['status']) }}
                                            </span>
                                        @endif
                                        @if(isset($session['duration']))
                                            <p class="text-[10px] text-gray-400 mt-0.5">{{ $session['duration'] }} dk</p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-6 text-center">
                        <p class="text-sm text-gray-500">Henüz oturum kaydı bulunmuyor</p>
                    </div>
                @endif

                {{-- ── MISSIONWAY RAPORU ── --}}
            @elseif($connectorType === 'missionway')

                {{-- Player Composition --}}
                <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        Player Composition
                        @if(!empty($data['player_id']))
                            <span class="text-[10px] font-mono text-gray-400">(Player ID: {{ $data['player_id'] }})</span>
                        @endif
                    </h3>

                    @if(!empty($data['composition']))
                        {{-- Düz veriler --}}
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                            @foreach(collect($data['composition'])->reject(fn($v) => is_array($v) || is_object($v))->take(12) as $key => $value)
                                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-3">
                                    <p class="text-[10px] text-gray-500 mb-0.5">{{ ucfirst(str_replace('_', ' ', $key)) }}</p>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">
                                        @if(is_bool($value))
                                            {{ $value ? 'Evet' : 'Hayır' }}
                                        @elseif(is_null($value))
                                            <span class="text-gray-300">-</span>
                                        @else
                                            {{ $value }}
                                        @endif
                                    </p>
                                </div>
                            @endforeach
                        </div>

                        {{-- İç içe veriler --}}
                        @foreach(collect($data['composition'])->filter(fn($v) => is_array($v)) as $key => $arr)
                            <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                                <p class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                    {{ ucfirst(str_replace('_', ' ', $key)) }}
                                </p>
                                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-3">
                                    <pre
                                        class="text-xs text-gray-600 dark:text-gray-400 whitespace-pre-wrap overflow-auto max-h-40">{{ json_encode($arr, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-sm text-gray-500 text-center py-4">Composition verisi bulunamadı</p>
                    @endif
                </div>

                {{-- Oturum İstatistikleri --}}
                @if(!empty($data['session_count']) || !empty($data['sessions']))
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                        <div
                            class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5 text-center">
                            <p class="text-2xl font-extrabold text-gray-900 dark:text-white">{{ $data['session_count'] ?? 0 }}</p>
                            <p class="text-xs text-gray-500 mt-1">Toplam Oturum</p>
                        </div>
                        @if(!empty($data['profile']))
                            @foreach(collect($data['profile'])->only(['totalScore', 'totalPlayTime', 'level'])->take(2) as $key => $val)
                                <div
                                    class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5 text-center">
                                    <p class="text-2xl font-extrabold text-blue-600">{{ $val ?? 0 }}</p>
                                    <p class="text-xs text-gray-500 mt-1">{{ ucfirst(str_replace(['total', 'Total'], '', $key)) }}</p>
                                </div>
                            @endforeach
                        @endif
                    </div>
                @endif

                {{-- Player Profile --}}
                @if(!empty($data['profile']))
                    <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4">Player Profili</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            @foreach(collect($data['profile'])->reject(fn($v) => is_array($v) || is_object($v))->take(8) as $key => $value)
                                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-3">
                                    <p class="text-[10px] text-gray-500 mb-0.5">{{ ucfirst(str_replace('_', ' ', $key)) }}</p>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ $value ?? '-' }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Oturum Listesi --}}
                @if(!empty($data['sessions']))
                    <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4">Katıldığı Oturumlar</h3>
                        <div class="space-y-3">
                            @foreach(collect($data['sessions'])->take(15) as $session)
                                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4 flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                                Oturum {{ $session['sessionId'] ?? $session['id'] ?? '-' }}
                                            </p>
                                            <p class="text-[10px] text-gray-400">
                                                @if(isset($session['createdAt']))
                                                    {{ \Carbon\Carbon::parse($session['createdAt'])->format('d.m.Y H:i') }}
                                                @elseif(isset($session['created_at']))
                                                    {{ \Carbon\Carbon::parse($session['created_at'])->format('d.m.Y H:i') }}
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                    @if(isset($session['role']))
                                        <span
                                            class="text-xs px-2 py-0.5 rounded-full font-medium bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                                            {{ $session['role'] }}
                                        </span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- İlerleme — Görsel Metrik Kartları --}}
                @if(!empty($data['progress']))
                    <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4">İlerleme Kayıtları</h3>
                        <div class="space-y-4">
                            @foreach(collect($data['progress'])->take(10) as $prog)
                                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                                            Simülasyon {{ $prog['simulationVersionId'] ?? $prog['simulationSessionId'] ?? '-' }}
                                        </p>
                                        @php $curScore = $prog['currentScore'] ?? 0; @endphp
                                        <span class="text-sm font-bold {{ $curScore >= 70 ? 'text-emerald-600' : ($curScore >= 40 ? 'text-amber-600' : 'text-red-600') }}">
                                            {{ $curScore }} puan
                                        </span>
                                    </div>
                                    @if(!empty($prog['currentMetrics']))
                                        @php $m = $prog['currentMetrics']; @endphp
                                        <div class="grid grid-cols-4 gap-3">
                                            <div class="text-center">
                                                <div class="w-12 h-12 mx-auto rounded-full border-3 border-red-400 flex items-center justify-center">
                                                    <span class="text-xs font-bold text-red-600">{{ $m['health'] ?? 0 }}</span>
                                                </div>
                                                <p class="text-[9px] text-gray-500 mt-1">❤️ Sağlık</p>
                                            </div>
                                            <div class="text-center">
                                                <div class="w-12 h-12 mx-auto rounded-full border-3 border-green-400 flex items-center justify-center">
                                                    <span class="text-xs font-bold text-green-600">{{ $m['resource'] ?? 0 }}</span>
                                                </div>
                                                <p class="text-[9px] text-gray-500 mt-1">🌿 Kaynak</p>
                                            </div>
                                            <div class="text-center">
                                                <div class="w-12 h-12 mx-auto rounded-full border-3 border-amber-400 flex items-center justify-center">
                                                    <span class="text-xs font-bold text-amber-600">{{ $m['ethics'] ?? 0 }}</span>
                                                </div>
                                                <p class="text-[9px] text-gray-500 mt-1">🧡 Etik</p>
                                            </div>
                                            <div class="text-center">
                                                <div class="w-12 h-12 mx-auto rounded-full border-3 border-blue-400 flex items-center justify-center">
                                                    <span class="text-xs font-bold text-blue-600">{{ $m['adaptation'] ?? 0 }}</span>
                                                </div>
                                                <p class="text-[9px] text-gray-500 mt-1">✅ Adaptasyon</p>
                                            </div>
                                        </div>
                                    @endif
                                    <div class="flex items-center gap-2 mt-2 text-[10px] text-gray-400">
                                        @if(isset($prog['startedAt']))
                                            <span>Başlangıç: {{ \Carbon\Carbon::parse($prog['startedAt'])->format('d.m.Y H:i') }}</span>
                                        @endif
                                        @if(isset($prog['completedAt']))
                                            <span>· Bitiş: {{ \Carbon\Carbon::parse($prog['completedAt'])->format('d.m.Y H:i') }}</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- ── WAYSTARTUP RAPORU ── --}}
            @elseif($connectorType === 'waystartup')

                {{-- Üye Bilgileri --}}
                <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                        WayStartup Üye Bilgileri
                        @if(!empty($data['member_id']))
                            <span class="text-[10px] font-mono text-gray-400">(Member ID: {{ $data['member_id'] }})</span>
                        @endif
                    </h3>

                    @if(!empty($data['member']))
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                            @foreach(collect($data['member'])->reject(fn($v) => is_array($v) || is_object($v))->take(12) as $key => $value)
                                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-3">
                                    <p class="text-[10px] text-gray-500 mb-0.5">{{ ucfirst(str_replace('_', ' ', $key)) }}</p>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">
                                        @if(is_bool($value))
                                            {{ $value ? 'Evet' : 'Hayır' }}
                                        @elseif(is_null($value))
                                            <span class="text-gray-300">-</span>
                                        @else
                                            {{ $value }}
                                        @endif
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500 text-center py-4">Üye verisi bulunamadı</p>
                    @endif
                </div>

                {{-- İlerleme İstatistikleri --}}
                <div class="grid grid-cols-3 gap-4">
                    <div
                        class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5 text-center">
                        <p class="text-2xl font-extrabold text-gray-900 dark:text-white">{{ $data['progress_count'] ?? 0 }}</p>
                        <p class="text-xs text-gray-500 mt-1">Simülasyon İlerlemesi</p>
                    </div>
                    <div
                        class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5 text-center">
                        <p class="text-2xl font-extrabold text-emerald-600">{{ $data['completed_steps'] ?? 0 }}</p>
                        <p class="text-xs text-gray-500 mt-1">Tamamlanan Adım</p>
                    </div>
                    <div
                        class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5 text-center">
                        <p class="text-2xl font-extrabold text-indigo-600">{{ $data['total_steps'] ?? 0 }}</p>
                        <p class="text-xs text-gray-500 mt-1">Toplam Adım</p>
                    </div>
                </div>

                {{-- Simülasyon İlerlemesi --}}
                @if(!empty($data['progress']))
                    <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4">Simülasyon İlerlemesi</h3>
                        <div class="space-y-3">
                            @foreach(collect($data['progress'])->take(10) as $prog)
                                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                                            Simülasyon {{ $prog['simulationId'] ?? $prog['simulation_id'] ?? '-' }}
                                        </p>
                                        @if(isset($prog['status']))
                                            <span
                                                class="text-xs px-2 py-0.5 rounded-full font-medium
                                                                        {{ $prog['status'] === 'completed' ? 'bg-emerald-100 text-emerald-700' : 'bg-indigo-100 text-indigo-700' }}">
                                                {{ ucfirst($prog['status']) }}
                                            </span>
                                        @endif
                                    </div>
                                    @if(isset($prog['completionPercentage']) || isset($prog['completion_percentage']))
                                        @php $pct = $prog['completionPercentage'] ?? $prog['completion_percentage'] ?? 0; @endphp
                                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                            <div class="bg-indigo-500 h-2 rounded-full transition-all" style="width: {{ min($pct, 100) }}%">
                                            </div>
                                        </div>
                                        <p class="text-[10px] text-gray-400 mt-1">{{ $pct }}% tamamlandı</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Adım İlerlemesi --}}
                @if(!empty($data['step_progress']))
                    <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4">Adım İlerlemesi</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="text-left border-b border-gray-100 dark:border-gray-700">
                                        <th class="pb-2 font-semibold text-gray-500 text-xs">Adım</th>
                                        <th class="pb-2 font-semibold text-gray-500 text-xs text-center">Durum</th>
                                        <th class="pb-2 font-semibold text-gray-500 text-xs text-center">Puan</th>
                                        <th class="pb-2 font-semibold text-gray-500 text-xs text-center">Coin</th>
                                        <th class="pb-2 font-semibold text-gray-500 text-xs text-right">Tarih</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                                    @foreach(collect($data['step_progress'])->take(20) as $sp)
                                        <tr>
                                            <td class="py-2 text-xs text-gray-900 dark:text-white">
                                                Adım {{ $sp['stepId'] ?? $sp['step_id'] ?? '-' }}
                                            </td>
                                            <td class="py-2 text-center">
                                                @php $spStatus = $sp['status'] ?? 'unknown'; @endphp
                                                <span
                                                    class="text-[10px] px-2 py-0.5 rounded-full font-medium
                                                        {{ $spStatus === 'completed' ? 'bg-emerald-100 text-emerald-700' : '' }}
                                                        {{ $spStatus === 'in_progress' ? 'bg-blue-100 text-blue-700' : '' }}
                                                        {{ $spStatus === 'locked' ? 'bg-gray-100 text-gray-500' : '' }}
                                                        {{ !in_array($spStatus, ['completed','in_progress','locked']) ? 'bg-gray-100 text-gray-600' : '' }}">
                                                    {{ match($spStatus) { 'completed' => 'Tamamlandı', 'in_progress' => 'Devam', 'locked' => 'Kilitli', default => ucfirst(str_replace('_', ' ', $spStatus)) } }}
                                                </span>
                                            </td>
                                            <td class="py-2 text-center">
                                                @if(isset($sp['earnedPoint']))
                                                    <span class="text-xs font-bold text-indigo-600">{{ $sp['earnedPoint'] }}</span>
                                                @else
                                                    <span class="text-xs text-gray-300">-</span>
                                                @endif
                                            </td>
                                            <td class="py-2 text-center">
                                                @if(isset($sp['earnedCoin']) && $sp['earnedCoin'] > 0)
                                                    <span class="text-xs font-semibold text-amber-600">🪙 {{ $sp['earnedCoin'] }}</span>
                                                @else
                                                    <span class="text-xs text-gray-300">-</span>
                                                @endif
                                            </td>
                                            <td class="py-2 text-right text-[10px] text-gray-400">
                                                @if(isset($sp['completedAt']))
                                                    {{ \Carbon\Carbon::parse($sp['completedAt'])->format('d.m.Y H:i') }}
                                                @elseif(isset($sp['startedAt']))
                                                    {{ \Carbon\Carbon::parse($sp['startedAt'])->format('d.m.Y H:i') }}
                                                @elseif(isset($sp['updatedAt']))
                                                    {{ \Carbon\Carbon::parse($sp['updatedAt'])->format('d.m.Y H:i') }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

            @else
                {{-- Generic / Bilinmeyen --}}
                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-8 text-center">
                    <p class="text-sm text-gray-500">Bu connector türü için detaylı raporlama henüz desteklenmiyor.</p>
                </div>
            @endif

            {{-- Ham JSON (debug) --}}
            <details class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C]">
                <summary
                    class="px-5 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800/30 transition rounded-xl">
                    Ham API Yanıtı (JSON)
                </summary>
                <div class="px-5 pb-4">
                    <pre
                        class="text-xs text-gray-600 dark:text-gray-400 whitespace-pre-wrap overflow-auto max-h-96 bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4">{{ json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </div>
            </details>

        @endif

    </div>
@endsection