{{-- MissionWay Connector Rapor Sekmesi --}}

<div class="space-y-6">

    {{-- API Health + Genel İstatistikler --}}
    <div
        class="bg-gradient-to-r from-blue-50 to-cyan-50 dark:from-blue-900/10 dark:to-cyan-900/10 rounded-xl border border-blue-100 dark:border-blue-800/30 p-5">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
            </div>
            <div class="flex-1">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white">Mission Way — Simülasyon Raporu</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">Görev tabanlı öğrenme platformu — Simülasyonlar,
                    oturumlar ve oyuncu verileri</p>
            </div>
            @if(!empty($reportData['api_health']))
                <span
                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-semibold
                        {{ ($reportData['api_health']['status'] ?? '') === 'ok' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' }}">
                    <span
                        class="w-1.5 h-1.5 rounded-full {{ ($reportData['api_health']['status'] ?? '') === 'ok' ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
                    API {{ ($reportData['api_health']['status'] ?? '') === 'ok' ? 'Aktif' : 'Hata' }}
                </span>
            @endif
        </div>

        @if($reportData)
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-4">
                <div class="bg-white/60 dark:bg-gray-800/40 rounded-lg p-3 text-center">
                    <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $reportData['user_count'] ?? 0 }}</p>
                    <p class="text-[10px] text-gray-500">Toplam Kullanıcı</p>
                </div>
                <div class="bg-white/60 dark:bg-gray-800/40 rounded-lg p-3 text-center">
                    <p class="text-lg font-bold text-emerald-600">{{ $reportData['synced_count'] ?? 0 }}</p>
                    <p class="text-[10px] text-gray-500">Senkronize</p>
                </div>
                <div class="bg-white/60 dark:bg-gray-800/40 rounded-lg p-3 text-center">
                    @php
                        $simList = $reportData['simulations'] ?? [];
                        $simCount = isset($simList['data']) ? count($simList['data']) : (isset($simList[0]) ? count($simList) : 0);
                    @endphp
                    <p class="text-lg font-bold text-blue-600">{{ $simCount }}</p>
                    <p class="text-[10px] text-gray-500">Simülasyon</p>
                </div>
                <div class="bg-white/60 dark:bg-gray-800/40 rounded-lg p-3 text-center">
                    <p class="text-lg font-bold text-purple-600">{{ $reportData['session_stats']['total'] ?? 0 }}</p>
                    <p class="text-[10px] text-gray-500">Toplam Oturum</p>
                </div>
            </div>
        @endif
    </div>

    {{-- Simülasyon Listesi --}}
    @php
        $simulations = $reportData['simulations'] ?? [];
        $simData = isset($simulations['data']) ? $simulations['data'] : (isset($simulations[0]) ? $simulations : []);
    @endphp

    @if(!empty($simData))
        <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5">
            <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4">Simülasyonlar</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach(collect($simData)->take(9) as $sim)
                    <div
                        class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4 border border-gray-100 dark:border-gray-700/50 hover:shadow-md transition">
                        <div class="flex items-center gap-3 mb-3">
                            @if(!empty($sim['iconUrl']))
                                <img src="{{ $sim['iconUrl'] }}" alt="" class="w-10 h-10 rounded-lg object-cover">
                            @else
                                <div class="w-10 h-10 rounded-lg flex items-center justify-center text-white text-sm font-bold"
                                    style="background-color: {{ $sim['color'] ?? '#6366f1' }}">
                                    {{ mb_strtoupper(mb_substr($sim['name'] ?? 'S', 0, 1)) }}
                                </div>
                            @endif
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">
                                    {{ $sim['name'] ?? 'İsimsiz' }}
                                </p>
                                @if(!empty($sim['description']))
                                    <p class="text-[10px] text-gray-400 truncate">
                                        {{ is_array($sim['description']) ? json_encode($sim['description']) : \Illuminate\Support\Str::limit($sim['description'], 60) }}
                                    </p>
                                @endif
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            @if(isset($sim['difficultyLevel']))
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Zorluk</span>
                                    <span class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-semibold
                                        {{ $sim['difficultyLevel'] === 'easy' ? 'bg-emerald-100 text-emerald-700' : '' }}
                                        {{ $sim['difficultyLevel'] === 'medium' ? 'bg-amber-100 text-amber-700' : '' }}
                                        {{ $sim['difficultyLevel'] === 'hard' ? 'bg-red-100 text-red-700' : '' }}">
                                        {{ match($sim['difficultyLevel']) { 'easy' => 'Kolay', 'medium' => 'Orta', 'hard' => 'Zor', default => ucfirst($sim['difficultyLevel']) } }}
                                    </span>
                                </div>
                            @endif
                            @if(isset($sim['estimatedDuration']))
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Süre</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $sim['estimatedDuration'] }} dk</span>
                                </div>
                            @endif
                            @if(isset($sim['minPlayers']) || isset($sim['maxPlayers']))
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Oyuncu</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $sim['minPlayers'] ?? 1 }}-{{ $sim['maxPlayers'] ?? '∞' }}</span>
                                </div>
                            @endif
                            @if(isset($sim['totalSteps']))
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Adım</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $sim['totalSteps'] }}</span>
                                </div>
                            @endif
                            @if(isset($sim['status']))
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Durum</span>
                                    <span
                                        class="font-medium {{ $sim['status'] === 'active' ? 'text-emerald-600' : 'text-gray-400' }}">
                                        {{ ucfirst($sim['status']) }}
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Oturum İstatistikleri --}}
    @if(!empty($reportData['session_stats']['by_status']))
        <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5">
            <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4">Oturum İstatistikleri</h3>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                @foreach($reportData['session_stats']['by_status'] as $status => $count)
                    <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-3 text-center">
                        <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $count }}</p>
                        <p class="text-[10px] text-gray-500">{{ ucfirst($status ?: 'Bilinmeyen') }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Oturum Listesi Tablosu --}}
    @if(!empty($reportData['sessions'] ?? []))
        <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5">
            <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4">Son Oturumlar</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left border-b border-gray-100 dark:border-gray-700">
                            <th class="pb-3 font-semibold text-gray-500 dark:text-gray-400">Oturum Kodu</th>
                            <th class="pb-3 font-semibold text-gray-500 dark:text-gray-400">Simülasyon</th>
                            <th class="pb-3 font-semibold text-gray-500 dark:text-gray-400 text-center">Oyuncu</th>
                            <th class="pb-3 font-semibold text-gray-500 dark:text-gray-400 text-center">Final Skor</th>
                            <th class="pb-3 font-semibold text-gray-500 dark:text-gray-400 text-center">Durum</th>
                            <th class="pb-3 font-semibold text-gray-500 dark:text-gray-400 text-right">Tarih</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                        @foreach(collect($reportData['sessions'])->take(15) as $session)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition">
                                <td class="py-3 text-xs font-mono text-gray-500">{{ $session['code'] ?? $session['session_code'] ?? Str::limit($session['id'] ?? '-', 8) }}</td>
                                <td class="py-3 text-xs font-medium text-gray-900 dark:text-white">{{ $session['simulation_name'] ?? $session['simulationName'] ?? '-' }}</td>
                                <td class="py-3 text-center">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                        {{ $session['player_count'] ?? $session['playerCount'] ?? '-' }}
                                    </span>
                                </td>
                                <td class="py-3 text-center">
                                    @php $finalScore = $session['final_score'] ?? $session['finalScore'] ?? null; @endphp
                                    @if($finalScore !== null)
                                        <span class="text-xs font-bold {{ $finalScore >= 70 ? 'text-emerald-600' : ($finalScore >= 40 ? 'text-amber-600' : 'text-red-600') }}">
                                            {{ $finalScore }}
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="py-3 text-center">
                                    @php $sesStatus = $session['status'] ?? 'active'; @endphp
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-medium
                                        {{ $sesStatus === 'completed' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : '' }}
                                        {{ $sesStatus === 'active' || $sesStatus === 'in_progress' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' : '' }}
                                        {{ $sesStatus === 'abandoned' || $sesStatus === 'cancelled' ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' : '' }}">
                                        {{ match($sesStatus) { 'completed' => 'Tamamlandı', 'active' => 'Aktif', 'in_progress' => 'Devam', 'abandoned' => 'Terk', 'cancelled' => 'İptal', default => ucfirst($sesStatus) } }}
                                    </span>
                                </td>
                                <td class="py-3 text-right text-xs text-gray-400">
                                    {{ isset($session['started_at']) ? \Carbon\Carbon::parse($session['started_at'])->format('d.m.Y H:i') : (isset($session['created_at']) ? \Carbon\Carbon::parse($session['created_at'])->format('d.m.Y H:i') : '-') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Örnek Kompozisyon Verileri --}}
    @if(!empty($reportData['sample_compositions'] ?? []))
        <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5">
            <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4">Player Composition Örnekleri</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($reportData['sample_compositions'] as $comp)
                    <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4">
                        <div class="flex items-center gap-2 mb-3">
                            <div
                                class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-cyan-600 flex items-center justify-center text-white text-xs font-bold">
                                {{ mb_strtoupper(mb_substr($comp['user_name'] ?? 'U', 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $comp['user_name'] }}
                                </p>
                                <p class="text-[10px] text-gray-400">ID: {{ $comp['user_id'] }}</p>
                            </div>
                        </div>

                        @if(is_array($comp['data']))
                            <div class="space-y-1.5">
                                @foreach(collect($comp['data'])->take(6) as $key => $value)
                                    @if(!is_array($value) && !is_object($value))
                                        <div class="flex justify-between text-xs">
                                            <span class="text-gray-500">{{ ucfirst(str_replace('_', ' ', $key)) }}</span>
                                            <span
                                                class="font-medium text-gray-900 dark:text-white">{{ is_bool($value) ? ($value ? 'Evet' : 'Hayır') : $value }}</span>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Kullanıcı Rapor Listesi --}}
    <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-bold text-gray-900 dark:text-white">Kullanıcı Raporları</h3>
            <p class="text-xs text-gray-400">Her kullanıcı için detaylı MissionWay raporu</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left border-b border-gray-100 dark:border-gray-700">
                        <th class="pb-3 font-semibold text-gray-500 dark:text-gray-400">Kullanıcı</th>
                        <th class="pb-3 font-semibold text-gray-500 dark:text-gray-400">E-posta</th>
                        <th class="pb-3 font-semibold text-gray-500 dark:text-gray-400 text-center">Sync</th>
                        <th class="pb-3 font-semibold text-gray-500 dark:text-gray-400 text-right">Rapor</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                    @foreach($users as $user)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition">
                            <td class="py-3">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-cyan-600 flex items-center justify-center text-white text-xs font-bold">
                                        {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
                                    </div>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $user->full_name }}</span>
                                </div>
                            </td>
                            <td class="py-3 text-gray-500 dark:text-gray-400 text-xs">{{ $user->email }}</td>
                            <td class="py-3 text-center">
                                @php $syncStatus = $user->pivot->sync_status ?? 'pending'; @endphp
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium
                                            {{ $syncStatus === 'synced' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : '' }}
                                            {{ $syncStatus === 'failed' ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' : '' }}
                                            {{ $syncStatus === 'pending' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' : '' }}">
                                    {{ ucfirst($syncStatus) }}
                                </span>
                            </td>
                            <td class="py-3 text-right">
                                <a href="{{ route('admin.applications.user-report', [$application, $user]) }}"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 dark:text-blue-300 dark:bg-blue-900/20 dark:hover:bg-blue-900/30 rounded-lg transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    Detay Gör
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                {{ $users->appends(['tab' => 'report'])->links() }}
            </div>
        @endif
    </div>

    {{-- ═══ API CATALOG DATA ═══ --}}

    {{-- Objectives --}}
    @if(!empty($reportData['objectives']) && count($reportData['objectives']) > 0)
    <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5">
        <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4">🎯 Simulation Objectives</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left border-b border-gray-100 dark:border-gray-700">
                        <th class="pb-3 font-semibold text-gray-500">#</th>
                        <th class="pb-3 font-semibold text-gray-500">Ad</th>
                        <th class="pb-3 font-semibold text-gray-500">Anahtar</th>
                        <th class="pb-3 font-semibold text-gray-500">Açıklama</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                    @foreach(collect($reportData['objectives'])->take(20) as $i => $obj)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30">
                        <td class="py-2 text-xs text-gray-400">{{ $i + 1 }}</td>
                        <td class="py-2 text-xs font-medium text-gray-900 dark:text-white">{{ $obj['name'] ?? $obj['title'] ?? '-' }}</td>
                        <td class="py-2"><code class="text-[10px] bg-gray-100 dark:bg-gray-800 px-1.5 py-0.5 rounded">{{ $obj['key'] ?? $obj['slug'] ?? '-' }}</code></td>
                        <td class="py-2 text-xs text-gray-400 max-w-xs truncate">{{ $obj['description'] ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Media Assets (GİZLENDİ) --}}
    {{-- 
    @if(!empty($reportData['mediaAssets']) && count($reportData['mediaAssets']) > 0)
    <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5">
        ... (Kullanılmayan/Çalışmayan medya assetleri bloğu)
    </div>
    @endif 
    --}}

    {{-- Version Roles --}}
    @if(!empty($reportData['simVersionRoles']) && count($reportData['simVersionRoles']) > 0)
    <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5">
        <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4">🎭 Simulation Version Roles</h3>
        <div class="flex flex-wrap gap-2">
            @foreach($reportData['simVersionRoles'] as $role)
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-300 border border-blue-100 dark:border-blue-800/30">
                🎭 {{ $role['name'] ?? $role['roleName'] ?? 'Role #' . ($role['id'] ?? '') }}
            </span>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Languages --}}
    @if(!empty($reportData['languages']) && count($reportData['languages']) > 0)
    <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5">
        <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4">🌐 Desteklenen Diller</h3>
        <div class="flex flex-wrap gap-2">
            @foreach($reportData['languages'] as $lang)
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium bg-gradient-to-r from-cyan-50 to-purple-50 dark:from-cyan-900/20 dark:to-purple-900/20 text-cyan-700 dark:text-cyan-300 border border-cyan-100 dark:border-cyan-800/30">
                🌍 {{ $lang['name'] ?? $lang['code'] ?? 'Language' }}
            </span>
            @endforeach
        </div>
    </div>
    @endif

</div>