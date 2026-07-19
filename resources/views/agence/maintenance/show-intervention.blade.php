@extends('agence.layouts.app')

@section('title', 'Détail intervention')
@section('header_title', 'Détail intervention')

@section('content')
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

        $dateDebut = isset($intervention['date_debut']) ? \Carbon\Carbon::parse($intervention['date_debut']) : null;
        $dateFin = isset($intervention['date_fin']) ? \Carbon\Carbon::parse($intervention['date_fin']) : null;

        $isEnRetard = $dateFin && $dateFin->isPast() && $intervention['statut'] !== 'terminee';
    @endphp

    <section class="intervention-page">

        {{-- Hero --}}
        <div class="intervention-hero">
            <div class="intervention-hero-main">
                <div class="intervention-avatar">
                    AGAT
                </div>
                <div class="intervention-hero-copy">
                    <span class="intervention-eyebrow">Intervention</span>
                    <h2>DOE</h2>
                    <p>
                        <span class="intervention-meta">{{ $intervention['type_intervention']['name'] ?? 'Type non défini' }}</span>
                        <span class="intervention-meta-separator">·</span>
                        <span class="intervention-meta badge badge-{{ $statutClass }}">{{ $statutLabel }}</span>
                    </p>
                </div>
            </div>

            <div class="intervention-actions">
                <a href="{{ route('agence.maintenance.index') }}" class="btn btn-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round">
                        <path d="m15 18-6-6 6-6"/>
                    </svg>
                    Retour
                </a>
{{--                @if($intervention['statut'] !== 'terminee' && $intervention['statut'] !== 'annulee')--}}
{{--                    <div class="intervention-status-actions">--}}
{{--                        <button class="btn btn-warning btn-changer-statut"--}}
{{--                                data-id="{{ $intervention['id'] }}"--}}
{{--                                data-statut="en_cours">--}}
{{--                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"--}}
{{--                                 fill="none" stroke="currentColor" stroke-width="2"--}}
{{--                                 stroke-linecap="round" stroke-linejoin="round">--}}
{{--                                <circle cx="12" cy="12" r="10"/>--}}
{{--                                <polyline points="12 6 12 12 16 14"/>--}}
{{--                            </svg>--}}
{{--                            Démarrer--}}
{{--                        </button>--}}
{{--                        <button class="btn btn-success btn-changer-statut"--}}
{{--                                data-id="{{ $intervention['id'] }}"--}}
{{--                                data-statut="terminee">--}}
{{--                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"--}}
{{--                                 fill="none" stroke="currentColor" stroke-width="2"--}}
{{--                                 stroke-linecap="round" stroke-linejoin="round">--}}
{{--                                <polyline points="20 6 9 17 4 12"/>--}}
{{--                            </svg>--}}
{{--                            Terminer--}}
{{--                        </button>--}}
{{--                    </div>--}}
{{--                @endif--}}
{{--                <button class="btn btn-primary btn-edit-intervention"--}}
{{--                        data-id="{{ $intervention['id'] }}">--}}
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
                <span>Montant total</span>
                <strong>{{ number_format($intervention['montant_global'] ?? 0, 0, ',', ' ') }} FCFA</strong>
            </article>
            <article class="stat-card">
                <span>Prise en charge</span>
                <strong class="is-info">{{ ucfirst($intervention['prise_en_charge_par'] ?? 'Non défini') }}</strong>
            </article>
            <article class="stat-card">
                <span>Propriétaire</span>
                <strong>{{ $intervention['proprietaire']->name ?? 'Non défini' }}</strong>
            </article>
            <article class="stat-card">
                <span>Statut</span>
                <strong class="badge badge-{{ $statutClass }}" style="font-size: 1rem;">
                    {{ $statutLabel }}
                </strong>
            </article>
        </div>

        {{-- Grille principale --}}
        <div class="intervention-grid">

            {{-- Carte Informations générales --}}
            <article class="intervention-card">
                <div class="intervention-card-header">
                    <div class="intervention-card-header-left">
                        <div class="intervention-icon intervention-icon-info">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="2"
                                 stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"/>
                                <line x1="12" y1="16" x2="12" y2="12"/>
                                <line x1="12" y1="8" x2="12.01" y2="8"/>
                            </svg>
                        </div>
                        <div>
                            <span class="intervention-eyebrow">Informations</span>
                            <h3>Générales</h3>
                        </div>
                    </div>
                </div>

                <div class="intervention-info-list">
                    <div class="intervention-info-item">
                        <div class="intervention-info-label">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="2"
                                 stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                <polyline points="14 2 14 8 20 8"/>
                                <line x1="16" x2="8" y1="13" y2="13"/>
                                <line x1="16" x2="8" y1="17" y2="17"/>
                                <polyline points="10 9 9 9 8 9"/>
                            </svg>
                            <span>Description générale</span>
                        </div>
                        <div class="intervention-description">
                            {{ $intervention['description'] ?? 'Aucune description' }}
                        </div>
                    </div>

                    <div class="intervention-info-item">
                        <div class="intervention-info-label">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="2"
                                 stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                                <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                            </svg>
                            <span>Type d'intervention</span>
                        </div>
                        <div>
                            <strong>{{ $intervention['type_intervention']['name'] ?? 'Non défini' }}</strong>
                            @if(isset($intervention['type_intervention']['categorie']))
                                <span class="badge badge-neutral">{{ $intervention['type_intervention']['categorie'] }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </article>

            {{-- Carte Localisation --}}
            <article class="intervention-card">
                <div class="intervention-card-header">
                    <div class="intervention-card-header-left">
                        <div class="intervention-icon intervention-icon-location">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="2"
                                 stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                <circle cx="12" cy="10" r="3"/>
                            </svg>
                        </div>
                        <div>
                            <span class="intervention-eyebrow">Localisation</span>
                            <h3>Bien concerné</h3>
                        </div>
                    </div>
                </div>

                <div class="intervention-info-list">
                    @if(isset($intervention['lot']) && $intervention['lot'])
                        <div class="intervention-info-item">
                            <div class="intervention-info-label">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                     fill="none" stroke="currentColor" stroke-width="2"
                                     stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                    <line x1="16" y1="2" x2="16" y2="6"/>
                                    <line x1="8" y1="2" x2="8" y2="6"/>
                                    <line x1="3" y1="10" x2="21" y2="10"/>
                                </svg>
                                <span>Lot</span>
                            </div>
                            <strong>{{ $intervention['lot']['nom'] ?? 'Non spécifié' }}</strong>
                        </div>
                    @endif

                    @if(isset($intervention['batiment']) && $intervention['batiment'])
                        <div class="intervention-info-item">
                            <div class="intervention-info-label">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                     fill="none" stroke="currentColor" stroke-width="2"
                                     stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="4" y="2" width="16" height="20" rx="2" ry="2"/>
                                    <line x1="9" y1="22" x2="15" y2="22"/>
                                    <line x1="8" y1="6" x2="16" y2="6"/>
                                    <line x1="8" y1="10" x2="16" y2="10"/>
                                    <line x1="8" y1="14" x2="12" y2="14"/>
                                </svg>
                                <span>Bâtiment</span>
                            </div>
                            <strong>{{ $intervention['batiment']['nom'] ?? 'Non spécifié' }}</strong>
                        </div>
                    @endif

                    @if(isset($intervention['porte']) && $intervention['porte'])
                        <div class="intervention-info-item">
                            <div class="intervention-info-label">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                     fill="none" stroke="currentColor" stroke-width="2"
                                     stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                                    <line x1="9" y1="9" x2="15" y2="15"/>
                                    <line x1="15" y1="9" x2="9" y2="15"/>
                                </svg>
                                <span>Porte</span>
                            </div>
                            <strong>{{ $intervention['porte']['numero'] ?? 'Non spécifié' }}</strong>
                        </div>
                    @endif
                </div>
            </article>

            {{-- Carte Calendrier --}}
            <article class="intervention-card">
                <div class="intervention-card-header">
                    <div class="intervention-card-header-left">
                        <div class="intervention-icon intervention-icon-calendar">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="2"
                                 stroke-linecap="round" stroke-linejoin="round">
                                <rect width="18" height="18" x="3" y="4" rx="2" ry="2"/>
                                <line x1="16" x2="16" y1="2" y2="6"/>
                                <line x1="8" x2="8" y1="2" y2="6"/>
                                <line x1="3" x2="21" y1="10" y2="10"/>
                            </svg>
                        </div>
                        <div>
                            <span class="intervention-eyebrow">Planning</span>
                            <h3>Dates et délais</h3>
                        </div>
                    </div>
                    @if($isEnRetard)
                        <span class="intervention-badge-danger">En retard</span>
                    @endif
                </div>

                <div class="intervention-info-list">
                    <div class="intervention-info-item">
                        <div class="intervention-info-label">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="2"
                                 stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"/>
                                <polyline points="12 6 12 12 16 14"/>
                            </svg>
                            <span>Date début</span>
                        </div>
                        <strong>{{ $dateDebut ? $dateDebut->format('d/m/Y H:i') : 'Non définie' }}</strong>
                    </div>

                    <div class="intervention-info-item">
                        <div class="intervention-info-label">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="2"
                                 stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"/>
                                <polyline points="12 6 12 12 16 14"/>
                            </svg>
                            <span>Date fin prévue</span>
                        </div>
                        <strong class="{{ $isEnRetard ? 'text-danger' : '' }}">
                            {{ $dateFin ? $dateFin->format('d/m/Y H:i') : 'Non définie' }}
                        </strong>
                    </div>

                    @if($dateDebut && $dateFin)
                        <div class="intervention-progress-wrapper">
                            <div class="intervention-progress-header">
                                <span>Avancement</span>
                                <strong>{{ $intervention['pourcentage_avancement'] ?? 0 }}%</strong>
                            </div>
                            <div class="intervention-progress-bar">
                                <div class="intervention-progress-fill"
                                     data-progress="{{ $intervention['pourcentage_avancement'] ?? 0 }}"
                                     style="width: {{ $intervention['pourcentage_avancement'] ?? 0 }}%"></div>
                            </div>
                        </div>
                    @endif
                </div>
            </article>

        </div>

        {{-- Section Détails des travaux --}}
        @if(isset($intervention['details']) && count($intervention['details']) > 0)
            <div class="intervention-details-section">
                <div class="intervention-section-header">
                    <div class="intervention-section-header-left">
                        <div class="intervention-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="2"
                                 stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
                            </svg>
                        </div>
                        <div>
                            <span class="intervention-eyebrow">Travaux</span>
                            <h3>Détail des interventions</h3>
                        </div>
                    </div>
                    <span class="intervention-chip">
                    {{ count($intervention['details']) }} tâche(s)
                </span>
                </div>

                <div class="intervention-tasks-list">
                    @foreach($intervention['details'] as $index => $detail)
                        @php
                            $detailStatutClass = match($detail['statut'] ?? 'en attente') {
                                'en attente' => 'info',
                                'en_cours' => 'warning',
                                'termine' => 'success',
                                'annule' => 'danger',
                                default => 'neutral'
                            };
                            $detailStatutLabel = match($detail['statut'] ?? 'en attente') {
                                'en attente' => 'Planifié',
                                'en_cours' => 'En cours',
                                'termine' => 'Terminé',
                                'annule' => 'Annulé',
                                default => $detail['statut']
                            };
                        @endphp
                        <div class="intervention-task-card">
                            <div class="intervention-task-header">
                                <div class="intervention-task-title">
                                    <span class="intervention-task-number">Tâche {{ $index + 1 }}</span>
                                    <span class="badge badge-{{ $detailStatutClass }}">{{ $detailStatutLabel }}</span>
                                </div>
                                <div class="intervention-task-amount">
                                    <strong>{{ number_format($detail['prix'], 0, ',', ' ') }} FCFA</strong>
                                </div>
                            </div>
                            <div class="intervention-task-body">
                                <div class="intervention-task-row">
                                    <div class="intervention-task-label">Type d'intervention</div>
                                    <div>{{ $detail['type_intervention']['name'] ?? 'Non défini' }}</div>
                                </div>
                                <div class="intervention-task-row">
                                    <div class="intervention-task-label">Maintenancier</div>
                                    <div>
                                        <strong>{{ $detail['maintenancier']['name'] ?? 'Non défini' }}</strong>
                                        @if(isset($detail['maintenancier']['entreprise']))
                                            <span class="text-muted">({{ $detail['maintenancier']['entreprise'] }})</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="intervention-task-row">
                                    <div class="intervention-task-label">Priorité</div>
                                    <div>
                                    <span class="badge badge-{{ match($detail['priorite'] ?? 'normale') {
                                        'basse' => 'neutral',
                                        'normale' => 'info',
                                        'haute' => 'danger',
                                        default => 'neutral'
                                    } }}">
                                        {{ ucfirst($detail['priorite'] ?? 'Normale') }}
                                    </span>
                                    </div>
                                </div>
                                <div class="intervention-task-row">
                                    <div class="intervention-task-label">Période</div>
                                    <div>
                                        {{ isset($detail['date_debut']) ? \Carbon\Carbon::parse($detail['date_debut'])->format('d/m/Y') : 'Date non définie' }}
                                        @if(isset($detail['date_fin']))
                                            → {{ \Carbon\Carbon::parse($detail['date_fin'])->format('d/m/Y') }}
                                        @endif
                                    </div>
                                </div>
                                @if($detail['description'] ?? false)
                                    <div class="intervention-task-row">
                                        <div class="intervention-task-label">Description</div>
                                        <div class="intervention-task-description">{{ $detail['description'] }}</div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="intervention-total-box">
                    <span>Total des travaux</span>
                    <strong>{{ number_format($intervention['montant_global'] ?? 0, 0, ',', ' ') }} FCFA</strong>
                </div>
            </div>
        @endif

    </section>

    @push('styles')
        <style>
            .intervention-page {
                max-width: 1400px;
                margin: 0 auto;
                padding: 1.5rem;
            }

            /* Hero */
            .intervention-hero {
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

            .intervention-hero-main {
                display: flex;
                align-items: center;
                gap: 1.25rem;
            }

            .intervention-avatar {
                width: 70px;
                height: 70px;
                background: linear-gradient(135deg, #f59e0b 0%, #ea580c 100%);
                border-radius: 1.25rem;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.75rem;
                font-weight: 700;
                color: white;
            }

            .intervention-hero-copy h2 {
                margin: 0 0 0.25rem 0;
                font-size: 1.5rem;
                font-weight: 700;
                color: var(--foreground);
            }

            .intervention-eyebrow {
                font-size: 0.75rem;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                color: var(--muted-foreground);
            }

            .intervention-meta {
                font-size: 0.875rem;
                color: var(--muted-foreground);
            }

            .intervention-meta-separator {
                margin: 0 0.5rem;
                color: var(--border);
            }

            .intervention-actions {
                display: flex;
                gap: 0.75rem;
            }

            .intervention-status-actions {
                display: flex;
                gap: 0.5rem;
            }

            /* Cards */
            .intervention-card {
                background: var(--card);
                border-radius: 1rem;
                border: 1px solid var(--border);
                overflow: hidden;
            }

            .intervention-card-header {
                padding: 1rem 1.25rem;
                border-bottom: 1px solid var(--border);
                display: flex;
                align-items: center;
                justify-content: space-between;
            }

            .intervention-card-header-left {
                display: flex;
                align-items: center;
                gap: 0.75rem;
            }

            .intervention-icon {
                width: 32px;
                height: 32px;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 0.5rem;
                background: var(--muted);
                color: var(--primary);
            }

            .intervention-card-header-left h3 {
                margin: 0;
                font-size: 1rem;
                font-weight: 600;
            }

            .intervention-info-list {
                padding: 1rem 1.25rem;
                display: flex;
                flex-direction: column;
                gap: 1rem;
            }

            .intervention-info-item {
                display: flex;
                flex-direction: column;
                gap: 0.5rem;
            }

            .intervention-info-label {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                font-size: 0.875rem;
                color: var(--muted-foreground);
            }

            .intervention-description {
                padding: 0.75rem;
                background: var(--muted);
                border-radius: 0.5rem;
                font-size: 0.875rem;
                line-height: 1.5;
            }

            /* Grid */
            .intervention-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
                gap: 1.5rem;
                margin-bottom: 2rem;
                margin-top: 2rem;
            }

            /* Progress bar */
            .intervention-progress-wrapper {
                margin-top: 0.5rem;
            }

            .intervention-progress-header {
                display: flex;
                justify-content: space-between;
                margin-bottom: 0.5rem;
                font-size: 0.875rem;
            }

            .intervention-progress-bar {
                height: 8px;
                background: var(--muted);
                border-radius: 1rem;
                overflow: hidden;
            }

            .intervention-progress-fill {
                height: 100%;
                background: #3b82f6;
                border-radius: 1rem;
                width: 0%;
                transition: width 0.3s ease;
            }

            /* Détails section */
            .intervention-details-section {
                background: var(--card);
                border-radius: 1rem;
                border: 1px solid var(--border);
                padding: 1.25rem;
            }

            .intervention-section-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                margin-bottom: 1.25rem;
                padding-bottom: 0.75rem;
                border-bottom: 1px solid var(--border);
            }

            .intervention-section-header-left {
                display: flex;
                align-items: center;
                gap: 0.75rem;
            }

            .intervention-section-header-left h3 {
                margin: 0;
                font-size: 1rem;
                font-weight: 600;
            }

            .intervention-chip {
                padding: 0.25rem 0.75rem;
                background: var(--muted);
                border-radius: 2rem;
                font-size: 0.75rem;
                font-weight: 600;
            }

            .intervention-badge-danger {
                padding: 0.25rem 0.75rem;
                background: rgba(239, 68, 68, 0.1);
                color: #ef4444;
                border-radius: 2rem;
                font-size: 0.75rem;
                font-weight: 600;
            }

            /* Tasks */
            .intervention-tasks-list {
                display: flex;
                flex-direction: column;
                gap: 1rem;
                margin-bottom: 1rem;
            }

            .intervention-task-card {
                border: 1px solid var(--border);
                border-radius: 1rem;
                overflow: hidden;
            }

            .intervention-task-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 1rem 1.25rem;
                background: var(--muted);
                border-bottom: 1px solid var(--border);
            }

            .intervention-task-title {
                display: flex;
                align-items: center;
                gap: 0.75rem;
            }

            .intervention-task-number {
                font-weight: 600;
                color: var(--foreground);
            }

            .intervention-task-amount strong {
                font-size: 1.125rem;
                color: #3b82f6;
            }

            .intervention-task-body {
                padding: 1rem 1.25rem;
                display: flex;
                flex-direction: column;
                gap: 0.75rem;
            }

            .intervention-task-row {
                display: grid;
                grid-template-columns: 140px 1fr;
                gap: 1rem;
                font-size: 0.875rem;
            }

            .intervention-task-label {
                color: var(--muted-foreground);
            }

            .intervention-task-description {
                margin-top: 0.25rem;
                line-height: 1.4;
            }

            .intervention-total-box {
                display: flex;
                align-items: center;
                justify-content: space-between;
                margin-top: 1rem;
                padding: 1rem 1.25rem;
                border-radius: 0.75rem;
                background: rgba(37, 99, 235, 0.1);
                border: 1px solid rgba(37, 99, 235, 0.2);
            }

            .intervention-total-box strong {
                font-size: 1.25rem;
                color: #3b82f6;
            }

            .text-danger {
                color: #ef4444;
            }

            .btn-warning {
                background: #f59e0b;
                color: white;
            }

            .btn-warning:hover {
                background: #d97706;
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            // Changement de statut
            document.querySelectorAll('.btn-changer-statut').forEach(btn => {
                btn.addEventListener('click', async function() {
                    const id = this.dataset.id;
                    const newStatut = this.dataset.statut;

                    if (confirm(`Confirmez-vous le passage au statut "${newStatut}" ?`)) {
                        try {
                            const response = await fetch(`/agence/maintenance/${id}/statut`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                },
                                body: JSON.stringify({ statut: newStatut })
                            });

                            const result = await response.json();

                            if (result.success) {
                                window.location.reload();
                            } else {
                                alert('Erreur: ' + result.message);
                            }
                        } catch (error) {
                            console.error('Erreur:', error);
                            alert('Une erreur est survenue');
                        }
                    }
                });
            });

            // Édition
            document.querySelectorAll('.btn-edit-intervention').forEach(btn => {
                btn.addEventListener('click', function() {
                    // Ouvrir le modal d'édition avec les données de l'intervention
                    console.log('Édition intervention:', this.dataset.id);
                });
            });

            // Barre de progression
            document.querySelectorAll('.intervention-progress-fill').forEach(bar => {
                const progress = bar.dataset.progress;
                if (progress) {
                    bar.style.width = progress + '%';
                }
            });
        </script>
    @endpush
@endsection
