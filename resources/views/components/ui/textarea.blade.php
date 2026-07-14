@props([
    'name',
    'id' => null,
    'value' => null,
    'rows' => 3,
    'placeholder' => null,
])

@php
    $inputId = $id ?? $name;
@endphp

<textarea
        id="{{ $inputId }}"
        name="{{ $name }}"
        rows="{{ $rows }}"
        @if(!is_null($placeholder))
            placeholder="{{ $placeholder }}"
    @endif
        {{ $attributes->merge(['class' => 'ui-textarea']) }}
>{{ $value }}</textarea>
