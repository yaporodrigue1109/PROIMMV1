<?php

namespace App\Services\Agence;

use App\Models\TypePropriete;
use App\Repositories\Agence\Interfaces\TypeProprieteRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;

class TypeProprieteService
{
    protected  $repository;
    public function __construct(
         TypeProprieteRepositoryInterface $repository
    ) {
        $this->repository = $repository;
    }

    // ─────────────────────────────────────────────────────────────

    public function list(): Collection
    {
        return $this->repository->getAllByAgence($this->agenceId());
    }

    // ─────────────────────────────────────────────────────────────

    public function create(array $data): TypePropriete
    {
        dd($data);
        $data['agence_id'] = $this->agenceId();


        return $this->repository->create($data);
    }

    // ─────────────────────────────────────────────────────────────

    public function update(int $id, array $data): TypePropriete
    {
        $type = $this->findOrFail($id);

        return $this->repository->update($type, [
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
        ]);
    }

    // ─────────────────────────────────────────────────────────────

    public function delete(int $id): void
    {
       // dd('dfsbjhbfd');
        $type = $this->findOrFail($id);
        $this->repository->delete($type);
    }

    // ─────────────────────────────────────────────────────────────

    public function findOrFail(int $id): TypePropriete
    {
        $type = $this->repository->findById($id);

        if (!$type || $type->agence_id !== $this->agenceId()) {
            throw new ModelNotFoundException("Type de propriété #$id introuvable.");
        }

        return $type;
    }

    // ─────────────────────────────────────────────────────────────

    private function agenceId(): string
    {
        return getInfoAgent()->users->agence_id;
    }
}