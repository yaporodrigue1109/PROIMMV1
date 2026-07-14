@extends('agence.layouts.app')

@section('title', 'Propriétés')

@section('content')
    <div class="page">

        {{-- Hero header --}}
        <div class="page-header">
            <div class="page-header-copy">
                <div class="page-heading">
                    <h2>Propriétés</h2>
                </div>
                <p>Gérez vos biens en vente, en location et configurez les référentiels associés.</p>
            </div>
            <div class="page-actions">
                <button class="btn btn-outline" id="btnImport">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                    </svg>
                    Importer
                </button>
                <a href="{{ route('agence.proprietes.create') }}" class="btn btn-primary" id="btnAddPropriete">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Nouvelle propriété
                </a>
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
        <div class="stats-grid">
            <div class="stat-card">
                <span>Total</span>
                <strong>{{ $stats['total'] }}</strong>
            </div>
            <div class="stat-card">
                <span>En allocation</span>
                <strong class="is-success">{{ $stats['allocation'] }}</strong>
            </div>
            <div class="stat-card">
                <span>En vente</span>
                <strong class="is-info">{{ $stats['non_allocation'] }}</strong>
            </div>
            <div class="stat-card">
                <span>Ajoutées ce mois</span>
                <strong>+{{ $stats['ce_mois'] }}</strong>
            </div>
        </div>

        {{-- Module shell : sidebar nav + contenu --}}
        <div class="prop-shell">

            {{-- Sidebar de navigation --}}
            <nav class="prop-sidenav" aria-label="Navigation propriétés">
                <div class="prop-nav-section">
                    <div class="prop-nav-label">Propriétés</div>
                    <a href="#" class="prop-nav-item active" data-panel="toutes">
                        <span class="prop-nav-dot"></span>
                        Toutes les propriétés
                        <span class="prop-nav-count">{{ $stats['total'] }}</span>
                    </a>
                    <a href="#" class="prop-nav-item" data-panel="allocation">
                        <span class="prop-nav-dot"></span>
                        En allocation
                        <span class="prop-nav-count">{{ $stats['allocation'] }}</span>
                    </a>
                    <a href="#" class="prop-nav-item" data-panel="hors-allocation">
                        <span class="prop-nav-dot"></span>
                        En vente
                        <span class="prop-nav-count">{{ $stats['non_allocation'] }}</span>
                    </a>
                </div>
                <div class="prop-nav-divider"></div>
                <div class="prop-nav-section">
                    <div class="prop-nav-label">Référentiel</div>
                    <a href="#" class="prop-nav-item" data-panel="types">
                        <span class="prop-nav-dot"></span>
                        Types de propriété
                    </a>
                    <a href="#" class="prop-nav-item" data-panel="equipements">
                        <span class="prop-nav-dot"></span>
                        Équipements
                    </a>
                    <a href="#" class="prop-nav-item" data-panel="proximites">
                        <span class="prop-nav-dot"></span>
                        Proximités
                    </a>
                </div>
            </nav>

            {{-- Zone de contenu principale --}}
            <div class="prop-content">

                {{-- PANEL : Toutes les propriétés --}}
                <div class="prop-panel active" id="panel-toutes">
                    <div class="prop-panel-header">
                        <div>
                            <h3 class="prop-panel-title">Toutes les propriétés</h3>
                            <p class="prop-panel-sub">{{ $proprietes->total() }} propriété(s) enregistrée(s)</p>
                        </div>
                        <div class="u-flex u-gap-xs">
                            <form method="GET" action="{{ route('agence.proprietes.index') }}" class="u-flex u-gap-xs" id="form-search">
                                <div class="search-field">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                                    </svg>
                                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher…" />
                                </div>
                                <div class="filter-dropdown">
                                    <button type="button" class="filter-btn" aria-expanded="false">
                                        <span>{{ request('type_id') ? $types->firstWhere('id', request('type_id'))?->name ?? 'Tous les types' : 'Tous les types' }}</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                                    </button>
                                    <div class="filter-menu">
                                        <button type="button" class="filter-option {{ !request('type_id') ? 'is-selected' : '' }}"
                                                onclick="setFilter('')">Tous les types</button>
                                        @foreach($types as $type)
                                            <button type="button"
                                                    class="filter-option {{ request('type_id') == $type->id ? 'is-selected' : '' }}"
                                                    onclick="setFilter('{{ $type->id }}')">
                                                {{ $type->name }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                                <input type="hidden" name="type_id" id="input-type-id" value="{{ request('type_id') }}" />
                            </form>
                        </div>
                    </div>

                    <x-table
                            :collection="$proprietes"
                            empty-message="Aucune propriété trouvée."
                            :colspan="7"
                            id="panel-toutes-table"
                    >
                        <x-slot:thead>
                            <th>Proprietaire</th>
                            <th>Propriété</th>
                            <th>Bâtiments</th>
                            <th>Portes libres</th>
                            <th>Portes occupées</th>
                            <th>Allocation</th>
                            <th class="table-actions-col">Actions</th>
                        </x-slot:thead>

                        @forelse($proprietes as $prop)
                            <tr>
                                <td>
                                    <div class="entity-cell">
                                        <div class="entity-thumb entity-thumb-info">
                                            {{ strtoupper(substr($prop->proprietaire->name ?? 'P', 0, 1)) }}
                                        </div>
                                        <div class="entity-copy">
                                            <strong>{{ $prop->proprietaire->name }}</strong>
                                            <span>{{ $prop->proprietaire->tel1 ?? '—' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="entity-cell">
                                        <div class="entity-thumb entity-thumb-info">
                                            {{ strtoupper(substr($prop->typePropriete->libelle ?? 'P', 0, 1)) }}
                                        </div>
                                        <div class="entity-copy">
                                            <strong>{{ $prop->reference }}</strong>
                                            <span>{{ $prop->adresse_complete ?? '—' }}</span>
                                        </div>
                                    </div>
                                </td>

                                <td>{{ $prop->batiments->count() }}</td>
                                <td><span class="badge badge-success">{{ $prop->nbre_porte_libre }}</span></td>
                                <td><span class="badge badge-danger">{{ $prop->nbre_porte_occupe }}</span></td>
                                <td>
                                    @if($prop->is_allocation)
                                        <span class="badge badge-warning">Oui</span>
                                    @else
                                        <span class="badge badge-neutral">Non</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <a href="{{ route('agence.proprietes.show', $prop->propriete_id) }}"
                                           class="action-btn info" title="Voir">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                        </a>
                                        <a href="{{ route('agence.proprietes.edit', $prop->propriete_id) }}"
                                           class="action-btn neutral" title="Modifier">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" /></svg>
                                        </a>
                                        <form action="{{ route('agence.proprietes.destroy', $prop->propriete_id) }}"
                                              method="POST" class="u-inline-form"
                                              onsubmit="return confirm('Désactiver cette propriété ?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="action-btn danger" title="Désactiver">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">Aucune propriété trouvée.</div>
                                </td>
                            </tr>
                        @endforelse
                    </x-table>
                </div>

                {{-- PANEL : En allocation --}}
                <div class="prop-panel" id="panel-allocation">
                    <div class="prop-panel-header">
                        <div>
                            <h3 class="prop-panel-title">Propriétés en allocation</h3>
                            <p class="prop-panel-sub">{{ $stats['allocation'] }} propriété(s) en mode allocation</p>
                        </div>
                        <a href="{{ route('agence.proprietes.create') }}" class="btn btn-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                            Ajouter
                        </a>
                    </div>
                    <x-table
                            :collection="$proprietes->where('is_allocation', true)"
                            empty-message="Aucune propriété en allocation."
                            :colspan="5"
                            id="panel-allocation-table"
                    >
                        <x-slot:thead>
                            <th>Propriété</th>
                            <th>Type</th>
                            <th>Adresse</th>
                            <th>Portes</th>
                            <th class="table-actions-col">Actions</th>
                        </x-slot:thead>

                        @forelse($proprietes->where('is_allocation', true) as $prop)
                            <tr>
                                <td>
                                    <div class="entity-cell">
                                        <div class="entity-thumb entity-thumb-success">
                                            {{ strtoupper(substr($prop->typePropriete->libelle ?? 'P', 0, 1)) }}
                                        </div>
                                        <div class="entity-copy">
                                            <strong>{{ $prop->reference }}</strong>
                                            <span>{{ $prop->typePropriete->libelle ?? '—' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge badge-info">{{ $prop->typePropriete->libelle ?? '—' }}</span></td>
                                <td>{{ $prop->adresse_complete ?? '—' }}</td>
                                <td>
                                    <span class="badge badge-success">{{ $prop->nbre_porte_libre }} libres</span>
                                    <span class="badge badge-danger">{{ $prop->nbre_porte_occupe }} occupées</span>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <a href="{{ route('agence.proprietes.show', $prop->propriete_id) }}" class="action-btn info" title="Voir">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                        </a>
                                        <a href="{{ route('agence.proprietes.edit', $prop->propriete_id) }}" class="action-btn neutral" title="Modifier">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" /></svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5"><div class="empty-state">Aucune propriété en allocation.</div></td>
                            </tr>
                        @endforelse
                    </x-table>
                </div>

                {{-- PANEL : Hors allocation (En vente) --}}
                <div class="prop-panel" id="panel-hors-allocation">
                    <div class="prop-panel-header">
                        <div>
                            <h3 class="prop-panel-title">Propriétés hors allocation</h3>
                            <p class="prop-panel-sub">{{ $stats['non_allocation'] }} propriété(s) hors allocation</p>
                        </div>
                        <a href="{{ route('agence.proprietes.create') }}" class="btn btn-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                            Ajouter
                        </a>
                    </div>
                    <x-table
                            :collection="$proprietes->where('is_allocation', false)"
                            empty-message="Aucune propriété hors allocation."
                            :colspan="5"
                            id="panel-hors-allocation-table"
                    >
                        <x-slot:thead>
                            <th>Propriété</th>
                            <th>Type</th>
                            <th>Adresse</th>
                            <th>Portes</th>
                            <th class="table-actions-col">Actions</th>
                        </x-slot:thead>

                        @forelse($proprietes->where('is_allocation', false) as $prop)
                            <tr>
                                <td>
                                    <div class="entity-cell">
                                        <div class="entity-thumb entity-thumb-success">
                                            {{ strtoupper(substr($prop->typePropriete->libelle ?? 'P', 0, 1)) }}
                                        </div>
                                        <div class="entity-copy">
                                            <strong>{{ $prop->reference }}</strong>
                                            <span>{{ $prop->typePropriete->libelle ?? '—' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge badge-info">{{ $prop->typePropriete->libelle ?? '—' }}</span></td>
                                <td>{{ $prop->adresse_complete ?? '—' }}</td>
                                <td>
                                    <span class="badge badge-success">{{ $prop->nbre_porte_libre }} libres</span>
                                    <span class="badge badge-danger">{{ $prop->nbre_porte_occupe }} occupées</span>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <a href="{{ route('agence.proprietes.show', $prop->propriete_id) }}" class="action-btn info" title="Voir">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                        </a>
                                        <a href="{{ route('agence.proprietes.edit', $prop->propriete_id) }}" class="action-btn neutral" title="Modifier">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" /></svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5"><div class="empty-state">Aucune propriété hors allocation.</div></td>
                            </tr>
                        @endforelse
                    </x-table>
                </div>

                {{-- PANEL : Types de propriété --}}
                <div class="prop-panel" id="panel-types">
                    <div class="prop-panel-header">
                        <div>
                            <h3 class="prop-panel-title">Types de propriété</h3>
                            <p class="prop-panel-sub">{{ $types->count() }} types configurés</p>
                        </div>
                        <button class="btn btn-primary" data-open-modal="modal-add-type">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                            Nouveau type
                        </button>
                    </div>
                    <div class="prop-ref-grid">
                        @foreach($types as $type)
                            <div class="prop-ref-card">
                                <div class="prop-ref-name">{{ $type->libelle }}</div>
                                @if($type->description)
                                    <p class="prop-ref-desc">{{ $type->description }}</p>
                                @endif
                                <div class="table-actions u-mt-sm">
                                    <button type="button" class="action-btn neutral btn-edit-ref" title="Modifier"
                                            data-modal="modal-edit-type"
                                            data-id="{{ $type->id }}"
                                            data-name="{{ $type->name }}"
                                            data-libelle="{{ $type->name }}"
                                            data-description="{{ $type->description ?? '' }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" /></svg>
                                    </button>
                                    <form action="{{ route('agence.types-propriete.destroy', $type->id) }}"
                                          method="POST" class="u-inline-form"
                                          onsubmit="return confirm('Supprimer ce type ?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="action-btn danger" title="Supprimer">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- PANEL : Équipements --}}
                <div class="prop-panel" id="panel-equipements">
                    <div class="prop-panel-header">
                        <div>
                            <h3 class="prop-panel-title">Équipements</h3>
                            <p class="prop-panel-sub">{{ $equipements->count() }} équipements configurés</p>
                        </div>
                        <button class="btn btn-primary" data-open-modal="modal-add-equipement">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                            Nouvel équipement
                        </button>
                    </div>
                    <div class="prop-ref-grid">
                        @foreach($equipements as $eq)
                            <div class="prop-ref-card">
                                <div class="prop-ref-name">{{ $eq->libelle }}</div>
                                @if($eq->description)
                                    <p class="prop-ref-desc">{{ $eq->description }}</p>
                                @endif
                                <div class="table-actions u-mt-sm">
                                    <button type="button" class="action-btn neutral btn-edit-ref" title="Modifier"
                                            data-modal="modal-edit-equipement"
                                            data-id="{{ $eq->equipement_id }}"
                                            data-libelle="{{ $eq->name }}"
                                            data-icone="{{ $eq->icone ?? '' }}"
                                            data-description="{{ $eq->description ?? '' }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" /></svg>
                                    </button>
                                    <form action="{{ route('agence.equipement-propriete.destroy', $eq->id) }}"
                                          method="POST" class="u-inline-form"
                                          onsubmit="return confirm('Supprimer cet équipement ?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="action-btn danger" title="Supprimer">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- PANEL : Proximités --}}
                <div class="prop-panel" id="panel-proximites">
                    <div class="prop-panel-header">
                        <div>
                            <h3 class="prop-panel-title">Proximités</h3>
                            <p class="prop-panel-sub">{{ $proximites->count() }} catégories de proximité</p>
                        </div>
                        <button class="btn btn-primary" data-open-modal="modal-add-proximite">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                            Nouvelle proximité
                        </button>
                    </div>
                    <div class="prop-ref-grid">
                        @foreach($proximites as $prox)
                            <div class="prop-ref-card">
                                <div class="prop-ref-name">{{ $prox->libelle }}</div>
                                @if($prox->categorie)
                                    <p class="prop-ref-desc">{{ ucfirst($prox->categorie) }}</p>
                                @endif
                                <div class="table-actions u-mt-sm">
                                    <button type="button" class="action-btn neutral btn-edit-ref" title="Modifier"
                                            data-modal="modal-edit-proximite"
                                            data-id="{{ $prox->proximite_id }}"
                                            data-libelle="{{ $prox->name }}"
                                            data-description="{{ $prox->description ?? '' }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" /></svg>
                                    </button>
                                    <form action="{{ route('agence.possimite-propriete.destroy', $prox->id) }}"
                                          method="POST" class="u-inline-form"
                                          onsubmit="return confirm('Supprimer cette proximité ?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="action-btn danger" title="Supprimer">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>{{-- /.prop-content --}}
        </div>{{-- /.prop-shell --}}

    </div>{{-- /.page --}}


    {{-- ============================================================
         MODAL : Ajouter un type de propriété
         ============================================================ --}}
    <div class="modal" data-modal="modal-add-type" aria-hidden="true">
        <div class="modal-box u-modal-sm">
            <div class="modal-header">
                <h3>Nouveau type de propriété</h3>
                <button class="modal-close" data-close-modal aria-label="Fermer">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="u-icon-sm"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ route('agence.types-propriete.store') }}" method="POST">
                    @csrf
                    <div class="form-grid u-form-single">
                        <div class="form-field">
                            <span>Libellé *</span>
                            <input type="text" name="name" placeholder="Ex: Villa, Duplex, Bureau…" required />
                        </div>
                        <div class="form-field">
                            <span>Description</span>
                            <textarea name="description" placeholder="Description facultative…"></textarea>
                        </div>
                    </div>
                    <div class="u-flex-end u-mt-lg">
                        <button type="button" class="btn btn-outline" data-close-modal>Annuler</button>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ============================================================
         MODAL : Modifier un type de propriété
         ============================================================ --}}
    <div class="modal" data-modal="modal-edit-type" aria-hidden="true">
        <div class="modal-box u-modal-sm">
            <div class="modal-header">
                <h3>Modifier le type</h3>
                <button class="modal-close" data-close-modal aria-label="Fermer">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="u-icon-sm"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <div class="modal-body">
                <form action="" method="POST" id="form-edit-type">
                    @csrf @method('PUT')
                    <div class="form-grid u-form-single">
                        <div class="form-field">
                            <span>Libellé *</span>
                            <input type="text" name="name" id="edit-type-libelle" placeholder="Ex: Villa, Duplex, Bureau…" required />
                        </div>
                        <div class="form-field">
                            <span>Description</span>
                            <textarea name="description" id="edit-type-description" placeholder="Description facultative…"></textarea>
                        </div>
                    </div>
                    <div class="u-flex-end u-mt-lg">
                        <button type="button" class="btn btn-outline" data-close-modal>Annuler</button>
                        <button type="submit" class="btn btn-primary">Mettre à jour</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ============================================================
         MODAL : Ajouter un équipement
         ============================================================ --}}
    <div class="modal" data-modal="modal-add-equipement" aria-hidden="true">
        <div class="modal-box u-modal-sm">
            <div class="modal-header">
                <h3>Nouvel équipement</h3>
                <button class="modal-close" data-close-modal aria-label="Fermer">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="u-icon-sm"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ route('agence.equipement-propriete.store') }}" method="POST">
                    @csrf
                    <div class="form-grid u-form-single">
                        <div class="form-field">
                            <span>Libellé *</span>
                            <input type="text" name="name" placeholder="Ex: Piscine, Garage, Climatisation…" required />
                        </div>
                        <div class="form-field">
                            <span>Description</span>
                            <textarea name="description" placeholder="Description facultative…"></textarea>
                        </div>
                    </div>
                    <div class="u-flex-end u-mt-lg">
                        <button type="button" class="btn btn-outline" data-close-modal>Annuler</button>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ============================================================
         MODAL : Modifier un équipement
         ============================================================ --}}
    <div class="modal" data-modal="modal-edit-equipement" aria-hidden="true">
        <div class="modal-box u-modal-sm">
            <div class="modal-header">
                <h3>Modifier l'équipement</h3>
                <button class="modal-close" data-close-modal aria-label="Fermer">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="u-icon-sm"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <div class="modal-body">
                <form action="" method="POST" id="form-edit-equipement">
                    @csrf @method('PUT')
                    <div class="form-grid u-form-single">
                        <div class="form-field">
                            <span>Libellé *</span>
                            <input type="text" name="libelle" id="edit-equipement-libelle" placeholder="Ex: Piscine, Garage…" required />
                        </div>
                        <div class="form-field">
                            <span>Icône (optionnel)</span>
                            <input type="text" name="icone" id="edit-equipement-icone" placeholder="Ex: pool, garage…" />
                        </div>
                        <div class="form-field">
                            <span>Description</span>
                            <textarea name="description" id="edit-equipement-description" placeholder="Description facultative…"></textarea>
                        </div>
                    </div>
                    <div class="u-flex-end u-mt-lg">
                        <button type="button" class="btn btn-outline" data-close-modal>Annuler</button>
                        <button type="submit" class="btn btn-primary">Mettre à jour</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ============================================================
         MODAL : Ajouter une proximité
         ============================================================ --}}
    <div class="modal" data-modal="modal-add-proximite" aria-hidden="true">
        <div class="modal-box u-modal-sm">
            <div class="modal-header">
                <h3>Nouvelle proximité</h3>
                <button class="modal-close" data-close-modal aria-label="Fermer">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="u-icon-sm"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ route('agence.possimite-propriete.store') }}" method="POST">
                    @csrf
                    <div class="form-grid u-form-single">
                        <div class="form-field">
                            <span>Libellé *</span>
                            <input type="text" name="name" placeholder="Ex: École, Hôpital, Marché…" required />
                        </div>
                        <div class="form-field">
                            <span>Description</span>
                            <textarea name="description" placeholder="Description facultative…"></textarea>
                        </div>
                    </div>
                    <div class="u-flex-end u-mt-lg">
                        <button type="button" class="btn btn-outline" data-close-modal>Annuler</button>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ============================================================
         MODAL : Modifier une proximité
         ============================================================ --}}
    <div class="modal" data-modal="modal-edit-proximite" aria-hidden="true">
        <div class="modal-box u-modal-sm">
            <div class="modal-header">
                <h3>Modifier la proximité</h3>
                <button class="modal-close" data-close-modal aria-label="Fermer">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="u-icon-sm"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <div class="modal-body">
                <form action="" method="POST" id="form-edit-proximite">
                    @csrf @method('PUT')
                    <div class="form-grid u-form-single">
                        <div class="form-field">
                            <span>Libellé *</span>
                            <input type="text" name="libelle" id="edit-proximite-libelle" placeholder="Ex: École, Hôpital…" required />
                        </div>
                        <div class="form-field">
                            <span>Description</span>
                            <textarea name="description" id="edit-proximite-description" placeholder="Description facultative…"></textarea>
                        </div>
                    </div>
                    <div class="u-flex-end u-mt-lg">
                        <button type="button" class="btn btn-outline" data-close-modal>Annuler</button>
                        <button type="submit" class="btn btn-primary">Mettre à jour</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // Panels sidebar
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

            // Filtre type (dropdown)
            function setFilter(typeId) {
                document.getElementById('input-type-id').value = typeId;
                document.getElementById('form-search').submit();
            }
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const expanded = btn.getAttribute('aria-expanded') === 'true';
                    btn.setAttribute('aria-expanded', !expanded);
                    btn.closest('.filter-dropdown').querySelector('.filter-menu').classList.toggle('is-open');
                });
            });

            // Modals génériques
            document.querySelectorAll('[data-open-modal]').forEach(btn => {
                btn.addEventListener('click', () => {
                    document.querySelector(`[data-modal="${btn.dataset.openModal}"]`)?.removeAttribute('aria-hidden');
                });
            });
            document.querySelectorAll('[data-close-modal], .modal-close').forEach(btn => {
                btn.addEventListener('click', () => btn.closest('.modal')?.setAttribute('aria-hidden', 'true'));
            });

            // Modal edit TYPE
            document.addEventListener('click', function (e) {
                // Edit type
                const btnType = e.target.closest('.btn-edit-ref[data-modal="modal-edit-type"]');
                if (btnType) {
                    document.getElementById('edit-type-libelle').value     = btnType.dataset.name ?? btnType.dataset.libelle ?? '';
                    document.getElementById('edit-type-description').value = btnType.dataset.description ?? '';
                    document.getElementById('form-edit-type').action       = `/agence/types-propriete/${btnType.dataset.id}`;
                    document.querySelector('[data-modal="modal-edit-type"]')?.removeAttribute('aria-hidden');
                    return;
                }

                // Edit équipement
                const btnEq = e.target.closest('.btn-edit-ref[data-modal="modal-edit-equipement"]');
                if (btnEq) {
                    document.getElementById('edit-equipement-libelle').value     = btnEq.dataset.libelle ?? '';
                    document.getElementById('edit-equipement-icone').value       = btnEq.dataset.icone ?? '';
                    document.getElementById('edit-equipement-description').value = btnEq.dataset.description ?? '';
                    document.getElementById('form-edit-equipement').action       = `/agence/equipements/${btnEq.dataset.id}`;
                    document.querySelector('[data-modal="modal-edit-equipement"]')?.removeAttribute('aria-hidden');
                    return;
                }

                // Edit proximité
                const btnProx = e.target.closest('.btn-edit-ref[data-modal="modal-edit-proximite"]');
                if (btnProx) {
                    document.getElementById('edit-proximite-libelle').value     = btnProx.dataset.libelle ?? '';
                    document.getElementById('edit-proximite-description').value = btnProx.dataset.description ?? '';
                    document.getElementById('edit-proximite-categorie').value   = btnProx.dataset.categorie ?? '';
                    document.getElementById('form-edit-proximite').action       = `/agence/proximites/${btnProx.dataset.id}`;
                    document.querySelector('[data-modal="modal-edit-proximite"]')?.removeAttribute('aria-hidden');
                    return;
                }
            });
        </script>
    @endpush
@endsection