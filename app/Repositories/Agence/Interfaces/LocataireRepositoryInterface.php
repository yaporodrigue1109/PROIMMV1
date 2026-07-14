<?php

namespace App\Repositories\Agence\Interfaces;

use App\Models\Locataire;
use App\Models\LocataireAgence;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface LocataireRepositoryInterface
{
    // ─── Lecture ──────────────────────────────────────────────────────────────

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findById(string $id): ?Locataire;

    public function findByCode(string $code): ?Locataire;

    /**
     * Recherche un locataire existant par numéro de pièce ou numéro de téléphone.
     */
    public function findByPieceOrTel(string $numPiece = null, string $tel = null): ?Locataire;

    /**
     * Vérifie si un locataire est déjà rattaché à la même porte et au même propriétaire
     * au sein de l'agence courante (contrat actif ou non).
     */
    public function existeContratSurMemPorte(Locataire $locataire, string $porteId, string $proprietaireId): bool;

    public function stats(): array;

    // ─── Écriture locataire ───────────────────────────────────────────────────

    public function create(array $data): Locataire;

    public function update(Locataire $locataire, array $data): Locataire;

    public function delete(Locataire $locataire): bool;

    // ─── Enregistrement complet (règles métier) ───────────────────────────────

    /**
     * Point d'entrée principal pour l'enregistrement d'un locataire.
     *
     * Applique les règles suivantes :
     *  1. Recherche par num_piece ou tel1/tel2 → récupère ou crée le locataire.
     *  2. Vérifie doublon (même porte + même propriétaire) → exception si déjà actif.
     *  3. Crée le contrat (locataire_agence).
     *  4. Si nouveau locataire : génère les factures loyer (une par mois d'avance)
     *     + une seule transaction_agence.
     *  5. Si ancien locataire sans arriéré : ne touche pas loyer ni transaction.
     *  6. Si ancien locataire avec arriéré : crée les lignes loyer d'arriéré uniquement.
     *
     * @param  array $locataireData   Données du locataire (pièce, tel, nom…)
     * @param  array $contratData     Données du contrat (porte_id, propriete_id, date_entree…)
     * @param  array $paiementData    Informations de paiement (avances, arriérés, montants…)
     * @return array{locataire: Locataire, contrat: LocataireAgence, estNouveau: bool}
     */
    public function enregistrer(array $locataireData, array $contratData, array $paiementData): array;

    // ─── Contrat ──────────────────────────────────────────────────────────────

    public function createContrat(Locataire $locataire, array $data): LocataireAgence;

    public function resilierContrat(Locataire $locataire): bool;

    // ─── Loyers & transactions ────────────────────────────────────────────────

    /**
     * Génère une facture loyer par mois d'avance pour un nouveau locataire
     * et crée la transaction globale correspondante.
     *
     * @param  Locataire       $locataire
     * @param  LocataireAgence $contrat
     * @param  array           $paiementData  {
     *     mois_avance      : int,
     *     montant_loyer    : float,
     *     montant_agence   : float,
     *     montant_proprio  : float,
     *     date_debut       : string (Y-m-d),
     *     montant_total    : float,
     * }
     */
    public function genererFacturesAvance(
        Locataire       $locataire,
        LocataireAgence $contrat,
        array           $paiementData
    ): void;

    /**
     * Enregistre les loyers en arriéré pour un locataire ancien
     * (ne crée PAS de transaction_agence).
     *
     * @param  Locataire       $locataire
     * @param  LocataireAgence $contrat
     * @param  array           $arrieres  [{mois, annee, montant_a_payer, montant_payer, ...}]
     */
    public function enregistrerArrieres(
        Locataire       $locataire,
        LocataireAgence $contrat,
        array           $arrieres
    ): void;
    public function newlocataire(  Locataire $locataire,LocataireAgence $contrat): void;
}