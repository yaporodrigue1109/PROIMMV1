<?php

namespace App\Repositories\Repository;

use App\Models\Transaction;
use App\Repositories\Interfaces\TransactionRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransactionRepository implements TransactionRepositoryInterface
{
    public function __construct(protected Transaction $model) {}

    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator|Collection
    {
        $query = $this->model->with(['agence', 'abonnement']);

        if (!empty($filters['agence_id'])) {
            $query->where('agence_id', $filters['agence_id']);
        }

        if (!empty($filters['statut'])) {
            $query->where('statut', $filters['statut']);
        }

        if (!empty($filters['type_operation'])) {
            $query->where('type_operation', $filters['type_operation']);
        }

        if (!empty($filters['date_debut'])) {
            $query->whereDate('created_at', '>=', $filters['date_debut']);
        }

        if (!empty($filters['date_fin'])) {
            $query->whereDate('created_at', '<=', $filters['date_fin']);
        }

        $query->orderBy('created_at', 'desc');

        return $perPage === -1 ? $query->get() : $query->paginate($perPage);
    }

    public function findById(int $id): ?Transaction
    {
        return $this->model->with(['agence', 'abonnement', 'abonnementHistorique'])->find($id);
    }

    public function findByReference(string $reference): ?Transaction
    {
        return $this->model->where('reference', $reference)->first();
    }

    public function create(array $data): Transaction
    {
        // Générer la référence si non fournie
        if (empty($data['reference'])) {
            $data['reference'] = $this->generateReference();
        }

        return $this->model->create($data);
    }

    public function update(int $id, array $data): Transaction
    {
        $transaction = $this->findById($id);

        if (!$transaction) {
            throw new \Exception("Transaction introuvable: {$id}");
        }

        $transaction->update($data);

        return $transaction->fresh(['agence', 'abonnement']);
    }

    public function valider(int $id, array $data): bool
    {
        $transaction = $this->findById($id);

        if (!$transaction) {
            return false;
        }

        return $transaction->update(array_merge($data, [
            'statut'          => 'validee',
            'date_validation' => now(),
        ]));
    }

    public function getPourAgence(string $agenceId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->with(['abonnement'])
            ->where('agence_id', $agenceId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function generateReference(): string
    {
        $year   = date('Y');
        $prefix = "TXN-{$year}-";

        do {
            $suffix    = strtoupper(substr(uniqid(), -6));
            $reference = $prefix . $suffix;
        } while ($this->model->where('reference', $reference)->exists());

        return $reference;
    }

    public function getTotalEncaisseParAgence(string $agenceId): float
    {
        return (float) $this->model
            ->where('agence_id', $agenceId)
            ->where('statut', 'validee')
            ->sum('montant_ttc');
    }
}