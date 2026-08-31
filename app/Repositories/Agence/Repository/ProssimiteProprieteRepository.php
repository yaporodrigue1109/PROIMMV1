<?php

namespace App\Repositories\Agence\Repository;

use App\Models\ProssimitePropriete;
use App\Repositories\Agence\Interfaces\ProssimiteProprieteRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProssimiteProprieteRepository implements ProssimiteProprieteRepositoryInterface
{
    public function getAllByAgence(string $agenceId): Collection
    {
        return ProssimitePropriete::visibleForAgence($agenceId)
            ->orderByRaw('agence_id IS NOT NULL')
            ->orderBy('name')
            ->get();
    }

    public function findById(int $id): ?ProssimitePropriete
    {
        return ProssimitePropriete::find($id);
    }

    public function findByName(string $name, string $agenceId): ?ProssimitePropriete
    {
        return ProssimitePropriete::where('name', $name)
            ->where('agence_id', $agenceId)
            ->first();
    }

    public function create(array $data): ProssimitePropriete
    {
        $data['agence_id'] = $this->agenceId();
        return ProssimitePropriete::create($data);
    }

    public function update(ProssimitePropriete $type, array $data): ProssimitePropriete
    {
        $type->update($data);
        return $type->fresh();
    }

    public function delete(ProssimitePropriete $type): bool
    {
        if ($type->agence_id !== $this->agenceId()) {
            throw new ModelNotFoundException('Cette proximité globale est gérée uniquement par l’administrateur.');
        }

        return $type->delete();
    }

    public function isUsed(ProssimitePropriete $type): bool
    {
        return $type->proprietes()->exists();
    }

    public function countProprietes(ProssimitePropriete $type): int
    {
        return $type->proprietes()->count();
    }

    private function agenceId(): string
    {
        return getInfoAgent()->users->agence_id;
    }
    public function findOrFail(int $id): ProssimitePropriete
    {
        $type = $this->findById($id);

        if (!$type || $type->agence_id !== $this->agenceId()) {
            throw new ModelNotFoundException("Proximité #$id introuvable ou non modifiable.");
        }

        return $type;
    }

}
