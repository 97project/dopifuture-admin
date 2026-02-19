@extends('admin.layouts.app')

@section('title', __('admin.forms'))

@section('breadcrumb')
    <div class="flex items-center gap-2 text-sm">
        <a href="{{ route('admin.dashboard') }}"
            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">{{ __('admin.dashboard') }}</a>
        <svg class="w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-gray-900 dark:text-gray-100 font-semibold">{{ __('admin.forms') }}</span>
    </div>
@endsection

@section('content')
    <div class="space-y-6">
        <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] overflow-hidden">
            <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100 dark:border-[#1A3A5C]">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white">{{ __('admin.forms') }} <span
                        class="text-gray-400 font-normal">({{ $forms->total() }})</span></h3>
                @can('forms.create')
                    <a href="{{ route('admin.forms.create') }}"
                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-[#0B6AB2] text-white rounded-lg text-xs font-medium hover:bg-[#13398E] transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        {{ __('admin.new_form') }}
                    </a>
                @endcan
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-[#1A3A5C]">
                        <th class="text-left px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                            {{ __('admin.name') }}</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">Slug
                        </th>
                        <th class="text-center px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                            {{ __('admin.fields') }}</th>
                        <th class="text-center px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                            {{ __('admin.submissions') }}</th>
                        <th class="text-center px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                            {{ __('admin.status') }}</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                            {{ __('admin.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-[#1A3A5C]/50">
                    @forelse($forms as $form)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-[#0A1628]/30 transition">
                            <td class="px-5 py-3 font-medium text-gray-900 dark:text-white">{{ $form->name }}</td>
                            <td class="px-5 py-3 text-xs text-gray-500 font-mono">{{ $form->slug }}</td>
                            <td class="px-5 py-3 text-center"><span
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 dark:bg-blue-900/20 text-blue-600">{{ count($form->fields ?? []) }}</span>
                            </td>
                            <td class="px-5 py-3 text-center">
                                <a href="{{ route('admin.forms.submissions', $form) }}"
                                    class="inline-flex items-center gap-1 text-[#0B6AB2] hover:underline text-xs font-medium">
                                    {{ $form->submissions_count }}
                                    @if($form->unread_submissions_count)
                                        <span
                                            class="inline-flex items-center justify-center w-4 h-4 text-[9px] font-bold bg-red-500 text-white rounded-full">{{ $form->unread_submissions_count }}</span>
                                    @endif
                                </a>
                            </td>
                            <td class="px-5 py-3 text-center">
                                @if($form->is_active)
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
                                    @can('forms.edit')<a href="{{ route('admin.forms.edit', $form) }}"
                                    class="text-xs text-gray-500 hover:underline">{{ __('admin.edit') }}</a>@endcan
                                    @can('forms.delete')
                                        <form action="{{ route('admin.forms.destroy', $form) }}" method="POST"
                                            onsubmit="return confirm('{{ __('admin.confirm') }}?')">@csrf @method('DELETE')
                                            <button class="text-xs text-red-500 hover:underline">{{ __('admin.delete') }}</button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-gray-400">{{ __('admin.no_data') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if($forms->hasPages())
                <div class="px-5 py-3 border-t border-gray-100 dark:border-[#1A3A5C]">{{ $forms->links() }}</div>
            @endif
        </div>
    </div>
@endsection