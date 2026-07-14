<?php

namespace App\Repositories\Agence\Repository;

use App\Models\ProprietaireLot;
use App\Repositories\Agence\Interfaces\LotRepositoryInterface;

class LotRepository implements LotRepositoryInterface
{
    public function getAllByProprietaire(string $proprietaireId, string $agenceId)
    {
        return ProprietaireLot::with(['region', 'ville'])
            ->withCount('proprietes')
            ->where('proprietaire_id', $proprietaireId)
            ->where('agence_id', $agenceId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findById(string $id): ProprietaireLot
    {
        return ProprietaireLot::with(['region', 'ville'])->withCount('proprietes')->findOrFail($id);
    }

    public function create(array $data): ProprietaireLot
    {
        $data['created_by'] = getInfoAgent()->users->id_users ?? null;
        return ProprietaireLot::create($data);
    }

    public function update(string $id, array $data): ProprietaireLot
    {
        $lot = $this->findById($id);
        $data['updated_by'] = getInfoAgent()->users->id_users ?? null;
        $lot->update($data);
        return $lot->fresh(['region', 'ville'])->loadCount('proprietes');
    }

    public function delete(string $id): bool
    {
        $lot = $this->findById($id);
        return $lot->delete();
    }
}
