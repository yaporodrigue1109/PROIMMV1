<?php


namespace App\Http\Requests\Agence;

use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PersonnelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('id');
        $isEdit = $this->isMethod('PUT') || $this->isMethod('PATCH');
        $roleRules = Schema::hasTable('roles')
            ? ['required', 'string', Rule::exists('roles', 'role_id')]
            : ['required', 'string', Rule::in([
                'role-responsable',
                'role-agent',
                'role-comptable',
                'role-technicien',
            ])];

        return [
            'name'      => ['required', 'string', 'max:255'],
            'adresse'   => ['required', 'string', 'max:500'],
            'email'     => [
                'nullable', 'email', 'max:255',
                $isEdit
                    ? 'unique:users,email,' . $userId . ',id_users'
                    : 'unique:users,email',
            ],
            'tel1'      => ['required', 'string', 'max:20'],
            'tel2'      => ['nullable', 'string', 'max:20'],
            'role_id'   => $roleRules,
            'password'  => [$isEdit ? 'nullable' : 'required', 'string', 'min:8'],
            'photo'     => ['nullable', 'image', 'max:2048'],
            'statut'    => ['nullable', 'in:actif,inactif,suspendu'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'     => 'Le nom est obligatoire.',
            'adresse.required'  => 'L\'adresse est obligatoire.',
            'email.unique'      => 'Cet email est déjà utilisé.',
            'tel1.required'     => 'Le contact 1 est obligatoire.',
            'role_id.required'  => 'Le rôle est obligatoire.',
            'role_id.exists'    => 'Le rôle sélectionné est invalide.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.min'      => 'Le mot de passe doit contenir au moins 8 caractères.',
            'photo.image'       => 'Le fichier doit être une image.',
            'photo.max'         => 'L\'image ne doit pas dépasser 2 Mo.',
        ];
    }
}
