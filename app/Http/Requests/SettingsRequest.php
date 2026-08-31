<?php

namespace App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;

class SettingsRequest extends FormRequest
{
    /**
     * Déterminer si l'utilisateur est autorisé à faire cette requête
     */
//    public function authorize(): bool
//    {
//        return auth()->check() && auth()->user()->hasPermission('settings.update');
//    }

    /**
     * Obtenir les règles de validation
     */
    public function rules(): array
    {
        return [
            // Informations générales
            'name' => 'required|string|max:255',
            'raison_social' => 'required|string|max:255',
            'adresse' => 'required|string|max:500',
            'boite_postal' => 'nullable|string|max:50',

            // Contacts
            'email1' => 'required|email|max:255',
            'email2' => 'nullable|email|max:255',
            'contact1' => 'required|string|max:20',
            'contact2' => 'nullable|string|max:20',
            'contact3' => 'nullable|string|max:20',

            // Paiement temporaire des abonnements agence
            'subscription_manual_payment_enabled' => 'required|boolean',
            'subscription_wave_number' => 'nullable|string|max:30',
            'subscription_orange_money_number' => 'nullable|string|max:30',
            'subscription_moov_money_number' => 'nullable|string|max:30',
            'subscription_mtn_money_number' => 'nullable|string|max:30',

            // Web et localisation
            'site_web' => 'nullable|url|max:255',
            'langue' => 'required|in:fr,en',

            // Informations légales
            'num_rccm' => 'nullable|string|max:50',
            'num_cc' => 'nullable|string|max:50',
            'num_cnps' => 'nullable|string|max:50',
            'capital' => 'nullable|numeric|min:0',

            // Politiques
            'politique_confidentialite' => 'nullable|string',
            'condition_generale' => 'nullable|string',
            'cgu' => 'nullable|string',
            'mention_legale' => 'nullable|string',

            // Réseaux sociaux
            'facebook' => 'nullable|url|max:255',
            'instagram' => 'nullable|url|max:255',
            'linkedin' => 'nullable|url|max:255',
            'twitter' => 'nullable|url|max:255',
            'google' => 'nullable|url|max:255',

            // Fichiers médias
            'logo' => 'nullable|image|mimes:jpeg,png,gif,webp|max:2048',
            'flavicon' => 'nullable|image|mimes:jpeg,png,gif,x-icon|max:1024',
        ];
    }

    /**
     * Obtenir les messages d'erreur personnalisés
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Le nom commercial est requis.',
            'name.max' => 'Le nom commercial ne doit pas dépasser 255 caractères.',

            'raison_social.required' => 'La raison sociale est requise.',
            'raison_social.max' => 'La raison sociale ne doit pas dépasser 255 caractères.',

            'adresse.required' => 'L\'adresse est requise.',
            'adresse.max' => 'L\'adresse ne doit pas dépasser 500 caractères.',

            'email1.required' => 'L\'email principal est requis.',
            'email1.email' => 'L\'email principal doit être une adresse valide.',
            'email2.email' => 'L\'email secondaire doit être une adresse valide.',

            'contact1.required' => 'Le téléphone principal est requis.',

            'site_web.url' => 'Le site web doit être une URL valide.',

            'logo.image' => 'Le logo doit être une image.',
            'logo.mimes' => 'Le logo doit être au format JPEG, PNG, GIF ou WebP.',
            'logo.max' => 'Le logo ne doit pas dépasser 2MB.',

            'flavicon.image' => 'Le favicon doit être une image.',
            'flavicon.mimes' => 'Le favicon doit être au format JPEG, PNG, GIF ou ICO.',
            'flavicon.max' => 'Le favicon ne doit pas dépasser 1MB.',

            'facebook.url' => 'L\'URL Facebook doit être valide.',
            'instagram.url' => 'L\'URL Instagram doit être valide.',
            'linkedin.url' => 'L\'URL LinkedIn doit être valide.',
            'twitter.url' => 'L\'URL Twitter doit être valide.',
            'google.url' => 'L\'URL Google Business doit être valide.',
        ];
    }

    /**
     * Préparer les données pour la validation
     */
    protected function prepareForValidation(): void
    {
        $normalizeUrl = static function ($value): ?string {
            $value = trim((string) $value);

            if ($value === '') {
                return null;
            }

            return preg_match('#^https?://#i', $value) ? $value : 'https://' . $value;
        };

        // Convertir les URLs vides en null
        $this->merge([
            'email2' => $this->email2 ?: null,
            'contact2' => $this->contact2 ?: null,
            'contact3' => $this->contact3 ?: null,
            'subscription_manual_payment_enabled' => $this->boolean('subscription_manual_payment_enabled'),
            'subscription_wave_number' => $this->subscription_wave_number ?: null,
            'subscription_orange_money_number' => $this->subscription_orange_money_number ?: null,
            'subscription_moov_money_number' => $this->subscription_moov_money_number ?: null,
            'subscription_mtn_money_number' => $this->subscription_mtn_money_number ?: null,
            'boite_postal' => $this->boite_postal ?: null,
            'site_web' => $normalizeUrl($this->site_web),
            'num_rccm' => $this->num_rccm ?: null,
            'num_cc' => $this->num_cc ?: null,
            'num_cnps' => $this->num_cnps ?: null,
            'capital' => $this->capital ?: null,
            'facebook' => $normalizeUrl($this->facebook),
            'instagram' => $normalizeUrl($this->instagram),
            'linkedin' => $normalizeUrl($this->linkedin),
            'twitter' => $normalizeUrl($this->twitter),
            'google' => $normalizeUrl($this->google),
        ]);
    }
}
