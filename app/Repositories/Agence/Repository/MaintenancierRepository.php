<?php

namespace App\Repositories\Agence\Repository;

use App\Models\Maintenancier;
use App\Repositories\Agence\Interfaces\MaintenancierRepositoryInterface;

class MaintenancierRepository implements MaintenancierRepositoryInterface
{
    protected $model;

    public function __construct(Maintenancier $model)
    {
        $this->model = $model;
    }

    public function all(array $filters = [])
    {
        $query = $this->model->with(['fonction', 'agence']);

        if (!empty($filters['fonction_id'])) {
            $query->where('fonction_maintenance_id', $filters['fonction_id']);
        }

        if (!empty($filters['agence_id'])) {
            $query->where('agence_id', $filters['agence_id']);
        }

        if (!empty($filters['statut'])) {
            $query->where('statut', $filters['statut']);
        }

        return $query->latest()->paginate($filters['per_page'] ?? 15);
    }

//    public function find($id)
//    {
//        return $this->model->with(['fonction', 'agence', 'maintenances'])->find($id);
//    }
    public function find($id)
    {
        return $this->model->withDefaultRelations()->find($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update($id, array $data)
    {
        $maintenancier = $this->find($id);
        if ($maintenancier) {
            $maintenancier->update($data);
            return $maintenancier;
        }
        return null;
    }

    public function delete($id)
    {
        $maintenancier = $this->find($id);
        if ($maintenancier) {
            return $maintenancier->delete();
        }
        return false;
    }

    public function getByFonction($fonctionId)
    {
        return $this->model->where('fonction_maintenance_id', $fonctionId)->get();
    }

    public function getByAgence($agenceId)
    {
        return $this->model->where('agence_id', $agenceId)->get();
    }

    public function getActifs()
    {
        return $this->model->actif()->get();
    }

    public function getDisponibles()
    {
        return $this->model->where('statut', true)
            ->whereDoesntHave('maintenances', function($q) {
                $q->whereIn('statut', ['en_cours', 'en_attente']);
            })
            ->get();
    }
}
