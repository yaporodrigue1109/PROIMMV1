<?php

namespace App\Http\Controllers\Agence\Loyer;

use App\Http\Controllers\Controller;
use App\Models\Locataire;
use App\Models\Loyer;
use App\Models\ModePaiement;
use App\Models\CaisseSession;
use App\Models\TransactionAgence;
use App\Models\ParametrageAgence;
use App\Services\Agence\PaiementLoyerService;
use App\Services\Agence\LoyerReceiptService;
use App\Services\Agence\CaisseClotureService;
use App\Services\Agence\FacturationNotificationService;
use App\Http\Requests\Agence\PayerLoyerRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;

class LoyerController extends Controller
{
protected  $paiementLoyerService;
 public function __construct(
     PaiementLoyerService $paiementLoyerService,
     private CaisseClotureService $caisseClotureService,
     private FacturationNotificationService $facturationNotifications,
     private LoyerReceiptService $receiptService,
 )
    {
        $this->paiementLoyerService = $paiementLoyerService;
    }

    public function index()
    {
        $this->caisseClotureService->cloturerCaissesExpirees($this->agenceId());
        $modePaiement = ModePaiement::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('Agence/Caisse/Loyer', [
            'modePaiement' => $modePaiement,
            'caisseOuverte' => CaisseSession::where('agence_id', $this->agenceId())->whereNull('closed_at')->exists(),
        ]);
    }

    public function search(Request $request)
    {
        $agenceId = $this->agenceId();
        $query = trim($request->get('q', ''));
        $hasLoyerTable = Schema::hasTable('loyer');

        if (strlen($query) < 2) {
            return response()->json(['data' => null]);
        }
//dd( $hasLoyerTable,$query);
      $locataire = Locataire::whereHas('contrats', function ($q) use ($agenceId) {
        $q->where('agence_id', $agenceId) 
          ->where('is_active', 1); // uniquement les baux actifs de cette agence
    })
            ->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                    ->orWhere('tel1', 'LIKE', "%{$query}%")
                    ->orWhere('tel2', 'LIKE', "%{$query}%")
                    ->orWhere('email', 'LIKE', "%{$query}%");
            })
            ->first();

        if (! $locataire) {
            return response()->json(['data' => null]);
        }

        $bauxActifs = $locataire->bauxActifs($agenceId)
            ->where('agence_id', $agenceId)
            ->where('locataire_id', $locataire->locataire_id)
            ->withDefaultRelations()
            ->get();

        if ($bauxActifs->isEmpty()) {
            return response()->json(['data' => null]);
        }

        $rentals = [];
//dd($bauxActifs->where('locataire_id', $locataire->locataire_id),$locataire->locataire_id);
        foreach ($bauxActifs->where('locataire_id', $locataire->locataire_id)  as $bail) {
            $dernierPaiement = null;
            $montantDu = 0;
            $nbMoisRetard = 0;
            $history = [];
            $periode = '—';
            $statut = $hasLoyerTable ? 'A jour' : 'Historique indisponible';
            $statusClass = $hasLoyerTable ? 'payment-status-success' : 'payment-status-warning';
            $statusLabel = $hasLoyerTable ? 'Solde' : 'Donnees locales absentes';

            if ($hasLoyerTable) {
                $dernierPaiement = Loyer::where('locataire_id', $locataire->locataire_id)
                    ->where('agence_id', $agenceId)
                    ->where('proprietaire_id', $bail->proprietaire_id)
                    ->where('batiment_id', $bail->batiment_id)
                    ->where('lot_id', $bail->lot_id)
                    ->where('porte_id', $bail->porte_id)
                    ->whereNotNull('date_paiement')
                    ->latest('date_paiement')
                    ->first([
                        'loyer_id',
                        'mois_paiement',
                        'annee_paiement',
                        'date_paiement',
                        'montant_payer',
                        'montant_restant',
                        'statut',
                    ]);

                $montantDu = Loyer::where('locataire_id', $locataire->locataire_id)
                    ->where('agence_id', $agenceId)
                    ->where('proprietaire_id', $bail->proprietaire_id)
                    ->where('porte_id', $bail->porte_id)
                    ->impayesOuPartiels()
                    ->sum('montant_restant');

                $nbMoisRetard = Loyer::where('locataire_id', $locataire->locataire_id)
                    ->where('agence_id', $agenceId)
                    ->where('porte_id', $bail->porte_id)
                    ->where('proprietaire_id', $bail->proprietaire_id)
                    ->impayesOuPartiels()
                    ->count();

                [$statut, $statusClass, $statusLabel] = $this->determinerStatut(
                    $montantDu,
                    $nbMoisRetard,
                    $dernierPaiement
                );

                $periode = $this->getPeriodeConcernee(
                    $locataire->locataire_id,
                    $agenceId,
                    $bail->porte_id,
                    $dernierPaiement
                );

                $history = Loyer::where('locataire_id', $locataire->locataire_id)
                    ->where('agence_id', $agenceId)
                    ->where('porte_id', $bail->porte_id)
                    ->where('proprietaire_id', $bail->proprietaire_id)
                    ->orderByDesc('annee_paiement')
                    ->orderByDesc('mois_paiement')
                    ->limit(5)
                    ->get()
                    ->map(fn ($row) => $this->formatHistorique($row))
                    ->toArray();
            }

            $rentals[] = [
                'locataire_agence_id' => $bail->locataire_agence_id,
                'locataire_id' => $bail->locataire_id,
                'agence_id' => $bail->agence_id,
                'proprietaire_id' => $bail->proprietaire_id,
                'propriete_id' => $bail->propriete_id,
                'batiment_id' => $bail->batiment_id,
                'lot_id' => $bail->lot_id,
                'porte_id' => $bail->porte_id,
                'property' => trim("{$bail->batiment->name} - {$bail->porte->numero_porte}"),
                'location' => $bail->lot->name ?? '',
                'rent' => (int) $bail->porte->mt_loyer,
                'period' => $periode,
                'due' => (int) $montantDu,
                'lastPayment' => $dernierPaiement?->date_paiement?->format('d/m/Y') ?? '—',
                'delay' => $nbMoisRetard > 0 ? "{$nbMoisRetard} mois de retard" : 'Aucun retard',
                'status' => $statut,
                'paymentStatus' => [
                    'label' => $statusLabel,
                    'className' => $statusClass,
                ],
                'history' => $history,
            ];
        }
//dd($rentals);
        return response()->json([
            'data' => [
                'locataire_id' => $locataire->locataire_id,
                'name' => $locataire->name,
                'phone' => $locataire->tel1,
                'rentals' => $rentals,
            ],
        ]);
    }

    private function determinerStatut($montantDu, $nbMoisRetard, $dernierPaiement): array
    {
        if ($montantDu <= 0) {
            return ['A jour', 'payment-status-success', 'Solde'];
        }

        if ($nbMoisRetard > 0 && $dernierPaiement && $dernierPaiement->montant_payer > 0) {
            return ['Partiel', 'payment-status-warning', 'Partiellement paye'];
        }

        return ['En retard', 'payment-status-danger', 'Non paye'];
    }

    private function getPeriodeConcernee($locataireId, $agenceId, $porteId, $dernierPaiement): string
    {
        $moisConcerne = Loyer::where('locataire_id', $locataireId)
            ->where('agence_id', $agenceId)
            ->where('porte_id', $porteId)
            ->impayesOuPartiels()
            ->orderBy('annee_paiement')
            ->orderBy('mois_paiement')
            ->first(['mois_paiement', 'annee_paiement']);

        if ($moisConcerne) {
            return formatMoisAnnee($moisConcerne->mois_paiement, $moisConcerne->annee_paiement);
        }

        if ($dernierPaiement) {
            return formatMoisAnnee($dernierPaiement->mois_paiement, $dernierPaiement->annee_paiement);
        }

        return '—';
    }

    private function formatHistorique($row): array
    {
        $statusMap = [
            Loyer::STATUT_PAYE => ['label' => Loyer::STATUT_PAYE, 'class' => '#20c997'],
            Loyer::STATUT_PARTIEL => ['label' => Loyer::STATUT_PARTIEL, 'class' => '#fd7e14'],
            Loyer::STATUT_IMPAYE => ['label' => Loyer::STATUT_IMPAYE, 'class' => '#dc3545'],
            Loyer::STATUT_EN_COURS => ['label' => Loyer::STATUT_EN_COURS, 'class' => '#fd7e14'],
        ];

        $info = $statusMap[$row->statut] ?? ['label' => ucfirst((string) $row->statut), 'class' => ''];

        return [
            'period' => formatMoisAnnee($row->mois_paiement, $row->annee_paiement),
            'status' => $info['label'],
            'className' => $info['class'],
            'amount' => (int) ($row->montant_payer ?: $row->montant_a_payer),
        ];
    }

  public function pay(PayerLoyerRequest $request)
{
    $donnees = $request->validated();
    $this->caisseClotureService->cloturerCaissesExpirees($this->agenceId());

    if (! CaisseSession::where('agence_id', $this->agenceId())->whereNull('closed_at')->exists()) {
        return response()->json([
            'success' => false,
            'message' => 'La caisse doit être ouverte avant tout encaissement.',
        ], 422);
    }
 
    try {
        $resultat = $this->paiementLoyerService->payer(
            $this->agenceId(),
            $donnees['locataire_id'],
            $donnees['porte_id'],
            (float) $donnees['montant'],
            (int) $donnees['mode_paiement_id'],
            $donnees['commentaire'] ?? null,
            $this->userId(),
        );

        $this->facturationNotifications->sendPaymentReceipt(
            $this->agenceId(),
            $donnees['locataire_id'],
            $resultat
        );
 
        return response()->json([
            'success' => true,
            'message' => 'Paiement enregistre avec succes.',
            'receipt_url' => route('agence.caisse.loyer.receipt', $resultat['transaction_id']),
        ] + $resultat);
    } catch (PaiementLoyerException $e) {
        // Erreur métier attendue (données manquantes/incohérentes) -> 422
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ], 422);
    } catch (\Throwable $e) {
        // Erreur système inattendue -> 500
        report($e);
 
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors du paiement : ' . $e->getMessage(),
        ], 500);
    }
}

    public function receipt(string $transaction)
    {
        $payment = TransactionAgence::query()
            ->where('agence_id', $this->agenceId())
            ->where('type_transaction', TransactionAgence::STATUT_LOYER)
            ->findOrFail($transaction);

        if (empty($payment->numero_recu)) {
            $settings = ParametrageAgence::where('agence_id', $this->agenceId())->first();
            $payment->numero_recu = $settings
                ? $settings->getNextFactureNumber()
                : TransactionAgence::genererReferenceUnique();
            $payment->save();
        }

        $filename = 'recu-loyer-'.$payment->numero_recu.'.pdf';

        return response($this->receiptService->generate($payment), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    private function moisSuivant(int $mois, int $annee): array
    {
        if ($mois === 12) {
            return [1, $annee + 1];
        }

        return [$mois + 1, $annee];
    }

    private function agenceId(): string
    {
        return getInfoAgent()->users->agence_id;
    }

    private function userId(): string
    {
        return getInfoAgent()->users->id_users ?? 'system';
    }


}
