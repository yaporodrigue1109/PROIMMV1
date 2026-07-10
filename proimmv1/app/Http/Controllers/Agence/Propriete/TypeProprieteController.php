<?php

namespace App\Http\Controllers\Agence\Propriete;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Agence\StoreTypeProprieteRequest;
use App\Http\Requests\AgenceUpdateTypeProprieteRequest;
use App\Repositories\Agence\Interfaces\TypeProprieteRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;

class TypeProprieteController extends Controller
{
    protected  $service;
    public function __construct(
        TypeProprieteRepositoryInterface $service
    ) {
        $this->service = $service;
    }

    // ─────────────────────────────────────────────────────────────
    // STORE
    // ─────────────────────────────────────────────────────────────

    public function store(Request $request): RedirectResponse
    {
        try {
           // dd($request->all());
            $data = $request->all();

            $this->service->create($data);

            return redirect()
                ->route('agence.proprietes.index', ['#panel-types'])
                ->with('success', 'Type « ' . $request->name . ' » créé avec succès.');

        } catch (\Exception $e) {
            dd($e->getMessage());
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────
    // UPDATE
    // ─────────────────────────────────────────────────────────────

    public function update(UpdateTypeProprieteRequest $request, int $types_propriete): RedirectResponse
    {
        try {
            $this->service->update($types_propriete, $request->validated());

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

    public function destroy(int $types_propriete): RedirectResponse
    {
        try {
           // dd($types_propriete);
            $this->service->delete($types_propriete);

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