{{-- Uygulama Rapor Sekmesi (Role Galaxy / WAY AI Coach / Study Space) --}}
{{-- Her biri VegaConnector kullanır, slug'a göre farklı modül verisi gösterir --}}

@php
    $appSlug = $application->slug;
    $sessions = $reportData['sessions'] ?? [];
    $sessionCount = $reportData['session_count'] ?? 0;
    $apiHealth = $reportData['api_health'] ?? null;

    // Threshold helper
    function thresholdBadge($score) {
        if ($score >= 90) return ['Refah', 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400', 'bg-emerald-500'];
        if ($score >= 70) return ['Denge', 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400', 'bg-blue-500'];
        if ($score >= 50) return ['Kriz', 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400', 'bg-amber-500'];
        return ['Felaket', 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400', 'bg-red-500'];
    }

    // Uygulama bazlı renk ve ikon (Vega terimi kullanılmaz)
    $appConfig = match($appSlug) {
        'role-galaxy' => ['label' => 'Role Galaxy', 'sub' => 'Senaryo Simülasyonu', 'from' => 'from-orange-50', 'to' => 'to-amber-50', 'darkFrom' => 'dark:from-orange-900/10', 'darkTo' => 'dark:to-amber-900/10', 'border' => 'border-orange-100 dark:border-orange-800/30', 'icon_bg' => 'bg-orange-100 dark:bg-orange-900/30', 'icon_color' => 'text-orange-600 dark:text-orange-400', 'badge_color' => 'text-orange-700 bg-orange-50 hover:bg-orange-100 dark:text-orange-300 dark:bg-orange-900/20 dark:hover:bg-orange-900/30', 'icon' => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z'],
        'way-ai-coach' => ['label' => 'WAY AI Coach', 'sub' => 'Yapay Zeka Eğitmen', 'from' => 'from-teal-50', 'to' => 'to-cyan-50', 'darkFrom' => 'dark:from-teal-900/10', 'darkTo' => 'dark:to-cyan-900/10', 'border' => 'border-teal-100 dark:border-teal-800/30', 'icon_bg' => 'bg-teal-100 dark:bg-teal-900/30', 'icon_color' => 'text-teal-600 dark:text-teal-400', 'badge_color' => 'text-teal-700 bg-teal-50 hover:bg-teal-100 dark:text-teal-300 dark:bg-teal-900/20 dark:hover:bg-teal-900/30', 'icon' => 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z'],
        'study-space' => ['label' => 'Study Space', 'sub' => 'Soru-Cevap & Sohbet', 'from' => 'from-violet-50', 'to' => 'to-purple-50', 'darkFrom' => 'dark:from-violet-900/10', 'darkTo' => 'dark:to-purple-900/10', 'border' => 'border-violet-100 dark:border-violet-800/30', 'icon_bg' => 'bg-violet-100 dark:bg-violet-900/30', 'icon_color' => 'text-violet-600 dark:text-violet-400', 'badge_color' => 'text-violet-700 bg-violet-50 hover:bg-violet-100 dark:text-violet-300 dark:bg-violet-900/20 dark:hover:bg-violet-900/30', 'icon' => 'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z'],
        default => ['label' => 'Uygulama', 'sub' => '', 'from' => 'from-gray-50', 'to' => 'to-gray-50', 'darkFrom' => 'dark:from-gray-900/10', 'darkTo' => 'dark:to-gray-900/10', 'border' => 'border-gray-100', 'icon_bg' => 'bg-gray-100', 'icon_color' => 'text-gray-600', 'badge_color' => 'text-gray-700 bg-gray-50', 'icon' => 'M4 6h16M4 12h16M4 18h16'],
    };
@endphp

<div class="space-y-6">

    {{-- Uygulama Başlık Kartı --}}
    <div class="bg-gradient-to-r {{ $appConfig['from'] }} {{ $appConfig['to'] }} {{ $appConfig['darkFrom'] }} {{ $appConfig['darkTo'] }} rounded-xl border {{ $appConfig['border'] }} p-5">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-xl {{ $appConfig['icon_bg'] }} flex items-center justify-center">
                <svg class="w-5 h-5 {{ $appConfig['icon_color'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $appConfig['icon'] }}" />
                </svg>
            </div>
            <div class="flex-1">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white">{{ $appConfig['label'] }} — Rapor</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $appConfig['sub'] }}</p>
            </div>
            @if($apiHealth)
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-semibold
                    {{ ($apiHealth['ok'] ?? false) ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ ($apiHealth['ok'] ?? false) ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
                    API {{ ($apiHealth['ok'] ?? false) ? 'Aktif' : 'Hata' }}
                </span>
            @endif
        </div>

        {{-- İstatistik Kartları (Slug bazlı) --}}
        @if($reportData)
            <div class="grid grid-cols-4 gap-3 mt-4">
                <div class="bg-white/60 dark:bg-gray-800/40 rounded-lg p-3 text-center">
                    <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $reportData['user_count'] ?? 0 }}</p>
                    <p class="text-[10px] text-gray-500">Toplam Kullanıcı</p>
                </div>
                <div class="bg-white/60 dark:bg-gray-800/40 rounded-lg p-3 text-center">
                    <p class="text-lg font-bold text-emerald-600">{{ $reportData['synced_count'] ?? 0 }}</p>
                    <p class="text-[10px] text-gray-500">Senkronize</p>
                </div>
                <div class="bg-white/60 dark:bg-gray-800/40 rounded-lg p-3 text-center">
                    <p class="text-lg font-bold text-purple-600">{{ $sessionCount }}</p>
                    <p class="text-[10px] text-gray-500">Toplam Oturum</p>
                </div>

                @if($appSlug === 'role-galaxy')
                    <div class="bg-white/60 dark:bg-gray-800/40 rounded-lg p-3 text-center">
                        <p class="text-lg font-bold text-orange-600">{{ $reportData['avg_score'] ?? 0 }}</p>
                        <p class="text-[10px] text-gray-500">Ort. Skor</p>
                    </div>
                @elseif($appSlug === 'way-ai-coach')
                    <div class="bg-white/60 dark:bg-gray-800/40 rounded-lg p-3 text-center">
                        <p class="text-lg font-bold text-teal-600">{{ $reportData['total_messages'] ?? 0 }}</p>
                        <p class="text-[10px] text-gray-500">Toplam Mesaj</p>
                    </div>
                @elseif($appSlug === 'study-space')
                    <div class="bg-white/60 dark:bg-gray-800/40 rounded-lg p-3 text-center">
                        <p class="text-lg font-bold text-violet-600">{{ $sessionCount }}</p>
                        <p class="text-[10px] text-gray-500">Soru Sayısı</p>
                    </div>
                @endif
            </div>
        @endif
    </div>

    {{-- ═══ ROLE GALAXY — Simülatör Oturum Listesi ═══ --}}
    @if($appSlug === 'role-galaxy' && count($sessions) > 0)
        <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5">
            <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4">Son Simülasyon Oturumları</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left border-b border-gray-100 dark:border-gray-700">
                            <th class="pb-3 font-semibold text-gray-500 dark:text-gray-400">Öğrenci</th>
                            <th class="pb-3 font-semibold text-gray-500 dark:text-gray-400">Senaryo</th>
                            <th class="pb-3 font-semibold text-gray-500 dark:text-gray-400 text-center">Skor</th>
                            <th class="pb-3 font-semibold text-gray-500 dark:text-gray-400 text-center">Durum</th>
                            <th class="pb-3 font-semibold text-gray-500 dark:text-gray-400 text-right">Tarih</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                        @foreach($sessions as $session)
                            @php
                                $score = $session['score'] ?? 0;
                                [$thLabel, $thClass, $thDot] = thresholdBadge($score);
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition">
                                <td class="py-3 font-medium text-gray-900 dark:text-white text-xs">{{ $session['panel_user_name'] ?? '-' }}</td>
                                <td class="py-3 text-gray-500 dark:text-gray-400 text-xs">{{ $session['scenario_name'] ?? $session['type'] ?? '-' }}</td>
                                <td class="py-3 text-center">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $thClass }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $thDot }}"></span>
                                        {{ $score }} — {{ $thLabel }}
                                    </span>
                                </td>
                                <td class="py-3 text-center">
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-medium
                                        {{ ($session['status'] ?? '') === 'completed' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' }}">
                                        {{ ($session['status'] ?? '') === 'completed' ? 'Tamamlandı' : ucfirst($session['status'] ?? 'Aktif') }}
                                    </span>
                                </td>
                                <td class="py-3 text-right text-xs text-gray-400">{{ isset($session['created_at']) ? \Carbon\Carbon::parse($session['created_at'])->format('d.m.Y H:i') : '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- ═══ WAY AI COACH — Öğretmen Oturum Listesi ═══ --}}
    @if($appSlug === 'way-ai-coach' && count($sessions) > 0)
        <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5">
            <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4">Son AI Coach Oturumları</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left border-b border-gray-100 dark:border-gray-700">
                            <th class="pb-3 font-semibold text-gray-500 dark:text-gray-400">Öğrenci</th>
                            <th class="pb-3 font-semibold text-gray-500 dark:text-gray-400">Konu</th>
                            <th class="pb-3 font-semibold text-gray-500 dark:text-gray-400 text-center">Mesaj</th>
                            <th class="pb-3 font-semibold text-gray-500 dark:text-gray-400 text-center">Durum</th>
                            <th class="pb-3 font-semibold text-gray-500 dark:text-gray-400 text-right">Tarih</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                        @foreach($sessions as $session)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition">
                                <td class="py-3 font-medium text-gray-900 dark:text-white text-xs">{{ $session['panel_user_name'] ?? '-' }}</td>
                                <td class="py-3 text-gray-500 dark:text-gray-400 text-xs">{{ $session['title'] ?? $session['topic'] ?? '-' }}</td>
                                <td class="py-3 text-center">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-teal-100 text-teal-700 dark:bg-teal-900/30 dark:text-teal-400">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" /></svg>
                                        {{ $session['message_count'] ?? 0 }}
                                    </span>
                                </td>
                                <td class="py-3 text-center">
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-medium
                                        {{ ($session['status'] ?? '') === 'ended' ? 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' }}">
                                        {{ ($session['status'] ?? '') === 'ended' ? 'Sonlandı' : 'Aktif' }}
                                    </span>
                                </td>
                                <td class="py-3 text-right text-xs text-gray-400">{{ isset($session['created_at']) ? \Carbon\Carbon::parse($session['created_at'])->format('d.m.Y H:i') : '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- ═══ STUDY SPACE — Chatbot Oturum Listesi ═══ --}}
    @if($appSlug === 'study-space' && count($sessions) > 0)
        <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5">
            <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4">Son Chatbot Oturumları</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left border-b border-gray-100 dark:border-gray-700">
                            <th class="pb-3 font-semibold text-gray-500 dark:text-gray-400">Öğrenci</th>
                            <th class="pb-3 font-semibold text-gray-500 dark:text-gray-400">Konu / Thread</th>
                            <th class="pb-3 font-semibold text-gray-500 dark:text-gray-400 text-center">Mesaj</th>
                            <th class="pb-3 font-semibold text-gray-500 dark:text-gray-400 text-right">Tarih</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                        @foreach($sessions as $session)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition">
                                <td class="py-3 font-medium text-gray-900 dark:text-white text-xs">{{ $session['panel_user_name'] ?? '-' }}</td>
                                <td class="py-3 text-gray-500 dark:text-gray-400 text-xs">{{ $session['title'] ?? Str::limit($session['thread_id'] ?? '-', 20) }}</td>
                                <td class="py-3 text-center">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-400">
                                        {{ $session['message_count'] ?? 0 }}
                                    </span>
                                </td>
                                <td class="py-3 text-right text-xs text-gray-400">{{ isset($session['created_at']) ? \Carbon\Carbon::parse($session['created_at'])->format('d.m.Y H:i') : '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Oturum yoksa bilgi mesajı --}}
    @if(count($sessions) === 0)
        <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-8 text-center">
            <div class="w-12 h-12 mx-auto mb-3 rounded-full {{ $appConfig['icon_bg'] }} flex items-center justify-center">
                <svg class="w-6 h-6 {{ $appConfig['icon_color'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                </svg>
            </div>
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Henüz oturum verisi bulunamadı</p>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Senkronize kullanıcılar etkinlik gösterdiğinde burada listelenecek</p>
        </div>
    @endif

    {{-- Kullanıcı Rapor Listesi (Tüm slug'lar için ortak) --}}
    <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-bold text-gray-900 dark:text-white">Kullanıcı Raporları</h3>
            <p class="text-xs text-gray-400">Her kullanıcı için detaylı oturum/performans raporu</p>
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
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center text-white text-xs font-bold">
                                        {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
                                    </div>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $user->full_name }}</span>
                                </div>
                            </td>
                            <td class="py-3 text-gray-500 dark:text-gray-400 text-xs">{{ $user->email }}</td>
                            <td class="py-3 text-center">
                                @php $syncStatus = $user->pivot->sync_status ?? 'pending'; @endphp
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium
                                    {{ $syncStatus === 'synced' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : '' }}
                                    {{ $syncStatus === 'failed' ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' : '' }}
                                    {{ $syncStatus === 'pending' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' : '' }}">
                                    {{ ucfirst($syncStatus) }}
                                </span>
                            </td>
                            <td class="py-3 text-right">
                                <a href="{{ route('admin.applications.user-report', [$application, $user]) }}"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium {{ $appConfig['badge_color'] }} rounded-lg transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                    Rapor Gör
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

</div>