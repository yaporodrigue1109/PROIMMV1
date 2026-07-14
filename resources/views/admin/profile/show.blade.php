@extends('admin.layouts.app')

@section('title', 'Mon profil')
@section('header_title', 'Mon profil')

@php
    $adminName = $admin?->name ?: 'Administrateur';
    $adminEmail = $admin?->email ?: 'Email non renseigné';
    $adminPhone = $admin?->phone ?: 'Téléphone non renseigné';
    $adminPhoto = $admin?->photo ?: null; // Assurez-vous que ce champ existe dans votre base de données
    $nameParts = preg_split('/\s+/', trim($adminName), -1, PREG_SPLIT_NO_EMPTY);
    $adminInitials = collect($nameParts)
        ->take(2)
        ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
        ->implode('') ?: 'AD';
    $isActive = (int) ($admin?->statut ?? 0) === 1;
@endphp

@section('content')
    <section class="page">

        <div class="content-grid">
            <article class="card" id="profile-details">
                <div class="card-header">
                    <div>
                        <span class="eyebrow">Identité</span>
                        <h3>Informations personnelles</h3>
                    </div>
                </div>

                <div class="info-list">
                    <div class="info-item">
                        <span>Nom complet</span>
                        <strong>{{ $adminName }}</strong>
                    </div>
                    <div class="info-item">
                        <span>Adresse e-mail</span>
                        <strong>{{ $adminEmail }}</strong>
                    </div>
                    <div class="info-item">
                        <span>Téléphone</span>
                        <strong>{{ $adminPhone }}</strong>
                    </div>
                    <div class="info-item">
                        <span>Identifiant</span>
                        <strong>{{ $admin?->id_admin ?: 'Non disponible' }}</strong>
                    </div>
                </div>
            </article>

            <article class="card">
                <div class="card-header">
                    <div>
                        <h3>Photo de profil</h3>
                    </div>
                </div>

                <div class="info-list">

                </div>

                <div class="card-actions">
                    <a href="#" class="btn btn-primary">
                        Modifier le profil
                    </a>
                </div>
            </article>
        </div>

    </section>
@endsection

