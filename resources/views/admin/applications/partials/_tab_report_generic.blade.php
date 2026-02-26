{{-- Generic / WayStartup — Rapor Endpoint Yok --}}

<div class="space-y-6">

    <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-8 text-center">
        <div class="w-14 h-14 mx-auto mb-4 rounded-2xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
            <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
        </div>
        <h3 class="text-base font-bold text-gray-900 dark:text-white mb-2">Raporlama Henüz Mevcut Değil</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 max-w-md mx-auto leading-relaxed">
            Bu uygulama için rapor verileri çekecek API endpoint'i henüz aktif değil.
            Connector entegrasyonu tamamlandığında rapor verileri burada görüntülenecek.
        </p>

        <div class="mt-6 inline-flex items-center gap-2 px-4 py-2 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="text-xs text-gray-500 dark:text-gray-400">
                Connector: <code
                    class="font-mono bg-gray-100 dark:bg-gray-700 px-1 rounded">{{ class_basename($application->connector_class ?? 'Tanımsız') }}</code>
            </span>
        </div>
    </div>

    {{-- Mevcut Kullanıcı Özeti --}}
    @if($syncStats['total'] > 0)
        <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5">
            <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-3">Kullanıcı Özeti</h3>
            <div class="grid grid-cols-3 gap-4">
                <div class="text-center">
                    <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $syncStats['total'] }}</p>
                    <p class="text-[10px] text-gray-500">Toplam</p>
                </div>
                <div class="text-center">
                    <p class="text-lg font-bold text-emerald-600">{{ $syncStats['synced'] }}</p>
                    <p class="text-[10px] text-gray-500">Senkronize</p>
                </div>
                <div class="text-center">
                    <p class="text-lg font-bold text-red-500">{{ $syncStats['failed'] }}</p>
                    <p class="text-[10px] text-gray-500">Başarısız</p>
                </div>
            </div>
        </div>
    @endif

</div>