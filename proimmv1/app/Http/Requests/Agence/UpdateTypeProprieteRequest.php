<?php

namespace App\Http\Requests\Agence;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class UpdateTypeProprieteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $agenceId = getInfoAgent()->users->agence_id;

        // Récupérer l'ID depuis la route (paramètre nommé "types_propriete")
        $currentId = $this->route('types_propriete');

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                // Exclure l'enregistrement courant du check unicité
                Rule::unique('type_proprietes')
                    ->where('agence_id', $agenceId)
                    ->ignore($currentId),
            ],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le nom du type est obligatoire.',
            'name.max'      => 'Le nom ne doit pas dépasser 100 caractères.',
            'name.unique'   => 'Ce type existe déjà dans votre agence.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name'        => trim($this->name ?? ''),
            'description' => $this->description ? trim($this->description) : null,
        ]);
    }
}