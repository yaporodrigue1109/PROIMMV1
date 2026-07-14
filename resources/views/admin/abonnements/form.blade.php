@extends('admin.layouts.app')

@php
    $isEdit = $mode === 'edit';
    $pageTitle = $isEdit ? 'Modifier un abonnement' : 'Nouvel abonnement';
    $fieldValue = fn ($field, $default = '') => old($field, $abonnement[$field] ?? $default);
@endphp

@section('title', $pageTitle)
@section('header_title', $pageTitle)

@section('content')
    <section class="page">
        <div class="page-hero">
            <div>
                <h2>{{ $pageTitle }}</h2>
            </div>

            <div class="page-actions">
                <a href="{{ route('admin.abonnements.index') }}" class="btn btn-outline">Retour</a>
                @if($isEdit)
                    <a href="{{ route('admin.abonnements.show', $abonnement['code_agence']) }}" class="btn btn-outline">Voir le detail</a>
                @endif
            </div>
        </div>

        <form class="form-layout" action="#" method="POST">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <div class="form-main">
                <article class="card">
                    <div class="card-header">
                        <div>
                            <span class="eyebrow">Agence</span>
                            <h3>Compte concerne</h3>
                        </div>
                    </div>

                    <div class="form-grid">
                        <label class="form-field form-field-wide" for="agence">
                            <span>Agence</span>
                            <input id="agence" name="agence" type="text" value="{{ $fieldValue('agence') }}" placeholder="Pros Immobilier Cocody">
                        </label>

                        <label class="form-field" for="code_agence">
                            <span>Code agence</span>
                            <input id="code_agence" name="code_agence" type="text" value="{{ $fieldValue('code_agence') }}" placeholder="AGC-001">
                        </label>

                        <label class="form-field" for="statut">
                            <span>Statut</span>
                            <x-ui.select
                                    name="statut"
                                    :options="array_combine(
                                    ['Actif', 'En attente', 'Expire', 'Suspendu'],
                                    ['Actif', 'En attente', 'Expire', 'Suspendu']
                                )"
                                    :value="$fieldValue('statut', 'En attente')"
                            />
                        </label>
                    </div>
                </article>

                <article class="card">
                    <div class="card-header">
                        <div>
                            <span class="eyebrow">Facturation</span>
                            <h3>Plan et periode</h3>
                        </div>
                    </div>

                    <div class="form-grid">
                        <label class="form-field" for="plan">
                            <span>Plan</span>
                            <x-ui.select
                                    name="plan"
                                    :options="collect($plans)->pluck('nom', 'nom')->toArray()"
                                    :value="$fieldValue('plan', 'Standard')"
                            />
                        </label>

                        <label class="form-field" for="montant">
                            <span>Montant</span>
                            <input id="montant" name="montant" type="number" value="{{ $fieldValue('montant', 25000) }}" placeholder="25000">
                        </label>

                        <label class="form-field" for="cycle">
                            <span>Cycle</span>
                            <x-ui.select
                                    name="cycle"
                                    :options="array_combine(
                                    ['Mensuel', 'Trimestriel', 'Semestriel', 'Annuel'],
                                    ['Mensuel', 'Trimestriel', 'Semestriel', 'Annuel']
                                )"
                                    :value="$fieldValue('cycle', 'Mensuel')"
                            />
                        </label>

                        <label class="form-field" for="date_debut">
                            <span>Date de debut</span>
                            <x-ui.date-picker
                                    name="date_debut"
                                    :value="$fieldValue('date_debut')"
                                    placeholder="Selectionner la date de debut"
                            />
                        </label>

                        <label class="form-field" for="date_fin">
                            <span>Date de fin</span>
                            <x-ui.date-picker
                                    name="date_fin"
                                    :value="$fieldValue('date_fin')"
                                    placeholder="Selectionner la date de fin"
                            />
                        </label>

                        <label class="form-field form-field-wide" for="notes">
                            <span>Notes</span>
                            <textarea id="notes" name="notes" rows="3" placeholder="Commentaire interne">{{ $fieldValue('notes') }}</textarea>
                        </label>
                    </div>
                </article>
            </div>

            <aside class="card summary-panel">
                <span class="eyebrow">Modules</span>
                <h3>Options incluses</h3>

                <div class="option-list">
                    @foreach(['Annonces illimitees', 'SMS', 'WhatsApp', 'Statistiques', 'Support prioritaire', 'Rapports simples'] as $module)
                        <label class="option-item">
                            <input type="checkbox" name="modules[]" value="{{ $module }}" @checked(in_array($module, $abonnement['modules'] ?? []))>
                            <span>{{ $module }}</span>
                        </label>
                    @endforeach
                </div>

                <button class="btn btn-primary form-submit" type="button">
                    {{ $isEdit ? 'Enregistrer' : "Creer l'abonnement" }}
                </button>
            </aside>
        </form>
    </section>
@endsection
