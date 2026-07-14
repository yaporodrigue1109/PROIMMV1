<?php

namespace App\Repositories\Agence\Interfaces;

use App\Models\EquipementPropriete;
use Illuminate\Database\Eloquent\Collection;

interface EquipementProprieteRepositoryInterface
{
    public function getAllByAgence(string $agenceId): Collection;

    public function findById(int $id): ?EquipementPropriete;

    public function findByName(string $name, string $agenceId): ?EquipementPropriete;

    public function create(array $data): EquipementPropriete;

    public function update(EquipementPropriete $type, array $data): EquipementPropriete;

    /**
     * @throws \Exception si utilisé par des propriétés
     */
    public function delete(EquipementPropriete $type): bool;

    public function isUsed(EquipementPropriete $type): bool;

    public function countProprietes(EquipementPropriete $type): int;
}