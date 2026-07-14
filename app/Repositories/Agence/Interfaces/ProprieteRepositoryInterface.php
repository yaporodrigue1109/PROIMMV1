<?php

namespace App\Repositories\Agence\Interfaces;


use App\Models\Propriete;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ProprieteRepositoryInterface
{
    /**
     * Retourner toutes les propriétés paginées avec filtres optionnels.
     *
     * @param  array  $filters  ['search' => '', 'type_id' => null, 'is_actif' => true]
     * @param  int    $perPage
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /** Trouver une propriété par son ID avec ses relations chargées. */
    public function findById(string $id): ?Propriete;
    public function findByAgence(string $id): ?Propriete;
    /** Trouver une propriété par sa référence. */
    public function findByReference(string $reference): ?Propriete;

    /** Créer une nouvelle propriété. */
    public function create(array $data): Propriete;

    /** Mettre à jour une propriété existante. */
    public function update(Propriete $propriete, array $data): Propriete;

    /** Désactiver (soft-delete logique) une propriété. */
    public function deactivate(Propriete $propriete): bool;

    /** Supprimer définitivement une propriété. */
    public function delete(Propriete $propriete): bool;

    /** Stats globales pour le dashboard. */
    public function stats(): array;

    /** Générer un code de référence unique. */
    public function generateReference(): string;

}