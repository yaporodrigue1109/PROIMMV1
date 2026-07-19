<?php

namespace App\Http\Controllers\Admin\Agence;

use App\Http\Controllers\Controller;
use App\Http\Requests\AgenceRequest;
use App\Services\AgenceService;

use App\Services\TransactionService;
use App\Models\Abonnement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Services\UserService;
use App\Repositories\Repository\UserRepository;
use App\Services\ConfigurationTarifService;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin;
use App\Models\Agence;
use App\Models\Region;
use App\Models\Ville;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Repositories\Interfaces\AgenceRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;


    class AgenceController extends Controller
{
    protected AgenceService $agenceService;
    protected TransactionService $transactionService;

    protected UserService $userService;
    protected ConfigurationTarifService $configurationTarifService;
    public function __construct(
         AgenceService $agenceService,
         TransactionService $transactionService,
         UserService $userService,
         ConfigurationTarifService $configurationTarifService
    ) {
        $this->agenceService = $agenceService;
        $this->transactionService = $transactionService;
        $this->userService = $userService;
        $this->configurationTarifService = $configurationTarifService;
    }

    // ─── Liste ────────────────────────────────────────────────────────────────

    public function index(Request $request): Response
    {
        $filters = $request->only([
            'statut', 'region_id', 'ville_id', 'is_principale',
            'search', 'sort_by', 'sort_order',
        ]);
        $selectedAgenceId = $request->string('selected_agence_id')->toString();

       // $agences = $this->agenceService->repository->getAll($filters, 15);
        $agences = $this->agenceService->getAll($filters, 15);
        $agences = $this->hydrateAgenceSubscriptions($agences);
        $agenceItems = collect($agences instanceof \Illuminate\Pagination\AbstractPaginator ? $agences->items() : $agences);
        $hasFilters = collect($filters)->filter(fn ($value) => filled($value))->isNotEmpty();

        if ($agenceItems->isEmpty() && !$hasFilters) {
            $agenceItems = $this->buildDemoAgences();
            $agences = $agenceItems;
        }

        $agenceStats = $agenceItems->mapWithKeys(fn ($agence) => [
            ($agence['agence_id'] ?? $agence->agence_id) => [
                'proprietaires' => $agence['proprietaires'] ?? 0,
                'locataires' => $agence['locataires'] ?? 0,
                'utilisateurs' => $agence['utilisateurs'] ?? 0,
                'biens' => $agence['biens'] ?? 0,
                'lots' => $agence['lots'] ?? 0,
                'tickets' => $agence['tickets'] ?? 0,
                'tickets_resolus' => $agence['tickets_resolus'] ?? 0,
            ],
        ])->toArray();

        return Inertia::render('Admin/Agences/Index', [
            'agences' => $agences,
            'filters' => $filters,
            'selectedAgenceId' => $selectedAgenceId,
            'stats' => [
                'total' => $agenceItems->count(),
                'active' => $agenceItems->where('statut', 'active')->count(),
                'en_demo' => $agenceItems->where('statut', 'en_demo')->count(),
                'desactive' => $agenceItems->where('statut', 'desactive')->count(),
            ],
            'agenceStats' => $agenceStats,
            'meta' => [
                'regions' => Region::orderBy('name')->get(['id', 'name']),
            ],
        ]);
    }

    // ─── Formulaire création ──────────────────────────────────────────────────

    public function create(): Response
    {
        [$regions,$villes, $responsables, $tarifications] = $this->getFormDependencies();
    //  dd($regions, $responsables, $tarifications);

        return Inertia::render('Admin/Agences/Form', [
            'mode'           => 'create',
            'agence'         => [],
            'regions'        => $regions,
            'responsables'   => $responsables,
            'tarifications'  => $tarifications,
            'responsable_mode' => 'existing',
            'villes'         => $villes,
        ]);
    }

    // ─── STORE ────────────────────────────────────────────────────────────────

    /**
     * Enregistrement d'une nouvelle agence.
     *
     * Flux :
     *  1. La FormRequest AgenceRequest valide les données.
     *  2. Le service createAgence() prend en charge toute la logique métier :
     *     - statut en_demo  → simple création, pas d'abonnement
     *     - statut active   → abonnement + historique + transaction
     *     - responsable_mode existing → récupération user
     *     - responsable_mode new      → création user responsable
     */
    public function store(AgenceRequest $request)
    {
        try {

            $agence = $this->agenceService->createAgence($request->all());
           // dd($request->all());
            return redirect()
                ->route('admin.agences.index', ['selected_agence_id' => $agence->agence_id])
                ->with('success', "L'agence « {$agence->name} » a été créée avec succès.");

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    // ─── Détail ───────────────────────────────────────────────────────────────

    public function show(string $codeAgence): RedirectResponse
    {
        $agence = $this->agenceService->findByCode($codeAgence);

        abort_if(!$agence, 404, 'Agence introuvable.');

        return redirect()->route('admin.agences.index', [
            'selected_agence_id' => $agence->agence_id,
        ]);
    }

    // ─── Formulaire édition ───────────────────────────────────────────────────

    public function edit(string $id): Response
    {
        $agence = $this->agenceService->findByIdOrCode($id);

        abort_if(!$agence, 404);

        $agence = $this->agenceService->findWithRelations($agence->agence_id);

        [$regions,$villes, $responsables, $tarifications] = $this->getFormDependencies();

        return Inertia::render('Admin/Agences/Form', [
            'mode'           => 'edit',
            'agence'         => $agence,
            'regions'        => $regions,
            'responsables'   => $responsables,
            'tarifications'  => $tarifications,
            'responsable_mode' => 'existing',
            'villes'         =>$villes
        ]);



    }

    // ─── UPDATE ───────────────────────────────────────────────────────────────

    public function update(AgenceRequest $request, string $id): RedirectResponse
    {
        try {
            $agence = $this->agenceService->updateAgence($id, $request->validated());

            return redirect()
                ->route('admin.agences.index', [
                    'selected_agence_id' => $agence->agence_id,
                ])
                ->with('success', "L'agence a été mise à jour avec succès.");

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    // ─── DESTROY ─────────────────────────────────────────────────────────────

    public function destroy(string $id): RedirectResponse
    {
        try {
            $this->agenceService->deleteAgence($id);

            return redirect()
                ->route('admin.agences.index')
                ->with('success', 'Agence supprimée avec succès.');

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // ─── Changer statut (AJAX ou redirect) ───────────────────────────────────

    public function changerStatut(Request $request, string $id)
    {
        $request->validate([
            'statut' => 'required|in:en_demo,active,desactive',
            'motif'  => 'nullable|string|max:500',
        ]);

        try {
            $this->agenceService->changerStatut($id, $request->statut, $request->motif);

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Statut mis à jour.']);
            }

            return back()->with('success', 'Statut de l\'agence mis à jour.');

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return back()->with('error', $e->getMessage());
        }
    }

    public function abonnementAgence(): Response
    {
        $agence = $this->agenceService->getAll([], 1);
        $selectedAgence = $agence instanceof \Illuminate\Pagination\AbstractPaginator
            ? ($agence->items()[0] ?? null)
            : ($agence[0] ?? null);

        return Inertia::render('Admin/Agences/Abonnement', [
            'agence' => $selectedAgence,
        ]);
    }

    public function life(string $code): Response
    {
        $agence = $this->agenceService->findByCode($code);

        abort_if(!$agence, 404, 'Agence introuvable.');

        $agence = $this->agenceService->findWithRelations($agence->agence_id);

        $activities = collect()
            ->merge($agence->abonnementHistoriques->take(5)->map(function ($historique) {
                return [
                    'title' => ucfirst($historique->action ?? 'Mise à jour'),
                    'description' => trim(($historique->notes ?: 'Historique de facturation') . ' · ' . ($historique->montant_ht ? number_format($historique->montant_ht, 0, ',', ' ') . ' FCFA' : '')),
                    'date' => optional($historique->created_at)->format('d/m/Y H:i'),
                    'color' => $historique->action === 'renouvellement' ? 'green' : 'blue',
                    'user' => $historique->action_par ?? 'Système',
                ];
            }))
            ->merge($agence->transactions->take(5)->map(function ($transaction) {
                return [
                    'title' => 'Transaction ' . ($transaction->reference ?? 'sans référence'),
                    'description' => number_format((float) ($transaction->montant_ttc ?? 0), 0, ',', ' ') . ' FCFA',
                    'date' => optional($transaction->created_at)->format('d/m/Y H:i'),
                    'color' => $transaction->statut === 'validee' ? 'green' : ($transaction->statut === 'en_attente' ? 'yellow' : 'red'),
                    'user' => $transaction->created_by ?? 'Système',
                ];
            }))
            ->sortByDesc('date')
            ->values();

        $stats = [
            'nb_locataires' => 0,
            'nb_proprietaires' => 0,
            'nb_biens' => 0,
            'nb_lots' => 0,
            'nb_tickets' => 0,
            'nb_tickets_resolus' => 0,
        ];

        return Inertia::render('Admin/Agences/Life', [
            'agence' => $agence,
            'activities' => $activities,
            'stats' => $stats,
        ]);
    }

    // ─── Helpers privés ───────────────────────────────────────────────────────

    /**
     * Retourne les dépendances communes aux formulaires create/edit.
     */
    private function buildDemoAgences(): \Illuminate\Support\Collection
    {
        return collect([
            [
                'agence_id' => 'demo-agence-001',
                'code_agence' => 'AGR2026DEMO',
                'name' => 'Nova Habitat Cocody',
                'adresse' => 'Riviera 2, boulevard Latrille, Cocody',
                'tel1' => '07 00 00 00 01',
                'tel2' => '01 00 00 00 01',
                'email1' => 'contact@novahabitat.ci',
                'email2' => 'direction@novahabitat.ci',
                'statut' => 'en_demo',
                'is_principale' => true,
                'region_id' => 1,
                'ville_id' => 1,
                'region' => (object) ['id' => 1, 'name' => 'Lagunes'],
                'ville' => (object) ['id' => 1, 'name' => 'Abidjan'],
                'responsable' => (object) [
                    'id_users' => 'usr-demo-001',
                    'name' => 'Awa Konan',
                    'tel1' => '07 07 07 07 07',
                    'email' => 'awa@novahabitat.ci',
                ],
                'abonnement' => (object) [
                    'abonnement_id' => 'plan-demo-premium',
                    'name' => 'Essentiel Demo',
                    'description' => 'Agence de démonstration pour visualiser le tableau de bord admin.',
                    'prix_ht' => 49900,
                    'modules' => [
                        (object) ['nom' => 'SMS'],
                        (object) ['nom' => 'WhatsApp'],
                    ],
                ],
                'abonnement_start' => now()->subDays(12)->toDateString(),
                'abonnement_end' => now()->addMonths(3)->toDateString(),
                'duree_mois' => 6,
                'montant_base_total' => 99800,
                'montant_total' => 119800,
                'capital_social' => 1500000,
                'forme_juridique' => 'SARL',
                'numero_identification' => 'CI-ABJ-2026-001',
                'tva_number' => 'TVA-2026-001',
                'nombre_employes' => 8,
                'pays' => 'Côte d\'Ivoire',
                'siege_social' => 'Riviera 2, Cocody',
                'date_creation' => now()->subYear()->toDateString(),
                'modules_payants' => [
                    ['nom' => 'SMS', 'statut' => 'Actif'],
                    ['nom' => 'WhatsApp', 'statut' => 'Actif'],
                    ['nom' => 'Assistant IA', 'statut' => 'Inactif'],
                ],
                'proprietaires' => 14,
                'locataires' => 36,
                'utilisateurs' => 9,
                'biens' => 22,
                'lots' => 58,
                'tickets' => 4,
                'tickets_resolus' => 3,
            ],
        ]);
    }

    private function hydrateAgenceSubscriptions($agences)
    {
        if ($agences instanceof \Illuminate\Pagination\AbstractPaginator) {
            $agences->setCollection($agences->getCollection()->map(fn ($agence) => $this->hydrateAgenceSubscription($agence)));

            return $agences;
        }

        return collect($agences)->map(fn ($agence) => $this->hydrateAgenceSubscription($agence));
    }

    private function hydrateAgenceSubscription($agence)
    {
        if (!is_object($agence) || !isset($agence->agence_id)) {
            return $agence;
        }

        $snapshot = Abonnement::query()
            ->where('type', 'subscription')
            ->where('agence_id', $agence->agence_id)
            ->with(['nouvelAbonnement'])
            ->first();

        $subscriptionSource = $snapshot ?? $agence->abonnement ?? null;
        if ($subscriptionSource) {
            $subscriptionSource->setAttribute('prix_ht', $subscriptionSource->prix_ht ?? $subscriptionSource->prix_mensuel_ht ?? 0);
            $subscriptionSource->setAttribute('modules', $this->extractModulesFromFeatures($subscriptionSource->features ?? []));
            $agence->setRelation('subscription', $subscriptionSource);
        }

        $agence->setAttribute(
            'modules_payants',
            $this->extractModulesAsItems($subscriptionSource?->features ?? [])
        );

        if ($snapshot) {
            $agence->setAttribute('abonnement_start', $snapshot->nouvelle_date_debut ?? $agence->abonnement_start ?? null);
            $agence->setAttribute('abonnement_end', $snapshot->nouvelle_date_fin ?? $agence->abonnement_end ?? null);
            $agence->setAttribute('duree_mois', $snapshot->duree_mois ?? $agence->duree_mois ?? null);
            $agence->setAttribute('montant_total', $snapshot->montant_ht ?? $agence->montant_total ?? 0);
        }

        return $agence;
    }

    private function extractModulesFromFeatures($features): array
    {
        if (is_string($features)) {
            $decoded = json_decode($features, true);
            $features = json_last_error() === JSON_ERROR_NONE ? $decoded : [$features];
        }

        if (!is_array($features)) {
            return [];
        }

        return collect($features)
            ->map(function ($item) {
                if (is_string($item)) {
                    return trim($item);
                }

                if (is_array($item)) {
                    return $item['label']
                        ?? $item['name']
                        ?? $item['nom']
                        ?? $item['libelle']
                        ?? null;
                }

                return null;
            })
            ->filter()
            ->values()
            ->all();
    }

    private function extractModulesAsItems($features): array
    {
        return collect($this->extractModulesFromFeatures($features))
            ->map(fn ($name) => ['nom' => $name])
            ->all();
    }

    private function getFormDependencies(): array
    {
        $regions      = \App\Models\Region::orderBy('name')->get();
        $villes = \App\Models\Ville::orderBy('name')->get();

        $responsables= $this->userService->getResponsables();

        $tarifications = $this->configurationTarifService->getTarifsPourFormulaire();

        // Plan tarifaire unique (à adapter si table abonnements)
//        $tarifications = [
//            'plan_nom'          => 'Plan Standard',
//            'plan_description'  => 'Accès complet à toutes les fonctionnalités de base',
//            'plan_prix_mensuel' => 50000,
//            'durees'            => [1, 3, 6, 12, 24, 36],
//            'modules'           => [
//                ['id' => 1, 'label' => 'Module Comptabilité',  'prix_mensuel' => 15000],
//                ['id' => 2, 'label' => 'Module Reporting',     'prix_mensuel' => 10000],
//                ['id' => 3, 'label' => 'Module API',           'prix_mensuel' => 20000],
//                ['id' => 4, 'label' => 'Support prioritaire',  'prix_mensuel' => 8000],
//                ['id' => 5, 'label' => 'Sauvegarde avancée',   'prix_mensuel' => 5000],
//                ['id' => 6, 'label' => 'Multi-utilisateurs+',  'prix_mensuel' => 12000],
//            ],
//        ];

        return [$regions,$villes, $responsables, $tarifications];
    }
}









//
//namespace App\Http\Controllers\Admin\Agence;
//
//use App\Http\Controllers\Controller;
//
//use App\Services\AgenceService;
//use App\Services\UserService;
//use App\Http\Requests\AgenceRequest;
//use App\Services\ConfigurationTarifService;
//
//use Illuminate\Http\Request;
//use Illuminate\Support\Facades\Auth;
//use App\Models\Admin;
//use App\Models\Agence;
//use App\Models\Region;
//use App\Models\Ville;
//use Illuminate\Support\Facades\View;
//use Illuminate\Support\Facades\DB;
//use Illuminate\Support\Facades\Schema;
//use App\Repositories\Interfaces\AgenceRepositoryInterface;
//use App\Repositories\Interfaces\UserRepositoryInterface;
//
//class AgenceController extends Controller
//{
//
//
//    /**
//     * @var AgenceRepositoryInterface
//     */
//    protected AgenceRepositoryInterface $agenceRepository;
//    protected UserRepositoryInterface $userRepository;
//
//    /**
//     * @var AgenceService
//     */
//    protected AgenceService $agenceService;
//    protected UserService $userService;
//    protected ConfigurationTarifService $configurationTarifService;
//
//    /**
//     * Constructor
//     */
//    public function __construct(
//        AgenceRepositoryInterface $agenceRepository,
//        AgenceService $agenceService,UserService $userService,ConfigurationTarifService $configurationTarifService
//    ) {
//        $this->agenceRepository = $agenceRepository;
//        $this->agenceService = $agenceService;
//        $this->userService = $userService;
//        $this->configurationTarifService = $configurationTarifService;
//
//        // Appliquer les middlewares
//        $this->middleware('permission:view_agences')->only(['index', 'show', 'search', 'export', 'life']); // Ajout de 'life'
//        $this->middleware('permission:create_agences')->only(['create', 'store']);
//        $this->middleware('permission:edit_agences')->only(['edit', 'update']);
//        $this->middleware('permission:delete_agences')->only(['destroy', 'bulkDelete']);
//        $this->middleware('permission:manage_abonnements')->only(['renewSubscription', 'changeStatut']);
//    }
//
////    public function index()
////    {
////        return view('admin.agences.index');
////    }
//
//    /**
//     * Afficher la liste des agences
//     *
//     * @param Request $request
//     * @return View
//     */
//    public function index(Request $request)
//    {
//
//        $filters = $request->only([
//            'statut',
//            'region_id',
//            'ville_id',
//            'is_principale',
//            'abonnement_expire_bientot',
//            'abonnement_expire',
//            'sort_by',
//            'sort_order',
//            'per_page'
//        ]);
//
//        $perPage = $request->get('per_page', 15);
//        $agences = $this->agenceRepository->getAll($filters, $perPage);
//        $agencesCollection = collect($agences instanceof \Illuminate\Pagination\AbstractPaginator ? $agences->items() : $agences);
//        $agenceStats = $this->buildAgenceStats($agencesCollection->pluck('agence_id')->filter()->values());
//
//        // Pour les formulaires de filtre
//        $regions = Region::orderBy('name')->get();
//        $statuts = ['en_demo' => 'En démo', 'active' => 'Actif', 'desactive' => 'Désactivé'];
//
//        return view('admin.agences.index', compact('agences', 'filters', 'regions', 'statuts', 'agenceStats'));
//    }
//
//    private function buildAgenceStats($agencyIds): array
//    {
//        return collect($agencyIds)->mapWithKeys(function ($agenceId) {
//            return [
//                $agenceId => [
//                    'proprietaires' => $this->countTableRows('proprietaires', $agenceId),
//                    'locataires' => $this->countTableRows('locataires', $agenceId),
//                    'utilisateurs' => $this->countTableRows('users', $agenceId),
//                    'biens' => $this->countTableRows('biens', $agenceId),
//                    'lots' => $this->countTableRows('lots', $agenceId),
//                    'tickets' => $this->countTableRows('tickets', $agenceId),
//                    'tickets_resolus' => $this->countTableRowsWhere('tickets', $agenceId, 'statut', 'resolu'),
//                ],
//            ];
//        })->toArray();
//    }
//
//// Nouvelle méthode pour compter avec condition
//    private function countTableRowsWhere(string $table, string $agenceId, string $column, string $value): int
//    {
//        if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'agence_id')) {
//            return 0;
//        }
//
//        return DB::table($table)
//            ->where('agence_id', $agenceId)
//            ->where($column, $value)
//            ->count();
//    }
//
//    private function countTableRows(string $table, string $agenceId): int
//    {
//        if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'agence_id')) {
//            return 0;
//        }
//
//        return DB::table($table)->where('agence_id', $agenceId)->count();
//    }
//
//    /**
//     * Afficher le formulaire de création
//     *
//     * @return View
//     */
////    public function create(): View
////    {
////        $abonnements = \App\Models\Abonnement::where('is_active', true)->get();
////        $regions = \App\Models\Region::with('villes')->orderBy('nom')->get();
////        $responsables = \App\Models\User::whereHas('role', function($q) {
////            $q->where('name', 'responsable_agence');
////        })->get();
////
////        return view('admin.agences.create', compact('abonnements', 'regions', 'responsables'));
////    }
//
//
//
//
//    private function mockAgences()
//    {
//        return collect([
//            [
//                'nom' => 'Pros Immobilier Cocody',
//                'code' => 'AGC-001',
//                'responsable' => 'Jean Kouassi',
//                'email' => 'contact@cocody.com',
//                'telephone' => '0700000001',
//                'ville' => 'Abidjan',
//                'commune' => 'Cocody',
//                'adresse' => 'Cocody Riviera 2',
//                'abonnement_base' => 'Vanille',
//                'modules_payants' => [
//                    [
//                        'nom' => 'SMS',
//                        'type' => 'Communication',
//                        'statut' => 'Actif',
//                        'tarification' => '25 000 FCFA / mois',
//                    ],
//                    [
//                        'nom' => 'WhatsApp',
//                        'type' => 'Communication',
//                        'statut' => 'Actif',
//                        'tarification' => '15 000 FCFA / mois',
//                    ],
//                ],
//                'statut' => 'Active',
//                'date_creation' => '2024-01-10',
//            ],
//            [
//                'nom' => 'Pros Immobilier Plateau',
//                'code' => 'AGC-002',
//                'responsable' => 'Awa Konan',
//                'email' => 'contact@plateau.com',
//                'telephone' => '0700000002',
//                'ville' => 'Abidjan',
//                'commune' => 'Plateau',
//                'adresse' => 'Avenue Houdaille',
//                'abonnement_base' => 'Vanille',
//                'modules_payants' => [],
//                'statut' => 'Active',
//                'date_creation' => '2024-02-15',
//            ],
//        ]);
//    }
//
//    public function create()
//    {
//
//      //  dd($this->configurationTarifService->getTarifsPourFormulaire());
//
//        return view('admin.agences.form', [
//            'mode' => 'create',
//            'regions' => Region::orderBy('name')->get(),
//            'agence' => null,
//            'ville' => null,
//            'responsables' => $this->userService->getResponsables(),
//            'tarifications' => $this->configurationTarifService->getTarifsPourFormulaire(),
//        ]);
//    }
//
////    public function show($code)
////    {
////        $agence = $this->mockAgences()->firstWhere('code', $code);
////
////        if (!$agence) {
////            abort(404);
////        }
////
////        return view('admin.agences.show', compact('agence'));
////    }
//
//    public function edit($code)
//    {
//
//        return view('admin.agences.form', [
//            'mode' => 'edit',
//            'regions' => Region::orderBy('name')->get(),
//            'agence' => $this->agenceService->getAgenceByCode($code),
//            'ville' => Ville::orderBy('name')->get(),
//            'responsables' => $this->userService->getResponsables(),
//            'tarifications' => $this->configurationTarifService->getTarifsPourFormulaire(),
//        ]);
//    }
//
//
//
//    public function abonnementAgence()
//    {
//
//        return view('admin.agences.agence-abonnement');
//    }
//
//
//
//    public function store(AgenceRequest $request): RedirectResponse
//    {
//        try {
//            $agence = $this->agenceService->createAgence($request->validated());
//
//            return redirect()
//                ->route('admin.agences.show', $agence->code_agence)
//                ->with('success', "L'agence « {$agence->name} » a été créée avec succès.");
//
//        } catch (\Exception $e) {
//            return back()
//                ->withInput()
//                ->with('error', $e->getMessage());
//        }
//    }
//
//    // ─── Détail ───────────────────────────────────────────────────────────────
//
//    public function show(string $codeAgence): View
//    {
//        $agence = $this->agenceService->repository->findByCode($codeAgence);
//
//        abort_if(!$agence, 404, 'Agence introuvable.');
//
//        $agence       = $this->agenceService->repository->findWithRelations($agence->agence_id);
//        $transactions = $this->transactionService->getTransactionsPourAgence($agence->agence_id, 10);
//        $totalEncaisse = $this->transactionService->getTotalEncaisseParAgence($agence->agence_id);
//
//        return view('admin.agences.show', compact('agence', 'transactions', 'totalEncaisse'));
//    }
//
//
//
//    public function update(AgenceRequest $request, $code)
//    {
//        dd($request->all());
//
//    }
//
//}
