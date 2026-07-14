@props([
    'userInitials'      => 'AD',
    'userName'          => 'Utilisateur',
    'userEmail'         => '',
    'profileRoute'      => '#',
    'settingsRoute'     => '#',
    'logoutRoute'       => '#',
    'notificationCount' => 0,
])

<header class="header">
    <div class="header-left">
        <button class="toggle-btn mobile-menu-btn" id="mobileMenuToggle" type="button">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>
        <button class="toggle-btn" id="sidebarToggle" type="button">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"/>
            </svg>
        </button>
        <h1 class="header-title">@yield('header_title', '')</h1>
    </div>

    <div class="header-right">
        {{-- Bascule thème --}}
        <button class="theme-toggle" id="themeToggle" type="button"
                aria-label="Passer en mode clair" aria-pressed="false">
            <span class="theme-icon-sun" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25M12 18.75V21m9-9h-2.25M5.25 12H3m15.114 6.364-1.591-1.591M7.477 7.477 5.886 5.886m12.228 0-1.591 1.591M7.477 16.523l-1.591 1.591M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z"/>
                </svg>
            </span>
            <span class="theme-icon-moon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 12c0 5.385 4.365 9.75 9.75 9.75a9.753 9.753 0 009.002-6.748z"/>
                </svg>
            </span>
            <span class="theme-toggle-label" id="themeToggleLabel">Mode clair</span>
        </button>

        {{-- Notifications --}}
        <div class="notification-menu-wrapper">
            <button class="icon-btn" id="notificationToggle" type="button"
                    aria-label="Ouvrir les notifications" aria-expanded="false" aria-controls="notificationMenu">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <span class="notification-badge">{{ $notificationCount }}</span>
            </button>
            <div class="notification-menu" id="notificationMenu" role="dialog" aria-label="Notifications">
                <div class="notification-menu-header">
                    <div>
                        <h3>Notifications</h3>
                        <span>
                            {{ $notificationCount > 0 ? $notificationCount . ' nouvelle' . ($notificationCount > 1 ? 's' : '') : 'Aucune nouvelle notification' }}
                        </span>
                    </div>
                    <span>Aujourd'hui</span>
                </div>
                <div class="notification-list">
                    <div class="notification-item">
                        <div class="notification-copy">
                            <p>Aucune notification pour le moment.</p>
                        </div>
                    </div>
                </div>
                <div class="notification-menu-footer">
                    <a class="notification-link" href="#">Voir toutes les notifications</a>
                </div>
            </div>
        </div>

        {{-- Profil --}}
        <div class="profile-menu-wrapper">
            <button class="avatar-btn" id="profileToggle" type="button"
                    aria-label="Ouvrir le menu profil" aria-expanded="false" aria-controls="profileMenu">
                <div class="avatar large">{{ $userInitials }}</div>
            </button>
            <div class="profile-menu" id="profileMenu" role="dialog" aria-label="Menu profil">
                <div class="profile-menu-header">
                    <div class="avatar">{{ $userInitials }}</div>
                    <div>
                        <strong>{{ $userName }}</strong>
                        @if($userEmail)
                            <span>{{ $userEmail }}</span>
                        @endif
                    </div>
                </div>
                <div class="profile-menu-list">
                    <a class="profile-menu-item" href="{{ $profileRoute }}"><span>Mon profil</span></a>
                    <a class="profile-menu-item" href="{{ $settingsRoute }}"><span>Paramètres</span></a>
                    <form action="{{ $logoutRoute }}" method="POST">
                        @csrf
                        <a href="#" class="profile-menu-item"
                           onclick="event.preventDefault(); this.closest('form').submit();">
                            <span>Se déconnecter</span>
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
