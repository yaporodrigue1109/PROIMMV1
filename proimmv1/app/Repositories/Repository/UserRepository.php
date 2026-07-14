<?php

namespace App\Repositories\Repository;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class UserRepository implements UserRepositoryInterface
{
    public function __construct(protected User $model) {}

    public function getAll(array $filters = []): Collection
    {
        $query = $this->model->newQuery();

        if (!empty($filters['statut'])) {
            $query->where('statut', $filters['statut']);
        }

        if (!empty($filters['is_responsable'])) {
            $query->where('is_responsable', true);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function paginate(int $perPage = 15, array $filters = []): Paginator
    {
        $query = $this->model->newQuery();

        if (!empty($filters['statut'])) {
            $query->where('statut', $filters['statut']);
        }

        return $query->paginate($perPage);
    }

    public function getById(string $id): ?User
    {
        return $this->model->find($id);
    }

    public function getByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    public function create(array $data): User
    {
        return $this->model->create($data);
    }

    public function update(string $id, array $data): User
    {
        $user = $this->getById($id);

        if (!$user) {
            throw new \Exception("Utilisateur introuvable: {$id}");
        }

        $user->update($data);

        return $user->fresh();
    }

    public function delete(string $id): bool
    {
        $user = $this->getById($id);
        return $user ? (bool) $user->delete() : false;
    }

    public function restore(string $id): bool
    {
        $user = $this->model->withTrashed()->find($id);
        return $user ? (bool) $user->restore() : false;
    }

    public function forceDelete(string $id): bool
    {
        $user = $this->model->withTrashed()->find($id);
        return $user ? (bool) $user->forceDelete() : false;
    }

    public function getByAgence(string $agenceId, array $filters = []): Collection
    {
        // Adapter selon votre structure (relation directe ou via role)
        return $this->model->where('agence_id', $agenceId)
            ->orWhereHas('agences', fn($q) => $q->where('agence_id', $agenceId))
            ->get();
    }

    public function getByRole(int $roleId, array $filters = []): Collection
    {
        return $this->model->where('role_id', $roleId)->get();
    }

    public function getResponsables(): Collection
    {
        return $this->model->where('is_responsable', true)->get();
    }

    public function search(string $term, array $filters = []): Collection
    {
        return $this->model->where(function ($q) use ($term) {
            $q->where('name', 'LIKE', "%{$term}%")
                ->orWhere('email', 'LIKE', "%{$term}%")
                ->orWhere('tel1', 'LIKE', "%{$term}%");
        })->get();
    }

    public function emailExists(string $email, ?string $excludeId = null): bool
    {
        $query = $this->model->where('email', $email);

        if ($excludeId) {
            $query->where('id_users', '!=', $excludeId);
        }

        return $query->exists();
    }

    public function activate(string $id): User
    {
        return $this->update($id, ['statut' => true, 'activated_at' => now()]);
    }

    public function deactivate(string $id): User
    {
        return $this->update($id, ['statut' => false]);
    }

    public function suspend(string $id): User
    {
        return $this->update($id, ['statut' => false, 'suspended_at' => now()]);
    }

    public function count(array $filters = []): int
    {
        $query = $this->model->newQuery();

        if (!empty($filters['statut'])) {
            $query->where('statut', $filters['statut']);
        }

        return $query->count();
    }

    public function getStatistics(): array
    {
        return [
            'total'         => $this->model->count(),
            'actifs'        => $this->model->where('statut', true)->count(),
            'inactifs'      => $this->model->where('statut', false)->count(),
            'responsables'  => $this->model->where('is_responsable', true)->count(),
        ];
    }
}