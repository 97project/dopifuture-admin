@props([
    'id' => 'drawer',
    'title' => '',
    'position' => 'right', // left or right
    'width' => 'max-w-md',
])

@php
    $translateClass = $position === 'right' ? 'translate-x-full' : '-translate-x-full';
    $positionClass = $position === 'right' ? 'right-0' : 'left-0';
@endphp

{{-- Backdrop --}}
<div id="{{ $id }}-backdrop"
    class="fixed inset-0 z-40 bg-gray-900/50 dark:bg-gray-900/75 hidden transition-opacity duration-300"
    onclick="document.getElementById('{{ $id }}').classList.add('{{ $translateClass }}'); this.classList.add('hidden');">
</div>

{{-- Drawer panel --}}
<div id="{{ $id }}"
    class="fixed inset-y-0 {{ $positionClass }} z-50 w-full {{ $width }} bg-white dark:bg-gray-800 shadow-xl transform {{ $translateClass }} transition-transform duration-300 ease-in-out"
    {{ $attributes }}>

    {{-- Header --}}
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $title }}</h3>
        <button type="button"
            onclick="document.getElementById('{{ $id }}').classList.add('{{ $translateClass }}'); document.getElementById('{{ $id }}-backdrop').classList.add('hidden');"
            class="p-1.5 text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    {{-- Content --}}
    <div class="flex-1 overflow-y-auto px-6 py-4">
    {{ $slot }}
  </div>
    {{-- Footer (optional) --}}
    @if(isset($footer))
        <div class="border-t border-gray-200 dark:border-gray-700 px-6 py-4">
            {{ $footer }}
        </div>
    @endif
</div>
