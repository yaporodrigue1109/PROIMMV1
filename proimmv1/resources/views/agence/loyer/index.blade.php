@extends('agence.layouts.app')

@section('title', 'Loyers et Paiements')

@section('content')
    @php
        $loyers = [
            [
                'id' => 1,
                'locataire' => 'ISMAILA SANGARE',
                'telephone' => '0701020304',
                'propriete' => '2 PIECES (ANYAMA MARTENITE)',
                'periode' => 'mai 2026',
                'montant_net' => 35000,
                'montant_paye' => 0,
                'date_paiement' => null,
                'paiement' => 'Paiement en retard',
                'arriéré' => 120000,
            ],
            [
                'id' => 2,
                'locataire' => 'COULIBALY MOUSSA',
                'telephone' => '0506070809',
                'propriete' => '2 PIECES (ANYAMA MARTENITE)',
                'periode' => 'mai 2026',
                'montant_net' => 35000,
                'montant_paye' => 0,
                'date_paiement' => null,
                'paiement' => 'Paiement en retard',
                'arriéré' => 175000,
            ],
            [
                'id' => 3,
                'locataire' => 'BAMA EMILE',
                'telephone' => '0102030405',
                'propriete' => '2 PIECES (ANYAMA MARTENITE)',
                'periode' => 'mai 2026',
                'montant_net' => 40000,
                'montant_paye' => 40000,
                'date_paiement' => '2026-05-14',
                'paiement' => 'Paiement total',
                'arriéré' => 0,
            ],
            [
                'id' => 4,
                'locataire' => 'TRAORE FANTA',
                'telephone' => '0777115543',
                'propriete' => '2 PIECES (ANYAMA MARTENITE)',
                'periode' => 'mai 2026',
                'montant_net' => 40000,
                'montant_paye' => 0,
                'date_paiement' => null,
                'paiement' => 'Paiement en retard',
                'arriéré' => 40000,
            ],
        ];

        $items = collect($loyers);

        $page = request()->get('page', 1);
        $perPage = request()->get('per_page', 10);

        $totalItems = $items->count();
        $totalPages = (int) ceil($totalItems / $perPage);
        $currentPage = max(1, min((int) $page, max($totalPages, 1)));

        $start = $totalItems > 0 ? (($currentPage - 1) * $perPage) + 1 : 0;
        $end = min($currentPage * $perPage, $totalItems);

        $paginatedItems = $items->slice(($currentPage - 1) * $perPage, $perPage);

        $totalNet = $items->sum('montant_net');
        $totalPaye = $items->sum('montant_paye');
        $totalArrieres = $items->sum('arriéré');
        $totalImpayes = $items->where('montant_paye', 0)->count();

        $formatMoney = fn ($amount) => number_format((float) $amount, 0, ',', ' ') . ' FCFA';

        $formatDate = function ($date) {
            if (!$date) return 'Non payé';
            return \Carbon\Carbon::parse($date)->format('d/m/Y');
        };

        $paiementClass = fn ($paiement) => match ($paiement) {
            'Paiement total' => 'badge-success',
            'Paiement partiel' => 'badge-warning',
            'Paiement en retard' => 'badge-danger',
            default => 'badge-info',
        };
    @endphp

    <section class="page">

        <div class="page-header">
            <div class="page-header-copy">
                <div class="page-heading">
                    <div>
                        <h2>Loyers et paiements</h2>
                        <p class="text-muted mb-0">
                            Suivi des loyers mensuels, paiements effectués, factures et arriérés.
                        </p>
                    </div>
                </div>
            </div>

            <div class="page-actions">
                <a class="btn btn-outline" href="#">Réinitialiser</a>
                <a class="btn btn-primary" href="#">Exporter</a>
            </div>
        </div>

        <div class="stats-grid">
            <article class="stat-card">
                <span>Total loyers</span>
                <strong>{{ $formatMoney($totalNet) }}</strong>
            </article>

            <article class="stat-card">
                <span>Total payé</span>
                <strong class="is-success">{{ $formatMoney($totalPaye) }}</strong>
            </article>

            <article class="stat-card">
                <span>Arriérés</span>
                <strong class="is-danger">{{ $formatMoney($totalArrieres) }}</strong>
            </article>

            <article class="stat-card">
                <span>Impayés</span>
                <strong class="u-text-warning">{{ $totalImpayes }}</strong>
                <small>locataire(s)</small>
            </article>
        </div>

        <div class="table-workspace">
            <div class="card">

                <div class="table-toolbar">
                    <div>
                        <h3>Liste des loyers</h3>
                    </div>

                    <div class="table-toolbar-actions">
                        <select class="entries-per-page">
                            <option>10 résultats</option>
                            <option>25 résultats</option>
                            <option>50 résultats</option>
                        </select>

                        <select class="entries-per-page">
                            <option>Propriétaire</option>
                            <option>TRAORE YOUSSOUF</option>
                            <option>KONE AMARA</option>
                        </select>

                        <select class="entries-per-page">
                            <option>Propriété</option>
                            <option>2 PIECES (ANYAMA MARTENITE)</option>
                            <option>STUDIO (PATATE)</option>
                        </select>

                        <label class="search-field" for="loyer-search">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                      d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z"
                                      clip-rule="evenodd"/>
                            </svg>
                            <input id="loyer-search" type="search" placeholder="Rechercher locataire...">
                        </label>
                    </div>
                </div>

                <div class="table-shell u-table-flush">
                    <table class="data-table responsive-table" id="loyer-table">
                        <thead>
                        <tr>
                            <th>Locataire</th>
                            <th>Propriété</th>
                            <th>Loyer de</th>
                            <th>Montant net</th>
                            <th>Montant payé</th>
                            <th>Date paiement</th>
                            <th>Paiement</th>
                            <th>Arriéré</th>
                            <th class="table-actions-col">Action</th>
                        </tr>
                        </thead>

                        <tbody>
                        @foreach($paginatedItems as $loyer)
                            <tr>
                                <td>
                                    <div class="entity-cell">
                                        <span class="entity-avatar">
                                            {{ strtoupper(mb_substr($loyer['locataire'], 0, 1)) }}
                                        </span>
                                        <div>
                                            <strong>{{ $loyer['locataire'] }}</strong>
                                            <small>{{ $loyer['telephone'] }}</small>
                                        </div>
                                    </div>
                                </td>

                                <td><strong>{{ $loyer['propriete'] }}</strong></td>

                                <td>
                                    <span class="badge badge-info">{{ $loyer['periode'] }}</span>
                                </td>

                                <td class="text-right">
                                    <strong>{{ $formatMoney($loyer['montant_net']) }}</strong>
                                </td>

                                <td class="text-right @if($loyer['montant_paye'] > 0) is-success @endif">
                                    <strong>{{ $formatMoney($loyer['montant_paye']) }}</strong>
                                </td>

                                <td>
                                    <span class="{{ $loyer['date_paiement'] ? 'is-info' : 'text-muted' }}">
                                        {{ $formatDate($loyer['date_paiement']) }}
                                    </span>
                                </td>

                                <td>
                                    <span class="badge {{ $paiementClass($loyer['paiement']) }}">
                                        {{ $loyer['paiement'] }}
                                    </span>
                                </td>

                                <td class="text-right @if($loyer['arriéré'] > 0) text-danger @else is-success @endif">
                                    <strong>{{ $formatMoney($loyer['arriéré']) }}</strong>
                                </td>

                                <td>
                                    <div class="table-actions">
                                        <div class="dropdown-container">
                                            <button class="action-btn info dropdown-trigger" title="Documents">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 2v4h4"/>
                                                </svg>

                                                <svg class="dropdown-arrow" xmlns="http://www.w3.org/2000/svg"
                                                     width="12" height="12" viewBox="0 0 24 24" fill="none"
                                                     stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                                </svg>
                                            </button>

                                            <div class="dropdown-menu">
                                                <a href="#" class="dropdown-item" data-modal-target="payerModal{{ $loyer['id'] }}">
                                                    Payer la facture
                                                </a>

                                                <a href="#" class="dropdown-item" target="_blank">
                                                    Dernière facture
                                                </a>

                                                <a href="#" class="dropdown-item" target="_blank">
                                                    Toutes les factures
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="payment-modal" id="payerModal{{ $loyer['id'] }}">
                                        <div class="payment-modal-content">
                                            <div class="payment-modal-header">
                                                <h3>Payer la facture</h3>
                                                <button type="button" class="modal-close">×</button>
                                            </div>

                                            <div class="payment-modal-body">
                                                <p><strong>{{ $loyer['locataire'] }}</strong></p>
                                                <p>{{ $loyer['propriete'] }}</p>

                                                <label>Montant payé</label>
                                                <input type="number" value="{{ $loyer['montant_net'] - $loyer['montant_paye'] }}">

                                                <label>Mode de paiement</label>
                                                <select>
                                                    <option>Espèces</option>
                                                    <option>Orange Money</option>
                                                    <option>MTN Money</option>
                                                    <option>Wave</option>
                                                    <option>Virement</option>
                                                </select>

                                                <label>Date de paiement</label>
                                                <input type="date" value="{{ now()->format('Y-m-d') }}">
                                            </div>

                                            <div class="payment-modal-footer">
                                                <button type="button" class="btn btn-outline modal-close">Annuler</button>
                                                <button type="button" class="btn btn-primary">Valider le paiement</button>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="table-toolbar">
                    <div class="pagination-info">
                        Affichage de <strong>{{ $start }}</strong> à <strong>{{ $end }}</strong>
                        sur <strong>{{ $totalItems }}</strong> résultats
                    </div>

                    <div class="pagination">
                        <span class="pagination-item disabled">Précédent</span>
                        <span class="pagination-item active">1</span>
                        <span class="pagination-item disabled">Suivant</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <style>
        .entries-per-page {
            height: 40px;
            border: 1px solid #e2e8f0;
            border-radius: .6rem;
            padding: 0 .75rem;
            background: #fff;
            color: #0f172a;
            font-size: .875rem;
        }

        .text-right { text-align: right; }
        .text-danger { color: #dc2626; }
        .text-muted { color: #64748b; }
        .is-success { color: #16a34a; }
        .is-info { color: #2563eb; }

        .dropdown-container {
            position: relative;
            display: inline-block;
        }

        .dropdown-trigger {
            display: inline-flex;
            align-items: center;
            gap: .25rem;
        }

        .dropdown-arrow {
            transition: transform .2s ease;
        }

        .dropdown-container.active .dropdown-arrow {
            transform: rotate(180deg);
        }

        .dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            margin-top: .5rem;
            background: #fff;
            border-radius: .75rem;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, .1);
            border: 1px solid #e2e8f0;
            min-width: 220px;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all .2s ease;
        }

        .dropdown-container.active .dropdown-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            padding: .625rem 1rem;
            color: #1e293b;
            text-decoration: none;
            font-size: .875rem;
            border-bottom: 1px solid #f1f5f9;
        }

        .dropdown-item:hover {
            background: #f8fafc;
        }

        .payment-modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, .45);
            z-index: 2000;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .payment-modal.active {
            display: flex;
        }

        .payment-modal-content {
            width: min(520px, 100%);
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 20px 50px rgba(15, 23, 42, .2);
            overflow: hidden;
        }

        .payment-modal-header,
        .payment-modal-footer {
            padding: 1rem 1.25rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #f1f5f9;
        }

        .payment-modal-footer {
            border-top: 1px solid #f1f5f9;
            border-bottom: none;
        }

        .payment-modal-body {
            padding: 1.25rem;
            display: grid;
            gap: .75rem;
        }

        .payment-modal-body input,
        .payment-modal-body select {
            height: 42px;
            border: 1px solid #e2e8f0;
            border-radius: .6rem;
            padding: 0 .75rem;
        }

        .modal-close {
            border: 0;
            background: transparent;
            font-size: 1.4rem;
            cursor: pointer;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const dropdowns = document.querySelectorAll('.dropdown-container');

            dropdowns.forEach(dropdown => {
                const trigger = dropdown.querySelector('.dropdown-trigger');

                trigger.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();

                    dropdowns.forEach(d => {
                        if (d !== dropdown) d.classList.remove('active');
                    });

                    dropdown.classList.toggle('active');
                });
            });

            document.addEventListener('click', function (e) {
                if (!e.target.closest('.dropdown-container')) {
                    dropdowns.forEach(dropdown => dropdown.classList.remove('active'));
                }
            });

            const searchInput = document.getElementById('loyer-search');
            const rows = document.querySelectorAll('#loyer-table tbody tr');

            searchInput.addEventListener('input', function () {
                const search = this.value.toLowerCase().trim();

                rows.forEach(row => {
                    row.style.display = row.innerText.toLowerCase().includes(search) ? '' : 'none';
                });
            });

            document.querySelectorAll('[data-modal-target]').forEach(btn => {
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    const modal = document.getElementById(this.dataset.modalTarget);
                    if (modal) modal.classList.add('active');
                });
            });

            document.querySelectorAll('.modal-close').forEach(btn => {
                btn.addEventListener('click', function () {
                    this.closest('.payment-modal')?.classList.remove('active');
                });
            });

            document.querySelectorAll('.payment-modal').forEach(modal => {
                modal.addEventListener('click', function (e) {
                    if (e.target === modal) modal.classList.remove('active');
                });
            });
        });
    </script>
@endsection