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

{{-- Raw data accordion --}}
<details class="mt-6 bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800">
    <summary class="px-5 py-3 cursor-pointer text-sm font-semibold text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
        🔍 {{ $isTr ? 'Ham Veri (JSON)' : 'Raw Data (JSON)' }}
    </summary>
    <pre class="p-5 text-xs text-gray-600 dark:text-gray-400 overflow-x-auto max-h-96">{{ json_encode($sessionData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
</details>
@endsection
