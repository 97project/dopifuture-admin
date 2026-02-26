{{-- Uygulama Detay Tab — Her uygulama için ayrı içerik --}}
@php
    $app = $currentApp ?? null;
    $slug = $appSlug ?? '';
    $syncStatus = $app ? ($app->pivot->sync_status ?? 'pending') : 'unknown';
    $connector = $app ? $app->resolveConnector() : null;
    $isReady = $connector && $connector::isReady();

    $appLabels = [
        'mission-way'  => ['Mission Way',  '🎯', '#F87D17',  'Oyun tabanlı görev platformu'],
        'way-startup'  => ['Way Startup',  '🚀', '#6366F1',  'Girişimcilik eğitim platformu'],
        'role-galaxy'  => ['Role Galaxy',  '🎭', '#EC4899',  'Senaryo simülasyonu — karar verme ve rol yapma'],
        'way-ai-coach' => ['Way AI Coach', '🤖', '#0B6AB2',  'Yapay zeka destekli ders asistanı'],
        'study-space'  => ['Study Space',  '📚', '#10B981',  'Soru-cevap ve çalışma alanı'],
    ];
    [$appLabel, $appEmoji, $appColor, $appDesc] = $appLabels[$slug] ?? [$slug, '📦', '#6B7280', ''];
@endphp

<div class="space-y-6">

    {{-- ═══ APP HEADER CARD ═══ --}}
    <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] overflow-hidden">
        <div class="p-5 flex items-center justify-between" style="border-left: 4px solid {{ $appColor }}">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center text-2xl"
                    style="background: {{ $appColor }}15">
                    {{ $appEmoji }}
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $appLabel }}</h3>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $appDesc }}</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                {{-- Sync Status Badge --}}
                @if(!$isReady)
                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-gray-100 dark:bg-gray-800/40 text-gray-500">
                        ⚙️ Entegrasyon Bekleniyor
                    </span>
                @elseif($syncStatus === 'synced')
                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>Senkron
                    </span>
                @elseif($syncStatus === 'failed')
                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-red-50 dark:bg-red-900/20 text-red-500">
                        <span class="w-2 h-2 rounded-full bg-red-500"></span>Başarısız
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 dark:bg-amber-900/20 text-amber-600">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>Bekliyor
                    </span>
                @endif

                {{-- Actions --}}
                @if($app && $isReady)
                    <form action="{{ route('admin.applications.sync-user', [$app, $user]) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" title="Tekrar Senkronla"
                            class="p-2 rounded-lg text-[#0B6AB2] hover:bg-blue-50 dark:hover:bg-blue-900/20 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </button>
                    </form>
                @endif
                @if($app)
                    <a href="{{ route('admin.applications.show', $app) }}" title="Uygulama Detay"
                        class="p-2 rounded-lg text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                    </a>
                @endif
            </div>
        </div>

        {{-- Sync Details --}}
        @if($app)
            <div class="px-5 py-3 bg-gray-50/50 dark:bg-[#0A1628]/30 border-t border-gray-100 dark:border-[#1A3A5C] grid grid-cols-2 sm:grid-cols-4 gap-4 text-center">
                <div>
                    <p class="text-[10px] text-gray-400 uppercase tracking-wider">Atanma Tarihi</p>
                    <p class="text-xs font-semibold text-gray-700 dark:text-gray-300 mt-0.5">
                        {{ $app->pivot->granted_at ? \Carbon\Carbon::parse($app->pivot->granted_at)->format('d.m.Y') : '—' }}
                    </p>
                </div>
                <div>
                    <p class="text-[10px] text-gray-400 uppercase tracking-wider">Son Senkron</p>
                    <p class="text-xs font-semibold text-gray-700 dark:text-gray-300 mt-0.5">
                        {{ $app->pivot->synced_at ? \Carbon\Carbon::parse($app->pivot->synced_at)->format('d.m.Y H:i') : '—' }}
                    </p>
                </div>
                <div>
                    <p class="text-[10px] text-gray-400 uppercase tracking-wider">Atayan</p>
                    <p class="text-xs font-semibold text-gray-700 dark:text-gray-300 mt-0.5">
                        @if($app->pivot->granted_by)
                            {{ \App\Models\User::find($app->pivot->granted_by)?->full_name ?? '—' }}
                        @else
                            —
                        @endif
                    </p>
                </div>
                <div>
                    <p class="text-[10px] text-gray-400 uppercase tracking-wider">Connector</p>
                    <p class="text-xs font-mono text-gray-500 mt-0.5">{{ class_basename($app->connector_class ?? '') ?: '—' }}</p>
                </div>
            </div>
        @endif
    </div>

    {{-- ═══ HATA DETAYI ═══ --}}
    @if($syncStatus === 'failed' && ($app->pivot->sync_error ?? null))
        <div class="flex items-start gap-3 bg-red-50 dark:bg-red-900/10 border border-red-200 dark:border-red-800/50 rounded-xl p-4">
            <svg class="w-5 h-5 text-red-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>
                <p class="text-sm font-semibold text-red-700 dark:text-red-400">Son Senkron Hatası</p>
                <p class="text-xs text-red-600 dark:text-red-300 font-mono mt-1">{{ $app->pivot->sync_error }}</p>
            </div>
        </div>
    @endif

    {{-- ═══ REMOTE USER DATA (Connector'dan çekilen) ═══ --}}
    @if($isReady && ($remoteUser ?? null))
        <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5">
            <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                <svg class="w-4 h-4 text-[#0B6AB2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Uzak Platform Profili
            </h4>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                @foreach([
                    'ID' => $remoteUser['id'] ?? '—',
                    'İsim' => ($remoteUser['name'] ?? '') . ' ' . ($remoteUser['surname'] ?? ''),
                    'Email' => $remoteUser['email'] ?? '—',
                    'Premium' => isset($remoteUser['is_premium']) ? ($remoteUser['is_premium'] ? '✅ Evet' : '❌ Hayır') : '—',
                    'Roller' => isset($remoteUser['roles']) ? implode(', ', array_column($remoteUser['roles'], 'title')) : '—',
                    '2FA' => isset($remoteUser['two_factor']) ? ($remoteUser['two_factor'] ? '🔒 Aktif' : 'Pasif') : '—',
                ] as $label => $value)
                    <div class="bg-gray-50 dark:bg-[#0A1628]/40 rounded-lg p-3">
                        <p class="text-[10px] text-gray-400 uppercase tracking-wider">{{ $label }}</p>
                        <p class="text-xs font-semibold text-gray-700 dark:text-gray-300 mt-0.5 truncate">{{ $value }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ═══ OTURUM GEÇMİŞİ (Vega uygulamaları) ═══ --}}
    @if(!empty($sessions))
        <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100 dark:border-[#1A3A5C]">
                <h4 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#0B6AB2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Oturum Geçmişi ({{ count($sessions) }})
                </h4>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-[#1A3A5C]">
                        <th class="text-left px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">Oturum ID</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">Modül</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">Tarih</th>
                        <th class="text-center px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">Durum</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-[#1A3A5C]/50">
                    @foreach($sessions as $session)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-[#0A1628]/30 transition">
                            <td class="px-5 py-3 font-mono text-xs text-gray-600 dark:text-gray-400">{{ $session['id'] ?? $session['session_id'] ?? '—' }}</td>
                            <td class="px-5 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold
                                    {{ ($session['module'] ?? '') === 'lecturer' ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600' : 'bg-pink-50 dark:bg-pink-900/20 text-pink-600' }}">
                                    {{ $session['app_name'] ?? ucfirst($session['module'] ?? 'Unknown') }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-xs text-gray-500">{{ $session['created_at'] ?? $session['started_at'] ?? '—' }}</td>
                            <td class="px-5 py-3 text-center">
                                @if(($session['status'] ?? '') === 'completed' || ($session['ended'] ?? false))
                                    <span class="text-emerald-500 text-xs font-bold">✅ Tamamlandı</span>
                                @elseif(($session['status'] ?? '') === 'active')
                                    <span class="text-blue-500 text-xs font-bold">🔵 Aktif</span>
                                @else
                                    <span class="text-gray-400 text-xs">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @elseif($isReady && ($remoteUser ?? null))
        {{-- Connector hazır, kullanıcı var ama oturum yok --}}
        <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-12 text-center">
            <div class="w-14 h-14 rounded-2xl bg-gray-100 dark:bg-[#0A1628] flex items-center justify-center mx-auto mb-3">
                <svg class="w-7 h-7 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <p class="text-sm font-medium text-gray-400">Bu uygulamada henüz oturum kaydı yok</p>
        </div>
    @endif

    {{-- ═══ MISSION WAY & WAY STARTUP SPECIFIC ═══ --}}
    @if(in_array($slug, ['mission-way', 'way-startup']) && $isReady && ($remoteUser ?? null))
        <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5">
            <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                <svg class="w-4 h-4" style="color: {{ $appColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                Platform Verileri
            </h4>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                @foreach($remoteUser as $key => $value)
                    @if(!is_array($value) && !is_null($value) && !in_array($key, ['id', 'email', 'name', 'surname', 'created_at', 'updated_at']))
                        <div class="bg-gray-50 dark:bg-[#0A1628]/40 rounded-lg p-3">
                            <p class="text-[10px] text-gray-400 uppercase tracking-wider">{{ str_replace('_', ' ', $key) }}</p>
                            <p class="text-xs font-semibold text-gray-700 dark:text-gray-300 mt-0.5">{{ is_bool($value) ? ($value ? 'Evet' : 'Hayır') : $value }}</p>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    @endif

    {{-- ═══ CONNECTOR HAZIR DEĞİLSE BİLGİ ═══ --}}
    @if(!$isReady && $app)
        <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-12 text-center">
            <div class="w-14 h-14 rounded-2xl bg-amber-50 dark:bg-amber-900/20 flex items-center justify-center mx-auto mb-3">
                <svg class="w-7 h-7 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
            </div>
            <p class="text-sm font-medium text-gray-500">Bu uygulamanın API entegrasyonu henüz tamamlanmadı</p>
            <p class="text-xs text-gray-400 mt-1">Connector yapılandırıldığında platform verileri burada görüntülenecek.</p>
        </div>
    @endif

    {{-- ═══ UYGULAMA ATANAMADIYSA ═══ --}}
    @if(!$app)
        <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-12 text-center">
            <div class="w-14 h-14 rounded-2xl bg-gray-100 dark:bg-[#0A1628] flex items-center justify-center mx-auto mb-3">
                <svg class="w-7 h-7 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                </svg>
            </div>
            <p class="text-sm font-medium text-gray-400">Bu uygulama kullanıcıya atanmamış</p>
        </div>
    @endif
</div>
