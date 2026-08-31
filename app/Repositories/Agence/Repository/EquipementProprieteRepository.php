<?php

namespace App\Repositories\Agence\Repository;

use App\Models\EquipementPropriete;
use App\Repositories\Agence\Interfaces\EquipementProprieteRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class EquipementProprieteRepository implements EquipementProprieteRepositoryInterface
{
    public function getAllByAgence(string $agenceId): Collection
    {
        return EquipementPropriete::visibleForAgence($agenceId)
            ->orderByRaw('agence_id IS NOT NULL')
            ->orderBy('name')
            ->get();
    }

    public function findById(int $id): ?EquipementPropriete
    {
        return EquipementPropriete::find($id);
    }

    public function findByName(string $name, string $agenceId): ?EquipementPropriete
    {
        return EquipementPropriete::where('name', $name)
            ->where('agence_id', $agenceId)
            ->first();
    }

    public function create(array $data): EquipementPropriete
    {
        //dd($data);
        $data['agence_id'] = $this->agenceId();
        return EquipementPropriete::create($data);
    }

    public function update(EquipementPropriete $type, array $data): EquipementPropriete
    {
        $type->update($data);
        return $type->fresh();
    }

    public function delete(EquipementPropriete $type): bool
    {
        if ($type->agence_id !== $this->agenceId()) {
            throw new ModelNotFoundException('Cet équipement global est géré uniquement par l’administrateur.');
        }

        return $type->delete();
    }

    public function isUsed(EquipementPropriete $type): bool
    {
        return $type->proprietes()->exists();
    }

    public function countProprietes(EquipementPropriete $type): int
    {
        return $type->proprietes()->count();
    }

    private function agenceId(): string
    {
        return getInfoAgent()->users->agence_id;
    }
    public function findOrFail(int $id): EquipementPropriete
    {
        $type = $this->findById($id);

        if (!$type || $type->agence_id !== $this->agenceId()) {
            throw new ModelNotFoundException("Équipement #$id introuvable ou non modifiable.");
        }

        return $type;
    }

}
