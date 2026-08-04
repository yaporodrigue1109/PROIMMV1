<?php

namespace App\Repositories\Agence\Repository;

use App\Models\ReversementDetail;
use App\Repositories\Agence\Interfaces\ReversementDetailRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReversementDetailRepository implements ReversementDetailRepositoryInterface
{
    public function create(array $data): ReversementDetail
    {
       // $data['id_reversement_detail'] = ReversementDetail::generateId();
        $detail = new ReversementDetail($data);
        $detail->calculerTotaux();
        $detail->save();
        return $detail;
    }

    public function createMany(array $data): Collection
    {
        $details = collect();

        DB::transaction(function () use ($data, &$details) {
            foreach ($data as $item) {
                $detail = $this->create($item);
                $details->push($detail);
            }
        });

        return $details;
    }

    public function find(string $id): ?ReversementDetail
    {
        return ReversementDetail::withDefaultRelations()->find($id);
    }

    public function findByReversement(string $reversementId): Collection
    {
        return ReversementDetail::withDefaultRelations()
                               ->where('reversement_id', $reversementId)
                               ->where('agence_id',$this->agenceId())
                               ->orderBy('porte_id')
                               ->get();
    }

    public function findByLocataireAndReversement(string $reversementId, int $locataireId): ?ReversementDetail
    {
        return ReversementDetail::withDefaultRelations()->where('reversement_id', $reversementId)
                               ->where('locataire_id', $locataireId)
                               ->where('agence_id',$this->agenceId())
                               ->first();
    }

    public function update(string $id, array $data): ?ReversementDetail
    {
        $detail = ReversementDetail::withDefaultRelations()
        ->where('agence_id',$this->agenceId())
        ->find($id);
        if (!$detail) {
            return null;
        }

        $detail->fill($data);
        $detail->calculerTotaux();
        $detail->save();

        return $detail->fresh();
    }

    public function updatePaiements(string $reversementId, array $paiements): Collection
    {
        $updated = collect();

        DB::transaction(function () use ($reversementId, $paiements, &$updated) {
            foreach ($paiements as $paiement) {
                $detail = $this->findByLocataireAndReversement(
                    $reversementId,
                    $paiement['locataire_id']
                );

                if ($detail) {
                    $updatedDetail = $this->update($detail->id, [
                        'loyer_paye' => $paiement['loyer_paye'] ?? 0,
                        'arriere_paye' => $paiement['arriere_paye'] ?? 0
                    ]);
                    if ($updatedDetail) {
                        $updated->push($updatedDetail);
                    }
                }
            }
        });

        return $updated;
    }

    public function delete(string $id): bool
    {
        $detail = ReversementDetail::where('agence_id',$this->agenceId())->find($id);
        if (!$detail) {
            return false;
        }

        return $detail->delete();
    }

    public function deleteByReversement(string $reversementId): bool
    {
        return ReversementDetail::where('agence_id',$this->agenceId())->where('reversement_id', $reversementId)->delete() > 0;
    }

    public function getSummaryByReversement(string $reversementId): array
    {
        $stats = ReversementDetail::where('agence_id',$this->agenceId())->where('reversement_id', $reversementId)
                                 ->select(
                                     DB::raw('COUNT(*) as total_locataires'),
                                     DB::raw('COALESCE(SUM(montant_attendu), 0) as total_attendu'),
                                     DB::raw('COALESCE(SUM(total_paye), 0) as total_paye'),
                                     DB::raw('COALESCE(SUM(impayes), 0) as total_impayes'),
                                     DB::raw('COUNT(CASE WHEN impayes = 0 THEN 1 END) as locataires_payes'),
                                     DB::raw('COUNT(CASE WHEN impayes > 0 THEN 1 END) as locataires_impayes')
                                 )
                                 ->first();

        return $stats ? $stats->toArray() : [
            'total_locataires' => 0,
            'total_attendu' => 0,
            'total_paye' => 0,
            'total_impayes' => 0,
            'locataires_payes' => 0,
            'locataires_impayes' => 0
        ];
    }

            private function agenceId(): string
    {
        return getInfoAgent()->users->agence_id;
    }
}