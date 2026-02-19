{{-- Schools Tab (for teacher, school-admin, school-principal, student roles) --}}
<div class="space-y-6">
    @php $userSchools = $schools ?? collect(); @endphp

    @if($userSchools->count())
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach($userSchools as $school)
                <div
                    class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] overflow-hidden">
                    <div class="p-6">
                        <div class="flex items-start gap-4">
                            <div
                                class="w-12 h-12 rounded-xl bg-indigo-50 dark:bg-indigo-900/20 flex items-center justify-center flex-shrink-0">
                                <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="text-sm font-bold text-gray-900 dark:text-white">{{ $school->name }}</h3>
                                <div class="flex items-center gap-2 mt-1">
                                    <span
                                        class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600">{{ ucfirst($school->pivot->role ?? 'üye') }}</span>
                                    @if($school->is_active)
                                        <span
                                            class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600">Aktif</span>
                                    @else
                                        <span
                                            class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-50 dark:bg-red-900/20 text-red-500">Pasif</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- School Details --}}
                        <div class="mt-4 space-y-2">
                            @if($school->city || $school->country)
                                <div class="flex items-center gap-2 text-xs text-gray-500">
                                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    {{ $school->city }}{{ $school->country ? ', ' . $school->country : '' }}
                                </div>
                            @endif
                            @if($school->email)
                                <div class="flex items-center gap-2 text-xs text-gray-500">
                                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    {{ $school->email }}
                                </div>
                            @endif
                            @if($school->phone)
                                <div class="flex items-center gap-2 text-xs text-gray-500">
                                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                    </svg>
                                    {{ $school->phone }}
                                </div>
                            @endif
                        </div>

                        {{-- Classes in this school --}}
                        @if($school->classes && $school->classes->count())
                            <div class="mt-4 pt-4 border-t border-gray-100 dark:border-[#1A3A5C]">
                                <h4 class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Sınıflar
                                    ({{ $school->classes->count() }})</h4>
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($school->classes->take(8) as $class)
                                        <span
                                            class="px-2 py-1 bg-gray-100 dark:bg-[#0A1628] text-gray-600 dark:text-gray-400 rounded-lg text-[10px] font-medium">
                                            {{ $class->name }} · {{ $class->grade_level }}
                                        </span>
                                    @endforeach
                                    @if($school->classes->count() > 8)
                                        <span
                                            class="px-2 py-1 bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 rounded-lg text-[10px] font-bold">+{{ $school->classes->count() - 8 }}</span>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-16 text-center">
            <div class="w-16 h-16 rounded-2xl bg-gray-100 dark:bg-[#0A1628] flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5" />
                </svg>
            </div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Bu kullanıcı herhangi bir okula kayıtlı değil
            </p>
        </div>
    @endif
</div>