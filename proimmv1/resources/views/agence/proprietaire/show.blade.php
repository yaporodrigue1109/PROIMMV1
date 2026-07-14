@extends('agence.layouts.app')

@section('title', 'Détail propriétaire')
@section('header_title', 'Détail propriétaire')

@section('content')
    @php
        $fmt     = fn($v) => number_format((float) $v, 0, ',', ' ') . ' FCFA';
        $liaison = $proprietaire->agences->first();
    @endphp

    <div class="rp-page">

        {{-- En-tête --}}
        <div class="page-header">
            <div class="page-header-copy">
                <div class="page-heading">
                    <div>
                        <h2>Propriétaire — Détail</h2>
                        <p class="text-muted mb-0">
                            {{ $proprietaire->name }}
                            · {{ $proprietaire->tel1 }}
                            @if($proprietaire->code)
                                · Code : <strong>{{ $proprietaire->code }}</strong>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
            <div class="page-actions">
                <a href="{{ route('agence.proprietaire.edit', $proprietaire->proprietaire_id) }}" class="btn btn-outline">
                    Modifier
                </a>
                <a href="{{ route('agence.proprietaire.index') }}" class="btn btn-primary">
                    Liste propriétaires
                </a>
            </div>
        </div>

        {{-- Navigation par onglets --}}
        <nav class="rp-topbar" aria-label="Navigation propriétaire">
            <button class="rp-tab is-active" data-tab="profil" type="button">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.5 20.25a8.25 8.25 0 0115 0"/>
                </svg>
                Profil
            </button>

            <button class="rp-tab" data-tab="lots" type="button">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498l4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 00-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0z"/>
                </svg>
                Lots <span class="agency-count">{{ $lots->count() }}</span>
            </button>

            <button class="rp-tab" data-tab="proprietes" type="button">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M3 3h12m-.75 4.5H21"/>
                </svg>
                Propriétés
            </button>

            <button class="rp-tab" data-tab="recapitulatif" type="button">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125h4.5v7.5H3v-7.5zM9.75 8.625h4.5v12h-4.5v-12zM16.5 3.375H21v17.25h-4.5V3.375z"/>
                </svg>
                Tableau récapitulatif
            </button>

            <button class="rp-tab" data-tab="loyers" type="button">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15A2.25 2.25 0 002.25 6.75v10.5A2.25 2.25 0 004.5 19.5z"/>
                </svg>
                Consolidé loyers
            </button>

            <button class="rp-tab" data-tab="reversements" type="button">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75L21 10.5m0 0l-3.75 3.75M21 10.5H3m3.75 6.75L3 13.5m0 0l3.75-3.75M3 13.5h18"/>
                </svg>
                Historique des reversements
            </button>
        </nav>

        {{-- ============================================================
             ONGLET 1 — Profil
        ============================================================ --}}
        <div class="rp-panel is-active" id="panel-profil">

            <div class="rp-kpi-strip">
                <div class="rp-kpi">
                    <span class="rp-kpi-label">Propriétés</span>
                    <span class="rp-kpi-value">{{ $proprietaire->proprietes_count ?? '—' }}</span>
                    <span class="rp-kpi-delta">Biens enregistrés</span>
                </div>
                <div class="rp-kpi">
                    <span class="rp-kpi-label">Lots</span>
                    <span class="rp-kpi-value">{{ $lots->count() }}</span>
                    <span class="rp-kpi-delta">Lots enregistrés</span>
                </div>
                <div class="rp-kpi">
                    <span class="rp-kpi-label">Locataires</span>
                    <span class="rp-kpi-value u-text-primary">{{ $proprietaire->locataires_count ?? '—' }}</span>
                    <span class="rp-kpi-delta">Locataires associés</span>
                </div>
                <div class="rp-kpi">
                    <span class="rp-kpi-label">Statut agence</span>
                    <span class="rp-kpi-value">
                        @if($liaison?->is_active)
                            <span class="badge badge-success">Actif</span>
                        @else
                            <span class="badge badge-danger">Inactif</span>
                        @endif
                    </span>
                    <span class="rp-kpi-delta">
                        @if($liaison?->date_activation)
                            Depuis le {{ \Carbon\Carbon::parse($liaison->date_activation)->format('d/m/Y') }}
                        @else
                            Non renseigné
                        @endif
                    </span>
                </div>
            </div>

            <div class="rp-two-col u-table-fit">

                <div class="rp-card">
                    <div class="rp-card-head">
                        <h3 class="rp-card-title">Informations du propriétaire</h3>
                        <span class="rp-card-count">{{ $proprietaire->code }}</span>
                    </div>
                    <div class="rp-info-grid">
                        <div class="rp-info-row">
                            <span class="rp-info-label">Nom et prénom</span>
                            <span class="rp-info-value">{{ $proprietaire->name }}</span>
                        </div>
                        <div class="rp-info-row">
                            <span class="rp-info-label">Email</span>
                            <span class="rp-info-value">{{ $proprietaire->email ?? '—' }}</span>
                        </div>
                        <div class="rp-info-row">
                            <span class="rp-info-label">Téléphone 1</span>
                            <span class="rp-info-value">{{ $proprietaire->tel1 }}</span>
                        </div>
                        <div class="rp-info-row">
                            <span class="rp-info-label">Téléphone 2</span>
                            <span class="rp-info-value">{{ $proprietaire->tel2 ?? '—' }}</span>
                        </div>
                        <div class="rp-info-row">
                            <span class="rp-info-label">Adresse</span>
                            <span class="rp-info-value">{{ $proprietaire->adresse ?? '—' }}</span>
                        </div>
                        <div class="rp-info-row">
                            <span class="rp-info-label">Profession</span>
                            <span class="rp-info-value">{{ $proprietaire->profession ?? '—' }}</span>
                        </div>
                        <div class="rp-info-row">
                            <span class="rp-info-label">Nationalité</span>
                            <span class="rp-info-value">{{ $proprietaire->nationalite ?? '—' }}</span>
                        </div>
                        <div class="rp-info-row">
                            <span class="rp-info-label">Date de naissance</span>
                            <span class="rp-info-value">{{ $proprietaire->date_naiss?->format('d/m/Y') ?? '—' }}</span>
                        </div>
                        <div class="rp-info-row">
                            <span class="rp-info-label">Lieu de naissance</span>
                            <span class="rp-info-value">{{ $proprietaire->lieu_naiss ?? '—' }}</span>
                        </div>
                        <div class="rp-info-row">
                            <span class="rp-info-label">N° pièce d'identité</span>
                            <span class="rp-info-value">{{ $proprietaire->numpiece ?? '—' }}</span>
                        </div>
                    </div>
                </div>

                <div class="rp-two-col-stack owner-side-stack">
                    <div class="rp-card">
                        <div class="rp-card-head">
                            <h3 class="rp-card-title">Localisation</h3>
                        </div>
                        <div class="rp-info-grid">
                            <div class="rp-info-row">
                                <span class="rp-info-label">Région</span>
                                <span class="rp-info-value">{{ $proprietaire->region?->name ?? '—' }}</span>
                            </div>
                            <div class="rp-info-row">
                                <span class="rp-info-label">Ville</span>
                                <span class="rp-info-value">{{ $proprietaire->ville?->name ?? '—' }}</span>
                            </div>
                        </div>
                    </div>

                    @if($liaison?->name_representant)
                        <div class="rp-card">
                            <div class="rp-card-head">
                                <h3 class="rp-card-title">Représentant</h3>
                            </div>
                            <div class="rp-info-grid">
                                <div class="rp-info-row">
                                    <span class="rp-info-label">Nom</span>
                                    <span class="rp-info-value">{{ $liaison->name_representant }}</span>
                                </div>
                                <div class="rp-info-row">
                                    <span class="rp-info-label">Adresse</span>
                                    <span class="rp-info-value">{{ $liaison->adresse_representant ?? '—' }}</span>
                                </div>
                                <div class="rp-info-row">
                                    <span class="rp-info-label">Téléphone 1</span>
                                    <span class="rp-info-value">{{ $liaison->tel1_representant ?? '—' }}</span>
                                </div>
                                <div class="rp-info-row">
                                    <span class="rp-info-label">Téléphone 2</span>
                                    <span class="rp-info-value">{{ $liaison->tel2_representant ?? '—' }}</span>
                                </div>
                                <div class="rp-info-row">
                                    <span class="rp-info-label">Email</span>
                                    <span class="rp-info-value">{{ $liaison->email_representant ?? '—' }}</span>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($liaison)
                        <div class="rp-card">
                            <div class="rp-card-head">
                                <h3 class="rp-card-title">Gestion du compte</h3>
                            </div>
                            <div class="rp-info-grid u-mb-sm">
                                <div class="rp-info-row">
                                    <span class="rp-info-label">Date activation</span>
                                    <span class="rp-info-value">{{ $liaison->date_activation?->format('d/m/Y') ?? '—' }}</span>
                                </div>
                                <div class="rp-info-row">
                                    <span class="rp-info-label">Date désactivation</span>
                                    <span class="rp-info-value">{{ $liaison->date_desactivation?->format('d/m/Y') ?? '—' }}</span>
                                </div>
                            </div>
                            @if($liaison->is_active)
                                <form method="POST" action="{{ route('agence.proprietaire.deactivate', $liaison->proprietaire_agence_id) }}">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn-danger btn-sm">Désactiver ce propriétaire</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('agence.proprietaire.activate', $liaison->proprietaire_agence_id) }}">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn-success btn-sm">Activer ce propriétaire</button>
                                </form>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ============================================================
             ONGLET 2 — Lots (avec le composant x-table)
        ============================================================ --}}
        <div class="rp-panel" id="panel-lots">
            <x-table
                    title="Lots du propriétaire"
                    :collection="$lots"
                    empty-message="Aucun lot enregistré pour ce propriétaire."
                    :colspan="7"
                    class="rp-table"
            >
                <x-slot:actions>
                    <button type="button"
                            class="btn btn-primary btn-sm"
                            data-open-modal="modal-lot"
                            onclick="prepareLotModal()">
                        + Ajouter un lot
                    </button>
                </x-slot:actions>

                <x-slot:thead>
                    <th>Nom</th>
                    <th>N° Lot</th>
                    <th>N° Îlot</th>
                    <th>Superficie</th>
                    <th>Région / Ville</th>
                    <th>Adresse</th>
                    <th class="col-r">Actions</th>
                </x-slot:thead>

                @forelse($lots as $lot)
                    <tr id="lot-row-{{ $lot->propreietaire_lot_id }}">
                        <td><strong>{{ $lot->name }}</strong></td>
                        <td>{{ $lot->num_lot ?? '—' }}</td>
                        <td>{{ $lot->num_ilot ?? '—' }}</td>
                        <td>{{ $lot->superficie ? $lot->superficie . ' m²' : '—' }}</td>
                        <td>
                            <strong>{{ $lot->region?->name ?? '—' }}</strong>
                            <small>{{ $lot->ville?->name ?? '—' }}</small>
                        </td>
                        <td>{{ $lot->adresse ?? '—' }}</td>
                        <td class="col-r">
                            <div class="table-actions u-flex-end">
                                <button type="button"
                                        class="action-btn info"
                                        title="Modifier"
                                        data-open-modal="modal-lot"
                                        onclick='prepareLotModal(@json($lot))'>
                                    ✎
                                </button>

                                <button type="button"
                                        class="action-btn danger"
                                        title="Supprimer"
                                        onclick="deleteLot('{{ $lot->propreietaire_lot_id }}')">
                                    ×
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    {{-- Le message vide est géré par empty-message du composant --}}
                @endforelse
            </x-table>
        </div>

        {{-- ============================================================
             ONGLET 3 — Propriétés (avec x-table)
        ============================================================ --}}
        <div class="rp-panel" id="panel-proprietes">
            <x-table
                    title="Liste des propriétés"
                    :collection="$proprietes ?? []"
                    empty-message="Aucune propriété associée à ce propriétaire."
                    :colspan="9"
                    search-id="property-search"
                    class="rp-table"
            >
                <x-slot:thead>
                    <th>#</th>
                    <th>Nom</th>
                    <th>Adresse</th>
                    <th class="col-hide-sm">Région / Ville</th>
                    <th>Type</th>
                    <th>Statut</th>
                    <th class="col-r">Portes</th>
                    <th class="col-r">Prix</th>
                    <th class="col-r">Actions</th>
                </x-slot:thead>

                @forelse($proprietes ?? [] as $propriete)
                    <tr data-search="{{ strtolower(($propriete->nom ?? '') . ' ' . ($propriete->adresse ?? '')) }}">
                        <td><strong>{{ $loop->iteration }}</strong></td>
                        <td>
                            <strong>{{ $propriete->nom ?? '—' }}</strong>
                            <small>Ref : #{{ $propriete->id }}</small>
                        </td>
                        <td>{{ $propriete->adresse ?? '—' }}</td>
                        <td class="col-hide-sm">
                            <strong>{{ $propriete->region?->name ?? '—' }}</strong>
                            <small>{{ $propriete->ville?->name ?? '—' }}</small>
                        </td>
                        <td><span class="owner-pill type">{{ $propriete->type ?? '—' }}</span></td>
                        <td><span class="badge badge-success">{{ $propriete->statut ?? '—' }}</span></td>
                        <td class="col-r">
                            <strong>{{ $propriete->portes ?? 0 }}</strong>
                            <small>portes</small>
                        </td>
                        <td class="col-r"><strong class="owner-price">{{ $fmt($propriete->prix ?? 0) }}</strong></td>
                        <td class="col-r">
                            <div class="table-actions u-flex-end">
                                <a href="#" class="action-btn info" title="Voir">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.644C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .638C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    {{-- Le message vide est géré par empty-message du composant --}}
                @endforelse
            </x-table>
        </div>

        {{-- ============================================================
             ONGLET 4 — Tableau récapitulatif
        ============================================================ --}}
        <div class="rp-panel" id="panel-recapitulatif">
            <div class="rp-card">
                <div class="rp-card-head">
                    <h3 class="rp-card-title">Tableau récapitulatif de paiement</h3>
                    @if(isset($paiementsRecap))
                        <span class="rp-card-count">{{ count($paiementsRecap) }} locataire(s)</span>
                    @endif
                </div>
                @if(isset($paiementsRecap, $moisRecap) && count($paiementsRecap))
                    <div class="rp-table-wrap">
                        <table class="rp-table">
                            <thead>
                            <tr>
                                <th>Locataire / Mois</th>
                                @foreach($moisRecap as $mois)
                                    <th class="col-r">{{ $mois }}</th>
                                @endforeach
                                <th class="col-r">Total</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($paiementsRecap as $locataire => $paiements)
                                <tr>
                                    <td><strong>{{ $locataire }}</strong></td>
                                    @foreach($paiements as $montant)
                                        <td class="col-r">{{ number_format($montant, 0, ',', ' ') }}</td>
                                    @endforeach
                                    <td class="col-r">
                                        <strong>{{ number_format(array_sum($paiements), 0, ',', ' ') }}</strong>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                            <tfoot>
                            <tr>
                                <td><strong>TOTAUX</strong></td>
                                @foreach($totauxMois as $totalMois)
                                    <td class="col-r"><strong>{{ number_format($totalMois, 0, ',', ' ') }}</strong></td>
                                @endforeach
                                <td class="col-r">
                                    <strong>{{ number_format($totalGeneralRecap ?? 0, 0, ',', ' ') }}</strong>
                                </td>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                @else
                    <div class="empty-panel">Aucune donnée de paiement disponible pour ce propriétaire.</div>
                @endif
            </div>
        </div>

        {{-- ============================================================
             ONGLET 5 — Consolidé loyers
        ============================================================ --}}
        @php
            $consolide = [
                'total_loyers'    => 670000,
                'commission'      => 67000,
                'depenses'        => 0,
                'net_a_reverser'  => 603000,
                'total_cautions'  => 0,
                'reversement'     => 603000,
                'cours' => [
                    [
                        'nom'                  => 'Cours KONATE AROUNA',
                        'lot'                  => '213',
                        'adresse'              => 'ANYAMA CHRISTIANKOI 1',
                        'total_loyer'          => 670000,
                        'commission'           => 67000,
                        'apres_commission'     => 603000,
                        'nouvelle_caution'     => 0,
                        'reversement'          => 603000,
                        'depenses'             => 0,
                        'montant_a_reverser'   => 603000,
                    ]
                ]
            ];
        @endphp

        <div class="rp-panel" id="panel-loyers">

            {{-- FILTRES --}}
            <div class="rp-card consolidation-card">
                <div class="rp-card-head">
                    <h3 class="rp-card-title">Période de consolidation</h3>
                </div>

                <form method="GET"
                      action="{{ route('agence.proprietaire.show', $proprietaire->proprietaire_id) }}"
                      class="consolidation-filter">

                    <input type="hidden" name="tab" value="loyers">

                    <label class="form-field">
                        <span>Date de début *</span>
                        <x-ui.date-picker
                                name="date_debut"
                                :value="request('date_debut', now()->startOfMonth()->format('Y-m-d'))"
                                placeholder="Sélectionner la date"/>
                    </label>

                    <label class="form-field">
                        <span>Date de fin *</span>
                        <x-ui.date-picker
                                name="date_fin"
                                :value="request('date_fin', now()->endOfMonth()->format('Y-m-d'))"
                                placeholder="Sélectionner la date"/>
                    </label>

                    <div class="consolidation-filter-action">
                        <button type="submit" class="btn btn-primary">
                            Générer le consolidé
                        </button>
                    </div>
                </form>
            </div>

            @if(isset($consolide))
                {{-- ENTETE --}}
                <div class="rp-card u-mt-md">
                    <div class="rp-card-head">
                        <h3 class="rp-card-title">
                            Consolidé du
                            {{ \Carbon\Carbon::parse(request('date_debut'))->format('d/m/Y') }}
                            au
                            {{ \Carbon\Carbon::parse(request('date_fin'))->format('d/m/Y') }}
                        </h3>
                    </div>

                    <div class="rp-kpi-strip">
                        <div class="rp-kpi">
                            <span class="rp-kpi-label">Total Loyers</span>
                            <span class="rp-kpi-value">{{ $fmt($consolide['total_loyers'] ?? 0) }}</span>
                        </div>
                        <div class="rp-kpi">
                            <span class="rp-kpi-label">Commission (10%)</span>
                            <span class="rp-kpi-value">{{ $fmt($consolide['commission'] ?? 0) }}</span>
                        </div>
                        <div class="rp-kpi">
                            <span class="rp-kpi-label">Dépenses</span>
                            <span class="rp-kpi-value">{{ $fmt($consolide['depenses'] ?? 0) }}</span>
                        </div>
                        <div class="rp-kpi">
                            <span class="rp-kpi-label">Net à reverser</span>
                            <span class="rp-kpi-value u-text-primary">{{ $fmt($consolide['net_a_reverser'] ?? 0) }}</span>
                        </div>
                    </div>
                </div>

                {{-- TABLEAU AVEC x-table --}}
                <div class="rp-card u-mt-md">
                    <x-table
                            title="Détail par cours"
                            :collection="$consolide['cours'] ?? []"
                            empty-message="Aucun cours pour cette période."
                            :colspan="9"
                            class="rp-table"
                    >
                        <x-slot:thead>
                            <th>Cours</th>
                            <th class="col-r">Total Loyer</th>
                            <th class="col-r">Commission 10%</th>
                            <th class="col-r">Montant après Commission</th>
                            <th class="col-r">Nouvelle caution</th>
                            <th class="col-r">Reversement</th>
                            <th class="col-r">Dépenses</th>
                            <th class="col-r">Montant à reverser</th>
                            <th class="col-r">Actions</th>
                        </x-slot:thead>

                        @forelse($consolide['cours'] ?? [] as $cours)
                            <tr>
                                <td>
                                    <strong>{{ $cours['nom'] }}</strong>
                                    <small>Lot : {{ $cours['lot'] ?? '—' }}</small>
                                    <small>{{ $cours['adresse'] ?? '' }}</small>
                                </td>
                                <td class="col-r">{{ $fmt($cours['total_loyer']) }}</td>
                                <td class="col-r">{{ $fmt($cours['commission']) }}</td>
                                <td class="col-r">{{ $fmt($cours['apres_commission']) }}</td>
                                <td class="col-r">{{ $fmt($cours['nouvelle_caution'] ?? 0) }}</td>
                                <td class="col-r">{{ $fmt($cours['reversement']) }}</td>
                                <td class="col-r">{{ $fmt($cours['depenses'] ?? 0) }}</td>
                                <td class="col-r">
                                    <strong class="owner-price">{{ $fmt($cours['montant_a_reverser']) }}</strong>
                                </td>
                                <td class="col-r">—</td>
                            </tr>
                        @empty
                            {{-- Le message vide est géré par empty-message du composant --}}
                        @endforelse

                        <x-slot:footer>
                            <tr>
                                <td><strong>TOTAUX</strong></td>
                                <td class="col-r">{{ $fmt($consolide['total_loyers'] ?? 0) }}</td>
                                <td class="col-r">{{ $fmt($consolide['commission'] ?? 0) }}</td>
                                <td class="col-r">{{ $fmt(($consolide['total_loyers'] ?? 0) - ($consolide['commission'] ?? 0)) }}</td>
                                <td class="col-r">{{ $fmt($consolide['total_cautions'] ?? 0) }}</td>
                                <td class="col-r">{{ $fmt($consolide['reversement'] ?? 0) }}</td>
                                <td class="col-r">{{ $fmt($consolide['depenses'] ?? 0) }}</td>
                                <td class="col-r">{{ $fmt($consolide['net_a_reverser'] ?? 0) }}</td>
                                <td></td>
                            </tr>
                        </x-slot:footer>
                    </x-table>
                </div>

                {{-- DETAILS --}}
                <div class="rp-two-col u-table-fit u-mt-md">
                    <div class="rp-card">
                        <div class="rp-card-head">
                            <h3 class="rp-card-title">Calcul détaillé du reversement</h3>
                        </div>
                        <div class="rp-info-grid">
                            <div class="rp-info-row">
                                <span class="rp-info-label">Total des loyers :</span>
                                <span class="rp-info-value">{{ $fmt($consolide['total_loyers'] ?? 0) }}</span>
                            </div>
                            <div class="rp-info-row">
                                <span class="rp-info-label">Commission (10%) :</span>
                                <span class="rp-info-value">- {{ $fmt($consolide['commission'] ?? 0) }}</span>
                            </div>
                            <div class="rp-info-row">
                                <span class="rp-info-label">Montant après commission :</span>
                                <span class="rp-info-value">{{ $fmt(($consolide['total_loyers'] ?? 0) - ($consolide['commission'] ?? 0)) }}</span>
                            </div>
                            <div class="rp-info-row">
                                <span class="rp-info-label">Nouvelles cautions :</span>
                                <span class="rp-info-value">+ {{ $fmt($consolide['total_cautions'] ?? 0) }}</span>
                            </div>
                            <div class="rp-info-row">
                                <span class="rp-info-label">Total reversement :</span>
                                <span class="rp-info-value">{{ $fmt($consolide['reversement'] ?? 0) }}</span>
                            </div>
                            <div class="rp-info-row">
                                <span class="rp-info-label">Dépenses :</span>
                                <span class="rp-info-value">- {{ $fmt($consolide['depenses'] ?? 0) }}</span>
                            </div>
                            <div class="rp-info-row">
                                <span class="rp-info-label"><strong>NET À REVERSER :</strong></span>
                                <span class="rp-info-value u-text-primary"><strong>{{ $fmt($consolide['net_a_reverser'] ?? 0) }}</strong></span>
                            </div>
                        </div>
                        <div class="empty-panel u-mt-md">
                            Calcul: (Total Loyers - 10% Commission + Nouvelles Cautions) - Dépenses = Net à reverser
                        </div>
                    </div>

                    <div class="rp-card">
                        <div class="rp-card-head">
                            <h3 class="rp-card-title">Informations</h3>
                        </div>
                        <div class="rp-info-grid">
                            <div class="rp-info-row">
                                <span class="rp-info-label">Propriétaire :</span>
                                <span class="rp-info-value">{{ $proprietaire->name }}</span>
                            </div>
                            <div class="rp-info-row">
                                <span class="rp-info-label">Période :</span>
                                <span class="rp-info-value">
                                    {{ \Carbon\Carbon::parse(request('date_debut'))->format('d/m/Y') }}
                                    <br>au<br>
                                    {{ \Carbon\Carbon::parse(request('date_fin'))->format('d/m/Y') }}
                                </span>
                            </div>
                            <div class="rp-info-row">
                                <span class="rp-info-label">Nombre de cours :</span>
                                <span class="rp-info-value">{{ count($consolide['cours'] ?? []) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- ============================================================
             ONGLET 6 — Historique des reversements (avec x-table)
        ============================================================ --}}
        @php
            $reversements = [
                [
                    'date'              => '31/05/2026',
                    'reference'         => 'REV-2026-001',
                    'periode'           => '01/05/2026 au 31/05/2026',
                    'montant_loyers'    => 670000,
                    'commission'        => 67000,
                    'depenses'          => 0,
                    'net'               => 603000,
                    'mode_paiement'     => 'Virement bancaire',
                    'statut'            => 'Payé',
                ],
                [
                    'date'              => '30/04/2026',
                    'reference'         => 'REV-2026-002',
                    'periode'           => '01/04/2026 au 30/04/2026',
                    'montant_loyers'    => 540000,
                    'commission'        => 54000,
                    'depenses'          => 15000,
                    'net'               => 471000,
                    'mode_paiement'     => 'Espèces',
                    'statut'            => 'Payé',
                ],
            ];
        @endphp

        <div class="rp-panel" id="panel-reversements">
            {{-- KPI --}}
            <div class="rp-kpi-strip">
                <div class="rp-kpi">
                    <span class="rp-kpi-label">Total reversements</span>
                    <span class="rp-kpi-value">{{ count($reversements) }}</span>
                    <span class="rp-kpi-delta">Reversements effectués</span>
                </div>
                <div class="rp-kpi">
                    <span class="rp-kpi-label">Montant reversé</span>
                    <span class="rp-kpi-value u-text-primary">{{ $fmt(collect($reversements)->sum('net')) }}</span>
                    <span class="rp-kpi-delta">Total cumulé</span>
                </div>
            </div>

            {{-- TABLEAU AVEC x-table --}}
            <div class="rp-card u-mt-md">
                <x-table
                        title="Historique des reversements"
                        :collection="$reversements"
                        empty-message="Aucun reversement enregistré."
                        :colspan="10"
                        class="rp-table"
                >
                    <x-slot:thead>
                        <th>Date</th>
                        <th>Référence</th>
                        <th>Période</th>
                        <th class="col-r">Loyers</th>
                        <th class="col-r">Commission</th>
                        <th class="col-r">Dépenses</th>
                        <th class="col-r">Net reversé</th>
                        <th>Mode paiement</th>
                        <th>Statut</th>
                        <th class="col-r">Actions</th>
                    </x-slot:thead>

                    @forelse($reversements as $reversement)
                        <tr>
                            <td><strong>{{ $reversement['date'] }}</strong></td>
                            <td>{{ $reversement['reference'] }}</td>
                            <td>{{ $reversement['periode'] }}</td>
                            <td class="col-r">{{ $fmt($reversement['montant_loyers']) }}</td>
                            <td class="col-r">{{ $fmt($reversement['commission']) }}</td>
                            <td class="col-r">{{ $fmt($reversement['depenses']) }}</td>
                            <td class="col-r"><strong class="owner-price">{{ $fmt($reversement['net']) }}</strong></td>
                            <td>{{ $reversement['mode_paiement'] }}</td>
                            <td><span class="badge badge-success">{{ $reversement['statut'] }}</span></td>
                            <td class="col-r">
                                <div class="table-actions u-flex-end">
                                    <a href="#" class="action-btn info" title="Voir">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.644C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .638C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        {{-- Le message vide est géré par empty-message du composant --}}
                    @endforelse
                </x-table>
            </div>
        </div>

        {{-- ============================================================
                MODAL LOT
        ============================================================ --}}
        <div class="modal" data-modal="modal-lot" aria-hidden="true">
            <div class="modal-box u-modal-md">
                <div class="modal-header">
                    <h3 id="lot-modal-title">Ajouter un lot</h3>
                    <button class="modal-close" data-close-modal="" aria-label="Fermer">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="u-icon-sm"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="modal-body">
                    <div id="lot-modal-error" class="alert alert-danger" style="display:none;"></div>

                    <div class="form-grid">
                        <input type="hidden" id="lot-id">

                        <label class="form-field ">
                            <span>Nom du lot *</span>
                            <input type="text" id="lot-name" placeholder="Lot principal">
                        </label>

                        <label class="form-field">
                            <span>N° Lot</span>
                            <input type="text" id="lot-num_lot" placeholder="001">
                        </label>

                        <label class="form-field">
                            <span>N° Îlot</span>
                            <input type="text" id="lot-num_ilot" placeholder="A">
                        </label>

                        <label class="form-field">
                            <span>Superficie (m²)</span>
                            <input type="number" id="lot-superficie" placeholder="150" step="0.01">
                        </label>

                        <label class="form-field form-field-wide">
                            <span>Adresse</span>
                            <input type="text" id="lot-adresse" placeholder="Cocody Riviera 3">
                        </label>

                        <label class="form-field">
                            <span>Région</span>
                            <select id="lot-region_id"
                                    onchange="getRequest('{{ url('/') }}/admin/list/city?parent_id='+this.value,'lot-ville_id','select',this.value)"
                            >
                                <option value="">Sélectionner</option>
                                @foreach($regions as $regionId => $regionName)
                                    <option value="{{ $regionId }}">{{ $regionName }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label class="form-field">
                            <span>Ville</span>
                            <select id="lot-ville_id" >
                                <option value="">Sélectionner</option>
                                @foreach($villes as $villeId => $villeName)
                                    <option value="{{ $villeId }}">{{ $villeName }}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>

                    <div class="u-flex-end u-mt-lg">
                        <button type="button" class="btn btn-outline" data-close-modal>Annuler</button>
                        <button type="button" onclick="saveLot()" class="btn btn-primary" id="lot-save-btn">Enregistrer</button>
                    </div>
                </div>
            </div>
        </div>
    </div>{{-- /.rp-page --}}

    <style>
        #panel-loyers .consolidation-card {
            overflow: visible;
            padding: 22px 24px 24px;
        }

        #panel-loyers .consolidation-card .rp-card-head {
            margin-bottom: 18px;
        }

        .consolidation-filter {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 22px;
        }

        .consolidation-filter .form-field {
            margin: 0;
        }

        .consolidation-filter-action {
            grid-column: 1 / -1;
            display: flex;
            justify-content: flex-end;
            padding-top: 4px;
        }

        .consolidation-filter-action .btn {
            min-width: 220px;
        }

        @media (max-width: 768px) {
            .consolidation-filter {
                grid-template-columns: 1fr;
            }

            .consolidation-filter-action .btn {
                width: 100%;
            }
        }

        .owner-side-stack {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .owner-side-stack .rp-card form {
            margin: 18px;
        }
    </style>
@endsection

@push('scripts')
    <script>
        // ── Onglets ───────────────────────────────────────────────────────
        const tabs   = document.querySelectorAll('.rp-tab');
        const panels = document.querySelectorAll('.rp-panel');

        tabs.forEach(tab => {
            tab.addEventListener('click', function () {
                tabs.forEach(t => t.classList.remove('is-active'));
                panels.forEach(p => p.classList.remove('is-active'));
                this.classList.add('is-active');
                document.getElementById('panel-' + this.dataset.tab)?.classList.add('is-active');
            });
        });

        const activeTab = new URLSearchParams(window.location.search).get('tab');
        if (activeTab) {
            document.querySelector(`[data-tab="${activeTab}"]`)?.click();
        }

        // ── Recherche propriétés ─────────────────────────────────────────
        document.getElementById('property-search')?.addEventListener('input', function () {
            const q = this.value.toLowerCase();
            document.querySelectorAll('#properties-table tbody tr').forEach(row => {
                row.style.display = (row.dataset.search ?? '').includes(q) ? '' : 'none';
            });
        });

        // ── Lots — config ─────────────────────────────────────────────────
        const PROPRIETAIRE_ID = '{{ $proprietaire->proprietaire_id }}';
        const STORE_URL       = '{{ route("agence.proprietaire.lots.store", $proprietaire->proprietaire_id) }}';
        const UPDATE_URL      = (id) => `/agence/proprietaire/lots/${id}`;
        const DELETE_URL      = (id) => `/agence/proprietaire/lots/${id}`;
        const CSRF            = '{{ csrf_token() }}';

        // ── Ouvrir modal ──────────────────────────────────────────────────
        window.prepareLotModal = function (lot = null) {
            document.getElementById('lot-modal-error').style.display = 'none';

            document.getElementById('lot-modal-title').textContent = lot ? 'Modifier le lot' : 'Ajouter un lot';

            document.getElementById('lot-id').value         = lot?.propreietaire_lot_id ?? '';
            document.getElementById('lot-name').value       = lot?.name ?? '';
            document.getElementById('lot-num_lot').value    = lot?.num_lot ?? '';
            document.getElementById('lot-num_ilot').value   = lot?.num_ilot ?? '';
            document.getElementById('lot-superficie').value = lot?.superficie ?? '';
            document.getElementById('lot-adresse').value    = lot?.adresse ?? '';
            document.getElementById('lot-region_id').value  = lot?.region_id ?? '';
            document.getElementById('lot-ville_id').value   = lot?.ville_id ?? '';
        };

        // ── Sauvegarder lot ───────────────────────────────────────────────
        async function saveLot() {
            const id     = document.getElementById('lot-id').value;
            const isEdit = !!id;
            const btn    = document.getElementById('lot-save-btn');

            const payload = {
                name:       document.getElementById('lot-name').value,
                num_lot:    document.getElementById('lot-num_lot').value,
                num_ilot:   document.getElementById('lot-num_ilot').value,
                superficie: document.getElementById('lot-superficie').value,
                adresse:    document.getElementById('lot-adresse').value,
                region_id:  document.getElementById('lot-region_id').value,
                ville_id:   document.getElementById('lot-ville_id').value,
                _token:     CSRF,
            };

            if (isEdit) payload['_method'] = 'PUT';

            btn.disabled    = true;
            btn.textContent = 'Enregistrement…';

            try {
                const res  = await fetch(isEdit ? UPDATE_URL(id) : STORE_URL, {
                    method:  'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF,
                        'Accept':       'application/json',
                    },
                    body: JSON.stringify(payload),
                });

                const data = await res.json();

                if (!res.ok || !data.success) {
                    const msg = data.errors
                        ? Object.values(data.errors).flat().join(' · ')
                        : (data.message ?? 'Une erreur est survenue.');
                    showLotError(msg);
                    return;
                }

                const modal = document.querySelector('[data-modal="modal-lot"]');
                modal?.classList.remove('is-open', 'active');
                modal?.setAttribute('aria-hidden', 'true');
                window.location.reload();

            } catch (e) {
                showLotError('Erreur réseau. Veuillez réessayer.');
            } finally {
                btn.disabled    = false;
                btn.textContent = 'Enregistrer';
            }
        }

        // ── Supprimer lot ─────────────────────────────────────────────────
        async function deleteLot(id) {
            if (!confirm('Supprimer ce lot ? Cette action est irréversible.')) return;

            try {
                const res  = await fetch(DELETE_URL(id), {
                    method:  'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF,
                        'Accept':       'application/json',
                    },
                    body: JSON.stringify({ _method: 'DELETE', _token: CSRF }),
                });
                const data = await res.json();

                if (data.success) {
                    const row = document.getElementById(`lot-row-${id}`);
                    row?.remove();

                    const count = document.querySelectorAll('#lots-table tbody tr:not(#lots-empty)').length;
                    const counter = document.getElementById('lots-count');
                    if (counter) counter.textContent = count + ' lot(s)';

                    if (count === 0) {
                        document.querySelector('#lots-table tbody').innerHTML =
                            '<tr id="lots-empty"><td colspan="7" class="text-center text-muted">Aucun lot enregistré.</td></tr>';
                    }
                }
            } catch (e) {
                alert('Erreur réseau. Veuillez réessayer.');
            }
        }

        // ── Afficher erreur modal ─────────────────────────────────────────
        function showLotError(msg) {
            const el = document.getElementById('lot-modal-error');
            el.textContent   = msg;
            el.style.display = 'block';
        }
    </script>
@endpush