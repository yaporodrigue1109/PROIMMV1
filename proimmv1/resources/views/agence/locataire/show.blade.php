@extends('agence.layouts.app')
@section('title', 'Locataire · ' . $locataire->name)

@section('content')
    <div class="page">

        <div class="page-header">
            <div class="page-header-copy">
                <div class="page-heading">
                    <a href="{{ route('agence.locataires.index') }}" class="back-link">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                        </svg>
                    </a>
                    <h2>{{ $locataire->name }}</h2>
                    <span class="badge badge-neutral">{{ $locataire->code }}</span>
                    @php $contrat = $locataire->contrats->where('is_active', true)->first(); @endphp
                    @if($contrat)
                        <span class="badge badge-success">Actif</span>
                    @else
                        <span class="badge badge-neutral">Sans contrat actif</span>
                    @endif
                </div>
                <p>{{ $locataire->tel1 }}{{ $locataire->tel2 ? ' · ' . $locataire->tel2 : '' }}{{ $locataire->email ? ' · ' . $locataire->email : '' }}</p>
            </div>
            <div class="page-actions">
                <a href="{{ route('agence.locataires.edit', $locataire->locataire_id) }}" class="btn btn-outline">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" />
                    </svg>
                    Modifier
                </a>
                @if($contrat)
                    <form action="{{ route('agence.locataires.resilier', $locataire->locataire_id) }}"
                          method="POST" class="u-inline-form"
                          onsubmit="return confirm('Résilier le contrat ?')">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn btn-danger-outline">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                            Résilier
                        </button>
                    </form>
                @endif
            </div>
        </div>

        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

        {{-- Layout 2 colonnes --}}
        <div class="show-layout u-mt-md">

            {{-- Fiche identité --}}
            <div class="show-card">
                <div class="show-card-header">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                    </svg>
                    <span>Identité</span>
                </div>
                <div class="show-card-body">
                    <dl class="info-list">
                        <div class="info-row"><dt>Nom</dt><dd>{{ $locataire->name }}</dd></div>
                        <div class="info-row"><dt>Code</dt><dd>{{ $locataire->code }}</dd></div>
                        <div class="info-row"><dt>Téléphone</dt><dd>{{ $locataire->tel1 }}{{ $locataire->tel2 ? ' / ' . $locataire->tel2 : '' }}</dd></div>
                        <div class="info-row"><dt>Email</dt><dd>{{ $locataire->email ?? '—' }}</dd></div>
                        <div class="info-row"><dt>Profession</dt><dd>{{ $locataire->profession ?? '—' }}</dd></div>
                        <div class="info-row"><dt>Nationalité</dt><dd>{{ $locataire->nationalite ?? '—' }}</dd></div>
                        <div class="info-row"><dt>Naissance</dt><dd>{{ $locataire->date_naissance?->format('d/m/Y') ?? '—' }}</dd></div>
                        @if($locataire->num_piece)
                            <div class="info-row"><dt>Pièce N°</dt><dd>{{ $locataire->num_piece }}</dd></div>
                        @endif
                    </dl>
                </div>
            </div>

            {{-- Contrat actif --}}
            <div class="show-card">
                <div class="show-card-header">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>
                    <span>Contrat actif</span>
                </div>
                <div class="show-card-body">
                    @if($contrat)
                        <dl class="info-list">
                            <div class="info-row"><dt>Proprietaire</dt><dd>{{ $contrat->proprietaire?->name ?? '—' }} - {{ $contrat->proprietaire?->tel1 ?? '—' }}</dd></div>
                            <div class="info-row"><dt>Lot</dt><dd>{{ $contrat->lot?->name ?? '—' }}</dd></div>
                            <div class="info-row"><dt>Propriété</dt><dd>{{ $contrat->propriete?->reference ?? '—' }}</dd></div>
                            <div class="info-row"><dt>Bâtiment</dt><dd>{{ $contrat->batiment?->name ?? '—' }}</dd></div>
                            <div class="info-row"><dt>Porte</dt><dd><strong>{{ $contrat->porte?->numero_porte ?? '—' }}</strong></dd></div>
                            <div class="info-row"><dt>Loyer mensuel</dt><dd><strong>{{ number_format($contrat->porte?->mt_loyer ?? 0, 0, ',', ' ') }} F</strong></dd></div>
                            <div class="info-row"><dt>Caution</dt><dd>{{ $contrat->porte?->caution }} mois</dd></div>
                            <div class="info-row"><dt>Avance</dt><dd>{{ $contrat->porte?->avance }} mois</dd></div>
                            @if($contrat->date_debut_bail)
                                <div class="info-row"><dt>Début bail</dt><dd>{{ $contrat->date_debut_bail->format('d/m/Y') }}</dd></div>
                            @endif
                        </dl>
                    @else
                        <div class="empty-state">Aucun contrat actif.</div>
                    @endif
                </div>
            </div>

        </div>

        {{-- Historique loyers --}}
        <div class="u-mt-lg">
            <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1rem;">
                <h3 style="margin:0;font-size:1rem;font-weight:700;">Historique des loyers</h3>
            </div>
            <div class="table-shell">
                <table class="data-table">
                    <thead>
                    <tr>
                        <th>Période</th>
                        <th>À payer</th>
                        <th>Payé</th>
                        <th>Restant</th>
                        <th>Arriéré</th>
                        <th>Statut</th>
                        <th>Date paiement</th>
                        <th>Mode paiement</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($locataire->loyers->take(12) as $loyer)
                        <tr>
                            <td><strong>{{ $loyer->periode }}</strong></td>
                            <td>{{ number_format($loyer->montant_a_payer, 0, ',', ' ') }} F</td>
                            <td>{{ number_format($loyer->montant_paye, 0, ',', ' ') }} F</td>
                            <td>
                                @if($loyer->montant_restant > 0)
                                    <span style="color:#ef4444;font-weight:600;">{{ number_format($loyer->montant_restant, 0, ',', ' ') }} F</span>
                                @else
                                    <span style="color:#10b981;">0 F</span>
                                @endif
                            </td>
                            <td>
                                @if($loyer->arriere_actuel > 0)
                                    <span class="badge badge-danger">{{ number_format($loyer->arriere_actuel, 0, ',', ' ') }} F</span>
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                @if($loyer->statut === 'Paiement total')
                                    <span class="badge badge-success">Paiement total</span>
                                @elseif($loyer->statut === 'Paiement partiel')
                                    <span class="badge badge-warning">Paiement partiel</span>

                                    @elseif($loyer->statut === 'Paiement en cours')
                                        <span class="badge badge-primary">Paiement en cours</span>
                                    @else
                                    <span class="badge badge-danger">Paiement en retard</span>
                                @endif
                            </td>
                            <td>{{ $loyer->date_paiement?->format('d/m/Y') ?? '—' }}</td>
                            <td><span class="badge badge-neutral">{{ ucfirst(str_replace('_', ' ', $tx->modePaiement?->name ?? 'espèces')) }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><div class="empty-state">Aucun loyer enregistré.</div></td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Dernières transactions --}}
        <div class="u-mt-lg">
            <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1rem;">
                <h3 style="margin:0;font-size:1rem;font-weight:700;">Dernières transactions</h3>
            </div>
            <div class="table-shell">
                <table class="data-table">
                    <thead>
                    <tr>
                        <th>Date</th>
                        <th>Période</th>
                        <th>Montant versé</th>
                        <th>Montant loyer</th>
                        <th>Montant arriéré</th>
                        <th>Mode Paiement</th>
                        <th>Est Reversé</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($locataire->transactions as $tx)
                        <tr>
                            <td>{{ $tx->date_transaction->format('d/m/Y H:i') }}</td>
                            <td>
                                @php $mois = json_decode($tx->mois_payer,true); @endphp
                                @foreach($mois as $moisItem)
                                    {{ $moisItem }}@if(!$loop->last), @endif
                                @endforeach
                            </td>
                            <td><strong>{{ number_format($tx->montant_global_verser, 0, ',', ' ') }} F</strong></td>
                            <td>{{ number_format($tx->montant_loyer_payer, 0, ',', ' ') }} F</td>
                            <td>
                                @if($tx->montant_arriere_actuel > 0)
                                    <span style="color:#059669;">{{ number_format($tx->montant_arriere_actuel, 0, ',', ' ') }} F</span>
                                @else — @endif
                            </td>
                            <td><span class="badge badge-neutral">{{ ucfirst(str_replace('_', ' ', $tx->modePaiement?->name ?? 'espèces')) }}</span></td>
                            <td>{{ $tx->is_reversement==1 ? 'Oui' : 'Non' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><div class="empty-state">Aucune transaction.</div></td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    @push('styles')
        <style>
            .show-layout { display:grid; grid-template-columns:1fr 1fr; gap:1rem; }
            .show-card { background:#fff; border:1px solid #e5e7eb; border-radius:12px; overflow:hidden; }
            .show-card-header { display:flex; align-items:center; gap:.45rem; padding:.7rem 1rem; background:#f9fafb; border-bottom:1px solid #e5e7eb; font-size:.78rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:#6b7280; }
            .show-card-header svg { width:.9rem; height:.9rem; }
            .show-card-body { padding:1rem; }
            .info-list { margin:0; display:flex; flex-direction:column; }
            .info-row { display:flex; align-items:baseline; justify-content:space-between; padding:.45rem 0; border-bottom:1px solid #f3f4f6; gap:1rem; }
            .info-row:last-child { border-bottom:none; }
            .info-row dt { font-size:.75rem; font-weight:600; color:#9ca3af; text-transform:uppercase; letter-spacing:.04em; white-space:nowrap; flex-shrink:0; }
            .info-row dd { font-size:.875rem; color:#111827; margin:0; text-align:right; }
            .btn-danger-outline { background:transparent; border:1px solid #ef4444; color:#ef4444; }
            .btn-danger-outline:hover { background:#fee2e2; }
            .alert { padding:1rem; border-radius:8px; margin-bottom:1.5rem; }
            .alert-success { background:#d1fae5; border:1px solid #10b981; color:#065f46; }
            @media (max-width:860px) { .show-layout { grid-template-columns:1fr; } }
        </style>
    @endpush
@endsection



















{{--@extends('agence.layouts.app')--}}

{{--@section('title', 'Factures du locataire')--}}

{{--@section('content')--}}
{{--    @php--}}
{{--        // Données simulées--}}
{{--        $locataire = [--}}
{{--            'id' => 4,--}}
{{--            'nom' => 'KOUAKOU KOUAKOU ROSTAND',--}}
{{--            'telephone' => '0101377709',--}}
{{--            'propriete' => 'Appartement 2 piece (AYAMA PK 18 EXTENSION)',--}}
{{--            'cours_porte' => 'Cours 1 / porte 10'--}}
{{--        ];--}}

{{--        $factures = [--}}
{{--            ['id' => 1, 'numero' => '1834449', 'montant' => 80000, 'periode' => 'Mai-2026', 'mode' => 'WAVE', 'date_paiement' => '05/05/2026'],--}}
{{--            ['id' => 2, 'numero' => '5003327', 'montant' => 80000, 'periode' => 'Avril-2026', 'mode' => 'WAVE', 'date_paiement' => '13/04/2026'],--}}
{{--            ['id' => 3, 'numero' => '2859966', 'montant' => 80000, 'periode' => 'Mars-2026', 'mode' => 'WAVE', 'date_paiement' => '14/03/2026'],--}}
{{--            ['id' => 4, 'numero' => '7538026', 'montant' => 80000, 'periode' => 'Février-2026', 'mode' => 'WAVE', 'date_paiement' => '05/02/2026'],--}}
{{--            ['id' => 5, 'numero' => '3162470', 'montant' => 80000, 'periode' => 'Janvier-2026', 'mode' => 'WAVE', 'date_paiement' => '08/01/2026'],--}}
{{--            ['id' => 6, 'numero' => '5818246', 'montant' => 80000, 'periode' => 'Décembre-2025', 'mode' => 'WAVE', 'date_paiement' => '03/12/2025'],--}}
{{--            ['id' => 7, 'numero' => '9330276', 'montant' => 400000, 'periode' => 'Septembre-2025, Octobre-2025', 'mode' => 'Espèces', 'date_paiement' => '23/09/2025'],--}}
{{--        ];--}}

{{--        $totalFactures = count($factures);--}}
{{--        $montantTotal = array_sum(array_column($factures, 'montant'));--}}
{{--        $derniereFacture = $factures[0]['montant'] ?? 0;--}}

{{--        $formatMoney = fn($amount) => number_format((float) $amount, 0, ',', ' ') . ' FCFA';--}}
{{--    @endphp--}}

{{--    <div class="page">--}}
{{--        --}}{{-- En-tête avec retour --}}
{{--        <div class="page-header">--}}
{{--            <div class="page-header-copy">--}}
{{--                <div class="page-heading">--}}

{{--                    <div>--}}
{{--                        <h2>Factures de loyers</h2>--}}
{{--                        <p class="text-muted">--}}
{{--                            {{ $locataire['nom'] }} • {{ $locataire['propriete'] }} • {{ $locataire['cours_porte'] }}--}}
{{--                        </p>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}

{{--            <div class="page-actions">--}}
{{--                <a href="{{ route('agence.locataire.index') }}" class="btn btn-outline">--}}
{{--                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">--}}
{{--                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />--}}
{{--                    </svg>--}}
{{--                    Retour à la liste--}}
{{--                </a>--}}
{{--                <button class="btn btn-primary" id="exportAllBtn" data-url="{{ route('agence.locataire.factures.export-all', ['locataireId' => $locataire['id']]) }}">--}}
{{--                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">--}}
{{--                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />--}}
{{--                    </svg>--}}
{{--                    Exporter tout--}}
{{--                </button>--}}
{{--            </div>--}}
{{--        </div>--}}

{{--        --}}{{-- Cartes de statistiques --}}
{{--        <div class="stats-grid">--}}
{{--            <article class="stat-card">--}}
{{--                <span>Total Factures</span>--}}
{{--                <strong>{{ $totalFactures }}</strong>--}}

{{--            </article>--}}
{{--            <article class="stat-card">--}}
{{--                <span>Montant Total</span>--}}
{{--                <strong class="is-success">{{ $formatMoney($montantTotal) }}</strong>--}}
{{--                <small>toutes factures confondues</small>--}}
{{--            </article>--}}
{{--            <article class="stat-card">--}}
{{--                <span>Dernière Facture</span>--}}
{{--                <strong class="is-info">{{ $formatMoney($derniereFacture) }}</strong>--}}
{{--                <small>mai 2026</small>--}}
{{--            </article>--}}
{{--            <article class="stat-card">--}}
{{--                <span>Moyenne mensuelle</span>--}}
{{--                <strong>{{ $formatMoney(round($montantTotal / $totalFactures)) }}</strong>--}}
{{--                <small>sur {{ $totalFactures }} mois</small>--}}
{{--            </article>--}}
{{--        </div>--}}


{{--        <nav class="rp-topbar" aria-label="Sections des rapports">--}}
{{--         --}}

{{--            <div class="rp-topbar-right">--}}
{{--                <button class="rp-export-btn csv" id="btn-pdf" type="button">--}}
{{--                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">--}}
{{--                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5zm6-10.125a1.875 1.875 0 11-3.75 0 1.875 1.875 0 013.75 0zm1.294 6.336a6.721 6.721 0 01-3.17.789 6.721 6.721 0 01-3.168-.789 3.376 3.376 0 016.338 0z"/>--}}
{{--                    </svg>                    Procuration--}}
{{--                </button>--}}


{{--                <button class="rp-export-btn csv" data-tab="avis-locataires" type="button">--}}
{{--                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">--}}
{{--                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.5c0 1.518 1.05 2.79 2.47 3.158.979.255 1.985.339 3.03.47.67.084 1.34.189 2 .315.902.174 1.782.468 2.59.878.675.342 1.26.825 1.71 1.41.45.585.75 1.275.87 2.016a4.467 4.467 0 00-.75-3.922 4.46 4.46 0 00-2.42-1.758 4.483 4.483 0 00-1.476-.237h-3.75A4.493 4.493 0 014.5 12.75z"/>--}}
{{--                    </svg>--}}
{{--                    Avis aux locataires--}}
{{--                </button>--}}
{{--                <button class="rp-export-btn csv" data-tab="contrat-bail" type="button">--}}
{{--                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">--}}
{{--                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>--}}
{{--                    </svg>--}}
{{--                    Contrat de bail à usage d'habitation--}}
{{--                </button>--}}
{{--                <button class="rp-export-btn csv" data-tab="fiche-renseignement" type="button">--}}
{{--                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">--}}
{{--                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5zm6-10.125a1.875 1.875 0 11-3.75 0 1.875 1.875 0 013.75 0zm1.294 6.336a6.721 6.721 0 01-3.17.789 6.721 6.721 0 01-3.168-.789 3.376 3.376 0 016.338 0z"/>--}}
{{--                    </svg>--}}
{{--                    Fiche de renseignement--}}
{{--                </button>--}}
{{--                <button class="rp-export-btn csv" data-tab="etat-lieux" type="button">--}}
{{--                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">--}}
{{--                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z"/>--}}
{{--                    </svg>--}}
{{--                    Etat des lieux--}}
{{--                </button>--}}

{{--            </div>--}}

{{--        </nav>--}}

{{--        --}}{{-- Tableau des factures --}}
{{--        <div class="table-workspace">--}}
{{--            <div class="card">--}}
{{--                <div class="table-toolbar">--}}
{{--                    <div>--}}
{{--                        <h3>Historique des factures</h3>--}}
{{--                        <p class="text-muted" style="font-size: 0.78rem; margin-top: 0.25rem;">--}}
{{--                            Les différentes factures du locataire--}}
{{--                        </p>--}}
{{--                    </div>--}}

{{--                    <div class="table-toolbar-actions">--}}
{{--                        <label class="search-field" for="invoice-search">--}}
{{--                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">--}}
{{--                                <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd"/>--}}
{{--                            </svg>--}}
{{--                            <input id="invoice-search" type="search" placeholder="Rechercher une facture...">--}}
{{--                        </label>--}}
{{--                    </div>--}}
{{--                </div>--}}

{{--                <div class="table-shell">--}}
{{--                    <table class="data-table" id="invoicesTable">--}}
{{--                        <thead>--}}
{{--                        <tr>--}}
{{--                            <th>#</th>--}}
{{--                            <th>Numéro</th>--}}
{{--                            <th>Montant</th>--}}
{{--                            <th>Période Payée</th>--}}
{{--                            <th>Mode Paiement</th>--}}
{{--                            <th>Date Paiement</th>--}}
{{--                            <th class="table-actions-col">Actions</th>--}}
{{--                        </tr>--}}
{{--                        </thead>--}}
{{--                        <tbody>--}}
{{--                        @foreach($factures as $index => $facture)--}}
{{--                            <tr>--}}
{{--                                <td>{{ $index + 1 }}</td>--}}
{{--                                <td>--}}
{{--                                    <strong>{{ $facture['numero'] }}</strong>--}}
{{--                                </td>--}}
{{--                                <td class="text-right">--}}
{{--                                    <strong class="is-success">{{ $formatMoney($facture['montant']) }}</strong>--}}
{{--                                </td>--}}
{{--                                <td>{{ $facture['periode'] }}</td>--}}
{{--                                <td>--}}
{{--                                    <span class="badge {{ $facture['mode'] == 'WAVE' ? 'badge-info' : 'badge-primary' }}">--}}
{{--                                        {{ $facture['mode'] }}--}}
{{--                                    </span>--}}
{{--                                </td>--}}
{{--                                <td>{{ $facture['date_paiement'] }}</td>--}}
{{--                                <td>--}}
{{--                                    <div class="table-actions">--}}
{{--                                        --}}{{-- Bouton Voir le détail --}}
{{--                                        <button class="action-btn info view-invoice"--}}
{{--                                                data-id="{{ $facture['id'] }}"--}}
{{--                                                data-numero="{{ $facture['numero'] }}"--}}
{{--                                                data-montant="{{ $facture['montant'] }}"--}}
{{--                                                data-periode="{{ $facture['periode'] }}"--}}
{{--                                                data-mode="{{ $facture['mode'] }}"--}}
{{--                                                data-date="{{ $facture['date_paiement'] }}"--}}
{{--                                                data-pdf-url="{{ route('agence.locataire.factures.export-pdf', ['locataireId' => $locataire['id'], 'factureId' => $facture['id']]) }}"--}}
{{--                                                data-excel-url="{{ route('agence.locataire.factures.export-excel', ['locataireId' => $locataire['id'], 'factureId' => $facture['id']]) }}"--}}
{{--                                                title="Voir le détail">--}}
{{--                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">--}}
{{--                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />--}}
{{--                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />--}}
{{--                                            </svg>--}}
{{--                                        </button>--}}
{{--                                    </div>--}}
{{--                                </td>--}}
{{--                            </tr>--}}
{{--                        @endforeach--}}
{{--                        </tbody>--}}
{{--                    </table>--}}
{{--                </div>--}}

{{--                --}}{{-- Pagination --}}
{{--                <div class="table-toolbar">--}}
{{--                    <div class="pagination-info">--}}
{{--                        Affichage de <strong>1</strong> à <strong>{{ $totalFactures }}</strong> sur <strong>{{ $totalFactures }}</strong> factures--}}
{{--                    </div>--}}
{{--                    <div class="pagination">--}}
{{--                        <span class="pagination-item disabled">--}}
{{--                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">--}}
{{--                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />--}}
{{--                            </svg>--}}
{{--                            Précédent--}}
{{--                        </span>--}}
{{--                        <span class="pagination-item active">1</span>--}}
{{--                        <span class="pagination-item disabled">--}}
{{--                            Suivant--}}
{{--                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">--}}
{{--                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />--}}
{{--                            </svg>--}}
{{--                        </span>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}

{{--    --}}{{-- Modal de détail de facture --}}
{{--    <div id="invoiceModal" class="modal" style="display: none;">--}}
{{--        <div class="modal-overlay"></div>--}}
{{--        <div class="modal-container">--}}
{{--            <div class="modal-header">--}}
{{--                <h3>Détail de la facture</h3>--}}
{{--                <button class="modal-close" id="closeModalBtn">--}}
{{--                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">--}}
{{--                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />--}}
{{--                    </svg>--}}
{{--                </button>--}}
{{--            </div>--}}
{{--            <div class="modal-body">--}}
{{--                <div class="invoice-detail-card">--}}
{{--                    <div class="invoice-header">--}}
{{--                        <div class="invoice-logo">--}}
{{--                            <div class="sidebar-logo">--}}
{{--                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">--}}
{{--                                    <path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>--}}
{{--                                </svg>--}}
{{--                            </div>--}}
{{--                            <h2>FACTURE DE LOYER</h2>--}}
{{--                        </div>--}}
{{--                        <div class="invoice-number">--}}
{{--                            <strong>N° : <span id="invoiceNumero"></span></strong>--}}
{{--                        </div>--}}
{{--                    </div>--}}

{{--                    <div class="invoice-divider"></div>--}}

{{--                    <div class="invoice-info-grid">--}}
{{--                        <div class="info-group">--}}
{{--                            <span class="info-label">LOCATAIRE</span>--}}
{{--                            <strong>{{ $locataire['nom'] }}</strong>--}}
{{--                            <span>{{ $locataire['telephone'] }}</span>--}}
{{--                        </div>--}}
{{--                        <div class="info-group">--}}
{{--                            <span class="info-label">BIEN LOUE</span>--}}
{{--                            <strong>{{ $locataire['propriete'] }}</strong>--}}
{{--                            <span>{{ $locataire['cours_porte'] }}</span>--}}
{{--                        </div>--}}
{{--                    </div>--}}

{{--                    <div class="invoice-details">--}}
{{--                        <table class="invoice-table">--}}
{{--                            <tr>--}}
{{--                                <td>Période concernée</td>--}}
{{--                                <td class="text-right"><strong id="invoicePeriode"></strong></td>--}}
{{--                            </tr>--}}
{{--                            <tr>--}}
{{--                                <td>Date de paiement</td>--}}
{{--                                <td class="text-right"><strong id="invoiceDate"></strong></td>--}}
{{--                            </tr>--}}
{{--                            <tr>--}}
{{--                                <td>Mode de paiement</td>--}}
{{--                                <td class="text-right"><span class="badge" id="invoiceMode"></span></td>--}}
{{--                            </tr>--}}
{{--                            <tr class="total-row">--}}
{{--                                <td>Montant total</td>--}}
{{--                                <td class="text-right"><strong id="invoiceMontant" class="invoice-total"></strong></td>--}}
{{--                            </tr>--}}
{{--                        </table>--}}
{{--                    </div>--}}

{{--                    <div class="invoice-footer">--}}
{{--                        <div class="payment-confirmation">--}}
{{--                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">--}}
{{--                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />--}}
{{--                            </svg>--}}
{{--                            <span>Paiement confirmé</span>--}}
{{--                        </div>--}}
{{--                        <div class="invoice-actions">--}}
{{--                            <button class="btn btn-outline" id="exportModalPdfBtn">--}}
{{--                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">--}}
{{--                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />--}}
{{--                                </svg>--}}
{{--                                Exporter PDF--}}
{{--                            </button>--}}
{{--                            <button class="btn btn-primary" id="printInvoiceBtn">--}}
{{--                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">--}}
{{--                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2z" />--}}
{{--                                </svg>--}}
{{--                                Imprimer--}}
{{--                            </button>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}

{{--    <style>--}}
{{--        /* Modal styles */--}}
{{--        .modal {--}}
{{--            position: fixed;--}}
{{--            inset: 0;--}}
{{--            background: rgba(0, 0, 0, 0.6);--}}
{{--            display: flex;--}}
{{--            align-items: center;--}}
{{--            justify-content: center;--}}
{{--            z-index: 1000;--}}
{{--            backdrop-filter: blur(4px);--}}
{{--        }--}}

{{--        .modal-overlay {--}}
{{--            position: absolute;--}}
{{--            inset: 0;--}}
{{--        }--}}

{{--        .modal-container {--}}
{{--            position: relative;--}}
{{--            width: 90%;--}}
{{--            max-width: 680px;--}}
{{--            max-height: 90vh;--}}
{{--            overflow-y: auto;--}}
{{--            background: var(--card);--}}
{{--            border-radius: 1.5rem;--}}
{{--            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);--}}
{{--            animation: modalIn 0.25s ease-out;--}}
{{--        }--}}

{{--        @keyframes modalIn {--}}
{{--            from {--}}
{{--                opacity: 0;--}}
{{--                transform: scale(0.95) translateY(-10px);--}}
{{--            }--}}
{{--            to {--}}
{{--                opacity: 1;--}}
{{--                transform: scale(1) translateY(0);--}}
{{--            }--}}
{{--        }--}}

{{--        .modal-header {--}}
{{--            display: flex;--}}
{{--            align-items: center;--}}
{{--            justify-content: space-between;--}}
{{--            padding: 1.25rem 1.5rem;--}}
{{--            border-bottom: 1px solid var(--border);--}}
{{--        }--}}

{{--        .modal-header h3 {--}}
{{--            font-size: 1.1rem;--}}
{{--            font-weight: 700;--}}
{{--            margin: 0;--}}
{{--        }--}}

{{--        .modal-close {--}}
{{--            width: 36px;--}}
{{--            height: 36px;--}}
{{--            border-radius: 0.75rem;--}}
{{--            display: flex;--}}
{{--            align-items: center;--}}
{{--            justify-content: center;--}}
{{--            background: transparent;--}}
{{--            border: none;--}}
{{--            cursor: pointer;--}}
{{--            color: var(--muted-foreground);--}}
{{--            transition: all 0.2s;--}}
{{--        }--}}

{{--        .modal-close:hover {--}}
{{--            background: var(--muted);--}}
{{--            color: var(--foreground);--}}
{{--        }--}}

{{--        .modal-body {--}}
{{--            padding: 1.5rem;--}}
{{--        }--}}

{{--        /* Invoice detail card */--}}
{{--        .invoice-detail-card {--}}
{{--            background: var(--card);--}}
{{--            border-radius: 1rem;--}}
{{--        }--}}

{{--        .invoice-header {--}}
{{--            display: flex;--}}
{{--            justify-content: space-between;--}}
{{--            align-items: flex-start;--}}
{{--            margin-bottom: 1rem;--}}
{{--        }--}}

{{--        .invoice-logo {--}}
{{--            display: flex;--}}
{{--            align-items: center;--}}
{{--            gap: 0.75rem;--}}
{{--        }--}}

{{--        .invoice-logo .sidebar-logo {--}}
{{--            width: 40px;--}}
{{--            height: 40px;--}}
{{--            background: linear-gradient(135deg, var(--accent-blue), var(--accent-blue-soft));--}}
{{--            border-radius: 12px;--}}
{{--            display: flex;--}}
{{--            align-items: center;--}}
{{--            justify-content: center;--}}
{{--        }--}}

{{--        .invoice-logo .sidebar-logo svg {--}}
{{--            width: 24px;--}}
{{--            height: 24px;--}}
{{--            color: white;--}}
{{--        }--}}

{{--        .invoice-logo h2 {--}}
{{--            font-size: 1rem;--}}
{{--            font-weight: 700;--}}
{{--            letter-spacing: 0.05em;--}}
{{--            margin: 0;--}}
{{--        }--}}

{{--        .invoice-number strong {--}}
{{--            font-size: 0.9rem;--}}
{{--            color: var(--primary);--}}
{{--        }--}}

{{--        .invoice-divider {--}}
{{--            height: 1px;--}}
{{--            background: var(--border);--}}
{{--            margin: 1rem 0;--}}
{{--        }--}}

{{--        .invoice-info-grid {--}}
{{--            display: grid;--}}
{{--            grid-template-columns: 1fr 1fr;--}}
{{--            gap: 1rem;--}}
{{--            margin-bottom: 1.5rem;--}}
{{--        }--}}

{{--        .info-group {--}}
{{--            display: flex;--}}
{{--            flex-direction: column;--}}
{{--            gap: 0.25rem;--}}
{{--        }--}}

{{--        .info-label {--}}
{{--            font-size: 0.7rem;--}}
{{--            font-weight: 700;--}}
{{--            text-transform: uppercase;--}}
{{--            letter-spacing: 0.08em;--}}
{{--            color: var(--muted-foreground);--}}
{{--        }--}}

{{--        .info-group strong {--}}
{{--            font-size: 0.95rem;--}}
{{--        }--}}

{{--        .info-group span {--}}
{{--            font-size: 0.85rem;--}}
{{--            color: var(--muted-foreground);--}}
{{--        }--}}

{{--        .invoice-details {--}}
{{--            background: var(--surface-subtle);--}}
{{--            border-radius: 1rem;--}}
{{--            padding: 1rem;--}}
{{--            margin-bottom: 1.5rem;--}}
{{--        }--}}

{{--        .invoice-table {--}}
{{--            width: 100%;--}}
{{--            border-collapse: collapse;--}}
{{--        }--}}

{{--        .invoice-table tr td {--}}
{{--            padding: 0.75rem 0;--}}
{{--            border-bottom: 1px dashed var(--border);--}}
{{--        }--}}

{{--        .invoice-table tr:last-child td {--}}
{{--            border-bottom: none;--}}
{{--        }--}}

{{--        .invoice-table td:first-child {--}}
{{--            color: var(--muted-foreground);--}}
{{--        }--}}

{{--        .total-row td {--}}
{{--            padding-top: 1rem;--}}
{{--            font-weight: 700;--}}
{{--        }--}}

{{--        .invoice-total {--}}
{{--            font-size: 1.25rem;--}}
{{--            color: var(--primary);--}}
{{--        }--}}

{{--        .invoice-footer {--}}
{{--            display: flex;--}}
{{--            justify-content: space-between;--}}
{{--            align-items: center;--}}
{{--            flex-wrap: wrap;--}}
{{--            gap: 1rem;--}}
{{--            padding-top: 1rem;--}}
{{--            border-top: 1px solid var(--border);--}}
{{--        }--}}

{{--        .payment-confirmation {--}}
{{--            display: flex;--}}
{{--            align-items: center;--}}
{{--            gap: 0.5rem;--}}
{{--            color: #16a34a;--}}
{{--        }--}}

{{--        .payment-confirmation svg {--}}
{{--            width: 20px;--}}
{{--            height: 20px;--}}
{{--        }--}}

{{--        .invoice-actions {--}}
{{--            display: flex;--}}
{{--            gap: 0.75rem;--}}
{{--        }--}}

{{--        /* Animation pour le toast */--}}
{{--        .toast-notification {--}}
{{--            position: fixed;--}}
{{--            bottom: 2rem;--}}
{{--            right: 2rem;--}}
{{--            padding: 0.75rem 1.25rem;--}}
{{--            background: var(--primary);--}}
{{--            color: white;--}}
{{--            border-radius: 0.75rem;--}}
{{--            font-size: 0.875rem;--}}
{{--            font-weight: 500;--}}
{{--            z-index: 1100;--}}
{{--            animation: slideIn 0.3s ease-out;--}}
{{--            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);--}}
{{--        }--}}

{{--        @keyframes slideIn {--}}
{{--            from {--}}
{{--                opacity: 0;--}}
{{--                transform: translateX(100%);--}}
{{--            }--}}
{{--            to {--}}
{{--                opacity: 1;--}}
{{--                transform: translateX(0);--}}
{{--            }--}}
{{--        }--}}

{{--        /* Recherche */--}}
{{--        .no-results {--}}
{{--            text-align: center;--}}
{{--            padding: 2rem;--}}
{{--            color: var(--muted-foreground);--}}
{{--        }--}}
{{--    </style>--}}

{{--    <script>--}}
{{--        document.addEventListener('DOMContentLoaded', function() {--}}
{{--            // Variables pour la facture courante--}}
{{--            let currentInvoice = null;--}}

{{--            // Éléments du DOM--}}
{{--            const modal = document.getElementById('invoiceModal');--}}
{{--            const closeModalBtn = document.getElementById('closeModalBtn');--}}
{{--            const exportModalPdfBtn = document.getElementById('exportModalPdfBtn');--}}
{{--            const printInvoiceBtn = document.getElementById('printInvoiceBtn');--}}

{{--            // Éléments d'affichage dans la modal--}}
{{--            const invoiceNumero = document.getElementById('invoiceNumero');--}}
{{--            const invoicePeriode = document.getElementById('invoicePeriode');--}}
{{--            const invoiceDate = document.getElementById('invoiceDate');--}}
{{--            const invoiceMode = document.getElementById('invoiceMode');--}}
{{--            const invoiceMontant = document.getElementById('invoiceMontant');--}}

{{--            const formatMoney = (amount) => new Intl.NumberFormat('fr-FR').format(amount) + ' FCFA';--}}

{{--            // Fonction pour afficher la modal--}}
{{--            function showModal(invoice) {--}}
{{--                currentInvoice = invoice;--}}
{{--                invoiceNumero.textContent = invoice.numero;--}}
{{--                invoicePeriode.textContent = invoice.periode;--}}
{{--                invoiceDate.textContent = invoice.date;--}}
{{--                invoiceMode.textContent = invoice.mode;--}}
{{--                invoiceMode.className = 'badge ' + (invoice.mode === 'WAVE' ? 'badge-info' : 'badge-primary');--}}
{{--                invoiceMontant.textContent = formatMoney(invoice.montant);--}}
{{--                modal.style.display = 'flex';--}}
{{--            }--}}

{{--            // Fermer la modal--}}
{{--            function closeModal() {--}}
{{--                modal.style.display = 'none';--}}
{{--                currentInvoice = null;--}}
{{--            }--}}

{{--            // Afficher un toast de notification--}}
{{--            function showToast(message, type = 'success') {--}}
{{--                const toast = document.createElement('div');--}}
{{--                toast.className = 'toast-notification';--}}
{{--                toast.style.background = type === 'success' ? '#16a34a' : '#dc2626';--}}
{{--                toast.textContent = message;--}}
{{--                document.body.appendChild(toast);--}}
{{--                setTimeout(() => toast.remove(), 3000);--}}
{{--            }--}}

{{--            // Action d'export PDF (simulée)--}}
{{--            function exportPDF(invoice, isAll = false) {--}}
{{--                if (isAll) {--}}
{{--                    showToast('Export de toutes les factures en cours...');--}}
{{--                    setTimeout(() => showToast('Export terminé ! Téléchargement en cours'), 1500);--}}
{{--                } else {--}}
{{--                    showToast(`Export de la facture ${invoice.numero} en PDF...`);--}}
{{--                    setTimeout(() => showToast(`Facture ${invoice.numero} exportée avec succès !`), 1500);--}}
{{--                }--}}
{{--            }--}}

{{--            // Action d'export Excel (simulée)--}}
{{--            function exportExcel(invoice, isAll = false) {--}}
{{--                if (isAll) {--}}
{{--                    showToast('Export Excel de toutes les factures en cours...');--}}
{{--                    setTimeout(() => showToast('Export Excel terminé !'), 1500);--}}
{{--                } else {--}}
{{--                    showToast(`Export Excel de la facture ${invoice.numero} en cours...`);--}}
{{--                    setTimeout(() => showToast(`Facture ${invoice.numero} exportée en Excel !`), 1500);--}}
{{--                }--}}
{{--            }--}}

{{--            // Action d'impression--}}
{{--            function printInvoice() {--}}
{{--                const printContent = document.querySelector('.invoice-detail-card').cloneNode(true);--}}
{{--                const printWindow = window.open('', '_blank');--}}
{{--                printWindow.document.write(`--}}
{{--                    <html>--}}
{{--                        <head>--}}
{{--                            <title>Facture ${currentInvoice.numero}</title>--}}
{{--                            <style>--}}
{{--                                body { font-family: 'Outfit', sans-serif; padding: 2rem; }--}}
{{--                                .invoice-detail-card { max-width: 800px; margin: 0 auto; }--}}
{{--                                ${document.querySelector('style').innerHTML}--}}
{{--                            </style>--}}
{{--                        </head>--}}
{{--                        <body>${printContent.outerHTML}</body>--}}
{{--                    </html>--}}
{{--                `);--}}
{{--                printWindow.document.close();--}}
{{--                printWindow.print();--}}
{{--                showToast('Envoi vers l\'imprimante...');--}}
{{--            }--}}

{{--            // Écouteurs d'événements pour les boutons Voir--}}
{{--            document.querySelectorAll('.view-invoice').forEach(btn => {--}}
{{--                btn.addEventListener('click', function() {--}}
{{--                    const invoice = {--}}
{{--                        id: this.dataset.id,--}}
{{--                        numero: this.dataset.numero,--}}
{{--                        montant: parseInt(this.dataset.montant),--}}
{{--                        periode: this.dataset.periode,--}}
{{--                        mode: this.dataset.mode,--}}
{{--                        date: this.dataset.date--}}
{{--                    };--}}
{{--                    showModal(invoice);--}}
{{--                });--}}
{{--            });--}}

{{--            // Écouteurs pour exports PDF individuels--}}
{{--            document.querySelectorAll('.export-pdf').forEach(btn => {--}}
{{--                btn.addEventListener('click', function(e) {--}}
{{--                    e.stopPropagation();--}}
{{--                    const invoice = {--}}
{{--                        id: this.dataset.id,--}}
{{--                        numero: this.dataset.numero,--}}
{{--                        montant: parseInt(this.closest('tr').querySelector('td:nth-child(3)').textContent.replace(/[^\d]/g, '')),--}}
{{--                        periode: this.closest('tr').querySelector('td:nth-child(4)').textContent,--}}
{{--                        mode: this.closest('tr').querySelector('td:nth-child(5)').textContent.trim(),--}}
{{--                        date: this.closest('tr').querySelector('td:nth-child(6)').textContent--}}
{{--                    };--}}
{{--                    exportPDF(invoice);--}}
{{--                });--}}
{{--            });--}}

{{--            // Écouteurs pour exports Excel individuels--}}
{{--            document.querySelectorAll('.export-excel').forEach(btn => {--}}
{{--                btn.addEventListener('click', function(e) {--}}
{{--                    e.stopPropagation();--}}
{{--                    const invoice = {--}}
{{--                        id: this.dataset.id,--}}
{{--                        numero: this.dataset.numero,--}}
{{--                        montant: parseInt(this.closest('tr').querySelector('td:nth-child(3)').textContent.replace(/[^\d]/g, '')),--}}
{{--                        periode: this.closest('tr').querySelector('td:nth-child(4)').textContent,--}}
{{--                        mode: this.closest('tr').querySelector('td:nth-child(5)').textContent.trim(),--}}
{{--                        date: this.closest('tr').querySelector('td:nth-child(6)').textContent--}}
{{--                    };--}}
{{--                    exportExcel(invoice);--}}
{{--                });--}}
{{--            });--}}

{{--            // Export tout--}}
{{--            const exportAllBtn = document.getElementById('exportAllBtn');--}}
{{--            if (exportAllBtn) {--}}
{{--                exportAllBtn.addEventListener('click', function() {--}}
{{--                    if (confirm('Exporter toutes les factures ?')) {--}}
{{--                        exportPDF(null, true);--}}
{{--                    }--}}
{{--                });--}}
{{--            }--}}

{{--            // Export depuis la modal--}}
{{--            if (exportModalPdfBtn) {--}}
{{--                exportModalPdfBtn.addEventListener('click', () => {--}}
{{--                    if (currentInvoice) exportPDF(currentInvoice);--}}
{{--                });--}}
{{--            }--}}

{{--            // Impression--}}
{{--            if (printInvoiceBtn) {--}}
{{--                printInvoiceBtn.addEventListener('click', printInvoice);--}}
{{--            }--}}

{{--            // Fermeture modal--}}
{{--            if (closeModalBtn) {--}}
{{--                closeModalBtn.addEventListener('click', closeModal);--}}
{{--            }--}}

{{--            modal?.addEventListener('click', function(e) {--}}
{{--                if (e.target === modal) closeModal();--}}
{{--            });--}}

{{--            // Recherche--}}
{{--            const searchInput = document.getElementById('invoice-search');--}}
{{--            const table = document.getElementById('invoicesTable');--}}
{{--            const tbody = table?.querySelector('tbody');--}}
{{--            const originalRows = tbody ? Array.from(tbody.querySelectorAll('tr')) : [];--}}

{{--            if (searchInput) {--}}
{{--                searchInput.addEventListener('input', function() {--}}
{{--                    const searchTerm = this.value.toLowerCase();--}}
{{--                    let hasResults = false;--}}

{{--                    originalRows.forEach(row => {--}}
{{--                        const text = row.textContent.toLowerCase();--}}
{{--                        if (text.includes(searchTerm)) {--}}
{{--                            row.style.display = '';--}}
{{--                            hasResults = true;--}}
{{--                        } else {--}}
{{--                            row.style.display = 'none';--}}
{{--                        }--}}
{{--                    });--}}

{{--                    // Afficher un message si aucun résultat--}}
{{--                    let noResultsRow = tbody.querySelector('.no-results-row');--}}
{{--                    if (!hasResults && !noResultsRow) {--}}
{{--                        noResultsRow = document.createElement('tr');--}}
{{--                        noResultsRow.className = 'no-results-row';--}}
{{--                        noResultsRow.innerHTML = `<td colspan="7" class="no-results">Aucune facture trouvée</td>`;--}}
{{--                        tbody.appendChild(noResultsRow);--}}
{{--                    } else if (hasResults && noResultsRow) {--}}
{{--                        noResultsRow.remove();--}}
{{--                    }--}}
{{--                });--}}
{{--            }--}}

{{--            // ESC pour fermer la modal--}}
{{--            document.addEventListener('keydown', function(e) {--}}
{{--                if (e.key === 'Escape' && modal?.style.display === 'flex') {--}}
{{--                    closeModal();--}}
{{--                }--}}
{{--            });--}}
{{--        });--}}
{{--    </script>--}}
{{--@endsection--}}