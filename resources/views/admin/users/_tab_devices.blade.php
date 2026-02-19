{{-- Devices Tab --}}
<div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C]">
    <div class="px-6 py-4 border-b border-gray-100 dark:border-[#1A3A5C] flex items-center justify-between">
        <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
            <svg class="w-4 h-4 text-[#0B6AB2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
            </svg>
            {{ __('admin.devices') }}
        </h3>
        <span
            class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-gray-100 dark:bg-[#0A1628] text-gray-500">{{ isset($devices) ? $devices->total() : $stats['devices'] }}</span>
    </div>
    @if(isset($devices) && $devices->count())
        <div class="divide-y divide-gray-50 dark:divide-[#1A3A5C]/50">
            @foreach($devices as $device)
                <div
                    class="px-6 py-4 flex items-center justify-between hover:bg-gray-50/50 dark:hover:bg-[#0A1628]/30 transition">
                    <div class="flex items-center gap-4">
                        <div
                            class="w-10 h-10 rounded-xl {{ strtolower($device->platform) === 'ios' ? 'bg-gray-100 dark:bg-gray-800' : 'bg-green-50 dark:bg-green-900/20' }} flex items-center justify-center">
                            @if(strtolower($device->platform) === 'ios')
                                <svg class="w-5 h-5 text-gray-600" viewBox="0 0 24 24" fill="currentColor">
                                    <path
                                        d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.8-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z" />
                                </svg>
                            @else
                                <svg class="w-5 h-5 text-green-600" viewBox="0 0 24 24" fill="currentColor">
                                    <path
                                        d="M17.523 15.341a8.584 8.584 0 01-.282.534c-.179.304-.42.67-.721.907-.43.34-.893.343-1.164.343-.27 0-.698-.093-1.166-.266a3.638 3.638 0 00-1.228-.268 3.593 3.593 0 00-1.236.268c-.477.176-.886.271-1.228.283-.257.012-.734-.012-1.164-.352a6.45 6.45 0 01-.744-.925c-.8-1.14-1.48-2.955-1.48-4.65 0-2.73 1.778-4.18 3.523-4.18.655 0 1.2.215 1.636.38.332.126.621.235.9.235.257 0 .558-.114.913-.25.477-.181 1.07-.406 1.755-.347.495.043 1.886.2 2.78 1.5l-.068.044c-.706.448-1.884 1.297-1.866 3.24.02 2.336 1.543 3.145 1.84 3.288zM14.2 2.5c.48-.72 1.33-1.28 2.16-1.32.1.89-.28 1.78-.81 2.46-.52.69-1.36 1.22-2.19 1.15-.11-.87.33-1.77.84-2.29z" />
                                </svg>
                            @endif
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $device->device_name }}</p>
                            <div class="flex items-center gap-2 mt-0.5">
                                <span
                                    class="text-[10px] font-bold uppercase px-1.5 py-0.5 rounded {{ strtolower($device->platform) === 'ios' ? 'bg-gray-100 dark:bg-gray-800 text-gray-600' : 'bg-green-50 dark:bg-green-900/20 text-green-600' }}">{{ $device->platform }}</span>
                                <span
                                    class="text-[10px] text-gray-400 font-mono">{{ substr($device->fcm_token, 0, 24) }}...</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        @if($device->is_active)
                            <span
                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>{{ __('admin.active') }}
                            </span>
                        @else
                            <span
                                class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-50 dark:bg-red-900/20 text-red-500">{{ __('admin.inactive') }}</span>
                        @endif
                        <span class="text-xs text-gray-400">{{ $device->created_at->format('d.m.Y') }}</span>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="px-6 py-3 border-t border-gray-100 dark:border-[#1A3A5C]">
            {{ $devices->appends(['tab' => 'devices'])->links() }}</div>
    @else
        <div class="p-16 text-center">
            <div class="w-16 h-16 rounded-2xl bg-gray-100 dark:bg-[#0A1628] flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
            </div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('admin.no_data') }}</p>
            <p class="text-xs text-gray-400 mt-1">Bu kullanıcının kayıtlı cihazı yok</p>
        </div>
    @endif
</div>