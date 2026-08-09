<?php

namespace App\Http\Requests\Admin;

use App\Models\Module;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class ModuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'parent_id' => in_array($this->input('parent'), [null, '', 'none'], true)
                ? null
                : $this->input('parent'),
            'is_active' => $this->input('status') === 'Actif',
        ]);
    }

    public function rules(): array
    {
        $module = null;

        if (Schema::hasTable('modules') && $this->route('code')) {
            $module = Module::query()->where('slug', $this->route('code'))->first();
        }

        $slugRule = Rule::unique('modules', 'slug');
        if ($module) {
            $slugRule->ignore($module->module_id, 'module_id');
        }

        return [
            'name' => ['required', 'string', 'max:150'],
            'slug' => Schema::hasTable('modules')
                ? ['required', 'string', 'max:150', 'alpha_dash:ascii', $slugRule]
                : ['required', 'string', 'max:150', 'alpha_dash:ascii'],
            'route' => ['nullable', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:255'],
            'parent_id' => Schema::hasTable('modules')
                ? [
                    'nullable',
                    'uuid',
                    Rule::exists('modules', 'module_id')
                        ->whereNull('deleted_at')
                        ->whereNull('parent_id'),
                    Rule::notIn(array_filter([$module?->module_id])),
                ]
                : ['nullable'],
            'status' => ['required', Rule::in(['Actif', 'Inactif'])],
            'is_active' => ['required', 'boolean'],
            'actions' => ['present', 'array'],
            'actions.*.module_action_id' => ['nullable', 'uuid'],
            'actions.*.label' => ['required', 'string', 'max:150'],
            'actions.*.slug' => ['required', 'string', 'max:100', 'alpha_dash:ascii', 'distinct'],
            'actions.*.order' => ['required', 'integer', 'min:0'],
            'actions.*.is_critical' => ['sometimes', 'boolean'],
            'actions.*.is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le nom du module est obligatoire.',
            'slug.required' => 'Le slug du module est obligatoire.',
            'slug.alpha_dash' => 'Le slug ne peut contenir que des lettres, chiffres, tirets et underscores.',
            'slug.unique' => 'Ce slug est déjà utilisé par un autre module.',
            'parent_id.exists' => 'Le module parent sélectionné est invalide.',
            'parent_id.not_in' => 'Un module ne peut pas être son propre parent.',
            'actions.*.label.required' => 'Le nom de chaque action est obligatoire.',
            'actions.*.slug.required' => 'Le slug de chaque action est obligatoire.',
            'actions.*.slug.distinct' => 'Les slugs des actions doivent être uniques.',
            'actions.*.slug.alpha_dash' => 'Le slug d’une action contient des caractères invalides.',
            'actions.*.order.required' => 'L’ordre de chaque action est obligatoire.',
        ];
    }
}
