<?php

namespace App\Http\Controllers\Admin\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Agence;
use App\Models\SupportTicket;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $admin = Auth::guard('admin')->user();
        $now = now();
        $agencies = Agence::query()->get();
        $transactions = Transaction::with('agence')->latest()->get();
        $validatedTransactions = $transactions->where('statut', 'validee');
        $transactionDate = static fn (Transaction $transaction) =>
            $transaction->date_validation ?? $transaction->date_paiement ?? $transaction->created_at;
        $monthRevenue = (float) $validatedTransactions
            ->filter(fn (Transaction $transaction) => $transactionDate($transaction)?->isSameMonth($now))
            ->sum('montant_ttc');
        $previousMonth = $now->copy()->subMonth();
        $previousRevenue = (float) $validatedTransactions
            ->filter(fn (Transaction $transaction) => $transactionDate($transaction)?->isSameMonth($previousMonth))
            ->sum('montant_ttc');
        $trend = $previousRevenue > 0
            ? round((($monthRevenue - $previousRevenue) / $previousRevenue) * 100)
            : ($monthRevenue > 0 ? 100 : 0);
        $active = $agencies->filter(fn ($agency) => $agency->statut === 'active'
            && $agency->abonnement_start
            && $agency->abonnement_end
            && $now->between($agency->abonnement_start, $agency->abonnement_end))->count();
        $waiting = $transactions->where('statut', 'en_attente')->count();
        $openTickets = SupportTicket::whereNotIn('statut', ['resolu', 'ferme'])->count();

        $revenueSeries = collect(range(5, 0))->map(function ($offset) use ($validatedTransactions, $transactionDate, $now) {
            $month = $now->copy()->subMonths($offset);
            return [
                'label' => ucfirst($month->locale('fr')->translatedFormat('M')),
                'value' => (float) $validatedTransactions
                    ->filter(fn (Transaction $transaction) => $transactionDate($transaction)?->isSameMonth($month))
                    ->sum('montant_ttc'),
            ];
        })->values();

        $alerts = $agencies->filter(fn ($agency) => $agency->abonnement_end && $agency->abonnement_end->lte($now->copy()->addDays(15)))
            ->sortBy('abonnement_end')->take(5)->map(function ($agency) use ($now) {
                $expired = $agency->abonnement_end->lt($now);
                return [
                    'tone' => $expired ? 'danger' : 'warning',
                    'title' => $agency->name.' - '.($expired ? 'abonnement expiré' : 'échéance proche'),
                    'detail' => ($expired ? 'Expiré le ' : 'Échéance le ').$agency->abonnement_end->format('d/m/Y'),
                    'href' => '/admin/abonnements/'.$agency->code_agence,
                ];
            })->values();

        $activity = $transactions->take(8)->map(fn (Transaction $transaction) => [
            'agency' => $transaction->agence?->name ?? 'Agence',
            'code' => $transaction->agence?->code_agence ?? '—',
            'action' => match ($transaction->type_operation) {
                'renouvellement' => 'Abonnement renouvelé',
                default => 'Souscription créée',
            },
            'status' => match ($transaction->statut) {
                'validee' => 'Payé',
                'en_attente' => 'En attente',
                default => 'Annulé',
            },
            'date' => optional($transactionDate($transaction))->format('d/m/Y H:i'),
        ])->values();

        $latestValidatedByAgency = $validatedTransactions
            ->sortByDesc(fn (Transaction $transaction) => $transactionDate($transaction)?->timestamp ?? 0)
            ->groupBy('agence_id')
            ->map->first();

        $deadlines = $agencies->filter(fn ($agency) => $agency->abonnement_end && $agency->abonnement_end->gte($now))
            ->sortBy('abonnement_end')->take(5)->map(fn ($agency) => [
                'agency' => $agency->name,
                'code' => $agency->code_agence,
                'dateFin' => $agency->abonnement_end->format('d/m/Y'),
                'amount' => (float) ($latestValidatedByAgency->get($agency->agence_id)?->montant_ttc ?? 0),
            ])->values();

        return Inertia::render('Admin/Dashboard/Index', [
            'admin' => $admin?->only(['id_admin', 'name', 'email', 'phone', 'statut']),
            'kpis' => [
                ['label' => 'Revenu du mois', 'value' => number_format($monthRevenue, 0, ',', ' ').' FCFA', 'trend' => ($trend >= 0 ? '+' : '').$trend.' %', 'tone' => $trend >= 0 ? 'success' : 'danger'],
                ['label' => 'Abonnements actifs', 'value' => (string) $active, 'trend' => $agencies->count().' agences', 'tone' => 'info'],
                ['label' => 'Paiements en attente', 'value' => (string) $waiting, 'trend' => 'à traiter', 'tone' => 'warning'],
                ['label' => 'Alertes ouvertes', 'value' => (string) $openTickets, 'trend' => 'Tickets', 'tone' => $openTickets ? 'danger' : 'success'],
            ],
            'revenueSeries' => $revenueSeries,
            'alerts' => $alerts,
            'activity' => $activity,
            'deadlines' => $deadlines,
            'updatedAt' => now()->format('d/m/Y H:i:s'),
        ]);
    }
}
