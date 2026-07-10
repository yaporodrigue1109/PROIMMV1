<?php

namespace App\Jobs;

use App\Models\Agence;
use App\Models\Loyer;
use App\Models\LocataireAgence;
use App\Models\ParametrageAgence;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GenerateLoyerMensuel implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public readonly ?Carbon $targetDate = null,
        public readonly ?string $agenceId   = null
    ) {}

    // =========================================================================
    // HANDLE
    // =========================================================================

    public function handle(): void
    {
        $target = ($this->targetDate ?? now())->startOfMonth();

        Log::info("[GenerateLoyerMensuel] Démarrage génération pour {$target->format('d/m/Y')}");

        $agencesQuery = Agence::query()
            ->withDefaultRelations()
            ->where('statut', 'active');

        if ($this->agenceId) {
            $agencesQuery->where('agence_id', $this->agenceId);
        }

        $agences   = $agencesQuery->get();
        $generated = 0;
        $skipped   = 0;

        foreach ($agences as $agence) {
            $parametrage = $agence->parametrage;

            if (!$parametrage) {
                Log::warning("[GenerateLoyerMensuel] Agence #{$agence->agence_id} sans paramétrage — ignorée.");
                continue;
            }

            $bails = LocataireAgence::query()
                ->withDefaultRelations()
                ->where('is_active', true)
                ->where('agence_id', $agence->agence_id)
                ->get();

            foreach ($bails as $bail) {
                try {
                   // dd($bail);
                    $result = $this->processLocataire($bail, $target, $parametrage);
                    $result ? $generated++ : $skipped++;
                } catch (\Throwable $e) {
                    Log::error("[GenerateLoyerMensuel] Erreur locataire #{$bail->locataire_id} agence #{$agence->agence_id} : {$e->getMessage()}");
                }
            }
        }

        Log::info("[GenerateLoyerMensuel] Terminé — Générés: {$generated} | Ignorés: {$skipped}");
    }

    // =========================================================================
    // TRAITEMENT D'UN LOCATAIRE
    // =========================================================================

    private function processLocataire(
        LocataireAgence   $bail,
        Carbon            $target,
        ParametrageAgence $param
    ): bool {

        // 1. Éligibilité (date_entree + avance + période de facturation)
        if (!$this->estEligible($bail, $target, $param)) {
            Log::debug("[GenerateLoyerMensuel] Locataire #{$bail->locataire_id} non éligible pour {$target->format('m/Y')}");
            return false;
        }

        // 2. Anti-doublon : facture déjà générée ce mois pour ce locataire / porte ?
        $exists = Loyer::where('locataire_id',   $bail->locataire_id)
            ->where('porte_id',                  $bail->porte_id)
            ->where('propriete_id',              $bail->propriete_id)
            ->where('batiment_id',               $bail->batiment_id)
            ->where('proprietaire_id',           $bail->proprietaire_id)
            ->where('mois_paiement',             $target->month)
            ->where('annee_paiement',            $target->year)
            ->exists();

        if ($exists) {
            Log::debug("[GenerateLoyerMensuel] Doublon — Locataire #{$bail->locataire_id} / {$target->format('m/Y')}");
            return false;
        }

        // 3. Calcul des montants avec les paramètres de l'agence
        $montants = $this->calculerMontants($bail, $param);

        // 4. Arrière cumulé : somme de tous les mois antérieurs non soldés
        $arrierePrecedent = $this->getArrierePrecedent(
            $bail->locataire_id,
            $bail->porte_id,
            $bail->proprietaire_id,
            $target
        );



        // 5. Date limite selon la période de facturation
        $dateLimite = $this->calculerDateLimite($target, $param);

        // 6. Création en transaction
        DB::transaction(function () use ($bail, $target, $montants, $arrierePrecedent, $dateLimite) {
         $loyer=    Loyer::create([
                'locataire_id'           => $bail->locataire_id,
                'proprietaire_id'        => $bail->proprietaire_id,
                'lot_id'                 => $bail->lot_id,
                'agence_id'              => $bail->agence_id,
                'propriete_id'           => $bail->propriete_id,
                'batiment_id'            => $bail->batiment_id,
                'porte_id'               => $bail->porte_id,
                'statut'                 => 'Paiement en cours',
                'montant_a_payer'        => $montants['montant_a_payer'],
                'montant_payer'          => 0,
                'montant_restant'        => $montants['montant_a_payer'],
                'montant_proprio'        => 0,
                'montant_agence'         => 0,
                'montant_global_proprio' => 0,
                'montant_global_agence'  => 0,
                'montant_penalite'       => 0,
                'arriere_precedent'      => $arrierePrecedent,
                'is_first'               => false,
                'mois_paiement'          => $target->month,
                'annee_paiement'         => $target->year,
                'date_limit_paiement'    => $dateLimite,
                'creaeted_by'            => 'system',
            ]);
           // dd($loyer);
        });

        Log::info("[GenerateLoyerMensuel] ✓ Locataire #{$bail->locataire_id} / Porte #{$bail->porte_id} / {$target->format('m/Y')} → limite: {$dateLimite->format('d/m/Y H:i:s')}");

        return true;
    }

    // =========================================================================
    // ÉLIGIBILITÉ
    // =========================================================================

    /**
     * Vérifie si ce locataire doit recevoir une facture au mois/semaine cible.
     *
     * Logique :
     *   - depart = date_entree + nbre_avance (mois)
     *   - target doit être >= depart
     *   - ET target doit tomber sur un cycle de facturation (multiple du pas)
     *
     * Exemples (date_entree=01/04/2026, avance=2 → depart=01/06/2026) :
     *   mensuelle     → juin, juil, août…         (pas = 1 mois)
     *   bimestrielle  → juin, août, oct…           (pas = 2 mois)
     *   trimestrielle → juin, sept, déc…           (pas = 3 mois)
     *   semestrielle  → juin 2026, déc 2026…       (pas = 6 mois)
     *   annuelle      → juin 2026, juin 2027…      (pas = 12 mois)
     *   hebdomadaire  → chaque semaine (pas en mois)
     */
    private function estEligible(LocataireAgence $bail, Carbon $target, ParametrageAgence $param): bool
    {
        $dateEntree = Carbon::parse($bail->date_entree)->startOfMonth();
        $nbreAvance = (int) ($bail->porte?->avance ?? 0);

        $depart     = $dateEntree->copy()->addMonths($nbreAvance)->startOfMonth();

        $periode    = strtolower(trim($param->periode_facturation ?? 'mensuelle'));

        // Trop tôt — avance pas encore écoulée
        if ($target->lt($depart)) {
            return false;
        }

        // Hebdomadaire / deux semaines : pas de filtre par mois,
        // le scheduler dailyAt gère la fréquence
        if (in_array($periode, ['hebdomadaire', 'une semaine', 'deux semaines'])) {
            return true;
        }

        // Pour les autres : vérifier que target est bien sur un cycle
        $diffMois = $depart->diffInMonths($target->copy()->startOfMonth());

        $pasEnMois = match ($periode) {
            'mensuelle', 'mensuel'         => 1,
            'bimestrielle', 'bimestriel'   => 2,
            'trimestrielle', 'trimestriel' => 3,
            'semestrielle', 'semestriel'   => 6,
            'annuelle', 'annuel'           => 12,
            default                        => 1,
        };
      //  dd($diffMois % $pasEnMois);
        return $diffMois % $pasEnMois === 0;
    }

    // =========================================================================
    // DATE LIMITE
    // =========================================================================

    /**
     * Date limite = fin de la période de facturation + delai_paiement jours, à 23:59:59.
     *
     * Exemples (delai_paiement=10) :
     *   mensuelle     / juin → 30/06 + 10j = 10/07 23:59:59
     *   trimestrielle / juin → 31/08 + 10j = 10/09 23:59:59
     *   hebdomadaire  / 01/06 → 07/06 + 10j = 17/06 23:59:59
     *   annuelle      / juin 2026 → 31/05/2027 + 10j = 10/06/2027 23:59:59
     */
    private function calculerDateLimite(Carbon $target, ParametrageAgence $param): Carbon
    {
        $delaiPaiement = max(0, (int) ($param->delai_paiement ?? 10));
        $periode       = strtolower(trim($param->periode_facturation ?? 'mensuelle'));

        $finPeriode = match ($periode) {
            'hebdomadaire', 'une semaine'  => $target->copy()->addDays(6),
            'deux semaines'                => $target->copy()->addDays(13),
            'mensuelle', 'mensuel'         => $target->copy()->addDays(0)->startOfMonth(),
            'bimestrielle', 'bimestriel'   => $target->copy()->addMonths(2)->subDay(),
            'trimestrielle', 'trimestriel' => $target->copy()->addMonths(3)->subDay(),
            'semestrielle', 'semestriel'   => $target->copy()->addMonths(6)->subDay(),
            'annuelle', 'annuel'           => $target->copy()->addYear()->subDay(),
            default                        => $target->copy()->startOfMonth(),
        };

        return $finPeriode->addDays($delaiPaiement)->endOfDay(); // 23:59:59
    }

    // =========================================================================
    // CALCUL DES MONTANTS
    // =========================================================================

    /**
     * Calcule tous les montants à partir du loyer brut de la porte
     * et des taux configurés dans le paramétrage de l'agence.
     */
    private function calculerMontants(LocataireAgence $bail, ParametrageAgence $param): array
    {
        $loyerBrut = (float) ($bail->porte?->mt_loyer ?? 0);
        $tauxComm  = (float) ($param->commission     ?? 10);
        $baseComm  = $param->base_commission          ?? 'ttc';
        $tauxTva   = (float) ($param->tva             ?? 0);
        $tauxAib   = (float) ($param->aib             ?? 0);
        $tauxRas   = (float) ($param->ras             ?? 0);

        $baseCalcul = match ($baseComm) {
            'loyer_net' => $loyerBrut / (1 + $tauxTva / 100),
            default     => $loyerBrut,
        };

        $commissionAgence = round($baseCalcul * $tauxComm / 100, 2);
        $tva              = round($commissionAgence * $tauxTva / 100, 2);
        $aib              = round($loyerBrut * $tauxAib / 100, 2);
        $ras              = round($commissionAgence * $tauxRas / 100, 2);

        $montantAgence  = round($commissionAgence + $tva - $ras, 2);
        $montantProprio = round($loyerBrut - $commissionAgence - $aib, 2);
        $montantAPayer  = round($loyerBrut, 2);

        return [
            'montant_a_payer' => $montantAPayer,
            'montant_agence'  => $montantAgence,
            'montant_proprio' => $montantProprio,
            'commission'      => $commissionAgence,
            'tva'             => $tva,
            'aib'             => $aib,
            'ras'             => $ras,
        ];
    }

    // =========================================================================
    // ARRIÈRE PRÉCÉDENT
    // =========================================================================

    /**
     * Somme de tous les montants_restant > 0 sur les mois ANTÉRIEURS au mois cible.
     * Un locataire peut avoir des impayés sur plusieurs mois.
     */
    private function getArrierePrecedent(
        string $locataireId,
        string $porteId,
        string $proprietaireId,
        Carbon $target
    ): float {
        return (float) getArrierePrecedentLocataire($locataireId,$porteId,$proprietaireId,$target);
    }
}