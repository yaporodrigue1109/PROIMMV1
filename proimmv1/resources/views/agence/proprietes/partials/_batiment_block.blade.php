
<div class="batiment-card-header">
    <div class="batiment-info">
        <span class="batiment-icon">🏢</span>
        <input type="text"
               name="batiments[{{ $bi }}][name]"
               value="{{ old("batiments.$bi.name", $batiment->name ?? 'Bâtiment ' . (intval($bi) + 1)) }}"
               class="batiment-name-input"
               placeholder="Nom du bâtiment"
               required />
    </div>
    <div style="display:flex;align-items:center;gap:.5rem;">
                    <button type="button" class="btn-add-porte btn btn-outline btn-sm" data-bi="{{ $bi }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                        Ajouter une porte
                    </button>
{{--        <button type="button" class="btn-remove-batiment" title="Supprimer ce bâtiment">--}}
{{--            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">--}}
{{--                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />--}}
{{--            </svg>--}}
{{--            Supprimer--}}
{{--        </button>--}}

                    <button type="button" class="btn-remove-batiment action-btn danger" title="Supprimer ce bâtiment">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                        Supprimer ce bâtiment
                    </button>
    </div>
</div>

{{-- Métadonnées bâtiment --}}
<div class="batiment-meta">
    <div class="form-field">
        <label>Nombre d'étages</label>
        <input type="number"
               name="batiments[{{ $bi }}][nbre_etages]"
               value="{{ old("batiments.$bi.nbre_etages", $batiment->nbre_etages ?? 0) }}"
               min="0" max="100" />
    </div>
    <div class="form-field" style="flex:2;">
        <label>Description du bâtiment</label>
        <input type="text"
               name="batiments[{{ $bi }}][description]"
               value="{{ old("batiments.$bi.description", $batiment->description ?? '') }}"
               placeholder="Optionnel" />
    </div>
</div>

{{-- Portes du bâtiment --}}
<div class="portes-container" id="portes-container-{{ $bi }}">
    @php $portes = $batiment?->portes ?? collect(); @endphp
    @if($portes->isEmpty())
        @include('agence.proprietes.partials._porte_block', [
            'bi'          => $bi,
            'pi'          => 0,
            'typesPorte'  => $typesPorte,
            'equipements' => $equipements ?? collect(),
            'porte'       => null,
        ])
    @else
        @foreach($portes as $pi => $porte)
            @include('agence.proprietes.partials._porte_block', [
                'bi'          => $bi,
                'pi'          => $pi,
                'typesPorte'  => $typesPorte,
                'equipements' => $equipements ?? collect(),
                'porte'       => $porte,
            ])
        @endforeach
    @endif
</div>

{{-- Bouton ajouter porte --}}
<div class="btn-add-porte-wrap">
    <button type="button" class="btn-add-porte" data-bi="{{ $bi }}">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
        </svg>
        Ajouter une porte
    </button>
</div>