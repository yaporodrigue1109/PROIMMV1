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
        const nextThemeLabel = nextTheme === 'light' ? 'Mode clair' : 'Mode sombre';

        themeToggle.setAttribute('aria-label', nextTheme === 'light' ? 'Passer en mode clair' : 'Passer en mode sombre');
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

    const filterDropdowns = document.querySelectorAll('.filter-dropdown');

    function closeFilterDropdown(dropdown) {
        if (!dropdown) {
            return;
        }

        const toggle = dropdown.querySelector('.filter-btn');
        dropdown.classList.remove('open');

        if (toggle) {
            toggle.setAttribute('aria-expanded', 'false');
        }
    }

    function openFilterDropdown(dropdown) {
        if (!dropdown) {
            return;
        }

        const toggle = dropdown.querySelector('.filter-btn');
        filterDropdowns.forEach((item) => {
            if (item !== dropdown) {
                closeFilterDropdown(item);
            }
        });

        dropdown.classList.add('open');

        if (toggle) {
            toggle.setAttribute('aria-expanded', 'true');
        }
    }

    if (filterDropdowns.length > 0) {
        filterDropdowns.forEach((dropdown) => {
            const toggle = dropdown.querySelector('.filter-btn');
            const label = toggle ? toggle.querySelector('span') : null;
            const options = dropdown.querySelectorAll('.filter-option');

            if (toggle) {
                toggle.addEventListener('click', (event) => {
                    event.stopPropagation();
                    const isOpen = dropdown.classList.contains('open');

                    if (isOpen) {
                        closeFilterDropdown(dropdown);
                    } else {
                        openFilterDropdown(dropdown);
                    }
                });
            }

            options.forEach((option) => {
                option.addEventListener('click', (event) => {
                    event.stopPropagation();
                    options.forEach((item) => item.classList.remove('is-selected'));
                    option.classList.add('is-selected');

                    if (label) {
                        label.textContent = option.textContent.trim();
                    }

                    closeFilterDropdown(dropdown);
                });
            });
        });

        document.addEventListener('click', () => {
            filterDropdowns.forEach((dropdown) => closeFilterDropdown(dropdown));
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                filterDropdowns.forEach((dropdown) => closeFilterDropdown(dropdown));
            }
        });
    }

    document.addEventListener('click', function (event) {
        const toggle = event.target.closest('.ui-dropdown-toggle');

        if (toggle) {
            const dropdown = toggle.closest('.ui-dropdown');

            document.querySelectorAll('.ui-dropdown').forEach(item => {
                if (item !== dropdown) item.classList.remove('open');
            });

            dropdown.classList.toggle('open');
            return;
        }

        document.querySelectorAll('.ui-dropdown').forEach(item => {
            item.classList.remove('open');
        });
    });

    document.querySelectorAll('.ui-select-dropdown .ui-dropdown-item').forEach(item => {
        item.addEventListener('click', function () {
            const dropdown = this.closest('.ui-select-dropdown');
            const selectId = dropdown.dataset.selectTarget;
            const select = document.getElementById(selectId);
            const label = dropdown.querySelector('.ui-dropdown-toggle span');

            select.value = this.dataset.value;
            label.textContent = this.textContent.trim();

            dropdown.querySelectorAll('.ui-dropdown-item').forEach(option => {
                option.classList.remove('is-selected');
            });

            this.classList.add('is-selected');
            dropdown.classList.remove('open');

            select.dispatchEvent(new Event('change', { bubbles: true }));
        });
    });

    // ==================== DATEPICKER ====================
    // Définir les formatters et fonctions utilitaires D'ABORD
    const dateFormatter = new Intl.DateTimeFormat('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
    });
    const monthFormatter = new Intl.DateTimeFormat('fr-FR', {
        month: 'long',
        year: 'numeric',
    });

    function parseIsoDate(value) {
        if (!value) {
            return null;
        }

        const parts = value.split('-').map(Number);
        if (parts.length !== 3 || parts.some(isNaN)) {
            return null;
        }

        return new Date(parts[0], parts[1] - 1, parts[2]);
    }

    function formatIsoDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');

        return `${year}-${month}-${day}`;
    }

    function updateDateLabel(picker) {
        const input = picker.querySelector('.ui-date-native');
        const labelSpan = picker.querySelector('[data-date-label]');
        const selectedDate = parseIsoDate(input.value);
        if (selectedDate) {
            labelSpan.textContent = dateFormatter.format(selectedDate);
        } else {
            labelSpan.textContent = 'Sélectionner une date';
        }
    }

    function renderDatePicker(picker) {
        const input = picker.querySelector('.ui-date-native');
        const grid = picker.querySelector('[data-date-grid]');
        const monthLabel = picker.querySelector('[data-date-month]');
        // Utiliser la date mémorisée ou la date sélectionnée ou la date du jour
        let viewed = picker._viewedDate;
        if (!viewed) {
            const selectedDate = parseIsoDate(input.value);
            viewed = selectedDate || new Date();
        }

        const year = viewed.getFullYear();
        const month = viewed.getMonth();
        const firstDayOfMonth = new Date(year, month, 1);
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        // Ajustement pour que la semaine commence le lundi (getDay() -> 0 pour dimanche)
        const startDayOfWeek = (firstDayOfMonth.getDay() + 6) % 7;

        // Mettre à jour la date de référence dans le picker
        picker._viewedDate = new Date(year, month, 1);

        // Formater et afficher le mois et l'année
        monthLabel.textContent = monthFormatter.format(picker._viewedDate);

        // Vider la grille
        grid.innerHTML = '';

        // Ajouter les cellules vides pour les jours précédant le premier du mois
        for (let i = 0; i < startDayOfWeek; i++) {
            const emptyCell = document.createElement('span');
            emptyCell.className = 'ui-date-day is-empty';
            grid.appendChild(emptyCell);
        }

        const today = new Date();
        const todayIso = formatIsoDate(today);
        const selectedIso = input.value;

        // Générer les jours du mois
        for (let day = 1; day <= daysInMonth; day++) {
            const currentDate = new Date(year, month, day);
            const isoDate = formatIsoDate(currentDate);
            const dayButton = document.createElement('button');
            dayButton.type = 'button';
            dayButton.className = 'ui-date-day';
            dayButton.textContent = day;
            dayButton.dataset.dateValue = isoDate;

            if (isoDate === selectedIso) {
                dayButton.classList.add('is-selected');
            }
            if (isoDate === todayIso) {
                dayButton.classList.add('is-today');
            }

            dayButton.addEventListener('click', () => {
                input.value = isoDate;
                updateDateLabel(picker);
                renderDatePicker(picker); // Re-render pour mettre à jour la sélection
                picker.classList.remove('open');
                // Déclencher un événement 'change' pour que d'autres scripts réagissent
                input.dispatchEvent(new Event('change', { bubbles: true }));
            });

            grid.appendChild(dayButton);
        }
    }

    function closeDatePickers(except = null) {
        const datePickers = document.querySelectorAll('[data-date-picker]');
        datePickers.forEach((picker) => {
            if (picker !== except) {
                picker.classList.remove('open');
            }
        });
    }

    const datePickers = document.querySelectorAll('[data-date-picker]');

    // Initialisation de chaque datepicker
    datePickers.forEach(picker => {
        const input = picker.querySelector('.ui-date-native');
        const toggleButton = picker.querySelector('.ui-date-toggle');
        const prevMonthButton = picker.querySelector('[data-date-prev]');
        const nextMonthButton = picker.querySelector('[data-date-next]');
        const prevYearButton = picker.querySelector('[data-year-prev]');
        const nextYearButton = picker.querySelector('[data-year-next]');

        // Initialiser la vue sur la date sélectionnée ou aujourd'hui
        const initialDate = parseIsoDate(input.value) || new Date();
        picker._viewedDate = new Date(initialDate.getFullYear(), initialDate.getMonth(), 1);

        // Mettre à jour l'affichage initial
        updateDateLabel(picker);
        renderDatePicker(picker);

        // Écouteur pour le bouton d'ouverture/fermeture
        toggleButton?.addEventListener('click', (e) => {
            e.stopPropagation();
            // Fermer tous les autres datepickers
            document.querySelectorAll('[data-date-picker]').forEach(p => {
                if (p !== picker) p.classList.remove('open');
            });
            picker.classList.toggle('open');
            // Re-rendre au cas où la vue serait obsolète
            renderDatePicker(picker);
        });

        // Navigation mois
        prevMonthButton?.addEventListener('click', (e) => {
            e.stopPropagation();
            const currentView = picker._viewedDate || new Date();
            picker._viewedDate = new Date(currentView.getFullYear(), currentView.getMonth() - 1, 1);
            renderDatePicker(picker);
        });

        nextMonthButton?.addEventListener('click', (e) => {
            e.stopPropagation();
            const currentView = picker._viewedDate || new Date();
            picker._viewedDate = new Date(currentView.getFullYear(), currentView.getMonth() + 1, 1);
            renderDatePicker(picker);
        });

        // Navigation année (NOUVEAU)
        prevYearButton?.addEventListener('click', (e) => {
            e.stopPropagation();
            const currentView = picker._viewedDate || new Date();
            picker._viewedDate = new Date(currentView.getFullYear() - 1, currentView.getMonth(), 1);
            renderDatePicker(picker);
        });

        nextYearButton?.addEventListener('click', (e) => {
            e.stopPropagation();
            const currentView = picker._viewedDate || new Date();
            picker._viewedDate = new Date(currentView.getFullYear() + 1, currentView.getMonth(), 1);
            renderDatePicker(picker);
        });

        // Empêcher la propagation des clics à l'intérieur du panneau
        picker.querySelector('[data-date-panel]')?.addEventListener('click', (e) => e.stopPropagation());

        // Mettre à jour si la valeur change via autre chose (ex: JS)
        input?.addEventListener('change', () => {
            updateDateLabel(picker);
            // Réinitialiser la vue sur la nouvelle date sélectionnée
            const newDate = parseIsoDate(input.value);
            if (newDate) {
                picker._viewedDate = new Date(newDate.getFullYear(), newDate.getMonth(), 1);
                renderDatePicker(picker);
            }
        });
    });

    // Fermer tous les datepickers si on clique ailleurs
    document.addEventListener('click', () => {
        datePickers.forEach(picker => picker.classList.remove('open'));
    });

    // Fermer avec la touche Echap
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            datePickers.forEach(picker => picker.classList.remove('open'));
        }
    });

    // Rendre la fonction syncUiDatePicker disponible globalement si besoin
    window.syncUiDatePicker = function(inputOrId) {
        const input = typeof inputOrId === 'string' ? document.getElementById(inputOrId) : inputOrId;
        if (!input) return;
        const picker = input.closest('[data-date-picker]');
        if (!picker) return;
        updateDateLabel(picker);
        const newDate = parseIsoDate(input.value);
        if (newDate) {
            picker._viewedDate = new Date(newDate.getFullYear(), newDate.getMonth(), 1);
            renderDatePicker(picker);
        }
    };

    // ==================== FIN DATEPICKER ====================

    function syncDataDrivenStyles(root = document) {
        root.querySelectorAll('[data-progress]').forEach((element) => {
            const value = Number.parseFloat(element.dataset.progress || '0');
            element.style.width = `${Math.max(0, Math.min(value, 100))}%`;
        });

        root.querySelectorAll('[data-bg]').forEach((element) => {
            element.style.background = element.dataset.bg || '';
        });
    }

    window.syncDataDrivenStyles = syncDataDrivenStyles;
    syncDataDrivenStyles();

    function filterRows({ rowsSelector, searchSelector, activeFilterSelector = '.filter-pill.active', statusAttribute = 'filterStatus', textMatcher }) {
        const activeFilter = document.querySelector(activeFilterSelector)?.dataset.filter ?? 'tous';
        const query = (document.querySelector(searchSelector)?.value ?? '').toLowerCase();

        document.querySelectorAll(rowsSelector).forEach((row) => {
            const status = row.dataset[statusAttribute] ?? row.dataset.status ?? '';
            const matchesFilter = activeFilter === 'tous' || status === activeFilter;
            const matchesSearch = textMatcher ? textMatcher(row, query) : row.textContent.toLowerCase().includes(query);
            row.style.display = matchesFilter && matchesSearch ? '' : 'none';
        });
    }

    function bindFilterPills({ pillsSelector, rowsSelector, searchSelector, statusAttribute = 'filterStatus', activeClass = 'active', textMatcher }) {
        const pills = document.querySelectorAll(pillsSelector);
        if (!pills.length || !document.querySelector(rowsSelector)) {
            return;
        }

        const update = () => filterRows({
            rowsSelector,
            searchSelector,
            activeFilterSelector: `${pillsSelector}.${activeClass}`,
            statusAttribute,
            textMatcher,
        });

        pills.forEach((pill) => {
            pill.addEventListener('click', function () {
                pills.forEach((item) => item.classList.remove(activeClass));
                this.classList.add(activeClass);
                update();
            });
        });

        document.querySelector(searchSelector)?.addEventListener('input', update);
    }

    bindFilterPills({
        pillsSelector: '.table-toolbar .filter-pills .filter-pill',
        rowsSelector: '.filterable-row',
        searchSelector: '#billing-search, #proprietaire-search',
    });

    bindFilterPills({
        pillsSelector: '.split-list-header .filter-pills .filter-pill',
        rowsSelector: '.item-row',
        searchSelector: '#support-search',
        statusAttribute: 'status',
    });

    function bindPanelTabs(tabSelector, panelSelector, panelPrefix, activeClass = 'is-active') {
        const tabs = document.querySelectorAll(tabSelector);
        if (!tabs.length) {
            return;
        }

        tabs.forEach((tab) => {
            tab.addEventListener('click', function () {
                tabs.forEach((item) => item.classList.remove(activeClass));
                document.querySelectorAll(panelSelector).forEach((panel) => panel.classList.remove(activeClass));
                this.classList.add(activeClass);
                document.getElementById(panelPrefix + this.dataset.tab)?.classList.add(activeClass);
            });
        });
    }

    bindPanelTabs('.rp-tab', '.rp-panel', 'panel-');

    document.querySelectorAll('.rp-period-pill').forEach((pill) => {
        pill.addEventListener('click', function () {
            document.querySelectorAll('.rp-period-pill').forEach((item) => item.classList.remove('is-active'));
            this.classList.add('is-active');
        });
    });

    function filterStatsTable(tableId, search, status) {
        document.querySelectorAll(`#${tableId} tbody tr`).forEach((row) => {
            const matchesSearch = !search || row.dataset.search?.includes(search.toLowerCase());
            const matchesStatus = !status || row.dataset.statut === status;
            row.style.display = matchesSearch && matchesStatus ? '' : 'none';
        });
    }

    window.applyAboFilter = function applyAboFilter(element, value) {
        document.querySelectorAll('#panel-abonnements .ui-dropdown-item').forEach((item) => {
            item.classList.toggle('is-selected', item === element || item.dataset.value === value);
        });
        filterStatsTable('table-abo', document.getElementById('search-abo')?.value || '', value);
    };

    window.applyPayFilter = function applyPayFilter(element, value) {
        document.querySelectorAll('#panel-paiements .ui-dropdown-item').forEach((item) => {
            item.classList.toggle('is-selected', item === element || item.dataset.value === value);
        });
        filterStatsTable('table-pay', document.getElementById('search-pay')?.value || '', value);
    };

    ['abo', 'pay'].forEach((key) => {
        const search = document.getElementById(`search-${key}`);
        const filter = document.getElementById(`filter-${key}`);
        if (!search || !filter) {
            return;
        }

        search.addEventListener('input', () => filterStatsTable(`table-${key}`, search.value, filter.value));
        filter.addEventListener('change', () => filterStatsTable(`table-${key}`, search.value, filter.value));
    });

    const propNavItems = document.querySelectorAll('.prop-nav-item[data-panel]');
    const propPanels = document.querySelectorAll('.prop-panel');
    propNavItems.forEach((item) => {
        item.addEventListener('click', (event) => {
            event.preventDefault();
            propNavItems.forEach((navItem) => navItem.classList.remove('active'));
            propPanels.forEach((panel) => panel.classList.remove('active'));
            item.classList.add('active');
            document.getElementById(`panel-${item.dataset.panel}`)?.classList.add('active');
        });
    });

    document.querySelector('#property-search')?.addEventListener('input', function () {
        const query = this.value.toLowerCase();
        document.querySelectorAll('#properties-table tbody tr').forEach((row) => {
            row.style.display = row.dataset.search?.includes(query) ? '' : 'none';
        });
    });

    document.querySelectorAll('.wysiwyg').forEach((wrapper) => {
        const target = document.getElementById(wrapper.dataset.editorTarget);
        const editor = wrapper.querySelector('.wysiwyg-editor');
        if (!target || !editor) {
            return;
        }

        function syncEditor() {
            target.value = editor.innerHTML.trim();
        }

        wrapper.querySelectorAll('[data-command]').forEach((button) => {
            button.addEventListener('click', () => {
                editor.focus();
                document.execCommand(button.dataset.command, false, button.dataset.value || null);
                syncEditor();
            });
        });

        editor.addEventListener('input', syncEditor);
        editor.closest('form')?.addEventListener('submit', syncEditor);
        syncEditor();
    });

    const representantCheckbox = document.getElementById('has_representant');
    const representantFields = document.getElementById('representant-fields');
    function toggleRepresentantFields() {
        if (!representantCheckbox || !representantFields) {
            return;
        }
        representantFields.classList.toggle('section-hidden', !representantCheckbox.checked);
    }
    representantCheckbox?.addEventListener('change', toggleRepresentantFields);
    toggleRepresentantFields();

    window.selectTicket = function selectTicket(element) {
        document.querySelectorAll('.item-row').forEach((row) => row.classList.remove('selected'));
        element?.classList.add('selected');
    };

    window.confirmDelete = function confirmDelete(id, nom) {
        if (!confirm(`Êtes-vous sûr de vouloir supprimer le propriétaire "${nom}" ? Cette action est irréversible.`)) {
            return;
        }

        const form = document.createElement('form');
        const token = document.querySelector('input[name="_token"]')?.value;
        form.method = 'POST';
        form.action = `${window.location.origin}/admin/proprietaires/${id}`;
        form.innerHTML = `
            ${token ? `<input type="hidden" name="_token" value="${token}">` : ''}
            <input type="hidden" name="_method" value="DELETE">
        `;
        document.body.appendChild(form);
        form.submit();
    };

    window.getRequest = function getRequest(route, id, type) {
        const onSuccess = (data) => {
            if (type === 'select') {
                const target = document.getElementById(id);
                if (target) {
                    target.innerHTML = data.select_tag || '';
                }
            }
        };

        if (window.jQuery) {
            window.jQuery.get({ url: route, dataType: 'json', success: onSuccess });
            return;
        }

        fetch(route, { headers: { Accept: 'application/json' } })
            .then((response) => response.json())
            .then(onSuccess);
    };

    window.calculerDateFin = function calculerDateFin() {
        const dateDebut = document.getElementById('abonnement_start')?.value;
        const dureeMois = Number.parseInt(document.getElementById('duree_mois')?.value || '0', 10);
        const dateFinInput = document.getElementById('abonnement_end');
        if (!dateFinInput) {
            return;
        }

        if (!dateDebut || !dureeMois) {
            dateFinInput.value = '';
            return;
        }

        const fin = new Date(dateDebut);
        fin.setMonth(fin.getMonth() + dureeMois);
        dateFinInput.value = `${fin.getFullYear()}-${String(fin.getMonth() + 1).padStart(2, '0')}-${String(fin.getDate()).padStart(2, '0')}`;
        dateFinInput.dispatchEvent(new Event('change', { bubbles: true }));
    };

    window.calculerMontantTotal = function calculerMontantTotal() {
        const dureeMois = Number.parseInt(document.getElementById('duree_mois')?.value || '0', 10);
        if (!dureeMois) {
            return;
        }

        document.getElementById('duree_affichee').innerText = `${dureeMois} mois`;
        document.getElementById('duree_multiplicateur').innerText = dureeMois;
        document.getElementById('duree_mois_selected').value = dureeMois;

        const baseMensuel = Number.parseInt(document.getElementById('prix_base_mensuel')?.value || '50000', 10);
        const montantBaseTotal = baseMensuel * dureeMois;
        let totalOptionsMensuel = 0;
        document.querySelectorAll('input[name="options[]"]:checked').forEach((checkbox) => {
            totalOptionsMensuel += Number.parseInt(checkbox.dataset.prixMensuel || '0', 10);
        });

        const totalOptionsTotal = totalOptionsMensuel * dureeMois;
        const montantTotal = montantBaseTotal + totalOptionsTotal;
        const format = (value) => new Intl.NumberFormat('fr-FR').format(value);

        document.getElementById('montant_base_display').innerText = `${format(montantBaseTotal)} FCFA`;
        document.getElementById('total_options_display').innerHTML = `${format(totalOptionsTotal)} FCFA <span class="module-price-unit">(${format(totalOptionsMensuel)} FCFA/mois)</span>`;
        document.getElementById('montant_total_display').innerHTML = `${format(montantTotal)} FCFA`;
        document.getElementById('montant_total_input').value = montantTotal;
        document.getElementById('montant_base_total_input').value = montantBaseTotal;
    };

    window.toggleResponsableMode = function toggleResponsableMode() {
        const mode = document.querySelector('input[name="responsable_mode"]:checked')?.value;
        const existingSection = document.getElementById('existing-responsable-section');
        const newSection = document.getElementById('new-responsable-section');
        if (!mode || !existingSection || !newSection) {
            return;
        }

        const useExisting = mode === 'existing';
        existingSection.classList.toggle('section-hidden', !useExisting);
        newSection.classList.toggle('section-hidden', useExisting);

        document.querySelectorAll('#new-responsable-section input, #new-responsable-section textarea, #new-responsable-section select').forEach((field) => {
            field.disabled = useExisting;
        });
        document.querySelectorAll('#existing-responsable-section input, #existing-responsable-section select').forEach((field) => {
            field.disabled = !useExisting;
        });
    };

    window.toggleAbonnementSection = function toggleAbonnementSection() {
        const statut = document.getElementById('statut')?.value;
        const abonnementSection = document.getElementById('abonnement-section');
        if (!abonnementSection) {
            return;
        }
        abonnementSection.classList.toggle('section-hidden', statut !== 'active');
        if (statut === 'active') {
            window.calculerMontantTotal();
        }
    };

    if (document.getElementById('abonnement-section')) {
        window.toggleResponsableMode();
        window.toggleAbonnementSection();
        document.getElementById('statut')?.addEventListener('change', window.toggleAbonnementSection);
        document.getElementById('duree_mois')?.addEventListener('change', () => {
            window.calculerDateFin();
            window.calculerMontantTotal();
        });
        document.getElementById('abonnement_start')?.addEventListener('change', window.calculerDateFin);
    }

    document.querySelectorAll('.period-btn').forEach((button) => {
        button.addEventListener('click', function () {
            document.querySelectorAll('.period-btn').forEach((item) => item.classList.remove('active'));
            this.classList.add('active');
        });
    });

    function isDarkTheme() {
        return document.documentElement.getAttribute('data-theme') !== 'light';
    }

    function chartMutedColor() {
        return isDarkTheme() ? 'rgba(255,255,255,.35)' : 'rgba(0,0,0,.35)';
    }

    document.querySelectorAll('[data-chart="agency-donut"]').forEach((canvas) => {
        const context = canvas.getContext('2d');
        const vente = Number(canvas.dataset.vente || 0);
        const location = Number(canvas.dataset.location || 0);
        const total = vente + location || 1;

        function drawDonut() {
            const size = 160;
            canvas.width = size;
            canvas.height = size;
            const cx = size / 2;
            const cy = size / 2;
            const outer = 66;
            const inner = 46;
            const gap = 0.04;
            context.clearRect(0, 0, size, size);

            if (!vente && !location) {
                context.beginPath();
                context.arc(cx, cy, outer, 0, Math.PI * 2);
                context.arc(cx, cy, inner, 0, Math.PI * 2, true);
                context.fillStyle = '#e5e7eb';
                context.fill();
            } else {
                let start = -Math.PI / 2;
                [{ value: location, color: '#22c55e' }, { value: vente, color: '#f59e0b' }].forEach((segment) => {
                    if (!segment.value) {
                        return;
                    }
                    const end = start + (segment.value / total) * Math.PI * 2;
                    context.beginPath();
                    context.arc(cx, cy, outer, start + gap, end - gap);
                    context.arc(cx, cy, inner, end - gap, start + gap, true);
                    context.closePath();
                    context.fillStyle = segment.color;
                    context.fill();
                    start = end;
                });
            }

            context.beginPath();
            context.arc(cx, cy, inner - 2, 0, Math.PI * 2);
            context.fillStyle = isDarkTheme() ? '#1a1a1a' : '#ffffff';
            context.fill();
        }

        drawDonut();
        new MutationObserver(drawDonut).observe(document.documentElement, { attributes: true, attributeFilter: ['data-theme'] });
        window.addEventListener('resize', drawDonut);
    });

    document.querySelectorAll('[data-chart="agency-revenue"]').forEach((canvas) => {
        if (!window.Chart) {
            return;
        }

        const labels = JSON.parse(canvas.dataset.labels || '[]');
        const data = JSON.parse(canvas.dataset.values || '[]');
        const primary = getComputedStyle(document.documentElement).getPropertyValue('--primary').trim() || '#76c300';

        new window.Chart(canvas, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: 'Loyers encaissés',
                    data,
                    backgroundColor: `${primary}22`,
                    borderColor: primary,
                    borderWidth: 1.5,
                    borderRadius: 6,
                    borderSkipped: false,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (context) => ` ${Number(context.parsed.y).toLocaleString('fr-FR')} FCFA`,
                        },
                    },
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: chartMutedColor(), font: { size: 11 } },
                        border: { display: false },
                    },
                    y: {
                        grid: { color: isDarkTheme() ? 'rgba(255,255,255,.05)' : 'rgba(0,0,0,.05)' },
                        ticks: {
                            color: chartMutedColor(),
                            font: { size: 11 },
                            callback: (value) => value >= 1000000 ? `${(value / 1000000).toFixed(1)}M` : value >= 1000 ? `${(value / 1000).toFixed(0)}k` : value,
                        },
                        border: { display: false },
                    },
                },
            },
        });
    });

    document.querySelectorAll('[data-chart="admin-revenue"]').forEach((canvas) => {
        if (!window.Chart) {
            return;
        }

        const labels = JSON.parse(canvas.dataset.labels || '[]');
        const data = JSON.parse(canvas.dataset.values || '[]');

        new window.Chart(canvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    data,
                    backgroundColor: 'rgba(118,195,0,.72)',
                    borderRadius: 6,
                    borderSkipped: false,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (context) => ` ${context.raw.toLocaleString('fr-FR')} FCFA`,
                        },
                    },
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: isDarkTheme() ? '#a1a1aa' : '#46657f', font: { size: 11, weight: '600' } },
                        border: { display: false },
                    },
                    y: {
                        grid: { color: isDarkTheme() ? 'rgba(255,255,255,.06)' : 'rgba(15,23,42,.07)' },
                        ticks: {
                            color: isDarkTheme() ? '#a1a1aa' : '#46657f',
                            font: { size: 11 },
                            callback: (value) => value === 0 ? '0' : `${value / 1000}k`,
                        },
                        border: { display: false },
                    },
                },
            },
        });
    });

    document.querySelectorAll('[data-chart="stats-revenue"]').forEach((canvas) => {
        if (!window.Chart) {
            return;
        }

        const styles = getComputedStyle(document.documentElement);
        const primary = styles.getPropertyValue('--primary').trim() || '#76c300';
        const muted = styles.getPropertyValue('--muted-foreground').trim() || '#a1a1aa';
        const border = styles.getPropertyValue('--border').trim() || '#27272a';

        new window.Chart(canvas, {
            type: 'bar',
            data: {
                labels: JSON.parse(canvas.dataset.labels || '[]'),
                datasets: [{
                    data: JSON.parse(canvas.dataset.values || '[]'),
                    backgroundColor: `${primary}b0`,
                    borderColor: primary,
                    borderWidth: 1.5,
                    borderRadius: 6,
                    borderSkipped: false,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: (context) => ` ${context.raw.toLocaleString('fr-FR')} FCFA` } },
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: muted, font: { size: 11, weight: '600', family: 'Outfit' } },
                        border: { display: false },
                    },
                    y: {
                        grid: { color: `${border}55` },
                        ticks: { color: muted, font: { size: 11 }, callback: (value) => value === 0 ? '0' : `${value / 1000}k` },
                        border: { display: false },
                    },
                },
            },
        });
    });

    document.querySelectorAll('[data-chart="stats-agencies"]').forEach((canvas) => {
        if (!window.Chart) {
            return;
        }

        const styles = getComputedStyle(document.documentElement);
        const primary = styles.getPropertyValue('--primary').trim() || '#76c300';
        const blue = styles.getPropertyValue('--accent-blue').trim() || '#005499';
        const blueSoft = styles.getPropertyValue('--accent-blue-soft').trim() || '#2c6393';

        new window.Chart(canvas, {
            type: 'doughnut',
            data: {
                labels: JSON.parse(canvas.dataset.labels || '[]'),
                datasets: [{
                    data: JSON.parse(canvas.dataset.values || '[]'),
                    backgroundColor: [primary, blue, blueSoft, '#a1a1aa', '#52525b'],
                    borderWidth: 0,
                    borderRadius: 4,
                    hoverOffset: 6,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '68%',
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: (context) => ` ${context.raw.toLocaleString('fr-FR')} FCFA` } },
                },
            },
        });
    });

    document.getElementById('btn-csv')?.addEventListener('click', function () {
        const rows = JSON.parse(this.dataset.csvRows || '[]');
        let csv = 'Période,Abonnements,Montant FCFA\n';
        rows.forEach((row) => {
            csv += `${row.mois_full},${row.abo},${row.montant}\n`;
        });
        const link = document.createElement('a');
        link.href = `data:text/csv;charset=utf-8,${encodeURIComponent(csv)}`;
        link.download = 'rapport_revenus.csv';
        link.click();
    });

    window.showToast = window.showToast || function showToast(message, type = 'success', duration = 3500) {
        const container = document.getElementById('toast-container');
        if (!container) {
            return;
        }

        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.setAttribute('role', 'status');
        const icon = type === 'success'
            ? '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>'
            : '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>';
        toast.innerHTML = `${icon}<span>${message}</span>`;
        container.appendChild(toast);
        setTimeout(() => {
            toast.classList.add('out');
            toast.addEventListener('animationend', () => toast.remove(), { once: true });
        }, duration);
    };

    function initSortableMenus() {
        const tbody = document.getElementById('sortableMenus');
        const toggleDragBtn = document.getElementById('toggleDragBtn');
        if (!tbody || !toggleDragBtn || !window.Sortable) {
            return;
        }

        let dragEnabled = false;

        function updateOrderAndCounters() {
            let parentCounter = 0;
            const subCounter = {};

            document.querySelectorAll('#sortableMenus tr').forEach((row) => {
                const type = row.dataset.type;

                if (type === 'parent') {
                    parentCounter++;
                    const parentId = row.dataset.parentId;
                    subCounter[parentId] = 0;
                    row.querySelector('.order-number').innerText = parentCounter;
                    const submenuCount = document.querySelectorAll(`tr.submenu-row[data-parent-id="${parentId}"]`).length;
                    row.querySelector('.submenu-count').innerText = submenuCount;
                }

                if (type === 'submenu') {
                    const parentId = row.dataset.parentId;
                    subCounter[parentId] = (subCounter[parentId] || 0) + 1;
                    row.querySelector('.order-number').innerText = `${parentCounter}.${subCounter[parentId]}`;
                }
            });
        }

        function toggleSubmenus(parentRow) {
            if (parentRow.dataset.hasSubmenus !== 'true') {
                return;
            }

            const parentId = parentRow.dataset.parentId;
            const submenuRows = document.querySelectorAll(`.submenu-row.parent-${parentId}`);
            if (!submenuRows.length) {
                return;
            }

            const isCollapsed = submenuRows[0].classList.contains('collapsed');
            submenuRows.forEach((row) => row.classList.toggle('collapsed', !isCollapsed));
            localStorage.setItem(`collapse-parent-${parentId}`, isCollapsed ? 'expanded' : 'collapsed');
            parentRow.querySelector('.submenu-count').innerText = submenuRows.length;
            updateOrderAndCounters();
        }

        document.querySelectorAll('tr[data-type="parent"][data-has-submenus="true"]').forEach((row) => {
            row.classList.add('is-clickable');
            const parentId = row.dataset.parentId;
            const savedState = localStorage.getItem(`collapse-parent-${parentId}`);
            const submenuRows = document.querySelectorAll(`.submenu-row.parent-${parentId}`);
            submenuRows.forEach((submenu) => submenu.classList.toggle('collapsed', savedState === 'collapsed'));
            row.querySelector('.submenu-count').innerText = submenuRows.length;

            row.addEventListener('click', function (event) {
                if (dragEnabled || event.target.closest('button') || event.target.closest('.drag-handle')) {
                    return;
                }
                toggleSubmenus(this);
            });
        });

        const sortable = new window.Sortable(tbody, {
            animation: 150,
            handle: '.drag-handle',
            ghostClass: 'dragging-row',
            disabled: true,
            onMove(event) {
                const draggedLevel = event.dragged?.dataset.level;
                const targetLevel = event.related?.dataset.level;
                return !((draggedLevel === 'submenu' && targetLevel === 'parent') || (draggedLevel === 'parent' && targetLevel === 'submenu'));
            },
            onEnd(event) {
                const dragged = event.item;
                const oldParentId = dragged.dataset.parentId;
                let previous = dragged.previousElementSibling;
                let newParent = null;

                while (previous) {
                    if (previous.dataset.type === 'parent') {
                        newParent = previous;
                        break;
                    }
                    previous = previous.previousElementSibling;
                }

                if (dragged.dataset.level === 'submenu' && newParent) {
                    const newParentId = newParent.dataset.parentId;
                    if (oldParentId !== newParentId) {
                        dragged.dataset.parentId = newParentId;
                        dragged.classList.remove(`parent-${oldParentId}`);
                        dragged.classList.add(`parent-${newParentId}`);
                        dragged.classList.toggle('collapsed', localStorage.getItem(`collapse-parent-${newParentId}`) === 'collapsed');
                    }
                }

                updateOrderAndCounters();
            },
        });

        toggleDragBtn.addEventListener('click', function () {
            dragEnabled = !dragEnabled;
            sortable.option('disabled', !dragEnabled);
            document.body.classList.toggle('drag-mode-enabled', dragEnabled);
            this.classList.toggle('drag-active', dragEnabled);
            this.textContent = dragEnabled ? 'Désactiver le déplacement' : 'Activer le déplacement';
        });

        updateOrderAndCounters();
    }

    initSortableMenus();

    window.selectMembre = function selectMembre(row) {
        document.querySelectorAll('.agency-row').forEach((item) => {
            item.classList.remove('selected');
            item.setAttribute('aria-selected', 'false');
        });
        row.classList.add('selected');
        row.setAttribute('aria-selected', 'true');

        const mappings = {
            'personnel-detail-matricule': 'matricule',
            'personnel-detail-name': 'name',
            'personnel-detail-name-card': 'name',
            'personnel-detail-initials': 'initials',
            'personnel-detail-email': 'email',
            'personnel-detail-phone': 'phone',
            'personnel-detail-poste': 'poste',
            'personnel-detail-agence': 'agence',
            'personnel-detail-departement': 'departement',
            'personnel-detail-superviseur': 'superviseur',
            'personnel-detail-adresse': 'adresse',
            'personnel-detail-date-embauche': 'dateEmbauche',
            'personnel-detail-salaire': 'salaire',
        };

        Object.entries(mappings).forEach(([id, key]) => {
            const element = document.getElementById(id);
            if (element) {
                element.textContent = row.dataset[key] || (key === 'adresse' ? 'Adresse non définie' : 'Non défini');
            }
        });

        const status = document.getElementById('personnel-detail-status');
        const role = document.getElementById('personnel-detail-role');
        if (status) {
            status.className = `badge ${row.dataset.statusClass}`;
            status.textContent = row.dataset.statusLabel;
        }
        if (role) {
            role.className = `badge ${row.dataset.roleClass}`;
            role.textContent = row.dataset.roleLabel;
        }

        document.getElementById('personnel-show-link').href = row.dataset.showUrl;
        document.getElementById('personnel-edit-link').href = row.dataset.editUrl;

        const toggleBtn = document.getElementById('personnel-toggle-status');
        if (toggleBtn) {
            toggleBtn.dataset.currentStatus = row.dataset.status;
            toggleBtn.dataset.toggleUrl = row.dataset.toggleUrl;
            toggleBtn.textContent = row.dataset.status === 'actif' ? 'Désactiver' : 'Activer';
        }

        let permissions = [];
        try {
            permissions = JSON.parse(row.dataset.permissions || '[]');
        } catch (error) {
            permissions = [];
        }

        const permissionsContainer = document.getElementById('personnel-detail-permissions');
        if (permissionsContainer) {
            permissionsContainer.innerHTML = permissions.length ? '' : '<span>Aucune permission spécifique</span>';
            permissions.forEach((permission) => {
                const badge = document.createElement('span');
                badge.textContent = permission;
                permissionsContainer.appendChild(badge);
            });
        }

        window.openMobileDetail();
    };

    window.openMobileDetail = function openMobileDetail() {
        const shell = document.getElementById('agency-shell');
        if (shell && window.innerWidth <= 900) {
            shell.classList.add('detail-open');
            document.body.style.overflow = 'hidden';
        }
    };

    window.closeMobileDetail = function closeMobileDetail() {
        document.getElementById('agency-shell')?.classList.remove('detail-open');
        document.body.style.overflow = '';
    };

    window.toggleMembreStatus = async function toggleMembreStatus(button) {
        const currentStatus = button.dataset.currentStatus;
        const toggleUrl = button.dataset.toggleUrl;
        const newStatus = currentStatus === 'actif' ? 'inactif' : 'actif';
        const statusMap = {
            actif: { cls: 'badge-success', label: 'Actif' },
            en_conge: { cls: 'badge-warning', label: 'En congé' },
            inactif: { cls: 'badge-danger', label: 'Inactif' },
        };

        async function applyStatus(status) {
            const info = statusMap[status] || { cls: 'badge-info', label: status };
            button.dataset.currentStatus = status;
            button.textContent = status === 'actif' ? 'Désactiver' : 'Activer';
            const detailBadge = document.getElementById('personnel-detail-status');
            if (detailBadge) {
                detailBadge.className = `badge ${info.cls}`;
                detailBadge.textContent = info.label;
            }
            const selectedRow = document.querySelector('.agency-row.selected');
            if (selectedRow) {
                selectedRow.dataset.status = status;
                selectedRow.dataset.statusClass = info.cls;
                selectedRow.dataset.statusLabel = info.label;
                const rowBadge = selectedRow.querySelector('.badge');
                if (rowBadge) {
                    rowBadge.className = `badge ${info.cls}`;
                    rowBadge.textContent = info.label;
                }
            }
            window.showToast(`Membre ${status === 'actif' ? 'activé' : 'désactivé'} avec succès.`, 'success');
        }

        if (!toggleUrl || toggleUrl === '#') {
            await applyStatus(newStatus);
            return;
        }

        const originalText = button.textContent;
        button.classList.add('btn-loading');
        button.textContent = currentStatus === 'actif' ? 'Désactivation...' : 'Activation...';
        try {
            const response = await fetch(toggleUrl, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    Accept: 'application/json',
                },
            });
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            const data = await response.json();
            await applyStatus(data.statut || newStatus);
        } catch (error) {
            button.textContent = originalText;
            window.showToast('Une erreur est survenue. Veuillez réessayer.', 'error');
        } finally {
            button.classList.remove('btn-loading');
        }
    };

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            window.closeMobileDetail();
        }
    });

    const personnelSearch = document.getElementById('personnel-search');
    if (personnelSearch) {
        document.querySelectorAll('.agency-filter-pills .filter-pill').forEach((pill) => {
            pill.addEventListener('click', function () {
                document.querySelectorAll('.agency-filter-pills .filter-pill').forEach((item) => item.classList.remove('active'));
                this.classList.add('active');
                const filter = this.dataset.filter;
                let anyVisible = false;
                document.querySelectorAll('.agency-row').forEach((row) => {
                    const show = filter === 'tous' || row.dataset.status === filter;
                    row.style.display = show ? '' : 'none';
                    if (show) {
                        anyVisible = true;
                    }
                });
                document.getElementById('personnel-no-results').classList.toggle('u-hidden', anyVisible);
            });
        });

        personnelSearch.addEventListener('input', function () {
            const query = this.value.toLowerCase().trim();
            let anyVisible = false;
            document.querySelectorAll('.agency-row').forEach((row) => {
                const match = !query
                    || row.dataset.name.toLowerCase().includes(query)
                    || row.dataset.matricule.toLowerCase().includes(query)
                    || row.dataset.poste.toLowerCase().includes(query)
                    || row.dataset.roleLabel.toLowerCase().includes(query);
                row.style.display = match ? '' : 'none';
                if (match) {
                    anyVisible = true;
                }
            });
            document.getElementById('personnel-no-results').classList.toggle('u-hidden', anyVisible);
        });
    }
});