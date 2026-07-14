<?php

namespace App\Repositories\Agence\Repository;

use App\Models\EquipementPropriete;
use App\Repositories\Agence\Interfaces\EquipementProprieteRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EquipementProprieteRepository implements EquipementProprieteRepositoryInterface
{
    public function getAllByAgence(string $agenceId): Collection
    {
        return EquipementPropriete::where('agence_id', $agenceId)
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

    public function delete( $id): bool
    {
        $type = $this->findOrFail($id);
//        if ($this->isUsed($type)) {
//            throw new \Exception(
//                "Impossible de supprimer « {$type->name} » : {$this->countProprietes($type)} propriété(s) l'utilisent."
//            );
//        }

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
            throw new ModelNotFoundException("Type de propriété #$id introuvable.");
        }

        return $type;
    }

}