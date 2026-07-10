@extends('agence.layouts.app')
@section('title', 'Locataires')

@section('content')
    @php
        $hasFilters = request()->filled('search')
            || request()->filled('propriete_id')
            || request()->filled('is_actif');
    @endphp

    <div class="page">
        <div class="page-header">
            <div class="page-header-copy">
                <div class="page-heading">
                    <h2>Locataires</h2>
                </div>
                <p>Suivez les locataires, leurs contrats, les arriérés et l’accès rapide à chaque fiche.</p>
            </div>

            <div class="page-actions">
                @if($hasFilters)
                    <a href="{{ route('agence.locataires.index') }}" class="btn btn-outline">
                        Réinitialiser
                    </a>
                @endif
                <a href="{{ route('agence.locataires.create') }}" class="btn btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
                    </svg>
                    Nouveau locataire
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="stats-grid">
            <div class="stat-card">
                <span>Total</span>
                <strong>{{ $stats['total'] ?? 0 }}</strong>
            </div>
            <div class="stat-card">
                <span>Actifs</span>
                <strong class="is-success">{{ $stats['actifs'] ?? 0 }}</strong>
            </div>
            <div class="stat-card">
                <span>Résiliés</span>
                <strong class="is-danger">{{ $stats['resilies'] ?? 0 }}</strong>
            </div>
            <div class="stat-card">
                <span>Ce mois</span>
                <strong>{{ $stats['ce_mois'] ?? 0 }}</strong>
            </div>
        </div>

        <div class="form-card u-mb-md">
            <div class="form-card-body">
                <form method="GET" action="{{ route('agence.locataires.index') }}" class="u-flex u-gap-xs" style="flex-wrap:wrap;align-items:center;">
                    <div class="search-field" style="flex:1;min-width:240px;">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                        <input
                            type="text"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Nom, code, téléphone, numéro de pièce..."
                        />
                    </div>

                    <select
                        name="propriete_id"
                        onchange="this.form.submit()"
                        style="padding:.55rem .85rem;border:1px solid #d1d5db;border-radius:12px;font-size:.875rem;min-width:220px;"
                    >
                        <option value="">Toutes les propriétés</option>
                        @foreach($proprietes as $prop)
                            <option value="{{ $prop->propriete_id }}" @selected(request('propriete_id') == $prop->propriete_id)>
                                {{ $prop->reference }}
                            </option>
                        @endforeach
                    </select>

                    <select
                        name="is_actif"
                        onchange="this.form.submit()"
                        style="padding:.55rem .85rem;border:1px solid #d1d5db;border-radius:12px;font-size:.875rem;min-width:180px;"
                    >
                        <option value="">Tous les statuts</option>
                        <option value="1" @selected(request('is_actif') === '1')>Actifs</option>
                        <option value="0" @selected(request('is_actif') === '0')>Résiliés</option>
                    </select>

                    <button type="submit" class="btn btn-primary">
                        Filtrer
                    </button>
                </form>
            </div>
        </div>

        <x-table
            :collection="$locataires"
            empty-message="Aucun locataire trouvé."
            :colspan="8"
        >
            <x-slot:thead>
                <th>Locataire</th>
                <th>Contact</th>
                <th>Propriétaire</th>
                <th>Bien</th>
                <th>Logement</th>
                <th>Loyer mensuel</th>
                <th>Arriéré</th>
                <th class="table-actions-col">Actions</th>
            </x-slot:thead>

            @foreach($locataires as $loc)
                @php
                    $contrat = $loc->contrats->firstWhere('is_active', true) ?? $loc->contrats->first();
                    $arriere = $loc->loyers()->where('statut', '!=', 'paye')->sum('montant_restant');
                    $initial = strtoupper(substr($loc->name ?? 'L', 0, 1));
                @endphp

                <tr>
                    <td>
                        <div class="entity-cell">
                            <div class="entity-thumb entity-thumb-info">
                                {{ $initial }}
                            </div>
                            <div class="entity-copy">
                                <strong>{{ $loc->name }}</strong>
                                <span>{{ $loc->code }}</span>
                                <div style="margin-top:.35rem;">
                                    @if($contrat?->is_active)
                                        <span class="badge badge-success">Contrat actif</span>
                                    @else
                                        <span class="badge badge-neutral">Sans contrat actif</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </td>

                    <td>
                        <div>{{ $loc->tel1 ?? '—' }}</div>
                        @if($loc->tel2)
                            <div style="font-size:.78rem;color:#9ca3af;">{{ $loc->tel2 }}</div>
                        @endif
                    </td>

                    <td>
                        @if($contrat)
                            <strong>{{ $contrat->proprietaire?->name ?? '—' }}</strong>
                            <div style="font-size:.78rem;color:#9ca3af;">{{ $contrat->proprietaire?->tel1 ?? '—' }}</div>
                        @else
                            <span style="color:#d1d5db;">—</span>
                        @endif
                    </td>

                    <td>
                        @if($contrat)
                            <strong>{{ $contrat->propriete?->reference ?? '—' }}</strong>
                            <div style="font-size:.78rem;color:#9ca3af;">
                                {{ $contrat->lot?->name ?? 'Lot non précisé' }}
                            </div>
                        @else
                            <span style="color:#d1d5db;">—</span>
                        @endif
                    </td>

                    <td>
                        @if($contrat)
                            <strong>{{ $contrat->batiment?->name ?? '—' }}</strong>
                            <div style="font-size:.78rem;color:#9ca3af;">{{ $contrat->porte?->numero_porte ?? '—' }}</div>
                        @else
                            <span style="color:#d1d5db;">—</span>
                        @endif
                    </td>

                    <td>
                        @if($contrat)
                            <strong>{{ number_format($contrat->porte?->mt_loyer ?? 0, 0, ',', ' ') }} F</strong>
                        @else
                            —
                        @endif
                    </td>

                    <td>
                        @if($arriere > 0)
                            <span class="badge badge-danger">{{ number_format($arriere, 0, ',', ' ') }} F</span>
                        @else
                            <span class="badge badge-success">À jour</span>
                        @endif
                    </td>

                    <td>
                        <div class="table-actions">
                            <a
                                href="{{ route('agence.locataires.show', $loc->locataire_id) }}"
                                class="action-btn info"
                                title="Voir"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>
                            </a>

                            <a
                                href="{{ route('agence.locataires.edit', $loc->locataire_id) }}"
                                class="action-btn neutral"
                                title="Modifier"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" />
                                </svg>
                            </a>

                            @if($contrat?->is_active)
                                <form
                                    action="{{ route('agence.locataires.resilier', $loc->locataire_id) }}"
                                    method="POST"
                                    class="u-inline-form"
                                    onsubmit="return confirm('Résilier le contrat de {{ addslashes($loc->name) }} ?')"
                                >
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="action-btn danger" title="Résilier">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                                        </svg>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
        </x-table>
    </div>
@endsection
