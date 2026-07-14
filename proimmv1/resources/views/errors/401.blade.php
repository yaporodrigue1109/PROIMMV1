@extends('admin.layouts.error')

@section('title', '401 - Non authentifié')

@section('error_content')
    <div class="login-card text-center">
        <h1 style="font-size: 100px; color:#f59e0b;">401</h1>

        <h3>Non authentifié</h3>

        <p class="text-muted">
            Vous devez être connecté pour accéder à cette page.
        </p>

        <a href="{{ route('admin.login') }}" class="btn-primary-custom mt-3">
            Se connecter
        </a>
    </div>
@endsection
