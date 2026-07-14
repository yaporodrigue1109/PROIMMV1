<?php

namespace App\Http\Requests\Agence;

use Illuminate\Foundation\Http\FormRequest;

class UpdateParametrageLogosRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'logo' => 'nullable|image|mimes:png,jpg,jpeg,svg|max:2048',
            'logo_tutelle' => 'nullable|image|mimes:png,jpg,jpeg,svg|max:2048',
            'logo_partenaire' => 'nullable|image|mimes:png,jpg,jpeg,svg|max:2048',
            'cachet' => 'nullable|image|mimes:png|max:2048',
            'logo_largeur' => 'nullable|integer|min:50|max:500',
            'logo_position' => 'nullable|in:gauche,centre,droit',
        ];
    }
}