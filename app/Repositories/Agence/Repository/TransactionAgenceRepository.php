<?php

namespace App\Repositories\Agence\Repository;

use App\Models\TransactionAgence;
use App\Repositories\Agence\Interfaces\TransactionAgenceRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class TransactionAgenceRepository implements TransactionAgenceRepositoryInterface
{
    protected TransactionAgence $model;

    public function __construct(TransactionAgence $model)
    {
        $this->model = $model;
    }

    /**
     * {@inheritDoc}
     */
    public function getAll(): Collection
    {
        return $this->model->all();
    }

    /**
     * {@inheritDoc}
     */
    public function findById(string $id): ?object
    {
        return $this->model->find($id);
    }

    /**
     * {@inheritDoc}
     */
    public function create(array $data): object
    {
        return $this->model->create($data);
    }

    /**
     * {@inheritDoc}
     */
    public function update(string $id, array $data): bool
    {
        $transaction = $this->findById($id);
        if (!$transaction) {
            return false;
        }
        return $transaction->update($data);
    }

    /**
     * {@inheritDoc}
     */
    public function delete(string $id): bool
    {
        $transaction = $this->findById($id);
        if (!$transaction) {
            return false;
        }
        return $transaction->delete();
    }

    /**
     * {@inheritDoc}
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->paginate($perPage);
    }

    /**
     * {@inheritDoc}
     */
    public function getByAgence(string $agenceId): Collection
    {
        return $this->model->where('agence_id', $agenceId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getByLocataire(string $locataireId): Collection
    {
        return $this->model->where('locataire_id', $locataireId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getByProprietaire(string $proprietaireId): Collection
    {
        return $this->model->where('proprietaire_id', $proprietaireId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getByPropriete(string $proprieteId): Collection
    {
        return $this->model->where('propriete_id', $proprieteId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getByPorte(string $porteId): Collection
    {
        return $this->model->where('porte_id', $porteId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getByType(string $typeTransaction): Collection
    {
        return $this->model->where('type_transaction', $typeTransaction)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getByModePaiement(string $modePaiementId): Collection
    {
        return $this->model->where('mode_paiement_id', $modePaiementId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getByDateRange(string $startDate, string $endDate): Collection
    {
        return $this->model->whereBetween('date_transaction', [$startDate, $endDate])
            ->orderBy('date_transaction', 'desc')
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getWithReversement(bool $isReversement = true): Collection
    {
        return $this->model->where('is_reversement', $isReversement)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getByMonth(string $month): Collection
    {
        return $this->model->where('mois_payer', $month)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getWithRelations(array $relations = []): Collection
    {
        $query = $this->model->newQuery();
        
        if (!empty($relations)) {
            $query->with($relations);
        }
        
        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getTotalVersementsByAgence(string $agenceId): float
    {
        return $this->model->where('agence_id', $agenceId)
            ->sum('montant_global_verser') ?? 0;
    }

    /**
     * {@inheritDoc}
     */
    public function getTotalArrieresByAgence(string $agenceId): float
    {
        return $this->model->where('agence_id', $agenceId)
            ->sum('arriere_actuel') ?? 0;
    }

    /**
     * {@inheritDoc}
     */
    public function getFiltered(array $filters = []): Collection
    {
        $query = $this->model->newQuery();

        // Filtres disponibles
        if (isset($filters['agence_id'])) {
            $query->where('agence_id', $filters['agence_id']);
        }

        if (isset($filters['locataire_id'])) {
            $query->where('locataire_id', $filters['locataire_id']);
        }

        if (isset($filters['proprietaire_id'])) {
            $query->where('proprietaire_id', $filters['proprietaire_id']);
        }

        if (isset($filters['propriete_id'])) {
            $query->where('propriete_id', $filters['propriete_id']);
        }

        if (isset($filters['porte_id'])) {
            $query->where('porte_id', $filters['porte_id']);
        }

        if (isset($filters['type_transaction'])) {
            $query->where('type_transaction', $filters['type_transaction']);
        }

        if (isset($filters['mode_paiement_id'])) {
            $query->where('mode_paiement_id', $filters['mode_paiement_id']);
        }

        if (isset($filters['is_reversement'])) {
            $query->where('is_reversement', $filters['is_reversement']);
        }

        if (isset($filters['is_first'])) {
            $query->where('is_first', $filters['is_first']);
        }

        if (isset($filters['date_from']) && isset($filters['date_to'])) {
            $query->whereBetween('date_transaction', [$filters['date_from'], $filters['date_to']]);
        }

        if (isset($filters['month'])) {
            $query->where('mois_payer', $filters['month']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('transaction_agence_id', 'like', "%{$filters['search']}%")
                  ->orWhere('mois_payer', 'like', "%{$filters['search']}%");
            });
        }

        // Relations à charger
        if (isset($filters['with_relations']) && is_array($filters['with_relations'])) {
            $query->with($filters['with_relations']);
        }

        // Tri
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getFirstTransactionByLocataire(string $locataireId): ?object
    {
        return $this->model->where('locataire_id', $locataireId)
            ->where('is_first', true)
            ->first();
    }

    /**
     * {@inheritDoc}
     */
    public function getStatisticsByAgence(string $agenceId): array
    {
        $stats = $this->model->where('agence_id', $agenceId)
            ->select([
                DB::raw('COUNT(*) as total_transactions'),
                DB::raw('SUM(montant_global_verser) as total_versements'),
                DB::raw('SUM(montant_loyer_payer) as total_loyers_payes'),
                DB::raw('SUM(arriere_actuel) as total_arrieres'),
                DB::raw('SUM(montant_arriere_payer) as total_arrieres_payes'),
                DB::raw('SUM(montant_avance_payer) as total_avances'),
                DB::raw('COUNT(CASE WHEN is_reversement = 1 THEN 1 END) as total_reversements'),
            ])
            ->first()
            ->toArray();

        return $stats;
    }

    /**
     * {@inheritDoc}
     */
    public function getWithAllRelations(): Collection
    {
        return $this->model->with([
            'locataire',
            'loyer',
            'porte',
            'agence',
            'modePaiement'
        ])->orderBy('created_at', 'desc')->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getByMonthAndYear(int $month, int $year): Collection
    {
        $monthFormatted = sprintf('%04d-%02d', $year, $month);
        return $this->model->where('mois_payer', $monthFormatted)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getWithoutReversement(): Collection
    {
        return $this->model->where('is_reversement', false)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}