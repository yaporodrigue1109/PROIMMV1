<?php

namespace App\Http\Requests\Agence;


use Illuminate\Foundation\Http\FormRequest;

class PayerLoyerRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Adaptez selon votre gestion des rôles/permissions
        // (ex: return $this->user()->can('encaisser-loyer'); )
        return true;
    }

    public function rules(): array
    {
        return [
            'locataire_id' => ['required', 'exists:locataire_agence,locataire_id'],
            'agence_id' => ['required', 'exists:locataire_agence,agence_id'],
            'propriete_id' => ['required', 'exists:locataire_agence,propriete_id'],
            'batiment_id' => ['required', 'exists:locataire_agence,batiment_id'],
            'lot_id' => ['required', 'exists:locataire_agence,lot_id'],
            'porte_id' => ['required', 'exists:locataire_agence,porte_id'],
            'montant' => ['required', 'numeric', 'min:1'],
            'mode_paiement_id' => ['required', 'integer'],
            'commentaire' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'locataire_id.required' => 'Veuillez sélectionner un locataire.',
            'locataire_id.exists' => 'Ce locataire est introuvable.',
            'agence_id.required' => 'Veuillez sélectionner une agence.',
            'agence_id.exists' => 'Cette agence est introuvable.',
            'propriete_id.required' => 'Veuillez sélectionner une propriété.',
            'propriete_id.exists' => 'Cette propriété est introuvable.',
            'batiment_id.required' => 'Veuillez sélectionner un bâtiment.',
            'batiment_id.exists' => 'Ce bâtiment est introuvable.',
            'lot_id.required' => 'Veuillez sélectionner un lot.',
            'lot_id.exists' => 'Ce lot est introuvable.',
            'porte_id.required' => 'Veuillez sélectionner la porte concernée.',
            'porte_id.exists' => 'Cette porte est introuvable.',
            'montant.required' => 'Veuillez saisir le montant versé.',
            'montant.numeric' => 'Le montant doit être un nombre.',
            'montant.min' => 'Le montant doit être supérieur à 0.',
            'mode_paiement_id.required' => 'Veuillez sélectionner un mode de paiement.',
        ];
    }
}
