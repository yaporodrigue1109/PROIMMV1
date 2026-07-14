@extends('agence.layouts.app')

@section('title', 'Paramétrage')
@section('header_title', 'Paramétrage')

@section('content')
    @php
        $fieldValue = fn ($field, $default = '') => old($field, $agence[$field] ?? $default);
        $agencyName = $agence->name ?? auth('user')->user()?->name ?? 'Mon Agence';

        $navItems = [
            ['id' => 'agence', 'label' => 'Agence', 'icon' => 'M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.53L12 21.35z'],
            ['id' => 'general', 'label' => 'Général', 'icon' => 'M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 010 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 010-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z'],
            ['id' => 'facturation', 'label' => 'Facturation', 'icon' => 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z'],
            ['id' => 'visuel', 'label' => 'Visuel', 'icon' => 'M3.75 3.75h16.5v16.5H3.75V3.75zm4.5 4.5h7.5m-7.5 4.5h7.5m-7.5 4.5h4.5'],
            ['id' => 'notifications', 'label' => 'Notifications', 'icon' => 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.172V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.172c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9'],
        ];

        $statusPills = [
            ['label' => 'Région', 'value' => $agence?->region?->name ?? '—'],
            ['label' => 'Ville', 'value' => $agence?->ville?->name ?? '—'],
            ['label' => 'Devise', 'value' => $parametrage->devise ?? 'XOF'],
            ['label' => 'Fuseau', 'value' => $parametrage->timezone ?? 'Africa/Abidjan'],
        ];

        $on = fn ($value) => (int) ($value ?? 0) === 1;
    @endphp

    <div class="rp-page param-page">
        <div class="page-header">
            <div class="page-header-copy">
                <div class="page-heading">
                    <div>
                        <h2>Paramétrage</h2>
                        <p class="text-muted mb-0">
                            Configurez {{ $agencyName }} sans quitter l’interface agence.
                        </p>
                    </div>
                </div>
            </div>

            <div class="page-actions">
                <a href="{{ route('agence.dashboard') }}" class="btn btn-outline">Retour dashboard</a>
                <a href="{{ route('agence.parametrage.index') }}#agence" class="btn btn-primary">Éditer</a>
            </div>
        </div>

        <div class="rp-kpi-strip">
            @foreach($statusPills as $pill)
                <div class="rp-kpi">
                    <span class="rp-kpi-label">{{ $pill['label'] }}</span>
                    <span class="rp-kpi-value">{{ $pill['value'] }}</span>
                    <span class="rp-kpi-delta neutral">Paramètres agence</span>
                </div>
            @endforeach
        </div>

        <nav class="rp-topbar" aria-label="Paramètres agence">
            @foreach($navItems as $item)
                <button class="rp-tab {{ $loop->first ? 'is-active' : '' }}" data-tab="{{ $item['id'] }}" type="button">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" />
                    </svg>
                    {{ $item['label'] }}
                </button>
            @endforeach
        </nav>

        <div class="rp-period-bar">
            <span class="rp-period-label">Dernière mise à jour</span>
            <div class="rp-period-pills">
                <span class="rp-period-pill is-active">{{ now()->format('d/m/Y') }}</span>
            </div>
            <div class="rp-period-right">
                Agence : <strong>{{ $agencyName }}</strong>
            </div>
        </div>

        @if(session('success'))
            <div class="rp-alert is-success">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="rp-alert is-error">
                {{ session('error') }}
            </div>
        @endif

        <div class="rp-panel is-active" id="panel-agence">
            <form action="{{ route('agence.parametrage.agence.update') }}" method="POST" id="form-agence" class="param-form-grid">
                @csrf
                @method('PUT')

                <div class="rp-card">
                    <div class="rp-card-head">
                        <h3 class="rp-card-title">Informations de l’agence</h3>
                        <span class="rp-card-count">Identité officielle</span>
                    </div>

                    <div class="form-grid">
                        <label class="form-field form-field-wide">
                            <span>Nom de l’agence <span class="required">*</span></span>
                            <x-ui.input name="name" type="text" :value="$fieldValue('name')" placeholder="Pros Immobilier Cocody" required />
                        </label>
                        <label class="form-field">
                            <span>Sigle / Abréviation</span>
                            <x-ui.input name="sigle" type="text" :value="$fieldValue('sigle')" placeholder="ANC" />
                        </label>
                        <label class="form-field">
                            <span>Numéro RCCM</span>
                            <x-ui.input name="rccm" type="text" :value="$fieldValue('rccm')" placeholder="CI-ABJ-2015-B-12345" />
                        </label>
                        <label class="form-field">
                            <span>Numéro contribuable</span>
                            <x-ui.input name="num_contribuable" type="text" :value="$fieldValue('num_contribuable')" placeholder="0012345678A" />
                        </label>
                        <label class="form-field">
                            <span>Régime fiscal</span>
                            <x-ui.select
                                    name="regime_fiscal"
                                    :options="['SARL' => 'SARL', 'SAS' => 'SAS', 'SA' => 'SA']"
                                    :value="$fieldValue('regime_fiscal')"
                                    placeholder="Sélectionnez votre régime fiscal"
                            />
                        </label>
                    </div>
                </div>

                <div class="rp-card">
                    <div class="rp-card-head">
                        <h3 class="rp-card-title">Coordonnées</h3>
                        <span class="rp-card-count">Contact et adresse</span>
                    </div>

                    <div class="form-grid">
                        <label class="form-field form-field-wide">
                            <span>Adresse</span>
                            <x-ui.textarea name="adresse" :value="$fieldValue('adresse')" rows="3" placeholder="Adresse complète de l’agence" />
                        </label>
                        <label class="form-field">
                            <span>Téléphone 1</span>
                            <x-ui.input name="tel1" type="tel" :value="$fieldValue('tel1')" placeholder="xxxxxxxxxx" />
                        </label>
                        <label class="form-field">
                            <span>Téléphone 2</span>
                            <x-ui.input name="tel2" type="tel" :value="$fieldValue('tel2')" placeholder="xxxxxxxxxx" />
                        </label>
                        <label class="form-field">
                            <span>Email principal</span>
                            <x-ui.input name="email1" type="email" :value="$fieldValue('email1')" placeholder="contact@agence.com" />
                        </label>
                        <label class="form-field">
                            <span>Email secondaire</span>
                            <x-ui.input name="email2" type="email" :value="$fieldValue('email2')" placeholder="comptabilite@agence.com" />
                        </label>
                        <label class="form-field">
                            <span>Région</span>
                            <x-ui.select
                                    name="region_id"
                                    :options="collect($regions)->pluck('name', 'id')->toArray()"
                                    :value="$fieldValue('region_id')"
                                    placeholder="Sélectionnez votre région"
                                    onchange="getRequest('{{ url('/') }}/admin/list/city?parent_id='+this.value,'ville_id','select',this.value)"
                            />
                        </label>
                        <label class="form-field">
                            <span>Ville</span>
                            <x-ui.select
                                    name="ville_id"
                                    :options="collect($villes ?? [])->where('region_id', $fieldValue('region_id'))->pluck('name', 'id')->toArray()"
                                    :value="$fieldValue('ville_id')"
                                    placeholder="Sélectionnez votre ville"
                            />
                        </label>
                        <label class="form-field">
                            <span>Boîte postale</span>
                            <x-ui.input name="bp" type="text" :value="$fieldValue('bp')" placeholder="BP 1234" />
                        </label>
                        <label class="form-field">
                            <span>Site web</span>
                            <x-ui.input name="site_web" type="text" :value="$fieldValue('site_web')" placeholder="https://www.agence.ci" />
                        </label>
                    </div>
                </div>

                <div class="rp-card">
                    <div class="rp-card-head">
                        <h3 class="rp-card-title">Compte bancaire</h3>
                        <span class="rp-card-count">Domiciliation</span>
                    </div>

                    <div class="form-grid">
                        <label class="form-field">
                            <span>Banque domiciliataire</span>
                            <x-ui.input name="banque" type="text" :value="$fieldValue('banque')" placeholder="SGBCI, ECOBANK..." />
                        </label>
                        <label class="form-field">
                            <span>Agence bancaire</span>
                            <x-ui.input name="agence_bancaire" type="text" :value="$fieldValue('agence_bancaire')" placeholder="Plateau Centre" />
                        </label>
                        <label class="form-field form-field-wide">
                            <span>Numéro de compte (IBAN / RIB)</span>
                            <x-ui.input name="rib" type="text" :value="$fieldValue('rib')" placeholder="CIxxx xxxxx xxxxxxxxxxx xx" />
                        </label>
                    </div>

                    <div class="u-flex-end u-mt-lg">
                        <button type="button" class="btn btn-outline" onclick="resetForm('form-agence')">Annuler</button>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="rp-panel" id="panel-general">
            <form action="{{ route('agence.parametrage.general.update') }}" method="POST" id="form-general" class="param-form-grid">
                @csrf
                @method('PUT')

                <div class="rp-card">
                    <div class="rp-card-head">
                        <h3 class="rp-card-title">Devise & localisation</h3>
                        <span class="rp-card-count">Paramètres globaux</span>
                    </div>

                    <div class="form-grid">
                        <label class="form-field">
                            <span>Devise par défaut</span>
                            <x-ui.select
                                    name="devise"
                                    :options="[
                                    'XOF' => 'XOF — Franc CFA (BCEAO)',
                                    'EUR' => 'EUR — Euro',
                                    'USD' => 'USD — Dollar américain',
                                ]"
                                    :value="$parametrage->devise ?? 'XOF'"
                            />
                        </label>
                        <label class="form-field">
                            <span>Langue de l’interface</span>
                            <x-ui.select
                                    name="langue"
                                    :options="['fr' => 'Français', 'en' => 'Anglais']"
                                    :value="$parametrage->langue ?? 'fr'"
                            />
                        </label>
                        <label class="form-field">
                            <span>Format de date</span>
                            <x-ui.select
                                    name="format_date"
                                    :options="[
                                    'd/m/Y' => 'JJ/MM/AAAA',
                                    'm/d/Y' => 'MM/JJ/AAAA',
                                    'Y-m-d' => 'AAAA-MM-JJ',
                                ]"
                                    :value="$parametrage->format_date ?? 'd/m/Y'"
                            />
                        </label>
                        <label class="form-field">
                            <span>Fuseau horaire</span>
                            <x-ui.select
                                    name="timezone"
                                    :options="[
                                    'Africa/Abidjan' => 'Africa/Abidjan (GMT+0)',
                                    'Europe/Paris' => 'Europe/Paris (GMT+1/+2)',
                                ]"
                                    :value="$parametrage->timezone ?? 'Africa/Abidjan'"
                            />
                        </label>
                    </div>
                </div>

                <div class="rp-card">
                    <div class="rp-card-head">
                        <h3 class="rp-card-title">Préférences système</h3>
                        <span class="rp-card-count">Automatisations</span>
                    </div>

                    <div class="toggle-list">
                        <div class="toggle-row">
                            <div class="toggle-info">
                                <p>Sauvegarde automatique</p>
                                <span>Enregistrer les modifications toutes les 5 minutes</span>
                            </div>
                            <button type="button" class="tgl {{ $on($parametrage->sauvegarde_auto ?? 1) ? 'on' : '' }}" onclick="toggleSwitch(this, 'sauvegarde_auto')"></button>
                            <input type="hidden" name="sauvegarde_auto" value="{{ $parametrage->sauvegarde_auto ?? 1 }}">
                        </div>
                        <div class="toggle-row">
                            <div class="toggle-info">
                                <p>Mode double validation</p>
                                <span>Exiger une confirmation avant toute suppression</span>
                            </div>
                            <button type="button" class="tgl {{ $on($parametrage->double_validation ?? 1) ? 'on' : '' }}" onclick="toggleSwitch(this, 'double_validation')"></button>
                            <input type="hidden" name="double_validation" value="{{ $parametrage->double_validation ?? 1 }}">
                        </div>
                        <div class="toggle-row">
                            <div class="toggle-info">
                                <p>Journal d’activités</p>
                                <span>Enregistrer toutes les actions des utilisateurs</span>
                            </div>
                            <button type="button" class="tgl {{ $on($parametrage->journal_activites ?? 1) ? 'on' : '' }}" onclick="toggleSwitch(this, 'journal_activites')"></button>
                            <input type="hidden" name="journal_activites" value="{{ $parametrage->journal_activites ?? 1 }}">
                        </div>
                        <div class="toggle-row">
                            <div class="toggle-info">
                                <p>Accès multi-session</p>
                                <span>Autoriser la connexion simultanée sur plusieurs appareils</span>
                            </div>
                            <button type="button" class="tgl {{ $on($parametrage->multi_session ?? 0) ? 'on' : '' }}" onclick="toggleSwitch(this, 'multi_session')"></button>
                            <input type="hidden" name="multi_session" value="{{ $parametrage->multi_session ?? 0 }}">
                        </div>
                    </div>

                    <div class="u-flex-end u-mt-lg">
                        <button type="button" class="btn btn-outline" onclick="resetForm('form-general')">Annuler</button>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="rp-panel" id="panel-facturation">
            <form action="{{ route('agence.parametrage.facturation.update') }}" method="POST" id="form-facturation" class="param-form-grid">
                @csrf
                @method('PUT')

                <div class="rp-card">
                    <div class="rp-card-head">
                        <h3 class="rp-card-title">Cycle de facturation</h3>
                        <span class="rp-card-count">Périodicité</span>
                    </div>

                    <div class="form-grid">
                        <label class="form-field">
                            <span>Période de facturation</span>
                            <x-ui.select
                                    name="periode_facturation"
                                    :options="[
                                    'mensuelle' => 'Mensuelle',
                                    'trimestrielle' => 'Trimestrielle',
                                    'semestrielle' => 'Semestrielle',
                                    'annuelle' => 'Annuelle',
                                    'commande' => 'À la commande',
                                ]"
                                    :value="$parametrage->periode_facturation ?? 'mensuelle'"
                            />
                        </label>
                        <label class="form-field">
                            <span>Jour d’émission</span>
                            <x-ui.select
                                    name="jour_emission"
                                    :options="[
                                    '1' => '1er du mois',
                                    '5' => '5 du mois',
                                    '15' => '15 du mois',
                                    'last' => 'Dernier jour du mois',
                                ]"
                                    :value="$parametrage->jour_emission ?? '1'"
                            />
                        </label>
                        <label class="form-field">
                            <span>Délai limite de paiement (jours)</span>
                            <x-ui.input type="number" name="delai_paiement" :value="old('delai_paiement', $parametrage->delai_paiement ?? 30)" min="0" max="180" />
                        </label>
                        <label class="form-field">
                            <span>Pénalité de retard (%/mois)</span>
                            <x-ui.input type="number" name="penalite_retard" :value="old('penalite_retard', $parametrage->penalite_retard ?? 1.5)" step="0.1" min="0" />
                        </label>
                        <label class="form-field">
                            <span>Préfixe numéro de facture</span>
                            <x-ui.input type="text" name="prefixe_facture" :value="old('prefixe_facture', $parametrage->prefixe_facture ?? ('FAC-'.date('Y').'-'))" />
                        </label>
                        <label class="form-field">
                            <span>Prochain numéro de séquence</span>
                            <x-ui.input type="number" name="sequence_facture" :value="old('sequence_facture', $parametrage->sequence_facture ?? 1)" min="1" />
                        </label>
                    </div>
                </div>

                <div class="rp-card">
                    <div class="rp-card-head">
                        <h3 class="rp-card-title">Commission & taxes</h3>
                        <span class="rp-card-count">Calculs financiers</span>
                    </div>

                    <div class="form-grid">
                        <label class="form-field">
                            <span>Commission agence (%)</span>
                            <x-ui.input type="number" name="commission" id="inp-commission" :value="old('commission', $parametrage->commission ?? 15)" step="0.5" min="0" max="100" oninput="document.getElementById('comm-val').textContent=this.value+'%'" />
                        </label>
                        <label class="form-field">
                            <span>Base de calcul</span>
                            <x-ui.select
                                    name="base_commission"
                                    :options="[
                                    'ht' => 'Sur le montant HT',
                                    'ttc' => 'Sur le montant TTC',
                                    'brut' => 'Sur le budget brut',
                                ]"
                                    :value="$parametrage->base_commission ?? 'ht'"
                            />
                        </label>
                        <label class="form-field">
                            <span>TVA (%)</span>
                            <x-ui.input type="number" name="tva" :value="old('tva', $parametrage->tva ?? 18)" step="0.5" min="0" />
                        </label>
                        <label class="form-field">
                            <span>AIB (%)</span>
                            <x-ui.input type="number" name="aib" :value="old('aib', $parametrage->aib ?? 5)" step="0.5" min="0" />
                        </label>
                        <label class="form-field">
                            <span>RAS (%)</span>
                            <x-ui.input type="number" name="ras" :value="old('ras', $parametrage->ras ?? 2)" step="0.5" min="0" />
                        </label>
                    </div>

                    <div class="param-note">
                        Commission actuelle : <strong id="comm-val">{{ $parametrage->commission ?? 15 }}%</strong>
                    </div>
                </div>

                <div class="rp-card">
                    <div class="rp-card-head">
                        <h3 class="rp-card-title">Acomptes & règlements</h3>
                        <span class="rp-card-count">Conditions de paiement</span>
                    </div>

                    <div class="form-grid">
                        <label class="form-field">
                            <span>Acompte minimum exigé (%)</span>
                            <x-ui.input type="number" name="acompte_min" :value="old('acompte_min', $parametrage->acompte_min ?? 30)" min="0" max="100" />
                        </label>
                        <label class="form-field">
                            <span>Mode de règlement par défaut</span>
                            <x-ui.select
                                    name="mode_reglement_id"
                                    :options="collect($modePaiement)->pluck('name', 'id')->toArray()"
                                    :value="old('mode_reglement_id', $parametrage->mode_reglement_id ?? 1)"
                            />
                        </label>
                    </div>

                    <div class="u-flex-end u-mt-lg">
                        <button type="button" class="btn btn-outline" onclick="resetForm('form-facturation')">Annuler</button>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="rp-panel" id="panel-visuel">
            <form action="{{ route('agence.parametrage.logos.update') }}" method="POST" enctype="multipart/form-data" id="form-logos" class="param-form-grid">
                @csrf
                @method('PUT')

                <div class="rp-card">
                    <div class="rp-card-head">
                        <h3 class="rp-card-title">Logo principal</h3>
                        <span class="rp-card-count">Image de marque</span>
                    </div>

                    <div class="upload-layout">
                        <div>
                            <div class="upload-zone" onclick="document.getElementById('inp-logo').click()">
                                <span class="upload-ico">↑</span>
                                <p>Importer le logo</p>
                                <span>PNG, SVG - max 2 Mo</span>
                            </div>
                            <input type="file" id="inp-logo" name="logo" accept="image/*" class="d-none" onchange="previewFile(this, 'prev-logo')">
                        </div>

                        <div>
                            <div class="preview-box" id="prev-logo">
                                @if($parametrage->logo ?? false)
                                    <img src="{{ asset('storage/'.$parametrage->logo) }}" alt="Logo agence">
                                @else
                                    <span>Aperçu du logo</span>
                                @endif
                            </div>
                            <div class="form-grid">
                                <label class="form-field">
                                    <span>Largeur sur facture (px)</span>
                                    <x-ui.input type="number" name="logo_largeur" :value="$parametrage->logo_largeur ?? 200" />
                                </label>
                                <label class="form-field">
                                    <span>Position sur facture</span>
                                    <x-ui.select
                                            name="logo_position"
                                            :options="['gauche' => 'En-tête gauche', 'centre' => 'En-tête centré', 'droit' => 'En-tête droit']"
                                            :value="$parametrage->logo_position ?? 'gauche'"
                                    />
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rp-card">
                    <div class="rp-card-head">
                        <h3 class="rp-card-title">Logos secondaires</h3>
                        <span class="rp-card-count">Tutelle, partenaire, cachet</span>
                    </div>

                    <div class="upload-grid-3">
                        @foreach([
                            ['id' => 'inp-tutelle', 'name' => 'logo_tutelle', 'label' => 'Logo tutelle', 'icon' => '🏛', 'preview' => 'prev-tutelle'],
                            ['id' => 'inp-partenaire', 'name' => 'logo_partenaire', 'label' => 'Logo partenaire', 'icon' => '🤝', 'preview' => 'prev-partenaire'],
                            ['id' => 'inp-cachet', 'name' => 'cachet', 'label' => 'Cachet / Tampon', 'icon' => '🧾', 'preview' => 'prev-cachet'],
                        ] as $logo)
                            <div class="mini-upload">
                                <div class="mini-upload-label">{{ $logo['label'] }}</div>
                                <div class="upload-zone" onclick="document.getElementById('{{ $logo['id'] }}').click()">
                                    <span class="upload-ico">{{ $logo['icon'] }}</span>
                                    <p>Importer</p>
                                    <span>PNG, SVG</span>
                                </div>
                                <input type="file" id="{{ $logo['id'] }}" name="{{ $logo['name'] }}" accept="image/*" class="d-none" onchange="previewFile(this, '{{ $logo['preview'] }}')">
                                <div class="preview-mini" id="{{ $logo['preview'] }}"></div>
                            </div>
                        @endforeach
                    </div>

                    <div class="u-flex-end u-mt-lg">
                        <button type="button" class="btn btn-outline" onclick="resetForm('form-logos')">Annuler</button>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </div>
            </form>

            <form action="{{ route('agence.parametrage.signatures.update') }}" method="POST" enctype="multipart/form-data" id="form-signatures" class="param-form-grid u-mt-lg">
                @csrf
                @method('PUT')

                <div class="rp-card">
                    <div class="rp-card-head">
                        <h3 class="rp-card-title">Signatures officielles</h3>
                        <span class="rp-card-count">DG, secrétariat, comptabilité</span>
                    </div>

                    <div class="sig-grid">
                        @foreach([
                            ['id' => 'sig-dg', 'img' => 'prev-sig-dg', 'ico' => 'ico-dg', 'name' => 'signature_dg', 'title' => 'Directeur Général (DG)', 'nom' => 'dg_nom', 'titre' => 'dg_titre', 'default' => 'Directeur Général'],
                            ['id' => 'sig-sg', 'img' => 'prev-sig-sg', 'ico' => 'ico-sg', 'name' => 'signature_sg', 'title' => 'Secrétariat Général', 'nom' => 'sg_nom', 'titre' => 'sg_titre', 'default' => 'Secrétaire Général(e)'],
                            ['id' => 'sig-cpt', 'img' => 'prev-sig-cpt', 'ico' => 'ico-cpt', 'name' => 'signature_cpt', 'title' => 'Comptabilité', 'nom' => 'cpt_nom', 'titre' => 'cpt_titre', 'default' => 'Responsable Comptable'],
                        ] as $sig)
                            <div class="sig-card">
                                <div class="sig-card-label">{{ $sig['title'] }}</div>
                                <div class="sig-preview" onclick="document.getElementById('{{ $sig['id'] }}').click()">
                                    <i class="fas fa-pen" id="{{ $sig['ico'] }}"></i>
                                    <img id="{{ $sig['img'] }}" @if($parametrage->{$sig['name']} ?? false) src="{{ asset('storage/'.$parametrage->{$sig['name']}) }}" style="display:block;" @endif alt="{{ $sig['title'] }}">
                                </div>
                                <input type="file" id="{{ $sig['id'] }}" name="{{ $sig['name'] }}" accept="image/*" class="d-none" onchange="previewSig(this, '{{ $sig['img'] }}', '{{ $sig['ico'] }}')">
                                <button type="button" class="btn btn-outline btn-sm sig-upload-btn" onclick="document.getElementById('{{ $sig['id'] }}').click()">Importer la signature</button>

                                <div class="form-grid">
                                    <label class="form-field">
                                        <span>Nom complet</span>
                                        <x-ui.input type="text" name="{{ $sig['nom'] }}" :value="$parametrage->{$sig['nom']} ?? ''" placeholder="M. Prénom NOM" />
                                    </label>
                                    <label class="form-field">
                                        <span>Titre</span>
                                        <x-ui.input type="text" name="{{ $sig['titre'] }}" :value="$parametrage->{$sig['titre']} ?? $sig['default']" placeholder="{{ $sig['default'] }}" />
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="param-note">
                        Formats acceptés : PNG avec fond transparent recommandé. Taille max 500 Ko par signature.
                    </div>
                </div>

                <div class="rp-card">
                    <div class="rp-card-head">
                        <h3 class="rp-card-title">Règles d’apposition</h3>
                        <span class="rp-card-count">Automatisation des documents</span>
                    </div>

                    <div class="toggle-list">
                        <div class="toggle-row">
                            <div class="toggle-info">
                                <p>Signature DG sur toutes les factures</p>
                                <span>Apposer automatiquement sur chaque facture émise</span>
                            </div>
                            <button type="button" class="tgl {{ $on($parametrage->sig_dg_facture ?? 1) ? 'on' : '' }}" onclick="toggleSwitch(this, 'sig_dg_facture')"></button>
                            <input type="hidden" name="sig_dg_facture" value="{{ $parametrage->sig_dg_facture ?? 1 }}">
                        </div>
                        <div class="toggle-row">
                            <div class="toggle-info">
                                <p>Double signature (DG + Comptabilité)</p>
                                <span>Exiger deux signatures pour les montants supérieurs au seuil</span>
                            </div>
                            <button type="button" class="tgl {{ $on($parametrage->sig_double ?? 1) ? 'on' : '' }}" onclick="toggleSwitch(this, 'sig_double')"></button>
                            <input type="hidden" name="sig_double" value="{{ $parametrage->sig_double ?? 1 }}">
                        </div>
                        <div class="toggle-row">
                            <div class="toggle-info">
                                <p>Cachet automatique</p>
                                <span>Apposer le cachet de l’agence sur chaque document</span>
                            </div>
                            <button type="button" class="tgl {{ $on($parametrage->cachet_auto ?? 0) ? 'on' : '' }}" onclick="toggleSwitch(this, 'cachet_auto')"></button>
                            <input type="hidden" name="cachet_auto" value="{{ $parametrage->cachet_auto ?? 0 }}">
                        </div>
                    </div>

                    <div class="u-flex-end u-mt-lg">
                        <button type="button" class="btn btn-outline" onclick="resetForm('form-signatures')">Annuler</button>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="rp-panel" id="panel-notifications">
            <form action="{{ route('agence.parametrage.notifications.update') }}" method="POST" id="form-notif" class="param-form-grid">
                @csrf
                @method('PUT')

                <div class="rp-card">
                    <div class="rp-card-head">
                        <h3 class="rp-card-title">Alertes de facturation</h3>
                        <span class="rp-card-count">Notifications automatiques</span>
                    </div>

                    <div class="toggle-list">
                        <div class="toggle-row">
                            <div class="toggle-info">
                                <p>Rappel avant échéance</p>
                                <span>Envoyer un email X jours avant la date limite de paiement</span>
                            </div>
                            <button type="button" class="tgl {{ $on($parametrage->notif_rappel ?? 1) ? 'on' : '' }}" onclick="toggleSwitch(this, 'notif_rappel')"></button>
                            <input type="hidden" name="notif_rappel" value="{{ $parametrage->notif_rappel ?? 1 }}">
                        </div>
                        <div class="toggle-row">
                            <div class="toggle-info">
                                <p>Alerte de retard de paiement</p>
                                <span>Notifier le service comptable dès le dépassement de l’échéance</span>
                            </div>
                            <button type="button" class="tgl {{ $on($parametrage->notif_retard ?? 1) ? 'on' : '' }}" onclick="toggleSwitch(this, 'notif_retard')"></button>
                            <input type="hidden" name="notif_retard" value="{{ $parametrage->notif_retard ?? 1 }}">
                        </div>
                        <div class="toggle-row">
                            <div class="toggle-info">
                                <p>Confirmation de réception de paiement</p>
                                <span>Envoyer un reçu automatique au client après enregistrement</span>
                            </div>
                            <button type="button" class="tgl {{ $on($parametrage->notif_recu ?? 0) ? 'on' : '' }}" onclick="toggleSwitch(this, 'notif_recu')"></button>
                            <input type="hidden" name="notif_recu" value="{{ $parametrage->notif_recu ?? 0 }}">
                        </div>
                    </div>
                </div>

                <div class="rp-card">
                    <div class="rp-card-head">
                        <h3 class="rp-card-title">Destinataires par défaut</h3>
                        <span class="rp-card-count">Diffusion des alertes</span>
                    </div>

                    <div class="form-grid">
                        <label class="form-field form-field-wide">
                            <span>Email comptabilité</span>
                            <x-ui.input type="email" name="email_compta" :value="old('email_compta', $parametrage->email_compta ?? '')" placeholder="comptabilite@agence.ci" />
                        </label>
                        <label class="form-field form-field-wide">
                            <span>Email DG</span>
                            <x-ui.input type="email" name="email_dg" :value="old('email_dg', $parametrage->email_dg ?? '')" placeholder="dg@agence.ci" />
                        </label>
                        <label class="form-field">
                            <span>Délai de rappel (jours avant échéance)</span>
                            <x-ui.input type="number" name="delai_rappel" :value="old('delai_rappel', $parametrage->delai_rappel ?? 7)" min="1" />
                        </label>
                        <label class="form-field">
                            <span>Seuil pour copie DG (XOF)</span>
                            <x-ui.input type="number" name="seuil_dg" :value="old('seuil_dg', $parametrage->seuil_dg ?? 1000000)" step="50000" />
                        </label>
                    </div>

                    <div class="u-flex-end u-mt-lg">
                        <button type="button" class="btn btn-outline" onclick="resetForm('form-notif')">Annuler</button>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function showSection(id) {
            document.querySelectorAll('.rp-panel').forEach((panel) => panel.classList.remove('is-active'));
            document.querySelectorAll('.rp-tab').forEach((tab) => tab.classList.remove('is-active'));
            document.getElementById('panel-' + id)?.classList.add('is-active');
            document.querySelector(`[data-tab="${id}"]`)?.classList.add('is-active');
        }

        document.querySelectorAll('.rp-tab').forEach((tab) => {
            tab.addEventListener('click', function () {
                showSection(this.dataset.tab);
            });
        });

        function toggleSwitch(btn, fieldName) {
            btn.classList.toggle('on');
            const isOn = btn.classList.contains('on') ? 1 : 0;
            const form = btn.closest('form');
            if (form) {
                const hidden = form.querySelector('input[name="' + fieldName + '"]');
                if (hidden) hidden.value = isOn;
            }
        }

        function previewFile(input, previewId) {
            const file = input.files && input.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function (e) {
                const box = document.getElementById(previewId);
                if (!box) return;

                box.innerHTML = '';
                const img = document.createElement('img');
                img.src = e.target.result;
                img.alt = 'Aperçu';
                box.appendChild(img);
            };
            reader.readAsDataURL(file);
        }

        function previewSig(input, imgId, iconId) {
            const file = input.files && input.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function (e) {
                const img = document.getElementById(imgId);
                const ico = document.getElementById(iconId);
                if (img) {
                    img.src = e.target.result;
                    img.style.display = 'block';
                }
                if (ico) ico.style.display = 'none';
            };
            reader.readAsDataURL(file);
        }

        function resetForm(formId) {
            const form = document.getElementById(formId);
            if (form) form.reset();
        }

        const hash = window.location.hash.replace('#', '');
        if (hash && document.querySelector(`[data-tab="${hash}"]`)) {
            showSection(hash);
        }
    </script>
@endsection

@push('styles')
    <style>
        .param-page {
            display: grid;
            gap: 1rem;
        }

        .param-page .rp-card {
            padding: 1.15rem 1.25rem 1.25rem;
            overflow: visible;
        }

        .param-page .rp-card-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1rem;
            padding-bottom: .9rem;
            border-bottom: 1px solid rgba(148, 163, 184, .18);
        }

        .param-page .rp-card-title {
            margin: 0;
        }

        .param-page .rp-card-count {
            white-space: nowrap;
        }

        .param-form-grid {
            display: grid;
            gap: 1rem;
        }

        .param-page .form-field {
            position: relative;
            z-index: 0;
        }

        .param-page .form-field:has(.ui-dropdown.open) {
            z-index: 60;
        }

        .param-page .ui-select-dropdown {
            position: relative;
            z-index: 2;
        }

        .param-page .ui-dropdown.open {
            z-index: 70 !important;
        }

        .required {
            color: #dc2626;
        }

        .toggle-list {
            display: grid;
            gap: .85rem;
        }

        .toggle-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 1rem 1.1rem;
            border-radius: 1rem;
            border: 1px solid var(--border);
            background: var(--card);
        }

        .toggle-info p {
            margin: 0 0 .2rem;
            font-weight: 700;
        }

        .toggle-info span {
            color: var(--muted-foreground);
            font-size: .9rem;
        }

        .tgl {
            position: relative;
            width: 52px;
            height: 30px;
            border: 0;
            border-radius: 999px;
            background: rgba(148, 163, 184, .35);
            cursor: pointer;
            flex-shrink: 0;
        }

        .tgl::after {
            content: '';
            position: absolute;
            top: 3px;
            left: 3px;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: #fff;
            transition: transform .18s ease;
            box-shadow: 0 4px 12px rgba(15, 23, 42, .18);
        }

        .tgl.on {
            background: linear-gradient(135deg, #005499, #76c300);
        }

        .tgl.on::after {
            transform: translateX(22px);
        }

        .param-note {
            margin-top: .9rem;
            padding: .85rem 1rem;
            border-radius: 1rem;
            background: rgba(118, 195, 0, .08);
            color: var(--foreground);
            font-size: .92rem;
        }

        .upload-layout {
            display: grid;
            gap: 1rem;
            grid-template-columns: 280px minmax(0, 1fr);
            align-items: start;
        }

        .upload-zone {
            display: grid;
            place-items: center;
            gap: .35rem;
            min-height: 180px;
            padding: 1rem;
            border-radius: 1.25rem;
            border: 1px dashed var(--border);
            background: var(--muted);
            text-align: center;
            cursor: pointer;
        }

        .upload-ico {
            display: grid;
            place-items: center;
            width: 54px;
            height: 54px;
            border-radius: 50%;
            background: rgba(118, 195, 0, .12);
            color: var(--primary);
            font-weight: 800;
        }

        .upload-zone p,
        .upload-zone span {
            margin: 0;
        }

        .preview-box {
            min-height: 180px;
            border-radius: 1.25rem;
            border: 1px solid var(--border);
            background: var(--card);
            display: grid;
            place-items: center;
            overflow: hidden;
            margin-bottom: 1rem;
        }

        .preview-box img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .upload-grid-3 {
            display: grid;
            gap: 1rem;
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .mini-upload {
            display: grid;
            gap: .7rem;
        }

        .mini-upload-label {
            font-size: .82rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: var(--muted-foreground);
            text-align: center;
        }

        .preview-mini {
            min-height: 56px;
            border-radius: 1rem;
            border: 1px solid var(--border);
            display: grid;
            place-items: center;
            overflow: hidden;
            background: var(--muted);
        }

        .preview-mini img {
            max-width: 100%;
            max-height: 56px;
            object-fit: contain;
        }

        .sig-grid {
            display: grid;
            gap: 1rem;
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .sig-card {
            display: grid;
            gap: .8rem;
            padding: 1rem;
            border-radius: 1.25rem;
            border: 1px solid var(--border);
            background: var(--card);
        }

        .sig-card-label {
            font-weight: 700;
        }

        .sig-preview {
            min-height: 170px;
            border-radius: 1rem;
            border: 1px dashed var(--border);
            background: var(--muted);
            display: grid;
            place-items: center;
            position: relative;
            overflow: hidden;
            cursor: pointer;
        }

        .sig-preview i {
            font-size: 1.2rem;
            color: var(--primary);
        }

        .sig-preview img {
            display: none;
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .sig-upload-btn {
            justify-self: start;
        }

        .rp-alert {
            padding: .9rem 1rem;
            border-radius: 1rem;
            border: 1px solid transparent;
        }

        .rp-alert.is-success {
            color: #166534;
            border-color: rgba(34, 197, 94, .22);
            background: rgba(34, 197, 94, .1);
        }

        .rp-alert.is-error {
            color: #991b1b;
            border-color: rgba(239, 68, 68, .22);
            background: rgba(239, 68, 68, .1);
        }

        .d-none {
            display: none;
        }

        @media (max-width: 1100px) {
            .upload-layout,
            .sig-grid,
            .upload-grid-3,
            .form-grid {
                grid-template-columns: 1fr;
            }

            .form-field-wide {
                grid-column: auto;
            }
        }

        @media (max-width: 760px) {
            .toggle-row {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
@endpush
