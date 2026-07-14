<?php

namespace App\Services;

use App\Models\Transaction;
use App\Repositories\Interfaces\TransactionRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Exception;

class TransactionService
{
    public function __construct(
        protected TransactionRepositoryInterface $repository
    ) {}

    public function getTransactionsPourAgence(string $agenceId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getPourAgence($agenceId, $perPage);
    }

    public function validerTransaction(int $transactionId, array $data): bool
    {
        try {
            $transaction = $this->repository->findById($transactionId);

            if (!$transaction) {
                throw new Exception("Transaction introuvable: {$transactionId}");
            }

            if ($transaction->statut !== 'en_attente') {
                throw new Exception("Seules les transactions en attente peuvent être validées.");
            }

            $result = $this->repository->valider($transactionId, [
                'mode_paiement'      => $data['mode_paiement'] ?? null,
                'reference_paiement' => $data['reference_paiement'] ?? null,
                'date_paiement'      => $data['date_paiement'] ?? now(),
                'updated_by'         => getInfoAdmin()->admin->id_admin ?? 1,
                'notes'              => $data['notes'] ?? null,
            ]);

            Log::info('Transaction validée', [
                'transaction_id' => $transactionId,
                'reference'      => $transaction->reference,
            ]);

            return $result;

        } catch (Exception $e) {
            Log::error('Erreur validation transaction: ' . $e->getMessage());
            throw $e;
        }
    }

    public function annulerTransaction(int $transactionId, string $motif): bool
    {
        try {
            $transaction = $this->repository->findById($transactionId);

            if (!$transaction) {
                throw new Exception("Transaction introuvable");
            }

            if (!in_array($transaction->statut, ['en_attente'])) {
                throw new Exception("Cette transaction ne peut pas être annulée.");
            }

            return $this->repository->update($transactionId, [
                    'statut'     => 'annulee',
                    'notes'      => $motif,
                    'updated_by' => getInfoAdmin()->admin->id_admin ?? 1,
                ]) instanceof Transaction;

        } catch (Exception $e) {
            Log::error('Erreur annulation transaction: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getTotalEncaisseParAgence(string $agenceId): float
    {
        return $this->repository->getTotalEncaisseParAgence($agenceId);
    }
}