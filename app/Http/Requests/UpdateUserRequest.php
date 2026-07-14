<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Ajouter la logique d'autorisation si nécessaire
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $userId = $this->route('user') ?? $this->user()->id_users;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId, 'id_users'),
            ],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'adresse' => ['nullable', 'string', 'max:500'],
            'agence_id' => ['nullable', 'exists:agences,id'],
            'is_responsable' => ['boolean'],
            'role_id' => ['required', 'exists:roles,id'],
            'tel1' => [
                'required',
                'string',
                'regex:/^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$/',
            ],
            'tel2' => [
                'nullable',
                'string',
                'regex:/^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$/',
            ],
            'statut' => ['required', Rule::in(['actif', 'inactif', 'suspendu'])],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Le nom est obligatoire',
            'name.string' => 'Le nom doit être un texte',
            'name.max' => 'Le nom ne peut pas dépasser 255 caractères',

            'email.required' => 'L\'email est obligatoire',
            'email.email' => 'L\'email doit être valide',
            'email.unique' => 'Cet email est déjà utilisé',

            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères',
            'password.confirmed' => 'Les mots de passe ne correspondent pas',

            'agence_id.exists' => 'L\'agence sélectionnée n\'existe pas',
            'role_id.required' => 'Le rôle est obligatoire',
            'role_id.exists' => 'Le rôle sélectionné n\'existe pas',

            'tel1.required' => 'Le téléphone principal est obligatoire',
            'tel1.regex' => 'Le format du téléphone n\'est pas valide',
            'tel2.regex' => 'Le format du téléphone n\'est pas valide',

            'statut.required' => 'Le statut est obligatoire',
            'statut.in' => 'Le statut doit être : actif, inactif ou suspendu',

            'photo.image' => 'Le fichier doit être une image',
            'photo.mimes' => 'L\'image doit être au format : jpeg, png, jpg ou gif',
            'photo.max' => 'L\'image ne peut pas dépasser 2MB',
        ];
    }

    /**
     * Get the validated data from the request.
     */
    public function validated($key = null, $default = null)
    {
        $validated = parent::validated($key, $default);

        // Hasher le mot de passe seulement s'il a été fourni
        if (isset($validated['password']) && !empty($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        } else {
            unset($validated['password']);
        }

        // Traiter la photo s'il y en a une
        if ($this->hasFile('photo')) {
            $validated['photo'] = $this->file('photo')->store('users', 'public');
        } else {
            unset($validated['photo']);
        }

        return $validated;
    }
}