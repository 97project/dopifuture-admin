{{-- WayStartup Connector Rapor Sekmesi --}}

<div class="space-y-6">

    {{-- API Health + Genel İstatistikler --}}
    <div
        class="bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/10 dark:to-purple-900/10 rounded-xl border border-indigo-100 dark:border-indigo-800/30 p-5">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
                <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                </svg>
            </div>
            <div class="flex-1">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white">Way Startup — Girişimcilik Raporu</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">Startup simülasyonları — Üye ilerlemeleri ve adım
                    tamamlama verileri</p>
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
                    <p class="text-lg font-bold text-indigo-600">{{ $simCount }}</p>
                    <p class="text-[10px] text-gray-500">Simülasyon</p>
                </div>
                <div class="bg-white/60 dark:bg-gray-800/40 rounded-lg p-3 text-center">
                    <p class="text-lg font-bold text-purple-600">
                        {{ count($reportData['sample_members'] ?? []) }}/{{ $reportData['user_count'] ?? 0 }}
                    </p>
                    <p class="text-[10px] text-gray-500">Aktif Üye</p>
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
            <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4">Startup Simülasyonları</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach(collect($simData)->take(9) as $sim)
                    <div
                        class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4 border border-gray-100 dark:border-gray-700/50 hover:shadow-md transition">
                        <div class="flex items-center gap-3 mb-3">
                            <div
                                class="w-10 h-10 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-sm font-bold">
                                {{ mb_strtoupper(mb_substr($sim['name'] ?? 'S', 0, 1)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">
                                    {{ $sim['name'] ?? 'İsimsiz' }}</p>
                                @if(!empty($sim['description']))
                                    <p class="text-[10px] text-gray-400 truncate">
                                        {{ is_array($sim['description']) ? json_encode($sim['description']) : \Illuminate\Support\Str::limit($sim['description'], 60) }}
                                    </p>
                                @endif
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            @if(isset($sim['totalSteps']))
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Toplam Adım</span>
                                    <span
                                        class="font-medium text-gray-900 dark:text-white">{{ $sim['totalSteps'] }}</span>
                                </div>
                            @endif
                            @if(isset($sim['status']))
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Durum</span>
                                    <span
                                        class="font-medium {{ ($sim['status'] ?? '') === 'active' ? 'text-emerald-600' : 'text-gray-400' }}">
                                        {{ ucfirst($sim['status'] ?? '-') }}
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Örnek Üye Verileri --}}
    @if(!empty($reportData['sample_members'] ?? []))
        <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5">
            <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4">Üye Örnekleri</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($reportData['sample_members'] as $member)
                    <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4">
                        <div class="flex items-center gap-2 mb-3">
                            <div
                                class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-xs font-bold">
                                {{ mb_strtoupper(mb_substr($member['user_name'] ?? 'U', 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $member['user_name'] }}</p>
                                <p class="text-[10px] text-gray-400">ID: {{ $member['user_id'] }}</p>
                            </div>
                        </div>

                        @if(is_array($member['data']))
                            <div class="space-y-1.5">
                                @foreach(collect($member['data'])->only(['name', 'email', 'points', 'id'])->take(4) as $key => $value)
                                    @if(!is_array($value) && !is_object($value))
                                        <div class="flex justify-between text-xs">
                                            <span class="text-gray-500">{{ ucfirst(str_replace('_', ' ', $key)) }}</span>
                                            <span
                                                class="font-medium text-gray-900 dark:text-white">{{ $value }}</span>
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
            <p class="text-xs text-gray-400">Her kullanıcı için detaylı WayStartup raporu</p>
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
                                        class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-xs font-bold">
                                        {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
                                    </div>
                                    <span
                                        class="font-medium text-gray-900 dark:text-white">{{ $user->full_name }}</span>
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
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-indigo-700 bg-indigo-50 hover:bg-indigo-100 dark:text-indigo-300 dark:bg-indigo-900/20 dark:hover:bg-indigo-900/30 rounded-lg transition">
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

</div>
