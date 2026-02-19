@props([
    'items' => [],
])

<nav class="flex items-center text-sm text-gray-500 dark:text-gray-400" aria-label="Breadcrumb">
    <a href="{{ route('admin.dashboard') }}" class="hover:text-gray-700 dark:hove
        r       :text-gray-200 transition-colors">
         <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
        </svg>
    </a>
 @foreach($items as $item)
         <svg class="w-4 h-4 mx-2 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
      @if(!$loop->last && isset($item['url']))
        <a href="{{ $item['url'] }}" class="hover:text-gray-700 dark:hover:text-gray-200 transition-colors">
                {{ $item['label'] }}
        </a>
    @else
            <span class="text-gray-900 dark:text-gray-100 font-medium">{{ $item['label'] }}</span>
        @endif
@endforeach
</nav>
