
@extends('agence.layouts.app')

@section('title', 'Propriété · ' . $propriete->reference)

@section('content')
    <div class="page">

        {{-- ── Header ── --}}
        <div class="page-header">
            <div class="page-header-copy">
                <div class="page-heading">
                    <a href="{{ route('agence.proprietes.index') }}" class="back-link">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                        </svg>
                    </a>
                    <h2>{{ $propriete->reference }}</h2>
                    @if($propriete->is_allocation)
                        <span class="badge badge-warning">Allocation</span>
                    @endif
                    @if(!$propriete->is_actif)
                        <span class="badge badge-danger">Inactive</span>
                    @endif
                </div>
                <p>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:13px;height:13px;vertical-align:middle;margin-right:3px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                    </svg>
                    {{ $propriete->adresse_complete ?? 'Adresse non renseignée' }}
                </p>
            </div>
            <div class="page-actions">
                <a href="{{ route('agence.proprietes.edit', $propriete->propriete_id) }}" class="btn btn-outline">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" />
                    </svg>
                    Modifier
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        {{-- ── Stats ── --}}
        <div class="stats-grid" style="grid-template-columns: repeat(4,1fr);">
            <div class="stat-card">
                <span>Bâtiments</span>
                <strong>{{ $propriete->nbre_batiment }}</strong>
            </div>
            <div class="stat-card">
                <span>Portes total</span>
                <strong>{{ $propriete->nbre_porte_total }}</strong>
            </div>
            <div class="stat-card">
                <span>Libres</span>
                <strong class="is-success">{{ $propriete->nbre_porte_libre }}</strong>
            </div>
            <div class="stat-card">
                <span>Occupées</span>
                <strong class="is-danger">{{ $propriete->nbre_porte_occupe }}</strong>
            </div>
        </div>

        {{-- ── Fiche info + taux d'occupation ── --}}
        <div class="show-layout u-mt-md">

            {{-- Informations --}}
            <div class="show-card">
                <div class="show-card-header">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                    </svg>
                    <span>Informations</span>
                </div>
                <div class="show-card-body">
                    <dl class="info-list">
                        <div class="info-row">
                            <dt>Type</dt>
                            <dd>
                                <span class="badge badge-info">{{ $propriete->typePropriete->name ?? '—' }}</span>
                            </dd>
                        </div>
                        <div class="info-row">
                            <dt>Propriétaire</dt>
                            <dd>{{ $propriete->proprietaire->name ?? '—' }}</dd>
                        </div>
                        <div class="info-row">
                            <dt>Agence</dt>
                            <dd>{{ $propriete->agence->name ?? '—' }}</dd>
                        </div>
                        <div class="info-row">
                            <dt>Lot / Zone</dt>
                            <dd>{{ $propriete->lot?->name ?? '—' }} ({{ $propriete->lot?->adresse ?? '—' }}) </dd>
                        </div>
                        <div class="info-row">
                            <dt>Mode</dt>
                            <dd>
                                @if($propriete->is_allocation)
                                    <span class="badge badge-warning">Allocation</span>
                                @else
                                    <span class="badge badge-neutral">Standard</span>
                                @endif
                            </dd>
                        </div>
                        @if($propriete->description)
                            <div class="info-row info-row-full">
                                <dt>Description</dt>
                                <dd class="desc-text">{{ $propriete->description }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>

            {{-- Taux d'occupation --}}
            <div class="show-card">
                <div class="show-card-header">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 14.25v2.25m3-4.5v4.5m3-6.75v6.75m3-9v9M6 20.25h12A2.25 2.25 0 0 0 20.25 18V6A2.25 2.25 0 0 0 18 3.75H6A2.25 2.25 0 0 0 3.75 6v12A2.25 2.25 0 0 0 6 20.25Z" />
                    </svg>
                    <span>Occupation</span>
                </div>
                <div class="show-card-body">
                    @php

                        $total  = $propriete->nbre_porte_total;
                        $occupe =  $propriete->nbre_porte_occupe;
                        $libre  = $propriete->nbre_porte_libre;
                        $taux   = $total > 0 ? round($occupe / $total * 100) : 0;
                    @endphp
                    <div class="occ-taux">{{ $taux }}<span>%</span></div>
                    <div class="occ-bar-wrap">
                        <div class="occ-bar">
                            <div class="occ-bar-fill" style="width: {{ $taux }}%"></div>
                        </div>
                        <div class="occ-legend">
                        <span class="occ-legend-item occ-occupe">
                            <span class="occ-dot"></span>
                            {{ $occupe }} occupée{{ $occupe > 1 ? 's' : '' }}
                        </span>
                            <span class="occ-legend-item occ-libre">
                            <span class="occ-dot"></span>
                            {{ $libre }} libre{{ $libre > 1 ? 's' : '' }}
                        </span>
                        </div>
                    </div>

                    {{-- Mini stats par bâtiment --}}
                    <div class="occ-batiments">
                        @foreach($propriete->batiments as $bat)
                            @php
                                $bTotal  = $bat->portes->where('is_actif', true)->count();
                                $bOccupe = $bat->portes->where('is_actif', true)->where('is_occupe', true)->count();
                                $bTaux   = $bTotal > 0 ? round($bOccupe / $bTotal * 100) : 0;
                            @endphp
                            <div class="occ-bat-row">
                                <span class="occ-bat-name">{{ $bat->nom }}</span>
                                <div class="occ-bat-bar">
                                    <div class="occ-bat-fill" style="width: {{ $bTaux }}%"></div>
                                </div>
                                <span class="occ-bat-pct">{{ $bOccupe }}/{{ $bTotal }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

        </div>

        {{-- ── Bâtiments & portes ── --}}
        <div class="u-mt-lg">
            <div class="section-title-row">
                <h3>Bâtiments & portes</h3>
                <span class="section-count">{{ $propriete->nbre_porte_total }} porte{{ $propriete->nbre_porte_total > 1 ? 's' : '' }}</span>
            </div>

            @foreach($propriete->batiments as $batiment)
                <div class="bat-show u-mb-md">

                    {{-- Header bâtiment --}}
                    <div class="bat-show-header">
                        <div class="bat-show-left">
                            <div class="bat-show-icon">🏢</div>
                            <div>
                                <strong class="bat-show-name">{{ $batiment->nom }}</strong>
                                @if($batiment->nbre_etages > 0)
                                    <span class="badge badge-neutral" style="margin-left:.4rem;">R+{{ $batiment->nbre_etages }}</span>
                                @endif
                                @if($batiment->description)
                                    <p class="bat-show-desc">{{ $batiment->description }}</p>
                                @endif
                            </div>
                        </div>
                        <div class="bat-show-right">
                    <span class="bat-pill bat-pill-libre">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                        {{ $batiment->portes->where('is_actif', true)->where('is_occupe', false)->count() }} libres
                    </span>
                            <span class="bat-pill bat-pill-occupe">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l3 3m0 0-3 3m3-3H2.25" /></svg>
                        {{ $batiment->portes->where('is_actif', true)->where('is_occupe', true)->count() }} occupées
                    </span>
                        </div>
                    </div>

                    {{-- Tableau portes --}}
                    <div class="table-shell">
                        <table class="data-table">
                            <thead>
                            <tr>
                                <th>N° Porte</th>
                                <th>Type</th>
                                <th>Étage</th>
                                <th>Surface</th>
                                <th>Loyer</th>
                                <th>Caution</th>
                                <th>Avance</th>
                                <th>Frais agence</th>
                                <th>Total entrée</th>
                                <th>Statut</th>
                                <th class="table-actions-col">Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($batiment->portes->where('is_actif', true) as $porte)
                                @php

                                    $mt_loyer     = $porte?->mt_loyer          ?? 0;
                                    $mt_caution   = $porte?->caution         ?? 0;
                                    $mt_avance    = $porte?->avance          ?? 0;
                                    $mt_agence    = $porte?->agence    ?? 0;
                                    $mt_cie       = $porte?->mt_caution_cie     ?? 0;
                                    $mt_sodeci    = $porte?->mt_caution_sodeci  ?? 0;
                                    $total_entree = $mt_loyer*($mt_caution + $mt_avance + $mt_agence) + $mt_cie + $mt_sodeci;
                                @endphp
                                <tr class="{{ $porte->is_occupe ? 'row-occupe' : '' }}">
                                    <td>
                                        <span class="porte-num">{{ $porte->numero_porte }}</span>
                                    </td>
                                    <td>
                                        <span class="badge badge-info badge-sm">{{ $porte->typePorte->libelle ?? '—' }}</span>
                                    </td>
                                    <td>{{ $porte->etage === 0 ? 'RDC' : 'É' . $porte->etage }}</td>
                                    <td>{{ $porte->superficie_m2 ? number_format($porte->superficie_m2, 0, ',', ' ') . ' m²' : '—' }}</td>
                                    <td class="td-money">
                                        @if($mt_loyer)
                                            <strong>{{ number_format($mt_loyer, 0, ',', ' ') }}</strong>
                                        @else
                                            <span class="td-empty">—</span>
                                        @endif
                                    </td>
                                    <td class="td-money">{{ $mt_caution ? number_format($mt_caution, 0, ',', ' ') : '—' }}</td>
                                    <td class="td-money">{{ $mt_avance  ? number_format($mt_avance,  0, ',', ' ') : '—' }}</td>
                                    <td class="td-money">{{ $mt_agence  ? number_format($mt_agence,  0, ',', ' ') : '—' }}</td>
                                    <td class="td-money td-total">
                                        @if($total_entree)
                                            {{ number_format($total_entree, 0, ',', ' ') }}
                                        @else
                                            <span class="td-empty">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($porte->is_occupe)
                                            <span class="status-dot status-occupe">Occupée</span>
                                        @else
                                            <span class="status-dot status-libre">Libre</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="table-actions">
                                            @if($porte->is_occupe)
                                                <form action="{{ route('agence.proprietes.liberer', $porte->porte_id) }}"
                                                      method="POST" class="u-inline-form">
                                                    @csrf @method('PATCH')
                                                    <button type="submit" class="action-btn success" title="Marquer libre">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15M12 9l3 3m0 0-3 3m3-3H2.25" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            @else
                                                <span class="action-placeholder">—</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11">
                                        <div class="empty-state">Aucune porte active dans ce bâtiment.</div>
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>

    </div>

    @push('styles')
        <style>
            /* ── Layout info + occupation ── */
            .show-layout {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 1rem;
            }
            .show-card {
                background: #fff;
                border: 1px solid #e5e7eb;
                border-radius: 12px;
                overflow: hidden;
            }
            .show-card-header {
                display: flex;
                align-items: center;
                gap: .45rem;
                padding: .7rem 1rem;
                background: #f9fafb;
                border-bottom: 1px solid #e5e7eb;
                font-size: .78rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: .05em;
                color: #6b7280;
            }
            .show-card-header svg { width: .9rem; height: .9rem; }
            .show-card-body { padding: 1rem; }

            /* info-list */
            .info-list { margin: 0; display: flex; flex-direction: column; gap: 0; }
            .info-row {
                display: flex;
                align-items: baseline;
                justify-content: space-between;
                padding: .5rem 0;
                border-bottom: 1px solid #f3f4f6;
                gap: 1rem;
            }
            .info-row:last-child { border-bottom: none; }
            .info-row-full { flex-direction: column; gap: .25rem; }
            .info-row dt {
                font-size: .75rem;
                font-weight: 600;
                color: #9ca3af;
                text-transform: uppercase;
                letter-spacing: .04em;
                white-space: nowrap;
                flex-shrink: 0;
            }
            .info-row dd { font-size: .875rem; color: #111827; margin: 0; text-align: right; }
            .info-row-full dd { text-align: left; }
            .desc-text { font-size: .83rem; color: #6b7280; line-height: 1.55; }

            /* Taux d'occupation */
            .occ-taux {
                font-size: 2.5rem;
                font-weight: 800;
                color: #111827;
                line-height: 1;
                margin-bottom: .75rem;
            }
            .occ-taux span { font-size: 1.2rem; color: #6b7280; }

            .occ-bar-wrap { margin-bottom: .75rem; }
            .occ-bar {
                height: 8px;
                border-radius: 999px;
                background: #f3f4f6;
                overflow: hidden;
                margin-bottom: .45rem;
            }
            .occ-bar-fill {
                height: 100%;
                border-radius: 999px;
                background: linear-gradient(90deg, #10b981, #059669);
                transition: width .4s ease;
            }
            .occ-legend { display: flex; gap: 1rem; }
            .occ-legend-item { display: flex; align-items: center; gap: .35rem; font-size: .75rem; color: #6b7280; }
            .occ-dot { width: .5rem; height: .5rem; border-radius: 50%; }
            .occ-occupe .occ-dot { background: #ef4444; }
            .occ-libre  .occ-dot { background: #10b981; }

            /* Mini bâtiments bars */
            .occ-batiments { display: flex; flex-direction: column; gap: .45rem; margin-top: .75rem; padding-top: .75rem; border-top: 1px solid #f3f4f6; }
            .occ-bat-row { display: flex; align-items: center; gap: .6rem; }
            .occ-bat-name { font-size: .72rem; color: #6b7280; white-space: nowrap; min-width: 80px; }
            .occ-bat-bar { flex: 1; height: 5px; border-radius: 999px; background: #f3f4f6; overflow: hidden; }
            .occ-bat-fill { height: 100%; border-radius: 999px; background: #2563eb; }
            .occ-bat-pct { font-size: .72rem; font-weight: 600; color: #374151; white-space: nowrap; }

            /* Section title */
            .section-title-row {
                display: flex;
                align-items: center;
                gap: .75rem;
                margin-bottom: 1rem;
            }
            .section-title-row h3 { margin: 0; font-size: 1rem; font-weight: 700; }
            .section-count {
                font-size: .72rem;
                font-weight: 600;
                color: #6b7280;
                background: #f3f4f6;
                padding: .15rem .5rem;
                border-radius: 999px;
            }

            /* Bâtiment show card */
            .bat-show {
                background: #fff;
                border: 1px solid #e5e7eb;
                border-radius: 12px;
                overflow: hidden;
            }
            .bat-show-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: .7rem 1rem;
                background: linear-gradient(to right, #f9fafb, #fff);
                border-bottom: 1px solid #e5e7eb;
                gap: .75rem;
            }
            .bat-show-left { display: flex; align-items: flex-start; gap: .6rem; }
            .bat-show-icon { font-size: 1.2rem; margin-top: 1px; }
            .bat-show-name { font-size: .9rem; font-weight: 700; color: #111827; }
            .bat-show-desc { font-size: .75rem; color: #9ca3af; margin: .15rem 0 0; }
            .bat-show-right { display: flex; align-items: center; gap: .4rem; flex-shrink: 0; }

            .bat-pill {
                display: inline-flex;
                align-items: center;
                gap: .3rem;
                padding: .25rem .6rem;
                border-radius: 999px;
                font-size: .72rem;
                font-weight: 600;
            }
            .bat-pill svg { width: .8rem; height: .8rem; }
            .bat-pill-libre  { background: #d1fae5; color: #059669; }
            .bat-pill-occupe { background: #fee2e2; color: #dc2626; }

            /* Table tweaks */
            .porte-num {
                font-weight: 700;
                font-size: .85rem;
                background: #f3f4f6;
                padding: .15rem .45rem;
                border-radius: 5px;
                color: #374151;
            }
            .badge-sm { font-size: .65rem !important; padding: .1rem .4rem !important; }
            .td-money { font-size: .83rem; color: #374151; font-variant-numeric: tabular-nums; }
            .td-total { font-weight: 600; color: #111827; }
            .td-empty { color: #d1d5db; }
            .row-occupe td { background: #fefce8; }

            /* Status dots */
            .status-dot {
                display: inline-flex;
                align-items: center;
                gap: .3rem;
                font-size: .72rem;
                font-weight: 600;
                padding: .2rem .5rem;
                border-radius: 999px;
            }
            .status-dot::before { content: ''; width: .45rem; height: .45rem; border-radius: 50%; flex-shrink: 0; }
            .status-libre  { background: #d1fae5; color: #059669; }
            .status-libre::before  { background: #10b981; }
            .status-occupe { background: #fee2e2; color: #dc2626; }
            .status-occupe::before { background: #ef4444; }

            .action-placeholder { color: #e5e7eb; font-size: .75rem; }

            @media (max-width: 860px) {
                .show-layout { grid-template-columns: 1fr; }
            }
        </style>
    @endpush

@endsection