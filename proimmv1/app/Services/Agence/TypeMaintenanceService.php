<?php

namespace App\Services\Agence;

use App\Repositories\Agence\Interfaces\TypeMaintenanceRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TypeMaintenanceService
{
    protected $repository;

    public function __construct(TypeMaintenanceRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getAllTypes(array $filters = [])
    {
        return $this->repository->all($filters);
    }

    public function getType($id)
    {
        return $this->repository->find($id);
    }

    public function createType(array $data)
    {
        try {
            DB::beginTransaction();

            $data['created_by'] = Auth::user()->id ?? null;
            $type = $this->repository->create($data);

            DB::commit();
            return $type;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception("Erreur lors de la création: " . $e->getMessage());
        }
    }

    public function updateType($id, array $data)
    {
        try {
            DB::beginTransaction();

            $data['updated_by'] = Auth::user()->id ?? null;
            $type = $this->repository->update($id, $data);

            DB::commit();
            return $type;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception("Erreur lors de la mise à jour: " . $e->getMessage());
        }
    }

    public function deleteType($id)
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