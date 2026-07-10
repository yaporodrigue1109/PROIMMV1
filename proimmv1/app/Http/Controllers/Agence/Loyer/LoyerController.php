<?php

namespace App\Http\Controllers\Agence\Loyer;

use App\Http\Controllers\Controller;
use App\Models\Locataire;
use App\Models\Loyer;
use App\Models\ModePaiement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;

class LoyerController extends Controller
{
    public function index()
    {
        $modePaiement = ModePaiement::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('Agence/Caisse/Loyer', [
            'modePaiement' => $modePaiement,
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

        $locataire = Locataire::where(function ($q) use ($query) {
            $q->where('name', 'LIKE', "%{$query}%")
                ->orWhere('tel1', 'LIKE', "%{$query}%")
                ->orWhere('tel2', 'LIKE', "%{$query}%");
        })->first();

        if (! $locataire) {
            return response()->json(['data' => null]);
        }

        $bauxActifs = $locataire->bauxActifs($agenceId)
            ->where('agence_id', $agenceId)
            ->withDefaultRelations()
            ->get();

        if ($bauxActifs->isEmpty()) {
            return response()->json(['data' => null]);
        }

        $rentals = [];

        foreach ($bauxActifs as $bail) {
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
            'Paiement total' => ['label' => 'Paye', 'class' => 'is-success'],
            'Paiement partiel' => ['label' => 'Partiel', 'class' => 'payment-status-warning'],
            'Paiement en retard' => ['label' => 'En retard', 'class' => 'is-danger'],
            'Paiement en cours' => ['label' => 'En cours', 'class' => 'payment-status-info'],
        ];

        $info = $statusMap[$row->statut] ?? ['label' => ucfirst((string) $row->statut), 'class' => ''];

        return [
            'period' => formatMoisAnnee($row->mois_paiement, $row->annee_paiement),
            'status' => $info['label'],
            'className' => $info['class'],
            'amount' => (int) ($row->montant_payer ?: $row->montant_a_payer),
        ];
    }

    public function pay(Request $request)
    {
        $request->validate([
            'locataire_agence_id' => 'required|string',
            'porte_id' => 'required|string',
            'montant' => 'required|numeric|min:1',
            'mode_paiement_id' => 'required|integer',
            'commentaire' => 'nullable|string|max:500',
        ]);

        if (! Schema::hasTable('loyer')) {
            return response()->json([
                'success' => false,
                'message' => 'Table loyer introuvable. Lancez les migrations avant de payer.',
            ], 422);
        }

        $agenceId = $this->agenceId();
        $userId = $this->userId();

        $params = DB::table('parametrages_agence')
            ->where('agence_id', $agenceId)
            ->first();

        if (! $params) {
            return response()->json(['success' => false, 'message' => 'Parametres agence introuvables.'], 422);
        }

        $tauxCommission = (float) $params->commission;
        $baseCommission = $params->base_commission;
        $penaliteRetard = (float) $params->penalite_retard;

        $bail = DB::table('locataire_agence as la')
            ->join('porte as p', 'p.porte_id', '=', 'la.porte_id')
            ->where('la.locataire_agence_id', $request->locataire_agence_id)
            ->where('la.agence_id', $agenceId)
            ->where('la.is_active', 1)
            ->select(
                'la.locataire_id',
                'la.proprietaire_id',
                'la.propriete_id',
                'la.batiment_id',
                'la.lot_id',
                'la.porte_id',
                'la.is_new',
                'p.mt_loyer as loyer_mensuel'
            )
            ->first();

        if (! $bail) {
            return response()->json(['success' => false, 'message' => 'Bail introuvable.'], 422);
        }

        $lignesImpayees = Loyer::where('locataire_id', $bail->locataire_id)
            ->where('agence_id', $agenceId)
            ->where('porte_id', $bail->porte_id)
            ->whereIn('statut', ['Paiement en retard', 'Paiement partiel'])
            ->orderBy('annee_paiement')
            ->orderBy('mois_paiement')
            ->get();

        $montantVerse = (float) $request->montant;
        $montantRestant = $montantVerse;
        $now = now();
        $dateTransaction = $now->toDateString();

        $totalLoyerPaye = 0;
        $totalArrierePaye = 0;
        $totalAvancePaye = 0;
        $arriereActuel = 0;

        DB::beginTransaction();

        try {
            foreach ($lignesImpayees as $ligne) {
                if ($montantRestant <= 0) {
                    break;
                }

                $du = (float) $ligne->montant_restant;

                if ($montantRestant >= $du) {
                    $montantRestant -= $du;
                    $totalLoyerPaye += $du;

                    Loyer::where('loyer_id', $ligne->loyer_id)
                        ->update([
                            'statut' => 'Paiement total',
                            'montant_payer' => $ligne->montant_a_payer,
                            'montant_restant' => 0,
                            'date_paiement' => $dateTransaction,
                            'mode_paiement_id' => $request->mode_paiement_id,
                            'commentaire' => $request->commentaire,
                            'updated_by' => $userId,
                            'updated_at' => $now,
                        ]);
                } else {
                    $nouveauRestant = $du - $montantRestant;
                    $totalLoyerPaye += $montantRestant;

                    DB::table('loyer')
                        ->where('loyer_id', $ligne->loyer_id)
                        ->update([
                            'statut' => 'Paiement partiel',
                            'montant_payer' => $ligne->montant_payer + $montantRestant,
                            'montant_restant' => $nouveauRestant,
                            'date_paiement' => $dateTransaction,
                            'mode_paiement_id' => $request->mode_paiement_id,
                            'commentaire' => $request->commentaire,
                            'updated_by' => $userId,
                            'updated_at' => $now,
                        ]);

                    $arriereActuel = $nouveauRestant;
                    $montantRestant = 0;
                }
            }

            if ($montantRestant > 0) {
                $dernierLoyer = DB::table('loyer')
                    ->where('locataire_id', $bail->locataire_id)
                    ->where('agence_id', $agenceId)
                    ->where('porte_id', $bail->porte_id)
                    ->orderByDesc('annee_paiement')
                    ->orderByDesc('mois_paiement')
                    ->first();

                [$moisSuivant, $anneeSuivante] = $this->moisSuivant(
                    $dernierLoyer?->mois_paiement ?? (int) $now->format('m'),
                    $dernierLoyer?->annee_paiement ?? (int) $now->format('Y')
                );

                $baseCalc = $baseCommission === 'ttc' ? (float) $bail->mt_loyer : $montantRestant;
                $commAgence = round($baseCalc * $tauxCommission / 100, 2);
                $commProprietaire = $montantRestant - $commAgence;

                DB::table('loyer')->insert([
                    'locataire_id' => $bail->locataire_id,
                    'proprietaire_id' => $bail->proprietaire_id,
                    'lot_id' => $bail->lot_id,
                    'agence_id' => $agenceId,
                    'propriete_id' => $bail->propriete_id,
                    'batiment_id' => $bail->batiment_id,
                    'porte_id' => $bail->porte_id,
                    'statut' => 'Paiement total',
                    'montant_a_payer' => $bail->loyer_mensuel,
                    'montant_payer' => $montantRestant,
                    'montant_restant' => 0,
                    'montant_proprio' => $commProprietaire,
                    'montant_agence' => $commAgence,
                    'montant_global_proprio' => $commProprietaire,
                    'montant_global_agence' => $commAgence,
                    'arriere_precedent' => 0,
                    'montant_penalite' => 0,
                    'is_first' => 0,
                    'mode_paiement_id' => $request->mode_paiement_id,
                    'date_paiement' => $dateTransaction,
                    'mois_paiement' => $moisSuivant,
                    'annee_paiement' => $anneeSuivante,
                    'date_limit_paiement' => null,
                    'commentaire' => $request->commentaire,
                    'creaeted_by' => $userId,
                    'updated_by' => $userId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $totalAvancePaye = $montantRestant;
                $montantRestant = 0;
            }

            $baseCalc = $baseCommission === 'ttc' ? (float) $bail->loyer_mensuel : $montantVerse;
            $commAgence = round($baseCalc * $tauxCommission / 100, 2);
            $commProprietaire = $montantVerse - $commAgence;

            $transactionId = DB::table('transaction_agences')->insertGetId([
                'locataire_id' => $bail->locataire_id,
                'agence_id' => $agenceId,
                'proprietaire_id' => $bail->proprietaire_id,
                'propriete_id' => $bail->propriete_id,
                'batiment_id' => $bail->batiment_id,
                'porte_id' => $bail->porte_id,
                'montant_global_verser' => $montantVerse,
                'mois_payer' => $now->format('m'),
                'arriere_actuel' => $arriereActuel,
                'montant_arriere_payer' => $totalArrierePaye,
                'montant_arriere_actuel' => $arriereActuel,
                'montant_loyer_payer' => $totalLoyerPaye,
                'montant_avance_payer' => $totalAvancePaye,
                'is_first' => $bail->is_new,
                'mode_paiement_id' => $request->mode_paiement_id,
                'is_reversement' => 0,
                'date_transaction' => $dateTransaction,
                'created_by' => $userId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $caisse = DB::table('caisses')
                ->where('agence_id', $agenceId)
                ->where('is_active', 1)
                ->first();

            if ($caisse) {
                DB::table('mouvements_caisse')->insert([
                    'caisse_id' => $caisse->caisse_id,
                    'agence_id' => $agenceId,
                    'transaction_agence_id' => $transactionId,
                    'loyer_id' => null,
                    'type' => 'entree',
                    'motif' => 'loyer',
                    'montant' => $montantVerse,
                    'mode_paiement_id' => $request->mode_paiement_id,
                    'reference' => $params->prefixe_facture ?? 'TRX-LOY-' . str_pad($transactionId, 4, '0', STR_PAD_LEFT),
                    'commentaire' => $request->commentaire,
                    'date_mouvement' => $dateTransaction,
                    'created_by' => $userId,
                    'updated_by' => $userId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                DB::table('caisses')
                    ->where('caisse_id', $caisse->caisse_id)
                    ->increment('solde', $montantVerse);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Paiement enregistre avec succes.',
                'reference' => 'TRX-LOY-' . str_pad($transactionId, 4, '0', STR_PAD_LEFT),
                'montant' => $montantVerse,
                'commission' => $commAgence,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du paiement : ' . $e->getMessage(),
            ], 500);
        }
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
