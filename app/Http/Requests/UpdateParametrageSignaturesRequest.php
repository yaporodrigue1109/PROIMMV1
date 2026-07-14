<?php

namespace App\Http\Requests\Agence;

use Illuminate\Foundation\Http\FormRequest;

class UpdateParametrageSignaturesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'signature_dg' => 'nullable|image|mimes:png|max:500',
            'dg_nom' => 'nullable|string|max:255',
            'dg_titre' => 'nullable|string|max:255',
            'signature_sg' => 'nullable|image|mimes:png|max:500',
            'sg_nom' => 'nullable|string|max:255',
            'sg_titre' => 'nullable|string|max:255',
            'signature_cpt' => 'nullable|image|mimes:png|max:500',
            'cpt_nom' => 'nullable|string|max:255',
            'cpt_titre' => 'nullable|string|max:255',
            'sig_dg_facture' => 'nullable|boolean',
            'sig_double' => 'nullable|boolean',
            'cachet_auto' => 'nullable|boolean',
        ];
    }
}