@props([
    'headers' => [],
    'rows' => [],
    'checkboxes' => false,
    'actions' => true,
    'emptyText' => __('messages.no_results'),
    'sortField' => null,
    'sortDirection' => 'asc',
])

<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900/50">
                <tr>
                    @if($checkboxes)
                        <th class="w-12 px-4 py-3">
                            <input type="checkbox" id="selectAll"
                                class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500"
                                onchange="document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = this.checked)">
                        </th>
                    @endif
                    @foreach($headers as $header)
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            @if(isset($header['sortable']) && $header['sortable'])
                                <a href="?sort={{ $header['field'] }}&direction={{ $sortField === $header['field'] && $sortDirection === 'asc' ? 'desc' : 'asc' }}"
                                    class="flex items-center gap-1 hover:text-gray-700 dark:hover:text-gray-200">
                                    {{ $header['label'] }}
                                    @if($sortField === $header['field'])
                                        <svg class="w-3 h-3 {{ $sortDirection === 'desc' ? 'rotate-180' : '' }}" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M5 10l5-5 5 5H5z"/>
                                        </svg>
                                    @endif
                                </a>
                            @else
                                {{ $header['label'] }}
                            @endif
                        </th>
                    @endforeach
                    @if($actions)
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            {{ __('admin.actions') }}
                        </th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                {{ $slot }}
            </tbody>
        </table>
    </div>

    @if(count($rows ?? []) === 0 && !$slot->isNotEmpty())
        <div class="text-center py-12 text-gray-500 dark:text-gray-400">
            <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
            </svg>
            <p class="text-sm">{{ $emptyText }}</p>
        </div>
    @endif
</div>
