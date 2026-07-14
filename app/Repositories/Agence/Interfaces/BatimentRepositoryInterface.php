<?php

namespace App\Repositories\Agence\Interfaces;

use App\Models\Batiment;
use Illuminate\Database\Eloquent\Collection;

interface BatimentRepositoryInterface
{
    /** Tous les bâtiments d'une propriété. */
    public function getByPropriete(string $proprieteId): Collection;

    /** Trouver un bâtiment par ID. */
    public function findById(string $id): ?Batiment;

    /** Créer un bâtiment. */
    public function create(array $data): Batiment;

    /** Mettre à jour un bâtiment. */
    public function update(Batiment $batiment, array $data): Batiment;

    /** Supprimer un bâtiment (et ses portes en cascade). */
    public function delete(Batiment $batiment): bool;
}