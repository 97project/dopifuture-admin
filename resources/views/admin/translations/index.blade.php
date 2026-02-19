@extends('admin.layouts.app')

@section('title', __('admin.translations'))

@section('breadcrumb')
    <div class="flex items-center gap-2 text-sm">
        <a href="{{ route('admin.dashboard') }}"
            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">{{ __('admin.dashboard') }}</a>
        <svg class="w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-gray-900 dark:text-gray-100 font-semibold">{{ __('admin.translations') }}</span>
    </div>
@endsection

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-end gap-2">
            @can('translations.export')
                <a href="{{ route('admin.translations.export', ['locale' => 'tr']) }}"
                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 border border-gray-200 dark:border-[#1A3A5C] rounded-lg text-xs font-medium hover:bg-gray-50 dark:hover:bg-[#0A1628] transition">📥
                    {{ __('admin.export') }} (TR)</a>
                <a href="{{ route('admin.translations.export', ['locale' => 'en']) }}"
                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 border border-gray-200 dark:border-[#1A3A5C] rounded-lg text-xs font-medium hover:bg-gray-50 dark:hover:bg-[#0A1628] transition">📥
                    {{ __('admin.export') }} (EN)</a>
            @endcan
            @can('translations.create')
                <a href="{{ route('admin.translations.create') }}"
                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-[#0B6AB2] text-white rounded-lg text-xs font-medium hover:bg-[#13398E] transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    {{ __('admin.new_translation') }}
                </a>
            @endcan
        </div>

        @can('translations.import')
            <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-4">
                <form action="{{ route('admin.translations.import') }}" method="POST" enctype="multipart/form-data"
                    class="flex flex-col sm:flex-row gap-3 items-end">
                    @csrf
                    <div class="flex-1">
                        <label class="text-xs font-semibold uppercase tracking-wider text-gray-400">{{ __('admin.import') }}
                            JSON</label>
                        <input type="file" name="file" accept=".json" required
                            class="w-full text-sm mt-1 file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-blue-50 dark:file:bg-blue-900/20 file:text-blue-700 file:text-xs">
                    </div>
                    <select name="locale" class="rounded-lg border-gray-200 dark:border-[#1A3A5C] dark:bg-[#0A1628] text-sm">
                        @foreach($languages as $lang) <option value="{{ $lang->code }}">{{ $lang->native_name }}</option>
                        @endforeach
                    </select>
                    <button type="submit"
                        class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white text-sm rounded-lg transition">{{ __('admin.import') }}</button>
                </form>
            </div>
        @endcan

        <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-4">
            <form method="GET" class="grid grid-cols-1 sm:grid-cols-5 gap-3 items-end">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('admin.search') }}..."
                    class="rounded-lg border-gray-200 dark:border-[#1A3A5C] dark:bg-[#0A1628] text-sm focus:ring-[#0B6AB2] focus:border-[#0B6AB2]">
                <select name="group" class="rounded-lg border-gray-200 dark:border-[#1A3A5C] dark:bg-[#0A1628] text-sm">
                    <option value="">{{ __('admin.group') }}</option>
                    @foreach($groups as $g) <option value="{{ $g }}" {{ request('group') === $g ? 'selected' : '' }}>{{ $g }}
                    </option> @endforeach
                </select>
                <select name="locale" class="rounded-lg border-gray-200 dark:border-[#1A3A5C] dark:bg-[#0A1628] text-sm">
                    <option value="">All</option>
                    @foreach($languages as $lang) <option value="{{ $lang->code }}" {{ request('locale') === $lang->code ? 'selected' : '' }}>{{ $lang->code }}</option> @endforeach
                </select>
                <label class="flex items-center gap-2 text-xs text-gray-500"><input type="checkbox" name="missing_only"
                        value="1" {{ request('missing_only') ? 'checked' : '' }}
                        class="rounded border-gray-300 text-[#0B6AB2] focus:ring-[#0B6AB2]">
                    {{ __('admin.missing_translations') }}</label>
                <div class="flex gap-2">
                    <button type="submit"
                        class="flex-1 px-4 py-2 bg-[#0B6AB2] text-white rounded-lg text-sm hover:bg-[#13398E] transition">{{ __('admin.filter') }}</button>
                    <a href="{{ route('admin.translations.index') }}"
                        class="px-3 py-2 border border-gray-200 dark:border-[#1A3A5C] rounded-lg text-sm hover:bg-gray-50 dark:hover:bg-[#0A1628] transition">✕</a>
                </div>
            </form>
        </div>

        <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-[#1A3A5C]">
                        <th class="text-left px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                            {{ __('admin.group') }}</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                            {{ __('admin.key') }}</th>
                        <th class="text-center px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                            Locale</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                            {{ __('admin.value') }}</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                            {{ __('admin.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-[#1A3A5C]/50">
                    @forelse($translations as $t)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-[#0A1628]/30 transition">
                            <td class="px-5 py-3"><span
                                    class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-gray-100 dark:bg-[#0A1628] text-gray-500">{{ $t->group }}</span>
                            </td>
                            <td class="px-5 py-3 font-mono text-xs text-gray-600 dark:text-gray-400">{{ $t->key }}</td>
                            <td class="px-5 py-3 text-center"><span
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 dark:bg-blue-900/20 text-blue-600">{{ $t->locale }}</span>
                            </td>
                            <td class="px-5 py-3 text-gray-500 truncate max-w-xs">{{ $t->value ?? '—' }}</td>
                            <td class="px-5 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @can('translations.edit')<a href="{{ route('admin.translations.edit', $t) }}"
                                    class="text-xs text-gray-500 hover:underline">{{ __('admin.edit') }}</a>@endcan
                                    @can('translations.delete')
                                        <form action="{{ route('admin.translations.destroy', $t) }}" method="POST"
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
            @if($translations->hasPages())
                <div class="px-5 py-3 border-t border-gray-100 dark:border-[#1A3A5C]">{{ $translations->links() }}</div>
            @endif
        </div>
    </div>
@endsection