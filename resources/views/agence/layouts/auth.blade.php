<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Connexion — Espace Agence')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="{{ asset('app.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('styles.css') }}" />
</head>
<body class="login-page">
<div class="page-shell">
    <div class="topbar">
        <button class="theme-toggle" id="themeToggle" type="button" aria-label="Passer en mode clair" aria-pressed="false">
            <span class="theme-icon-sun" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25M12 18.75V21m9-9h-2.25M5.25 12H3m15.114 6.364-1.591-1.591M7.477 7.477 5.886 5.886m12.228 0-1.591 1.591M7.477 16.523l-1.591 1.591M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0z"/></svg>
            </span>
            <span class="theme-icon-moon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 12c0 5.385 4.365 9.75 9.75 9.75a9.753 9.753 0 0 0 9.002-6.748z"/></svg>
            </span>
            <span class="theme-toggle-label" id="themeToggleLabel">Mode clair</span>
        </button>
    </div>

    <div class="center-wrap">
        @yield('auth_content')
    </div>
</div>
</body>
</html>
