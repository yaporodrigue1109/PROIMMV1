/* ── Utilitaire toast ── */
function showToast(message, type = 'success', duration = 3500) {
    const container = document.getElementById('toast-container');
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.setAttribute('role', 'status');
    const icon = type === 'success'
        ? '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>'
        : '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>';
    toast.innerHTML = icon + '<span>' + message + '</span>';
    container.appendChild(toast);
    setTimeout(() => {
        toast.classList.add('out');
        toast.addEventListener('animationend', () => toast.remove(), { once: true });
    }, duration);
}

function escapeHtml(str) {
    return String(str ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function formatMoneyLocal(amount) {
    return Number(amount).toLocaleString('fr-FR') + ' FCFA';
}

/* ── Gestion des onglets ── */
function switchTab(tabName, btn) {
    document.querySelectorAll('.tab-btn').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tab-panel').forEach(c => c.classList.remove('active'));
    if (btn) btn.classList.add('active');
    const content = document.getElementById('tab-' + tabName);
    if (content) content.classList.add('active');
    if (tabName === 'life') loadAgencyLife();
}

function setText(id, value) {
    const el = document.getElementById(id);
    if (el) el.textContent = value ?? '';
}

/* ── Sélection d'une agence ── */
function selectAgency(row) {
    document.querySelectorAll('.agency-row').forEach(r => {
        r.classList.remove('selected');
        r.setAttribute('aria-selected', 'false');
    });
    row.classList.add('selected');
    row.setAttribute('aria-selected', 'true');

    const statusBadge = document.getElementById('agency-detail-status');
    if (statusBadge) {
        statusBadge.className = `badge ${row.dataset.statusClass}`;
        statusBadge.textContent = row.dataset.statusLabel;
    }

    setText('agency-detail-code', row.dataset.code);
    setText('agency-detail-name', row.dataset.name);
    setText('agency-detail-abonnement', row.dataset.abonnement);
    setText('agency-detail-start', row.dataset.start);
    setText('agency-detail-end', row.dataset.end);
    setText('agency-detail-total-paid', row.dataset.totalPaid);

    // Onglet info
    setText('agency-detail-name-full', row.dataset.name);
    setText('agency-detail-initials', row.dataset.initials);
    setText('agency-detail-email', row.dataset.email);
    setText('agency-detail-phone', row.dataset.phone);
    setText('agency-detail-location', row.dataset.location);
    setText('agency-detail-address', row.dataset.address);
    setText('agency-detail-legal-form', row.dataset.legalForm ?? 'Non spécifié');
    setText('agency-detail-reg-number', row.dataset.regNumber ?? 'Non spécifié');
    setText('agency-detail-tva', row.dataset.tva ?? 'Non spécifié');
    setText('agency-detail-employees', row.dataset.employees ?? 'Non renseigné');
    setText('agency-detail-country', row.dataset.country ?? '');
    setText('agency-detail-capital', row.dataset.capital ?? '0 FCFA');
    setText('agency-detail-created-at', row.dataset.createdAt ?? 'Non défini');
    setText('agency-detail-siege', row.dataset.siege ?? 'Non spécifié');

    // Onglet abonnement
    let modules = [];
    try { modules = JSON.parse(row.dataset.modules); } catch(e) { modules = []; }
    const basePrice = parseInt(row.dataset.basePrice) || 49900;
    const modulesPrice = parseInt(row.dataset.modulesPrice) || (modules.length * 5000);
    const totalPerMonth = basePrice + modulesPrice;

    setText('sub-plan-name', row.dataset.abonnement || 'Aucun abonnement');
    setText('sub-plan-desc', row.dataset.planDesc || '');
    setText('sub-base-price', formatMoneyLocal(basePrice));
    setText('sub-modules-total', '+ Modules : ' + formatMoneyLocal(modulesPrice));
    setText('sub-total-display', formatMoneyLocal(totalPerMonth));
    setText('sub-stat-modules', modules.length + ' / 4');
    setText('sub-period-current', row.dataset.start + ' → ' + row.dataset.end);
    setText('sub-next-renewal', 'Prochain renouvellement : ' + row.dataset.end);
    setText('sub-stat-total', row.dataset.totalPaid);

    const badgesEl = document.getElementById('sub-modules-badges');
    if (badgesEl) {
        if (modules.length === 0) {
            badgesEl.innerHTML = '<span class="badge badge-neutral">Aucun module actif</span>';
        } else {
            badgesEl.innerHTML = modules.map(m => `<span class="badge badge-success">✓ ${escapeHtml(m)}</span>`).join('');
        }
    }

    // Badge statut abonnement
    const statusBadgeAbo = document.getElementById('sub-status-badge');
    if (row.dataset.status === 'active') {
        statusBadgeAbo.innerHTML = '<span class="status-dot"></span> Actif';
        statusBadgeAbo.style.background = 'rgba(118,195,0,0.12)';
        statusBadgeAbo.style.borderColor = 'rgba(118,195,0,0.28)';
        statusBadgeAbo.style.color = 'var(--primary)';
    } else if (row.dataset.status === 'en_demo') {
        statusBadgeAbo.innerHTML = '<span class="status-dot status-dot-warning"></span> Période d\'essai';
        statusBadgeAbo.style.background = 'rgba(245,158,11,0.12)';
        statusBadgeAbo.style.borderColor = 'rgba(245,158,11,0.28)';
        statusBadgeAbo.style.color = '#f59e0b';
    } else {
        statusBadgeAbo.innerHTML = '<span class="status-dot status-dot-danger"></span> Suspendu';
        statusBadgeAbo.style.background = 'rgba(220,38,38,0.12)';
        statusBadgeAbo.style.borderColor = 'rgba(220,38,38,0.28)';
        statusBadgeAbo.style.color = '#dc2626';
    }

    // Alerte renouvellement
    const endDateStr = row.dataset.end;
    const alertDiv = document.getElementById('sub-renewal-alert');
    if (endDateStr && endDateStr !== 'Non défini' && alertDiv) {
        try {
            const parts = endDateStr.split('/');
            const endDate = new Date(parts[2], parts[1]-1, parts[0]);
            const today = new Date();
            const diffDays = Math.ceil((endDate - today) / (1000 * 60 * 60 * 24));
            if (diffDays <= 30 && diffDays >= 0) {
                alertDiv.style.display = 'flex';
                const msg = diffDays <= 7
                    ? `⚠️ Renouvellement imminent dans ${diffDays} jour(s) ! Veuillez vérifier vos informations de paiement.`
                    : `Renouvellement automatique dans ${diffDays} jours (le ${endDateStr}).`;
                document.getElementById('renewal-message').innerText = msg;
                document.getElementById('renewal-title').innerText = diffDays <= 7 ? '⚠️ Renouvellement imminent' : 'Renouvellement automatique';
            } else {
                alertDiv.style.display = 'none';
            }
        } catch(e) { console.warn(e); }
    } else if (alertDiv) {
        alertDiv.style.display = 'none';
    }

    // Onglet vie
    setText('life-locataires', row.dataset.locataires ?? 0);
    setText('life-proprietaires', row.dataset.proprietaires ?? 0);
    setText('life-utilisateurs', row.dataset.utilisateurs ?? 0);
    setText('life-biens', row.dataset.biens ?? 0);
    setText('life-lots', row.dataset.lots ?? 0);
    setText('life-tickets', row.dataset.tickets ?? 0);
    setText('life-tickets-resolus', row.dataset.ticketsResolus ?? 0);

    const totalTickets = parseInt(row.dataset.tickets ?? 0);
    const resolus = parseInt(row.dataset.ticketsResolus ?? 0);
    const taux = totalTickets > 0 ? Math.round((resolus / totalTickets) * 100) : 0;
    setText('life-taux-resolution', taux + '%');

    // Bouton toggle
    const toggleBtn = document.getElementById('agency-toggle-status');
    if (toggleBtn) {
        toggleBtn.dataset.currentStatus = row.dataset.status;
        toggleBtn.textContent = row.dataset.status === 'active' ? 'Désactiver' : 'Activer';
    }

    // Lien modifier
    const editLink = document.getElementById('agency-edit-link');
    if (editLink) editLink.setAttribute('href', row.dataset.editUrl);

    // Stocker ID agence
    window.currentAgencyId = row.dataset.agencyId || row.dataset.code;

    // Charger historique factures
    chargerHistoriqueFactures(window.currentAgencyId);

    // Réinitialiser onglet info
    const infoTab = document.querySelector('.tab-btn[data-tab="info"]');
    switchTab('info', infoTab);

    openMobileDetail();
}

/* ── Historique factures ── */
function chargerHistoriqueFactures(agenceId) {
    if (!agenceId) return;

    fetch(`/admin/agences/${agenceId}/factures/historique`, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
        .then(res => res.ok ? res.json() : Promise.reject())
        .then(data => {
            const factures = data.factures || [];
            const tbody = document.getElementById('sub-invoices-list');
            if (factures.length === 0) {
                tbody.innerHTML = `<tr><td colspan="8" class="table-empty-cell">Aucune facture trouvée</td></tr>`;
                return;
            }
            tbody.innerHTML = factures.map(f => `
                    <tr>
                        <td><div class="invoice-period">${f.periode_debut}</div><div class="table-muted-xs">→ ${f.periode_fin}</div></td>
                        <td><span class="badge badge-info">${f.modules_count || 0} module(s)</span></td>
                        <td class="u-text-muted">${f.montant_base || '0 FCFA'}</td>
                        <td class="u-text-muted">${f.montant_modules || '0 FCFA'}</td>
                        <td class="table-strong">${f.total || '0 FCFA'}</td>
                        <td><span class="badge ${f.statut === 'paye' ? 'badge-success' : (f.statut === 'echec' ? 'badge-danger' : 'badge-warning')}">${f.statut === 'paye' ? 'Payé' : (f.statut === 'echec' ? 'Échec' : 'En attente')}</span></td>
                        <td class="table-muted-sm">${f.moyen_paiement || '—'}</td>
                        <td>${f.facture_url ? `<a href="${f.facture_url}" class="btn btn-secondary btn-sm" target="_blank">FAC-${f.numero || ''}</a>` : '<span class="table-muted-xs">—</span>'}</td>
                    </tr>
                `).join('');
            setText('sub-payments-success', (data.paiements_reussis || 0) + ' / ' + (data.total_tentatives || 0));
        })
        .catch(err => {
            console.error(err);
            document.getElementById('sub-invoices-list').innerHTML = `<tr><td colspan="8" class="table-error-cell">Erreur chargement</td></tr>`;
        });
}

/* ── Actions abonnement ── */
function gererAbonnement() {
    window.location.href = `/admin/agences/${window.currentAgencyId}/abonnement/edit`;
}

function toggleRenouvellementAuto() {
    if (!confirm('Activer/désactiver le renouvellement automatique ?')) return;
    fetch(`/admin/agences/${window.currentAgencyId}/abonnement/renouvellement`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content },
        body: JSON.stringify({ action: 'toggle' })
    })
        .then(res => res.json())
        .then(data => showToast(data.message || 'Mis à jour', data.success ? 'success' : 'info'))
        .catch(() => showToast('Erreur', 'error'));
}

function confirmerResiliation() {
    if (!confirm('⚠️ Attention : La résiliation suspend tous les services. Confirmer ?')) return;
    if (!confirm('Dernière confirmation : action irréversible sans réactivation manuelle.')) return;

    fetch(`/admin/agences/${window.currentAgencyId}/abonnement/resilier`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content }
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast('Abonnement résilié', 'success');
                setTimeout(() => location.reload(), 1500);
            } else showToast(data.message || 'Erreur', 'error');
        })
        .catch(() => showToast('Erreur réseau', 'error'));
}

function exportFacturation() {
    window.open(`/admin/agences/${window.currentAgencyId}/factures/export`, '_blank');
}

/* ── Toggle statut agence ── */
async function toggleAgencyStatus(btn) {
    const currentStatus = btn.dataset.currentStatus;
    const originalText = btn.textContent;
    btn.classList.add('btn-loading');
    btn.textContent = currentStatus === 'active' ? 'Désactivation…' : 'Activation…';

    try {
        const res = await fetch(`/admin/agences/${window.currentAgencyId}/toggle-status`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content }
        });
        if (!res.ok) throw new Error();
        const data = await res.json();
        const newStatus = data.statut ?? (currentStatus === 'active' ? 'desactive' : 'active');
        btn.dataset.currentStatus = newStatus;
        btn.textContent = newStatus === 'active' ? 'Désactiver' : 'Activer';
        const statusMap = {
            active: { cls: 'badge-success', label: 'Active' },
            en_demo: { cls: 'badge-warning', label: 'En démo' },
            desactive: { cls: 'badge-danger', label: 'Désactivée' }
        };
        const info = statusMap[newStatus] ?? { cls: 'badge-info', label: newStatus };
        const badge = document.getElementById('agency-detail-status');
        if (badge) {
            badge.className = `badge ${info.cls}`;
            badge.textContent = info.label;
        }
        const selectedRow = document.querySelector('.agency-row.selected');
        if (selectedRow) {
            selectedRow.dataset.status = newStatus;
            selectedRow.dataset.statusClass = info.cls;
            selectedRow.dataset.statusLabel = info.label;
            const rowBadge = selectedRow.querySelector('.badge');
            if (rowBadge) {
                rowBadge.className = `badge ${info.cls}`;
                rowBadge.textContent = info.label;
            }
        }
        showToast(`Agence ${newStatus === 'active' ? 'activée' : 'désactivée'} avec succès.`, 'success');
    } catch (err) {
        btn.textContent = originalText;
        showToast('Une erreur est survenue.', 'error');
    } finally {
        btn.classList.remove('btn-loading');
    }
}

/* ── Vie de l'agence ── */
function loadAgencyLife() {
    const selectedRow = document.querySelector('.agency-row.selected');
    if (!selectedRow) return;
    const code = selectedRow.dataset.code;
    const container = document.getElementById('life-activities-container');
    const btn = document.getElementById('life-refresh-btn');
    if (!container) return;
    container.innerHTML = `<div class="loading-panel"><div class="loading-spinner"></div> Chargement…</div>`;
    if (btn) { btn.textContent = 'Chargement…'; btn.disabled = true; }
    fetch(`/admin/agences/life/${code}/activities`, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
        .then(res => res.ok ? res.json() : Promise.reject())
        .then(data => {
            const activities = data.activities ?? [];
            if (activities.length === 0) {
                container.innerHTML = `<div class="activities-empty">Aucune activité enregistrée.</div>`;
                return;
            }
            const colorMap = { blue: 'var(--primary)', green: '#0f9f6e', red: '#dc2626', yellow: '#f59e0b', gray: 'var(--muted-foreground)' };
            container.innerHTML = activities.map(a => `
                    <div class="life-timeline-item">
                        <div class="timeline-dot" data-bg="${colorMap[a.color] ?? colorMap.gray}"></div>
                        <div class="timeline-content">
                            <div class="timeline-header">
                                <div><p class="timeline-title">${escapeHtml(a.title)}</p><p class="timeline-description">${escapeHtml(a.description)}</p></div>
                                <small class="timeline-date">${escapeHtml(a.date_human ?? '')}</small>
                            </div>
                        </div>
                    </div>
                `).join('');
            window.syncDataDrivenStyles?.(container);
        })
        .catch(() => container.innerHTML = `<div class="activities-empty">Impossible de charger les activités.</div>`)
        .finally(() => { if (btn) { btn.textContent = 'Actualiser'; btn.disabled = false; } });
}

/* ── Mobile drawer ── */
function openMobileDetail() {
    if (window.innerWidth <= 900) {
        document.getElementById('agency-shell').classList.add('detail-open');
        document.body.style.overflow = 'hidden';
    }
}
function closeMobileDetail() {
    document.getElementById('agency-shell').classList.remove('detail-open');
    document.body.style.overflow = '';
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeMobileDetail(); });

/* ── Filtres et recherche ── */
document.querySelectorAll('.filter-pill').forEach(pill => {
    pill.addEventListener('click', function() {
        document.querySelectorAll('.filter-pill').forEach(p => p.classList.remove('active'));
        this.classList.add('active');
        const filter = this.dataset.filter;
        let anyVisible = false;
        document.querySelectorAll('.agency-row').forEach(row => {
            const show = filter === 'tous' || row.dataset.status === filter;
            row.style.display = show ? '' : 'none';
            if (show) anyVisible = true;
        });
        document.getElementById('agency-no-results').style.display = anyVisible ? 'none' : '';
    });
});

const searchInput = document.getElementById('agency-search');
if (searchInput) {
    searchInput.addEventListener('input', function() {
        const query = this.value.toLowerCase().trim();
        let anyVisible = false;
        document.querySelectorAll('.agency-row').forEach(row => {
            const match = !query || row.dataset.name.toLowerCase().includes(query) || row.dataset.code.toLowerCase().includes(query);
            row.style.display = match ? '' : 'none';
            if (match) anyVisible = true;
        });
        document.getElementById('agency-no-results').style.display = anyVisible ? 'none' : '';
    });
}
