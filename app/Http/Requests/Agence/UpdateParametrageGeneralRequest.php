<?php

namespace App\Http\Requests\Agence;

use Illuminate\Foundation\Http\FormRequest;

class UpdateParametrageGeneralRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'devise' => 'nullable|in:XOF,EUR,USD',
            'langue' => 'nullable|in:fr,en',
            'format_date' => 'nullable|string',
            'timezone' => 'nullable|string',
            'sauvegarde_auto' => 'nullable|boolean',
            'double_validation' => 'nullable|boolean',
            'journal_activites' => 'nullable|boolean',
            'multi_session' => 'nullable|boolean',
        ];
    }
}