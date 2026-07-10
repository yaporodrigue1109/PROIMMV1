<?php

namespace App\Repositories\Agence\Repository;

use App\Models\Batiment;
use App\Repositories\Agence\Interfaces\BatimentRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class BatimentRepository implements BatimentRepositoryInterface
{
    public function getByPropriete(string $proprieteId): Collection
    {
        return Batiment::with(['portes.typePorte', 'portes.tarifActif'])
            ->where('propriete_id', $proprieteId)
            ->orderBy('name')
            ->get();
    }

    public function findById(string $id): ?Batiment
    {
        return Batiment::with(['portes.typePorte', 'portes.tarifActif', 'propriete'])->find($id);
    }

    public function create(array $data): Batiment
    {

     //   $data['name'] = $data['nom'];

        return Batiment::create($data);
    }

    public function update(Batiment $batiment, array $data): Batiment
    {
        $batiment->update($data);
        return $batiment->fresh();
    }

    public function delete(Batiment $batiment): bool
    {
        return $batiment->delete();
    }
}
