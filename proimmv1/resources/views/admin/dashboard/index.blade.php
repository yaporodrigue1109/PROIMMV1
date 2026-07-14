@extends('admin.layouts.app')

@section('title', 'Tableau de bord')
@section('header_title', 'Tableau de bord')

@section('content')
    @php
        /* ── Données (à remplacer par des requêtes Eloquent) ─────────── */

        $user = auth()->user();

        $kpis = [
            [
                'label'  => 'Revenu du mois',
                'valeur' => '174 000 FCFA',
                'class'  => 'is-success',
                'trend'  => '+12 %',
                'dir'    => 'up',
                'sub'    => 'vs mois précédent',
            ],
            [
                'label'  => 'Abonnements actifs',
                'valeur' => '6',
                'class'  => '',
                'trend'  => '+2',
                'dir'    => 'up',
                'sub'    => 'ce mois',
            ],
            [
                'label'  => 'Paiements en attente',
                'valeur' => '3',
                'class'  => 'is-warning',
                'trend'  => '72 000 FCFA',
                'dir'    => 'flat',
                'sub'    => 'à confirmer',
            ],
            [
                'label'  => 'Expirés à relancer',
                'valeur' => '2',
                'class'  => 'is-danger',
                'trend'  => 'Urgent',
                'dir'    => 'down',
                'sub'    => '',
            ],
        ];

        $revenusParMois = [
            'labels' => ['Jan', 'Fév', 'Mar', 'Avr', 'Mai'],
            'data'   => [120000, 145000, 98000, 162000, 174000],
        ];

        $alertes = [
            [
                'type'    => 'danger',
                'titre'   => 'Pros Immo Yopougon — abonnement expiré',
                'detail'  => 'Expiré depuis le 31/03/2026 · 50 000 FCFA',
                'route'   => 'admin.abonnements.show',
                'param'   => 'AGC-003',
            ],
            [
                'type'    => 'warning',
                'titre'   => 'Pros Immo Bingerville — paiement à confirmer',
                'detail'  => 'Échéance le 15/05/2026 · 72 000 FCFA',
                'route'   => 'admin.abonnements.show',
                'param'   => 'AGC-004',
            ],
            [
                'type'    => 'info',
                'titre'   => 'Pros Immo Cocody — renouvellement dans 5 jours',
                'detail'  => 'Échéance le 30/04/2026 · 50 000 FCFA',
                'route'   => 'admin.abonnements.show',
                'param'   => 'AGC-001',
            ],
        ];

        $activiteRecente = [
            ['agence' => 'Pros Immo Cocody',      'code' => 'AGC-001', 'action' => 'Abonnement créé',    'statut' => 'Actif',      'date' => "Aujourd'hui, 09:40"],
            ['agence' => 'Pros Immo Bingerville', 'code' => 'AGC-004', 'action' => 'Paiement en attente','statut' => 'En attente', 'date' => 'Hier, 17:15'],
            ['agence' => 'Pros Immo Plateau',     'code' => 'AGC-002', 'action' => 'Abonnement renouvelé','statut' => 'Actif',     'date' => '27/04/2026'],
            ['agence' => 'Pros Immo Yopougon',    'code' => 'AGC-003', 'action' => 'Abonnement expiré',  'statut' => 'Expiré',     'date' => '31/03/2026'],
        ];

        $prochainesEcheances = [
            ['agence' => 'Pros Immo Cocody',      'code' => 'AGC-001', 'date_fin' => '30/04/2026', 'montant' => 50000],
            ['agence' => 'Pros Immo Plateau',     'code' => 'AGC-002', 'date_fin' => '05/05/2026', 'montant' => 52000],
            ['agence' => 'Pros Immo Bingerville', 'code' => 'AGC-004', 'date_fin' => '15/05/2026', 'montant' => 72000],
        ];

        $badgeClass = fn ($s) => match ($s) {
            'Actif'      => 'badge-success',
            'En attente' => 'badge-warning',
            default      => 'badge-danger',
        };

        $formatMoney = fn ($v) => number_format((float) $v, 0, ',', ' ') . ' FCFA';
    @endphp

    <section class="dashboard-page">

        {{-- En-tête --}}
        <div class="dashboard-header">
            <div>
                <p class="dashboard-header-greeting">Tableau de bord</p>
                <h2>Bonjour, {{ $user->name ?? 'Administrateur' }}</h2>
                <p>Voici un résumé de l'activité du mois en cours.</p>
            </div>
            <div class="dashboard-header-actions">
                <a href="{{ route('admin.abonnements.create') }}" class="btn btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nouvel abonnement
                </a>
            </div>
        </div>

        {{-- KPIs --}}
        <div class="dashboard-kpi-grid">
            @foreach($kpis as $kpi)
                <article class="dashboard-kpi-card">
                    <span class="dashboard-kpi-label">{{ $kpi['label'] }}</span>
                    <strong class="dashboard-kpi-value {{ $kpi['class'] }}">{{ $kpi['valeur'] }}</strong>
                    <span class="dashboard-kpi-trend {{ $kpi['dir'] }}">
                        @if($kpi['dir'] === 'up')
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                 stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/>
                            </svg>
                        @elseif($kpi['dir'] === 'down')
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                 stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                            </svg>
                        @endif
                        {{ $kpi['trend'] }}{{ $kpi['sub'] ? ' · ' . $kpi['sub'] : '' }}
                    </span>
                </article>
            @endforeach
        </div>

        {{-- Grille principale : graphique + alertes --}}
        <div class="dashboard-main-grid">

            {{-- Graphique revenus --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Évolution des revenus</h3>
                    <a href="{{ route('admin.statistiques.index') }}" class="dashboard-view-all">
                        Voir les stats →
                    </a>
                </div>
                <div class="card-body">
                    <div class="dashboard-chart-wrap">
                        <canvas
                                id="chart-revenus"
                                data-chart="admin-revenue"
                                data-labels='@json($revenusParMois['labels'])'
                                data-values='@json($revenusParMois['data'])'
                        ></canvas>
                    </div>
                </div>
            </div>

            {{-- Alertes --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Alertes</h3>
                    <span class="u-text-muted u-text-sm">
                        {{ count($alertes) }} en cours
                    </span>
                </div>
                <div class="card-body">
                    <div class="dashboard-alert-list">
                        @foreach($alertes as $alerte)
                            <a href="{{ route($alerte['route'], $alerte['param']) }}"
                               class="dashboard-alert-item">
                                <span class="dashboard-alert-dot {{ $alerte['type'] }}"></span>
                                <div class="dashboard-alert-copy">
                                    <strong>{{ $alerte['titre'] }}</strong>
                                    <span>{{ $alerte['detail'] }}</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Grille secondaire : activité + échéances --}}
        <div class="dashboard-secondary-grid">

            {{-- Activité récente --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Activité récente</h3>
                    <a href="{{ route('admin.abonnements.index') }}" class="dashboard-view-all">
                        Voir tout
                    </a>
                </div>
                <table class="dashboard-table">
                    <thead>
                    <tr>
                        <th>Agence</th>
                        <th>Action</th>
                        <th>Statut</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($activiteRecente as $ligne)
                        <tr>
                            <td>
                                <strong class="u-block">{{ $ligne['agence'] }}</strong>
                                <span class="u-text-muted u-text-xs">
                                        {{ $ligne['date'] }}
                                    </span>
                            </td>
                            <td class="u-text-muted">{{ $ligne['action'] }}</td>
                            <td>
                                    <span class="badge {{ $badgeClass($ligne['statut']) }}">
                                        {{ $ligne['statut'] }}
                                    </span>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Prochaines échéances --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Prochaines échéances</h3>
                    <a href="{{ route('admin.abonnements.index') }}" class="dashboard-view-all">
                        Voir tout
                    </a>
                </div>
                <div class="card-body">
                    <div class="dashboard-deadline-list">
                        @foreach($prochainesEcheances as $echeance)
                            <a href="{{ route('admin.abonnements.show', $echeance['code']) }}"
                               class="dashboard-deadline-item">
                                <span class="dashboard-deadline-dot"></span>
                                <div class="dashboard-deadline-copy">
                                    <strong>{{ $echeance['agence'] }}</strong>
                                    <span>{{ $echeance['date_fin'] }}</span>
                                </div>
                                <span class="dashboard-deadline-amount">
                                    {{ $formatMoney($echeance['montant']) }}
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

        </div>
    </section>
@endsection



@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
@endsection
