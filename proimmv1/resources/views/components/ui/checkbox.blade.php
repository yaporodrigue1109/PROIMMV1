@props([
    'name',
    'id' => null,
    'checked' => false,
    'value' => '1',
    'label' => null,
    'inputAttributes' => [],
])

@php
    $inputId = $id ?? $name;

    $inputAttributesString = collect($inputAttributes)
        ->map(function ($attributeValue, $attribute) {
            if (is_bool($attributeValue)) {
                return $attributeValue ? $attribute : null;
            }

            return $attribute . '="' . e($attributeValue) . '"';
        })
        ->filter()
        ->implode(' ');
@endphp

<label {{ $attributes->merge(['class' => 'option-item']) }} for="{{ $inputId }}">
    <input
            id="{{ $inputId }}"
            name="{{ $name }}"
            type="checkbox"
            value="{{ $value }}"
            @checked($checked)
            {!! $inputAttributesString ? ' ' . $inputAttributesString : '' !!}
    >
    <span>{{ $label ?? $slot }}</span>
</label>
