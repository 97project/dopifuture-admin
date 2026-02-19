{{-- Security Tab --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        {{-- 2FA Status --}}
        <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div
                        class="w-12 h-12 rounded-xl {{ $user->hasTwoFactorEnabled() ? 'bg-emerald-50 dark:bg-emerald-900/20' : 'bg-amber-50 dark:bg-amber-900/20' }} flex items-center justify-center">
                        <svg class="w-6 h-6 {{ $user->hasTwoFactorEnabled() ? 'text-emerald-500' : 'text-amber-500' }}"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="{{ $user->hasTwoFactorEnabled() ? 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z' : 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z' }}" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white">{{ __('admin.2fa') }}</h3>
                        <p
                            class="text-xs {{ $user->hasTwoFactorEnabled() ? 'text-emerald-600' : 'text-amber-600' }} font-medium mt-0.5">
                            {{ $user->hasTwoFactorEnabled() ? __('admin.active') . ' · ' . ($user->two_factor_confirmed_at?->format('d.m.Y') ?? '') : __('admin.inactive') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sanctum Tokens --}}
        <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C]">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-[#1A3A5C] flex items-center justify-between">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#0B6AB2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                    Sanctum Tokens
                </h3>
                <span
                    class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-gray-100 dark:bg-[#0A1628] text-gray-500">{{ isset($tokens) ? $tokens->total() : $stats['sessions'] }}</span>
            </div>
            @if(isset($tokens) && $tokens->count())
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 dark:border-[#1A3A5C]">
                                <th
                                    class="text-left px-6 py-3 text-[10px] font-bold uppercase tracking-wider text-gray-400">
                                    {{ __('admin.name') }}</th>
                                <th
                                    class="text-left px-6 py-3 text-[10px] font-bold uppercase tracking-wider text-gray-400">
                                    {{ __('admin.last_login') }}</th>
                                <th
                                    class="text-left px-6 py-3 text-[10px] font-bold uppercase tracking-wider text-gray-400">
                                    {{ __('admin.date') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-[#1A3A5C]/50">
                            @foreach($tokens as $token)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-[#0A1628]/30 transition">
                                    <td class="px-6 py-3">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center">
                                                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                                </svg></div>
                                            <span
                                                class="font-medium text-gray-900 dark:text-white text-xs">{{ $token->name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-3 text-gray-500 text-xs">
                                        {{ $token->last_used_at?->format('d.m.Y H:i') ?? '—' }}</td>
                                    <td class="px-6 py-3 text-gray-500 text-xs">{{ $token->created_at->format('d.m.Y H:i') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-3 border-t border-gray-100 dark:border-[#1A3A5C]">
                    {{ $tokens->appends(['tab' => 'security'])->links() }}</div>
            @else
                <div class="p-12 text-center">
                    <svg class="w-10 h-10 text-gray-300 dark:text-gray-600 mx-auto mb-3" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                    <p class="text-sm text-gray-400">{{ __('admin.no_data') }}</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Sidebar --}}
    <div class="space-y-4">
        <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5">
            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-4">Güvenlik Bilgisi</h3>
            <div class="space-y-3">
                <div class="flex items-center justify-between py-2 border-b border-gray-50 dark:border-[#1A3A5C]/50">
                    <span class="text-xs text-gray-500">Başarısız giriş</span>
                    <span
                        class="text-xs font-bold text-gray-900 dark:text-white">{{ $user->failed_login_count ?? 0 }}</span>
                </div>
                <div class="flex items-center justify-between py-2 border-b border-gray-50 dark:border-[#1A3A5C]/50">
                    <span class="text-xs text-gray-500">Hesap durumu</span>
                    <span
                        class="text-xs font-bold {{ $user->isLocked() ? 'text-red-500' : 'text-emerald-500' }}">{{ $user->isLocked() ? 'Kilitli' : 'Açık' }}</span>
                </div>
                @if($user->locked_until)
                    <div class="flex items-center justify-between py-2">
                        <span class="text-xs text-gray-500">Kilit bitiş</span>
                        <span
                            class="text-xs font-bold text-gray-900 dark:text-white">{{ $user->locked_until->format('d.m.Y H:i') }}</span>
                    </div>
                @endif
                <div class="flex items-center justify-between py-2">
                    <span class="text-xs text-gray-500">Son giriş IP</span>
                    <span
                        class="text-xs font-mono text-gray-900 dark:text-white">{{ $user->last_login_ip ?? '—' }}</span>
                </div>
            </div>
        </div>
    </div>
</div>