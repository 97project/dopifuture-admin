@extends('admin.layouts.app')

@section('title', $application->name)

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
        <span class="text-gray-900 dark:text-gray-100 font-semibold">{{ $application->name }}</span>
    </div>
@endsection

@section('content')
    <div class="space-y-6">

        {{-- ═══ HEADER ═══ --}}
        <div class="flex items-start justify-between">
            <div class="flex items-center gap-4">
                @if($application->icon)
                    <div class="w-14 h-14 rounded-xl flex items-center justify-center shadow-lg"
                        style="background: {{ $application->color ?? '#0B6AB2' }}20; color: {{ $application->color ?? '#0B6AB2' }}">
                        @include('admin.partials._app_icon', ['icon' => $application->icon, 'class' => 'w-7 h-7'])
                    </div>
                @endif
                <div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ $application->name }}</h1>
                    <div class="flex items-center gap-2 mt-0.5">
                        <span
                            class="text-xs font-mono text-gray-400 bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded">{{ $application->slug }}</span>
                        @if($connectorReady)
                            <span
                                class="text-[10px] px-1.5 py-0.5 bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 rounded font-medium">Connector
                                {{ __('admin.active') }}</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2">
                @can('update', $application)
                    <form action="{{ route('admin.applications.sync-all', $application) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit"
                            class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition"
                            onclick="return confirm('Tüm kullanıcılar senkronlanacak. Devam?')">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Toplu Senkron
                        </button>
                    </form>
                    <a href="{{ route('admin.applications.edit', $application) }}"
                        class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-white bg-[#0B6AB2] rounded-lg hover:bg-[#13398E] transition">{{ __('admin.edit') }}</a>
                @endcan
            </div>
        </div>

        {{-- ═══ TAB NAVİGASYON ═══ --}}
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="flex gap-6" aria-label="Tabs">
                <a href="{{ route('admin.applications.show', [$application, 'tab' => 'overview']) }}"
                    class="py-3 px-1 text-sm font-medium border-b-2 transition {{ $activeTab === 'overview' ? 'border-[#0B6AB2] text-[#0B6AB2] dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400' }}">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                        Genel Bakış
                    </span>
                </a>
                <a href="{{ route('admin.applications.show', [$application, 'tab' => 'users']) }}"
                    class="py-3 px-1 text-sm font-medium border-b-2 transition {{ $activeTab === 'users' ? 'border-[#0B6AB2] text-[#0B6AB2] dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400' }}">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        Kullanıcılar
                        <span
                            class="text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 px-1.5 py-0.5 rounded-full">{{ $syncStats['total'] }}</span>
                    </span>
                </a>
                <a href="{{ route('admin.applications.show', [$application, 'tab' => 'report']) }}"
                    class="py-3 px-1 text-sm font-medium border-b-2 transition {{ $activeTab === 'report' ? 'border-[#0B6AB2] text-[#0B6AB2] dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400' }}">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        Raporlar
                    </span>
                </a>
            </nav>
        </div>

        {{-- ═══ TAB 1: GENEL BAKIŞ ═══ --}}
        @if($activeTab === 'overview')

            {{-- Sync İstatistik Kartları --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div
                    class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-4 text-center">
                    <p class="text-2xl font-extrabold text-gray-900 dark:text-white">{{ $syncStats['total'] }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">Toplam Kullanıcı</p>
                </div>
                <div
                    class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-4 text-center">
                    <p class="text-2xl font-extrabold text-emerald-500">{{ $syncStats['synced'] }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">Senkronize ✓</p>
                </div>
                <div
                    class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-4 text-center">
                    <p class="text-2xl font-extrabold text-red-500">{{ $syncStats['failed'] }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">Başarısız ✗</p>
                </div>
                <div
                    class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-4 text-center">
                    <p class="text-2xl font-extrabold text-amber-500">{{ $syncStats['pending'] }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">Bekliyor ⏳</p>
                </div>
            </div>

            {{-- API Health (Vega) --}}
            @if(isset($apiHealth))
                <div
                    class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-4 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        @if($apiHealth['ok'] ?? false)
                            <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 flex items-center justify-center">
                                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('admin.api_connection_active') }}</p>
                                <p class="text-[10px] text-gray-400 mt-0.5">{{ $apiHealth['service'] ?? '' }} — Son kontrol:
                                    {{ now()->format('H:i') }}
                                </p>
                            </div>
                        @else
                            <div class="w-10 h-10 rounded-xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.858 15.355-5.858 21.213 0" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-500 dark:text-gray-400">API Bağlantısı Kurulamadı</p>
                                <p class="text-[10px] text-gray-400 mt-0.5">Harici sunucuya şu an ulaşılamıyor — ağ bağlantısını kontrol
                                    edin</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Connector Uyarı --}}
            @if(!$connectorReady)
                <div
                    class="bg-amber-50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-800 rounded-xl p-4 flex items-start gap-3">
                    <svg class="w-5 h-5 text-amber-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                    <div>
                        <p class="text-sm font-semibold text-amber-800 dark:text-amber-300">{{ __('admin.connector_not_ready') }}</p>
                        <p class="text-xs text-amber-600 dark:text-amber-400 mt-1">{{ __('admin.integration_not_complete') }}
                            endpoint'leri henüz aktif değil.</p>
                    </div>
                </div>
            @endif

            {{-- Açıklama & Connector --}}
            @if($application->description || $application->connector_class)
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    @if($application->description)
                        <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5">
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-2">Açıklama</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                                {{ $application->getTranslation('description') }}
                            </p>
                        </div>
                    @endif
                    @if($application->connector_class)
                        <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5">
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-2">Connector</h3>
                            <div class="flex items-center gap-2">
                                <span
                                    class="text-xs font-mono bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 px-2 py-1 rounded">{{ class_basename($application->connector_class) }}</span>
                                <span
                                    class="text-xs px-2 py-0.5 rounded {{ $connectorType === 'vega' ? 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400' : ($connectorType === 'missionway' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' : 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400') }}">{{ ucfirst($connectorType) }}</span>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

        @endif

        {{-- ═══ TAB 2: KULLANICILAR ═══ --}}
        @if($activeTab === 'users')

            {{-- Kullanıcı Atama Formu --}}
            @can('update', $application)
                <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-3">Kullanıcı Ata</h3>
                    <form action="{{ route('admin.applications.assign-user', $application) }}" method="POST"
                        class="flex items-end gap-3">
                        @csrf
                        <div class="flex-1">
                            <select name="user_id" required
                                class="w-full rounded-lg border-gray-200 dark:border-gray-700 dark:bg-gray-800 text-sm focus:ring-[#0B6AB2] focus:border-[#0B6AB2]">
                                <option value="">Kullanıcı seçin...</option>
                                @foreach($availableUsers as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }} {{ $u->surname }} ({{ $u->email }})</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-[#0B6AB2] rounded-lg hover:bg-[#13398E] transition">Ata</button>
                    </form>
                </div>
            @endcan

            {{-- Kullanıcı Arama --}}
            <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">Atanmış Kullanıcılar</h3>
                    <form action="{{ route('admin.applications.show', [$application, 'tab' => 'users']) }}" method="GET"
                        class="flex gap-2">
                        <input type="hidden" name="tab" value="users">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Ara..."
                            class="text-sm rounded-lg border-gray-200 dark:border-gray-700 dark:bg-gray-800 px-3 py-1.5 w-48 focus:ring-[#0B6AB2] focus:border-[#0B6AB2]">
                        <button type="submit"
                            class="px-3 py-1.5 text-xs font-medium text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 transition">Ara</button>
                    </form>
                </div>

                {{-- Kullanıcı Tablosu --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left border-b border-gray-100 dark:border-gray-700">
                                <th class="pb-3 font-semibold text-gray-500 dark:text-gray-400">Kullanıcı</th>
                                <th class="pb-3 font-semibold text-gray-500 dark:text-gray-400">E-posta</th>
                                <th class="pb-3 font-semibold text-gray-500 dark:text-gray-400 text-center">Sync</th>
                                <th class="pb-3 font-semibold text-gray-500 dark:text-gray-400 text-center">Tarih</th>
                                <th class="pb-3 font-semibold text-gray-500 dark:text-gray-400 text-right">İşlem</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                            @forelse($users as $user)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition">
                                    <td class="py-3">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-8 h-8 rounded-full bg-gradient-to-br from-[#0B6AB2] to-[#13398E] flex items-center justify-center text-white text-xs font-bold">
                                                {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
                                            </div>
                                            <span class="font-medium text-gray-900 dark:text-white">{{ $user->full_name }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3 text-gray-500 dark:text-gray-400">{{ $user->email }}</td>
                                    <td class="py-3 text-center">
                                        @php $syncStatus = $user->pivot->sync_status ?? 'pending'; @endphp
                                        <span
                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium
                                                                                {{ $syncStatus === 'synced' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : '' }}
                                                                                {{ $syncStatus === 'failed' ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' : '' }}
                                                                                {{ $syncStatus === 'pending' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' : '' }}">
                                            {{ $syncStatus === 'synced' ? '✓' : ($syncStatus === 'failed' ? '✗' : '⏳') }}
                                            {{ ucfirst($syncStatus) }}
                                        </span>
                                    </td>
                                    <td class="py-3 text-center text-xs text-gray-400">
                                        {{ $user->pivot->synced_at ? \Carbon\Carbon::parse($user->pivot->synced_at)->format('d.m.Y H:i') : '-' }}
                                    </td>
                                    <td class="py-3">
                                        <div class="flex items-center justify-end gap-1">
                                            {{-- Rapor Gör --}}
                                            <a href="{{ route('admin.applications.user-report', [$application, $user]) }}"
                                                class="p-1.5 text-gray-400 hover:text-[#0B6AB2] transition rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/20"
                                                title="Kullanıcı Raporu">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                                </svg>
                                            </a>
                                            {{-- Tekrar Senkronla --}}
                                            @can('update', $application)
                                                <form action="{{ route('admin.applications.sync-user', [$application, $user]) }}"
                                                    method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit"
                                                        class="p-1.5 text-gray-400 hover:text-emerald-600 transition rounded-lg hover:bg-emerald-50 dark:hover:bg-emerald-900/20"
                                                        title="Tekrar Senkronla">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endcan
                                            {{-- Kaldır --}}
                                            @can('update', $application)
                                                <form action="{{ route('admin.applications.remove-user', [$application, $user]) }}"
                                                    method="POST" class="inline"
                                                    onsubmit="return confirm('{{ __('admin.confirm_remove_user_app') }}')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="p-1.5 text-gray-400 hover:text-red-600 transition rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20"
                                                        title="Kaldır">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-gray-400">
                                        <svg class="w-8 h-8 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                        </svg>
                                        {{ __('admin.no_assigned_users') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($users->hasPages())
                    <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                        {{ $users->appends(['tab' => 'users', 'search' => request('search')])->links() }}
                    </div>
                @endif
            </div>

        @endif

        {{-- ═══ TAB 3: RAPORLAR ═══ --}}
        @if($activeTab === 'report')

            @if(!$connectorReady)
                <div
                    class="bg-amber-50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-800 rounded-xl p-6 text-center">
                    <svg class="w-10 h-10 mx-auto mb-3 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                    <h3 class="text-sm font-bold text-amber-800 dark:text-amber-300">{{ __('admin.connector_not_active') }}</h3>
                    <p class="text-xs text-amber-600 dark:text-amber-400 mt-1">Bu uygulama için raporlama yapılabilmesi için
                        {{ __('admin.connector_must_active') }}</p>
                </div>
            @else
                @includeWhen($connectorType === 'vega', 'admin.applications.partials._tab_report_vega')
                @includeWhen($connectorType === 'missionway', 'admin.applications.partials._tab_report_missionway')
                @includeWhen($connectorType === 'waystartup', 'admin.applications.partials._tab_report_waystartup')
                @includeWhen($connectorType === 'generic', 'admin.applications.partials._tab_report_generic')
            @endif

        @endif

    </div>
@endsection