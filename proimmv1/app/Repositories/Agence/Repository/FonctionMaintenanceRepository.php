<?php

namespace App\Repositories\Agence\Repository;

use App\Models\FonctionMaintenance;
use App\Repositories\Agence\Interfaces\FonctionMaintenanceRepositoryInterface;

class FonctionMaintenanceRepository implements FonctionMaintenanceRepositoryInterface
{
    protected $model;

    public function __construct(FonctionMaintenance $model)
    {
        $this->model = $model;
    }

    public function all(array $filters = [])
    {
        $query = $this->model->with('agence')->where('agence_id', $this->agenceId());


        return $query->latest()->paginate($filters['per_page'] ?? 15);
    }

    public function find($id)
    {
        return $this->model->with(['agence', 'maintenanciers'])->where('agence_id', $this->agenceId())->find($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update($id, array $data)
    {
        $fonction = $this->find($id);
        if ($fonction) {
            $fonction->update($data);
            return $fonction;
        }
        return null;
    }

    public function delete($id)
    {
        $fonction = $this->where('agence_id', $this->agenceId())->find($id);
        if ($fonction) {
            return $fonction->delete();
        }
        return false;
    }

    public function getByAgence($agenceId)
    {
        return $this->model->where('agence_id', $agenceId)->get();
    }

    public function agenceId(){
        return getInfoAgent()->users->agence_id;
    }
}