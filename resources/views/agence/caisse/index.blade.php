@extends('agence.layouts.app')

@section('title', 'Caisse')

@section('content')

    @php
        // STATIQUE : change à false pour voir l'écran "Caisse fermée"
        $caisseOuverte = true;

        $soldeOuverture = 125000;
        $totalEntrees = 299000;
        $totalSorties = 0;
        $soldeTheorique = $soldeOuverture + $totalEntrees - $totalSorties;
    @endphp

    <section class="page">

        <header class="page-header">
            <div class="page-header-copy">
                <div class="page-heading">
                    <div>
                        <h2>Caisse</h2>
{{--                        <p>--}}
{{--                            Suivi des encaissements, sorties, commissions, impayés et reversements propriétaires.--}}
{{--                        </p>--}}
                    </div>
                </div>
            </div>

            <div class="page-actions">

                @if($caisseOuverte)

                    <button class="btn btn-secondary">
                        <svg xmlns="http://www.w3.org/2000/svg"
                             width="18"
                             height="18"
                             fill="none"
                             viewBox="0 0 24 24"
                             stroke="currentColor"
                             stroke-width="2">

                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>

                        {{ now()->format('d/m/Y') }}
                    </button>

                    <!-- Bouton Actions avec dropdown -->
                    <div class="dropdown">
                        <button class="btn btn-outline dropdown-toggle"
                                type="button"
                                id="actionsDropdown">
                            Actions
                        </button>

                        <ul class="dropdown-menu" aria-labelledby="actionsDropdown">
                            <li>
                                <a class="dropdown-item" href="#"
                                   onclick="event.preventDefault(); alert('Rapport journalier');">
                                    Rapport journalier
                                </a>
                            </li>

                            <li>
                                <a class="dropdown-item" href="#"
                                   onclick="event.preventDefault(); alert('Journal de caisse du jour');">
                                    Journal de caisse du jour
                                </a>
                            </li>

                            <li>
                                <a class="dropdown-item" href="#"
                                   onclick="event.preventDefault(); alert('Solde de caisse');">
                                    Solde de caisse
                                </a>
                            </li>
                        </ul>
                    </div>

                    <button type="button"
                            class="btn btn-outline danger"
                            onclick="showCloseCashForm()">
                        Fermer la caisse
                    </button>

                    <button type="button"
                            class="btn btn-primary"
                            data-open-modal="mouvementModal">
                        + Nouveau mouvement
                    </button>

                @endif

            </div>
        </header>

        @if(!$caisseOuverte)
            <section id="cash-closed-state" class="cash-state-wrapper">
                <div class="cash-state-card">
                    <h3>Caisse fermée</h3>
                    <p>Vous devez ouvrir la caisse avant d’enregistrer des paiements.</p>

                    <div id="open-cash-trigger-view">
                        <button type="button" class="btn btn-primary" onclick="showOpenCashForm()">
                            Ouvrir la caisse
                        </button>
                    </div>

                    <div id="open-cash-form-view" class="d-none">
                        <div class="form-grid mt-lg">
                            <label class="form-field">
                                <span>Solde d’ouverture</span>
                                <input type="number" value="{{ $soldeOuverture }}">
                            </label>

                            <label class="form-field form-field-wide">
                                <span>Observation</span>
                                <textarea placeholder="Observation facultative..."></textarea>
                            </label>
                        </div>

                        <div class="cash-actions">
                            <button type="button" class="btn btn-outline" onclick="hideOpenCashForm()">Annuler</button>
                            <button type="button" class="btn btn-primary" onclick="alert('Caisse ouverte en statique')">
                                Valider l’ouverture
                            </button>
                        </div>
                    </div>
                </div>
            </section>
        @endif

        @if($caisseOuverte)
            <section id="close-cash-form-card" class="cash-state-wrapper d-none">
                <div class="cash-state-card">
                    <h3>Arrêté de caisse</h3>
                    <p>Clôture journalière de la caisse agence.</p>

                    <div class="closure-summary">
                        <div><span>Solde ouverture</span><strong>{{ number_format($soldeOuverture, 0, ',', ' ') }} FCFA</strong></div>
                        <div><span>Total entrées</span><strong class="is-success">+ {{ number_format($totalEntrees, 0, ',', ' ') }} FCFA</strong></div>
                        <div><span>Total sorties</span><strong class="is-danger">- {{ number_format($totalSorties, 0, ',', ' ') }} FCFA</strong></div>
                        <div><span>Solde théorique</span><strong>{{ number_format($soldeTheorique, 0, ',', ' ') }} FCFA</strong></div>
                    </div>

                    <div class="form-grid mt-lg">
                        <label class="form-field">
                            <span>Solde réel de fermeture</span>
                            <input type="number" value="{{ $soldeTheorique }}">
                        </label>

                        <label class="form-field form-field-wide">
                            <span>Observation de fermeture</span>
                            <textarea placeholder="Observation facultative..."></textarea>
                        </label>
                    </div>

                    <div class="cash-actions">
                        <button type="button" class="btn btn-outline" onclick="hideCloseCashForm()">Annuler</button>
                        <button type="button" class="btn btn-primary" onclick="alert('Caisse fermée en statique')">
                            Valider & clôturer
                        </button>
                    </div>
                </div>
            </section>
        @endif

        <div id="cash-main-content" class="{{ !$caisseOuverte ? 'd-none' : '' }}">

            <section class="stats-grid" id="main-stats">
                {{-- Les stats seront injectées ici dynamiquement --}}
            </section>


        <section>


            <div class="tabs-container">

                <div class="tabs-header">

                    <button type="button" class="tab-btn active" data-tab="transactions-tab">
                        <!-- Credit Card -->
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                        </svg>
                        Transactions
                    </button>

                    <button type="button" class="tab-btn" data-tab="loyers-tab">
                        <!-- Home -->
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                        </svg>
                        Loyers
                    </button>



                    <button type="button" class="tab-btn" data-tab="maintenance-tab">
                        <!-- Wrench Screwdriver -->
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437 1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008Z" />
                        </svg>
                        Maintenance
                    </button>

                    <button type="button" class="tab-btn" data-tab="depenses-tab">
                        <!-- Shopping Bag -->
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007Z" />
                        </svg>
                        Dépenses agence
                    </button>

                    <button type="button" class="tab-btn" data-tab="ventes-biens-tab">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                        </svg>
                        Vente de biens
                    </button>

                    <button type="button" class="tab-btn" data-tab="summary-tab">
                        <!-- Chart Bar -->
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                        </svg>
                        Résumé par mode de paiement
                    </button>

                </div>

            </div>

                    <!-- Contenu de l'onglet Transactions -->
            <!-- Contenu de l'onglet Transactions -->
            <div id="transactions-tab" class="tab-content active">
                <x-table
                        title="Mouvements"
                        :collection="collect([1])"
                        empty-message="Aucune écriture trouvée."
                        :colspan="7"
                        search-id="caisse-search"
                        class="responsive-table u-table-fit"
                >
                    <x-slot:actions>
                        <div class="filter-pills">
                            <button class="filter-pill active" type="button">Toutes</button>
                            <button class="filter-pill" type="button">Entrées</button>
                            <button class="filter-pill" type="button">Sorties</button>
                        </div>
                    </x-slot:actions>

                    <x-slot:thead>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Libellé</th>
                        <th>Référence</th>
                        <th class="text-end">Entrée</th>
                        <th class="text-end">Sortie</th>
                        <th class="table-actions-col">Actions</th>
                    </x-slot:thead>

                    {{-- Loyer (Entrée) --}}
                    <tr>
                        <td data-label="Date">11/05/2026<small>09:30</small></td>
                        <td data-label="Type"><span class="badge success">Entrée</span></td>
                        <td data-label="Libellé">Paiement loyer — Kouamé Jean (Appt. B2, Mai 2026)</td>
                        <td data-label="Référence">TRX-0001</td>
                        <td data-label="Entrée"
                            class="text-end text-success fw-semibold">
                            + 85 000 FCFA
                        </td>
                        <td data-label="Sortie" class="text-end">—</td>
                        <td data-label="Actions">
                            <div class="table-actions">
                                <button class="action-btn info" title="Voir">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.644C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .638C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>

                    {{-- Facture payée (Sortie) --}}
                    <tr>
                        <td data-label="Date">11/05/2026<small>11:00</small></td>
                        <td data-label="Type"><span class="badge danger">Sortie</span></td>
                        <td data-label="Libellé">Paiement facture — CIE (FAC-2026-0041, Électricité)</td>
                        <td data-label="Référence">TRX-0002</td>
                        <td data-label="Entrée" class="text-end">—</td>
                        <td data-label="Sortie"
                            class="text-end text-danger fw-semibold">
                            - 28 000 FCFA
                        </td>
                        <td data-label="Actions">
                            <div class="table-actions">
                                <button class="action-btn info" title="Voir">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.644C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .638C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>

                    {{-- Maintenance (Sortie) --}}
                    <tr>
                        <td data-label="Date">11/05/2026<small>08:00</small></td>
                        <td data-label="Type"><span class="badge danger">Sortie</span></td>
                        <td data-label="Libellé">Maintenance — Plomberie, Villa C3 Riviera (Kouassi & Fils)</td>
                        <td data-label="Référence">TRX-0003</td>
                        <td data-label="Entrée" class="text-end">—</td>
                        <td data-label="Sortie"
                            class="text-end text-danger fw-semibold">
                            - 15 000 FCFA
                        </td>
                        <td data-label="Actions">
                            <div class="table-actions">
                                <button class="action-btn info" title="Voir">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.644C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .638C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>

                    {{-- Dépense maison (Sortie) --}}
                    <tr>
                        <td data-label="Date">11/05/2026<small>14:20</small></td>
                        <td data-label="Type"><span class="badge danger">Sortie</span></td>
                        <td data-label="Libellé">Dépense — Fournitures, Ramettes de papier A4</td>
                        <td data-label="Référence">TRX-0004</td>
                        <td data-label="Entrée" class="text-end">—</td>
                        <td data-label="Sortie"
                            class="text-end text-danger fw-semibold">
                            - 4 500 FCFA
                        </td>
                        <td data-label="Actions">
                            <div class="table-actions">
                                <button class="action-btn info" title="Voir">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.644C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .638C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>

                </x-table>
            </div>


                    <!-- Contenu de l'onglet Résumé par Mode de Paiement -->
                    <div id="summary-tab" class="tab-content">
                        <div class="summary-stats-grid">

                            <!-- Carte Espèces -->
                            <div class="summary-card">
                                <div class="summary-card-header">
                                    <div class="summary-icon bg-info">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <path d="M12 6v6l4 2"></path>
                                        </svg>
                                    </div>
                                    <h3>Espèces</h3>
                                </div>
                                <div class="summary-card-body">
                                    <div class="summary-row">
                                        <span>Montant total :</span>
                                        <strong class="is-success">54 000 FCFA</strong>
                                    </div>
                                    <div class="summary-row">
                                        <span>Nombre transactions :</span>
                                        <span>2</span>
                                    </div>
                                    <div class="summary-row">
                                        <span>Part agence (10%) :</span>
                                        <span>5 400 FCFA</span>
                                    </div>
                                    <div class="summary-row">
                                        <span>Part propriétaires :</span>
                                        <span>48 600 FCFA</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Carte WAVE -->
                            <div class="summary-card">
                                <div class="summary-card-header">
                                    <div class="summary-icon bg-primary">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M2 20L22 4"></path>
                                            <path d="M6 12L2 8"></path>
                                            <path d="M22 12L18 16"></path>
                                        </svg>
                                    </div>
                                    <h3>WAVE</h3>
                                </div>
                                <div class="summary-card-body">
                                    <div class="summary-row">
                                        <span>Montant total :</span>
                                        <strong class="is-success">50 000 FCFA</strong>
                                    </div>
                                    <div class="summary-row">
                                        <span>Nombre transactions :</span>
                                        <span>1</span>
                                    </div>
                                    <div class="summary-row">
                                        <span>Part agence (10%) :</span>
                                        <span>5 000 FCFA</span>
                                    </div>
                                    <div class="summary-row">
                                        <span>Part propriétaires :</span>
                                        <span>45 000 FCFA</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Carte Orange Money -->
                            <div class="summary-card">
                                <div class="summary-card-header">
                                    <div class="summary-icon bg-warning">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="2" y="6" width="20" height="12" rx="2"></rect>
                                            <path d="M8 10h.01"></path>
                                            <path d="M16 10h.01"></path>
                                        </svg>
                                    </div>
                                    <h3>Orange Money</h3>
                                </div>
                                <div class="summary-card-body">
                                    <div class="summary-row">
                                        <span>Montant total :</span>
                                        <strong class="is-success">50 000 FCFA</strong>
                                    </div>
                                    <div class="summary-row">
                                        <span>Nombre transactions :</span>
                                        <span>1</span>
                                    </div>
                                    <div class="summary-row">
                                        <span>Part agence (10%) :</span>
                                        <span>5 000 FCFA</span>
                                    </div>
                                    <div class="summary-row">
                                        <span>Part propriétaires :</span>
                                        <span>45 000 FCFA</span>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- Tableau récapitulatif global -->
                        <div class="summary-total-table">
                            <h4>Récapitulatif global</h4>
                            <table class="data-table">
                                <thead>
                                <tr>
                                    <th>Mode de paiement</th>
                                    <th>Montant total</th>
                                    <th>Nb transactions</th>
                                    <th>Commission agence</th>
                                    <th>Net propriétaire</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td>Espèces</td>
                                    <td><strong>54 000 FCFA</strong></td>
                                    <td>2</td>
                                    <td>5 400 FCFA</td>
                                    <td>48 600 FCFA</td>
                                </tr>
                                <tr>
                                    <td>WAVE</td>
                                    <td><strong>50 000 FCFA</strong></td>
                                    <td>1</td>
                                    <td>5 000 FCFA</td>
                                    <td>45 000 FCFA</td>
                                </tr>
                                <tr>
                                    <td>Orange Money</td>
                                    <td><strong>50 000 FCFA</strong></td>
                                    <td>1</td>
                                    <td>5 000 FCFA</td>
                                    <td>45 000 FCFA</td>
                                </tr>
                                <tr class="summary-total-row">
                                    <td><strong>TOTAL</strong></td>
                                    <td><strong>154 000 FCFA</strong></td>
                                    <td><strong>4</strong></td>
                                    <td><strong>15 400 FCFA</strong></td>
                                    <td><strong>138 600 FCFA</strong></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
            <div id="loyers-tab" class="tab-content">


                <x-table
                        title="Paiements de loyers"
                        :collection="collect([1])"
                        empty-message="Aucun paiement de loyer trouvé."
                        :colspan="7"
                        search-id="loyers-search"
                        class="responsive-table u-table-fit"
                >
                    <x-slot:actions>
                        <div class="filter-pills">
                            <button class="filter-pill active" type="button">Tous</button>
                            <button class="filter-pill" type="button">Payés</button>
                            <button class="filter-pill" type="button">En attente</button>
                            <button class="filter-pill" type="button">En retard</button>
                        </div>
                    </x-slot:actions>

                    <x-slot:thead>
                        <th>Date</th>
                        <th>Locataire</th>
                        <th>Bien</th>
                        <th>Période</th>
                        <th class="text-end">Montant</th>
                        <th>Mode</th>
                        <th class="table-actions-col">Actions</th>
                    </x-slot:thead>

                    <tr>
                        <td data-label="Date">
                            11/05/2026
                            <small>09:30</small>
                        </td>
                        <td data-label="Locataire">Kouamé Jean</td>
                        <td data-label="Bien">Appt. B2 — Cocody</td>
                        <td data-label="Période">Mai 2026</td>
                        <td data-label="Montant" class="text-end is-success fw-semibold">85 000 FCFA</td>
                        <td data-label="Mode">
                            <span class="badge info">Espèces</span>
                        </td>
                        <td data-label="Actions">
                            <div class="table-actions">
                                <button class="action-btn info" title="Voir">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.644C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .638C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>

                </x-table>

            </div>



            <div id="ventes-biens-tab" class="tab-content">
                <x-table
                        title="Ventes de biens"
                        :collection="collect([1])"
                        empty-message="Aucune vente de bien trouvée."
                        :colspan="7"
                        search-id="ventes-biens-search"
                        class="responsive-table u-table-fit"
                >
                    <x-slot:actions>
                        <div class="filter-pills">
                            <button class="filter-pill active" type="button">Toutes</button>
                            <button class="filter-pill" type="button">Payées</button>
                            <button class="filter-pill" type="button">En attente</button>
                        </div>
                    </x-slot:actions>

                    <x-slot:thead>
                        <th>Date</th>
                        <th>Client</th>
                        <th>Bien</th>
                        <th>Référence</th>
                        <th class="text-end">Montant</th>
                        <th>Statut</th>
                        <th class="table-actions-col">Actions</th>
                    </x-slot:thead>

                    <tr>
                        <td data-label="Date">11/05/2026<small>15:00</small></td>
                        <td data-label="Client">Koné Moussa</td>
                        <td data-label="Bien">Terrain — Bingerville</td>
                        <td data-label="Référence">VTE-0001</td>
                        <td data-label="Montant" class="text-end is-success fw-semibold">2 500 000 FCFA</td>
                        <td data-label="Statut"><span class="badge success">Payée</span></td>
                        <td data-label="Actions">
                            <div class="table-actions">
                                <button class="action-btn info" title="Voir">  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.644C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .638C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg></button>
                            </div>
                        </td>
                    </tr>
                </x-table>
            </div>



            <div id="maintenance-tab" class="tab-content">

                <x-table
                        title="Interventions maintenance"
                        :collection="collect([1])"
                        empty-message="Aucune intervention trouvée."
                        :colspan="7"
                        search-id="maintenance-search"
                        class="responsive-table u-table-fit"
                >
                    <x-slot:actions>
                        <div class="filter-pills">
                            <button class="filter-pill active" type="button">Toutes</button>
                            <button class="filter-pill" type="button">En cours</button>
                            <button class="filter-pill" type="button">Terminées</button>
                        </div>
                    </x-slot:actions>

                    <x-slot:thead>
                        <th>Date</th>
                        <th>Bien</th>
                        <th>Type</th>
                        <th>Prestataire</th>
                        <th class="text-end">Coût</th>
                        <th>Statut</th>
                        <th class="table-actions-col">Actions</th>
                    </x-slot:thead>

                    <tr>
                        <td data-label="Date">
                            11/05/2026
                            <small>08:00</small>
                        </td>
                        <td data-label="Bien">Villa C3 — Riviera</td>
                        <td data-label="Type">Plomberie</td>
                        <td data-label="Prestataire">Kouassi & Fils</td>
                        <td data-label="Coût" class="text-end is-danger fw-semibold">15 000 FCFA</td>
                        <td data-label="Statut">
                            <span class="badge success">Terminée</span>
                        </td>
                        <td data-label="Actions">
                            <div class="table-actions">
                                <button class="action-btn info" title="Voir">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.644C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .638C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>

                </x-table>

            </div>


            <div id="depenses-tab" class="tab-content">




                <x-table
                        title="Dépenses agence"
                        :collection="collect([1])"
                        empty-message="Aucune dépense trouvée."
                        :colspan="6"
                        search-id="depenses-search"
                        class="responsive-table u-table-fit"
                >
                    <x-slot:actions>
                        <div class="filter-pills">
                            <button class="filter-pill active" type="button">Toutes</button>
                            <button class="filter-pill" type="button">Paiement factures</button>
                            <button class="filter-pill" type="button">Fournitures</button>
                            <button class="filter-pill" type="button">Transport</button>
                            <button class="filter-pill" type="button">Divers</button>
                        </div>
                    </x-slot:actions>

                    <x-slot:thead>
                        <th>Date</th>
                        <th>Catégorie</th>
                        <th>Libellé</th>
                        <th>Justificatif</th>
                        <th class="text-end">Montant</th>
                        <th class="table-actions-col">Actions</th>
                    </x-slot:thead>

                    <tr>
                        <td data-label="Date">
                            11/05/2026
                            <small>14:20</small>
                        </td>
                        <td data-label="Catégorie">Fournitures</td>
                        <td data-label="Libellé">Ramettes de papier A4</td>
                        <td data-label="Justificatif">
                            <span class="badge info">Reçu</span>
                        </td>
                        <td data-label="Montant" class="text-end is-danger fw-semibold">4 500 FCFA</td>
                        <td data-label="Actions">
                            <div class="table-actions">
                                <button class="action-btn info" title="Voir">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.644C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .638C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td data-label="Date">
                            11/05/2026
                            <small>11:00</small>
                        </td>
                        <td data-label="Catégorie">Paiement facture</td>
                        <td data-label="Libellé">CIE — FAC-2026-0041, Électricité</td>
                        <td data-label="Justificatif">
                            <span class="badge info">Facture</span>
                        </td>
                        <td data-label="Montant" class="text-end is-danger fw-semibold">28 000 FCFA</td>
                        <td data-label="Actions">
                            <div class="table-actions">
                                <button class="action-btn info" title="Voir">  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.644C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .638C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg></button>
                            </div>
                        </td>
                    </tr>

                </x-table>

            </div>



        </section>

        </div>

        <!-- Modal Nouveau Mouvement -->
        <!-- Modal Nouveau mouvement -->
        <div class="modal" data-modal="mouvementModal" aria-hidden="true">

            <div class="modal-box u-modal-md">

                <div class="modal-header">
                    <h3>Nouveau mouvement</h3>
                    <button class="modal-close" data-close-modal aria-label="Fermer">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="u-icon-sm"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>

                    </button>
                </div>

                <div class="modal-body">

                    <div class="mouvement-grid">

                 

                        <!-- Loyer -->
                        <a href="{{ route('agence.caisse.loyer') }}"
                           class="mouvement-option">

                            <div class="mouvement-option-icon info">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                     fill="none"
                                     viewBox="0 0 24 24"
                                     stroke-width="1.5"
                                     stroke="currentColor">

                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75" />
                                </svg>
                            </div>

                            <div class="mouvement-option-content">
                                <strong>Paiement loyer</strong>
                                <span>Enregistrer un paiement de loyer</span>
                            </div>

                        </a>

                        <!-- Vente de biens -->
                        <a href="{{ route('agence.caisse.loyer') }}"
                           class="mouvement-option">
                            <div class="mouvement-option-icon success">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                     fill="none"
                                     viewBox="0 0 24 24"
                                     stroke-width="1.5"
                                     stroke="currentColor">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>
                            </div>

                            <div class="mouvement-option-content">
                                <strong>Vente de biens</strong>
                                <span>Enregistrer une vente de bien immobilier</span>
                            </div>
                        </a>
                        <!-- Maintenance -->
                        <a href="{{ route('agence.caisse.maintenance') }}"
                           class="mouvement-option">
                            <div class="mouvement-option-icon neutral">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437 1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008Z" />
                                </svg>
                            </div>
                            <div class="mouvement-option-content">
                                <strong>Maintenance</strong>
                                <span>Enregistrer une dépense maintenance</span>
                            </div>
                        </a>

                        <!-- Dépense maison -->
                        <a href="{{ route('agence.caisse.depense.agence') }}"
                           class="mouvement-option">
                            <div class="mouvement-option-icon dark">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007Z" />
                                </svg>
                            </div>
                            <div class="mouvement-option-content">
                                <strong>Dépense agence</strong>
                                <span>Enregistrer une dépense générale de l’agence</span>
                            </div>
                        </a>
                    </div>

                </div>

            </div>

        </div>

    </section>

    <style>

        /* Styles pour la barre d'outils de la caisse */
        .caisse-toolbar {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
            padding: 1rem;
            border-bottom: 1px solid #e9ecef;
        }

        /* Conteneur du champ de recherche */
        .caisse-toolbar .search-field {
            margin-right: auto;
            flex-shrink: 0;
            min-width: 250px;
        }

        .caisse-toolbar .filter-input {
            flex-shrink: 0;
        }

        /* Styles pour les onglets */
        .tabs-container {
            margin-top: 5px;
            margin-bottom: 0;
        }




        .tab-content {
            display: none;
            animation: fadeIn 0.3s ease;
        }

        .tab-content.active {
            display: block;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Styles pour le résumé par mode de paiement */
        .summary-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            padding: 1.5rem;
        }

        .summary-card {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 12px;
            overflow: hidden;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .tabs-header {
            display: flex;
            align-items: center;
            justify-content: flex-start !important;
            gap: 0.5rem;
            padding: 0 1rem;
            margin-top: 2rem;
        }

        .tabs-header a.tab-btn {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            text-decoration: none;
            color: var(--foreground);
        }


        /* ===== CORRECTION DARK MODE CAISSE ===== */

        .caisse-toolbar {
            border-bottom-color: var(--border) !important;
            background: var(--card);
        }

        .tabs-container {
            border-bottom-color: var(--border) !important;
        }




        /* Résumé par mode de paiement */
        .summary-card {
            background: var(--card) !important;
            border-color: var(--border) !important;
            color: var(--foreground);
        }

        .summary-card-header {
            background: var(--surface-subtle) !important;
            border-bottom-color: var(--border) !important;
        }

        .summary-card-header h3,
        .summary-total-table h4 {
            color: var(--foreground) !important;
        }

        .summary-row {
            border-bottom-color: var(--border) !important;
        }

        .summary-row span:first-child {
            color: var(--muted-foreground) !important;
        }

        .summary-total-row {
            background: var(--surface-subtle) !important;
        }

        .summary-total-row td {
            border-top-color: var(--border) !important;
        }

        /* Dropdown Actions */
        .dropdown-menu {
            background: var(--card) !important;
            color: var(--foreground) !important;
            border-color: var(--border) !important;
            box-shadow: var(--shadow) !important;
        }

        .dropdown-item {
            color: var(--foreground) !important;
        }

        .dropdown-item:hover {
            color: var(--foreground) !important;
            background: var(--surface-hover) !important;
        }



        .summary-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .summary-card-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 1.5rem;
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            border-bottom: 1px solid #e9ecef;
        }

        .summary-icon {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            color: white;
        }

        .summary-icon.bg-info {
            background: linear-gradient(135deg, #17a2b8 0%, #0d8ba0 100%);
        }

        .summary-icon.bg-primary {
            background: linear-gradient(135deg, #0066b3 0%, #0051a1 100%);
        }

        .summary-icon.bg-warning {
            background: linear-gradient(135deg, #fd7e14 0%, #e96a0b 100%);
        }

        .summary-card-header h3 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
            color: #212529;
        }

        .summary-card-body {
            padding: 1.5rem;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px dashed #e9ecef;
        }

        .summary-row:last-child {
            border-bottom: none;
        }

        .summary-row span:first-child {
            color: #6c757d;
            font-size: 0.9rem;
        }

        .summary-row strong {
            font-size: 1rem;
        }

        .summary-total-table {
            padding: 0 1.5rem 1.5rem 1.5rem;
        }

        .summary-total-table h4 {
            margin: 0 0 1rem 0;
            font-size: 1.1rem;
            font-weight: 600;
            color: #212529;
        }

        .summary-total-row {
            background-color: #f8f9fa;
            font-weight: 600;
        }

        .summary-total-row td {
            border-top: 2px solid #dee2e6;
        }

        /* Ajustements responsifs */
        @media (max-width: 768px) {
            .caisse-toolbar {
                flex-direction: column;
                align-items: stretch;
            }

            .caisse-toolbar .search-field {
                margin-right: 0;
                margin-bottom: 0.5rem;
            }

            .tabs-header {
                flex-direction: column;
                gap: 0;
            }



            .summary-stats-grid {
                grid-template-columns: 1fr;
            }
        }

        .search-field {
            max-width: 400px !important;
        }

        /* Styles pour le dropdown */
        .dropdown {
            position: relative;
            display: inline-block;
        }

        .dropdown-toggle::after {
            display: inline-block;
            margin-left: 0.5em;
            vertical-align: middle;
            content: "";
            border-top: 0.3em solid;
            border-right: 0.3em solid transparent;
            border-bottom: 0;
            border-left: 0.3em solid transparent;
        }

        .dropdown-menu {
            position: absolute;
            top: 100%;
            left: 0;
            z-index: 1000;
            display: none;
            float: left;
            min-width: 220px;
            padding: 0.5rem 0;
            margin: 0.125rem 0 0;
            font-size: 0.9rem;
            color: #212529;
            text-align: left;
            list-style: none;
            background-color: #fff;
            background-clip: padding-box;
            border: 1px solid rgba(0,0,0,.15);
            border-radius: 0.375rem;
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,.15);
        }

        .dropdown-menu.show {
            display: block;
        }

        .dropdown-item {
            display: block;
            width: 100%;
            padding: 0.5rem 1rem;
            clear: both;
            font-weight: 400;
            color: #212529;
            text-align: inherit;
            text-decoration: none;
            white-space: nowrap;
            background-color: transparent;
            border: 0;
            cursor: pointer;
        }

        .dropdown-item:hover {
            color: #1e2125;
            background-color: #f8f9fa;
        }

        .btn-outline.dropdown-toggle {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .cash-state-wrapper {
            display: flex;
            justify-content: center;
            margin: 2rem 0;
        }


        .cash-state-card {
            width: 100%;
            max-width: 760px;

            padding: 2rem;

            border: 1px solid var(--border);
            border-radius: 1.5rem;

            background: var(--card);
            box-shadow: var(--shadow);

            text-align: center;
        }

        .cash-state-card h3 {
            margin-bottom: .5rem;
        }

        .cash-state-card p {
            color: var(--muted-foreground);
            margin-bottom: 1.5rem;
        }

        .cash-actions,
        #open-cash-trigger-view {
            display: flex;
            justify-content: center;
            gap: .75rem;
            margin-top: 1rem;
        }

        .closure-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .closure-summary div {
            padding: 1rem;
            border: 1px solid var(--border);
            border-radius: 1rem;
            background: var(--surface-subtle);
        }

        .closure-summary span {
            display: block;
            color: var(--muted-foreground);
            font-size: .8rem;
            margin-bottom: .35rem;
        }



        .d-none {
            display: none !important;
        }

        .btn.danger,
        .btn-outline.danger {
            color: #dc2626;
            border: 1px solid rgba(220, 38, 38, .35);
        }

        .cash-state-card .form-field {
            text-align: left;
        }

        .cash-state-card .form-field span {
            display: block;
            text-align: left;
            margin-bottom: .5rem;
            font-weight: 500;
        }

        .tab-btn {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
        }

        .modal {
            display: none;
        }

        .modal.active {
            display: flex;
        }


        .mouvement-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .mouvement-option {
            display: flex;
            align-items: center;
            gap: 14px;
            width: 100%;
            padding: 16px;
            border-radius: 16px;
            border: 1px solid #e5e7eb;
            background: #ffffff;
            text-align: left;
            cursor: pointer;
            transition: .2s ease;
        }

        .mouvement-option:hover {
            transform: translateY(-2px);
            border-color: #3b82f6;
            box-shadow: 0 12px 24px rgba(15, 23, 42, .08);
        }

        .mouvement-option-icon {
            width: 46px;
            height: 46px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .mouvement-option-icon svg {
            width: 22px;
            height: 22px;
        }

        .mouvement-option-content {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .mouvement-option-content strong {
            font-size: 14px;
            font-weight: 700;
            color: #111827;
        }

        .mouvement-option-content span {
            font-size: 12px;
            color: #6b7280;
            line-height: 1.4;
        }

        .mouvement-divider {
            grid-column: 1 / -1;
            margin: 4px 0 2px;
            padding-top: 16px;
            border-top: 1px solid #e5e7eb;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
            color: #6b7280;
        }

        /* Couleurs */
        .mouvement-option-icon.success {
            background: #dcfce7;
            color: #16a34a;
        }

        .mouvement-option-icon.danger {
            background: #fee2e2;
            color: #dc2626;
        }

        .mouvement-option-icon.info {
            background: #dbeafe;
            color: #2563eb;
        }

        .mouvement-option-icon.warning {
            background: #fef3c7;
            color: #d97706;
        }

        .mouvement-option-icon.neutral {
            background: #f3f4f6;
            color: #4b5563;
        }

        .mouvement-option-icon.dark {
            background: #ede9fe;
            color: #7c3aed;
        }

        /* =========================
           DARK MODE
        ========================= */

        .dark .mouvement-option,
        [data-theme="dark"] .mouvement-option {
            background: #111827;
            border-color: #374151;
        }

        .dark .mouvement-option:hover,
        [data-theme="dark"] .mouvement-option:hover {
            border-color: #60a5fa;
            box-shadow: 0 12px 24px rgba(0, 0, 0, .35);
        }

        .dark .mouvement-option-content strong,
        [data-theme="dark"] .mouvement-option-content strong {
            color: #f9fafb;
        }

        .dark .mouvement-option-content span,
        [data-theme="dark"] .mouvement-option-content span {
            color: #9ca3af;
        }

        .dark .mouvement-divider,
        [data-theme="dark"] .mouvement-divider {
            border-color: #374151;
            color: #9ca3af;
        }

        /* Dark mode icones */
        .dark .mouvement-option-icon.success,
        [data-theme="dark"] .mouvement-option-icon.success {
            background: rgba(34, 197, 94, .15);
        }

        .dark .mouvement-option-icon.danger,
        [data-theme="dark"] .mouvement-option-icon.danger {
            background: rgba(239, 68, 68, .15);
        }

        .dark .mouvement-option-icon.info,
        [data-theme="dark"] .mouvement-option-icon.info {
            background: rgba(59, 130, 246, .15);
        }

        .dark .mouvement-option-icon.warning,
        [data-theme="dark"] .mouvement-option-icon.warning {
            background: rgba(245, 158, 11, .15);
        }

        .dark .mouvement-option-icon.neutral,
        [data-theme="dark"] .mouvement-option-icon.neutral {
            background: rgba(156, 163, 175, .15);
        }

        .dark .mouvement-option-icon.dark,
        [data-theme="dark"] .mouvement-option-icon.dark {
            background: rgba(124, 58, 237, .15);
        }

        /* Responsive */
        @media (max-width: 640px) {

            .mouvement-grid {
                grid-template-columns: 1fr;
            }

        }

        .text-success {
            color: #16a34a !important;
        }

        .text-danger {
            color: #dc2626 !important;
        }

        .fw-semibold {
            font-weight: 600;
        }


    </style>


    <script>
        const tabStats = {
            'transactions-tab': [
                { label: 'Total perçu',        value: '299 000',  color: 'is-success', note: '8 transactions encaissées aujourd\'hui' },
                { label: 'Part agence',         value: '29 900',   color: 'is-info',    note: 'Commission agence calculée à 10%' },
                { label: 'Part propriétaires',  value: '269 100',  color: '',           note: 'Montant estimé à reverser' },
                { label: 'Impayés',             value: '0',        color: 'is-danger',  note: 'Aucun impayé sur la période filtrée' },
            ],
            'loyers-tab': [
                { label: 'Total loyers perçus', value: '245 000', color: 'is-success', note: '6 paiements ce jour' },
                { label: 'En attente',          value: '3',       color: 'is-warning', note: 'Locataires n\'ayant pas encore payé' },
                { label: 'Commission agence',   value: '24 500',  color: 'is-info',    note: '10% des loyers encaissés' },
                { label: 'Net propriétaires',   value: '220 500', color: '',           note: 'Montant à reverser' },
            ],

            'maintenance-tab': [
                { label: 'Dépenses maintenance',    value: '38 000', color: 'is-danger',  note: '3 interventions ce jour' },
                { label: 'Interventions en cours',  value: '1',      color: 'is-warning', note: 'Travaux non encore clôturés' },
                { label: 'Interventions terminées', value: '2',      color: 'is-success', note: 'Clôturées aujourd\'hui' },
                { label: 'Biens concernés',         value: '3',      color: '',           note: 'Logements avec maintenance active' },
            ],
            'depenses-tab': [
                { label: 'Total dépenses agence', value: '49 500', color: 'is-danger', note: 'Dépenses internes et factures payées' },
                { label: 'Paiement factures', value: '28 000', color: 'is-danger', note: 'Factures réglées aujourd’hui' },
                { label: 'Fournitures bureau', value: '8 500', color: '', note: '2 achats ce jour' },
                { label: 'Divers', value: '13 000', color: '', note: 'Autres charges agence' },
            ],

            'ventes-biens-tab': [
                { label: 'Total ventes', value: '2 500 000', color: 'is-success', note: '1 vente enregistrée aujourd’hui' },
                { label: 'Ventes payées', value: '1', color: 'is-success', note: 'Paiement confirmé' },
                { label: 'En attente', value: '0', color: 'is-warning', note: 'Aucune vente en attente' },
                { label: 'Commission agence', value: '250 000', color: 'is-info', note: 'Commission estimée à 10%' },
            ],
            'summary-tab': [
                { label: 'Total encaissé',      value: '154 000', color: 'is-success', note: '4 transactions toutes méthodes' },
                { label: 'Commission agence',   value: '15 400',  color: 'is-info',    note: '10% du total encaissé' },
                { label: 'Net propriétaires',   value: '138 600', color: '',           note: 'Montant à reverser' },
                { label: 'Modes de paiement',   value: '3',       color: '',           note: 'Espèces, WAVE, Orange Money' },
            ],
        };

        function renderStats(tabId) {
            const container = document.getElementById('main-stats');
            if (!container) return;
            const stats = tabStats[tabId] || tabStats['transactions-tab'];
            container.innerHTML = stats.map(s => `
            <article class="stat-card">
                <span>${s.label}</span>
                <strong class="${s.color}">${s.value}</strong>
                <small>${s.note}</small>
            </article>
        `).join('');
        }

        function showOpenCashForm() {
            document.getElementById('open-cash-trigger-view')?.classList.add('d-none');
            document.getElementById('open-cash-form-view')?.classList.remove('d-none');
        }

        function hideOpenCashForm() {
            document.getElementById('open-cash-form-view')?.classList.add('d-none');
            document.getElementById('open-cash-trigger-view')?.classList.remove('d-none');
        }

        function showCloseCashForm() {
            document.getElementById('close-cash-form-card')?.classList.remove('d-none');
            document.getElementById('cash-main-content')?.classList.add('d-none');
        }

        function hideCloseCashForm() {
            document.getElementById('close-cash-form-card')?.classList.add('d-none');
            document.getElementById('cash-main-content')?.classList.remove('d-none');
        }

        document.addEventListener('DOMContentLoaded', function () {
            const dropdownToggle = document.getElementById('actionsDropdown');
            const dropdownMenu = document.querySelector('#actionsDropdown + .dropdown-menu');

            if (dropdownToggle && dropdownMenu) {
                dropdownToggle.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    dropdownMenu.classList.toggle('show');
                });
                document.addEventListener('click', function (e) {
                    if (!dropdownToggle.contains(e.target) && !dropdownMenu.contains(e.target)) {
                        dropdownMenu.classList.remove('show');
                    }
                });
            }

            const tabBtns = document.querySelectorAll('.tab-btn');
            const tabContents = document.querySelectorAll('.tab-content');

            function switchTab(tabId) {
                tabContents.forEach(c => c.classList.remove('active'));
                document.getElementById(tabId)?.classList.add('active');
                renderStats(tabId);
            }

            tabBtns.forEach(btn => {
                btn.addEventListener('click', function () {
                    tabBtns.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    switchTab(this.getAttribute('data-tab'));
                });
            });

            switchTab('transactions-tab');
        });


    </script>

    <script>

        document.addEventListener('click', function (e) {
            const openBtn = e.target.closest('[data-open-modal]');
            if (openBtn) {
                const modalName = openBtn.getAttribute('data-open-modal');
                const modal = document.querySelector(`[data-modal="${modalName}"]`);

                if (modal) {
                    modal.classList.add('active');
                    modal.setAttribute('aria-hidden', 'false');
                }
            }

            const closeBtn = e.target.closest('[data-close-modal]');
            if (closeBtn) {
                const modal = closeBtn.closest('.modal');

                if (modal) {
                    modal.classList.remove('active');
                    modal.setAttribute('aria-hidden', 'true');
                }
            }
        });

        function openMouvementModal() {
            const modal = document.querySelector('[data-modal="mouvementModal"]');

            if (modal) {
                modal.classList.add('active');
                modal.setAttribute('aria-hidden', 'false');
            }
        }

        function closeMouvementModal() {
            const modal = document.querySelector('[data-modal="mouvementModal"]');

            if (modal) {
                modal.classList.remove('active');
                modal.setAttribute('aria-hidden', 'true');
            }
        }

        document.addEventListener('click', function (e) {

            if (e.target.matches('[data-close-modal]')) {
                closeMouvementModal();
            }

        });

    </script>

@endsection