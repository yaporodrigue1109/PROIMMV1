<?php

namespace App\Http\Requests\Agence;

use Illuminate\Foundation\Http\FormRequest;

class UpdateParametrageFacturationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'periode_facturation' => 'nullable|in:mensuelle,trimestrielle,semestrielle,annuelle,commande',
            'jour_emission' => 'nullable|string',
            'delai_paiement' => 'nullable|integer|min:0|max:180',
            'penalite_retard' => 'nullable|numeric|min:0|max:100',
            'prefixe_facture' => 'nullable|string|max:50',
            'sequence_facture' => 'nullable|integer|min:1',
            'commission' => 'nullable|numeric|min:0|max:100',
            'base_commission' => 'nullable|in:ht,ttc,brut',
            'tva' => 'nullable|numeric|min:0|max:100',
            'aib' => 'nullable|numeric|min:0|max:100',
            'ras' => 'nullable|numeric|min:0|max:100',
            'acompte_min' => 'nullable|numeric|min:0|max:100',
            'mode_reglement' => 'nullable|in:virement,cheque,especes,mobile_money',
        ];
    }
}