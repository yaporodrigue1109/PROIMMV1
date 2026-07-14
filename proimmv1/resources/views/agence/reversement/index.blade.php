@extends('agence.layouts.app')

@section('title', 'Reversements')

@section('content')
    @php
        $reversements = [
            [
                'id' => 1,
                'cour' => 'COUR BLANKRO',
                'proprietaire' => 'KOUASSI MARCEL',
                'telephone' => '0701020304',
                'montant_attendu' => 850000,
                'montant_paye' => 850000,
                'montant_restant' => 0,
                'periode' => 'Mai 2026',
            ],
            [
                'id' => 2,
                'cour' => 'COUR ATTIESSO',
                'proprietaire' => 'TRAORE AMINATA',
                'telephone' => '0506070809',
                'montant_attendu' => 620000,
                'montant_paye' => 400000,
                'montant_restant' => 220000,
                'periode' => 'Mai 2026',
            ],
            [
                'id' => 3,
                'cour' => 'COUR 1',
                'proprietaire' => "N'GUESSAN RODRIGUE",
                'telephone' => '0102030405',
                'montant_attendu' => 1100000,
                'montant_paye' => 0,
                'montant_restant' => 1100000,
                'periode' => 'Mai 2026',
            ],
            [
                'id' => 4,
                'cour' => 'ORODAPO 1',
                'proprietaire' => 'BAMBA SEYDOU',
                'telephone' => '0777115543',
                'montant_attendu' => 740000,
                'montant_paye' => 740000,
                'montant_restant' => 0,
                'periode' => 'Avril 2026',
            ],
        ];

        $items = collect($reversements);

        $page = request()->get('page', 1);
        $perPage = request()->get('per_page', 25);

        $totalItems = $items->count();
        $totalPages = (int) ceil($totalItems / $perPage);
        $currentPage = max(1, min((int) $page, max($totalPages, 1)));

        $start = $totalItems > 0 ? (($currentPage - 1) * $perPage) + 1 : 0;
        $end = min($currentPage * $perPage, $totalItems);

        $paginatedItems = $items->slice(($currentPage - 1) * $perPage, $perPage);

        $totalAttendu = $items->sum('montant_attendu');
        $totalPaye = $items->sum('montant_paye');
        $totalRestant = $items->sum('montant_restant');
        $coursAvecReste = $items->where('montant_restant', '>', 0)->count();

        $formatMoney = fn ($amount) => number_format((float) $amount, 0, ',', ' ') . ' FCFA';

        $statutReversement = fn ($reste, $paye) => $reste <= 0 ? 'Payé' : ($paye > 0 ? 'Partiel' : 'En attente');

        $statutClass = fn ($reste, $paye) => $reste <= 0
            ? 'badge-success'
            : ($paye > 0 ? 'badge-warning' : 'badge-danger');
    @endphp

    <section class="page">

        <div class="page-header">
            <div class="page-header-copy">
                <div class="page-heading">
                    <div>
                        <h2>Fiche reversement</h2>
                        <p class="text-muted mb-0">
                            Suivi des montants attendus, payés et restants par cour et propriétaire.
                        </p>
                    </div>
                </div>
            </div>

            <div class="page-actions">
                <form method="GET" action="#" class="reversement-date-filter">
                    <x-ui.input
                            name="date_debut"
                            type="date"
                            :value="request('date_debut', now()->format('Y-m-d'))"
                    />
                    <x-ui.input
                            name="date_fin"
                            type="date"
                            :value="request('date_fin', now()->format('Y-m-d'))"
                    />
                    <button class="btn btn-outline" type="submit">Filtrer</button>
                </form>

                <a class="btn btn-primary" href="#">
                    Exporter
                </a>
            </div>
        </div>

        <div class="stats-grid">
            <article class="stat-card">
                <span>Total attendu</span>
                <strong>{{ $formatMoney($totalAttendu) }}</strong>
            </article>

            <article class="stat-card">
                <span>Total payé</span>
                <strong class="is-success">{{ $formatMoney($totalPaye) }}</strong>
            </article>

            <article class="stat-card">
                <span>Total restant</span>
                <strong class="is-danger">{{ $formatMoney($totalRestant) }}</strong>
                <small>{{ $coursAvecReste }} cour(s)</small>
            </article>

            <article class="stat-card">
                <span>Taux payé</span>
                <strong class="is-info">
                    {{ $totalAttendu > 0 ? round(($totalPaye / $totalAttendu) * 100) : 0 }}%
                </strong>
            </article>
        </div>

        <div class="table-workspace">
            <div class="card">
                <div class="table-toolbar">
                    <div>
                        <h3>Liste des biens</h3>
                    </div>

                    <div class="table-toolbar-actions">
                        <form method="GET" action="#" class="entries-selector">
                            <x-ui.select
                                    name="per_page"
                                    :options="collect([5, 10, 15, 20, 25])->mapWithKeys(fn ($limit) => [$limit => $limit])->toArray()"
                                    :value="$perPage"
                                    onchange="this.form.submit()"
                            />
                            <span>résultats par page</span>
                        </form>

                        <label class="search-field" for="reversement-search">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                      d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z"
                                      clip-rule="evenodd"/>
                            </svg>
                            <input id="reversement-search" type="search" placeholder="Rechercher...">
                        </label>
                    </div>
                </div>

                <div class="table-shell u-table-flush">
                    <table class="data-table responsive-table sortable-table" id="reversement-table">
                        <thead>
                        <tr>
                            <th data-sort="text">Nom de la cour</th>
                            <th data-sort="text">Propriétaire</th>
                            <th data-sort="number">Montant attendu</th>
                            <th data-sort="number">Montant payé</th>
                            <th data-sort="number">Montant restant</th>
                            <th>Statut</th>
                            <th class="table-actions-col">Fiche</th>
                        </tr>
                        </thead>

                        <tbody>
                        @foreach($paginatedItems as $reversement)
                            <tr>
                                <td>
                                    <div class="entity-cell">
                                        <span class="entity-avatar">
                                            {{ strtoupper(mb_substr($reversement['cour'], 0, 1)) }}
                                        </span>
                                        <div>
                                            <strong>{{ $reversement['cour'] }}</strong>
                                            <small>{{ $reversement['periode'] }}</small>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <div>
                                        <strong>{{ $reversement['proprietaire'] }}</strong>
                                        <small>{{ $reversement['telephone'] }}</small>
                                    </div>
                                </td>

                                <td class="text-right" data-value="{{ $reversement['montant_attendu'] }}">
                                    <strong>{{ $formatMoney($reversement['montant_attendu']) }}</strong>
                                </td>

                                <td class="text-right is-success" data-value="{{ $reversement['montant_paye'] }}">
                                    <strong>{{ $formatMoney($reversement['montant_paye']) }}</strong>
                                </td>

                                <td class="text-right @if($reversement['montant_restant'] > 0) text-danger @endif"
                                    data-value="{{ $reversement['montant_restant'] }}">
                                    <strong>{{ $formatMoney($reversement['montant_restant']) }}</strong>
                                </td>

                                <td>
                                    <span class="badge {{ $statutClass($reversement['montant_restant'], $reversement['montant_paye']) }}">
                                        {{ $statutReversement($reversement['montant_restant'], $reversement['montant_paye']) }}
                                    </span>
                                </td>

                                <td>
                                    <div class="table-actions">
                                        <div class="dropdown-container">
                                            <button class="action-btn info dropdown-trigger" title="Fiche">
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
                                                <a href="#" class="dropdown-item">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                    </svg>
                                                    Fiche de reversement
                                                </a>

                                                <a href="#" class="dropdown-item">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1M12 12v8m0-8l-4 4m4-4l4 4M4 4h16"/>
                                                    </svg>
                                                    Télécharger PDF
                                                </a>
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
                        @if($currentPage > 1)
                            <a href="?page={{ $currentPage - 1 }}&per_page={{ $perPage }}" class="pagination-item">
                                Précédent
                            </a>
                        @else
                            <span class="pagination-item disabled">Précédent</span>
                        @endif

                        @for($p = 1; $p <= $totalPages; $p++)
                            @if($p == $currentPage)
                                <span class="pagination-item active">{{ $p }}</span>
                            @else
                                <a href="?page={{ $p }}&per_page={{ $perPage }}" class="pagination-item">{{ $p }}</a>
                            @endif
                        @endfor

                        @if($currentPage < $totalPages)
                            <a href="?page={{ $currentPage + 1 }}&per_page={{ $perPage }}" class="pagination-item">
                                Suivant
                            </a>
                        @else
                            <span class="pagination-item disabled">Suivant</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>

    <style>
        .reversement-date-filter {
            display: flex;
            align-items: center;
            gap: .5rem;
            flex-wrap: wrap;
        }

        .reversement-date-filter input,
        .entries-per-page {
            height: 40px;
            border: 1px solid #e2e8f0;
            border-radius: .6rem;
            padding: 0 .75rem;
            background: #fff;
            color: #0f172a;
            font-size: .875rem;
        }

        .entries-selector {
            display: flex;
            align-items: center;
            gap: .5rem;
            font-size: .875rem;
            color: #64748b;
        }

        .text-right {
            text-align: right;
        }

        .text-danger {
            color: #dc2626;
        }

        .is-success {
            color: #16a34a;
        }

        .is-info {
            color: #2563eb;
        }

        .sortable-table th[data-sort] {
            cursor: pointer;
            user-select: none;
            white-space: nowrap;
        }

        .sortable-table th[data-sort]::after {
            content: "↕";
            font-size: .75rem;
            margin-left: .4rem;
            opacity: .45;
        }

        .sortable-table th.sorted-asc::after {
            content: "↑";
            opacity: .9;
        }

        .sortable-table th.sorted-desc::after {
            content: "↓";
            opacity: .9;
        }

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
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, .1), 0 10px 10px -5px rgba(0, 0, 0, .02);
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
            gap: .75rem;
            padding: .625rem 1rem;
            color: #1e293b;
            text-decoration: none;
            font-size: .875rem;
            transition: background .15s;
            border-bottom: 1px solid #f1f5f9;
        }

        .dropdown-item:last-child {
            border-bottom: none;
        }

        .dropdown-item:hover {
            background: #f8fafc;
        }

        .dropdown-item svg {
            color: #64748b;
            flex-shrink: 0;
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

            const searchInput = document.getElementById('reversement-search');
            const table = document.getElementById('reversement-table');
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));

            searchInput.addEventListener('input', function () {
                const search = this.value.toLowerCase().trim();

                rows.forEach(row => {
                    row.style.display = row.innerText.toLowerCase().includes(search) ? '' : 'none';
                });
            });

            table.querySelectorAll('th[data-sort]').forEach((th, index) => {
                th.addEventListener('click', function () {
                    const type = th.dataset.sort;
                    const direction = th.classList.contains('sorted-asc') ? 'desc' : 'asc';

                    table.querySelectorAll('th').forEach(h => h.classList.remove('sorted-asc', 'sorted-desc'));
                    th.classList.add(direction === 'asc' ? 'sorted-asc' : 'sorted-desc');

                    const sortedRows = rows.sort((a, b) => {
                        let aValue = a.children[index].dataset.value || a.children[index].innerText.trim();
                        let bValue = b.children[index].dataset.value || b.children[index].innerText.trim();

                        if (type === 'number') {
                            aValue = Number(aValue) || 0;
                            bValue = Number(bValue) || 0;
                        } else {
                            aValue = aValue.toLowerCase();
                            bValue = bValue.toLowerCase();
                        }

                        if (aValue < bValue) return direction === 'asc' ? -1 : 1;
                        if (aValue > bValue) return direction === 'asc' ? 1 : -1;
                        return 0;
                    });

                    sortedRows.forEach(row => tbody.appendChild(row));
                });
            });
        });
    </script>
@endsection
