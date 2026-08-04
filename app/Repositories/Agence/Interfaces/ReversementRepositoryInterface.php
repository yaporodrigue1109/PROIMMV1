<?php

namespace App\Repositories\Agence\Interfaces;

use App\Models\Reversement;
use Illuminate\Pagination\LengthAwarePaginator;

interface ReversementRepositoryInterface
{
    public function create(array $data): Reversement;
    public function find(string $id): ?Reversement;
    public function findAll(array $filters = []): LengthAwarePaginator;
    public function update(string $id, array $data): ?Reversement;
    public function updateTotals(string $id): ?Reversement;
    public function delete(string $id): bool;
    public function getStatistics(): array;
    public function getByPeriode(string $dateDebut, string $dateFin): array;
}