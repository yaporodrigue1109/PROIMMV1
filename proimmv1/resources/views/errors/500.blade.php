@extends('admin.layouts.error')

@section('title', '500 - Erreur serveur')

@section('error_content')
    <div class="login-card text-center">
        <h1 style="font-size: 100px; color:#ef4444;">500</h1>

        <h3>Erreur interne</h3>

        <p class="text-muted">
            Une erreur inattendue est survenue. Notre équipe technique a été notifiée.
        </p>

        <a href="{{ route('admin.dashboard') }}" class="btn-primary-custom mt-3">
            Retour au dashboard
        </a>
    </div>
@endsection