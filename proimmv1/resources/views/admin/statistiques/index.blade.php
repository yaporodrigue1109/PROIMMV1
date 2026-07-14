@extends('admin.layouts.app')

@section('title', 'Statistiques & Rapports')
@section('header_title', 'Statistiques & Rapports')

@section('content')
    @php
        $fmt = fn($v) => number_format((float)$v, 0, ',', "\u{00A0}") . "\u{00A0}FCFA";

        $lignesRevenus = [
            ['mois' => 'Jan', 'mois_full' => 'Janvier 2026',  'abo' => 3, 'montant' => 120000, 'pct' => 69],
            ['mois' => 'Fév', 'mois_full' => 'Février 2026',  'abo' => 4, 'montant' => 145000, 'pct' => 83],
            ['mois' => 'Mar', 'mois_full' => 'Mars 2026',     'abo' => 2, 'montant' => 98000,  'pct' => 57],
            ['mois' => 'Avr', 'mois_full' => 'Avril 2026',    'abo' => 5, 'montant' => 162000, 'pct' => 93],
            ['mois' => 'Mai', 'mois_full' => 'Mai 2026',      'abo' => 6, 'montant' => 174000, 'pct' => 100],
        ];
        $totalRevenus = collect($lignesRevenus)->sum('montant');

        $lignesAbonnements = [
            ['agence' => 'Pros Immo Cocody',      'code' => 'AGC-001', 'plan' => 'Standard', 'debut' => '01/04/2026', 'fin' => '30/04/2026', 'statut' => 'Actif',      'montant' => 50000],
            ['agence' => 'Pros Immo Bingerville', 'code' => 'AGC-004', 'plan' => 'Standard', 'debut' => '15/04/2026', 'fin' => '15/05/2026', 'statut' => 'En attente', 'montant' => 72000],
            ['agence' => 'Pros Immo Plateau',     'code' => 'AGC-002', 'plan' => 'Standard', 'debut' => '05/04/2026', 'fin' => '05/05/2026', 'statut' => 'Actif',      'montant' => 52000],
            ['agence' => 'Pros Immo Yopougon',    'code' => 'AGC-003', 'plan' => 'Standard', 'debut' => '01/03/2026', 'fin' => '31/03/2026', 'statut' => 'Expiré',     'montant' => 50000],
            ['agence' => 'Pros Immo Marcory',     'code' => 'AGC-005', 'plan' => 'Standard', 'debut' => '01/04/2026', 'fin' => '30/04/2026', 'statut' => 'Actif',      'montant' => 55000],
        ];

        $lignesAgences = [
            ['agence' => 'Pros Immo Cocody',      'code' => 'AGC-001', 'modules' => 4, 'statut' => 'Actif',      'montant' => 50000, 'pct' => 69],
            ['agence' => 'Pros Immo Bingerville', 'code' => 'AGC-004', 'modules' => 3, 'statut' => 'En attente', 'montant' => 72000, 'pct' => 100],
            ['agence' => 'Pros Immo Plateau',     'code' => 'AGC-002', 'modules' => 1, 'statut' => 'Actif',      'montant' => 52000, 'pct' => 72],
            ['agence' => 'Pros Immo Yopougon',    'code' => 'AGC-003', 'modules' => 0, 'statut' => 'Expiré',     'montant' => 50000, 'pct' => 69],
            ['agence' => 'Pros Immo Marcory',     'code' => 'AGC-005', 'modules' => 2, 'statut' => 'Actif',      'montant' => 55000, 'pct' => 76],
        ];

        $lignesPaiements = [
            ['agence' => 'Pros Immo Cocody',      'code' => 'AGC-001', 'date' => '01 avr. 2026', 'montant' => 50000, 'mode' => 'Mobile Money', 'statut' => 'Payé',       'ref' => 'PAY-0041'],
            ['agence' => 'Pros Immo Plateau',     'code' => 'AGC-002', 'date' => '05 avr. 2026', 'montant' => 52000, 'mode' => 'Virement',     'statut' => 'Payé',       'ref' => 'PAY-0042'],
            ['agence' => 'Pros Immo Bingerville', 'code' => 'AGC-004', 'date' => '15 avr. 2026', 'montant' => 72000, 'mode' => '—',            'statut' => 'En attente', 'ref' => 'PAY-0043'],
            ['agence' => 'Pros Immo Yopougon',    'code' => 'AGC-003', 'date' => '01 mar. 2026', 'montant' => 50000, 'mode' => 'Espèces',      'statut' => 'Payé',       'ref' => 'PAY-0038'],
            ['agence' => 'Pros Immo Marcory',     'code' => 'AGC-005', 'date' => '02 avr. 2026', 'montant' => 55000, 'mode' => 'Mobile Money', 'statut' => 'Payé',       'ref' => 'PAY-0039'],
        ];

        $statusBadge = fn($s) => match($s) {
            'Actif'      => 'badge-success',
            'En attente' => 'badge-warning',
            default      => 'badge-danger',
        };
        $payBadge = fn($s) => match($s) {
            'Payé'       => 'badge-success',
            'En attente' => 'badge-warning',
            default      => 'badge-danger',
        };
        $ini = function(string $n): string {
            $w = explode(' ', $n); $r = '';
            foreach (array_slice($w, -2) as $x) $r .= mb_strtoupper(mb_substr($x, 0, 1));
            return $r;
        };
        $donutPalette = ['#76c300', '#005499', '#2c6393', '#a1a1aa', '#52525b'];
    @endphp

    <div class="rp-page">



        {{-- ── Topbar navigation ────────────────────────────────────── --}}
        <nav class="rp-topbar" aria-label="Sections des rapports">
            <button class="rp-tab is-active" data-tab="revenus" type="button">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/>
                </svg>
                Revenus
            </button>
            <button class="rp-tab" data-tab="abonnements" type="button">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                </svg>
                Abonnements
            </button>
            <button class="rp-tab" data-tab="agences" type="button">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z"/>
                </svg>
                Agences
            </button>
            <button class="rp-tab" data-tab="paiements" type="button">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/>
                </svg>
                Paiements
            </button>

            <div class="rp-topbar-sep"></div>

            <div class="rp-topbar-right">
                <button class="rp-export-btn pdf" id="btn-pdf" type="button">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                    Exporter PDF
                </button>
                <button class="rp-export-btn csv" id="btn-csv" type="button" data-csv-rows='@json($lignesRevenus)'>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                    Exporter CSV
                </button>
            </div>
        </nav>

        {{-- ── Sélecteur de période ─────────────────────────────────── --}}
        <div class="rp-period-bar">
            <span class="rp-period-label">Période</span>
            <div class="rp-period-pills">
                <button class="rp-period-pill" data-period="7j" type="button">7 j</button>
                <button class="rp-period-pill" data-period="30j" type="button">30 j</button>
                <button class="rp-period-pill is-active" data-period="3m" type="button">3 mois</button>
                <button class="rp-period-pill" data-period="ytd" type="button">YTD</button>
                <button class="rp-period-pill" data-period="12m" type="button">12 mois</button>
            </div>
            <div class="rp-period-right">
                Dernière mise à jour : <strong>aujourd'hui à 08:42</strong>
            </div>
        </div>

        {{-- ══════════════════════════════ REVENUS ══════════════════════ --}}
        <div class="rp-panel is-active" id="panel-revenus">

            {{-- KPIs --}}
            <div class="rp-kpi-strip">
                <div class="rp-kpi">
                    <span class="rp-kpi-label">Revenu total</span>
                    <span class="rp-kpi-value">699 000</span>
                    <span class="rp-kpi-delta up">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5l15-15m0 0H8.25m11.25 0v11.25"/></svg>
                    +12 % vs trimestre précédent
                </span>
                </div>
                <div class="rp-kpi">
                    <span class="rp-kpi-label">Ce mois (mai)</span>
                    <span class="rp-kpi-value">174 000</span>
                    <span class="rp-kpi-delta up">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5l15-15m0 0H8.25m11.25 0v11.25"/></svg>
                    +7,4 % vs avril
                </span>
                </div>
                <div class="rp-kpi">
                    <span class="rp-kpi-label">Moyenne mensuelle</span>
                    <span class="rp-kpi-value">139 800</span>
                    <span class="rp-kpi-delta neutral">sur 5 mois</span>
                </div>
                <div class="rp-kpi">
                    <span class="rp-kpi-label">Meilleur mois</span>
                    <span class="rp-kpi-value u-text-lg">Mai 2026</span>
                    <span class="rp-kpi-delta neutral">174 000 FCFA</span>
                </div>
            </div>

            {{-- Chart + Feed --}}
            <div class="rp-two-col">
                <div class="rp-card">
                    <div class="rp-card-head">
                        <h3 class="rp-card-title">Évolution mensuelle des revenus</h3>
                        <span class="rp-card-count">Jan – Mai 2026</span>
                    </div>
                    <div class="rp-chart-wrap">
                        <canvas
                                id="chart-revenus"
                                data-chart="stats-revenue"
                                data-labels='@json(collect($lignesRevenus)->pluck('mois')->toArray())'
                                data-values='@json(collect($lignesRevenus)->pluck('montant')->toArray())'
                        ></canvas>
                    </div>
                </div>

                <div class="rp-card">
                    <div class="rp-card-head">
                        <h3 class="rp-card-title">Activité récente</h3>
                    </div>
                    <div class="rp-feed">
                        @foreach([
                            ['type'=>'up',   'agence'=>'Pros Immo Marcory',     'action'=>'Abonnement renouvelé',  'time'=>'Il y a 2h', 'amount'=>'55 000'],
                            ['type'=>'warn', 'agence'=>'Pros Immo Bingerville', 'action'=>'Paiement en attente',   'time'=>'Il y a 5h', 'amount'=>'72 000'],
                            ['type'=>'up',   'agence'=>'Pros Immo Plateau',     'action'=>'Virement reçu',         'time'=>'Hier',      'amount'=>'52 000'],
                            ['type'=>'up',   'agence'=>'Pros Immo Cocody',      'action'=>'Mobile Money confirmé', 'time'=>'01 avr.',   'amount'=>'50 000'],
                        ] as $ev)
                            @php
                                $ic_bg  = $ev['type'] === 'up' ? 'rgba(118,195,0,.15)' : 'rgba(217,119,6,.15)';
                                $ic_clr = $ev['type'] === 'up' ? '#76c300' : '#d97706';
                            @endphp
                            <div class="rp-feed-item">
                                <div class="rp-feed-icon" data-bg="{{ $ic_bg }}">
                                    @if($ev['type'] === 'up')
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="{{ $ic_clr }}" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5l15-15m0 0H8.25m11.25 0v11.25"/></svg>
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="{{ $ic_clr }}" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                                    @endif
                                </div>
                                <div class="rp-feed-body">
                                    <div class="rp-feed-line"><strong>{{ $ev['agence'] }}</strong> — {{ $ev['action'] }}</div>
                                    <div class="rp-feed-time">{{ $ev['time'] }}</div>
                                </div>
                                <div class="rp-feed-amount">{{ $ev['amount'] }} FCFA</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Tableau détail --}}
            <div class="rp-card">
                <div class="rp-card-head">
                    <h3 class="rp-card-title">Détail par période</h3>
                    <span class="rp-card-count">{{ count($lignesRevenus) }} entrées</span>
                </div>
                <div class="rp-table-wrap">
                    <table class="rp-table">
                        <thead>
                        <tr>
                            <th>Période</th>
                            <th>Abonnements</th>
                            <th class="col-hide-sm">Part du total</th>
                            <th class="col-r">Revenu</th>
                            <th class="col-r col-hide-sm">vs mois préc.</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($lignesRevenus as $i => $l)
                            <tr>
                                <td><strong>{{ $l['mois_full'] }}</strong></td>
                                <td class="col-muted">{{ $l['abo'] }} agence{{ $l['abo'] > 1 ? 's' : '' }}</td>
                                <td class="col-hide-sm">
                                    <div class="rp-bar-cell">
                                        <div class="rp-bar-track"><div class="rp-bar-fill" data-progress="{{ $l['pct'] }}"></div></div>
                                        <span class="rp-bar-label">{{ $l['pct'] }} %</span>
                                    </div>
                                </td>
                                <td class="col-r"><strong>{{ $fmt($l['montant']) }}</strong></td>
                                <td class="col-r col-hide-sm">
                                    @if($i > 0)
                                        @php $prev=$lignesRevenus[$i-1]['montant']; $diff=round(($l['montant']-$prev)/$prev*100,1); @endphp
                                        <span class="rp-delta {{ $diff>=0?'up':'down' }}">
                                        <svg class="{{ $diff < 0 ? 'u-rotate-negative' : 'u-rotate-positive' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5l15-15m0 0H8.25m11.25 0v11.25"/></svg>
                                        {{ $diff>0?'+':'' }}{{ $diff }} %
                                    </span>
                                    @else
                                        <span class="col-muted u-text-xs">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                        <tfoot>
                        <tr>
                            <td colspan="3">Total cumulé</td>
                            <td class="col-r">{{ $fmt($totalRevenus) }}</td>
                            <td class="col-hide-sm"></td>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════ ABONNEMENTS ═════════════════════ --}}
        <div class="rp-panel" id="panel-abonnements">

            <div class="rp-kpi-strip">
                <div class="rp-kpi">
                    <span class="rp-kpi-label">Total</span>
                    <span class="rp-kpi-value">12</span>
                    <span class="rp-kpi-delta neutral">sur 8 agences</span>
                </div>
                <div class="rp-kpi">
                    <span class="rp-kpi-label">Actifs</span>
                    <span class="rp-kpi-value u-text-primary">6</span>
                    <span class="rp-kpi-delta up">50 % du portefeuille</span>
                </div>
                <div class="rp-kpi">
                    <span class="rp-kpi-label">En attente</span>
                    <span class="rp-kpi-value kpi-value-warning">3</span>
                    <span class="rp-kpi-delta neutral">à valider</span>
                </div>
                <div class="rp-kpi">
                    <span class="rp-kpi-label">Expirés</span>
                    <span class="rp-kpi-value kpi-value-danger">3</span>
                    <span class="rp-kpi-delta down">-1 vs mois dernier</span>
                </div>
            </div>

            <div class="rp-card">
                <div class="rp-card-head">
                    <h3 class="rp-card-title">Liste des abonnements</h3>
                    <div class="stat-toolbar-inline">

                        <label class="search-field u-search-compact" for="search-abo">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd"/>
                            </svg>
                            <input type="text" placeholder="Agence, code…" id="search-abo" autocomplete="off"
                                   oninput="filterTable('table-abo', this.value, currentAboFilter)">
                        </label>

                        <select id="filter-abo-native" class="ui-native-select"></select>
                        <div class="ui-dropdown ui-select-dropdown select-width-md" data-select-target="filter-abo-native">
                            <button class="ui-dropdown-toggle dropdown-toggle-sm" type="button">
                                <span>Tous les statuts</span>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/>
                                </svg>
                            </button>
                            <div class="ui-dropdown-menu">
                                <button class="ui-dropdown-item is-selected" type="button" data-value="" onclick="applyAboFilter(this, '')">Tous les statuts</button>
                                <button class="ui-dropdown-item" type="button" data-value="Actif" onclick="applyAboFilter(this, 'Actif')">Actifs</button>
                                <button class="ui-dropdown-item" type="button" data-value="En attente" onclick="applyAboFilter(this, 'En attente')">En attente</button>
                                <button class="ui-dropdown-item" type="button" data-value="Expiré" onclick="applyAboFilter(this, 'Expiré')">Expirés</button>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="rp-table-wrap">
                    <table class="rp-table" id="table-abo">
                        <thead>
                        <tr>
                            <th>Agence</th>
                            <th class="col-hide-sm">Plan</th>
                            <th class="col-hide-sm">Début</th>
                            <th class="col-hide-sm">Échéance</th>
                            <th>Statut</th>
                            <th class="col-r">Montant</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($lignesAbonnements as $l)
                            <tr data-statut="{{ $l['statut'] }}" data-search="{{ strtolower($l['agence'].' '.$l['code']) }}">
                                <td>
                                    <div class="rp-agency">
                                        <span class="entity-avatar">{{ $ini($l['agence']) }}</span>
                                        <div>
                                            <div class="rp-agency-name">{{ $l['agence'] }}</div>
                                            <div class="rp-agency-code">{{ $l['code'] }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="col-muted col-hide-sm">{{ $l['plan'] }}</td>
                                <td class="col-muted col-hide-sm">{{ $l['debut'] }}</td>
                                <td class="col-hide-sm"><strong>{{ $l['fin'] }}</strong></td>
                                <td><span class="badge {{ $statusBadge($l['statut']) }}">{{ $l['statut'] }}</span></td>
                                <td class="col-r"><strong>{{ $fmt($l['montant']) }}</strong></td>
                            </tr>
                        @endforeach
                        </tbody>
                        <tfoot>
                        <tr>
                            <td colspan="5">Total</td>
                            <td class="col-r">{{ $fmt(collect($lignesAbonnements)->sum('montant')) }}</td>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════ AGENCES ═════════════════════ --}}
        <div class="rp-panel" id="panel-agences">

            <div class="rp-kpi-strip">
                <div class="rp-kpi">
                    <span class="rp-kpi-label">Agences totales</span>
                    <span class="rp-kpi-value">8</span>
                    <span class="rp-kpi-delta neutral">dans le réseau</span>
                </div>
                <div class="rp-kpi">
                    <span class="rp-kpi-label">Avec abonnement</span>
                    <span class="rp-kpi-value u-text-primary">6</span>
                    <span class="rp-kpi-delta up">75 % de couverture</span>
                </div>
                <div class="rp-kpi">
                    <span class="rp-kpi-label">Sans abonnement</span>
                    <span class="rp-kpi-value kpi-value-warning">2</span>
                    <span class="rp-kpi-delta neutral">à convertir</span>
                </div>
                <div class="rp-kpi">
                    <span class="rp-kpi-label">Revenu max / agence</span>
                    <span class="rp-kpi-value u-text-lg">72 000</span>
                    <span class="rp-kpi-delta neutral">Bingerville</span>
                </div>
            </div>

            <div class="rp-two-col">
                {{-- Tableau agences --}}
                <div class="rp-card">
                    <div class="rp-card-head">
                        <h3 class="rp-card-title">Performance par agence</h3>
                        <span class="rp-card-count">{{ count($lignesAgences) }} agences</span>
                    </div>
                    <div class="rp-table-wrap">
                        <table class="rp-table">
                            <thead>
                            <tr>
                                <th>Agence</th>
                                <th class="col-hide-sm">Modules</th>
                                <th>Statut</th>
                                <th>Part revenu</th>
                                <th class="col-r">Mensuel</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($lignesAgences as $l)
                                <tr>
                                    <td>
                                        <div class="rp-agency">
                                            <span class="entity-avatar">{{ $ini($l['agence']) }}</span>
                                            <div>
                                                <div class="rp-agency-name">{{ $l['agence'] }}</div>
                                                <div class="rp-agency-code">{{ $l['code'] }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="col-muted col-hide-sm">{{ $l['modules'] }} mod.</td>
                                    <td><span class="badge {{ $statusBadge($l['statut']) }}">{{ $l['statut'] }}</span></td>
                                    <td>
                                        <div class="rp-bar-cell">
                                            <div class="rp-bar-track"><div class="rp-bar-fill" data-progress="{{ $l['pct'] }}"></div></div>
                                            <span class="rp-bar-label">{{ $l['pct'] }} %</span>
                                        </div>
                                    </td>
                                    <td class="col-r"><strong>{{ $fmt($l['montant']) }}</strong></td>
                                </tr>
                            @endforeach
                            </tbody>
                            <tfoot>
                            <tr>
                                <td colspan="4">Total</td>
                                <td class="col-r">{{ $fmt(collect($lignesAgences)->sum('montant')) }}</td>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                {{-- Donut --}}
                <div class="rp-card">
                    <div class="rp-card-head">
                        <h3 class="rp-card-title">Répartition du revenu</h3>
                    </div>
                    <div class="chart-pad-sm">
                        <canvas
                                id="chart-agences"
                                class="u-chart-sm"
                                data-chart="stats-agencies"
                                data-labels='@json(collect($lignesAgences)->map(fn($l) => str_replace('Pros Immo ', '', $l['agence']))->toArray())'
                                data-values='@json(collect($lignesAgences)->pluck('montant')->toArray())'
                        ></canvas>
                    </div>
                    <div class="rp-donut-zone">
                        <div class="rp-legend-list">
                            @foreach($lignesAgences as $idx => $l)
                                <div class="rp-legend-item">
                                    <div class="rp-legend-dot" data-bg="{{ $donutPalette[$idx % count($donutPalette)] }}"></div>
                                    <span class="rp-legend-name">{{ str_replace('Pros Immo ', '', $l['agence']) }}</span>
                                    <span class="rp-legend-val">{{ $fmt($l['montant']) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════ PAIEMENTS ═══════════════════════ --}}
        <div class="rp-panel" id="panel-paiements">

            <div class="rp-kpi-strip">
                <div class="rp-kpi">
                    <span class="rp-kpi-label">Total encaissé</span>
                    <span class="rp-kpi-value">699 000</span>
                    <span class="rp-kpi-delta up">9 transactions confirmées</span>
                </div>
                <div class="rp-kpi">
                    <span class="rp-kpi-label">En attente</span>
                    <span class="rp-kpi-value kpi-value-warning">195 000</span>
                    <span class="rp-kpi-delta neutral">3 paiements</span>
                </div>
                <div class="rp-kpi">
                    <span class="rp-kpi-label">Mobile Money</span>
                    <span class="rp-kpi-value">52 %</span>
                    <span class="rp-kpi-delta neutral">mode principal</span>
                </div>
                <div class="rp-kpi">
                    <span class="rp-kpi-label">Délai moyen</span>
                    <span class="rp-kpi-value">2,3 j</span>
                    <span class="rp-kpi-delta up">-0,4 j vs mois dernier</span>
                </div>
            </div>

            <div class="rp-card">
                <div class="rp-card-head">
                    <h3 class="rp-card-title">Historique des paiements</h3>
                    <div class="stat-toolbar-inline">

                        <label class="search-field u-search-compact" for="search-pay">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd"/>
                            </svg>
                            <input type="text" placeholder="Réf., agence…" id="search-pay" autocomplete="off"
                                   oninput="filterTable('table-pay', this.value, currentPayFilter)">
                        </label>

                        <select id="filter-pay-native" class="ui-native-select"></select>
                        <div class="ui-dropdown ui-select-dropdown select-width-sm" data-select-target="filter-pay-native">
                            <button class="ui-dropdown-toggle dropdown-toggle-sm" type="button">
                                <span>Tous</span>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/>
                                </svg>
                            </button>
                            <div class="ui-dropdown-menu">
                                <button class="ui-dropdown-item is-selected" type="button" data-value="" onclick="applyPayFilter(this, '')">Tous</button>
                                <button class="ui-dropdown-item" type="button" data-value="Payé" onclick="applyPayFilter(this, 'Payé')">Payés</button>
                                <button class="ui-dropdown-item" type="button" data-value="En attente" onclick="applyPayFilter(this, 'En attente')">En attente</button>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="rp-table-wrap">
                    <table class="rp-table" id="table-pay">
                        <thead>
                        <tr>
                            <th>Référence</th>
                            <th>Agence</th>
                            <th class="col-hide-sm">Date</th>
                            <th class="col-hide-sm">Mode</th>
                            <th>Statut</th>
                            <th class="col-r">Montant</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($lignesPaiements as $l)
                            <tr data-statut="{{ $l['statut'] }}" data-search="{{ strtolower($l['ref'].' '.$l['agence'].' '.$l['code']) }}">
                                <td><span class="rp-ref">{{ $l['ref'] }}</span></td>
                                <td>
                                    <div class="rp-agency">
                                        <span class="entity-avatar">{{ $ini($l['agence']) }}</span>
                                        <div>
                                            <div class="rp-agency-name">{{ $l['agence'] }}</div>
                                            <div class="rp-agency-code">{{ $l['code'] }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="col-muted col-hide-sm">{{ $l['date'] }}</td>
                                <td class="col-muted col-hide-sm">{{ $l['mode'] }}</td>
                                <td><span class="badge {{ $payBadge($l['statut']) }}">{{ $l['statut'] }}</span></td>
                                <td class="col-r"><strong>{{ $fmt($l['montant']) }}</strong></td>
                            </tr>
                        @endforeach
                        </tbody>
                        <tfoot>
                        <tr>
                            <td colspan="5">Total encaissé (Payés)</td>
                            <td class="col-r">{{ $fmt(collect($lignesPaiements)->where('statut','Payé')->sum('montant')) }}</td>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

    </div>{{-- /.rp-page --}}
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
@endsection
