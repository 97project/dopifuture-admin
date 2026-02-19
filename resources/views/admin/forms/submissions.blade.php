@extends('admin.layouts.app')
@section('title', __('admin.submissions') . ': ' . $form->name)
@section('content')
    <div class="space-y-4">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $form->name }} —
                    {{ __('admin.submissions') }}</h1>
                <p class="text-sm text-gray-500">{{ $submissions->total() }} {{ __('admin.total') }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.forms.submissions', [$form, 'unread' => 1]) }}"
                    class="px-3 py-2 text-sm rounded-lg {{ request('unread') ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600' }}">{{ __('admin.unread_only') }}</a>
                <a href="{{ route('admin.forms.edit', $form) }}"
                    class="px-3 py-2 text-sm bg-gray-100 dark:bg-[#0A1628] rounded-lg text-gray-600">{{ __('admin.edit_form') }}</a>
            </div>
        </div>

        <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-[#0A1628]">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">#</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">{{ __('admin.data') }}
                        </th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">IP</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">{{ __('admin.date') }}
                        </th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600 dark:text-gray-300">
                            {{ __('admin.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($submissions as $sub)
                        <tr
                            class="hover:bg-gray-50 dark:hover:bg-gray-700/50 {{ !$sub->is_read ? 'bg-blue-50/50 dark:bg-blue-900/10' : '' }}">
                            <td class="px-4 py-3 font-mono text-gray-400">#{{ $sub->id }}</td>
                            <td class="px-4 py-3">
                                <div class="text-xs text-gray-500 max-w-md truncate">
                                    @foreach(array_slice($sub->data ?? [], 0, 3) as $key => $val)
                                        <span class="font-medium">{{ $key }}:</span>
                                        {{ is_string($val) ? \Illuminate\Support\Str::limit($val, 30) : json_encode($val) }}{{ !$loop->last ? ' · ' : '' }}
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-4 py-3 text-gray-400 text-xs font-mono">{{ $sub->ip_address }}</td>
                            <td class="px-4 py-3 text-gray-500 text-xs">{{ $sub->created_at->format('d.m.Y H:i') }}</td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-1">
                                    <a href="{{ route('admin.forms.submissions.show', [$form, $sub]) }}"
                                        class="p-1.5 rounded hover:bg-gray-100 text-gray-500">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                    <form action="{{ route('admin.forms.submissions.destroy', [$form, $sub]) }}" method="POST"
                                        onsubmit="return confirm('{{ __('admin.confirm') }}?')">@csrf @method('DELETE')
                                        <button class="p-1.5 rounded hover:bg-red-50 text-gray-500 hover:text-red-600"><svg
                                                class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center text-gray-500">{{ __('admin.no_submissions') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-4 py-3 border-t border-gray-100 dark:border-[#1A3A5C]">{{ $submissions->links() }}</div>
        </div>
    </div>
@endsection