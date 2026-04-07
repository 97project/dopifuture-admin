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
                        <div class="space-y-2">
                            <div class="grid grid-cols-2 gap-2 text-xs">
                                @if(isset($sim['totalSteps']) || isset($sim['totalStep']))
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Toplam Adım</span>
                                        <span class="font-medium text-gray-900 dark:text-white">{{ $sim['totalSteps'] ?? $sim['totalStep'] ?? 0 }}</span>
                                    </div>
                                @endif
                                @if(isset($sim['difficulty']))
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Zorluk</span>
                                        <span class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-semibold
                                            {{ $sim['difficulty'] === 'easy' ? 'bg-emerald-100 text-emerald-700' : '' }}
                                            {{ $sim['difficulty'] === 'medium' ? 'bg-amber-100 text-amber-700' : '' }}
                                            {{ $sim['difficulty'] === 'hard' ? 'bg-red-100 text-red-700' : '' }}">
                                            {{ match($sim['difficulty']) { 'easy' => 'Kolay', 'medium' => 'Orta', 'hard' => 'Zor', default => ucfirst($sim['difficulty']) } }}
                                        </span>
                                    </div>
                                @endif
                                @if(isset($sim['status']))
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Durum</span>
                                        <span class="font-medium {{ ($sim['status'] ?? '') === 'active' ? 'text-emerald-600' : 'text-gray-400' }}">
                                            {{ ucfirst($sim['status'] ?? '-') }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                            {{-- Progress bar (simulations_with_progress'ten gelir) --}}
                            @php
                                $progressData = collect($reportData['simulations_with_progress']['data'] ?? [])->firstWhere('id', $sim['id'] ?? null);
                                $completion = $progressData['userProgress']['completionPercentage'] ?? null;
                            @endphp
                            @if($completion !== null)
                                <div class="mt-2">
                                    <div class="flex justify-between text-[10px] mb-1">
                                        <span class="text-gray-500">İlerleme</span>
                                        <span class="font-semibold text-indigo-600">{{ number_format($completion, 0) }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
                                        <div class="bg-gradient-to-r from-indigo-500 to-purple-600 h-1.5 rounded-full transition-all" style="width: {{ min($completion, 100) }}%"></div>
                                    </div>
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

    {{-- AI Değerlendirme Özeti --}}
    @if(!empty($reportData['ai_evaluation'] ?? []))
        @php $aiEval = $reportData['ai_evaluation']; @endphp
        <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                </div>
                <h3 class="text-sm font-bold text-gray-900 dark:text-white">AI Değerlendirme Özeti</h3>
            </div>
            <div class="grid grid-cols-3 gap-4 mb-4">
                <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-3 text-center">
                    <p class="text-2xl font-bold text-purple-600">{{ $aiEval['total_score'] ?? 0 }}</p>
                    <p class="text-[10px] text-gray-500">AI Toplam Puanı</p>
                </div>
                <div class="bg-amber-50 dark:bg-amber-900/20 rounded-lg p-3 text-center">
                    <p class="text-2xl font-bold text-amber-600">{{ $aiEval['total_coins'] ?? 0 }} 🪙</p>
                    <p class="text-[10px] text-gray-500">Kazanılan Coin</p>
                </div>
                <div class="bg-emerald-50 dark:bg-emerald-900/20 rounded-lg p-3 text-center">
                    <p class="text-2xl font-bold text-emerald-600">{{ $aiEval['evaluated_steps'] ?? 0 }}</p>
                    <p class="text-[10px] text-gray-500">Değerlendirilen Adım</p>
                </div>
            </div>
            @if(!empty($aiEval['overall_feedback']))
                <div class="bg-gray-50 dark:bg-gray-800/40 rounded-lg p-3 text-xs text-gray-600 dark:text-gray-400 leading-relaxed">
                    <strong class="text-purple-600">Genel Değerlendirme:</strong> {{ $aiEval['overall_feedback'] }}
                </div>
            @endif
        </div>
    @endif

    {{-- Adım Soru-Cevap Accordion --}}
    @if(!empty($reportData['step_qna'] ?? []))
        <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5">
            <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4">Adım Soru-Cevap Detayı</h3>
            <div class="space-y-3">
                @foreach($reportData['step_qna'] as $stepIdx => $stepQna)
                    <details class="group border border-gray-100 dark:border-gray-700 rounded-lg">
                        <summary class="flex items-center justify-between p-3 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800/30 transition rounded-lg">
                            <div class="flex items-center gap-3">
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 text-xs font-bold">
                                    {{ ($stepQna['step_number'] ?? $stepIdx + 1) }}
                                </span>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $stepQna['step_title'] ?? 'Adım ' . ($stepIdx + 1) }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                @if(isset($stepQna['ai_score']))
                                    <span class="text-xs font-semibold text-purple-600 bg-purple-50 dark:bg-purple-900/20 px-2 py-0.5 rounded-full">{{ $stepQna['ai_score'] }} puan</span>
                                @endif
                                @if(isset($stepQna['ai_coins']))
                                    <span class="text-xs font-semibold text-amber-600">🪙 {{ $stepQna['ai_coins'] }}</span>
                                @endif
                                <svg class="w-4 h-4 text-gray-400 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </summary>
                        <div class="px-3 pb-3 space-y-2 border-t border-gray-100 dark:border-gray-700 pt-3">
                            @foreach($stepQna['questions'] ?? [] as $qna)
                                <div class="bg-gray-50 dark:bg-gray-800/40 rounded-lg p-3">
                                    <p class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                        <span class="text-indigo-500">Q:</span> {{ $qna['question'] ?? '-' }}
                                    </p>
                                    <p class="text-xs text-gray-600 dark:text-gray-400 mb-2 italic">
                                        <span class="text-emerald-500 not-italic">A:</span> {{ $qna['answer'] ?? '-' }}
                                    </p>
                                    @if(!empty($qna['ai_feedback']))
                                        <div class="flex items-start gap-1.5 mt-1">
                                            <svg class="w-3 h-3 text-purple-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                                            <p class="text-[10px] text-purple-600 dark:text-purple-400">{{ $qna['ai_feedback'] }}</p>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </details>
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

    {{-- ═══ API CATALOG DATA ═══ --}}

    {{-- Step Question Answers --}}
    @if(!empty($reportData['stepQuestionAnswers']) && count($reportData['stepQuestionAnswers']) > 0)
    <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5">
        <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4">📝 Soru Cevapları ({{ count($reportData['stepQuestionAnswers']) }})</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left border-b border-gray-100 dark:border-gray-700">
                        <th class="pb-3 font-semibold text-gray-500">#</th>
                        <th class="pb-3 font-semibold text-gray-500">Soru</th>
                        <th class="pb-3 font-semibold text-gray-500">Cevap</th>
                        <th class="pb-3 font-semibold text-gray-500 text-right">Tarih</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                    @foreach(collect($reportData['stepQuestionAnswers'])->take(20) as $i => $answer)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30">
                        <td class="py-2 text-xs text-gray-400">{{ $i + 1 }}</td>
                        <td class="py-2 text-xs font-medium text-gray-900 dark:text-white max-w-xs truncate">{{ $answer['question'] ?? $answer['questionText'] ?? '-' }}</td>
                        <td class="py-2 text-xs text-gray-600 dark:text-gray-400 max-w-xs truncate">{{ $answer['answer'] ?? $answer['answerText'] ?? '-' }}</td>
                        <td class="py-2 text-right text-xs text-gray-400">{{ isset($answer['created_at']) ? \Carbon\Carbon::parse($answer['created_at'])->format('d.m.Y') : '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- All Members --}}
    @if(!empty($reportData['allMembers']) && count($reportData['allMembers']) > 0)
    <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5">
        <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4">👥 Tüm Startup Üyeleri ({{ count($reportData['allMembers']) }})</h3>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            @foreach(collect($reportData['allMembers'])->take(16) as $member)
            <div class="bg-gray-50 dark:bg-gray-800/40 rounded-lg p-3 flex items-center gap-2">
                <div class="w-7 h-7 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-[10px] font-bold flex-shrink-0">
                    {{ mb_strtoupper(mb_substr($member['name'] ?? 'U', 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-medium text-gray-900 dark:text-white truncate">{{ $member['name'] ?? $member['email'] ?? 'Üye' }}</p>
                    <p class="text-[10px] text-gray-400 truncate">{{ $member['email'] ?? '' }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>
