@php
    $admin = auth('admin')->user();
    $adminName = $admin?->name ?: 'Administrateur';
    $nameParts = preg_split('/\s+/', trim($adminName), -1, PREG_SPLIT_NO_EMPTY);
    $adminInitials = collect($nameParts)
        ->take(2)
        ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
        ->implode('') ?: 'AD';
@endphp

<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo" style="margin-right: 15px">
            <img alt="pros immobilier" src="{{ asset('admin/logo/playstore-icon-revised.png')}}">
        </div>
        <div class="sidebar-title">
            <h2>PROS IMMOBILIER</h2>
        </div>
    </div>

    {{--    <div class="sidebar-search">--}}
    {{--        <div class="search-wrapper">--}}
    {{--            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">--}}
    {{--                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>--}}
    {{--            </svg>--}}
    {{--            <input type="search" class="search-input" placeholder="Search...">--}}
    {{--        </div>--}}
    {{--    </div>--}}

    <nav class="sidebar-nav">

        {{-- DASHBOARD --}}
        <a href="{{ route('admin.dashboard') }}"
           class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <div class="nav-item-left">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span>Tableau de bord</span>
            </div>
        </a>

        <div class="nav-divider"></div>

        {{-- AGENCES --}}
        <a href="{{ route('admin.agences.index') }}"
           class="nav-item {{ request()->routeIs('admin.agences.*') ? 'active' : '' }}">
            <div class="nav-item-left">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                <span>Agences</span>
            </div>
        </a>

        {{-- ABONNEMENTS --}}
        <a href="{{ route('admin.abonnements.index') }}"
           class="nav-item {{ request()->routeIs('admin.abonnements.*') ? 'active' : '' }}">
            <div class="nav-item-left">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                </svg>
                <span>Abonnements</span>
            </div>
        </a>

        <div class="nav-divider"></div>

        {{-- COMPTES --}}
        {{--        <a href="#"--}}
        {{--           class="nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">--}}
        {{--            <div class="nav-item-left">--}}
        {{--                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">--}}
        {{--                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>--}}
        {{--                </svg>--}}
        {{--                <span>Comptes & Accès</span>--}}
        {{--            </div>--}}
        {{--        </a>--}}

        {{-- MODULES --}}
        <a href="{{ route('admin.modules.index') }}"
           class="nav-item {{ request()->routeIs('admin.modules.*') ? 'active' : '' }}">
            <div class="nav-item-left">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                </svg>
                <span>Modules</span>
            </div>
        </a>



        {{-- CONFIG --}}
        <a href="{{ route('admin.settings.index') }}"
           class="nav-item {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
            <div class="nav-item-left">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span>Configuration</span>
            </div>
        </a>

        {{-- STATS --}}
        <a href="{{ route('admin.statistiques.index') }}"
           class="nav-item {{ request()->routeIs('admin.statistiques.*') ? 'active' : '' }}">
            <div class="nav-item-left">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                <span>Statistiques</span>
            </div>
        </a>

        <div class="nav-divider"></div>

        {{-- SUPPORT --}}
        <a href="{{ route('admin.tickets.index') }}"
           class="nav-item {{ request()->routeIs('admin.tickets.*') ? 'active' : '' }}">
            <div class="nav-item-left">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <span>Tickets</span>
            </div>
        </a>

        {{-- AUDIT --}}
        {{--        <a href="#"--}}
        {{--           class="nav-item {{ request()->routeIs('admin.audit.*') ? 'active' : '' }}">--}}
        {{--            <div class="nav-item-left">--}}
        {{--                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">--}}
        {{--                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>--}}
        {{--                </svg>--}}
        {{--                <span>Audit & Sécurité</span>--}}
        {{--            </div>--}}
        {{--        </a>--}}

    </nav>

    <div class="sidebar-footer">

        {{--        <a href="#" class="nav-item">--}}
        {{--            <div class="nav-item-left">--}}
        {{--                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">--}}
        {{--                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>--}}
        {{--                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>--}}
        {{--                </svg>--}}
        {{--                <span>Settings</span>--}}
        {{--            </div>--}}
        {{--        </a>--}}

        <div class="user-info">
            <div class="user-info-left">
                <div class="avatar">{{ $adminInitials }}</div>
                <span>{{ $adminName }}</span>
            </div>
        </div>

    </div>
</aside>
