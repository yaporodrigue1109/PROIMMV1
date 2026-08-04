<?php

namespace App\Repositories\Agence\Interfaces;

use App\Models\ReversementDetail;
use Illuminate\Support\Collection;

interface ReversementDetailRepositoryInterface
{
    public function create(array $data): ReversementDetail;
    public function createMany(array $data): Collection;
    public function find(string $id): ?ReversementDetail;
    public function findByReversement(string $reversementId): Collection;
    public function findByLocataireAndReversement(string $reversementId, int $locataireId): ?ReversementDetail;
    public function update(string $id, array $data): ?ReversementDetail;
    public function updatePaiements(string $reversementId, array $paiements): Collection;
    public function delete(string $id): bool;
    public function deleteByReversement(string $reversementId): bool;
    public function getSummaryByReversement(string $reversementId): array;
}