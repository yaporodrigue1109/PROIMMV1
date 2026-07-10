@extends('agence.layouts.app')

@section('title', 'Détail maintenancier')
@section('header_title', 'Détail maintenancier')

@section('content')
    @php
    //dd($maintenancier);
        $isDisponible = $maintenancier['statut'] ?? false;
        $interventionsCount = $maintenancier->maintenances->count() ?? 0 ; // Toujours 0 pour une page statique
    @endphp

    <section class="maintenancier-page">

        {{-- Hero --}}
        <div class="maintenancier-hero">
            <div class="maintenancier-hero-main">
                <div class="maintenancier-avatar">
                    {{ strtoupper(substr($maintenancier['name'] ?? 'M', 0, 2)) }}
                </div>
                <div class="maintenancier-hero-copy">
                    <span class="maintenancier-eyebrow">Maintenancier</span>
                    <h2>Doe</h2>
                    <p>
                        <span class="maintenancier-meta">{{ $maintenancier['entreprise'] ?? 'Indépendant' }}</span>
                        <span class="maintenancier-meta-separator">·</span>
                        <span class="maintenancier-meta">{{ $maintenancier['fonction']['name'] ?? 'Non défini' }}</span>
                    </p>
                </div>
            </div>

            <div class="maintenancier-actions">
                <a href="{{ route('agence.maintenance.index') }}" class="btn btn-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round">
                        <path d="m15 18-6-6 6-6"/>
                    </svg>
                    Retour
                </a>
{{--                <button class="btn btn-primary btn-edit-maintenancier"--}}
{{--                        data-id="{{ $maintenancier['id'] }}"--}}
{{--                        data-name="{{ $maintenancier['name'] }}"--}}
{{--                        data-email="{{ $maintenancier['email'] }}"--}}
{{--                        data-tel1="{{ $maintenancier['tel1'] ?? '' }}"--}}
{{--                        data-tel2="{{ $maintenancier['tel2'] ?? '' }}"--}}
{{--                        data-entreprise="{{ $maintenancier['entreprise'] ?? '' }}"--}}
{{--                        data-fonction-id="{{ $maintenancier['fonction']['id'] ?? '' }}"--}}
{{--                        data-statut="{{ $maintenancier['statut'] ?? false }}">--}}
{{--                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"--}}
{{--                         fill="none" stroke="currentColor" stroke-width="2"--}}
{{--                         stroke-linecap="round" stroke-linejoin="round">--}}
{{--                        <path d="M17 3l4 4-7 7H10v-4l7-7z"/>--}}
{{--                        <path d="M4 20h16"/>--}}
{{--                    </svg>--}}
{{--                    Modifier--}}
{{--                </button>--}}
            </div>
        </div>

        {{-- Cartes stats --}}
        <div class="stats-grid">
            <article class="stat-card">
                <span>Entreprise</span>
                <strong>{{ $maintenancier['entreprise'] ?? 'Indépendant' }}</strong>
            </article>
            <article class="stat-card">
                <span>Fonction</span>
                <strong>{{ $maintenancier['fonction']['name'] ?? 'Non défini' }}</strong>
            </article>
            <article class="stat-card">
                <span>Interventions</span>
                <strong class="is-info">{{ $interventionsCount }}</strong>
            </article>
            <article class="stat-card">
                <span>Disponibilité</span>
                <strong class="{{ $isDisponible ? 'is-success' : 'is-danger' }}">
                    <span class="statut-indicator {{ $isDisponible ? 'active' : 'inactive' }}"></span>
                    {{ $isDisponible ? 'Disponible' : 'Indisponible' }}
                </strong>
            </article>
        </div>

        {{-- Grille principale --}}
        <div class="maintenancier-grid">

            {{-- Carte Informations de contact --}}
            <article class="maintenancier-card">
                <div class="maintenancier-card-header">
                    <div class="maintenancier-card-header-left">
                        <div class="maintenancier-icon maintenancier-icon-contact">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="2"
                                 stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.362 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.338 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/>
                            </svg>
                        </div>
                        <div>
                            <span class="maintenancier-eyebrow">Contact</span>
                            <h3>Coordonnées</h3>
                        </div>
                    </div>
                </div>

                <div class="maintenancier-info-list">
                    <div class="maintenancier-info-item">
                        <div class="maintenancier-info-label">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="2"
                                 stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                <polyline points="22,6 12,13 2,6"/>
                            </svg>
                            <span>Email</span>
                        </div>
                        <strong>{{ $maintenancier['email'] }}</strong>
                    </div>

                    <div class="maintenancier-info-item">
                        <div class="maintenancier-info-label">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="2"
                                 stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.362 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.338 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/>
                            </svg>
                            <span>Téléphone 1</span>
                        </div>
                        <strong>{{ $maintenancier['tel1'] ?? 'Non renseigné' }}</strong>
                    </div>

                    <div class="maintenancier-info-item">
                        <div class="maintenancier-info-label">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="2"
                                 stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.362 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.338 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/>
                            </svg>
                            <span>Téléphone 2</span>
                        </div>
                        <strong>{{ $maintenancier['tel2'] ?? 'Non renseigné' }}</strong>
                    </div>
                </div>
            </article>

            {{-- Carte Détails professionnels --}}
            <article class="maintenancier-card">
                <div class="maintenancier-card-header">
                    <div class="maintenancier-card-header-left">
                        <div class="maintenancier-icon maintenancier-icon-professional">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="2"
                                 stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                                <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                            </svg>
                        </div>
                        <div>
                            <span class="maintenancier-eyebrow">Professionnel</span>
                            <h3>Détails</h3>
                        </div>
                    </div>
                </div>

                <div class="maintenancier-info-list">
                    <div class="maintenancier-info-item">
                        <div class="maintenancier-info-label">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="2"
                                 stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 7h-4.18A3 3 0 0 0 16 5.18V4a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v1.18A3 3 0 0 0 8.18 7H4"/>
                                <path d="M20 7v10a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7"/>
                                <path d="M12 11v4"/>
                                <path d="M9 13h6"/>
                            </svg>
                            <span>Spécialités</span>
                        </div>
                        <div class="maintenancier-specialites">
                            @php
                                $specialites = isset($maintenancier['specialites']) ? explode(',', $maintenancier['specialites']) : [];
                            @endphp
                            @if(count($specialites) > 0)
                                @foreach($specialites as $spec)
                                    <span class="badge badge-neutral">{{ trim($spec) }}</span>
                                @endforeach
                            @else
                                <span class="text-muted">Aucune spécialité renseignée</span>
                            @endif
                        </div>
                    </div>
                </div>
            </article>

        </div>

        {{-- Section des dernières interventions --}}
        @if(isset($interventions) && count($interventions) > 0)
            <div class="maintenancier-interventions-section">
                <div class="maintenancier-section-header">
                    <div class="maintenancier-section-header-left">
                        <div class="maintenancier-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="2"
                                 stroke-linecap="round" stroke-linejoin="round">
                                <polygon points="12 2 2 7 12 12 22 7 12 2"/>
                                <polyline points="2 17 12 22 22 17"/>
                                <polyline points="2 12 12 17 22 12"/>
                            </svg>
                        </div>
                        <div>
                            <span class="maintenancier-eyebrow">Activités</span>
                            <h3>Dernières interventions</h3>
                        </div>
                    </div>
                    <span class="maintenancier-chip">
                    {{ count($interventions) }} intervention(s)
                </span>
                </div>

                <div class="table-shell">
                    <table class="data-table">
                        <thead>
                        <tr>
                            <th>Titre</th>
                            <th>Statut</th>
                            <th>Date début</th>
                            <th>Montant</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($interventions as $intervention)
                            @php
                                $statutClass = match($intervention['statut'] ?? 'planifiee') {
                                    'planifiee' => 'info',
                                    'en_cours' => 'warning',
                                    'terminee' => 'success',
                                    'annulee' => 'danger',
                                    default => 'neutral'
                                };
                                $statutLabel = match($intervention['statut'] ?? 'planifiee') {
                                    'planifiee' => 'Planifiée',
                                    'en_cours' => 'En cours',
                                    'terminee' => 'Terminée',
                                    'annulee' => 'Annulée',
                                    default => $intervention['statut']
                                };
                            @endphp
                            <tr>
                                <td>
                                    <div class="entity-cell">
                                        <div class="entity-thumb entity-thumb-info">
                                            {{ strtoupper(substr($intervention['titre'], 0, 1)) }}
                                        </div>
                                        <div class="entity-copy">
                                            <strong>{{ $intervention['titre'] }}</strong>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge badge-{{ $statutClass }}">{{ $statutLabel }}</span></td>
                                <td>{{ isset($intervention['date_debut']) ? \Carbon\Carbon::parse($intervention['date_debut'])->format('d/m/Y') : '-' }}</td>
                                <td>{{ number_format($intervention['montant_global'] ?? 0, 0, ',', ' ') }} FCFA</td>
                                <td>
                                    <button class="action-btn info btn-view-intervention"
                                            data-id="{{ $intervention['id'] }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

    </section>

    @push('styles')
        <style>
            .maintenancier-page {
                max-width: 1400px;
                margin: 0 auto;
                padding: 1.5rem;
            }

            /* Hero */
            .maintenancier-hero {
                display: flex;
                align-items: center;
                justify-content: space-between;
                flex-wrap: wrap;
                gap: 1rem;
                margin-bottom: 2rem;
                padding: 1.5rem;
                background: var(--card);
                border-radius: 1.5rem;
                border: 1px solid var(--border);
            }

            .maintenancier-hero-main {
                display: flex;
                align-items: center;
                gap: 1.25rem;
            }

            .maintenancier-avatar {
                width: 70px;
                height: 70px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                border-radius: 1.25rem;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.75rem;
                font-weight: 700;
                color: white;
            }

            .maintenancier-hero-copy h2 {
                margin: 0 0 0.25rem 0;
                font-size: 1.5rem;
                font-weight: 700;
                color: var(--foreground);
            }

            .maintenancier-eyebrow {
                font-size: 0.75rem;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                color: var(--muted-foreground);
            }

            .maintenancier-meta {
                font-size: 0.875rem;
                color: var(--muted-foreground);
            }

            .maintenancier-meta-separator {
                margin: 0 0.5rem;
                color: var(--border);
            }

            .maintenancier-actions {
                display: flex;
                gap: 0.75rem;
            }

            /* Cards */
            .maintenancier-card {
                background: var(--card);
                border-radius: 1rem;
                border: 1px solid var(--border);
                overflow: hidden;
            }

            .maintenancier-card-header {
                padding: 1rem 1.25rem;
                border-bottom: 1px solid var(--border);
                display: flex;
                align-items: center;
                justify-content: space-between;
            }

            .maintenancier-card-header-left {
                display: flex;
                align-items: center;
                gap: 0.75rem;
            }

            .maintenancier-icon {
                width: 32px;
                height: 32px;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 0.5rem;
                background: var(--muted);
                color: var(--primary);
            }

            .maintenancier-card-header-left h3 {
                margin: 0;
                font-size: 1rem;
                font-weight: 600;
            }

            .maintenancier-info-list {
                padding: 1rem 1.25rem;
                display: flex;
                flex-direction: column;
                gap: 1rem;
            }

            .maintenancier-info-item {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                flex-wrap: wrap;
                gap: 0.5rem;
            }

            .maintenancier-info-label {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                font-size: 0.875rem;
                color: var(--muted-foreground);
            }

            .maintenancier-specialites {
                display: flex;
                flex-wrap: wrap;
                gap: 0.5rem;
            }

            /* Grid */
            .maintenancier-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
                gap: 1.5rem;
                margin-bottom: 2rem;
                margin-top: 2rem;
            }

            /* Interventions section */
            .maintenancier-interventions-section {
                background: var(--card);
                border-radius: 1rem;
                border: 1px solid var(--border);
                padding: 1.25rem;
            }

            .maintenancier-section-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                margin-bottom: 1.25rem;
                padding-bottom: 0.75rem;
                border-bottom: 1px solid var(--border);
            }

            .maintenancier-section-header-left {
                display: flex;
                align-items: center;
                gap: 0.75rem;
            }

            .maintenancier-section-header-left h3 {
                margin: 0;
                font-size: 1rem;
                font-weight: 600;
            }

            .maintenancier-chip {
                padding: 0.25rem 0.75rem;
                background: var(--muted);
                border-radius: 2rem;
                font-size: 0.75rem;
                font-weight: 600;
                color: var(--foreground);
            }

            /* Badges et statuts */
            .statut-indicator {
                display: inline-block;
                width: 8px;
                height: 8px;
                border-radius: 50%;
                margin-right: 0.5rem;
            }

            .statut-indicator.active {
                background: #10b981;
                box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
            }

            .statut-indicator.inactive {
                background: #ef4444;
            }

            .is-success {
                color: #10b981;
            }

            .is-danger {
                color: #ef4444;
            }

            .is-info {
                color: #3b82f6;
            }

            .text-muted {
                color: var(--muted-foreground);
            }
        </style>
    @endpush
@endsection