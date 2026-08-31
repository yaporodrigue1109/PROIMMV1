<?php

namespace App\Repositories\Agence\Repository;

use App\Models\TypePropriete;
use App\Repositories\Agence\Interfaces\TypeProprieteRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TypeProprieteRepository implements TypeProprieteRepositoryInterface
{
    public function getAllByAgence(string $agenceId): Collection
    {
        return TypePropriete::visibleForAgence($agenceId)
            ->orderByRaw('agence_id IS NOT NULL')
            ->orderBy('name')
            ->get();
    }

    public function findById(int $id): ?TypePropriete
    {
        return TypePropriete::find($id);
    }

    public function findByName(string $name, string $agenceId): ?TypePropriete
    {
        return TypePropriete::where('name', $name)
            ->where('agence_id', $agenceId)
            ->first();
    }

    public function create(array $data): TypePropriete
    {
        $data['agence_id'] = $this->agenceId();
        return TypePropriete::create($data);
    }

    public function update(TypePropriete $type, array $data): TypePropriete
    {
        $type->update($data);
        return $type->fresh();
    }

    public function delete(TypePropriete $type): bool
    {
        if ($type->agence_id !== $this->agenceId()) {
            throw new ModelNotFoundException('Ce type global est géré uniquement par l’administrateur.');
        }

        return $type->delete();
    }

    public function isUsed(TypePropriete $type): bool
    {
        return $type->proprietes()->exists();
    }

    public function countProprietes(TypePropriete $type): int
    {
        return $type->proprietes()->count();
    }

    private function agenceId(): string
    {
        return getInfoAgent()->users->agence_id;
    }
    public function findOrFail(int $id): TypePropriete
    {
        $type = $this->findById($id);

        if (!$type || $type->agence_id !== $this->agenceId()) {
            throw new ModelNotFoundException("Type de propriété #$id introuvable ou non modifiable.");
        }

        return $type;
    }

}
