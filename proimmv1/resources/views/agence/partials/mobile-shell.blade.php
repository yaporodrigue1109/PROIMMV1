@php
    $user = auth('user')->user();
    $userName = $user?->name ?: 'Mon Agence';
    $nameParts = preg_split('/\s+/', trim($userName), -1, PREG_SPLIT_NO_EMPTY);
    $userInitials = collect($nameParts)
        ->take(2)
        ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
        ->implode('') ?: 'AG';
@endphp

<div class="mobile-overlay" id="mobileOverlay"></div>

<div class="sidebar-mobile" id="mobileSidebar">
    <button class="close-btn" id="mobileClose" type="button" aria-label="Fermer le menu">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>

    <div class="sidebar-header">
        <div class="sidebar-logo" style="margin-right: 15px">
            <img alt="pros immobilier" src="{{ asset('admin/logo/playstore-icon-revised.png') }}">
        </div>
        <div class="sidebar-title">
            <h2>PROS IMMOBILIER</h2>
        </div>
    </div>

    <nav class="sidebar-nav">

        <a href="{{ route('agence.dashboard') }}"
           class="nav-item {{ request()->routeIs('agence.dashboard') ? 'active' : '' }}">
            <div class="nav-item-left">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span>Tableau de bord</span>
            </div>
        </a>

        <div class="nav-divider"></div>

        <a href="#"
           class="nav-item {{ request()->routeIs('agence.biens.*') ? 'active' : '' }}">
            <div class="nav-item-left">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/>
                </svg>
                <span>Biens immobiliers</span>
            </div>
        </a>

        <a href="#"
           class="nav-item {{ request()->routeIs('agence.clients.*') ? 'active' : '' }}">
            <div class="nav-item-left">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <span>Clients</span>
            </div>
        </a>

        <a href="#"
           class="nav-item {{ request()->routeIs('agence.transactions.*') ? 'active' : '' }}">
            <div class="nav-item-left">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
                <span>Transactions</span>
            </div>
        </a>

        <a href="#"
           class="nav-item {{ request()->routeIs('agence.documents.*') ? 'active' : '' }}">
            <div class="nav-item-left">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
                <span>Documents</span>
            </div>
        </a>

        <div class="nav-divider"></div>

        <a href="#"
           class="nav-item {{ request()->routeIs('agence.equipe.*') ? 'active' : '' }}">
            <div class="nav-item-left">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <span>Mon équipe</span>
            </div>
        </a>

        <a href="{{ route('agence.statistiques.index') }}"
           class="nav-item {{ request()->routeIs('agence.statistiques.*') ? 'active' : '' }}">
            <div class="nav-item-left">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                <span>Statistiques</span>
            </div>
        </a>

        <div class="nav-divider"></div>

        <a href="#"
           class="nav-item {{ request()->routeIs('agence.abonnement.*') ? 'active' : '' }}">
            <div class="nav-item-left">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                </svg>
                <span>Abonnement</span>
            </div>
        </a>

        <a href="#"
           class="nav-item {{ request()->routeIs('agence.tickets.*') ? 'active' : '' }}">
            <div class="nav-item-left">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <span>Support</span>
            </div>
        </a>

    </nav>

    <div class="sidebar-footer">
        <div class="user-info">
            <div class="user-info-left">
                <div class="avatar">{{ $userInitials }}</div>
                <span>{{ $userName }}</span>
            </div>
        </div>
    </div>
</div>
