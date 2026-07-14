<?php

namespace App\Http\Requests\Agence\Propriete;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePorteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'batiment_id'           => ['required', 'string', 'exists:batiment,batiment_id'],
            'type_porte_id'         => ['required', 'integer', 'exists:type_porte,type_porte_id'],
            'numero_porte'          => [
                'required', 'string', 'max:20',
                Rule::unique('porte')->where('batiment_id', $this->batiment_id),
            ],
            'superficie_m2'         => ['nullable', 'integer', 'min:1'],
            'etage'                 => ['nullable', 'integer', 'min:0'],
            'description'           => ['nullable', 'string', 'max:500'],

            // Tarif
            'tarif.mt_loyer'           => ['required', 'numeric', 'min:0'],
            'tarif.mt_caution'         => ['required', 'integer', 'min:0'],
            'tarif.mt_avance'          => ['required', 'integer', 'min:0'],
            'tarif.mt_frais_agence'    => ['required', 'integer', 'min:0'],
            'tarif.mt_frais_dossier'   => ['nullable', 'numeric', 'min:0'],
            'tarif.mt_caution_cie'     => ['nullable', 'numeric', 'min:0'],
            'tarif.mt_caution_sodeci'  => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'batiment_id.required'          => 'Le bâtiment est obligatoire.',
            'type_porte_id.required'        => 'Le type de porte est obligatoire.',
            'numero_porte.required'         => 'Le numéro de porte est obligatoire.',
            'numero_porte.unique'           => 'Ce numéro existe déjà dans ce bâtiment.',
            'tarif.mt_loyer.required'       => 'Le montant du loyer est obligatoire.',
        ];
    }
}
