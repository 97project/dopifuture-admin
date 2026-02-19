@props([
    'paginator' => null,
])

@if($paginator && $paginator->hasPages())
    <div class="flex items-center justify-between px-4 py-3 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
        {{-- Mobile: simple prev/next --}}
        <div class="flex flex-1 justify-between sm:hidden">
            @if($paginator->onFirstPage())
                <span class="px-4 py-2 text-sm text-gray-400 dark:text-gray-600 bg-gray-100 dark:bg-gray-700 rounded-lg cursor-not-allowed">
                    {!! __('pagination.previous') !!}
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}"
                    class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                    {!! __('pagination.previous') !!}
                </a>
            @endif

            @if($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}"
                    class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                    {!! __('pagination.next') !!}
                </a>
            @else
                <span class="px-4 py-2 text-sm text-gray-400 dark:text-gray-600 bg-gray-100 dark:bg-gray-700 rounded-lg cursor-not-allowed">
                    {!! __('pagination.next') !!}
                </span>
            @endif
        </div>

        {{-- Desktop: full pagination --}}
        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
            <div class="text-sm text-gray-500 dark:text-gray-400">
                {{ __('pagination.showing', [
                    'first' => $paginator->firstItem() ?? 0,
                    'last' => $paginator->lastItem() ?? 0,
                    'total' => $paginator->total(),
                ]) }}
            </div>

            <nav class="flex items-center gap-1">
                {{-- Previous --}}
                @if($paginator->onFirstPage())
                    <span class="w-8 h-8 flex items-center justify-center text-gray-300 dark:text-gray-600 rounded-lg cursor-not-allowed">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}"
                        class="w-8 h-8 flex items-center justify-center text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </a>
                @endif

                {{-- Pages --}}
                @foreach($paginator->getUrlRange(1, $paginator->lastPage()) as $page => $url)
                    @if($page == $paginator->currentPage())
                        <span class="w-8 h-8 flex items-center justify-center text-sm font-medium text-white bg-blue-600 dark:bg-blue-500 rounded-lg">
                            {{ $page }}
                        </span>
                    @else
                        <a href="{{ $url }}"
                            class="w-8 h-8 flex items-center justify-center text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                            {{ $page }}
                        </a>
                    @endif
                @endforeach

                {{-- Next --}}
                @if($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}"
                        class="w-8 h-8 flex items-center justify-center text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                @else
                    <span class="w-8 h-8 flex items-center justify-center text-gray-300 dark:text-gray-600 rounded-lg cursor-not-allowed">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </span>
                @endif
            </nav>
        </div>
    </div>
@endif
