@props([
    'name',
    'id' => null,
    'type' => 'text',
    'value' => null,
    'placeholder' => null,
    'required' => false,
])

@php
    $inputId = $id ?? $name;
@endphp

<input
        id="{{ $inputId }}"
        name="{{ $name }}"
        type="{{ $type }}"
        @if($type !== 'file' && !is_null($value))
            value="{{ $value }}"
        @endif
        @if(!is_null($placeholder))
            placeholder="{{ $placeholder }}"
        @endif
        @if($required)
            required
        @endif
        {{ $attributes->merge(['class' => 'ui-input']) }}
>
