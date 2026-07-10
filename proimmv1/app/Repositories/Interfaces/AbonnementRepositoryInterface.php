<?php

namespace App\Repositories\Interfaces;

use App\Models\Abonnement;
use Illuminate\Database\Eloquent\Collection;

interface AbonnementRepositoryInterface
{
    public function getAll(array $filters = []): Collection;
    public function findById(int $id): ?Abonnement;
    public function findDefault(): ?Abonnement;
    public function create(array $data): Abonnement;
    public function update(int $id, array $data): Abonnement;
    public function delete(int $id): bool;
    public function getActifs(): Collection;
}