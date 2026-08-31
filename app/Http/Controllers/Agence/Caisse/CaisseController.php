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
use App\Models\TypePiece;
use App\Models\Maintenance;
use App\Models\TransactionAgence;
use App\Models\CaisseDepense;
use App\Models\Acheteur;
use App\Models\VenteBien;
use App\Models\VenteEcheance;
use App\Models\Propriete;
use App\Services\Agence\SaleInvoiceService;
use App\Services\Agence\CashHistoryPdfService;
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

    public function __construct(TransactionAgenceRepositoryInterface $transactionRepository,MaintenanceRepositoryInterface $maintenanceRepository, private CaisseClotureService $caisseClotureService, private SaleInvoiceService $saleInvoiceService, private CashHistoryPdfService $cashHistoryPdfService)
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

        $allTransactions = $this->transactionRepository->getByAgence($agenceId);
        $transactions = $allTransactions
            ->filter(function ($transaction) use ($startOfDay, $endOfDay) {
                // Une date de paiement saisie via un champ HTML `date` arrive à
                // 00:00:00. Elle ne doit pas exclure une opération réellement
                // enregistrée après l'ouverture de la caisse. La session est donc
                // filtrée sur l'heure comptable de création, avec repli sur
                // date_transaction pour les anciennes lignes sans created_at.
                $accountingDate = $transaction->created_at ?: $transaction->date_transaction;

                if (empty($accountingDate)) {
                    return false;
                }

                $transactionDate = $accountingDate instanceof Carbon
                    ? $accountingDate
                    : Carbon::parse($accountingDate);

                return $transactionDate->between($startOfDay, $endOfDay, true);
            });

        $maintenanceById = Maintenance::query()
            ->where('agence_id', $agenceId)
            ->whereIn(
                'maintenance_id',
                $transactions->where('type_transaction', 'maintenance')->pluck('reference')->filter()->all()
            )
            ->with(['proprietaire', 'lot', 'porte', 'propriete', 'details.maintenancier', 'details.typeIntervention'])
            ->get()
            ->keyBy(fn ($maintenance) => (string) $maintenance->maintenance_id);

        $saleById = VenteBien::query()
            ->where('agence_id', $agenceId)
            ->whereIn(
                'id_vente',
                $transactions->where('type_transaction', 'vente')->pluck('reference')->filter()->all()
            )
            ->with(['acheteur', 'proprietaire', 'lot', 'propriete.lot', 'porte'])
            ->get()
            ->keyBy(fn ($sale) => (string) $sale->getKey());

        $contractsByTenantAndDoor = \App\Models\LocataireAgence::withoutGlobalScopes()
            ->where('agence_id', $agenceId)
            ->latest('created_at')
            ->get()
            ->groupBy(fn ($contract) => $contract->locataire_id.'|'.$contract->porte_id);

        $contractForTransaction = static function ($transaction) use ($contractsByTenantAndDoor) {
            $contracts = $contractsByTenantAndDoor->get($transaction->locataire_id.'|'.$transaction->porte_id, collect());

            return $contracts->first(function ($contract) use ($transaction) {
                return ! $contract->created_at
                    || ! $transaction->date_transaction
                    || $contract->created_at->lte($transaction->date_transaction);
            }) ?? $contracts->first();
        };

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
        $transactionsData = $transactions->values()->map(function ($transaction) use ($maintenanceById, $saleById) {
            $maintenance = $maintenanceById->get((string) $transaction->reference);
            $sale = $saleById->get((string) $transaction->reference);

            return [
                'id' => $transaction->transaction_agence_id,
                'date' => $transaction->date_transaction ? $transaction->date_transaction->format('d/m/Y') : '',
                'time' => $transaction->date_transaction ? $transaction->date_transaction->format('H:i') : '',
                'type' => in_array($transaction->type_transaction, ['loyer', 'vente']) ? 'in' : 'out',
                'label' => $this->getTransactionLabel($transaction, $maintenance, $sale),
                'reference' => $transaction->transaction_agence_id,
                'amount' => (float) $transaction->montant_global_verser,
            ];
        })->values()->all();

        // Préparer les loyers
        $loyersData = $transactions->where('type_transaction', 'loyer')->values()->map(function ($transaction) use ($contractForTransaction) {
            $contract = $contractForTransaction($transaction);

            return [
                'id' => $transaction->transaction_agence_id,
                'receipt_number' => $transaction->numero_recu ?: 'À générer',
                'receipt_url' => route('agence.caisse.loyer.receipt', $transaction->transaction_agence_id),
                'date' => $transaction->date_transaction ? $transaction->date_transaction->format('d/m/Y') : '',
                'time' => $transaction->date_transaction ? $transaction->date_transaction->format('H:i') : '',
                'tenant' => $transaction->locataire->name ?? 'N/A',
                'owner' => $contract?->proprietaire?->name ?? 'N/A',
                'lot' => $contract?->lot?->name ?? 'N/A',
                'door' => $contract?->porte?->numero_porte ?? $transaction->porte?->numero_porte ?? 'N/A',
                'period' => $this->formatPaidPeriods($transaction->mois_payer),
                'amount' => (float) $transaction->montant_global_verser,
                'mode' => $transaction->modePaiement->name ?? 'N/A',
                'breakdown' => $transaction->is_first && $contract
                    ? $this->initialPaymentBreakdown($contract)
                    : [],
            ];
        })->values()->all();

        // Préparer les maintenances
        $maintenancesData = $transactions->where('type_transaction', 'maintenance')->values()->map(function ($transaction) use ($maintenanceById) {
            $maintenance = $maintenanceById->get((string) $transaction->reference);
            $propertyParts = collect([
                $maintenance?->propriete?->reference,
                $maintenance?->lot?->name ? 'Lot '.$maintenance->lot->name : null,
                $maintenance?->porte?->numero_porte ? 'Porte '.$maintenance->porte->numero_porte : null,
            ])->filter()->values();
            $providers = $maintenance?->details
                ?->pluck('maintenancier.name')
                ->filter()
                ->unique()
                ->values()
                ->implode(', ');
            $interventionTypes = $maintenance?->details
                ?->pluck('typeIntervention.name')
                ->filter()
                ->unique()
                ->values()
                ->implode(', ');

            return [
                'id' => $transaction->transaction_agence_id,
                'date' => $transaction->date_transaction ? $transaction->date_transaction->format('d/m/Y') : '',
                'time' => $transaction->date_transaction ? $transaction->date_transaction->format('H:i') : '',
                'property' => $propertyParts->isNotEmpty() ? $propertyParts->implode(' · ') : 'N/A',
                'type' => $interventionTypes ?: ($maintenance?->titre ?? 'Maintenance'),
                'provider' => $providers ?: 'Non renseigné',
                'cost' => (float) $transaction->montant_global_verser,
                'status' => 'Terminée', // À adapter selon votre structure
            ];
        })->values()->all();

        // Préparer les dépenses
        $depensesData = $transactions->where('type_transaction', 'depense')->values()->map(function ($transaction) {
            return [
                'id' => $transaction->transaction_agence_id,
                'date' => $transaction->date_transaction ? $transaction->date_transaction->format('d/m/Y') : '',
                'time' => $transaction->date_transaction ? $transaction->date_transaction->format('H:i') : '',
                'category' => 'Divers',
                'label' => $this->getTransactionLabel($transaction),
                'proof' => 'Reçu',
                'amount' => (float) $transaction->montant_global_verser,
            ];
        })->values()->all();

        // Préparer les ventes
        $ventesData = $transactions->where('type_transaction', 'vente')->values()->map(function ($transaction) use ($saleById) {
            $sale = $saleById->get((string) $transaction->reference);
            $property = collect([
                $sale?->lot?->name ? 'Lot '.$sale->lot->name : null,
                ! $sale?->lot && $sale?->propriete?->lot?->name ? 'Lot '.$sale->propriete->lot->name : null,
                $sale?->propriete?->reference,
                $sale?->porte?->numero_porte ? 'Porte '.$sale->porte->numero_porte : null,
            ])->filter()->unique()->implode(' · ');

            return [
                'id' => $transaction->transaction_agence_id,
                'date' => $transaction->date_transaction ? $transaction->date_transaction->format('d/m/Y') : '',
                'time' => $transaction->date_transaction ? $transaction->date_transaction->format('H:i') : '',
                'client' => $sale?->acheteur?->name ?? 'N/A',
                'client_phone' => $sale?->acheteur?->telephone1 ?? 'N/A',
                'owner' => $sale?->proprietaire?->name ?? 'N/A',
                'property' => $property ?: 'N/A',
                'amount' => (float) $transaction->montant_global_verser,
                'status' => 'Payée',
                'receipt_number' => $transaction->numero_recu ?: 'À générer',
                'receipt_url' => route('agence.caisse.vente.facture', $transaction->transaction_agence_id),
            ];
        })->values()->all();

        // Préparer le résumé par mode de paiement
        $modesPaiement = ModePaiement::all();
           
        $summaryData = [];
        foreach ($modesPaiement as $mode) {
            $modeTransactions = $transactions->where('mode_paiement_id', $mode->getKey());
            $entriesTransactions = $modeTransactions->whereIn('type_transaction', ['loyer', 'vente']);
            $outputsTransactions = $modeTransactions->whereIn('type_transaction', ['maintenance', 'depense']);
            $entries = (float) $entriesTransactions->sum('montant_global_verser');
            $outputs = (float) $outputsTransactions->sum('montant_global_verser');
            $nbTransactions = $modeTransactions->count();
            
            if ($nbTransactions > 0) {
                $summaryData[] = [
                    'mode' => $mode->name,
                    'entries' => $entries,
                    'outputs' => $outputs,
                    'balance' => $entries - $outputs,
                    'count' => $nbTransactions,
                    'entries_count' => $entriesTransactions->count(),
                    'outputs_count' => $outputsTransactions->count(),
                    'icon' => $this->getModeIcon($mode->name),
                    'accent' => $this->getModeAccent($mode->name),
                ];
            }
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

    public function historique(Request $request)
    {
        $agenceId = $this->agenceId();
        $this->caisseClotureService->cloturerCaissesExpirees($agenceId);
        $transactions = $this->transactionRepository->getByAgence($agenceId);
        $filters = $request->validate([
            'date_debut' => ['nullable', 'date'],
            'date_fin' => ['nullable', 'date', 'after_or_equal:date_debut'],
        ]);
        if (empty($filters['date_debut']) && empty($filters['date_fin'])) {
            $filters['date_debut'] = now()->startOfMonth()->toDateString();
            $filters['date_fin'] = now()->endOfMonth()->toDateString();
        }
        $dateDebut = ! empty($filters['date_debut']) ? Carbon::parse($filters['date_debut'])->startOfDay() : null;
        $dateFin = ! empty($filters['date_fin']) ? Carbon::parse($filters['date_fin'])->endOfDay() : null;

        $sessions = CaisseSession::query()
            ->where('agence_id', $agenceId)
            ->with(['opener:id_users,name', 'closer:id_users,name'])
            ->latest('opened_at')
            ->get()
            ->filter(function (CaisseSession $session) use ($dateDebut, $dateFin) {
                $start = $this->effectiveSessionStart($session);
                return (! $dateDebut || $start->gte($dateDebut)) && (! $dateFin || $start->lte($dateFin));
            })
            ->sortByDesc(fn (CaisseSession $session) => $this->effectiveSessionStart($session)->timestamp)
            ->take(100)
            ->map(function (CaisseSession $session) use ($transactions) {
                $start = $this->effectiveSessionStart($session);
                $end = $session->closed_at ?: now();
                $sessionTransactions = $transactions->filter(function ($transaction) use ($start, $end) {
                    $date = $transaction->created_at ?: $transaction->date_transaction;

                    return $date && Carbon::parse($date)->between($start, $end, true);
                });
                $entries = (float) $sessionTransactions->whereIn('type_transaction', ['loyer', 'vente'])->sum('montant_global_verser');
                $outputs = (float) $sessionTransactions->whereIn('type_transaction', ['maintenance', 'depense'])->sum('montant_global_verser');
                $theoretical = $session->solde_theorique !== null
                    ? (float) $session->solde_theorique
                    : (float) $session->solde_ouverture + $entries - $outputs;

                return [
                    'id' => $session->getKey(),
                    'opened_at' => $start->format('d/m/Y H:i'),
                    'closed_at' => $session->closed_at?->format('d/m/Y H:i'),
                    'opened_by' => $session->opener?->name ?: 'Non renseigné',
                    'closed_by' => $session->closer?->name,
                    'opening_balance' => (float) $session->solde_ouverture,
                    'entries' => $entries,
                    'outputs' => $outputs,
                    'theoretical_balance' => $theoretical,
                    'closing_balance' => $session->solde_fermeture !== null ? (float) $session->solde_fermeture : null,
                    'difference' => $session->ecart !== null ? (float) $session->ecart : null,
                    'status' => $session->closed_at ? 'Clôturée' : 'Ouverte',
                ];
            });

        return Inertia::render('Agence/Caisse/Historique', [
            'sessions' => $sessions->values(),
            'filters' => [
                'date_debut' => $filters['date_debut'] ?? '',
                'date_fin' => $filters['date_fin'] ?? '',
            ],
        ]);
    }

    public function detailHistorique(string $session)
    {
        $agenceId = $this->agenceId();
        $cashSession = CaisseSession::query()
            ->where('agence_id', $agenceId)
            ->with(['opener:id_users,name', 'closer:id_users,name'])
            ->findOrFail($session);
        $start = $this->effectiveSessionStart($cashSession);
        $end = $cashSession->closed_at ?: now();

        $sessionTransactions = $this->transactionRepository->getByAgence($agenceId)
            ->filter(function ($transaction) use ($start, $end) {
                $date = $transaction->created_at ?: $transaction->date_transaction;
                return $date && Carbon::parse($date)->between($start, $end, true);
            });

        $maintenanceById = Maintenance::withoutGlobalScopes()
            ->where('agence_id', $agenceId)
            ->whereIn('maintenance_id', $sessionTransactions->where('type_transaction', 'maintenance')->pluck('reference')->filter()->all())
            ->with(['proprietaire', 'lot', 'porte', 'details.maintenancier', 'details.typeIntervention'])
            ->get()
            ->keyBy(fn ($maintenance) => (string) $maintenance->getKey());
        $saleById = VenteBien::withoutGlobalScopes()
            ->where('agence_id', $agenceId)
            ->whereIn('id_vente', $sessionTransactions->where('type_transaction', 'vente')->pluck('reference')->filter()->all())
            ->with(['acheteur', 'proprietaire', 'lot', 'propriete.lot', 'porte'])
            ->get()
            ->keyBy(fn ($sale) => (string) $sale->getKey());

        $transactions = $sessionTransactions
            ->map(fn ($transaction) => [
                'id' => $transaction->getKey(),
                'date' => ($transaction->created_at ?: $transaction->date_transaction)?->format('d/m/Y H:i'),
                'type' => $transaction->type_transaction,
                'direction' => in_array($transaction->type_transaction, ['loyer', 'vente'], true) ? 'in' : 'out',
                'label' => $this->getHistoryTransactionLabel(
                    $transaction,
                    $maintenanceById->get((string) $transaction->reference),
                    $saleById->get((string) $transaction->reference)
                ),
                'reference' => $transaction->numero_recu ?: $transaction->getKey(),
                'mode' => $transaction->modePaiement?->name ?: 'Non renseigné',
                'amount' => (float) $transaction->montant_global_verser,
            ])->values();
        $entries = (float) $transactions->where('direction', 'in')->sum('amount');
        $outputs = (float) $transactions->where('direction', 'out')->sum('amount');

        return Inertia::render('Agence/Caisse/HistoriqueDetail', [
            'session' => [
                'id' => $cashSession->getKey(),
                'opened_at' => $start->format('d/m/Y H:i'),
                'closed_at' => $cashSession->closed_at?->format('d/m/Y H:i'),
                'opened_by' => $cashSession->opener?->name ?: 'Non renseigné',
                'closed_by' => $cashSession->closer?->name,
                'opening_balance' => (float) $cashSession->solde_ouverture,
                'entries' => $entries,
                'outputs' => $outputs,
                'theoretical_balance' => $cashSession->solde_theorique !== null ? (float) $cashSession->solde_theorique : (float) $cashSession->solde_ouverture + $entries - $outputs,
                'closing_balance' => $cashSession->solde_fermeture !== null ? (float) $cashSession->solde_fermeture : null,
                'difference' => $cashSession->ecart !== null ? (float) $cashSession->ecart : null,
            ],
            'transactions' => $transactions,
        ]);
    }

    public function historiquePdf(Request $request)
    {
        $filters = $request->validate([
            'date_debut' => ['nullable', 'date'],
            'date_fin' => ['nullable', 'date', 'after_or_equal:date_debut'],
        ]);
        if (empty($filters['date_debut']) && empty($filters['date_fin'])) {
            $filters['date_debut'] = now()->startOfMonth()->toDateString();
            $filters['date_fin'] = now()->endOfMonth()->toDateString();
        }
        return response($this->cashHistoryPdfService->history($this->agenceId(), $filters), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="historique-caisse.pdf"',
        ]);
    }

    public function detailHistoriquePdf(string $session)
    {
        $cashSession = CaisseSession::where('agence_id', $this->agenceId())->findOrFail($session);
        return response($this->cashHistoryPdfService->session($cashSession), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="rapport-caisse-'.$cashSession->getKey().'.pdf"',
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

        $closedSession = DB::transaction(function () use ($agenceId, $data) {
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

            return $session;
        });

        return to_route('agence.caisse.index')
            ->with('success', 'La caisse a été clôturée avec succès.')
            ->with('cash_report_url', route('agence.caisse.historique.detail.pdf', $closedSession->getKey()));
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
    $maintenanceIdsPayees = $this->transactionRepository->getByAgence($agenceId)
        ->where('type_transaction', 'maintenance')
        ->pluck('reference')
        ->filter()
        ->map(fn ($id) => (string) $id)
        ->all();

    $maintenancesData = $maintenances
        ->filter(fn ($maintenance) => in_array(mb_strtolower(trim((string) $maintenance->statut)), ['en cours', 'en_cours'], true))
        ->reject(fn ($maintenance) => in_array((string) $maintenance->maintenance_id, $maintenanceIdsPayees, true))
        ->values()
        ->map(function ($maintenance) use ($maintenanceIdsPayees) {
        return [
            'maintenance_id' => (string) $maintenance->maintenance_id,
            'titre' => $maintenance->titre,
            'description' => $maintenance->description_generale,
            'proprietaire_id' => $maintenance->proprietaire_id ? (string) $maintenance->proprietaire_id : '',
            'lot_id' => $maintenance->lot_id ? (string) $maintenance->lot_id : '',
            'propriete_id' => $maintenance->propriete_id ? (string) $maintenance->propriete_id : '',
            'batiment_id' => $maintenance->batiment_id ? (string) $maintenance->batiment_id : '',
            'porte_id' => $maintenance->porte_id ? (string) $maintenance->porte_id : '',
            'proprietaire_name' => $maintenance->proprietaire?->name ?? '—',
            'lot_name' => $maintenance->lot?->name ?? '—',
            'porte_name' => $maintenance->porte?->numero_porte ?? '—',
            'prise_en_charge_par' => $maintenance->prise_en_charge_par ?? 'proprietaire',
            'statut' => $maintenance->statut ?? 'en_cours',
            'montant_global' => (float) ($maintenance->montant_global ?? 0),
            'est_reglee' => in_array((string) $maintenance->maintenance_id, $maintenanceIdsPayees, true),
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
        ->whereIn('statut', ['en cours', 'en_cours'])
        ->when($maintenanceIdsPayees, fn ($query) => $query->whereNotIn('maintenance_id', $maintenanceIdsPayees))
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
        ->whereIn('statut', ['en cours', 'en_cours'])
        ->when($maintenanceIdsPayees, fn ($query) => $query->whereNotIn('maintenance_id', $maintenanceIdsPayees))
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
        'modesPaiement' => ModePaiement::query()->get()->map(fn ($mode) => [
            'value' => (string) $mode->getKey(),
            'label' => $mode->name ?? $mode->libelle ?? 'Mode de paiement',
        ])->values()->all(),
    ]);
}

public function payerMaintenance(Request $request, string $maintenance)
{
    $agenceId = $this->agenceId();

    $validated = $request->validate([
        'mode_paiement_id' => 'required|exists:mode_paiements,id',
    ], [
        'mode_paiement_id.required' => 'Sélectionnez un mode de paiement.',
        'mode_paiement_id.exists' => 'Le mode de paiement sélectionné est invalide.',
    ]);

    if (! $this->caisseEstOuverte($agenceId)) {
        throw ValidationException::withMessages([
            'caisse' => "La caisse doit être ouverte avant de régler une maintenance.",
        ]);
    }

    $transaction = DB::transaction(function () use ($agenceId, $maintenance, $validated) {
        $intervention = Maintenance::query()
            ->where('agence_id', $agenceId)
            ->lockForUpdate()
            ->findOrFail($maintenance);

        $dejaReglee = $this->transactionRepository->getByAgence($agenceId)
            ->where('type_transaction', 'maintenance')
            ->where('reference', (string) $intervention->maintenance_id)
            ->first();

        if ($dejaReglee) {
            throw ValidationException::withMessages([
                'maintenance' => 'Cette maintenance a déjà été réglée.',
            ]);
        }

        $montant = (int) round((float) $intervention->montant_global);

        if ($montant <= 0) {
            throw ValidationException::withMessages([
                'montant' => 'Le montant de la maintenance doit être supérieur à zéro.',
            ]);
        }

        return $this->transactionRepository->create([
            'locataire_id' => $intervention->locataire_id ?: null,
            'agence_id' => $agenceId,
            'proprietaire_id' => (string) ($intervention->proprietaire_id ?? ''),
            'propriete_id' => (string) ($intervention->propriete_id ?? ''),
            'batiment_id' => $intervention->batiment_id ?: null,
            'porte_id' => $intervention->porte_id ?: null,
            'montant_global_verser' => $montant,
            'reference' => (string) $intervention->maintenance_id,
            'type_transaction' => TransactionAgence::STATUT_MAINTENANCE,
            'is_reversement' => false,
            'mode_paiement_id' => $validated['mode_paiement_id'],
            'date_transaction' => now(),
            'created_by' => $this->userId(),
            'updated_by' => $this->userId(),
        ]);
    });

    return response()->json([
        'success' => true,
        'message' => 'Dépense de maintenance réglée avec succès.',
        'data' => ['transaction_id' => $transaction->transaction_agence_id],
    ]);
}


    public function loyer()
    {
        $agenceId = $this->agenceId();
        $transactions = $this->transactionRepository->getByAgence($agenceId);
        
        $loyersData = $transactions->where('type_transaction', 'loyer')->values()->map(function ($transaction) {
            return [
                'id' => $transaction->transaction_agence_id,
                'date' => $transaction->date_transaction ? $transaction->date_transaction->format('d/m/Y') : '',
                'time' => $transaction->date_transaction ? $transaction->date_transaction->format('H:i') : '',
                'tenant' => $transaction->locataire->name ?? 'N/A',
                'property' => $this->getPropertyName($transaction),
                'period' => $this->formatPaidPeriods($transaction->mois_payer),
                'amount' => (float) $transaction->montant_global_verser,
                'mode' => $transaction->modePaiement->name ?? 'N/A',
            ];
        })->values()->all();

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
        
        $depensesData = $transactions->where('type_transaction', 'depense')->values()->map(function ($transaction) {
            return [
                'id' => $transaction->transaction_agence_id,
                'date' => $transaction->date_transaction ? $transaction->date_transaction->format('d/m/Y') : '',
                'time' => $transaction->date_transaction ? $transaction->date_transaction->format('H:i') : '',
                'category' => $transaction->modePaiement->name ?? 'Divers',
                'label' => $this->getTransactionLabel($transaction),
                'proof' => 'Reçu',
                'amount' => (float) $transaction->montant_global_verser,
            ];
        })->values()->all();

        $totalDepenses = $transactions->where('type_transaction', 'depense')->sum('montant_global_verser');

        return Inertia::render('Agence/Caisse/DepenseAgence', [
            'caisseOuverte' => $this->caisseEstOuverte($agenceId),
            'depenses' => $depensesData,
            'totalDepenses' => (float) $totalDepenses,
            'modesPaiement' => ModePaiement::query()->get()->map(fn ($mode) => [
                'value' => (string) $mode->getKey(),
                'label' => $mode->name ?? 'Mode de paiement',
            ])->values()->all(),
        ]);
    }

    public function enregistrerDepensesAgence(Request $request)
    {
        $agenceId = $this->agenceId();
        $validated = $request->validate([
            'depenses' => 'required|array|min:1|max:50',
            'depenses.*.categorie' => 'required|string|max:100',
            'depenses.*.libelle' => 'required|string|max:255',
            'depenses.*.montant' => 'required|integer|min:1',
            'depenses.*.mode_paiement_id' => 'required|exists:mode_paiements,id',
            'depenses.*.type_justificatif' => 'nullable|string|max:50',
            'depenses.*.observation' => 'nullable|string|max:2000',
        ]);

        if (! $this->caisseEstOuverte($agenceId)) {
            throw ValidationException::withMessages([
                'caisse' => "La caisse doit être ouverte avant d'enregistrer une dépense.",
            ]);
        }

        $transactions = DB::transaction(function () use ($validated, $agenceId) {
            return collect($validated['depenses'])->map(function (array $ligne) use ($agenceId) {
                $depense = CaisseDepense::create([
                    'agence_id' => $agenceId,
                    'categorie' => $ligne['categorie'],
                    'libelle' => $ligne['libelle'],
                    'montant' => $ligne['montant'],
                    'mode_paiement_id' => $ligne['mode_paiement_id'],
                    'type_justificatif' => $ligne['type_justificatif'] ?? null,
                    'observation' => $ligne['observation'] ?? null,
                    'date_depense' => now(),
                    'created_by' => $this->userId(),
                ]);

                $transaction = $this->transactionRepository->create([
                    'locataire_id' => null,
                    'agence_id' => $agenceId,
                    'proprietaire_id' => null,
                    'propriete_id' => null,
                    'batiment_id' => null,
                    'porte_id' => null,
                    'montant_global_verser' => $ligne['montant'],
                    'reference' => $depense->caisse_depense_id,
                    'type_transaction' => TransactionAgence::STATUT_DEPENSE,
                    'mode_paiement_id' => $ligne['mode_paiement_id'],
                    'mois_payer' => $ligne['libelle'],
                    'date_transaction' => now(),
                    'created_by' => $this->userId(),
                    'updated_by' => $this->userId(),
                ]);

                $depense->update(['transaction_agence_id' => $transaction->transaction_agence_id]);

                return $transaction;
            });
        });

        return response()->json([
            'success' => true,
            'message' => $transactions->count().' dépense(s) enregistrée(s) avec succès.',
        ], 201);
    }

    public function venteBien()
    {
        $agenceId = $this->agenceId();
        $transactions = $this->transactionRepository->getByAgence($agenceId);

        $ventesData = $transactions->where('type_transaction', 'vente')->values()->map(function ($transaction) {
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
        })->values()->all();

        $totalVentes = $transactions->where('type_transaction', 'vente')->sum('montant_global_verser');

        $ongoingSales = VenteBien::query()
            ->where('agence_id', $agenceId)
            ->whereIn('statut', [VenteBien::STATUT_EN_COURS, VenteBien::STATUT_PARTIEL])
            ->with(['acheteur', 'proprietaire', 'lot', 'propriete', 'porte', 'echeances.modePaiement'])
            ->latest('created_at')
            ->get()
            ->map(function ($sale) use ($transactions) {
                $payments = $transactions
                    ->where('type_transaction', 'vente')
                    ->where('reference', (string) $sale->getKey())
                    ->values();
                $paid = (float) $payments->sum('montant_global_verser');
                $property = collect([
                    $sale->propriete?->reference,
                    $sale->lot?->name ? 'Lot '.$sale->lot->name : null,
                    $sale->porte?->numero_porte ? 'Porte '.$sale->porte->numero_porte : null,
                ])->filter()->implode(' · ');

                return [
                    'id' => (string) $sale->getKey(),
                    'owner_id' => (string) $sale->proprietaire_id,
                    'target' => $sale->porte_id
                        ? 'door-'.$sale->porte_id
                        : ($sale->propriete_id ? 'property-'.$sale->propriete_id : 'lot-'.$sale->lot_id),
                    'reference' => $sale->reference,
                    'buyer' => $sale->acheteur?->name ?? 'Acheteur non renseigné',
                    'owner' => $sale->proprietaire?->name ?? 'Propriétaire non renseigné',
                    'property' => $property ?: 'Bien vendu',
                    'price' => (float) $sale->prix_vente,
                    'paid' => $paid,
                    'remaining' => max((float) $sale->prix_vente - $paid, 0),
                    'payments' => $payments->map(fn ($payment) => [
                        'id' => (string) $payment->getKey(),
                        'date' => $payment->date_transaction?->format('d/m/Y H:i'),
                        'amount' => (float) $payment->montant_global_verser,
                        'receipt_number' => $payment->numero_recu,
                        'invoice_url' => route('agence.caisse.vente.facture', $payment->getKey()),
                    ])->all(),
                    'schedule' => $sale->echeances->map(fn ($due) => [
                        'id' => (string) $due->getKey(),
                        'label' => $due->libelle,
                        'date' => $due->date_echeance?->format('d/m/Y'),
                        'amount' => (float) $due->montant_prevu,
                        'paid' => (float) $due->montant_paye,
                        'penalty' => (float) $due->montant_amende,
                        'status' => $due->statut,
                        'overdue' => $due->statut !== 'payee' && $due->date_echeance?->isPast(),
                        'mode' => $due->modePaiement?->name,
                    ])->all(),
                ];
            })->values()->all();

        $completedSaleTargets = VenteBien::query()
            ->where('agence_id', $agenceId)
            ->get()
            ->filter(function ($sale) use ($transactions) {
                $paid = (float) $transactions
                    ->where('type_transaction', 'vente')
                    ->where('reference', (string) $sale->getKey())
                    ->sum('montant_global_verser');

                return $sale->statut === VenteBien::STATUT_TERMINE || $paid >= (float) $sale->prix_vente;
            })
            ->map(fn ($sale) => $sale->porte_id
                ? 'door-'.$sale->porte_id
                : ($sale->propriete_id ? 'property-'.$sale->propriete_id : 'lot-'.$sale->lot_id))
            ->filter()
            ->values();

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

        $saleOwners = $ownerLinks->map(function ($link) use ($completedSaleTargets) {
            $owner = $link->proprietaire;
            if (!$owner) {
                return null;
            }

            $lotsForSale = $owner->lots
                ->reject(fn ($lot) => $completedSaleTargets->contains('lot-'.$lot->propreietaire_lot_id))
                ->map(fn ($lot) => $this->saleTarget(
                'lot-'.$lot->propreietaire_lot_id,
                $lot->name ?? 'Lot',
                $lot->adresse ?? '',
                'Lot entier',
                (float) $lot->sale_price,
                $lot->num_lot ?? ''
            ));

            $propertiesForSale = $owner->proprietes->flatMap(function ($property) use ($completedSaleTargets) {
                if (($property->sale_type ?? 'none') === 'whole') {
                    if ($completedSaleTargets->contains('property-'.$property->propriete_id)) {
                        return [];
                    }
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

                return $property->batiments->flatMap(function ($building) use ($property, $completedSaleTargets) {
                    return $building->portes
                        ->filter(fn ($door) => $door->is_actif
                            && ! $door->is_occupe
                            && $door->is_allocation === false
                            && $door->tarifActif?->mt_vente
                            && ! $completedSaleTargets->contains('door-'.$door->porte_id))
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
            'typePieces' => TypePiece::query()->orderBy('name')->get(['type_pieces_id', 'name']),
            'ongoingSales' => $ongoingSales,
        ]);
    }

    public function payerVente(Request $request, string $vente)
    {
        $agenceId = $this->agenceId();
        $data = $request->validate([
            'montant' => ['required', 'numeric', 'min:1'],
            'mode_paiement' => ['required', 'string'],
        ]);
        if (! $this->caisseEstOuverte($agenceId)) {
            throw ValidationException::withMessages(['caisse' => 'La caisse doit être ouverte avant d’enregistrer ce versement.']);
        }

        $modeId = ModePaiement::query()->whereRaw('LOWER(name) = ?', [mb_strtolower($data['mode_paiement'])])->value('id')
            ?: ModePaiement::query()->where('name', 'like', '%'.strtok($data['mode_paiement'], ' ').'%')->value('id');
        if (! $modeId) {
            throw ValidationException::withMessages(['mode_paiement' => 'Mode de paiement invalide.']);
        }

        $transaction = DB::transaction(function () use ($agenceId, $vente, $data, $modeId) {
            $sale = VenteBien::query()->where('agence_id', $agenceId)->lockForUpdate()->findOrFail($vente);
            $alreadyPaid = (float) TransactionAgence::query()
                ->where('agence_id', $agenceId)
                ->where('type_transaction', TransactionAgence::STATUT_VENTE)
                ->where('reference', (string) $sale->getKey())
                ->sum('montant_global_verser');
            $remaining = max((float) $sale->prix_vente - $alreadyPaid, 0);
            if ($remaining <= 0) {
                throw ValidationException::withMessages(['vente' => 'Cette vente est déjà entièrement réglée.']);
            }
            if ((float) $data['montant'] > $remaining) {
                throw ValidationException::withMessages(['montant' => 'Le versement ne peut pas dépasser le reste à payer de '.number_format($remaining, 0, ',', ' ').' FCFA.']);
            }

            $ids = ['lot_id' => $sale->lot_id, 'propriete_id' => $sale->propriete_id, 'porte_id' => $sale->porte_id, 'batiment_id' => $sale->batiment_id];
            $payment = TransactionAgence::create(array_merge($ids, [
                'agence_id' => $agenceId,
                'proprietaire_id' => $sale->proprietaire_id,
                'locataire_id' => null,
                'montant_global_verser' => $data['montant'],
                'reference' => (string) $sale->getKey(),
                'type_transaction' => TransactionAgence::STATUT_VENTE,
                'mode_paiement_id' => $modeId,
                'date_transaction' => now(),
                'is_first' => false,
                'is_reversement' => false,
            ]));
            $sale->update([
                'statut' => $alreadyPaid + (float) $data['montant'] >= (float) $sale->prix_vente
                    ? VenteBien::STATUT_TERMINE
                    : VenteBien::STATUT_PARTIEL,
            ]);

            $amountToAllocate = (float) $data['montant'];
            $sale->echeances()
                ->whereIn('statut', ['en_attente', 'partielle', 'en_retard'])
                ->orderBy('date_echeance')
                ->lockForUpdate()
                ->get()
                ->each(function ($due) use (&$amountToAllocate, $payment) {
                    if ($amountToAllocate <= 0) {
                        return false;
                    }
                    $dueRemaining = max((float) $due->montant_prevu + (float) $due->montant_amende - (float) $due->montant_paye, 0);
                    $allocated = min($amountToAllocate, $dueRemaining);
                    $newPaid = (float) $due->montant_paye + $allocated;
                    $due->update([
                        'montant_paye' => $newPaid,
                        'statut' => $newPaid >= (float) $due->montant_prevu + (float) $due->montant_amende ? 'payee' : 'partielle',
                        'paye_at' => $newPaid >= (float) $due->montant_prevu + (float) $due->montant_amende ? now() : null,
                        'transaction_agence_id' => $payment->getKey(),
                    ]);
                    $amountToAllocate -= $allocated;
                });

            return $payment;
        });

        return response()->json([
            'success' => true,
            'message' => 'Versement enregistré avec succès.',
            'invoice_number' => $transaction->numero_recu,
            'invoice_url' => route('agence.caisse.vente.facture', $transaction->getKey()),
        ], 201);
    }

    public function enregistrerVente(Request $request)
    {
        $agenceId = $this->agenceId();
        $data = $request->validate([
            'target' => ['required', 'string', 'regex:/^(lot|property|door)-.+$/'],
            'proprietaire_id' => ['required', 'string'],
            'date_accord' => ['required', 'date'],
            'prix_vente' => ['required', 'numeric', 'min:1'],
            'montant_paye' => ['required', 'numeric', 'min:1'],
            'type_paiement' => ['required', 'in:complete,tranches,monthly,custom'],
            'mode_paiement' => ['required', 'string'],
            'acheteur.name' => ['required', 'string', 'max:255'],
            'acheteur.phone' => ['required', 'string', 'max:50'],
            'acheteur.email' => ['nullable', 'email', 'max:255'],
            'acheteur.address' => ['required', 'string', 'max:255'],
            'acheteur.id_type' => ['required', 'integer', 'exists:type_pieces,type_pieces_id'],
            'acheteur.id_number' => ['required', 'string', 'max:100'],
            'nombre_mensualites' => ['nullable', 'integer', 'min:1'],
            'date_premiere_mensualite' => ['nullable', 'date'],
            'echeances' => ['required_if:type_paiement,tranches,custom', 'array', 'min:1'],
            'echeances.*.label' => ['required_if:type_paiement,tranches,custom', 'string', 'max:150'],
            'echeances.*.amount' => ['required_if:type_paiement,tranches,custom', 'numeric', 'min:1'],
            'echeances.*.date' => ['required_if:type_paiement,tranches,custom', 'date', 'after_or_equal:date_accord'],
            'echeances.*.mode' => ['required_if:type_paiement,tranches,custom', 'string'],
        ]);
        if (! $this->caisseEstOuverte($agenceId)) {
            throw ValidationException::withMessages(['caisse' => 'La caisse doit être ouverte avant une vente.']);
        }

        $prixVente = (float) $data['prix_vente'];
        $premierVersement = (float) $data['montant_paye'];
        if ($premierVersement > $prixVente) {
            throw ValidationException::withMessages(['montant_paye' => 'Le montant encaissé ne peut pas dépasser le prix de vente.']);
        }
        if ($data['type_paiement'] === 'complete' && abs($premierVersement - $prixVente) > 0.01) {
            throw ValidationException::withMessages(['montant_paye' => 'Un paiement complet doit couvrir la totalité du prix de vente.']);
        }
        if ($data['type_paiement'] !== 'complete' && $premierVersement >= $prixVente) {
            throw ValidationException::withMessages(['montant_paye' => 'Pour un paiement échelonné, le premier versement doit être inférieur au prix de vente afin de laisser un solde à répartir.']);
        }
        $resteAEchelonner = $prixVente - $premierVersement;
        if (in_array($data['type_paiement'], ['tranches', 'custom'], true)) {
            $totalEcheances = collect($data['echeances'])->sum(fn ($line) => (float) $line['amount']);
            if (abs($totalEcheances - $resteAEchelonner) > 0.01) {
                throw ValidationException::withMessages(['echeances' => 'Le total des échéances doit correspondre exactement au reste à payer de '.number_format($resteAEchelonner, 0, ',', ' ').' FCFA.']);
            }
        }

        [$targetType, $targetId] = explode('-', $data['target'], 2);
        $modeId = ModePaiement::query()->whereRaw('LOWER(name) = ?', [mb_strtolower($data['mode_paiement'])])->value('id')
            ?: ModePaiement::query()->where('name', 'like', '%'.strtok($data['mode_paiement'], ' ').'%')->value('id');
        if (! $modeId) throw ValidationException::withMessages(['mode_paiement' => 'Mode de paiement invalide.']);

        $result = DB::transaction(function () use ($data, $agenceId, $targetType, $targetId, $modeId) {
            $ids = ['lot_id' => null, 'propriete_id' => null, 'porte_id' => null, 'batiment_id' => null];
            if ($targetType === 'lot') {
                $lot = ProprietaireLot::where('agence_id', $agenceId)->where('proprietaire_id', $data['proprietaire_id'])->findOrFail($targetId);
                $ids['lot_id'] = $lot->getKey();
            } elseif ($targetType === 'property') {
                $property = Propriete::where('agence_id', $agenceId)->where('proprietaire_id', $data['proprietaire_id'])->findOrFail($targetId);
                $ids['propriete_id'] = $property->getKey(); $ids['lot_id'] = $property->lot_id;
            } else {
                $door = Porte::with('batiment')->where('agence_id', $agenceId)->findOrFail($targetId);
                $ids['porte_id'] = $door->getKey(); $ids['batiment_id'] = $door->batiment_id;
                $ids['propriete_id'] = $door->batiment?->propriete_id;
            }
            $existingSale = VenteBien::query()
                ->where('agence_id', $agenceId)
                ->whereIn('statut', [VenteBien::STATUT_EN_COURS, VenteBien::STATUT_PARTIEL])
                ->where(function ($query) use ($targetType, $targetId) {
                    $column = $targetType === 'lot' ? 'lot_id' : ($targetType === 'property' ? 'propriete_id' : 'porte_id');
                    $query->where($column, $targetId);
                })
                ->lockForUpdate()
                ->exists();
            if ($existingSale) {
                throw ValidationException::withMessages(['target' => 'Ce bien possède déjà une vente en cours de règlement. Enregistrez le versement dans le dossier existant.']);
            }
            $buyer = Acheteur::create(['agence_id' => $agenceId, 'name' => $data['acheteur']['name'], 'telephone1' => $data['acheteur']['phone'], 'telephone2' => null, 'type_piece_id' => $data['acheteur']['id_type'],
                'email' => $data['acheteur']['email'] ?? null, 'adresse' => $data['acheteur']['address'] ?? null,
                'numero_piece' => $data['acheteur']['id_number'] ?? null, 'created_by' => $this->userId()]);
            $sale = VenteBien::create(array_merge($ids, ['reference' => 'VTE-'.now()->format('YmdHis').'-'.strtoupper(substr((string) \Illuminate\Support\Str::uuid(), 0, 6)), 'agence_id' => $agenceId, 'proprietaire_id' => $data['proprietaire_id'],
                'acheteur_vente_id' => $buyer->getKey(), 'date_accord' => $data['date_accord'], 'prix_vente' => $data['prix_vente'],
                'commission' => 0, 'montant_proprietaire' => $data['prix_vente'], 'type_paiement' => $data['type_paiement'] === 'complete' ? 'complet' : ($data['type_paiement'] === 'monthly' ? 'mensuel' : ($data['type_paiement'] === 'custom' ? 'personnalise' : 'tranches')),
                'acompte_mensuel' => $data['montant_paye'], 'nombre_mensualites' => $data['nombre_mensualites'] ?? null,
                'date_premiere_mensualite' => $data['date_premiere_mensualite'] ?? null,
                'statut' => (float) $data['montant_paye'] >= (float) $data['prix_vente'] ? VenteBien::STATUT_TERMINE : VenteBien::STATUT_PARTIEL,
                'created_by' => $this->userId()]));
            $schedule = collect();
            if ($data['type_paiement'] === 'monthly') {
                $count = (int) ($data['nombre_mensualites'] ?? 0);
                $firstDate = ! empty($data['date_premiere_mensualite']) ? Carbon::parse($data['date_premiere_mensualite']) : null;
                if ($count < 1 || ! $firstDate) {
                    throw ValidationException::withMessages(['nombre_mensualites' => 'Le nombre et la date de début des mensualités sont obligatoires.']);
                }
                $remainingCents = (int) round(((float) $data['prix_vente'] - (float) $data['montant_paye']) * 100);
                $baseCents = intdiv($remainingCents, $count);
                $schedule = collect(range(1, $count))->map(fn ($number) => [
                    'label' => 'Mensualité '.$number,
                    'amount' => ($baseCents + ($number <= $remainingCents % $count ? 1 : 0)) / 100,
                    'date' => $firstDate->copy()->addMonthsNoOverflow($number - 1)->toDateString(),
                    'mode' => $data['mode_paiement'],
                ]);
            } elseif (in_array($data['type_paiement'], ['tranches', 'custom'], true)) {
                $schedule = collect($data['echeances']);
            }
            $schedule->values()->each(function ($line, $index) use ($sale, $agenceId) {
                $scheduledModeId = ModePaiement::query()->whereRaw('LOWER(name) = ?', [mb_strtolower($line['mode'])])->value('id')
                    ?: ModePaiement::query()->where('name', 'like', '%'.strtok($line['mode'], ' ').'%')->value('id');
                VenteEcheance::create([
                    'vente_id' => $sale->getKey(),
                    'agence_id' => $agenceId,
                    'libelle' => $line['label'],
                    'numero_echeance' => $index + 1,
                    'date_echeance' => $line['date'],
                    'montant_prevu' => $line['amount'],
                    'mode_paiement_id' => $scheduledModeId,
                    'statut' => 'en_attente',
                ]);
            });
            $transaction = TransactionAgence::create(array_merge($ids, ['agence_id' => $agenceId, 'proprietaire_id' => $data['proprietaire_id'],
                'locataire_id' => null, 'montant_global_verser' => $data['montant_paye'], 'reference' => $sale->getKey(),
                'type_transaction' => TransactionAgence::STATUT_VENTE, 'mode_paiement_id' => $modeId, 'date_transaction' => now(),
                'is_first' => true, 'is_reversement' => false]));
            return [$sale, $transaction];
        });
        [$sale, $transaction] = $result;
        return response()->json(['success' => true, 'message' => 'Vente enregistrée et facture générée avec succès.',
            'invoice_number' => $transaction->numero_recu, 'invoice_url' => route('agence.caisse.vente.facture', $transaction->getKey())], 201);
    }

    public function factureVente(string $transaction)
    {
        $payment = TransactionAgence::where('agence_id', $this->agenceId())->where('type_transaction', TransactionAgence::STATUT_VENTE)->findOrFail($transaction);
        return response($this->saleInvoiceService->generate($payment), 200, ['Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="facture-vente-'.$payment->numero_recu.'.pdf"']);
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

    private function getTransactionLabel($transaction, $maintenance = null, $sale = null)
    {
        switch ($transaction->type_transaction) {
            case 'loyer':
                $locataire = $transaction->locataire->name ?? 'N/A';
                $porte = $transaction->porte->numero_porte ?? 'N/A';
                $mois = $this->formatPaidPeriods($transaction->mois_payer);
                return "Paiement loyer — {$locataire} ({$porte}, {$mois})";
            case 'maintenance':
                $proprietaire = $maintenance?->proprietaire?->name ?? 'N/A';
                $lot = $maintenance?->lot?->name ?? 'N/A';
                $porte = $maintenance?->porte?->numero_porte ?? $transaction->porte?->numero_porte ?? 'N/A';
                return "Maintenance — {$proprietaire} · Lot {$lot} · Porte {$porte}";
            case 'depense':
                $libelle = $transaction->mois_payer ?: 'Dépense diverse';
                return "Dépense agence — {$libelle}";
            case 'vente':
                $proprietaire = $sale?->proprietaire?->name ?? 'N/A';
                $lot = $sale?->lot?->name ?? $sale?->propriete?->lot?->name ?? 'N/A';
                return "Vente de bien — {$proprietaire} · Lot {$lot}";
            default:
                return "Transaction";
        }
    }

    private function getHistoryTransactionLabel($transaction, $maintenance = null, $sale = null): string
    {
        if ($transaction->type_transaction === TransactionAgence::STATUT_VENTE) {
            $buyer = $sale?->acheteur?->name ?? 'Acheteur non renseigné';
            $owner = $sale?->proprietaire?->name ?? 'Propriétaire non renseigné';
            $asset = collect([
                $sale?->lot?->name ? 'Lot '.$sale->lot->name : null,
                ! $sale?->lot && $sale?->propriete?->lot?->name ? 'Lot '.$sale->propriete->lot->name : null,
                $sale?->propriete?->reference ? 'Propriété '.$sale->propriete->reference : null,
                $sale?->porte?->numero_porte ? 'Porte '.$sale->porte->numero_porte : null,
            ])->filter()->unique()->implode(' · ');

            return "Versement vente — {$buyer} · {$owner} · ".($asset ?: 'Bien non renseigné');
        }

        if ($transaction->type_transaction === TransactionAgence::STATUT_MAINTENANCE) {
            $owner = $maintenance?->proprietaire?->name ?? 'Propriétaire non renseigné';
            $lot = $maintenance?->lot?->name ?? 'Non renseigné';
            $door = $maintenance?->porte?->numero_porte ?? 'Non renseignée';
            $providers = $maintenance?->details
                ?->pluck('maintenancier.name')
                ->filter()
                ->unique()
                ->implode(', ') ?: 'Prestataire non renseigné';
            $title = $maintenance?->titre ?: 'Maintenance';

            return "{$title} — {$owner} · Lot {$lot} · Porte {$door} · {$providers}";
        }

        return $this->getTransactionLabel($transaction, $maintenance, $sale);
    }

    private function initialPaymentBreakdown($contract): array
    {
        $loyer = (float) $contract->loyer_net;

        return collect([
            ['label' => 'Loyers d’avance', 'amount' => $loyer * (int) $contract->avance],
            ['label' => 'Caution locative', 'amount' => $loyer * (int) $contract->caution],
            ['label' => 'Frais d’agence', 'amount' => $loyer * (int) $contract->agence],
            ['label' => 'Caution CIE', 'amount' => (float) $contract->caution_cie],
            ['label' => 'Caution SODECI', 'amount' => (float) $contract->caution_sodeci],
            ['label' => 'Frais de dossier', 'amount' => (float) $contract->frais_de_dossier],
            ['label' => 'Pas-de-porte / autres frais', 'amount' => (float) $contract->pas_de_porte],
        ])->filter(fn (array $item) => $item['amount'] > 0)->values()->all();
    }

    private function formatPaidPeriods(mixed $periods): string
    {
        if ($periods === null || $periods === '') {
            return 'N/A';
        }

        $decoded = $periods;

        // Certaines anciennes transactions contiennent un tableau JSON, parfois encodé deux fois.
        for ($attempt = 0; $attempt < 2 && is_string($decoded); $attempt++) {
            $candidate = json_decode($decoded, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                break;
            }

            $decoded = $candidate;
        }

        $values = is_array($decoded) ? $decoded : [$decoded];
        $formatted = collect($values)
            ->flatten()
            ->filter(fn ($value) => is_scalar($value) && trim((string) $value) !== '')
            ->map(function ($value) {
                $period = trim((string) $value);

                return preg_replace('/-(\d{4})$/u', ' $1', $period) ?? $period;
            })
            ->values()
            ->all();

        return $formatted !== [] ? implode(', ', $formatted) : 'N/A';
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

        return Maintenance::query()
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

    private function effectiveSessionStart(CaisseSession $session): Carbon
    {
        if ($session->closed_at && $session->opened_at->gt($session->closed_at) && $session->created_at) {
            return Carbon::parse($session->created_at);
        }

        return $session->opened_at->copy();
    }

    private function caisseEstOuverte(string $agenceId): bool
    {
        $this->caisseClotureService->cloturerCaissesExpirees($agenceId);

        return CaisseSession::where('agence_id', $agenceId)->whereNull('closed_at')->exists();
    }
}
