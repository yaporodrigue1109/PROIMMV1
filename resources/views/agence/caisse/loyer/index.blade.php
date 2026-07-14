
@extends('agence.layouts.app')

@section('title', 'Paiement des loyers')

@section('content')

    <section class="page">

        <header class="page-header">
            <div class="page-header-copy">
                <div class="page-heading">
                    <div>
                        <h2>Paiement des loyers</h2>
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
               class="payment-shortcut active">

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
               class="payment-shortcut">

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
               class="payment-shortcut">

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

        <div class="loyer-payment-wrapper">

            <div class="loyer-search-card">
                <label class="form-field">
                    <span>Rechercher un locataire</span>
                    <input type="text"
                           id="tenant-search"
                           placeholder="Tapez le nom ou le numéro du locataire...">
                </label>
            </div>

            <div id="tenant-loading" class="tenant-loading-skeleton d-none">

                <aside class="tenant-rental-list-card">
                    <div class="skeleton-title"></div>

                    <div class="skeleton-rental-item">
                        <div class="skeleton-line skeleton-lg"></div>
                        <div class="skeleton-line skeleton-sm"></div>
                        <div class="skeleton-line skeleton-md"></div>
                    </div>

                    <div class="skeleton-rental-item">
                        <div class="skeleton-line skeleton-lg"></div>
                        <div class="skeleton-line skeleton-sm"></div>
                        <div class="skeleton-line skeleton-md"></div>
                    </div>
                </aside>

                <div class="tenant-rental-detail-card">
                    <div class="skeleton-line skeleton-title-wide"></div>

                    <div class="tenant-info-grid">
                        <div class="skeleton-box"></div>
                        <div class="skeleton-box"></div>
                        <div class="skeleton-box"></div>
                        <div class="skeleton-box"></div>
                        <div class="skeleton-box"></div>
                        <div class="skeleton-box"></div>
                    </div>
                </div>

            </div>

            <div id="tenant-result" class="loyer-layout d-none">

                <aside class="tenant-rental-list-card">
                    <div class="tenant-rental-list-header">
                        <div>
                            <h3 id="tenant-name">Locataire</h3>
                            <p id="tenant-phone">Téléphone</p>
                        </div>

                        <span id="rental-count">0 bien(s)</span>
                    </div>

                    <div id="rental-list" class="rental-list"></div>
                </aside>

                <div class="tenant-rental-detail-card">

                    <div class="tenant-card-header">
                        <div>
                            <h3 id="rental-title">Bien loué</h3>
                            <p id="rental-location">Localisation</p>
                        </div>

                        <span id="rental-status" class="badge">En retard</span>
                    </div>

                    <div class="tenant-info-grid">
                        <div>
                            <span>Loyer mensuel</span>
                            <strong id="tenant-rent">0 FCFA</strong>
                        </div>

                        <div>
                            <span>Période concernée</span>
                            <strong id="tenant-period">-</strong>
                        </div>

                        <div>
                            <span>Montant dû</span>
                            <strong id="tenant-due" class="is-danger">0 FCFA</strong>
                        </div>

                        <div>
                            <span>Dernier paiement</span>
                            <strong id="tenant-last-payment">-</strong>
                        </div>

                        <div>
                            <span>Retard</span>
                            <strong id="tenant-delay" class="is-danger">-</strong>
                        </div>

                        <div>
                            <span>Statut paiement</span>
                            <strong id="tenant-payment-status">-</strong>
                        </div>
                    </div>

                    <div class="payment-history">
                        <h4>Historique des paiements</h4>
                        <div id="payment-history-list"></div>
                    </div>

                    <div class="tenant-actions">
                        <button type="button"
                                class="btn btn-primary"
                                data-open-modal="payRentModal">
                            Payer le loyer
                        </button>
                    </div>

                </div>

            </div>

        </div>

        <div class="modal" data-modal="payRentModal" aria-hidden="true">
            <div class="modal-box u-modal-md">

                <div class="modal-header">
                    <h3>Encaissement du loyer</h3>

                    <button class="modal-close" data-close-modal aria-label="Fermer">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="u-icon-sm"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>

                    </button>
                </div>

                <div class="modal-body">
                    <form action="#" method="POST">
                        @csrf

                        <div class="form-grid u-form-grid-2">

                            <label class="form-field">
                                <span>Référence paiement</span>
                                <input id="payment-reference"
                                       type="text"
                                       value="TRX-LOY-0001"
                                       disabled>
                            </label>

                            <label class="form-field">
                                <span>Bien concerné</span>
                                <input id="payment-property"
                                       type="text"
                                       disabled>
                            </label>

                            <label class="form-field">
                                <span>Montant</span>
                                <input id="payment-amount"
                                       type="number"
                                       min="0"
                                       placeholder="Montant encaissé">
                            </label>

                            <label class="form-field">
                                <span>Mode de paiement</span>
                                <select id="payment-method">
                                    <option value="especes">Espèces</option>
                                    <option value="wave">Wave</option>
                                    <option value="orange_money">Orange Money</option>
                                    <option value="virement">Virement bancaire</option>
                                </select>
                            </label>

                            <label class="form-field form-field-wide">
                                <span>Observation</span>
                                <textarea id="payment-observation" placeholder="Observation facultative..."></textarea>
                            </label>

                        </div>

                        <div class="modal-actions">
                            <button type="button" class="btn btn-outline" data-close-modal>
                                Annuler
                            </button>

                            <button type="button" id="submit-payment" class="btn btn-primary">
                                Valider le paiement
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>

    </section>

    <style>
        .loyer-payment-wrapper {
            width: 1100px;
            max-width: 100%;
            margin: 2rem auto 0;
        }

        .loyer-search-card,
        .tenant-rental-list-card,
        .tenant-rental-detail-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 1.25rem;
            box-shadow: var(--shadow);
        }

        .loyer-search-card {
            padding: 1.5rem;
            margin-bottom: 1.25rem;
        }

        .loyer-layout {
            display: grid;
            grid-template-columns: 1fr 3fr;
            gap: 1.25rem;
            align-items: start;
        }

        .tenant-rental-list-card,
        .tenant-rental-detail-card {
            padding: 1.25rem;
        }

        .tenant-rental-list-header {
            margin-bottom: 1rem;
        }

        .tenant-rental-list-header h3 {
            margin: 0;
            font-size: 1.05rem;
        }

        .tenant-rental-list-header p {
            margin: .25rem 0 0;
            color: var(--muted-foreground);
            font-size: .85rem;
        }

        .tenant-rental-list-header span {
            display: inline-block;
            margin-top: .75rem;
            color: var(--muted-foreground);
            font-size: .85rem;
        }

        .rental-list {
            display: flex;
            flex-direction: column;
            gap: .75rem;
        }

        .rental-item {
            width: 100%;
            text-align: left;
            padding: .9rem;
            border: 1px solid var(--border);
            border-radius: .9rem;
            background: var(--surface-subtle);
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .rental-item:hover {
            border-color: var(--primary);
            transform: translateX(4px);
        }

        .rental-item.active {
            border-color: var(--primary);
            background: rgba(37, 99, 235, .08);
        }

        .rental-item strong {
            display: block;
            font-size: .9rem;
            margin-bottom: .25rem;
        }

        .rental-item span {
            display: block;
            color: var(--muted-foreground);
            font-size: .8rem;
        }

        .rental-item small {
            display: block;
            margin-top: .4rem;
            font-weight: 700;
        }

        .tenant-card-header {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            align-items: flex-start;
            margin-bottom: 1.5rem;
        }

        .tenant-card-header h3 {
            margin: 0;
            font-size: 1.25rem;
        }

        .tenant-card-header p {
            margin: .25rem 0 0;
            color: var(--muted-foreground);
        }

        .tenant-info-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1rem;
        }

        .tenant-info-grid > div:not(.skeleton-box) {
            padding: 1rem;
            border: 1px solid var(--border);
            border-radius: 1rem;
            background: var(--surface-subtle);
        }

        .skeleton-box {
            height: 76px;
            min-height: 76px;
            padding: 0 !important;
        }

        .skeleton-title,
        .skeleton-line,
        .skeleton-box,
        .skeleton-rental-item {
            background: rgba(255,255,255,.08);
        }

        .tenant-info-grid span {
            display: block;
            font-size: .78rem;
            color: var(--muted-foreground);
            margin-bottom: .35rem;
        }

        .tenant-info-grid strong {
            font-size: .95rem;
        }

        .payment-history {
            margin-top: 1.5rem;
            padding: 1rem;
            border: 1px dashed var(--border);
            border-radius: 1rem;
            background: var(--surface-subtle);
        }

        .payment-history h4 {
            margin: 0 0 .75rem;
        }

        .payment-row {
            display: grid;
            grid-template-columns: 1fr auto auto;
            gap: 1rem;
            align-items: center;
            padding: .85rem 0;
            border-bottom: 1px dashed var(--border);
        }

        .payment-row:last-child {
            border-bottom: none;
        }

        .payment-row small {
            color: var(--muted-foreground);
        }

        .tenant-actions,
        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: .75rem;
            margin-top: 1.5rem;
        }

        .is-success {
            color: #16a34a !important;
        }

        .is-danger {
            color: #dc2626 !important;
        }

        .payment-status-success {
            color: #16a34a !important;
        }

        .payment-status-warning {
            color: #d97706 !important;
        }

        .payment-status-danger {
            color: #dc2626 !important;
        }

        .payment-status-info {
            color: #2563eb !important;
        }

        /* Badge styles */
        .badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 600;
            background: var(--surface-subtle);
            border: 1px solid var(--border);
        }

        .badge.danger {
            background: rgba(220, 38, 38, 0.1);
            color: #dc2626;
            border-color: rgba(220, 38, 38, 0.2);
        }

        .badge.warning {
            background: rgba(217, 119, 6, 0.1);
            color: #d97706;
            border-color: rgba(217, 119, 6, 0.2);
        }

        .badge.success {
            background: rgba(22, 163, 74, 0.1);
            color: #16a34a;
            border-color: rgba(22, 163, 74, 0.2);
        }

        .d-none {
            display: none !important;
        }

        @media (max-width: 900px) {
            .loyer-layout {
                grid-template-columns: 1fr;
            }

            .tenant-info-grid {
                grid-template-columns: 1fr;
            }

            .tenant-card-header,
            .tenant-actions,
            .modal-actions {
                flex-direction: column;
                align-items: stretch;
            }

            .payment-row {
                grid-template-columns: 1fr;
                gap: .35rem;
            }
        }

        .tenant-loading-skeleton {
            display: grid;
            grid-template-columns: 1fr 3fr;
            gap: 1.25rem;
            align-items: start;
            margin-bottom: 1.25rem;
        }

        .skeleton-rental-item,
        .skeleton-box {
            position: relative;
            overflow: hidden;
            border-radius: 1rem;
            background: var(--surface-subtle);
            border: 1px solid var(--border);
        }

        .skeleton-rental-item {
            padding: .9rem;
            margin-top: .75rem;
        }

        .skeleton-title,
        .skeleton-line {
            position: relative;
            overflow: hidden;
            border-radius: 999px;
            background: var(--surface-subtle);
        }

        .skeleton-title {
            width: 55%;
            height: 18px;
            margin-bottom: 1rem;
        }

        .skeleton-line {
            height: 12px;
        }

        .skeleton-line + .skeleton-line {
            margin-top: .7rem;
        }

        .skeleton-sm {
            width: 45%;
        }

        .skeleton-md {
            width: 65%;
        }

        .skeleton-lg {
            width: 90%;
        }

        .skeleton-title-wide {
            width: 40%;
            height: 20px;
            margin-bottom: 1.5rem;
        }

        .skeleton-rental-item::after,
        .skeleton-box::after,
        .skeleton-title::after,
        .skeleton-line::after {
            content: "";
            position: absolute;
            top: 0;
            bottom: 0;
            left: -150%;
            width: 150%;
            background: linear-gradient(
                    90deg,
                    transparent 0%,
                    rgba(255,255,255,.35) 45%,
                    rgba(255,255,255,.55) 50%,
                    rgba(255,255,255,.35) 55%,
                    transparent 100%
            );
            animation: shimmer 1.2s ease-in-out infinite;
        }

        html[data-theme="light"] .skeleton-rental-item::after,
        html[data-theme="light"] .skeleton-box::after,
        html[data-theme="light"] .skeleton-title::after,
        html[data-theme="light"] .skeleton-line::after {
            background: linear-gradient(
                    90deg,
                    transparent,
                    rgba(255, 255, 255, .75),
                    transparent
            );
        }

        @keyframes shimmer {
            from {
                left: -150%;
            }
            to {
                left: 150%;
            }
        }

        @media (max-width: 900px) {
            .tenant-loading-skeleton {
                grid-template-columns: 1fr;
            }
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

        .payment-shortcut svg {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
        }

        .payment-shortcut:hover {
            border-color: var(--primary);
            color: var(--primary);
            transform: translateY(-1px);
        }

        .payment-shortcut.active {
            background: rgba(37,99,235,.12);
            border-color: var(--primary);
            color: var(--primary);
        }

        @media (max-width: 900px) {
            .payment-shortcuts {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 600px) {
            .payment-shortcuts {
                grid-template-columns: 1fr;
            }
        }

        /* Notification toast */
        .toast-notification {
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 1rem 1.5rem;
            border-radius: 0.5rem;
            background: var(--card);
            border: 1px solid var(--border);
            box-shadow: var(--shadow-lg);
            z-index: 9999;
            animation: slideIn 0.3s ease;
        }

        .toast-notification.success {
            border-left: 4px solid #16a34a;
        }

        .toast-notification.error {
            border-left: 4px solid #dc2626;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
    </style>

    <script>
        const tenantSearch = document.getElementById('tenant-search');
        const tenantResult = document.getElementById('tenant-result');
        const tenantLoading = document.getElementById('tenant-loading');

        let tenantSearchTimeout = null;
        const rentalList = document.getElementById('rental-list');
        const rentalCount = document.getElementById('rental-count');

        const paymentProperty = document.getElementById('payment-property');
        const paymentAmount = document.getElementById('payment-amount');

        let currentRental = null;
        let currentTenantData = null;
        let currentLocataireAgenceId = null;

        function formatAmount(amount) {
            return new Intl.NumberFormat('fr-FR').format(amount) + ' FCFA';
        }

        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `toast-notification ${type}`;
            toast.textContent = message;
            document.body.appendChild(toast);
            setTimeout(() => {
                toast.remove();
            }, 3000);
        }

        tenantSearch?.addEventListener('input', function () {
            const value = this.value.trim();

            clearTimeout(tenantSearchTimeout);

            tenantResult.classList.add('d-none');
            tenantLoading.classList.add('d-none');

            if (value.length < 2) {
                return;
            }

            tenantLoading.classList.remove('d-none');

            tenantSearchTimeout = setTimeout(async () => {
                try {
                    const res = await fetch(`{{ route('agence.caisse.loyer.search') }}?q=${encodeURIComponent(value)}`);
                    const json = await res.json();

                    tenantLoading.classList.add('d-none');

                    if (json.data && json.data.rentals && json.data.rentals.length > 0) {
                        showTenantRentals(json.data);
                    } else {
                        // Aucun résultat
                        tenantResult.classList.add('d-none');
                        showToast('Aucun locataire trouvé', 'error');
                    }

                } catch (e) {
                    tenantLoading.classList.add('d-none');
                    console.error(e);
                    showToast('Erreur lors de la recherche', 'error');
                }
            }, 500);
        });

        function showTenantRentals(data) {
            currentTenantData = data;

            // Remplir les infos du locataire
            document.getElementById('tenant-name').textContent = data.name;
            document.getElementById('tenant-phone').textContent = data.phone;

            rentalCount.textContent = `${data.rentals.length} bien(s)`;
            rentalList.innerHTML = '';

            data.rentals.forEach((rental, index) => {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = `rental-item ${index === 0 ? 'active' : ''}`;

                // Construction du nom de la propriété
                const propertyName = rental.property || 'Bien sans nom';

                button.innerHTML = `
                    <strong>${escapeHtml(propertyName)}</strong>
                    <span>${escapeHtml(rental.location || 'Localisation non définie')}</span>
                    <small class="${rental.due > 0 ? 'is-danger' : 'is-success'}">
                        ${rental.due > 0 ? formatAmount(rental.due) : 'À jour'}
                    </small>
                `;

                button.addEventListener('click', function () {
                    document.querySelectorAll('.rental-item').forEach(el => el.classList.remove('active'));
                    this.classList.add('active');
                    fillRentalDetails(rental);
                });

                rentalList.appendChild(button);
            });

            if (data.rentals.length > 0) {
                fillRentalDetails(data.rentals[0]);
            }

            tenantResult.classList.remove('d-none');
        }

        function fillRentalDetails(rental) {
            currentRental = rental;

            // Informations du bien
            document.getElementById('rental-title').textContent = rental.property || 'Bien loué';
            document.getElementById('rental-location').textContent = rental.location || 'Localisation non définie';

            // Statut du bail (À jour, Partiel, En retard)
            const statusBadge = document.getElementById('rental-status');
            statusBadge.textContent = rental.status;

            // Supprimer les anciennes classes de statut et ajouter la nouvelle
            statusBadge.classList.remove('danger', 'warning', 'success');
            if (rental.status === 'En retard') {
                statusBadge.classList.add('danger');
            } else if (rental.status === 'Partiel') {
                statusBadge.classList.add('warning');
            } else {
                statusBadge.classList.add('success');
            }

            // Informations financières
            document.getElementById('tenant-rent').textContent = formatAmount(rental.rent);
            document.getElementById('tenant-period').textContent = rental.period || '—';

            const dueElement = document.getElementById('tenant-due');
            dueElement.textContent = formatAmount(rental.due);
            if (rental.due > 0) {
                dueElement.classList.add('is-danger');
            } else {
                dueElement.classList.remove('is-danger');
            }

            document.getElementById('tenant-last-payment').textContent = rental.lastPayment || '—';

            const delayElement = document.getElementById('tenant-delay');
            delayElement.textContent = rental.delay || '—';
            if (rental.delay !== 'Aucun retard' && rental.delay !== '—') {
                delayElement.classList.add('is-danger');
            } else {
                delayElement.classList.remove('is-danger');
            }

            // Statut de paiement
            const paymentStatusElement = document.getElementById('tenant-payment-status');
            paymentStatusElement.textContent = rental.paymentStatus?.label || '—';
            paymentStatusElement.classList.remove(
                'payment-status-success',
                'payment-status-warning',
                'payment-status-danger',
                'payment-status-info'
            );
            if (rental.paymentStatus?.className) {
                paymentStatusElement.classList.add(rental.paymentStatus.className);
            }

            // Pré-remplir le formulaire de paiement
            if (paymentProperty) {
                paymentProperty.value = `${rental.property || ''} — ${rental.location || ''}`;
            }
            if (paymentAmount) {
                paymentAmount.value = rental.due > 0 ? rental.due : rental.rent;
                paymentAmount.min = 0;
                paymentAmount.max = rental.due > 0 ? rental.due : rental.rent;
            }

            // Stocker l'ID pour le paiement
            currentLocataireAgenceId = rental.locataire_agence_id;

            // Historique des paiements
            const historyList = document.getElementById('payment-history-list');
            historyList.innerHTML = '';

            if (rental.history && rental.history.length > 0) {
                rental.history.forEach(item => {
                    historyList.innerHTML += `
                        <div class="payment-row">
                            <span>${escapeHtml(item.period)}</span>
                            <strong class="${item.className}">${escapeHtml(item.status)}</strong>
                            <small>${formatAmount(item.amount)}</small>
                        </div>
                    `;
                });
            } else {
                historyList.innerHTML = `
                    <div class="payment-row">
                        <span style="text-align: center; color: var(--muted-foreground);">
                            Aucun historique de paiement
                        </span>
                    </div>
                `;
            }
        }

        // Validation du montant
        paymentAmount?.addEventListener('input', function() {
            const max = parseFloat(this.max);
            const value = parseFloat(this.value);

            if (value > max) {
                this.value = max;
                showToast(`Le montant ne peut pas dépasser ${formatAmount(max)}`, 'error');
            }

            if (value < 0) {
                this.value = 0;
            }
        });

        // Soumission du paiement
        document.getElementById('submit-payment')?.addEventListener('click', async function() {
            if (!currentRental || !currentLocataireAgenceId) {
                showToast('Aucun bail sélectionné', 'error');
                return;
            }

            const amount = parseFloat(paymentAmount.value);
            if (!amount || amount <= 0) {
                showToast('Veuillez saisir un montant valide', 'error');
                return;
            }

            const method = document.getElementById('payment-method').value;
            const observation = document.getElementById('payment-observation').value;

            // Désactiver le bouton pendant l'envoi
            const submitBtn = this;
            submitBtn.disabled = true;
            submitBtn.textContent = 'Envoi en cours...';

            try {
                const res = await fetch('{{ route("agence.caisse.loyer.payer") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                    },
                    body: JSON.stringify({
                        locataire_agence_id: currentLocataireAgenceId,
                        montant: amount,
                        mode_paiement: method,
                        observation: observation,
                        porte_id: currentRental.porte_id
                    })
                });

                const data = await res.json();

                if (data.success) {
                    showToast('Paiement effectué avec succès', 'success');

                    // Fermer le modal
                    const modal = document.querySelector('[data-modal="payRentModal"]');
                    if (modal) {
                        modal.setAttribute('aria-hidden', 'true');
                    }

                    // Recharger les données du locataire
                    setTimeout(() => {
                        tenantSearch.dispatchEvent(new Event('input'));
                    }, 1000);
                } else {
                    showToast(data.message || 'Erreur lors du paiement', 'error');
                }
            } catch (error) {
                console.error(error);
                showToast('Erreur lors du paiement', 'error');
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Valider le paiement';
            }
        });

        // Gestion du modal
        document.querySelectorAll('[data-open-modal]').forEach(trigger => {
            trigger.addEventListener('click', () => {
                if (!currentRental) {
                    showToast('Veuillez d\'abord sélectionner un bail', 'error');
                    return;
                }
                const modal = document.querySelector(`[data-modal="${trigger.dataset.openModal}"]`);
                if (modal) {
                    modal.setAttribute('aria-hidden', 'false');
                }
            });
        });

        document.querySelectorAll('[data-close-modal]').forEach(trigger => {
            trigger.addEventListener('click', () => {
                const modal = trigger.closest('[data-modal]');
                if (modal) {
                    modal.setAttribute('aria-hidden', 'true');
                }
            });
        });

        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>

@endsection