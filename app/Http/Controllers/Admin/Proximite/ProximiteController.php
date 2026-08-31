<?php

namespace App\Http\Controllers\Admin\Proximite;

use App\Http\Controllers\Controller;
use App\Models\ProprieteProximite;
use App\Models\ProssimitePropriete;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ProximiteController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->input('search', ''));
        $proximites = ProssimitePropriete::query()
            ->where(fn ($query) => $query->whereNull('agence_id')->orWhere('agence_id', ''))
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            }))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Proximites/Index', [
            'proximites' => $proximites,
            'filters' => ['search' => $search],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        ProssimitePropriete::query()->create($this->validated($request));

        return back()->with('success', 'Proximité globale créée avec succès.');
    }

    public function update(Request $request, ProssimitePropriete $proximite): RedirectResponse
    {
        abort_unless(blank($proximite->agence_id), 404);
        $proximite->update($this->validated($request, $proximite));

        return back()->with('success', 'Proximité globale mise à jour.');
    }

    public function destroy(ProssimitePropriete $proximite): RedirectResponse
    {
        abort_unless(blank($proximite->agence_id), 404);

        if (ProprieteProximite::query()->where('proximite_id', $proximite->getKey())->exists()) {
            return back()->with('error', 'Cette proximité est utilisée par une ou plusieurs propriétés et ne peut pas être supprimée.');
        }

        $proximite->delete();

        return back()->with('success', 'Proximité globale supprimée.');
    }

    private function validated(Request $request, ?ProssimitePropriete $proximite = null): array
    {
        return $request->validate([
            'name' => [
                'required', 'string', 'max:150',
                Rule::unique('prossimite_proprietes', 'name')
                    ->whereNull('agence_id')
                    ->ignore($proximite?->getKey()),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);
    }
}
