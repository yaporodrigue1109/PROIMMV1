@extends('agence.layouts.app')

@section('title', 'Maintenance')

@section('content')
    <div class="page">

        {{-- Hero header --}}
        <div class="page-header">
            <div class="page-header-copy">
                <div class="page-heading">
                    <h2>Maintenance</h2>
                </div>
                <p>Gérez les maintenanciers, leurs fonctions, les interventions et les types d'intervention.</p>
            </div>
            <div class="page-actions">
                <button class="btn btn-outline" id="btnImport">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                    </svg>
                    Importer
                </button>
                <button class="btn btn-primary" data-open-modal="modal-add-maintenancier">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Nouveau maintenancier
                </button>
            </div>
        </div>

        {{-- Alertes --}}
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        {{-- Stats --}}
        @php

            $maintenanciersStatiques = $maintenancier;
            $fonctionsStatiques = $fonctionMaintenance;
            $typesInterventionStatiques = $typeMaintenance;  // Changé: maintenances -> typeMaintenance
            $interventionsStatiques = $maintenances;         // Changé: typeMaintenance -> maintenances

            $stats = [
                'maintenanciers' => count($maintenanciersStatiques),
                'interventions' => count($interventionsStatiques),
                'en_cours' => count(array_filter($interventionsStatiques, fn($i) => $i['statut'] == 'en_cours')),
                'terminees_mois' => count(array_filter($interventionsStatiques, fn($i) => $i['statut'] == 'terminee' && substr($i['date_debut'], 5, 2) == date('m'))),
            ];
        @endphp

        <div class="stats-grid">
            <div class="stat-card">
                <span>Maintenanciers</span>
                <strong>{{ $stats['maintenanciers'] }}</strong>
            </div>
            <div class="stat-card">
                <span>Interventions</span>
                <strong class="is-info">{{ $stats['interventions'] }}</strong>
            </div>
            <div class="stat-card">
                <span>En cours</span>
                <strong class="is-warning">{{ $stats['en_cours'] }}</strong>
            </div>
            <div class="stat-card">
                <span>Terminées ce mois</span>
                <strong>+{{ $stats['terminees_mois'] }}</strong>
            </div>
        </div>

        {{-- Module shell : sidebar nav + contenu --}}
        <div class="prop-shell">

            {{-- Sidebar de navigation --}}
            <nav class="prop-sidenav" aria-label="Navigation maintenance">
                <div class="prop-nav-section">
                    <div class="prop-nav-label">Maintenance</div>
                    <a href="#" class="prop-nav-item active" data-panel="maintenanciers">
                        <span class="prop-nav-dot"></span>
                        Maintenanciers
                        <span class="prop-nav-count">{{ count($maintenanciersStatiques) }}</span>
                    </a>
                    <a href="#" class="prop-nav-item" data-panel="interventions">
                        <span class="prop-nav-dot"></span>
                        Interventions
                        <span class="prop-nav-count">{{ count($interventionsStatiques) }}</span>
                    </a>
                </div>
                <div class="prop-nav-divider"></div>
                <div class="prop-nav-section">
                    <div class="prop-nav-label">Référentiel</div>
                    <a href="#" class="prop-nav-item" data-panel="fonctions">
                        <span class="prop-nav-dot"></span>
                        Fonctions de maintenancier
                    </a>
                    <a href="#" class="prop-nav-item" data-panel="type-interventions">
                        <span class="prop-nav-dot"></span>
                        Types d'intervention
                    </a>
                </div>
            </nav>

            {{-- Zone de contenu principale --}}
            <div class="prop-content">

                {{-- PANEL : Liste des maintenanciers --}}
                <div class="prop-panel active" id="panel-maintenanciers">
                    <div class="prop-panel-header">
                        <div>
                            <h3 class="prop-panel-title">Liste des maintenanciers</h3>
                            <p class="prop-panel-sub">{{ count($maintenanciersStatiques) }} maintenancier(s) enregistré(s)</p>
                        </div>
                        <div class="u-flex u-gap-xs">
                            <div class="search-field">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                                </svg>
                                <input type="text" id="searchMaintenancier" placeholder="Rechercher…" />
                            </div>
                            <button class="btn btn-primary" data-open-modal="modal-add-maintenancier">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                                Nouveau
                            </button>
                        </div>
                    </div>

                    <div class="table-shell u-table-flush">
                        <table class="data-table" id="tableMaintenanciers">
                            <thead>
                            <tr>
                                <th>Maintenancier</th>
                                <th>Contact</th>
                                <th>Fonction</th>
                                {{--                                <th>Spécialités</th>--}}
                                <th>Disponibilité</th>
                                <th>Interventions</th>
                                <th class="table-actions-col">Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($maintenanciersStatiques as $m)
                                <tr>
                                    <td>
                                        <div class="entity-cell">
                                            <div class="entity-thumb entity-thumb-info">
                                                {{ strtoupper(substr($m['name'], 0, 1)) }}
                                            </div>
                                            <div class="entity-copy">
                                                <strong>{{ $m['name'] }}</strong>
                                                <span>{{ $m['entreprise'] ?? 'Indépendant' }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div>{{ $m['email'] }}</div>
                                        <span class="text-muted">{{ $m['tel1'] }}</span>
                                    </td>
                                    <td><span class="badge badge-info">{{ $m['fonction']['name'] }}</span></td>
                                    {{--                                    <td>--}}
                                    {{--                                        @foreach(explode(',', $m['specialites']) as $spec)--}}
                                    {{--                                            <span class="badge badge-neutral">{{ trim($spec) }}</span>--}}
                                    {{--                                        @endforeach--}}
                                    {{--                                    </td>--}}
                                    <td>
                                        @if($m['statut'])
                                            <span class="badge badge-success">Disponible</span>
                                        @else
                                            <span class="badge badge-danger">Indisponible</span>
                                        @endif
                                    </td>
                                    <td><span class="badge badge-info">{{ $m['interventions_count'] ?? 0 }}</span></td>
                                    <td>
                                        <div class="table-actions">
                                            {{-- ✅ CORRECT : utilise $m['id'] et la bonne route maintenancier --}}
                                            <a href="{{ route('agence.maintenance.maintenancier.show', $m['maintenancier_id']) }}"
                                               class="action-btn info"
                                               title="Voir">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                </svg>
                                            </a>
                                            <button class="action-btn neutral btn-edit"
                                                    title="Modifier"
                                                    data-open-modal="modal-edit-maintenancier"
                                                    data-id="{{ $m['maintenancier_id'] }}">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" />
                                                </svg>
                                            </button>
                                            <button class="action-btn danger btn-delete" title="Supprimer">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- PANEL : Liste des interventions --}}
                <div class="prop-panel" id="panel-interventions">
                    <div class="prop-panel-header">
                        <div>
                            <h3 class="prop-panel-title">Liste des interventions</h3>
                            <p class="prop-panel-sub">{{ count($interventionsStatiques) }} intervention(s) enregistrée(s)</p>
                        </div>
                        <div class="u-flex u-gap-xs">
                            <div class="search-field">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                                </svg>
                                <input type="text" id="searchIntervention" placeholder="Rechercher…" />
                            </div>
                            <button class="btn btn-primary" data-open-modal="modal-add-intervention">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                                Nouvelle intervention
                            </button>
                        </div>
                    </div>

                    <div class="table-shell u-table-flush">
                        <table class="data-table" id="tableInterventions">
                            <thead>
                            <tr>
                                <th>Intervention</th>
                                <th>Description</th>
                                <th>Proprietaire</th>

                                <th>Montant</th>
                                <th>Responsabilite</th>

                                <th>Statut</th>
                                <th class="table-actions-col">Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($interventionsStatiques as $i)
                                @php
                                    $prioriteClass = match($i['prise_en_charge_par']) {
                                        'proprietaire' => 'warning',
                                        'locataire' => 'info',
                                        'agence' => 'neutral',
                                        default => 'neutral'
                                    };
                                    $statutClass = match($i['statut']) {
                                        'planifiee' => 'info',
                                        'en_cours' => 'warning',
                                        'terminee' => 'success',
                                        'annulee' => 'danger',
                                        default => 'neutral'
                                    };
                                    $statutLabel = match($i['statut']) {
                                        'planifiee' => 'Planifiée',
                                        'en_cours' => 'En cours',
                                        'terminee' => 'Terminée',
                                        'annulee' => 'Annulée',
                                        default => $i['statut']
                                    };
                                @endphp
                                <tr>
                                    <td>
                                        <div class="entity-cell">
                                            <div class="entity-thumb entity-thumb-warning">
                                                {{ strtoupper(substr($i['titre'], 0, 1)) }}
                                            </div>
                                            <div class="entity-copy">
                                                <strong>{{ $i['titre'] }}</strong>

                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="badge ">{{ $i['description'] }}</span></td>
                                    <td> <strong>{{ $i['proprietaire']?->name }} </strong> <br>

                                        <span class="text-muted">{{ $i['proprietaire']?->tel1 }}</span>
                                    </td>
                                    <td>
                                        {{ number_format($i['montant_global'] ?? 0, 0, ',', ' ') }}
                                    </td>
                                    <td><span class="badge badge-{{ $prioriteClass }}">{{ ucfirst($i['prise_en_charge_par']) }}</span></td>
                                    <td><span class="badge badge-{{ $statutClass }}">{{ $statutLabel }}</span></td>
                                    <td>
                                        <div class="table-actions">
                                            {{-- ✅ CORRECT : utilise $i['id'] et la bonne route intervention --}}
                                            <a href="{{ route('agence.maintenance.intervention.show', $i['maintenance_id']) }}"
                                               class="action-btn info"
                                               title="Voir">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                </svg>
                                            </a>
                                            <button class="action-btn neutral btn-edit-intervention"
                                                    title="Modifier"
                                                    data-open-modal="modal-edit-intervention"
                                                    data-id="{{ $i['maintenance_id'] }}">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" />
                                                </svg>
                                            </button>
                                            <button class="action-btn danger btn-delete" title="Annuler">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- PANEL : Fonctions de maintenancier --}}
                <div class="prop-panel" id="panel-fonctions">
                    <div class="prop-panel-header">
                        <div>
                            <h3 class="prop-panel-title">Fonctions de maintenancier</h3>
                            <p class="prop-panel-sub">{{ count($fonctionsStatiques) }} fonctions configurées</p>
                        </div>
                        <button class="btn btn-primary" data-open-modal="modal-add-fonction">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                            Nouvelle fonction
                        </button>
                    </div>
                    <div class="prop-ref-grid">
                        @foreach($fonctionsStatiques as $f)
                            <div class="prop-ref-card">
                                <div class="prop-ref-name">{{ $f['libelle'] }}</div>
                                @if($f['description'])
                                    <p class="prop-ref-desc">{{ $f['description'] }}</p>
                                @endif
                                <div class="table-actions u-mt-sm">
                                    <button class="action-btn neutral btn-edit-fonction" title="Modifier"
                                            data-id="{{ $f['id'] }}" data-name="{{ $f['libelle'] }}" data-categorie="{{ $f['categorie'] ?? '' }}" data-description="{{ $f['description'] }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" /></svg>
                                    </button>
                                    <button class="action-btn danger btn-delete-fonction" title="Supprimer">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- PANEL : Types d'intervention --}}
                <div class="prop-panel" id="panel-type-interventions">
                    <div class="prop-panel-header">
                        <div>
                            <h3 class="prop-panel-title">Types d'intervention</h3>
                            <p class="prop-panel-sub">{{ count($typesInterventionStatiques) }} types configurés</p>
                        </div>
                        <button class="btn btn-primary" data-open-modal="modal-add-type-intervention">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                            Nouveau type
                        </button>
                    </div>
                    <div class="prop-ref-grid">
                        @foreach($typesInterventionStatiques as $t)
                            <div class="prop-ref-card">
                                <div class="prop-ref-name">{{ $t['name'] }}</div>
                                <div class="prop-ref-desc">{{ $t['categorie'] }}</div>
                                {{--                                @if($t['description'] ?? false)--}}
                                {{--                                    <p class="prop-ref-desc">{{ $t['description'] }}</p>--}}
                                {{--                                @endif--}}
                                <div class="table-actions u-mt-sm">
                                    <button class="action-btn neutral btn-edit-type" title="Modifier"
                                            data-id="{{ $t['type_maintenance_id'] }}" data-name="{{ $t['name'] }}" data-categorie="{{ $t['categorie'] }}" data-description="{{ $t['description'] ?? '' }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" /></svg>
                                    </button>
                                    <button class="action-btn danger btn-delete-type" title="Supprimer">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>{{-- /.prop-content --}}
        </div>{{-- /.prop-shell --}}

    </div>{{-- /.page --}}

    {{-- ============================================================
         MODAL : Ajouter un maintenancier
         ============================================================ --}}
    @include('agence.maintenance.modal-create-maintenancier')

    @include('agence.maintenance.modal-edit-maintenancier')

    {{-- ============================================================
   MODAL : Ajouter une intervention
   ============================================================ --}}
    @include('agence.maintenance.modal-create-intervention')
    @include('agence.maintenance.modal-edit-intervention')

    {{-- ============================================================
         MODAL : Ajouter une fonction de maintenancier
         ============================================================ --}}

    @include('agence.maintenance.modal-create-fonction')
    @include('agence.maintenance.modal-edit-fonction')
    {{-- ============================================================
         MODAL : Ajouter un type d'intervention
         ============================================================ --}}
    @include('agence.maintenance.modal-create-type-intervention')
    @include('agence.maintenance.modal-edit-type-intervention')


    @push('styles')
        <style>

            .u-modal-lg {
                max-width: 980px;
                width: min(980px, calc(100vw - 32px));
            }

            .intervention-section {
                padding: 18px;
                margin-bottom: 18px;
                background: var(--card);
                border: 1px solid var(--border);
                color: var(--foreground);
                border-radius: 1rem;
                margin-top: 10px;
            }

            .intervention-section-header {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 16px;
                margin-bottom: 16px;
            }

            .intervention-section-header h4 {
                margin: 0;
                font-size: 16px;
                font-weight: 700;
                color: var(--foreground);
            }

            .intervention-section-header p {
                margin: 4px 0 0;
                font-size: 13px;
                color: var(--muted-foreground);
            }

            .intervention-task-card {
                border: 1px solid var(--border);
                border-radius: 16px;
                padding: 16px;
                margin-bottom: 14px;
                background: var(--muted);
            }

            .intervention-task-head {
                display: flex;
                align-items: center;
                justify-content: space-between;
                margin-bottom: 14px;
            }

            .intervention-task-head strong {
                font-size: 15px;
                color: var(--foreground);
            }

            .task-remove-btn {
                border: 1px solid rgba(239, 68, 68, .28);
                background: rgba(239, 68, 68, .10);
                color: #ef4444;
                padding: 7px 10px;
                border-radius: 10px;
                font-size: 13px;
                font-weight: 600;
                cursor: pointer;
            }

            .task-remove-btn:hover {
                background: rgba(239, 68, 68, .16);
            }

            .intervention-total-box {
                display: flex;
                align-items: center;
                justify-content: space-between;
                margin-top: 16px;
                padding: 14px 16px;
                border-radius: 14px;
                border: 1px solid rgba(37, 99, 235, .22);
                background: rgba(37, 99, 235, .12);
                color: var(--foreground);
            }

            .intervention-total-box span {
                font-size: 14px;
                color: var(--muted-foreground);
            }

            .intervention-total-box strong {
                font-size: 18px;
                color: #60a5fa;
            }

            html[data-theme="light"] .intervention-total-box {
                background: rgba(37, 99, 235, .08);
            }

            html[data-theme="light"] .intervention-total-box strong {
                color: #2563eb;
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            // --- 1. Panels navigation ---
            document.querySelectorAll('.prop-nav-item[data-panel]').forEach(link => {
                link.addEventListener('click', e => {
                    e.preventDefault();
                    const target = link.dataset.panel;
                    document.querySelectorAll('.prop-nav-item').forEach(l => l.classList.remove('active'));
                    document.querySelectorAll('.prop-panel').forEach(p => p.classList.remove('active'));
                    link.classList.add('active');
                    document.getElementById('panel-' + target)?.classList.add('active');
                });
            });

            // --- 2. Modals ---
            document.querySelectorAll('[data-open-modal]').forEach(btn => {
                btn.addEventListener('click', () => {
                    const modal = document.querySelector(`[data-modal="${btn.dataset.openModal}"]`);
                    if (modal) {
                        modal.classList.add('open');
                        modal.setAttribute('aria-hidden', 'false');
                    }
                });
            });

            document.querySelectorAll('[data-close-modal], .modal-close').forEach(btn => {
                btn.addEventListener('click', () => {
                    const modal = btn.closest('.modal');
                    if (modal) {
                        modal.classList.remove('open');
                        modal.setAttribute('aria-hidden', 'true');
                    }
                });
            });

            // ==========================================================
            // === GESTION DES TÂCHES D'INTERVENTION (VERSION CORRIGÉE) ===
            // ==========================================================
            document.addEventListener('DOMContentLoaded', function() {


                const tasksContainer = document.getElementById('interventionTasksWrapper');
                const addTaskButton = document.getElementById('addInterventionTask');
                const totalSpan = document.getElementById('interventionTotal');

                if (!tasksContainer) console.error('❌ tasksContainer non trouvé');
                if (!addTaskButton) console.error('❌ addTaskButton non trouvé');
                if (!totalSpan) console.error('❌ totalSpan non trouvé');

                if (!tasksContainer || !addTaskButton) return;

                const proprietaireSelect = document.getElementById('interventionProprietaire');
                const proprieteSelect = document.getElementById('interventionPropriete');

                if (proprietaireSelect && proprieteSelect) {
                    const allProprieteOptions = Array.from(proprieteSelect.querySelectorAll('option'));

                    proprietaireSelect.addEventListener('change', function () {
                        const selectedProprietaireId = this.value;

                        proprieteSelect.innerHTML = '';

                        allProprieteOptions.forEach(option => {
                            if (!option.value) {
                                proprieteSelect.appendChild(option.cloneNode(true));
                                return;
                            }

                            if (option.dataset.proprietaireId === selectedProprietaireId) {
                                proprieteSelect.appendChild(option.cloneNode(true));
                            }
                        });

                        proprieteSelect.value = '';
                    });
                }


                // Recalcul du total
                function calculateTotal() {
                    let total = 0;
                    document.querySelectorAll('#interventionTasksWrapper .task-price').forEach(priceInput => {
                        let priceValue = parseFloat(priceInput.value);
                        if (!isNaN(priceValue)) {
                            total += priceValue;
                        }
                    });
                    if (totalSpan) {
                        totalSpan.innerText = new Intl.NumberFormat('fr-FR').format(total) + ' FCFA';
                    }
                }

                // Mise à jour des indices
                function updateIndicesAndListeners() {
                    const tasks = document.querySelectorAll('#interventionTasksWrapper .intervention-task-card');


                    tasks.forEach((task, index) => {
                        // Met à jour le titre
                        const titleSpan = task.querySelector('.intervention-task-head strong');
                        if (titleSpan) titleSpan.innerText = `Travail ${index + 1}`;

                        // Met à jour les noms des champs
                        task.querySelectorAll('input, select, textarea').forEach(field => {
                            let name = field.getAttribute('name');
                            if (name && name.includes('details[')) {
                                // Regex plus robuste
                                let newName = name.replace(/details\[\d+\]/, `details[${index}]`);
                                field.setAttribute('name', newName);

                            }
                        });

                        // Gère le bouton supprimer
                        const removeBtn = task.querySelector('.task-remove-btn');
                        if (removeBtn) {
                            removeBtn.hidden = (tasks.length === 1);
                        }
                    });

                    // Réattache les événements de prix
                    document.querySelectorAll('#interventionTasksWrapper .task-price').forEach(priceField => {
                        priceField.removeEventListener('input', calculateTotal);
                        priceField.addEventListener('input', calculateTotal);
                    });

                    calculateTotal();
                }

                // Création d'une nouvelle tâche vide
                function createEmptyTask() {
                    const firstTask = document.querySelector('#interventionTasksWrapper .intervention-task-card');
                    if (!firstTask) {
                        console.error('❌ Aucune tâche template trouvée');
                        return null;
                    }

                    const newTask = firstTask.cloneNode(true);

                    // Vide toutes les valeurs
                    newTask.querySelectorAll('input, select, textarea').forEach(field => {
                        if (field.type !== 'button' && field.type !== 'submit' && field.type !== 'hidden') {
                            if (field.tagName === 'SELECT') {
                                field.selectedIndex = 0;
                            } else if (field.type === 'checkbox' || field.type === 'radio') {
                                field.checked = false;
                            } else if (field.type === 'date') {
                                field.value = '';
                            } else {
                                field.value = '';
                            }
                        }
                    });

                    // Met le prix à 0
                    const priceField = newTask.querySelector('.task-price');
                    if (priceField) priceField.value = '0';

                    return newTask;
                }

                // Ajouter une tâche
                function addNewTask() {

                    const newTask = createEmptyTask();
                    if (newTask && tasksContainer) {
                        tasksContainer.appendChild(newTask);
                        updateIndicesAndListeners();
                    } else {
                        console.error('❌ Impossible d\'ajouter la tâche');
                    }
                }

                // Supprimer une tâche
                function deleteTask(buttonElement) {
                    const taskCard = buttonElement.closest('.intervention-task-card');
                    const totalTasks = document.querySelectorAll('#interventionTasksWrapper .intervention-task-card').length;

                    if (totalTasks > 1) {

                        taskCard.remove();
                        updateIndicesAndListeners();
                    } else {
                        alert("⚠️ Il doit y avoir au moins un travail à effectuer.");
                    }
                }

                // Initialisation des événements
                addTaskButton.addEventListener('click', addNewTask);

                // Délégation pour les boutons supprimer
                tasksContainer.addEventListener('click', function(e) {
                    const deleteBtn = e.target.closest('[data-remove-task]');
                    if (deleteBtn) {
                        e.preventDefault();

                        deleteTask(deleteBtn);
                    }
                });

                // Initialisation
                const firstRemoveBtn = document.querySelector('#interventionTasksWrapper .intervention-task-card .task-remove-btn');
                if (firstRemoveBtn) firstRemoveBtn.hidden = true;

                // Initialise le calcul
                document.querySelectorAll('#interventionTasksWrapper .task-price').forEach(priceField => {
                    priceField.addEventListener('input', calculateTotal);
                });
                calculateTotal();



                // ==========================================================
                // === GESTION DES MODALES DE MODIFICATION ===
                // ==========================================================

                // --- 1. MODIFICATION D'UN MAINTENANCIER ---
                document.querySelectorAll('#tableMaintenanciers .btn-edit').forEach(btn => {
                    btn.addEventListener('click', function(e) {
                        e.preventDefault();
                        const row = this.closest('tr');
                        const viewLink = row.querySelector('a.action-btn.info');
                        if (viewLink) {
                            const url = viewLink.getAttribute('href');
                            const id = url.split('/').pop();

                            // Charger les données via AJAX
                            fetch(`/agence/maintenance/maintenancier/${id}/json`)
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        const maintenancier = data.data;

                                        // Remplir tous les champs
                                        document.getElementById('edit_maintenancier_name').value = maintenancier.name || '';
                                        document.getElementById('edit_maintenancier_entreprise').value = maintenancier.entreprise || '';
                                        document.getElementById('edit_maintenancier_email').value = maintenancier.email || '';
                                        document.getElementById('edit_maintenancier_tel1').value = maintenancier.tel1 || '';
                                        document.getElementById('edit_maintenancier_tel2').value = maintenancier.tel2 || '';
                                        document.getElementById('edit_maintenancier_fonction').value = maintenancier.fonction_maintenance_id || '';
                                        document.getElementById('edit_maintenancier_statut').value = maintenancier.statut ? '1' : '0';

                                        // Champs supplémentaires
                                        if (document.getElementById('edit_type_piece_id')) {
                                            document.getElementById('edit_type_piece_id').value = maintenancier.type_piece_id || '';
                                        }
                                        if (document.getElementById('edit_numero_piece')) {
                                            document.getElementById('edit_numero_piece').value = maintenancier.numero_piece || '';
                                        }
                                        if (document.getElementById('edit_date_validite_piece')) {
                                            document.getElementById('edit_date_validite_piece').value = maintenancier.date_validite_piece || '';
                                        }
                                        if (document.getElementById('edit_maintenancier_adresse')) {
                                            document.getElementById('edit_maintenancier_adresse').value = maintenancier.adresse || '';
                                        }

                                        const form = document.getElementById('formEditMaintenancier');
                                        form.action = `/agence/maintenance/maintenancier/${id}`;

                                        // S'assurer que la méthode PUT est bien présente
                                        if (!form.querySelector('input[name="_method"]')) {
                                            const methodInput = document.createElement('input');
                                            methodInput.type = 'hidden';
                                            methodInput.name = '_method';
                                            methodInput.value = 'PUT';
                                            form.appendChild(methodInput);
                                        } else {
                                            form.querySelector('input[name="_method"]').value = 'PUT';
                                        }

                                        // Ouvrir la modal (en utilisant la même structure que l'ajout)
                                        const modal = document.querySelector('[data-modal="modal-edit-maintenancier"]');
                                        if (modal) {
                                            modal.removeAttribute('aria-hidden');
                                        }
                                    } else {
                                        console.error('Erreur:', data.message);
                                        alert('Impossible de charger les données du maintenancier');
                                    }
                                })
                                .catch(error => {
                                    console.error('Erreur:', error);
                                    alert('Erreur lors du chargement des données');
                                });
                        }
                    });
                });

                // --- 2. MODIFICATION D'UNE FONCTION ---
                document.querySelectorAll('.btn-edit-fonction').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const id = this.dataset.id;
                        const name = this.dataset.name || this.dataset.libelle || '';
                        const categorie = this.dataset.categorie || '';
                        const description = this.dataset.description || '';

                        const modal = document.querySelector('[data-modal="modal-edit-fonction"]');
                        if (!modal) {
                            console.error('Modal edit fonction introuvable');
                            return;
                        }

                        const nameInput = document.getElementById('edit_fonction_name');
                        const categorieInput = document.getElementById('edit_fonction_categorie');
                        const descriptionInput = document.getElementById('edit_fonction_description');
                        const form = document.getElementById('formEditFonction');

                        if (nameInput) nameInput.value = name;
                        if (categorieInput) categorieInput.value = categorie;
                        if (descriptionInput) descriptionInput.value = description;
                        if (form) form.action = `/agence/maintenance/fonction/${id}`;

                        modal.classList.add('open');
                        modal.setAttribute('aria-hidden', 'false');
                    });
                });

                // --- 3. MODIFICATION D'UN TYPE D'INTERVENTION ---
                document.querySelectorAll('.btn-edit-type').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const id = this.dataset.id;
                        const name = this.dataset.name || this.dataset.libelle || '';
                        const categorie = this.dataset.categorie || '';
                        const description = this.dataset.description || '';

                        const modal = document.querySelector('[data-modal="modal-edit-type-intervention"]');
                        if (!modal) {
                            console.error('Modal edit type intervention introuvable');
                            return;
                        }

                        const nameInput = document.getElementById('edit_type_name');
                        const categorieInput = document.getElementById('edit_type_categorie');
                        const descriptionInput = document.getElementById('edit_type_description');
                        const form = document.getElementById('formEditTypeIntervention');

                        if (nameInput) nameInput.value = name;
                        if (categorieInput) categorieInput.value = categorie;
                        if (descriptionInput) descriptionInput.value = description;
                        if (form) form.action = `/agence/maintenance/type-intervention/${id}`;

                        modal.classList.add('open');
                        modal.setAttribute('aria-hidden', 'false');
                    });
                });

                // --- 4. MODIFICATION D'UNE INTERVENTION ---
                document.querySelectorAll('#tableInterventions .btn-edit').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const row = this.closest('tr');
                        const viewLink = row.querySelector('a.action-btn.info');
                        if (viewLink) {
                            const url = viewLink.getAttribute('href');
                            const id = url.split('/').pop();

                            // Charger les données via AJAX
                            fetch(`/agence/maintenance/intervention/${id}/json`)
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        const intervention = data.data;
                                        document.getElementById('edit_intervention_titre').value = intervention.titre || '';
                                        document.getElementById('edit_intervention_type').value = intervention.type_maintenance_id || '';
                                        document.getElementById('edit_intervention_description').value = intervention.description || '';
                                        document.getElementById('edit_intervention_montant').value = intervention.montant_global || '';
                                        document.getElementById('edit_intervention_prise_en_charge').value = intervention.prise_en_charge_par || '';
                                        document.getElementById('edit_intervention_statut').value = intervention.statut || '';
                                        document.getElementById('edit_intervention_proprietaire').value = intervention.proprietaire_id || '';
                                        document.getElementById('edit_intervention_date_debut').value = intervention.date_debut || '';
                                        document.getElementById('edit_intervention_date_fin').value = intervention.date_fin_prevue || '';

                                        // Charger les propriétés du propriétaire
                                        if (intervention.proprietaire_id) {
                                            loadProprietesForProprietaire(intervention.proprietaire_id, intervention.propriete_id);
                                        }

                                        // Charger les tâches
                                        loadTasksForIntervention(id);

                                        const form = document.getElementById('formEditIntervention');
                                        form.action = `/agence/maintenance/intervention/${id}`;

                                        if (!form.querySelector('input[name="_method"]')) {
                                            const methodInput = document.createElement('input');
                                            methodInput.type = 'hidden';
                                            methodInput.name = '_method';
                                            methodInput.value = 'PUT';
                                            form.appendChild(methodInput);
                                        } else {
                                            form.querySelector('input[name="_method"]').value = 'PUT';
                                        }

                                        const editModal = document.querySelector('[data-modal="modal-edit-intervention"]');
                                        if (editModal) {
                                            editModal.classList.add('open');
                                            editModal.setAttribute('aria-hidden', 'false');
                                        }
                                    } else {
                                        console.error('Erreur:', data.message);
                                        alert('Impossible de charger les données de l\'intervention');
                                    }
                                })
                                .catch(error => {
                                    console.error('Erreur:', error);
                                    alert('Erreur lors du chargement des données');
                                });
                        }
                    });
                });

                // ==========================================================
                // === FONCTIONS UTILITAIRES POUR LES MODALES ===
                // ==========================================================

                // Charger les propriétés d'un propriétaire
                function loadProprietesForProprietaire(proprietaireId, selectedProprieteId = null) {
                    const proprieteSelect = document.getElementById('edit_intervention_propriete');
                    if (!proprieteSelect) return;

                    proprieteSelect.innerHTML = '<option value="">Chargement...</option>';

                    fetch(`/agence/proprietes/by-proprietaire/${proprietaireId}`)
                        .then(response => response.json())
                        .then(data => {
                            proprieteSelect.innerHTML = '<option value="">Sélectionner une propriété</option>';
                            if (data.success && data.data) {
                                data.data.forEach(propriete => {
                                    const option = document.createElement('option');
                                    option.value = propriete.id;
                                    option.textContent = propriete.nom || propriete.libelle;
                                    if (selectedProprieteId && selectedProprieteId == propriete.id) {
                                        option.selected = true;
                                    }
                                    proprieteSelect.appendChild(option);
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Erreur chargement propriétés:', error);
                            proprieteSelect.innerHTML = '<option value="">Erreur de chargement</option>';
                        });
                }

                // Charger les tâches d'une intervention
                function loadTasksForIntervention(interventionId) {
                    const tasksContainer = document.getElementById('editInterventionTasksWrapper');
                    if (!tasksContainer) return;

                    fetch(`/agence/maintenance/intervention/${interventionId}/tasks`)
                        .then(response => response.json())
                        .then(data => {
                            tasksContainer.innerHTML = '';
                            if (data.success && data.data && data.data.length > 0) {
                                data.data.forEach((task, index) => {
                                    addEditTask(task, index);
                                });
                            } else {
                                // Ajouter une tâche vide par défaut
                                addEditTask(null, 0);
                            }
                            calculateEditTotal();
                            updateEditRemoveButtons();
                        })
                        .catch(error => {
                            console.error('Erreur chargement tâches:', error);
                            tasksContainer.innerHTML = '';
                            addEditTask(null, 0);
                            calculateEditTotal();
                        });
                }

                // Ajouter une tâche dans la modal d'édition
                function addEditTask(taskData = null, index = 0) {
                    const tasksContainer = document.getElementById('editInterventionTasksWrapper');
                    if (!tasksContainer) return;

                    const taskHtml = `
            <div class="intervention-task-card" data-task-index="${index}">
                <div class="intervention-task-head">
                    <strong>Travail ${index + 1}</strong>
                    <button type="button" class="task-remove-btn" data-remove-edit-task>Supprimer</button>
                </div>
                <div class="form-grid u-grid-cols-2">
                    <div class="form-group">
                        <label>Description du travail *</label>
                        <textarea name="details[${index}][description]" rows="2" required>${taskData ? (taskData.description || '') : ''}</textarea>
                    </div>
                    <div class="form-group">
                        <label>Prix estimé (FCFA)</label>
                        <input type="number" name="details[${index}][prix]" class="task-price" step="0.01" value="${taskData ? (taskData.prix || '0') : '0'}">
                    </div>
                    <div class="form-group">
                        <label>Date prévue</label>
                        <input type="date" name="details[${index}][date_prevue]" value="${taskData ? (taskData.date_prevue || '') : ''}">
                    </div>
                    <div class="form-group">
                        <label>Statut</label>
                        <select name="details[${index}][statut]">
                            <option value="en_attente" ${taskData && taskData.statut === 'en_attente' ? 'selected' : ''}>En attente</option>
                            <option value="en_cours" ${taskData && taskData.statut === 'en_cours' ? 'selected' : ''}>En cours</option>
                            <option value="termine" ${taskData && taskData.statut === 'termine' ? 'selected' : ''}>Terminé</option>
                        </select>
                    </div>
                </div>
            </div>
        `;

                    tasksContainer.insertAdjacentHTML('beforeend', taskHtml);

                    // Ajouter l'écouteur d'événement pour le prix
                    const newTask = tasksContainer.lastElementChild;
                    const priceField = newTask.querySelector('.task-price');
                    if (priceField) {
                        priceField.addEventListener('input', calculateEditTotal);
                    }
                }

                // Recalculer le total dans la modal d'édition
                function calculateEditTotal() {
                    let total = 0;
                    document.querySelectorAll('#editInterventionTasksWrapper .task-price').forEach(priceInput => {
                        let priceValue = parseFloat(priceInput.value);
                        if (!isNaN(priceValue)) {
                            total += priceValue;
                        }
                    });
                    const totalSpan = document.getElementById('editInterventionTotal');
                    if (totalSpan) {
                        totalSpan.innerText = new Intl.NumberFormat('fr-FR').format(total) + ' FCFA';
                    }
                }

                // Mettre à jour l'affichage des boutons supprimer
                function updateEditRemoveButtons() {
                    const tasks = document.querySelectorAll('#editInterventionTasksWrapper .intervention-task-card');
                    tasks.forEach((task, index) => {
                        const removeBtn = task.querySelector('[data-remove-edit-task]');
                        if (removeBtn) {
                            removeBtn.hidden = (tasks.length === 1);
                        }
                        // Mettre à jour le titre
                        const titleSpan = task.querySelector('.intervention-task-head strong');
                        if (titleSpan) titleSpan.innerText = `Travail ${index + 1}`;
                    });
                }

                // Réindexer les tâches
                function reindexEditTasks() {
                    const tasks = document.querySelectorAll('#editInterventionTasksWrapper .intervention-task-card');
                    tasks.forEach((task, index) => {
                        task.setAttribute('data-task-index', index);
                        task.querySelector('.intervention-task-head strong').innerText = `Travail ${index + 1}`;
                        task.querySelectorAll('input, select, textarea').forEach(field => {
                            let name = field.getAttribute('name');
                            if (name && name.includes('details[')) {
                                let newName = name.replace(/details\[\d+\]/, `details[${index}]`);
                                field.setAttribute('name', newName);
                            }
                        });
                    });
                    updateEditRemoveButtons();
                }

                // Supprimer une tâche
                function deleteEditTask(buttonElement) {
                    const taskCard = buttonElement.closest('.intervention-task-card');
                    const totalTasks = document.querySelectorAll('#editInterventionTasksWrapper .intervention-task-card').length;

                    if (totalTasks > 1) {
                        taskCard.remove();
                        reindexEditTasks();
                        calculateEditTotal();
                    } else {
                        alert("⚠️ Il doit y avoir au moins un travail à effectuer.");
                    }
                }

                // Événements pour les boutons d'ajout/suppression dans la modal d'édition
                document.addEventListener('DOMContentLoaded', function() {
                    const addEditTaskBtn = document.getElementById('editAddInterventionTask');
                    if (addEditTaskBtn) {
                        addEditTaskBtn.addEventListener('click', function() {
                            const tasksCount = document.querySelectorAll('#editInterventionTasksWrapper .intervention-task-card').length;
                            addEditTask(null, tasksCount);
                            reindexEditTasks();
                            calculateEditTotal();
                        });
                    }

                    // Délégation pour les boutons de suppression dans la modal d'édition
                    const editTasksContainer = document.getElementById('editInterventionTasksWrapper');
                    if (editTasksContainer) {
                        editTasksContainer.addEventListener('click', function(e) {
                            const deleteBtn = e.target.closest('[data-remove-edit-task]');
                            if (deleteBtn) {
                                e.preventDefault();
                                deleteEditTask(deleteBtn);
                            }
                        });
                    }

                    // Gestion du changement de propriétaire dans l'édition
                    const editProprietaireSelect = document.getElementById('edit_intervention_proprietaire');
                    if (editProprietaireSelect) {
                        editProprietaireSelect.addEventListener('change', function() {
                            const proprietaireId = this.value;
                            if (proprietaireId) {
                                loadProprietesForProprietaire(proprietaireId);
                            } else {
                                const proprieteSelect = document.getElementById('edit_intervention_propriete');
                                if (proprieteSelect) {
                                    proprieteSelect.innerHTML = '<option value="">Sélectionner une propriété</option>';
                                }
                            }
                        });
                    }
                });

            });
        </script>
    @endpush
@endsection
