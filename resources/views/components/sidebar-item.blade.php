@props([
    'href',
    'icon' => '',
    'active' => false,
])

<a href="{{ $href }}"
    {{ $attributes->merge([
        'class' => 'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors ' .
            ($active
                ? 'bg-blue-50 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300'
                : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700'),
    ]) }}>
    @if($icon)
        {!! $icon !!}
    @endif
    {{ $slot }}
</a>
