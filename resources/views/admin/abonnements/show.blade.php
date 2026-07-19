@extends('admin.layouts.app')

@section('title', 'Détail abonnement')
@section('header_title', 'Détail abonnement')

@section('content')
    @php
        /*
         * Calcul du temps restant.
         * diffInDays(now()) sans le flag "absolu" retourne un nombre
         * négatif si la date de fin est dans le passé.
         */
        $dateFin       = \Carbon\Carbon::parse($abonnement['date_fin']);
        $dateDebut     = \Carbon\Carbon::parse($abonnement['date_debut']);
        $joursRestants = (int) now()->diffInDays($dateFin, false); // négatif si expiré
        $dureeTotal    = max(1, $dateDebut->diffInDays($dateFin));
        $joursEcoules  = max(0, min($dureeTotal, $dateDebut->diffInDays(now())));
        $pourcentage   = min(100, max(0, round(($joursEcoules / $dureeTotal) * 100)));

        $progressClass = $joursRestants > 7
            ? ''
            : ($joursRestants > 0 ? 'is-warning' : 'is-danger');

        $isActif = $abonnement['statut'] === 'Actif';
    @endphp

    <section class="subscription-page">

        {{-- Hero --}}
        <div class="subscription-hero">
            <div class="subscription-hero-main">
                <div class="subscription-avatar">
                    {{ strtoupper(substr($abonnement['agence'], 0, 2)) }}
                </div>
                <div class="subscription-hero-copy">
                    <span class="subscription-eyebrow">Abonnement</span>
                    <h2>{{ $abonnement['agence'] }}</h2>
                    <p>
                        <span class="subscription-meta">{{ $abonnement['code_agence'] }}</span>
                        <span class="subscription-meta-separator">·</span>
                        <span class="subscription-meta">{{ $abonnement['plan'] }}</span>
                        <span class="subscription-meta-separator">·</span>
                        <span class="subscription-meta">
                            {{ number_format($abonnement['montant'], 0, ',', ' ') }} FCFA
                        </span>
                    </p>
                </div>
            </div>

            <div class="subscription-actions">
                <a href="{{ route('admin.abonnements.index') }}" class="btn btn-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round">
                        <path d="m15 18-6-6 6-6"/>
                    </svg>
                    Retour
                </a>
                <a href="{{ route('admin.agences.index', ['selected_agence_id' => $abonnement['agence_id'] ?? null]) }}"
                   class="btn btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                        <polyline points="9 22 9 12 15 12 15 22"/>
                    </svg>
                    Voir l'agence
                </a>
            </div>
        </div>

        {{-- Cartes stats --}}
        <div class="stats-grid">
            <article class="stat-card">
                <span>Plan</span>
                <strong>{{ $abonnement['plan'] }}</strong>
            </article>
            <article class="stat-card">
                <span>Montant</span>
                <strong>{{ number_format($abonnement['montant'], 0, ',', ' ') }} FCFA</strong>
            </article>
            <article class="stat-card">
                <span>Cycle</span>
                <strong>{{ $abonnement['cycle'] }}</strong>
            </article>
            <article class="stat-card">
                <span>Statut</span>
                <strong class="{{ $isActif ? 'is-success' : 'is-danger' }}">
                    <span class="statut-indicator {{ $isActif ? 'active' : 'inactive' }}"></span>
                    {{ $abonnement['statut'] }}
                </strong>
            </article>
        </div>

        {{-- Grille principale --}}
        <div class="subscription-grid">

            {{-- Carte Période --}}
            <article class="subscription-card">
                <div class="subscription-card-header">
                    <div class="subscription-card-header-left">
                        <div class="subscription-icon subscription-icon-calendar">
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
                            <span class="subscription-eyebrow">Période</span>
                            <h3>Validité</h3>
                        </div>
                    </div>
                </div>

                <div class="subscription-info-list">
                    <div class="subscription-info-item">
                        <div class="subscription-info-label">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="2"
                                 stroke-linecap="round" stroke-linejoin="round">
                                <rect width="18" height="18" x="3" y="4" rx="2"/>
                                <line x1="16" x2="16" y1="2" y2="6"/>
                                <line x1="8" x2="8" y1="2" y2="6"/>
                                <line x1="3" x2="21" y1="10" y2="10"/>
                                <path d="M8 14h.01"/><path d="M12 14h.01"/><path d="M16 14h.01"/>
                                <path d="M8 18h.01"/><path d="M12 18h.01"/><path d="M16 18h.01"/>
                            </svg>
                            <span>Date de début</span>
                        </div>
                        <strong>{{ $dateDebut->format('d/m/Y') }}</strong>
                    </div>

                    <div class="subscription-info-item">
                        <div class="subscription-info-label">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="2"
                                 stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"/>
                                <polyline points="12 6 12 12 16 14"/>
                            </svg>
                            <span>Date de fin</span>
                        </div>
                        <strong>{{ $dateFin->format('d/m/Y') }}</strong>
                    </div>

                    <div class="subscription-progress-wrapper">
                        <div class="subscription-progress-header">
                            <span>Temps restant</span>
                            <strong class="{{ $joursRestants > 7 ? 'is-success' : ($joursRestants > 0 ? 'is-warning' : 'is-danger') }}">
                                {{ $joursRestants > 0 ? $joursRestants . ' jour' . ($joursRestants > 1 ? 's' : '') : 'Expiré' }}
                            </strong>
                        </div>
                        <div class="subscription-progress-bar">
                            <div class="subscription-progress-fill {{ $progressClass }}"
                                 data-progress="{{ $pourcentage }}"></div>
                        </div>
                    </div>

                    <div class="subscription-info-item subscription-info-item-notes">
                        <div class="subscription-info-label">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="2"
                                 stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                <polyline points="14 2 14 8 20 8"/>
                                <line x1="16" x2="8" y1="13" y2="13"/>
                                <line x1="16" x2="8" y1="17" y2="17"/>
                            </svg>
                            <span>Notes</span>
                        </div>
                    </div>
                    <div class="subscription-notes">
                        {{ $abonnement['notes'] ?? 'Aucune note' }}
                    </div>
                </div>
            </article>

            {{-- Carte Modules --}}
            <article class="subscription-card">
                <div class="subscription-card-header">
                    <div class="subscription-card-header-left">
                        <div class="subscription-icon subscription-icon-modules">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="2"
                                 stroke-linecap="round" stroke-linejoin="round">
                                <rect width="7" height="9" x="3" y="3" rx="1"/>
                                <rect width="7" height="5" x="14" y="3" rx="1"/>
                                <rect width="7" height="9" x="14" y="12" rx="1"/>
                                <rect width="7" height="5" x="3" y="16" rx="1"/>
                            </svg>
                        </div>
                        <div>
                            <span class="subscription-eyebrow">Modules</span>
                            <h3>Fonctionnalités incluses</h3>
                        </div>
                    </div>
                    @if(count($abonnement['modules']) > 0)
                        <span class="subscription-chip">
                            {{ count($abonnement['modules']) }} module{{ count($abonnement['modules']) > 1 ? 's' : '' }}
                        </span>
                    @endif
                </div>

                @if(count($abonnement['modules']) > 0)
                    <div class="subscription-module-list">
                        @foreach($abonnement['modules'] as $module)
                            <div class="subscription-module-item">
                                <div class="subscription-module-check">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                         fill="none" stroke="currentColor" stroke-width="3"
                                         stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="20 6 9 17 4 12"/>
                                    </svg>
                                </div>
                                <span>{{ $module }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="subscription-module-empty">
                        Aucun module activé sur cet abonnement.
                    </div>
                @endif
            </article>

        </div>
    </section>
@endsection
