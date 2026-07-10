<div class="modal" data-modal="modal-add-maintenancier" aria-hidden="true">
    <div class="modal-box u-modal-md">
        <div class="modal-header">
            <h3>Nouveau maintenancier</h3>
            <button class="modal-close" data-close-modal aria-label="Fermer">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="u-icon-sm"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
            </button>
        </div>
        <div class="modal-body">
            <form action="{{route('agence.maintenance.maintenancier.store')}}" method="POST">
                @csrf
                <div class="form-grid u-form-grid-2">
                    <div class="form-field">
                        <span>Nom && Prenom *</span>
                        <x-ui.input name="name" type="text" placeholder="name" required />
                    </div>

                    <div class="form-field">
                        <span>Email</span>
                        <x-ui.input name="email" type="email" placeholder="Email" />
                    </div>
                    <div class="form-field">
                        <span>Telephone 1</span>
                        <x-ui.input name="tel1" type="tel" placeholder="xxxxxxxxxx " />
                    </div>
                    <div class="form-field">
                        <span>Téléphone 2</span>
                        <x-ui.input name="tel2" type="tel" placeholder="xxxxxxxxxx" />
                    </div>
                    <div class="form-field">
                        <span>Fonction *</span>
                        <x-ui.select
                                name="fonction_maintenance_id"
                                :options="collect($fonctionsStatiques)->mapWithKeys(fn ($f) => [$f['fonction_maintenance_id'] => $f['name']])->toArray()"
                                placeholder="Sélectionner une fonction"
                                required
                        />
                    </div>
                    <div class="form-field">
                        <span>Entreprise</span>
                        <x-ui.input name="entreprise" type="text" placeholder="Entreprise (si applicable)" />
                    </div>

                    <div class="form-field">
                        <span>Disponibilité</span>
                        <x-ui.select
                                name="statut"
                                :options="['1' => 'Disponible', '0' => 'Indisponible']"
                                value="1"
                        />
                    </div>
                    <div class="form-field">
                        <label for="type_piece_id">Type de Pièce *</label>
                        <x-ui.select
                                name="type_piece_id"
                                id="type_piece_id"
                                :options="collect($typePiece)->mapWithKeys(fn ($prop) => [$prop->type_pieces_id => $prop->name])->toArray()"
                                placeholder="Sélectionner un type de pièce"
                                required
                        />
                    </div>

                    <div class="form-field">
                        <label for="numero_piece">Numéro de pièce *</label>
                        <x-ui.input
                                name="numero_piece"
                                id="num_piece"
                                :value="old('numero_piece')"
                                placeholder="CNI, Passeport…"
                                required
                        />
                    </div>

                    <div class="form-field">
                        <label for="date_validite_piece">Date d'expiration</label>
                        <x-ui.input
                                name="date_validite_piece"
                                id="date_validite_piece"
                                type="date"
                                :value="old('date_validite_piece')"
                        />
                    </div>

                    <div class="form-field u-full-width">
                        <span>Adresse</span>
                        <x-ui.textarea name="adresse" placeholder="Adresse complète" />
                    </div>
                    {{--                        <div class="form-field u-full-width">--}}
                    {{--                            <span>Notes</span>--}}
                    {{--                            <textarea name="notes" placeholder="Notes complémentaires"></textarea>--}}
                    {{--                        </div>--}}
                </div>
                <div class="u-flex-end u-mt-lg">
                    <button type="button" class="btn btn-outline" data-close-modal>Annuler</button>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>
