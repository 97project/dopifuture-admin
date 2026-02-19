@props([
    'id' => 'modal',
    'title' => '',
    'maxWidth' => 'lg',
    'closeable' => true,
])

@php
    $widths = [
        'sm' => 'max-w-sm',
        'md' => 'max-w-md',
        'lg' => 'max-w-lg',
        'xl' => 'max-w-xl',
        '2xl' => 'max-w-2xl',
    ];
    $maxWidthClass = $widths[$maxWidth] ?? 'max-w-lg';
@endphp

<div id="{{ $id }}"
    class="fixed inset-0 z-50 hidden overflow-y-auto"
    aria-modal="true"
    role="dialog">
    {{-- Backdrop --}}
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity"
        @if($closeable) onclick="closeModal('{{ $id }}')" @endif></div>

    {{-- Modal Panel --}}
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative w-full {{ $maxWidthClass }} bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 transform transition-all">
            {{-- Header --}}
            @if($title)
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $title }}</h3>
                    @if($closeable)
                        <button onclick="closeModal('{{ $id }}')"
                            class="p-1 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    @endif
                </div>
            @endif

            {{-- Body --}}
            <div class="px-6 py-4">
                {{ $slot }}
            </div>

            {{-- Footer --}}
            @isset($footer)
                <div class="flex items-center justify-end gap-2 px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 rounded-b-xl">
                    {{ $footer }}
                </div>
            @endisset
        </div>
    </div>
</div>

<script>
    function openModal(id) {
        document.getElementById(id)?.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
    function closeModal(id) {
        document.getElementById(id)?.classList.add('hidden');
        document.body.style.overflow = '';
    }
</script>
