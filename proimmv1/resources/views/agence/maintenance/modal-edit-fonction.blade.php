{{-- ============================================================
     MODAL : Modifier une fonction de maintenancier
     ============================================================ --}}
<div class="modal" data-modal="modal-edit-fonction" aria-hidden="true">
    <div class="modal-overlay" data-close-modal></div>
    <div class="modal-box u-modal-sm">
        <div class="modal-header">
            <h3>Modifier la fonction</h3>
            <button class="modal-close" data-close-modal aria-label="Fermer">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="u-icon-sm">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <form id="formEditFonction" method="POST">
                @csrf
                @method('PUT')
                <div class="form-grid u-form-single">
                    <div class="form-field">
                        <span>Libellé *</span>
                        <x-ui.input name="name" id="edit_fonction_name" type="text" required />
                    </div>
                    <div class="form-field">
                        <span>Catégorie</span>
                        <x-ui.input
                                name="categorie"
                                id="edit_fonction_categorie"
                                type="text"
                                placeholder="Ex: technique, administratif..."
                        />
                    </div>
                    <div class="form-field">
                        <span>Description</span>
                        <x-ui.textarea
                                name="description"
                                id="edit_fonction_description"
                                placeholder="Description facultative..."
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
