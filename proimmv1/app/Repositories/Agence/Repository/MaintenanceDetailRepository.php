<?php

namespace App\Repositories\Agence\Repository;

use App\Models\MaintenanceDetail;
use App\Repositories\Interfaces\MaintenanceDetailRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class MaintenanceDetailRepository implements MaintenanceDetailRepositoryInterface
{
    protected  $model;
    public function __construct(
         MaintenanceDetail $model
    ) {
        $this->model = $model;
    }

    public function findByMaintenance(string $maintenanceId): Collection
    {
        return $this->model
            ->with(['maintenancier', 'typeIntervention'])
            ->where('maintenance_id', $maintenanceId)
            ->get();
    }

    public function findById(string $id): ?MaintenanceDetail
    {
        return $this->model
            ->with(['maintenancier', 'typeIntervention', 'maintenance'])
            ->find($id);
    }

    public function create(array $data): MaintenanceDetail
    {
        $data['created_by'] = Auth::id();

        return $this->model->create($data);
    }

    public function update(string $id, array $data): MaintenanceDetail
    {
        $detail = $this->model->findOrFail($id);

        $data['updated_by'] = Auth::id();
        $detail->update($data);

        return $detail->fresh();
    }

    public function delete(string $id): bool
    {
        $detail = $this->model->findOrFail($id);
        $detail->deleted_by = Auth::id();
        $detail->save();

        return (bool) $detail->delete();
    }

    public function deleteByMaintenance(string $maintenanceId): int
    {
        $details = $this->model->where('maintenance_id', $maintenanceId)->get();

        $count = 0;
        foreach ($details as $detail) {
            $detail->deleted_by = Auth::id();
            $detail->save();
            $detail->delete();
            $count++;
        }

        return $count;
    }

    public function bulkInsert(string $maintenanceId, array $details): bool
    {
        $now       = now();
        $userId    = Auth::id();
        $rows      = [];

        foreach ($details as $detail) {
            $rows[] = [
                'maintenance_id'       => $maintenanceId,
                'type_intervention_id' => $detail['type_intervention_id'] ?? null,
                'maintenancier_id'     => $detail['maintenancier_id']     ?? null,
                'date_debut'           => $detail['date_debut']           ?? null,
                'date_fin'             => $detail['date_fin']             ?? null,
                'priorite'             => $detail['priorite']             ?? 'normale',
                'montant'              => $detail['prix']                 ?? $detail['montant'] ?? 0,
                'note'                 => $detail['description']          ?? $detail['note'] ?? null,
                'statut'               => $detail['statut']               ?? 'en_attente',
                'created_by'           => $userId,
                'created_at'           => $now,
                'updated_at'           => $now,
            ];
        }

        return $this->model->insert($rows);
    }

    public function updateStatut(string $id, string $statut): MaintenanceDetail
    {
        $detail = $this->model->findOrFail($id);
        $detail->update([
            'statut'     => $statut,
            'updated_by' => Auth::id(),
        ]);

        return $detail;
    }

    public function findByMaintenancier(string $maintenancierId): Collection
    {
        return $this->model
            ->with(['maintenance', 'typeIntervention'])
            ->where('maintenancier_id', $maintenancierId)
            ->get();
    }
}