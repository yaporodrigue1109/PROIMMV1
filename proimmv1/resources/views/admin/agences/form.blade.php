
@extends('admin.layouts.app')

@php
    $isEdit = $mode === 'edit';
    $pageTitle = $isEdit ? 'Modifier une agence' : 'Ajouter une agence';
    $fieldValue = fn ($field, $default = '') => old($field, $agence[$field] ?? $default);
@endphp

@section('title', $pageTitle)
@section('header_title', 'Agences')

@section('content')
    <section class="space-y-6 px-4 py-6 md:px-6 xl:px-8">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Agence</p>
                    <h2 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900">{{ $pageTitle }}</h2>
                </div>

                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('admin.agences.index') }}" class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-medium text-slate-900 transition hover:bg-slate-50">Retour</a>
                    @if($isEdit)
                        <a href="{{ route('admin.agences.show', $agence['code_agence']) }}" class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-medium text-slate-900 transition hover:bg-slate-50">Voir la fiche</a>
                    @endif
                </div>
            </div>
        </div>

        <form class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_320px]" action="{{$isEdit ? route('admin.agences.update',['agence' =>$fieldValue('agence_id')]) :route('admin.agences.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <div class="space-y-6">
                <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="card-header">
                        <div>
                            <span class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Identité</span>
                            <h3 class="mt-1 text-lg font-semibold text-slate-900">Informations de l'agence</h3>
                        </div>
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-sm font-semibold text-slate-700">01</span>
                    </div>

                    <div class="form-grid">
                        <label class="form-field form-field-wide" for="name">
                            <span>Nom de l'agence <span class="required">*</span></span>
                            <input id="name" name="name" type="text" value="{{ $fieldValue('name') }}" placeholder="Pros Immobilier Cocody" required>
                            @error('name') <small class="error">{{ $message }}</small> @enderror
                        </label>

                        <label class="form-field form-field-wide" for="adresse">
                            <span>Adresse</span>
                            <textarea id="adresse" name="adresse" rows="3" placeholder="Adresse complète de l'agence">{{ $fieldValue('adresse') }}</textarea>
                            @error('adresse') <small class="error">{{ $message }}</small> @enderror
                        </label>

                        <label class="form-field" for="tel1">
                            <span>Téléphone 1</span>
                            <input id="tel1" name="tel1" type="tel" value="{{ $fieldValue('tel1') }}" placeholder="xxxxxxxxxx">
                            @error('tel1') <small class="error">{{ $message }}</small> @enderror
                        </label>

                        <label class="form-field" for="tel2">
                            <span>Téléphone 2</span>
                            <input id="tel2" name="tel2" type="tel" value="{{ $fieldValue('tel2') }}" placeholder="xxxxxxxxxx">
                            @error('tel2') <small class="error">{{ $message }}</small> @enderror
                        </label>

                        <label class="form-field" for="email1">
                            <span>Email principal</span>
                            <input id="email1" name="email1" type="email" value="{{ $fieldValue('email1') }}" placeholder="contact@agence.com">
                            @error('email1') <small class="error">{{ $message }}</small> @enderror
                        </label>

                        <label class="form-field" for="email2">
                            <span>Email secondaire</span>
                            <input id="email2" name="email2" type="email" value="{{ $fieldValue('email2') }}" placeholder="comptabilite@agence.com">
                            @error('email2') <small class="error">{{ $message }}</small> @enderror
                        </label>

                        @php
                            $currentStatut = $fieldValue('statut', 'en_demo');
                            $statutsAgence = [
                                'active' => 'Active',
                                'en_demo' => 'En demo',
                            ];
                        @endphp

                        <label class="form-field" for="statut">
                            <span>Statut de l'agence</span>

                            <x-ui.select
                                    name="statut"
                                    :options="[
                                        'active' => 'Active',
                                        'en_demo' => 'En demo'
                                    ]"
                                    :value="old('statut', $agence['statut'] ?? 'en_demo')"
                            />

                            @error('statut') <small class="error">{{ $message }}</small> @enderror
                        </label>
                    </div>
                </article>

                <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="card-header">
                        <div>
                            <span class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Localisation</span>
                            <h3 class="mt-1 text-lg font-semibold text-slate-900">Géolocalisation</h3>
                        </div>
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-sm font-semibold text-slate-700">02</span>
                    </div>

                    <div class="form-grid">
                        <label class="form-field" for="region_id">
                            <span>Région</span>



                            <select name="region" class="form-control mb-3" required
                                    onchange="getRequest('{{ url('/') }}/admin/list/city?parent_id='+this.value,'ville_id','select',this.value)"
                            >
                                <option value="">Sélectionnez votre région</option>

                                @foreach($regions as $item)
                                    <option {{ ($fieldValue('region_id')) == $item->id ? 'selected' : '' }} value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach




                            </select>

                            @error('region_id') <small class="error">{{ $message }}</small> @enderror
                        </label>

                        <label class="form-field" for="ville_id">
                            <span>Ville</span>

                            <select name="ville_id" class="form-control mb-3" id="ville_id" required>
                                <option value="">Sélectionnez votre ville</option>
                                @if($villes!=[])
                                    @foreach($villes->where('region_id',$fieldValue('ville_id')) as $item)

                                        <option {{ ($fieldValue('ville_id')) == $item->id ? 'selected' : '' }} value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                @endif

                            </select>

                            @error('ville_id') <small class="error">{{ $message }}</small> @enderror
                        </label>
                    </div>
                </article>

                <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="card-header">
                        <div>
                            <span class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Responsabilité</span>
                            <h3 class="mt-1 text-lg font-semibold text-slate-900">Responsable et statut</h3>
                        </div>
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-sm font-semibold text-slate-700">03</span>
                    </div>

                    <div class="form-grid">
                        {{-- Option: Choisir un responsable existant OU en créer un nouveau --}}
                        <div class="form-field form-field-wide">
                            <span>Mode de sélection du responsable</span>
                            <div class="radio-group radio-group-inline">
                                <label class="option-item option-inline">
                                    <input type="radio" name="responsable_mode" value="existing" {{ old('responsable_mode', $responsable_mode ?? 'existing') === 'existing' ? 'checked' : '' }} onchange="toggleResponsableMode()">
                                    <span>Sélectionner un responsable existant</span>
                                </label>
                                <label class="option-item option-inline">
                                    <input type="radio" name="responsable_mode" value="new" {{ old('responsable_mode') === 'new' ? 'checked' : '' }} onchange="toggleResponsableMode()">
                                    <span>Créer un nouveau responsable</span>
                                </label>
                            </div>
                        </div>

                        {{-- Section: Responsable existant --}}
                        <div id="existing-responsable-section" class="form-field form-field-wide">
                            <span>Responsable existant</span>

                            <x-ui.select
                                    name="responsable_id"
                                    :options="collect($responsables ?? [])->mapWithKeys(fn($r) => [
            ($r['id_users'] ?? $r['id']) => ($r['name'] ?? $r['nom']) . ' - ' . ($r['email'] ?? ''). ' ===> '.( $r['agence']['name'] ?? '')
        ])->toArray()"
                                    :value="$fieldValue('responsable_id')"
                                    placeholder="Sélectionner un responsable"
                            />

                            @error('responsable_id') <small class="error">{{ $message }}</small> @enderror
                        </div>

                        {{-- Section: Nouveau responsable --}}
                        <div id="new-responsable-section" class="form-field form-field-wide section-hidden">
                            <div class="card-header card-header-offset">
                                <div>
                                    <span class="eyebrow">Nouveau responsable</span>
                                    <h3>Informations du responsable</h3>
                                </div>
                            </div>
                            <div class="form-grid form-grid-offset">
                                <label class="form-field form-field-wide" for="new_responsable_name">
                                    <span>Nom complet <span class="required">*</span></span>
                                    <input id="new_responsable_name" name="new_responsable_name" type="text" value="{{ old('new_responsable_name') }}" placeholder="Jean Kouassi">
                                    @error('new_responsable_name') <small class="error">{{ $message }}</small> @enderror
                                </label>

                                <label class="form-field" for="new_responsable_email">
                                    <span>Email <span class="required">*</span></span>
                                    <input id="new_responsable_email" name="new_responsable_email" type="email" value="{{ old('new_responsable_email') }}" placeholder="responsable@agence.com">
                                    @error('new_responsable_email') <small class="error">{{ $message }}</small> @enderror
                                </label>

                                <label class="form-field" for="new_responsable_password">
                                    <span>Mot de passe <span class="required">*</span></span>
                                    <input id="new_responsable_password" name="new_responsable_password" type="password" placeholder="********">
                                    @error('new_responsable_password') <small class="error">{{ $message }}</small> @enderror
                                </label>

                                <label class="form-field" for="new_responsable_password_confirmation">
                                    <span>Confirmer le mot de passe</span>
                                    <input id="new_responsable_password_confirmation" name="new_responsable_password_confirmation" type="password" placeholder="********">
                                </label>

                                <label class="form-field" for="new_responsable_tel1">
                                    <span>Téléphone principal</span>
                                    <input id="new_responsable_tel1" name="new_responsable_tel1" type="tel" value="{{ old('new_responsable_tel1') }}" placeholder="0700000000">
                                    @error('new_responsable_tel1') <small class="error">{{ $message }}</small> @enderror
                                </label>

                                <label class="form-field" for="new_responsable_tel2">
                                    <span>Téléphone secondaire</span>
                                    <input id="new_responsable_tel2" name="new_responsable_tel2" type="tel" value="{{ old('new_responsable_tel2') }}" placeholder="0700000001">
                                    @error('new_responsable_tel2') <small class="error">{{ $message }}</small> @enderror
                                </label>

                                <label class="form-field form-field-wide" for="new_responsable_adresse">
                                    <span>Adresse</span>
                                    <textarea id="new_responsable_adresse" name="new_responsable_adresse" rows="2" placeholder="Adresse du responsable">{{ old('new_responsable_adresse') }}</textarea>
                                    @error('new_responsable_adresse') <small class="error">{{ $message }}</small> @enderror
                                </label>

                                <label class="form-field" for="new_responsable_photo">
                                    <span>Photo</span>
                                    <input id="new_responsable_photo" name="new_responsable_photo" type="file" accept="image/*">
                                    @error('new_responsable_photo') <small class="error">{{ $message }}</small> @enderror
                                </label>
                            </div>
                        </div>
                    </div>
                </article>

                <article id="abonnement-section" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm {{ $fieldValue('statut', 'en_demo') === 'active' ? '' : 'section-hidden' }}">
                    <div class="card-header">
                        <div>
                            <span class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Abonnement</span>
                            <h3 class="mt-1 text-lg font-semibold text-slate-900">Configuration abonnement</h3>
                        </div>
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-sm font-semibold text-slate-700">04</span>
                    </div>
                    <input type="hidden" name="abonnement_id" value="{{$tarifications['id']}}">
                    <div class="form-grid">
                        {{-- Prix de base fixe --}}
                        <div class="form-field form-field-wide summary-note">
                            <div class="u-flex-between">
                                <span class="agency-icon-label u-text-semibold">
                                    <svg class="agency-inline-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m3-9.75C14.45 7.52 13.4 7 12 7c-1.93 0-3.5 1.01-3.5 2.25 0 1.4 1.3 2.04 3.5 2.25 2.2.21 3.5.85 3.5 2.25C15.5 14.99 13.93 16 12 16c-1.4 0-2.45-.52-3-1.25"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    {{$tarifications['plan_nom']}}
                                </span>
                                <span class="plan-price">{{$tarifications['plan_prix_mensuel'] ?? ''}} FCFA / mois</span>
                            </div>
                            <small class="summary-note-text"> {{$tarifications['plan_description'] ?? ''}} </small>
                            <small class="summary-note-text">Ce montant est multiplié par le nombre de mois d'abonnement</small>
                            <input type="hidden" name="prix_base_mensuel" id="prix_base_mensuel" value="{{$tarifications['plan_prix_mensuel'] ?? 0}}">
                        </div>

                        <label class="form-field" for="abonnement_start">
                            <span>Date début abonnement</span>
                            <x-ui.date-picker
                                    name="abonnement_start"
                                    :value="$fieldValue('abonnement_start')"
                                    placeholder="Sélectionner la date de début"
                                    onchange="calculerDateFin()"
                            />
                            @error('abonnement_start') <small class="error">{{ $message }}</small> @enderror
                        </label>

                        <label class="form-field" for="abonnement_end">
                            <span>Date fin abonnement</span>
                            <x-ui.date-picker
                                    name="abonnement_end"
                                    :value="$fieldValue('abonnement_end')"
                                    placeholder="Date calculée automatiquement"
                                    readonly
                            />
                            @error('abonnement_end') <small class="error">{{ $message }}</small> @enderror
                        </label>

                        <label class="form-field" for="duree_mois">
                            <span>Durée d'abonnement (mois)</span>

                            <x-ui.select
                                    name="duree_mois"
                                    :options="collect($tarifications['durees'])->mapWithKeys(fn($duree) => [
            $duree => match($duree) {
                1 => '1 mois',
                3 => '3 mois',
                6 => '6 mois',
                12 => '12 mois (1 an)',
                24 => '24 mois (2 ans)',
                36 => '36 mois (3 ans)',
                default => $duree . ' mois'
            }
        ])->toArray()"
                                    :value="$fieldValue('duree_mois', 12)"
                                    onchange="calculerDateFin(); calculerMontantTotal()"
                            />

                            @error('duree_mois') <small class="error">{{ $message }}</small> @enderror
                        </label>
                    </div>

                    {{-- Options supplémentaires --}}
                    <div class="section-offset">
                        <h4 class="agency-section-title section-title-offset">
                            <svg class="agency-inline-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25l-9-4.5-9 4.5m18 0l-9 4.5m9-4.5v7.5l-9 4.5m0-7.5l-9-4.5m9 4.5v7.5m-9-12v7.5l9 4.5"/>
                            </svg>
                            Modules et options additionnels
                        </h4>
                        <p class="section-help">Les prix sont mensuels et seront multipliés par la durée sélectionnée</p>
                        <div class="option-list module-options-grid">
                            @php
                                $selectedOptions = old('options', json_decode($fieldValue('options', '[]'), true) ?? []);
                            @endphp
                            @foreach($tarifications['modules'] as $module)
                                <label class="option-item module-option-card">
                                    <input type="checkbox"
                                           name="options[]"
                                           value="{{ $module['id'] }}"
                                           data-prix-mensuel="{{ $module['prix_mensuel'] }}"
                                           onchange="calculerMontantTotal()"
                                            @checked(in_array($module['id'], $selectedOptions))>
                                    <span class="module-label">{{ $module['label'] }}</span>
                                    <span class="module-price">
                                        {{ number_format($module['prix_mensuel'], 0, ',', ' ') }} FCFA <span class="module-price-unit">/mois</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Récapitulatif des montants --}}
                    <div class="billing-summary">
                        <h4 class="agency-section-title section-title-offset">
                            <svg class="agency-inline-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5m-17.25-3h15a2.25 2.25 0 012.25 2.25v9a2.25 2.25 0 01-2.25 2.25h-15A2.25 2.25 0 012.25 16.5v-9A2.25 2.25 0 014.5 5.25z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 15.75h3"/>
                            </svg>
                            Récapitulatif financier
                        </h4>
                        <div class="billing-row-compact">
                            <span class="agency-icon-label">
                                <svg class="agency-inline-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3.75 8.25h16.5M5.25 5.25h13.5A1.5 1.5 0 0120.25 6.75v12A1.5 1.5 0 0118.75 20.25H5.25A1.5 1.5 0 013.75 18.75v-12A1.5 1.5 0 015.25 5.25z"/>
                                </svg>
                                Durée sélectionnée
                            </span>
                            <span id="duree_affichee" class="u-text-semibold">12 mois</span>
                        </div>
                        <div class="billing-row billing-row-divider">
                            <span class="agency-icon-label">
                                <svg class="agency-inline-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Prix de base mensuel (formule standard)
                            </span>
                            <span class="u-text-semibold">{{ number_format($tarifications['plan_prix_mensuel'], 0, ',', ' ') }} FCFA × <span id="duree_multiplicateur">12</span> mois</span>
                        </div>
                        <div class="billing-row">
                            <span class="agency-icon-label">
                                <svg class="agency-inline-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m3-9.75C14.45 7.52 13.4 7 12 7c-1.93 0-3.5 1.01-3.5 2.25 0 1.4 1.3 2.04 3.5 2.25 2.2.21 3.5.85 3.5 2.25C15.5 14.99 13.93 16 12 16c-1.4 0-2.45-.52-3-1.25"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Prix de base total
                            </span>
                            <span id="montant_base_display" class="u-text-semibold">0 FCFA</span>
                        </div>
                        <div class="billing-row billing-row-divider">
                            <span class="agency-icon-label">
                                <svg class="agency-inline-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 5.25v13.5M5.25 12h13.5"/>
                                </svg>
                                Options sélectionnées
                            </span>
                            <span id="total_options_display" class="u-text-semibold">0 FCFA</span>
                        </div>
                        <div class="billing-row-total">
                            <span class="agency-icon-label">
                                <svg class="agency-inline-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m3-9.75C14.45 7.52 13.4 7 12 7c-1.93 0-3.5 1.01-3.5 2.25 0 1.4 1.3 2.04 3.5 2.25 2.2.21 3.5.85 3.5 2.25C15.5 14.99 13.93 16 12 16c-1.4 0-2.45-.52-3-1.25"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Montant total à payer
                            </span>
                            <span id="montant_total_display" class="billing-total">0 FCFA</span>
                        </div>
                        <div class="billing-hint">
                            * TVA non applicable, article 297 bis du CGI
                        </div>
                        <input type="hidden" name="montant_total" id="montant_total_input" value="{{ old('montant_total', $fieldValue('montant_total', 0)) }}">
                        <input type="hidden" name="montant_base_total" id="montant_base_total_input" value="0">
                        <input type="hidden" name="duree_mois_selected" id="duree_mois_selected" value="12">
                    </div>
                </article>

            </div>

            <aside class="space-y-6 xl:sticky xl:top-6 xl:self-start">
                <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
{{--                    <span class="eyebrow">Validation</span>--}}
{{--                    <h3>Documents et logos</h3>--}}

{{--                    <label class="form-field" for="logo">--}}
{{--                        <span>Logo de l'agence</span>--}}
{{--                        <input id="logo" name="logo" type="file" accept="image/*">--}}
{{--                        @if($isEdit && !empty($agence['logo']))--}}
{{--                            <small>Fichier actuel : {{ basename($agence['logo']) }}</small>--}}
{{--                        @endif--}}
{{--                        @error('logo') <small class="error">{{ $message }}</small> @enderror--}}
{{--                    </label>--}}

{{--                    <label class="form-field" for="signature_responsable">--}}
{{--                        <span>Signature responsable</span>--}}
{{--                        <input id="signature_responsable" name="signature_responsable" type="file" accept="image/*">--}}
{{--                        @if($isEdit && !empty($agence['signature_responsable']))--}}
{{--                            <small>Fichier actuel : {{ basename($agence['signature_responsable']) }}</small>--}}
{{--                        @endif--}}
{{--                        @error('signature_responsable') <small class="error">{{ $message }}</small> @enderror--}}
{{--                    </label>--}}

{{--                    <label class="form-field" for="signature_comptabilite">--}}
{{--                        <span>Signature comptabilité</span>--}}
{{--                        <input id="signature_comptabilite" name="signature_comptabilite" type="file" accept="image/*">--}}
{{--                        @if($isEdit && !empty($agence['signature_comptabilite']))--}}
{{--                            <small>Fichier actuel : {{ basename($agence['signature_comptabilite']) }}</small>--}}
{{--                        @endif--}}
{{--                        @error('signature_comptabilite') <small class="error">{{ $message }}</small> @enderror--}}
{{--                    </label>--}}

{{--                    <label class="form-field" for="signature_marketing">--}}
{{--                        <span>Signature marketing</span>--}}
{{--                        <input id="signature_marketing" name="signature_marketing" type="file" accept="image/*">--}}
{{--                        @if($isEdit && !empty($agence['signature_marketing']))--}}
{{--                            <small>Fichier actuel : {{ basename($agence['signature_marketing']) }}</small>--}}
{{--                        @endif--}}
{{--                        @error('signature_marketing') <small class="error">{{ $message }}</small> @enderror--}}
{{--                    </label>--}}

                    <button class="inline-flex h-11 w-full items-center justify-center rounded-xl bg-[#00559b] px-4 text-sm font-medium text-white transition hover:bg-[#004980]" type="submit">
                        {{ $isEdit ? 'Enregistrer les modifications' : "Créer l'agence" }}
                    </button>
                </article>
            </aside>
        </form>
    </section>
@endsection

