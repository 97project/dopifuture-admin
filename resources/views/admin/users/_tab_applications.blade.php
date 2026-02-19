{{-- Applications Tab --}}
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
                    Uygulamalar
                </h3>
            </div>
            <div class="divide-y divide-gray-50 dark:divide-[#1A3A5C]/50">
                @foreach($userApps as $app)
                    <div
                        class="px-6 py-4 flex items-center justify-between hover:bg-gray-50/50 dark:hover:bg-[#0A1628]/30 transition">
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
                            @if($app->is_active)
                                <span
                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Aktif
                                </span>
                            @else
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-50 dark:bg-red-900/20 text-red-500">Pasif</span>
                            @endif
                            <div class="text-right">
                                @if($app->pivot->granted_at)
                                    <p class="text-[10px] text-gray-400">
                                        {{ \Carbon\Carbon::parse($app->pivot->granted_at)->format('d.m.Y') }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
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