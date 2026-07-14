@extends('admin.layouts.error')

@section('title', '503 - Maintenance')

@section('error_content')
    <div class="login-card text-center">
        <h1 style="font-size: 100px; color:#0ea5e9;">503</h1>

        <h3>Maintenance en cours</h3>

        <p class="text-muted">
            Le système est temporairement indisponible pour maintenance.
        </p>
    </div>
@endsection