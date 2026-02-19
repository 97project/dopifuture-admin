@props([
    'name',
    'label' => null,
    'value' => '',
    'placeholder' => '',
    'required' => false,
    'disabled' => false,
    'options' => [],
    'multiple' => false,
])

@php
    $id = $attributes->get('id', $name);
    $hasError = $errors->has($name);
    $selectClasses = 'w-full px-3 py-2.5 rounded-lg text-sm border transition-colors duration-150 ' .
        ($hasError
            ? 'border-red-500 dark:border-red-400 focus:ring-red-500 focus:border-red-500'
            : 'border-gray-300 dark:border-gray-600 focus:ring-blue-500 focus:border-blue-500') .
        ' bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100' .
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

    <select
        name="{{ $name }}{{ $multiple ? '[]' : '' }}"
        id="{{ $id }}"
        {{ $required ? 'required' : '' }}
        {{ $disabled ? 'disabled' : '' }}
        {{ $multiple ? 'multiple' : '' }}
        class="{{ $selectClasses }}"
    >
        @if($placeholder && !$multiple)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach($options as $optValue => $optLabel)
            <option value="{{ $optValue }}" {{ collect(old($name, $value))->contains($optValue) ? 'selected' : '' }}>
                {{ $optLabel }}
            </option>
        @endforeach
        {{ $slot }}
    </select>

    @error($name)
        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>
