<?php

namespace App\Http\Controllers\Admin\TypePropriete;

use App\Http\Controllers\Controller;
use App\Models\TypePropriete;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class TypeProprieteController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->input('search', ''));
        $types = TypePropriete::query()
            ->where(fn ($query) => $query->whereNull('agence_id')->orWhere('agence_id', ''))
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            }))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Proximites/Index', [
            'proximites' => $types,
            'filters' => ['search' => $search],
            'resource' => [
                'title' => 'Types de propriétés globaux',
                'singular' => 'type de propriété',
                'description' => 'Ces types sont visibles par toutes les agences, en lecture seule.',
                'endpoint' => '/admin/types-proprietes',
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        TypePropriete::query()->create($this->validated($request));
        return back()->with('success', 'Type de propriété global créé avec succès.');
    }

    public function update(Request $request, TypePropriete $types_propriete): RedirectResponse
    {
        abort_unless(blank($types_propriete->agence_id), 404);
        $types_propriete->update($this->validated($request, $types_propriete));
        return back()->with('success', 'Type de propriété global mis à jour.');
    }

    public function destroy(TypePropriete $types_propriete): RedirectResponse
    {
        abort_unless(blank($types_propriete->agence_id), 404);
        if ($types_propriete->proprietes()->exists()) {
            return back()->with('error', 'Ce type est utilisé par une ou plusieurs propriétés et ne peut pas être supprimé.');
        }
        $types_propriete->delete();
        return back()->with('success', 'Type de propriété global supprimé.');
    }

    private function validated(Request $request, ?TypePropriete $type = null): array
    {
        return $request->validate([
            'name' => [
                'required', 'string', 'max:100',
                Rule::unique('type_proprietes', 'name')->whereNull('agence_id')->ignore($type?->getKey()),
            ],
            'description' => ['nullable', 'string', 'max:500'],
        ]);
    }
}
