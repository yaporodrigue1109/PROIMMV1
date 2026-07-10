
@extends('admin.layouts.app')

@section('title', 'Agences')
@section('header_title', 'Agences')

@section('content')
    @php
        // Gestion robuste du paginator ou collection simple
        $isPaginator = $agences instanceof \Illuminate\Pagination\AbstractPaginator;
        $agenceItems = $isPaginator ? collect($agences->items()) : collect($agences);
        $totalAgences = $isPaginator ? $agences->total() : $agenceItems->count();

        // Stats
        $agencesActives = $agenceItems->where('statut', 'active')->count();
        $agencesDemo = $agenceItems->where('statut', 'en_demo')->count();
        $agencesDesactivees = $agenceItems->where('statut', 'desactive')->count();

        // Agence sélectionnée (première par défaut)
        $selectedAgence = $agenceItems->first();

        // Helpers
        $get = fn ($agence, $key, $default = null) => data_get($agence, $key, $default);

        $statusLabel = fn ($status) => match ((string) $status) {
            'active' => 'Active',
            'en_demo' => 'En démo',
            'desactive' => 'Désactivée',
            default => ucfirst(str_replace('_', ' ', (string) $status)),
        };

        $statusClass = fn ($status) => match ((string) $status) {
            'active' => 'badge-success',
            'en_demo' => 'badge-warning',
            'desactive' => 'badge-danger',
            default => 'badge-info',
        };

        $initials = function ($name) {
            $parts = preg_split('/\s+/', trim((string) $name), -1, PREG_SPLIT_NO_EMPTY);
            return collect($parts)->take(2)->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->implode('') ?: 'AG';
        };

        $formatDate = function ($value) {
            if (!$value) return 'Non défini';
            try { return \Carbon\Carbon::parse($value)->format('d/m/Y'); }
            catch (\Throwable $e) { return (string) $value; }
        };

        $routeKey = fn ($agence) => $get($agence, 'agence_id', $get($agence, 'code_agence'));
        $formatMoney = fn ($amount) => number_format((float) ($amount ?? 0), 0, ',', ' ') . ' FCFA';

        // Stats par agence (depuis le contrôleur ou défaut)
        $agencyStats = $agenceStats ?? [];
        $statsFor = fn ($agence) => $agencyStats[$get($agence, 'agence_id')] ?? [
            'proprietaires' => 0,
            'locataires' => 0,
            'utilisateurs' => 0,
            'biens' => 0,
            'lots' => 0,
            'tickets' => 0,
            'tickets_resolus' => 0
        ];

        // Abonnement — gestion du cas null
        $totalPaid = fn ($agence) => $get($agence, 'montant_total',
            $get($agence, 'abonnement.montant',
            $get($agence, 'abonnement.prix_ht',
            $get($agence, 'abonnement.prix', 0))));

        // Modules — retourne collection vide si pas d'abonnement
        $activeModules = function ($agence) use ($get) {
            $modules = collect($get($agence, 'modules_payants', []))
                ->filter(fn ($m) => ($m['statut'] ?? 'Actif') === 'Actif')
                ->pluck('nom');

            if ($modules->isEmpty() && $get($agence, 'abonnement')) {
                $modules = collect($get($agence, 'abonnement.modules', []))
                    ->map(fn ($m) => is_array($m) ? ($m['nom'] ?? $m['name'] ?? null) : ($m->nom ?? $m->name ?? $m))
                    ->filter();
            }
            return $modules->values();
        };

        $getBasePrice = fn ($agence) => $get($agence, 'abonnement.prix_ht', $get($agence, 'abonnement.prix', 49900));
        $getModulesPrice = fn ($agence) => $activeModules($agence)->count() * 5000;
    @endphp

    {{-- Toast notification --}}
    <div id="toast-container" aria-live="polite" aria-atomic="true"></div>

    <section class="space-y-6 px-4 py-6 md:px-6 xl:px-8 page">
        <div class="page-header rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="page-header-copy">
                <div class="page-heading">
                    <div>
                        <h2>Gestion des agences</h2>
                        <p class="text-muted mb-0">Consultez les agences, leurs responsables et leur état d'abonnement.</p>
                    </div>
                </div>
            </div>
            <div class="page-actions">
                <a class="btn btn-primary" href="{{ route('admin.agences.create') }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Ajouter une agence
                </a>
            </div>
        </div>

        <div class="stats-grid grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <article class="stat-card rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <span>Total agences</span>
                <strong>{{ $totalAgences }}</strong>
            </article>
            <article class="stat-card rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <span>Actives</span>
                <strong class="is-success">{{ $agencesActives }}</strong>
            </article>
            <article class="stat-card rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <span>En démo</span>
                <strong class="is-info">{{ $agencesDemo }}</strong>
            </article>
            <article class="stat-card rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <span>Désactivées</span>
                <strong class="is-danger">{{ $agencesDesactivees }}</strong>
            </article>
        </div>

        {{-- Shell principal : sidebar + détail --}}
        <div class="agency-shell grid gap-6 xl:grid-cols-[360px_1fr]" id="agency-shell">

            {{-- Overlay mobile --}}
            <div class="agency-overlay" id="agency-overlay" onclick="closeMobileDetail()" aria-hidden="true"></div>

            {{-- ── SIDEBAR ──────────────────────────────────────────── --}}
            <div class="agency-sidebar rounded-3xl border border-slate-200 bg-white shadow-sm" id="agency-sidebar">
                <div class="agency-sidebar-header">
                    <div class="agency-sidebar-top">
                        <span class="agency-sidebar-title">
                            Agences <span class="agency-count">{{ $totalAgences }}</span>
                        </span>
                    </div>

                    <label class="search-field flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3" for="agency-search">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd"/>
                        </svg>
                        <input type="text" placeholder="Rechercher une agence…" id="agency-search" autocomplete="off">
                    </label>

                    <div class="agency-filter-pills flex flex-wrap gap-2">
                        <button class="filter-pill active inline-flex h-9 items-center justify-center rounded-full border border-slate-200 bg-slate-900 px-4 text-sm font-medium text-white" type="button" data-filter="tous">Toutes</button>
                        <button class="filter-pill inline-flex h-9 items-center justify-center rounded-full border border-slate-200 bg-white px-4 text-sm font-medium text-slate-900" type="button" data-filter="active">Actives</button>
                        <button class="filter-pill inline-flex h-9 items-center justify-center rounded-full border border-slate-200 bg-white px-4 text-sm font-medium text-slate-900" type="button" data-filter="en_demo">En démo</button>
                        <button class="filter-pill inline-flex h-9 items-center justify-center rounded-full border border-slate-200 bg-white px-4 text-sm font-medium text-slate-900" type="button" data-filter="desactive">Désactivées</button>
                    </div>
                </div>

                <div class="agency-list" id="agency-list" role="listbox" aria-label="Liste des agences">
                    @forelse($agenceItems as $agence)
                        @php
                            $name        = $get($agence, 'name', 'Agence sans nom');
                            $code        = $get($agence, 'code_agence', $get($agence, 'agence_id', 'N/A'));
                            $status      = $get($agence, 'statut', 'en_demo');
                            $responsable = optional($get($agence, 'responsable'))->name ?? 'Responsable non défini';
                            $phone       = optional($get($agence, 'responsable'))->tel1 ?? $get($agence, 'tel1', 'N/A');
                            $email       = $get($agence, 'email1', 'N/A');
                            $location    = trim(collect([optional($get($agence, 'ville'))->name, optional($get($agence, 'region'))->name])->filter()->implode(', ')) ?: 'Localisation non définie';
                            $abonnement  = optional($get($agence, 'abonnement'))->name ?? 'Aucun abonnement';
                            $planDesc    = optional($get($agence, 'abonnement'))->description ?? 'Accès complet · Annonces illimitées · Support prioritaire';
                            $startDate   = $formatDate($get($agence, 'abonnement_start'));
                            $endDate     = $formatDate($get($agence, 'abonnement_end'));
                            $stats       = $statsFor($agence);
                            $modules     = $activeModules($agence);
                            $editUrl     = route('admin.agences.edit', $routeKey($agence));
                            $basePrice   = $getBasePrice($agence);
                            $modulesPrice = $getModulesPrice($agence);
                            $agenceId    = $routeKey($agence);
                        @endphp

                        <button
                                class="agency-row {{ $loop->first ? 'selected' : '' }}"
                                type="button"
                                role="option"
                                aria-selected="{{ $loop->first ? 'true' : 'false' }}"
                                data-biens="{{ $stats['biens'] }}"
                                data-lots="{{ $stats['lots'] }}"
                                data-tickets="{{ $stats['tickets'] }}"
                                data-tickets-resolus="{{ $stats['tickets_resolus'] }}"
                                data-status="{{ $status }}"
                                data-name="{{ $name }}"
                                data-code="{{ $code }}"
                                data-agency-id="{{ $agenceId }}"
                                data-initials="{{ $initials($name) }}"
                                data-responsable="{{ $responsable }}"
                                data-phone="{{ $phone }}"
                                data-email="{{ $email }}"
                                data-location="{{ $location }}"
                                data-address="{{ $get($agence, 'adresse', 'Adresse non définie') }}"
                                data-abonnement="{{ $abonnement }}"
                                data-plan-desc="{{ $planDesc }}"
                                data-start="{{ $startDate }}"
                                data-end="{{ $endDate }}"
                                data-total-paid="{{ $formatMoney($totalPaid($agence)) }}"
                                data-base-price="{{ $basePrice }}"
                                data-modules-price="{{ $modulesPrice }}"
                                data-proprietaires="{{ $stats['proprietaires'] }}"
                                data-locataires="{{ $stats['locataires'] }}"
                                data-utilisateurs="{{ $stats['utilisateurs'] }}"
                                data-modules="{{ json_encode($modules->isNotEmpty() ? $modules->values()->all() : []) }}"
                                data-status-label="{{ $statusLabel($status) }}"
                                data-status-class="{{ $statusClass($status) }}"
                                data-edit-url="{{ $editUrl }}"
                                data-legal-form="{{ $get($agence, 'forme_juridique', $get($agence, 'type_entreprise', 'Non spécifié')) }}"
                                data-reg-number="{{ $get($agence, 'numero_identification', $get($agence, 'rc_number', $get($agence, 'nif', 'Non spécifié'))) }}"
                                data-tva="{{ $get($agence, 'tva_number', $get($agence, 'numero_tva', 'Non spécifié')) }}"
                                data-employees="{{ $get($agence, 'nombre_employes', $get($agence, 'effectif', 'Non renseigné')) }}"
                                data-country="{{ $get($agence, 'pays', $get($agence, 'country', 'Bénin')) }}"
                                data-capital="{{ $formatMoney($get($agence, 'capital_social', 0)) }}"
                                data-created-at="{{ $formatDate($get($agence, 'date_creation', $get($agence, 'created_at'))) }}"
                                data-siege="{{ $get($agence, 'siege_social', $get($agence, 'adresse', 'Non spécifié')) }}"
                                onclick="selectAgency(this)"
                        >
                            <span class="agency-row-top">
                                <span class="agency-ref">{{ $code }}</span>
                                <span class="badge {{ $statusClass($status) }}">{{ $statusLabel($status) }}</span>
                            </span>
                            <span class="agency-row-main">
                                <span class="entity-avatar">{{ $initials($name) }}</span>
                                <span>
                                    <strong>{{ $name }}</strong>
                                    <small>{{ $responsable }}</small>
                                </span>
                            </span>
                        </button>
                    @empty
                        <div class="agency-empty">Aucune agence trouvée.</div>
                    @endforelse

                    <div class="agency-empty u-hidden" id="agency-no-results">
                        Aucune agence ne correspond à la recherche.
                    </div>
                </div>
            </div>

            {{-- ── PANNEAU DÉTAIL ──────────────────────────────────── --}}
            <div class="agency-detail rounded-3xl border border-slate-200 bg-white shadow-sm" id="agency-detail" aria-label="Détail de l'agence">

                {{-- Bouton retour mobile --}}
                <button class="agency-back-btn inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-900 shadow-sm" id="agency-back-btn" type="button" onclick="closeMobileDetail()" aria-label="Retour à la liste">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 12H5M12 5l-7 7 7 7"/>
                    </svg>
                    Retour
                </button>

                @if($selectedAgence)
                    @php
                        $selectedName    = $get($selectedAgence, 'name', 'Agence sans nom');
                        $selectedStatus  = $get($selectedAgence, 'statut', 'en_demo');
                        $selectedStats   = $statsFor($selectedAgence);
                        $selectedModules = $activeModules($selectedAgence);
                        $selectedAbonnement = $get($selectedAgence, 'abonnement');
                    @endphp

                    {{-- Header du détail --}}
                    <div class="agency-detail-header flex flex-col gap-4 border-b border-slate-200 px-6 py-5 lg:flex-row lg:items-start lg:justify-between">
                        <div class="agency-detail-heading">
                            <span class="agency-detail-ref" id="agency-detail-code">{{ $get($selectedAgence, 'code_agence', $get($selectedAgence, 'agence_id', 'N/A')) }}</span>
                            <h3 id="agency-detail-name">{{ $selectedName }}</h3>
                        </div>
                        <div class="agency-detail-actions">
                            <button
                                    id="agency-toggle-status"
                                    type="button"
                                    class="btn btn-outline btn-sm"
                                    data-current-status="{{ $selectedStatus }}"
                                    data-agency-id="{{ $routeKey($selectedAgence) }}"
                                    onclick="toggleAgencyStatus(this)"
                            >
                                {{ $selectedStatus === 'active' ? 'Désactiver' : 'Activer' }}
                            </button>
                            <a id="agency-edit-link" href="{{ route('admin.agences.edit', $routeKey($selectedAgence)) }}" class="btn btn-primary btn-sm">Modifier</a>
                        </div>
                    </div>

                    {{-- Barre d'onglets --}}
                    <div class="tabs-list flex flex-wrap gap-2 border-b border-slate-200 px-6 pt-5">
                        <button class="tab-btn active inline-flex h-10 items-center justify-center rounded-t-xl border border-b-0 border-slate-200 bg-white px-4 text-sm font-medium text-slate-900" data-tab="info" onclick="switchTab('info', this)" type="button">Informations</button>
                        <button class="tab-btn inline-flex h-10 items-center justify-center rounded-t-xl border border-b-0 border-slate-200 bg-slate-50 px-4 text-sm font-medium text-slate-600" data-tab="abonnement" onclick="switchTab('abonnement', this)" type="button">Abonnement</button>
                        <button class="tab-btn inline-flex h-10 items-center justify-center rounded-t-xl border border-b-0 border-slate-200 bg-slate-50 px-4 text-sm font-medium text-slate-600" data-tab="life" onclick="switchTab('life', this)" type="button">Vie de l'agence</button>
                    </div>

                    {{-- Meta bar commune --}}
                    <div class="agency-meta-bar">
                        <div class="agency-meta-item">
                            <span>Statut</span>
                            <strong><span id="agency-detail-status" class="badge {{ $statusClass($selectedStatus) }}">{{ $statusLabel($selectedStatus) }}</span></strong>
                        </div>
                        <div class="agency-meta-item">
                            <span>Abonnement</span>
                            <strong id="agency-detail-abonnement">{{ optional($selectedAbonnement)->name ?? 'Aucun abonnement' }}</strong>
                        </div>
                        <div class="agency-meta-item">
                            <span>Début</span>
                            <strong id="agency-detail-start">{{ $formatDate($get($selectedAgence, 'abonnement_start')) }}</strong>
                        </div>
                        <div class="agency-meta-item">
                            <span>Fin</span>
                            <strong id="agency-detail-end">{{ $formatDate($get($selectedAgence, 'abonnement_end')) }}</strong>
                        </div>
                        <div class="agency-meta-item">
                            <span>Total payé</span>
                            <strong id="agency-detail-total-paid">{{ $formatMoney($totalPaid($selectedAgence)) }}</strong>
                        </div>
                    </div>

                    {{-- ═══ ONGLET : INFORMATIONS ═══ --}}
                    <div class="tab-panel active" id="tab-info">
                        <div class="agency-detail-body">

                            {{-- Carte d'identité --}}
                            <div class="agency-identity-card">
                                <div class="entity-avatar lg" id="agency-detail-initials">{{ $initials($selectedName) }}</div>
                                <div class="agency-identity-info">
                                    <div class="agency-identity-row">
                                        <span class="agency-identity-label">Raison sociale :</span>
                                        <strong class="agency-identity-value" id="agency-detail-name-full">{{ $selectedName }}</strong>
                                    </div>
                                    <div class="agency-identity-row">
                                        <span class="agency-identity-label">Forme juridique :</span>
                                        <strong class="agency-identity-value" id="agency-detail-legal-form">
                                            {{ $get($selectedAgence, 'forme_juridique', $get($selectedAgence, 'type_entreprise', 'Non spécifié')) }}
                                        </strong>
                                    </div>
                                    <div class="agency-identity-row">
                                        <span class="agency-identity-label">N° d'identification :</span>
                                        <strong class="agency-identity-value" id="agency-detail-reg-number">
                                            {{ $get($get($selectedAgence, 'responsable'), 'numero_identification', $get($selectedAgence, 'numero_identification', $get($selectedAgence, 'rc_number', $get($selectedAgence, 'nif', 'Non spécifié')))) }}
                                        </strong>
                                    </div>
                                    <div class="agency-identity-row">
                                        <span class="agency-identity-label">Numéro de TVA :</span>
                                        <strong class="agency-identity-value" id="agency-detail-tva">
                                            {{ $get($selectedAgence, 'tva_number', $get($selectedAgence, 'numero_tva', 'Non spécifié')) }}
                                        </strong>
                                    </div>
                                </div>
                            </div>

                            {{-- Coordonnées --}}
                            <div class="agency-contact-section">
                                <h4 class="agency-section-title">Coordonnées</h4>
                                <div class="agency-contact-grid">
                                    <div class="agency-contact-item">
                                        <span>Email</span>
                                        <strong id="agency-detail-email">{{ $get($selectedAgence, 'email1', $get($selectedAgence, 'email', 'Non défini')) }}</strong>
                                    </div>
                                    <div class="agency-contact-item">
                                        <span>Téléphone</span>
                                        <strong id="agency-detail-phone">{{ optional($get($selectedAgence, 'responsable'))->tel1 ?? $get($selectedAgence, 'tel1', $get($selectedAgence, 'phone', 'Non défini')) }}</strong>
                                    </div>
                                    <div class="agency-contact-item agency-contact-full">
                                        <span>Adresse</span>
                                        <strong id="agency-detail-address">{{ $get($selectedAgence, 'adresse', 'Adresse non définie') }}</strong>
                                    </div>
                                </div>
                            </div>

                            {{-- Localisation & effectif --}}
                            <div class="agency-detail-grid">
                                <div class="agency-detail-card">
                                    <div class="agency-detail-badge">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                            <circle cx="12" cy="10" r="3"/>
                                        </svg>
                                        Localisation
                                    </div>
                                    <strong id="agency-detail-location">
                                        {{ trim(collect([optional($get($selectedAgence, 'ville'))->name, optional($get($selectedAgence, 'region'))->name])->filter()->implode(', ')) ?: 'Localisation non définie' }}
                                    </strong>
                                    <span class="agency-detail-sub" id="agency-detail-country">
                                        {{ $get($selectedAgence, 'pays', $get($selectedAgence, 'country', 'Bénin')) }}
                                    </span>
                                </div>
                                <div class="agency-detail-card">
                                    <div class="agency-detail-badge">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                            <circle cx="9" cy="7" r="4"/>
                                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                                            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                                        </svg>
                                        Effectif
                                    </div>
                                    <strong id="agency-detail-employees">{{ $get($selectedAgence, 'nombre_employes', $get($selectedAgence, 'effectif', 'Non renseigné')) }}</strong>
                                    <span class="agency-detail-sub">employés</span>
                                </div>
                            </div>

                            {{-- Informations légales --}}
                            <div class="agency-legal-section">
                                <h4 class="agency-section-title">Informations légales</h4>
                                <div class="agency-legal-grid">
                                    <div class="agency-legal-item">
                                        <span>Date de création</span>
                                        <strong id="agency-detail-created-at">{{ $formatDate($get($selectedAgence, 'date_creation', $get($selectedAgence, 'created_at'))) }}</strong>
                                    </div>
                                    <div class="agency-legal-item">
                                        <span>Capital social</span>
                                        <strong id="agency-detail-capital">{{ $formatMoney($get($selectedAgence, 'capital_social', 0)) }}</strong>
                                    </div>
                                    <div class="agency-legal-item agency-legal-full">
                                        <span>Siège social</span>
                                        <strong id="agency-detail-siege">{{ $get($selectedAgence, 'siege_social', $get($selectedAgence, 'adresse', 'Non spécifié')) }}</strong>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    {{-- ═══ ONGLET : ABONNEMENT (COMPLET) ═══ --}}
                    <div class="tab-panel" id="tab-abonnement">
                        <div class="agency-detail-body">

                            {{-- Alerte renouvellement --}}
                            <div id="sub-renewal-alert" class="alert {{ $selectedAbonnement ? '' : 'u-hidden' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <div>
                                    <strong id="renewal-title">Renouvellement automatique bientôt</strong>
                                    <p id="renewal-message"></p>
                                </div>
                            </div>

                            {{-- Plan actuel --}}
                            <div class="agency-plan-card {{ $selectedAbonnement ? '' : 'agency-plan-card--empty' }}">
                                <div class="agency-plan-flex">
                                    <div class="agency-plan-info">
                                        <div class="sub-status-badge" id="sub-status-badge">
                                            <span class="status-dot {{ $selectedStatus === 'active' ? 'is-active' : ($selectedStatus === 'en_demo' ? 'is-demo' : 'is-inactive') }}"></span>
                                            {{ $selectedAbonnement ? 'Actif' : 'Aucun abonnement' }}
                                        </div>
                                        <div class="agency-plan-name" id="sub-plan-name">
                                            {{ optional($selectedAbonnement)->name ?? 'Aucun plan souscrit' }}
                                        </div>
                                        <div class="agency-plan-desc" id="sub-plan-desc">
                                            {{ optional($selectedAbonnement)->description ?? 'Cette agence n\'a pas encore souscrit à un abonnement.' }}
                                        </div>
                                        <div class="agency-modules-badges" id="sub-modules-badges">
                                            @foreach($selectedModules as $module)
                                                <span class="module-badge">{{ $module }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="agency-plan-pricing">
                                        @if($selectedAbonnement)
                                            <div class="price-label">Prix de base / mois</div>
                                            <div class="price-base" id="sub-base-price">{{ $formatMoney($getBasePrice($selectedAgence)) }}</div>
                                            <div class="price-modules" id="sub-modules-total">+ Modules : {{ $formatMoney($getModulesPrice($selectedAgence)) }}</div>
                                            <div class="price-divider"></div>
                                            <div class="price-label">Total / mois</div>
                                            <div class="price-total" id="sub-total-display">{{ $formatMoney($getBasePrice($selectedAgence) + $getModulesPrice($selectedAgence)) }}</div>
                                            <div class="next-renewal" id="sub-next-renewal">Prochain renouvellement : {{ $formatDate($get($selectedAgence, 'abonnement_end')) }}</div>
                                            <div class="member-since" id="sub-member-since">Membre depuis : {{ $formatDate($get($selectedAgence, 'abonnement_start')) }}</div>
                                        @else
                                            <div class="price-label">Prix de base / mois</div>
                                            <div class="price-base" id="sub-base-price">—</div>
                                            <div class="price-modules" id="sub-modules-total">+ Modules : —</div>
                                            <div class="price-divider"></div>
                                            <div class="price-label">Total / mois</div>
                                            <div class="price-total" id="sub-total-display">—</div>
                                            <div class="next-renewal" id="sub-next-renewal">Aucune période active</div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Actions abonnement --}}
                            <div class="agency-actions-group">
                                <button id="sub-manage-btn" class="btn btn-outline btn-sm" onclick="gererAbonnement('{{ $routeKey($selectedAgence) }}')" {{ $selectedAbonnement ? '' : 'disabled' }}>
                                    Gérer l'abonnement
                                </button>
                                <button id="sub-renewal-toggle-btn" class="btn btn-outline btn-sm" onclick="toggleRenouvellementAuto('{{ $routeKey($selectedAgence) }}')" {{ $selectedAbonnement ? '' : 'disabled' }}>
                                    Renouvellement auto
                                </button>
                                <button id="sub-cancel-btn" class="btn btn-danger-outline btn-sm" onclick="confirmerResiliation('{{ $routeKey($selectedAgence) }}')" {{ $selectedAbonnement ? '' : 'disabled' }}>
                                    Résilier
                                </button>
                            </div>

                            {{-- Stats abonnement --}}
                            <div class="agency-stats-grid">
                                <div class="agency-sub-stat-card">
                                    <span>Total facturé (cumul)</span>
                                    <strong class="is-success" id="sub-stat-total">{{ $formatMoney($totalPaid($selectedAgence)) }}</strong>
                                    <small>depuis l'activation</small>
                                </div>
                                <div class="agency-sub-stat-card">
                                    <span>Modules actifs</span>
                                    <strong id="sub-stat-modules">{{ $selectedModules->count() }} / 4</strong>
                                    <small>modules complémentaires</small>
                                </div>
                                <div class="agency-sub-stat-card">
                                    <span>Période en cours</span>
                                    <strong id="sub-period-current">
                                        {{ $formatDate($get($selectedAgence, 'abonnement_start')) }} → {{ $formatDate($get($selectedAgence, 'abonnement_end')) }}
                                    </strong>
                                    <small>cycle de facturation</small>
                                </div>
                                <div class="agency-sub-stat-card">
                                    <span>Paiements réussis</span>
                                    <strong class="is-info" id="sub-payments-success">—</strong>
                                    <small>tentatives</small>
                                </div>
                            </div>

                            {{-- Historique facturation (dynamique) --}}
                            <div class="agency-history-card">
                                <div class="agency-history-header">
                                    <div>
                                        <div class="history-title">Historique des factures</div>
                                        <div class="history-subtitle">Cycles de facturation mensuels</div>
                                    </div>
                                    <button id="sub-export-btn" class="btn btn-outline btn-sm" onclick="exportFacturation('{{ $routeKey($selectedAgence) }}')" type="button" {{ $selectedAbonnement ? '' : 'disabled' }}>
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="export-icon">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1M12 12V4m0 0L8 8m4-4l4 4"/>
                                        </svg>
                                        Exporter
                                    </button>
                                </div>
                                <div class="history-table-wrapper">
                                    <table class="data-table">
                                        <thead>
                                        <tr>
                                            <th>Période</th>
                                            <th>Modules</th>
                                            <th>Base</th>
                                            <th>Modules suppl.</th>
                                            <th>Total</th>
                                            <th>Statut</th>
                                            <th>Paiement</th>
                                            <th>Facture</th>
                                        </tr>
                                        </thead>
                                        <tbody id="sub-invoices-list">
                                        @if($selectedAbonnement)
                                            <tr>
                                                <td colspan="8" class="loading-cell">
                                                    <div class="loading-spinner"></div>
                                                    <span>Chargement en cours...</span>
                                                </td>
                                            </tr>
                                        @else
                                            <tr>
                                                <td colspan="8" class="empty-cell">
                                                    Aucune facture disponible — aucun abonnement actif.
                                                </td>
                                            </tr>
                                        @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        </div>
                    </div>

                    {{-- ═══ ONGLET : VIE DE L'AGENCE ═══ --}}
                    <div class="tab-panel" id="tab-life">
                        <div class="agency-detail-body">

                            {{-- Stats clés (plus complètes) --}}
                            <div class="agency-life-stats-grid">
                                <div class="agency-sub-stat-card">
                                    <span>Locataires</span>
                                    <strong id="life-locataires">{{ $selectedStats['locataires'] ?? 0 }}</strong>
                                </div>
                                <div class="agency-sub-stat-card">
                                    <span>Propriétaires</span>
                                    <strong id="life-proprietaires">{{ $selectedStats['proprietaires'] ?? 0 }}</strong>
                                </div>
                                <div class="agency-sub-stat-card">
                                    <span>Biens</span>
                                    <strong id="life-biens">{{ $selectedStats['biens'] ?? 0 }}</strong>
                                </div>
                                <div class="agency-sub-stat-card">
                                    <span>Lots</span>
                                    <strong id="life-lots">{{ $selectedStats['lots'] ?? 0 }}</strong>
                                </div>
                                <div class="agency-sub-stat-card">
                                    <span>Utilisateurs</span>
                                    <strong id="life-utilisateurs">{{ $selectedStats['utilisateurs'] ?? 0 }}</strong>
                                </div>
                                <div class="agency-sub-stat-card">
                                    <span>Tickets</span>
                                    <strong id="life-tickets">{{ $selectedStats['tickets'] ?? 0 }}</strong>
                                </div>
                                <div class="agency-sub-stat-card">
                                    <span>Tickets résolus</span>
                                    <strong id="life-tickets-resolus">{{ $selectedStats['tickets_resolus'] ?? 0 }}</strong>
                                </div>
                                <div class="agency-sub-stat-card">
                                    <span>Taux résolution</span>
                                    <strong id="life-taux-resolution">
                                        @php
                                            $totalTickets = $selectedStats['tickets'] ?? 0;
                                            $resolus = $selectedStats['tickets_resolus'] ?? 0;
                                            $taux = $totalTickets > 0 ? round(($resolus / $totalTickets) * 100) : 0;
                                        @endphp
                                        {{ $taux }}%
                                    </strong>
                                </div>
                            </div>

                            {{-- Timeline activités --}}
                            <div class="agency-activities-card">
                                <div class="agency-activities-header">
                                    <div>
                                        <div class="activities-title">Activités récentes</div>
                                        <div class="activities-subtitle">Historique des événements de l'agence</div>
                                    </div>
                                    <button class="btn btn-outline btn-sm" id="life-refresh-btn" onclick="loadAgencyLife('{{ $routeKey($selectedAgence) }}')" type="button">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="refresh-icon">
                                            <path d="M23 4v6h-6M1 20v-6h6M3.51 9a9 9 0 0114.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0020.49 15"/>
                                        </svg>
                                        Actualiser
                                    </button>
                                </div>
                                <div id="life-activities-container" class="activities-container">
                                    <div class="activities-empty">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m6-2a10 10 0 11-20 0 10 10 0 0120 0z"/>
                                        </svg>
                                        Cliquez sur "Actualiser" pour charger les activités.
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                @else
                    <div class="agency-empty-detail">
                        <h3>Aucune agence à afficher</h3>
                        <p>Créez une agence pour voir sa fiche ici.</p>
                    </div>
                @endif
            </div>
        </div>

        @if($isPaginator)
            <div class="agency-pagination">{{ $agences->links() }}</div>
        @endif
    </section>
    <script src="{{ asset('admin/js/agences-index.js') }}"></script>
@endsection
