<?php

namespace App\Repositories\Repository;

use App\Models\Agence;
use App\Repositories\Interfaces\AgenceRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AgenceRepository implements AgenceRepositoryInterface
{
    public function __construct(protected Agence $model) {}

    // ─── Lecture ─────────────────────────────────────────────────────────────

    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator|Collection
    {
        $query = $this->model->newQuery()
            ->with(['region', 'ville', 'responsable', 'abonnement']);

        if (!empty($filters['statut'])) {
            $query->where('statut', $filters['statut']);
        }

        if (!empty($filters['region_id'])) {
            $query->where('region_id', $filters['region_id']);
        }

        if (!empty($filters['ville_id'])) {
            $query->where('ville_id', $filters['ville_id']);
        }

        if (isset($filters['is_principale']) && $filters['is_principale'] !== '') {
            $query->where('is_principale', (bool) $filters['is_principale']);
        }

        if (!empty($filters['abonnement_expire_bientot'])) {
            $days = (int) $filters['abonnement_expire_bientot'];
            $query->where('abonnement_end', '<=', now()->addDays($days))
                ->where('abonnement_end', '>=', now())
                ->where('statut', 'active');
        }

        if (!empty($filters['search'])) {
            $term = $filters['search'];
            $query->where(function ($q) use ($term) {
                $q->where('name', 'LIKE', "%{$term}%")
                    ->orWhere('code_agence', 'LIKE', "%{$term}%")
                    ->orWhere('email1', 'LIKE', "%{$term}%")
                    ->orWhere('tel1', 'LIKE', "%{$term}%");
            });
        }

        $sortField = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortField, $sortOrder);

        return $perPage === -1 ? $query->get() : $query->paginate($perPage);
    }

    public function findById(string $id): ?Agence
    {
        return $this->model->with(['region', 'ville', 'responsable', 'abonnement'])->find($id);
    }

    public function findByCode(string $codeAgence): ?Agence
    {
        return $this->model->where('code_agence', $codeAgence)->first();
    }

    public function findByIdOrCode(string $value): ?Agence
    {
        return $this->findById($value) ?? $this->findByCode($value);
    }

    public function findWithRelations(string $id): ?Agence
    {
        return $this->model->with([
            'region',
            'ville',
            'responsable',
            'abonnement',
            'abonnementHistoriques.nouvelAbonnement',
            'transactions',
            'createur',
            'updateur',
        ])->find($id);
    }

    // ─── Écriture ─────────────────────────────────────────────────────────────

    public function create(array $data): Agence
    {
        if (empty($data['code_agence'])) {
            $data['code_agence'] = 'AG-'.$this->generateUniqueCode();
        }

        return $this->model->create($data);
    }

    public function update(string $id, array $data): Agence
    {
        $agence = $this->findById($id);

        if (!$agence) {
            throw new \Exception("Agence non trouvée: {$id}");
        }

        $agence->update($data);

        return $agence->fresh(['region', 'ville', 'responsable', 'abonnement']);
    }

    public function delete(string $id): bool
    {
        $agence = $this->findById($id);

        if (!$agence) {
            return false;
        }

        return (bool) $agence->delete();
    }

    public function restore(string $id): bool
    {
        $agence = $this->model->withTrashed()->find($id);

        return $agence ? (bool) $agence->restore() : false;
    }

    public function forceDelete(string $id): bool
    {
        $agence = $this->model->withTrashed()->find($id);

        return $agence ? (bool) $agence->forceDelete() : false;
    }

    public function updateStatut(string $id, string $statut): bool
    {
        $allowed = ['en_demo', 'active', 'desactive'];

        if (!in_array($statut, $allowed)) {
            throw new \InvalidArgumentException("Statut invalide: {$statut}");
        }

        $agence = $this->findById($id);

        return $agence ? (bool) $agence->update(['statut' => $statut]) : false;
    }

    // ─── Queries spécifiques ──────────────────────────────────────────────────

    public function hasAgencePrincipaleInRegion(int $regionId, ?string $excludeAgenceId = null): bool
    {
        $query = $this->model->where('region_id', $regionId)->where('is_principale', true);

        if ($excludeAgenceId) {
            $query->where('agence_id', '!=', $excludeAgenceId);
        }

        return $query->exists();
    }

    public function getAbonnementsExpirants(int $days = 7): Collection
    {
        return $this->model->with(['abonnement'])
            ->where('statut', 'active')
            ->whereNotNull('abonnement_end')
            ->whereBetween('abonnement_end', [now(), now()->addDays($days)])
            ->orderBy('abonnement_end')
            ->get();
    }

    public function getAbonnementsExpires(): Collection
    {
        return $this->model->with(['abonnement'])
            ->where('statut', 'active')
            ->whereNotNull('abonnement_end')
            ->where('abonnement_end', '<', now())
            ->orderBy('abonnement_end')
            ->get();
    }

    public function countByStatut(): array
    {
        $stats = $this->model->select('statut', DB::raw('count(*) as total'))
            ->groupBy('statut')
            ->get()
            ->pluck('total', 'statut')
            ->toArray();

        return [
            'en_demo'   => $stats['en_demo'] ?? 0,
            'active'    => $stats['active'] ?? 0,
            'desactive' => $stats['desactive'] ?? 0,
            'total'     => array_sum($stats),
        ];
    }

    public function search(string $searchTerm, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->with(['region', 'ville'])
            ->where(function ($query) use ($searchTerm) {
                $query->where('name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('code_agence', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('email1', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('tel1', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('adresse', 'LIKE', "%{$searchTerm}%");
            })
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function getAllWithTrashed(array $filters = []): Collection
    {
        $query = $this->model->withTrashed()->with(['region', 'ville', 'responsable']);

        if (!empty($filters['only_trashed'])) {
            $query->whereNotNull('deleted_at');
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    // ─── Helpers privés ───────────────────────────────────────────────────────

    protected function generateUniqueCode(): string
    {
        $prefix = 'AGR';
        $year   = date('Y');

        do {
            $random = str_pad(random_int(1, 99999), 5, '0', STR_PAD_LEFT);
            $code   = "{$prefix}{$year}{$random}";
        } while ($this->model->where('code_agence', $code)->exists());

        return $code;
    }
}
