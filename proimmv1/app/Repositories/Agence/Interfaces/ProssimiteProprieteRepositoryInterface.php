<?php

namespace App\Repositories\Agence\Interfaces;

use App\Models\ProssimitePropriete;
use Illuminate\Database\Eloquent\Collection;

interface ProssimiteProprieteRepositoryInterface
{
    public function getAllByAgence(string $agenceId): Collection;

    public function findById(int $id): ?ProssimitePropriete;

    public function findByName(string $name, int $agenceId): ?ProssimitePropriete;

    public function create(array $data): ProssimitePropriete;

    public function update(ProssimitePropriete $type, array $data): ProssimitePropriete;

    /**
     * @throws \Exception si utilisé par des propriétés
     */
    public function delete(ProssimitePropriete $type): bool;

    public function isUsed(ProssimitePropriete $type): bool;

    public function countProprietes(ProssimitePropriete $type): int;
}