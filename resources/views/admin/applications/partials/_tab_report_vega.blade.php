{{-- Vega Connector Rapor Sekmesi (Role Galaxy / Way AI Coach / Study Space) --}}

<div class="space-y-6">

    {{-- Modül Bilgilendirme --}}
    <div
        class="bg-gradient-to-r from-purple-50 to-indigo-50 dark:from-purple-900/10 dark:to-indigo-900/10 rounded-xl border border-purple-100 dark:border-purple-800/30 p-5">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-xl bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
                <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
            </div>
            <div>
                <h3 class="text-sm font-bold text-gray-900 dark:text-white">Vega Platform Raporu</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    @if($application->slug === 'role-galaxy')
                        Senaryo Simülasyonu — Simulator Oturumları
                    @elseif($application->slug === 'way-ai-coach')
                        Yapay Zeka Eğitmen — Lecturer Oturumları
                    @elseif($application->slug === 'study-space')
                        Soru-Cevap & Sohbet — Tüm Oturumlar
                    @endif
                </p>
            </div>
        </div>

        @if($reportData)
            <div class="grid grid-cols-3 gap-3 mt-4">
                <div class="bg-white/60 dark:bg-gray-800/40 rounded-lg p-3 text-center">
                    <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $reportData['user_count'] ?? 0 }}</p>
                    <p class="text-[10px] text-gray-500">Toplam Kullanıcı</p>
                </div>
                <div class="bg-white/60 dark:bg-gray-800/40 rounded-lg p-3 text-center">
                    <p class="text-lg font-bold text-emerald-600">{{ $reportData['synced_count'] ?? 0 }}</p>
                    <p class="text-[10px] text-gray-500">Senkronize</p>
                </div>
                <div class="bg-white/60 dark:bg-gray-800/40 rounded-lg p-3 text-center">
                    <p class="text-lg font-bold text-purple-600">{{ $reportData['module'] ?? '-' }}</p>
                    <p class="text-[10px] text-gray-500">Aktif Modül</p>
                </div>
            </div>
        @endif
    </div>

    {{-- Kullanıcı Rapor Listesi --}}
    <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-bold text-gray-900 dark:text-white">Kullanıcı Raporları</h3>
            <p class="text-xs text-gray-400">Her kullanıcı için Vega API'den detaylı oturum/performans raporu çekilir
            </p>
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
                                        class="w-8 h-8 rounded-full bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center text-white text-xs font-bold">
                                        {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
                                    </div>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $user->full_name }}</span>
                                </div>
                            </td>
                            <td class="py-3 text-gray-500 dark:text-gray-400 text-xs">{{ $user->email }}</td>
                            <td class="py-3 text-center">
                                @php $syncStatus = $user->pivot->sync_status ?? 'pending'; @endphp
                                <span
                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium
                                        {{ $syncStatus === 'synced' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : '' }}
                                        {{ $syncStatus === 'failed' ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' : '' }}
                                        {{ $syncStatus === 'pending' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' : '' }}">
                                    {{ ucfirst($syncStatus) }}
                                </span>
                            </td>
                            <td class="py-3 text-right">
                                <a href="{{ route('admin.applications.user-report', [$application, $user]) }}"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-purple-700 bg-purple-50 hover:bg-purple-100 dark:text-purple-300 dark:bg-purple-900/20 dark:hover:bg-purple-900/30 rounded-lg transition">
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