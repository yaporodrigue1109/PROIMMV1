@extends('admin.layouts.app')

@php
    $isEdit = $mode === 'edit';
    $pageTitle = $isEdit ? 'Modifier un module' : 'Ajouter un module';
    $fieldValue = fn ($field, $default = '') => old($field, $module[$field] ?? $default);
@endphp

@section('title', $pageTitle)
@section('header_title', $pageTitle)

@section('content')
    <section class="admin-page">
        <div class="admin-hero">
            <div>
                <h2>{{ $pageTitle }}</h2>
            </div>

            <div class="admin-actions">
                <a href="{{ route('admin.modules.index') }}" class="btn fleet-btn-outline">Retour</a>
                @if($isEdit)
                    <a href="{{ route('admin.modules.show', $module['code']) }}" class="btn fleet-btn-outline">Voir la fiche</a>
                @endif
            </div>
        </div>

        <form class="admin-form" action="#" method="POST">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <div class="admin-form-main">
                <article class="admin-card">
                    <div class="admin-card-header">
                        <div>
                            <span class="admin-eyebrow">Module</span>
                            <h3>Informations générales</h3>
                        </div>
                        <span class="admin-step">01</span>
                    </div>

                    <div class="admin-form-grid">
                        <label class="admin-field admin-field-wide" for="nom">
                            <span>Nom du module</span>
                            <input id="nom" name="nom" type="text" value="{{ $fieldValue('nom') }}" placeholder="SMS">
                        </label>

                        <label class="admin-field" for="code">
                            <span>Code module</span>
                            <input id="code" name="code" type="text" value="{{ $fieldValue('code') }}" placeholder="MOD-SMS">
                        </label>

                        <label class="admin-field" for="categorie">
                            <span>Catégorie</span>
                            <select id="categorie" name="categorie">
                                @foreach(['Communication', 'Espace client', 'Pilotage', 'Publication', 'Support'] as $categorie)
                                    <option value="{{ $categorie }}" @selected($fieldValue('categorie', 'Communication') === $categorie)>{{ $categorie }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label class="admin-field" for="statut">
                            <span>Statut</span>
                            <select id="statut" name="statut">
                                @foreach(['Actif', 'En attente', 'Suspendu'] as $statut)
                                    <option value="{{ $statut }}" @selected($fieldValue('statut', 'En attente') === $statut)>{{ $statut }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label class="admin-field admin-field-wide" for="description">
                            <span>Description</span>
                            <textarea id="description" name="description" rows="3" placeholder="Explique le rôle du module">{{ $fieldValue('description') }}</textarea>
                        </label>
                    </div>
                </article>

                <article class="admin-card">
                    <div class="admin-card-header">
                        <div>
                            <span class="admin-eyebrow">Tarification</span>
                            <h3>Prix et cycle</h3>
                        </div>
                        <span class="admin-step">02</span>
                    </div>

                    <div class="admin-form-grid">
                        <label class="admin-field" for="prix">
                            <span>Prix</span>
                            <input id="prix" name="prix" type="number" value="{{ $fieldValue('prix', 15000) }}" placeholder="15000">
                        </label>

                        <label class="admin-field" for="cycle">
                            <span>Cycle</span>
                            <select id="cycle" name="cycle">
                                @foreach(['Mensuel', 'Trimestriel', 'Semestriel', 'Annuel'] as $cycle)
                                    <option value="{{ $cycle }}" @selected($fieldValue('cycle', 'Mensuel') === $cycle)>{{ $cycle }}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>
                </article>
            </div>

            <aside class="admin-card admin-summary">
                <span class="admin-eyebrow">Permissions</span>
                <h3>Accès inclus</h3>

                <div class="admin-option-list">
                    @foreach(['Lecture', 'Création', 'Modification', 'Suppression', 'Export', 'Administration'] as $permission)
                        <label class="admin-option">
                            <input type="checkbox" name="permissions[]" value="{{ $permission }}" @checked(in_array($permission, $module['permissions'] ?? []))>
                            <span>{{ $permission }}</span>
                        </label>
                    @endforeach
                </div>

                <button class="admin-submit" type="button">
                    {{ $isEdit ? 'Enregistrer les modifications' : 'Créer le module' }}
                </button>
            </aside>
        </form>
    </section>
@endsection
