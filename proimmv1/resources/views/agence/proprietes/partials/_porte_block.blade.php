

{{--
    Partial : bloc porte avec tarif + équipements.
    Variables : $bi, $pi, $typesPorte, $equipements, $porte (null = création)
--}}
@php
    $tarif  = $porte?->tarifActif ?? null;
    $prefix = "batiments[$bi][portes][$pi]";
    $old    = fn($k, $def = '') => old("batiments.$bi.portes.$pi.$k", $def);
    $porteEquipements = $porte?->equipements?->pluck('id')->toArray() ?? [];
@endphp

<div class="porte-block">

    {{-- En-tête porte --}}
    <div class="porte-block-header">
        <span class="porte-label">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
            </svg>
            Porte {{ intval($pi) + 1 }}
        </span>
        <button type="button" class="btn-remove-porte" title="Supprimer cette porte">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
            Supprimer
        </button>
    </div>

    {{-- Champs --}}
    <div class="form-grid form-grid-3">

        {{-- ── Identité ── --}}
        <div class="form-field">
            <label>N° porte *</label>
            <input type="text"
                   name="{{ $prefix }}[numero_porte]"
                   value="{{ $old('numero_porte', $porte?->numero_porte ?? '') }}"
                   placeholder="01, A-01…"
                   required />
        </div>

        <div class="form-field">
            <label>Type de porte *</label>
            <select name="{{ $prefix }}[type_porte_id]" required>
                <option value="">Sélectionner…</option>
                @foreach($typesPorte as $tp)
                    <option value="{{ $tp->type_porte_id }}"
                            @selected($old('type_porte_id', $porte?->type_porte_id) == $tp->type_porte_id)>
                        {{ $tp->libelle }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-field">
            <label>Superficie (m²)</label>
            <input type="number" step="0.01"
                   name="{{ $prefix }}[superficie_m2]"
                   value="{{ $old('superficie_m2', $porte?->superficie_m2 ?? '') }}"
                   placeholder="Ex: 25" />
        </div>

        <div class="form-field">
            <label>Étage</label>
            <input type="number"
                   name="{{ $prefix }}[etage]"
                   value="{{ $old('etage', $porte?->etage ?? 0) }}"
                   min="0" />
        </div>

        {{-- ── Séparateur Tarif ── --}}
        <div class="porte-tarif-divider">Tarif mensuel</div>

        <div class="form-field">
            <label>Loyer (FCFA) *</label>
            <input type="number" min="0"
                   name="{{ $prefix }}[tarif][mt_loyer]"
                   value="{{ $old('tarif.mt_loyer', $tarif?->mt_loyer ?? '') }}"
                   placeholder="50 000"
                   required />
        </div>

        <div class="form-field">
            <label>Nombre Caution (mois) </label>
            <input type="number" min="0"
                   name="{{ $prefix }}[tarif][caution]"
                   value="{{ $old('tarif.caution', $tarif?->caution ?? 0) }}" />
        </div>

        <div class="form-field">
            <label>Nombre Avance (mois)</label>
            <input type="number"
                   name="{{ $prefix }}[tarif][avance]"
                   value="{{ $old('tarif.avance', $tarif?->avance ?? 0) }}" />
        </div>

        <div class="form-field">
            <label>Nombre agence (mois)</label>
            <input type="number" min="0"
                   name="{{ $prefix }}[tarif][agence]"
                   value="{{ $old('tarif.agence', $tarif?->agence ?? 0) }}" />
        </div>

        <div class="form-field">
            <label>Caution CIE</label>
            <input type="number" min="0"
                   name="{{ $prefix }}[tarif][mt_caution_cie]"
                   value="{{ $old('tarif.mt_caution_cie', $tarif?->mt_caution_cie ?? 0) }}" />
        </div>

        <div class="form-field">
            <label>Caution SODECI</label>
            <input type="number" min="0"
                   name="{{ $prefix }}[tarif][mt_caution_sodeci]"
                   value="{{ $old('tarif.mt_caution_sodeci', $tarif?->mt_caution_sodeci ?? 0) }}" />
        </div>

        <div class="form-field">
            <label>Autre frais</label>
            <input type="number" min="0"
                   name="{{ $prefix }}[tarif][mt_autre_frais]"
                   value="{{ $old('tarif.mt_autre_frais', $tarif?->mt_autre_frais ?? 0) }}" />
        </div>

{{--        <div class="form-field">--}}
{{--            <label>Date d'effet</label>--}}
{{--            <input type="date"--}}
{{--                   name="{{ $prefix }}[tarif][date_effet]"--}}
{{--                   value="{{ $old('tarif.date_effet', $tarif?->date_effet?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" />--}}
{{--        </div>--}}

    </div>

    {{-- ── Équipements (chips horizontaux) ── --}}
    @if(!empty($equipements) && count($equipements) > 0)
        <div class="porte-equipements">
            <div class="porte-equip-label">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l5.653-4.655m5.585-5.342C17.21 3.68 19.687 2.25 22 2.25c0 2.313-1.43 4.79-3.343 6.707a8.473 8.473 0 0 1-1.208.766M15.126 12.2l-3.03 2.496" />
                </svg>
                Équipements
            </div>
            <div class="porte-equip-list">
                @foreach($equipements as $item)
                    <label class="equip-chip">
                        <input
                                type="checkbox"
                                name="{{ $prefix }}[equipements][]"
                                value="{{ $item->id }}"
                                @checked(in_array($item->id, $porteEquipements))
                        />
                        <span>{{ $item->name }}</span>
                    </label>
                @endforeach
            </div>
        </div>
    @endif

</div>

@once
    <style>
        /* ══════════════════════════════════════════════
           Équipements porte — chips horizontaux
        ══════════════════════════════════════════════ */
        .porte-equipements {
            border-top: 1px solid var(--color-border, #e5e7eb);
            padding: .6rem .75rem .75rem;
        }

        .porte-equip-label {
            display: flex;
            align-items: center;
            gap: .3rem;
            font-size: .65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .07em;
            color: var(--color-text-light, #9ca3af);
            margin-bottom: .5rem;
        }
        .porte-equip-label svg {
            width: .75rem;
            height: .75rem;
        }

        /* Ligne de chips */
        .porte-equip-list {
            display: flex;
            flex-wrap: wrap;
            flex-direction: row;   /* horizontal */
            gap: .35rem;
        }

        /* Chip individuel */
        .equip-chip {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            padding: .2rem .6rem;
            border: 1px solid var(--color-border, #e5e7eb);
            border-radius: 999px;          /* pill */
            font-size: .75rem;
            font-weight: 500;
            color: var(--color-text-light, #6b7280);
            background: var(--color-bg-subtle, #f9fafb);
            cursor: pointer;
            transition: all .12s ease;
            user-select: none;
            white-space: nowrap;
        }
        .equip-chip:hover {
            border-color: var(--color-primary, #2563eb);
            color: var(--color-primary, #2563eb);
            background: var(--color-primary-light, #eff6ff);
        }

        /* Checkbox cachée mais fonctionnelle */
        .equip-chip input[type="checkbox"] {
            width: .75rem;
            height: .75rem;
            accent-color: var(--color-primary, #2563eb);
            cursor: pointer;
            margin: 0;
            flex-shrink: 0;
        }

        /* État coché */
        .equip-chip:has(input:checked) {
            border-color: var(--color-primary, #2563eb);
            background: var(--color-primary-light, #eff6ff);
            color: var(--color-primary, #2563eb);
            font-weight: 600;
            box-shadow: 0 0 0 2px rgba(37,99,235,.15);
        }
    </style>
@endonce