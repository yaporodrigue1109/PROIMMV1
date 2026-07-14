@extends('agence.layouts.app')

@section('title', 'Tableau de bord')


@section('content')
    @php

    $user = auth('user')->user();
      $userName  = $user?->name ?? 'Admin';
$userEmail = $user?->email ?? '—';

$userRole = $user?->role
    ?? ($user?->getRoleNames()?->first() ?? 'Utilisateur');
      $taux          = $stats['taux_recouvrement'] ?? 0;
      $totalProps    = ($stats['proprietes_location'] ?? 0) + ($stats['proprietes_vente'] ?? 0);
      $pctLocation   = $totalProps > 0 ? round(($stats['proprietes_location'] ?? 0) / $totalProps * 100) : 0;
      $pctVente      = $totalProps > 0 ? round(($stats['proprietes_vente']    ?? 0) / $totalProps * 100) : 0;
      $variation     = $stats['loyers_variation'] ?? 0;
      $loyerPaye     = $stats['loyers_payes']   ?? 0;
      $loyerRetard   = $stats['loyers_retard']  ?? 0;
      $loyerImpayes  = $stats['loyers_impayes'] ?? 0;
      $loyerTotal    = $loyerPaye + $loyerRetard + $loyerImpayes ?: 1;
      $pctPaye       = round($loyerPaye    / $loyerTotal * 100);
      $pctRetard     = round($loyerRetard  / $loyerTotal * 100);
      $pctImpaye     = round($loyerImpayes / $loyerTotal * 100);
      $revTotal      = ($stats['loyers_montant'] ?? 0) + ($stats['ventes_montant'] ?? 0);
      $depTotal      = $stats['depenses_montant']     ?? 0;
      $reversTotal   = $stats['reversements_montant'] ?? 0;
      $soldeNet      = $stats['caisse_solde']         ?? ($revTotal - $depTotal - $reversTotal);
      $alertes = collect([
          ['type'=>'danger',  'label'=>'Loyers impayés',        'value'=>$loyerImpayes,                          'unit'=>'en retard'],
          ['type'=>'warning', 'label'=>'Baux expirant bientôt', 'value'=>$stats['baux_expirants']       ?? 0,    'unit'=>'dans 30 j'],
          ['type'=>'info',    'label'=>'Maintenances ouvertes', 'value'=>$stats['maintenances_ouvertes'] ?? 0,    'unit'=>'en cours'],
      ])->filter(fn($a) => $a['value'] > 0);


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
                'label'  => 'Propriétés actives',
                'valeur' => '12',
                'class'  => '',
                'trend'  => '+2',
                'dir'    => 'up',
                'sub'    => 'ce mois',
            ],
            [
                'label'  => 'Loyers en attente',
                'valeur' => '3',
                'class'  => 'is-warning',
                'trend'  => '180 000 FCFA',
                'dir'    => 'flat',
                'sub'    => 'à confirmer',
            ],
            [
                'label'  => 'Maintenances urgentes',
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
                'titre'   => 'Loyer impayé — Villa Palmeraie',
                'detail'  => 'En retard depuis le 31/03/2026 · 150 000 FCFA',
                'route'   => 'agence.dashboard',
                'param'   => 'LOC-001',
            ],
            [
                'type'    => 'warning',
                'titre'   => 'Maintenance en cours — Immeuble Riviera',
                'detail'  => 'Devis à approuver · 250 000 FCFA',
                'route'   => 'agence.dashboard',
                'param'   => 'MT-001',
            ],
            [
                'type'    => 'info',
                'titre'   => 'Bail expirant dans 5 jours',
                'detail'  => 'Renouvellement à prévoir · Appartement Cocody',
                'route'   => 'agence.dashboard',
                'param'   => 'BAIL-001',
            ],
        ];

        $activiteRecente = [
            ['bien' => 'Villa Palmeraie',      'code' => 'VIL-001', 'action' => 'Loyer encaissé',     'statut' => 'Payé',       'date' => "Aujourd'hui, 09:40"],
            ['bien' => 'Immeuble Riviera',     'code' => 'IMM-002', 'action' => 'Maintenance demandée','statut' => 'En cours',    'date' => 'Hier, 17:15'],
            ['bien' => 'Appartement Cocody',   'code' => 'APP-003', 'action' => 'Bail renouvelé',      'statut' => 'Actif',      'date' => '27/04/2026'],
            ['bien' => 'Studio Plateau',       'code' => 'STU-004', 'action' => 'Contrat de vente signé','statut' => 'Vendu',      'date' => '25/04/2026'],
        ];

        $prochainesEcheances = [
            ['bien' => 'Villa Palmeraie',      'code' => 'VIL-001', 'date_fin' => '30/04/2026', 'montant' => 150000],
            ['bien' => 'Immeuble Riviera',     'code' => 'IMM-002', 'date_fin' => '05/05/2026', 'montant' => 250000],
            ['bien' => 'Appartement Cocody',   'code' => 'APP-003', 'date_fin' => '15/05/2026', 'montant' => 120000],
        ];

        $badgeClass = fn ($s) => match ($s) {
            'Payé', 'Actif', 'Vendu'      => 'badge-success',
            'En cours' => 'badge-warning',
            default      => 'badge-danger',
        };

        $formatMoney = fn ($v) => number_format((float) $v, 0, ',', ' ') . ' FCFA';
    @endphp

    <section class="dashboard-page">

        {{-- En-tête --}}
        <div class="dashboard-header">
            <div>
                <p class="dashboard-header-greeting">Tableau de bord</p>

                <h2>Bonjour, {{ $userName }}</h2>
            </div>
            <div class="dashboard-header-actions">
                <a href="{{ route('agence.proprietes.create') }}" class="btn btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nouveau bien
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
                    <a href="#" class="dashboard-view-all">
                        Voir les stats →
                    </a>
                </div>
                <div class="card-body">
                    <div class="dashboard-chart-wrap">
                        <canvas
                                id="chart-revenus"
                                data-chart="agency-revenue"
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
                    <a href="#" class="dashboard-view-all">
                        Voir tout
                    </a>
                </div>
                <table class="dashboard-table">
                    <thead>
                    <tr>
                        <th>Bien</th>
                        <th>Action</th>
                        <th>Statut</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($activiteRecente as $ligne)
                        <tr>
                            <td>
                                <strong class="u-block">{{ $ligne['bien'] }}</strong>
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
                    <a href="#" class="dashboard-view-all">
                        Voir tout
                    </a>
                </div>
                <div class="card-body">
                    <div class="dashboard-deadline-list">
                        @foreach($prochainesEcheances as $echeance)
                            <a href="#"
                               class="dashboard-deadline-item">
                                <span class="dashboard-deadline-dot"></span>
                                <div class="dashboard-deadline-copy">
                                    <strong>{{ $echeance['bien'] }}</strong>
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