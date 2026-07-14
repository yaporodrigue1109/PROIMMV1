<?php

namespace App\Repositories\Interfaces;

use App\Models\Transaction;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface TransactionRepositoryInterface
{
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator|Collection;
    public function findById(int $id): ?Transaction;
    public function findByReference(string $reference): ?Transaction;
    public function create(array $data): Transaction;
    public function update(int $id, array $data): Transaction;
    public function valider(int $id, array $data): bool;
    public function getPourAgence(string $agenceId, int $perPage = 15): LengthAwarePaginator;
    public function generateReference(): string;
    public function getTotalEncaisseParAgence(string $agenceId): float;
}