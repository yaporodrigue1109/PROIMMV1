<?php

namespace App\Http\Controllers\Agence\Propriete;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Repositories\Agence\Interfaces\ProssimiteProprieteRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;

class ProssimiteProprieteController extends Controller
{
    protected  $service;
    public function __construct(
        ProssimiteProprieteRepositoryInterface $service
    ) {
        $this->service = $service;
    }

    // ─────────────────────────────────────────────────────────────
    // STORE
    // ─────────────────────────────────────────────────────────────

    public function store(Request $request): RedirectResponse
    {
        try {
            $data = $request->validate([
                'name' => ['required', 'string', 'max:150'],
                'description' => ['nullable', 'string', 'max:1000'],
            ]);

            $this->service->create($data);

            return redirect()
                ->route('agence.proprietes.index', ['#panel-types'])
                ->with('success', 'Proximité « ' . $request->name . ' » créée avec succès.');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────
    // UPDATE
    // ─────────────────────────────────────────────────────────────

    public function update(Request $request, int $prossimite): RedirectResponse
    {
        try {
            $data = $request->validate([
                'name' => ['required', 'string', 'max:150'],
                'description' => ['nullable', 'string', 'max:1000'],
            ]);
            $item = $this->service->findById($prossimite);
            abort_unless($item && $item->agence_id === getInfoAgent()->users->agence_id, 403);
            $this->service->update($item, $data);

            return redirect()
                ->route('agence.proprietes.index', ['#panel-types'])
                ->with('success', 'Type mis à jour avec succès.');

        } catch (ModelNotFoundException) {
            return back()->with('error', 'Type introuvable.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────
    // DESTROY
    // ─────────────────────────────────────────────────────────────

    public function destroy(int $prossimite): RedirectResponse
    {
        try {
            // dd($types_propriete);
            $item = $this->service->findById($prossimite);
            abort_unless($item && $item->agence_id === getInfoAgent()->users->agence_id, 403);
            $this->service->delete($item);

            return redirect()
                ->route('agence.proprietes.index', ['#panel-types'])
                ->with('success', 'Type supprimé avec succès.');

        } catch (ModelNotFoundException) {
            return back()->with('error', 'Type introuvable.');
        } catch (\Exception $e) {
            // Ex: "Impossible de supprimer … propriété(s) l'utilisent."
            return back()->with('error', $e->getMessage());
        }
    }
}
