<?php

namespace App\Repositories\Agence\Repository;

use App\Models\TypeMaintenance;
use App\Repositories\Agence\Interfaces\TypeMaintenanceRepositoryInterface;

class TypeMaintenanceRepository implements TypeMaintenanceRepositoryInterface
{
    protected $model;

    public function __construct(TypeMaintenance $model)
    {
        $this->model = $model;
    }

    public function all(array $filters = [])
    {
        $query = $this->model->with(['agence', 'maintenanceCategory']);

        $query->where('agence_id', $this->agenceId());

        return $query->latest()->paginate($filters['per_page'] ?? 15);
    }

    public function find($id)
    {
        return $this->model->with(['agence', 'maintenances', 'maintenanceCategory'])
            ->where('agence_id', $this->agenceId())
            ->find($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update($id, array $data)
    {
        $type = $this->find($id);
        if ($type) {
            $type->update($data);
            return $type;
        }
        return null;
    }

    public function delete($id)
    {
        $type = $this->find($id);
        if ($type) {
            return $type->delete();
        }
        return false;
    }

    public function getByAgence($agenceId)
    {
        return $this->model->with('maintenanceCategory')->where('agence_id', $agenceId)->get();
    }

    private function agenceId(): string
    {
        return getInfoAgent()->users->agence_id;
    }
}
