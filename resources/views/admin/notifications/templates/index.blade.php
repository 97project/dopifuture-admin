@extends('admin.layouts.app')

@section('title', __('admin.notification_templates'))

@section('breadcrumb')
    <div class="flex items-center gap-2 text-sm">
        <a href="{{ route('admin.dashboard') }}"
            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">{{ __('admin.dashboard') }}</a>
        <svg class="w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <a href="{{ route('admin.notifications.index') }}"
            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">{{ __('admin.notifications') }}</a>
        <svg class="w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-gray-900 dark:text-gray-100 font-semibold">{{ __('admin.notification_templates') }}</span>
    </div>
@endsection

@section('content')
    <div class="space-y-6">
        <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] overflow-hidden">
            <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100 dark:border-[#1A3A5C]">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white">{{ __('admin.notification_templates') }}</h3>
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.notifications.index') }}"
                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 border border-gray-200 dark:border-[#1A3A5C] rounded-lg text-xs font-medium hover:bg-gray-50 dark:hover:bg-[#0A1628] transition">←
                        {{ __('admin.back') }}</a>
                    @can('notifications.create')
                        <a href="{{ route('admin.notification-templates.create') }}"
                            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-[#0B6AB2] text-white rounded-lg text-xs font-medium hover:bg-[#13398E] transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            {{ __('admin.new_template') }}
                        </a>
                    @endcan
                </div>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-[#1A3A5C]">
                        <th class="text-left px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                            {{ __('admin.key') }}</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                            {{ __('admin.title') }}</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                            {{ __('admin.channels') }}</th>
                        <th class="text-center px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                            {{ __('admin.status') }}</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                            {{ __('admin.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-[#1A3A5C]/50">
                    @forelse($templates as $template)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-[#0A1628]/30 transition">
                            <td class="px-5 py-3"><span class="font-mono text-xs text-gray-500">{{ $template->key }}</span></td>
                            <td class="px-5 py-3 font-medium text-gray-900 dark:text-white">
                                {{ $template->getTranslation('title') }}</td>
                            <td class="px-5 py-3">
                                <div class="flex flex-wrap gap-1">
                                    @foreach ($template->channels ?? [] as $ch)
                                        <span
                                            class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-blue-50 dark:bg-blue-900/20 text-blue-600">{{ $ch }}</span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-5 py-3 text-center">
                                @if($template->is_active)
                                    <span
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600"><span
                                            class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>{{ __('admin.active') }}</span>
                                @else
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-50 dark:bg-gray-900/20 text-gray-500">{{ __('admin.inactive') }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @can('notifications.edit')<a
                                        href="{{ route('admin.notification-templates.edit', $template) }}"
                                    class="text-xs text-gray-500 hover:underline">{{ __('admin.edit') }}</a>@endcan
                                    @can('notifications.delete')
                                        <form action="{{ route('admin.notification-templates.destroy', $template) }}" method="POST"
                                            onsubmit="return confirm('{{ __('admin.confirm') }}?')">@csrf @method('DELETE')
                                            <button class="text-xs text-red-500 hover:underline">{{ __('admin.delete') }}</button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-gray-400">{{ __('admin.no_data') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if($templates->hasPages())
                <div class="px-5 py-3 border-t border-gray-100 dark:border-[#1A3A5C]">{{ $templates->links() }}</div>
            @endif
        </div>
    </div>
@endsection