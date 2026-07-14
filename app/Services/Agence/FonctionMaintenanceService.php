<?php

namespace App\Services\Agence;

use App\Repositories\Agence\Interfaces\FonctionMaintenanceRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class FonctionMaintenanceService
{
    protected $repository;

    public function __construct(FonctionMaintenanceRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getAllFonctions(array $filters = [])
    {
        return $this->repository->all($filters);
    }

    public function getFonction($id)
    {
        return $this->repository->find($id);
    }

    public function createFonction(array $data)
    {
        try {
            DB::beginTransaction();

            $data['created_by'] = Auth::user()->id ?? null;
            $fonction = $this->repository->create($data);

            DB::commit();
            return $fonction;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception("Erreur lors de la création: " . $e->getMessage());
        }
    }

    public function updateFonction($id, array $data)
    {
        try {
            DB::beginTransaction();

            $data['updated_by'] = Auth::user()->id ?? null;
            $fonction = $this->repository->update($id, $data);

            DB::commit();
            return $fonction;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception("Erreur lors de la mise à jour: " . $e->getMessage());
        }
    }

    public function deleteFonction($id)
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
}