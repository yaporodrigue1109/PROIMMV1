<?php

namespace App\Http\Controllers\Admin\Statistique;

use App\Http\Controllers\Controller;
use App\Models\Agence;
use App\Models\AbonnementHistorique;
use App\Models\Batiment;
use App\Models\LocataireAgence;
use App\Models\Porte;
use App\Models\ProprietaireAgence;
use App\Models\ProprietaireLot;
use App\Models\Propriete;
use App\Models\SupportTicket;
use App\Models\Transaction;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class StatistiqueController extends Controller
{

    public function index(): Response
    {
        $now = now();
        $agencies = Agence::with('abonnement')->get();
        $transactions = Transaction::with(['agence', 'abonnement'])->latest()->get();
        $validatedTransactions = $transactions->where('statut', 'validee');
        $transactionDate = static fn (Transaction $transaction) =>
            $transaction->date_validation ?? $transaction->date_paiement ?? $transaction->created_at;

        $revenueRows = collect(range(5, 0))->map(function ($offset) use ($validatedTransactions, $transactionDate, $now) {
            $month = $now->copy()->subMonths($offset);
            $items = $validatedTransactions
                ->filter(fn (Transaction $transaction) => $transactionDate($transaction)?->isSameMonth($month));
            return [
                'mois' => ucfirst($month->locale('fr')->translatedFormat('F Y')),
                'abo' => $items->count(),
                'montant' => (float) $items->sum('montant_ttc'),
            ];
        })->values();

        $subscriptionHistories = AbonnementHistorique::query()
            ->where('created_at', '>=', $now->copy()->subMonths(11)->startOfMonth())
            ->get(['agence_id', 'action', 'montant_ht', 'created_at']);

        $subscriptionMonthlyRows = collect(range(11, 0))->map(function ($offset) use ($subscriptionHistories, $now) {
            $month = $now->copy()->subMonths($offset);
            $items = $subscriptionHistories
                ->filter(fn (AbonnementHistorique $history) => $history->created_at?->isSameMonth($month));
            $newSubscriptions = $items->where('action', 'creation')->count();
            $renewals = $items->where('action', 'renouvellement')->count();

            return [
                'mois' => ucfirst($month->locale('fr')->translatedFormat('M y')),
                'nouveaux' => $newSubscriptions,
                'renouvellements' => $renewals,
                'total' => $newSubscriptions + $renewals,
                'agences' => $items->pluck('agence_id')->filter()->unique()->count(),
                'montant' => (float) $items->sum('montant_ht'),
            ];
        })->values();

        $latestValidatedTransactionByAgency = $validatedTransactions
            ->groupBy('agence_id')
            ->map(fn ($items) => $items
                ->sortByDesc(fn (Transaction $transaction) => $transactionDate($transaction)?->timestamp ?? 0)
                ->first());

        $subscriptions = $agencies->map(function ($agency) use ($latestValidatedTransactionByAgency) {
            $transaction = $latestValidatedTransactionByAgency->get($agency->agence_id);
            $moduleAmount = (float) ($transaction?->montant_options_ht ?? 0);
            $totalAmount = (float) ($transaction?->montant_total_ht ?? $transaction?->montant_ttc ?? 0);
            $baseAmount = (float) ($transaction?->montant_base_ht ?? 0);

            if ($transaction && $baseAmount <= 0 && $totalAmount >= $moduleAmount) {
                $baseAmount = $totalAmount - $moduleAmount;
            }

            if (! $transaction) {
                $baseAmount = (float) ($agency->abonnement?->prix_mensuel_ht ?? 0);
                $totalAmount = $baseAmount;
            }

            return [
                'agence' => $agency->name,
                'code' => $agency->code_agence,
                'plan' => $agency->abonnement?->name ?? 'Sans plan',
                'debut' => optional($agency->abonnement_start)->format('d/m/Y') ?? '—',
                'fin' => optional($agency->abonnement_end)->format('d/m/Y') ?? '—',
                'statut' => $this->subscriptionStatus($agency),
                'montant_base' => $baseAmount,
                'montant_modules' => $moduleAmount,
                'montant_total' => $totalAmount,
                'montant' => $totalAmount,
            ];
        })->values();

        $revenueByAgency = $validatedTransactions->groupBy('agence_id')
            ->map(fn ($items) => (float) $items->sum('montant_ttc'));
        $agencyRows = $agencies->map(function ($agency) use ($revenueByAgency) {
            $features = collect($agency->abonnement?->features ?? [])->filter();
            return [
                'agence' => $agency->name,
                'code' => $agency->code_agence,
                'modules' => $features->count(),
                'statut' => $this->subscriptionStatus($agency),
                'montant' => (float) ($revenueByAgency->get($agency->agence_id) ?? 0),
            ];
        })->values();
        $maxAgencyRevenue = max(1, (float) $agencyRows->max('montant'));
        $agencyRows = $agencyRows->map(fn ($agency) => $agency + [
            'pct' => round(($agency['montant'] / $maxAgencyRevenue) * 100),
        ]);

        $locataireCounts = LocataireAgence::query()
            ->selectRaw('agence_id, COUNT(DISTINCT locataire_id) as total')
            ->groupBy('agence_id')
            ->pluck('total', 'agence_id');
        $proprieteCounts = Propriete::query()
            ->where('is_actif', true)
            ->selectRaw('agence_id, COUNT(*) as total')
            ->groupBy('agence_id')
            ->pluck('total', 'agence_id');
        $proprietaireCounts = ProprietaireAgence::query()
            ->active()
            ->selectRaw('agence_id, COUNT(DISTINCT proprietaire_id) as total')
            ->groupBy('agence_id')
            ->pluck('total', 'agence_id');
        $personnelCounts = User::query()
            ->where('statut', 'actif')
            ->selectRaw('agence_id, COUNT(*) as total')
            ->groupBy('agence_id')
            ->pluck('total', 'agence_id');

        $agencyRankings = [
            'locataires' => $this->buildAgencyRanking($agencies, $locataireCounts),
            'proprietes' => $this->buildAgencyRanking($agencies, $proprieteCounts),
            'proprietaires' => $this->buildAgencyRanking($agencies, $proprietaireCounts),
            'personnel' => $this->buildAgencyRanking($agencies, $personnelCounts),
        ];

        $payments = $transactions->take(30)->map(fn (Transaction $transaction) => [
            'agence' => $transaction->agence?->name ?? 'Agence',
            'code' => $transaction->agence?->code_agence ?? '—',
            'date' => optional($transactionDate($transaction))->format('d/m/Y'),
            'montant' => (float) $transaction->montant_ttc,
            'mode' => $transaction->mode_paiement ?: 'Non renseigné',
            'statut' => match ($transaction->statut) {
                'validee' => 'Payé',
                'en_attente' => 'En attente',
                default => 'Annulé',
            },
            'ref' => $transaction->reference ?: 'TXN-'.$transaction->getKey(),
        ])->values();

        $totalDoors = Porte::query()->count();
        $occupiedDoors = Porte::query()->where('is_occupe', true)->count();
        $freeDoors = Porte::query()->where('is_occupe', false)->count();
        $totalTickets = SupportTicket::query()->count();
        $resolvedTickets = SupportTicket::query()->whereIn('statut', ['resolu', 'ferme'])->count();
        $generalStats = [
            'agences' => $agencies->count(),
            'locataires' => LocataireAgence::query()->distinct('locataire_id')->count('locataire_id'),
            'utilisateurs' => User::query()->count(),
            'proprietaires' => ProprietaireAgence::query()->distinct('proprietaire_id')->count('proprietaire_id'),
            'proprietes' => Propriete::query()->count(),
            'portes' => $totalDoors,
            'portes_occupees' => $occupiedDoors,
            'portes_libres' => $freeDoors,
            'taux_occupation' => $totalDoors > 0 ? round(($occupiedDoors / $totalDoors) * 100) : 0,
            'batiments' => Batiment::query()->count(),
            'lots' => ProprietaireLot::query()->count(),
            'contrats_actifs' => LocataireAgence::query()->where('is_active', true)->count(),
            'tickets' => $totalTickets,
            'tickets_resolus' => $resolvedTickets,
            'taux_resolution_tickets' => $totalTickets > 0 ? round(($resolvedTickets / $totalTickets) * 100) : 0,
        ];

        return Inertia::render('Admin/Statistiques/Index', [
            'revenueRows' => $revenueRows,
            'subscriptions' => $subscriptions,
            'subscriptionMonthlyRows' => $subscriptionMonthlyRows,
            'agencies' => $agencyRows,
            'agencyRankings' => $agencyRankings,
            'payments' => $payments,
            'generalStats' => $generalStats,
            'summary' => [
                'total_agencies' => $agencies->count(),
                'subscribed_agencies' => $subscriptions->where('statut', 'Actif')->count(),
                'unsubscribed_agencies' => $subscriptions->where('statut', '!=', 'Actif')->count(),
                'max_agency_revenue' => (float) $agencyRows->max('montant'),
                'updated_at' => now()->format('d/m/Y H:i'),
                'period_label' => $revenueRows->first()['mois'].' — '.$revenueRows->last()['mois'],
                'period_months' => $revenueRows->count(),
            ],
        ]);
    }

    private function subscriptionStatus(Agence $agency): string
    {
        if (! $agency->abonnement_start || ! $agency->abonnement_end) {
            return 'En attente';
        }
        if (now()->lt($agency->abonnement_start)) {
            return 'En attente';
        }
        return now()->gt($agency->abonnement_end) ? 'Expiré' : 'Actif';
    }

    private function buildAgencyRanking($agencies, $counts)
    {
        return $agencies
            ->map(fn (Agence $agency) => [
                'agence' => $agency->name,
                'code' => $agency->code_agence,
                'total' => (int) ($counts->get($agency->agence_id) ?? 0),
            ])
            ->sortByDesc(fn (array $row) => $row['total'])
            ->values();
    }
}
