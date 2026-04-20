{{-- API Keys Tab with Full CRUD --}}
<div class="space-y-6">
    {{-- Create New API Key --}}
    @can('users.edit')
        <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C]">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-[#1A3A5C]">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Yeni API Anahtarı Oluştur
                </h3>
            </div>
            <form action="{{ route('admin.users.api-keys.store', $user) }}" method="POST" class="p-6">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="api_key_name"
                            class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5">{{ __('admin.name') }}
                            *</label>
                        <input type="text" id="api_key_name" name="name" required placeholder="Mobil Uygulama, Test..."
                            class="w-full px-3 py-2.5 rounded-lg text-sm">
                    </div>
                    <div>
                        <label for="api_key_abilities"
                            class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5">Yetkiler</label>
                        <input type="text" id="api_key_abilities" name="abilities" placeholder="* (tümü) veya read,write"
                            class="w-full px-3 py-2.5 rounded-lg text-sm">
                        <p class="text-[10px] text-gray-400 mt-1">Virgülle ayırın. Boş bırakılırsa tüm yetkiler verilir.</p>
                    </div>
                    <div>
                        <label for="api_key_expires"
                            class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5">Son Kullanma
                            Tarihi</label>
                        <input type="datetime-local" id="api_key_expires" name="expires_at"
                            class="w-full px-3 py-2.5 rounded-lg text-sm">
                        <p class="text-[10px] text-gray-400 mt-1">Boş bırakılırsa süresiz olur.</p>
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-bold rounded-lg transition shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Anahtar Oluştur
                    </button>
                </div>
            </form>
        </div>
    @endcan

    {{-- Flash message for newly created key --}}
    @if(session('success') && str_contains(session('success'), 'Key:'))
        <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl p-4">
            <div class="flex items-start gap-3">
                <span class="text-amber-500 text-xl">⚠️</span>
                <div>
                    <h4 class="text-sm font-bold text-amber-800 dark:text-amber-300">API Anahtarınız Oluşturuldu!</h4>
                    <p class="text-xs text-amber-700 dark:text-amber-400 mt-1">Bu anahtar sadece bir kez gösterilir. Lütfen
                        güvenli bir yere kaydedin.</p>
                    <code
                        class="block mt-2 p-3 bg-white dark:bg-[#0A1628] rounded-lg text-sm font-mono text-gray-900 dark:text-white break-all select-all border border-amber-200 dark:border-amber-800">{{ str_replace(__('admin.api_key_created') . ' Key: ', '', session('success')) }}</code>
                </div>
            </div>
        </div>
    @endif

    {{-- API Keys List --}}
    <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C]">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-[#1A3A5C] flex items-center justify-between">
            <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <svg class="w-4 h-4 text-[#0B6AB2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                </svg>
                {{ __('admin.api_keys') }}
            </h3>
            <span
                class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-gray-100 dark:bg-[#0A1628] text-gray-500">{{ isset($apiKeys) ? $apiKeys->total() : $stats['apiKeys'] }}</span>
        </div>
        @if(isset($apiKeys) && $apiKeys->count())
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-[#1A3A5C]">
                            <th class="text-left px-6 py-3 text-[10px] font-bold uppercase tracking-wider text-gray-400">
                                {{ __('admin.name') }}</th>
                            <th class="text-left px-6 py-3 text-[10px] font-bold uppercase tracking-wider text-gray-400">
                                Yetkiler</th>
                            <th class="text-center px-6 py-3 text-[10px] font-bold uppercase tracking-wider text-gray-400">
                                {{ __('admin.status') }}</th>
                            <th class="text-left px-6 py-3 text-[10px] font-bold uppercase tracking-wider text-gray-400">
                                Bitiş</th>
                            <th class="text-left px-6 py-3 text-[10px] font-bold uppercase tracking-wider text-gray-400">
                                {{ __('admin.last_login') }}</th>
                            <th class="text-right px-6 py-3 text-[10px] font-bold uppercase tracking-wider text-gray-400">
                                İşlemler</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-[#1A3A5C]/50">
                        @foreach($apiKeys as $key)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-[#0A1628]/30 transition">
                                <td class="px-6 py-3">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-8 h-8 rounded-lg bg-purple-50 dark:bg-purple-900/20 flex items-center justify-center">
                                            <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                                            </svg>
                                        </div>
                                        <div>
                                            <span
                                                class="font-semibold text-gray-900 dark:text-white text-xs">{{ $key->name }}</span>
                                            <p class="text-[10px] text-gray-400 font-mono mt-0.5">{{ $key->key_prefix }}...</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-3">
                                    <div class="flex flex-wrap gap-1">
                                        @foreach(($key->abilities ?? ['*']) as $ability)
                                            <span
                                                class="px-1.5 py-0.5 bg-gray-100 dark:bg-[#0A1628] text-gray-500 rounded text-[10px] font-mono">{{ $ability }}</span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-6 py-3 text-center">
                                    @if($key->is_active && !$key->isExpired())
                                        <span
                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>{{ __('admin.active') }}
                                        </span>
                                    @elseif($key->isExpired())
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 dark:bg-amber-900/20 text-amber-600">Süresi
                                            Doldu</span>
                                    @else
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-50 dark:bg-red-900/20 text-red-500">Devre
                                            Dışı</span>
                                    @endif
                                </td>
                                <td class="px-6 py-3 text-gray-500 text-xs">{{ $key->expires_at?->format('d.m.Y H:i') ?? '∞' }}
                                </td>
                                <td class="px-6 py-3 text-gray-500 text-xs">
                                    {{ $key->last_used_at?->format('d.m.Y H:i') ?? '—' }}</td>
                                <td class="px-6 py-3">
                                    <div class="flex items-center justify-end gap-1">
                                        @can('users.edit')
                                            {{-- Toggle Active/Inactive --}}
                                            <form action="{{ route('admin.users.api-keys.toggle', [$user, $key]) }}" method="POST"
                                                class="inline">
                                                @csrf @method('PUT')
                                                <button type="submit"
                                                    class="p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-[#0A1628] transition"
                                                    title="{{ $key->is_active ? __('admin.deactivate') : __('admin.activate') }}">
                                                    @if($key->is_active)
                                                        <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                                        </svg>
                                                    @else
                                                        <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                    @endif
                                                </button>
                                            </form>
                                            {{-- Delete --}}
                                            <form action="{{ route('admin.users.api-keys.destroy', [$user, $key]) }}" method="POST"
                                                class="inline"
                                                onsubmit="return confirm('{{ __('admin.confirm_delete_api_key') }}')">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                    class="p-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition"
                                                    title="Sil">
                                                    <svg class="w-4 h-4 text-red-400 hover:text-red-600" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-3 border-t border-gray-100 dark:border-[#1A3A5C]">
                {{ $apiKeys->appends(['tab' => 'api_keys'])->links() }}</div>
        @else
            <div class="p-16 text-center">
                <div
                    class="w-16 h-16 rounded-2xl bg-gray-100 dark:bg-[#0A1628] flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                    </svg>
                </div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('admin.no_data') }}</p>
                <p class="text-xs text-gray-400 mt-1">Bu kullanıcının henüz API anahtarı yok. Yukarıdan oluşturabilirsiniz.
                </p>
            </div>
        @endif
    </div>
</div>