<?php

namespace App\Repositories\Repository;

use App\Models\Abonnement;
use App\Repositories\Interfaces\AbonnementRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class AbonnementRepository implements AbonnementRepositoryInterface
{
    public function __construct(protected Abonnement $model) {}

    public function getAll(array $filters = []): Collection
    {
        $query = $this->model->newQuery();

        if (!empty($filters['statut'])) {
            $query->where('statut', $filters['statut']);
        }

        return $query->orderBy('ordre')->get();
    }

    public function findById(int $id): ?Abonnement
    {
        return $this->model->find($id);
    }

    public function findDefault(): ?Abonnement
    {
        return $this->model->where('is_default', true)
            ->where('statut', 'actif')
            ->first();
    }

    public function create(array $data): Abonnement
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): Abonnement
    {
        $abonnement = $this->findById($id);

        if (!$abonnement) {
            throw new \Exception("Abonnement introuvable: {$id}");
        }

        $abonnement->update($data);

        return $abonnement->fresh();
    }

    public function delete(int $id): bool
    {
        $abonnement = $this->findById($id);

        if (!$abonnement) {
            return false;
        }

        return $abonnement->delete();
    }

    public function getActifs(): Collection
    {
        return $this->model->where('statut', 'actif')
            ->orderBy('ordre')
            ->get();
    }
}