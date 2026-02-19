@props([
    'name',
    'label' => null,
    'value' => '1',
    'checked' => false,
    'disabled' => false,
    'hint' => null,
])

@php
    $id = $attributes->get('id', $name);
    $isChecked = old($name, $checked);
@endphp
<div {{ $attributes->only('class') }}>
    <label for="{{ $id }}" class="flex items-start gap-3 cursor-pointer group">
        <input
     type="checkbox"
            name="{{ $name }}"
            id="{{ $id }}"
        value="{{ $value }}"
        {{ $isChecked ? 'checked' : '' }}
        {{ $disabled ? 'disabled' : '' }}
            class="mt-0.5 h-4 w-4 rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 dark:bg-gray-700 dark:checked:bg-blue-600 transition-colors"
        />
        <div>
        @if($label)
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300 group-hover:text-gray-900 dark:group-hover:text-gray-100">
                {{ $label }}
            </span>
        @endif
            {{ $slot }}
        @if($hint)
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $hint }}</p>
        @endif
        </div>
    </label>

    @error($name)
        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>
