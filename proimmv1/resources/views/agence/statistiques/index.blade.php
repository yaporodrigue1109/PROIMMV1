@extends('agence.layouts.app')

@section('title', 'Statistiques')
@section('header_title', 'Statistiques')

@section('content')
    @php
        $fmt = fn ($value) => number_format((float) $value, 0, ',', ' ') . ' FCFA';
        $percent = fn ($value) => number_format((float) $value, 0, ',', ' ') . ' %';

        $agencyName = auth('user')->user()?->name ?? 'Mon Agence';
        $periodLabel = now()->translatedFormat('F Y');

        $summaryCards = [
            [
                'label' => 'Propriétés',
                'value' => $stats['proprietes_total'] ?? 0,
                'sub' => $percent($stats['allocation_rate'] ?? 0) . ' en location',
            ],
            [
                'label' => 'Locataires actifs',
                'value' => $stats['locataires_actifs'] ?? 0,
                'sub' => ($stats['locataires_ce_mois'] ?? 0) . ' ajoutés ce mois',
            ],
            [
                'label' => 'Portes occupées',
                'value' => $stats['portes_occupees'] ?? 0,
                'sub' => $percent($stats['occupation_rate'] ?? 0) . ' d’occupation',
            ],
            [
                'label' => 'Maintenances en cours',
                'value' => $stats['maintenances_en_cours'] ?? 0,
                'sub' => $percent($stats['maintenance_close_rate'] ?? 0) . ' terminées',
            ],
            [
                'label' => 'Encaissements validés',
                'value' => $stats['transactions_validees'] ?? 0,
                'sub' => $fmt($stats['total_encaisse'] ?? 0),
            ],
            [
                'label' => 'Coût maintenance du mois',
                'value' => $fmt($stats['cout_maintenance_mois'] ?? 0),
                'sub' => $fmt($stats['revenu_mois'] ?? 0) . ' de revenus validés',
            ],
        ];

        $monthlyRows = collect($monthlyLabels)->map(function ($label, $index) use ($revenueSeries, $maintenanceMonthSeries) {
            return [
                'label' => $label,
                'revenue' => (float) ($revenueSeries[$index] ?? 0),
                'maintenance' => (float) ($maintenanceMonthSeries[$index] ?? 0),
            ];
        })->all();

        $maintenanceStatusPalette = [
            'En attente' => '#f59e0b',
            'En cours' => '#3b82f6',
            'Terminée' => '#22c55e',
            'Annulée' => '#ef4444',
            'Validée' => '#10b981',
            'Échouée' => '#f43f5e',
        ];

        $recentActivities = collect();

        foreach ($recentTransactions as $transaction) {
            $recentActivities->push([
                'type' => 'transaction',
                'title' => $transaction->reference ?: 'Transaction',
                'subtitle' => trim(($transaction->type_operation ?? 'Opération') . ' · ' . optional($transaction->created_at)->format('d/m/Y H:i')),
                'amount' => $fmt($transaction->montant_ttc ?? 0),
                'tone' => match ($transaction->statut ?? '') {
                    'validee' => 'success',
                    'en_attente' => 'warning',
                    default => 'danger',
                },
                'timestamp' => $transaction->created_at?->getTimestamp() ?? 0,
            ]);
        }

        foreach ($recentMaintenances as $maintenance) {
            $recentActivities->push([
                'type' => 'maintenance',
                'title' => $maintenance->titre ?: 'Maintenance',
                'subtitle' => trim(($maintenance->propriete?->reference ?? 'Bien non précisé') . ' · ' . optional($maintenance->created_at)->format('d/m/Y H:i')),
                'amount' => $fmt($maintenance->montant_global ?? 0),
                'tone' => match ($maintenance->statut ?? '') {
                    'termine' => 'success',
                    'en_cours' => 'warning',
                    'en_attente' => 'info',
                    default => 'danger',
                },
                'timestamp' => $maintenance->created_at?->getTimestamp() ?? 0,
            ]);
        }

        $recentActivities = $recentActivities
            ->sortByDesc('timestamp')
            ->take(6)
            ->values();

        $propertySummary = [
            ['label' => 'Biens total', 'value' => $stats['proprietes_total'] ?? 0],
            ['label' => 'Biens en location', 'value' => $stats['proprietes_allocation'] ?? 0],
            ['label' => 'Biens libres', 'value' => $stats['proprietes_non_allocation'] ?? 0],
            ['label' => 'Nouveaux biens', 'value' => $stats['proprietes_ce_mois'] ?? 0],
            ['label' => 'Propriétaires actifs', 'value' => ($stats['proprietaires_actifs'] ?? 0) . ' / ' . ($stats['proprietaires_total'] ?? 0)],
        ];

        $maintenanceSummary = [
            ['label' => 'Total interventions', 'value' => $stats['maintenances_total'] ?? 0],
            ['label' => 'En attente', 'value' => $stats['maintenances_en_attente'] ?? 0],
            ['label' => 'En cours', 'value' => $stats['maintenances_en_cours'] ?? 0],
            ['label' => 'Terminées', 'value' => $stats['maintenances_terminees'] ?? 0],
            ['label' => 'Annulées', 'value' => $stats['maintenances_annulees'] ?? 0],
        ];

        $financeSummary = [
            ['label' => 'Total encaissé', 'value' => $fmt($stats['total_encaisse'] ?? 0)],
            ['label' => 'Transactions validées', 'value' => $stats['transactions_validees'] ?? 0],
            ['label' => 'Transactions en attente', 'value' => $stats['transactions_en_attente'] ?? 0],
            ['label' => 'Transactions échouées', 'value' => $stats['transactions_echouees'] ?? 0],
            ['label' => 'Taux de clôture maintenance', 'value' => $percent($stats['maintenance_close_rate'] ?? 0)],
        ];
    @endphp

    <div class="rp-page">
        <div class="page-header">
            <div class="page-header-copy">
                <div class="page-heading">
                    <div>
                        <h2>Statistiques & rapports</h2>
                        <p class="text-muted mb-0">
                            Vue d’ensemble de {{ $agencyName }} sur les propriétés, les locataires, la maintenance et la caisse.
                        </p>
                    </div>
                </div>
            </div>

            <div class="page-actions">
                <a href="{{ route('agence.proprietes.index') }}" class="btn btn-outline">Propriétés</a>
                <a href="{{ route('agence.maintenance.index') }}" class="btn btn-outline">Maintenance</a>
                <a href="{{ route('agence.caisse.index') }}" class="btn btn-primary">Caisse</a>
            </div>
        </div>

        <nav class="rp-topbar" aria-label="Sections des rapports">
            <button class="rp-tab is-active" data-tab="vue-ensemble" type="button">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zm6.75-4.5c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zm6.75-4.5c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                </svg>
                Vue d’ensemble
            </button>
            <button class="rp-tab" data-tab="proprietes" type="button">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z" />
                </svg>
                Propriétés
            </button>
            <button class="rp-tab" data-tab="maintenance" type="button">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l5.654-4.654m5.65-4.622 2.617-1.96a1.03 1.03 0 011.397.136l.355.355a1.03 1.03 0 01.136 1.397l-1.96 2.617m-5.549-.641 3.054-3.054a1.03 1.03 0 000-1.458l-.709-.709a1.03 1.03 0 00-1.458 0L9.88 8.908" />
                </svg>
                Maintenance
            </button>
            <button class="rp-tab" data-tab="finance" type="button">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0Z" />
                </svg>
                Finance
            </button>
        </nav>

        <div class="rp-period-bar">
            <span class="rp-period-label">Période</span>
            <div class="rp-period-pills">
                <button class="rp-period-pill" type="button">7 j</button>
                <button class="rp-period-pill" type="button">30 j</button>
                <button class="rp-period-pill is-active" type="button">3 mois</button>
                <button class="rp-period-pill" type="button">YTD</button>
                <button class="rp-period-pill" type="button">12 mois</button>
            </div>
            <div class="rp-period-right">
                Dernière mise à jour : <strong>{{ now()->format('d/m/Y à H:i') }}</strong>
            </div>
        </div>

        <div class="rp-panel is-active" id="panel-vue-ensemble">
            <div class="rp-kpi-strip">
                @foreach($summaryCards as $card)
                    <div class="rp-kpi">
                        <span class="rp-kpi-label">{{ $card['label'] }}</span>
                        <span class="rp-kpi-value">{{ $card['value'] }}</span>
                        <span class="rp-kpi-delta neutral">{{ $card['sub'] }}</span>
                    </div>
                @endforeach
            </div>

            <div class="rp-two-col">
                <div class="rp-card">
                    <div class="rp-card-head">
                        <h3 class="rp-card-title">Évolution mensuelle des revenus</h3>
                        <span class="rp-card-count">{{ $periodLabel }}</span>
                    </div>
                    <div class="rp-chart-wrap">
                        <canvas
                                id="chart-agence-finance"
                                data-labels='@json($monthlyLabels)'
                                data-revenue='@json($revenueSeries)'
                                data-maintenance='@json($maintenanceMonthSeries)'
                        ></canvas>
                    </div>
                </div>

                <div class="rp-card">
                    <div class="rp-card-head">
                        <h3 class="rp-card-title">Activité récente</h3>
                        <span class="rp-card-count">{{ $recentActivities->count() }} éléments</span>
                    </div>
                    <div class="rp-feed">
                        @forelse($recentActivities as $activity)
                            @php
                                $bg = match ($activity['tone']) {
                                    'success' => 'rgba(118, 195, 0, .14)',
                                    'warning' => 'rgba(217, 119, 6, .14)',
                                    'info' => 'rgba(59, 130, 246, .14)',
                                    default => 'rgba(239, 68, 68, .14)',
                                };

                                $stroke = match ($activity['tone']) {
                                    'success' => '#76c300',
                                    'warning' => '#d97706',
                                    'info' => '#3b82f6',
                                    default => '#ef4444',
                                };
                            @endphp
                            <div class="rp-feed-item">
                                <div class="rp-feed-icon" data-bg="{{ $bg }}">
                                    @if($activity['type'] === 'transaction')
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="{{ $stroke }}" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                        </svg>
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="{{ $stroke }}" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                        </svg>
                                    @endif
                                </div>
                                <div class="rp-feed-body">
                                    <div class="rp-feed-line"><strong>{{ $activity['title'] }}</strong> — {{ $activity['subtitle'] }}</div>
                                    <div class="rp-feed-time">
                                        {{ $activity['type'] === 'transaction' ? 'Transaction' : 'Maintenance' }}
                                    </div>
                                </div>
                                <div class="rp-feed-amount">{{ $activity['amount'] }}</div>
                            </div>
                        @empty
                            <div class="u-text-muted">Aucune activité récente.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="rp-panel" id="panel-proprietes">
            <div class="rp-kpi-strip">
                @foreach($propertySummary as $card)
                    <div class="rp-kpi">
                        <span class="rp-kpi-label">{{ $card['label'] }}</span>
                        <span class="rp-kpi-value">{{ $card['value'] }}</span>
                        <span class="rp-kpi-delta neutral">Synthèse parc immobilier</span>
                    </div>
                @endforeach
            </div>

            <div class="rp-two-col">
                <div class="rp-card">
                    <div class="rp-card-head">
                        <h3 class="rp-card-title">Répartition du parc</h3>
                        <span class="rp-card-count">{{ $stats['proprietes_total'] ?? 0 }} biens</span>
                    </div>
                    <div class="rp-table-wrap">
                        <table class="rp-table">
                            <thead>
                            <tr>
                                <th>Indicateur</th>
                                <th class="col-r">Valeur</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($propertySummary as $row)
                                <tr>
                                    <td><strong>{{ $row['label'] }}</strong></td>
                                    <td class="col-r">{{ $row['value'] }}</td>
                                </tr>
                            @endforeach
                            <tr>
                                <td><strong>Batiments</strong></td>
                                <td class="col-r">{{ $stats['batiments_total'] ?? 0 }}</td>
                            </tr>
                            <tr>
                                <td><strong>Portes libres</strong></td>
                                <td class="col-r">{{ $stats['portes_libres'] ?? 0 }}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="rp-card">
                    <div class="rp-card-head">
                        <h3 class="rp-card-title">Biens les plus sollicités</h3>
                        <span class="rp-card-count">{{ $topProperties->count() }} lignes</span>
                    </div>
                    <div class="rp-table-wrap">
                        <table class="rp-table">
                            <thead>
                            <tr>
                                <th>Bien</th>
                                <th>Interventions</th>
                                <th class="col-r">Budget</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($topProperties as $item)
                                <tr>
                                    <td>
                                        <strong>{{ $item->propriete?->reference ?? 'Bien #' . ($item->propriete_id ?? '—') }}</strong>
                                        <span class="u-block u-text-muted u-text-xs">
                                            {{ $item->propriete?->description ?? '—' }}
                                        </span>
                                    </td>
                                    <td>{{ $item->total_maintenances }}</td>
                                    <td class="col-r">{{ $fmt($item->montant_total) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="u-text-muted">Aucune donnée disponible.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="rp-panel" id="panel-maintenance">
            <div class="rp-kpi-strip">
                @foreach($maintenanceSummary as $card)
                    <div class="rp-kpi">
                        <span class="rp-kpi-label">{{ $card['label'] }}</span>
                        <span class="rp-kpi-value">{{ $card['value'] }}</span>
                        <span class="rp-kpi-delta neutral">Suivi maintenance</span>
                    </div>
                @endforeach
                <div class="rp-kpi">
                    <span class="rp-kpi-label">Taux de clôture</span>
                    <span class="rp-kpi-value">{{ $percent($stats['maintenance_close_rate'] ?? 0) }}</span>
                    <span class="rp-kpi-delta neutral">Interventions terminées</span>
                </div>
            </div>

            <div class="rp-two-col">
                <div class="rp-card">
                    <div class="rp-card-head">
                        <h3 class="rp-card-title">Répartition par statut</h3>
                        <span class="rp-card-count">{{ $stats['maintenances_total'] ?? 0 }} interventions</span>
                    </div>
                    <div class="rp-chart-wrap">
                        <canvas
                                id="chart-maintenance-status"
                                data-labels='@json(collect($maintenanceSeries)->pluck("label")->all())'
                                data-values='@json(collect($maintenanceSeries)->pluck("value")->all())'
                                data-colors='@json(collect($maintenanceSeries)->pluck("label")->map(fn ($label) => $maintenanceStatusPalette[$label] ?? "#64748b")->all())'
                        ></canvas>
                    </div>
                </div>

                <div class="rp-card">
                    <div class="rp-card-head">
                        <h3 class="rp-card-title">Types d’intervention les plus fréquents</h3>
                        <span class="rp-card-count">{{ $topMaintenanceTypes->count() }} types</span>
                    </div>
                    <div class="rp-table-wrap">
                        <table class="rp-table">
                            <thead>
                            <tr>
                                <th>Type</th>
                                <th>Catégorie</th>
                                <th>Volume</th>
                                <th class="col-r">Montant</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($topMaintenanceTypes as $type)
                                <tr>
                                    <td><strong>{{ $type->name }}</strong></td>
                                    <td class="u-text-muted">{{ $type->categorie ?: '—' }}</td>
                                    <td>{{ $type->total_interventions }}</td>
                                    <td class="col-r">{{ $fmt($type->montant_total) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="u-text-muted">Aucune intervention enregistrée.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="rp-card">
                <div class="rp-card-head">
                    <h3 class="rp-card-title">Dernières maintenances</h3>
                    <span class="rp-card-count">{{ $recentMaintenances->count() }} interventions</span>
                </div>
                <div class="rp-table-wrap">
                    <table class="rp-table">
                        <thead>
                        <tr>
                            <th>Titre</th>
                            <th>Bien</th>
                            <th>Statut</th>
                            <th class="col-r">Montant</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($recentMaintenances as $maintenance)
                            <tr>
                                <td>
                                    <strong>{{ $maintenance->titre ?? '—' }}</strong>
                                    <span class="u-block u-text-muted u-text-xs">
                                        {{ optional($maintenance->created_at)->format('d/m/Y H:i') ?? '—' }}
                                    </span>
                                </td>
                                <td class="u-text-muted">
                                    {{ $maintenance->propriete?->reference ?? '—' }}
                                </td>
                                <td>
                                    <span class="badge {{ match ($maintenance->statut ?? '') {
                                        'en_attente' => 'badge-warning',
                                        'en_cours' => 'badge-info',
                                        'termine' => 'badge-success',
                                        'annule' => 'badge-danger',
                                        default => 'badge-info',
                                    } }}">
                                        {{ match ($maintenance->statut ?? '') {
                                            'en_attente' => 'En attente',
                                            'en_cours' => 'En cours',
                                            'termine' => 'Terminée',
                                            'annule' => 'Annulée',
                                            default => ucfirst($maintenance->statut ?? '—'),
                                        } }}
                                    </span>
                                </td>
                                <td class="col-r">{{ $fmt($maintenance->montant_global ?? 0) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="u-text-muted">Aucune maintenance enregistrée.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="rp-panel" id="panel-finance">
            <div class="rp-kpi-strip">
                @foreach($financeSummary as $card)
                    <div class="rp-kpi">
                        <span class="rp-kpi-label">{{ $card['label'] }}</span>
                        <span class="rp-kpi-value">{{ $card['value'] }}</span>
                        <span class="rp-kpi-delta neutral">Flux financier</span>
                    </div>
                @endforeach
            </div>

            <div class="rp-two-col">
                <div class="rp-card">
                    <div class="rp-card-head">
                        <h3 class="rp-card-title">Flux mensuel</h3>
                        <span class="rp-card-count">{{ $periodLabel }}</span>
                    </div>
                    <div class="rp-table-wrap">
                        <table class="rp-table">
                            <thead>
                            <tr>
                                <th>Mois</th>
                                <th class="col-r">Revenus validés</th>
                                <th class="col-r">Maintenance</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($monthlyRows as $row)
                                <tr>
                                    <td><strong>{{ $row['label'] }}</strong></td>
                                    <td class="col-r">{{ $fmt($row['revenue']) }}</td>
                                    <td class="col-r">{{ $fmt($row['maintenance']) }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="rp-card">
                    <div class="rp-card-head">
                        <h3 class="rp-card-title">Transactions récentes</h3>
                        <span class="rp-card-count">{{ $recentTransactions->count() }} lignes</span>
                    </div>
                    <div class="rp-feed">
                        @forelse($recentTransactions as $transaction)
                            @php
                                $tone = match ($transaction->statut ?? '') {
                                    'validee' => 'success',
                                    'en_attente' => 'warning',
                                    default => 'danger',
                                };

                                $bg = match ($tone) {
                                    'success' => 'rgba(118, 195, 0, .14)',
                                    'warning' => 'rgba(217, 119, 6, .14)',
                                    default => 'rgba(239, 68, 68, .14)',
                                };

                                $stroke = match ($tone) {
                                    'success' => '#76c300',
                                    'warning' => '#d97706',
                                    default => '#ef4444',
                                };
                            @endphp
                            <div class="rp-feed-item">
                                <div class="rp-feed-icon" data-bg="{{ $bg }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="{{ $stroke }}" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                    </svg>
                                </div>
                                <div class="rp-feed-body">
                                    <div class="rp-feed-line">
                                        <strong>{{ $transaction->reference ?? 'Transaction' }}</strong> — {{ $transaction->type_operation ?? 'Opération' }}
                                    </div>
                                    <div class="rp-feed-time">
                                        {{ optional($transaction->created_at)->format('d/m/Y H:i') ?? '—' }}
                                    </div>
                                </div>
                                <div class="rp-feed-amount">{{ $fmt($transaction->montant_ttc ?? 0) }}</div>
                            </div>
                        @empty
                            <div class="u-text-muted">Aucune transaction enregistrée.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
    <script>
        (function () {
            const tabs = document.querySelectorAll('.rp-tab');
            const panels = document.querySelectorAll('.rp-panel');

            tabs.forEach((tab) => {
                tab.addEventListener('click', function () {
                    tabs.forEach((item) => item.classList.remove('is-active'));
                    panels.forEach((panel) => panel.classList.remove('is-active'));

                    this.classList.add('is-active');
                    document.getElementById('panel-' + this.dataset.tab)?.classList.add('is-active');
                });
            });

            const activeTab = new URLSearchParams(window.location.search).get('tab');
            if (activeTab) {
                document.querySelector(`[data-tab="${activeTab}"]`)?.click();
            }

            const financeCanvas = document.getElementById('chart-agence-finance');
            if (financeCanvas && window.Chart) {
                const labels = JSON.parse(financeCanvas.dataset.labels || '[]');
                const revenue = JSON.parse(financeCanvas.dataset.revenue || '[]');
                const maintenance = JSON.parse(financeCanvas.dataset.maintenance || '[]');

                new Chart(financeCanvas, {
                    type: 'line',
                    data: {
                        labels,
                        datasets: [
                            {
                                label: 'Revenus validés',
                                data: revenue,
                                borderColor: '#76c300',
                                backgroundColor: 'rgba(118, 195, 0, .14)',
                                tension: .35,
                                fill: true,
                            },
                            {
                                label: 'Maintenance',
                                data: maintenance,
                                borderColor: '#005499',
                                backgroundColor: 'rgba(0, 84, 153, .14)',
                                tension: .35,
                                fill: true,
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                labels: {
                                    usePointStyle: true,
                                },
                            },
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: (value) => new Intl.NumberFormat('fr-FR').format(value),
                                },
                            },
                        },
                    },
                });
            }

            const statusCanvas = document.getElementById('chart-maintenance-status');
            if (statusCanvas && window.Chart) {
                const labels = JSON.parse(statusCanvas.dataset.labels || '[]');
                const values = JSON.parse(statusCanvas.dataset.values || '[]');
                const colors = JSON.parse(statusCanvas.dataset.colors || '[]');

                new Chart(statusCanvas, {
                    type: 'doughnut',
                    data: {
                        labels,
                        datasets: [
                            {
                                data: values,
                                backgroundColor: colors,
                                borderWidth: 0,
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '66%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                            },
                        },
                    },
                });
            }
        })();
    </script>
@endpush
