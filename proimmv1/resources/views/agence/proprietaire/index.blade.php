@extends('agence.layouts.app')

@section('title', 'Propriétaires')

@section('content')
    @php
        $statusClass = fn ($isActive) => $isActive ? 'badge-success' : 'badge-danger';
        $statusLabel = fn ($isActive) => $isActive ? 'Actif' : 'Inactif';
        $statusFilter = fn ($isActive) => $isActive ? 'actif' : 'inactif';
    @endphp

    <section class="page">

        {{-- En-tête --}}
        <div class="page-header">
            <div class="page-header-copy">
                <div class="page-heading">
                    <div>
                        <h2>Gestion des propriétaires</h2>
                        <p class="text-muted mb-0">
                            Consultez, ajoutez et gérez tous les propriétaires de vos biens immobiliers.
                        </p>
                    </div>
                </div>
            </div>

            <div class="page-actions">
                <a class="btn btn-primary" href="{{ route('agence.proprietaire.create') }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nouveau propriétaire
                </a>
            </div>
        </div>

        {{-- Cartes de statistiques --}}
        <div class="stats-grid">
            <article class="stat-card">
                <span>Total propriétaires</span>
                <strong>{{ $proprietaires->total() }}</strong>
            </article>
            <article class="stat-card">
                <span>Comptes actifs</span>
                <strong class="is-success">{{ $proprietaires->getCollection()->where('agences.0.is_active', true)->count() }}</strong>
            </article>
            <article class="stat-card">
                <span>Comptes inactifs</span>
                <strong class="is-danger">{{ $proprietaires->getCollection()->where('agences.0.is_active', false)->count() }}</strong>
            </article>
            <article class="stat-card">
                <span>Page actuelle</span>
                <strong>{{ $proprietaires->currentPage() }} / {{ $proprietaires->lastPage() }}</strong>
            </article>
        </div>


        <x-table
                title="Liste des propriétaires"
                :collection="$proprietaires"
                empty-message="Aucun propriétaire enregistré."
                :colspan="7"
                search-id="proprietaire-search"
                class="responsive-table u-table-fit"
        >

            {{-- TOOLBAR ACTIONS --}}
            <x-slot:actions>

                <div class="filter-pills">

                    <button class="filter-pill active"
                            type="button"
                            data-filter="tous">
                        Tous
                    </button>

                    <button class="filter-pill"
                            type="button"
                            data-filter="actif">
                        Actifs
                    </button>

                    <button class="filter-pill"
                            type="button"
                            data-filter="inactif">
                        Inactifs
                    </button>

                </div>

            </x-slot:actions>

            {{-- TABLE HEADER --}}
            <x-slot:thead>

                <th>Propriétaire</th>

                <th>Contact</th>

                <th>Adresse</th>

                <th>Représentant</th>

                <th>Statut</th>

                <th>Enregistré le</th>

                <th class="table-actions-col">
                    Actions
                </th>

            </x-slot:thead>

            {{-- TABLE BODY --}}
            @forelse($proprietaires as $proprietaire)

                @php
                    $liaison = $proprietaire->agences->first();

                    $isActive = $liaison?->is_active ?? false;
                @endphp

                <tr class="filterable-row"
                    data-filter-status="{{ $statusFilter($isActive) }}">

                    {{-- NOM --}}
                    <td data-label="Propriétaire">

                        <div class="entity-cell">

                    <span class="entity-avatar">
                        {{ strtoupper(mb_substr($proprietaire->name, 0, 1)) }}
                    </span>

                            <div>

                                <strong>
                                    {{ $proprietaire->name }}
                                </strong>

                                <small>
                                    Email :
                                    {{ $proprietaire->email ?? '—' }}
                                </small>

                                <small>
                                    Code :
                                    {{ $proprietaire->code }}
                                </small>

                            </div>

                        </div>

                    </td>

                    {{-- CONTACT --}}
                    <td data-label="Contact">

                        <strong>
                            {{ $proprietaire->tel1 }}
                        </strong>

                        @if($proprietaire->tel2)
                            <small>
                                {{ $proprietaire->tel2 }}
                            </small>
                        @endif

                    </td>

                    {{-- ADRESSE --}}
                    <td data-label="Adresse">

                <span>
                    {{ \Illuminate\Support\Str::limit($proprietaire->adresse ?? '—', 40) }}
                </span>

                    </td>

                    {{-- REPRESENTANT --}}
                    <td data-label="Représentant">

                        @if($liaison?->name_representant)

                            <strong>
                                {{ $liaison->name_representant }}
                            </strong>

                            <small>
                                {{ $liaison->tel1_representant ?? '' }}
                            </small>

                        @else

                            <span class="text-muted">—</span>

                        @endif

                    </td>

                    {{-- STATUT --}}
                    <td data-label="Statut">

                <span class="badge {{ $statusClass($isActive) }}">
                    {{ $statusLabel($isActive) }}
                </span>

                    </td>

                    {{-- DATE --}}
                    <td data-label="Enregistré le">

                        {{ $proprietaire->created_at?->format('d/m/Y') ?? '—' }}

                    </td>

                    {{-- ACTIONS --}}
                    <td data-label="Actions">

                        <div class="table-actions">

                            {{-- VOIR --}}
                            <a href="{{ route('agence.proprietaire.show', $proprietaire->proprietaire_id) }}"
                               class="action-btn info"
                               title="Voir le détail">

                                <svg xmlns="http://www.w3.org/2000/svg"
                                     fill="none"
                                     viewBox="0 0 24 24"
                                     stroke="currentColor"
                                     stroke-width="2">

                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          d="M2.036 12.322a1.012 1.012 0 010-.644C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .638C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z"/>

                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>

                                </svg>

                            </a>

                            {{-- MODIFIER --}}
                            <a href="{{ route('agence.proprietaire.edit', $proprietaire->proprietaire_id) }}"
                               class="action-btn neutral"
                               title="Modifier">

                                <svg xmlns="http://www.w3.org/2000/svg"
                                     fill="none"
                                     viewBox="0 0 24 24"
                                     stroke="currentColor"
                                     stroke-width="2">

                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          d="m16.862 4.487 1.687-1.688a2.25 2.25 0 1 1 3.182 3.182L10.582 17.13a4.5 4.5 0 0 1-1.897 1.13L6 19l.74-2.685a4.5 4.5 0 0 1 1.13-1.897L16.862 4.487z"/>

                                </svg>

                            </a>

                            {{-- ACTIVER / DESACTIVER --}}
                            @if($liaison)

                                @if($isActive)

                                    <form method="POST"
                                          action="{{ route('agence.proprietaire.deactivate', $liaison->proprietaire_agence_id) }}"
                                          class="d-inline">

                                        @csrf
                                        @method('PATCH')

                                        <button type="submit"
                                                class="action-btn warning"
                                                title="Désactiver">

                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                 fill="none"
                                                 viewBox="0 0 24 24"
                                                 stroke="currentColor"
                                                 stroke-width="2">

                                                <path stroke-linecap="round"
                                                      stroke-linejoin="round"
                                                      d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>

                                            </svg>

                                        </button>

                                    </form>

                                @else

                                    <form method="POST"
                                          action="{{ route('agence.proprietaire.activate', $liaison->proprietaire_agence_id) }}"
                                          class="d-inline">

                                        @csrf
                                        @method('PATCH')

                                        <button type="submit"
                                                class="action-btn success"
                                                title="Activer">

                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                 fill="none"
                                                 viewBox="0 0 24 24"
                                                 stroke="currentColor"
                                                 stroke-width="2">

                                                <path stroke-linecap="round"
                                                      stroke-linejoin="round"
                                                      d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>

                                            </svg>

                                        </button>

                                    </form>

                                @endif

                            @endif

                            {{-- SUPPRIMER --}}
                            <form method="POST"
                                  action="{{ route('agence.proprietaire.destroy', $proprietaire->proprietaire_id) }}"
                                  style="display:flex;"
                                  onsubmit="return confirm('Confirmer la suppression de {{ addslashes($proprietaire->name) }} ?')">

                                @csrf
                                @method('DELETE')

                                <button type="submit"
                                        class="action-btn danger"
                                        title="Supprimer">

                                    <svg xmlns="http://www.w3.org/2000/svg"
                                         fill="none"
                                         viewBox="0 0 24 24"
                                         stroke="currentColor"
                                         stroke-width="2">

                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>

                                    </svg>

                                </button>

                            </form>

                        </div>

                    </td>

                </tr>

            @empty
                <tr>
                    <td colspan="7">
                        <div class="empty-state">
                            Aucun propriétaire enregistré.
                        </div>
                    </td>
                </tr>

            @endforelse
            <tr id="filter-empty-row" style="display:none;">
                <td colspan="7">
                    <div class="empty-state">
                        Aucun propriétaire ne correspond à ce filtre.
                    </div>
                </td>
            </tr>

        </x-table>
    </section>
@endsection

@push('scripts')
    <script>
        function updateEmptyState() {
            const rows = document.querySelectorAll('.filterable-row');
            const emptyRow = document.getElementById('filter-empty-row');

            if (!emptyRow) return;

            const hasVisibleRow = Array.from(rows).some(row => row.style.display !== 'none');

            emptyRow.style.display = hasVisibleRow ? 'none' : '';
        }

        document.querySelectorAll('.filter-pill').forEach(btn => {
            btn.addEventListener('click', function () {
                document.querySelectorAll('.filter-pill').forEach(b => b.classList.remove('active'));
                this.classList.add('active');

                const filter = this.dataset.filter;

                document.querySelectorAll('.filterable-row').forEach(row => {
                    row.style.display =
                        filter === 'tous' || row.dataset.filterStatus === filter ? '' : 'none';
                });

                updateEmptyState();
            });
        });

        const searchInput = document.getElementById('proprietaire-search');

        searchInput?.addEventListener('input', function () {
            const q = this.value.toLowerCase();

            document.querySelectorAll('.filterable-row').forEach(row => {
                row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
            });

            updateEmptyState();
        });
    </script>
@endpush