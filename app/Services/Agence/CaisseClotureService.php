<?php

namespace App\Services\Agence;

use App\Models\CaisseSession;
use App\Models\TransactionAgence;
use Illuminate\Support\Facades\DB;

class CaisseClotureService
{
    public function cloturerCaissesExpirees(?string $agenceId = null): int
    {
        $query = CaisseSession::query()
            ->whereNull('closed_at')
            ->where('opened_at', '<', now()->startOfDay());

        if ($agenceId !== null) {
            $query->where('agence_id', $agenceId);
        }

        $ids = $query->pluck('caisse_session_id');
        $closed = 0;

        foreach ($ids as $id) {
            $closed += DB::transaction(function () use ($id) {
                $session = CaisseSession::query()
                    ->whereKey($id)
                    ->whereNull('closed_at')
                    ->lockForUpdate()
                    ->first();

                if (! $session || $session->opened_at->isToday()) {
                    return 0;
                }

                $closedAt = $session->opened_at->copy()->endOfDay();
                $transactions = TransactionAgence::query()
                    ->where('agence_id', $session->agence_id)
                    ->whereBetween('date_transaction', [$session->opened_at, $closedAt])
                    ->get(['type_transaction', 'montant_global_verser']);

                $entrees = (float) $transactions
                    ->whereIn('type_transaction', ['loyer', 'vente'])
                    ->sum('montant_global_verser');
                $sorties = (float) $transactions
                    ->whereIn('type_transaction', ['maintenance', 'depense'])
                    ->sum('montant_global_verser');
                $theorique = (float) $session->solde_ouverture + $entrees - $sorties;

                $session->update([
                    'closed_by' => null,
                    'solde_theorique' => $theorique,
                    'solde_fermeture' => $theorique,
                    'ecart' => 0,
                    'observation_fermeture' => 'Clôture automatique à minuit : fermeture manuelle non effectuée.',
                    'closed_at' => $closedAt,
                ]);

                return 1;
            });
        }

        return $closed;
    }
}
