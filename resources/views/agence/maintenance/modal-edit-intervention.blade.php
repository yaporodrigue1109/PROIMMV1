{{-- ============================================================
     MODAL : Modifier une intervention
     ============================================================ --}}
<div class="modal" aria-hidden="true" data-modal="modal-edit-intervention">
    <div class="modal-overlay" data-close-modal></div>
    <div class="modal-box u-modal-lg">
        <div class="modal-header">
            <h3>Modifier l'intervention</h3>
            <button class="modal-close" data-close-modal aria-label="Fermer">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="u-icon-sm">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <form id="formEditIntervention" method="POST">
                @csrf
                @method('PUT')
                <div class="form-grid u-form-grid-2">
                    <div class="form-field">
                        <span>Titre *</span>
                        <x-ui.input name="titre" id="edit_intervention_titre" type="text" required />
                    </div>

                    <div class="form-field">
                        <span>Type d'intervention *</span>
                        <x-ui.select
                                name="type_maintenance_id"
                                id="edit_intervention_type"
                                :options="collect($typeMaintenance)->mapWithKeys(fn ($t) => [$t['type_maintenance_id'] => $t['name']])->toArray()"
                                placeholder="Sélectionner un type"
                                required
                        />
                    </div>

                    <div class="form-field u-full-width">
                        <span>Description</span>
                        <x-ui.textarea
                                name="description"
                                id="edit_intervention_description"
                                rows="3"
                                placeholder="Description de l'intervention..."
                        />
                    </div>

                    <div class="form-field">
                        <span>Montant global</span>
                        <x-ui.input
                                name="montant_global"
                                id="edit_intervention_montant"
                                type="number"
                                step="0.01"
                                placeholder="0"
                        />
                    </div>

                    <div class="form-field">
                        <span>Responsabilité</span>
                        <x-ui.select
                                name="prise_en_charge_par"
                                id="edit_intervention_prise_en_charge"
                                :options="[
                                'proprietaire' => 'Propriétaire',
                                'locataire' => 'Locataire',
                                'agence' => 'Agence',
                            ]"
                        />
                    </div>

                    <div class="form-field">
                        <span>Statut</span>
                        <x-ui.select
                                name="statut"
                                id="edit_intervention_statut"
                                :options="[
                                'planifiee' => 'Planifiée',
                                'en_cours' => 'En cours',
                                'terminee' => 'Terminée',
                                'annulee' => 'Annulée',
                            ]"
                        />
                    </div>

                    <div class="form-field">
                        <span>Propriétaire</span>
                        <select name="proprietaire_id" id="edit_intervention_proprietaire">
                            <option value="">Sélectionner un propriétaire</option>
                            @foreach($proprietaires as $p)
                                <option value="{{ $p->proprietaire_id }}">{{ $p->name }} - {{ $p->email }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-field">
                        <span>Propriété</span>
                        <select name="propriete_id" id="edit_intervention_propriete">
                            <option value="">Sélectionner une propriété</option>
                        </select>
                    </div>

                    <div class="form-field">
                        <span>Date de début</span>
                        <x-ui.input name="date_debut" id="edit_intervention_date_debut" type="date" />
                    </div>

                    <div class="form-field">
                        <span>Date de fin prévue</span>
                        <x-ui.input name="date_fin_prevue" id="edit_intervention_date_fin" type="date" />
                    </div>
                </div>

                {{-- Section des tâches --}}
                <div class="intervention-section">
                    <div class="intervention-section-header">
                        <div>
                            <h4>Travaux à effectuer</h4>
                            <p>Détaillez les différentes tâches de cette intervention</p>
                        </div>
                        <button type="button" class="btn btn-outline btn-sm" id="editAddInterventionTask">
                            + Ajouter un travail
                        </button>
                    </div>
                    <div id="editInterventionTasksWrapper">
                        {{-- Les tâches seront chargées dynamiquement --}}
                    </div>
                    <div class="intervention-total-box">
                        <span>Total estimé :</span>
                        <strong id="editInterventionTotal">0 FCFA</strong>
                    </div>
                </div>

                <div class="u-flex-end u-mt-lg">
                    <button type="button" class="btn btn-outline" data-close-modal>Annuler</button>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>
