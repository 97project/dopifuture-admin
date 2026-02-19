@props([
    'name',
    'label' => null,
    'value' => '',
    'placeholder' => '',
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'rows' => 3,
    'hint' => null,
])

@php
    $id = $attributes->get('id', $name);
    $hasError = $errors->has($name);
    $textareaClasses = 'w-full px-3 py-2.5 rounded-lg text-sm border transition-colors duration-150 resize-y min-h-[80px] ' .
        ($hasError
            ? 'border-red-500 dark:border-red-400 focus:ring-red-500 focus:border-red-500'
            : 'border-gray-300 dark:border-gray-600 focus:ring-blue-500 focus:border-blue-500') .
        ' bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100' .
        ' placeholder-gray-400 dark:placeholder-gray-500' .
        ' focus:outline-none focus:ring-2 focus:ring-offset-0';
@endphp

<div {{ $attributes->only('class') }}>
    @if($label)
        <label for="{{ $id }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif

    <textarea
        name="{{ $name }}"
        id="{{ $id }}"
        placeholder="{{ $placeholder }}"
        {{ $required ? 'required' : '' }}
        {{ $disabled ? 'disabled' : '' }}
        {{ $readonly ? 'readonly' : '' }}
        class="{{ $textareaClasses }}"
        rows="{{ $rows }}"
    >{{ old($name, $value) }}</textarea>

    @if($hint && !$hasError)
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $hint }}</p>
    @endif

    @error($name)
        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>
