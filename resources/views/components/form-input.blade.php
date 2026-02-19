@props([
    'type' => 'text',
    'name',
    'label' => null,
    'value' => '',
    'placeholder' => '',
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'hint' => null,
    'options' => [],
])

@php
    $id = $attributes->get('id', $name);
    $hasError = $errors->has($name);
    $inputClasses = 'w-full px-3 py-2.5 rounded-lg text-sm border transition-colors duration-150 ' .
        ($hasError
            ? 'border-red-500 dark:border-red-400 focus:ring-red-500 focus:border-red-500'
            : 'border-gray-300 dark:border-gray-600 focus:ring-blue-500 focus:border-blue-500') .
        ' bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100' .
        ' placeholder-gray-400 dark:placeholder-gray-500' .
        ' focus:outline-none focus:ring-2 focus:ring-offset-0';
@endphp

<div {{ $attributes->class(['']) }}>
    @if($label)
        <label for="{{ $id }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif

    @if($type === 'textarea')
        <textarea
            name="{{ $name }}"
            id="{{ $id }}"
            placeholder="{{ $placeholder }}"
            {{ $required ? 'required' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            {{ $readonly ? 'readonly' : '' }}
            class="{{ $inputClasses }} resize-y min-h-[80px]"
            rows="3"
        >{{ old($name, $value) }}</textarea>
    @elseif($type === 'select')
        <select
            name="{{ $name }}"
            id="{{ $id }}"
            {{ $required ? 'required' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            class="{{ $inputClasses }}"
        >
            @if($placeholder)
                <option value="">{{ $placeholder }}</option>
            @endif
            @foreach($options as $optValue => $optLabel)
                <option value="{{ $optValue }}" {{ old($name, $value) == $optValue ? 'selected' : '' }}>
                    {{ $optLabel }}
                </option>
            @endforeach
        </select>
    @else
        <input
            type="{{ $type }}"
            name="{{ $name }}"
            id="{{ $id }}"
            value="{{ old($name, $value) }}"
            placeholder="{{ $placeholder }}"
            {{ $required ? 'required' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            {{ $readonly ? 'readonly' : '' }}
            class="{{ $inputClasses }}"
        />
    @endif

    @if($hint && !$hasError)
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $hint }}</p>
    @endif

    @error($name)
        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>
