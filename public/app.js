(function () {
    const storageKey = 'designali-theme';
    let theme = 'dark';

    try {
        const savedTheme = window.localStorage.getItem(storageKey);
        if (savedTheme === 'light' || savedTheme === 'dark') {
            theme = savedTheme;
        } else if (window.matchMedia('(prefers-color-scheme: light)').matches) {
            theme = 'light';
        }
    } catch (error) {
        if (window.matchMedia('(prefers-color-scheme: light)').matches) {
            theme = 'light';
        }
    }

    document.documentElement.setAttribute('data-theme', theme);
})();

document.addEventListener('DOMContentLoaded', () => {
    const themeStorageKey = 'designali-theme';
    const root = document.documentElement;
    const themeToggle = document.getElementById('themeToggle');
    const themeToggleLabel = document.getElementById('themeToggleLabel');

    function getCurrentTheme() {
        return root.getAttribute('data-theme') === 'light' ? 'light' : 'dark';
    }

    function updateThemeToggle(theme) {
        if (!themeToggle || !themeToggleLabel) {
            return;
        }

        const nextTheme = theme === 'light' ? 'dark' : 'light';
        const nextThemeLabel = nextTheme === 'light' ? 'Light mode' : 'Dark mode';

        themeToggle.setAttribute('aria-label', `Switch to ${nextTheme} mode`);
        themeToggle.setAttribute('aria-pressed', String(theme === 'light'));
        themeToggleLabel.textContent = nextThemeLabel;
    }

    function setTheme(theme) {
        root.setAttribute('data-theme', theme);
        updateThemeToggle(theme);

        try {
            window.localStorage.setItem(themeStorageKey, theme);
        } catch (error) {
            // Ignore storage failures and keep the in-memory theme.
        }
    }

    updateThemeToggle(getCurrentTheme());

    if (themeToggle) {
        themeToggle.addEventListener('click', () => {
            const nextTheme = getCurrentTheme() === 'light' ? 'dark' : 'light';
            setTheme(nextTheme);
        });
    }

    const notificationToggle = document.getElementById('notificationToggle');
    const notificationMenu = document.getElementById('notificationMenu');
    const profileToggle = document.getElementById('profileToggle');
    const profileMenu = document.getElementById('profileMenu');

    function setNotificationsOpen(isOpen) {
        if (!notificationMenu || !notificationToggle) {
            return;
        }

        notificationMenu.classList.toggle('open', isOpen);
        notificationToggle.setAttribute('aria-expanded', String(isOpen));
    }

    function setProfileOpen(isOpen) {
        if (!profileMenu || !profileToggle) {
            return;
        }

        profileMenu.classList.toggle('open', isOpen);
        profileToggle.setAttribute('aria-expanded', String(isOpen));
    }

    if (notificationToggle && notificationMenu) {
        notificationToggle.addEventListener('click', (event) => {
            event.stopPropagation();
            setProfileOpen(false);
            setNotificationsOpen(!notificationMenu.classList.contains('open'));
        });

        notificationMenu.addEventListener('click', (event) => {
            event.stopPropagation();
        });
    }

    if (profileToggle && profileMenu) {
        profileToggle.addEventListener('click', (event) => {
            event.stopPropagation();
            setNotificationsOpen(false);
            setProfileOpen(!profileMenu.classList.contains('open'));
        });

        profileMenu.addEventListener('click', (event) => {
            event.stopPropagation();
        });
    }

    if (notificationToggle || profileToggle) {
        document.addEventListener('click', () => {
            setNotificationsOpen(false);
            setProfileOpen(false);
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                setNotificationsOpen(false);
                setProfileOpen(false);
            }
        });
    }

    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');

    tabBtns.forEach((btn) => {
        btn.addEventListener('click', () => {
            const tabId = btn.dataset.tab;

            tabBtns.forEach((item) => item.classList.remove('active'));
            btn.classList.add('active');

            tabContents.forEach((content) => {
                content.classList.remove('active');
                if (content.id === `tab-${tabId}`) {
                    content.classList.add('active');
                }
            });
        });
    });

    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const mobileSidebar = document.getElementById('mobileSidebar');
    const mobileOverlay = document.getElementById('mobileOverlay');
    const mobileClose = document.getElementById('mobileClose');

    if (sidebarToggle && sidebar && mainContent) {
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('hidden');
            mainContent.classList.toggle('full');
        });
    }

    function openMobileMenu() {
        if (!mobileSidebar || !mobileOverlay) {
            return;
        }

        mobileSidebar.classList.add('active');
        mobileOverlay.classList.add('active');
    }

    function closeMobileMenu() {
        if (!mobileSidebar || !mobileOverlay) {
            return;
        }

        mobileSidebar.classList.remove('active');
        mobileOverlay.classList.remove('active');
    }

    if (mobileMenuToggle) {
        mobileMenuToggle.addEventListener('click', openMobileMenu);
    }

    if (mobileOverlay) {
        mobileOverlay.addEventListener('click', closeMobileMenu);
    }

    if (mobileClose) {
        mobileClose.addEventListener('click', closeMobileMenu);
    }

    const modalTriggers = document.querySelectorAll('[data-open-modal]');
    const modalClosers = document.querySelectorAll('[data-close-modal]');
    const modalBackdrops = document.querySelectorAll('.modal');
    const drawerTriggers = document.querySelectorAll('[data-open-drawer]');
    const drawerClosers = document.querySelectorAll('[data-close-drawer]');
    const drawers = document.querySelectorAll('.drawer');

    function closeModal(modal) {
        if (!modal) {
            return;
        }

        modal.classList.remove('open');
        modal.setAttribute('aria-hidden', 'true');
    }

    function openModal(modal) {
        if (!modal) {
            return;
        }

        modal.classList.add('open');
        modal.setAttribute('aria-hidden', 'false');
    }

    modalTriggers.forEach((trigger) => {
        trigger.addEventListener('click', () => {
            const modalId = trigger.getAttribute('data-open-modal');
            openModal(document.querySelector(`[data-modal="${modalId}"]`));
        });
    });

    modalClosers.forEach((closer) => {
        closer.addEventListener('click', () => {
            const modal = closer.closest('.modal');
            closeModal(modal);
        });
    });

    modalBackdrops.forEach((modal) => {
        modal.addEventListener('click', (event) => {
            if (event.target === modal) {
                closeModal(modal);
            }
        });
    });

    if (modalBackdrops.length > 0) {
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                modalBackdrops.forEach((modal) => closeModal(modal));
            }
        });
    }

    function closeDrawer(drawer) {
        if (!drawer) {
            return;
        }

        drawer.classList.remove('open');
        drawer.setAttribute('aria-hidden', 'true');
    }

    function openDrawer(drawer) {
        if (!drawer) {
            return;
        }

        drawer.classList.add('open');
        drawer.setAttribute('aria-hidden', 'false');
    }

    drawerTriggers.forEach((trigger) => {
        trigger.addEventListener('click', () => {
            const drawerId = trigger.getAttribute('data-open-drawer');
            openDrawer(document.querySelector(`[data-drawer="${drawerId}"]`));
        });
    });

    drawerClosers.forEach((closer) => {
        closer.addEventListener('click', () => {
            closeDrawer(closer.closest('.drawer'));
        });
    });

    drawers.forEach((drawer) => {
        drawer.addEventListener('click', (event) => {
            if (event.target === drawer) {
                closeDrawer(drawer);
            }
        });
    });

    if (drawers.length > 0) {
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                drawers.forEach((drawer) => closeDrawer(drawer));
            }
        });
    }
});
