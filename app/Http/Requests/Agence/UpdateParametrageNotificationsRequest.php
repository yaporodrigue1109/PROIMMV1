<?php

namespace App\Http\Requests\Agence;

use Illuminate\Foundation\Http\FormRequest;

class UpdateParametrageNotificationsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('user')->check();
    }

    public function rules(): array
    {
        return [
            'notif_rappel' => ['required', 'boolean'],
            'notif_retard' => ['required', 'boolean'],
            'notif_recu' => ['required', 'boolean'],
            'email_compta' => ['nullable', 'email', 'max:255'],
            'email_dg' => ['nullable', 'email', 'max:255'],
            'delai_rappel' => ['required', 'integer', 'min:1', 'max:90'],
            'seuil_dg' => ['required', 'numeric', 'min:0'],
        ];
    }
}
