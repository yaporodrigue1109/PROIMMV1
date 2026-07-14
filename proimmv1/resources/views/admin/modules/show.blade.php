@extends('admin.layouts.app')

@section('title', $module['nom'])
@section('header_title', 'Détail du module')

@section('content')
    <section class="admin-page">
        <div class="admin-hero">
            <div>
                <span class="admin-eyebrow">{{ $module['code'] }}</span>
                <h2>{{ $module['nom'] }}</h2>
                <p>{{ $module['description'] }}</p>
            </div>

            <div class="admin-actions">
                <a href="{{ route('admin.modules.index') }}" class="btn fleet-btn-outline">Retour</a>
                <a href="{{ route('admin.modules.edit', $module['code']) }}" class="btn fleet-btn-primary">Modifier</a>
            </div>
        </div>

        <div class="admin-grid">
            <article class="admin-card">
                <div class="admin-card-header">
                    <div>
                        <span class="admin-eyebrow">Informations</span>
                        <h3>Configuration du module</h3>
                    </div>
                    <span class="admin-status {{ $module['statut'] === 'Actif' ? 'is-active' : '' }}">{{ $module['statut'] }}</span>
                </div>

                <div class="admin-info-list">
                    <div class="admin-info-item">
                        <span>Catégorie</span>
                        <strong>{{ $module['categorie'] }}</strong>
                    </div>
                    <div class="admin-info-item">
                        <span>Tarif</span>
                        <strong>{{ number_format($module['prix'], 0, ',', ' ') }} FCFA / {{ strtolower($module['cycle']) }}</strong>
                    </div>
                    <div class="admin-info-item">
                        <span>Agences actives</span>
                        <strong>{{ $module['agences'] }}</strong>
                    </div>
                    <div class="admin-info-item">
                        <span>Code technique</span>
                        <strong>{{ $module['code'] }}</strong>
                    </div>
                </div>
            </article>

            <article class="admin-card">
                <div class="admin-card-header">
                    <div>
                        <span class="admin-eyebrow">Accès</span>
                        <h3>Permissions incluses</h3>
                    </div>
                    <span class="admin-chip">{{ count($module['permissions']) }} règles</span>
                </div>

                <div class="admin-chip-list">
                    @foreach($module['permissions'] as $permission)
                        <span>{{ $permission }}</span>
                    @endforeach
                </div>
            </article>
        </div>
    </section>
@endsection
