@extends('agence.layouts.app')

@php
    $isEdit     = $mode === 'edit';
    $pageTitle  = $isEdit ? 'Modifier un membre' : 'Nouveau membre';

    // $personnel est un objet Eloquent en edit, un tableau vide en create
    $fieldValue = function ($field, $default = '') use ($isEdit, $personnel) {
        if (old($field) !== null) return old($field);
        if ($isEdit && is_object($personnel)) return $personnel->{$field} ?? $default;
        return $default;
    };
@endphp

@section('title', $pageTitle)

@section('content')
    <section class="page">
        <div class="page-hero">
            <div>
                <h2>{{ $pageTitle }}</h2>
            </div>

            <div class="page-actions">
                <a href="{{ route('agence.personnel.index') }}" class="btn btn-outline">
                    Retour
                </a>

                @if($isEdit)
                    <a href="#" class="btn btn-outline">
                        Voir le détail
                    </a>
                @endif
            </div>
        </div>

        <form
                class="form-layout"
                action="{{ $isEdit
                ? route('agence.personnel.update', ['id' => $personnel->id_users])
                : route('agence.personnel.store') }}"
                method="POST"
                enctype="multipart/form-data"
        >
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <div class="form-main">
                <article class="card">
                    <div class="card-header">
                        <div>
                            <span class="eyebrow">Personnel</span>
                            <h3>Informations du membre</h3>
                        </div>
                    </div>

                    <div class="form-grid">

                        <label class="form-field form-field-wide" for="name">
                            <span>Nom et prénom *</span>
                            <x-ui.input
                                    id="name"
                                    name="name"
                                    type="text"
                                    :value="$fieldValue('name')"
                                    placeholder="Kouamé Jean-Baptiste"
                                    required
                            />
                        </label>

                        <label class="form-field form-field-wide" for="adresse">
                            <span>Adresse *</span>
                            <x-ui.textarea
                                    id="adresse"
                                    name="adresse"
                                    :value="$fieldValue('adresse')"
                                    rows="3"
                                    placeholder="Cocody Riviera 3, Abidjan"
                                    required
                            />
                        </label>

                        <label class="form-field" for="email">
                            <span>Email</span>
                            <x-ui.input
                                    id="email"
                                    name="email"
                                    type="email"
                                    :value="$fieldValue('email')"
                                    placeholder="exemple@agence.ci"
                            />
                        </label>

                        <label class="form-field" for="tel1">
                            <span>Contact 1 *</span>
                            <x-ui.input
                                    id="tel1"
                                    name="tel1"
                                    type="text"
                                    :value="$fieldValue('tel1')"
                                    placeholder="+225 07 01 23 45 67"
                                    required
                            />
                        </label>

                        <label class="form-field" for="tel2">
                            <span>Contact 2</span>
                            <x-ui.input
                                    id="tel2"
                                    name="tel2"
                                    type="text"
                                    :value="$fieldValue('tel2')"
                                    placeholder="+225 05 44 78 12 99"
                            />
                        </label>

                        <label class="form-field" for="role_id">
                            <span>Rôle *</span>
                            <x-ui.select
                                    name="role_id"
                                    :options="$roles"
                                    :value="$fieldValue('role_id')"
                            />
                        </label>

                        <label class="form-field" for="password">
                            <span>Mot de passe {{ $isEdit ? '' : '*' }}</span>
                            <x-ui.input
                                    id="password"
                                    name="password"
                                    type="password"
                                    placeholder="********"
                                    :required="!$isEdit"
                            />
                        </label>

                        @if($isEdit)
                            <label class="form-field" for="statut">
                                <span>Statut</span>
                                <x-ui.select
                                        name="statut"
                                        :options="['actif' => 'Actif', 'inactif' => 'Inactif', 'suspendu' => 'Suspendu']"
                                        :value="$fieldValue('statut', 'actif')"
                                />
                            </label>
                        @endif

                        {{--                        <label class="form-field" for="is_responsable">--}}
                        {{--                            <span>Responsable d'agence</span>--}}
                        {{--                            <x-ui.select--}}
                        {{--                                    name="is_responsable"--}}
                        {{--                                    :options="[0 => 'Non', 1 => 'Oui']"--}}
                        {{--                                    :value="$fieldValue('is_responsable', 0)"--}}
                        {{--                            />--}}
                        {{--                        </label>--}}

                        <label class="form-field form-field-wide" for="photo">
                            <span>Photo</span>
                            @if($isEdit && $personnel->photo)
                                <img src="{{ Storage::url($personnel->photo) }}"
                                     alt="Photo actuelle"
                                     style="height:60px;border-radius:6px;margin-bottom:8px;display:block;">
                            @endif
                            <x-ui.file id="photo" name="photo" accept="image/*" />
                        </label>

                    </div>
                </article>
            </div>

            <aside class="card summary-panel">
                <span class="eyebrow">Personnel</span>
                <h3>Validation</h3>

                <p class="text-muted u-mt-xs">
                    Vérifiez les informations avant l'enregistrement du membre du personnel.
                </p>

                @if ($errors->any())
                    <ul class="form-errors u-mt-xs">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                @endif

                <button class="btn btn-primary form-submit" type="submit">
                    {{ $isEdit ? 'Enregistrer' : 'Créer le membre' }}
                </button>
            </aside>
        </form>
    </section>
@endsection

{{--@extends('agence.layouts.app')--}}

{{--@php--}}
{{--    $isEdit = $mode === 'edit';--}}
{{--    $pageTitle = $isEdit ? 'Modifier un membre' : 'Nouveau membre';--}}
{{--    $fieldValue = fn ($field, $default = '') => old($field, $personnel[$field] ?? $default);--}}
{{--@endphp--}}

{{--@section('title', $pageTitle)--}}


{{--@section('content')--}}
{{--    <section class="page">--}}
{{--        <div class="page-hero">--}}
{{--            <div>--}}
{{--                <h2>{{ $pageTitle }}</h2>--}}
{{--            </div>--}}

{{--            <div class="page-actions">--}}
{{--                <a href="{{ route('agence.personnel.index') }}" class="btn btn-outline">--}}
{{--                    Retour--}}
{{--                </a>--}}

{{--                @if($isEdit)--}}
{{--                    <a href="{{ route('agence.personnel.show', $personnel['user_id']) }}"--}}
{{--                       class="btn btn-outline">--}}
{{--                        Voir le détail--}}
{{--                    </a>--}}
{{--                @endif--}}
{{--            </div>--}}
{{--        </div>--}}

{{--        <form class="form-layout" action="{{ $isEdit==='edit' ? route('agence.personnel.update',['id' => $personnel['user_id']] ) :  route('agence.personnel.store') }}" method="POST" enctype="multipart/form-data">--}}
{{--            @csrf--}}

{{--            @if($isEdit)--}}
{{--                @method('PUT')--}}
{{--            @endif--}}

{{--            <div class="form-main">--}}
{{--                <article class="card">--}}
{{--                    <div class="card-header">--}}
{{--                        <div>--}}
{{--                            <span class="eyebrow">Personnel</span>--}}
{{--                            <h3>Informations du membre</h3>--}}
{{--                        </div>--}}
{{--                    </div>--}}

{{--                    <div class="form-grid">--}}
{{--                        <label class="form-field form-field-wide" for="name">--}}
{{--                            <span>Nom et prénom *</span>--}}
{{--                            <input--}}
{{--                                    id="name"--}}
{{--                                    name="name"--}}
{{--                                    type="text"--}}
{{--                                    value="{{ $fieldValue('name') }}"--}}
{{--                                    placeholder="Kouamé Jean-Baptiste"--}}
{{--                                    required--}}
{{--                            >--}}
{{--                        </label>--}}

{{--                        <label class="form-field form-field-wide" for="adresse">--}}
{{--                            <span>Adresse *</span>--}}
{{--                            <textarea--}}
{{--                                    id="adresse"--}}
{{--                                    name="adresse"--}}
{{--                                    rows="3"--}}
{{--                                    placeholder="Cocody Riviera 3, Abidjan"--}}
{{--                                    required--}}
{{--                            >{{ $fieldValue('adresse') }}</textarea>--}}
{{--                        </label>--}}

{{--                        <label class="form-field" for="email">--}}
{{--                            <span>Email</span>--}}
{{--                            <input--}}
{{--                                    id="email"--}}
{{--                                    name="email"--}}
{{--                                    type="email"--}}
{{--                                    value="{{ $fieldValue('email') }}"--}}
{{--                                    placeholder="exemple@agence.ci"--}}
{{--                            >--}}
{{--                        </label>--}}

{{--                        <label class="form-field" for="contact_1">--}}
{{--                            <span>Contact 1 *</span>--}}
{{--                            <input--}}
{{--                                    id="contact_1"--}}
{{--                                    name="contact_1"--}}
{{--                                    type="text"--}}
{{--                                    value="{{ $fieldValue('contact_1') }}"--}}
{{--                                    placeholder="+225 07 01 23 45 67"--}}
{{--                                    required--}}
{{--                            >--}}
{{--                        </label>--}}

{{--                        <label class="form-field" for="contact_2">--}}
{{--                            <span>Contact 2</span>--}}
{{--                            <input--}}
{{--                                    id="contact_2"--}}
{{--                                    name="contact_2"--}}
{{--                                    type="text"--}}
{{--                                    value="{{ $fieldValue('contact_2') }}"--}}
{{--                                    placeholder="+225 05 44 78 12 99"--}}
{{--                            >--}}
{{--                        </label>--}}

{{--                        <label class="form-field" for="region">--}}
{{--                            <span>Région</span>--}}
{{--                            <input--}}
{{--                                    id="region"--}}
{{--                                    name="region"--}}
{{--                                    type="text"--}}
{{--                                    value="{{ $fieldValue('region') }}"--}}
{{--                                    placeholder="Abidjan"--}}
{{--                            >--}}
{{--                        </label>--}}

{{--                        <label class="form-field" for="ville">--}}
{{--                            <span>Ville</span>--}}
{{--                            <input--}}
{{--                                    id="ville"--}}
{{--                                    name="ville"--}}
{{--                                    type="text"--}}
{{--                                    value="{{ $fieldValue('ville') }}"--}}
{{--                                    placeholder="Cocody"--}}
{{--                            >--}}
{{--                        </label>--}}

{{--                        <label class="form-field" for="role">--}}
{{--                            <span>Rôle *</span>--}}

{{--                            <x-ui.select--}}
{{--                                    name="role"--}}
{{--                                    :options="array_combine(--}}
{{--                                    [--}}
{{--                                        'Administrateur',--}}
{{--                                        'Gestionnaire',--}}
{{--                                        'Commercial',--}}
{{--                                        'Comptable',--}}
{{--                                        'Technicien'--}}
{{--                                    ],--}}
{{--                                    [--}}
{{--                                        'Administrateur',--}}
{{--                                        'Gestionnaire',--}}
{{--                                        'Commercial',--}}
{{--                                        'Comptable',--}}
{{--                                        'Technicien'--}}
{{--                                    ]--}}
{{--                                )"--}}
{{--                                    :value="$fieldValue('role', 'Gestionnaire')"--}}
{{--                            />--}}
{{--                        </label>--}}

{{--                        <label class="form-field" for="password">--}}
{{--                            <span>Mot de passe *</span>--}}
{{--                            <input--}}
{{--                                    id="password"--}}
{{--                                    name="password"--}}
{{--                                    type="password"--}}
{{--                                    placeholder="********"--}}
{{--                                    {{ $isEdit ? '' : 'required' }}--}}
{{--                            >--}}
{{--                        </label>--}}

{{--                        <label class="form-field form-field-wide" for="photo">--}}
{{--                            <span>Photo</span>--}}
{{--                            <input--}}
{{--                                    id="photo"--}}
{{--                                    name="photo"--}}
{{--                                    type="file"--}}
{{--                                    accept="image/*"--}}
{{--                            >--}}
{{--                        </label>--}}
{{--                    </div>--}}
{{--                </article>--}}
{{--            </div>--}}

{{--            <aside class="card summary-panel">--}}
{{--                <span class="eyebrow">Personnel</span>--}}
{{--                <h3>Validation</h3>--}}

{{--                <p class="text-muted u-mt-xs">--}}
{{--                    Vérifiez les informations avant l'enregistrement du membre du personnel.--}}
{{--                </p>--}}

{{--                <button class="btn btn-primary form-submit" type="submit">--}}
{{--                    {{ $isEdit ? 'Enregistrer' : 'Créer le membre' }}--}}
{{--                </button>--}}
{{--            </aside>--}}
{{--        </form>--}}
{{--    </section>--}}
{{--@endsection--}}
