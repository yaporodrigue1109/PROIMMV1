<?php

namespace App\Repositories\Agence\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface VenteBienRepositoryInterface
{
    /**
     * Récupérer toutes les ventes
     */
    public function getAll(): Collection;

    /**
     * Récupérer une vente par son ID
     */
    public function findById(string $id): ?object;

    /**
     * Créer une nouvelle vente
     */
    public function create(array $data): object;

    /**
     * Mettre à jour une vente
     */
    public function update(string $id, array $data): bool;

    /**
     * Supprimer une vente
     */
    public function delete(string $id): bool;

    /**
     * Récupérer les ventes avec pagination
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    /**
     * Récupérer les ventes par agence
     */
    public function getByAgence(string $agenceId): Collection;

    /**
     * Récupérer les ventes par propriétaire
     */
    public function getByProprietaire(string $proprietaireId): Collection;

    /**
     * Récupérer les ventes par acheteur
     */
    public function getByAcheteur(string $acheteurId): Collection;

    /**
     * Récupérer les ventes par statut
     */
    public function getByStatut(string $statut): Collection;

    /**
     * Récupérer les ventes par type de paiement
     */
    public function getByTypePaiement(string $typePaiement): Collection;

    /**
     * Récupérer les ventes en cours
     */
    public function getEnCours(): Collection;

    /**
     * Récupérer les ventes terminées
     */
    public function getTermine(): Collection;

    /**
     * Récupérer les ventes annulées
     */
    public function getAnnule(): Collection;

    /**
     * Récupérer les ventes avec filtres
     */
    public function getFiltered(array $filters = []): Collection;

    /**
     * Calculer les statistiques des ventes par agence
     */
    public function getStatisticsByAgence(string $agenceId): array;

    /**
     * Calculer le total des ventes par agence
     */
    public function getTotalVentesByAgence(string $agenceId): float;

    /**
     * Calculer le total des commissions par agence
     */
    public function getTotalCommissionsByAgence(string $agenceId): float;

    /**
     * Générer la prochaine référence
     */
    public function generateReference(string $agenceId): string;
}