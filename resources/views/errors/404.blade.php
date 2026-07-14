@extends('admin.layouts.error')

@section('title', '404 - Page introuvable')

@section('error_content')
    <div class="login-card" style="text-align: center;">
        <h1 style="font-size: 100px; color:#00a76f; margin: 0;">404</h1>

        <h3 style="margin-top: 10px;">Page introuvable</h3>

        <p style="color: var(--muted-foreground); margin-top: 10px;">
            La ressource demandée est introuvable.
        </p>

    
    </div>
@endsection