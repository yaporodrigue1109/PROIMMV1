<?php

namespace App\Repositories\Interfaces;

use App\Models\Agence;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface AgenceRepositoryInterface
{
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator|Collection;
    public function findById(string $id): ?Agence;
    public function findByCode(string $codeAgence): ?Agence;
    public function findWithRelations(string $id): ?Agence;
    public function create(array $data): Agence;
    public function update(string $id, array $data): Agence;
    public function delete(string $id): bool;
    public function restore(string $id): bool;
    public function forceDelete(string $id): bool;
    public function updateStatut(string $id, string $statut): bool;
    public function hasAgencePrincipaleInRegion(int $regionId, ?string $excludeAgenceId = null): bool;
    public function getAbonnementsExpirants(int $days = 7): Collection;
    public function getAbonnementsExpires(): Collection;
    public function countByStatut(): array;
    public function search(string $searchTerm, int $perPage = 15): LengthAwarePaginator;
    public function getAllWithTrashed(array $filters = []): Collection;
}







//
//namespace App\Repositories\Interfaces;
//
//
//use Illuminate\Pagination\LengthAwarePaginator;
//use Illuminate\Database\Eloquent\Collection;
//use App\Models\Agence;
//
//interface AgenceRepositoryInterface
//{
//    /**
//     * Récupérer toutes les agences avec filtres
//     *
//     * @param array $filters
//     * @param int $perPage
//     * @return LengthAwarePaginator|Collection
//     */
//    public function getAll(array $filters = [], int $perPage = 15);
//
//    /**
//     * Trouver une agence par son ID
//     *
//     * @param string $id
//     * @return Agence|null
//     */
//    public function findById(string $id): ?Agence;
//
//    /**
//     * Trouver une agence par son code
//     *
//     * @param string $codeAgence
//     * @return Agence|null
//     */
//    public function findByCode(string $codeAgence): ?Agence;
//
//    /**
//     * Créer une nouvelle agence
//     *
//     * @param array $data
//     * @return Agence
//     */
//    public function create(array $data): Agence;
//
//    /**
//     * Mettre à jour une agence
//     *
//     * @param string $id
//     * @param array $data
//     * @return Agence
//     */
//    public function update(string $id, array $data): Agence;
//
//    /**
//     * Supprimer une agence (soft delete)
//     *
//     * @param string $id
//     * @return bool
//     */
//    public function delete(string $id): bool;
//
//    /**
//     * Restaurer une agence supprimée
//     *
//     * @param string $id
//     * @return bool
//     */
//    public function restore(string $id): bool;
//
//    /**
//     * Supprimer définitivement une agence
//     *
//     * @param string $id
//     * @return bool
//     */
//    public function forceDelete(string $id): bool;
//
//    /**
//     * Récupérer les agences principales par région
//     *
//     * @return Collection
//     */
//    public function getAgencesPrincipalesParRegion(): Collection;
//
//    /**
//     * Récupérer les agences dont l'abonnement expire bientôt
//     *
//     * @param int $days
//     * @return Collection
//     */
//    public function getAbonnementsExpirants(int $days = 7): Collection;
//
//    /**
//     * Récupérer les agences avec abonnement expiré
//     *
//     * @return Collection
//     */
//    public function getAbonnementsExpires(): Collection;
//
//    /**
//     * Récupérer les agences par statut
//     *
//     * @param string $statut
//     * @return Collection
//     */
//    public function getByStatut(string $statut): Collection;
//
//    /**
//     * Récupérer les agences par région
//     *
//     * @param int $regionId
//     * @return Collection
//     */
//    public function getByRegion(int $regionId): Collection;
//
//    /**
//     * Vérifier si une agence principale existe déjà dans une région
//     *
//     * @param int $regionId
//     * @param string|null $excludeAgenceId
//     * @return bool
//     */
//    public function hasAgencePrincipaleInRegion(int $regionId, ?string $excludeAgenceId = null): bool;
//
//    /**
//     * Compter les agences par statut
//     *
//     * @return array
//     */
//    public function countByStatut(): array;
//
//    /**
//     * Rechercher des agences
//     *
//     * @param string $searchTerm
//     * @param int $perPage
//     * @return LengthAwarePaginator
//     */
//    public function search(string $searchTerm, int $perPage = 15): LengthAwarePaginator;
//
//    /**
//     * Mettre à jour le statut d'une agence
//     *
//     * @param string $id
//     * @param string $statut
//     * @return bool
//     */
//    public function updateStatut(string $id, string $statut): bool;
//
//    /**
//     * Renouveler l'abonnement d'une agence
//     *
//     * @param string $id
//     * @param string $abonnementId
//     * @param string $dateDebut
//     * @param string $dateFin
//     * @return bool
//     */
//    public function renouvelerAbonnement(string $id, string $abonnementId, string $dateDebut, string $dateFin): bool;
//
//    /**
//     * Récupérer les agences avec toutes leurs relations
//     *
//     * @param string $id
//     * @return Agence|null
//     */
//    public function findWithRelations(string $id): ?Agence;
//
//    /**
//     * Récupérer toutes les agences même soft deleted
//     *
//     * @param array $filters
//     * @return Collection
//     */
//    public function getAllWithTrashed(array $filters = []): Collection;
//}