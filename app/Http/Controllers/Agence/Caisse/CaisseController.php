<?php

namespace App\Http\Controllers\Agence\Caisse;

use App\Http\Controllers\Controller;
use App\Models\ModePaiement;
use App\Models\CaisseSession;
use App\Services\Agence\CaisseClotureService;
use App\Repositories\Agence\Interfaces\TransactionAgenceRepositoryInterface;
use App\Repositories\Agence\Interfaces\MaintenanceRepositoryInterface;
use Carbon\Carbon;
use App\Models\ProprietaireAgence;
use App\Models\ProprietaireLot;
use App\Models\TypeMaintenance;
use App\Models\Maintenance;
use App\Models\Maintenancier;
use App\Models\Batiment;
use App\Models\Porte;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CaisseController extends Controller
{
    protected $transactionRepository;
    protected $maintenanceRepository;

    public function __construct(TransactionAgenceRepositoryInterface $transactionRepository,MaintenanceRepositoryInterface $maintenanceRepository, private CaisseClotureService $caisseClotureService)
    {
        $this->transactionRepository = $transactionRepository;
        $this->maintenanceRepository = $maintenanceRepository;
    }

    public function index()
    {
        $agenceId = $this->agenceId();
        $this->caisseClotureService->cloturerCaissesExpirees($agenceId);
        $session = CaisseSession::where('agence_id', $agenceId)
            ->latest('opened_at')
            ->first();
        $startOfDay = $session?->opened_at ?? now()->startOfDay();
        $endOfDay = $session?->closed_at ?? now();

        $transactions = $this->transactionRepository->getByAgence($agenceId)
            ->filter(function ($transaction) use ($startOfDay, $endOfDay) {
                if (empty($transaction->date_transaction)) {
                    return false;
                }

                $transactionDate = $transaction->date_transaction instanceof Carbon
                    ? $transaction->date_transaction
                    : Carbon::parse($transaction->date_transaction);

                return $transactionDate->between($startOfDay, $endOfDay, true);
            });

        $stats = [
            'total_transactions' => $transactions->count(),
            'total_versements' => (float) $transactions->sum('montant_global_verser'),
            'total_loyers_payes' => (float) $transactions->where('type_transaction', 'loyer')->sum('montant_loyer_payer'),
            'total_arrieres' => (float) $transactions->sum('arriere_actuel'),
            'total_arrieres_payes' => (float) $transactions->sum('montant_arriere_payer'),
            'total_avances' => (float) $transactions->sum('montant_avance_payer'),
            'total_reversements' => $transactions->where('is_reversement', 1)->count(),
        ];

        // Calcul des totaux pour le composant React
        $totalEntrees = $transactions->whereIn('type_transaction', ['loyer', 'vente'])->sum('montant_global_verser');
        $totalSorties = $transactions->whereIn('type_transaction', ['maintenance', 'depense'])->sum('montant_global_verser');

        // Préparer les transactions pour le composant React
        $transactionsData = $transactions->map(function ($transaction) {
            return [
                'id' => $transaction->transaction_agence_id,
                'date' => $transaction->date_transaction ? $transaction->date_transaction->format('d/m/Y') : '',
                'time' => $transaction->date_transaction ? $transaction->date_transaction->format('H:i') : '',
                'type' => in_array($transaction->type_transaction, ['loyer', 'vente']) ? 'in' : 'out',
                'label' => $this->getTransactionLabel($transaction),
                'reference' => $transaction->transaction_agence_id,
                'amount' => (float) $transaction->montant_global_verser,
            ];
        })->toArray();

        // Préparer les loyers
        $loyersData = $transactions->where('type_transaction', 'loyer')->map(function ($transaction) {
            return [
                'id' => $transaction->transaction_agence_id,
                'date' => $transaction->date_transaction ? $transaction->date_transaction->format('d/m/Y') : '',
                'time' => $transaction->date_transaction ? $transaction->date_transaction->format('H:i') : '',
                'tenant' => $transaction->locataire->name ?? 'N/A',
                'property' => $this->getPropertyName($transaction),
                'period' => $transaction->mois_payer ?? 'N/A',
                'amount' => (float) $transaction->montant_global_verser,
                'mode' => $transaction->modePaiement->name ?? 'N/A',
            ];
        })->toArray();

        // Préparer les maintenances
        $maintenancesData = $transactions->where('type_transaction', 'maintenance')->map(function ($transaction) {
            return [
                'id' => $transaction->transaction_agence_id,
                'date' => $transaction->date_transaction ? $transaction->date_transaction->format('d/m/Y') : '',
                'time' => $transaction->date_transaction ? $transaction->date_transaction->format('H:i') : '',
                'property' => $this->getPropertyName($transaction),
                'type' => 'Maintenance',
                'provider' => 'N/A', // À adapter selon votre structure
                'cost' => (float) $transaction->montant_global_verser,
                'status' => 'Terminée', // À adapter selon votre structure
            ];
        })->toArray();

        // Préparer les dépenses
        $depensesData = $transactions->where('type_transaction', 'depense')->map(function ($transaction) {
            return [
                'id' => $transaction->transaction_agence_id,
                'date' => $transaction->date_transaction ? $transaction->date_transaction->format('d/m/Y') : '',
                'time' => $transaction->date_transaction ? $transaction->date_transaction->format('H:i') : '',
                'category' => 'Divers',
                'label' => $this->getTransactionLabel($transaction),
                'proof' => 'Reçu',
                'amount' => (float) $transaction->montant_global_verser,
            ];
        })->toArray();

        // Préparer les ventes
        $ventesData = $transactions->where('type_transaction', 'vente')->map(function ($transaction) {
            return [
                'id' => $transaction->transaction_agence_id,
                'date' => $transaction->date_transaction ? $transaction->date_transaction->format('d/m/Y') : '',
                'time' => $transaction->date_transaction ? $transaction->date_transaction->format('H:i') : '',
                'client' => 'Client N/A',
                'property' => $transaction->propriete_id ?? 'N/A',
                'reference' => $transaction->transaction_agence_id,
                'amount' => (float) $transaction->montant_global_verser,
                'status' => 'Payée',
            ];
        })->toArray();

        // Préparer le résumé par mode de paiement
        $modesPaiement = ModePaiement::all();
           
        $summaryData = [];
        foreach ($modesPaiement as $mode) {
            $modeTransactions = $transactions->where('mode_paiement_id', $mode->mode_paiement_id);
            $totalMontant = $modeTransactions->sum('montant_global_verser');
            $nbTransactions = $modeTransactions->count();
            
            if ($nbTransactions > 0) {
                $commission = $totalMontant * 0.10;
                $net = $totalMontant - $commission;
                
                $summaryData[] = [
                    'mode' => $mode->name,
                    'total' => (float) $totalMontant,
                    'count' => $nbTransactions,
                    'commission' => (float) $commission,
                    'net' => (float) $net,
                    'icon' => $this->getModeIcon($mode->name),
                    'accent' => $this->getModeAccent($mode->name),
                ];
            }
        }

        // Si aucun résumé n'est disponible, ajouter des données par défaut
        if (empty($summaryData)) {
            $summaryData = [
                [
                    'mode' => 'Espèces',
                    'total' => 0,
                    'count' => 0,
                    'commission' => 0,
                    'net' => 0,
                    'icon' => 'Wallet',
                    'accent' => 'bg-[#eaf4fb] text-[#00559b]',
                ],
                [
                    'mode' => 'WAVE',
                    'total' => 0,
                    'count' => 0,
                    'commission' => 0,
                    'net' => 0,
                    'icon' => 'Smartphone',
                    'accent' => 'bg-[#00559b]/10 text-[#00559b]',
                ],
                [
                    'mode' => 'Orange Money',
                    'total' => 0,
                    'count' => 0,
                    'commission' => 0,
                    'net' => 0,
                    'icon' => 'CreditCard',
                    'accent' => 'bg-[#fff2e6] text-[#c2410c]',
                ],
            ];
        }

        return Inertia::render('Agence/Caisse/Index', [
            'caisseOuverte' => $session !== null && $session->closed_at === null,
            'soldeOuverture' => (float) ($session?->solde_ouverture ?? 0),
            'sessionCaisse' => $session ? [
                'openedAt' => $session->opened_at?->toISOString(),
                'closedAt' => $session->closed_at?->toISOString(),
                'soldeFermeture' => $session->solde_fermeture !== null ? (float) $session->solde_fermeture : null,
                'ecart' => $session->ecart !== null ? (float) $session->ecart : null,
            ] : null,
            'totalEntrees' => (float) $totalEntrees,
            'totalSorties' => (float) $totalSorties,
            'transactions' => $transactionsData,
            'loyers' => $loyersData,
            'maintenance' => $maintenancesData,
            'depenses' => $depensesData,
            'ventes' => $ventesData,
            'summary' => $summaryData,
            'statistiques' => $stats,
        ]);
    }

    public function ouvrir(Request $request)
    {
        $data = $request->validate([
            'solde_ouverture' => ['required', 'numeric', 'min:0', 'max:9999999999999.99'],
            'observation' => ['nullable', 'string', 'max:2000'],
        ]);

        $agenceId = $this->agenceId();
        $this->caisseClotureService->cloturerCaissesExpirees($agenceId);

        DB::transaction(function () use ($agenceId, $data) {
            DB::table('agences')->where('agence_id', $agenceId)->lockForUpdate()->first();

            if (CaisseSession::where('agence_id', $agenceId)->whereNull('closed_at')->exists()) {
                throw ValidationException::withMessages([
                    'solde_ouverture' => 'Une caisse est déjà ouverte pour cette agence.',
                ]);
            }

            CaisseSession::create([
                'agence_id' => $agenceId,
                'opened_by' => $this->userId(),
                'solde_ouverture' => $data['solde_ouverture'],
                'observation_ouverture' => $data['observation'] ?? null,
                'opened_at' => now(),
            ]);
        });

        return to_route('agence.caisse.index')
            ->with('success', 'La caisse a été ouverte avec succès.');
    }

    public function fermer(Request $request)
    {
        $data = $request->validate([
            'solde_fermeture' => ['required', 'numeric', 'min:0', 'max:9999999999999.99'],
            'observation' => ['nullable', 'string', 'max:2000'],
        ]);

        $agenceId = $this->agenceId();

        DB::transaction(function () use ($agenceId, $data) {
            $session = CaisseSession::where('agence_id', $agenceId)
                ->whereNull('closed_at')
                ->lockForUpdate()
                ->first();

            if (! $session) {
                throw ValidationException::withMessages([
                    'solde_fermeture' => 'Aucune caisse ouverte à clôturer.',
                ]);
            }

            $transactions = $this->transactionRepository->getByAgence($agenceId)
                ->filter(fn ($transaction) => $transaction->date_transaction
                    && Carbon::parse($transaction->date_transaction)->gte($session->opened_at));
            $entrees = (float) $transactions->whereIn('type_transaction', ['loyer', 'vente'])->sum('montant_global_verser');
            $sorties = (float) $transactions->whereIn('type_transaction', ['maintenance', 'depense'])->sum('montant_global_verser');
            $theorique = (float) $session->solde_ouverture + $entrees - $sorties;

            $session->update([
                'closed_by' => $this->userId(),
                'solde_theorique' => $theorique,
                'solde_fermeture' => $data['solde_fermeture'],
                'ecart' => (float) $data['solde_fermeture'] - $theorique,
                'observation_fermeture' => $data['observation'] ?? null,
                'closed_at' => now(),
            ]);
        });

        return to_route('agence.caisse.index')
            ->with('success', 'La caisse a été clôturée avec succès.');
    }

   
  
public function maintenance(Request $request)
{
    $agenceId = $this->agenceId();

    // Récupérer les paramètres de filtre
    $proprietaireFilter = $request->input('proprietaire_id');
    $lotFilter = $request->input('lot_id');
    $searchTerm = $request->input('search');

    // Récupérer les maintenances depuis la table maintenance avec filtres
    $maintenancesQuery = $this->maintenanceRepository->getByAgence($agenceId);

    // Filtre par propriétaire
    if ($proprietaireFilter) {
        $maintenancesQuery->where('proprietaire_id', $proprietaireFilter);
    }

    // Filtre par lot
    if ($lotFilter) {
        $maintenancesQuery->where('lot_id', $lotFilter);
    }

    // Recherche globale
    if ($searchTerm) {
        $maintenancesQuery->where(function ($query) use ($searchTerm) {
            $query->where('titre', 'LIKE', "%{$searchTerm}%")
                ->orWhere('description', 'LIKE', "%{$searchTerm}%")
                ->orWhereHas('proprietaire', function ($q) use ($searchTerm) {
                    $q->where('name', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('tel1', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('tel2', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('email', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('numpiece', 'LIKE', "%{$searchTerm}%");
                })
                ->orWhereHas('lot', function ($q) use ($searchTerm) {
                    $q->where('name', 'LIKE', "%{$searchTerm}%");
                });
        });
    }

    // Récupérer les maintenances avec les relations
    $maintenances = $maintenancesQuery;

    // Formater les données pour la vue
    $maintenancesData = $maintenances->map(function ($maintenance) {
        return [
            'maintenance_id' => (string) $maintenance->maintenance_id,
            'titre' => $maintenance->titre,
            'description' => $maintenance->description_generale,
            'proprietaire_id' => $maintenance->proprietaire_id ? (string) $maintenance->proprietaire_id : '',
            'lot_id' => $maintenance->lot_id ? (string) $maintenance->lot_id : '',
            'propriete_id' => $maintenance->propriete_id ? (string) $maintenance->propriete_id : '',
            'batiment_id' => $maintenance->batiment_id ? (string) $maintenance->batiment_id : '',
            'porte_id' => $maintenance->porte_id ? (string) $maintenance->porte_id : '',
            'prise_en_charge_par' => $maintenance->prise_en_charge_par ?? 'proprietaire',
            'statut' => $maintenance->statut ?? 'en_cours',
            'montant_global' => (float) ($maintenance->montant_global ?? 0),
            'created_at' => $maintenance->created_at?->toISOString(),
            'details' => $maintenance->details->map(function ($detail) {
                return [
                    'maintenance_detail_id' => (string) $detail->maintenance_detail_id,
                    'maintenancier_id' => $detail->maintenancier_id ? (string) $detail->maintenancier_id : '',
                    'type_intervention_id' => $detail->type_intervention_id ? (string) $detail->type_intervention_id : '',
                    'montant' => (float) ($detail->montant ?? 0),
                    'date_debut' => $detail->date_debut?->toISOString(),
                    'date_fin' => $detail->date_fin?->toISOString(),
                    'priorite' => $detail->priorite ?? 'normale',
                    'description' => $detail->description ?? '',
                    'note' => $detail->note ?? '',
                    'statut' => $detail->statut ?? 'en_cours',
                ];
            })->toArray(),
        ];
    })->toArray();

    // Récupérer les transactions de maintenance pour les stats
    $transactions = $this->transactionRepository->getByAgence($agenceId)->where('type_transaction', 'maintenance');
    $totalMaintenance = $transactions->sum('montant_global_verser');

    // ✅ Récupérer UNIQUEMENT les propriétaires qui ont des maintenances
    $proprietairesIdsAvecMaintenances = Maintenance::where('agence_id', $agenceId)
        ->whereNotNull('proprietaire_id')
        ->distinct()
        ->pluck('proprietaire_id')
        ->toArray();

    $proprietaires = ProprietaireAgence::whereHas('proprietaire', function ($q) use ($agenceId, $proprietairesIdsAvecMaintenances) {
        $q->where('agence_id', $agenceId)
            ->where('is_active', 1)
            ->whereIn('proprietaire_id', $proprietairesIdsAvecMaintenances); // ✅ Filtrer par propriétaires avec maintenances
    })
    ->with(['proprietaire'])
    ->get()
    ->map(function ($item) {
        return [
            'proprietaire_id' => (string) $item->proprietaire_id,
            'name' => $item->proprietaire?->name ?? 'Propriétaire sans nom',
        ];
    })
    ->unique('proprietaire_id')
    ->values()
    ->toArray();

    // ✅ Récupérer UNIQUEMENT les lots qui ont des maintenances
    $lotsIdsAvecMaintenances = Maintenance::where('agence_id', $agenceId)
        ->whereNotNull('lot_id')
        ->distinct()
        ->pluck('lot_id')
        ->toArray();

    $lots = ProprietaireLot::where('agence_id', $agenceId)
        ->whereHas('baux', function ($q) {
            $q->where('is_active', 1);
        })
        ->whereIn('propreietaire_lot_id', $lotsIdsAvecMaintenances) // ✅ Filtrer par lots avec maintenances
        ->select('propreietaire_lot_id', 'name', 'proprietaire_id')
        ->get()
        ->map(fn($item) => [
            'propreietaire_lot_id' => (string) $item->propreietaire_lot_id,
            'name' => $item->name,
            'proprietaire_id' => (string) $item->proprietaire_id,
        ])
        ->toArray();

    // Bâtiments
    $batiments = Batiment::where('agence_id', $agenceId)
        ->select('batiment_id', 'name', 'propriete_id')
        ->get()
        ->map(fn($item) => [
            'batiment_id' => (string) $item->batiment_id,
            'name' => $item->name,
            'propriete_id' => (string) $item->propriete_id,
        ])
        ->toArray();

    // Portes
    $portes = Porte::where('agence_id', $agenceId)
        ->select('porte_id', 'numero_porte', 'batiment_id')
        ->get()
        ->map(fn($item) => [
            'porte_id' => (string) $item->porte_id,
            'numero_porte' => $item->numero_porte,
            'batiment_id' => (string) $item->batiment_id,
        ])
        ->toArray();

    // Types d'intervention
    $typesIntervention = TypeMaintenance::select('type_maintenance_id', 'name', 'description')
        ->get()
        ->map(fn($item) => [
            'type_maintenance_id' => (string) $item->type_maintenance_id,
            'name' => $item->name,
            'description' => $item->description,
        ])
        ->toArray();

    // Maintenanciers
    $maintenanciers = Maintenancier::where('agence_id', $agenceId)
        ->select('maintenancier_id', 'name', 'fonction_maintenance_id')
        ->get()
        ->map(fn($item) => [
            'maintenancier_id' => (string) $item->maintenancier_id,
            'name' => $item->name,
            'fonction_maintenance_id' => (string) $item->fonction_maintenance_id,
        ])
        ->toArray();

    return Inertia::render('Agence/Caisse/Maintenance', [
        'caisseOuverte' => $this->caisseEstOuverte($agenceId),
        'maintenances' => $maintenancesData,
        'totalMaintenance' => (float) $totalMaintenance,
        'proprietaires' => $proprietaires,
        'lots' => $lots,
        'batiments' => $batiments,
        'portes' => $portes,
        'typesIntervention' => $typesIntervention,
        'maintenanciers' => $maintenanciers,
    ]);
}


    public function loyer()
    {
        $agenceId = $this->agenceId();
        $transactions = $this->transactionRepository->getByAgence($agenceId);
        
        $loyersData = $transactions->where('type_transaction', 'loyer')->map(function ($transaction) {
            return [
                'id' => $transaction->transaction_agence_id,
                'date' => $transaction->date_transaction ? $transaction->date_transaction->format('d/m/Y') : '',
                'time' => $transaction->date_transaction ? $transaction->date_transaction->format('H:i') : '',
                'tenant' => $transaction->locataire->name ?? 'N/A',
                'property' => $this->getPropertyName($transaction),
                'period' => $transaction->mois_payer ?? 'N/A',
                'amount' => (float) $transaction->montant_global_verser,
                'mode' => $transaction->modePaiement->name ?? 'N/A',
            ];
        })->toArray();

        $totalLoyers = $transactions->where('type_transaction', 'loyer')->sum('montant_global_verser');

        return Inertia::render('Agence/Caisse/Loyer', [
            'caisseOuverte' => $this->caisseEstOuverte($agenceId),
            'loyers' => $loyersData,
            'totalLoyers' => (float) $totalLoyers,
        ]);
    }

    public function depenseAgence()
    {
        $agenceId = $this->agenceId();
        $transactions = $this->transactionRepository->getByAgence($agenceId);
        
        $depensesData = $transactions->where('type_transaction', 'depense')->map(function ($transaction) {
            return [
                'id' => $transaction->transaction_agence_id,
                'date' => $transaction->date_transaction ? $transaction->date_transaction->format('d/m/Y') : '',
                'time' => $transaction->date_transaction ? $transaction->date_transaction->format('H:i') : '',
                'category' => $transaction->modePaiement->name ?? 'Divers',
                'label' => $this->getTransactionLabel($transaction),
                'proof' => 'Reçu',
                'amount' => (float) $transaction->montant_global_verser,
            ];
        })->toArray();

        $totalDepenses = $transactions->where('type_transaction', 'depense')->sum('montant_global_verser');

        return Inertia::render('Agence/Caisse/DepenseAgence', [
            'caisseOuverte' => $this->caisseEstOuverte($agenceId),
            'depenses' => $depensesData,
            'totalDepenses' => (float) $totalDepenses,
        ]);
    }

    public function venteBien()
    {
        $agenceId = $this->agenceId();
        $transactions = $this->transactionRepository->getByAgence($agenceId);

        $ventesData = $transactions->where('type_transaction', 'vente')->map(function ($transaction) {
            return [
                'id' => $transaction->transaction_agence_id,
                'date' => $transaction->date_transaction ? $transaction->date_transaction->format('d/m/Y') : '',
                'time' => $transaction->date_transaction ? $transaction->date_transaction->format('H:i') : '',
                'client' => 'Client N/A',
                'property' => $transaction->propriete_id ?? 'N/A',
                'reference' => $transaction->transaction_agence_id,
                'amount' => (float) $transaction->montant_global_verser,
                'status' => 'Payée',
            ];
        })->toArray();

        $totalVentes = $transactions->where('type_transaction', 'vente')->sum('montant_global_verser');

        $ownerLinks = \App\Models\ProprietaireAgence::query()
            ->where('agence_id', $agenceId)
            ->where('is_active', true)
            ->with(['proprietaire' => function ($query) use ($agenceId) {
                $query->with(['lots' => fn ($lotQuery) => $lotQuery->where('agence_id', $agenceId)->where('is_for_sale', true), 'proprietes' => function ($propertyQuery) use ($agenceId) {
                    $propertyQuery->where('agence_id', $agenceId)
                        ->where('is_actif', true)
                        ->with(['batiments' => function ($batimentQuery) {
                            $batimentQuery->with(['portes' => function ($porteQuery) {
                                $porteQuery->where('is_actif', true)
                                    ->with('tarifActif');
                            }]);
                        }]);
                }]);
            }])
            ->get();

        $saleOwners = $ownerLinks->map(function ($link) {
            $owner = $link->proprietaire;
            if (!$owner) {
                return null;
            }

            $lotsForSale = $owner->lots->map(fn ($lot) => $this->saleTarget(
                'lot-'.$lot->propreietaire_lot_id,
                $lot->name ?? 'Lot',
                $lot->adresse ?? '',
                'Lot entier',
                (float) $lot->sale_price,
                $lot->num_lot ?? ''
            ));

            $propertiesForSale = $owner->proprietes->flatMap(function ($property) {
                if (($property->sale_type ?? 'none') === 'whole') {
                    return [$this->saleTarget(
                        'property-'.$property->propriete_id,
                        $property->reference ?? 'Propriété',
                        $property->adresse_complete ?? '',
                        'Propriété entière',
                        (float) $property->sale_price,
                        $property->reference ?? ''
                    )];
                }

                if (($property->sale_type ?? 'none') !== 'by_door') {
                    return [];
                }

                return $property->batiments->flatMap(function ($building) use ($property) {
                    return $building->portes
                        ->filter(fn ($door) => $door->is_actif && ! $door->is_occupe && $door->is_allocation === false && $door->tarifActif?->mt_vente)
                        ->map(fn ($door) => $this->saleTarget(
                            'door-'.$door->porte_id,
                            ($property->reference ?? 'Propriété').' · '.($door->numero_porte ?? 'Porte'),
                            $property->adresse_complete ?? '',
                            'Vente par porte',
                            (float) $door->tarifActif->mt_vente,
                            $door->numero_porte ?? ''
                        ));
                });
            });

            $properties = $lotsForSale->concat($propertiesForSale)->values();

            return [
                'id' => $owner->proprietaire_id,
                'name' => $owner->name ?? 'Propriétaire',
                'phone' => $owner->tel1 ?? '',
                'properties' => $properties->all(),
            ];
        })->filter(function ($owner) {
            return $owner !== null && !empty($owner['properties']);
        })->values();
        return Inertia::render('Agence/Caisse/VenteBien', [
            'caisseOuverte' => $this->caisseEstOuverte($agenceId),
            'ventes' => $ventesData,
            'totalVentes' => (float) $totalVentes,
            'saleOwners' => $saleOwners->all(),
        ]);
    }

    private function saleTarget(string $id, string $title, string $location, string $type, float $price, string $reference): array
    {
        return [
            'id' => $id, 'title' => $title, 'location' => $location, 'type' => $type,
            'price' => $price, 'commission' => 0, 'ownerAmount' => 0, 'buyer' => '',
            'status' => 'Disponible', 'badge' => 'Disponible', 'reference' => $reference,
            'date' => '', 'observation' => 'Disponible à la vente', 'forSale' => true,
        ];
    }

    // Méthodes privées utilitaires

    private function getTransactionLabel($transaction)
    {
        switch ($transaction->type_transaction) {
            case 'loyer':
                $locataire = $transaction->locataire->name ?? 'N/A';
                $porte = $transaction->porte->numero_porte ?? 'N/A';
                $mois = $transaction->mois_payer ?? 'N/A';
                return "Paiement loyer — {$locataire} ({$porte}, {$mois})";
            case 'maintenance':
                $porte = $transaction->porte->numero_porte ?? 'N/A';
                return "Maintenance — {$porte}";
            case 'depense':
                $porte = $transaction->porte->numero_porte ?? 'N/A';
                return "Dépense agence — {$porte}";
            case 'vente':
                $proprieteId = $transaction->propriete_id ?? 'N/A';
                return "Vente de bien — {$proprieteId}";
            default:
                return "Transaction";
        }
    }

    private function getPropertyName($transaction)
    {
        if ($transaction->porte && $transaction->porte->numero_porte) {
            return $transaction->porte->numero_porte;
        }
        
        if ($transaction->batiment_id) {
            $batiment = DB::table('batiment')->where('batiment_id', $transaction->batiment_id)->first();
            return $batiment->name ?? 'N/A';
        }
        
        return 'N/A';
    }

    private function getModeIcon($modeName)
    {
        $icons = [
            'Espèces' => 'Wallet',
            'WAVE' => 'Smartphone',
            'Orange Money' => 'CreditCard',
            'Mobile Money' => 'Smartphone',
            'Virement' => 'Banknote',
        ];
        
        return $icons[$modeName] ?? 'Wallet';
    }

    private function getModeAccent($modeName)
    {
        $accents = [
            'Espèces' => 'bg-[#eaf4fb] text-[#00559b]',
            'WAVE' => 'bg-[#00559b]/10 text-[#00559b]',
            'Orange Money' => 'bg-[#fff2e6] text-[#c2410c]',
            'Mobile Money' => 'bg-[#eaf4fb] text-[#00559b]',
            'Virement' => 'bg-[#eef8df] text-[#4d8500]',
        ];
        
        return $accents[$modeName] ?? 'bg-[#f1f5f9] text-[#5f7182]';
    }

    private function safeMaintenanceRows(string $agenceId): array
    {
        if (! Schema::hasTable('maintenance')) {
            return [];
        }

        return DB::table('maintenance')
            ->where('agence_id', $agenceId)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get([
                'maintenance_id',
                'titre',
                'description',
                'statut',
                'montant_global',
                'proprietaire_id',
                'lot_id',
                'batiment_id',
                'porte_id',
                'prise_en_charge_par',
                'created_at',
            ])
            ->map(function ($row) {
                return [
                    'id' => $row->maintenance_id,
                    'date' => $row->created_at ? date('d/m/Y', strtotime($row->created_at)) : '',
                    'time' => $row->created_at ? date('H:i', strtotime($row->created_at)) : '',
                    'property' => $row->batiment_id ?? 'N/A',
                    'type' => $row->titre ?? 'Maintenance',
                    'provider' => $row->prise_en_charge_par ?? 'N/A',
                    'cost' => (float) ($row->montant_global ?? 0),
                    'status' => $row->statut ?? 'En cours',
                    'description' => $row->description ?? '',
                    'proprietaire_id' => $row->proprietaire_id,
                    'lot_id' => $row->lot_id,
                    'batiment_id' => $row->batiment_id,
                    'porte_id' => $row->porte_id,
                ];
            })
            ->toArray();
    }

    private function safeTableRows(string $table, array $columns): array
    {
        if (! Schema::hasTable($table)) {
            return [];
        }

        return DB::table($table)
            ->orderBy($columns[1] ?? $columns[0])
            ->get($columns)
            ->map(function ($row) use ($columns) {
                // Transformer en tableau associatif avec des clés value/label pour les selects
                $result = [];
                $firstKey = $columns[0];
                $secondKey = $columns[1] ?? $columns[0];
                
                $result['value'] = $row->$firstKey;
                $result['label'] = $row->$secondKey;
                
                // Ajouter les autres champs
                foreach ($columns as $key) {
                    if ($key !== $firstKey && $key !== $secondKey) {
                        $result[$key] = $row->$key;
                    }
                }
                
                return $result;
            })
            ->toArray();
    }

    private function agenceId(): string
    {
        return getInfoAgent()->users->agence_id;
    }

    private function userId(): ?string
    {
        return getInfoAgent()->users->id_users ?? null;
    }

    private function caisseEstOuverte(string $agenceId): bool
    {
        $this->caisseClotureService->cloturerCaissesExpirees($agenceId);

        return CaisseSession::where('agence_id', $agenceId)->whereNull('closed_at')->exists();
    }
}
