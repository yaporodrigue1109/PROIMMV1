<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ReversementResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id_reversement,
            'lot_id' => $this->lot_id,
            'lot_nom' => $this->lot->nom ?? null,
            'proprietaire_id' => $this->proprietaire_id,
            'proprietaire_nom' => $this->proprietaire->nom ?? null,
            'agence_id' => $this->agence_id,
            'agence_nom' => $this->agence->nom ?? null,
            'periode_debut' => $this->periode_debut->format('Y-m-d'),
            'periode_fin' => $this->periode_fin->format('Y-m-d'),
            'total_attendu' => $this->total_attendu,
            'total_encaisse' => $this->total_encaisse,
            'total_restant' => $this->total_restant,
            'total_loyer_paye' => $this->total_loyer_paye,
            'total_arriere_paye' => $this->total_arriere_paye,
            'taux_commission' => $this->taux_commission,
            'montant_commission' => $this->montant_commission,
            'montant_apres_commission' => $this->montant_apres_commission,
            'nouvelle_caution' => $this->nouvelle_caution,
            'depenses_effectuees' => $this->depenses_effectuees,
            'net_a_reverser' => $this->net_a_reverser,
            'statut' => $this->statut,
            'statut_label' => $this->getStatutLabel(),
            'date_reversement' => $this->date_reversement?->format('Y-m-d'),
            'mode_paiement' => $this->mode_paiement,
            'reference_paiement' => $this->reference_paiement,
            'numero_cheque' => $this->numero_cheque,
            'observation' => $this->observation,
            'signe_par' => $this->signe_par,
            'date_signature' => $this->date_signature?->format('Y-m-d H:i:s'),
            'pourcentage_encaisse' => $this->pourcentage_encaisse,
            'est_complet' => $this->est_complet,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'details_count' => $this->details->count(),
            'details' => ReversementDetailResource::collection($this->whenLoaded('details'))
        ];
    }

    protected function getStatutLabel(): string
    {
        return match($this->statut) {
            'en_attente' => 'En attente',
            'reverse' => 'Reversé',
            'annule' => 'Annulé',
            default => $this->statut
        };
    }
}