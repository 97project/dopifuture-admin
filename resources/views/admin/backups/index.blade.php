@extends('admin.layouts.app')

@section('title', __('admin.backups'))

@section('breadcrumb')
    <div class="flex items-center gap-2 text-sm">
        <a href="{{ route('admin.dashboard') }}"
            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">{{ __('admin.dashboard') }}</a>
        <svg class="w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-gray-900 dark:text-gray-100 font-semibold">{{ __('admin.backups') }}</span>
    </div>
@endsection

@section('content')
    <div class="space-y-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div
                class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-4 text-center">
                <p class="text-2xl font-extrabold text-[#13398E]">{{ count($backups) }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ __('admin.total_backups') }}</p>
            </div>
            <div
                class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-4 text-center">
                <p class="text-2xl font-extrabold text-emerald-500">
                    @php $totalBackupSize = collect($backups)->sum('size'); @endphp
                    @if($totalBackupSize >= 1073741824) {{ number_format($totalBackupSize / 1073741824, 1) }} GB
                    @elseif($totalBackupSize >= 1048576) {{ number_format($totalBackupSize / 1048576, 1) }} MB
                    @else {{ number_format($totalBackupSize / 1024, 1) }} KB @endif
                </p>
                <p class="text-xs text-gray-400 mt-0.5">{{ __('admin.total_size') }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] overflow-hidden">
            <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100 dark:border-[#1A3A5C]">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white">{{ __('admin.backups') }}</h3>
                @can('backups.create')
                    <form action="{{ route('admin.backups.create') }}" method="POST">@csrf
                        <button type="submit"
                            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-[#0B6AB2] text-white rounded-lg text-xs font-medium hover:bg-[#13398E] transition"
                            onclick="this.disabled=true;this.innerHTML='{{ __('admin.creating_backup') }}...';this.form.submit();">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            {{ __('admin.create_backup') }}
                        </button>
                    </form>
                @endcan
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-[#1A3A5C]">
                        <th class="text-left px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                            {{ __('admin.filename') }}</th>
                        <th class="text-center px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                            {{ __('admin.size') }}</th>
                        <th class="text-center px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                            {{ __('admin.date') }}</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                            {{ __('admin.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-[#1A3A5C]/50">
                    @forelse($backups as $backup)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-[#0A1628]/30 transition">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-[#0B6AB2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                                    </svg>
                                    <span class="font-mono text-xs text-gray-900 dark:text-white">{{ $backup['name'] }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-3 text-center"><span
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 dark:bg-blue-900/20 text-blue-600">{{ $backup['size_human'] }}</span>
                            </td>
                            <td class="px-5 py-3 text-center text-xs text-gray-500">{{ $backup['date_human'] }}</td>
                            <td class="px-5 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.backups.download', $backup['name']) }}"
                                        class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 hover:bg-emerald-100 transition">{{ __('admin.download') }}</a>
                                    @can('backups.delete')
                                        <form action="{{ route('admin.backups.destroy', $backup['name']) }}" method="POST"
                                            onsubmit="return confirm('{{ __('admin.confirm') }}?')">@csrf @method('DELETE')
                                            <button
                                                class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-red-50 dark:bg-red-900/20 text-red-500 hover:bg-red-100 transition">{{ __('admin.delete') }}</button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-8 text-center text-gray-400">{{ __('admin.no_backups') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection