@extends('agence.layouts.app')

@section('title', 'Vente de biens')

@section('content')

    <section class="page">

        <header class="page-header">
            <div class="page-header-copy">
                <div class="page-heading">
                    <div>
                        <h2>Vente de biens</h2>
                    </div>
                </div>
            </div>

            <div class="page-actions">
                <a href="{{ route('agence.caisse.index') }}" class="btn btn-outline">
                    Retour caisse
                </a>

                <button class="btn btn-secondary">
                    {{ now()->format('d/m/Y') }}
                </button>
            </div>
        </header>

        <div class="payment-shortcuts">

            <a href="{{ route('agence.caisse.loyer') }}"
               class="payment-shortcut">

                <svg xmlns="http://www.w3.org/2000/svg"
                     fill="none"
                     viewBox="0 0 24 24"
                     stroke-width="1.5"
                     stroke="currentColor">

                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75" />
                </svg>

                <span>Loyers</span>

            </a>

            <a href="{{ route('agence.caisse.maintenance') }}"
               class="payment-shortcut ">

                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437 1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008Z" />
                </svg>

                <span>Maintenance</span>

            </a>

            <a href="{{ route('agence.caisse.depense.agence') }}"
               class="payment-shortcut">

                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007Z" />
                </svg>

                <span>Dépenses agence</span>

            </a>

            <a href="{{ route('agence.caisse.vente.bien') }}"
               class="payment-shortcut active">

                <svg xmlns="http://www.w3.org/2000/svg"
                     fill="none"
                     viewBox="0 0 24 24"
                     stroke-width="1.5"
                     stroke="currentColor">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>

                <span>Vente de biens</span>

            </a>

        </div>

        <div class="sale-payment-wrapper">

            <div class="sale-search-card">
                <label class="form-field">
                    <span>Rechercher un propriétaire</span>
                    <input type="text"
                           id="owner-search"
                           placeholder="Tapez le nom ou le numéro du propriétaire...">
                </label>
            </div>

            <div id="owner-result" class="sale-layout d-none">

                <aside class="owner-property-list-card">
                    <div class="owner-property-list-header">
                        <div>
                            <h3 id="owner-name">Propriétaire</h3>
                            <p id="owner-phone">Téléphone</p>
                        </div>

                        <span id="property-count">0 bien(s)</span>
                    </div>

                    <div id="property-list" class="property-list"></div>
                </aside>

                <div class="owner-property-detail-card">

                    <div class="owner-card-header">
                        <div>
                            <h3 id="property-title">Bien concerné</h3>
                            <p id="property-location">Localisation</p>
                        </div>

                        <span id="property-status" class="badge success">Disponible</span>
                    </div>

                    <div class="owner-info-grid">
                        <div>
                            <span>Type de bien</span>
                            <strong id="property-type">-</strong>
                        </div>

                        <div>
                            <span>Prix de vente</span>
                            <strong id="property-price" class="is-success">0 FCFA</strong>
                        </div>

                        <div>
                            <span>Commission agence</span>
                            <strong id="agency-commission">0 FCFA</strong>
                        </div>

                        <div>
                            <span>Montant propriétaire</span>
                            <strong id="owner-amount">0 FCFA</strong>
                        </div>

                        <div>
                            <span>Client acheteur</span>
                            <strong id="buyer-name">-</strong>
                        </div>

                        <div>
                            <span>Statut vente</span>
                            <strong id="sale-status">-</strong>
                        </div>
                    </div>

                    <div class="sale-summary">
                        <h4>Détails de la vente</h4>

                        <div class="sale-row">
                            <span>Référence vente</span>
                            <strong id="sale-reference">VTE-2026-0001</strong>
                        </div>

                        <div class="sale-row">
                            <span>Date prévue</span>
                            <strong id="sale-date">04/06/2026</strong>
                        </div>

                        <div class="sale-row">
                            <span>Observation</span>
                            <strong id="sale-observation">Vente directe avec paiement complet.</strong>
                        </div>
                    </div>

                    <div class="owner-actions">
                        <button type="button"
                                class="btn btn-primary"
                                data-open-modal="salePaymentModal">
                            Créer l’accord de vente
                        </button>
                    </div>

                </div>

            </div>

        </div>

        <div class="modal" data-modal="salePaymentModal" aria-hidden="true">
            <div class="modal-box u-modal-lg">

                <div class="modal-header">
                    <h3>Accord de vente du bien</h3>

                    <button class="modal-close" data-close-modal aria-label="Fermer">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="u-icon-sm"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>

                    </button>
                </div>

                <div class="modal-body">
                    <form action="#" method="POST">
                        @csrf

                        <div class="sale-form-section">
                            <div class="sale-form-section-header">
                                <h4>Informations de la vente</h4>
                                <p>Bien, montant et référence de l’accord.</p>
                            </div>

                            <div class="form-grid u-form-grid-2">
                                <label class="form-field">
                                    <span>Référence vente</span>
                                    <input id="payment-reference"
                                           type="text"
                                           value="VTE-2026-0001"
                                           disabled>
                                </label>

                                <label class="form-field">
                                    <span>Bien concerné</span>
                                    <input id="payment-property"
                                           type="text"
                                           disabled>
                                </label>

                                <label class="form-field">
                                    <span>Prix de vente</span>
                                    <input id="payment-sale-price"
                                           type="number"
                                           min="0"
                                           disabled>
                                </label>

                                <label class="form-field">
                                    <span>Date de l’accord</span>
                                    <input type="date"
                                           value="{{ now()->format('Y-m-d') }}">
                                </label>
                            </div>
                        </div>

                        <div class="sale-form-section">
                            <div class="sale-form-section-header">
                                <h4>Informations de l’acheteur</h4>
                                <p>Identité complète du client acheteur.</p>
                            </div>

                            <div class="form-grid u-form-grid-2">
                                <label class="form-field">
                                    <span>Nom complet *</span>
                                    <input id="payment-buyer"
                                           type="text"
                                           placeholder="Nom complet de l’acheteur">
                                </label>

                                <label class="form-field">
                                    <span>Téléphone *</span>
                                    <input type="text"
                                           placeholder="Numéro de téléphone">
                                </label>

                                <label class="form-field">
                                    <span>Email</span>
                                    <input type="email"
                                           placeholder="Adresse email">
                                </label>

                                <label class="form-field">
                                    <span>Adresse</span>
                                    <input type="text"
                                           placeholder="Adresse de résidence">
                                </label>

                                <label class="form-field">
                                    <span>Type de pièce</span>
                                    <select>
                                        <option>CNI</option>
                                        <option>Passeport</option>
                                        <option>Permis de conduire</option>
                                        <option>Carte consulaire</option>
                                        <option>Autre</option>
                                    </select>
                                </label>

                                <label class="form-field">
                                    <span>Numéro de pièce</span>
                                    <input type="text"
                                           placeholder="Numéro de la pièce">
                                </label>
                            </div>
                        </div>

                        <div class="sale-form-section">
                            <div class="sale-form-section-header">
                                <h4>Mode de paiement convenu</h4>
                                <p>Choisissez la méthode selon l’entente entre les parties.</p>
                            </div>

                            <div class="payment-plan-grid">
                                <label class="payment-plan-option active">
                                    <input type="radio"
                                           name="payment_plan_type"
                                           value="complete"
                                           checked>
                                    <strong>Paiement complet</strong>
                                    <span>Le client paie tout en une seule fois.</span>
                                </label>

                                <label class="payment-plan-option">
                                    <input type="radio"
                                           name="payment_plan_type"
                                           value="tranches">
                                    <strong>Paiement par tranches</strong>
                                    <span>Plusieurs montants avec des dates précises.</span>
                                </label>

                                <label class="payment-plan-option">
                                    <input type="radio"
                                           name="payment_plan_type"
                                           value="monthly">
                                    <strong>Paiement mensuel</strong>
                                    <span>Acompte puis mensualités automatiques.</span>
                                </label>

                                <label class="payment-plan-option">
                                    <input type="radio"
                                           name="payment_plan_type"
                                           value="custom">
                                    <strong>Plan personnalisé</strong>
                                    <span>Accord libre selon la négociation.</span>
                                </label>
                            </div>
                        </div>

                        <div class="sale-form-section" id="complete-payment-box">
                            <div class="sale-form-section-header">
                                <h4>Paiement complet</h4>
                            </div>

                            <div class="form-grid u-form-grid-2">
                                <label class="form-field">
                                    <span>Montant encaissé</span>
                                    <input id="complete-payment-amount"
                                           type="number"
                                           min="0">
                                </label>

                                <label class="form-field">
                                    <span>Mode de paiement</span>
                                    <select>
                                        <option>Espèces</option>
                                        <option>Wave</option>
                                        <option>Orange Money</option>
                                        <option>Virement bancaire</option>
                                        <option>Chèque</option>
                                    </select>
                                </label>
                            </div>
                        </div>

                        <div class="sale-form-section d-none" id="tranches-payment-box">
                            <div class="sale-form-section-header section-between">
                                <div>
                                    <h4>Paiement par tranches</h4>
                                    <p>Ajoutez les tranches selon l’accord.</p>
                                </div>

                                <button type="button" class="btn btn-outline btn-sm" id="add-tranche">
                                    Ajouter une tranche
                                </button>
                            </div>

                            <div id="tranche-list" class="payment-schedule-list"></div>
                        </div>

                        <div class="sale-form-section d-none" id="monthly-payment-box">
                            <div class="sale-form-section-header">
                                <h4>Paiement mensuel</h4>
                            </div>

                            <div class="form-grid u-form-grid-3">
                                <label class="form-field">
                                    <span>Acompte</span>
                                    <input id="monthly-deposit"
                                           type="number"
                                           min="0"
                                           value="0">
                                </label>

                                <label class="form-field">
                                    <span>Nombre de mensualités</span>
                                    <input id="monthly-count"
                                           type="number"
                                           min="1"
                                           value="6">
                                </label>

                                <label class="form-field">
                                    <span>Date première mensualité</span>
                                    <input id="monthly-start-date"
                                           type="date">
                                </label>
                            </div>

                            <div class="monthly-preview">
                                <span>Mensualité estimée</span>
                                <strong id="monthly-amount-preview">0 FCFA</strong>
                            </div>
                        </div>

                        <div class="sale-form-section d-none" id="custom-payment-box">
                            <div class="sale-form-section-header section-between">
                                <div>
                                    <h4>Plan personnalisé</h4>
                                    <p>Ajoutez librement les paiements prévus.</p>
                                </div>

                                <button type="button" class="btn btn-outline btn-sm" id="add-custom-line">
                                    Ajouter une ligne
                                </button>
                            </div>

                            <div id="custom-payment-list" class="payment-schedule-list"></div>
                        </div>

                        <div class="sale-form-section">
                            <div class="sale-form-section-header">
                                <h4>Résumé financier</h4>
                            </div>

                            <div class="sale-total-grid">
                                <div>
                                    <span>Prix du bien</span>
                                    <strong id="summary-sale-price">0 FCFA</strong>
                                </div>

                                <div>
                                    <span>Total planifié</span>
                                    <strong id="summary-planned-total">0 FCFA</strong>
                                </div>

                                <div>
                                    <span>Reste à payer</span>
                                    <strong id="summary-remaining" class="is-danger">0 FCFA</strong>
                                </div>

                                <div>
                                    <span>Commission agence</span>
                                    <strong id="summary-commission">0 FCFA</strong>
                                </div>
                            </div>
                        </div>

                        <div class="sale-form-section">
                            <label class="form-field">
                                <span>Observation / Conditions particulières</span>
                                <textarea placeholder="Ex : le client paie un acompte aujourd’hui, puis le reste après signature chez le notaire..."></textarea>
                            </label>
                        </div>

                        <div class="modal-actions">
                            <button type="button" class="btn btn-outline" data-close-modal>
                                Annuler
                            </button>

                            <button type="button" class="btn btn-primary">
                                Valider l’accord de vente
                            </button>
                        </div>

                    </form>
                </div>

            </div>
        </div>

    </section>

    <style>
        .sale-payment-wrapper {
            width: 1100px;
            max-width: 100%;
            margin: 2rem auto 0;
        }

        .sale-search-card,
        .owner-property-list-card,
        .owner-property-detail-card,
        .sale-form-section {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 1.25rem;
            box-shadow: var(--shadow);
        }

        .sale-search-card {
            padding: 1.5rem;
            margin-bottom: 1.25rem;
        }

        .sale-layout {
            display: grid;
            grid-template-columns: 1fr 3fr;
            gap: 1.25rem;
            align-items: start;
        }

        .owner-property-list-card,
        .owner-property-detail-card {
            padding: 1.25rem;
        }

        .owner-property-list-header {
            margin-bottom: 1rem;
        }

        .owner-property-list-header h3 {
            margin: 0;
            font-size: 1.05rem;
        }

        .owner-property-list-header p,
        .owner-property-list-header span {
            color: var(--muted-foreground);
            font-size: .85rem;
        }

        .property-list {
            display: flex;
            flex-direction: column;
            gap: .75rem;
        }

        .property-item {
            width: 100%;
            text-align: left;
            padding: .9rem;
            border: 1px solid var(--border);
            border-radius: .9rem;
            background: var(--surface-subtle);
            color: var(--foreground);
            cursor: pointer;
        }

        .property-item.active {
            border-color: var(--primary);
            background: rgba(37, 99, 235, .08);
        }

        .property-item strong,
        .property-item span,
        .property-item small {
            display: block;
        }

        .property-item span {
            margin-top: .25rem;
            color: var(--muted-foreground);
            font-size: .8rem;
        }

        .property-item small {
            margin-top: .4rem;
            font-weight: 700;
        }

        .owner-card-header {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            align-items: flex-start;
            margin-bottom: 1.5rem;
        }

        .owner-card-header h3 {
            margin: 0;
            font-size: 1.25rem;
        }

        .owner-card-header p {
            margin: .25rem 0 0;
            color: var(--muted-foreground);
        }

        .owner-info-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1rem;
        }

        .owner-info-grid > div {
            padding: 1rem;
            border: 1px solid var(--border);
            border-radius: 1rem;
            background: var(--surface-subtle);
        }

        .owner-info-grid span {
            display: block;
            font-size: .78rem;
            color: var(--muted-foreground);
            margin-bottom: .35rem;
        }

        .sale-summary {
            margin-top: 1.5rem;
            padding: 1rem;
            border: 1px dashed var(--border);
            border-radius: 1rem;
            background: var(--surface-subtle);
        }

        .sale-summary h4 {
            margin: 0 0 .75rem;
        }

        .sale-row {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 1rem;
            padding: .85rem 0;
            border-bottom: 1px dashed var(--border);
        }

        .sale-row:last-child {
            border-bottom: none;
        }

        .owner-actions,
        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: .75rem;
            margin-top: 1.5rem;
        }

        .sale-form-section {
            padding: 1.25rem;
            margin-bottom: 1rem;
            box-shadow: none;
        }

        .sale-form-section-header {
            margin-bottom: 1rem;
        }

        .sale-form-section-header h4 {
            margin: 0;
            font-size: 1rem;
        }

        .sale-form-section-header p {
            margin: .25rem 0 0;
            font-size: .85rem;
            color: var(--muted-foreground);
        }

        .section-between {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            align-items: center;
        }

        .payment-plan-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: .75rem;
        }

        .payment-plan-option {
            display: block;
            padding: 1rem;
            border: 1px solid var(--border);
            border-radius: 1rem;
            background: var(--surface-subtle);
            cursor: pointer;
        }

        .payment-plan-option input {
            margin-bottom: .5rem;
        }

        .payment-plan-option strong,
        .payment-plan-option span {
            display: block;
        }

        .payment-plan-option span {
            margin-top: .35rem;
            color: var(--muted-foreground);
            font-size: .8rem;
            line-height: 1.35;
        }

        .payment-plan-option.active {
            border-color: var(--primary);
            background: rgba(37, 99, 235, .1);
        }

        .payment-schedule-list {
            display: flex;
            flex-direction: column;
            gap: .75rem;
        }

        .payment-line {
            display: grid;
            grid-template-columns: 1.3fr 1fr 1fr 1fr auto;
            gap: .75rem;
            align-items: end;
            padding: .85rem;
            border: 1px solid var(--border);
            border-radius: 1rem;
            background: var(--surface-subtle);
        }

        .payment-line .btn {
            height: 42px;
        }

        .monthly-preview {
            margin-top: 1rem;
            padding: 1rem;
            border: 1px dashed var(--border);
            border-radius: 1rem;
            background: var(--surface-subtle);
            display: flex;
            justify-content: space-between;
            gap: 1rem;
        }

        .monthly-preview span {
            color: var(--muted-foreground);
        }

        .sale-total-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: .75rem;
        }

        .sale-total-grid > div {
            padding: 1rem;
            border: 1px solid var(--border);
            border-radius: 1rem;
            background: var(--surface-subtle);
        }

        .sale-total-grid span {
            display: block;
            font-size: .8rem;
            color: var(--muted-foreground);
            margin-bottom: .35rem;
        }

        .btn-sm {
            padding: .55rem .85rem;
            font-size: .85rem;
        }

        .is-success {
            color: #16a34a !important;
        }

        .is-danger {
            color: #dc2626 !important;
        }

        .d-none {
            display: none !important;
        }

        .payment-shortcuts {
            width: 1100px;
            max-width: 100%;
            margin: 1rem auto 1.5rem;
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: .75rem;
        }

        .payment-shortcut {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .65rem;
            padding: 1rem;
            min-height: 60px;
            border-radius: 1rem;
            text-decoration: none;
            background: var(--card);
            border: 1px solid var(--border);
            color: var(--foreground);
            font-weight: 600;
            transition: .2s ease;
        }

        .payment-shortcut:hover,
        .payment-shortcut.active {
            border-color: var(--primary);
            color: var(--primary);
            background: rgba(37, 99, 235, .12);
        }

        .u-modal-lg {
            width: min(1100px, calc(100vw - 2rem));
        }

        @media (max-width: 1000px) {
            .sale-layout,
            .owner-info-grid,
            .payment-plan-grid,
            .sale-total-grid,
            .payment-line {
                grid-template-columns: 1fr;
            }

            .owner-card-header,
            .owner-actions,
            .modal-actions,
            .section-between,
            .monthly-preview {
                flex-direction: column;
                align-items: stretch;
            }

            .payment-shortcuts {
                grid-template-columns: 1fr 1fr;
            }
        }

        .payment-shortcuts{
            width:1100px;
            max-width:100%;
            margin:1rem auto 1.5rem;
            display:grid;
            grid-template-columns:repeat(4,minmax(0,1fr));
            gap:.75rem;
        }

        .payment-shortcut{
            display:flex;
            align-items:center;
            justify-content:center;
            gap:.65rem;

            padding:1rem;
            min-height:60px;

            border-radius:1rem;
            text-decoration:none;

            background:var(--card);
            border:1px solid var(--border);

            color:var(--foreground);
            font-weight:600;

            transition:.2s ease;
        }

        .payment-shortcut svg{
            width:20px;
            height:20px;
            flex-shrink:0;
        }

        .payment-shortcut:hover{
            border-color:var(--primary);
            color:var(--primary);
            transform:translateY(-1px);
        }

        .payment-shortcut.active{
            background:rgba(37,99,235,.12);
            border-color:var(--primary);
            color:var(--primary);
        }

        @media (max-width:900px){
            .payment-shortcuts{
                grid-template-columns:1fr 1fr;
            }
        }

        @media (max-width:600px){
            .payment-shortcuts{
                grid-template-columns:1fr;
            }
        }
    </style>

    <script>
        const ownerSearch = document.getElementById('owner-search');
        const ownerResult = document.getElementById('owner-result');
        const propertyList = document.getElementById('property-list');

        const paymentProperty = document.getElementById('payment-property');
        const paymentSalePrice = document.getElementById('payment-sale-price');
        const paymentBuyer = document.getElementById('payment-buyer');
        const completePaymentAmount = document.getElementById('complete-payment-amount');

        const summarySalePrice = document.getElementById('summary-sale-price');
        const summaryPlannedTotal = document.getElementById('summary-planned-total');
        const summaryRemaining = document.getElementById('summary-remaining');
        const summaryCommission = document.getElementById('summary-commission');

        let selectedProperty = null;

        const staticOwner = {
            name: 'Koffi Bernard',
            phone: '05 45 22 18 90',
            properties: [
                {
                    title: 'Villa basse 4 pièces',
                    location: 'Bingerville',
                    type: 'Villa',
                    price: 45000000,
                    commission: 2250000,
                    ownerAmount: 42750000,
                    buyer: 'Traoré Ibrahim',
                    status: 'Vente en cours',
                    badge: 'Disponible',
                    reference: 'VTE-2026-0001',
                    date: '04/06/2026',
                    observation: 'Vente avec accord personnalisable.'
                },
                {
                    title: 'Terrain 500 m²',
                    location: 'Songon',
                    type: 'Terrain',
                    price: 18000000,
                    commission: 900000,
                    ownerAmount: 17100000,
                    buyer: 'Aucun acheteur',
                    status: 'Non vendu',
                    badge: 'Disponible',
                    reference: 'VTE-2026-0002',
                    date: 'À définir',
                    observation: 'Bien disponible à la vente.'
                },
                {
                    title: 'Appartement 3 pièces',
                    location: 'Cocody Angré',
                    type: 'Appartement',
                    price: 32000000,
                    commission: 1600000,
                    ownerAmount: 30400000,
                    buyer: 'Yao Mireille',
                    status: 'Réservation',
                    badge: 'Réservé',
                    reference: 'VTE-2026-0003',
                    date: '06/06/2026',
                    observation: 'Acompte prévu avant validation finale.'
                }
            ]
        };

        function normalizeSearch(value) {
            return value
                .toLowerCase()
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .replace(/\s+/g, '');
        }

        function formatAmount(amount) {
            return new Intl.NumberFormat('fr-FR').format(Number(amount || 0)) + ' FCFA';
        }

        function getNumber(value) {
            return Number(value || 0);
        }

        ownerSearch?.addEventListener('input', function () {
            const value = normalizeSearch(this.value.trim());

            ownerResult.classList.add('d-none');

            if (value.length < 2) {
                return;
            }

            const ownerName = normalizeSearch(staticOwner.name);
            const ownerPhone = normalizeSearch(staticOwner.phone).replace(/\D/g, '');
            const searchedPhone = value.replace(/\D/g, '');

            const matchName = ownerName.includes(value);
            const matchPhone = searchedPhone.length >= 2 && ownerPhone.includes(searchedPhone);

            if (matchName || matchPhone) {
                showOwnerProperties();
            }
        });

        function showOwnerProperties() {
            document.getElementById('owner-name').textContent = staticOwner.name;
            document.getElementById('owner-phone').textContent = staticOwner.phone;
            document.getElementById('property-count').textContent = `${staticOwner.properties.length} bien(s)`;

            propertyList.innerHTML = '';

            staticOwner.properties.forEach((property, index) => {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = `property-item ${index === 0 ? 'active' : ''}`;

                button.innerHTML = `
                <strong>${property.title}</strong>
                <span>${property.location}</span>
                <small class="is-success">${formatAmount(property.price)}</small>
            `;

                button.addEventListener('click', function () {
                    document.querySelectorAll('.property-item').forEach(el => el.classList.remove('active'));
                    this.classList.add('active');
                    fillPropertyDetails(property);
                });

                propertyList.appendChild(button);
            });

            fillPropertyDetails(staticOwner.properties[0]);
            ownerResult.classList.remove('d-none');
        }

        function fillPropertyDetails(property) {
            selectedProperty = property;

            document.getElementById('property-title').textContent = property.title;
            document.getElementById('property-location').textContent = property.location;
            document.getElementById('property-status').textContent = property.badge;

            document.getElementById('property-type').textContent = property.type;
            document.getElementById('property-price').textContent = formatAmount(property.price);
            document.getElementById('agency-commission').textContent = formatAmount(property.commission);
            document.getElementById('owner-amount').textContent = formatAmount(property.ownerAmount);
            document.getElementById('buyer-name').textContent = property.buyer;
            document.getElementById('sale-status').textContent = property.status;

            document.getElementById('sale-reference').textContent = property.reference;
            document.getElementById('sale-date').textContent = property.date;
            document.getElementById('sale-observation').textContent = property.observation;

            document.getElementById('payment-reference').value = property.reference;
            paymentProperty.value = `${property.title} — ${property.location}`;
            paymentSalePrice.value = property.price;
            completePaymentAmount.value = property.price;
            paymentBuyer.value = property.buyer !== 'Aucun acheteur' ? property.buyer : '';

            updateSummary();
            updateMonthlyPreview();
        }

        document.querySelectorAll('input[name="payment_plan_type"]').forEach(radio => {
            radio.addEventListener('change', function () {
                document.querySelectorAll('.payment-plan-option').forEach(label => {
                    label.classList.remove('active');
                });

                this.closest('.payment-plan-option').classList.add('active');

                document.getElementById('complete-payment-box').classList.toggle('d-none', this.value !== 'complete');
                document.getElementById('tranches-payment-box').classList.toggle('d-none', this.value !== 'tranches');
                document.getElementById('monthly-payment-box').classList.toggle('d-none', this.value !== 'monthly');
                document.getElementById('custom-payment-box').classList.toggle('d-none', this.value !== 'custom');

                if (this.value === 'tranches' && document.querySelectorAll('#tranche-list .payment-line').length === 0) {
                    addPaymentLine('tranche-list', 'Tranche 1');
                    addPaymentLine('tranche-list', 'Tranche 2');
                }

                if (this.value === 'custom' && document.querySelectorAll('#custom-payment-list .payment-line').length === 0) {
                    addPaymentLine('custom-payment-list', 'Paiement prévu');
                }

                updateSummary();
            });
        });

        document.getElementById('add-tranche')?.addEventListener('click', function () {
            const count = document.querySelectorAll('#tranche-list .payment-line').length + 1;
            addPaymentLine('tranche-list', `Tranche ${count}`);
        });

        document.getElementById('add-custom-line')?.addEventListener('click', function () {
            const count = document.querySelectorAll('#custom-payment-list .payment-line').length + 1;
            addPaymentLine('custom-payment-list', `Paiement ${count}`);
        });

        function addPaymentLine(containerId, labelValue) {
            const container = document.getElementById(containerId);

            const line = document.createElement('div');
            line.className = 'payment-line';

            line.innerHTML = `
            <label class="form-field">
                <span>Libellé</span>
                <input type="text" value="${labelValue}">
            </label>

            <label class="form-field">
                <span>Montant</span>
                <input type="number" min="0" class="schedule-amount" placeholder="Montant">
            </label>

            <label class="form-field">
                <span>Date prévue</span>
                <input type="date">
            </label>

            <label class="form-field">
                <span>Mode</span>
                <select>
                    <option>Espèces</option>
                    <option>Wave</option>
                    <option>Orange Money</option>
                    <option>Virement bancaire</option>
                    <option>Chèque</option>
                </select>
            </label>

            <button type="button" class="btn btn-outline remove-payment-line">
                ×
            </button>
        `;

            container.appendChild(line);

            line.querySelector('.schedule-amount')?.addEventListener('input', updateSummary);

            line.querySelector('.remove-payment-line')?.addEventListener('click', function () {
                line.remove();
                updateSummary();
            });

            updateSummary();
        }

        completePaymentAmount?.addEventListener('input', updateSummary);

        document.getElementById('monthly-deposit')?.addEventListener('input', function () {
            updateMonthlyPreview();
            updateSummary();
        });

        document.getElementById('monthly-count')?.addEventListener('input', function () {
            updateMonthlyPreview();
            updateSummary();
        });

        function getSelectedPaymentType() {
            return document.querySelector('input[name="payment_plan_type"]:checked')?.value || 'complete';
        }

        function getPlannedTotal() {
            const type = getSelectedPaymentType();

            if (!selectedProperty) {
                return 0;
            }

            if (type === 'complete') {
                return getNumber(completePaymentAmount.value);
            }

            if (type === 'monthly') {
                return selectedProperty.price;
            }

            let total = 0;

            document.querySelectorAll('.payment-schedule-list:not(.d-none) .schedule-amount').forEach(input => {
                total += getNumber(input.value);
            });

            if (type === 'tranches') {
                total = 0;
                document.querySelectorAll('#tranche-list .schedule-amount').forEach(input => {
                    total += getNumber(input.value);
                });
            }

            if (type === 'custom') {
                total = 0;
                document.querySelectorAll('#custom-payment-list .schedule-amount').forEach(input => {
                    total += getNumber(input.value);
                });
            }

            return total;
        }

        function updateMonthlyPreview() {
            if (!selectedProperty) {
                return;
            }

            const deposit = getNumber(document.getElementById('monthly-deposit')?.value);
            const count = Math.max(getNumber(document.getElementById('monthly-count')?.value), 1);

            const remaining = Math.max(selectedProperty.price - deposit, 0);
            const monthlyAmount = Math.ceil(remaining / count);

            document.getElementById('monthly-amount-preview').textContent = formatAmount(monthlyAmount);
        }

        function updateSummary() {
            if (!selectedProperty) {
                return;
            }

            const salePrice = selectedProperty.price;
            const plannedTotal = getPlannedTotal();
            const remaining = Math.max(salePrice - plannedTotal, 0);

            summarySalePrice.textContent = formatAmount(salePrice);
            summaryPlannedTotal.textContent = formatAmount(plannedTotal);
            summaryRemaining.textContent = formatAmount(remaining);
            summaryCommission.textContent = formatAmount(selectedProperty.commission);
        }
    </script>

@endsection