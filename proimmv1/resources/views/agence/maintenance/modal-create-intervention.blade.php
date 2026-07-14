<div class="modal" data-modal="modal-add-intervention" aria-hidden="true">
    <div class="modal-box u-modal-lg">
        <div class="modal-header">
            <h3>Nouvelle intervention</h3>
            <button class="modal-close" data-close-modal aria-label="Fermer">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="u-icon-sm"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>

            </button>
        </div>

        <div class="modal-body">
            <form action="{{route('agence.maintenance.store')}}" method="POST" id="interventionForm">
                @csrf

                @php
                    $pick = fn ($item, $keys, $fallback = '') => collect($keys)
                        ->map(fn ($key) => data_get($item, $key))
                        ->first(fn ($value) => filled($value), $fallback);

                    $proprietaireOptions = collect($proprietaires ?? [])->mapWithKeys(function ($p) use ($pick) {
                        $id = $pick($p, ['proprietaire_id', 'id'], '');
                        $label = trim((string) ($pick($p, ['name', 'libelle', 'label'], '')) . ' ' . (string) ($pick($p, ['tel1', 'telephone', 'tel'], '')));

                        return [$id => $label !== '' ? $label : (string) $id];
                    })->toArray();

                    $lotOptions = collect($lots ?? [])->mapWithKeys(function ($lot) use ($pick) {
                        $id = $pick($lot, ['lot_id', 'id'], '');
                        $label = trim((string) ($pick($lot, ['libelle', 'name', 'reference'], '')) . ' – ' . (string) ($pick($lot, ['commune', 'ville', 'address'], '')));

                        return [$id => $label !== '' ? $label : (string) $id];
                    })->toArray();
                @endphp

                <div class="intervention-section">
                    <div class="intervention-section-header">
                        <div>
                            <h4>Informations générales</h4>
                            <p>Ces informations concernent l’intervention globale.</p>
                        </div>
                    </div>

                    <div class="form-field" style="margin-bottom: 15px">
                        <span>Titre de l’intervention *</span>
                        <x-ui.input
                                name="titre"
                                type="text"
                                placeholder="Ex : Rénovation appartement 3 pièces"
                                required
                        />
                    </div>
                    <div class="form-field">
                        <span>Maintenance prise en charge par</span>
                        <x-ui.select
                                name="prise_en_charge_par"
                                :options="[
                                'proprietaire' => 'Proprietaire',
                                'locataire' => 'Locataire',
                                'agence' => 'Agence',
                            ]"
                                value="proprietaire"
                        />
                    </div>
                    <div class="form-grid u-form-grid-2" style="margin-bottom: 15px">


                        <div class="form-field">
                            <label for="proprietaire_id">Propriétaire *</label>
                            <x-ui.select
                                    name="proprietaire_id"
                                    id="proprietaire_id"
                                    :options="$proprietaireOptions"
                                    :value="old('proprietaire_id')"
                                    placeholder="Sélectionner un propriétaire"
                                    required
                                    onchange="getRequest('{{ url('/') }}/admin/list/lotByProprietaire?parent_id='+this.value,'lot_id','select',this.value)"
                            />
                            @error('proprietaire_id')
                            <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-field ">
                            <label for="lot_id">Lot / Zone</label>
                            <x-ui.select
                                    name="lot_id"
                                    id="lot_id"
                                    :options="$lotOptions"
                                    :value="old('lot_id')"
                                    placeholder="Aucun lot spécifique"
                                    onchange="getRequest('{{ url('/') }}/admin/list/getBatimentBylot?parent_id='+this.value,'batiment_id','select',this.value)"
                            />
                            @error('lot_id')
                            <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-field">
                            <span>Batiment concernée </span>
                            <x-ui.select
                                    name="batiment_id"
                                    id="batiment_id"
                                    :options="[]"
                                    placeholder="Sélectionner un batiment"
                                    onchange="getRequest('{{ url('/') }}/admin/list/getPorteByBatiment?parent_id='+this.value,'porte_id','select',this.value)"
                            />
                        </div>
                        <div class="form-field">
                            <span>Porte concernée </span>
                            <x-ui.select
                                    name="porte_id"
                                    id="porte_id"
                                    :options="[]"
                                    placeholder="Sélectionner une porte"
                            />
                        </div>


                    </div>
                    <div class="form-field u-full-width">
                        <span>Description générale</span>
                        <x-ui.textarea
                                name="description_generale"
                                placeholder="Observation générale sur l’intervention..."
                        />
                    </div>
                </div>

                <div class="intervention-section">
                    <div class="intervention-section-header">
                        <div>
                            <h4>Détails des travaux</h4>
                            <p>Ajoutez un bloc par travail : carrelage, peinture, plomberie, etc.</p>
                        </div>

                        <button type="button" class="btn btn-outline" id="addInterventionTask">
                            + Ajouter un travail
                        </button>
                    </div>

                    <div id="interventionTasksWrapper">

                        <div class="intervention-task-card" data-task-index="0">
                            <div class="intervention-task-head">
                                <strong>Travail 1</strong>

                                <button type="button"
                                        class="task-remove-btn"
                                        data-remove-task
                                        hidden>
                                    Supprimer
                                </button>
                            </div>

                            <div class="form-grid u-form-grid-2" style="margin-bottom: 15px">
                                <div class="form-field">
                                    <span>Type d’intervention *</span>
                                    <x-ui.select
                                            name="details[0][type_intervention_id]"
                                            :options="collect($typesInterventionStatiques)->mapWithKeys(fn ($t) => [($t['type_maintenance_id'] ?? $t['id'] ?? '') => ($t['name'] ?? $t['libelle'] ?? '')])->toArray()"
                                            placeholder="Sélectionner un type"
                                            required
                                    />
                                </div>

                                <div class="form-field">
                                    <span>Maintenancier *</span>
                                    <x-ui.select
                                            name="details[0][maintenancier_id]"
                                            :options="collect($maintenanciersStatiques)->mapWithKeys(fn ($m) => [$m['maintenancier_id'] => trim(($m['name'] ?? '') . ' ' . ($m['tel1'] ?? ''))])->toArray()"
                                            placeholder="Sélectionner un maintenancier"
                                            required
                                    />
                                </div>

                                <div class="form-field">
                                    <span>Date début *</span>
                                    <x-ui.input name="details[0][date_debut]" type="date" required />
                                </div>

                                <div class="form-field">
                                    <span>Date fin</span>
                                    <x-ui.input name="details[0][date_fin]" type="date" />
                                </div>

                                <div class="form-field">
                                    <span>Priorité</span>
                                    <x-ui.select
                                            name="details[0][priorite]"
                                            :options="[
                                            'basse' => 'Basse',
                                            'normale' => 'Normale',
                                            'haute' => 'Haute',
                                        ]"
                                            value="normale"
                                    />
                                </div>

                                <div class="form-field">
                                    <span>Prix *</span>
                                    <x-ui.input
                                            name="details[0][prix]"
                                            type="number"
                                            class="task-price"
                                            min="0"
                                            step="1"
                                            placeholder="Ex : 25000"
                                            required
                                    />
                                </div>


                            </div>
                            <div class="form-field u-full-width">
                                <span>Détail du travail</span>
                                <x-ui.textarea
                                        name="details[0][description]"
                                        placeholder="Ex : Poser les carreaux dans la douche..."
                                />
                            </div>
                        </div>

                    </div>

                    <div class="intervention-total-box">
                        <span>Total intervention</span>
                        <strong id="interventionTotal">0 FCFA</strong>
                    </div>
                </div>

                <div class="u-flex-end u-mt-lg">
                    <button type="button" class="btn btn-outline" data-close-modal>Annuler</button>
                    <button type="submit" class="btn btn-primary">Planifier</button>
                </div>
            </form>
        </div>
    </div>
</div>
