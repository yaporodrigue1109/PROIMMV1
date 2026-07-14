<?php

namespace App\Http\Controllers\Agence\Statistique;

use App\Http\Controllers\Controller;
use App\Models\Batiment;
use App\Models\Maintenance;
use App\Models\MaintenanceDetail;
use App\Models\Porte;
use App\Models\Propriete;
use App\Models\ProprietaireAgence;
use App\Models\Transaction;
use App\Repositories\Agence\Interfaces\LocataireRepositoryInterface;
use App\Repositories\Agence\Interfaces\MaintenanceRepositoryInterface;
use App\Repositories\Agence\Interfaces\ProprieteRepositoryInterface;
use App\Repositories\Interfaces\TransactionRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class StatistiqueController extends Controller
{
    public function __construct(
        protected ProprieteRepositoryInterface $proprieteRepo,
        protected LocataireRepositoryInterface $locataireRepo,
        protected MaintenanceRepositoryInterface $maintenanceRepo,
        protected TransactionRepositoryInterface $transactionRepo,
    ) {
    }

    public function index(): Response
    {
        $agenceId = $this->agenceId();
        $year     = now()->year;
        $month    = now()->month;

        $proprietesQuery   = Propriete::query()->where('agence_id', $agenceId);
        $batimentsQuery    = Batiment::query()->where('agence_id', $agenceId);
        $portesQuery       = Porte::query()->where('agence_id', $agenceId);
        $maintenancesQuery = Maintenance::query()->where('agence_id', $agenceId);
        $transactionsQuery = Transaction::query()->where('agence_id', $agenceId);

        $proprietesStats = $this->safeArray(
            fn () => $this->proprieteRepo->stats(),
            ['total' => 0, 'allocation' => 0, 'non_allocation' => 0, 'ce_mois' => 0]
        );
        $locatairesStats = $this->safeArray(
            fn () => $this->locataireRepo->stats(),
            ['total' => 0, 'actifs' => 0, 'resilies' => 0, 'ce_mois' => 0]
        );
        $maintenanceStats = $this->safeArray(
            fn () => $this->maintenanceRepo->countByStatut(),
            ['en_attente' => 0, 'en_cours' => 0, 'termine' => 0, 'annule' => 0, 'validee' => 0, 'echouee' => 0]
        );
        $totalEncaisse = $this->safeFloat(
            fn () => $this->transactionRepo->getTotalEncaisseParAgence($agenceId)
        );

        $proprietairesTotal = $this->safeCount(
            fn () => ProprietaireAgence::where('agence_id', $agenceId)->count()
        );
        $proprietairesActifs = $this->safeCount(
            fn () => ProprietaireAgence::where('agence_id', $agenceId)->where('is_active', true)->count()
        );

        $batimentsTotal = $this->safeCount(fn () => $batimentsQuery->count());
        $portesTotal = $this->safeCount(fn () => $portesQuery->count());
        $portesOccupees = $this->safeCount(fn () => (clone $portesQuery)->where('is_occupe', true)->count());
        $portesLibres = $this->safeCount(fn () => (clone $portesQuery)->where('is_actif', true)->where('is_occupe', false)->count());

        $maintenancesTotal = $this->safeCount(fn () => $maintenancesQuery->count());
        $maintenancesEnCours = $this->safeCount(fn () => (clone $maintenancesQuery)->where('statut', Maintenance::STATUT_EN_COURS)->count());
        $maintenancesTerminees = $this->safeCount(fn () => (clone $maintenancesQuery)->where('statut', Maintenance::STATUT_TERMINE)->count());
        $maintenancesEnAttente = $this->safeCount(fn () => (clone $maintenancesQuery)->where('statut', Maintenance::STATUT_EN_ATTENTE)->count());
        $maintenancesAnnulees = $this->safeCount(fn () => (clone $maintenancesQuery)->where('statut', Maintenance::STATUT_ANNULE)->count());
        $coutMaintenanceMois = $this->safeFloat(fn () => (clone $maintenancesQuery)
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->sum('montant_global'));

        $transactionsValidees = $this->safeCount(fn () => (clone $transactionsQuery)->where('statut', 'validee')->count());
        $transactionsEnAttente = $this->safeCount(fn () => (clone $transactionsQuery)->where('statut', 'en_attente')->count());
        $transactionsEchouees = $this->safeCount(fn () => (clone $transactionsQuery)->where('statut', 'echouee')->count());
        $revenuMois = $this->safeFloat(fn () => (clone $transactionsQuery)
            ->where('statut', 'validee')
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->sum('montant_ttc'));

        $revenueByMonth = $this->safeArray(
            fn () => (clone $transactionsQuery)
                ->where('statut', 'validee')
                ->whereYear('created_at', $year)
                ->selectRaw('MONTH(created_at) as month, SUM(montant_ttc) as total')
                ->groupBy('month')
                ->pluck('total', 'month')
                ->toArray()
        );

        $maintenanceByMonth = $this->safeArray(
            fn () => (clone $maintenancesQuery)
                ->whereYear('created_at', $year)
                ->selectRaw('MONTH(created_at) as month, SUM(montant_global) as total')
                ->groupBy('month')
                ->pluck('total', 'month')
                ->toArray()
        );

        $statusLabels = [
            'en_attente' => 'En attente',
            'en_cours' => 'En cours',
            'termine' => 'Terminée',
            'annule' => 'Annulée',
            'validee' => 'Validée',
            'echouee' => 'Échouée',
        ];

        $maintenanceSeries = collect($statusLabels)
            ->map(fn ($label, $key) => [
                'label' => $label,
                'value' => (int) ($maintenanceStats[$key] ?? 0),
            ])
            ->values()
            ->all();

        $monthlyLabels = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'];
        $revenueSeries = [];
        $maintenanceMonthSeries = [];

        foreach (range(1, 12) as $monthIndex) {
            $revenueSeries[] = (float) ($revenueByMonth[$monthIndex] ?? 0);
            $maintenanceMonthSeries[] = (float) ($maintenanceByMonth[$monthIndex] ?? 0);
        }

        $recentTransactions = $this->safeCollection(fn () => (clone $transactionsQuery)
            ->with('abonnement')
            ->latest('created_at')
            ->limit(6)
            ->get());

        $recentMaintenances = $this->safeCollection(fn () => (clone $maintenancesQuery)
            ->with(['proprietaire', 'propriete'])
            ->latest('created_at')
            ->limit(6)
            ->get());

        $topMaintenanceTypes = $this->safeCollection(fn () => MaintenanceDetail::query()
            ->join('maintenance', 'maintenance.maintenance_id', '=', 'maintenance_detail.maintenance_id')
            ->join('type_maintenances', 'type_maintenances.type_maintenance_id', '=', 'maintenance_detail.type_intervention_id')
            ->where('maintenance.agence_id', $agenceId)
            ->select(
                'type_maintenances.type_maintenance_id',
                'type_maintenances.name',
                'type_maintenances.categorie',
                DB::raw('COUNT(*) as total_interventions'),
                DB::raw('COALESCE(SUM(maintenance_detail.montant), 0) as montant_total')
            )
            ->groupBy('type_maintenances.type_maintenance_id', 'type_maintenances.name', 'type_maintenances.categorie')
            ->orderByDesc('total_interventions')
            ->limit(5)
            ->get());

        $topProperties = $this->safeCollection(fn () => Maintenance::query()
            ->with('propriete')
            ->where('agence_id', $agenceId)
            ->select(
                'propriete_id',
                DB::raw('COUNT(*) as total_maintenances'),
                DB::raw('COALESCE(SUM(montant_global), 0) as montant_total')
            )
            ->groupBy('propriete_id')
            ->orderByDesc('total_maintenances')
            ->limit(5)
            ->get());

        $occupationRate = $portesTotal > 0 ? round(($portesOccupees / $portesTotal) * 100) : 0;
        $allocationRate = $proprietesStats['total'] > 0
            ? round((($proprietesStats['allocation'] ?? 0) / $proprietesStats['total']) * 100)
            : 0;
        $maintenanceCloseRate = $maintenancesTotal > 0
            ? round(($maintenancesTerminees / $maintenancesTotal) * 100)
            : 0;
        $transactionSuccessRate = ($transactionsValidees + $transactionsEnAttente + $transactionsEchouees) > 0
            ? round(($transactionsValidees / max(1, $transactionsValidees + $transactionsEnAttente + $transactionsEchouees)) * 100)
            : 0;

        $stats = [
            'proprietes_total' => $proprietesStats['total'] ?? 0,
            'proprietes_allocation' => $proprietesStats['allocation'] ?? 0,
            'proprietes_non_allocation' => $proprietesStats['non_allocation'] ?? 0,
            'proprietes_ce_mois' => $proprietesStats['ce_mois'] ?? 0,
            'proprietaires_total' => $proprietairesTotal,
            'proprietaires_actifs' => $proprietairesActifs,
            'locataires_total' => $locatairesStats['total'] ?? 0,
            'locataires_actifs' => $locatairesStats['actifs'] ?? 0,
            'locataires_resilies' => $locatairesStats['resilies'] ?? 0,
            'locataires_ce_mois' => $locatairesStats['ce_mois'] ?? 0,
            'batiments_total' => $batimentsTotal,
            'portes_total' => $portesTotal,
            'portes_occupees' => $portesOccupees,
            'portes_libres' => $portesLibres,
            'maintenances_total' => $maintenancesTotal,
            'maintenances_en_cours' => $maintenancesEnCours,
            'maintenances_en_attente' => $maintenancesEnAttente,
            'maintenances_terminees' => $maintenancesTerminees,
            'maintenances_annulees' => $maintenancesAnnulees,
            'cout_maintenance_mois' => $coutMaintenanceMois,
            'revenu_mois' => $revenuMois,
            'total_encaisse' => $totalEncaisse,
            'transactions_validees' => $transactionsValidees,
            'transactions_en_attente' => $transactionsEnAttente,
            'transactions_echouees' => $transactionsEchouees,
            'occupation_rate' => $occupationRate,
            'allocation_rate' => $allocationRate,
            'maintenance_close_rate' => $maintenanceCloseRate,
            'transaction_success_rate' => $transactionSuccessRate,
        ];

        return Inertia::render('Agence/Statistiques/Index', [
            'stats' => $stats,
            'monthlyLabels' => $monthlyLabels,
            'revenueSeries' => $revenueSeries,
            'maintenanceMonthSeries' => $maintenanceMonthSeries,
            'maintenanceSeries' => $maintenanceSeries,
            'topMaintenanceTypes' => $topMaintenanceTypes,
            'topProperties' => $topProperties,
            'recentTransactions' => $recentTransactions,
            'recentMaintenances' => $recentMaintenances,
            'year' => $year,
        ]);
    }

    private function agenceId(): string
    {
        return getInfoAgent()->users->agence_id;
    }

    private function safeArray(\Closure $callback, array $default = []): array
    {
        try {
            $value = $callback();

            return is_array($value) ? $value : $default;
        } catch (\Throwable) {
            return $default;
        }
    }

    private function safeCollection(\Closure $callback)
    {
        try {
            $value = $callback();

            return $value instanceof \Illuminate\Support\Collection ? $value : collect($value);
        } catch (\Throwable) {
            return collect();
        }
    }

    private function safeCount(\Closure $callback): int
    {
        try {
            return (int) $callback();
        } catch (\Throwable) {
            return 0;
        }
    }

    private function safeFloat(\Closure $callback): float
    {
        try {
            return (float) $callback();
        } catch (\Throwable) {
            return 0.0;
        }
    }
}
