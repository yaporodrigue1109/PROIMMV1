<?php

namespace App\Repositories\Agence\Interfaces;

use App\Models\Porte;
use App\Models\TarifPorte;
use Illuminate\Database\Eloquent\Collection;

interface PorteRepositoryInterface
{
    /** Toutes les portes d'un bâtiment. */
    public function getByBatiment(string $batimentId): Collection;

    /** Toutes les portes d'une propriété (via ses bâtiments). */
    public function getByPropriete(string $proprieteId): Collection;

    /** Trouver une porte par ID. */
    public function findById(string $id): ?Porte;

    /** Créer une porte. */
    public function create(array $data): Porte;

    /** Mettre à jour une porte. */
    public function update(Porte $porte, array $data): Porte;

    /** Supprimer une porte. */
    public function delete(Porte $porte): bool;

    /** Marquer une porte comme occupée. */
    /** Marquer une porte comme libre (is_occupe = false) */
    public function liberer(Porte $porte): bool;

    /** Marquer une porte comme occupée (is_occupe = true) */
    public function occuper(Porte $porte): bool;

    /** Créer ou remplacer le tarif actif d'une porte. */
    public function updateTarif(Porte $porte, array $tarifData): TarifPorte;
}