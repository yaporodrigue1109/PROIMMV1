<?php

namespace App\Services\Agence;

use App\Models\Reversement;
use App\Models\ReversementDetail;
use App\Models\ProprietaireLot;
use App\Models\TransactionAgence;
use App\Models\Loyer;
use App\Models\LocataireAgence;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReversementService
{
    /**
     * Calculer les données de reversement pour un lot sur une période donnée
     */
    public function calculateReversementData(string $lotId, string $dateDebut, string $dateFin): array
    {
        // 1. Récupérer le lot avec toutes ses relations
        $lot = ProprietaireLot::with([
            'proprietaire',
            'proprietes.batiment.portes.locataireAgence.locataire',
            'proprietes.batiment.portes.tarifs' => function($query) {
                $query->where('is_actif', 1)->latest('date_effet');
            }
        ])->findOrFail($lotId);

        // 2. Récupérer tous les locataires actifs du lot
        $locatairesActifs = $this->getActiveLocataires($lot);
        
        // 3. Récupérer les transactions de la période
        $transactions = $this->getTransactions($lotId, $dateDebut, $dateFin);
        
        // 4. Calculer les totaux
        $totalAttendu = 0;
        $totalEncaisse = 0;
        $totalLoyerPaye = 0;
        $totalArrierePaye = 0;
        $details = [];

        foreach ($locatairesActifs as $locataireAgence) {
            // Montant du loyer
            $montantLoyer = $this->getMontantLoyer($locataireAgence);
            
            // Arriérés initiaux
            $arrieresInit = $this->calculateArrieres($locataireAgence, $dateDebut);
            
            // Paiements effectués sur la période
            $paiements = $this->getPaiements($locataireAgence, $transactions);
            
            $loyerPaye = $paiements['loyer_paye'];
            $arrierePaye = $paiements['arriere_paye'];
            
            $montantAttendu = $montantLoyer + $arrieresInit;
            $totalPaye = $loyerPaye + $arrierePaye;
            
            $totalAttendu += $montantAttendu;
            $totalEncaisse += $totalPaye;
            $totalLoyerPaye += $loyerPaye;
            $totalArrierePaye += $arrierePaye;
            
            $details[] = [
                'locataire_id' => $locataireAgence->locataire_id,
                'porte_id' => $locataireAgence->porte_id,
                'agence_id' => $locataireAgence->agence_id,
                'proprietaire_id' => $lot->proprietaire_id,
                'lot_id' => $lotId,
                'propriete_id' => $locataireAgence->propriete_id,
                'batiment_id' => $locataireAgence->batiment_id,
                'montant_loyer' => $montantLoyer,
                'arrieres_init' => $arrieresInit,
                'montant_attendu' => $montantAttendu,
                'loyer_paye' => $loyerPaye,
                'arriere_paye' => $arrierePaye,
                'total_paye' => $totalPaye,
                'impayes' => $montantAttendu - $totalPaye,
            ];
        }

        return [
            'total_attendu' => $totalAttendu,
            'total_encaisse' => $totalEncaisse,
            'total_restant' => $totalAttendu - $totalEncaisse,
            'total_loyer_paye' => $totalLoyerPaye,
            'total_arriere_paye' => $totalArrierePaye,
            'details' => $details,
            'lot' => $lot,
        ];
    }

    /**
     * Créer un reversement complet
     */
    public function createReversement(array $data): array
    {
        return DB::transaction(function () use ($data) {
            // 1. Calculer les données
            $calculated = $this->calculateReversementData(
                $data['lot_id'],
                $data['periode_debut'],
                $data['periode_fin']
            );

            // 2. Créer le reversement
            $reversementData = [
                'lot_id' => $data['lot_id'],
                'proprietaire_id' => $data['proprietaire_id'],
                'agence_id' => $data['agence_id'],
                'periode_debut' => $data['periode_debut'],
                'periode_fin' => $data['periode_fin'],
                'total_attendu' => $calculated['total_attendu'],
                'total_encaisse' => $calculated['total_encaisse'],
                'total_restant' => $calculated['total_restant'],
                'total_loyer_paye' => $calculated['total_loyer_paye'],
                'total_arriere_paye' => $calculated['total_arriere_paye'],
                'taux_commission' => $data['taux_commission'] ?? 10.00,
                'nouvelle_caution' => $data['nouvelle_caution'] ?? 0,
                'depenses_effectuees' => $data['depenses_effectuees'] ?? 0,
                'observation' => $data['observation'] ?? null,
                'created_by' => $data['created_by'] ?? null,
            ];

            $reversement = Reversement::create($reversementData);
            
            // Calculer la commission
            $reversement->calculerCommission()->save();

            // 3. Créer les détails
            $details = [];
            foreach ($calculated['details'] as $detailData) {
                $detailData['id_reversement_detail'] = ReversementDetail::generateId();
                $detailData['reversement_id'] = $reversement->id_reversement;
                
                $detail = ReversementDetail::create($detailData);
                $detail->calculerTotaux()->save();
                $details[] = $detail;
            }

            // 4. Mettre à jour les totaux
            $reversement->updateTotalsFromDetails()->save();

            return [
                'reversement' => $reversement->fresh(['proprietaire', 'lot', 'agence']),
                'details' => $details,
            ];
        });
    }

    /**
     * Valider un reversement
     */
    public function validerReversement(string $reversementId, array $data): Reversement
    {
        return DB::transaction(function () use ($reversementId, $data) {
            $reversement = Reversement::with(['details', 'lot'])->findOrFail($reversementId);

            // Vérifier que tous les paiements sont complets
            if ($reversement->total_restant > 0) {
                throw new \Exception('Impossible de valider : il reste des impayés.');
            }

            // Valider le reversement
            $reversement->valider(
                $data['mode_paiement'],
                $data['reference_paiement'] ?? null,
                $data['signe_par'] ?? null
            );
            $reversement->save();

            // Marquer les transactions comme reversées
            $this->markTransactionsAsReversed($reversement);

            // Mettre à jour le statut du lot
            $this->updateLotStatut($reversement->lot_id, 'reverse');

            return $reversement->fresh();
        });
    }

    /**
     * Annuler un reversement
     */
    public function annulerReversement(string $reversementId, string $motif): Reversement
    {
        return DB::transaction(function () use ($reversementId, $motif) {
            $reversement = Reversement::findOrFail($reversementId);
            
            $reversement->annuler($motif);
            $reversement->save();

            // Remettre le statut du lot en attente
            $this->updateLotStatut($reversement->lot_id, 'en_attente');

            return $reversement->fresh();
        });
    }

    /**
     * Récupérer les reversements d'une agence
     */
    public function getReversements(string $agenceId, array $filters = []): Collection
    {
        $query = Reversement::withDefaultRelations()
            ->where('agence_id', $agenceId);

        if (!empty($filters['proprietaire_id'])) {
            $query->where('proprietaire_id', $filters['proprietaire_id']);
        }

        if (!empty($filters['statut'])) {
            $query->where('statut', $filters['statut']);
        }

        if (!empty($filters['date_debut'])) {
            $query->where('periode_debut', '>=', $filters['date_debut']);
        }

        if (!empty($filters['date_fin'])) {
            $query->where('periode_fin', '<=', $filters['date_fin']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('id_reversement', 'LIKE', "%{$search}%")
                  ->orWhereHas('proprietaire', function($sq) use ($search) {
                      $sq->where('name', 'LIKE', "%{$search}%");
                  })
                  ->orWhereHas('lot', function($sq) use ($search) {
                      $sq->where('name', 'LIKE', "%{$search}%");
                  });
            });
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Récupérer un reversement avec ses détails
     */
    public function getReversement(string $reversementId): ?Reversement
    {
        return Reversement::withDefaultRelations()
            ->with(['details' => function($query) {
                $query->with(['locataire', 'porte', 'propriete', 'batiment']);
            }])
            ->find($reversementId);
    }

    /**
     * Récupérer les statistiques des reversements
     */
    public function getStatistics(string $agenceId): array
    {
        $query = Reversement::where('agence_id', $agenceId);

        return [
            'total_reversements' => $query->count(),
            'en_attente' => (clone $query)->where('statut', 'en_attente')->count(),
            'reverses' => (clone $query)->where('statut', 'reverse')->count(),
            'annules' => (clone $query)->where('statut', 'annule')->count(),
            'total_attendu' => (clone $query)->sum('total_attendu') ?? 0,
            'total_encaisse' => (clone $query)->sum('total_encaisse') ?? 0,
            'total_restant' => (clone $query)->sum('total_restant') ?? 0,
            'total_net_a_reverser' => (clone $query)->sum('net_a_reverser') ?? 0,
        ];
    }

    // ============================================================
    // MÉTHODES PRIVÉES
    // ============================================================

    /**
     * Récupérer les locataires actifs d'un lot
     */
    protected function getActiveLocataires(ProprietaireLot $lot): Collection
    {
        $locataires = collect();

        foreach ($lot->proprietes as $propriete) {
            foreach ($propriete->batiment as $batiment) {
                foreach ($batiment->portes as $porte) {
                    if ($porte->locataireAgence && $porte->locataireAgence->is_active) {
                        $locataires->push($porte->locataireAgence);
                    }
                }
            }
        }

        return $locataires;
    }

    /**
     * Récupérer le montant du loyer d'un locataire
     */
    protected function getMontantLoyer(LocataireAgence $locataireAgence): int
    {
        // Priorité: tarif de la porte > loyer_net du locataire_agence
        $porte = $locataireAgence->porte;
        if ($porte && $porte->tarifs->isNotEmpty()) {
            $tarif = $porte->tarifs->first();
            return (int) $tarif->mt_loyer;
        }

        return (int) ($locataireAgence->loyer_net ?? 0);
    }

    /**
     * Calculer les arriérés d'un locataire
     */
    protected function calculateArrieres(LocataireAgence $locataireAgence, string $dateDebut): int
    {
        // Récupérer les loyers impayés des mois précédents
        $loyersImpayes = Loyer::where('locataire_id', $locataireAgence->locataire_id)
            ->where('statut', '!=', 'Paiement total')
            ->where('date_limit_paiement', '<', $dateDebut)
            ->sum('montant_restant');

        return (int) $loyersImpayes;
    }

    /**
     * Récupérer les transactions d'un lot sur une période
     */
    protected function getTransactions(string $lotId, string $dateDebut, string $dateFin): Collection
    {
        return TransactionAgence::where('lot_id', $lotId)
            ->whereBetween('date_transaction', [$dateDebut . ' 00:00:00', $dateFin . ' 23:59:59'])
            ->where('type_transaction', 'loyer')
            ->get();
    }

    /**
     * Récupérer les paiements d'un locataire
     */
    protected function getPaiements(LocataireAgence $locataireAgence, Collection $transactions): array
    {
        $locataireTransactions = $transactions->where('locataire_id', $locataireAgence->locataire_id);

        $loyerPaye = $locataireTransactions->sum('montant_loyer_payer');
        $arrierePaye = $locataireTransactions->sum('montant_arriere_payer');

        return [
            'loyer_paye' => (int) $loyerPaye,
            'arriere_paye' => (int) $arrierePaye,
        ];
    }

    /**
     * Marquer les transactions comme reversées
     */
    protected function markTransactionsAsReversed(Reversement $reversement): void
    {
        $locataireIds = $reversement->details->pluck('locataire_id')->toArray();
        
        TransactionAgence::where('agence_id', $reversement->agence_id)
            ->whereIn('locataire_id', $locataireIds)
            ->whereBetween('date_transaction', [
                $reversement->periode_debut->startOfDay(),
                $reversement->periode_fin->endOfDay()
            ])
            ->update(['is_reversement' => 1]);
    }

    /**
     * Mettre à jour le statut d'un lot
     */
    protected function updateLotStatut(string $lotId, string $statut): void
    {
        $lot = ProprietaireLot::find($lotId);
        if ($lot) {
            $lot->statut = $statut;
            $lot->save();
        }
    }
}