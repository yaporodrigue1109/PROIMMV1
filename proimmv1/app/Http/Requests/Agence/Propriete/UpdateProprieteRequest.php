<?php

namespace App\Http\Requests\Agence\Propriete;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateProprieteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reference'         => ['nullable', 'string', 'max:100'],
            'proprietaire_id'   => ['required', 'string', 'exists:proprietaires,proprietaire_id'],
            'lot_id'            => [
                'required',
                'string',
                Rule::exists('propietaire_lots', 'propreietaire_lot_id')
                    ->where(fn ($query) => $query->where('proprietaire_id', $this->input('proprietaire_id'))),
            ],
            'type_propriete_id' => ['nullable', 'integer', 'exists:type_proprietes,id'],
            'description'       => ['nullable', 'string', 'max:2000'],
            'adresse_complete'  => ['nullable', 'string', 'max:500'],
            'videos_url'        => ['nullable', 'array'],
            'videos_url.*'      => ['url'],
            'is_allocation'     => ['boolean'],
            'is_actif'          => ['boolean'],
            'proximites'        => ['nullable', 'array'],
            'proximites.*.id'   => ['required', 'string'],
            'proximites.*.distance' => ['nullable', 'integer', 'min:0'],
            'proximites.*.unite'    => ['nullable', Rule::in(['m', 'km'])],
            'batiments'                 => ['required', 'array', 'min:1'],
            'batiments.*.batiment_id'    => ['nullable', 'string'],
            'batiments.*.name'           => ['required', 'string', 'max:100'],
            'batiments.*.description'    => ['nullable', 'string', 'max:500'],
            'batiments.*.nbre_etages'    => ['nullable', 'integer', 'min:0', 'max:100'],
            'batiments.*.portes'                         => ['required', 'array', 'min:1'],
            'batiments.*.portes.*.porte_id'              => ['nullable', 'string'],
            'batiments.*.portes.*.type_porte_id'         => ['required', 'integer', 'exists:type_porte,type_porte_id'],
            'batiments.*.portes.*.numero_porte'          => ['required', 'string', 'max:20'],
            'batiments.*.portes.*.superficie_m2'         => ['nullable', 'integer', 'min:0'],
            'batiments.*.portes.*.etage'                 => ['nullable', 'integer', 'min:0'],
            'batiments.*.portes.*.is_allocation'         => ['required', 'boolean'],
            'batiments.*.portes.*.description'           => ['nullable', 'string', 'max:500'],
            'batiments.*.portes.*.equipements'           => ['nullable', 'array'],
            'batiments.*.portes.*.equipements.*'         => ['string'],
            'batiments.*.portes.*.tarif.mt_loyer'           => ['nullable', 'numeric', 'min:0'],
            'batiments.*.portes.*.tarif.mt_vente'           => ['nullable', 'numeric', 'min:0'],
            'batiments.*.portes.*.tarif.mt_caution'         => ['nullable', 'integer', 'min:0'],
            'batiments.*.portes.*.tarif.mt_avance'          => ['nullable', 'integer', 'min:0'],
            'batiments.*.portes.*.tarif.mt_frais_agence'    => ['nullable', 'integer', 'min:0'],
            'batiments.*.portes.*.tarif.mt_frais_dossier'    => ['nullable', 'numeric', 'min:0'],
            'batiments.*.portes.*.tarif.mt_autre_frais'      => ['nullable', 'numeric', 'min:0'],
            'batiments.*.portes.*.tarif.mt_caution_cie'     => ['nullable', 'numeric', 'min:0'],
            'batiments.*.portes.*.tarif.mt_caution_sodeci'  => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            foreach ($this->input('batiments', []) as $bIndex => $batiment) {
                foreach (($batiment['portes'] ?? []) as $pIndex => $porte) {
                    $prefix = "batiments.{$bIndex}.portes.{$pIndex}.tarif";
                    $isAllocation = filter_var($porte['is_allocation'] ?? true, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                    $isAllocation = $isAllocation === null ? true : $isAllocation;

                    if ($isAllocation) {
                        if (($porte['tarif']['mt_loyer'] ?? '') === '' || ($porte['tarif']['mt_loyer'] ?? null) === null) {
                            $validator->errors()->add("{$prefix}.mt_loyer", 'Le montant du loyer est obligatoire.');
                        }

                        if (($porte['tarif']['mt_caution'] ?? '') === '' || ($porte['tarif']['mt_caution'] ?? null) === null) {
                            $validator->errors()->add("{$prefix}.mt_caution", 'La caution est obligatoire.');
                        }

                        if (($porte['tarif']['mt_avance'] ?? '') === '' || ($porte['tarif']['mt_avance'] ?? null) === null) {
                            $validator->errors()->add("{$prefix}.mt_avance", 'L’avance est obligatoire.');
                        }

                        if (($porte['tarif']['mt_frais_agence'] ?? '') === '' || ($porte['tarif']['mt_frais_agence'] ?? null) === null) {
                            $validator->errors()->add("{$prefix}.mt_frais_agence", "Les frais d'agence sont obligatoires.");
                        }
                    } else {
                        if (($porte['tarif']['mt_vente'] ?? '') === '' || ($porte['tarif']['mt_vente'] ?? null) === null) {
                            $validator->errors()->add("{$prefix}.mt_vente", 'Le prix de vente est obligatoire.');
                        }
                    }
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'proprietaire_id.required'   => 'Le propriétaire est obligatoire.',
            'proximites.*.id.required'          => 'Sélectionnez une proximité.',
            'proximites.*.distance.required'    => 'La distance est obligatoire.',
            'proximites.*.unite.in'             => 'L’unité doit être en m ou km.',
            'batiments.required'                => 'Ajoutez au moins un bâtiment.',
            'batiments.*.name.required'         => 'Le nom du bâtiment est obligatoire.',
            'batiments.*.portes.required'       => 'Chaque bâtiment doit avoir au moins une porte.',
            'batiments.*.portes.*.type_porte_id.required'   => 'Le type de porte est obligatoire.',
            'batiments.*.portes.*.numero_porte.required'    => 'Le numéro de porte est obligatoire.',
            'batiments.*.portes.*.is_allocation.required'   => 'Choisissez Location ou Vente.',
        ];
    }
}
