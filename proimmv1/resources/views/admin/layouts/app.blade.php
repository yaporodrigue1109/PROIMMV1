@php
    $authAdmin    = auth('admin')->user();
    $adminName    = $authAdmin?->name ?: 'Administrateur';
    $adminEmail   = $authAdmin?->email ?: '';
    $nameParts    = preg_split('/\s+/', trim($adminName), -1, PREG_SPLIT_NO_EMPTY);
    $adminInitials = collect($nameParts)
        ->take(2)
        ->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))
        ->implode('') ?: 'AD';
@endphp
        <!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Pros Immobilier — Admin')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link rel="stylesheet" href="{{ asset('styles.css') }}" />
    @vite(['resources/css/app.css'])
    <script src="{{ asset('app.js') }}"></script>
    @stack('styles')
    @yield('styles')
</head>
<body class="dashboard-page">
<div class="gradient-bg"></div>

@include('admin.partials.mobile-shell')

<div class="app">
    @include('admin.partials.sidebar')

    <div class="main-content" id="mainContent">
        <x-app-header
                :user-initials="$adminInitials"
                :user-name="$adminName"
                :user-email="$adminEmail"
                :profile-route="route('admin.profile')"
                :settings-route="route('admin.settings.index')"
                :logout-route="route('admin.logout')"
                :notification-count="0"
        />

        <main class="main">
            @if (session('success'))
                <div class="alert alert-success" style="margin: 0 0 16px; padding: 12px 16px; border-radius: 12px; background: #e8fff1; color: #146c43; border: 1px solid #b7ebc6;">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger" style="margin: 0 0 16px; padding: 12px 16px; border-radius: 12px; background: #ffecec; color: #b42318; border: 1px solid #f5b7b1;">
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>
@stack('scripts')
@yield('scripts')
</body>
</html>
