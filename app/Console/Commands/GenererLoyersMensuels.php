<?php

namespace App\Console\Commands;

use App\Models\Loyer;
use App\Models\LocataireAgence;
use App\Models\ParametrageAgence;
use App\Models\Porte;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * php artisan loyer:generer --force
 * 
 * Génère automatiquement la facture de loyer (table `loyer`) du mois en cours
 * pour chaque locataire actif, en respectant le jour d'émission et le délai
 * de paiement définis par chaque agence dans `parametrages_agence`.
 *
 * Le montant du loyer est lu chaque mois sur la table `porte` (`mt_loyer`),
 * et non sur `locataire_agence`, car l'agence peut réviser le loyer d'une
 * porte à tout moment indépendamment du bail en cours.
 *
 * Hypothèse : cette commande ne génère jamais de "première facture"
 * (caution, avance, agence, pas-de-porte ne sont donc jamais ajoutés ici ;
 * ces frais d'entrée sont supposés gérés par un autre processus, à la
 * signature du bail).
 *
 * Déclenchement prévu : cron système, une fois par jour.
 * Exemple crontab (à adapter au chemin réel du projet) :
 *
 *   5 0 * * * cd /var/www/votre-projet && php artisan loyer:generer >> storage/logs/loyer_cron.log 2>&1
 *
 * La commande est idempotente : si elle est exécutée plusieurs fois le même
 * jour (ou relancée), elle ne recrée jamais deux fois la facture d'une même
 * porte/locataire pour le même mois/année.
 */
class GenererLoyersMensuels extends Command
{
    protected $signature = 'loyer:generer
                            {--force : Force la génération même si le jour du mois ne correspond pas au jour d\'émission de l\'agence}
                            {--agence= : Ne traiter qu\'une seule agence (agence_id)}';

    protected $description = "Génère les factures de loyer du mois en cours pour les locataires actifs, selon le jour d'émission et le délai de paiement de chaque agence";

    public function handle(): int
    {
        $today = Carbon::today();
        $mois = (int) $today->month;
        $annee = (int) $today->year;

        $this->info("Lancement de la génération des loyers - {$today->toDateString()}");

        $query = ParametrageAgence::query();

        if ($this->option('agence')) {
            $query->where('agence_id', $this->option('agence'));
        }

        $agencesParam = $query->get();

        if ($agencesParam->isEmpty()) {
            $this->warn('Aucun paramétrage agence trouvé.');
            return self::SUCCESS;
        }

        foreach ($agencesParam as $param) {
            $jourEmission = (int) $param->jour_emission;

            if (!$this->option('force') && $jourEmission !== $today->day) {
                // Ce n'est pas le jour d'émission de cette agence, on passe
                continue;
            }

            $this->genererPourAgence($param, $mois, $annee);
        }

        $this->info('Génération des loyers terminée.');
        return self::SUCCESS;
    }

    protected function genererPourAgence(ParametrageAgence $param, int $mois, int $annee): void
    {
        $jourEmission = (int) ($param->jour_emission ?? 1);
        $delaiPaiement = (int) ($param->delai_paiement ?? 10);

        // Date limite de paiement = jour d'émission du mois facturé + délai de paiement (en jours)
        // Ex: jour_emission = 10, mois = 07, annee = 2026, delai_paiement = 0 => 10-07-2026 23:59:59
        try {
            $dateEmission = Carbon::create($annee, $mois, $jourEmission, 23, 59, 59);
        } catch (Throwable $e) {
            // jour_emission invalide pour ce mois (ex: 31 pour février) -> on se cale sur le dernier jour du mois
            $dateEmission = Carbon::create($annee, $mois, 1)->endOfMonth();
        }

        $dateLimitePaiement = Carbon::create($annee, $mois, $delaiPaiement, 23, 59, 59);

        // On charge la relation `porte` en une seule fois pour éviter le N+1
        $locatairesActifs = LocataireAgence::WithDefaultRelations()
            ->where('agence_id', $param->agence_id)
            ->where('is_active', 1)
            ->get();

        $this->line("Agence {$param->agence_id} : {$locatairesActifs->count()} locataire(s) actif(s) à traiter.");

        foreach ($locatairesActifs as $la) {
            try {
                $this->genererPourLocataire($la, $param, $mois, $annee, $dateLimitePaiement);
            } catch (Throwable $e) {
                Log::error("Erreur génération loyer locataire_id={$la->locataire_id} agence_id={$param->agence_id}: " . $e->getMessage());
                $this->error("Erreur pour le locataire {$la->locataire_id} : {$e->getMessage()}");
            }
        }
    }

    protected function genererPourLocataire(
        LocataireAgence $la,
        ParametrageAgence $param,
        int $mois,
        int $annee,
        Carbon $dateLimitePaiement
    ): void {
        // La porte est la source de vérité pour le montant du loyer.
        $porte = $la->porte;

        if (!$porte) {
            $this->warn("  -> Aucune porte trouvée pour locataire_agence_id={$la->locataire_agence_id}, ignoré.");
            return;
        }

        if (!$porte->is_actif) {
            $this->warn("  -> Porte {$porte->porte_id} inactive, aucune facture générée.");
            return;
        }

        DB::transaction(function () use ($la, $param, $mois, $annee, $dateLimitePaiement, $porte) {

            // 1. Vérifier qu'aucune facture n'existe déjà pour cette porte/locataire ce mois-ci
            $dejaGenere = Loyer::where('locataire_id', $la->locataire_id)
                ->where('porte_id', $la->porte_id)
                ->where('mois_paiement', $mois)
                ->where('annee_paiement', $annee)
                ->lockForUpdate()
                ->exists();

            if ($dejaGenere) {
                $this->line("  -> Déjà généré pour locataire {$la->locataire_id} / porte {$la->porte_id} ({$mois}/{$annee}), ignoré.");
                return;
            }

            // 2. Facture précédente (pour arriéré, pénalité, cumuls) — on ne s'en sert
            //    jamais pour déterminer une "première facture" : cette commande ne
            //    génère que des factures récurrentes.
            $facturePrecedente = Loyer::where('locataire_id', $la->locataire_id)
                ->where('porte_id', $la->porte_id)
                ->where('agence_id', $la->agence_id)
                ->orderByDesc('annee_paiement')
                ->orderByDesc('mois_paiement')
                ->first();

            // 3. Montant à payer = loyer courant de la porte (relu chaque mois,
            //    pour refléter une éventuelle révision du loyer par l'agence)
            $montantAPayer = $this->calculerMontantAPayer($porte);

            // 4. Répartition propriétaire / agence (commission)
            [$montantProprio, $montantAgence] = $this->calculerRepartition($montantAPayer, $param);

            // 5. Arriéré précédent = montant_restant de la facture précédente (si non soldée)
            $arrierePrecedent = 0;
            if ($facturePrecedente && (float) $facturePrecedente->montant_restant > 0) {
                $arrierePrecedent = (float) $facturePrecedente->montant_restant;
            }

            // 6. Pénalité de retard sur la facture précédente si échéance dépassée et non soldée
            $montantPenalite = $this->calculerPenalite($facturePrecedente, $param);

            // 7. Montants globaux cumulés (propriétaire / agence) sur toute la durée du bail
            $montantGlobalProprioPrecedent = (float) ($facturePrecedente->montant_global_proprio ?? 0);
            $montantGlobalAgencePrecedent = (float) ($facturePrecedente->montant_global_agence ?? 0);

            $montantRestant = $montantAPayer + $arrierePrecedent + $montantPenalite;

            Loyer::create([
                'locataire_id' => $la->locataire_id,
                'proprietaire_id' => $la->proprietaire_id,
                'lot_id' => $la->lot_id,
                'agence_id' => $la->agence_id,
                'propriete_id' => $la->propriete_id,
                'batiment_id' => $la->batiment_id,
                'porte_id' => $la->porte_id,
                'statut' => Loyer::STATUT_EN_COURS,
                'montant_a_payer' => $montantAPayer,
                'montant_payer' => 0,
                'montant_restant' => $montantRestant,
                'montant_proprio' => 0,
                'montant_agence' => 0,
                'montant_global_proprio' => 0,
                'montant_global_agence' => 0,
                'arriere_precedent' => $arrierePrecedent,
                'montant_penalite' => 0,
                'is_first' => false,
                'mode_paiement_id' => null,
                'mois_paiement' => $mois,
                'annee_paiement' => $annee,
                'date_limit_paiement' => $dateLimitePaiement,
                'created_by' => 'system',
            ]);

            $this->info("  -> Loyer généré pour locataire {$la->locataire_id} / porte {$la->porte_id}, montant : {$montantAPayer}");
        });
    }

    /**
     * Calcule le montant du loyer mensuel à partir de la porte occupée.
     * `mt_loyer` est relu à chaque génération pour refléter une éventuelle
     * révision du loyer décidée par l'agence, même si le locataire reste
     * le même. `mt_autre_frais` est considéré comme une charge récurrente
     * (ex: entretien, ordures...) et ajouté chaque mois.
     *
     * Les frais d'entrée (caution, avance, agence) présents sur `porte` ne
     * sont volontairement PAS ajoutés ici : ils ne concernent que la
     * première facture, hors périmètre de cette commande.
     */
    protected function calculerMontantAPayer(Porte $porte): float
    {
        $montant = (float) $porte->mt_loyer;

        if ($porte->mt_autre_frais) {
            $montant += (float) $porte->mt_autre_frais;
        }

        return round($montant, 2);
    }

    /**
     * Répartit le montant entre propriétaire et agence selon le taux de
     * commission défini par l'agence (`commission`, `base_commission`).
     */
    protected function calculerRepartition(float $montantAPayer, ParametrageAgence $param): array
    {
        $tauxCommission = (float) ($param->commission ?? 0);
        $baseCommission = $montantAPayer; // à adapter si base_commission désigne un montant fixe et non un %

        $montantAgence = round($baseCommission * ($tauxCommission / 100), 2);
        $montantProprio = round($montantAPayer - $montantAgence, 2);

        return [$montantProprio, $montantAgence];
    }

    /**
     * Calcule la pénalité de retard applicable si la facture précédente
     * est toujours impayée après sa date limite de paiement.
     */
    protected function calculerPenalite(?Loyer $facturePrecedente, ParametrageAgence $param): float
    {
        if (!$facturePrecedente) {
            return 0;
        }

        $encoreDu = (float) $facturePrecedente->montant_restant > 0;
        $echeanceDepassee = $facturePrecedente->date_limit_paiement
            && Carbon::parse($facturePrecedente->date_limit_paiement)->isPast();

        if (!$encoreDu || !$echeanceDepassee) {
            return 0;
        }

        $tauxPenalite = (float) ($param->penalite_retard ?? 0);

        return round((float) $facturePrecedente->montant_restant * ($tauxPenalite / 100), 2);
    }
}