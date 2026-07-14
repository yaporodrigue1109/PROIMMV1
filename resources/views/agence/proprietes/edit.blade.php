@extends('agence.layouts.app')

@section('title', 'Modifier · ' . $propriete->reference)

@section('content')
    <div class="page">

        {{-- Header --}}
        <div class="page-header">
            <div class="page-header-copy">
                <div class="page-heading">
                    <a href="{{ route('agence.proprietes.show', $propriete->propriete_id) }}" class="back-link">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                        </svg>
                    </a>
                    <h2>Modifier · {{ $propriete->reference }}</h2>
                </div>
                <p>Modifiez les informations de la propriété, ses bâtiments et ses portes.</p>
            </div>
        </div>

        {{-- Erreurs de validation --}}
        @if($errors->any())
            <div class="alert alert-danger u-mb-md">
                <div class="alert-header">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                    </svg>
                    <strong>Veuillez corriger les erreurs suivantes :</strong>
                </div>
                <ul class="u-list-none u-m-0">
                    @foreach($errors->all() as $error)
                        <li>• {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('agence.proprietes.update', $propriete->propriete_id) }}"
              method="POST" id="form-propriete" enctype="multipart/form-data">
            @csrf @method('PUT')

            {{-- ═══ ÉTAPE 1 — Informations générales ═══ --}}
            <div class="create-section" data-section="1">
                <div class="create-section-aside">
                    <div class="create-step-badge">1</div>
                    <div>
                        <h3 class="create-section-title">Informations générales</h3>
                        <p class="create-section-hint">Identité et localisation de la propriété.</p>
                    </div>
                </div>
                <div class="form-card create-section-body">
                    <div class="form-card-body">
                        <div class="form-grid form-grid-3">

                            <div class="form-field form-field-wide">
                                <label for="proprietaire_id">Propriétaire *</label>
                                <select name="proprietaire_id" id="proprietaire_id" required
                                        onchange="getRequest('{{ url('/') }}/admin/list/lotByProprietaire?parent_id='+this.value,'lot_id','select',this.value)">
                                    <option value="">Sélectionner un propriétaire</option>
                                    @foreach($proprietaires as $p)
                                        <option value="{{ $p->proprietaire_id }}"
                                                @selected(old('proprietaire_id', $propriete->proprietaire_id) == $p->proprietaire_id)>
                                            {{ $p->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('proprietaire_id')<span class="form-error">{{ $message }}</span>@enderror
                            </div>

                            <div class="form-field">
                                <label for="lot_id">Lot / Zone</label>
                                <select name="lot_id" id="lot_id">
                                    <option value="">Aucun lot spécifique</option>
                                    @foreach($lots->where('proprietaire_id',$propriete->proprietaire_id)  as $lot)


                                        <option value="{{ $lot->propreietaire_lot_id }}"
                                                @selected(old('lot_id', $propriete->lot_id) == $lot->propreietaire_lot_id)
                                                >
                                            {{ $lot->name }} – {{ $lot->adresse  }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('lot_id')<span class="form-error">{{ $message }}</span>@enderror
                            </div>

                            <div class="form-field form-field-wide">
                                <label for="video_url">Vidéo</label>
                                <input type="file" name="video_url" id="video_url"
                                       accept="video/*" />
                                @if($propriete->videos_url)
                                    <p class="form-hint">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:12px;height:12px;">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m15.75 10.5 4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25h-9A2.25 2.25 0 0 0 2.25 7.5v9a2.25 2.25 0 0 0 2.25 2.25Z" />
                                        </svg>
                                        Une vidéo est déjà enregistrée. Uploader pour remplacer.
                                    </p>
                                @endif
                                @error('video_url')<span class="form-error">{{ $message }}</span>@enderror
                            </div>

                            <div class="form-field form-field-wide">
                                <label for="description">Description</label>
                                <textarea name="description" id="description" rows="3"
                                          placeholder="Décrivez la propriété (superficie, environnement, particularités…)">{{ old('description', $propriete->description) }}</textarea>
                                @error('description')<span class="form-error">{{ $message }}</span>@enderror
                            </div>

                            <div class="form-field">
                                <label class="checkbox-label">
                                    <input type="hidden" name="is_allocation" value="0" />
                                    <input type="checkbox" name="is_allocation" value="1"
                                            @checked(old('is_allocation', $propriete->is_allocation)) />
                                    <span>Mode allocation</span>
                                </label>
                                <p class="form-hint">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:12px;height:12px;">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z" />
                                    </svg>
                                    Le loyer est versé même si la porte est vacante
                                </p>
                            </div>

                            <div class="form-field">
                                <label class="checkbox-label">
                                    <input type="hidden" name="is_actif" value="0" />
                                    <input type="checkbox" name="is_actif" value="1"
                                            @checked(old('is_actif', $propriete->is_actif)) />
                                    <span>Propriété active</span>
                                </label>
                                <p class="form-hint">Décochez pour désactiver sans supprimer.</p>
                            </div>

                        </div>
                    </div>

                    {{-- ── Proximités ── --}}
                    @if(!empty($proximites) && count($proximites) > 0)
                        <div class="proximites-section">
                            <div class="proximites-header">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                                </svg>
                                <span>Proximités</span>
                                <span class="proximites-hint">Indiquez la distance en mètres (laisser vide si absent)</span>
                            </div>
                            <div class="proximites-grid">
                                @foreach($proximites as $item)
                                    @php
                                        // Récupérer les proximités existantes de la propriété
                                        $proprieteProximites = json_decode($propriete->prossimites ?? '[]', true) ?? [];
                                     //dd($proprieteProximites);
                                        $valeur = old('proximites.' . $item->id, $proprieteProximites[$item->id] ?? '');
                                    @endphp
                                    <div class="proximite-item">
                                        <div class="proximite-icon">
                                            @switch($item->categorie ?? '')
                                                @case('education') 🏫 @break
                                                @case('sante')     🏥 @break
                                                @case('commerce')  🛒 @break
                                                @case('transport') 🚌 @break
                                                @case('loisirs')   🎭 @break
                                                @default           📍
                                            @endswitch
                                        </div>
                                        <div class="proximite-body">
                                            <label class="proximite-label" for="prox_{{ $item->id }}">
                                                {{ $item->name }}
                                            </label>
                                            <div class="proximite-input-wrap">
                                                <input type="number"
                                                       id="prox_{{ $item->id }}"
                                                       name="proximites[{{ $item->id }}]"
                                                       min="0"
                                                       step="0.01"
                                                       placeholder="Distance en mètres"
                                                       value="{{ $valeur }}"
                                                       class="proximite-input" />
                                                <span class="proximite-unit">m</span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                </div>
            </div>

            {{-- ═══ ÉTAPE 2 — Bâtiments & portes ═══ --}}
            <div class="create-section" data-section="2">
                <div class="create-section-aside">
                    <div class="create-step-badge">2</div>
                    <div>
                        <h3 class="create-section-title">Bâtiments & portes</h3>
                        <p class="create-section-hint">
                            Modifiez les bâtiments et portes existants.
                            Changer le tarif crée un historique — l'ancien est conservé.
                        </p>
                    </div>
                </div>

                <div class="create-section-body">
                    <div id="batiments-container">
                        @foreach($propriete->batiments as $bi => $batiment)
                            <div class="batiment-block" data-index="{{ $bi }}">

                                {{-- ID caché --}}
                                <input type="hidden"
                                       name="batiments[{{ $bi }}][batiment_id]"
                                       value="{{ $batiment->batiment_id }}" />

                                {{-- Header bâtiment --}}
                                <div class="batiment-card-header">
                                    <div class="batiment-info">
                                        <span class="batiment-icon">🏢</span>
                                        <input type="text"
                                               name="batiments[{{ $bi }}][name]"
                                               value="{{ old("batiments.$bi.name", $batiment->name) }}"
                                               class="batiment-name-input"
                                               placeholder="Nom du bâtiment"
                                               required />
                                    </div>
                                    <div style="display:flex;align-items:center;gap:.6rem;">
                                        <button type="button" class="btn-add-porte btn btn-outline btn-sm" data-bi="{{ $bi }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                                            Ajouter une porte
                                        </button>

                                        <button type="button" class="btn-remove-batiment action-btn danger" title="Supprimer ce bâtiment">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                                            Supprimer ce bâtiment
                                        </button>
                                    </div>
                                </div>

                                {{-- Description bâtiment --}}
                                <div class="form-card create-section-body">
                                    <div class="form-card-body">
                                <div class="form-grid form-grid-3">


                                    <div class="form-field  form-field-wide" style="flex:2;">
                                        <label>Description du bâtiment</label>
                                        <input type="text"
                                               name="batiments[{{ $bi }}][description]"
                                               value="{{ old("batiments.$bi.description", $batiment->description ?? '') }}"
                                               placeholder="Optionnel" />
                                    </div>

                                    <div class="form-field">
                                        <label>Nombre d'étages</label>
                                        <input type="number"
                                               name="batiments[{{ $bi }}][nbre_etages]"
                                               value="{{ old("batiments.$bi.nbre_etages", $batiment->nbre_etages ?? 0) }}"
                                               min="0" max="100" />
                                    </div>

                                </div>
                                </div>
                                </div>


                                {{-- Portes --}}
                                <div class="portes-container" id="portes-container-{{ $bi }}">
                                    @foreach($batiment->portes->where('is_actif', true) as $pi => $porte)
                                        @php $tarif = $porte->tarifActif; @endphp
                                        <div class="porte-block">

                                            <input type="hidden"
                                                   name="batiments[{{ $bi }}][portes][{{ $pi }}][porte_id]"
                                                   value="{{ $porte->porte_id }}" />

                                            <div class="porte-block-header">
                                    <span class="porte-label">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                                        </svg>
                                        Porte {{ $pi + 1 }}
                                        @if($porte->is_occupe)
                                            <span style="font-size:.62rem;font-weight:700;padding:.1rem .4rem;border-radius:999px;background:#fee2e2;color:#dc2626;text-transform:none;letter-spacing:0;">Occupée</span>
                                        @else
                                            <span style="font-size:.62rem;font-weight:700;padding:.1rem .4rem;border-radius:999px;background:#d1fae5;color:#059669;text-transform:none;letter-spacing:0;">Libre</span>
                                        @endif
                                    </span>
                                                <button type="button" class="btn-remove-porte" title="Supprimer">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                                    </svg>
                                                    Supprimer
                                                </button>
                                            </div>

                                            <div class="form-grid form-grid-3" style="padding:.75rem;">

                                                {{-- Identité --}}
                                                <div class="form-field">
                                                    <label>N° porte *</label>
                                                    <input type="text"
                                                           name="batiments[{{ $bi }}][portes][{{ $pi }}][numero_porte]"
                                                           value="{{ old("batiments.$bi.portes.$pi.numero_porte", $porte->numero_porte) }}"
                                                           required />
                                                </div>

                                                <div class="form-field">
                                                    <label>Type *</label>
                                                    <select name="batiments[{{ $bi }}][portes][{{ $pi }}][type_porte_id]" required>
                                                        <option value="">—</option>
                                                        @foreach($typesPorte as $tp)
                                                            <option value="{{ $tp->type_porte_id }}"
                                                                    @selected(old("batiments.$bi.portes.$pi.type_porte_id", $porte->type_porte_id) == $tp->type_porte_id)>
                                                                {{ $tp->libelle }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="form-field">
                                                    <label>Superficie (m²)</label>
                                                    <input type="number" step="0.01"
                                                           name="batiments[{{ $bi }}][portes][{{ $pi }}][superficie_m2]"
                                                           value="{{ old("batiments.$bi.portes.$pi.superficie_m2", $porte->superficie_m2) }}"
                                                           placeholder="25" />
                                                </div>

                                                <div class="form-field">
                                                    <label>Étage</label>
                                                    <input type="number"
                                                           name="batiments[{{ $bi }}][portes][{{ $pi }}][etage]"
                                                           value="{{ old("batiments.$bi.portes.$pi.etage", $porte->etage) }}"
                                                           min="0" />
                                                </div>

                                                {{-- Séparateur tarif --}}
                                                <div class="porte-tarif-divider">
                                                    Tarif mensuel
                                                    @if($tarif)
                                                        <span style="font-size:.62rem;font-weight:500;color:#10b981;text-transform:none;letter-spacing:0;background:#d1fae5;padding:.1rem .4rem;border-radius:999px;">
                                                depuis le {{ $tarif->date_effet?->format('d/m/Y') }}
                                            </span>
                                                    @endif
                                                </div>

                                                <div class="form-field">
                                                    <label>Loyer (FCFA) *</label>
                                                    <input type="number"
                                                           name="batiments[{{ $bi }}][portes][{{ $pi }}][tarif][mt_loyer]"
                                                           value="{{ old("batiments.$bi.portes.$pi.mt_loyer", $porte?->mt_loyer ?? '') }}"
                                                           required />
                                                </div>

                                                <div class="form-field">
                                                    <label>Caution</label>
                                                    <input type="number"
                                                           name="batiments[{{ $bi }}][portes][{{ $pi }}][tarif][caution]"
                                                           value="{{ old("batiments.$bi.portes.$pi.caution", $porte?->caution ?? 0) }}" />
                                                </div>

                                                <div class="form-field">
                                                    <label>Avance</label>
                                                    <input type="number"
                                                           name="batiments[{{ $bi }}][portes][{{ $pi }}][tarif][avance]"
                                                           value="{{ old("batiments.$bi.portes.$pi.avance", $porte?->avance ?? 0) }}" />
                                                </div>

                                                <div class="form-field">
                                                    <label>Frais agence</label>
                                                    <input type="number"
                                                           name="batiments[{{ $bi }}][portes][{{ $pi }}][tarif][agence]"
                                                           value="{{ old("batiments.$bi.portes.$pi.agence", $porte?->agence ?? 0) }}" />
                                                </div>

                                                <div class="form-field">
                                                    <label>Caution CIE</label>
                                                    <input type="number"
                                                           name="batiments[{{ $bi }}][portes][{{ $pi }}][tarif][mt_caution_cie]"
                                                           value="{{ old("batiments.$bi.portes.$pi.mt_caution_cie", $porte?->mt_caution_cie ?? 0) }}" />
                                                </div>

                                                <div class="form-field">
                                                    <label>Caution SODECI</label>
                                                    <input type="number"
                                                           name="batiments[{{ $bi }}][portes][{{ $pi }}][tarif][mt_caution_sodeci]"
                                                           value="{{ old("batiments.$bi.portes.$pi.mt_caution_sodeci", $porte?->mt_caution_sodeci ?? 0) }}" />
                                                </div>

                                                <div class="form-field">
                                                    <label>Autre frais</label>
                                                    <input type="number" min="0"
                                                           name="batiments[{{ $bi }}][portes][{{ $pi }}][tarif][mt_autre_frais]"
                                                           value="{{ old("batiments.$bi.portes.$pi.mt_autre_frais", $porte?->mt_autre_frais ?? 0) }}" />
                                                </div>



                                            </div>



                                            {{-- Équipements --}}
                                            @if(!empty($equipements) && count($equipements) > 0)
                                                @php $porteEquipements = json_decode($porte->equipements,true) ?? []; @endphp
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
                                                                <input type="checkbox"
                                                                       name="batiments[{{ $bi }}][portes][{{ $pi }}][equipements][]"
                                                                       value="{{ $item->id }}"
                                                                        @checked(in_array($item->id, $porteEquipements)) />
                                                                <span>{{ $item->name }}</span>
                                                            </label>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif

                                        </div>{{-- /.porte-block --}}
                                    @endforeach
                                </div>

                                <div class="btn-add-porte-wrap">
                                    <button type="button" class="btn-add-porte" data-bi="{{ $bi }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                        </svg>
                                        Ajouter une porte
                                    </button>
                                </div>

                            </div>{{-- /.batiment-block --}}
                        @endforeach
                    </div>

                    <div class="add-batiment-wrapper">
                        <button type="button" class="btn-add-batiment" id="btn-add-batiment">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            Ajouter un bâtiment
                        </button>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="form-actions">
                <a href="{{ route('agence.proprietes.show', $propriete->propriete_id) }}" class="btn btn-outline">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                    Annuler
                </a>
                <div class="actions-right">
                    <button type="submit" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        Enregistrer les modifications
                    </button>
                </div>
            </div>

        </form>
    </div>

    {{-- ═══ Templates JS (nouveaux bâtiments / nouvelles portes) ═══ --}}
    <template id="tpl-batiment">
        <div class="batiment-block" data-index="__BI__">
            @include('agence.proprietes.partials._batiment_block', [
                'bi'          => '__BI__',
                'typesPorte'  => $typesPorte,
                'equipements' => $equipements,
                'batiment'    => null,
            ])
        </div>
    </template>

    <template id="tpl-porte">
        @include('agence.proprietes.partials._porte_block', [
            'bi'          => '__BI__',
            'pi'          => '__PI__',
            'typesPorte'  => $typesPorte,
            'equipements' => $equipements,
            'porte'       => null,
        ])
    </template>

    @push('styles')
        <style>
            /* ═══════════════════════════════════════════
               Variables
            ═══════════════════════════════════════════ */
            :root {
                --color-primary: #2563eb;
                --color-primary-dark: #1e40af;
                --color-primary-light: #eff6ff;
                --color-success: #10b981;
                --color-success-light: #d1fae5;
                --color-danger: #ef4444;
                --color-danger-light: #fee2e2;
                --color-text: #111827;
                --color-text-light: #6b7280;
                --color-border: #e5e7eb;
                --color-border-dark: #d1d5db;
                --color-bg: #ffffff;
                --color-bg-subtle: #f9fafb;
                --shadow-sm: 0 1px 2px 0 rgb(0 0 0/.05);
                --shadow-md: 0 4px 6px -1px rgb(0 0 0/.1);
                --shadow-lg: 0 10px 15px -3px rgb(0 0 0/.1);
                --radius-md: 8px;
                --radius-lg: 12px;
            }

            /* ═══════════════════════════════════════════
               Layout étapes
            ═══════════════════════════════════════════ */
            .create-section {
                display: grid;
                grid-template-columns: 260px 1fr;
                gap: 1.5rem;
                margin-bottom: 2rem;
                align-items: start;
            }
            .create-section-aside {
                display: flex;
                gap: .75rem;
                align-items: flex-start;
                padding-top: .25rem;
                position: sticky;
                top: 1.5rem;
            }
            .create-step-badge {
                flex-shrink: 0;
                width: 2rem;
                height: 2rem;
                border-radius: 50%;
                background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-dark) 100%);
                color: #fff;
                font-size: .8rem;
                font-weight: 700;
                display: flex;
                align-items: center;
                justify-content: center;
                box-shadow: var(--shadow-sm);
            }
            .create-section-title { font-size: .95rem; font-weight: 600; margin: 0 0 .25rem; color: var(--color-text); }
            .create-section-hint  { font-size: .8rem; color: var(--color-text-light); margin: 0; line-height: 1.5; }
            .create-section-body  { min-width: 0; }

            /* ═══════════════════════════════════════════
               Form Card
            ═══════════════════════════════════════════ */
            .form-card {
                background: var(--color-bg);
                border: 1px solid var(--color-border);
                border-radius: var(--radius-lg);
                overflow: hidden;
                transition: box-shadow .2s;
            }
            .form-card:hover { box-shadow: var(--shadow-md); }
            .form-card-body   { padding: 1.25rem; }

            /* ═══════════════════════════════════════════
               Form grid & fields
            ═══════════════════════════════════════════ */
            .form-grid   { display: grid; gap: 1rem; }
            .form-grid-3 { grid-template-columns: repeat(3, 1fr); }
            .form-grid-3 .form-field-wide { grid-column: span 2; }

            .form-field { display: flex; flex-direction: column; gap: .25rem; }
            .form-field label {
                font-size: .75rem;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: .05em;
                color: var(--color-text-light);
            }
            .form-field input,
            .form-field select,
            .form-field textarea {
                padding: .5rem .75rem;
                border: 1px solid var(--color-border-dark);
                border-radius: var(--radius-md);
                font-size: .875rem;
                transition: all .15s;
                background: var(--color-bg);
            }
            .form-field input:focus,
            .form-field select:focus,
            .form-field textarea:focus {
                outline: none;
                border-color: var(--color-primary);
                box-shadow: 0 0 0 3px rgba(37,99,235,.1);
            }
            .form-error { font-size: .7rem; color: var(--color-danger); margin-top: .25rem; }
            .form-hint  { font-size: .7rem; color: var(--color-text-light); margin: .25rem 0 0; display: flex; align-items: center; gap: .25rem; }
            .checkbox-label { display: flex; align-items: center; gap: .5rem; cursor: pointer; user-select: none; }
            .checkbox-label input[type="checkbox"] { width: 1rem; height: 1rem; cursor: pointer; }
            .checkbox-label span { font-size: .875rem; font-weight: 500; color: var(--color-text); }

            /* ═══════════════════════════════════════════
               Bâtiment
            ═══════════════════════════════════════════ */
            .batiment-block {
                background: var(--color-bg);
                border: 1px solid var(--color-border);
                border-radius: var(--radius-lg);
                margin-bottom: 1.25rem;
                overflow: hidden;
                transition: all .2s;
            }
            .batiment-block:hover { box-shadow: var(--shadow-sm); border-color: var(--color-border-dark); }

            .batiment-card-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: .75rem 1rem;
                background: linear-gradient(to right, var(--color-bg-subtle), var(--color-bg));
                border-bottom: 1px solid var(--color-border);
                gap: .75rem;
            }
            .batiment-info { display: flex; align-items: center; gap: .5rem; flex: 1; }
            .batiment-icon { font-size: 1.1rem; }
            .batiment-name-input {
                font-weight: 600;
                font-size: .9rem;
                border: none;
                background: transparent;
                outline: none;
                flex: 1;
                color: var(--color-text);
                padding: .25rem 0;
            }
            .batiment-name-input:focus { color: var(--color-primary); border-bottom: 1px solid var(--color-primary); }

            /* ═══════════════════════════════════════════
               Portes
            ═══════════════════════════════════════════ */
            .portes-container { padding: 1rem; display: flex; flex-direction: column; gap: .75rem; }

            .porte-block {
                border: 1px solid var(--color-border);
                border-radius: var(--radius-md);
                overflow: hidden;
                background: var(--color-bg);
                transition: all .15s;
            }
            .porte-block:hover { border-color: var(--color-border-dark); box-shadow: var(--shadow-sm); }

            .porte-block-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: .5rem .75rem;
                background: var(--color-bg-subtle);
                border-bottom: 1px solid var(--color-border);
            }
            .porte-label {
                display: flex;
                align-items: center;
                gap: .4rem;
                font-size: .7rem;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: .05em;
                color: var(--color-text-light);
            }
            .porte-label svg { width: .85rem; height: .85rem; }

            .porte-block .form-grid   { padding: .75rem; }
            .porte-block .form-grid-3 { grid-template-columns: repeat(4,1fr); gap: .6rem; }
            .porte-block .form-field  { margin: 0; }
            .porte-block .form-field label { font-size: .65rem; font-weight: 600; display: block; margin-bottom: .2rem; }
            .porte-block .form-field input,
            .porte-block .form-field select { font-size: .8rem; padding: .35rem .5rem; }

            .porte-tarif-divider {
                grid-column: 1/-1;
                display: flex;
                align-items: center;
                gap: .5rem;
                font-size: .65rem;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: .06em;
                color: var(--color-text-light);
                margin: .5rem 0 .25rem;
            }
            .porte-tarif-divider::before,
            .porte-tarif-divider::after {
                content: '';
                flex: 1;
                height: 1px;
                background: linear-gradient(to right, transparent, var(--color-border), transparent);
            }

            /* ═══════════════════════════════════════════
               Équipements chips
            ═══════════════════════════════════════════ */
            .porte-equipements {
                border-top: 1px solid var(--color-border);
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
                color: var(--color-text-light);
                margin-bottom: .5rem;
            }
            .porte-equip-label svg { width: .75rem; height: .75rem; }
            .porte-equip-list {
                display: flex;
                flex-wrap: wrap;
                flex-direction: row;
                gap: .35rem;
            }
            .equip-chip {
                display: inline-flex;
                align-items: center;
                gap: .3rem;
                padding: .22rem .6rem;
                border: 1px solid var(--color-border);
                border-radius: 999px;
                font-size: .75rem;
                font-weight: 500;
                color: var(--color-text-light);
                background: var(--color-bg-subtle);
                cursor: pointer;
                transition: all .12s;
                user-select: none;
                white-space: nowrap;
            }
            .equip-chip:hover { border-color: var(--color-primary); color: var(--color-primary); background: var(--color-primary-light); }
            .equip-chip input[type="checkbox"] { width: .75rem; height: .75rem; accent-color: var(--color-primary); cursor: pointer; margin: 0; flex-shrink: 0; }
            .equip-chip:has(input:checked) { border-color: var(--color-primary); background: var(--color-primary-light); color: var(--color-primary); font-weight: 600; box-shadow: 0 0 0 2px rgba(37,99,235,.12); }

            /* ═══════════════════════════════════════════
               Boutons
            ═══════════════════════════════════════════ */
            .btn-add-porte-wrap { padding: 0 1rem 1rem; }
            .btn-add-porte {
                width: 100%;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: .4rem;
                padding: .5rem;
                border: 1.5px dashed var(--color-border-dark);
                border-radius: var(--radius-md);
                background: transparent;
                color: var(--color-text-light);
                font-size: .8rem;
                font-weight: 500;
                cursor: pointer;
                transition: all .2s;
            }
            .btn-add-porte:hover { border-color: var(--color-primary); color: var(--color-primary); background: var(--color-primary-light); transform: translateY(-1px); }
            .btn-add-porte svg { width: .9rem; height: .9rem; }

            .btn-remove-porte,
            .btn-remove-batiment {
                padding: .25rem .5rem;
                background: transparent;
                border: none;
                color: var(--color-text-light);
                cursor: pointer;
                border-radius: var(--radius-md);
                transition: all .15s;
                display: inline-flex;
                align-items: center;
                gap: .25rem;
                font-size: .7rem;
            }
            .btn-remove-porte:hover,
            .btn-remove-batiment:hover { background: var(--color-danger-light); color: var(--color-danger); }
            .btn-remove-porte svg,
            .btn-remove-batiment svg { width: .85rem; height: .85rem; }

            .add-batiment-wrapper { margin-top: 1rem; }
            .btn-add-batiment {
                width: 100%;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: .5rem;
                padding: .75rem;
                border: 2px dashed var(--color-border-dark);
                border-radius: var(--radius-lg);
                background: transparent;
                color: var(--color-text-light);
                font-size: .875rem;
                font-weight: 500;
                cursor: pointer;
                transition: all .2s;
            }
            .btn-add-batiment:hover { border-color: var(--color-primary); color: var(--color-primary); background: var(--color-primary-light); transform: translateY(-2px); }
            .btn-add-batiment svg { width: 1rem; height: 1rem; }

            /* ═══════════════════════════════════════════
               Proximités
            ═══════════════════════════════════════════ */
            .proximites-section { border-top: 1px solid var(--color-border); padding: 1rem 1.25rem 1.25rem; background: var(--color-bg-subtle); }
            .proximites-header { display: flex; align-items: center; gap: .4rem; margin-bottom: .875rem; }
            .proximites-header svg { width: .95rem; height: .95rem; color: var(--color-primary); flex-shrink: 0; }
            .proximites-header span { font-size: .75rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--color-text); }
            .proximites-hint { font-size: .7rem !important; color: var(--color-text-light) !important; font-weight: 400 !important; text-transform: none !important; letter-spacing: 0 !important; margin-left: .25rem; }
            .proximites-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: .5rem; }
            .proximite-item { display: flex; align-items: center; gap: .5rem; background: var(--color-bg); border: 1px solid var(--color-border); border-radius: var(--radius-md); padding: .45rem .6rem; transition: border-color .12s; }
            .proximite-item:focus-within { border-color: var(--color-primary); box-shadow: 0 0 0 2px rgba(37,99,235,.08); }
            .proximite-icon  { font-size: 1rem; flex-shrink: 0; width: 1.5rem; text-align: center; }
            .proximite-body  { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: .15rem; }
            .proximite-label { font-size: .72rem; font-weight: 600; color: var(--color-text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
            .proximite-input-wrap { display: flex; align-items: center; gap: .25rem; }
            .proximite-input { width: 100%; font-size: .78rem; padding: .2rem .35rem; height: 1.75rem; border: 1px solid var(--color-border-dark); border-radius: 5px; background: var(--color-bg-subtle); text-align: right; transition: all .12s; }
            .proximite-input:focus { outline: none; border-color: var(--color-primary); background: var(--color-bg); box-shadow: none; }
            .proximite-input::placeholder { color: #d1d5db; }
            .proximite-unit { font-size: .68rem; font-weight: 600; color: var(--color-text-light); flex-shrink: 0; }

            /* ═══════════════════════════════════════════
               Alert + Actions
            ═══════════════════════════════════════════ */
            .alert { padding: 1rem; border-radius: var(--radius-md); margin-bottom: 1.5rem; }
            .alert-danger { background: var(--color-danger-light); border: 1px solid var(--color-danger); color: var(--color-danger); }
            .alert-header { display: flex; align-items: center; gap: .5rem; margin-bottom: .5rem; }
            .alert-header svg { width: 1rem; height: 1rem; }

            .form-actions { display: flex; justify-content: space-between; align-items: center; padding-top: 1.5rem; border-top: 1px solid var(--color-border); margin-top: 1rem; }
            .actions-right { display: flex; gap: .75rem; }

            .btn { display: inline-flex; align-items: center; gap: .5rem; padding: .5rem 1rem; border-radius: var(--radius-md); font-size: .875rem; font-weight: 500; cursor: pointer; transition: all .2s; border: none; text-decoration: none; }
            .btn svg { width: 1rem; height: 1rem; }
            .btn-primary  { background: var(--color-primary); color: #fff; }
            .btn-primary:hover { background: var(--color-primary-dark); transform: translateY(-1px); box-shadow: var(--shadow-md); }
            .btn-outline  { background: transparent; border: 1px solid var(--color-border-dark); color: var(--color-text-light); }
            .btn-outline:hover { border-color: var(--color-danger); color: var(--color-danger); background: var(--color-danger-light); }

            /* ═══════════════════════════════════════════
               Responsive
            ═══════════════════════════════════════════ */
            @media (max-width: 900px) {
                .create-section { grid-template-columns: 1fr; }
                .create-section-aside { position: static; }
                .form-grid-3 { grid-template-columns: repeat(2, 1fr); }
                .form-grid-3 .form-field-wide { grid-column: span 2; }
                .porte-block .form-grid-3 { grid-template-columns: repeat(2, 1fr); }
                .form-actions { flex-direction: column; gap: 1rem; }
                .actions-right { width: 100%; flex-direction: column; }
                .actions-right .btn { width: 100%; justify-content: center; }
                .proximites-grid { grid-template-columns: repeat(2, 1fr); }
            }
            @media (max-width: 640px) {
                .form-grid-3 { grid-template-columns: 1fr; }
                .form-grid-3 .form-field-wide { grid-column: span 1; }
                .porte-block .form-grid-3 { grid-template-columns: 1fr; }
                .proximites-grid { grid-template-columns: 1fr; }
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            (function () {
                let batimentCount = document.querySelectorAll('.batiment-block').length;

                // ── Ajouter un bâtiment ──────────────────────────────────
                document.getElementById('btn-add-batiment').addEventListener('click', () => {
                    const tpl = document.getElementById('tpl-batiment').innerHTML
                        .replaceAll('__BI__', batimentCount);
                    const div = document.createElement('div');
                    div.innerHTML = tpl;
                    const el = div.firstElementChild;
                    document.getElementById('batiments-container').appendChild(el);
                    el.style.opacity = '0';
                    el.style.transform = 'translateY(-10px)';
                    setTimeout(() => {
                        el.style.transition = 'all .3s ease';
                        el.style.opacity = '1';
                        el.style.transform = 'translateY(0)';
                    }, 10);
                    batimentCount++;
                    el.querySelector('.batiment-name-input')?.focus();
                });

                // ── Délégation événements ────────────────────────────────
                document.getElementById('batiments-container').addEventListener('click', e => {

                    // Ajouter une porte
                    const addPorteBtn = e.target.closest('.btn-add-porte');
                    if (addPorteBtn) {
                        const bi   = addPorteBtn.dataset.bi;
                        const cont = document.getElementById('portes-container-' + bi);
                        const pi   = cont.children.length;
                        const tpl  = document.getElementById('tpl-porte').innerHTML
                            .replaceAll('__BI__', bi)
                            .replaceAll('__PI__', pi);
                        const div  = document.createElement('div');
                        div.innerHTML = tpl;
                        const el = div.firstElementChild;
                        cont.appendChild(el);
                        el.style.opacity = '0';
                        setTimeout(() => { el.style.transition = 'opacity .2s'; el.style.opacity = '1'; }, 10);
                        el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                        return;
                    }

                    // Supprimer une porte
                    const removePorteBtn = e.target.closest('.btn-remove-porte');
                    if (removePorteBtn) {
                        const block = removePorteBtn.closest('.porte-block');
                        block.style.opacity = '0';
                        setTimeout(() => block.remove(), 200);
                        return;
                    }

                    // Supprimer un bâtiment
                    const removeBatBtn = e.target.closest('.btn-remove-batiment');
                    if (removeBatBtn) {
                        const block = removeBatBtn.closest('.batiment-block');
                        if (document.querySelectorAll('.batiment-block').length > 1) {
                            block.style.opacity = '0';
                            block.style.transform = 'translateX(-20px)';
                            setTimeout(() => block.remove(), 200);
                        } else {
                            alert('Vous devez conserver au moins un bâtiment.');
                        }
                    }
                });
            })();
        </script>
    @endpush
@endsection