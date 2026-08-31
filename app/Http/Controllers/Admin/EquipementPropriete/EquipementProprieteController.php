<?php

namespace App\Http\Controllers\Admin\EquipementPropriete;

use App\Http\Controllers\Controller;
use App\Models\EquipementPropriete;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class EquipementProprieteController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->input('search', ''));
        $equipements = EquipementPropriete::query()
            ->where(fn ($query) => $query->whereNull('agence_id')->orWhere('agence_id', ''))
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            }))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Proximites/Index', [
            'proximites' => $equipements,
            'filters' => ['search' => $search],
            'resource' => [
                'title' => 'Équipements globaux',
                'singular' => 'équipement',
                'description' => 'Ces équipements sont visibles par toutes les agences, en lecture seule.',
                'endpoint' => '/admin/equipements-proprietes',
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        EquipementPropriete::query()->create($this->validated($request));
        return back()->with('success', 'Équipement global créé avec succès.');
    }

    public function update(Request $request, EquipementPropriete $equipements_propriete): RedirectResponse
    {
        abort_unless(blank($equipements_propriete->agence_id), 404);
        $equipements_propriete->update($this->validated($request, $equipements_propriete));
        return back()->with('success', 'Équipement global mis à jour.');
    }

    public function destroy(EquipementPropriete $equipements_propriete): RedirectResponse
    {
        abort_unless(blank($equipements_propriete->agence_id), 404);
        if ($this->isUsed($equipements_propriete)) {
            return back()->with('error', 'Cet équipement est utilisé par une ou plusieurs portes et ne peut pas être supprimé.');
        }
        $equipements_propriete->delete();
        return back()->with('success', 'Équipement global supprimé.');
    }

    private function validated(Request $request, ?EquipementPropriete $equipement = null): array
    {
        return $request->validate([
            'name' => [
                'required', 'string', 'max:150',
                Rule::unique('equipement_proprietes', 'name')->whereNull('agence_id')->ignore($equipement?->getKey()),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);
    }

    private function isUsed(EquipementPropriete $equipement): bool
    {
        // Les équipements des portes sont historiquement stockés en JSON.
        return \App\Models\Porte::query()
            ->whereJsonContains('equipements', (string) $equipement->getKey())
            ->orWhereJsonContains('equipements', $equipement->getKey())
            ->exists();
    }
}
