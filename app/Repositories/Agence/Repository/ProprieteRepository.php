<?php

namespace App\Repositories\Agence\Repository;

use App\Models\Propriete;
use App\Models\ProprieteProximite;
use App\Repositories\Agence\Interfaces\ProprieteRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class ProprieteRepository implements ProprieteRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        if (!Schema::hasTable((new Propriete())->getTable())) {
            return new \Illuminate\Pagination\LengthAwarePaginator(
                collect([]),
                0,
                $perPage,
                1,
                ['path' => request()->url(), 'query' => request()->query()]
            );
        }

        $query = Propriete::withDefaultRelations()->where('agence_id', $this->agenceId() ?? null);

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('reference', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('description', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('adresse_complete', 'like', '%' . $filters['search'] . '%');
            });
        }

        if (!empty($filters['type_id'])) {
            $query->where('type_propriete_id', $filters['type_id']);
        }

        if (array_key_exists('is_actif', $filters) && $filters['is_actif'] !== '' && $filters['is_actif'] !== null && $filters['is_actif'] !== 'all') {
            $query->where('is_actif', filter_var($filters['is_actif'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? (bool) $filters['is_actif']);
        } else {
            $query->where('is_actif', true);
        }

        if (array_key_exists('is_allocation', $filters) && $filters['is_allocation'] !== '' && $filters['is_allocation'] !== null) {
            $query->where('is_allocation', filter_var($filters['is_allocation'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? (bool) $filters['is_allocation']);
        }

        return $query->latest('created_at')->paginate($perPage);
    }

    public function findByAgence(string $id): ?Propriete
    {
        return Propriete::withDefaultRelations()->where('agence_id', $id)->get();
    }

    public function findById(string $id): ?Propriete
    {
        if (!Schema::hasTable((new Propriete())->getTable())) {
            return null;
        }

        return Propriete::withDefaultRelations()->find($id);
    }

    public function findByReference(string $reference): ?Propriete
    {
        return Propriete::withDefaultRelations()->where('reference', $reference)->first();
    }

    public function create(array $data): Propriete
    {
        if (!Schema::hasTable((new Propriete())->getTable())) {
            throw new \RuntimeException('La table des propriÃ©tÃ©s est introuvable.');
        }

        $proximitesData = $this->normalizeProximitesData($data['proximites'] ?? []);
        unset($data['proximites']);

        $data['reference'] = $data['reference'] ?? $this->generateReference();
        $propriete = Propriete::create($data);

        $this->syncProximites($propriete, $proximitesData);

        return $propriete;
    }

    public function update(Propriete $propriete, array $data): Propriete
    {
        if (!Schema::hasTable((new Propriete())->getTable())) {
            throw new \RuntimeException('La table des propriÃ©tÃ©s est introuvable.');
        }

        $proximitesData = $this->normalizeProximitesData($data['proximites'] ?? []);
        unset($data['proximites']);

        $propriete->update($data);

        $this->syncProximites($propriete, $proximitesData);

        return $propriete->fresh();
    }

    public function deactivate(Propriete $propriete): bool
    {
        if (!Schema::hasTable((new Propriete())->getTable())) {
            return false;
        }

        return $propriete->update(['is_actif' => false]);
    }

    public function delete(Propriete $propriete): bool
    {
        if (!Schema::hasTable((new Propriete())->getTable())) {
            return false;
        }

        return $propriete->delete();
    }

    public function stats(array $filters = []): array
    {
        if (!Schema::hasTable((new Propriete())->getTable())) {
            return [
                'total' => 0,
                'allocation' => 0,
                'non_allocation' => 0,
                'ce_mois' => 0,
            ];
        }

        $agenceId = $this->agenceId() ?? null;

        $base = Propriete::withDefaultRelations()->where('agence_id', $agenceId);

        if (!empty($filters['search'])) {
            $base->where(function ($q) use ($filters) {
                $q->where('reference', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('description', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('adresse_complete', 'like', '%' . $filters['search'] . '%');
            });
        }

        if (array_key_exists('is_actif', $filters) && $filters['is_actif'] !== '' && $filters['is_actif'] !== null && $filters['is_actif'] !== 'all') {
            $base->where('is_actif', filter_var($filters['is_actif'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? (bool) $filters['is_actif']);
        }

        return [
            'total' => (clone $base)->count(),
            'allocation' => (clone $base)->where('is_allocation', true)->count(),
            'non_allocation' => (clone $base)->where('is_allocation', false)->count(),
            'ce_mois' => (clone $base)->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)->count(),
        ];
    }

    public function generateReference(): string
    {
        $year = now()->format('Y');
        $count = Propriete::whereYear('created_at', $year)->count() + 1;

        return sprintf('PROP-%s-%04d', $year, $count);
    }

    private function agenceId(): string
    {
        return getInfoAgent()->users->agence_id;
    }

    /**
     * @return array<int, array{proximite_id:int,distance:?int,unite:?string}>
     */
    private function normalizeProximitesData(array $proximites): array
    {
        return collect($proximites)
            ->map(function ($item) {
                if (is_string($item) || is_int($item)) {
                    $item = ['id' => $item];
                }

                if (!is_array($item)) {
                    return null;
                }

                $id = $item['id'] ?? $item['proximite_id'] ?? null;
                if ($id === null || $id === '') {
                    return null;
                }

                $distance = $item['distance'] ?? null;
                $unite = $item['unite'] ?? null;

                return [
                    'proximite_id' => (int) $id,
                    'distance' => $distance === '' || $distance === null ? null : (int) round((float) $distance),
                    'unite' => in_array($unite, ['m', 'km'], true) ? $unite : null,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Synchronise la table relationnelle et la colonne JSON héritée.
     *
     * @param  array<int, array{proximite_id:int,distance:?float,unite:?string}>  $proximitesData
     */
    private function syncProximites(Propriete $propriete, array $proximitesData): void
    {
        $ids = collect($proximitesData)->pluck('proximite_id')->values()->all();

        if (Schema::hasTable((new ProprieteProximite())->getTable())) {
            $propriete->proprieteProximites()->withTrashed()->get()->each->forceDelete();

            foreach ($proximitesData as $proximiteData) {
                $propriete->proprieteProximites()->create($proximiteData);
            }
        }

        if (Schema::hasColumn($propriete->getTable(), 'prossimites')) {
            $propriete->updateQuietly([
                'prossimites' => json_encode($ids),
            ]);
        }
    }
}
