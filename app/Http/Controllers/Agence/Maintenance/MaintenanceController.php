<?php

namespace App\Http\Controllers\Agence\Maintenance;

use App\Http\Controllers\Controller;
use App\Services\Agence\MaintenanceService;
use App\Services\Agence\TypeMaintenanceService;
use App\Services\Agence\MaintenancierService;
use App\Services\Agence\FonctionMaintenanceService;
use App\Repositories\Agence\Interfaces\MaintenanceDetailRepositoryInterface;
use App\Repositories\Interfaces\ProprietaireRepositoryInterface;
use App\Models\MaintenanceCategory;
use App\Models\Batiment;
use App\Models\Porte;
use App\Models\Propriete;
use App\Models\ProprietaireLot;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\TypePiece;

class MaintenanceController extends Controller
{
    protected               $service;
        protected           $typeMaintenanceService;
        protected             $maintenancierService;
     protected       $fonctionMaintenanceService;
     protected  $proprietaireRepo;
     protected  $maintenanceDetailRepo;
    public function __construct(
          MaintenanceService              $service,
          TypeMaintenanceService          $typeMaintenanceService,
          MaintenancierService            $maintenancierService,
          FonctionMaintenanceService      $fonctionMaintenanceService,
          ProprietaireRepositoryInterface $proprietaireRepo,
          MaintenanceDetailRepositoryInterface $maintenanceDetailRepo
    ) {
            $this->service = $service;
            $this->typeMaintenanceService = $typeMaintenanceService;
            $this->maintenancierService = $maintenancierService;
            $this->fonctionMaintenanceService = $fonctionMaintenanceService;
            $this->proprietaireRepo = $proprietaireRepo;
            $this->maintenanceDetailRepo = $maintenanceDetailRepo;

    }

    // =========================================================================
    // VUE LISTE
    // =========================================================================

    public function index(Request $request)
    {
        $filters = $request->only([
            'statut', 'maintenancier_id', 'agence_id',
            'date_debut', 'date_fin', 'per_page',
        ]);

        $agencyFilters = ['agence_id' => $this->agenceId()];
        $filters = array_merge($filters, $agencyFilters);

        $maintenances        = $this->toArray($this->service->getAllMaintenances($filters));
        $maintenancier       = $this->toArray($this->maintenancierService->getAllMaintenanciers($agencyFilters));
        $typeMaintenance     = $this->toArray($this->typeMaintenanceService->getAllTypes($agencyFilters));
        $fonctionMaintenance = $this->toArray($this->fonctionMaintenanceService->getAllFonctions($agencyFilters));
        $proprietaires       = $this->toArray($this->proprietaireRepo->getAllByAgence($this->agenceId()));
        $lots = $this->toArray(
            ProprietaireLot::query()
                ->with(['proprietaire'])
                ->where('agence_id', $this->agenceId())
                ->orderBy('name')
                ->get([
                    'propreietaire_lot_id',
                    'name',
                    'proprietaire_id',
                    'agence_id',
                    'num_lot',
                    'num_ilot',
                    'adresse',
                ])
        );
        $proprietes = $this->toArray(
            Propriete::query()
                ->with(['proprietaire', 'lot'])
                ->where('agence_id', $this->agenceId())
                ->orderBy('reference')
                ->get([
                    'propriete_id',
                    'proprietaire_id',
                    'agence_id',
                    'lot_id',
                    'type_propriete_id',
                    'reference',
                    'description',
                    'adresse_complete',
                ])
        );
        $batiments = $this->toArray(
            Batiment::query()
                ->with(['propriete'])
                ->whereHas('propriete', fn ($query) => $query->where('agence_id', $this->agenceId()))
                ->orderBy('name')
                ->get([
                    'batiment_id',
                    'propriete_id',
                    'agence_id',
                    'name',
                    'description',
                    'nbre_etages',
                ])
        );
        $portes = $this->toArray(
            Porte::query()
                ->with(['batiment.propriete'])
                ->whereHas('batiment.propriete', fn ($query) => $query->where('agence_id', $this->agenceId()))
                ->orderBy('numero_porte')
                ->get([
                    'porte_id',
                    'batiment_id',
                    'type_porte_id',
                    'numero_porte',
                    'agence_id',
                    'is_occupe',
                    'is_actif',
                ])
        );
        $typePiece =TypePiece::all();
        $maintenanceCategories = MaintenanceCategory::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['maintenance_category_id', 'name', 'description']);

        // Données statiques injectées dans le formulaire (select JS-chainé)
        $typesInterventionStatiques  = $typeMaintenance;
        $maintenancierStatiques      = $maintenancier;

       // dd($maintenances);

        return Inertia::render('Agence/Maintenance/Index', [
            'maintenances' => $maintenances,
            'maintenanciers' => $maintenancier,
            'typesMaintenance' => $typeMaintenance,
            'fonctionsMaintenance' => $fonctionMaintenance,
            'proprietaires' => $proprietaires,
            'lots' => $lots,
            'proprietes' => $proprietes,
            'batiments' => $batiments,
            'portes' => $portes,
            'typesInterventionStatiques' => $typesInterventionStatiques,
            'maintenancierStatiques' => $maintenancierStatiques,
            'typePiece' => $typePiece,
            'maintenanceCategories' => $maintenanceCategories,
            'filters' => $filters,
        ]);
    }

    // =========================================================================
    // SHOW (JSON — appel AJAX)
    // =========================================================================

    public function show($id)
    {
        $maintenance = $this->service->getMaintenance($id);

        if (!$maintenance) {
            return response()->json(['success' => false, 'message' => 'Maintenance non trouvée'], 404);
        }

        return response()->json(['success' => true, 'data' => $maintenance]);
    }

    // =========================================================================
    // STORE  — crée la maintenance + tous ses détails en une transaction
    // =========================================================================

    public function store(Request $request)
    {
        $validated = $request->validate([
            // --- En-tête maintenance ---
            'titre'              => 'required|string|max:255',
            'description_generale' => 'nullable|string',
            'proprietaire_id'    => 'required|exists:proprietaires,proprietaire_id',
            'lot_id'             => 'nullable|exists:propietaire_lots,propreietaire_lot_id',
            'propriete_id'       => 'nullable|exists:propriete,propriete_id',
            'batiment_id'        => 'nullable|exists:batiment,batiment_id',
            'porte_id'           => 'nullable|exists:porte,porte_id',
            'prise_en_charge_par'=> ['nullable', Rule::in(['proprietaire', 'locataire', 'agence'])],

            // --- Détails (tableau) ---
            'details'                              => 'required|array|min:1',
            'details.*.type_intervention_id'       => 'required|exists:type_maintenances,type_maintenance_id',
            'details.*.maintenancier_id'            => 'required|exists:maintenanciers,maintenancier_id',
            'details.*.date_debut'                 => 'required|date',
            'details.*.date_fin'                   => 'nullable|date|after_or_equal:details.*.date_debut',
            'details.*.priorite'                   => ['nullable', Rule::in(['basse', 'normale', 'haute'])],
            'details.*.prix'                       => 'required|numeric|min:0',
            'details.*.description'                => 'nullable|string',
        ]);

        // Ajoute l'agence courante à la donnée principale
        $data = array_merge(
            $validated,
            [
                'agence_id'   => $this->agenceId(),
                'created_by'  => $this->userId(),
                'description' => $validated['description_generale'] ?? null,
            ]
        );

        try {
            $maintenance = $this->service->createMaintenance($data, $validated['details']);

            return back()->with('success' , 'Intervention planifiée avec succès');

//            return response()->json([
//                'success' => true,
//                'message' => 'Intervention planifiée avec succès',
//                'data'    => $maintenance,
//            ], 201);

        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // =========================================================================
    // UPDATE — met à jour l'en-tête ET resynchronise les détails
    // =========================================================================

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'titre'               => 'sometimes|string|max:255',
            'description_generale'=> 'nullable|string',
            'statut'              => ['sometimes', Rule::in(['en_attente', 'en_cours', 'terminer', 'annule'])],
            'prise_en_charge_par' => ['nullable', Rule::in(['proprietaire', 'locataire', 'agence'])],
            'proprietaire_id'     => 'sometimes|exists:proprietaires,proprietaire_id',
            'lot_id'              => 'nullable|exists:propietaire_lots,propreietaire_lot_id',
            'propriete_id'        => 'nullable|exists:propriete,propriete_id',
            'batiment_id'         => 'nullable|exists:batiment,batiment_id',
            'porte_id'            => 'nullable|exists:porte,porte_id',

            // Détails optionnels : si absents, on ne touche pas aux détails existants
            'details'                        => 'sometimes|array|min:1',
            'details.*.type_intervention_id' => 'required_with:details|exists:type_maintenances,type_maintenance_id',
            'details.*.maintenancier_id'     => 'required_with:details|exists:maintenanciers,maintenancier_id',
            'details.*.date_debut'           => 'required_with:details|date',
            'details.*.date_fin'             => 'nullable|date',
            'details.*.priorite'             => ['nullable', Rule::in(['basse', 'normale', 'haute'])],
            'details.*.prix'                 => 'required_with:details|numeric|min:0',
            'details.*.description'          => 'nullable|string',
        ]);

        $data    = array_diff_key($validated, ['details' => null]);
        $details = $validated['details'] ?? [];

        if (isset($data['description_generale'])) {
            $data['description'] = $data['description_generale'];
            unset($data['description_generale']);
        }

        try {
            $maintenance = $this->service->updateMaintenance($id, $data, $details);

            if (!$maintenance) {
                return response()->json(['success' => false, 'message' => 'Maintenance non trouvée'], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Maintenance mise à jour avec succès',
                'data'    => $maintenance,
            ]);

        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // =========================================================================
    // DELETE
    // =========================================================================

    public function destroy($id)
    {
        try {
            $result = $this->service->deleteMaintenance($id);

            if (!$result) {
                return response()->json(['success' => false, 'message' => 'Maintenance non trouvée'], 404);
            }

            return response()->json(['success' => true, 'message' => 'Maintenance supprimée avec succès']);

        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // =========================================================================
    // ACTIONS MÉTIER
    // =========================================================================

    public function changerStatut(Request $request, $id)
    {
        $request->validate([
            'statut' => ['required', Rule::in(['en_attente', 'en_cours', 'terminer', 'annule'])],
        ]);

        try {
            $maintenance = $this->service->changerStatut($id, $request->statut);

            return response()->json([
                'success' => true,
                'message' => 'Statut mis à jour avec succès',
                'data'    => $maintenance,
            ]);

        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function changerDetailStatut(Request $request, $id)
    {
        $request->validate([
            'statut' => ['required', Rule::in(['en_attente', 'en_cours', 'terminer', 'annule'])],
        ]);

        try {
            $detail = $this->maintenanceDetailRepo->updateStatut($id, $request->statut);
            $maintenance = $this->service->recalculerStatutDepuisDetails($detail->maintenance_id);

            return response()->json([
                'success' => true,
                'message' => 'Statut du detail mis a jour avec succes',
                'data'    => $detail,
                'maintenance' => $maintenance,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function statistiques()
    {
        try {
            $stats = $this->service->getStatistiques();
            return response()->json(['success' => true, 'data' => $stats]);

        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // =========================================================================
    // HELPERS PRIVÉS
    // =========================================================================

    /**
     * Extrait un tableau pur depuis une Collection ou un Paginator.
     */
    private function toArray($result): array
    {
        if ($result instanceof \Illuminate\Contracts\Pagination\Paginator) {
            return $result->items();
        }

        if ($result instanceof \Illuminate\Support\Collection) {
            return $result->toArray();
        }

        return (array) $result;
    }

    private function agenceId(): string
    {
        return getInfoAgent()->users->agence_id;
    }

    private function userId(): string
    {
        return getInfoAgent()->users->id_users ?? 'system';
    }


    // =========================================================================
// VUES SHOW (pages complètes)
// =========================================================================

    /**
     * Affiche la page de détail d'un maintenancier
     */





    public function getMaintenancierJson($id)
    {
        try {
            $maintenancier = $this->maintenancierService->getMaintenancier($id);

            if (!$maintenancier) {
                return response()->json([
                    'success' => false,
                    'message' => 'Maintenancier non trouvé'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $this->toArray($maintenancier)
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupère une intervention au format JSON pour la modification
     */
    public function getInterventionJson($id)
    {
        try {
            $intervention = $this->service->getMaintenance($id);

            if (!$intervention) {
                return response()->json([
                    'success' => false,
                    'message' => 'Intervention non trouvée'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $this->toArray($intervention)
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupère les tâches d'une intervention au format JSON
     */
    public function getInterventionTasks($id)
    {
        try {
            $intervention = $this->service->getMaintenance($id);

            if (!$intervention) {
                return response()->json([
                    'success' => false,
                    'message' => 'Intervention non trouvée'
                ], 404);
            }

            // Récupérer les détails de l'intervention
            // Adapter selon la structure de votre modèle
            $details = [];
            if (is_object($intervention) && method_exists($intervention, 'details')) {
                $details = $intervention->details;
            } elseif (is_array($intervention) && isset($intervention['details'])) {
                $details = $intervention['details'];
            }

            return response()->json([
                'success' => true,
                'data' => $this->toArray($details)
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Met à jour un maintenancier
     */
    public function updateMaintenancier(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'entreprise' => 'nullable|string|max:255',
            'email' => 'required|email|max:255',
            'tel1' => 'required|string|max:20',
            'tel2' => 'nullable|string|max:20',
            'fonction_maintenance_id' => 'required|exists:fonction_maintenance,fonction_maintenance_id',
            'statut' => ['nullable', Rule::in(['0', '1'])],
        ]);

        try {
            $maintenancier = $this->maintenancierService->updateMaintenancier($id, $validated);

            if (!$maintenancier) {
                return response()->json([
                    'success' => false,
                    'message' => 'Maintenancier non trouvé'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Maintenancier mis à jour avec succès',
                'data' => $this->toArray($maintenancier)
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Met à jour une fonction de maintenancier
     */
    public function updateFonction(Request $request, $id)
    {
        $validated = $request->validate([
            'libelle' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        try {
            $fonction = $this->fonctionMaintenanceService->updateFonction($id, $validated);

            if (!$fonction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Fonction non trouvée'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Fonction mise à jour avec succès',
                'data' => $this->toArray($fonction)
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Met à jour un type d'intervention
     */
    public function updateTypeIntervention(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'categorie' => 'required|string|max:255',
            'duree_estimee' => 'nullable|numeric|min:0',
        ]);

        try {
            $type = $this->typeMaintenanceService->updateType($id, $validated);

            if (!$type) {
                return response()->json([
                    'success' => false,
                    'message' => 'Type d\'intervention non trouvé'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Type d\'intervention mis à jour avec succès',
                'data' => $this->toArray($type)
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    

    /**
     * Affiche la page de détail d'une intervention
     */
    public function showIntervention($id)
    {
        // Récupérer les données de l'intervention
        $intervention = $this->service->getMaintenance($id);

        if (!$intervention) {
            abort(404, 'Intervention non trouvée');
        }

        // Convertir en tableau pour la vue
        $intervention = $this->toArray($intervention);

        return Inertia::render('Agence/Maintenance/ShowIntervention', [
            'intervention' => $intervention,
        ]);
    }
    
}
