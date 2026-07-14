@extends('admin.layouts.app')

@section('title', 'Abonnements')
@section('header_title', 'Abonnements')

@section('content')
    @php
        $abonnements = [
            [
                'agence'      => 'Pros Immobilier Cocody',
                'code_agence' => 'AGC-001',
                'plan'        => 'Formule standard',
                'montant'     => 50000,
                'cycle'       => 'Mensuel',
                'date_debut'  => '2026-04-01',
                'date_fin'    => '2026-04-30',
                'statut'      => 'Actif',
                'paiement'    => 'Paye',
                'created_at'  => "Aujourd'hui, 09:40",
                'modules'     => ['SMS illimite', 'WhatsApp Business', 'Portail web', 'Statistiques avancees'],
            ],
            [
                'agence'      => 'Pros Immobilier Bingerville',
                'code_agence' => 'AGC-004',
                'plan'        => 'Formule standard',
                'montant'     => 72000,
                'cycle'       => 'Mensuel',
                'date_debut'  => '2026-04-15',
                'date_fin'    => '2026-05-15',
                'statut'      => 'En attente',
                'paiement'    => 'A confirmer',
                'created_at'  => 'Hier, 17:15',
                'modules'     => ['Portail proprietaire', 'Portail locataire', 'WhatsApp Business'],
            ],
            [
                'agence'      => 'Pros Immobilier Plateau',
                'code_agence' => 'AGC-002',
                'plan'        => 'Formule standard',
                'montant'     => 52000,
                'cycle'       => 'Mensuel',
                'date_debut'  => '2026-04-05',
                'date_fin'    => '2026-05-05',
                'statut'      => 'Actif',
                'paiement'    => 'Paye',
                'created_at'  => '27/04/2026, 12:08',
                'modules'     => ['Portail web'],
            ],
            [
                'agence'      => 'Pros Immobilier Yopougon',
                'code_agence' => 'AGC-003',
                'plan'        => 'Formule standard',
                'montant'     => 50000,
                'cycle'       => 'Mensuel',
                'date_debut'  => '2026-03-01',
                'date_fin'    => '2026-03-31',
                'statut'      => 'Expire',
                'paiement'    => 'Paye',
                'created_at'  => '01/03/2026, 08:22',
                'modules'     => [],
            ],
        ];

        $items               = collect($abonnements);
        $totalAbonnements    = $items->count();
        $abonnementsActifs   = $items->where('statut', 'Actif')->count();
        $abonnementsAttente  = $items->where('statut', 'En attente')->count();
        $abonnementsExpires  = $items->where('statut', 'Expire')->count();
        $revenuMensuel       = $items->where('statut', 'Actif')->sum('montant');
        $prochainesEcheances = $items->sortBy('date_fin')->take(3);

        $statusClass = fn ($status) => match ($status) {
            'Actif'      => 'badge-success',
            'En attente' => 'badge-warning',
            'Expire'     => 'badge-danger',
            default      => 'badge-info',
        };

        $paymentClass = fn ($status) => match ($status) {
            'Paye'        => 'status-pill-success',
            'A confirmer' => 'status-pill-warning',
            default       => 'status-pill-neutral',
        };

        $statusFilter = fn ($status) => match ($status) {
            'Actif'      => 'actif',
            'En attente' => 'attente',
            'Expire'     => 'expire',
            default      => 'autre',
        };

        $formatDate = function ($value) {
            try {
                return \Carbon\Carbon::parse($value)->format('d/m/Y');
            } catch (\Throwable $e) {
                return (string) $value;
            }
        };

        $formatMoney = fn ($amount) => number_format((float) $amount, 0, ',', ' ') . ' FCFA';
    @endphp

    <section class="page">

        {{-- En-tête --}}
        <div class="page-header">
            <div class="page-header-copy">
                <div class="page-heading">
                    <div>
                        <h2>Derniers abonnements</h2>
                        <p class="text-muted mb-0">
                            Consultez les abonnements recents, les paiements et les echeances de l'application.
                        </p>
                    </div>
                </div>
            </div>

            <div class="page-actions">
                <a class="btn btn-primary" href="{{ route('admin.abonnements.create') }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nouvel abonnement
                </a>
            </div>
        </div>

        {{-- Cartes de statistiques --}}
        <div class="stats-grid">
            <article class="stat-card">
                <span>Total</span>
                <strong>{{ $totalAbonnements }}</strong>
                <small>abonnements suivis</small>
            </article>
            <article class="stat-card">
                <span>Actifs</span>
                <strong class="is-success">{{ $abonnementsActifs }}</strong>
                <small>{{ $formatMoney($revenuMensuel) }} / mois</small>
            </article>
            <article class="stat-card">
                <span>En attente</span>
                <strong class="u-text-warning">{{ $abonnementsAttente }}</strong>
                <small>paiements a confirmer</small>
            </article>
            <article class="stat-card">
                <span>Expires</span>
                <strong class="is-danger">{{ $abonnementsExpires }}</strong>
                <small>a relancer</small>
            </article>
        </div>

        {{-- Encarts latéraux : Échéances + Synthèse --}}
        <div class="panel-grid">

            <article class="card">
                <h3>Prochaines echeances</h3>
                <div class="compact-list compact-list-grid">
                    @foreach($prochainesEcheances as $abonnement)
                        <a href="{{ route('admin.abonnements.show', $abonnement['code_agence']) }}"
                           class="compact-list-item">
                            <span></span>
                            <div>
                                <strong>{{ $abonnement['agence'] }}</strong>
                                <small>
                                    {{ $formatDate($abonnement['date_fin']) }}
                                    · {{ $formatMoney($abonnement['montant']) }}
                                </small>
                            </div>
                        </a>
                    @endforeach
                </div>
            </article>

            <article class="card">
                <h3>Synthese des paiements</h3>
                <div class="info-list">
                    <div class="info-item">
                        <span>Confirme</span>
                        <strong>{{ $items->where('paiement', 'Paye')->count() }}</strong>
                    </div>
                    <div class="info-item">
                        <span>A confirmer</span>
                        <strong>{{ $items->where('paiement', 'A confirmer')->count() }}</strong>
                    </div>
                    <div class="info-item">
                        <span>Revenu actif</span>
                        <strong>{{ $formatMoney($revenuMensuel) }}</strong>
                    </div>
                </div>
            </article>
        </div>

        {{-- Tableau principal --}}
        <div class="table-workspace">
            <div class="card">

                <div class="table-toolbar">
                    <div>
                        <h3>Abonnements recents</h3>
                    </div>

                    <div class="table-toolbar-actions">
                        <div class="filter-pills">
                            <button class="filter-pill active" type="button" data-filter="tous">Tous</button>
                            <button class="filter-pill" type="button" data-filter="actif">Actifs</button>
                            <button class="filter-pill" type="button" data-filter="attente">En attente</button>
                            <button class="filter-pill" type="button" data-filter="expire">Expires</button>
                        </div>

                        <label class="search-field" for="billing-search">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                      d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z"
                                      clip-rule="evenodd"/>
                            </svg>
                            <input id="billing-search" type="search" placeholder="Rechercher...">
                        </label>
                    </div>
                </div>

                <div class="table-shell u-table-flush">
                    <table class="data-table responsive-table u-table-fit">
                        <thead>
                        <tr>
                            <th>Agence</th>
                            <th>Abonnement</th>
                            <th>Suivi</th>
                            <th>Montant</th>
                            <th>Statut</th>
                            <th class="table-actions-col">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($items as $abonnement)
                            @php $modules = collect($abonnement['modules']); @endphp

                            <tr class="filterable-row"
                                data-filter-status="{{ $statusFilter($abonnement['statut']) }}">

                                {{-- Agence --}}
                                <td>
                                    <div class="entity-cell">
                                        <span class="entity-avatar">
                                            {{ strtoupper(mb_substr($abonnement['agence'], 0, 1)) }}
                                        </span>
                                        <div>
                                            <strong>{{ $abonnement['agence'] }}</strong>
                                            <small>
                                                {{ $abonnement['code_agence'] }}
                                                · {{ $abonnement['created_at'] }}
                                            </small>
                                        </div>
                                    </div>
                                </td>

                                {{-- Plan + modules --}}
                                <td>
                                    <strong>{{ $abonnement['plan'] }}</strong>
                                    <div class="tag-list">
                                        @forelse($modules->take(2) as $module)
                                            <span>{{ $module }}</span>
                                        @empty
                                            <span>Aucun module</span>
                                        @endforelse
                                        @if($modules->count() > 2)
                                            <span>+{{ $modules->count() - 2 }}</span>
                                        @endif
                                    </div>
                                </td>

                                {{-- Suivi --}}
                                <td>
                                    <strong>{{ $formatDate($abonnement['date_debut']) }}</strong>
                                    <small>au {{ $formatDate($abonnement['date_fin']) }}</small>
                                    <span class="status-pill {{ $paymentClass($abonnement['paiement']) }}">
                                        {{ $abonnement['paiement'] }}
                                    </span>
                                </td>

                                {{-- Montant --}}
                                <td><strong>{{ $formatMoney($abonnement['montant']) }}</strong></td>

                                {{-- Statut --}}
                                <td>
                                    <span class="badge {{ $statusClass($abonnement['statut']) }}">
                                        {{ $abonnement['statut'] }}
                                    </span>
                                </td>

                                {{-- Actions --}}
                                <td>
                                    <div class="table-actions">
                                        <a href="{{ route('admin.abonnements.show', $abonnement['code_agence']) }}"
                                           class="action-btn info" title="Voir">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M2.036 12.322a1.012 1.012 0 010-.644C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .638C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            </svg>
                                        </a>
                                        <a href="{{ route('admin.abonnements.edit', $abonnement['code_agence']) }}"
                                           class="action-btn neutral" title="Modifier">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="m16.862 4.487 1.687-1.688a2.25 2.25 0 1 1 3.182 3.182L10.582 17.13a4.5 4.5 0 0 1-1.897 1.13L6 19l.74-2.685a4.5 4.5 0 0 1 1.13-1.897L16.862 4.487z"/>
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

@endsection