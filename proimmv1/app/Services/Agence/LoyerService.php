<?php

namespace App\Services\Agence;

use App\Jobs\GenerateLoyerMensuel;
use App\Models\Loyer;
use App\Models\LocataireAgence;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class LoyerService
{
    // =========================================================================
    // GÉNÉRATION AUTOMATIQUE (appelée par le Scheduler)
    // =========================================================================

    /**
     * Dispatch le job de génération pour le mois courant.
     * Appelé par le Scheduler chaque 1er du mois.
     */
    public function dispatchGenerationMensuelle(?string $agenceId = null): void
    {
        GenerateLoyerMensuel::dispatch(now()->startOfMonth(), $agenceId);
    }

    /**
     * Rejouer la génération pour un mois/année précis (usage admin).
     */
    public function replayMois(int $mois, int $annee, ?string $agenceId = null): void
    {
        $date = Carbon::createFromDate($annee, $mois, 1)->startOfMonth();
        GenerateLoyerMensuel::dispatch($date, $agenceId);
    }

    // =========================================================================
    // VÉRIFICATIONS
    // =========================================================================

    /**
     * Vérifie si une facture de loyer existe déjà pour un locataire / porte / mois / année.
     */
    public function factureExiste(string $locataireId, string $porteId, int $mois, int $annee): bool
    {
        return Loyer::where('locataire_id',  $locataireId)
            ->where('porte_id',              $porteId)
            ->where('mois_paiement',         $mois)
            ->where('annee_paiement',        $annee)
            ->exists();
    }

    /**
     * Calcule le mois à partir duquel la facturation doit commencer.
     *
     * Ex : date_entree = 01/06/2026, nbre_avance = 2 → première facture = 08/2026
     */
    public function calculerPremierMoisFacturation(Carbon $dateEntree, int $nbreAvance): Carbon
    {
        return $dateEntree->copy()->startOfMonth()->addMonths($nbreAvance);
    }

    /**
     * Retourne true si le mois cible est facturable pour ce bail.
     */
    public function estFacturable(LocataireAgence $bail, Carbon $targetDate): bool
    {
        $dateEntree   = Carbon::parse($bail->date_entree)->startOfMonth();
        $nbreAvance   = (int) ($bail->porte?->nbre_avance ?? 0);
        $premierMois  = $this->calculerPremierMoisFacturation($dateEntree, $nbreAvance);

        return $targetDate->startOfMonth()->gte($premierMois);
    }

    // =========================================================================
    // LECTURE
    // =========================================================================

    /**
     * Retourne les loyers d'un locataire pour une année donnée.
     */
    public function getLoyersByLocataire(string $locataireId, int $annee): Collection
    {
        return Loyer::where('locataire_id', $locataireId)
            ->where('annee_paiement', $annee)
            ->orderBy('mois_paiement')
            ->get();
    }

    /**
     * Retourne les loyers impayés (montant_restant > 0) d'une agence pour le mois courant.
     */
    public function getLoyersImpayes(string $agenceId, ?int $mois = null, ?int $annee = null): Collection
    {
        $mois  = $mois  ?? now()->month;
        $annee = $annee ?? now()->year;

        return Loyer::where('agence_id',     $agenceId)
            ->where('mois_paiement',         $mois)
            ->where('annee_paiement',        $annee)
            ->where('montant_restant',       '>', 0)
            ->with(['locataire', 'porte'])
            ->get();
    }
}