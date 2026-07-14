@props([
    'name',
    'value' => null,
    'placeholder' => 'Sélectionner une date',
])

@php
    $inputValue = $value ?: '';
    $isReadonly = $attributes->has('readonly');
@endphp

<div class="ui-date-picker" data-date-picker data-date-target="{{ $name }}" @if($isReadonly) data-readonly="true" @endif>
    <input
            id="{{ $name }}"
            name="{{ $name }}"
            type="hidden"
            value="{{ $inputValue }}"
            {{ $attributes->class(['ui-date-native']) }}
    >

    <button class="ui-date-toggle" type="button" @disabled($isReadonly)>
        <span class="ui-date-label" data-date-label>{{ $inputValue ? \Carbon\Carbon::parse($inputValue)->translatedFormat('d F Y') : $placeholder }}</span>
        <svg class="ui-date-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3.75 8.25h16.5M5.25 5.25h13.5A1.5 1.5 0 0120.25 6.75v12A1.5 1.5 0 0118.75 20.25H5.25A1.5 1.5 0 013.75 18.75v-12A1.5 1.5 0 015.25 5.25z"/>
        </svg>
    </button>

    <div class="ui-date-panel" data-date-panel>
        <div class="ui-date-header">
            {{-- Nouveau bouton Année précédente --}}
            <button type="button" class="ui-date-nav" data-year-prev aria-label="Année précédente" title="Année précédente">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>
                </svg>
            </button>
            {{-- Navigation Mois précédent --}}
            <button type="button" class="ui-date-nav" data-date-prev aria-label="Mois précédent">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
                </svg>
            </button>

            {{-- Affichage Mois et Année avec sélecteur d'année potentiel --}}
            <div class="ui-date-month-year">
                <strong data-date-month></strong>
                {{-- Optionnel : Ajouter un selecteur d'année déroulant pour une navigation plus rapide --}}
                {{-- <select class="ui-date-year-select" data-year-select></select> --}}
            </div>

            {{-- Navigation Mois suivant --}}
            <button type="button" class="ui-date-nav" data-date-next aria-label="Mois suivant">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                </svg>
            </button>
            {{-- Nouveau bouton Année suivante --}}
            <button type="button" class="ui-date-nav" data-year-next aria-label="Année suivante" title="Année suivante">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
                </svg>
            </button>
        </div>

        <div class="ui-date-weekdays">
            @foreach(['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'] as $day)
                <span>{{ $day }}</span>
            @endforeach
        </div>

        <div class="ui-date-grid" data-date-grid></div>
    </div>
</div>