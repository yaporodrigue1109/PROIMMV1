<?php

namespace App\Http\Controllers\Admin\Module;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ModuleRequest;
use App\Models\Module as ModuleModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

class ModuleController extends Controller
{
    public function index(): Response
    {
        $menus = $this->databaseMenus() ?? $this->mockMenus();

        return Inertia::render('Admin/Modules/Index', [
            'menus' => $menus,
            'stats' => $this->buildStats($menus),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Modules/Form', [
            'mode' => 'create',
            'module' => null,
            'parentModules' => $this->parentModuleOptions(),
        ]);
    }

    public function show($code): Response
    {
        $module = $this->findModule($code);

        abort_if(!$module, 404, 'Module introuvable.');

        return Inertia::render('Admin/Modules/Show', [
            'module' => $module,
        ]);
    }

    public function edit($code): Response
    {
        $module = $this->findModule($code);

        abort_if(!$module, 404, 'Module introuvable.');

        return Inertia::render('Admin/Modules/Form', [
            'mode' => 'edit',
            'module' => $module,
            'parentModules' => $this->parentModuleOptions($module['module_id'] ?? null),
        ]);
    }

    public function store(ModuleRequest $request): RedirectResponse
    {
        $this->requireModuleTables();
        $data = $request->validated();

        $module = DB::transaction(function () use ($data): ModuleModel {
            $module = ModuleModel::query()->create([
                'name' => $data['name'],
                'slug' => $data['slug'],
                'route' => $data['route'] ?? null,
                'icon' => $data['icon'] ?? null,
                'parent_id' => $data['parent_id'],
                'order_index' => $this->nextOrderForParent($data['parent_id']),
                'is_active' => $data['is_active'],
            ]);

            $this->syncActions($module, $data['actions']);

            return $module;
        });

        return redirect()->route('admin.modules.edit', $module->slug)
            ->with('success', 'Module créé avec succès.');
    }

    public function update(ModuleRequest $request, string $code): RedirectResponse
    {
        $this->requireModuleTables();
        $module = $this->findDatabaseModule($code);
        $data = $request->validated();

        DB::transaction(function () use ($module, $data): void {
            $previousParentId = $module->parent_id;
            $parentChanged = $previousParentId !== $data['parent_id'];

            $module->update([
                'name' => $data['name'],
                'slug' => $data['slug'],
                'route' => $data['route'] ?? null,
                'icon' => $data['icon'] ?? null,
                'parent_id' => $data['parent_id'],
                'order_index' => $parentChanged
                    ? $this->nextOrderForParent($data['parent_id'])
                    : $module->order_index,
                'is_active' => $data['is_active'],
            ]);

            $this->syncActions($module, $data['actions']);

            if ($parentChanged) {
                $this->normalizeSiblingOrder($previousParentId);
            }
        });

        return redirect()->route('admin.modules.edit', $module->slug)
            ->with('success', 'Module mis à jour avec succès.');
    }

    public function toggle(string $code): RedirectResponse
    {
        $this->requireModuleTables();
        $module = $this->findDatabaseModule($code);
        $module->update(['is_active' => !$module->is_active]);

        return back()->with('success', $module->is_active
            ? 'Module activé avec succès.'
            : 'Module désactivé avec succès.');
    }

    public function reorder(Request $request): RedirectResponse
    {
        $this->requireModuleTables();
        $data = $request->validate([
            'items' => ['required', 'array'],
            'items.*.module_id' => ['required', 'uuid', 'distinct', 'exists:modules,module_id'],
            'items.*.parent_id' => ['nullable', 'uuid', 'exists:modules,module_id'],
            'items.*.order_index' => ['required', 'integer', 'min:0'],
        ]);

        DB::transaction(function () use ($data): void {
            foreach ($data['items'] as $item) {
                ModuleModel::query()
                    ->where('module_id', $item['module_id'])
                    ->update([
                        'parent_id' => $item['parent_id'],
                        'order_index' => $item['order_index'],
                    ]);
            }
        });

        return back()->with('success', 'Ordre des modules enregistré.');
    }

    public function destroy(string $code): RedirectResponse
    {
        $this->requireModuleTables();
        $module = $this->findDatabaseModule($code);

        DB::transaction(function () use ($module): void {
            $targetParentId = $module->parent_id;
            $nextOrder = $this->nextOrderForParent($targetParentId);

            foreach ($module->children()->get() as $child) {
                $child->update([
                    'parent_id' => $targetParentId,
                    'order_index' => $nextOrder++,
                ]);
            }

            $module->delete();
            $this->normalizeSiblingOrder($targetParentId);
        });

        return redirect()->route('admin.modules.index')
            ->with('success', 'Module supprimé avec succès.');
    }

    private function findModule(string $code): ?array
    {
        if ($this->moduleTablesExist()) {
            $databaseModule = ModuleModel::query()
                ->with('actions')
                ->where('slug', $code)
                ->first();

            if ($databaseModule) {
                return [
                    'module_id' => $databaseModule->module_id,
                    'name' => $databaseModule->name,
                    'nom' => $databaseModule->name,
                    'slug' => $databaseModule->slug,
                    'code' => $databaseModule->slug,
                    'route' => $databaseModule->route,
                    'icon' => $databaseModule->icon,
                    'parent_id' => $databaseModule->parent_id,
                    'order_index' => $databaseModule->order_index,
                    'is_active' => $databaseModule->is_active,
                    'statut' => $databaseModule->is_active ? 'Actif' : 'Inactif',
                    'actions' => $databaseModule->actions->toArray(),
                    'description' => "Gestion du module {$databaseModule->name}.",
                    'prix' => 0,
                    'cycle' => 'Mensuel',
                    'categorie' => 'Administration',
                    'agences' => 0,
                    'permissions' => $databaseModule->actions->pluck('name')->all(),
                ];
            }
        }

        $module = $this->mockModules()->firstWhere('code', $code);

        if ($module) {
            return $module;
        }

        $menu = $this->mockMenus()->firstWhere('code', $code);

        if (!$menu) {
            return null;
        }

        return [
            'id' => $menu['parent_id'],
            'name' => $menu['label'],
            'nom' => $menu['label'],
            'slug' => $menu['code'],
            'code' => $menu['code'],
            'route' => "agence.{$menu['code']}.index",
            'icon' => "admin/icones_module/{$menu['code']}.svg",
            'parent' => 'none',
            'order_index' => $menu['order'],
            'is_active' => $menu['active'],
            'statut' => $menu['active'] ? 'Actif' : 'Inactif',
            'actions' => $this->mockModuleActions($menu['code']),
            'description' => "Gestion du module {$menu['label']}.",
            'prix' => 0,
            'cycle' => 'Mensuel',
            'categorie' => 'Administration',
            'agences' => 0,
            'permissions' => [],
        ];
    }

    private function databaseMenus(): ?Collection
    {
        if (!$this->moduleTablesExist()) {
            return null;
        }

        return ModuleModel::query()
            ->parents()
            ->with(['actions', 'children.actions'])
            ->orderBy('order_index')
            ->get()
            ->map(function (ModuleModel $module): array {
                return $this->moduleToMenu($module, true);
            });
    }

    private function moduleToMenu(ModuleModel $module, bool $withChildren = false): array
    {
        return [
            'type' => $module->parent_id ? 'submenu' : 'parent',
            'id' => $module->module_id,
            'parent_id' => $module->parent_id ?: $module->module_id,
            'label' => $module->name,
            'code' => $module->slug,
            'route' => $module->route,
            'icon' => $module->icon,
            'order' => $module->order_index,
            'active' => $module->is_active,
            'actions' => $module->actions->toArray(),
            'submenus' => $withChildren
                ? $module->children->map(fn (ModuleModel $child): array => $this->moduleToMenu($child))->values()->all()
                : [],
        ];
    }

    private function parentModuleOptions(?string $excludedModuleId = null): array
    {
        if (!$this->moduleTablesExist()) {
            return $this->mockMenus()
                ->map(fn (array $menu): array => [
                    'value' => (string) $menu['parent_id'],
                    'label' => $menu['label'],
                ])
                ->all();
        }

        return ModuleModel::query()
            ->parents()
            ->when($excludedModuleId, fn ($query) => $query->where('module_id', '!=', $excludedModuleId))
            ->orderBy('order_index')
            ->get(['module_id', 'name'])
            ->map(fn (ModuleModel $module): array => [
                'value' => $module->module_id,
                'label' => $module->name,
            ])
            ->all();
    }

    private function moduleTablesExist(): bool
    {
        return Schema::hasTable('modules') && Schema::hasTable('module_actions');
    }

    private function requireModuleTables(): void
    {
        abort_unless(
            $this->moduleTablesExist(),
            503,
            'Importez le script modules_permissions08082026.sql avant d’utiliser la gestion des modules.'
        );
    }

    private function findDatabaseModule(string $code): ModuleModel
    {
        $module = ModuleModel::query()->where('slug', $code)->first();

        abort_if(!$module, 404, 'Module introuvable.');

        return $module;
    }

    private function syncActions(ModuleModel $module, array $actions): void
    {
        $keptActionIds = [];

        foreach ($actions as $index => $actionData) {
            $action = null;

            if (!empty($actionData['module_action_id'])) {
                $action = $module->actions()
                    ->where('module_action_id', $actionData['module_action_id'])
                    ->first();
            }

            $action ??= $module->actions()->firstOrNew(['slug' => $actionData['slug']]);
            $action->fill([
                'name' => $actionData['label'],
                'slug' => $actionData['slug'],
                'order_index' => $actionData['order'] ?? $index + 1,
                'is_critical' => $actionData['is_critical'] ?? false,
                'is_active' => $actionData['is_active'] ?? true,
            ]);
            $action->save();
            $keptActionIds[] = $action->module_action_id;
        }

        $obsoleteActions = $module->actions();

        if ($keptActionIds !== []) {
            $obsoleteActions->whereNotIn('module_action_id', $keptActionIds);
        }

        $obsoleteActions->delete();
    }

    private function nextOrderForParent(?string $parentId): int
    {
        return ((int) $this->siblingsQuery($parentId)->max('order_index')) + 1;
    }

    private function normalizeSiblingOrder(?string $parentId): void
    {
        $this->siblingsQuery($parentId)
            ->orderBy('order_index')
            ->orderBy('created_at')
            ->get()
            ->each(function (ModuleModel $module, int $index): void {
                $module->update(['order_index' => $index + 1]);
            });
    }

    private function siblingsQuery(?string $parentId)
    {
        return ModuleModel::query()->when(
            $parentId,
            fn ($query) => $query->where('parent_id', $parentId),
            fn ($query) => $query->whereNull('parent_id')
        );
    }

    private function mockModuleActions(string $code): array
    {
        $actionsByModule = [
            'dashboard' => ['Voir' => 'view'],
            'proprietes' => ['Voir' => 'view', 'Créer' => 'create', 'Modifier' => 'edit', 'Supprimer' => 'delete'],
            'proprietaire' => ['Voir' => 'view', 'Créer' => 'create', 'Modifier' => 'edit', 'Supprimer' => 'delete'],
            'locataires' => ['Voir' => 'view', 'Créer' => 'create', 'Modifier' => 'edit', 'Supprimer' => 'delete'],
            'personnel' => ['Voir' => 'view', 'Créer' => 'create', 'Modifier' => 'edit', 'Supprimer' => 'delete'],
            'maintenance' => ['Voir' => 'view', 'Créer' => 'create', 'Modifier' => 'edit', 'Valider' => 'validate', 'Annuler' => 'cancel'],
            'caisse' => ['Voir' => 'view', 'Créer' => 'create', 'Valider' => 'validate', 'Annuler' => 'cancel'],
            'reversement' => ['Voir' => 'view', 'Créer' => 'create', 'Valider' => 'validate', 'Exporter' => 'export'],
            'statistiques' => ['Voir' => 'view', 'Exporter' => 'export'],
            'support' => ['Voir' => 'view', 'Créer' => 'create', 'Modifier' => 'edit', 'Clôturer' => 'close'],
            'parametrage' => ['Voir' => 'view', 'Modifier' => 'edit'],
        ];

        $actions = $actionsByModule[$code] ?? ['Voir' => 'view'];
        $order = 0;

        return collect($actions)
            ->map(function (string $slug, string $name) use (&$order) {
                $order++;

                return [
                    'id' => $order,
                    'name' => $name,
                    'slug' => $slug,
                    'order_index' => $order,
                    'is_active' => true,
                ];
            })
            ->values()
            ->all();
    }

    private function mockMenus()
    {
        return collect([
            [
                'type' => 'parent',
                'parent_id' => 1,
                'label' => 'Tableau de bord',
                'code' => 'dashboard',
                'submenus' => [],
                'order' => 1,
                'active' => true,
            ],
            [
                'type' => 'parent',
                'parent_id' => 2,
                'label' => 'Propriétés',
                'code' => 'proprietes',
                'submenus' => [],
                'order' => 2,
                'active' => true,
            ],
            [
                'type' => 'parent',
                'parent_id' => 3,
                'label' => 'Propriétaires',
                'code' => 'proprietaire',
                'submenus' => [],
                'order' => 3,
                'active' => true,
            ],
            [
                'type' => 'parent',
                'parent_id' => 4,
                'label' => 'Locataires',
                'code' => 'locataires',
                'submenus' => [],
                'order' => 4,
                'active' => true,
            ],
            [
                'type' => 'parent',
                'parent_id' => 5,
                'label' => 'Personnel',
                'code' => 'personnel',
                'submenus' => [],
                'order' => 5,
                'active' => true,
            ],
            [
                'type' => 'parent',
                'parent_id' => 6,
                'label' => 'Maintenance',
                'code' => 'maintenance',
                'submenus' => [],
                'order' => 6,
                'active' => true,
            ],
            [
                'type' => 'parent',
                'parent_id' => 7,
                'label' => 'Caisse',
                'code' => 'caisse',
                'submenus' => [],
                'order' => 7,
                'active' => true,
            ],
            [
                'type' => 'parent',
                'parent_id' => 8,
                'label' => 'Reversement',
                'code' => 'reversement',
                'submenus' => [],
                'order' => 8,
                'active' => true,
            ],
            [
                'type' => 'parent',
                'parent_id' => 9,
                'label' => 'Statistiques',
                'code' => 'statistiques',
                'submenus' => [],
                'order' => 9,
                'active' => true,
            ],
            [
                'type' => 'parent',
                'parent_id' => 10,
                'label' => 'Support',
                'code' => 'support',
                'submenus' => [],
                'order' => 10,
                'active' => true,
            ],
            [
                'type' => 'parent',
                'parent_id' => 11,
                'label' => 'Paramétrage',
                'code' => 'parametrage',
                'submenus' => [],
                'order' => 11,
                'active' => true,
            ],
        ]);
    }

    private function mockModules()
    {
        return collect([
            [
                'code' => 'MOD-SMS',
                'nom' => 'SMS',
                'categorie' => 'Communication',
                'prix' => 25000,
                'cycle' => 'Mensuel',
                'statut' => 'Actif',
                'agences' => 18,
                'description' => 'Envoi de notifications SMS aux prospects, clients et propriétaires.',
                'permissions' => ['Envoyer des SMS', 'Consulter les historiques', 'Exporter les campagnes'],
            ],
            [
                'code' => 'MOD-WHATSAPP',
                'nom' => 'WhatsApp',
                'categorie' => 'Communication',
                'prix' => 15000,
                'cycle' => 'Mensuel',
                'statut' => 'Actif',
                'agences' => 12,
                'description' => 'Relance et suivi des contacts via WhatsApp depuis l’espace agence.',
                'permissions' => ['Envoyer des messages', 'Gérer les modèles', 'Suivre les conversations'],
            ],
            [
                'code' => 'MOD-OWNER',
                'nom' => 'Portail propriétaire',
                'categorie' => 'Espace client',
                'prix' => 30000,
                'cycle' => 'Mensuel',
                'statut' => 'En attente',
                'agences' => 5,
                'description' => 'Accès dédié aux propriétaires pour consulter les biens, visites et revenus.',
                'permissions' => ['Accès propriétaire', 'Voir les revenus', 'Consulter les rapports'],
            ],
            [
                'code' => 'MOD-STATS',
                'nom' => 'Statistiques avancées',
                'categorie' => 'Pilotage',
                'prix' => 20000,
                'cycle' => 'Mensuel',
                'statut' => 'Actif',
                'agences' => 9,
                'description' => 'Tableaux de bord avancés pour suivre les performances commerciales.',
                'permissions' => ['Voir les tableaux de bord', 'Exporter les statistiques', 'Comparer les agences'],
            ],
        ]);
    }

    private function buildStats($menus): array
    {
        $items = collect($menus);
        $submenus = $items->pluck('submenus')->flatten(1);

        return [
            'totalMenus' => $items->count(),
            'menusActifs' => $items->where('active', true)->count(),
            'menusInactifs' => $items->where('active', false)->count(),
            'totalSubmenus' => $submenus->count(),
            'submenusActifs' => $submenus->where('active', true)->count(),
            'submenusInactifs' => $submenus->where('active', false)->count(),
        ];
    }
}
