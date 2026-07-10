<?php

namespace App\Http\Requests\Agence;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Schema;

class StoreLocataireRequest extends FormRequest
{
   // public function authorize(): bool { return true; }

    public function rules(): array
    {
        $modePaiementRules = ['nullable', 'integer'];
        if (Schema::hasTable('mode_paiements')) {
            $modePaiementRules[] = 'exists:mode_paiements,id';
        }

        $periodicitePaiementRules = ['nullable', 'integer'];
        if (Schema::hasTable('periodicite_paiements')) {
            $periodicitePaiementRules[] = 'exists:periodicite_paiements,id';
        }

        return [
            // Identité
            'name'                   => ['required', 'string', 'max:150'],
            'tel1'                   => ['required', 'string', 'max:20'],
            'tel2'                   => ['nullable', 'string', 'max:20'],
            'email'                  => ['nullable', 'email', 'max:150'],
            'genre_id'               => ['required', 'string'],
            'date_naissance'         => ['nullable', 'date'],
            'lieu_naissance'         => ['nullable', 'string', 'max:150'],
            'nationalite'            => ['nullable', 'string', 'max:100'],
            'profession'             => ['nullable', 'string', 'max:100'],
            'adresse'                => ['nullable', 'string', 'max:300'],
            'ville_id'               => ['nullable', 'string'],
            'region_id'              => ['nullable', 'string'],


            // Pièce d'identité
            'type_piece_id'          => ['nullable', 'string'],
            'num_piece'              => ['nullable', 'string', 'max:50'],
            'date_expiration_piece'  => ['nullable', 'date', 'after_or_equal:today'],

            // Photos
            'photo'                  => ['nullable', 'image', 'max:2048'],
            'image_pice'             => ['nullable', 'image', 'max:2048'],

            // Arriérés
            'a_des_arrieres'         => ['nullable', 'boolean'],
            'is_new'                 => ['nullable', 'boolean'],
            'arrieres'               => ['nullable', 'array', 'required_if:a_des_arrieres,1'],
            'arrieres.*.mois'        => ['required_if:a_des_arrieres,1', 'string', 'regex:/^\d{4}-\d{2}$/'],
            'arrieres.*.montant'     => ['required_if:a_des_arrieres,1', 'numeric', 'min:0'],

            // Contrat (optionnel à la création)
            'contrat.porte_id'             => ['nullable', 'string', 'exists:porte,porte_id'],
            'contrat.propriete_id'         => ['nullable', 'string', 'exists:propriete,propriete_id'],
            'contrat.batiment_id'          => ['nullable', 'string', 'exists:batiment,batiment_id'],
            'contrat.proprietaire_id'      => ['nullable', 'string'],
            'contrat.loyer_net'            => ['nullable', 'numeric', 'min:0'],
            'contrat.nbre_personne'        => ['nullable', 'integer', 'min:1'],
            'contrat.caution'         => ['nullable', 'integer', 'min:0'],
            'contrat.avance'          => ['nullable', 'integer', 'min:0'],
            'contrat.agence'          => ['nullable', 'integer', 'min:0'],
            'contrat.caution_cie'          => ['nullable', 'numeric', 'min:0'],
            'contrat.caution_sodeci'       => ['nullable', 'numeric', 'min:0'],
            'contrat.frais_de_dossier'     => ['nullable', 'numeric', 'min:0'],
            'contrat.pas_de_porte'         => ['nullable', 'numeric', 'min:0'],
            'contrat.montant_global_garantie' => ['nullable', 'numeric', 'min:0'],
            'contrat.date_debut_bail'      => ['nullable', 'date'],
            'contrat.date_entree'          => ['nullable', 'date'],
            'contrat.date_signature_bail'  => ['nullable', 'date'],
            'contrat.periodicite_paiement_id' => $periodicitePaiementRules,
            'contrat.mode_paiement_id'     => $modePaiementRules,
            'contrat.nbre_enfant'          => ['nullable', 'integer', 'min:0', 'lt:contrat.nbre_personne'],
            'contrat.name_representant'    => ['nullable', 'string', 'max:150'],
            'contrat.adresse_representant' => ['nullable', 'string', 'max:300'],
            'contrat.contant_representant' => ['nullable', 'string', 'max:20'],
            'contrat.versements_depot_garantie' => ['nullable', 'array', 'required_if:is_new,1'],
            'contrat.versements_depot_garantie.*.montant' => ['required_if:is_new,1', 'nullable', 'numeric', 'min:0', 'lte:contrat.montant_global_garantie'],
            'contrat.versements_depot_garantie.*.date_versement' => ['required_if:is_new,1', 'nullable', 'date'],
            'contrat.versements_depot_garantie.*.mode_paiement_id' => array_merge(['required_if:is_new,1'], $modePaiementRules),
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le nom du locataire est obligatoire.',
            'tel1.required' => 'Le téléphone principal est obligatoire.',
            'genre_id.required' => 'Le genre est obligatoire.',
            'contrat.porte_id.exists' => 'La porte sélectionnée est introuvable.',
            'contrat.periodicite_paiement_id.exists' => 'La périodicité de paiement sélectionnée est introuvable.',
            'arrieres.required_if' => 'Veuillez renseigner les arriérés ou décochez la case.',
            'arrieres.*.mois.required_if' => 'Le mois de l\'arriéré est obligatoire.',
            'arrieres.*.montant.required_if' => 'Le montant de l\'arriéré est obligatoire.',
            'arrieres.*.mois.regex' => 'Le format du mois doit être AAAA-MM (ex: 2024-01).',
            'arrieres.*.montant.min' => 'Le montant doit être supérieur ou égal à 0.',
            'date_expiration_piece.after_or_equal' => "La date d'expiration ne peut pas être antérieure à aujourd'hui.",
            'contrat.frais_de_dossier.min' => 'Le frais de dossier doit être supérieur ou égal à 0.',
            'contrat.nbre_enfant.lt' => "Le nombre d'enfants doit être strictement inférieur au nombre de personnes.",
            'contrat.versements_depot_garantie.required_if' => 'Ajoutez au moins un versement du dépôt de garantie.',
            'contrat.versements_depot_garantie.*.montant.required_if' => 'Le montant versé est obligatoire.',
            'contrat.versements_depot_garantie.*.date_versement.required_if' => 'La date de versement est obligatoire.',
            'contrat.versements_depot_garantie.*.mode_paiement_id.required_if' => 'Sélectionnez un mode de paiement.',
            'contrat.versements_depot_garantie.*.montant.min' => 'Le montant versé doit être supérieur ou égal à 0.',
            'contrat.versements_depot_garantie.*.montant.lte' => 'Le montant versé ne peut pas dépasser la garantie globale.',
        ];
    }
}
