{{-- ============================================================
     MODAL : Modifier un maintenancier
     ============================================================ --}}
<div class="modal" aria-hidden="true" data-modal="modal-edit-maintenancier">
    <div class="modal-overlay" data-close-modal></div>
    <div class="modal-box u-modal-md">
        <div class="modal-header">
            <h3>Modifier le maintenancier</h3>
            <button class="modal-close" data-close-modal aria-label="Fermer">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="u-icon-sm">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <form id="formEditMaintenancier" method="POST">
                @csrf
                @method('PUT')
                <div class="form-grid u-form-grid-2">
                    <div class="form-field">
                        <span>Nom & Prénom *</span>
                        <x-ui.input name="name" id="edit_maintenancier_name" type="text" required />
                    </div>

                    <div class="form-field">
                        <span>Email</span>
                        <x-ui.input name="email" id="edit_maintenancier_email" type="email" />
                    </div>

                    <div class="form-field">
                        <span>Téléphone 1</span>
                        <x-ui.input name="tel1" id="edit_maintenancier_tel1" type="tel" />
                    </div>

                    <div class="form-field">
                        <span>Téléphone 2</span>
                        <x-ui.input name="tel2" id="edit_maintenancier_tel2" type="tel" />
                    </div>

                    <div class="form-field">
                        <span>Fonction *</span>
                        <x-ui.select
                                name="fonction_maintenance_id"
                                id="edit_maintenancier_fonction"
                                :options="collect($fonctionMaintenance)->mapWithKeys(fn ($f) => [$f['id'] => $f['libelle']])->toArray()"
                                placeholder="Sélectionner une fonction"
                                required
                        />
                    </div>

                    <div class="form-field">
                        <span>Entreprise</span>
                        <x-ui.input
                                name="entreprise"
                                id="edit_maintenancier_entreprise"
                                type="text"
                                placeholder="Entreprise (si applicable)"
                        />
                    </div>

                    <div class="form-field">
                        <span>Disponibilité</span>
                        <x-ui.select
                                name="statut"
                                id="edit_maintenancier_statut"
                                :options="['1' => 'Disponible', '0' => 'Indisponible']"
                        />
                    </div>

                    <div class="form-field">
                        <label for="edit_type_piece_id">Type de Pièce *</label>
                        <x-ui.select
                                name="type_piece_id"
                                id="edit_type_piece_id"
                                :options="collect($typePiece)->mapWithKeys(fn ($prop) => [$prop->type_pieces_id => $prop->name])->toArray()"
                                placeholder="Sélectionner un type de pièce"
                                required
                        />
                    </div>

                    <div class="form-field">
                        <label for="edit_numero_piece">Numéro de pièce *</label>
                        <x-ui.input
                                name="numero_piece"
                                id="edit_numero_piece"
                                type="text"
                                placeholder="CNI, Passeport…"
                                required
                        />
                    </div>

                    <div class="form-field">
                        <label for="edit_date_validite_piece">Date d'expiration</label>
                        <x-ui.input
                                name="date_validite_piece"
                                id="edit_date_validite_piece"
                                type="date"
                        />
                    </div>

                    <div class="form-field u-full-width">
                        <span>Adresse</span>
                        <x-ui.textarea
                                name="adresse"
                                id="edit_maintenancier_adresse"
                                placeholder="Adresse complète"
                        />
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
