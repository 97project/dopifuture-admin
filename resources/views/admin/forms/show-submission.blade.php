@extends('admin.layouts.app')
@section('title', __('admin.submission_detail'))
@section('content')
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $form->name }} — #{{ $submission->id }}</h1>
            <a href="{{ route('admin.forms.submissions', $form) }}" class="text-sm text-gray-500 hover:text-gray-700">←
                {{ __('admin.back') }}</a>
        </div>

        <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-6 space-y-4">
            <div class="grid grid-cols-3 gap-4 pb-4 border-b border-gray-100 dark:border-[#1A3A5C]">
                <div>
                    <p class="text-xs text-gray-400 uppercase">{{ __('admin.date') }}</p>
                    <p class="text-sm font-medium">{{ $submission->created_at->format('d.m.Y H:i:s') }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase">IP</p>
                    <p class="text-sm font-mono">{{ $submission->ip_address }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase">User Agent</p>
                    <p class="text-xs text-gray-500 truncate" title="{{ $submission->user_agent }}">
                        {{ \Illuminate\Support\Str::limit($submission->user_agent, 50) }}</p>
                </div>
            </div>

            @foreach($submission->data ?? [] as $key => $value)
                <div class="py-2">
                    <p class="text-xs text-gray-400 uppercase font-medium mb-1">{{ $key }}</p>
                    <p class="text-sm text-gray-900 dark:text-white whitespace-pre-wrap">
                        {{ is_string($value) ? $value : json_encode($value, JSON_PRETTY_PRINT) }}</p>
                </div>
            @endforeach
        </div>

        <form action="{{ route('admin.forms.submissions.destroy', [$form, $submission]) }}" method="POST"
            onsubmit="return confirm('{{ __('admin.confirm') }}?')">
            @csrf @method('DELETE')
            <button
                class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm rounded-lg">{{ __('admin.delete_submission') }}</button>
        </form>
    </div>
@endsection