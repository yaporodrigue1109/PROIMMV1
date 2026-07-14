@php
    $authUser = auth('user')->user();
    $agenceName = $authUser?->name ?: 'Mon Agence';
    $agenceEmail = $authUser?->email ?: '';
    $nameParts = preg_split('/\s+/', trim($agenceName), -1, PREG_SPLIT_NO_EMPTY);
    $agenceInitials = collect($nameParts)
        ->take(2)
        ->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))
        ->implode('') ?: 'AG';
@endphp
        <!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Pros Immobilier — Espace Agence')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="{{ asset('app.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('styles.css') }}" />
    @vite(['resources/css/app.css'])
    @stack('styles')
    @yield('styles')
</head>
<body class="@yield('body_class', 'dashboard-page')">
<div class="gradient-bg"></div>

@yield('layout')

@stack('scripts')
@yield('scripts')
</body>
</html>
