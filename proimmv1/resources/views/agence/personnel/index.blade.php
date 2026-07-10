@extends('agence.layouts.app')

@section('title', 'Personnel')

@section('content')
    @php
        $sel = $personnel->first();

        $statusLabel = fn ($s) => match ($s) {
            'actif'    => 'Actif',
            'inactif'  => 'Inactif',

            default    => ucfirst((string) $s),
        };

        $statusClass = fn ($s) => match ($s) {
            'actif'    => 'badge-success',
            'inactif'  => 'badge-danger',
           // 'suspendu' => 'badge-warning',
            default    => 'badge-info',
        };

        $roleLabel = fn ($r) => match ($r) {
            'admin'        => 'Administrateur',
            'gestionnaire' => 'Gestionnaire',
            'commercial'   => 'Commercial',
            'comptable'    => 'Comptable',
            'technicien'   => 'Technicien',
            default        => ucfirst((string) $r),
        };

        $roleClass = fn ($r) => match ($r) {
            'admin'        => 'badge-danger',
            'gestionnaire' => 'badge-primary',
            'commercial'   => 'badge-success',
            'comptable'    => 'badge-warning',
            default        => 'badge-info',
        };

        $initials = function ($name) {
            $parts = preg_split('/\s+/', trim((string) $name), -1, PREG_SPLIT_NO_EMPTY);
            return collect($parts)->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('') ?: 'PE';
        };

        $formatDate = function ($value) {
            if (!$value) return 'Non défini';
            try { return \Carbon\Carbon::parse($value)->format('d/m/Y'); }
            catch (\Throwable $e) { return (string) $value; }
        };
    @endphp

    {{-- Toast notification --}}
    <div id="toast-container" aria-live="polite" aria-atomic="true"></div>

    <section class="page">
        <div class="page-header">
            <div class="page-header-copy">
                <div class="page-heading">
                    <div>
                        <h2>Gestion du personnel</h2>
                        <p class="text-muted mb-0">Consultez les membres du personnel, leurs rôles et leur statut.</p>
                    </div>
                </div>
            </div>
            <div class="page-actions">
                <a class="btn btn-outline" href="#">Rôles &amp; permissions</a>
                <a class="btn btn-primary" href="{{ route('agence.personnel.create') }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Ajouter un membre
                </a>
            </div>
        </div>

        <div class="stats-grid">
            <article class="stat-card">
                <span>Total membres</span>
                <strong>{{ $stats['total'] }}</strong>
            </article>
            <article class="stat-card">
                <span>Actifs</span>
                <strong class="is-success">{{ $stats['actifs'] }}</strong>
            </article>
            <article class="stat-card">
                <span>En congé</span>
                <strong class="is-info">{{ $stats['suspendu'] }}</strong>
            </article>
            <article class="stat-card">
                <span>Inactifs</span>
                <strong class="is-danger">{{ $stats['inactifs'] }}</strong>
            </article>
        </div>

        <div class="agency-shell" id="agency-shell">

            <div class="agency-overlay" id="agency-overlay" onclick="closeMobileDetail()" aria-hidden="true"></div>

            {{-- ── SIDEBAR ─────────────────────────────────────────── --}}
            <div class="agency-sidebar">
                <div class="agency-sidebar-header">
                    <div class="agency-sidebar-top">
                        <span class="agency-sidebar-title">
                            Personnel <span class="agency-count">{{ $stats['total'] }}</span>
                        </span>
                    </div>

                    <label class="search-field u-search-compact" for="personnel-search">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd"/>
                        </svg>
                        <input type="text" placeholder="Rechercher un membre…" id="personnel-search" autocomplete="off">
                    </label>

                    <div class="agency-filter-pills">
                        <button class="filter-pill active" type="button" data-filter="tous">Tous</button>
                        <button class="filter-pill" type="button" data-filter="actif">Actifs</button>
                        <button class="filter-pill" type="button" data-filter="suspendu">Suspendus</button>
                        <button class="filter-pill" type="button" data-filter="inactif">Inactifs</button>
                    </div>
                </div>

                <div class="agency-list" role="listbox" aria-label="Liste du personnel">
                    @foreach($personnel as $membre)
                        @php $roleName = $membre->role?->name ?? ''; @endphp
                        <button
                                class="agency-row {{ $loop->first ? 'selected' : '' }}"
                                type="button"
                                role="option"
                                aria-selected="{{ $loop->first ? 'true' : 'false' }}"
                                data-status="{{ $membre->statut }}"
                                data-name="{{ $membre->name }}"
                                data-matricule="{{ $membre->id_users }}"
                                data-initials="{{ $initials($membre->name) }}"
                                data-role="{{ $roleName }}"
                                data-role-label="{{ $roleLabel($roleName) }}"
                                data-role-class="{{ $roleClass($roleName) }}"
                                data-email="{{ $membre->email }}"
                                data-phone="{{ $membre->tel1 }}"
                                data-poste="{{ $roleLabel($roleName) }}"
                                data-agence="{{ $membre->agence?->nom ?? 'N/A' }}"
                                data-departement="{{ $membre->role?->name ?? 'N/A' }}"
                                data-superviseur="N/A"
                                data-adresse="{{ $membre->adresse }}"
                                data-date-embauche="{{ $formatDate($membre->created_at) }}"
                                data-permissions="{{ json_encode($membre->getPermissions()) }}"
                                data-status-label="{{ $statusLabel($membre->statut) }}"
                                data-status-class="{{ $statusClass($membre->statut) }}"
                                data-show-url="#"
                                data-edit-url="{{ route('agence.personnel.edit', $membre->id_users) }}"
                                data-activate-url="{{ route('agence.personnel.activate', $membre->id_users) }}"
                                data-deactivate-url="{{ route('agence.personnel.deactivate', $membre->id_users) }}"
                                onclick="selectMembre(this)"
                        >
                          <span class="agency-row-top">
                                <span class="badge {{ $statusClass($membre->statut) }}" style="margin-left: auto">
                                    {{ $statusLabel($membre->statut) }}
                                </span>
                            </span>
                            <span class="agency-row-main">
                                <span class="entity-avatar">{{ $initials($membre->name) }}</span>
                                <span>
                                    <strong>{{ $membre->name }}</strong>
                                    <small>{{ $roleLabel($roleName) }}</small>
                                </span>
                            </span>
                        </button>
                    @endforeach

                    <div class="agency-empty u-hidden" id="personnel-no-results">
                        Aucun membre ne correspond à la recherche.
                    </div>
                </div>
            </div>

            {{-- ── PANNEAU DÉTAIL ───────────────────────────────────── --}}
            @if($sel)
                @php $selRole = $sel->role?->name ?? ''; @endphp
                <div class="agency-detail" id="agency-detail" aria-label="Détail du membre">

                    <button class="agency-back-btn" type="button" onclick="closeMobileDetail()" aria-label="Retour à la liste">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 12H5M12 5l-7 7 7 7"/>
                        </svg>
                        Retour
                    </button>

                    <div class="agency-detail-header">
                        <div class="agency-detail-heading ">
{{--                            <span class="agency-detail-ref" id="personnel-detail-matricule">{{ $sel->id_users }}</span>--}}
                            <h3 id="personnel-detail-name ">{{ $sel->name }}</h3>
                        </div>
                        <div class="agency-detail-actions">
                            <button
                                    id="personnel-toggle-status"
                                    type="button"
                                    class="btn btn-outline btn-sm"
                                    data-current-status="{{ $sel->statut }}"
                                    data-activate-url="{{ route('agence.personnel.activate', $sel->id_users) }}"
                                    data-deactivate-url="{{ route('agence.personnel.deactivate', $sel->id_users) }}"
                                    onclick="toggleMembreStatus(this)"
                            >
                                {{ $sel->statut === 'actif' ? 'Désactiver' : 'Activer' }}
                            </button>
                            <a id="personnel-show-link" href="#" class="btn btn-outline btn-sm">Voir</a>
                            <a id="personnel-edit-link"
                               href="{{ route('agence.personnel.edit', $sel->id_users) }}"
                               class="btn btn-primary btn-sm">Modifier</a>
                        </div>
                    </div>

                    <div class="agency-meta-bar">
                        <div class="agency-meta-item">
                            <span>Statut</span>
                            <strong>
                            <span id="personnel-detail-status" class="badge {{ $statusClass($sel->statut) }}">
                                {{ $statusLabel($sel->statut) }}
                            </span>
                            </strong>
                        </div>
                        <div class="agency-meta-item">
                            <span>Rôle</span>
                            <strong>
                            <span id="personnel-detail-role" class="badge {{ $roleClass($selRole) }}">
                                {{ $roleLabel($selRole) }}
                            </span>
                            </strong>
                        </div>
                        <div class="agency-meta-item">
                            <span>Poste</span>
                            <strong id="personnel-detail-poste">{{ $roleLabel($selRole) }}</strong>
                        </div>
                        <div class="agency-meta-item">
                            <span>Date d'embauche</span>
                            <strong id="personnel-detail-date-embauche">{{ $formatDate($sel->created_at) }}</strong>
                        </div>
                        <div class="agency-meta-item">
                            <span>Responsable</span>
                            <strong id="personnel-detail-salaire">{{ $sel->is_responsable ? 'Oui' : 'Non' }}</strong>
                        </div>
                    </div>

                    <div class="agency-detail-body">
                        <div class="agency-profile-card">
                            <div class="entity-avatar lg" id="personnel-detail-initials">{{ $initials($sel->name) }}</div>
                            <div>
                                <strong id="personnel-detail-name-card">{{ $sel->name }}</strong>
                                <span id="personnel-detail-email">{{ $sel->email }}</span>
                            </div>
                        </div>

                        <div class="agency-info-grid">
                            <div class="agency-info-item">
                                <span>Téléphone</span>
                                <strong id="personnel-detail-phone">{{ $sel->tel1 ?? 'N/A' }}</strong>
                            </div>
                            <div class="agency-info-item">
                                <span>Agence</span>
                                <strong id="personnel-detail-agence">{{ $sel->agence?->nom ?? 'N/A' }}</strong>
                            </div>
                            <div class="agency-info-item">
                                <span>Département</span>
                                <strong id="personnel-detail-departement">{{ $roleLabel($selRole) }}</strong>
                            </div>
                            <div class="agency-info-item">
                                <span>Superviseur</span>
                                <strong id="personnel-detail-superviseur">N/A</strong>
                            </div>
                            <div class="agency-info-item agency-info-wide">
                                <span>Adresse</span>
                                <strong id="personnel-detail-adresse">{{ $sel->adresse ?? 'N/A' }}</strong>
                            </div>
                            <div class="agency-info-item agency-info-wide">
                                <span>Permissions</span>
                                <div class="agency-module-list" id="personnel-detail-permissions">
                                    @foreach($sel->getPermissions() as $perm)
                                        <span>{{ $perm }}</span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>

    <script>
        function selectMembre(btn) {
            document.querySelectorAll('.agency-row').forEach(r => {
                r.classList.remove('selected');
                r.setAttribute('aria-selected', 'false');
            });
            btn.classList.add('selected');
            btn.setAttribute('aria-selected', 'true');

            const d = btn.dataset;

            setText('personnel-detail-matricule',   d.matricule);
            setText('personnel-detail-name',         d.name);
            setText('personnel-detail-name-card',    d.name);
            setText('personnel-detail-email',        d.email);
            setText('personnel-detail-phone',        d.phone    || 'N/A');
            setText('personnel-detail-agence',       d.agence   || 'N/A');
            setText('personnel-detail-departement',  d.departement || 'N/A');
            setText('personnel-detail-superviseur',  d.superviseur || 'N/A');
            setText('personnel-detail-adresse',      d.adresse  || 'N/A');
            setText('personnel-detail-poste',        d.poste    || 'N/A');
            setText('personnel-detail-date-embauche',d.dateEmbauche || 'N/A');

            setBadge('personnel-detail-status', d.statusLabel, d.statusClass);
            setBadge('personnel-detail-role',   d.roleLabel,   d.roleClass);

            // Initiales
            const initialsEl = document.getElementById('personnel-detail-initials');
            if (initialsEl) initialsEl.textContent = d.initials;

            // Permissions
            const permsEl = document.getElementById('personnel-detail-permissions');
            if (permsEl) {
                const perms = JSON.parse(d.permissions || '[]');
                permsEl.innerHTML = perms.map(p => `<span>${p}</span>`).join('');
            }

            // Liens
            setHref('personnel-edit-link', d.editUrl);

            // Toggle bouton
            const toggleBtn = document.getElementById('personnel-toggle-status');
            if (toggleBtn) {
                toggleBtn.dataset.currentStatus  = d.status;
                toggleBtn.dataset.activateUrl    = d.activateUrl;
                toggleBtn.dataset.deactivateUrl  = d.deactivateUrl;
                toggleBtn.textContent = d.status === 'actif' ? 'Désactiver' : 'Activer';
            }

            document.getElementById('agency-shell')?.classList.add('detail-open');
        }

        function toggleMembreStatus(btn) {
            const isActif = btn.dataset.currentStatus === 'actif';
            const url     = isActif ? btn.dataset.deactivateUrl : btn.dataset.activateUrl;

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = url;
            form.innerHTML = `
                @csrf
            <input type="hidden" name="_method" value="PATCH">
`;
            document.body.appendChild(form);
            form.submit();
        }

        function setText(id, value) {
            const el = document.getElementById(id);
            if (el) el.textContent = value;
        }

        function setHref(id, value) {
            const el = document.getElementById(id);
            if (el && value) el.href = value;
        }

        function setBadge(id, label, cls) {
            const el = document.getElementById(id);
            if (!el) return;
            el.textContent = label;
            el.className   = 'badge ' + cls;
        }

        function closeMobileDetail() {
            document.getElementById('agency-shell')?.classList.remove('detail-open');
        }

        // Recherche
        document.getElementById('personnel-search')?.addEventListener('input', filterList);

        // Filtres
        document.querySelectorAll('.filter-pill').forEach(pill => {
            pill.addEventListener('click', function () {
                document.querySelectorAll('.filter-pill').forEach(p => p.classList.remove('active'));
                this.classList.add('active');
                filterList();
            });
        });

        function filterList() {
            const search     = document.getElementById('personnel-search')?.value.toLowerCase() ?? '';
            const activePill = document.querySelector('.filter-pill.active')?.dataset.filter ?? 'tous';
            const rows       = document.querySelectorAll('.agency-row');
            let visible      = 0;

            rows.forEach(row => {
                const matchStatus = activePill === 'tous' || row.dataset.status === activePill;
                const matchSearch = row.dataset.name.toLowerCase().includes(search)
                    || (row.dataset.email ?? '').toLowerCase().includes(search)
                    || (row.dataset.phone ?? '').toLowerCase().includes(search);

                const show = matchStatus && matchSearch;
                row.style.display = show ? '' : 'none';
                if (show) visible++;
            });

            const noResults = document.getElementById('personnel-no-results');
            if (noResults) noResults.classList.toggle('u-hidden', visible > 0);
        }
    </script>
@endsection




{{--@extends('agence.layouts.app')--}}

{{--@section('title', 'Personnel')--}}


{{--@section('content')--}}
{{--    @php--}}
{{--        // ── Données statiques de démonstration ────────────────────────--}}
{{--        $personnelItems = collect([--}}
{{--            [--}}
{{--                'personnel_id'   => 'PER-001',--}}
{{--                'matricule'      => 'MAT-001',--}}
{{--                'name'           => 'Kouamé Jean-Baptiste',--}}
{{--                'statut'         => 'actif',--}}
{{--                'role'           => 'gestionnaire',--}}
{{--                'poste'          => 'Gestionnaire locatif',--}}
{{--                'email'          => 'jb.kouame@agence.ci',--}}
{{--                'tel1'           => '+225 07 01 23 45 67',--}}
{{--                'date_embauche'  => '2021-03-15',--}}
{{--                'salaire'        => 450000,--}}
{{--                'departement'    => 'Gestion locative',--}}
{{--                'superviseur_nom'=> 'Yao Ama Directrice',--}}
{{--                'agence_nom'     => 'Agence Plateau',--}}
{{--                'adresse'        => 'Cocody Riviera 3, Abidjan',--}}
{{--                'permissions'    => ['Gérer contrats', 'Encaisser loyers', 'Voir propriétaires'],--}}
{{--            ],--}}
{{--            [--}}
{{--                'personnel_id'   => 'PER-002',--}}
{{--                'matricule'      => 'MAT-002',--}}
{{--                'name'           => 'Traoré Fatoumata',--}}
{{--                'statut'         => 'actif',--}}
{{--                'role'           => 'commercial',--}}
{{--                'poste'          => 'Chargée de clientèle',--}}
{{--                'email'          => 'f.traore@agence.ci',--}}
{{--                'tel1'           => '+225 05 44 78 12 99',--}}
{{--                'date_embauche'  => '2022-07-01',--}}
{{--                'salaire'        => 380000,--}}
{{--                'departement'    => 'Commercial',--}}
{{--                'superviseur_nom'=> 'Kouamé Jean-Baptiste',--}}
{{--                'agence_nom'     => 'Agence Plateau',--}}
{{--                'adresse'        => 'Marcory Zone 4, Abidjan',--}}
{{--                'permissions'    => ['Créer prospects', 'Gérer visites'],--}}
{{--            ],--}}
{{--            [--}}
{{--                'personnel_id'   => 'PER-003',--}}
{{--                'matricule'      => 'MAT-003',--}}
{{--                'name'           => "N'Guessan Rodrigue",--}}
{{--                'statut'         => 'en_conge',--}}
{{--                'role'           => 'comptable',--}}
{{--                'poste'          => 'Comptable principal',--}}
{{--                'email'          => 'r.nguessan@agence.ci',--}}
{{--                'tel1'           => '+225 01 56 89 34 22',--}}
{{--                'date_embauche'  => '2020-01-10',--}}
{{--                'salaire'        => 520000,--}}
{{--                'departement'    => 'Finance',--}}
{{--                'superviseur_nom'=> 'Yao Ama Directrice',--}}
{{--                'agence_nom'     => 'Agence Plateau',--}}
{{--                'adresse'        => 'Yopougon Selmer, Abidjan',--}}
{{--                'permissions'    => ['Voir finances', 'Éditer factures', 'Exporter rapports'],--}}
{{--            ],--}}
{{--            [--}}
{{--                'personnel_id'   => 'PER-004',--}}
{{--                'matricule'      => 'MAT-004',--}}
{{--                'name'           => 'Bamba Seydou',--}}
{{--                'statut'         => 'inactif',--}}
{{--                'role'           => 'technicien',--}}
{{--                'poste'          => 'Technicien maintenance',--}}
{{--                'email'          => 's.bamba@agence.ci',--}}
{{--                'tel1'           => '+225 07 77 11 55 43',--}}
{{--                'date_embauche'  => '2019-05-20',--}}
{{--                'salaire'        => 310000,--}}
{{--                'departement'    => 'Technique',--}}
{{--                'superviseur_nom'=> 'Non défini',--}}
{{--                'agence_nom'     => 'Agence Cocody',--}}
{{--                'adresse'        => 'Abobo Baoulé, Abidjan',--}}
{{--                'permissions'    => ['Voir biens', 'Créer rapports maintenance'],--}}
{{--            ],--}}
{{--            [--}}
{{--                'personnel_id'   => 'PER-005',--}}
{{--                'matricule'      => 'MAT-005',--}}
{{--                'name'           => 'Yao Ama',--}}
{{--                'statut'         => 'actif',--}}
{{--                'role'           => 'admin',--}}
{{--                'poste'          => 'Directrice générale',--}}
{{--                'email'          => 'a.yao@agence.ci',--}}
{{--                'tel1'           => '+225 05 00 12 00 00',--}}
{{--                'date_embauche'  => '2018-01-02',--}}
{{--                'salaire'        => 900000,--}}
{{--                'departement'    => 'Direction',--}}
{{--                'superviseur_nom'=> 'Non défini',--}}
{{--                'agence_nom'     => 'Siège',--}}
{{--                'adresse'        => 'Plateau, Abidjan',--}}
{{--                'permissions'    => ['Accès total', 'Gestion utilisateurs', 'Paramètres système'],--}}
{{--            ],--}}
{{--        ]);--}}

{{--        $totalPersonnel    = $personnelItems->count();--}}
{{--        $personnelActifs   = $personnelItems->where('statut', 'actif')->count();--}}
{{--        $personnelConges   = $personnelItems->where('statut', 'en_conge')->count();--}}
{{--        $personnelInactifs = $personnelItems->where('statut', 'inactif')->count();--}}
{{--        $sel               = $personnelItems->first();--}}

{{--        $statusLabel = fn ($s) => match ($s) {--}}
{{--            'actif'    => 'Actif',--}}
{{--            'en_conge' => 'En congé',--}}
{{--            'inactif'  => 'Inactif',--}}
{{--            default    => ucfirst($s),--}}
{{--        };--}}

{{--        $statusClass = fn ($s) => match ($s) {--}}
{{--            'actif'    => 'badge-success',--}}
{{--            'en_conge' => 'badge-warning',--}}
{{--            'inactif'  => 'badge-danger',--}}
{{--            default    => 'badge-info',--}}
{{--        };--}}

{{--        $roleLabel = fn ($r) => match ($r) {--}}
{{--            'admin'        => 'Administrateur',--}}
{{--            'gestionnaire' => 'Gestionnaire',--}}
{{--            'commercial'   => 'Commercial',--}}
{{--            'comptable'    => 'Comptable',--}}
{{--            'technicien'   => 'Technicien',--}}
{{--            default        => ucfirst($r),--}}
{{--        };--}}

{{--        $roleClass = fn ($r) => match ($r) {--}}
{{--            'admin'        => 'badge-danger',--}}
{{--            'gestionnaire' => 'badge-primary',--}}
{{--            'commercial'   => 'badge-success',--}}
{{--            'comptable'    => 'badge-warning',--}}
{{--            default        => 'badge-info',--}}
{{--        };--}}

{{--        $initials = function ($name) {--}}
{{--            $parts = preg_split('/\s+/', trim((string) $name), -1, PREG_SPLIT_NO_EMPTY);--}}
{{--            return collect($parts)->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('') ?: 'PE';--}}
{{--        };--}}

{{--        $formatDate = function ($value) {--}}
{{--            if (!$value) return 'Non défini';--}}
{{--            try { return \Carbon\Carbon::parse($value)->format('d/m/Y'); }--}}
{{--            catch (\Throwable $e) { return (string) $value; }--}}
{{--        };--}}

{{--        $formatMoney = fn ($amount) => number_format((float) ($amount ?? 0), 0, ',', ' ') . ' FCFA';--}}
{{--    @endphp--}}

{{--    --}}{{-- Toast notification --}}
{{--    <div id="toast-container" aria-live="polite" aria-atomic="true"></div>--}}

{{--    <section class="page">--}}
{{--        <div class="page-header">--}}
{{--            <div class="page-header-copy">--}}
{{--                <div class="page-heading">--}}
{{--                    <div>--}}
{{--                        <h2>Gestion du personnel</h2>--}}
{{--                        <p class="text-muted mb-0">Consultez les membres du personnel, leurs rôles et leur statut.</p>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--            <div class="page-actions">--}}
{{--                <a class="btn btn-outline" href="#">Rôles &amp; permissions</a>--}}
{{--                <a class="btn btn-primary" href="{{ route('agence.personnel.create') }}">--}}
{{--                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">--}}
{{--                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>--}}
{{--                    </svg>--}}
{{--                    Ajouter un membre--}}
{{--                </a>--}}
{{--            </div>--}}
{{--        </div>--}}

{{--        <div class="stats-grid">--}}
{{--            <article class="stat-card">--}}
{{--                <span>Total membres</span>--}}
{{--                <strong>{{ $totalPersonnel }}</strong>--}}
{{--            </article>--}}
{{--            <article class="stat-card">--}}
{{--                <span>Actifs</span>--}}
{{--                <strong class="is-success">{{ $personnelActifs }}</strong>--}}
{{--            </article>--}}
{{--            <article class="stat-card">--}}
{{--                <span>En congé</span>--}}
{{--                <strong class="is-info">{{ $personnelConges }}</strong>--}}
{{--            </article>--}}
{{--            <article class="stat-card">--}}
{{--                <span>Inactifs</span>--}}
{{--                <strong class="is-danger">{{ $personnelInactifs }}</strong>--}}
{{--            </article>--}}
{{--        </div>--}}

{{--        <div class="agency-shell" id="agency-shell">--}}

{{--            <div class="agency-overlay" id="agency-overlay" onclick="closeMobileDetail()" aria-hidden="true"></div>--}}

{{--            --}}{{-- ── SIDEBAR ─────────────────────────────────────────── --}}
{{--            <div class="agency-sidebar">--}}
{{--                <div class="agency-sidebar-header">--}}
{{--                    <div class="agency-sidebar-top">--}}
{{--                        <span class="agency-sidebar-title">--}}
{{--                            Personnel <span class="agency-count">{{ $totalPersonnel }}</span>--}}
{{--                        </span>--}}
{{--                    </div>--}}

{{--                    <label class="search-field u-search-compact" for="personnel-search">--}}
{{--                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">--}}
{{--                            <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd"/>--}}
{{--                        </svg>--}}
{{--                        <input type="text" placeholder="Rechercher un membre…" id="personnel-search" autocomplete="off">--}}
{{--                    </label>--}}

{{--                    <div class="agency-filter-pills">--}}
{{--                        <button class="filter-pill active" type="button" data-filter="tous">Tous</button>--}}
{{--                        <button class="filter-pill" type="button" data-filter="actif">Actifs</button>--}}
{{--                        <button class="filter-pill" type="button" data-filter="en_conge">En congé</button>--}}
{{--                        <button class="filter-pill" type="button" data-filter="inactif">Inactifs</button>--}}
{{--                    </div>--}}
{{--                </div>--}}

{{--                <div class="agency-list" role="listbox" aria-label="Liste du personnel">--}}
{{--                    @foreach($personnelItems as $membre)--}}
{{--                        <button--}}
{{--                                class="agency-row {{ $loop->first ? 'selected' : '' }}"--}}
{{--                                type="button"--}}
{{--                                role="option"--}}
{{--                                aria-selected="{{ $loop->first ? 'true' : 'false' }}"--}}
{{--                                data-status="{{ $membre['statut'] }}"--}}
{{--                                data-name="{{ $membre['name'] }}"--}}
{{--                                data-matricule="{{ $membre['matricule'] }}"--}}
{{--                                data-initials="{{ $initials($membre['name']) }}"--}}
{{--                                data-role="{{ $membre['role'] }}"--}}
{{--                                data-role-label="{{ $roleLabel($membre['role']) }}"--}}
{{--                                data-role-class="{{ $roleClass($membre['role']) }}"--}}
{{--                                data-email="{{ $membre['email'] }}"--}}
{{--                                data-phone="{{ $membre['tel1'] }}"--}}
{{--                                data-poste="{{ $membre['poste'] }}"--}}
{{--                                data-agence="{{ $membre['agence_nom'] }}"--}}
{{--                                data-departement="{{ $membre['departement'] }}"--}}
{{--                                data-superviseur="{{ $membre['superviseur_nom'] }}"--}}
{{--                                data-adresse="{{ $membre['adresse'] }}"--}}
{{--                                data-date-embauche="{{ $formatDate($membre['date_embauche']) }}"--}}
{{--                                data-salaire="{{ $formatMoney($membre['salaire']) }}"--}}
{{--                                data-permissions="{{ json_encode($membre['permissions']) }}"--}}
{{--                                data-status-label="{{ $statusLabel($membre['statut']) }}"--}}
{{--                                data-status-class="{{ $statusClass($membre['statut']) }}"--}}
{{--                                data-show-url="#"--}}
{{--                                data-edit-url="#"--}}
{{--                                data-toggle-url="#"--}}
{{--                                onclick="selectMembre(this)"--}}
{{--                        >--}}
{{--                            <span class="agency-row-top">--}}
{{--                                <span class="agency-ref">{{ $membre['matricule'] }}</span>--}}
{{--                                <span class="badge {{ $statusClass($membre['statut']) }}">{{ $statusLabel($membre['statut']) }}</span>--}}
{{--                            </span>--}}
{{--                            <span class="agency-row-main">--}}
{{--                                <span class="entity-avatar">{{ $initials($membre['name']) }}</span>--}}
{{--                                <span>--}}
{{--                                    <strong>{{ $membre['name'] }}</strong>--}}
{{--                                    <small>{{ $membre['poste'] }}</small>--}}
{{--                                </span>--}}
{{--                            </span>--}}
{{--                        </button>--}}
{{--                    @endforeach--}}

{{--                    <div class="agency-empty u-hidden" id="personnel-no-results">--}}
{{--                        Aucun membre ne correspond à la recherche.--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}

{{--            --}}{{-- ── PANNEAU DÉTAIL ───────────────────────────────────── --}}
{{--            <div class="agency-detail" id="agency-detail" aria-label="Détail du membre">--}}

{{--                <button class="agency-back-btn" type="button" onclick="closeMobileDetail()" aria-label="Retour à la liste">--}}
{{--                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">--}}
{{--                        <path d="M19 12H5M12 5l-7 7 7 7"/>--}}
{{--                    </svg>--}}
{{--                    Retour--}}
{{--                </button>--}}

{{--                <div class="agency-detail-header">--}}
{{--                    <div class="agency-detail-heading">--}}
{{--                        <span class="agency-detail-ref" id="personnel-detail-matricule">{{ $sel['matricule'] }}</span>--}}
{{--                        <h3 id="personnel-detail-name">{{ $sel['name'] }}</h3>--}}
{{--                    </div>--}}
{{--                    <div class="agency-detail-actions">--}}
{{--                        <button--}}
{{--                                id="personnel-toggle-status"--}}
{{--                                type="button"--}}
{{--                                class="btn btn-outline btn-sm"--}}
{{--                                data-current-status="{{ $sel['statut'] }}"--}}
{{--                                data-toggle-url="#"--}}
{{--                                onclick="toggleMembreStatus(this)"--}}
{{--                        >--}}
{{--                            {{ $sel['statut'] === 'actif' ? 'Désactiver' : 'Activer' }}--}}
{{--                        </button>--}}
{{--                        <a id="personnel-show-link" href="#" class="btn btn-outline btn-sm">Voir</a>--}}
{{--                        <a id="personnel-edit-link" href="#" class="btn btn-primary btn-sm">Modifier</a>--}}
{{--                    </div>--}}
{{--                </div>--}}

{{--                <div class="agency-meta-bar">--}}
{{--                    <div class="agency-meta-item">--}}
{{--                        <span>Statut</span>--}}
{{--                        <strong>--}}
{{--                            <span id="personnel-detail-status" class="badge {{ $statusClass($sel['statut']) }}">--}}
{{--                                {{ $statusLabel($sel['statut']) }}--}}
{{--                            </span>--}}
{{--                        </strong>--}}
{{--                    </div>--}}
{{--                    <div class="agency-meta-item">--}}
{{--                        <span>Rôle</span>--}}
{{--                        <strong>--}}
{{--                            <span id="personnel-detail-role" class="badge {{ $roleClass($sel['role']) }}">--}}
{{--                                {{ $roleLabel($sel['role']) }}--}}
{{--                            </span>--}}
{{--                        </strong>--}}
{{--                    </div>--}}
{{--                    <div class="agency-meta-item">--}}
{{--                        <span>Poste</span>--}}
{{--                        <strong id="personnel-detail-poste">{{ $sel['poste'] }}</strong>--}}
{{--                    </div>--}}
{{--                    <div class="agency-meta-item">--}}
{{--                        <span>Date d'embauche</span>--}}
{{--                        <strong id="personnel-detail-date-embauche">{{ $formatDate($sel['date_embauche']) }}</strong>--}}
{{--                    </div>--}}
{{--                    <div class="agency-meta-item">--}}
{{--                        <span>Salaire</span>--}}
{{--                        <strong id="personnel-detail-salaire">{{ $formatMoney($sel['salaire']) }}</strong>--}}
{{--                    </div>--}}
{{--                </div>--}}

{{--                <div class="agency-detail-body">--}}
{{--                    <div class="agency-profile-card">--}}
{{--                        <div class="entity-avatar lg" id="personnel-detail-initials">{{ $initials($sel['name']) }}</div>--}}
{{--                        <div>--}}
{{--                            <strong id="personnel-detail-name-card">{{ $sel['name'] }}</strong>--}}
{{--                            <span id="personnel-detail-email">{{ $sel['email'] }}</span>--}}
{{--                        </div>--}}
{{--                    </div>--}}

{{--                    <div class="agency-info-grid">--}}
{{--                        <div class="agency-info-item">--}}
{{--                            <span>Téléphone</span>--}}
{{--                            <strong id="personnel-detail-phone">{{ $sel['tel1'] }}</strong>--}}
{{--                        </div>--}}
{{--                        <div class="agency-info-item">--}}
{{--                            <span>Agence</span>--}}
{{--                            <strong id="personnel-detail-agence">{{ $sel['agence_nom'] }}</strong>--}}
{{--                        </div>--}}
{{--                        <div class="agency-info-item">--}}
{{--                            <span>Département</span>--}}
{{--                            <strong id="personnel-detail-departement">{{ $sel['departement'] }}</strong>--}}
{{--                        </div>--}}
{{--                        <div class="agency-info-item">--}}
{{--                            <span>Superviseur</span>--}}
{{--                            <strong id="personnel-detail-superviseur">{{ $sel['superviseur_nom'] }}</strong>--}}
{{--                        </div>--}}
{{--                        <div class="agency-info-item agency-info-wide">--}}
{{--                            <span>Adresse</span>--}}
{{--                            <strong id="personnel-detail-adresse">{{ $sel['adresse'] }}</strong>--}}
{{--                        </div>--}}
{{--                        <div class="agency-info-item agency-info-wide">--}}
{{--                            <span>Permissions</span>--}}
{{--                            <div class="agency-module-list" id="personnel-detail-permissions">--}}
{{--                                @foreach($sel['permissions'] as $perm)--}}
{{--                                    <span>{{ $perm }}</span>--}}
{{--                                @endforeach--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </section>--}}

{{--    --}}{{-- ══ STYLES ══════════════════════════════════════════════════════ --}}
{{--    --}}{{-- ══ JAVASCRIPT ══════════════════════════════════════════════════ --}}
{{--@endsection--}}