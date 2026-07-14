

@extends('agence.layouts.app')

@php
    $isEdit    = $mode === 'edit';
    $pageTitle = $isEdit ? 'Modifier un propriétaire' : 'Nouveau propriétaire';

    //dd($proprietaire);
    // Helper : récupère old() en priorité, puis la valeur du modèle, puis le défaut
   $val = function (string $field, mixed $default = '') use ($isEdit, $proprietaire) {
    if ($isEdit && isset($proprietaire)) {
        return old($field, $proprietaire->$field ?? $default);
    }
    return old($field, $default);
};

    // Valeur dans la liaison agence (ProprietaireAgence)
   $valLiaison = function (string $field, mixed $default = '') use ($isEdit, $proprietaire) {
    if ($isEdit && isset($proprietaire)) {
        $liaison = $proprietaire->agences->first();
        return old($field, $liaison?->$field ?? $default);
    }
    return old($field, $default);
};

  // dd($valLiaison);

    $formAction = $isEdit
        ? route('agence.proprietaire.update', $proprietaire->proprietaire_id)
        : route('agence.proprietaire.store');

    $hasRepresentant = $isEdit && $proprietaire->agences->first()?->name_representant;
@endphp

@section('title', $pageTitle)

@section('content')
    <section class="page">

        <div class="page-hero">
            <div>
                <h2>{{ $pageTitle }}</h2>
            </div>

            <div class="page-actions">
                <a href="{{ route('agence.proprietaire.index') }}" class="btn btn-outline">
                    Retour
                </a>

                @if($isEdit)
                    <a href="{{ route('agence.proprietaire.show', $proprietaire->proprietaire_id) }}"
                       class="btn btn-outline">
                        Voir le détail
                    </a>
                @endif
            </div>
        </div>

        {{-- Erreurs de validation globales --}}
        @if($errors->any())
            <div class="alert alert-danger u-mb-md">
                <ul class="u-mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form class="form-layout" action="{{ $formAction }}" method="POST" enctype="multipart/form-data">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <div class="form-main">

                {{-- ============================================================
                     SECTION 1 – Informations personnelles
                ============================================================ --}}
                <article class="card">
                    <div class="card-header">
                        <div>
                            <span class="eyebrow">Propriétaire</span>
                            <h3>Informations personnelles</h3>
                        </div>
                    </div>

                    <div class="form-grid">
                        <label class="form-field form-field-wide" for="genre_id">
                            <span>Genre</span>
                            <x-ui.select
                                    name="genre_id"
                                    :options="$genres ?? []"
                                    :value="$val('genre_id')"
                            />
                            @error('genre_id')
                            <small class="field-error">{{ $message }}</small>
                            @enderror
                        </label>

                        {{-- Nom --}}

                        <label class="form-field form-field-wide" for="name">
                            <span>Nom et prénom *</span>
                            <x-ui.input
                                    id="name"
                                    name="name"
                                    type="text"
                                    :value="$val('name')"
                                    placeholder="Kouadio Jean"
                                    required
                            />
                            @error('name')
                            <small class="field-error">{{ $message }}</small>
                            @enderror
                        </label>

                        {{-- Adresse --}}
                        <label class="form-field form-field-wide" for="adresse">
                            <span>Adresse</span>
                            <x-ui.textarea
                                    id="adresse"
                                    name="adresse"
                                    :value="$val('adresse')"
                                    rows="3"
                                    placeholder="Cocody, Abidjan"
                            />
                            @error('adresse')
                            <small class="field-error">{{ $message }}</small>
                            @enderror
                        </label>

                        {{-- Email --}}
                        <label class="form-field" for="email">
                            <span>Email</span>
                            <x-ui.input
                                    id="email"
                                    name="email"
                                    type="email"
                                    :value="$val('email')"
                                    placeholder="email@exemple.com"
                            />
                            @error('email')
                            <small class="field-error">{{ $message }}</small>
                            @enderror
                        </label>

                        {{-- Téléphone 1 --}}
                        <label class="form-field" for="tel1">
                            <span>Téléphone 1 *</span>
                            <x-ui.input
                                    id="tel1"
                                    name="tel1"
                                    type="text"
                                    :value="$val('tel1')"
                                    placeholder="+225 07 00 00 00 00"
                                    required
                            />
                            @error('tel1')
                            <small class="field-error">{{ $message }}</small>
                            @enderror
                        </label>

                        {{-- Téléphone 2 --}}
                        <label class="form-field" for="tel2">
                            <span>Téléphone 2</span>
                            <x-ui.input
                                    id="tel2"
                                    name="tel2"
                                    type="text"
                                    :value="$val('tel2')"
                                    placeholder="+225 05 00 00 00 00"
                            />
                            @error('tel2')
                            <small class="field-error">{{ $message }}</small>
                            @enderror
                        </label>

                        {{-- Date de naissance --}}
                        <label class="form-field" for="date_naiss">
                            <span>Date de naissance</span>
                            <x-ui.date-picker
                                    name="date_naiss"
                                    :value="$val('date_naiss')"
                                    placeholder="Sélectionner la date"
                            />
                            @error('date_naiss')
                            <small class="field-error">{{ $message }}</small>
                            @enderror
                        </label>


                        {{-- Lieu de naissance --}}
                        <label class="form-field" for="lieu_naiss">
                            <span>Lieu de naissance</span>
                            <x-ui.input
                                    id="lieu_naiss"
                                    name="lieu_naiss"
                                    type="text"
                                    :value="$val('lieu_naiss')"
                                    placeholder="Abidjan"
                            />
                            @error('lieu_naiss')
                            <small class="field-error">{{ $message }}</small>
                            @enderror
                        </label>

                        {{-- Nationalité --}}
                        <label class="form-field" for="nationalite">
                            <span>Nationalité</span>
                            <x-ui.input
                                    id="nationalite"
                                    name="nationalite"
                                    type="text"
                                    :value="$val('nationalite')"
                                    placeholder="Ivoirienne"
                            />
                            @error('nationalite')
                            <small class="field-error">{{ $message }}</small>
                            @enderror
                        </label>


                        {{-- Type de pièce --}}
                        <label class="form-field" for="type_pieces_id">
                            <span>Type de pièce</span>
                            <x-ui.select
                                    name="type_pieces_id"
                                    :options="$typePiece ?? []"
                                    :value="$val('type_pieces_id')"
                            />
                            @error('type_pieces_id')
                            <small class="field-error">{{ $message }}</small>
                            @enderror
                        </label>

                        {{-- Numéro de pièce --}}
                        <label class="form-field" for="numpiece">
                            <span>Numéro de pièce</span>
                            <x-ui.input
                                    id="numpiece"
                                    name="numpiece"
                                    type="text"
                                    :value="$val('numpiece')"
                                    placeholder="CI000000000"
                            />
                            @error('numpiece')
                            <small class="field-error">{{ $message }}</small>
                            @enderror
                        </label>

                        <label class="form-field" for="numpiece">
                            <span>Date d'expiration de pièce</span>
                            <x-ui.input
                                    id="date_expiration_piece"
                                    name="date_expiration_piece"
                                    type="date"
                                    :value="$val('date_expiration_piece')"
                            />
                            @error('date_expiration_piece')
                            <small class="field-error">{{ $message }}</small>
                            @enderror
                        </label>



                        {{-- Profession --}}
                        <label class="form-field" for="profession">
                            <span>Profession</span>
                            <x-ui.input
                                    id="profession"
                                    name="profession"
                                    type="text"
                                    :value="$val('profession')"
                                    placeholder="Commerçant"
                            />
                            @error('profession')
                            <small class="field-error">{{ $message }}</small>
                            @enderror
                        </label>

                        <label class="form-field" for="region_id">
                            <span>Région</span>
                            <x-ui.select
                                    name="region_id"
                                    :options="collect($regions)->pluck('name', 'id')->toArray()"
                                    :value="$val('region_id')"
                                    placeholder="Sélectionnez votre région"
                                    onchange="getRequest('{{ url('/') }}/admin/list/city?parent_id='+this.value,'ville_id','select',this.value)"
                            />

                            @error('region_id') <small class="error">{{ $message }}</small> @enderror
                        </label>

                        <label class="form-field" for="ville_id">
                            <span>Ville</span>
                            <x-ui.select
                                    name="ville_id"
                                    :options="collect($villes ?? [])->where('region_id', $val('region_id'))->pluck('name', 'id')->toArray()"
                                    :value="$val('ville_id')"
                                    placeholder="Sélectionnez votre ville"
                            />

                            @error('ville_id') <small class="error">{{ $message }}</small> @enderror
                        </label>


                        {{-- Région --}}
                        {{--                        <label class="form-field" for="region_id">--}}
                        {{--                            <span>Région</span>--}}
                        {{--                            <x-ui.select--}}
                        {{--                                    name="region_id"--}}
                        {{--                                    :options="$regions ?? []"--}}
                        {{--                                    :value="$val('region_id')"--}}
                        {{--                            />--}}
                        {{--                            @error('region_id')--}}
                        {{--                            <small class="field-error">{{ $message }}</small>--}}
                        {{--                            @enderror--}}
                        {{--                        </label>--}}

                        {{--                        --}}{{-- Ville --}}
                        {{--                        <label class="form-field" for="ville_id">--}}
                        {{--                            <span>Ville</span>--}}
                        {{--                            <x-ui.select--}}
                        {{--                                    name="ville_id"--}}
                        {{--                                    :options="$villes ?? []"--}}
                        {{--                                    :value="$val('ville_id')"--}}
                        {{--                            />--}}
                        {{--                            @error('ville_id')--}}
                        {{--                            <small class="field-error">{{ $message }}</small>--}}
                        {{--                            @enderror--}}
                        {{--                        </label>--}}

                    </div>
                </article>

                {{-- ============================================================
                     SECTION 2 – Photo
                ============================================================ --}}
                <article class="card">
                    <div class="card-header">
                        <div>
                            <span class="eyebrow">Documents</span>
                            <h3>Photo du propriétaire</h3>
                        </div>
                    </div>

                    <div class="form-grid">
                        <label class="form-field form-field-wide" for="photo">
                            <span>Photo [ png, jpeg, jpg ]</span>

                            @if($isEdit && $proprietaire->photo)
                                <div class="u-mb-xs">
                                    <img src="{{ asset('storage/' . $proprietaire->photo) }}"
                                         alt="Photo actuelle"
                                         style="height:80px;border-radius:6px;object-fit:cover;">
                                    <small class="text-muted u-ml-xs">Photo actuelle</small>
                                </div>
                            @endif

                            <x-ui.file id="photo" name="photo" accept=".png,.jpeg,.jpg,image/*" />
                            <small class="text-muted">Faites glisser et déposez le fichier ici …</small>

                            @error('photo')
                            <small class="field-error">{{ $message }}</small>
                            @enderror
                        </label>
                    </div>
                </article>

                {{-- ============================================================
                     SECTION 3 – Représentant (liaison agence)
                ============================================================ --}}
                <article class="card">
                    <div class="card-header">
                        <div>
                            <span class="eyebrow">Représentant</span>
                            <h3>Informations du représentant</h3>
                        </div>
                    </div>

                    <div class="form-grid">
                        <x-ui.checkbox
                                id="has_representant"
                                name="has_representant"
                                :checked="$hasRepresentant || old('has_representant')"
                                class="form-field-wide"
                                label="Le propriétaire a un représentant"
                        />

                        <div id="representant-fields"
                             class="form-grid form-field-wide {{ $hasRepresentant || old('has_representant') ? '' : 'u-hidden' }}">

                            {{-- Nom représentant --}}
                            <label class="form-field form-field-wide" for="name_representant">
                                <span>Nom et prénom</span>
                                <x-ui.input
                                        id="name_representant"
                                        name="name_representant"
                                        type="text"
                                        :value="$valLiaison('name_representant')"
                                        placeholder="Nom du représentant"
                                />
                                @error('name_representant')
                                <small class="field-error">{{ $message }}</small>
                                @enderror
                            </label>

                            {{-- Adresse représentant --}}
                            <label class="form-field form-field-wide" for="adresse_representant">
                                <span>Adresse</span>
                                <x-ui.textarea
                                        id="adresse_representant"
                                        name="adresse_representant"
                                        :value="$valLiaison('adresse_representant')"
                                        rows="3"
                                        placeholder="Adresse du représentant"
                                />
                                @error('adresse_representant')
                                <small class="field-error">{{ $message }}</small>
                                @enderror
                            </label>

                            {{-- Tel 1 représentant --}}
                            <label class="form-field" for="tel1_representant">
                                <span>Téléphone 1</span>
                                <x-ui.input
                                        id="tel1_representant"
                                        name="tel1_representant"
                                        type="text"
                                        :value="$valLiaison('tel1_representant')"
                                        placeholder="+225 07 00 00 00 00"
                                />
                                @error('tel1_representant')
                                <small class="field-error">{{ $message }}</small>
                                @enderror
                            </label>

                            {{-- Tel 2 représentant --}}
                            <label class="form-field" for="tel2_representant">
                                <span>Téléphone 2</span>
                                <x-ui.input
                                        id="tel2_representant"
                                        name="tel2_representant"
                                        type="text"
                                        :value="$valLiaison('tel2_representant')"
                                        placeholder="+225 05 00 00 00 00"
                                />
                                @error('tel2_representant')
                                <small class="field-error">{{ $message }}</small>
                                @enderror
                            </label>

                            {{-- Email représentant --}}
                            <label class="form-field" for="email_representant">
                                <span>Email</span>
                                <x-ui.input
                                        id="email_representant"
                                        name="email_representant"
                                        type="email"
                                        :value="$valLiaison('email_representant')"
                                        placeholder="representant@exemple.com"
                                />
                                @error('email_representant')
                                <small class="field-error">{{ $message }}</small>
                                @enderror
                            </label>

                        </div>
                    </div>
                </article>

            </div>{{-- /.form-main --}}

            {{-- ============================================================
                 PANNEAU LATÉRAL – Validation
            ============================================================ --}}
            <aside class="card summary-panel">
                <span class="eyebrow">Propriétaire</span>
                <h3>Validation</h3>

                <p class="text-muted u-mt-xs">
                    Vérifiez les informations avant l'enregistrement du propriétaire.
                </p>

                @if($isEdit)
                    <div class="u-mb-sm">
                        <small class="text-muted">Code : <strong>{{ $proprietaire->code }}</strong></small><br>
                        <small class="text-muted">
                            Créé le : {{ $proprietaire->created_at?->format('d/m/Y') ?? '—' }}
                        </small>
                    </div>
                @endif

                <button class="btn btn-primary form-submit" type="submit">
                    {{ $isEdit ? 'Enregistrer les modifications' : 'Créer le propriétaire' }}
                </button>
            </aside>

        </form>
    </section>
@endsection

@push('scripts')
    <script>
        const checkbox       = document.getElementById('has_representant');
        const fieldsWrapper  = document.getElementById('representant-fields');

        checkbox?.addEventListener('change', function () {
            fieldsWrapper.classList.toggle('u-hidden', !this.checked);
        });
    </script>
@endpush
