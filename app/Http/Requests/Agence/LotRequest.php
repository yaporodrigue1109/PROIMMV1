<?php

namespace App\Http\Requests\Agence;

use Illuminate\Foundation\Http\FormRequest;

class LotRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'       => ['required', 'string', 'max:255'],
            'superficie' => ['nullable', 'integer', 'min:0'],
            'adresse'    => ['nullable', 'string', 'max:500'],
            'num_lot'    => ['nullable', 'string', 'max:100'],
            'num_ilot'   => ['nullable', 'string', 'max:100'],
            'region_id'  => ['nullable', 'integer', 'exists:regions,id'],
            'ville_id'   => ['nullable', 'integer', 'exists:villes,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le nom du lot est obligatoire.',
            'superficie.integer' => 'La superficie doit être un nombre entier.',
            'region_id.exists'   => 'La région sélectionnée est invalide.',
            'ville_id.exists'    => 'La ville sélectionnée est invalide.',
        ];
    }
}
