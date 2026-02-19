@extends('admin.layouts.app')

@section('title', __('admin.file_manager'))

@section('breadcrumb')
    <div class="flex items-center gap-2 text-sm">
        <a href="{{ route('admin.dashboard') }}"
            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">{{ __('admin.dashboard') }}</a>
        <svg class="w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-gray-900 dark:text-gray-100 font-semibold">{{ __('admin.file_manager') }}</span>
    </div>
@endsection

@section('content')
    <div class="space-y-6">
        @php $totalSize = isset($stats['total_size']) ? $stats['total_size'] : 0; @endphp
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div
                class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-4 text-center">
                <p class="text-2xl font-extrabold text-[#13398E]">{{ $stats['total'] ?? 0 }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ __('admin.total_files') }}</p>
            </div>
            <div
                class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-4 text-center">
                <p class="text-2xl font-extrabold text-[#0B6AB2]">{{ $stats['images'] ?? 0 }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ __('admin.images') }}</p>
            </div>
            <div
                class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-4 text-center">
                <p class="text-2xl font-extrabold text-emerald-500">
                    @if($totalSize >= 1073741824) {{ number_format($totalSize / 1073741824, 1) }} GB
                    @elseif($totalSize >= 1048576) {{ number_format($totalSize / 1048576, 1) }} MB
                    @else {{ number_format($totalSize / 1024, 1) }} KB @endif
                </p>
                <p class="text-xs text-gray-400 mt-0.5">{{ __('admin.total_size') }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-4">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2 flex-wrap">
                    <a href="{{ route('admin.media.index', ['folder' => '/']) }}"
                        class="px-3 py-1.5 text-xs rounded-lg font-medium {{ $folder === '/' ? 'bg-[#0B6AB2] text-white' : 'bg-gray-100 dark:bg-[#0A1628] text-gray-500 hover:bg-gray-200 dark:hover:bg-[#1A3A5C]' }} transition">/
                        Root</a>
                    @foreach($folders as $f)
                        <a href="{{ route('admin.media.index', ['folder' => $f]) }}"
                            class="px-3 py-1.5 text-xs rounded-lg font-medium {{ $folder === $f ? 'bg-[#0B6AB2] text-white' : 'bg-gray-100 dark:bg-[#0A1628] text-gray-500 hover:bg-gray-200 dark:hover:bg-[#1A3A5C]' }} transition">{{ $f }}</a>
                    @endforeach
                </div>
                @can('media.upload')
                    <button onclick="document.getElementById('upload-modal').classList.remove('hidden')"
                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-[#0B6AB2] text-white rounded-lg text-xs font-medium hover:bg-[#13398E] transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        {{ __('admin.upload_files') }}
                    </button>
                @endcan
            </div>
            <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <input type="hidden" name="folder" value="{{ $folder }}">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('admin.search') }}..."
                    class="rounded-lg border-gray-200 dark:border-[#1A3A5C] dark:bg-[#0A1628] text-sm focus:ring-[#0B6AB2] focus:border-[#0B6AB2]">
                <select name="type" class="rounded-lg border-gray-200 dark:border-[#1A3A5C] dark:bg-[#0A1628] text-sm">
                    <option value="">{{ __('admin.all_types') }}</option>
                    <option value="images" {{ request('type') === 'images' ? 'selected' : '' }}>{{ __('admin.images_only') }}
                    </option>
                </select>
                <div class="flex gap-2">
                    <button type="submit"
                        class="flex-1 px-4 py-2 bg-[#0B6AB2] text-white rounded-lg text-sm hover:bg-[#13398E] transition">{{ __('admin.filter') }}</button>
                    <a href="{{ route('admin.media.index', ['folder' => $folder]) }}"
                        class="px-3 py-2 border border-gray-200 dark:border-[#1A3A5C] rounded-lg text-sm hover:bg-gray-50 dark:hover:bg-[#0A1628] transition">✕</a>
                </div>
            </form>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-4">
            @forelse($media as $item)
                <div
                    class="group relative bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] overflow-hidden hover:shadow-lg hover:border-[#0B6AB2]/30 transition-all">
                    @if($item->is_image)
                        <div class="aspect-square"><img src="{{ $item->url }}"
                                alt="{{ $item->alt_text[app()->getLocale()] ?? $item->name }}" class="w-full h-full object-cover">
                        </div>
                    @else
                        <div class="aspect-square flex items-center justify-center bg-gray-50 dark:bg-[#0A1628]">
                            <svg class="w-12 h-12 text-gray-200 dark:text-gray-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                        </div>
                    @endif
                    <div class="p-2.5">
                        <p class="text-xs font-medium text-gray-900 dark:text-white truncate">{{ $item->name }}</p>
                        <p class="text-[10px] text-gray-400">{{ $item->human_readable_size }}</p>
                    </div>
                    @can('media.delete')
                        <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                            <form action="{{ route('admin.media.destroy', $item) }}" method="POST"
                                onsubmit="return confirm('{{ __('admin.confirm') }}?')">@csrf @method('DELETE')
                                <button class="p-1.5 bg-red-500 text-white rounded-lg shadow-md hover:bg-red-600 transition"><svg
                                        class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg></button>
                            </form>
                        </div>
                    @endcan
                </div>
            @empty
                <div class="col-span-6 py-12 text-center text-gray-400">{{ __('admin.no_files') }}</div>
            @endforelse
        </div>

        @if($media->hasPages())
            <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] px-5 py-3">
                {{ $media->links() }}</div>
        @endif
    </div>

    {{-- Upload Modal --}}
    <div id="upload-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
        <div
            class="bg-white dark:bg-[#0E2442] rounded-xl shadow-2xl border border-gray-200 dark:border-[#1A3A5C] w-full max-w-md p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ __('admin.upload_files') }}</h3>
                <button onclick="document.getElementById('upload-modal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
            </div>
            <form action="{{ route('admin.media.upload') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <input type="hidden" name="folder" value="{{ $folder }}">
                <div class="border-2 border-dashed border-gray-200 dark:border-[#1A3A5C] rounded-xl p-8 text-center">
                    <input type="file" name="files[]" multiple
                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-blue-50 dark:file:bg-blue-900/20 file:text-blue-700">
                    <p class="text-xs text-gray-400 mt-2">Max 10MB per file</p>
                </div>
                <button type="submit"
                    class="w-full py-2.5 bg-[#0B6AB2] hover:bg-[#13398E] text-white font-medium rounded-lg transition">{{ __('admin.upload') }}</button>
            </form>
        </div>
    </div>
@endsection