<?php

namespace App\Services\Agence;

use App\Repositories\Agence\Interfaces\MaintenancierRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class MaintenancierService
{
    protected $repository;

    public function __construct(MaintenancierRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getAllMaintenanciers(array $filters = [])
    {
        return $this->repository->all($filters);
    }

    public function getMaintenancier($id)
    {
        return $this->repository->find($id);
    }

    public function createMaintenancier(array $data)
    {
        try {
            DB::beginTransaction();

            $data['created_by'] = Auth::user()->id ?? null;
            $data['statut'] = $data['statut'] ?? 'actif';

            $maintenancier = $this->repository->create($data);

            DB::commit();
            return $maintenancier;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception("Erreur lors de la création: " . $e->getMessage());
        }
    }

    public function updateMaintenancier($id, array $data)
    {
        try {
            DB::beginTransaction();

            $data['updated_by'] = Auth::user()->id ?? null;

            $maintenancier = $this->repository->update($id, $data);

            DB::commit();
            return $maintenancier;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception("Erreur lors de la mise à jour: " . $e->getMessage());
        }
    }

    public function deleteMaintenancier($id)
    {
        try {
            DB::beginTransaction();
            $result = $this->repository->delete($id);
            DB::commit();
            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception("Erreur lors de la suppression: " . $e->getMessage());
        }
    }

    public function getDisponibles()
    {
        return $this->repository->getDisponibles();
    }
}