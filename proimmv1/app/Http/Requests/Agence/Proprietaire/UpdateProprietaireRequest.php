<?php

namespace App\Http\Requests\Agence\Proprietaire;

use App\Models\Proprietaire;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProprietaireRequest extends FormRequest
{
    public function rules(): array
    {
        $proprietaireId = $this->route('id');

        return [
            'name' => 'required|string|max:255',
            'tel1' => "required|string|max:20|unique:proprietaires,tel1,{$proprietaireId},proprietaire_id",
            'tel2' => 'nullable|string|max:20',
            'type_pieces_id' => 'nullable|integer|exists:type_pieces,type_pieces_id',
            'numpiece' => "nullable|string|max:100|unique:proprietaires,numpiece,{$proprietaireId},proprietaire_id",
            'email' => "nullable|email|max:255|unique:proprietaires,email,{$proprietaireId},proprietaire_id",
            'profession' => 'nullable|string|max:255',
            'nationalite' => 'nullable|string|max:100',
            'date_naiss' => 'nullable|date',
            'lieu_naiss' => 'nullable|string|max:255',
            'region_id' => 'nullable|integer|exists:regions,id',
            'ville_id' => 'nullable|integer|exists:villes,id',
            'adresse' => 'nullable|string|max:500',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'has_representant' => 'nullable|boolean',

            // Représentant (liaison agence)
            'name_representant' => 'required_if:has_representant,1,true,on,yes|nullable|string|max:255',
            'genre_representant_id' => 'nullable|integer|exists:genres,id',
            'adresse_representant' => 'nullable|string|max:500',
            'tel1_representant' => 'required_if:has_representant,1,true,on,yes|nullable|string|max:20',
            'tel2_representant' => 'nullable|string|max:20',
            'email_representant' => 'nullable|email|max:255',
            'type_pieces_representant_id' => 'required_if:has_representant,1,true,on,yes|nullable|integer|exists:type_pieces,type_pieces_id',
            'numpiece_representant' => 'required_if:has_representant,1,true,on,yes|nullable|string|max:100',
            'photo_representant' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (!$this->boolean('has_representant')) {
                return;
            }

            $proprietaireId = $this->route('id');
            $agenceId = getInfoAgent()->users->agence_id ?? null;

            if (!$agenceId) {
                return;
            }

            $proprietaire = Proprietaire::with([
                'agences' => fn ($query) => $query->where('agence_id', $agenceId),
            ])
                ->whereHas('agences', fn ($query) => $query->where('agence_id', $agenceId))
                ->find($proprietaireId);

            $liaison = $proprietaire?->agences?->first();

        });
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le nom du propriétaire est obligatoire.',
            'tel1.required' => 'Le téléphone principal est obligatoire.',
            'tel1.unique' => 'Ce numéro de téléphone est déjà utilisé.',
            'email.email' => "L'adresse email n'est pas valide.",
            'email.unique' => 'Cet email est déjà utilisé.',
            'numpiece.unique' => 'Ce numéro de pièce est déjà utilisé.',
            'photo.image' => 'La photo doit être une image.',
            'photo.max' => 'La photo ne doit pas dépasser 2 Mo.',
            'tel1_representant.required_if' => 'Le premier téléphone du représentant est obligatoire.',
            'type_pieces_representant_id.required_if' => 'Le type de pièce du représentant est obligatoire.',
            'numpiece_representant.required_if' => 'Le numéro de pièce du représentant est obligatoire.',
            'photo_representant.image' => 'La photo du représentant doit être une image.',
            'photo_representant.max' => 'La photo du représentant ne doit pas dépasser 2 Mo.',
        ];
    }
}
