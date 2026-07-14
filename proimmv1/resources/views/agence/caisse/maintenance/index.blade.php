@extends('agence.layouts.app')

@section('title', 'Paiement maintenance')

@section('content')

    <section class="page">

        <header class="page-header">
            <div class="page-header-copy">
                <div class="page-heading">
                    <div>
                        <h2>Maintenance</h2>
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
               class="payment-shortcut active">

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

        <div class="maintenance-payment-wrapper">

            <div class="maintenance-search-card">
                <div class="form-grid u-form-grid-2">
                    <label class="form-field">
                        <span>Propriétaire</span>
                        <select id="owner-select" class="select2">
                            <option value="">Sélectionner un propriétaire</option>
                            <option value="1">Kouadio Marc</option>
                            <option value="2">Amani Claire</option>
                        </select>
                    </label>

                    <label class="form-field">
                        <span>Propriété associée</span>
                        <select id="property-select" class="select2" disabled>
                            <option value="">Sélectionner une propriété</option>
                        </select>
                    </label>
                </div>
            </div>

            <div id="maintenance-loading" class="maintenance-loading-skeleton d-none">

                <aside class="maintenance-list-card">
                    <div class="skeleton-title"></div>

                    <div class="skeleton-maintenance-item">
                        <div class="skeleton-line skeleton-lg"></div>
                        <div class="skeleton-line skeleton-sm"></div>
                        <div class="skeleton-line skeleton-md"></div>
                    </div>

                    <div class="skeleton-maintenance-item">
                        <div class="skeleton-line skeleton-lg"></div>
                        <div class="skeleton-line skeleton-sm"></div>
                        <div class="skeleton-line skeleton-md"></div>
                    </div>
                </aside>

                <div class="maintenance-detail-card skeleton-panel">
                    <div class="skeleton-line skeleton-title-wide"></div>

                    <div class="maintenance-info-grid">
                        <div class="skeleton-box"></div>
                        <div class="skeleton-box"></div>
                        <div class="skeleton-box"></div>
                        <div class="skeleton-box"></div>
                        <div class="skeleton-box"></div>
                        <div class="skeleton-box"></div>
                    </div>


                </div>

            </div>

            <div id="maintenance-result" class="maintenance-layout d-none">

                <aside class="maintenance-list-card">
                    <div class="maintenance-list-header">
                        <h3>Maintenances</h3>
                        <span id="maintenance-count">0</span>
                    </div>

                    <div id="maintenance-list" class="maintenance-list"></div>
                </aside>

                <div class="maintenance-detail-card">
                    <div class="maintenance-detail-header">
                        <div>
                            <h3 id="maintenance-title">Maintenance</h3>
                            <p id="maintenance-property">Propriété</p>
                        </div>

                        <span id="maintenance-status" class="badge danger">À payer</span>
                    </div>

                    <div class="maintenance-info-grid">
                        <div>
                            <span>Propriétaire</span>
                            <strong id="detail-owner">-</strong>
                        </div>

                        <div>
                            <span>Type maintenance</span>
                            <strong id="detail-type">-</strong>
                        </div>

                        <div>
                            <span>Date intervention</span>
                            <strong id="detail-date">-</strong>
                        </div>

                        <div>
                            <span>Prestataire</span>
                            <strong id="detail-provider">-</strong>
                        </div>

                        <div>
                            <span>Montant total</span>
                            <strong id="detail-amount" class="is-danger">0 FCFA</strong>
                        </div>

                        <div>
                            <span>Statut paiement</span>
                            <strong id="detail-payment-status">Non payé</strong>
                        </div>
                    </div>

                    <div class="maintenance-description">
                        <h4>Description</h4>
                        <p id="detail-description">-</p>
                    </div>

                    <div class="maintenance-expenses">
                        <div class="maintenance-expenses-header">
                            <h4>Détails du paiement</h4>
                            <strong id="detail-total" class="is-danger">0 FCFA</strong>
                        </div>

                        <div id="expense-list" class="expense-list"></div>
                    </div>

                    <div class="maintenance-actions">
                        <button type="button"
                                class="btn btn-primary"
                                data-open-modal="payMaintenanceModal">
                            Décaisser / Payer
                        </button>
                    </div>
                </div>

            </div>

        </div>

        <div class="modal" data-modal="payMaintenanceModal" aria-hidden="true">
            <div class="modal-box u-modal-md">

                <div class="modal-header">
                    <h3>Décaissement maintenance</h3>

                    <button class="modal-close" data-close-modal aria-label="Fermer">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="u-icon-sm"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>

                    </button>
                </div>

                <div class="modal-body">
                    <form action="#" method="POST">
                        @csrf

                        <div class="form-grid u-form-grid-2">

                            <label class="form-field">
                                <span>Montant à décaisser</span>
                                <input id="payment-amount"
                                       type="number"
                                       min="0"
                                       placeholder="Montant payé">
                            </label>

                            <label class="form-field">
                                <span>Mode de paiement</span>
                                <select>
                                    <option>Espèces</option>
                                    <option>Wave</option>
                                    <option>Orange Money</option>
                                    <option>Virement bancaire</option>
                                </select>
                            </label>

                            <label class="form-field form-field-wide">
                                <span>Observation</span>
                                <textarea placeholder="Observation facultative..."></textarea>
                            </label>

                        </div>

                        <div class="modal-actions">
                            <button type="button" class="btn btn-outline" data-close-modal>
                                Annuler
                            </button>

                            <button type="button" class="btn btn-primary">
                                Valider le décaissement
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>

    </section>

    <style>
        .maintenance-payment-wrapper {
            width: 1100px;
            max-width: 100%;
            margin: 2rem auto 0;
        }

        .maintenance-search-card,
        .maintenance-list-card,
        .maintenance-detail-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 1.25rem;
            box-shadow: var(--shadow);
        }

        .maintenance-search-card {
            padding: 1.5rem;
            margin-bottom: 1.25rem;
        }

        .maintenance-layout,
        .maintenance-loading-skeleton {
            display: grid;
            grid-template-columns: 1fr 3fr;
            gap: 1.25rem;
            align-items: start;
        }

        .maintenance-list-card,
        .maintenance-detail-card {
            padding: 1.25rem;
        }

        .maintenance-list-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .maintenance-list-header h3,
        .maintenance-detail-header h3 {
            margin: 0;
            font-size: 1.1rem;
        }

        .maintenance-list-header span {
            color: var(--muted-foreground);
            font-size: .85rem;
        }

        .maintenance-list {
            display: flex;
            flex-direction: column;
            gap: .75rem;
        }

        .maintenance-item {
            width: 100%;
            text-align: left;
            padding: .9rem;
            border: 1px solid var(--border);
            border-radius: .9rem;
            background: var(--surface-subtle);
            cursor: pointer;
        }

        .maintenance-item.active {
            border-color: var(--primary);
            background: rgba(37, 99, 235, .08);
        }

        .maintenance-item strong {
            display: block;
            font-size: .9rem;
            margin-bottom: .25rem;
        }

        .maintenance-item span {
            display: block;
            color: var(--muted-foreground);
            font-size: .8rem;
        }

        .maintenance-item small {
            display: block;
            margin-top: .4rem;
            font-weight: 700;
        }

        .maintenance-detail-header {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            align-items: flex-start;
            margin-bottom: 1.5rem;
        }

        .maintenance-detail-header p {
            margin: .25rem 0 0;
            color: var(--muted-foreground);
        }

        .maintenance-info-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1rem;
        }

        .maintenance-info-grid > div:not(.skeleton-box) {
            padding: 1rem;
            border: 1px solid var(--border);
            border-radius: 1rem;
            background: var(--surface-subtle);
        }

        .maintenance-info-grid span {
            display: block;
            font-size: .78rem;
            color: var(--muted-foreground);
            margin-bottom: .35rem;
        }

        .maintenance-description,
        .maintenance-expenses {
            margin-top: 1.5rem;
            padding: 1rem;
            border: 1px dashed var(--border);
            border-radius: 1rem;
            background: var(--surface-subtle);
        }

        .maintenance-description h4,
        .maintenance-expenses h4 {
            margin: 0;
        }

        .maintenance-description p {
            margin: .5rem 0 0;
            color: var(--muted-foreground);
        }

        .maintenance-expenses-header,
        .expense-row {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            align-items: center;
        }

        .maintenance-expenses-header {
            margin-bottom: .75rem;
        }

        .expense-row {
            padding: .75rem 0;
            border-bottom: 1px dashed var(--border);
        }

        .expense-row:last-child {
            border-bottom: none;
        }

        .expense-row span {
            color: var(--muted-foreground);
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

        .maintenance-actions,
        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: .75rem;
            margin-top: 1.5rem;
        }

        .is-danger {
            color: #dc2626 !important;
        }

        .d-none {
            display: none !important;
        }

        .skeleton-maintenance-item,
        .skeleton-box,
        .skeleton-title,
        .skeleton-line {
            position: relative;
            overflow: hidden;
            background: var(--surface-subtle);
        }

        .skeleton-maintenance-item {
            padding: .9rem;
            margin-top: .75rem;
            border-radius: 1rem;
            border: 1px solid var(--border);
        }

        .skeleton-box {
            height: 76px;
            min-height: 76px;
            padding: 0 !important;
            border-radius: 1rem;
            border: 1px solid var(--border);
        }

        .skeleton-title,
        .skeleton-line {
            border-radius: 999px;
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

        .skeleton-maintenance-item::after,
        .skeleton-box::after,
        .skeleton-title::after,
        .skeleton-line::after {
            content: "";
            position: absolute;
            inset: 0;
            transform: translateX(-100%);
            background: linear-gradient(
                    90deg,
                    transparent,
                    rgba(255, 255, 255, .18),
                    transparent
            );
            animation: shimmer 1.25s infinite;
        }

        html[data-theme="light"] .skeleton-maintenance-item::after,
        html[data-theme="light"] .skeleton-box::after,
        html[data-theme="light"] .skeleton-title::after,
        html[data-theme="light"] .skeleton-line::after {
            background: linear-gradient(
                    90deg,
                    transparent,
                    rgba(255, 255, 255, .8),
                    transparent
            );
        }

        @keyframes shimmer {
            100% {
                transform: translateX(100%);
            }
        }

        .skeleton-panel {
            position: relative;
            overflow: hidden;
        }

        .skeleton-panel::after {
            content: "";
            position: absolute;
            inset: 0;
            transform: translateX(-100%);
            background: linear-gradient(
                    90deg,
                    transparent,
                    rgba(255,255,255,.12),
                    transparent
            );
            animation: shimmer 1.25s infinite;
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
        const ownerSelect = document.getElementById('owner-select');
        const propertySelect = document.getElementById('property-select');
        const maintenanceResult = document.getElementById('maintenance-result');
        const maintenanceLoading = document.getElementById('maintenance-loading');
        const maintenanceList = document.getElementById('maintenance-list');
        const maintenanceCount = document.getElementById('maintenance-count');

        const paymentAmount = document.getElementById('payment-amount');
        const detailPaymentStatus = document.getElementById('detail-payment-status');

        let currentMaintenanceTotal = 0;
        let maintenanceSearchTimeout = null;

        const properties = {
            1: [
                { id: 1, name: 'Appartement B2 — Cocody' },
                { id: 2, name: 'Villa duplex — Angré' }
            ],
            2: [
                { id: 3, name: 'Studio A1 — Marcory' }
            ]
        };

        const maintenances = {
            1: [
                {
                    title: 'Réparation plomberie',
                    property: 'Appartement B2 — Cocody',
                    owner: 'Kouadio Marc',
                    type: 'Plomberie',
                    date: '03/06/2026',
                    provider: 'Société Aqua Service',
                    priority: 'Urgente',
                    status: 'À payer',
                    description: 'Fuite constatée dans la salle de bain. Intervention nécessaire pour remplacement du flexible.',
                    paidAmount: 0,
                    items: [
                        { label: 'Main d’œuvre', amount: 20000 },
                        { label: 'Flexible plomberie', amount: 10000 },
                        { label: 'Déplacement technicien', amount: 5000 },
                        { label: 'Achat robinet', amount: 10000 }
                    ]
                },
                {
                    title: 'Remplacement serrure',
                    property: 'Appartement B2 — Cocody',
                    owner: 'Kouadio Marc',
                    type: 'Serrurerie',
                    date: '01/06/2026',
                    provider: 'Serrurier Express',
                    priority: 'Normale',
                    status: 'À payer',
                    description: 'Serrure de la porte principale bloquée. Remplacement demandé.',
                    paidAmount: 10000,
                    items: [
                        { label: 'Nouvelle serrure', amount: 15000 },
                        { label: 'Main d’œuvre', amount: 8000 },
                        { label: 'Déplacement', amount: 2000 }
                    ]
                }
            ],
            2: [
                {
                    title: 'Entretien climatisation',
                    property: 'Villa duplex — Angré',
                    owner: 'Kouadio Marc',
                    type: 'Climatisation',
                    date: '02/06/2026',
                    provider: 'Cool Service',
                    priority: 'Normale',
                    status: 'À payer',
                    description: 'Nettoyage complet et recharge de gaz pour la climatisation du salon.',
                    paidAmount: 60000,
                    items: [
                        { label: 'Nettoyage climatisation', amount: 25000 },
                        { label: 'Recharge gaz', amount: 30000 },
                        { label: 'Déplacement', amount: 5000 }
                    ]
                }
            ],
            3: [
                {
                    title: 'Réparation électricité',
                    property: 'Studio A1 — Marcory',
                    owner: 'Amani Claire',
                    type: 'Électricité',
                    date: '31/05/2026',
                    provider: 'Élec Pro',
                    priority: 'Urgente',
                    status: 'À payer',
                    description: 'Problème de disjoncteur signalé par le locataire.',
                    paidAmount: 0,
                    items: [
                        { label: 'Disjoncteur', amount: 18000 },
                        { label: 'Main d’œuvre', amount: 12000 },
                        { label: 'Déplacement', amount: 5000 }
                    ]
                }
            ]
        };

        ownerSelect?.addEventListener('change', function () {
            const ownerId = this.value;

            clearTimeout(maintenanceSearchTimeout);

            propertySelect.innerHTML = '<option value="">Sélectionner une propriété</option>';
            propertySelect.disabled = true;

            maintenanceResult.classList.add('d-none');
            maintenanceLoading.classList.add('d-none');

            if (!ownerId || !properties[ownerId]) return;

            properties[ownerId].forEach(property => {
                propertySelect.innerHTML += `<option value="${property.id}">${property.name}</option>`;
            });

            propertySelect.disabled = false;
        });

        propertySelect?.addEventListener('change', function () {
            const propertyId = this.value;

            clearTimeout(maintenanceSearchTimeout);

            maintenanceList.innerHTML = '';
            maintenanceResult.classList.add('d-none');
            maintenanceLoading.classList.add('d-none');

            if (!propertyId || !maintenances[propertyId]) return;

            maintenanceLoading.classList.remove('d-none');

            maintenanceSearchTimeout = setTimeout(() => {
                const data = maintenances[propertyId];

                maintenanceCount.textContent = `${data.length} dossier(s)`;
                maintenanceList.innerHTML = '';

                data.forEach((item, index) => {
                    const total = getMaintenanceTotal(item);
                    const status = getPaymentStatus(item.paidAmount, total);

                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = `maintenance-item ${index === 0 ? 'active' : ''}`;
                    button.innerHTML = `
                        <strong>${item.title}</strong>
                        <span>${status.label}</span>
                        <small class="is-danger">${formatAmount(total)}</small>
                    `;

                    button.addEventListener('click', function () {
                        document.querySelectorAll('.maintenance-item').forEach(el => el.classList.remove('active'));
                        this.classList.add('active');
                        fillMaintenanceDetails(item);
                    });

                    maintenanceList.appendChild(button);
                });

                fillMaintenanceDetails(data[0]);

                maintenanceLoading.classList.add('d-none');
                maintenanceResult.classList.remove('d-none');
            }, 800);
        });

        paymentAmount?.addEventListener('input', function () {
            updatePaymentStatus(Number(this.value || 0), currentMaintenanceTotal);
        });

        function getMaintenanceTotal(item) {
            return item.items.reduce((sum, expense) => sum + expense.amount, 0);
        }

        function formatAmount(amount) {
            return new Intl.NumberFormat('fr-FR').format(amount) + ' FCFA';
        }

        function getPaymentStatus(paidAmount, totalAmount) {
            if (paidAmount <= 0) {
                return {
                    label: 'Non payé',
                    className: 'payment-status-danger'
                };
            }

            if (paidAmount < totalAmount) {
                return {
                    label: 'Partiellement payé',
                    className: 'payment-status-warning'
                };
            }

            if (paidAmount === totalAmount) {
                return {
                    label: 'Soldé',
                    className: 'payment-status-success'
                };
            }

            return {
                label: 'Surpayé',
                className: 'payment-status-info'
            };
        }

        function setStatusElement(element, status) {
            element.textContent = status.label;

            element.classList.remove(
                'payment-status-success',
                'payment-status-warning',
                'payment-status-danger',
                'payment-status-info'
            );

            element.classList.add(status.className);
        }

        function updatePaymentStatus(paidAmount, totalAmount) {
            const status = getPaymentStatus(paidAmount, totalAmount);
            setStatusElement(detailPaymentStatus, status);
        }

        function fillMaintenanceDetails(item) {
            const total = getMaintenanceTotal(item);
            currentMaintenanceTotal = total;

            document.getElementById('maintenance-title').textContent = item.title;
            document.getElementById('maintenance-property').textContent = item.property;
            document.getElementById('maintenance-status').textContent = item.status;

            document.getElementById('detail-owner').textContent = item.owner;
            document.getElementById('detail-type').textContent = item.type;
            document.getElementById('detail-date').textContent = item.date;
            document.getElementById('detail-provider').textContent = item.provider;
            document.getElementById('detail-amount').textContent = formatAmount(total);
            document.getElementById('detail-description').textContent = item.description;

            document.getElementById('detail-total').textContent = formatAmount(total);

            paymentAmount.value = item.paidAmount || 0;

            updatePaymentStatus(Number(paymentAmount.value || 0), total);

            const expenseList = document.getElementById('expense-list');
            expenseList.innerHTML = '';

            item.items.forEach(expense => {
                expenseList.innerHTML += `
                    <div class="expense-row">
                        <span>${expense.label}</span>
                        <strong>${formatAmount(expense.amount)}</strong>
                    </div>
                `;
            });
        }
    </script>

@endsection