<?php

namespace App\Repositories\Agence\Repository;

use App\Models\Porte;
use App\Models\TarifPorte;
use App\Repositories\Agence\Interfaces\PorteRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class PorteRepository implements PorteRepositoryInterface
{
    public function getByBatiment(string $batimentId): Collection
    {
        return Porte::with(['typePorte', 'tarifActif'])
            ->where('batiment_id', $batimentId)
            ->where('is_actif', true)
            ->orderBy('numero_porte')
            ->get();
    }

    public function getByPropriete(string $proprieteId): Collection
    {
        return Porte::with(['typePorte', 'tarifActif', 'batiment'])
            ->whereHas('batiment', fn($q) => $q->where('propriete_id', $proprieteId))
            ->where('is_actif', true)
            ->orderBy('numero_porte')
            ->get();
    }

    public function findById(string $id): ?Porte
    {
        return Porte::with(['typePorte', 'tarifs', 'tarifActif', 'batiment.propriete'])->find($id);
    }

    public function create(array $data): Porte
    {

        return DB::transaction(function () use ($data) {
            $tarifData = $data['tarif'] ?? null;
            $equipementsData = $data['equipements'] ?? null;
            unset($data['tarif']);
            unset($data['equipements']);
         //   dd($data,$tarifData);

         //   dd($data,$tarifData,$datas);
            $porte = Porte::create($data);

            if ($tarifData) {
                $this->updateTarif($porte, $tarifData);
            }
            if ($equipementsData && is_array($equipementsData)) {

                $equipementsIds = array_filter($equipementsData, fn($id) => !empty($id));
                //dd($equipementsIds);
                $porte->update(['equipements' => json_encode($equipementsIds)]);
            }

            return $porte->fresh(['typePorte', 'tarifActif']);
        });
    }

    public function update(Porte $porte, array $data): Porte
    {

        return DB::transaction(function () use ($porte, $data) {
            $tarifData = $data['tarif'] ?? null;
            $equipementsData = $data['equipements'] ?? null;

            unset($data['tarif']);
            unset($data['equipements']);


            $porte->update($data);

            if ($tarifData) {
                $this->updateTarif($porte, $tarifData);
            }


            // Gérer les équipements (relation many-to-many)
            if ($equipementsData && is_array($equipementsData)) {

                $equipementsIds = array_filter($equipementsData, fn($id) => !empty($id));
                //dd($equipementsIds);
                $porte->update(['equipements' => json_encode($equipementsIds)]);
            }
           // dd($data);
            return $porte->fresh(['typePorte', 'tarifActif']);
        });
    }

    public function delete(Porte $porte): bool
    {
        return $porte->update(['is_actif' => false]);
    }

    public function occuper(Porte $porte): bool
    {
        return $porte->update(['is_occupe' => true]);
    }

    public function liberer(Porte $porte): bool
    {
        return $porte->update(['is_occupe' => false]);
    }

    public function updateTarif(Porte $porte, array $tarifData): TarifPorte
    {
        // Désactiver l'ancien tarif actif
        TarifPorte::where('porte_id', $porte->porte_id)
            ->where('is_actif', true)
            ->update(['is_actif' => false]);

        $porte->update([
            'mt_loyer' => $porte->is_allocation ? ($tarifData['mt_loyer'] ?? 0) : 0,
            'mt_caution_cie' => $porte->is_allocation ? ($tarifData['mt_caution_cie'] ?? 0) : 0,
            'mt_caution_sodeci' => $porte->is_allocation ? ($tarifData['mt_caution_sodeci'] ?? 0) : 0,
            'mt_autre_frais' => $porte->is_allocation ? ($tarifData['mt_autre_frais'] ?? 0) : 0,
            'equipements' => $porte->equipements,
        ]);

        return TarifPorte::create([
            'porte_id'          => $porte->porte_id,
            'mt_loyer'          => $porte->is_allocation ? ($tarifData['mt_loyer'] ?? 0) : 0,
            'mt_vente'          => $porte->is_allocation ? null : ($tarifData['mt_vente'] ?? null),
            'mt_caution'        => $porte->is_allocation ? ($tarifData['mt_caution'] ?? 0) : 0,
            'mt_avance'         => $porte->is_allocation ? ($tarifData['mt_avance'] ?? 0) : 0,
            'mt_frais_agence'   => $porte->is_allocation ? ($tarifData['mt_frais_agence'] ?? 0) : 0,
            'mt_frais_dossier'  => $tarifData['mt_frais_dossier'] ?? null,
            'mt_caution_cie'    => $porte->is_allocation ? ($tarifData['mt_caution_cie'] ?? 0) : 0,
            'mt_caution_sodeci' => $porte->is_allocation ? ($tarifData['mt_caution_sodeci'] ?? 0) : 0,
            'date_effet'        => $tarifData['date_effet']        ?? now()->toDateString(),
            'is_actif'          => true,
            'created_at'        => now(),
        ]);
    }
}
