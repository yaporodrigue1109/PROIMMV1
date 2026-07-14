@extends('admin.layouts.app')

@section('title', 'Menus sidebar')
@section('header_title', 'Menus sidebar')

@section('content')
    <section class="page">

        <div class="page-header">
            <div class="page-header-copy">
                <div class="page-heading">
                    <div class="page-heading-text">
                        <h2>Gestion des menus sidebar</h2>
                        <p class="text-muted">
                            Activez, désactivez et réorganisez les menus.
                        </p>
                    </div>

                    <button type="button" id="toggleDragBtn" class="action-btn drag-toggle-btn">
                        Activer le déplacement
                    </button>
                </div>
            </div>
        </div>

        <div class="table-shell">
            <table class="data-table">
                <thead>
                <tr>
                    <th></th>
                    <th>Menu</th>
                    <th>Sous-menus</th>
                    <th>Ordre</th>
                    <th>État</th>
                    <th>Action</th>
                </tr>
                </thead>

                <tbody id="sortableMenus">

                <tr data-type="parent" data-parent-id="1" data-level="parent" data-has-submenus="false">
                    <td class="drag-cell">
                        <span class="drag-handle">⋮⋮</span>
                    </td>
                    <td class="parent-cell">
                        <strong>Tableau de bord</strong>
                        <div class="text-muted small">dashboard</div>
                    </td>
                    <td class="submenu-count">0</td>
                    <td class="order-number">1</td>
                    <td><span class="badge badge-success">Actif</span></td>
                    <td>
                        <button type="button" class="action-btn toggle-inactive">Désactiver</button>
                    </td>
                </tr>

                <tr data-type="parent" data-parent-id="2" data-level="parent" data-has-submenus="true">
                    <td class="drag-cell">
                        <span class="drag-handle">⋮⋮</span>
                    </td>
                    <td class="parent-cell clickable-parent">
                        <strong>Missions</strong>
                        <div class="text-muted small">missions</div>
                    </td>
                    <td class="submenu-count">3</td>
                    <td class="order-number">2</td>
                    <td><span class="badge badge-success">Actif</span></td>
                    <td>
                        <button type="button" class="action-btn toggle-inactive">Désactiver</button>
                    </td>
                </tr>

                <tr data-type="submenu" data-parent-id="2" data-submenu-id="2-1" data-level="submenu" class="submenu-row parent-2">
                    <td class="drag-cell submenu-drag-cell">
                        <span class="drag-handle submenu-handle">⋮⋮</span>
                    </td>
                    <td class="submenu-cell">
                        <span class="submenu-indent">↳</span>
                        <strong>Toutes les missions</strong>
                        <div class="text-muted small">missions/all</div>
                    </td>
                    <td>-</td>
                    <td class="order-number">2.1</td>
                    <td><span class="badge badge-success">Actif</span></td>
                    <td>
                        <button type="button" class="action-btn toggle-inactive">Désactiver</button>
                    </td>
                </tr>

                <tr data-type="submenu" data-parent-id="2" data-submenu-id="2-2" data-level="submenu" class="submenu-row parent-2">
                    <td class="drag-cell submenu-drag-cell">
                        <span class="drag-handle submenu-handle">⋮⋮</span>
                    </td>
                    <td class="submenu-cell">
                        <span class="submenu-indent">↳</span>
                        <strong>Nouvelle mission</strong>
                        <div class="text-muted small">missions/create</div>
                    </td>
                    <td>-</td>
                    <td class="order-number">2.2</td>
                    <td><span class="badge badge-danger">Désactivé</span></td>
                    <td>
                        <button type="button" class="action-btn toggle-active">Activer</button>
                    </td>
                </tr>

                <tr data-type="submenu" data-parent-id="2" data-submenu-id="2-3" data-level="submenu" class="submenu-row parent-2">
                    <td class="drag-cell submenu-drag-cell">
                        <span class="drag-handle submenu-handle">⋮⋮</span>
                    </td>
                    <td class="submenu-cell">
                        <span class="submenu-indent">↳</span>
                        <strong>Rapports missions</strong>
                        <div class="text-muted small">missions/reports</div>
                    </td>
                    <td>-</td>
                    <td class="order-number">2.3</td>
                    <td><span class="badge badge-success">Actif</span></td>
                    <td>
                        <button type="button" class="action-btn toggle-inactive">Désactiver</button>
                    </td>
                </tr>

                <tr data-type="parent" data-parent-id="3" data-level="parent" data-has-submenus="true">
                    <td class="drag-cell">
                        <span class="drag-handle">⋮⋮</span>
                    </td>
                    <td class="parent-cell clickable-parent">
                        <strong>Véhicules</strong>
                        <div class="text-muted small">vehicules</div>
                    </td>
                    <td class="submenu-count">2</td>
                    <td class="order-number">3</td>
                    <td><span class="badge badge-success">Actif</span></td>
                    <td>
                        <button type="button" class="action-btn toggle-inactive">Désactiver</button>
                    </td>
                </tr>

                <tr data-type="submenu" data-parent-id="3" data-submenu-id="3-1" data-level="submenu" class="submenu-row parent-3">
                    <td class="drag-cell submenu-drag-cell">
                        <span class="drag-handle submenu-handle">⋮⋮</span>
                    </td>
                    <td class="submenu-cell">
                        <span class="submenu-indent">↳</span>
                        <strong>Liste des véhicules</strong>
                        <div class="text-muted small">vehicules/list</div>
                    </td>
                    <td>-</td>
                    <td class="order-number">3.1</td>
                    <td><span class="badge badge-success">Actif</span></td>
                    <td>
                        <button type="button" class="action-btn toggle-inactive">Désactiver</button>
                    </td>
                </tr>

                <tr data-type="submenu" data-parent-id="3" data-submenu-id="3-2" data-level="submenu" class="submenu-row parent-3">
                    <td class="drag-cell submenu-drag-cell">
                        <span class="drag-handle submenu-handle">⋮⋮</span>
                    </td>
                    <td class="submenu-cell">
                        <span class="submenu-indent">↳</span>
                        <strong>Catégories</strong>
                        <div class="text-muted small">vehicules/categories</div>
                    </td>
                    <td>-</td>
                    <td class="order-number">3.2</td>
                    <td><span class="badge badge-success">Actif</span></td>
                    <td>
                        <button type="button" class="action-btn toggle-inactive">Désactiver</button>
                    </td>
                </tr>

                <tr data-type="parent" data-parent-id="4" data-level="parent" data-has-submenus="false">
                    <td class="drag-cell">
                        <span class="drag-handle">⋮⋮</span>
                    </td>
                    <td class="parent-cell">
                        <strong>Facturation</strong>
                        <div class="text-muted small">facturation</div>
                    </td>
                    <td class="submenu-count">0</td>
                    <td class="order-number">4</td>
                    <td><span class="badge badge-danger">Désactivé</span></td>
                    <td>
                        <button type="button" class="action-btn toggle-active">Activer</button>
                    </td>
                </tr>

                <tr data-type="parent" data-parent-id="5" data-level="parent" data-has-submenus="true">
                    <td class="drag-cell">
                        <span class="drag-handle">⋮⋮</span>
                    </td>
                    <td class="parent-cell clickable-parent">
                        <strong>Caisse</strong>
                        <div class="text-muted small">caisse</div>
                    </td>
                    <td class="submenu-count">2</td>
                    <td class="order-number">5</td>
                    <td><span class="badge badge-success">Actif</span></td>
                    <td>
                        <button type="button" class="action-btn toggle-inactive">Désactiver</button>
                    </td>
                </tr>

                <tr data-type="submenu" data-parent-id="5" data-submenu-id="5-1" data-level="submenu" class="submenu-row parent-5">
                    <td class="drag-cell submenu-drag-cell">
                        <span class="drag-handle submenu-handle">⋮⋮</span>
                    </td>
                    <td class="submenu-cell">
                        <span class="submenu-indent">↳</span>
                        <strong>Transactions</strong>
                        <div class="text-muted small">caisse/transactions</div>
                    </td>
                    <td>-</td>
                    <td class="order-number">5.1</td>
                    <td><span class="badge badge-success">Actif</span></td>
                    <td>
                        <button type="button" class="action-btn toggle-inactive">Désactiver</button>
                    </td>
                </tr>

                <tr data-type="submenu" data-parent-id="5" data-submenu-id="5-2" data-level="submenu" class="submenu-row parent-5">
                    <td class="drag-cell submenu-drag-cell">
                        <span class="drag-handle submenu-handle">⋮⋮</span>
                    </td>
                    <td class="submenu-cell">
                        <span class="submenu-indent">↳</span>
                        <strong>Rapports de caisse</strong>
                        <div class="text-muted small">caisse/reports</div>
                    </td>
                    <td>-</td>
                    <td class="order-number">5.2</td>
                    <td><span class="badge badge-danger">Désactivé</span></td>
                    <td>
                        <button type="button" class="action-btn toggle-active">Activer</button>
                    </td>
                </tr>

                <tr data-type="parent" data-parent-id="6" data-level="parent" data-has-submenus="false">
                    <td class="drag-cell">
                        <span class="drag-handle">⋮⋮</span>
                    </td>
                    <td class="parent-cell">
                        <strong>Personnel</strong>
                        <div class="text-muted small">personnel</div>
                    </td>
                    <td class="submenu-count">0</td>
                    <td class="order-number">6</td>
                    <td><span class="badge badge-success">Actif</span></td>
                    <td>
                        <button type="button" class="action-btn toggle-inactive">Désactiver</button>
                    </td>
                </tr>

                <tr data-type="parent" data-parent-id="7" data-level="parent" data-has-submenus="false">
                    <td class="drag-cell">
                        <span class="drag-handle">⋮⋮</span>
                    </td>
                    <td class="parent-cell">
                        <strong>Reporting</strong>
                        <div class="text-muted small">reporting</div>
                    </td>
                    <td class="submenu-count">0</td>
                    <td class="order-number">7</td>
                    <td><span class="badge badge-danger">Désactivé</span></td>
                    <td>
                        <button type="button" class="action-btn toggle-active">Activer</button>
                    </td>
                </tr>

                </tbody>
            </table>
        </div>

    </section>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
@endsection