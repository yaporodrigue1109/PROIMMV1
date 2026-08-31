<?php

namespace App\Http\Controllers\Agence\Statistique;

use App\Http\Controllers\Controller;
use App\Models\Batiment;
use App\Models\Maintenance;
use App\Models\MaintenanceDetail;
use App\Models\LocataireAgence;
use App\Models\Loyer;
use App\Models\ProprietaireLot;
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
        $portesTotal = $this->safeCount(fn () => (clone $portesQuery)->where('is_actif', true)->count());
        $portesOccupees = $this->safeCount(fn () => (clone $portesQuery)->where('is_actif', true)->where('is_occupe', true)->count());
        $portesLibres = $this->safeCount(fn () => (clone $portesQuery)->where('is_actif', true)->where('is_occupe', false)->count());
        $lotsTotal = $this->safeCount(fn () => ProprietaireLot::where('agence_id', $agenceId)->count());
        $personnelTotal = $this->safeCount(fn () => User::where('agence_id', $agenceId)->count());
        $personnelActifs = $this->safeCount(fn () => User::where('agence_id', $agenceId)->where('statut', 1)->count());
        $personnelParRole = $this->safeCollection(fn () => User::query()
            ->leftJoin('roles', 'roles.role_id', '=', 'users.role_id')
            ->where('users.agence_id', $agenceId)
            ->selectRaw("COALESCE(roles.name, 'Sans rôle') as label, COUNT(*) as value")
            ->groupBy('roles.name')
            ->get());

        $maintenancesTotal = $this->safeCount(fn () => $maintenancesQuery->count());
        $maintenancesEnCours = $this->safeCount(fn () => (clone $maintenancesQuery)->where('statut', Maintenance::STATUT_EN_COURS)->count());
        $maintenancesTerminees = $this->safeCount(fn () => (clone $maintenancesQuery)->where('statut', Maintenance::STATUT_TERMINE)->count());
        $maintenancesEnAttente = $this->safeCount(fn () => (clone $maintenancesQuery)->where('statut', Maintenance::STATUT_EN_ATTENTE)->count());
        $maintenancesAnnulees = $this->safeCount(fn () => (clone $maintenancesQuery)->where('statut', Maintenance::STATUT_ANNULE)->count());
        $coutMaintenanceMois = $this->safeFloat(fn () => (clone $transactionsQuery)
            ->where('type_transaction', 'maintenance')
            ->sum('montant_global_verser'));
        $depensesAgenceMontant = $this->safeFloat(fn () => (clone $transactionsQuery)
            ->where('type_transaction', 'depense')
            ->sum('montant_global_verser'));
        $depensesTotal = $coutMaintenanceMois + $depensesAgenceMontant;

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
        $agencyExpensesByDay = $this->transactionAmountsByDay($transactionsQuery, 'depense');
        $maintenanceExpensesByDay = $this->transactionAmountsByDay($transactionsQuery, 'maintenance');

        $loyersQuery = Loyer::query()
            ->where('agence_id', $agenceId)
            ->whereBetween('date_limit_paiement', [$periodStart, $periodEnd]);
        $loyersAttendus = $this->safeFloat(fn () => (clone $loyersQuery)->sum('montant_a_payer'));
        // Le recouvrement doit porter sur les mêmes échéances que le montant attendu.
        // Les transactions encaissées pendant la période peuvent contenir des avances
        // ou le règlement d'anciens impayés et ne constituent donc pas le numérateur.
        $loyersRecouvres = $this->safeFloat(fn () => (clone $loyersQuery)
            ->selectRaw('COALESCE(SUM(LEAST(COALESCE(montant_payer, 0), montant_a_payer)), 0) AS total')
            ->value('total'));
        $impayesTotal = $this->safeFloat(fn () => (clone $loyersQuery)
            ->whereIn('statut', [Loyer::STATUT_IMPAYE, Loyer::STATUT_PARTIEL])
            ->sum('montant_restant'));
        $impayesByDay = $this->safeArray(fn () => (clone $loyersQuery)
            ->whereIn('statut', [Loyer::STATUT_IMPAYE, Loyer::STATUT_PARTIEL])
            ->selectRaw('DATE(date_limit_paiement) as day, SUM(montant_restant) as total')
            ->groupBy('day')
            ->pluck('total', 'day')
            ->toArray());

        $modesPaiementRecouvrement = $this->safeCollection(fn () => (clone $transactionsQuery)
            ->leftJoin('mode_paiements', 'mode_paiements.id', '=', 'transaction_agences.mode_paiement_id')
            ->where('transaction_agences.type_transaction', TransactionAgence::STATUT_LOYER)
            ->where('transaction_agences.montant_global_verser', '>', 0)
            ->selectRaw("COALESCE(mode_paiements.name, 'Non renseigné') AS mode")
            ->selectRaw('COUNT(transaction_agences.transaction_agence_id) AS nombre_paiements')
            ->selectRaw('SUM(transaction_agences.montant_global_verser) AS montant_total')
            ->groupBy('transaction_agences.mode_paiement_id', 'mode_paiements.name')
            ->orderByDesc('nombre_paiements')
            ->get()
            ->map(fn ($mode) => [
                'mode' => $mode->mode,
                'nombre_paiements' => (int) $mode->nombre_paiements,
                'montant_total' => (float) $mode->montant_total,
            ]));

        $meilleursPayeurs = $this->safeCollection(fn () => (clone $transactionsQuery)
            ->join('locataire', 'locataire.locataire_id', '=', 'transaction_agences.locataire_id')
            ->where('transaction_agences.type_transaction', TransactionAgence::STATUT_LOYER)
            ->where('transaction_agences.montant_global_verser', '>', 0)
            ->select('transaction_agences.locataire_id', 'locataire.name', 'locataire.code')
            ->selectRaw('SUM(transaction_agences.montant_global_verser) AS montant_total')
            ->groupBy('transaction_agences.locataire_id', 'locataire.name', 'locataire.code')
            ->orderByDesc('montant_total')
            ->limit(10)
            ->get()
            ->map(fn ($locataire) => [
                'locataire_id' => $locataire->locataire_id,
                'name' => $locataire->name,
                'code' => $locataire->code,
                'montant' => (float) $locataire->montant_total,
            ]));

        $mauvaisPayeurs = $this->safeCollection(fn () => (clone $loyersQuery)
            ->join('locataire', 'locataire.locataire_id', '=', 'loyer.locataire_id')
            ->where('loyer.montant_restant', '>', 0)
            ->select('loyer.locataire_id', 'locataire.name', 'locataire.code')
            ->selectRaw('SUM(loyer.montant_restant) AS montant_total')
            ->groupBy('loyer.locataire_id', 'locataire.name', 'locataire.code')
            ->orderByDesc('montant_total')
            ->limit(10)
            ->get()
            ->map(fn ($locataire) => [
                'locataire_id' => $locataire->locataire_id,
                'name' => $locataire->name,
                'code' => $locataire->code,
                'montant' => (float) $locataire->montant_total,
            ]));

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

        $maintenanceSeries = [
            ['label' => 'En attente', 'value' => $maintenancesEnAttente],
            ['label' => 'En cours', 'value' => $maintenancesEnCours],
            ['label' => 'Terminée', 'value' => $maintenancesTerminees],
            ['label' => 'Annulée', 'value' => $maintenancesAnnulees],
        ];

        $monthlyLabels = [];
        $revenueSeries = [];
        $salesMonthSeries = [];
        $maintenanceMonthSeries = [];
        $proprietairesMonthSeries = [];
        $locatairesMonthSeries = [];
        $personnelMonthSeries = [];
        $loyersMonthSeries = [];
        $depensesAgenceSeries = [];
        $depensesMaintenanceSeries = [];

        for ($date = $periodStart->copy()->startOfDay(); $date->lte($periodEnd); $date->addDay()) {
            $dateKey = $date->toDateString();
            $monthlyLabels[] = $date->format('d/m');
            $revenueSeries[] = (float) ($revenueByDay[$dateKey] ?? 0);
            $salesMonthSeries[] = (float) ($salesByDay[$dateKey] ?? 0);
            $maintenanceMonthSeries[] = (float) ($maintenanceByDay[$dateKey] ?? 0);
            $depensesAgenceSeries[] = (float) ($agencyExpensesByDay[$dateKey] ?? 0);
            $depensesMaintenanceSeries[] = (float) ($maintenanceExpensesByDay[$dateKey] ?? 0);
            $proprietairesMonthSeries[] = (int) ($proprietairesByDay[$dateKey] ?? 0);
            $locatairesMonthSeries[] = (int) ($locatairesByDay[$dateKey] ?? 0);
            $personnelMonthSeries[] = (int) ($personnelByDay[$dateKey] ?? 0);
            $encaisse = (float) ($revenueByDay[$dateKey] ?? 0);
            $loyersMonthSeries[] = ['encaisse' => $encaisse, 'impaye' => (float) ($impayesByDay[$dateKey] ?? 0)];
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
        $recentExpenses = $this->safeCollection(fn () => (clone $transactionsQuery)
            ->whereIn('type_transaction', ['depense', 'maintenance'])
            ->with('modePaiement')
            ->latest('date_transaction')
            ->limit(20)
            ->get()
            ->map(fn ($transaction) => [
                'id' => $transaction->transaction_agence_id,
                'type' => $transaction->type_transaction,
                'label' => $transaction->mois_payer
                    ?: ($transaction->type_transaction === 'maintenance' ? 'Maintenance' : 'Dépense agence'),
                'amount' => (float) $transaction->montant_global_verser,
                'mode' => $transaction->modePaiement?->name ?? 'Non renseigné',
                'date' => optional($transaction->date_transaction)->format('d/m/Y H:i'),
            ]));

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
        $reversementsEnAttente = $this->safeCount(fn () => Reversement::query()
            ->where('agence_id', $agenceId)
            ->where('statut', 'en_attente')
            ->whereBetween('created_at', [$periodStart, $periodEnd])
            ->count());
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
            'lots_total' => $lotsTotal,
            'personnel_total' => $personnelTotal,
            'personnel_actifs' => $personnelActifs,
            'locataires_a_jour' => max(0, (int) ($locatairesStats['actifs'] ?? 0) - $this->safeCount(fn () => (clone $loyersQuery)->impayesOuPartiels()->distinct('locataire_id')->count('locataire_id'))),
            'locataires_en_retard' => $this->safeCount(fn () => (clone $loyersQuery)->impayesOuPartiels()->distinct('locataire_id')->count('locataire_id')),
            'maintenances_total' => $maintenancesTotal,
            'maintenances_en_cours' => $maintenancesEnCours,
            'maintenances_en_attente' => $maintenancesEnAttente,
            'maintenances_terminees' => $maintenancesTerminees,
            'maintenances_annulees' => $maintenancesAnnulees,
            'cout_maintenance_mois' => $coutMaintenanceMois,
            'depenses_agence_montant' => $depensesAgenceMontant,
            'depenses_total' => $depensesTotal,
            'loyers_attendus' => $loyersAttendus,
            'loyers_recouvres' => $loyersRecouvres,
            'impayes_total' => $impayesTotal,
            'revenu_mois' => $revenuMois,
            'ventes_montant' => $ventesMontant,
            'ventes_nombre' => $ventesNombre,
            'total_encaisse' => $totalEncaisse,
            'reversements_attendu' => $loyersAttendus,
            'occupation_rate' => $occupationRate,
            'allocation_rate' => $allocationRate,
            'maintenance_close_rate' => $maintenanceCloseRate,
            'reversements_en_attente' => $reversementsEnAttente,
        ];

        return Inertia::render('Agence/Statistiques/Index', [
            'stats' => $stats,
            'monthlyLabels' => $monthlyLabels,
            'revenueSeries' => $revenueSeries,
            'salesMonthSeries' => $salesMonthSeries,
            'maintenanceMonthSeries' => $maintenanceMonthSeries,
            'depensesAgenceSeries' => $depensesAgenceSeries,
            'depensesMaintenanceSeries' => $depensesMaintenanceSeries,
            'proprietairesMonthSeries' => $proprietairesMonthSeries,
            'locatairesMonthSeries' => $locatairesMonthSeries,
            'personnelMonthSeries' => $personnelMonthSeries,
            'personnelParRole' => $personnelParRole->all(),
            'loyersMonthSeries' => $loyersMonthSeries,
            'maintenanceSeries' => $maintenanceSeries,
            'topMaintenanceTypes' => $topMaintenanceTypes,
            'topProperties' => $topProperties,
            'recentTransactions' => $recentTransactions,
            'recentMaintenances' => $recentMaintenances,
            'recentExpenses' => $recentExpenses,
            'modesPaiementRecouvrement' => $modesPaiementRecouvrement->all(),
            'meilleursPayeurs' => $meilleursPayeurs->all(),
            'mauvaisPayeurs' => $mauvaisPayeurs->all(),
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

    private function transactionAmountsByDay($query, string $type): array
    {
        return $this->safeArray(fn () => (clone $query)
            ->where('type_transaction', $type)
            ->selectRaw('DATE(date_transaction) as day, SUM(montant_global_verser) as total')
            ->groupBy('day')
            ->pluck('total', 'day')
            ->toArray());
    }
}
