<?php

namespace App\Repositories\Interfaces;

use App\Models\Proprietaire;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ProprietaireRepositoryInterface
{
    /**
     * Récupère la liste paginée des propriétaires de l'agence connectée.
     */
    public function getAllByAgence(string $agenceId, int $perPage = 15, array $filters = []): LengthAwarePaginator;

    /**
     * Trouve un propriétaire par son ID (avec liaison agence).
     */
    public function findById(string $id): ?Proprietaire;

    /**
     * Trouve un propriétaire par son ID dans le contexte d'une agence.
     */
    public function findByIdAndAgence(string $id, string $agenceId): ?Proprietaire;

    /**
     * Crée un nouveau propriétaire et sa liaison agence.
     */
    public function create(array $proprietaireData, array $agenceData, string $agenceId): Proprietaire;

    /**
     * Met à jour un propriétaire et sa liaison agence.
     */
    public function update(string $id, array $proprietaireData, array $agenceData): Proprietaire;

    /**
     * Supprime (soft delete) un propriétaire.
     */
    public function delete(string $id): bool;

    /**
     * Active un propriétaire dans l'agence.
     */
    public function activate(string $proprietaireAgenceId): bool;

    /**
     * Désactive un propriétaire dans l'agence.
     */
    public function deactivate(string $proprietaireAgenceId): bool;
    public function getAvecPortesDisponibles(): Collection;
    public function getAvecPortesDisponiblesByAgence(?string $agenceId = null): Collection;
}
