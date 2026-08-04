<?php

namespace App\Services\Agence;

use App\Models\ReversementDetail;
use App\Repositories\Agence\Interfaces\ReversementDetailRepositoryInterface;
use Illuminate\Support\Collection;
use Exception;

class ReversementDetailService
{
    protected ReversementDetailRepositoryInterface $detailRepository;

    public function __construct(ReversementDetailRepositoryInterface $detailRepository)
    {
        $this->detailRepository = $detailRepository;
    }

    public function createDetail(array $data): ReversementDetail
    {
        return $this->detailRepository->create($data);
    }

    public function createDetails(array $data): Collection
    {
        return $this->detailRepository->createMany($data);
    }

    public function getDetail(string $id): ?ReversementDetail
    {
        return $this->detailRepository->find($id);
    }

    public function getDetailsByReversement(string $reversementId): Collection
    {
        return $this->detailRepository->findByReversement($reversementId);
    }

    public function updateDetail(string $id, array $data): ReversementDetail
    {
        $detail = $this->detailRepository->update($id, $data);
        if (!$detail) {
            throw new Exception('Détail non trouvé');
        }
        return $detail;
    }

    public function deleteDetail(string $id): bool
    {
        return $this->detailRepository->delete($id);
    }

    public function getSummaryByReversement(string $reversementId): array
    {
        return $this->detailRepository->getSummaryByReversement($reversementId);
    }
}