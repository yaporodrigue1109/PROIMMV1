<?php

namespace App\Http\Requests\Agence;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class StoreTypeProprieteRequest extends FormRequest
{
//    public function authorize(): bool
//    {
//        return true;
//    }

    public function rules(): array
    {
        $agenceId = getInfoAgent()->users->agence_id;

        return [
            'name' => 'required|string|max:150',
            'description' => 'nullable|string|max:150',
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

    /**
     * Préparer les données avant validation :
     * nettoyer les espaces superflus.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name'        => trim($this->name ?? ''),
            'description' => $this->description ? trim($this->description) : null,
        ]);
    }
}