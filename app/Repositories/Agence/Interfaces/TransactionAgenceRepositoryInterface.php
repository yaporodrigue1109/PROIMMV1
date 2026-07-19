<?php

namespace App\Repositories\Agence\Interfaces;


use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface TransactionAgenceRepositoryInterface
{
    /**
     * Récupérer toutes les transactions
     */
    public function getAll(): Collection;

    /**
     * Récupérer une transaction par son ID
     */
    public function findById(string $id): ?object;

    /**
     * Créer une nouvelle transaction
     */
    public function create(array $data): object;

    /**
     * Mettre à jour une transaction
     */
    public function update(string $id, array $data): bool;

    /**
     * Supprimer une transaction
     */
    public function delete(string $id): bool;

    /**
     * Récupérer les transactions avec pagination
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    /**
     * Récupérer les transactions par agence
     */
    public function getByAgence(string $agenceId): Collection;

    /**
     * Récupérer les transactions par locataire
     */
    public function getByLocataire(string $locataireId): Collection;

    /**
     * Récupérer les transactions par propriétaire
     */
    public function getByProprietaire(string $proprietaireId): Collection;

    /**
     * Récupérer les transactions par propriété
     */
    public function getByPropriete(string $proprieteId): Collection;

    /**
     * Récupérer les transactions par porte
     */
    public function getByPorte(string $porteId): Collection;

    /**
     * Récupérer les transactions par type
     */
    public function getByType(string $typeTransaction): Collection;

    /**
     * Récupérer les transactions par mode de paiement
     */
    public function getByModePaiement(string $modePaiementId): Collection;

    /**
     * Récupérer les transactions d'une période donnée
     */
    public function getByDateRange(string $startDate, string $endDate): Collection;

    /**
     * Récupérer les transactions avec reversement
     */
    public function getWithReversement(bool $isReversement = true): Collection;

    /**
     * Récupérer les transactions d'un mois spécifique
     */
    public function getByMonth(string $month): Collection;

    /**
     * Récupérer les transactions avec leurs relations
     */
    public function getWithRelations(array $relations = []): Collection;

    /**
     * Calculer le total des versements par agence
     */
    public function getTotalVersementsByAgence(string $agenceId): float;

    /**
     * Calculer le total des arriérés par agence
     */
    public function getTotalArrieresByAgence(string $agenceId): float;

    /**
     * Récupérer les transactions avec filtres avancés
     */
    public function getFiltered(array $filters = []): Collection;

    /**
     * Récupérer la première transaction d'un locataire
     */
    public function getFirstTransactionByLocataire(string $locataireId): ?object;
}