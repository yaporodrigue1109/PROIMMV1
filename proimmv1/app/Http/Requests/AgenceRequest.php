<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AgenceRequest extends FormRequest
{
    /**
     * Déterminer si l'utilisateur est autorisé à faire cette requête
     */
//    public function authorize(): bool
//    {
//        return auth()->check() && auth()->user()->can('manage.agences');
//    }

    /**
     * Obtenir les règles de validation
     */
    public function rules(): array
    {
        $agenceId = $this->route('agence');
        $isEdit   = $this->isMethod('PUT') || $this->isMethod('PATCH');

        return [
            // ── Identité ──────────────────────────────────────────────
            'name'    => ['required', 'string', 'max:255'],
            'adresse' => ['nullable', 'string', 'max:500'],
            'statut'  => ['required', Rule::in(['en_demo', 'active'])],

            // ── Contacts ──────────────────────────────────────────────
            'tel1' => [
                'nullable', 'string', 'max:20',
                $isEdit
                    ? Rule::unique('agences', 'tel1')->ignore($agenceId, 'agence_id')
                    : Rule::unique('agences', 'tel1'),
            ],
            'tel2'   => ['nullable', 'string', 'max:20'],
            'email1' => [
                'nullable', 'email', 'max:255',
                $isEdit
                    ? Rule::unique('agences', 'email1')->ignore($agenceId, 'agence_id')
                    : Rule::unique('agences', 'email1'),
            ],
            'email2' => ['nullable', 'email', 'max:255'],

            // ── Localisation ──────────────────────────────────────────
            'region'   => ['required', 'exists:regions,id'],
            'ville_id' => ['required', 'exists:villes,id'],

            // ── Responsable ───────────────────────────────────────────
            'responsable_mode' => ['required', Rule::in(['existing', 'new'])],

            // Responsable existant
            'responsable_id' => [
                Rule::requiredIf($this->input('responsable_mode') === 'existing'),
                'nullable',
                'exists:users,id_users',
            ],

            // Nouveau responsable
            'new_responsable_name' => [
                Rule::requiredIf($this->input('responsable_mode') === 'new'),
                'nullable', 'string', 'max:255',
            ],
            'new_responsable_email' => [
                Rule::requiredIf($this->input('responsable_mode') === 'new'),
                'nullable', 'email', 'max:255',
                'unique:users,email',
            ],
            'new_responsable_password' => [
                Rule::requiredIf($this->input('responsable_mode') === 'new'),
                'nullable', 'string', 'min:8', 'confirmed',
            ],
            'new_responsable_tel1'    => ['nullable', 'string', 'max:20'],
            'new_responsable_tel2'    => ['nullable', 'string', 'max:20'],
            'new_responsable_adresse' => ['nullable', 'string', 'max:500'],
            'new_responsable_photo'   => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],

            // ── Médias ────────────────────────────────────────────────
//            'logo'                   => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
//            'signature_responsable'  => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
//            'signature_comptabilite' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
//            'signature_marketing'    => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],

            // ── Abonnement ────────────────────────────────────────────
            'abonnement_start' => [
                Rule::requiredIf($this->input('statut') === 'active'),
                'nullable', 'date',
            ],
            'abonnement_end' => [
                Rule::requiredIf($this->input('statut') === 'active'),
                'nullable', 'date', 'after:abonnement_start',
            ],
            'duree_mois' => [
                Rule::requiredIf($this->input('statut') === 'active'),
                'nullable', 'integer', 'min:1',
            ],

            // ── Facturation ───────────────────────────────────────────
            'prix_base_mensuel'  => ['nullable', 'numeric', 'min:0'],
            'montant_total'      => ['nullable', 'numeric', 'min:0'],
            'montant_base_total' => ['nullable', 'numeric', 'min:0'],
            'options'            => ['nullable', 'array'],
            'options.*'            => ['integer'],
        ];
    }

    /**
     * Obtenir les messages d'erreur personnalisés
     */
    public function messages(): array
    {
        return [
            // ── Identité ──────────────────────────────────────────────
            'name.required'   => 'Le nom de l\'agence est obligatoire.',
            'name.string'     => 'Le nom de l\'agence doit être une chaîne de caractères.',
            'name.max'        => 'Le nom de l\'agence ne peut pas dépasser 255 caractères.',
            'adresse.string'  => 'L\'adresse doit être une chaîne de caractères.',
            'adresse.max'     => 'L\'adresse ne peut pas dépasser 500 caractères.',
            'statut.required' => 'Le statut est obligatoire.',
            'statut.in'       => 'Le statut doit être : En démo ou Active.',

            // ── Contacts ──────────────────────────────────────────────
            'tel1.string'     => 'Le numéro de téléphone doit être une chaîne de caractères.',
            'tel1.max'        => 'Le numéro de téléphone ne peut pas dépasser 20 caractères.',
            'tel1.unique'     => 'Ce numéro de téléphone est déjà utilisé par une autre agence.',
            'tel2.string'     => 'Le numéro de téléphone secondaire doit être une chaîne de caractères.',
            'tel2.max'        => 'Le numéro de téléphone secondaire ne peut pas dépasser 20 caractères.',
            'email1.email'    => 'Veuillez saisir une adresse email valide pour l\'email principal.',
            'email1.max'      => 'L\'email principal ne peut pas dépasser 255 caractères.',
            'email1.unique'   => 'Cet email principal est déjà utilisé par une autre agence.',
            'email2.email'    => 'Veuillez saisir une adresse email valide pour l\'email secondaire.',
            'email2.max'      => 'L\'email secondaire ne peut pas dépasser 255 caractères.',

            // ── Localisation ──────────────────────────────────────────
            'region.required'   => 'La région est obligatoire.',
            'region.exists'     => 'La région sélectionnée n\'existe pas dans la base de données.',
            'ville_id.required' => 'La ville est obligatoire.',
            'ville_id.exists'   => 'La ville sélectionnée n\'existe pas dans la base de données.',

            // ── Responsable ───────────────────────────────────────────
            'responsable_mode.required' => 'Veuillez choisir un mode de responsable.',
            'responsable_mode.in'       => 'Le mode de responsable doit être : Sélectionner un existant ou Créer un nouveau.',
            'responsable_id.required'   => 'Veuillez sélectionner un responsable.',
            'responsable_id.exists'     => 'Le responsable sélectionné n\'existe pas dans la base de données.',

            // Nouveau responsable
            'new_responsable_name.required'     => 'Le nom du responsable est obligatoire.',
            'new_responsable_name.string'       => 'Le nom du responsable doit être une chaîne de caractères.',
            'new_responsable_name.max'          => 'Le nom du responsable ne peut pas dépasser 255 caractères.',
            'new_responsable_email.required'    => 'L\'email du responsable est obligatoire.',
            'new_responsable_email.email'       => 'Veuillez saisir une adresse email valide pour le responsable.',
            'new_responsable_email.max'         => 'L\'email du responsable ne peut pas dépasser 255 caractères.',
            'new_responsable_email.unique'      => 'Cet email est déjà utilisé par un autre utilisateur.',
            'new_responsable_password.required' => 'Le mot de passe est obligatoire.',
            'new_responsable_password.string'   => 'Le mot de passe doit être une chaîne de caractères.',
            'new_responsable_password.min'      => 'Le mot de passe doit contenir au moins 8 caractères.',
            'new_responsable_password.confirmed'=> 'Les mots de passe ne correspondent pas.',
            'new_responsable_tel1.string'       => 'Le téléphone du responsable doit être une chaîne de caractères.',
            'new_responsable_tel1.max'          => 'Le téléphone du responsable ne peut pas dépasser 20 caractères.',
            'new_responsable_tel2.string'       => 'Le téléphone secondaire du responsable doit être une chaîne de caractères.',
            'new_responsable_tel2.max'          => 'Le téléphone secondaire du responsable ne peut pas dépasser 20 caractères.',
            'new_responsable_adresse.string'    => 'L\'adresse du responsable doit être une chaîne de caractères.',
            'new_responsable_adresse.max'       => 'L\'adresse du responsable ne peut pas dépasser 500 caractères.',
            'new_responsable_photo.image'       => 'La photo du responsable doit être une image.',
            'new_responsable_photo.mimes'       => 'La photo du responsable doit être au format : JPEG, PNG, JPG ou GIF.',
            'new_responsable_photo.max'         => 'La photo du responsable ne doit pas dépasser 2 Mo.',

            // ── Médias ────────────────────────────────────────────────
//            'logo.image'                   => 'Le logo doit être une image.',
//            'logo.mimes'                   => 'Le logo doit être au format : JPEG, PNG, JPG, GIF ou SVG.',
//            'logo.max'                     => 'Le logo ne doit pas dépasser 2 Mo.',
//            'signature_responsable.image'  => 'La signature du responsable doit être une image.',
//            'signature_responsable.mimes'  => 'La signature du responsable doit être au format : JPEG, PNG, JPG ou GIF.',
//            'signature_responsable.max'    => 'La signature du responsable ne doit pas dépasser 2 Mo.',
//            'signature_comptabilite.image' => 'La signature comptabilité doit être une image.',
//            'signature_comptabilite.mimes' => 'La signature comptabilité doit être au format : JPEG, PNG, JPG ou GIF.',
//            'signature_comptabilite.max' => 'La signature comptabilité ne doit pas dépasser 2 Mo.',
//            'signature_marketing.image'    => 'La signature marketing doit être une image.',
//            'signature_marketing.mimes'    => 'La signature marketing doit être au format : JPEG, PNG, JPG ou GIF.',
//            'signature_marketing.max'      => 'La signature marketing ne doit pas dépasser 2 Mo.',

            // ── Abonnement ────────────────────────────────────────────
            'abonnement_start.required' => 'La date de début d\'abonnement est requise pour une agence active.',
            'abonnement_start.date'      => 'La date de début d\'abonnement doit être une date valide.',
            'abonnement_end.required'    => 'La date de fin d\'abonnement est requise pour une agence active.',
            'abonnement_end.date'        => 'La date de fin d\'abonnement doit être une date valide.',
            'abonnement_end.after'       => 'La date de fin doit être postérieure à la date de début.',
            'duree_mois.required'        => 'La durée d\'abonnement est requise pour une agence active.',
            'duree_mois.integer'         => 'La durée d\'abonnement doit être un nombre entier.',
            'duree_mois.min'             => 'La durée d\'abonnement doit être d\'au moins 1 mois.',

            // ── Facturation ───────────────────────────────────────────
            'prix_base_mensuel.numeric'  => 'Le prix de base mensuel doit être un nombre.',
            'prix_base_mensuel.min'      => 'Le prix de base mensuel doit être positif.',
            'montant_total.numeric'      => 'Le montant total doit être un nombre.',
            'montant_total.min'          => 'Le montant total doit être positif.',
            'montant_base_total.numeric' => 'Le montant de base total doit être un nombre.',
            'montant_base_total.min'     => 'Le montant de base total doit être positif.',
            'options.array'              => 'Les options doivent être une liste.',
            'options.*.integer'          => 'Chaque option doit être un identifiant valide.',
        ];
    }

    /**
     * Préparer les données avant validation
     */
    protected function prepareForValidation(): void
    {
        // Convertir les chaînes vides en null
        $this->merge([
            'email2'  => $this->email2 ?: null,
            'tel2'    => $this->tel2 ?: null,
            'adresse' => $this->adresse ?: null,
        ]);
    }
}