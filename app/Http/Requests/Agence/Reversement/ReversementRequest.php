<?php

namespace App\Http\Requests\Agence\Reversement;


use Illuminate\Foundation\Http\FormRequest;

class ReversementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'lot_id' => 'required|string|exists:propietaire_lots,propreietaire_lot_id',
            'proprietaire_id' => 'required|string|exists:proprietaires,proprietaire_id',
            'agence_id' => 'required|string|exists:agences,agence_id',
            'periode_debut' => 'required|date',
            'periode_fin' => 'required|date|after_or_equal:periode_debut',
            'taux_commission' => 'nullable|numeric|min:0|max:100',
            'observation' => 'nullable|string'
        ];
    }

    public function messages(): array
    {
        return [
            'lot_id.required' => 'Le lot est obligatoire',
            'lot_id.exists' => 'Le lot sélectionné n\'existe pas',
            'proprietaire_id.required' => 'Le propriétaire est obligatoire',
            'proprietaire_id.exists' => 'Le propriétaire sélectionné n\'existe pas',
            'agence_id.required' => 'L\'agence est obligatoire',
            'agence_id.exists' => 'L\'agence sélectionnée n\'existe pas',
            'periode_debut.required' => 'La date de début est obligatoire',
            'periode_fin.required' => 'La date de fin est obligatoire',
            'periode_fin.after_or_equal' => 'La date de fin doit être postérieure ou égale à la date de début',
            'taux_commission.numeric' => 'Le taux de commission doit être un nombre',
            'taux_commission.min' => 'Le taux de commission doit être au minimum 0',
            'taux_commission.max' => 'Le taux de commission doit être au maximum 100'
        ];
    }
}