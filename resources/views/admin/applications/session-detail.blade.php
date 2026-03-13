@extends('admin.layouts.app')
@section('title', 'Oturum Detay — ' . $application->name)
@section('breadcrumb')
    <a href="{{ route('admin.applications.index') }}" class="hover:text-gray-700 dark:hover:text-gray-200">{{ __('admin.applications') }}</a>
    <span class="mx-2">/</span>
    <a href="{{ route('admin.applications.show', $application) }}" class="hover:text-gray-700 dark:hover:text-gray-200">{{ $application->name }}</a>
    <span class="mx-2">/</span>
    <span>Oturum {{ Str::limit($sessionId, 12) }}</span>
@endsection

@section('content')
@php
    $isTr = app()->getLocale() === 'tr';
    $user = $sessionData['userName'] ?? $sessionData['user']['name'] ?? 'Kullanıcı';
    $startedAt = $sessionData['startedAt'] ?? $sessionData['created_at'] ?? null;
    $messages = $sessionData['messages'] ?? $sessionData['history'] ?? [];
@endphp

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
        {{ $module === 'simulator' ? '🎭 Role Galaxy' : ($module === 'lecturer' ? '🤖 WAY AI Coach' : '💬 Study Space') }}
        — {{ $isTr ? 'Oturum Detayı' : 'Session Detail' }}
    </h1>
    <p class="text-sm text-gray-500 mt-1">
        {{ $user }} · {{ $startedAt ? \Carbon\Carbon::parse($startedAt)->format('d.m.Y H:i') : '' }}
        · Session: <code class="text-xs bg-gray-100 dark:bg-gray-800 px-1.5 py-0.5 rounded">{{ $sessionId }}</code>
    </p>
</div>

{{-- Session Overview Cards --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4 text-center">
        <div class="text-2xl font-extrabold text-blue-500">{{ count($messages) }}</div>
        <div class="text-xs text-gray-500">{{ $isTr ? 'Mesaj' : 'Messages' }}</div>
    </div>
    @if(!empty($sessionData['score']))
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4 text-center">
        <div class="text-2xl font-extrabold text-emerald-500">{{ $sessionData['score'] }}</div>
        <div class="text-xs text-gray-500">{{ $isTr ? 'Puan' : 'Score' }}</div>
    </div>
    @endif
    @if(!empty($sessionData['duration']))
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4 text-center">
        <div class="text-2xl font-extrabold text-amber-500">{{ $sessionData['duration'] }}</div>
        <div class="text-xs text-gray-500">{{ $isTr ? 'Süre (dk)' : 'Duration (min)' }}</div>
    </div>
    @endif
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4 text-center">
        <div class="text-2xl font-extrabold text-purple-500">{{ ucfirst($module) }}</div>
        <div class="text-xs text-gray-500">{{ $isTr ? 'Modül' : 'Module' }}</div>
    </div>
</div>

{{-- Messages/Transcript --}}
<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">💬 {{ $isTr ? 'Oturum Transkripti' : 'Session Transcript' }}</h3>
    </div>

    @if(count($messages) === 0)
    <div class="p-12 text-center">
        <div class="text-3xl mb-2">📭</div>
        <p class="text-gray-400">{{ $isTr ? 'Bu oturumda mesaj bulunamadı.' : 'No messages found in this session.' }}</p>
    </div>
    @else
    <div class="divide-y divide-gray-100 dark:divide-gray-800">
        @foreach($messages as $i => $msg)
        @php
            $role = $msg['role'] ?? $msg['sender'] ?? 'system';
            $content = $msg['content'] ?? $msg['text'] ?? $msg['question'] ?? '';
            $isAI = in_array($role, ['assistant', 'ai', 'system', 'bot']);
        @endphp
        <div class="px-5 py-3 flex gap-3 {{ $isAI ? 'bg-blue-50/30 dark:bg-blue-900/10' : '' }}">
            <div class="flex-shrink-0 w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold {{ $isAI ? 'bg-blue-100 dark:bg-blue-900/40 text-blue-600' : 'bg-gray-100 dark:bg-gray-800 text-gray-600' }}">
                {{ $isAI ? '🤖' : '👤' }}
            </div>
            <div class="flex-1 min-w-0">
                <div class="text-[10px] font-semibold text-gray-400 mb-0.5">{{ ucfirst($role) }} · #{{ $i + 1 }}</div>
                <div class="text-sm text-gray-900 dark:text-gray-100 whitespace-pre-line">{{ $content }}</div>
                @if(!empty($msg['score']))
                <span class="inline-block mt-1 px-2 py-0.5 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 text-[10px] font-semibold rounded">Score: {{ $msg['score'] }}</span>
                @endif
                @if(!empty($msg['feedback']))
                <div class="mt-1 text-xs text-purple-600 dark:text-purple-400 italic">💡 {{ $msg['feedback'] }}</div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>

{{-- Simulator Turn Visualization (Faz 3 — Admin parity) --}}
@if($module === 'simulator' && count($messages) > 0)
@php
    // Parse simulator turns from messages
    $turns = [];
    foreach ($messages as $mi => $msg) {
        $role = $msg['role'] ?? $msg['sender'] ?? '';
        if ($role === 'assistant' || isset($msg['choices']) || isset($msg['node_id'])) {
            $choices = $msg['choices'] ?? $msg['options'] ?? [];
            $delta = $msg['delta'] ?? $msg['metrics_change'] ?? [];
            $metrics = $msg['metrics'] ?? $msg['metrics_after'] ?? $msg['currentMetrics'] ?? [];
            $turns[] = [
                'num' => count($turns) + 1,
                'narrative' => $msg['content'] ?? $msg['text'] ?? $msg['narrative'] ?? '',
                'choices' => is_array($choices) ? $choices : [],
                'choice_made' => $msg['choice_made'] ?? $msg['selected_choice'] ?? $msg['choice_id'] ?? null,
                'score_change' => $msg['score_change'] ?? $msg['delta_score'] ?? 0,
                'score_after' => $msg['score_after'] ?? $msg['currentScore'] ?? null,
                'threshold' => $msg['threshold_after'] ?? $msg['threshold'] ?? null,
                'metrics' => $metrics,
                'delta' => $delta,
            ];
        }
    }
@endphp
@if(count($turns) > 0)
<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden mt-6">
    <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">🎮 {{ $isTr ? 'Turn Bazlı Simülasyon Detayı' : 'Turn-by-Turn Simulation Detail' }} ({{ count($turns) }} {{ $isTr ? 'tur' : 'turns' }})</h3>
    </div>
    <div class="divide-y divide-gray-100 dark:divide-gray-800">
        @foreach($turns as $turn)
        @php
            $th = $turn['threshold'];
            $thColor = match(true) {
                $th === 'prosperity' || $th === 'refah' => '#10B981',
                $th === 'balance' || $th === 'denge' => '#3B82F6',
                $th === 'crisis' || $th === 'kriz' => '#F59E0B',
                $th === 'disaster' || $th === 'felaket' => '#EF4444',
                default => '#6B7280',
            };
        @endphp
        <div class="px-5 py-4">
            <div class="flex items-start gap-3">
                <div class="w-7 h-7 rounded-full flex-shrink-0 flex items-center justify-center text-xs font-bold text-white" style="background:{{ $thColor }}">{{ $turn['num'] }}</div>
                <div class="flex-1">
                    @if($turn['narrative'])
                    <p class="text-sm text-gray-900 dark:text-gray-100 mb-2">{{ Str::limit($turn['narrative'], 200) }}</p>
                    @endif

                    {{-- Choices --}}
                    @if(count($turn['choices']) > 0)
                    <div class="flex flex-wrap gap-1.5 mb-2">
                        @foreach($turn['choices'] as $ci => $ch)
                        @php $sel = ($turn['choice_made'] !== null && (is_numeric($turn['choice_made']) ? $ci == $turn['choice_made'] : ($ch['id'] ?? $ci) == $turn['choice_made'])); @endphp
                        <span class="text-[10px] px-2.5 py-1 rounded-md font-medium {{ $sel ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 ring-1 ring-blue-400' : 'bg-gray-100 dark:bg-gray-800 text-gray-500' }}">
                            {{ is_array($ch) ? ($ch['text'] ?? $ch['label'] ?? json_encode($ch)) : $ch }}
                            @if($sel) ✓ @endif
                        </span>
                        @endforeach
                    </div>
                    @endif

                    {{-- Metrics row --}}
                    <div class="flex items-center gap-3 text-xs">
                        @if($turn['score_change'] != 0)
                        <span class="font-semibold {{ $turn['score_change'] > 0 ? 'text-emerald-600' : 'text-red-600' }}">
                            {{ $turn['score_change'] > 0 ? '+' : '' }}{{ $turn['score_change'] }} pts
                        </span>
                        @endif
                        @if($turn['score_after'])
                        <span class="text-gray-400">Total: <strong>{{ $turn['score_after'] }}</strong></span>
                        @endif
                        @if($th)
                        <span class="font-medium" style="color:{{ $thColor }}">{{ $th }}</span>
                        @endif
                        @foreach((array)$turn['delta'] as $dk => $dv)
                        @if($dv != 0)
                        <span class="text-gray-400">{{ $dk }}: <strong class="{{ $dv > 0 ? 'text-emerald-600' : 'text-red-600' }}">{{ $dv > 0 ? '+' : '' }}{{ $dv }}</strong></span>
                        @endif
                        @endforeach
                    </div>

                    {{-- Metrics circles --}}
                    @if(!empty($turn['metrics']))
                    <div class="flex gap-3 mt-2">
                        @foreach(['health' => ['❤️', '#EF4444'], 'resource' => ['🌿', '#10B981'], 'ethics' => ['🧡', '#F59E0B'], 'adaptation' => ['✅', '#3B82F6']] as $mk => $mInfo)
                        @if(isset($turn['metrics'][$mk]))
                        <div class="text-center">
                            <div class="w-8 h-8 rounded-full border-2 flex items-center justify-center text-[10px] font-bold" style="border-color:{{ $mInfo[1] }};color:{{ $mInfo[1] }}">{{ $turn['metrics'][$mk] }}</div>
                            <div class="text-[8px] text-gray-400 mt-0.5">{{ $mInfo[0] }}</div>
                        </div>
                        @endif
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif
@endif

{{-- Raw data accordion --}}
<details class="mt-6 bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800">
    <summary class="px-5 py-3 cursor-pointer text-sm font-semibold text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
        🔍 {{ $isTr ? 'Ham Veri (JSON)' : 'Raw Data (JSON)' }}
    </summary>
    <pre class="p-5 text-xs text-gray-600 dark:text-gray-400 overflow-x-auto max-h-96">{{ json_encode($sessionData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
</details>
@endsection
