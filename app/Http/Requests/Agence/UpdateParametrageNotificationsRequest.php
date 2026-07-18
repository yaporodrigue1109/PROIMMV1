<?php

namespace App\Http\Requests\Agence;

use Illuminate\Foundation\Http\FormRequest;

class UpdateParametrageNotificationsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'notif_rappel' => 'nullable|boolean',
            'notif_retard' => 'nullable|boolean',
            'notif_recu' => 'nullable|boolean',
            'email_compta' => 'nullable|email|max:255',
            'email_dg' => 'nullable|email|max:255',
            'delai_rappel' => 'nullable|integer|min:1',
            'seuil_dg' => 'nullable|numeric|min:0',
        ];
    }
}
