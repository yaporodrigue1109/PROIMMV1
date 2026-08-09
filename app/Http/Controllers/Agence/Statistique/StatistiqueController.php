<?php

namespace App\Http\Controllers\Agence\Statistique;

use App\Http\Controllers\Controller;
use App\Models\Batiment;
use App\Models\Maintenance;
use App\Models\MaintenanceDetail;
use App\Models\LocataireAgence;
use App\Models\Porte;
use App\Models\Propriete;
use App\Models\ProprietaireAgence;
use App\Models\TransactionAgence;
use App\Models\Reversement;
use App\Models\User;
use App\Repositories\Agence\Interfaces\LocataireRepositoryInterface;
use App\Repositories\Agence\Interfaces\MaintenanceRepositoryInterface;
use App\Repositories\Agence\Interfaces\ProprieteRepositoryInterface;
use App\Repositories\Interfaces\TransactionRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;
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

    public function index(Request $request): Response
    {
        $agenceId = $this->agenceId();
        $filters = $request->validate([
            'periode' => ['nullable', 'date_format:Y-m'],
            'date_debut' => ['nullable', 'required_with:date_fin', 'date_format:Y-m-d'],
            'date_fin' => ['nullable', 'required_with:date_debut', 'date_format:Y-m-d', 'after_or_equal:date_debut'],
        ]);

        if (! empty($filters['date_debut']) && ! empty($filters['date_fin'])) {
            $periodStart = Carbon::createFromFormat('Y-m-d', $filters['date_debut'])->startOfDay();
            $periodEnd = Carbon::createFromFormat('Y-m-d', $filters['date_fin'])->endOfDay();
        } else {
            $selectedMonth = Carbon::createFromFormat('Y-m', $filters['periode'] ?? now()->format('Y-m'));
            $periodStart = $selectedMonth->copy()->startOfMonth();
            $periodEnd = $selectedMonth->copy()->endOfMonth();
        }

        $selectedDate = $periodEnd->copy()->startOfMonth();
        $period = $periodStart->format('Y-m');
        $year = $periodStart->year === $periodEnd->year
            ? (string) $periodStart->year
            : $periodStart->year.' – '.$periodEnd->year;

        $proprietesQuery   = Propriete::query()->where('agence_id', $agenceId);
        $batimentsQuery    = Batiment::query()->where('agence_id', $agenceId);
        $portesQuery       = Porte::query()->where('agence_id', $agenceId);
        $maintenancesQuery = Maintenance::query()->where('agence_id', $agenceId)->whereBetween('created_at', [$periodStart, $periodEnd]);
        $transactionsQuery = TransactionAgence::query()
            ->where('agence_id', $agenceId)
            ->whereBetween('date_transaction', [$periodStart, $periodEnd]);

        $proprietesStats = $this->safeArray(
            fn () => $this->proprieteRepo->stats(),
            ['total' => 0, 'allocation' => 0, 'non_allocation' => 0, 'ce_mois' => 0]
        );
        $locatairesStats = $this->safeArray(
            fn () => $this->locataireRepo->stats(),
            ['total' => 0, 'actifs' => 0, 'resilies' => 0, 'ce_mois' => 0]
        );
        $maintenanceStats = $this->safeArray(
            fn () => (clone $maintenancesQuery)
                ->selectRaw('statut, COUNT(*) as total')
                ->groupBy('statut')
                ->pluck('total', 'statut')
                ->toArray(),
            ['en_attente' => 0, 'en_cours' => 0, 'termine' => 0, 'annule' => 0, 'validee' => 0, 'echouee' => 0]
        );
        $totalEncaisse = $this->safeFloat(
            fn () => (clone $transactionsQuery)
                ->whereIn('type_transaction', ['loyer', 'vente'])
                ->sum('montant_global_verser')
        );

        $proprietesStats['ce_mois'] = $this->safeCount(fn () => Propriete::where('agence_id', $agenceId)->whereBetween('created_at', [$periodStart, $periodEnd])->count());
        $locatairesStats['ce_mois'] = $this->safeCount(fn () => LocataireAgence::where('agence_id', $agenceId)->whereBetween('created_at', [$periodStart, $periodEnd])->count());

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
        $coutMaintenanceMois = $this->safeFloat(fn () => (clone $maintenancesQuery)->sum('montant_global'));

        $transactionsValidees = $this->safeCount(fn () => (clone $transactionsQuery)->count());
        $transactionsEnAttente = 0;
        $transactionsEchouees = 0;
        $revenuMois = $this->safeFloat(fn () => (clone $transactionsQuery)
            ->where('type_transaction', 'loyer')
            ->sum('montant_global_verser'));
        $ventesMontant = $this->safeFloat(fn () => (clone $transactionsQuery)
            ->where('type_transaction', 'vente')
            ->sum('montant_global_verser'));
        $ventesNombre = $this->safeCount(fn () => (clone $transactionsQuery)
            ->where('type_transaction', 'vente')
            ->count());

        $revenueByDay = $this->safeArray(
            fn () => (clone $transactionsQuery)
                ->where('type_transaction', 'loyer')
                ->selectRaw('DATE(date_transaction) as day, SUM(montant_global_verser) as total')
                ->groupBy('day')
                ->pluck('total', 'day')
                ->toArray()
        );
        $salesByDay = $this->safeArray(
            fn () => (clone $transactionsQuery)
                ->where('type_transaction', 'vente')
                ->selectRaw('DATE(date_transaction) as day, SUM(montant_global_verser) as total')
                ->groupBy('day')
                ->pluck('total', 'day')
                ->toArray()
        );

        $maintenanceByDay = $this->safeArray(
            fn () => (clone $maintenancesQuery)
                ->selectRaw('DATE(created_at) as day, SUM(montant_global) as total')
                ->groupBy('day')
                ->pluck('total', 'day')
                ->toArray()
        );

        $proprietairesByDay = $this->dailyCounts(
            ProprietaireAgence::query()->where('agence_id', $agenceId)->whereBetween('created_at', [$periodStart, $periodEnd])
        );
        $locatairesByDay = $this->dailyCounts(
            LocataireAgence::query()->where('agence_id', $agenceId)->whereBetween('created_at', [$periodStart, $periodEnd])
        );
        $personnelByDay = $this->dailyCounts(
            User::query()->where('agence_id', $agenceId)->whereBetween('created_at', [$periodStart, $periodEnd])
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

        $monthlyLabels = [];
        $revenueSeries = [];
        $salesMonthSeries = [];
        $maintenanceMonthSeries = [];
        $proprietairesMonthSeries = [];
        $locatairesMonthSeries = [];
        $personnelMonthSeries = [];
        $loyersMonthSeries = [];

        for ($date = $periodStart->copy()->startOfDay(); $date->lte($periodEnd); $date->addDay()) {
            $dateKey = $date->toDateString();
            $monthlyLabels[] = $date->format('d/m');
            $revenueSeries[] = (float) ($revenueByDay[$dateKey] ?? 0);
            $salesMonthSeries[] = (float) ($salesByDay[$dateKey] ?? 0);
            $maintenanceMonthSeries[] = (float) ($maintenanceByDay[$dateKey] ?? 0);
            $proprietairesMonthSeries[] = (int) ($proprietairesByDay[$dateKey] ?? 0);
            $locatairesMonthSeries[] = (int) ($locatairesByDay[$dateKey] ?? 0);
            $personnelMonthSeries[] = (int) ($personnelByDay[$dateKey] ?? 0);
            $encaisse = (float) ($revenueByDay[$dateKey] ?? 0);
            $loyersMonthSeries[] = ['encaisse' => $encaisse, 'impaye' => max($encaisse * 0.12, 0)];
        }

        $recentTransactions = $this->safeCollection(fn () => (clone $transactionsQuery)
            ->latest('date_transaction')
            ->limit(6)
            ->get()
            ->map(fn ($transaction) => [
                'id' => $transaction->transaction_agence_id,
                'type' => $transaction->type_transaction,
                'tenant' => ucfirst((string) $transaction->type_transaction),
                'amount' => (float) $transaction->montant_global_verser,
                'date' => optional($transaction->date_transaction)->format('d/m/Y H:i'),
            ]));

        $recentMaintenances = $this->safeCollection(fn () => (clone $maintenancesQuery)
            ->with(['proprietaire', 'propriete'])
            ->latest('created_at')
            ->limit(6)
            ->get());

        $topMaintenanceTypes = $this->safeCollection(fn () => MaintenanceDetail::query()
            ->join('maintenance', 'maintenance.maintenance_id', '=', 'maintenance_detail.maintenance_id')
            ->join('type_maintenances', 'type_maintenances.type_maintenance_id', '=', 'maintenance_detail.type_intervention_id')
            ->where('maintenance.agence_id', $agenceId)
            ->whereBetween('maintenance.created_at', [$periodStart, $periodEnd])
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
            ->whereBetween('created_at', [$periodStart, $periodEnd])
            ->select(
                'propriete_id',
                DB::raw('COUNT(*) as total_maintenances'),
                DB::raw('COALESCE(SUM(montant_global), 0) as montant_total')
            )
            ->groupBy('propriete_id')
            ->orderByDesc('total_maintenances')
            ->limit(5)
            ->get());

        $reversementMonthKeys = collect(range(0, 11))
            ->map(fn ($offset) => $selectedDate->copy()->subMonths($offset)->format('Y-m'))
            ->values();
        $reversementMonthLabels = $reversementMonthKeys->all();
        $reversementPeriodStart = $selectedDate->copy()->subMonths(11)->startOfMonth();
        $reversementPeriodEnd = $selectedDate->copy()->endOfMonth();
        $reversementsByProprietaire = $this->safeCollection(fn () => Reversement::query()
            ->where('agence_id', $agenceId)
            ->where('statut', 'reverse')
            ->whereBetween('date_reversement', [$reversementPeriodStart, $reversementPeriodEnd])
            ->orderBy('date_reversement')
            ->get())
            ->groupBy('proprietaire_id');
        $proprietairesActifsReversements = $this->safeCollection(fn () => ProprietaireAgence::query()
            ->with('proprietaire')
            ->where('agence_id', $agenceId)
            ->where('is_active', true)
            ->get());
        $reversementsYearMatrix = $proprietairesActifsReversements
            ->map(function ($proprietaireAgence) use ($reversementsByProprietaire, $reversementMonthKeys) {
                $proprietaireId = $proprietaireAgence->proprietaire_id;
                $reversements = $reversementsByProprietaire->get($proprietaireId, collect());
                $months = array_fill(0, 12, 0.0);
                foreach ($reversements as $reversement) {
                    $monthKey = optional($reversement->date_reversement)->format('Y-m');
                    $monthIndex = $reversementMonthKeys->search($monthKey);
                    if ($monthIndex !== false) {
                        $months[$monthIndex] += (float) $reversement->net_a_reverser;
                    }
                }

                return [
                    'proprietaire_id' => $proprietaireId,
                    'proprietaire' => $proprietaireAgence->proprietaire?->name ?? 'Propriétaire non renseigné',
                    'months' => $months,
                    'total' => array_sum($months),
                ];
            })
            ->sortBy('proprietaire')
            ->values();

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
            'ventes_montant' => $ventesMontant,
            'ventes_nombre' => $ventesNombre,
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
            'salesMonthSeries' => $salesMonthSeries,
            'maintenanceMonthSeries' => $maintenanceMonthSeries,
            'proprietairesMonthSeries' => $proprietairesMonthSeries,
            'locatairesMonthSeries' => $locatairesMonthSeries,
            'personnelMonthSeries' => $personnelMonthSeries,
            'loyersMonthSeries' => $loyersMonthSeries,
            'maintenanceSeries' => $maintenanceSeries,
            'topMaintenanceTypes' => $topMaintenanceTypes,
            'topProperties' => $topProperties,
            'recentTransactions' => $recentTransactions,
            'recentMaintenances' => $recentMaintenances,
            'reversementsYearMatrix' => $reversementsYearMatrix->all(),
            'reversementMonthLabels' => array_values($reversementMonthLabels),
            'year' => $year,
            'periode' => $period,
            'dateStart' => $periodStart->toDateString(),
            'dateEnd' => $periodEnd->toDateString(),
            'periodLabel' => $periodStart->locale('fr')->translatedFormat('d F Y')
                .' – '
                .$periodEnd->locale('fr')->translatedFormat('d F Y'),
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

    private function dailyCounts($query): array
    {
        return $this->safeArray(fn () => $query
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day')
            ->toArray());
    }
}
