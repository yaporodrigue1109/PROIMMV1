<?php

namespace App\Http\Controllers\Admin\Module;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class ModuleController extends Controller
{
    public function index(): Response
    {
        $menus = $this->mockMenus();

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
        ]);
    }

    private function findModule(string $code): ?array
    {
        return $this->mockModules()->firstWhere('code', $code);
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
