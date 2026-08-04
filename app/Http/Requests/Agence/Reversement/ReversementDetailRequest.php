<?php

namespace App\Http\Requests\Agence\Reversement;

use Illuminate\Foundation\Http\FormRequest;

class ReversementDetailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reversement_id' => 'required|string|exists:reversements,id_reversement',
            'locataire_id' => 'required|integer|exists:locataire,locataire_id',
            'porte_id' => 'required|string|exists:porte,porte_id',
            'agence_id' => 'required|string|exists:agences,agence_id',
            'proprietaire_id' => 'required|string|exists:proprietaires,proprietaire_id',
            'lot_id' => 'required|string|exists:propietaire_lots,propreietaire_lot_id',
            'propriete_id' => 'required|string|exists:propriete,propriete_id',
            'batiment_id' => 'required|string|exists:batiment,batiment_id',
            'montant_loyer' => 'required|integer|min:0',
            'arrieres_init' => 'required|integer|min:0',
            'loyer_paye' => 'required|integer|min:0',
            'arriere_paye' => 'required|integer|min:0'
        ];
    }

    public function messages(): array
    {
        return [
            'reversement_id.required' => 'L\'ID du reversement est obligatoire',
            'reversement_id.exists' => 'Le reversement sélectionné n\'existe pas',
            'locataire_id.required' => 'Le locataire est obligatoire',
            'locataire_id.exists' => 'Le locataire sélectionné n\'existe pas',
            'porte_id.required' => 'La porte est obligatoire',
            'porte_id.exists' => 'La porte sélectionnée n\'existe pas',
            'montant_loyer.required' => 'Le montant du loyer est obligatoire',
            'montant_loyer.min' => 'Le montant du loyer doit être supérieur ou égal à 0',
            'arrieres_init.required' => 'Le montant des arriérés est obligatoire',
            'arrieres_init.min' => 'Le montant des arriérés doit être supérieur ou égal à 0'
        ];
    }
}