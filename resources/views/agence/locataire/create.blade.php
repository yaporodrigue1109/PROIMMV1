@extends('agence.layouts.app')
@section('title', 'Nouveau locataire')

@section('content')
    <div class="page">

        <div class="page-header">
            <div class="page-header-copy">
                <div class="page-heading">
                    <a href="{{ route('agence.locataires.index') }}" class="back-link">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                        </svg>
                    </a>
                    <h2>Nouveau locataire</h2>
                </div>
                <p>Renseignez l'identité du locataire et son contrat de location.</p>
            </div>
        </div>

        @if($errors->any())
            <div class="alert alert-danger u-mb-md">
                <div class="alert-header">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                    </svg>
                    <strong>Veuillez corriger les erreurs suivantes :</strong>
                </div>
                <ul class="u-list-none u-m-0">
                    @foreach($errors->all() as $error)<li>• {{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('agence.locataires.store') }}" method="POST"
              enctype="multipart/form-data" id="form-locataire">
            @csrf

            {{-- ═══ ÉTAPE 1 — Identité ═══ --}}
            <div class="create-section" data-section="1">
                <div class="create-section-aside">
                    <div class="create-step-badge">1</div>
                    <div>
                        <h3 class="create-section-title">Identité</h3>
                        <p class="create-section-hint">Informations personnelles du locataire.</p>
                    </div>
                </div>
                <div class="form-card create-section-body">
                    <div class="form-card-body">
                        <div class="form-grid form-grid-3">

                            <div class="form-field form-field-wide">
                                <label for="name">Nom complet *</label>
                                <x-ui.input
                                        name="name"
                                        id="name"
                                        :value="old('name')"
                                        placeholder="Ex: KOUASSI Aya Marie"
                                        required
                                />
                                @error('name')<span class="form-error">{{ $message }}</span>@enderror
                            </div>

                            <div class="form-field">
                                <label for="tel1">Téléphone 1 *</label>
                                <x-ui.input
                                        name="tel1"
                                        id="tel1"
                                        :value="old('tel1')"
                                        placeholder="07 00 00 00 00"
                                        required
                                />
                                @error('tel1')<span class="form-error">{{ $message }}</span>@enderror
                            </div>

                            <div class="form-field">
                                <label for="tel2">Téléphone 2</label>
                                <x-ui.input name="tel2" id="tel2" :value="old('tel2')" placeholder="05 00 00 00 00" />
                            </div>

                            <div class="form-field">
                                <label for="email">Email</label>
                                <x-ui.input
                                        name="email"
                                        id="email"
                                        type="email"
                                        :value="old('email')"
                                        placeholder="email@exemple.com"
                                />
                            </div>

                            <div class="form-field">
                                <label for="profession">Profession</label>
                                <x-ui.input name="profession" id="profession" :value="old('profession')" placeholder="Ex: Commerçant" />
                            </div>

                            <div class="form-field">
                                <label for="nationalite">Nationalité</label>
                                <x-ui.input name="nationalite" id="nationalite" :value="old('nationalite')" placeholder="Ex: Ivoirien" />
                            </div>

                            <div class="form-field">
                                <label for="date_naissance">Date de naissance</label>
                                <x-ui.input
                                        name="date_naissance"
                                        id="date_naissance"
                                        type="date"
                                        :value="old('date_naissance')"
                                />
                            </div>

                            <div class="form-field">
                                <label for="lieu_naissance">Lieu de naissance</label>
                                <x-ui.input name="lieu_naissance" id="lieu_naissance" :value="old('lieu_naissance')" placeholder="Ex: Abidjan" />
                            </div>

                            <div class="form-field">
                                <label for="region_id">Région</label>
                                <x-ui.select
                                        name="region_id"
                                        :options="collect($regions)->pluck('name', 'id')->toArray()"
                                        :value="old('region_id')"
                                        placeholder="Sélectionnez votre région"
                                        onchange="getRequest('{{ url('/') }}/admin/list/city?parent_id='+this.value,'ville_id','select',this.value)"
                                />
                            </div>

                            <div class="form-field">
                                <label for="ville_id">Ville</label>
                                <x-ui.select
                                        name="ville_id"
                                        id="ville_id"
                                        :options="collect($villes ?? [])->where('region_id', old('region_id'))->pluck('name', 'id')->toArray()"
                                        :value="old('ville_id')"
                                        placeholder="Sélectionnez votre ville"
                                />
                                @error('ville_id') <small class="error">{{ $message }}</small> @enderror
                            </div>

                            <div class="form-field ">
                                <label for="adresse">Adresse</label>
                                <x-ui.input name="adresse" id="adresse" :value="old('adresse')" placeholder="Adresse du locataire" />
                            </div>

                        </div>
                    </div>

                    {{-- Pièce d'identité --}}
                    <div class="piece-section">
                        <div class="piece-header">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Zm6-10.125a1.875 1.875 0 1 1-3.75 0 1.875 1.875 0 0 1 3.75 0Zm1.294 6.336a6.721 6.721 0 0 1-3.17.789 6.721 6.721 0 0 1-3.168-.789 3.376 3.376 0 0 1 6.338 0Z" />
                            </svg>
                            <span>Pièce d'identité</span>
                        </div>
                        <div class="form-grid form-grid-3" style="padding:1rem 1.25rem 1.25rem;">

                            <div class="form-field">
                                <label for="type_piece_id">Type de Pièce *</label>
                                <x-ui.select
                                        name="type_piece_id"
                                        id="type_piece_id"
                                        :options="collect($typePiece)->mapWithKeys(fn ($prop) => [$prop->type_pieces_id => $prop->name])->toArray()"
                                        :value="old('type_piece_id')"
                                        placeholder="Sélectionner un type de pièce"
                                />
                            </div>

                            <div class="form-field">
                                <label for="num_piece">Numéro de pièce *</label>
                                <x-ui.input
                                        name="num_piece"
                                        id="num_piece"
                                        :value="old('num_piece')"
                                        placeholder="CNI, Passeport…"
                                        required
                                />
                            </div>

                            <div class="form-field">
                                <label for="date_expiration_piece">Date d'expiration</label>
                                <x-ui.input
                                        name="date_expiration_piece"
                                        id="date_expiration_piece"
                                        type="date"
                                        :value="old('date_expiration_piece')"
                                />
                            </div>

                            <div class="form-field">
                                <label for="photo">Photo du locataire</label>
                                <x-ui.file name="photo" id="photo" accept="image/*" />
                            </div>

                            <div class="form-field">
                                <label for="image_pice">Photo de la pièce</label>
                                <x-ui.file name="image_pice" id="image_pice" accept="image/*" />
                            </div>
                            <div class="form-grid form-grid-3">
                                <div class="form-field form-field-wide">
                                    <x-ui.checkbox
                                            name="is_new"
                                            id="is_new"
                                            value="0"
                                            :checked="(bool) old('is_new')"
                                            label="Est ce un ancien locataire?"
                                            :input-attributes="['onchange' => 'afficherArriereSection(this.checked)']"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>


            {{-- ═══ ÉTAPE 2 — Contrat de location ═══ --}}
            <div class="create-section" data-section="2">
                <div class="create-section-aside">
                    <div class="create-step-badge">2</div>
                    <div>
                        <h3 class="create-section-title">Contrat de location</h3>
                        <p class="create-section-hint">Sélectionnez le propriétaire, puis la propriété, le bâtiment et enfin la porte.</p>
                    </div>
                </div>
                <div class="form-card create-section-body">
                    <div class="form-card-body">
                        <div class="form-grid form-grid-3">

                            {{-- ── A : Propriétaire ── --}}
                            <div class="form-field form-field-wide">
                                <label for="contrat_proprietaire_id">Propriétaire *</label>
                                <select name="contrat[proprietaire_id]" id="contrat_proprietaire_id"
                                        onchange="onProprietaireChange(this.value)">
                                    <option value="">Sélectionner un propriétaire</option>
                                    @foreach($proprio as $prop)
                                        <option value="{{ $prop->proprietaire_id }}"
                                                data-proprietes="{{ $prop->proprietes->map(fn($p) => [
                                                        'id'    => $p->propriete_id,
                                                        'label' => $p->reference . ($p->adresse_complete ? ' — ' . $p->adresse_complete : ''),
                                                        'batiments' => $p->batiments->map(fn($b) => [
                                                            'id'    => $b->batiment_id,
                                                            'label' => $b->name,
                                                            'portes'=> $b->portes->map(fn($po) => [
                                                                'id'     => $po->porte_id,
                                                                'label'  => $po->numero_porte . ' (' . ($po->typePorte?->libelle ?? '—') . ')',
                                                                'loyer'  => $po->mt_loyer          ?? 0,
                                                                'caution'=> $po->caution         ?? 0,
                                                                'avance' => $po->avance          ?? 0,
                                                                'agence' => $po->agence          ?? 0,
                                                                'cie'    => $po->mt_caution_cie     ?? 0,
                                                                'sodeci' => $po->mt_caution_sodeci  ?? 0,
                                                            ])->values(),
                                                        ])->values(),
                                                    ])->values()->toJson() }}"
                                                @selected(old('contrat.proprietaire_id') == $prop->proprietaire_id)>
                                            {{ $prop->name }} — {{ $prop->tel1 }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- ── B : Propriété ── --}}
                            <div class="form-field cascade-field" id="field-propriete" style="display:none;">
                                <label for="contrat_propriete_id">Propriété *</label>
                                <select name="contrat[propriete_id]" id="contrat_propriete_id"
                                        onchange="onProprieteChange(this.value)">
                                    <option value="">Sélectionner une propriété</option>
                                </select>
                            </div>

                            {{-- ── C : Bâtiment ── --}}
                            <div class="form-field cascade-field" id="field-batiment" style="display:none;">
                                <label for="contrat_batiment_id">Bâtiment *</label>
                                <select name="contrat[batiment_id]" id="contrat_batiment_id"
                                        onchange="onBatimentChange(this.value)">
                                    <option value="">Sélectionner un bâtiment</option>
                                </select>
                            </div>

                            {{-- ── D : Porte ── --}}
                            <div class="form-field cascade-field" id="field-porte" style="display:none;">
                                <label for="contrat_porte_id">Porte disponible *</label>
                                <select name="contrat[porte_id]" id="contrat_porte_id"
                                        onchange="onPorteChange(this)">
                                    <option value="">Sélectionner une porte</option>
                                </select>
                            </div>

                            {{-- ── Résumé visuel ── --}}
                            <div class="cascade-resume" id="cascade-resume" style="display:none;">
                                <span class="resume-item" id="resume-proprio"></span>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:.75rem;height:.75rem;color:#9ca3af;flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                                <span class="resume-item" id="resume-propriete" style="display:none;"></span>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" id="sep-bat" style="width:.75rem;height:.75rem;color:#9ca3af;flex-shrink:0;display:none;"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                                <span class="resume-item" id="resume-batiment" style="display:none;"></span>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" id="sep-porte" style="width:.75rem;height:.75rem;color:#9ca3af;flex-shrink:0;display:none;"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                                <span class="resume-item resume-porte" id="resume-porte" style="display:none;"></span>
                            </div>


                            <div class="tarif-sep-contrat">Conditions financières</div>

                            <div class="form-field">
                                <label for="contrat_loyer_net">Loyer net (FCFA) *</label>
                                <x-ui.input
                                        name="contrat[loyer_net]"
                                        id="contrat_loyer_net"
                                        type="number"
                                        :value="old('contrat.loyer_net')"
                                        placeholder="Sélectionnez une porte"
                                        readonly
                                        class="input-readonly"
                                />
                            </div>


                            <div class="form-field">
                                <label for="contrat_caution_montant">
                                    Caution
                                    <span class="tarif-lock-badge">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                                        </svg>
                                        Nombre Mois
                                    </span>
                                </label>
                                <x-ui.input id="contrat_caution_montant" type="number" readonly class="input-readonly" placeholder="—" />
                                <input type="hidden" name="contrat[caution]" id="contrat_caution_hidden" value="0" />
                            </div>

                            <div class="form-field">
                                <label for="contrat_avance_montant">
                                    Avance
                                    <span class="tarif-lock-badge">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                                        </svg>
                                        Nombre Mois
                                    </span>
                                </label>
                                <x-ui.input id="contrat_avance_montant" type="number" readonly class="input-readonly" placeholder="—" />
                                <input type="hidden" name="contrat[avance]" id="contrat_avance_hidden" value="0" />
                            </div>

                            <div class="form-field">
                                <label for="contrat_agence_montant">
                                    Agence
                                    <span class="tarif-lock-badge">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                                        </svg>
                                       Nombre Mois
                                    </span>
                                </label>
                                <x-ui.input id="contrat_agence_montant" type="number" readonly class="input-readonly" placeholder="—" />
                                <input type="hidden" name="contrat[agence]" id="contrat_agence_hidden" value="0" />
                            </div>

                            <div class="form-field">
                                <label for="contrat_cie_montant">
                                    Caution CIE
                                    <span class="tarif-lock-badge">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                                        </svg>
                                        Montant (FCFA)
                                    </span>
                                </label>
                                <x-ui.input id="contrat_cie_montant" type="number" readonly class="input-readonly" placeholder="—" />
                                <input type="hidden" name="contrat[caution_cie]" id="contrat_caution_cie" value="0" />
                            </div>

                            <div class="form-field">
                                <label for="contrat_sodeci_montant">
                                    Caution SODECI
                                    <span class="tarif-lock-badge">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                                        </svg>
                                        Montant (FCFA)
                                    </span>
                                </label>
                                <x-ui.input id="contrat_sodeci_montant" type="number" readonly class="input-readonly" placeholder="—" />
                                <input type="hidden" name="contrat[caution_sodeci]" id="contrat_caution_sodeci" value="0" />
                            </div>

                            <div class="form-field">
                                <label for="contrat_nbre_personne">Nombre de personnes</label>
                                <x-ui.input
                                        name="contrat[nbre_personne]"
                                        id="contrat_nbre_personne"
                                        type="number"
                                        :value="old('contrat.nbre_personne', 1)"
                                        min="1"
                                />
                            </div>

                            <div class="form-field">
                                <label for="contrat_nbre_enfant">Nombre d'enfants</label>
                                <x-ui.input
                                        name="contrat[nbre_enfant]"
                                        id="contrat_nbre_enfant"
                                        type="number"
                                        :value="old('contrat.nbre_enfant', 0)"
                                        min="0"
                                />
                            </div>
                            <div class="form-field">
                                <label for="contrat_date_debut">Date début bail</label>
                                <x-ui.input
                                        name="contrat[date_debut_bail]"
                                        id="contrat_date_debut"
                                        type="date"
                                        :value="old('contrat.date_debut_bail', now()->format('Y-m-d'))"
                                />
                            </div>
                            <div class="form-field">
                                <label for="contrat_date_entree">Date d'entrée</label>
                                <x-ui.input
                                        name="contrat[date_entree]"
                                        id="contrat_date_entree"
                                        type="date"
                                        :value="old('contrat.date_entree', now()->format('Y-m-d'))"
                                        required
                                />
                            </div>
                            <div class="form-field">
                                <label for="contrat_date_entree">Mode de paiement</label>
                                <x-ui.select
                                        name="contrat[mode_paiement_id]"
                                        id="contrat_mode_paiement_id"
                                        :options="collect($modePaiement)->pluck('name', 'id')->toArray()"
                                        placeholder="Selectionez le mode paiement"
                                        required
                                />

                            </div>

                        </div>
                    </div>

                    <div class="total-entree-section" id="total-entree-section" style="display:none;">
                        <div class="total-entree-label">Total à payer à l'entrée</div>
                        <div class="total-entree-value" id="total-entree-value">0 FCFA</div>
                    </div>

                </div>
            </div>


            {{-- ═══ ÉTAPE 3 — Arriérés ═══ --}}
            {{-- ═══ ÉTAPE 3 — Arriérés ═══ --}}
            <div class="create-section" data-section="3" id="create-section-trois" style="display: none;">
                <div class="create-section-aside">
                    <div class="create-step-badge">3</div>
                    <div>
                        <h3 class="create-section-title">Arriérés de loyer</h3>
                        <p class="create-section-hint">Si le locataire a des dettes antérieures, renseignez-les ici.</p>
                    </div>
                </div>
                <div class="form-card create-section-body">
                    <div class="form-card-body">
                        <div class="form-grid form-grid-3">
                            <div class="form-field form-field-wide">
                                <x-ui.checkbox
                                        name="a_des_arrieres"
                                        id="a_des_arrieres"
                                        :checked="(bool) old('a_des_arrieres')"
                                        label="Ce locataire a des arriérés de loyer"
                                        :input-attributes="['onchange' => 'toggleArrieres(this.checked)']"
                                />
                            </div>
                        </div>

                        <div id="section-arrieres" style="display: {{ old('a_des_arrieres') ? 'block' : 'none' }}; margin-top: 1rem;">
                            <div class="form-grid form-grid-3">
                                <div class="form-field form-field-wide">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                                        <label>Liste des mois d'arriérés</label>
                                        <button type="button" class="btn-add-arriere" onclick="ajouterLigneArriere()">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 1rem; height: 1rem;">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                            </svg>
                                            Ajouter un mois
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div id="liste-arrieres">
                                @if(old('a_des_arrieres') && old('arrieres'))
                                    @foreach(old('arrieres') as $index => $arriere)
                                        <div class="ligne-arriere" data-index="{{ $index }}">
                                            <div class="form-grid" style="grid-template-columns: 1fr 1fr auto; gap: 0.5rem;">
                                                <div class="form-field">
                                                    <label>Mois *</label>
                                                    <x-ui.input
                                                            type="month"
                                                            name="arrieres[{{ $index }}][mois]"
                                                            :value="$arriere['mois']"
                                                            required
                                                    />
                                                </div>
                                                <div class="form-field">
                                                    <label>Montant (FCFA) *</label>
                                                    <x-ui.input
                                                            type="number"
                                                            name="arrieres[{{ $index }}][montant]"
                                                            :value="$arriere['montant']"
                                                            placeholder="Ex: 50000"
                                                            required
                                                    />
                                                </div>
                                                <div class="form-field">
                                                    <button type="button" class="btn-remove-arriere" onclick="supprimerLigneArriere(this)">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 1rem; height: 1rem;">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                                        </svg>
                                                        Supprimer
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            {{-- Actions --}}
            <div class="form-actions">
                <a href="{{ route('agence.locataires.index') }}" class="btn btn-outline">
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
                        Enregistrer le locataire
                    </button>
                </div>
            </div>

        </form>
    </div>
@endsection

@push('styles')
    <style>
        /* Styles pour les arriérés */
        .btn-add-arriere {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.4rem 0.8rem;
            background: #10b981;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-add-arriere:hover {
            background: #059669;
            transform: translateY(-1px);
        }

        .btn-remove-arriere {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            padding: 0.3rem 0.6rem;
            background: #ef4444;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 0.7rem;
            cursor: pointer;
            transition: all 0.2s;
            width: 100%;
            justify-content: center;
        }

        .btn-remove-arriere:hover {
            background: #dc2626;
        }

        #section-arrieres .form-field {
            margin-bottom: 0;
        }

        #liste-arrieres {
            max-height: 400px;
            overflow-y: auto;
        }

        .ligne-arriere {
            margin-bottom: 1rem;
        }

        .ligne-arriere:last-child {
            margin-bottom: 0;
        }
    </style>

    <style>
        /* Champ verrouillé */
        .input-readonly {
            background: #f3f4f6 !important;
            color: #6b7280 !important;
            cursor: not-allowed !important;
            border-style: dashed !important;
        }

        /* Badge "Depuis le tarif" */
        .tarif-lock-badge {
            display: inline-flex;
            align-items: center;
            gap: .2rem;
            font-size: .6rem;
            font-weight: 600;
            color: #2563eb;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            padding: .05rem .35rem;
            border-radius: 999px;
            margin-left: .35rem;
            text-transform: none;
            letter-spacing: 0;
            vertical-align: middle;
        }
        .tarif-lock-badge svg { width: .55rem; height: .55rem; }

        /* Cascade : apparition animée */
        .cascade-field { animation: fadeSlideIn .2s ease; }
        @keyframes fadeSlideIn {
            from { opacity: 0; transform: translateY(-6px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* Fil d'Ariane */
        .cascade-resume {
            grid-column: 1 / -1;
            display: flex;
            align-items: center;
            gap: .4rem;
            flex-wrap: wrap;
            padding: .5rem .75rem;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            margin-bottom: .25rem;
        }
        .resume-item { font-size: .75rem; font-weight: 600; color: #065f46; }
        .resume-porte {
            color: #2563eb;
            background: #eff6ff;
            padding: .1rem .45rem;
            border-radius: 999px;
            border: 1px solid #bfdbfe;
        }

        /* Styles principaux */
        :root {
            --color-primary:#2563eb; --color-primary-dark:#1e40af; --color-primary-light:#eff6ff;
            --color-danger:#ef4444; --color-danger-light:#fee2e2;
            --color-text:#111827; --color-text-light:#6b7280;
            --color-border:#e5e7eb; --color-border-dark:#d1d5db;
            --color-bg:#fff; --color-bg-subtle:#f9fafb;
            --shadow-md:0 4px 6px -1px rgb(0 0 0/.1); --radius-md:8px; --radius-lg:12px;
        }
        .create-section { display:grid; grid-template-columns:260px 1fr; gap:1.5rem; margin-bottom:2rem; align-items:start; }
        .create-section-aside { display:flex; gap:.75rem; align-items:flex-start; padding-top:.25rem; position:sticky; top:1.5rem; }
        .create-step-badge { flex-shrink:0; width:2rem; height:2rem; border-radius:50%; background:linear-gradient(135deg,var(--color-primary) 0%,var(--color-primary-dark) 100%); color:#fff; font-size:.8rem; font-weight:700; display:flex; align-items:center; justify-content:center; }
        .create-section-title { font-size:.95rem; font-weight:600; margin:0 0 .25rem; color:var(--color-text); }
        .create-section-hint  { font-size:.8rem; color:var(--color-text-light); margin:0; line-height:1.5; }
        .create-section-body  { min-width:0; }
        .form-card { background:var(--color-bg); border:1px solid var(--color-border); border-radius:var(--radius-lg); overflow:hidden; }
        .form-card-body { padding:1.25rem; }
        .form-grid { display:grid; gap:1rem; }
        .form-grid-3 { grid-template-columns:repeat(3,1fr); }
        .form-grid-3 .form-field-wide { grid-column:span 2; }
        .form-field { display:flex; flex-direction:column; gap:.25rem; }
        .form-field label { font-size:.75rem; font-weight:600; text-transform:uppercase; letter-spacing:.05em; color:var(--color-text-light); }
        .form-field input, .form-field select, .form-field textarea { padding:.5rem .75rem; border:1px solid var(--color-border-dark); border-radius:var(--radius-md); font-size:.875rem; transition:all .15s; background:var(--color-bg); }
        .form-field input:focus, .form-field select:focus { outline:none; border-color:var(--color-primary); box-shadow:0 0 0 3px rgba(37,99,235,.1); }
        .form-error { font-size:.7rem; color:var(--color-danger); margin-top:.25rem; }
        .piece-section { border-top:1px solid var(--color-border); }
        .piece-header { display:flex; align-items:center; gap:.4rem; padding:.7rem 1rem; background:var(--color-bg-subtle); font-size:.75rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:var(--color-text-light); }
        .piece-header svg { width:.9rem; height:.9rem; }
        .tarif-sep-contrat { grid-column:1/-1; display:flex; align-items:center; gap:.5rem; font-size:.65rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:var(--color-text-light); margin:.5rem 0 .25rem; }
        .tarif-sep-contrat::before, .tarif-sep-contrat::after { content:''; flex:1; height:1px; background:linear-gradient(to right,transparent,var(--color-border),transparent); }
        .total-entree-section { display:flex; align-items:center; justify-content:space-between; padding:.875rem 1.25rem; background:linear-gradient(to right,#eff6ff,#f0fdf4); border-top:1px solid var(--color-border); }
        .total-entree-label { font-size:.8rem; font-weight:600; color:var(--color-text-light); text-transform:uppercase; letter-spacing:.04em; }
        .total-entree-value { font-size:1.25rem; font-weight:800; color:#059669; }
        .form-actions { display:flex; justify-content:space-between; align-items:center; padding-top:1.5rem; border-top:1px solid var(--color-border); margin-top:.5rem; }
        .actions-right { display:flex; gap:.75rem; }
        .btn { display:inline-flex; align-items:center; gap:.5rem; padding:.5rem 1rem; border-radius:var(--radius-md); font-size:.875rem; font-weight:500; cursor:pointer; transition:all .2s; border:none; text-decoration:none; }
        .btn svg { width:1rem; height:1rem; }
        .btn-primary { background:var(--color-primary); color:#fff; }
        .btn-primary:hover { background:var(--color-primary-dark); transform:translateY(-1px); }
        .btn-outline { background:transparent; border:1px solid var(--color-border-dark); color:var(--color-text-light); }
        .btn-outline:hover { border-color:var(--color-danger); color:var(--color-danger); background:var(--color-danger-light); }
        .alert { padding:1rem; border-radius:var(--radius-md); margin-bottom:1.5rem; }
        .alert-danger { background:var(--color-danger-light); border:1px solid var(--color-danger); color:var(--color-danger); }
        .alert-header { display:flex; align-items:center; gap:.5rem; margin-bottom:.5rem; }
        .alert-header svg { width:1rem; height:1rem; }
        @media (max-width:900px) {
            .create-section { grid-template-columns:1fr; }
            .create-section-aside { position:static; }
            .form-grid-3 { grid-template-columns:repeat(2,1fr); }
            .form-grid-3 .form-field-wide { grid-column:span 2; }
            .form-actions { flex-direction:column; gap:1rem; }
            .actions-right { width:100%; }
            .actions-right .btn { width:100%; justify-content:center; }
        }
    </style>
@endpush

@push('scripts')
    <script>
        // Compteur pour les index des arriérés
        let arriereCounter = 0;

        function afficherArriereSection(checked) {
            const createSection = document.getElementById('create-section-trois');

            if (checked) {
                // Afficher toute la section
                if (createSection) {
                    createSection.style.display = '';
                }


            } else {
                // Masquer toute la section
                if (createSection) {
                    createSection.style.display = 'none';
                }

                // Vider la liste des arriérés
                const liste = document.getElementById('liste-arrieres');
                if (liste) {
                    liste.innerHTML = '';
                }

                // Réinitialiser le compteur
                arriereCounter = 0;
            }
        }



        // Gestion de l'affichage des arriérés
        function toggleArrieres(checked) {
            const section = document.getElementById('section-arrieres');
            const liste = document.getElementById('liste-arrieres');

            if (checked) {
                section.style.display = 'block';
                // Ajouter une première ligne si la liste est vide
                if (liste && liste.children.length === 0) {
                    ajouterLigneArriere();
                }
            } else {
                section.style.display = 'none';
                // Vider la liste des arriérés
                if (liste) {
                    liste.innerHTML = '';
                }
                arriereCounter = 0;
            }
        }

        // Ajouter une nouvelle ligne d'arriéré
        function ajouterLigneArriere() {
            const index = arriereCounter++;
            const liste = document.getElementById('liste-arrieres');

            const div = document.createElement('div');
            div.className = 'ligne-arriere';
            div.setAttribute('data-index', index);
            div.innerHTML = `
            <div class="form-grid" style="grid-template-columns: 1fr 1fr auto; gap: 0.5rem;">
                <div class="form-field">
                    <label>Mois *</label>
                    <input type="month" name="arrieres[${index}][mois]" required />
                </div>
                <div class="form-field">
                    <label>Montant (FCFA) *</label>
                    <input type="number" name="arrieres[${index}][montant]" placeholder="Ex: 50000" required />
                </div>
                <div class="form-field">
                    <button type="button" class="btn-remove-arriere" onclick="supprimerLigneArriere(this)">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 1rem; height: 1rem;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                        Supprimer
                    </button>
                </div>
            </div>
        `;
            liste.appendChild(div);
        }

        // Supprimer une ligne d'arriéré
        function supprimerLigneArriere(button) {
            const ligne = button.closest('.ligne-arriere');
            if (ligne) {
                ligne.remove();
                reindexerLignes();
            }
        }

        // Re-indexer les lignes après suppression
        function reindexerLignes() {
            const lignes = document.querySelectorAll('#liste-arrieres .ligne-arriere');
            arriereCounter = 0;

            lignes.forEach((ligne, newIndex) => {
                ligne.setAttribute('data-index', newIndex);
                const moisInput = ligne.querySelector('input[name*="[mois]"]');
                const montantInput = ligne.querySelector('input[name*="[montant]"]');

                if (moisInput) {
                    moisInput.name = `arrieres[${newIndex}][mois]`;
                }
                if (montantInput) {
                    montantInput.name = `arrieres[${newIndex}][montant]`;
                }
                arriereCounter = newIndex + 1;
            });
        }

        // Initialisation - ne s'affiche que si la case est cochée
        document.addEventListener('DOMContentLoaded', function() {
            const checkbox = document.getElementById('a_des_arrieres');
            const section = document.getElementById('section-arrieres');

            if (checkbox && checkbox.checked) {
                const liste = document.getElementById('liste-arrieres');
                if (liste && liste.children.length === 0) {
                    ajouterLigneArriere();
                }
            } else if (section) {
                section.style.display = 'none';
            }
        });
    </script>

    <script>
        // Fonction pour charger les villes
        function getRequest(route, id, type, value) {
            console.log('Chargement des villes pour parent_id:', value);

            $.ajax({
                url: route,
                type: 'GET',
                data: { parent_id: value },
                dataType: 'json',
                success: function(data) {
                    if (type == 'select' && data.select_tag) {
                        $('#' + id).html(data.select_tag);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Erreur:', error);
                    $('#' + id).html('<option value="">Erreur de chargement</option>');
                }
            });
        }

        // Données en mémoire
        let _cascadeData = {};
        let _batimentsData = {};

        // A : Sélection propriétaire
        function onProprietaireChange(proprioId) {
            resetField('field-propriete', 'contrat_propriete_id');
            resetField('field-batiment', 'contrat_batiment_id');
            resetField('field-porte', 'contrat_porte_id');
            hide('cascade-resume');
            setResume('resume-propriete', null);
            setResume('resume-batiment', null);
            setResume('resume-porte', null);
            clearTarifFields();

            if (!proprioId) { hide('field-propriete'); return; }

            const opt = document.querySelector(`#contrat_proprietaire_id option[value="${proprioId}"]`);
            if (!opt) return;

            const proprietes = JSON.parse(opt.dataset.proprietes || '[]');
            _cascadeData = {};

            const sel = document.getElementById('contrat_propriete_id');
            sel.innerHTML = '<option value="">Sélectionner une propriété</option>';

            proprietes.forEach(p => {
                _cascadeData[p.id] = p.batiments;
                const o = new Option(p.label, p.id);
                sel.appendChild(o);
            });

            show('field-propriete');
            show('cascade-resume');
            setResume('resume-proprio', opt.textContent.trim().split('—')[0].trim());
        }

        // B : Sélection propriété
        function onProprieteChange(proprieteId) {
            resetField('field-batiment', 'contrat_batiment_id');
            resetField('field-porte', 'contrat_porte_id');
            setResume('resume-batiment', null);
            setResume('resume-porte', null);
            hide('field-batiment');
            hide('field-porte');
            clearTarifFields();

            if (!proprieteId) return;

            const batiments = _cascadeData[proprieteId] || [];
            _batimentsData = {};

            const sel = document.getElementById('contrat_batiment_id');
            sel.innerHTML = '<option value="">Sélectionner un bâtiment</option>';

            batiments.forEach(b => {
                _batimentsData[b.id] = b.portes;
                sel.appendChild(new Option(b.label, b.id));
            });

            show('field-batiment');
            const label = document.querySelector(`#contrat_propriete_id option[value="${proprieteId}"]`)?.textContent.trim();
            setResume('resume-propriete', label, true);
        }

        // C : Sélection bâtiment
        function onBatimentChange(batimentId) {
            resetField('field-porte', 'contrat_porte_id');
            setResume('resume-porte', null);
            hide('field-porte');
            clearTarifFields();

            if (!batimentId) return;

            const portes = _batimentsData[batimentId] || [];
            const sel = document.getElementById('contrat_porte_id');
            sel.innerHTML = '<option value="">Sélectionner une porte</option>';

            if (portes.length === 0) {
                sel.innerHTML = '<option value="" disabled>Aucune porte disponible</option>';
            } else {
                portes.forEach(p => {
                    const o = new Option(`${p.label} — ${Number(p.loyer).toLocaleString('fr-FR')} FCFA/mois`, p.id);
                    o.dataset.loyer = p.loyer;
                    o.dataset.caution = p.caution;
                    o.dataset.avance = p.avance;
                    o.dataset.agence = p.agence;
                    o.dataset.cie = p.cie;
                    o.dataset.sodeci = p.sodeci;
                    sel.appendChild(o);
                });
            }

            show('field-porte');
            const label = document.querySelector(`#contrat_batiment_id option[value="${batimentId}"]`)?.textContent.trim();
            setResume('resume-batiment', label, true);
        }

        // D : Sélection porte
        function onPorteChange(select) {
            const opt = select.selectedOptions[0];
            if (!opt || !opt.value) {
                clearTarifFields();
                return;
            }

            setField('contrat_loyer_net', opt.dataset.loyer);
            setField('contrat_caution_montant', opt.dataset.caution);
            setField('contrat_avance_montant', opt.dataset.avance);
            setField('contrat_agence_montant', opt.dataset.agence);
            setField('contrat_cie_montant', opt.dataset.cie);
            setField('contrat_sodeci_montant', opt.dataset.sodeci);

            setHidden('contrat_caution_hidden', opt.dataset.caution);
            setHidden('contrat_avance_hidden', opt.dataset.avance);
            setHidden('contrat_agence_hidden', opt.dataset.agence);
            setHidden('contrat_caution_cie', opt.dataset.cie);
            setHidden('contrat_caution_sodeci', opt.dataset.sodeci);

            setResume('resume-porte', opt.textContent.trim().split('—')[0].trim(), true);
            recalcTotal();
        }

        function setField(id, value) {
            const el = document.getElementById(id);
            if (el) el.value = value ?? 0;
        }

        function setHidden(id, value) {
            const el = document.getElementById(id);
            if (el) el.value = value ?? 0;
        }

        function clearTarifFields() {
            ['contrat_loyer_net', 'contrat_caution_montant', 'contrat_avance_montant',
                'contrat_agence_montant', 'contrat_cie_montant', 'contrat_sodeci_montant']
                .forEach(id => setField(id, ''));

            ['contrat_caution_hidden', 'contrat_avance_hidden', 'contrat_agence_hidden',
                'contrat_caution_cie', 'contrat_caution_sodeci']
                .forEach(id => setHidden(id, 0));

            hide('total-entree-section');
        }

        function recalcTotal() {
            const loyer = parseFloat(document.getElementById('contrat_loyer_net').value) || 0;
            const nbreCaution = parseFloat(document.getElementById('contrat_caution_montant').value) || 0;
            const nbreAvance = parseFloat(document.getElementById('contrat_avance_montant').value) || 0;
            const nbreAgence = parseFloat(document.getElementById('contrat_agence_montant').value) || 0;
            const cie = parseFloat(document.getElementById('contrat_caution_cie').value) || 0;
            const sodeci = parseFloat(document.getElementById('contrat_caution_sodeci').value) || 0;

            const total = (loyer * nbreCaution) + (loyer * nbreAvance) + (loyer * nbreAgence) + cie + sodeci;

            const section = document.getElementById('total-entree-section');
            const valEl = document.getElementById('total-entree-value');

            if (total > 0) {
                section.style.display = 'flex';
                valEl.textContent = new Intl.NumberFormat('fr-FR').format(total) + ' FCFA';
            } else {
                section.style.display = 'none';
            }
        }

        function show(id) {
            const el = document.getElementById(id);
            if (el) el.style.display = '';
        }

        function hide(id) {
            const el = document.getElementById(id);
            if (el) el.style.display = 'none';
        }

        function resetField(fieldId, selectId) {
            hide(fieldId);
            const sel = document.getElementById(selectId);
            if (sel) sel.innerHTML = '<option value="">—</option>';
        }

        function setResume(spanId, text, visible = false) {
            const el = document.getElementById(spanId);
            if (!el) return;
            if (text) {
                el.textContent = text;
                el.style.display = '';
                if (spanId === 'resume-propriete') {
                    const sep = document.getElementById('sep-bat');
                    if (sep) sep.style.display = '';
                }
                if (spanId === 'resume-batiment') {
                    const sep = document.getElementById('sep-bat');
                    if (sep) sep.style.display = '';
                }
                if (spanId === 'resume-porte') {
                    const sep = document.getElementById('sep-porte');
                    if (sep) sep.style.display = '';
                }
            } else {
                el.textContent = '';
                el.style.display = 'none';
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            ['contrat_nbre_caution', 'contrat_nbre_avance', 'contrat_nbre_agence'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.addEventListener('input', recalcTotal);
            });
        });
    </script>
@endpush
