<?php

namespace App\Http\Controllers\Agence\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\LocataireAgence;
use App\Models\Loyer;
use App\Models\Porte;
use App\Models\Propriete;
use App\Models\ProprietaireAgence;
use App\Models\ProprietaireLot;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::guard('user')->user();
        $agenceId = $user?->agence_id;
        $agence = $user?->agence;
        $canViewCaisse = $user?->canPerform('caisse', 'view') ?? false;
        $canViewFinancials = $canViewCaisse
            || ($user?->canPerform('loyer', 'view') ?? false)
            || ($user?->canPerform('reversement', 'view') ?? false);

        $now = now();
        $monthStart = $now->copy()->startOfMonth();
        $monthEnd = $now->copy()->endOfMonth();
        $currentMonth = $now->month;
        $currentYear = $now->year;
        $previousMonth = $now->copy()->subMonth()->month;
        $previousYear = $now->copy()->subMonth()->year;

        $proprietes = Propriete::query()->where('agence_id', $agenceId)->count();
        $personnel = User::query()->where('agence_id', $agenceId)->count();
        $proprietaires = ProprietaireAgence::query()
            ->where('agence_id', $agenceId)
            ->distinct('proprietaire_id')
            ->count('proprietaire_id');
        $lots = ProprietaireLot::query()->where('agence_id', $agenceId)->count();
        $locataires = LocataireAgence::query()
            ->where('agence_id', $agenceId)
            ->where('is_active', true)
            ->distinct('locataire_id')
            ->count('locataire_id');

        $portesTotal = Porte::query()->where('agence_id', $agenceId)->count();
        $portesOccupees = Porte::query()
            ->where('agence_id', $agenceId)
            ->where('is_actif', true)
            ->where('is_occupe', true)
            ->count();
        $portesLibres = Porte::query()
            ->where('agence_id', $agenceId)
            ->where('is_actif', true)
            ->where('is_occupe', false)
            ->count();

        $montantAttendu = null;
        $montantVerse = null;
        $pendingPayments = null;
        $revenueTrend = null;

        if ($canViewFinancials) {
            $loyersCurrent = Loyer::query()
                ->where('agence_id', $agenceId)
                ->where('mois_paiement', $currentMonth)
                ->where('annee_paiement', $currentYear);

            $loyersPrevious = Loyer::query()
                ->where('agence_id', $agenceId)
                ->where('mois_paiement', $previousMonth)
                ->where('annee_paiement', $previousYear);

            $montantAttendu = (float) $loyersCurrent->sum('montant_a_payer');
            $montantVerse = (float) $loyersCurrent->sum('montant_payer');
            $montantVerseMoisPrecedent = (float) $loyersPrevious->sum('montant_payer');
            $pendingPayments = max($montantAttendu - $montantVerse, 0);
            $revenueTrend = $montantVerseMoisPrecedent > 0
                ? round((($montantVerse - $montantVerseMoisPrecedent) / $montantVerseMoisPrecedent) * 100)
                : 0;
        }

        $occupancyRate = $portesTotal > 0 ? round(($portesOccupees / $portesTotal) * 100) : 0;

        $recentPayments = $canViewFinancials
            ? Loyer::query()
                ->with(['locataire', 'propriete'])
                ->where('agence_id', $agenceId)
                ->whereNotNull('date_paiement')
                ->whereBetween('date_paiement', [$monthStart, $monthEnd])
                ->orderByDesc('date_paiement')
                ->limit(5)
                ->get()
                ->map(fn ($payment) => [
                    'id' => $payment->loyer_id,
                    'tenant' => $payment->locataire?->name ?? 'Locataire',
                    'property' => $payment->propriete?->reference ?? 'Bien',
                    'date' => $payment->date_paiement?->format('d/m/Y') ?? '—',
                    'amount' => (float) ($payment->montant_payer ?? 0),
                    'status' => $payment->statut === 'Paiement total' ? 'payé' : 'en attente',
                ])
                ->values()
                ->all()
            : [];

        $upcomingLeases = LocataireAgence::query()
            ->with(['locataire', 'propriete'])
            ->where('agence_id', $agenceId)
            ->where('is_active', true)
            ->whereNotNull('date_fin_bail')
            ->orderBy('date_fin_bail')
            ->limit(5)
            ->get()
            ->map(fn ($lease) => [
                'id' => $lease->locataire_agence_id,
                'tenant' => $lease->locataire?->name ?? 'Locataire',
                'property' => $lease->propriete?->reference ?? 'Bien',
                'endDate' => $lease->date_fin_bail?->format('d/m/Y') ?? '—',
            ])
            ->values()
            ->all();

        $recentProperties = Propriete::query()
            ->where('agence_id', $agenceId)
            ->orderByDesc('created_at')
            ->limit(3)
            ->get()
            ->map(fn ($property) => [
                'id' => $property->propriete_id,
                'name' => $property->reference ?? 'Propriété',
                'location' => $property->adresse_complete ?? '—',
                'rent' => $canViewFinancials
                    ? (float) ($property->batiments->sum(fn ($batiment) => $batiment->portes->sum('mt_loyer')) ?? 0)
                    : null,
                'status' => $property->nbre_porte_occupe > 0 ? 'Occupé' : 'Libre',
            ])
            ->values()
            ->all();

        $stats = [
            'properties' => $proprietes,
            'occupiedUnits' => $portesOccupees,
            'vacantUnits' => $portesLibres,
            'tenants' => $locataires,
            'personnel' => $personnel,
            'proprietaires' => $proprietaires,
            'lots' => $lots,
            'monthlyRevenue' => $montantVerse,
            'expectedRevenue' => $montantAttendu,
            'pendingPayments' => $pendingPayments,
            'occupancyRate' => $occupancyRate,
            'revenueTrend' => $revenueTrend,
        ];

        return Inertia::render('Agence/Dashboard/Index', [
            'agence' => $user?->only([
                'id_users',
                'name',
                'email',
                'phone',
                'statut',
            ]),
            'abonnement' => [
                'dateFin' => $agence?->abonnement_end?->format('d/m/Y'),
                'dateFinIso' => $agence?->abonnement_end?->toDateString(),
            ],
            'stats' => $stats,
            'recentPayments' => $recentPayments,
            'upcomingLeases' => $upcomingLeases,
            'recentProperties' => $recentProperties,
            'abilities' => [
                'viewFinancials' => $canViewFinancials,
                'viewCaisse' => $canViewCaisse,
            ],
            'periodLabel' => ucfirst($now->locale('fr')->translatedFormat('F Y')),
        ]);
    }
}
