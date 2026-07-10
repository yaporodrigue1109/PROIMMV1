<div class="modal" data-modal="modal-add-fonction" aria-hidden="true">
    <div class="modal-box u-modal-sm">
        <div class="modal-header">
            <h3>Nouvelle fonction</h3>
            <button class="modal-close" data-close-modal aria-label="Fermer">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="u-icon-sm"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
            </button>
        </div>
        <div class="modal-body">
            <form action="{{route('agence.maintenance.fonction.store')}}" method="POST">
                @csrf
                <div class="form-grid u-form-single">
                    <div class="form-field">
                        <span>Libellé *</span>
                        <x-ui.input
                                name="name"
                                type="text"
                                placeholder="Ex: Plombier, Électricien, Serrurier..."
                                required
                        />
                    </div>
                    <div class="form-field">
                        <span>Description</span>
                        <x-ui.textarea name="description" placeholder="Description facultative..." />
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
