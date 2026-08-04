<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ReversementDetailResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id_reversement_detail,
            'reversement_id' => $this->reversement_id,
            'locataire_id' => $this->locataire_id,
            'locataire_nom' => $this->locataire->nom ?? null,
            'porte_id' => $this->porte_id,
            'porte_numero' => $this->porte->numero ?? null,
            'agence_id' => $this->agence_id,
            'proprietaire_id' => $this->proprietaire_id,
            'lot_id' => $this->lot_id,
            'propriete_id' => $this->propriete_id,
            'batiment_id' => $this->batiment_id,
            'montant_loyer' => $this->montant_loyer,
            'arrieres_init' => $this->arrieres_init,
            'montant_attendu' => $this->montant_attendu,
            'loyer_paye' => $this->loyer_paye,
            'arriere_paye' => $this->arriere_paye,
            'total_paye' => $this->total_paye,
            'impayes' => $this->impayes,
            'est_paye' => $this->est_paye,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s')
        ];
    }
}