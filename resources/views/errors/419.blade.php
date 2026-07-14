@extends('admin.layouts.error')

@section('title', '419 - Session expirée')

@section('error_content')
    <div class="login-card text-center">
        <h1 style="font-size: 100px; color:#6366f1;">419</h1>

        <h3>Session expirée</h3>

        <p class="text-muted">
            Votre session a expiré. Veuillez rafraîchir la page et réessayer.
        </p>

        <a href="{{ url()->current() }}" class="btn-primary-custom mt-3">
            Recharger la page
        </a>
    </div>
@endsection