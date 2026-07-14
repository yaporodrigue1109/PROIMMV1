<?php

namespace App\Services\Agence;

use App\Exceptions\PaiementLoyerException;
use App\Models\Loyer;
use App\Models\LocataireAgence;
use App\Models\ParametrageAgence;
use App\Models\TransactionAgence;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

/**
 * Gère le paiement d'un locataire pour une porte donnée : c'est la SEULE
 * source de vérité pour l'encaissement d'un loyer (à appeler depuis le
 * contrôleur utilisé par l'interface caissier).
 *
 * Le montant versé est imputé, dans l'ordre chronologique croissant
 * (du mois le plus ancien au plus récent) :
 *   1. sur les factures déjà générées et non soldées (arriérés + mois en cours)
 *   2. puis, s'il reste de l'argent, sur les mois futurs, en créant les
 *      factures manquantes au fur et à mesure (paiement par anticipation)
 *
 * Le dernier mois couvert peut être soldé partiellement si le montant versé
 * ne suffit pas à le couvrir entièrement.
 *
 * En plus des lignes `loyer`, chaque paiement génère :
 *   - une ligne `transaction_agences` (récapitulatif comptable du versement)
 *   - un mouvement de caisse + incrément du solde de la caisse active
 *
 * Exemple : loyer = 50 000, montant versé = 320 000, aucune facture payée
 * depuis avril (avril, mai, juin, juillet dues, août non générée) :
 *   avril: 50 000 (soldé) | mai: 50 000 (soldé) | juin: 50 000 (soldé)
 *   juillet: 50 000 (soldé) | août: 50 000 (soldé, créée) | septembre: 20 000 (partiel, créée)
 */
class PaiementLoyerService
{
    private const MOIS_FR = [
        1 => 'janvier', 2 => 'février', 3 => 'mars', 4 => 'avril',
        5 => 'mai', 6 => 'juin', 7 => 'juillet', 8 => 'août',
        9 => 'septembre', 10 => 'octobre', 11 => 'novembre', 12 => 'décembre',
    ];

    public function __construct(
        protected LoyerCalculateur $calculateur = new LoyerCalculateur()
    ) {
    }

    /**
     * @param string      $agenceId          Agence du caissier connecté (scope multi-tenant)
     * @param string      $locataireId       Locataire concerné
     * @param string      $porteId           Porte concernée
     * @param float       $montantVerse      Montant total versé par le locataire
     * @param int         $modePaiementId
     * @param string|null $commentaire
     * @param mixed       $utilisateurId     Identifiant du caissier (created_by / updated_by)
     *
     * @return array{
     *     reference: string,
     *     montant_verse: float,
     *     commission: float,
     *     montant_proprietaire: float,
     *     arriere_actuel: float,
     *     montant_arriere_paye: float,
     *     montant_loyer_paye: float,
     *     montant_avance_paye: float,
     *     mois_regles: array<int, array{mois: string, montant_applique: float, solde: bool}>
     * }
     *
     * @throws PaiementLoyerException si les données métier sont invalides (montant <= 0, bail introuvable, ...)
     */
    public function payer(
        string $agenceId,
        string $locataireId,
        string $porteId,
        float $montantVerse,
        int $modePaiementId,
        ?string $commentaire,
        mixed $utilisateurId
    ): array {
        if ($montantVerse <= 0) {
            throw new PaiementLoyerException('Le montant versé doit être strictement positif.');
        }

        if (! Schema::hasTable('loyer')) {
            throw new PaiementLoyerException('Table loyer introuvable. Lancez les migrations avant de payer.');
        }

        return DB::transaction(function () use (
            $agenceId,
            $locataireId,
            $porteId,
            $montantVerse,
            $modePaiementId,
            $commentaire,
            $utilisateurId
        ) {
            $params = ParametrageAgence::where('agence_id', $agenceId)->first();

            if (! $params) {
                throw new PaiementLoyerException('Paramètres agence introuvables.');
            }

            $bail = LocataireAgence::with('porte')
                ->where('locataire_id', $locataireId)
                ->where('porte_id', $porteId)
                ->where('agence_id', $agenceId)
                ->where('is_active', 1)
                ->first();

            if (! $bail || ! $bail->porte) {
                throw new PaiementLoyerException('Bail introuvable.');
            }

            $now = Carbon::now();
            $dateTransaction = $now->toDateString();
            $moisCourant = (int) $now->format('n');
            $anneeCourante = (int) $now->format('Y');

            $montantDisponible = $montantVerse;
            $moisRegles = [];
            $totalLoyerPaye = 0.0;
            $totalArrierePaye = 0.0;
            $arriereActuel = 0.0;

            // 1. Régler les factures existantes non soldées, du plus ancien au plus récent
            $facturesImpayees = Loyer::where('locataire_id', $bail->locataire_id)
                ->where('agence_id', $agenceId)
                ->where('porte_id', $porteId)
                ->where('montant_restant', '>', 0)
                ->orderBy('annee_paiement')
                ->orderBy('mois_paiement')
                ->lockForUpdate()
                ->get();

            foreach ($facturesImpayees as $facture) {
                if ($montantDisponible <= 0) {
                    break;
                }

                $du = (float) $facture->montant_restant;
                $montantAAppliquer = min($montantDisponible, $du);
                $nouveauRestant = round($du - $montantAAppliquer, 2);

                $facture->montant_payer = round((float) $facture->montant_payer + $montantAAppliquer, 2);
                $facture->montant_restant = $nouveauRestant;
                $facture->statut = $nouveauRestant <= 0 ? 'Paiement total' : 'Paiement partiel';
                $facture->date_paiement = $dateTransaction;
                $facture->mode_paiement_id = $modePaiementId;

                if ($commentaire) {
                    $facture->commentaire = $commentaire;
                }

                $facture->updated_by = $utilisateurId;
                $facture->save();

                // Ventilation par rapport au mois courant : arriéré, courant ou avance.
                $estArriere = ($facture->annee_paiement < $anneeCourante)
                    || ((int) $facture->annee_paiement === $anneeCourante && (int) $facture->mois_paiement < $moisCourant);
                $estAvance = ($facture->annee_paiement > $anneeCourante)
                    || ((int) $facture->annee_paiement === $anneeCourante && (int) $facture->mois_paiement > $moisCourant);

                if ($estArriere) {
                    $totalArrierePaye = round($totalArrierePaye + $montantAAppliquer, 2);
                } elseif ($estAvance) {
                    $totalAvancePaye = round($totalAvancePaye + $montantAAppliquer, 2);
                } else {
                    $totalLoyerPaye = round($totalLoyerPaye + $montantAAppliquer, 2);
                }

                if ($nouveauRestant > 0) {
                    $arriereActuel = $nouveauRestant;
                }

                $moisRegles[] = [
                    'mois' => $this->moisAnnee((int) $facture->mois_paiement, (int) $facture->annee_paiement),
                    'montant_applique' => $montantAAppliquer,
                    'solde' => $nouveauRestant <= 0,
                ];

                $montantDisponible = round($montantDisponible - $montantAAppliquer, 2);
            }

            $totalAvancePaye = 0.0;

            // 2. S'il reste de l'argent, créer et régler les périodes manquantes.
            if ($montantDisponible > 0) {
                $totauxPeriodesCreees = $this->payerMoisFuturs(
                    $bail,
                    $agenceId,
                    $porteId,
                    $params,
                    $montantDisponible,
                    $modePaiementId,
                    $commentaire,
                    $utilisateurId,
                    $dateTransaction,
                    $moisRegles
                );

                $totalArrierePaye = round($totalArrierePaye + $totauxPeriodesCreees['arriere'], 2);
                $totalLoyerPaye = round($totalLoyerPaye + $totauxPeriodesCreees['loyer'], 2);
                $totalAvancePaye = round($totalAvancePaye + $totauxPeriodesCreees['avance'], 2);
            }

            // 3. Commission globale de la transaction (comptabilité agence)
            $tauxCommission = (float) ($params->commission ?? 0);
            $baseCalc = $params->base_commission === 'ttc'
                ? (float) $bail->porte->mt_loyer
                : $montantVerse;
            $commAgence = round($baseCalc * $tauxCommission / 100, 2);
            $commProprietaire = round($montantVerse - $commAgence, 2);
            $moisPayes = array_values(array_unique(array_column($moisRegles, 'mois')));

            // 4. Enregistrement de la transaction agence
            $transactionId = (string) Str::uuid();

            DB::table('transaction_agences')->insert([
                'transaction_agence_id' => $transactionId,
                'locataire_id' => $bail->locataire_id,
                'agence_id' => $agenceId,
                'proprietaire_id' => $bail->proprietaire_id,
                'propriete_id' => $bail->propriete_id,
                'batiment_id' => $bail->batiment_id,
                'porte_id' => $porteId,
                'montant_global_verser' => $montantVerse,
                'mois_payer' => json_encode($moisPayes, JSON_UNESCAPED_UNICODE),
                'arriere_actuel' => $arriereActuel,
                'montant_arriere_payer' => $totalArrierePaye,
                'montant_arriere_actuel' => $arriereActuel,
                'montant_loyer_payer' => $totalLoyerPaye,
                'montant_avance_payer' => $totalAvancePaye,
                'is_first' => 0,
                'type_transaction' => TransactionAgence::STATUT_LOYER,
                'mode_paiement_id' => $modePaiementId,
                'is_reversement' => 0,
                'date_transaction' => $dateTransaction,
                'created_by' => $utilisateurId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $reference = 'TRX-LOY-' . str_pad((string) $transactionId, 4, '0', STR_PAD_LEFT);

            // 5. Mouvement de caisse
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
                    'mode_paiement_id' => $modePaiementId,
                    'reference' => $params->prefixe_facture ?? $reference,
                    'commentaire' => $commentaire,
                    'date_mouvement' => $dateTransaction,
                    'created_by' => $utilisateurId,
                    'updated_by' => $utilisateurId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                DB::table('caisses')
                    ->where('caisse_id', $caisse->caisse_id)
                    ->increment('solde', $montantVerse);
            }

            return [
                'reference' => $reference,
                'montant_verse' => $montantVerse,
                'commission' => $commAgence,
                'montant_proprietaire' => $commProprietaire,
                'arriere_actuel' => $arriereActuel,
                'montant_arriere_paye' => $totalArrierePaye,
                'montant_loyer_paye' => $totalLoyerPaye,
                'montant_avance_paye' => $totalAvancePaye,
                'mois_regles' => $moisRegles,
            ];
        });
    }

    /**
     * Crée les factures des mois futurs (à partir du mois suivant la
     * dernière facture connue) et leur impute le montant disponible,
     * jusqu'à épuisement de la somme. Alimente $moisRegles par référence
     * et retourne la ventilation arriéré / mois courant / avance.
     */
    protected function payerMoisFuturs(
        LocataireAgence $bail,
        string $agenceId,
        string $porteId,
        ParametrageAgence $params,
        float $montantDisponible,
        int $modePaiementId,
        ?string $commentaire,
        mixed $utilisateurId,
        string $dateTransaction,
        array &$moisRegles
    ): array {
        $montantMensuel = $this->calculateur->montantMensuel($bail->porte);

        if ($montantMensuel <= 0) {
            return ['arriere' => 0.0, 'loyer' => 0.0, 'avance' => 0.0];
        }

        $derniereFacture = Loyer::where('locataire_id', $bail->locataire_id)
            ->where('agence_id', $agenceId)
            ->where('porte_id', $porteId)
            ->orderByDesc('annee_paiement')
            ->orderByDesc('mois_paiement')
            ->first();

        $curseur = $derniereFacture
            ? Carbon::create((int) $derniereFacture->annee_paiement, (int) $derniereFacture->mois_paiement, 1)->addMonth()
            : Carbon::now()->startOfMonth();

        $montantGlobalProprio = (float) ($derniereFacture->montant_global_proprio ?? 0);
        $montantGlobalAgence = (float) ($derniereFacture->montant_global_agence ?? 0);
        $delaiPaiement = (int) ($params->delai_paiement ?? 1);

        $totaux = ['arriere' => 0.0, 'loyer' => 0.0, 'avance' => 0.0];
        $maintenant = Carbon::now();
        $moisCourant = (int) $maintenant->format('n');
        $anneeCourante = (int) $maintenant->format('Y');

        while ($montantDisponible > 0) {
            $mois = $curseur->month;
            $annee = $curseur->year;

            [$montantProprio, $montantAgence] = $this->calculateur->repartition($montantMensuel, $params);

            $montantAAppliquer = min($montantDisponible, $montantMensuel);
            $montantRestant = round($montantMensuel - $montantAAppliquer, 2);

            try {
                $dateLimite = Carbon::create($annee, $mois, $delaiPaiement, 23, 59, 59);
            } catch (Throwable $e) {
                $dateLimite = Carbon::create($annee, $mois, 1)->endOfMonth()->setTime(23, 59, 59);
            }

            Loyer::create([
                'locataire_id' => $bail->locataire_id,
                'proprietaire_id' => $bail->proprietaire_id,
                'lot_id' => $bail->lot_id,
                'agence_id' => $agenceId,
                'propriete_id' => $bail->propriete_id,
                'batiment_id' => $bail->batiment_id,
                'porte_id' => $porteId,
                'statut' => $montantRestant <= 0 ? 'Paiement total' : 'Paiement partiel',
                'montant_a_payer' => $montantMensuel,
                'montant_payer' => $montantAAppliquer,
                'montant_restant' => $montantRestant,
                'montant_proprio' => $montantProprio,
                'montant_agence' => $montantAgence,
                'montant_global_proprio' => $montantGlobalProprio + $montantProprio,
                'montant_global_agence' => $montantGlobalAgence + $montantAgence,
                'arriere_precedent' => 0,
                'montant_penalite' => 0,
                'is_first' => false,
                'mode_paiement_id' => $modePaiementId,
                'date_paiement' => $dateTransaction,
                'mois_paiement' => $mois,
                'annee_paiement' => $annee,
                'date_limit_paiement' => $dateLimite,
                'commentaire' => $commentaire,
                'created_by' => $utilisateurId ?? 'system',
            ]);

            $montantGlobalProprio += $montantProprio;
            $montantGlobalAgence += $montantAgence;

            $moisRegles[] = [
                'mois' => $this->moisAnnee($mois, $annee),
                'montant_applique' => $montantAAppliquer,
                'solde' => $montantRestant <= 0,
            ];

            $estArriere = $annee < $anneeCourante
                || ($annee === $anneeCourante && $mois < $moisCourant);
            $estAvance = $annee > $anneeCourante
                || ($annee === $anneeCourante && $mois > $moisCourant);
            $categorie = $estArriere ? 'arriere' : ($estAvance ? 'avance' : 'loyer');
            $totaux[$categorie] = round($totaux[$categorie] + $montantAAppliquer, 2);
            $montantDisponible = round($montantDisponible - $montantAAppliquer, 2);
            $curseur->addMonth();
        }

        return $totaux;
    }

    protected function moisAnnee(int $mois, int $annee): string
    {
        $nom = self::MOIS_FR[$mois] ?? (string) $mois;

        return ucfirst($nom) . "-{$annee}";
    }
}
