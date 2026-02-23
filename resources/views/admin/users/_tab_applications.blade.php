{{-- Applications Tab — Sync durumu ile --}}
<div class="space-y-6">
    @php $userApps = $applications ?? collect(); @endphp

    @if($userApps->count())
        <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C]">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-[#1A3A5C]">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#0B6AB2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                    Uygulamalar ({{ $userApps->count() }})
                </h3>
            </div>
            <div class="divide-y divide-gray-50 dark:divide-[#1A3A5C]/50">
                @foreach($userApps as $app)
                    <div class="px-6 py-4 flex items-center justify-between hover:bg-gray-50/50 dark:hover:bg-[#0A1628]/30 transition group">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center"
                                style="background-color: {{ $app->color ?? '#0B6AB2' }}20">
                                @if($app->icon)
                                    <img src="{{ $app->icon }}" alt="" class="w-6 h-6 rounded">
                                @else
                                    <svg class="w-5 h-5" fill="none" stroke="{{ $app->color ?? '#0B6AB2' }}" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6z" />
                                    </svg>
                                @endif
                            </div>
                            <div>
                                <h4 class="text-sm font-semibold text-gray-900 dark:text-white">{{ $app->name }}</h4>
                                <p class="text-[10px] text-gray-400 mt-0.5">{{ $app->slug }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            {{-- Sync Status Badge --}}
                            @php $syncStatus = $app->pivot->sync_status ?? 'pending'; @endphp
                            @if($syncStatus === 'synced')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Senkron
                                </span>
                            @elseif($syncStatus === 'failed')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-50 dark:bg-red-900/20 text-red-500"
                                    title="{{ $app->pivot->sync_error }}">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>Başarısız
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 dark:bg-amber-900/20 text-amber-600">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>Bekliyor
                                </span>
                            @endif

                            {{-- Senkron tarihi --}}
                            <div class="text-right">
                                @if($app->pivot->synced_at)
                                    <p class="text-[10px] text-gray-400">
                                        {{ \Carbon\Carbon::parse($app->pivot->synced_at)->format('d.m.Y H:i') }}
                                    </p>
                                @endif
                                @if($app->pivot->granted_at)
                                    <p class="text-[10px] text-gray-300">
                                        Atandı: {{ \Carbon\Carbon::parse($app->pivot->granted_at)->format('d.m.Y') }}
                                    </p>
                                @endif
                            </div>

                            {{-- Aksiyon butonları --}}
                            <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition">
                                {{-- Tekrar Senkronla --}}
                                <form action="{{ route('admin.applications.sync-user', [$app, $user]) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" title="Tekrar Senkronla"
                                        class="p-1.5 rounded-lg text-[#0B6AB2] hover:bg-blue-50 dark:hover:bg-blue-900/20 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                    </button>
                                </form>
                                {{-- App Detay --}}
                                <a href="{{ route('admin.applications.show', $app) }}" title="Uygulama Detay"
                                    class="p-1.5 rounded-lg text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- Failed hata detayı --}}
                    @if($syncStatus === 'failed' && $app->pivot->sync_error)
                        <div class="px-6 py-2 bg-red-50/50 dark:bg-red-900/10">
                            <p class="text-[10px] text-red-500 font-mono">⚠️ {{ $app->pivot->sync_error }}</p>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    @else
        <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-16 text-center">
            <div class="w-16 h-16 rounded-2xl bg-gray-100 dark:bg-[#0A1628] flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6z" />
                </svg>
            </div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Bu kullanıcıya atanmış uygulama yok</p>
        </div>
    @endif
</div>