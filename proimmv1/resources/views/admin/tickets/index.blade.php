@extends('admin.layouts.app')

@section('title', 'Tickets')
@section('header_title', 'Tickets')

@section('content')
    <section class="page">

        <div class="page-header">
            <div class="page-header-copy">
                <div class="page-heading">
                    <div>
                        <h2>Tickets de support</h2>
                        <p class="text-muted mb-0">Consultez et gérez l'ensemble des demandes de support client.</p>
                    </div>
                </div>
            </div>
            <div class="page-actions">
                <a href="#" class="btn btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Nouveau ticket
                </a>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <span>Total tickets</span>
                <strong>48</strong>
            </div>
            <div class="stat-card">
                <span>Ouverts</span>
                <strong class="is-info">21</strong>
            </div>
            <div class="stat-card">
                <span>En cours</span>
                <strong class="u-text-warning">12</strong>
            </div>
            <div class="stat-card">
                <span>Résolus</span>
                <strong class="is-success">15</strong>
            </div>
        </div>

        <div class="split-view">

            <div class="split-list-pane">

                <div class="split-list-header">
                    <div class="split-list-top">
                        <span class="split-list-title">Tickets <span class="count-pill">48</span></span>
                    </div>
                    <div class="search-field u-search-compact">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd"/></svg>
                        <input type="text" placeholder="Rechercher un ticket, un client…" id="support-search">
                    </div>
                    <div class="filter-pills">
                        <button class="filter-pill active" data-filter="tous">Tous</button>
                        <button class="filter-pill" data-filter="ouvert">Ouverts</button>
                        <button class="filter-pill" data-filter="encours">En cours</button>
                        <button class="filter-pill" data-filter="resolved">Résolus</button>
                        <button class="filter-pill" data-filter="ferme">Fermés</button>
                    </div>
                </div>

                <div class="item-list" id="item-list">

                    <div class="item-row selected" data-id="0048" data-status="ouvert" onclick="selectTicket(this)">
                        <div class="item-row-top">
                            <span class="item-ref">#TK-0048</span>
                            <span class="item-time">12 min</span>
                        </div>
                        <div class="item-title">Konan Djibril</div>
                        <div class="item-subtitle">Impossible de créer une mission</div>
                        <div class="item-badges">
                            <span class="badge ticket-badge-urgent">Urgente</span>
                            <span class="badge badge-danger">Ouvert</span>
                        </div>
                    </div>

                    <div class="item-row" data-id="0047" data-status="encours" onclick="selectTicket(this)">
                        <div class="item-row-top">
                            <span class="item-ref">#TK-0047</span>
                            <span class="item-time">1h</span>
                        </div>
                        <div class="item-title">Yao Tanoh</div>
                        <div class="item-subtitle">Facture non générée après mission</div>
                        <div class="item-badges">
                            <span class="badge ticket-badge-haute">Haute</span>
                            <span class="badge ticket-badge-encours">En cours</span>
                        </div>
                    </div>

                    <div class="item-row" data-id="0046" data-status="resolved" onclick="selectTicket(this)">
                        <div class="item-row-top">
                            <span class="item-ref">#TK-0046</span>
                            <span class="item-time">3h</span>
                        </div>
                        <div class="item-title">Fatou Ouattara</div>
                        <div class="item-subtitle">Problème de connexion au compte</div>
                        <div class="item-badges">
                            <span class="badge ticket-badge-normale">Normale</span>
                            <span class="badge badge-success">Résolu</span>
                        </div>
                    </div>

                    <div class="item-row" data-id="0045" data-status="ouvert" onclick="selectTicket(this)">
                        <div class="item-row-top">
                            <span class="item-ref">#TK-0045</span>
                            <span class="item-time">1 jour</span>
                        </div>
                        <div class="item-title">Moussa Bamba</div>
                        <div class="item-subtitle">Véhicule non listé dans le parc</div>
                        <div class="item-badges">
                            <span class="badge ticket-badge-basse">Basse</span>
                            <span class="badge badge-danger">Ouvert</span>
                        </div>
                    </div>

                    <div class="item-row" data-id="0044" data-status="ferme" onclick="selectTicket(this)">
                        <div class="item-row-top">
                            <span class="item-ref">#TK-0044</span>
                            <span class="item-time">2 jours</span>
                        </div>
                        <div class="item-title">Sandrine Coulibaly</div>
                        <div class="item-subtitle">Rapport de caisse incorrect</div>
                        <div class="item-badges">
                            <span class="badge ticket-badge-haute">Haute</span>
                            <span class="badge ticket-badge-ferme">Fermé</span>
                        </div>
                    </div>

                </div>
            </div>

            <div class="detail-pane" id="detail-pane">

                <div class="detail-header">
                    <div class="detail-header-copy">
                        <span class="detail-eyebrow">#TK-0048 &middot; Agence Plateau</span>
                        <h3 class="detail-title">Impossible de créer une mission</h3>
                    </div>
                    <div class="detail-actions">

                        <a href="#" class="btn btn-outline btn-sm">Changer statut</a>
                        <a href="#" class="btn btn-primary btn-sm">Résoudre</a>
                    </div>
                </div>

                <div class="meta-grid">
                    <div class="meta-item">
                        <span class="meta-label">Priorité</span>
                        <span class="badge ticket-badge-urgent">Urgente</span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Statut</span>
                        <span class="badge badge-danger">Ouvert</span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Dernière activité</span>
                        <span class="meta-value">il y a 12 min</span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Agence</span>
                        <span class="meta-value">Agence Plateau</span>
                    </div>
                </div>

                <div class="detail-body">

                    <div class="entity-card">
                        <div class="entity-avatar">KD</div>
                        <div>
                            <div class="entity-name">Konan Djibril</div>
                            <div class="entity-muted">konan@example.ci</div>
                        </div>
                    </div>

                    <div class="prose-box">
                        Depuis ce matin, impossible de créer une nouvelle mission depuis le tableau de bord.
                        Le bouton "Créer" reste grisé après authentification. Le problème persiste sur Chrome et Firefox.
                    </div>

                    <div class="thread">

                        <div class="thread-message">
                            <div class="entity-avatar sm">KD</div>
                            <div class="message-bubble">
                                <div class="message-author">Konan Djibril</div>
                                <div class="message-text">Bonjour, le bouton créer une mission est grisé depuis ce matin. J'ai vidé le cache et redémarré sans succès.</div>
                                <div class="message-time">08h14</div>
                            </div>
                        </div>

                        <div class="thread-message is-agent">
                            <div class="entity-avatar sm entity-avatar-success">SP</div>
                            <div class="message-bubble is-agent">
                                <div class="message-author">Support</div>
                                <div class="message-text">Bonjour Konan, merci pour votre retour. Nous regardons le problème. Pouvez-vous préciser votre navigateur et la version ?</div>
                                <div class="message-time">08h31</div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="reply-box">
                    <textarea class="reply-input" rows="2" placeholder="Écrire une réponse…"></textarea>
                    <button class="btn btn-primary btn-sm">Envoyer</button>
                </div>

            </div>
        </div>

    </section>
@endsection