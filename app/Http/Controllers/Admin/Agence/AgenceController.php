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
use App\Models\LocataireAgence;
use App\Models\ProprietaireAgence;
use App\Models\ProprietaireLot;
use App\Models\Propriete;
use App\Models\SupportTicket;
use App\Models\Transaction;
use App\Models\User;
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

        $agenceStats = $this->getAgencyLifeStats(
            $agenceItems->pluck('agence_id')->filter()->values()->all()
        );

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

            return redirect()->route('admin.agences.index', [
                'selected_agence_id' => $id,
            ])->with('success', 'Statut de l\'agence mis à jour.');

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return redirect()->route('admin.agences.index', [
                'selected_agence_id' => $id,
            ])->with('error', $e->getMessage());
        }
    }

    public function abonnementAgence(Request $request): Response
    {
        $agencyKey = $request->string('agence')->toString()
            ?: $request->string('selected_agence_id')->toString();
        $selectedAgence = $agencyKey
            ? $this->agenceService->findByIdOrCode($agencyKey)
            : Agence::query()->latest()->first();

        abort_if(! $selectedAgence, 404, 'Agence introuvable.');

        $selectedAgence->load(['abonnement', 'transactions']);
        $selectedAgence = $this->hydrateAgenceSubscription($selectedAgence);
        $transactions = $selectedAgence->transactions->sortByDesc('created_at')->values();
        $validatedTransactions = $transactions->where('statut', 'validee');
        $latestTransaction = $transactions->first();
        $currentTotal = (float) ($latestTransaction?->montant_ttc ?? 0);
        $currentModules = (float) ($latestTransaction?->montant_options_ht ?? 0);
        $currentBase = (float) ($latestTransaction?->montant_base_ht ?? 0);
        if ($currentBase <= 0 && $currentTotal > $currentModules) {
            $currentBase = $currentTotal - $currentModules;
        }

        return Inertia::render('Admin/Agences/Abonnement', [
            'agence' => $selectedAgence,
            'billing' => [
                'total_facture' => (float) $validatedTransactions->sum('montant_ttc'),
                'paiements_reussis' => $validatedTransactions->count(),
                'membre_depuis' => optional($selectedAgence->created_at)->locale('fr')->diffForHumans(null, true),
                'montant_base' => $currentBase,
                'montant_modules' => $currentModules,
                'montant_courant' => $currentTotal,
                'statut' => $selectedAgence->statut === 'active'
                    && $selectedAgence->abonnement_end
                    && $selectedAgence->abonnement_end->isFuture() ? 'Actif' : 'Inactif',
                'historique' => $transactions->take(12)->map(fn (Transaction $transaction) => [
                    'reference' => $transaction->reference,
                    'debut' => optional($transaction->periode_debut)->format('d/m/Y'),
                    'fin' => optional($transaction->periode_fin)->format('d/m/Y'),
                    'montant' => (float) $transaction->montant_ttc,
                    'statut' => match ($transaction->statut) {
                        'validee' => 'Payé',
                        'en_attente' => 'En attente',
                        default => 'Annulé',
                    },
                ])->values(),
            ],
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
                    'sort_date' => optional($historique->created_at)?->timestamp ?? 0,
                    'color' => $historique->action === 'renouvellement' ? 'green' : 'blue',
                    'user' => $historique->action_par ?? 'Système',
                ];
            }))
            ->merge($agence->transactions->take(5)->map(function ($transaction) {
                return [
                    'title' => 'Transaction ' . ($transaction->reference ?? 'sans référence'),
                    'description' => number_format((float) ($transaction->montant_ttc ?? 0), 0, ',', ' ') . ' FCFA',
                    'date' => optional($transaction->created_at)->format('d/m/Y H:i'),
                    'sort_date' => optional($transaction->created_at)?->timestamp ?? 0,
                    'color' => $transaction->statut === 'validee' ? 'green' : ($transaction->statut === 'en_attente' ? 'yellow' : 'red'),
                    'user' => $transaction->created_by ?? 'Système',
                ];
            }))
            ->sortByDesc('date')
            ->values();

        $agenceId = $agence->agence_id;
        $agencyStats = $this->getAgencyLifeStats([$agenceId])[$agenceId] ?? [];
        $stats = [
            'nb_locataires' => $agencyStats['locataires'] ?? 0,
            'nb_proprietaires' => $agencyStats['proprietaires'] ?? 0,
            'nb_biens' => $agencyStats['biens'] ?? 0,
            'nb_lots' => $agencyStats['lots'] ?? 0,
            'nb_utilisateurs' => $agencyStats['utilisateurs'] ?? 0,
            'nb_tickets' => $agencyStats['tickets'] ?? 0,
            'nb_tickets_resolus' => $agencyStats['tickets_resolus'] ?? 0,
        ];

        $operationalActivities = collect()
            ->merge(Propriete::where('agence_id', $agenceId)->latest()->take(5)->get()->map(fn ($item) => [
                'title' => 'Bien ajouté',
                'description' => $item->reference ?: ($item->adresse_complete ?: 'Nouvelle propriété'),
                'date' => optional($item->created_at)->format('d/m/Y H:i'),
                'sort_date' => optional($item->created_at)?->timestamp ?? 0,
                'color' => 'blue',
                'user' => $item->created_by ?: 'Système',
            ]))
            ->merge(ProprietaireAgence::with('proprietaire')->where('agence_id', $agenceId)->latest()->take(5)->get()->map(fn ($item) => [
                'title' => 'Propriétaire ajouté',
                'description' => $item->proprietaire?->name ?? 'Nouveau propriétaire',
                'date' => optional($item->created_at)->format('d/m/Y H:i'),
                'sort_date' => optional($item->created_at)?->timestamp ?? 0,
                'color' => 'green',
                'user' => $item->created_by ?: 'Système',
            ]))
            ->merge(LocataireAgence::with('locataire')->where('agence_id', $agenceId)->latest()->take(5)->get()->map(fn ($item) => [
                'title' => 'Locataire ajouté',
                'description' => $item->locataire?->name ?? 'Nouveau locataire',
                'date' => optional($item->created_at)->format('d/m/Y H:i'),
                'sort_date' => optional($item->created_at)?->timestamp ?? 0,
                'color' => 'green',
                'user' => $item->created_by ?: 'Système',
            ]))
            ->merge(SupportTicket::where('agence_id', $agenceId)->latest()->take(5)->get()->map(fn ($item) => [
                'title' => 'Ticket '.$item->reference,
                'description' => $item->sujet,
                'date' => optional($item->created_at)->format('d/m/Y H:i'),
                'sort_date' => optional($item->created_at)?->timestamp ?? 0,
                'color' => in_array($item->statut, ['resolu', 'ferme'], true) ? 'green' : 'yellow',
                'user' => $item->demandeur_id ?: 'Système',
            ]));

        $activities = $activities
            ->map(fn ($item) => $item + ['sort_date' => $item['sort_date'] ?? 0])
            ->merge($operationalActivities)
            ->sortByDesc('sort_date')
            ->take(20);

        $actorIds = $activities->pluck('user')
            ->filter(fn ($actor) => $actor && $actor !== 'Système')
            ->unique()
            ->values();
        $actorNames = User::whereIn('id_users', $actorIds)->pluck('name', 'id_users')
            ->merge(Admin::whereIn('id_admin', $actorIds)->pluck('name', 'id_admin'));
        $activities = $activities->map(function ($item) use ($actorNames) {
            $item['user'] = $actorNames->get($item['user'], $item['user'] ?: 'Système');

            return collect($item)->except('sort_date')->all();
        })->values();

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
    private function getAgencyLifeStats(array $agencyIds): array
    {
        $agencyIds = collect($agencyIds)->filter()->unique()->values();
        if ($agencyIds->isEmpty()) {
            return [];
        }

        $groupedCount = static function ($query, string $distinctColumn) use ($agencyIds): array {
            return $query
                ->whereIn('agence_id', $agencyIds)
                ->select('agence_id')
                ->selectRaw("COUNT(DISTINCT {$distinctColumn}) AS aggregate")
                ->groupBy('agence_id')
                ->pluck('aggregate', 'agence_id')
                ->map(fn ($value) => (int) $value)
                ->all();
        };

        $owners = $groupedCount(ProprietaireAgence::query(), 'proprietaire_id');
        $tenants = $groupedCount(LocataireAgence::query(), 'locataire_id');
        $properties = $groupedCount(Propriete::query(), 'propriete_id');
        $lots = $groupedCount(ProprietaireLot::query(), 'propreietaire_lot_id');
        $users = $groupedCount(User::query(), 'id_users');
        $tickets = $groupedCount(SupportTicket::query(), 'support_ticket_id');
        $resolvedTickets = $groupedCount(
            SupportTicket::query()->whereIn('statut', ['resolu', 'ferme']),
            'support_ticket_id'
        );

        return $agencyIds->mapWithKeys(fn ($agencyId) => [$agencyId => [
            'proprietaires' => $owners[$agencyId] ?? 0,
            'locataires' => $tenants[$agencyId] ?? 0,
            'biens' => $properties[$agencyId] ?? 0,
            'lots' => $lots[$agencyId] ?? 0,
            'utilisateurs' => $users[$agencyId] ?? 0,
            'tickets' => $tickets[$agencyId] ?? 0,
            'tickets_resolus' => $resolvedTickets[$agencyId] ?? 0,
        ]])->all();
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
            ->latest('created_at')
            ->first();

        $subscriptionSource = $snapshot ?? $agence->abonnement ?? null;
        $latestTransaction = Transaction::where('agence_id', $agence->agence_id)
            ->latest('created_at')
            ->first();
        $selectedModuleIds = collect($latestTransaction?->options_souscrites ?? [])
            ->map(fn ($id) => (string) $id)
            ->all();
        $moduleItems = $this->extractModulesAsItems(
            $subscriptionSource?->features ?? [],
            $selectedModuleIds
        );
        if ($subscriptionSource) {
            $subscriptionSource->setAttribute('prix_ht', $subscriptionSource->prix_ht ?? $subscriptionSource->prix_mensuel_ht ?? 0);
            $subscriptionSource->setAttribute('description_text', $this->plainText($subscriptionSource->description));
            $subscriptionSource->setAttribute('modules', collect($moduleItems)->pluck('nom')->all());
            $subscriptionSource->setAttribute('modules_count', count($moduleItems));
            $subscriptionSource->setAttribute('modules_available', count($this->extractModulesAsItems($subscriptionSource->features ?? [])));
            $agence->setRelation('subscription', $subscriptionSource);
        }

        $agence->setAttribute('modules_payants', $moduleItems);

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

    private function extractModulesAsItems($features, array $selectedIds = []): array
    {
        if (is_string($features)) {
            $features = json_decode($features, true) ?: [];
        }

        return collect(is_array($features) ? $features : [])
            ->filter(function ($item) use ($selectedIds) {
                if (! is_array($item) || ($item['actif'] ?? true) === false) {
                    return false;
                }

                return $selectedIds === []
                    || in_array((string) ($item['id'] ?? ''), $selectedIds, true);
            })
            ->map(fn ($item) => [
                'id' => $item['id'] ?? null,
                'nom' => $item['label'] ?? $item['name'] ?? $item['nom'] ?? $item['libelle'] ?? 'Module',
                'prix' => (float) ($item['prix_mensuel'] ?? $item['prix'] ?? 0),
            ])
            ->all();
    }

    private function plainText(?string $value): string
    {
        return trim(html_entity_decode(strip_tags($value ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
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
