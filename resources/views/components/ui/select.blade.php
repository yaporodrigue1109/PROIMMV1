@props([
    'name',
    'id'          => null,
    'options'     => [],
    'value'       => null,
    'placeholder' => null,
])

@php
    $inputId = $id ?? $name;

    // Normalise les options — accepte objets Eloquent ou tableaux
    $normalizedOptions = collect($options)->mapWithKeys(function ($label, $val) {
        if (is_object($label)) {
            return [$label->id => $label->name];
        }
        return [$val => $label];
    })->toArray();

    $selectedLabel = $normalizedOptions[$value] ?? $placeholder ?? 'Sélectionner';
@endphp

<select id="{{ $inputId }}" name="{{ $name }}" {{ $attributes->merge(['class' => 'ui-native-select']) }}>
    @if($placeholder)
        <option value="" @selected($value === null || $value === '')>{{ $placeholder }}</option>
    @endif

    @foreach($normalizedOptions as $val => $label)
        <option value="{{ $val }}" @selected((string)$value === (string)$val)>
            {{ $label }}
        </option>
    @endforeach
</select>

<div class="ui-dropdown ui-select-dropdown" data-select-target="{{ $inputId }}">
    <button class="ui-dropdown-toggle" type="button">
        <span>{{ $selectedLabel }}</span>
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/>
        </svg>
    </button>

    <div class="ui-dropdown-menu">
        @if($placeholder)
            <button
                    class="ui-dropdown-item {{ $value === null || $value === '' ? 'is-selected' : '' }}"
                    type="button"
                    data-value="">
                {{ $placeholder }}
            </button>
        @endif

        @foreach($normalizedOptions as $val => $label)
            <button
                    class="ui-dropdown-item {{ (string)$value === (string)$val ? 'is-selected' : '' }}"
                    type="button"
                    data-value="{{ $val }}">
                {{ $label }}
            </button>
        @endforeach
    </div>
</div>