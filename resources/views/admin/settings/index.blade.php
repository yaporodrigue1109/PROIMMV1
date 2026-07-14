@extends('admin.layouts.app')

@section('title', 'Configuration')
@section('header_title', 'Configuration')

@section('content')
    <section class="page">

        <div class="tabs card">
            <div class="tabs-list tabs-list-grid" role="tablist" aria-label="Sections de configuration">
                <button class="tab-btn active" type="button" data-tab="entreprise" role="tab" aria-controls="tab-entreprise">
                    Configuration de l'entreprise
                </button>
                <button class="tab-btn" type="button" data-tab="tarifaire" role="tab" aria-controls="tab-tarifaire">
                    Configuration tarifaire
                </button>
            </div>
        </div>

        {{-- TAB ENTREPRISE --}}
        <div class="tab-content active" id="tab-entreprise" role="tabpanel">
            <form class="form-layout form-panel" action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="form-main">
                    {{-- IDENTITE GENERALE --}}
                    <article class="card">
                        <div class="card-header">
                            <div>
                                <span class="eyebrow">Entreprise</span>
                                <h3>Identité générale</h3>
                            </div>
                            <span class="step-marker">01</span>
                        </div>

                        <div class="form-grid">
                            <label class="form-field" for="name">
                                <span>Nom commercial</span>
                                <input
                                        id="name"
                                        name="name"
                                        type="text"
                                        value="{{ old('name', $setting->name ?? '') }}"
                                        class="@error('name') is-invalid @enderror"
                                >
                                @error('name')
                                <span class="error-message">{{ $message }}</span>
                                @enderror
                            </label>

                            <label class="form-field" for="raison_social">
                                <span>Raison sociale</span>
                                <input
                                        id="raison_social"
                                        name="raison_social"
                                        type="text"
                                        value="{{ old('raison_social', $setting->raison_social ?? '') }}"
                                        class="@error('raison_social') is-invalid @enderror"
                                >
                                @error('raison_social')
                                <span class="error-message">{{ $message }}</span>
                                @enderror
                            </label>

                            <label class="form-field" for="email1">
                                <span>Email principal</span>
                                <input
                                        id="email1"
                                        name="email1"
                                        type="email"
                                        value="{{ old('email1', $setting->email1 ?? '') }}"
                                        class="@error('email1') is-invalid @enderror"
                                >
                                @error('email1')
                                <span class="error-message">{{ $message }}</span>
                                @enderror
                            </label>

                            <label class="form-field" for="email2">
                                <span>Email secondaire</span>
                                <input
                                        id="email2"
                                        name="email2"
                                        type="email"
                                        value="{{ old('email2', $setting->email2 ?? '') }}"
                                        class="@error('email2') is-invalid @enderror"
                                >
                                @error('email2')
                                <span class="error-message">{{ $message }}</span>
                                @enderror
                            </label>

                            <label class="form-field" for="contact1">
                                <span>Téléphone principal</span>
                                <input
                                        id="contact1"
                                        name="contact1"
                                        type="tel"
                                        value="{{ old('contact1', $setting->contact1 ?? '') }}"
                                        class="@error('contact1') is-invalid @enderror"
                                >
                                @error('contact1')
                                <span class="error-message">{{ $message }}</span>
                                @enderror
                            </label>

                            <label class="form-field" for="contact2">
                                <span>Téléphone secondaire</span>
                                <input
                                        id="contact2"
                                        name="contact2"
                                        type="tel"
                                        value="{{ old('contact2', $setting->contact2 ?? '') }}"
                                        class="@error('contact2') is-invalid @enderror"
                                >
                                @error('contact2')
                                <span class="error-message">{{ $message }}</span>
                                @enderror
                            </label>

                            <label class="form-field" for="contact3">
                                <span>Téléphone tertiaire</span>
                                <input
                                        id="contact3"
                                        name="contact3"
                                        type="tel"
                                        value="{{ old('contact3', $setting->contact3 ?? '') }}"
                                        class="@error('contact3') is-invalid @enderror"
                                >
                                @error('contact3')
                                <span class="error-message">{{ $message }}</span>
                                @enderror
                            </label>

                            <label class="form-field form-field-wide" for="adresse">
                                <span>Adresse</span>
                                <textarea
                                        id="adresse"
                                        name="adresse"
                                        rows="3"
                                        class="@error('adresse') is-invalid @enderror"
                                >{{ old('adresse', $setting->adresse ?? '') }}</textarea>
                                @error('adresse')
                                <span class="error-message">{{ $message }}</span>
                                @enderror
                            </label>

                            <label class="form-field form-field-wide" for="boite_postal">
                                <span>Boîte postale</span>
                                <input
                                        id="boite_postal"
                                        name="boite_postal"
                                        type="text"
                                        value="{{ old('boite_postal', $setting->boite_postal ?? '') }}"
                                        class="@error('boite_postal') is-invalid @enderror"
                                >
                                @error('boite_postal')
                                <span class="error-message">{{ $message }}</span>
                                @enderror
                            </label>
                        </div>
                    </article>

                    {{-- LOCALISATION ET AFFICHAGE --}}
                    <article class="card">
                        <div class="card-header">
                            <div>
                                <span class="eyebrow">Préférences</span>
                                <h3>Localisation et affichage</h3>
                            </div>
                            <span class="step-marker">02</span>
                        </div>

                        <div class="form-grid">
                            <label class="form-field" for="site_web">
                                <span>Site web</span>
                                <input
                                        id="site_web"
                                        name="site_web"
                                        type="url"
                                        value="{{ old('site_web', $setting->site_web ?? '') }}"
                                        class="@error('site_web') is-invalid @enderror"
                                >
                                @error('site_web')
                                <span class="error-message">{{ $message }}</span>
                                @enderror
                            </label>

                            <label class="form-field" for="langue">
                                <span>Langue par défaut</span>
                                <x-ui.select
                                        id="langue"
                                        name="langue"
                                        :options="['fr' => 'Français', 'en' => 'Anglais']"
                                        :value="old('langue', $setting->langue ?? 'fr')"
                                />
                                @error('langue')
                                <span class="error-message">{{ $message }}</span>
                                @enderror
                            </label>
                        </div>
                    </article>

                    {{-- INFORMATIONS LEGALES --}}
                    <article class="card">
                        <div class="card-header">
                            <div>
                                <span class="eyebrow">Informations légales</span>
                                <h3>Numéros d'enregistrement</h3>
                            </div>
                            <span class="step-marker">03</span>
                        </div>

                        <div class="form-grid">
                            <label class="form-field" for="num_rccm">
                                <span>Numéro RCCM</span>
                                <input
                                        id="num_rccm"
                                        name="num_rccm"
                                        type="text"
                                        value="{{ old('num_rccm', $setting->num_rccm ?? '') }}"
                                        class="@error('num_rccm') is-invalid @enderror"
                                >
                                @error('num_rccm')
                                <span class="error-message">{{ $message }}</span>
                                @enderror
                            </label>

                            <label class="form-field" for="num_cc">
                                <span>Numéro de compte courant</span>
                                <input
                                        id="num_cc"
                                        name="num_cc"
                                        type="text"
                                        value="{{ old('num_cc', $setting->num_cc ?? '') }}"
                                        class="@error('num_cc') is-invalid @enderror"
                                >
                                @error('num_cc')
                                <span class="error-message">{{ $message }}</span>
                                @enderror
                            </label>

                            <label class="form-field" for="num_cnps">
                                <span>Numéro CNPS</span>
                                <input
                                        id="num_cnps"
                                        name="num_cnps"
                                        type="text"
                                        value="{{ old('num_cnps', $setting->num_cnps ?? '') }}"
                                        class="@error('num_cnps') is-invalid @enderror"
                                >
                                @error('num_cnps')
                                <span class="error-message">{{ $message }}</span>
                                @enderror
                            </label>

                            <label class="form-field" for="capital">
                                <span>Capital social</span>
                                <input
                                        id="capital"
                                        name="capital"
                                        type="number"
                                        min="0"
                                        value="{{ old('capital', $setting->capital ?? '') }}"
                                        step="1000"
                                        class="@error('capital') is-invalid @enderror"
                                >
                                @error('capital')
                                <span class="error-message">{{ $message }}</span>
                                @enderror
                            </label>
                        </div>
                    </article>

                    {{-- POLITIQUES ET CONDITIONS --}}
                    <article class="card">
                        <div class="card-header">
                            <div>
                                <span class="eyebrow">Confidentialité</span>
                                <h3>Règles et politique</h3>
                            </div>
                            <span class="step-marker">04</span>
                        </div>

                        <div class="form-grid">
                            <div class="form-field form-field-wide">
                                <span>Politique de confidentialité</span>
                                <input type="hidden" id="politique_confidentialite" name="politique_confidentialite" value="{{ old('politique_confidentialite', $setting->politique_confidentialite ?? '') }}">
                                <div class="wysiwyg" data-editor-target="politique_confidentialite">
                                    <div class="wysiwyg-toolbar">
                                        <button type="button" data-command="bold"><strong>B</strong></button>
                                        <button type="button" data-command="italic"><em>I</em></button>
                                        <button type="button" data-command="insertUnorderedList">Liste</button>
                                        <button type="button" data-command="formatBlock" data-value="p">P</button>
                                    </div>
                                    <div class="wysiwyg-editor" contenteditable="true">{!! old('politique_confidentialite', $setting->politique_confidentialite ?? '') !!}</div>
                                </div>
                                @error('politique_confidentialite')
                                <span class="error-message">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-field form-field-wide">
                                <span>Conditions générales</span>
                                <input type="hidden" id="condition_generale" name="condition_generale" value="{{ old('condition_generale', $setting->condition_generale ?? '') }}">
                                <div class="wysiwyg" data-editor-target="condition_generale">
                                    <div class="wysiwyg-toolbar">
                                        <button type="button" data-command="bold"><strong>B</strong></button>
                                        <button type="button" data-command="italic"><em>I</em></button>
                                        <button type="button" data-command="insertUnorderedList">Liste</button>
                                        <button type="button" data-command="formatBlock" data-value="p">P</button>
                                    </div>
                                    <div class="wysiwyg-editor" contenteditable="true">{!! old('condition_generale', $setting->condition_generale ?? '') !!}</div>
                                </div>
                                @error('condition_generale')
                                <span class="error-message">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-field form-field-wide">
                                <span>CGU (Conditions d'utilisation)</span>
                                <input type="hidden" id="cgu" name="cgu" value="{{ old('cgu', $setting->cgu ?? '') }}">
                                <div class="wysiwyg" data-editor-target="cgu">
                                    <div class="wysiwyg-toolbar">
                                        <button type="button" data-command="bold"><strong>B</strong></button>
                                        <button type="button" data-command="italic"><em>I</em></button>
                                        <button type="button" data-command="insertUnorderedList">Liste</button>
                                        <button type="button" data-command="formatBlock" data-value="p">P</button>
                                    </div>
                                    <div class="wysiwyg-editor" contenteditable="true">{!! old('cgu', $setting->cgu ?? '') !!}</div>
                                </div>
                                @error('cgu')
                                <span class="error-message">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </article>

                    {{-- RESEAUX SOCIAUX --}}
                    <article class="card">
                        <div class="card-header">
                            <div>
                                <span class="eyebrow">Réseaux sociaux</span>
                                <h3>Liens vers les réseaux sociaux</h3>
                            </div>
                            <span class="step-marker">05</span>
                        </div>

                        <div class="form-grid">
                            <label class="form-field" for="facebook">
                                <span>Facebook</span>
                                <input
                                        id="facebook"
                                        name="facebook"
                                        type="url"
                                        value="{{ old('facebook', $setting->facebook ?? '') }}"
                                        placeholder="https://facebook.com/..."
                                        class="@error('facebook') is-invalid @enderror"
                                >
                                @error('facebook')
                                <span class="error-message">{{ $message }}</span>
                                @enderror
                            </label>

                            <label class="form-field" for="instagram">
                                <span>Instagram</span>
                                <input
                                        id="instagram"
                                        name="instagram"
                                        type="url"
                                        value="{{ old('instagram', $setting->instagram ?? '') }}"
                                        placeholder="https://instagram.com/..."
                                        class="@error('instagram') is-invalid @enderror"
                                >
                                @error('instagram')
                                <span class="error-message">{{ $message }}</span>
                                @enderror
                            </label>

                            <label class="form-field" for="linkedin">
                                <span>LinkedIn</span>
                                <input
                                        id="linkedin"
                                        name="linkedin"
                                        type="url"
                                        value="{{ old('linkedin', $setting->linkedin ?? '') }}"
                                        placeholder="https://linkedin.com/..."
                                        class="@error('linkedin') is-invalid @enderror"
                                >
                                @error('linkedin')
                                <span class="error-message">{{ $message }}</span>
                                @enderror
                            </label>

                            <label class="form-field" for="twitter">
                                <span>Twitter</span>
                                <input
                                        id="twitter"
                                        name="twitter"
                                        type="url"
                                        value="{{ old('twitter', $setting->twitter ?? '') }}"
                                        placeholder="https://twitter.com/..."
                                        class="@error('twitter') is-invalid @enderror"
                                >
                                @error('twitter')
                                <span class="error-message">{{ $message }}</span>
                                @enderror
                            </label>

                            <label class="form-field" for="google">
                                <span>Google Business</span>
                                <input
                                        id="google"
                                        name="google"
                                        type="url"
                                        value="{{ old('google', $setting->google ?? '') }}"
                                        placeholder="https://business.google.com/..."
                                        class="@error('google') is-invalid @enderror"
                                >
                                @error('google')
                                <span class="error-message">{{ $message }}</span>
                                @enderror
                            </label>
                        </div>
                    </article>
                </div>

                {{-- MEDIAS SIDEBAR --}}
                <aside class="card summary-panel">
                    <span class="eyebrow">Médias</span>
                    <h3>Identité visuelle</h3>

                    @if($setting->logo)
                        <div class="media-preview">
                            <img src="{{ asset('storage/' . $setting->logo) }}" alt="Logo actuel" class="u-image-logo">
                        </div>
                    @endif

                    <label class="form-field" for="logo">
                        <span>Logo</span>
                        <input
                                id="logo"
                                name="logo"
                                type="file"
                                accept="image/*"
                                class="@error('logo') is-invalid @enderror"
                        >
                        <small>Formats acceptés: JPG, PNG, GIF, WebP (Max: 2MB)</small>
                        @error('logo')
                        <span class="error-message">{{ $message }}</span>
                        @enderror
                    </label>

                    @if($setting->flavicon)
                        <div class="media-preview media-preview-compact">
                            <img src="{{ asset('storage/' . $setting->flavicon) }}" alt="Favicon actuel" class="u-image-favicon">
                        </div>
                    @endif

                    <label class="form-field" for="flavicon">
                        <span>Favicon</span>
                        <input
                                id="flavicon"
                                name="flavicon"
                                type="file"
                                accept="image/*,.ico"
                                class="@error('flavicon') is-invalid @enderror"
                        >
                        <small>Formats acceptés: ICO, PNG (Max: 1MB)</small>
                        @error('flavicon')
                        <span class="error-message">{{ $message }}</span>
                        @enderror
                    </label>

                    <button class="btn btn-primary form-submit" type="submit">Enregistrer l'entreprise</button>
                </aside>
            </form>
        </div>

        <div class="tab-content" id="tab-tarifaire" role="tabpanel">
            <form class="form-layout form-panel" action="{{ route('admin.settings.update_tarification') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-main">
                    {{-- PLAN --}}
                    <article class="card">
                        <div class="card-header">
                            <div>
                                <span class="eyebrow">Plan unique</span>
                                <h3>Abonnement de base</h3>
                            </div>
                            <span class="step-marker">01</span>
                        </div>

                        <div class="form-grid">
                            <label class="form-field" for="plan_nom">
                                <span>Nom du plan</span>
                                <input id="plan_nom" name="plan_nom" type="text"
                                       value="{{ old('plan_nom', $tarifs['plan_nom']) }}">
                                @error('plan_nom') <span class="text-red-500">{{ $message }}</span> @enderror
                            </label>

                            <label class="form-field" for="plan_prix_mensuel">
                                <span>Prix mensuel de base (FCFA)</span>
                                <input id="plan_prix_mensuel" name="plan_prix_mensuel" type="number" min="0" step="100"
                                       value="{{ old('plan_prix_mensuel', $tarifs['plan_prix_mensuel']) }}">
                                @error('plan_prix_mensuel') <span class="text-red-500">{{ $message }}</span> @enderror
                            </label>

                            <label class="form-field" for="delai_grace">
                                <span>Délai de grâce (jours)</span>
                                <input id="delai_grace" name="delai_grace" type="number" min="0"
                                       value="{{ old('delai_grace', $tarifs['delai_grace']) }}">
                                @error('delai_grace') <span class="text-red-500">{{ $message }}</span> @enderror
                            </label>

                            <label class="form-field" for="cycle_facturation">
                                <span>Cycle par défaut</span>
                                <x-ui.select
                                        name="cycle_facturation"
                                        :options="[
                            'mensuel' => 'Mensuel',
                            'annuel' => 'Annuel',
                        ]"
                                        :value="old('cycle_facturation', $tarifs['cycle_facturation'])"
                                />
                                @error('cycle_facturation') <span class="text-red-500">{{ $message }}</span> @enderror
                            </label>

                            <label class="form-field form-field-wide" for="plan_description">
                                <span>Description du plan</span>
                                <textarea id="plan_description" name="plan_description" rows="3">{{ old('plan_description', $tarifs['plan_description']) }}</textarea>
                                @error('plan_description') <span class="text-red-500">{{ $message }}</span> @enderror
                            </label>
                        </div>
                    </article>

                    {{-- DUREES --}}
                    <article class="card">
                        <div class="card-header">
                            <div>
                                <span class="eyebrow">Durées</span>
                                <h3>Durées d'abonnement disponibles</h3>
                            </div>
                            <span class="step-marker">02</span>
                        </div>

                        <div class="option-grid option-grid-3">
                            @foreach([
                                1 => '1 mois',
                                3 => '3 mois',
                                6 => '6 mois',
                                12 => '12 mois (1 an)',
                                24 => '24 mois (2 ans)',
                                36 => '36 mois (3 ans)'
                            ] as $value => $label)
                                <label class="option-item option-tile">
                                    <input type="checkbox" name="durees[]" value="{{ $value }}"
                                            @checked(in_array($value, old('durees', $tarifs['durees'])))>
                                    <span>{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('durees') <span class="text-red-500">{{ $message }}</span> @enderror
                    </article>

                    {{-- MODULES --}}
                    <article class="card">
                        <div class="card-header">
                            <div>
                                <span class="eyebrow">Modules</span>
                                <h3>Modules complémentaires</h3>
                            </div>
                            <span class="step-marker">03</span>
                        </div>

                        <div class="option-grid option-grid-2">
                            @forelse($tarifs['modules'] as $module)
                                <article class="option-card">
                                    {{-- Utiliser l'ID du module comme clé --}}
                                    <label class="option-item option-toggle">
                                        <input type="checkbox" name="modules[{{ $module['id'] }}][actif]" value="1"
                                                @checked(old("modules.{$module['id']}.actif", $module['actif']))>
                                        <span>Actif</span>
                                    </label>

                                    <label class="form-field" for="module_{{ $module['id'] }}_label">
                                        <span>Nom du module</span>
                                        <input id="module_{{ $module['id'] }}_label"
                                               name="modules[{{ $module['id'] }}][label]"
                                               type="text"
                                               value="{{ old("modules.{$module['id']}.label", $module['label']) }}">
                                    </label>

                                    <label class="form-field" for="module_{{ $module['id'] }}_prix">
                                        <span>Prix mensuel (FCFA)</span>
                                        <input id="module_{{ $module['id'] }}_prix"
                                               name="modules[{{ $module['id'] }}][prix_mensuel]"
                                               type="number" min="0" step="100"
                                               value="{{ old("modules.{$module['id']}.prix_mensuel", $module['prix_mensuel']) }}">
                                    </label>
                                </article>
                            @empty
                                <p>Aucun module disponible</p>
                            @endforelse
                        </div>
                    </article>
                </div>

                {{-- RÉSUMÉ --}}
                <aside class="card summary-panel">
                    <span class="eyebrow">Résumé</span>
                    <h3>Configuration des tarifs</h3>

                    <div class="info-list">
                        <div class="info-item">
                            <span>Plan</span>
                            <strong id="summary-plan">{{ $tarifs['plan_nom'] }}</strong>
                        </div>
                        <div class="info-item">
                            <span>Prix mensuel</span>
                            <strong id="summary-prix">{{ number_format($tarifs['plan_prix_mensuel'], 0, ',', ' ') }} FCFA</strong>
                        </div>
                        <div class="info-item">
                            <span>Durées disponibles</span>
                            <strong id="summary-durees">{{ count($tarifs['durees']) }}</strong>
                        </div>
                        <div class="info-item">
                            <span>Modules actifs</span>
                            <strong id="summary-modules">{{ collect($tarifs['modules'])->where('actif', true)->count() }}</strong>
                        </div>
                    </div>

                    <button class="btn btn-primary form-submit" type="submit">✅ Enregistrer les tarifs</button>
                </aside>
            </form>


        </div>


    </section>
@endsection