<?php

namespace App\Repositories\Agence\Interfaces;

use App\Models\TypePropriete;
use Illuminate\Database\Eloquent\Collection;

interface TypeProprieteRepositoryInterface
{
    public function getAllByAgence(string $agenceId): Collection;

    public function findById(int $id): ?TypePropriete;

    public function findByName(string $name, int $agenceId): ?TypePropriete;

    public function create(array $data): TypePropriete;

    public function update(TypePropriete $type, array $data): TypePropriete;

    /**
     * @throws \Exception si utilisé par des propriétés
     */
    public function delete(TypePropriete $type): bool;

    public function isUsed(TypePropriete $type): bool;

    public function countProprietes(TypePropriete $type): int;
}