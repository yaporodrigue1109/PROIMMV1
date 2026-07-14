<?php


namespace App\Repositories\Agence\Repository;

use App\Repositories\Agence\Interfaces\PersonnelRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class PersonnelRepository implements PersonnelRepositoryInterface
{
    public function getAllByAgence(string $agenceId, array $filters = [])
    {
        $query = User::with(['role'])
            ->where('agence_id', $agenceId);

        if (!empty($filters['statut'])) {
            $query->where('statut', $filters['statut']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('email', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('tel1', 'like', '%' . $filters['search'] . '%');
            });
        }

        if (!empty($filters['role_id'])) {
            $query->where('role_id', $filters['role_id']);
        }

        return $query->orderBy('name')->get();
    }

    public function findById(string $id)
    {
        return User::with(['role', 'agence'])
            ->where('id_users', $id)
            ->firstOrFail();
    }

    public function create(array $data): mixed
    {
      //  dd($data);
        $data['password'] = Hash::make($data['password']);
        $data['created_by'] = auth('user')->id();
        $data['statut'] = $data['statut'] ?? 'actif';
        return User::create($data);
    }

    public function update(string $id, array $data): mixed
    {
        $user = $this->findById($id);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $data['updated_by'] = auth('user')->id();
        $user->update($data);

        return $user->fresh();
    }

    public function delete(string $id): bool
    {
        $user = $this->findById($id);
        $user->deleted_by = auth('user')->id();
        $user->save();

        return $user->delete();
    }

    public function activate(string $id): bool
    {
        return User::where('id_users', $id)
                ->update([
                    'statut'     => 'actif',

                ]) > 0;
    }

    public function deactivate(string $id): bool
    {
        return User::where('id_users', $id)
                ->update([
                    'statut'     => 'inactif',
                    
                ]) > 0;
    }
}
