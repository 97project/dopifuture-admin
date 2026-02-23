@extends('admin.layouts.app')

@section('title', $application->name)

@section('breadcrumb')
    <div class="flex items-center gap-2 text-sm">
        <a href="{{ route('admin.dashboard') }}"
            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">{{ __('admin.dashboard') }}</a>
        <svg class="w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <a href="{{ route('admin.applications.index') }}"
            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">{{ __('admin.applications') }}</a>
        <svg class="w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                    <div class="w-14 h-14 rounded-xl flex items-center justify-center text-2xl shadow-lg"
                        style="background: {{ $application->color ?? '#0B6AB2' }}20">
                        {{ $application->icon }}
                    </div>
                @endif
                <div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ is_array($application->name) ? ($application->name[app()->getLocale()] ?? $application->name['tr'] ?? reset($application->name)) : $application->name }}</h1>
                    <div class="flex items-center gap-3 mt-1 text-xs text-gray-400">
                        <span class="font-mono">{{ $application->slug }}</span>
                        @if($application->is_active)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>{{ __('admin.active') }}
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-50 dark:bg-gray-900/20 text-gray-500">{{ __('admin.inactive') }}</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2">
                {{-- Toplu Senkron --}}
                @can('update', $application)
                    <form action="{{ route('admin.applications.sync-all', $application) }}" method="POST" class="inline"
                        onsubmit="return confirm('Tüm kullanıcıları senkronla?')">
                        @csrf
                        <button type="submit"
                            class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-[#0B6AB2] bg-blue-50 dark:bg-blue-900/20 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/40 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Toplu Senkron
                        </button>
                    </form>
                    <a href="{{ route('admin.applications.edit', $application) }}"
                        class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-white bg-[#0B6AB2] rounded-lg hover:bg-[#13398E] transition">{{ __('admin.edit') }}</a>
                @endcan
            </div>
        </div>

        {{-- ═══ SYNC İSTATİSTİK KARTLARI ═══ --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-4 text-center">
                <p class="text-2xl font-extrabold text-[#0B6AB2]">{{ $syncStats['total'] }}</p>
                <p class="text-xs text-gray-400 mt-0.5">Toplam Kullanıcı</p>
            </div>
            <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-4 text-center">
                <p class="text-2xl font-extrabold text-emerald-500">{{ $syncStats['synced'] }}</p>
                <p class="text-xs text-gray-400 mt-0.5">Senkronlandı ✅</p>
            </div>
            <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-4 text-center">
                <p class="text-2xl font-extrabold text-red-500">{{ $syncStats['failed'] }}</p>
                <p class="text-xs text-gray-400 mt-0.5">Başarısız ❌</p>
            </div>
            <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-4 text-center">
                <p class="text-2xl font-extrabold text-amber-500">{{ $syncStats['pending'] }}</p>
                <p class="text-xs text-gray-400 mt-0.5">Bekliyor ⏳</p>
            </div>
        </div>

        {{-- ═══ CONNECTOR HAZIR DEĞİLSE UYARI ═══ --}}
        @if(!$connectorReady)
            <div class="flex items-start gap-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800/50 rounded-xl p-4">
                <svg class="w-5 h-5 text-amber-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
                <div>
                    <p class="text-sm font-semibold text-amber-800 dark:text-amber-300">API Entegrasyonu Henüz Tamamlanmadı</p>
                    <p class="text-xs text-amber-600 dark:text-amber-400 mt-0.5">Bu uygulamanın API bağlantısı henüz yapılandırılmadı. Kullanıcı atamaları yapılabilir ancak senkronizasyon işlemleri entegrasyon tamamlanana kadar beklemede kalacaktır.</p>
                </div>
            </div>
        @endif

        {{-- ═══ AÇIKLAMA & CONNECTOR ═══ --}}
        @if($application->description || $application->connector_class)
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                @if($application->description)
                    <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-2">{{ __('admin.description') }}</h3>
                        <p class="text-sm text-gray-500 leading-relaxed">{{ is_array($application->description) ? ($application->description[app()->getLocale()] ?? $application->description['tr'] ?? reset($application->description)) : $application->description }}</p>
                    </div>
                @endif
                @if($application->connector_class)
                    <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-2">Connector</h3>
                        <code class="text-xs font-mono text-[#0B6AB2] bg-blue-50 dark:bg-blue-900/20 px-2 py-1 rounded">{{ $application->connector_class }}</code>
                    </div>
                @endif
            </div>
        @endif

        {{-- ═══ KULLANICI ATAMA FORMU ═══ --}}
        @can('update', $application)
            <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-3">Kullanıcı Ata</h3>
                <form action="{{ route('admin.applications.assign-user', $application) }}" method="POST" class="flex items-end gap-3">
                    @csrf
                    <div class="flex-1">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Kullanıcı Seç</label>
                        <select name="user_id" required
                            class="w-full rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628] text-sm text-gray-900 dark:text-white px-3 py-2 focus:ring-2 focus:ring-[#0B6AB2]/30 focus:border-[#0B6AB2]">
                            <option value="">-- Kullanıcı seçin --</option>
                            @foreach($availableUsers as $u)
                                <option value="{{ $u->id }}">{{ $u->name }} {{ $u->surname }} ({{ $u->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit"
                        class="inline-flex items-center gap-1.5 px-5 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Ata & Senkronla
                    </button>
                </form>
            </div>
        @endcan

        {{-- ═══ KULLANICI TABLOSU ═══ --}}
        <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] overflow-hidden">
            {{-- Header --}}
            <div class="px-5 py-3 border-b border-gray-100 dark:border-[#1A3A5C] flex items-center justify-between">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white">
                    {{ __('admin.users') }} ({{ $users->total() }})
                </h3>
                {{-- Arama --}}
                <form action="{{ route('admin.applications.show', $application) }}" method="GET" class="flex items-center gap-2">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Ara..."
                        class="w-48 rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628] text-sm text-gray-900 dark:text-white px-3 py-1.5 focus:ring-2 focus:ring-[#0B6AB2]/30 focus:border-[#0B6AB2]">
                    <button type="submit"
                        class="px-3 py-1.5 text-xs font-medium text-[#0B6AB2] bg-blue-50 dark:bg-blue-900/20 rounded-lg hover:bg-blue-100 transition">Ara</button>
                </form>
            </div>

            {{-- Tablo --}}
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-[#1A3A5C]">
                        <th class="text-left px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">{{ __('admin.name') }}</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">{{ __('admin.email') }}</th>
                        <th class="text-center px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">Sync Durumu</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">Son Senkron</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">{{ __('admin.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-[#1A3A5C]/50">
                    @forelse($users as $user)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-[#0A1628]/30 transition group">
                            <td class="px-5 py-3">
                                <a href="{{ route('admin.users.show', $user) }}" class="font-medium text-gray-900 dark:text-white hover:text-[#0B6AB2] transition">
                                    {{ $user->name }} {{ $user->surname }}
                                </a>
                            </td>
                            <td class="px-5 py-3 text-gray-500">{{ $user->email }}</td>
                            <td class="px-5 py-3 text-center">
                                @php $status = $user->pivot->sync_status ?? 'pending'; @endphp
                                @if($status === 'synced')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Senkron
                                    </span>
                                @elseif($status === 'failed')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-50 dark:bg-red-900/20 text-red-500"
                                        title="{{ $user->pivot->sync_error }}">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>Başarısız
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 dark:bg-amber-900/20 text-amber-600">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>Bekliyor
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-xs text-gray-400">
                                @if($user->pivot->synced_at)
                                    {{ \Carbon\Carbon::parse($user->pivot->synced_at)->format('d.m.Y H:i') }}
                                @else
                                    <span class="text-gray-300">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-right">
                                <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition">
                                    {{-- Tekrar Senkronla --}}
                                    <form action="{{ route('admin.applications.sync-user', [$application, $user]) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" title="Tekrar Senkronla"
                                            class="p-1.5 rounded-lg text-[#0B6AB2] hover:bg-blue-50 dark:hover:bg-blue-900/20 transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                            </svg>
                                        </button>
                                    </form>
                                    {{-- Çıkar --}}
                                    <form action="{{ route('admin.applications.remove-user', [$application, $user]) }}" method="POST" class="inline"
                                        onsubmit="return confirm('{{ $user->name }} kullanıcısını {{ $application->name }} uygulamasından çıkarmak istediğinize emin misiniz?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Çıkar"
                                            class="p-1.5 rounded-lg text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                    {{-- Profil --}}
                                    <a href="{{ route('admin.users.show', $user) }}" title="Profil"
                                        class="p-1.5 rounded-lg text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center text-gray-400">
                                <div class="flex flex-col items-center gap-2">
                                    <svg class="w-10 h-10 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <p class="text-sm font-medium">Henüz kullanıcı atanmamış</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Pagination --}}
            @if($users->hasPages())
                <div class="px-5 py-3 border-t border-gray-100 dark:border-[#1A3A5C]">
                    {{ $users->appends(request()->query())->links() }}
                </div>
            @endif
        </div>

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="fixed bottom-6 right-6 z-50 animate-fade-in bg-emerald-600 text-white px-5 py-3 rounded-xl shadow-2xl text-sm font-medium flex items-center gap-2"
                x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" x-transition>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ session('success') }}
            </div>
        @endif
        @if(session('error') || session('warning'))
            <div class="fixed bottom-6 right-6 z-50 animate-fade-in bg-red-600 text-white px-5 py-3 rounded-xl shadow-2xl text-sm font-medium flex items-center gap-2"
                x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 6000)" x-transition>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ session('error') ?? session('warning') }}
            </div>
        @endif

    </div>
@endsection