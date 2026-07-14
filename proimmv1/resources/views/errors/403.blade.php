@extends('admin.layouts.error')

@section('title', '403 - Accès interdit')

@section('error_content')
    <div class="login-card text-center">
        <h1 style="font-size: 100px; color:#ff3b3b;">403</h1>

        <h3>Accès interdit</h3>

        <p class="text-muted">
            Vous n'avez pas les permissions nécessaires pour accéder à cette page.
        </p>

        <a href="{{ route('admin.dashboard') }}" class="btn-primary-custom mt-3">
            Retour au dashboard
        </a>
    </div>
@endsection
