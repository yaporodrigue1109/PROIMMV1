@props([
    'name',
    'id' => null,
    'accept' => null,
])

@php
    $inputId = $id ?? $name;
@endphp

<input
        id="{{ $inputId }}"
        name="{{ $name }}"
        type="file"
        @if(!is_null($accept))
            accept="{{ $accept }}"
        @endif
        {{ $attributes->merge(['class' => 'ui-file']) }}
>
