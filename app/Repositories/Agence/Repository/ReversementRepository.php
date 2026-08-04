<?php

namespace App\Repositories\Agence\Repository;

use App\Models\Reversement;
use App\Repositories\Agence\Interfaces\ReversementRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ReversementRepository implements ReversementRepositoryInterface
{
    public function create(array $data): Reversement
    {
       // $data['id_reversement'] = Reversement::generateId();
        return Reversement::create($data);
    }

    public function find(string $id): ?Reversement
    {
        return Reversement::withDefaultRelations()->where('agence_id',$this->agenceId())
                         ->find($id);
    }

    public function findAll(array $filters = []): LengthAwarePaginator
    {
        $query = Reversement::withDefaultRelations()->where('agence_id',$this->agenceId());

        // Filtres
        if (!empty($filters['lot_id'])) {
            $query->where('lot_id', $filters['lot_id']);
        }

        if (!empty($filters['proprietaire_id'])) {
            $query->where('proprietaire_id', $filters['proprietaire_id']);
        }

        if (!empty($filters['statut'])) {
            $query->where('statut', $filters['statut']);
        }

        if (!empty($filters['date_debut']) && !empty($filters['date_fin'])) {
            $query->whereBetween('periode_debut', [$filters['date_debut'], $filters['date_fin']]);
        }

        if (!empty($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->where('observation', 'LIKE', '%' . $filters['search'] . '%')
                  ->orWhere('id_reversement', 'LIKE', '%' . $filters['search'] . '%');
            });
        }

        // Tri
        $query->orderBy($filters['sort_by'] ?? 'created_at', $filters['sort_order'] ?? 'desc');

        $perPage = $filters['per_page'] ?? 20;
        return $query->paginate($perPage);
    }

    public function update(string $id, array $data): ?Reversement
    {
        $reversement = Reversement::where('agence_id',$this->agenceId())->find($id);
        if (!$reversement) {
            return null;
        }

        $reversement->update($data);
        return $reversement->fresh();
    }

    public function updateTotals(string $id): ?Reversement
    {
        $reversement = Reversement::where('agence_id',$this->agenceId())->with('details')->find($id);
        if (!$reversement) {
            return null;
        }

        DB::transaction(function () use ($reversement) {
            $reversement->updateTotalsFromDetails();
            $reversement->save();
        });

        return $reversement->fresh();
    }

    public function delete(string $id): bool
    {
        $reversement = Reversement::find($id);
        if (!$reversement) {
            return false;
        }

        return $reversement->delete();
    }

public function getStatistics(): array
{
    // Utiliser directement le modèle sans query()
    $query = Reversement::where('agence_id', $this->agenceId());

    return [
        'total_reversements' => $query->count(),
        'en_attente' => (clone $query)->where('statut', 'en_attente')->count(),
        'reverses' => (clone $query)->where('statut', 'reverse')->count(),
        'annules' => (clone $query)->where('statut', 'annule')->count(),
        'total_attendu' => (clone $query)->sum('total_attendu') ?? 0,
        'total_encaisse' => (clone $query)->sum('total_encaisse') ?? 0,
        'total_restant' => (clone $query)->sum('total_restant') ?? 0,
        'total_net_a_reverser' => (clone $query)->sum('net_a_reverser') ?? 0,
    ];
}

    public function getByPeriode(string $dateDebut, string $dateFin): array
    {
        $query = Reversement::where('agence_id',$this->agenceId())->whereBetween('periode_debut', [$dateDebut, $dateFin])
                           ->orWhereBetween('periode_fin', [$dateDebut, $dateFin]);

        

        return $query->get()->toArray();
    }

        private function agenceId(): string
    {
        return getInfoAgent()->users->agence_id;
    }
}